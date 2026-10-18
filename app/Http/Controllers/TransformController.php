<?php

namespace App\Http\Controllers;

use App\Models\Convertion;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\InventoryTransaction;
use App\Models\Menu;
use App\Models\Transform;
use App\Models\TransformItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TransformController extends Controller
{
    /**
     * Get the current user's store_code and branch_code
     */
    private function getUserStoreAndBranch()
    {
        return [
            'store_code' => Auth::user()->store_code ?? 'store-001',
            'branch_code' => Auth::user()->branch_code ?? '',
        ];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Transform::with(['source.brand', 'createdBy', 'approvedBy'])
            ->where('is_deleted', 0)
            ->withCount('items');

        if ($user->store_code) {
            $query->whereHas('source', function ($q) use ($user) {
                $q->where('store_code', $user->store_code);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transform_no', 'like', '%' . $search . '%')
                    ->orWhere('approval_reference', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('source', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%')
                            ->orWhereHas('brand', function ($q3) use ($search) {
                                $q3->where('name', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('createdBy', function ($q2) use ($search) {
                        $q2->where('firstname', 'like', '%' . $search . '%')
                            ->orWhere('lastname', 'like', '%' . $search . '%')
                            ->orWhere('middlename', 'like', '%' . $search . '%')
                            ->orWhere('username', 'like', '%' . $search . '%')
                            ->orWhere('business_name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('date_from')) {
            $from = \Carbon\Carbon::parse($request->date_from)->format('Y-m-d H:i:s');
            $query->where('date', '>=', $from);
        }

        if ($request->filled('date_to')) {
            $to = \Carbon\Carbon::parse($request->date_to)->format('Y-m-d H:i:s');
            $query->where('date', '<=', $to);
        }

        $transforms = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('user.transform.index', compact('transforms'));
    }

    public function create()
    {
        $storeCode = Auth::user()->store_code;
        
        $menus = Menu::where('is_deleted', 0)
            ->where('store_code', $storeCode)
            ->with('brand')
            ->orderBy('name', 'asc')
            ->get();

        $retailMenus = DB::table('menus')
            ->join('menu_categories', 'menus.category_id', '=', 'menu_categories.id')
            ->where('menus.is_deleted', 0)
            ->where('menus.store_code', $storeCode)
            ->select('menus.*')
            ->distinct()
            ->get();

        $retailMenuIds = $retailMenus->pluck('id')->toArray();
        $retailMenus = Menu::whereIn('id', $retailMenuIds)
            ->with('brand')
            ->orderBy('name', 'asc')
            ->get();

        $inventories = Inventory::where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();

        return view('user.transform.create', compact('menus', 'retailMenus', 'inventories'));
    }

    public function store(Request $request)
    {
        Log::info('=== TRANSFORM STORE START ===');
        
        $request->validate([
            'date' => 'required|date',
            'source_inventory_id' => 'required|integer|exists:menus,id',
            'source_quantity' => 'required|numeric|min:0.01',
            'source_unit' => 'required|string',
            'transformed_item_id' => 'required|array|min:1',
            'transformed_item_id.*' => 'required|integer|exists:menus,id',
            'transformed_quantity' => 'required|array|min:1',
            'transformed_quantity.*' => 'required|numeric|min:0.01',
            'transformed_unit' => 'required|array|min:1',
            'transformed_unit.*' => 'required|string',
            'status' => 'required|string',
        ]);

        $sourceInventory = Menu::find($request->source_inventory_id);
        if (!$sourceInventory) {
            return back()->withInput()->with('error', 'Source menu item not found.');
        }

        $sourceQuantity = floatval($request->source_quantity);
        $sourceInventoryUnit = $this->normalizeUnit($request->source_unit);
        $sourceQuantityInBase = $this->convertToUnit($sourceInventory, $sourceQuantity, $sourceInventoryUnit);

        $sourceEffectiveQuantity = $this->getEffectiveQuantity($sourceInventory);
        if ($sourceQuantityInBase > $sourceEffectiveQuantity) {
            return back()->withInput()->with('error', 'Insufficient stock. Required: ' . number_format($sourceQuantityInBase, 2) . ', Available: ' . number_format($sourceEffectiveQuantity, 2));
        }

        try {
            DB::beginTransaction();

            $userInfo = $this->getUserStoreAndBranch();
            $storeCode = $userInfo['store_code'];
            $branchCode = $userInfo['branch_code'];
            
            $setting = \App\Models\Setting::where('store_code', $storeCode)->first();
            $prefix = $setting ? ($setting->tr_number_1st ?? 'LTC') : 'LTC';
            
            $sourceItemName = $sourceInventory->name ?? $sourceInventory->item_name ?? 'Unknown Item';
            
            $outputItemNames = [];
            foreach ($request->transformed_item_id as $itemId) {
                $itemMenu = Menu::find($itemId);
                if ($itemMenu) {
                    $outputItemNames[] = $itemMenu->name ?? $itemMenu->item_name ?? 'Unknown Item';
                }
            }
            $outputItemName = implode(', ', $outputItemNames);

            $status = $request->status;

            // Generate the transform number first
            $tempId = DB::table('transforms')->max('id') + 1;
            $transformNo = $prefix . date('mdHis') . '-APR-' . str_pad($tempId, 3, '0', STR_PAD_LEFT);
            
            // Generate approval reference for pending status
            $approvalReference = null;
            if ($status === 'PENDING') {
                $approvalReference = $prefix . date('mdHis') . '-ADJ-' . str_pad($tempId, 3, '0', STR_PAD_LEFT);
            }

            $transform = Transform::create([
                'menu_id' => $sourceInventory->id,
                'transform_no' => $transformNo,
                'slug' => Str::random(60),
                'date' => $request->date,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
                'status' => $status,
                'source_inventory_id' => $sourceInventory->id,
                'source_quantity' => $sourceQuantity,
                'source_unit' => $sourceInventoryUnit,
                'waste_quantity' => $request->waste_quantity ?: null,
                'waste_unit' => $request->waste_unit ?: null,
                'waste_description' => $request->waste_description ?: null,
                'store_code' => $storeCode,
                'branch_code' => $branchCode,
                'approval_reference' => $approvalReference,
            ]);

            foreach ($request->transformed_item_id as $index => $itemId) {
                $itemQuantity = floatval($request->transformed_quantity[$index]);
                $itemUnit = $this->normalizeUnit($request->transformed_unit[$index]);
                
                $itemMenu = Menu::find($itemId);
                if (!$itemMenu) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Transformed menu item not found.');
                }

                TransformItem::create([
                    'transform_id' => $transform->id,
                    'inventory_id' => null,
                    'menu_id' => $itemMenu->id,
                    'quantity' => $itemQuantity,
                    'unit' => $itemUnit,
                ]);
            }

            // Save "For Approval" inventory movements - deduct source and record waste
            $this->saveForApprovalMovements($transform, $sourceInventory, $sourceQuantityInBase, $request, $sourceItemName, $outputItemName, $storeCode, $branchCode);
            
            // Deduct source quantity
            $sourceInventory->decrement('quantity', $sourceQuantityInBase);
            if ($this->hasCurrentQuantityField($sourceInventory)) {
                $sourceInventory->decrement('current_quantity', $sourceQuantityInBase);
            }

            // If direct COMPLETED
            if ($status === 'COMPLETED') {
                $transform->approved_by = Auth::id();
                $transform->save();
                
                $this->saveApprovedMovements($transform, $sourceInventory, $sourceItemName, $outputItemName, $storeCode, $branchCode);
            }

            DB::commit();
            
            Log::info('=== TRANSFORM STORE COMPLETED SUCCESSFULLY ===');
            
            if ($status === 'COMPLETED') {
                return redirect()->route('transform.index')->with('message', 'Transform record created and completed successfully.');
            } else {
                return redirect()->route('transform.index')->with('message', 'Transform record created successfully. Awaiting approval.');
            }
            
        } catch (\Throwable $exception) {
            Log::error('=== TRANSFORM STORE FAILED: ' . $exception->getMessage() . ' ===');
            Log::error('File: ' . $exception->getFile() . ':' . $exception->getLine());
            DB::rollBack();
            return back()->withInput()->with('error', 'Unable to save transform: ' . $exception->getMessage());
        }
    }
    
    public function show($slug)
    {
        $transform = Transform::with(['source.brand', 'createdBy', 'approvedBy', 'items.menu', 'items.inventory'])
            ->where('slug', $slug)
            ->where('is_deleted', 0)
            ->first();

        if (!$transform) {
            abort(404);
        }

        return view('user.transform.show', compact('transform'));
    }

    public function edit($slug)
    {
        $transform = Transform::with(['source.brand', 'items.menu', 'items.inventory'])
            ->where('slug', $slug)
            ->where('is_deleted', 0)
            ->first();

        if (!$transform) {
            abort(404);
        }

        if (!in_array($transform->status, ['PENDING', 'REJECTED'])) {
            return redirect()->route('transform.show', $transform->slug)
                ->with('error', 'Only pending or rejected transforms can be edited.');
        }

        return view('user.transform.edit', compact('transform'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|exists:transforms,slug',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
            'status' => 'required|string',
            'waste_quantity' => 'nullable|numeric|min:0',
            'waste_unit' => 'nullable|string',
            'waste_description' => 'nullable|string',
        ]);

        $transform = Transform::where('slug', $request->slug)->first();
        if (!$transform) {
            return back()->with('error', 'Transform record not found.');
        }

        if (!in_array($transform->status, ['PENDING', 'REJECTED'])) {
            return back()->with('error', 'Only pending or rejected transforms can be updated.');
        }

        try {
            DB::beginTransaction();

            $userInfo = $this->getUserStoreAndBranch();
            $storeCode = $userInfo['store_code'];
            $branchCode = $userInfo['branch_code'];

            $sourceInventory = Menu::find($transform->source_inventory_id);
            $sourceItemName = $sourceInventory ? ($sourceInventory->name ?? $sourceInventory->item_name ?? 'Unknown Item') : 'Unknown Item';
            
            $outputItemNames = [];
            foreach ($transform->items as $item) {
                if ($item->menu) {
                    $outputItemNames[] = $item->menu->name ?? $item->menu->item_name ?? 'Unknown Item';
                }
            }
            $outputItemName = implode(', ', $outputItemNames);

            $transform->update([
                'date' => $request->date,
                'remarks' => $request->remarks,
                'status' => $request->status,
                'waste_quantity' => $request->waste_quantity ?: null,
                'waste_unit' => $request->waste_unit ?: null,
                'waste_description' => $request->waste_description ?: null,
                'store_code' => $storeCode,
                'branch_code' => $branchCode,
            ]);

            if ($request->status === 'COMPLETED') {
                $transform->approved_by = Auth::id();
                
                // Generate approval reference if not exists
                if (empty($transform->approval_reference)) {
                    $setting = \App\Models\Setting::where('store_code', $storeCode)->first();
                    $prefix = $setting ? ($setting->tr_number_1st ?? 'LTC') : 'LTC';
                    $transform->approval_reference = $prefix . date('mdHis') . '-APR-' . str_pad($transform->id, 3, '0', STR_PAD_LEFT);
                }
                $transform->save();
                
                $this->saveApprovedMovements($transform, $sourceInventory, $sourceItemName, $outputItemName, $storeCode, $branchCode);
            } else {
                $transform->save();
            }

            DB::commit();
            
            Log::info('Transform updated', [
                'transform_id' => $transform->id,
                'new_status' => $request->status
            ]);
            
            return redirect()->route('transform.index')->with('message', 'Transform record updated successfully.');
        } catch (\Throwable $exception) {
            Log::error('Update error: ' . $exception->getMessage());
            DB::rollBack();
            return back()->with('error', 'Unable to update transform: ' . $exception->getMessage());
        }
    }

    /**
     * APPROVE - Add outputs, mark as COMPLETED
     * Note: Waste is already saved during pending stage
     */
    public function approve(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|exists:transforms,slug',
        ]);

        $transform = Transform::where('slug', $request->slug)->first();
        if (!$transform) {
            return back()->with('error', 'Transform record not found.');
        }

        if ($transform->status !== 'PENDING') {
            return back()->with('error', 'Only pending transforms can be approved. Current status: ' . $transform->status);
        }

        try {
            DB::beginTransaction();

            $userInfo = $this->getUserStoreAndBranch();
            $storeCode = $userInfo['store_code'];
            $branchCode = $userInfo['branch_code'];
            
            $sourceInventory = Menu::find($transform->source_inventory_id);
            
            if (!$sourceInventory) {
                DB::rollBack();
                return back()->with('error', 'Source inventory item not found.');
            }
            
            $sourceItemName = $sourceInventory->name ?? $sourceInventory->item_name ?? 'Unknown Item';
            
            $outputItemNames = [];
            foreach ($transform->items as $item) {
                if ($item->menu) {
                    $outputItemNames[] = $item->menu->name ?? $item->menu->item_name ?? 'Unknown Item';
                }
            }
            $outputItemName = implode(', ', $outputItemNames);

            // Generate new approval reference if not already set
            if (empty($transform->approval_reference)) {
                $setting = \App\Models\Setting::where('store_code', $storeCode)->first();
                $prefix = $setting ? ($setting->tr_number_1st ?? 'LTC') : 'LTC';
                $transform->approval_reference = $prefix . date('mdHis') . '-ARP-' . str_pad($transform->id, 3, '0', STR_PAD_LEFT);
                $transform->save();
            }

            $this->saveApprovedMovements($transform, $sourceInventory, $sourceItemName, $outputItemName, $storeCode, $branchCode);

            // Update status
            $transform->status = 'COMPLETED';
            $transform->approved_by = Auth::id();
            $transform->save();
            
            DB::commit();
            
            Log::info('Transform approved', [
                'transform_id' => $transform->id,
                'transform_no' => $transform->transform_no,
                'approval_reference' => $transform->approval_reference
            ]);
            
            return redirect()->route('transform.index')->with('message', 'Transform record approved successfully.');
        } catch (\Throwable $exception) {
            Log::error('Approve error: ' . $exception->getMessage());
            Log::error('File: ' . $exception->getFile() . ':' . $exception->getLine());
            DB::rollBack();
            return back()->with('error', 'Unable to approve transform: ' . $exception->getMessage());
        }
    }

    /**
     * REJECT - Revert source deduction, save reverted movements, put reason in remarks
     * Note: Waste is already saved during pending stage and should remain
     */
    public function reject(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|exists:transforms,slug',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $transform = Transform::where('slug', $request->slug)->first();
        if (!$transform) {
            return back()->with('error', 'Transform record not found.');
        }

        if ($transform->status !== 'PENDING') {
            return back()->with('error', 'Only pending transforms can be rejected. Current status: ' . $transform->status);
        }

        try {
            DB::beginTransaction();

            $userInfo = $this->getUserStoreAndBranch();
            $storeCode = $userInfo['store_code'];
            $branchCode = $userInfo['branch_code'];

            $sourceInventory = Menu::find($transform->source_inventory_id);
            $sourceItemName = $sourceInventory ? ($sourceInventory->name ?? $sourceInventory->item_name ?? 'Unknown Item') : 'Unknown Item';
            
            $outputItemNames = [];
            foreach ($transform->items as $item) {
                if ($item->menu) {
                    $outputItemNames[] = $item->menu->name ?? $item->menu->item_name ?? 'Unknown Item';
                }
            }
            $outputItemName = implode(', ', $outputItemNames);

            // Save Reverted movements and revert source deduction
            $this->saveRevertedMovements($transform, $sourceInventory, $sourceItemName, $outputItemName, $storeCode, $branchCode);

            // Build rejection remarks
            $rejectionNote = 'Rejected by ' . Auth::user()->firstname . ' ' . Auth::user()->lastname;
            if ($request->filled('rejection_reason')) {
                $rejectionNote .= ': ' . $request->rejection_reason;
            }
            
            // Append rejection to existing remarks
            $existingRemarks = $transform->remarks ?? '';
            $newRemarks = $existingRemarks 
                ? $existingRemarks . ' | ' . $rejectionNote 
                : $rejectionNote;

            // Update status and remarks
            $transform->status = 'REJECTED';
            $transform->approved_by = Auth::id();
            $transform->remarks = $newRemarks;
            $transform->save();
            
            DB::commit();
            
            Log::info('Transform rejected', [
                'transform_id' => $transform->id,
                'transform_no' => $transform->transform_no,
                'rejected_by' => Auth::id()
            ]);
            
            return redirect()->route('transform.index')->with('message', 'Transform record rejected successfully. Source quantity has been reverted.');
        } catch (\Throwable $exception) {
            Log::error('Reject error: ' . $exception->getMessage());
            DB::rollBack();
            return back()->with('error', 'Unable to reject transform: ' . $exception->getMessage());
        }
    }

    private function saveForApprovalMovements($transform, $sourceInventory, $sourceQuantityInBase, $request, $sourceItemName, $outputItemName, $storeCode, $branchCode = '')
    {
        if ($branchCode === null || $branchCode === '') {
            $userInfo = $this->getUserStoreAndBranch();
            $branchCode = $userInfo['branch_code'];
        }
        $branchCode = $branchCode ?? '';
        
        // Use transform_no for pending movements
        $referenceNumber = $transform->transform_no;
        
        $sourceQuantityBefore = $sourceInventory->quantity;
        $sourceCurrentQuantityBefore = $sourceInventory->current_quantity ?? $sourceQuantityBefore;
        $sourceCurrentQuantityAfter = $sourceCurrentQuantityBefore - $sourceQuantityInBase;

        // Save source movement - For Approval
        $sourceMovement = new InventoryMovement();
        $sourceMovement->type = 'TRANSFORMATION';
        $sourceMovement->menu_id = $sourceInventory->id;
        $sourceMovement->trans_id = $transform->id;
        $sourceMovement->trans_number = $transform->transform_no;
        $sourceMovement->reference = $referenceNumber;
        $sourceMovement->detail = 'Transform ' . $sourceItemName . ' to ' . $outputItemName . ' - For Approval';
        $sourceMovement->previous_qty = $sourceCurrentQuantityBefore;
        $sourceMovement->qty_in = 0;
        $sourceMovement->qty_out = $sourceQuantityInBase;
        $sourceMovement->remaining = $sourceCurrentQuantityAfter;
        $sourceMovement->sold_price = 0;
        $sourceMovement->cost = 0;
        $sourceMovement->branch_code = $branchCode;
        $sourceMovement->store_code = $storeCode;
        $sourceMovement->is_sync = 'no';
        $sourceMovement->save();

        // Save source transaction
        $sourceTransaction = new InventoryTransaction();
        $sourceTransaction->inv_id = $sourceInventory->id;
        $sourceTransaction->department_id = $sourceInventory->department ?? null;
        $sourceTransaction->category_id = $sourceInventory->category_id ?? null;
        $sourceTransaction->supplier_id = 1;
        $sourceTransaction->type = 'OUTGOING';
        $sourceTransaction->quantity_before = $sourceCurrentQuantityBefore;
        $sourceTransaction->quantity = $request ? $request->source_quantity : $transform->source_quantity;
        $sourceTransaction->quantity_after = $sourceCurrentQuantityAfter;
        $sourceTransaction->amount = 0;
        $sourceTransaction->remarks = 'Transform ' . $transform->transform_no . ' (source) - For Approval';
        $sourceTransaction->user_id = Auth::id();
        $sourceTransaction->store_code = $storeCode;
        $sourceTransaction->branch_code = $branchCode;
        $sourceTransaction->save();

        // SAVE WASTE TO INVENTORY MOVEMENT - For Approval (RECORD ONLY, NO DEDUCTION)
        if ($transform->waste_quantity && $transform->waste_quantity > 0) {
            $wasteQuantity = floatval($transform->waste_quantity);
            $wasteUnit = $transform->waste_unit ? $this->normalizeUnit($transform->waste_unit) : $transform->source_unit;
            
            try {
                $wasteQuantityInBase = $this->convertToUnit($sourceInventory, $wasteQuantity, $wasteUnit);
            } catch (\Exception $e) {
                Log::warning('Waste quantity conversion failed, using original quantity', [
                    'error' => $e->getMessage(),
                    'waste_quantity' => $wasteQuantity,
                    'waste_unit' => $wasteUnit
                ]);
                $wasteQuantityInBase = $wasteQuantity;
            }
            
            // Save waste to inventory_movement - For Approval (LOG ONLY)
            $wasteMovement = new InventoryMovement();
            $wasteMovement->type = 'WASTE';
            $wasteMovement->menu_id = $sourceInventory->id;
            $wasteMovement->trans_id = $transform->id;
            $wasteMovement->trans_number = $transform->transform_no;
            $wasteMovement->reference = $referenceNumber;
            $wasteMovement->detail = 'Waste from transformation: ' . $sourceItemName . ' to ' . $outputItemName . ' - ' . ($transform->waste_description ?? '') . ' - For Approval';
            $wasteMovement->unit = $wasteUnit;
            $wasteMovement->previous_qty = 0; //ken update it to 0 $sourceCurrentQuantityAfter
            $wasteMovement->qty_in = $wasteQuantityInBase;
            $wasteMovement->qty_out = 0;
            $wasteMovement->remaining = 0; //ken update it to 0 sourceCurrentQuantityAfter // NO DEDUCTION - waste is just logged
            $wasteMovement->sold_price = 0;
            $wasteMovement->cost = 0;
            $wasteMovement->branch_code = $branchCode;
            $wasteMovement->store_code = $storeCode;
            $wasteMovement->is_sync = 'no';
            $wasteMovement->save();
            
            // Save waste transaction - For Approval (LOG ONLY)
            $wasteTransaction = new InventoryTransaction();
            $wasteTransaction->inv_id = $sourceInventory->id;
            $wasteTransaction->department_id = $sourceInventory->department ?? null;
            $wasteTransaction->category_id = $sourceInventory->category_id ?? null;
            $wasteTransaction->supplier_id = 1;
            $wasteTransaction->type = 'WASTE';
            $wasteTransaction->quantity_before = 0; //ken update it to 0 $sourceCurrentQuantityBefore;
            $wasteTransaction->quantity = $wasteQuantity;
            $wasteTransaction->quantity_after = 0; //ken update it to 0 $sourceCurrentQuantityAfter; // NO CHANGE - waste doesn't affect quantity
            $wasteTransaction->amount = 0;
            $wasteTransaction->remarks = 'Waste from Transform ' . $transform->transform_no . ' - For Approval';
            $wasteTransaction->user_id = Auth::id();
            $wasteTransaction->store_code = $storeCode;
            $wasteTransaction->branch_code = $branchCode;
            $wasteTransaction->save();
            
            Log::info('Waste recorded to inventory_movement - For Approval (no quantity deduction)', [
                'transform_id' => $transform->id,
                'waste_quantity' => $wasteQuantityInBase,
                'source_item' => $sourceItemName,
                'reference' => $referenceNumber,
                'remaining_unchanged' => $sourceCurrentQuantityAfter
            ]);
        }

        Log::info('For Approval movements saved', [
            'transform_id' => $transform->id,
            'source_item' => $sourceItemName,
            'reference' => $referenceNumber
        ]);
    }

    private function saveApprovedMovements($transform, $sourceInventory, $sourceItemName, $outputItemName, $storeCode, $branchCode = '')
    {
        if ($branchCode === null || $branchCode === '') {
            $userInfo = $this->getUserStoreAndBranch();
            $branchCode = $userInfo['branch_code'];
        }
        $branchCode = $branchCode ?? '';
        
        // Properly calculate source quantity in base
        $sourceQuantityInBase = $this->convertToUnit($sourceInventory, $transform->source_quantity, $transform->source_unit);
        
        $sourceQuantityBefore = $sourceInventory->quantity;
        $sourceCurrentQuantityBefore = $sourceInventory->current_quantity ?? $sourceQuantityBefore;
        $sourceCurrentQuantityAfter = $sourceCurrentQuantityBefore - $sourceQuantityInBase;
        
        // Use approval_reference or fallback to transform_no
        $referenceNumber = $transform->approval_reference ?? $transform->transform_no;
        
        // SAVE WASTE TO INVENTORY MOVEMENT - Approved (RECORD ONLY, NO DEDUCTION)
        if ($transform->waste_quantity && $transform->waste_quantity > 0) {
            $wasteQuantity = floatval($transform->waste_quantity);
            $wasteUnit = $transform->waste_unit ? $this->normalizeUnit($transform->waste_unit) : $transform->source_unit;
            
            try {
                $wasteQuantityInBase = $this->convertToUnit($sourceInventory, $wasteQuantity, $wasteUnit);
            } catch (\Exception $e) {
                Log::warning('Waste quantity conversion failed, using original quantity', [
                    'error' => $e->getMessage(),
                    'waste_quantity' => $wasteQuantity,
                    'waste_unit' => $wasteUnit
                ]);
                $wasteQuantityInBase = $wasteQuantity;
            }
            
            // Save waste to inventory_movement - Approved (LOG ONLY)
            $wasteMovement = new InventoryMovement();
            $wasteMovement->type = 'WASTE';
            $wasteMovement->menu_id = $sourceInventory->id;
            $wasteMovement->trans_id = $transform->id;
            $wasteMovement->trans_number = $transform->transform_no;
            $wasteMovement->reference = $referenceNumber;
            $wasteMovement->detail = 'Waste from transformation: ' . $sourceItemName . ' to ' . $outputItemName . ' - ' . ($transform->waste_description ?? '') . ' - Approved';
            $wasteMovement->unit = $wasteUnit;
            $wasteMovement->previous_qty = 0; //ken update it to 0 $sourceCurrentQuantityAfter
            $wasteMovement->qty_in = $wasteQuantityInBase;
            $wasteMovement->qty_out = 0;
            $wasteMovement->remaining = 0; //ken update it to 0 sourceCurrentQuantityAfter // NO DEDUCTION - waste is just logged
            $wasteMovement->sold_price = 0;
            $wasteMovement->cost = 0;
            $wasteMovement->branch_code = $branchCode;
            $wasteMovement->store_code = $storeCode;
            $wasteMovement->is_sync = 'no';
            $wasteMovement->save();
            
            // Save waste transaction - Approved (LOG ONLY)
            $wasteTransaction = new InventoryTransaction();
            $wasteTransaction->inv_id = $sourceInventory->id;
            $wasteTransaction->department_id = $sourceInventory->department ?? null;
            $wasteTransaction->category_id = $sourceInventory->category_id ?? null;
            $wasteTransaction->supplier_id = 1;
            $wasteTransaction->type = 'WASTE';
            $wasteTransaction->quantity_before = 0; //ken update it to 0 $sourceCurrentQuantityAfter;
            $wasteTransaction->quantity = $wasteQuantity;
            $wasteTransaction->quantity_after = 0; //ken update it to 0 $sourceCurrentQuantityAfter; // NO CHANGE - waste doesn't affect quantity
            $wasteTransaction->amount = 0;
            $wasteTransaction->remarks = 'Waste from Transform ' . $transform->transform_no . ' - Approved';
            $wasteTransaction->user_id = Auth::id();
            $wasteTransaction->store_code = $storeCode;
            $wasteTransaction->branch_code = $branchCode;
            $wasteTransaction->save();
            
            Log::info('Waste recorded to inventory_movement - Approved (no quantity deduction)', [
                'transform_id' => $transform->id,
                'waste_quantity' => $wasteQuantityInBase,
                'source_item' => $sourceItemName,
                'reference' => $referenceNumber,
                'remaining_unchanged' => $sourceCurrentQuantityAfter
            ]);
        }
        
        // ADD TO OUTPUTS - Approved
        foreach ($transform->items as $item) {
            if (!$item->menu_id) continue;
            
            $itemMenu = Menu::find($item->menu_id);
            if (!$itemMenu) continue;
            
            $quantityInBase = $this->convertToUnit($itemMenu, $item->quantity, $item->unit);
            $currentItemName = $itemMenu->name ?? $itemMenu->item_name ?? 'Unknown Item';

            $itemQuantityBefore = $itemMenu->quantity;
            $itemCurrentQuantityBefore = $itemMenu->current_quantity ?? $itemQuantityBefore;
            
            // Increment output quantities
            $itemMenu->increment('quantity', $quantityInBase);
            if ($this->hasCurrentQuantityField($itemMenu)) {
                $itemMenu->increment('current_quantity', $quantityInBase);
            }
            
            $itemMenuFresh = Menu::find($itemMenu->id);
            $itemQuantityAfter = $itemMenuFresh->quantity;
            $itemCurrentQuantityAfter = $itemMenuFresh->current_quantity ?? $itemQuantityAfter;

            // Save output movement - Approved with approval reference
            $itemMovement = new InventoryMovement();
            $itemMovement->type = 'TRANSFORMATION';
            $itemMovement->menu_id = $itemMenu->id;
            $itemMovement->trans_id = $transform->id;
            $itemMovement->trans_number = $transform->transform_no;
            $itemMovement->reference = $referenceNumber;
            $itemMovement->detail = 'Transform from ' . $sourceItemName . ' to ' . $currentItemName . ' - Approved';
            $itemMovement->previous_qty = $itemCurrentQuantityBefore;
            $itemMovement->qty_in = $quantityInBase;
            $itemMovement->qty_out = 0;
            $itemMovement->remaining = $itemCurrentQuantityAfter;
            $itemMovement->sold_price = 0;
            $itemMovement->cost = 0;
            $itemMovement->branch_code = $branchCode;
            $itemMovement->store_code = $storeCode;
            $itemMovement->is_sync = 'no';
            $itemMovement->save();

            // Save output transaction - Approved
            $itemTransaction = new InventoryTransaction();
            $itemTransaction->inv_id = $itemMenu->id;
            $itemTransaction->department_id = $itemMenu->department ?? null;
            $itemTransaction->category_id = $itemMenu->category_id ?? null;
            $itemTransaction->supplier_id = 1;
            $itemTransaction->type = 'INCOMING';
            $itemTransaction->quantity_before = $itemCurrentQuantityBefore;
            $itemTransaction->quantity = $item->quantity;
            $itemTransaction->quantity_after = $itemCurrentQuantityAfter;
            $itemTransaction->amount = 0;
            $itemTransaction->remarks = 'Transform ' . $transform->transform_no . ' (output) - Approved';
            $itemTransaction->user_id = Auth::id();
            $itemTransaction->store_code = $storeCode;
            $itemTransaction->branch_code = $branchCode;
            $itemTransaction->save();

            Log::info('Output added - Approved', [
                'item' => $currentItemName,
                'before' => $itemCurrentQuantityBefore,
                'added' => $quantityInBase,
                'after' => $itemCurrentQuantityAfter,
                'approval_reference' => $referenceNumber
            ]);
        }
        
        Log::info('Approved movements saved (outputs and waste)', [
            'transform_id' => $transform->id,
            'approval_reference' => $referenceNumber
        ]);
    }

    /**
     * Save "Reverted" inventory movements - revert source deduction and log waste reversion
     */
    private function saveRevertedMovements($transform, $sourceInventory, $sourceItemName, $outputItemName, $storeCode, $branchCode = '')
    {
        if ($branchCode === null || $branchCode === '') {
            $userInfo = $this->getUserStoreAndBranch();
            $branchCode = $userInfo['branch_code'];
        }
        $branchCode = $branchCode ?? '';

        $sourceRevertQuantity = $this->convertToUnit($sourceInventory, $transform->source_quantity, $transform->source_unit);
        
        $sourceQuantityBefore = $sourceInventory->quantity;
        $sourceCurrentQuantityBefore = $sourceInventory->current_quantity ?? $sourceQuantityBefore;
        
        // Revert source deduction
        $sourceInventory->increment('quantity', $sourceRevertQuantity);
        if ($this->hasCurrentQuantityField($sourceInventory)) {
            $sourceInventory->increment('current_quantity', $sourceRevertQuantity);
        }
        
        $sourceInventoryFresh = Menu::find($sourceInventory->id);
        $sourceQuantityAfter = $sourceInventoryFresh->quantity;
        $sourceCurrentQuantityAfter = $sourceInventoryFresh->current_quantity ?? $sourceQuantityAfter;

        // Use transform_no for reverted movements
        $referenceNumber = $transform->transform_no . '-REVERTED';

        // Save source reversion movement - Reverted
        $sourceMovement = new InventoryMovement();
        $sourceMovement->type = 'TRANSFORMATION';
        $sourceMovement->menu_id = $sourceInventory->id;
        $sourceMovement->trans_id = $transform->id;
        $sourceMovement->trans_number = $transform->transform_no;
        $sourceMovement->reference = $referenceNumber;
        $sourceMovement->detail = 'Transform ' . $sourceItemName . ' to ' . $outputItemName . ' - Reverted';
        $sourceMovement->previous_qty = $sourceCurrentQuantityBefore;
        $sourceMovement->qty_in = $sourceRevertQuantity;
        $sourceMovement->qty_out = 0;
        $sourceMovement->remaining = $sourceCurrentQuantityAfter;
        $sourceMovement->sold_price = 0;
        $sourceMovement->cost = 0;
        $sourceMovement->branch_code = $branchCode;
        $sourceMovement->store_code = $storeCode;
        $sourceMovement->is_sync = 'no';
        $sourceMovement->save();

        // Save source reversion transaction - Reverted
        $sourceTransaction = new InventoryTransaction();
        $sourceTransaction->inv_id = $sourceInventory->id;
        $sourceTransaction->department_id = $sourceInventory->department ?? null;
        $sourceTransaction->category_id = $sourceInventory->category_id ?? null;
        $sourceTransaction->supplier_id = 1;
        $sourceTransaction->type = 'INCOMING';
        $sourceTransaction->quantity_before = $sourceCurrentQuantityBefore;
        $sourceTransaction->quantity = $transform->source_quantity;
        $sourceTransaction->quantity_after = $sourceCurrentQuantityAfter;
        $sourceTransaction->amount = 0;
        $sourceTransaction->remarks = 'Transform ' . $transform->transform_no . ' (source reverted) - Reverted';
        $sourceTransaction->user_id = Auth::id();
        $sourceTransaction->store_code = $storeCode;
        $sourceTransaction->branch_code = $branchCode;
        $sourceTransaction->save();

        // SAVE WASTE REVERSION TO INVENTORY MOVEMENT - Reverted (RECORD ONLY, NO DEDUCTION)
        if ($transform->waste_quantity && $transform->waste_quantity > 0) {
            $wasteQuantity = floatval($transform->waste_quantity);
            $wasteUnit = $transform->waste_unit ? $this->normalizeUnit($transform->waste_unit) : $transform->source_unit;
            
            try {
                $wasteQuantityInBase = $this->convertToUnit($sourceInventory, $wasteQuantity, $wasteUnit);
            } catch (\Exception $e) {
                Log::warning('Waste quantity conversion failed, using original quantity', [
                    'error' => $e->getMessage(),
                    'waste_quantity' => $wasteQuantity,
                    'waste_unit' => $wasteUnit
                ]);
                $wasteQuantityInBase = $wasteQuantity;
            }
            
            // Save waste reversion to inventory_movement - Reverted (LOG ONLY)
            $wasteMovement = new InventoryMovement();
            $wasteMovement->type = 'WASTE';
            $wasteMovement->menu_id = $sourceInventory->id;
            $wasteMovement->trans_id = $transform->id;
            $wasteMovement->trans_number = $transform->transform_no;
            $wasteMovement->reference = $referenceNumber;
            $wasteMovement->detail = 'Waste reverted from transformation: ' . $sourceItemName . ' to ' . $outputItemName . ' - ' . ($transform->waste_description ?? '') . ' - Reverted';
            $wasteMovement->unit = $wasteUnit;
            $wasteMovement->previous_qty = 0; //ken update it to 0 $sourceCurrentQuantityAfter
            $wasteMovement->qty_in = $wasteQuantityInBase;
            $wasteMovement->qty_out = 0;
            $wasteMovement->remaining = 0; //ken update it to 0 sourceCurrentQuantityAfter
            $wasteMovement->sold_price = 0;
            $wasteMovement->cost = 0;
            $wasteMovement->branch_code = $branchCode;
            $wasteMovement->store_code = $storeCode;
            $wasteMovement->is_sync = 'no';
            $wasteMovement->save();
            
            // Save waste reversion transaction - Reverted (LOG ONLY)
            $wasteTransaction = new InventoryTransaction();
            $wasteTransaction->inv_id = $sourceInventory->id;
            $wasteTransaction->department_id = $sourceInventory->department ?? null;
            $wasteTransaction->category_id = $sourceInventory->category_id ?? null;
            $wasteTransaction->supplier_id = 1;
            $wasteTransaction->type = 'WASTE';
            $wasteTransaction->quantity_before = 0; //ken update it to 0$sourceCurrentQuantityAfter;
            $wasteTransaction->quantity = $wasteQuantity;
            $wasteTransaction->quantity_after = 0; //ken update it to 0 $sourceCurrentQuantityAfter; // NO CHANGE - waste doesn't affect quantity
            $wasteTransaction->amount = 0;
            $wasteTransaction->remarks = 'Waste reverted from Transform ' . $transform->transform_no . ' - Reverted';
            $wasteTransaction->user_id = Auth::id();
            $wasteTransaction->store_code = $storeCode;
            $wasteTransaction->branch_code = $branchCode;
            $wasteTransaction->save();
            
            Log::info('Waste reversion recorded to inventory_movement - Reverted (no quantity deduction)', [
                'transform_id' => $transform->id,
                'waste_quantity' => $wasteQuantityInBase,
                'source_item' => $sourceItemName,
                'reference' => $referenceNumber,
                'remaining_unchanged' => $sourceCurrentQuantityAfter
            ]);
        }

        Log::info('Reverted movements saved', [
            'transform_id' => $transform->id,
            'source_item' => $sourceItemName,
            'reverted_quantity' => $sourceRevertQuantity,
            'reference' => $referenceNumber
        ]);
    }

    public function delete($slug)
    {
        $transform = Transform::with('items')->where('slug', $slug)->first();
        if (!$transform) {
            return redirect()->route('transform.index')->with('error', 'Transform record not found.');
        }

        try {
            DB::beginTransaction();

            Log::info('Transform delete attempt', [
                'transform_id' => $transform->id,
                'transform_slug' => $transform->slug,
                'status' => $transform->status
            ]);

            // Revert source deduction if PENDING (source was deducted)
            if ($transform->status === 'PENDING') {
                $sourceInventory = Menu::find($transform->source_inventory_id);
                if ($sourceInventory) {
                    $sourceRevert = $this->convertToUnit($sourceInventory, $transform->source_quantity, $transform->source_unit);
                    $sourceInventory->increment('quantity', $sourceRevert);
                    if ($this->hasCurrentQuantityField($sourceInventory)) {
                        $sourceInventory->increment('current_quantity', $sourceRevert);
                    }
                }
            }

            // Revert output additions and source if COMPLETED
            if ($transform->status === 'COMPLETED') {
                $sourceInventory = Menu::find($transform->source_inventory_id);
                if ($sourceInventory) {
                    $sourceRevert = $this->convertToUnit($sourceInventory, $transform->source_quantity, $transform->source_unit);
                    $sourceInventory->increment('quantity', $sourceRevert);
                    if ($this->hasCurrentQuantityField($sourceInventory)) {
                        $sourceInventory->increment('current_quantity', $sourceRevert);
                    }
                }
                
                foreach ($transform->items as $item) {
                    if ($item->menu_id) {
                        $outputMenu = Menu::find($item->menu_id);
                        if ($outputMenu) {
                            $revertQuantity = $this->convertToUnit($outputMenu, $item->quantity, $item->unit);
                            $outputEffectiveQuantity = $this->getEffectiveQuantity($outputMenu);
                            
                            if ($outputEffectiveQuantity < $revertQuantity) {
                                $itemName = $outputMenu->name . ($outputMenu->brand ? ' - ' . $outputMenu->brand->name : '');
                                DB::rollBack();
                                return redirect()->route('transform.index')->with('error', 'Cannot delete transform because output item "' . $itemName . '" has only ' . number_format($outputEffectiveQuantity, 2) . ' and requires ' . number_format($revertQuantity, 2) . '.');
                            }
                            
                            $outputMenu->decrement('quantity', $revertQuantity);
                            if ($this->hasCurrentQuantityField($outputMenu)) {
                                $outputMenu->decrement('current_quantity', $revertQuantity);
                            }
                        }
                    }
                }
            }

            if (Schema::hasColumn('transform_items', 'is_deleted')) {
                TransformItem::where('transform_id', $transform->id)->update(['is_deleted' => 1]);
            }

            if (Schema::hasColumn('transforms', 'is_deleted')) {
                $transform->is_deleted = 1;
                $transform->save();
            } else {
                $transform->delete();
            }

            DB::commit();
            return redirect()->route('transform.index')->with('message', 'Transform record deleted and stock reverted.');
        } catch (\Throwable $exception) {
            Log::error('Transform delete exception: ' . $exception->getMessage());
            DB::rollBack();
            return redirect()->route('transform.index')->with('error', 'Unable to delete transform: ' . $exception->getMessage());
        }
    }

    protected function getEffectiveQuantity($item): float
    {
        if ($this->hasCurrentQuantityField($item) && isset($item->current_quantity)) {
            return floatval($item->current_quantity);
        }
        return floatval($item->quantity);
    }

    protected function hasCurrentQuantityField($item): bool
    {
        return Schema::hasColumn($item->getTable(), 'current_quantity');
    }

    protected function normalizeUnit(string $unit): string
    {
        return Convertion::normalizeUnit($unit);
    }

    protected function convertToUnit($item, float $quantity, string $unit): float
    {
        return Convertion::convertQuantity($quantity, $unit, $item->unit);
    }
}