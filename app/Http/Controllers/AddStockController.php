<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryMovement;
use App\Models\Menu;
use App\Models\Supplier;
use App\Models\PendingPurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AddStockController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        
        $brands = Brand::where('is_deleted', 0)
            //->where('store_code', $user->store_code)
            ->orderBy('name', 'asc')
            ->get();
        
        $suppliers = Supplier::where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();
        
        $menus = Menu::where('is_deleted', 0)
            ->where('store_code', $user->store_code)
            //ken start uncomment this code block 8/6/2026 250623001 
            /* ->whereHas('brand', function($query) use ($user) {
                $query->where('is_deleted', 0)
                      ->where('store_code', $user->store_code);
            })
            ->with(['brand' => function($query) use ($user) {
                $query->where('is_deleted', 0)
                      ->where('store_code', $user->store_code);
            }]) */
            //ken end uncomment this code block 8/6/2026 250623001
            ->orderBy('name', 'asc')
            ->get();
        
        $pendingOrders = PendingPurchaseOrder::with(['supplier', 'user'])
            ->where('store_code', $user->store_code)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('user.inventory.add-stock', compact('brands', 'suppliers', 'menus', 'pendingOrders'));
    }

    public function store(Request $request)
    {
        Log::info('===== ADD STOCK PROCESS STARTED =====');
        Log::info('Request data:', $request->all());
        
        $user = Auth::user();
        Log::info('Authenticated user:', ['id' => $user->id, 'store_code' => $user->store_code]);
        
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'sales_invoice' => 'required|string|max:100',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer|exists:menus,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
        ]);
        
        Log::info('Validation passed');

        try {
            Log::info('Starting database transaction');
            DB::beginTransaction();
            
            $supplier = Supplier::find($request->supplier_id);
            Log::info('Supplier found:', ['supplier' => $supplier?->name, 'id' => $supplier?->id]);
            //ken update the reference number format 08-27-2026
            $today = now()->format('YmdHis');
            $todayCount = PendingPurchaseOrder::where('reference_number', 'like', "STOCK-$today%")
                ->lockForUpdate()
                ->count();
            $refNumber = 'STOCK-'. $today;
            //end of ken update the reference number format 08-27-2026
            
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }
            
            $pendingOrder = PendingPurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'sales_invoice' => $request->sales_invoice,
                'order_date' => $request->order_date,
                'reference_number' => $refNumber,
                'items_data' => json_encode($request->items),
                'user_id' => $user->id,
                'store_code' => $user->store_code,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'remarks' => 'Awaiting approval'
            ]);
            
            Log::info('Pending purchase order created:', ['id' => $pendingOrder->id]);
            
            DB::commit();
            Log::info('Transaction committed successfully');
            
            Log::info('===== ADD STOCK PROCESS COMPLETED SUCCESSFULLY =====');
            return redirect()->route('pending.purchase.orders.index')
                ->with('message', 'Purchase order submitted for approval successfully.');
                
        } catch (\Exception $e) {
            Log::error('===== EXCEPTION CAUGHT =====');
            Log::error('Error message:', ['message' => $e->getMessage()]);
            Log::error('File:', ['file' => $e->getFile(), 'line' => $e->getLine()]);
            Log::error('Stack trace:', ['trace' => $e->getTraceAsString()]);
            
            DB::rollBack();
            Log::error('Transaction rolled back');
            
            $errorMsg = 'Error submitting purchase order: ' . $e->getMessage();
            return back()->withInput()->with('error', $errorMsg);
        }
    }

    // ken update the pending approval method 07-28-2026
    public function pendingApproval()
    {
        $user = Auth::user();
        
        $pendingOrders = PendingPurchaseOrder::with(['supplier', 'user'])
            ->where('store_code', $user->store_code)
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->through(function ($order) {
                // Add supplier_name and user_name to each order
                $order->supplier_name = $order->supplier->name ?? 'N/A';
                $order->user_name = $order->user->name ?? 'Unknown';
                return $order;
            });
        
        $brands = Brand::where('is_deleted', 0)
            ->where('store_code', $user->store_code)
            ->orderBy('name', 'asc')
            ->get();
        
        $suppliers = Supplier::where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();
        
        $menus = Menu::where('is_deleted', 0)
            ->where('store_code', $user->store_code)
            ->whereHas('brand', function($query) use ($user) {
                $query->where('is_deleted', 0)
                    ->where('store_code', $user->store_code);
            })
            ->with(['brand' => function($query) use ($user) {
                $query->where('is_deleted', 0)
                    ->where('store_code', $user->store_code);
            }])
            ->orderBy('name', 'asc')
            ->get();
        
        return view('user.inventory.pending-approval', compact('pendingOrders', 'brands', 'suppliers', 'menus'));
    }
    // end of ken update the pending approval method 07-28-2026

    public function viewPendingApprovalJson($id)
    {
        $user = Auth::user();
        
        try {
            $pendingOrder = PendingPurchaseOrder::with(['supplier', 'user', 'approvedBy', 'rejectedBy'])
                ->where('store_code', $user->store_code)
                ->findOrFail($id);
            
            $items = json_decode($pendingOrder->items_data, true);
            $itemDetails = [];
            
            foreach ($items as $item) {
                $menu = Menu::with('brand')->find($item['menu_id']);
                if ($menu) {
                    $item['menu_name'] = $menu->name;
                    $item['brand_name'] = $menu->brand ? $menu->brand->name : 'N/A';
                    $item['current_quantity'] = $menu->current_quantity ?? 0;
                    $item['unit'] = $menu->unit ?? $item['unit'] ?? '';
                    $item['line_total'] = $item['quantity'] * $item['price'];
                    $itemDetails[] = $item;
                }
            }
            
            $orderData = [
                'id' => $pendingOrder->id,
                'reference_number' => $pendingOrder->reference_number,
                'supplier_name' => $pendingOrder->supplier->name ?? 'N/A',
                'sales_invoice' => $pendingOrder->sales_invoice,
                'order_date' => $pendingOrder->order_date,
                'total_amount' => $pendingOrder->total_amount,
                'status' => $pendingOrder->status,
                'user_name' => $pendingOrder->user->name ?? 'Unknown',
                'created_at' => $pendingOrder->created_at,
                'remarks' => $pendingOrder->remarks,
                'approved_by_name' => $pendingOrder->approvedBy->name ?? null,
                'approved_at' => $pendingOrder->approved_at,
                'rejected_by_name' => $pendingOrder->rejectedBy->name ?? null,
                'rejected_at' => $pendingOrder->rejected_at,
            ];
            
            return response()->json([
                'success' => true,
                'order' => $orderData,
                'items' => $itemDetails
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // start of update ken 250623001 07-29-2026
    public function editPendingApprovalJson($id)
    {
        $user = Auth::user();
        
        try {
            $pendingOrder = PendingPurchaseOrder::where('store_code', $user->store_code)
                ->where('status', 'pending')
                ->findOrFail($id);
            
            $items = json_decode($pendingOrder->items_data, true);
            $itemDetails = [];
            
            foreach ($items as $item) {
                $menu = Menu::with('brand')->find($item['menu_id']);
                if ($menu) {
                    $item['menu_id'] = $menu->id;
                    $item['menu_name'] = $menu->name;
                    $item['brand_name'] = $menu->brand ? $menu->brand->name : 'N/A';
                    $item['unit'] = $menu->unit ?? $item['unit'] ?? '';
                    $itemDetails[] = $item;
                }
            }
            
            $suppliers = Supplier::where('is_deleted', 0)
                ->orderBy('name', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $pendingOrder->id,
                    'reference_number' => $pendingOrder->reference_number,
                    'supplier_id' => $pendingOrder->supplier_id,
                    'sales_invoice' => $pendingOrder->sales_invoice,
                    'order_date' => $pendingOrder->order_date->format('Y-m-d'),
                    'total_amount' => $pendingOrder->total_amount,
                    'status' => $pendingOrder->status,
                ],
                'items' => $itemDetails,
                'suppliers' => $suppliers
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updatePendingApprovalJson(Request $request, $id)
    {
        $user = Auth::user();
        
        try {
            // Make supplier_id optional for updates
            $request->validate([
                'supplier_id' => 'sometimes|nullable|exists:suppliers,id',
                'sales_invoice' => 'required|string|max:100',
                'order_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.menu_id' => 'required|integer|exists:menus,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.unit' => 'nullable|string|max:50',
            ]);
            
            $pendingOrder = PendingPurchaseOrder::where('store_code', $user->store_code)
                ->where('status', 'pending')
                ->findOrFail($id);
            
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }
            
            // Prepare update data
            $updateData = [
                'sales_invoice' => $request->sales_invoice,
                'order_date' => $request->order_date,
                'items_data' => json_encode($request->items),
                'total_amount' => $totalAmount,
                'remarks' => 'Updated by ' . $user->name . ' at ' . now()->format('Y-m-d H:i:s'),
            ];
            
            // Only update supplier_id if provided
            if ($request->has('supplier_id') && !empty($request->supplier_id)) {
                $updateData['supplier_id'] = $request->supplier_id;
            }
            
            $pendingOrder->update($updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // end of update ken 250623001 07-29-2026

    public function approvePurchaseOrder($id)
    {
        $user = Auth::user();
        
        try {
            DB::beginTransaction();
            
            $pendingOrder = PendingPurchaseOrder::where('store_code', $user->store_code)
                ->where('status', 'pending')
                ->findOrFail($id);
            
            Log::info('Approving purchase order:', ['id' => $pendingOrder->id]);
            
            $items = json_decode($pendingOrder->items_data, true);
            $supplier = Supplier::find($pendingOrder->supplier_id);
            $supplierName = $supplier ? $supplier->name : 'Unknown Supplier';
            $refNumber = $pendingOrder->reference_number;
            $salesInvoice = $pendingOrder->sales_invoice ?? '';
            
            foreach ($items as $item) {
                $menu = Menu::find($item['menu_id']);
                
                if (!$menu) {
                    Log::warning("Menu not found for ID: {$item['menu_id']}");
                    continue;
                }
                
                $quantityBefore = $menu->current_quantity ?? 0;
                $newQuantity = $quantityBefore + $item['quantity'];
                
                $updateData = [
                    'current_quantity' => $newQuantity,
                    'quantity' => $newQuantity,
                    'updated_at' => now()
                ];
                
                if (!empty($item['unit']) && $menu->unit != $item['unit']) {
                    $updateData['unit'] = $item['unit'];
                    Log::info("Updating unit for menu {$menu->id} from '{$menu->unit}' to '{$item['unit']}'");
                }
                
                $updated = $menu->update($updateData);
                
                if (!$updated) {
                    throw new \Exception("Failed to update menu quantity for menu ID: {$menu->id}");
                }
                
                $menu->refresh();
                
                Log::info("After update - Menu ID: {$menu->id}, New Quantity: {$menu->current_quantity}");
                
                $totalAmount = $item['quantity'] * $item['price'];
                
                $transaction = InventoryTransaction::create([
                    'inv_id' => $menu->id,
                    'type' => 'TRADE PAYABLE',
                    'category_id' => 42,
                    'quantity_before' => $quantityBefore,
                    'quantity' => $item['quantity'],
                    'quantity_after' => $newQuantity,
                    'amount' => $totalAmount,
                    'unit_price' => $item['price'],
                    'exp_type' => 0,
                    'remarks' => "$refNumber - Purchase - $supplierName",
                    'unit' => $item['unit'] ?? $menu->unit,
                    'user_id' => $user->id,
                    'supplier_id' => $pendingOrder->supplier_id,
                    'is_paid' => 'CHEQUE',
                    'payment_class' => 1,
                    'store_code' => $pendingOrder->store_code,
                ]);
                
                if (!$transaction) {
                    throw new \Exception("Failed to create inventory transaction for menu ID: {$menu->id}");
                }
                
                $movement = InventoryMovement::create([
                    'type' => 'STOCK IN',
                    'ordered_id' => null,
                    'void_id' => null,
                    'menu_id' => $menu->id,
                    'trans_id' => $transaction->id,
                    'trans_number' => $refNumber,
                    'si_number' => $salesInvoice, 
                    'reference' => $refNumber,
                    'detail' => $supplierName,
                    'previous_qty' => $quantityBefore,
                    'qty_in' => $item['quantity'],
                    'qty_out' => 0,
                    'remaining' => $newQuantity,
                    'sold_price' => $item['price'],
                    'cost' => $totalAmount,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'branch_code' => $user->branch_code ?? '',
                    'store_code' => $pendingOrder->store_code,
                    'is_uuid' => null,
                    'is_sync' => '0'
                ]);
                
                if (!$movement) {
                    throw new \Exception("Failed to create inventory movement for menu ID: {$menu->id}");
                }

            // LINE 439: START: Save itemized data to purchase_ordered table by ken 09-5-2026
            try {
                DB::table('purchase_ordered')->insert([
                    'pending_purchase_order_id' => $pendingOrder->id,
                    'menu_id' => $menu->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'unit' => $item['unit'] ?? $menu->unit,
                    'menu_name' => $menu->name,
                    'brand_name' => $menu->brand ? $menu->brand->name : null,
                    'brand_id' => $menu->brand_id,
                    'supplier_id' => $pendingOrder->supplier_id,
                    'supplier_name' => $supplierName,
                    'reference_number' => $refNumber,
                    'sales_invoice' => $salesInvoice,
                    'store_code' => $pendingOrder->store_code,
                    'remaining_stocks' => $item['quantity'], // LINE 455: Save the remaining stocks after approval
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                Log::info("Purchase ordered item saved for menu_id: {$menu->id}");
            } catch (\Exception $e) {
                Log::error('Error saving to purchase_ordered table:', [
                    'message' => $e->getMessage(),
                    'menu_id' => $menu->id
                ]);
                // Don't throw exception here to avoid breaking the main transaction
                // Just log the error
            }
            // LINE 468: END: Save itemized data to purchase_ordered table by ken 09-5-2026               
                Log::info("Stock added successfully for menu_id: {$menu->id}, quantity: {$item['quantity']}");
            }
            
            $pendingOrder->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'remarks' => 'Approved and processed by ' . $user->name
            ]);
            
            DB::commit();
            
            Log::info("Purchase order {$pendingOrder->id} approved successfully");
            
            return redirect()->route('pending.purchase.orders.index')
                ->with('message', 'Purchase order approved and stocks added successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving purchase order:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $id
            ]);
            
            return back()->with('error', 'Error approving purchase order: ' . $e->getMessage());
        }
    }

    public function rejectPurchaseOrder(Request $request, $id)
    {
        $user = Auth::user();
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);
        
        try {
            $pendingOrder = PendingPurchaseOrder::where('store_code', $user->store_code)
                ->where('status', 'pending')
                ->findOrFail($id);
            
            $pendingOrder->update([
                'status' => 'rejected',
                'rejected_by' => $user->id,
                'rejected_at' => now(),
                'remarks' => $request->rejection_reason
            ]);
            
            return redirect()->route('pending.purchase.orders.index')
                ->with('message', 'Purchase order rejected successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error rejecting purchase order:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error rejecting purchase order: ' . $e->getMessage());
        }
    }

    public function deletePendingApproval($id)
    {
        $user = Auth::user();
        
        try {
            $pendingOrder = PendingPurchaseOrder::where('store_code', $user->store_code)
                ->where('status', 'pending')
                ->findOrFail($id);
            
            $pendingOrder->delete();
            
            return redirect()->route('pending.purchase.orders.index')
                ->with('message', 'Purchase order deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error deleting purchase order:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error deleting purchase order: ' . $e->getMessage());
        }
    }

    public function getMenusByBrand($brandId)
    {
        $user = Auth::user();
        
        $menus = Menu::where('is_deleted', 0)
            ->where('store_code', $user->store_code)
            ->where('brand_id', $brandId)
            ->select('id', 'name', 'price', 'brand_id', 'current_quantity', 'unit')
            ->orderBy('name', 'asc')
            ->get();
        
        return response()->json($menus);
    }

    public function getMenuDetails($menuId)
    {
        $user = Auth::user();
        
        $menu = Menu::with('brand')
            ->where('store_code', $user->store_code)
            ->findOrFail($menuId);
        
        return response()->json([
            'success' => true,
            'menu' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'brand_name' => $menu->brand ? $menu->brand->name : 'N/A',
                'unit' => $menu->unit ?? '',
                'current_quantity' => $menu->current_quantity ?? 0,
                'price' => $menu->price ?? 0
            ]
        ]);
    }

    public function searchMenus(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q', '');
        
        $menus = Menu::where('is_deleted', 0)
            ->where('store_code', $user->store_code)
            ->where('name', 'LIKE', "%{$search}%")
            ->with('brand')
            ->limit(20)
            ->get()
            ->map(function ($menu) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'brand_name' => $menu->brand ? $menu->brand->name : 'N/A',
                    'unit' => $menu->unit ?? '',
                    'current_quantity' => $menu->current_quantity ?? 0,
                    'price' => $menu->price ?? 0
                ];
            });
        
        return response()->json($menus);
    }
}