<?php

namespace App\Http\Controllers;

use App\Mail\MailNotify;
use App\Models\Brand;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\OrderRequest;
use App\Models\BranchOrderRequest;
use App\Models\OrderedRequest;
use App\Models\BranchRequest;
use App\Models\Transaction;
use App\Models\TransactionRequest;
use App\Models\BranchTransactionRequest;
use App\Models\Log;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Models\Transfer;



 
class BranchTransferController extends Controller {
    private const LOCATION_TRANSFER_PREFIXES = [
        'EMILIA' => 'KTC-ELT',
        'TARTARIA' => 'KTC-TLT',
        'LIMEATLESS' => 'KTC-MLT',
        'MEAT ELYU' => 'METLT',
        'MEAT EYLU' => 'METLT',
    ];

    public function create()
    {
        $user = Auth::user();

        $brands = Brand::where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();

        $branches = Setting::where('store_code',"!=", $user->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();

        $currentBranch = $this->getCurrentUserBranch($user);
        $transactionNumber = $this->getNextLocationTransferNumber(
            $this->getLocationTransferPrefix($currentBranch?->name ?? $user->branch_name ?? '')
        );

        $menus = Menu::where('is_deleted', 0)
            ->where('store_code', $user->store_code)
            ->orderBy('name', 'asc')
            ->get();

        $pendingOrders = DB::table('branch_transaction_requests')
            ->where('order_status', 'PENDING')
            ->get();

        return view('user.inventory.branch-transfer', compact('brands', 'branches', 'menus', 'pendingOrders', 'transactionNumber'));
    }

    public function store(Request $request)
    {
        Logger::info('===== BRANCH TRANSFER PROCESS STARTED =====');
        Logger::info('Branch transfer request data:', $request->all());

        $user = Auth::user();

        $request->validate([
            'branch_id' => [
                'required',
                Rule::exists('settings', 'id')
                    ->where(function ($query) use ($user) {
                        $query->where('store_code', '!=', $user->store_code)
                            ->where('is_deleted', 0);
                    }),
            ],
            'sales_invoice' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer|exists:menus,id',
            'items.*.price_type' => 'required|in:wholesale,retail,slab,buying_price',
            'items.*.price_type_price' => 'nullable|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.quantity_breakdown' => 'nullable|array',
            'items.*.quantity_breakdown.*' => 'numeric|min:0.001',
            'items.*.price' => 'required|integer|min:0|max:9999999',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        $branch = Setting::where('store_code', '!=', $user->store_code)
            ->where('is_deleted', 0)
            ->find($request->branch_id);
        $currentBranch = $this->getCurrentUserBranch($user);
        $transactionNumber = $this->getNextLocationTransferNumber(
            $this->getLocationTransferPrefix($currentBranch?->name ?? $user->branch_name ?? '')
        );
        $request->merge(['sales_invoice' => $transactionNumber]);

        try {
            DB::beginTransaction();

            $transactionSlug = $this->makeUniqueSlug('branch_transaction_requests');
            $branchCode = $branch?->branch_code ?: ($branch?->store_code ?: '');
            $totalAmount = 0;
            $preparedItems = [];

            foreach ($request->items as $item) {
                $menu = Menu::where('store_code', $user->store_code)->findOrFail($item['menu_id']);
                $quantity = (float) $item['quantity'];
                $quantityBreakdown = $this->normalizeQuantityBreakdown($item['quantity_breakdown'] ?? [], $quantity);
                $this->assertQuantityBreakdownMatches($quantityBreakdown, $quantity);
                $currentStock = $this->getMenuCurrentStock($menu);
                $this->assertQuantityWithinStock($quantity, $currentStock, $menu->name);

                $priceTypePrice = $this->getMenuPriceByType($menu, $item['price_type'] ?? '');
                $addedPrice = (float) $item['price'];
                $lineTotal = ($quantity * $priceTypePrice) + $addedPrice;

                $preparedItems[] = [
                    'item' => $item,
                    'menu' => $menu,
                    'quantity' => $quantity,
                    'quantity_breakdown' => $quantityBreakdown,
                    'current_stock' => $currentStock,
                    'price_type_price' => $priceTypePrice,
                    'added_price' => $addedPrice,
                    'line_total' => $lineTotal,
                ];

                $totalAmount += $lineTotal;
            }

            $transactionId = DB::table('branch_transaction_requests')->insertGetId([
                'number' => $transactionNumber,
                'total' => $totalAmount,
                'status' => 'PENDING',
                'branch_code' => $branchCode,
                'order_status' => 'PENDING',
                'order_remarks' => 'Awaiting approval',
                'cashier' => $user->id,
                'slug' => $transactionSlug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($preparedItems as $preparedItem) {
                $item = $preparedItem['item'];
                $menu = $preparedItem['menu'];
                $quantity = $preparedItem['quantity'];
                $quantityBreakdown = $preparedItem['quantity_breakdown'];
                $currentStock = $preparedItem['current_stock'];
                $priceTypePrice = $preparedItem['price_type_price'];
                $addedPrice = $preparedItem['added_price'];
                $lineTotal = $preparedItem['line_total'];

                DB::table('branch_requests')->insert(array_merge([
                    'tran_id' => $transactionId,
                    'inv_id' => $menu->id,
                    'item_code' => $menu->item_code ?: (string) $menu->id,
                    'masterlist_id' => $menu->masterlist_id ?? $menu->id,
                    'name' => $menu->name,
                    'unit' => $item['unit'] ?? $menu->unit,
                    'quantity' => $quantity,
                    'qty_approved' => $quantity,
                    'qty_received' => $quantity,
                    'price' => $priceTypePrice,
                    'total_price' => $lineTotal,
                    'current_stock' => $currentStock,
                    'reorder_point' => $menu->reorder_point ?? 0,
                    'cashier' => $user->id,
                    'role' => $user->role,
                    'department' => $menu->department ?? $user->department,
                    'branch' => $branch?->id,
                    'slug' => $this->makeUniqueSlug('branch_requests'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $this->makeLocationTransferMetadataFields($item['price_type'] ?? '', $addedPrice, $quantityBreakdown)));
            }

            $log = new Log();
            $log->trans_id = $transactionId;
            $log->classification = 'Branch Transfer';
            $log->info = 'Request Submit';
            $log->user_id = $user->id;
            $log->event = 'Add';
            $log->save();

            DB::commit();

            Logger::info('Branch transfer request created:', [
                'transaction_id' => $transactionId,
                'branch' => $branch?->name,
                'branch_id' => $branch?->id,
                'transaction_number' => $transactionNumber,
                'user_id' => $user?->id,
                'store_code' => $user?->store_code,
            ]);

            return redirect()->route('branch-transfer.pending')
                ->with('message', 'Location transfer request submitted for approval successfully.');
        } catch (ValidationException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Exception $exception) {
            DB::rollBack();

            Logger::error('Error submitting branch transfer request:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error submitting location transfer request: ' . $exception->getMessage());
        }
    }

    public function pendingApproval()
    {
        $user = Auth::user();
        $isMainLocationTransferBranch = $this->userIsMainLocationTransferBranch($user);
        $selectedBranch = request('branch_scope');

        $pendingQuery = DB::table('branch_transaction_requests')
            ->leftJoin('users', 'branch_transaction_requests.cashier', '=', 'users.id')
            ->select(
                'branch_transaction_requests.*',
                'users.store_code as sender_store_code',
                'users.branch_code as sender_branch_code',
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT_WS(' ', users.firstname, users.middlename, users.lastname)), ''), users.username, 'Unknown') as user_name")
            )
            ->whereIn('branch_transaction_requests.order_status', ['PENDING', 'APPROVED', 'REJECTED'])
            ->orderBy('branch_transaction_requests.created_at', 'desc');

        $this->scopeLocationTransferPendingQuery($pendingQuery, $user, $selectedBranch);

        $locationTransferBranches = $this->getLocationTransferBranchFilterOptions();
        $pendingOrders = $pendingQuery
            ->paginate(100)
            ->appends(request()->query())
            ->through(fn ($order) => $this->formatLocationTransferOrder($order));

        return view('user.inventory.branch-transfer-pending', compact(
            'pendingOrders',
            'isMainLocationTransferBranch',
            'locationTransferBranches',
            'selectedBranch'
        ));
    }

    public function viewPendingApprovalJson($id)
    {
        try {
            $transaction = DB::table('branch_transaction_requests')
                ->leftJoin('users', 'branch_transaction_requests.cashier', '=', 'users.id')
                ->select(
                    'branch_transaction_requests.*',
                    'users.store_code as sender_store_code',
                    'users.branch_code as sender_branch_code',
                    DB::raw("COALESCE(NULLIF(TRIM(CONCAT_WS(' ', users.firstname, users.middlename, users.lastname)), ''), users.username, 'Unknown') as user_name")
                )
                ->where('branch_transaction_requests.id', $id)
                ->first();

            if (!$transaction) {
                abort(404);
            }

            if (!$this->userCanViewLocationTransfer($transaction, Auth::user())) {
                abort(403, 'You are not allowed to view this location transfer.');
            }

            return response()->json([
                'success' => true,
                'order' => $this->formatLocationTransferOrder($transaction),
                'items' => $this->getLocationTransferItems($transaction->id),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function updatePendingApprovalJson(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:branch_requests,id',
            'items.*.menu_id' => 'required|integer|exists:menus,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.quantity_breakdown' => 'nullable|array',
            'items.*.quantity_breakdown.*' => 'numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.added_price' => 'nullable|integer|min:0|max:9999999',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $transaction = DB::table('branch_transaction_requests')
                ->where('id', $id)
                ->where('order_status', 'PENDING')
                ->first();

            if (!$transaction) {
                abort(404);
            }

            if (!$this->userCanManagePendingLocationTransfer($transaction, $user)) {
                throw ValidationException::withMessages([
                    'items' => 'Only the sending store can update this pending location transfer.',
                ]);
            }

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $branchRequestQuery = DB::table('branch_requests')->where('tran_id', $transaction->id);
                if (!empty($item['id'])) {
                    $branchRequestQuery->where('id', $item['id']);
                } else {
                    $branchRequestQuery->where('inv_id', $item['menu_id']);
                }

                $branchRequest = $branchRequestQuery->first();
                if (!$branchRequest) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more location transfer items could not be found.',
                    ]);
                }

                $menu = Menu::find($branchRequest->inv_id);
                if (!$menu) {
                    throw ValidationException::withMessages([
                        'items' => $branchRequest->name . ' could not be found in the sending branch inventory.',
                    ]);
                }

                $quantity = (float) $item['quantity'];
                $quantityBreakdown = $this->normalizeQuantityBreakdown(
                    $item['quantity_breakdown'] ?? $this->getQuantityBreakdownFromItem($branchRequest, $quantity),
                    $quantity
                );
                $this->assertQuantityBreakdownMatches($quantityBreakdown, $quantity);
                $currentStock = $this->getMenuCurrentStock($menu);
                $this->assertQuantityWithinStock($quantity, $currentStock, $menu->name);

                $price = (float) $item['price'];
                $addedPrice = array_key_exists('added_price', $item)
                    ? (float) $item['added_price']
                    : $this->getAddedPriceFromRemarks($branchRequest->remarks ?? '');
                $priceType = $this->getPriceTypeFromRemarks($branchRequest->remarks ?? '');
                $lineTotal = ($quantity * $price) + $addedPrice;
                $totalAmount += $lineTotal;

                DB::table('branch_requests')
                    ->where('id', $branchRequest->id)
                    ->update(array_merge([
                        'unit' => $item['unit'] ?? $menu->unit,
                        'quantity' => $quantity,
                        'qty_approved' => $quantity,
                        'qty_received' => $quantity,
                        'price' => $price,
                        'total_price' => $lineTotal,
                        'current_stock' => $currentStock,
                        'updated_at' => now(),
                    ], $this->makeLocationTransferMetadataFields($priceType, $addedPrice, $quantityBreakdown)));
            }

            DB::table('branch_transaction_requests')
                ->where('id', $transaction->id)
                ->update([
                    'total' => $totalAmount,
                    'order_remarks' => 'Updated by ' . $user->name . ' at ' . now()->format('Y-m-d H:i:s'),
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Location transfer updated successfully',
            ]);
        } catch (ValidationException $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?? $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (Exception $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function approvePendingTransfer($id)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $transaction = DB::table('branch_transaction_requests')
                ->where('id', $id)
                ->where('order_status', 'PENDING')
                ->first();

            if (!$transaction) {
                abort(404);
            }

            if (!$this->userCanManagePendingLocationTransfer($transaction, $user)) {
                throw ValidationException::withMessages([
                    'items' => 'Only the sending store can approve this location transfer.',
                ]);
            }

            $items = DB::table('branch_requests')->where('tran_id', $transaction->id)->get();

            foreach ($items as $item) {
                $menu = Menu::find($item->inv_id);
                if (!$menu) {
                    throw ValidationException::withMessages([
                        'items' => $item->name . ' could not be found in the sending branch inventory.',
                    ]);
                }

                $approvedQty = (float) $item->qty_approved;
                $quantityBefore = $this->getMenuCurrentStock($menu);
                $this->assertQuantityWithinStock($approvedQty, $quantityBefore, $menu->name);
                $quantityAfter = $quantityBefore - $approvedQty;

                $menu->update([
                    'current_quantity' => $quantityAfter,
                    'quantity' => $quantityAfter,
                    'updated_at' => now(),
                ]);

                DB::table('branch_requests')
                    ->where('id', $item->id)
                    ->update(array_merge([
                        'updated_at' => now(),
                    ], $this->makeLocationTransferMetadataFields(
                        $this->getPriceTypeFromRemarks($item->remarks ?? ''),
                        $this->getAddedPriceFromRemarks($item->remarks ?? ''),
                        $this->getQuantityBreakdownFromItem($item, (float) ($item->quantity ?? 0)),
                        $this->getQuantityBreakdownStatusesFromItem($item, count($this->getQuantityBreakdownFromItem($item, (float) ($item->quantity ?? 0)))),
                        'APPROVED'
                    )));
            }

            DB::table('branch_transaction_requests')
                ->where('id', $transaction->id)
                ->update([
                    'status' => 'APPROVED',
                    'order_status' => 'APPROVED',
                    'order_remarks' => 'Approved by ' . $user->name,
                    'updated_at' => now(),
                ]);

            $log = new Log();
            $log->trans_id = $transaction->id;
            $log->classification = 'Branch Transfer';
            $log->info = 'Request Approval';
            $log->user_id = $user->id;
            $log->event = 'Approval';
            $log->save();

            DB::commit();

            return redirect()->route('branch-transfer.pending')
                ->with('message', 'Location transfer approved successfully.');
        } catch (ValidationException $exception) {
            DB::rollBack();

            return back()->with('error', collect($exception->errors())->flatten()->first() ?? $exception->getMessage());
        } catch (Exception $exception) {
            DB::rollBack();

            Logger::error('Error approving location transfer:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'transfer_id' => $id,
            ]);

            return back()->with('error', 'Error approving location transfer: ' . $exception->getMessage());
        }
    }

    public function rejectPendingTransfer(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $transaction = DB::table('branch_transaction_requests')
            ->where('id', $id)
            ->where('order_status', 'PENDING')
            ->first();

        if (!$transaction) {
            return back()->with('error', 'This location transfer is no longer pending.');
        }

        if (!$this->userCanManagePendingLocationTransfer($transaction, $user)) {
            return back()->with('error', 'Only the sending store can reject this location transfer.');
        }

        DB::table('branch_transaction_requests')
            ->where('id', $id)
            ->update([
                'status' => 'REJECTED',
                'order_status' => 'REJECTED',
                'order_remarks' => $request->rejection_reason,
                'updated_at' => now(),
            ]);

        DB::table('branch_requests')
            ->where('tran_id', $id)
            ->update([
                'updated_at' => now(),
            ]);

        $log = new Log();
        $log->trans_id = $id;
        $log->classification = 'Branch Transfer';
        $log->info = 'Request Rejected';
        $log->user_id = $user->id;
        $log->event = 'Reject';
        $log->save();

        return redirect()->route('branch-transfer.pending')
            ->with('message', 'Location transfer rejected successfully.');
    }

    public function receivedTransfers()
    {
        $user = Auth::user();
        $isMainLocationTransferBranch = $this->userIsMainLocationTransferBranch($user);
        $selectedBranch = request('branch_scope');

        $receivedQuery = DB::table('branch_transaction_requests')
            ->leftJoin('users', 'branch_transaction_requests.cashier', '=', 'users.id')
            ->select(
                'branch_transaction_requests.*',
                'users.store_code as sender_store_code',
                'users.branch_code as sender_branch_code',
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT_WS(' ', users.firstname, users.middlename, users.lastname)), ''), users.username, 'Unknown') as user_name")
            )
            ->whereIn('branch_transaction_requests.order_status', ['APPROVED', 'CHECKING', 'RECEIVED'])
            ->orderBy('branch_transaction_requests.updated_at', 'desc');

        $this->scopeLocationTransferReceivedQuery($receivedQuery, $user, $selectedBranch);

        $locationTransferBranches = $this->getLocationTransferBranchFilterOptions();
        $receivedOrders = $receivedQuery
            ->paginate(100)
            ->appends(request()->query())
            ->through(function ($order) use ($user) {
                $order = $this->formatReceivedLocationTransferOrder($order);
                $order->can_receive = $this->userCanReceiveLocationTransfer($order, $user);

                return $order;
            });

        return view('user.inventory.branch-transfer-received', compact(
            'receivedOrders',
            'isMainLocationTransferBranch',
            'locationTransferBranches',
            'selectedBranch'
        ));
    }

    public function viewReceivedTransferJson($id)
    {
        return $this->viewPendingApprovalJson($id);
    }

    public function markReceivedTransferChecking($id)
    {
        $user = Auth::user();

        try {
            $transaction = DB::table('branch_transaction_requests')
                ->where('id', $id)
                ->whereIn('order_status', ['APPROVED', 'CHECKING'])
                ->first();

            if (!$transaction) {
                abort(404);
            }

            if (!$this->userCanReceiveLocationTransfer($transaction, $user)) {
                throw ValidationException::withMessages([
                    'items' => 'Only the selected receiving store can receive this location transfer.',
                ]);
            }

            $metadata = $this->getReceiveMetadataFromRemarks($transaction->order_remarks ?? '');
            $metadata['checked_by'] = $this->getCurrentUserDisplayName($user);

            DB::table('branch_transaction_requests')
                ->where('id', $transaction->id)
                ->update([
                    'status' => 'CHECKING',
                    'order_status' => 'CHECKING',
                    'order_remarks' => $this->buildReceiveRemarks($metadata),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Location transfer is now being checked.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function cancelReceivedTransferChecking($id)
    {
        $user = Auth::user();

        try {
            $transaction = DB::table('branch_transaction_requests')
                ->where('id', $id)
                ->where('order_status', 'CHECKING')
                ->first();

            if (!$transaction) {
                abort(404);
            }

            if (!$this->userCanReceiveLocationTransfer($transaction, $user)) {
                throw ValidationException::withMessages([
                    'items' => 'Only the selected receiving store can update this location transfer.',
                ]);
            }

            DB::table('branch_transaction_requests')
                ->where('id', $transaction->id)
                ->update([
                    'status' => 'APPROVED',
                    'order_status' => 'APPROVED',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Location transfer returned to Not Received.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function confirmReceivedTransfer(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'prepared_by' => 'nullable|string|max:255',
            'checked_by' => 'required|string|max:255',
            'received_by' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:branch_requests,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.quantity_breakdown' => 'required|array|min:1',
            'items.*.quantity_breakdown.*' => 'numeric|min:0.001',
            'items.*.breakdown_statuses' => 'nullable|array',
            'items.*.breakdown_statuses.*' => 'nullable|in:unchecked,received,rejected',
        ]);

        try {
            DB::beginTransaction();

            $transaction = DB::table('branch_transaction_requests')
                ->where('id', $id)
                ->whereIn('order_status', ['APPROVED', 'CHECKING'])
                ->first();

            if (!$transaction) {
                abort(404);
            }

            if (!$this->userCanReceiveLocationTransfer($transaction, $user)) {
                throw ValidationException::withMessages([
                    'items' => 'Only the selected receiving store can confirm this location transfer.',
                ]);
            }

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $branchRequest = DB::table('branch_requests')
                    ->where('id', $item['id'])
                    ->where('tran_id', $transaction->id)
                    ->first();

                if (!$branchRequest) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more transfer items could not be found.',
                    ]);
                }

                $receivedQty = (float) $item['quantity'];
                $quantityBreakdown = $this->normalizeQuantityBreakdown($item['quantity_breakdown'] ?? [], $receivedQty);
                $this->assertQuantityBreakdownMatches($quantityBreakdown, $receivedQty);
                $breakdownStatuses = $this->normalizeQuantityBreakdownStatuses($item['breakdown_statuses'] ?? [], count($quantityBreakdown));
                $approvedQty = (float) ($branchRequest->qty_approved ?? $branchRequest->quantity ?? 0);
                $price = (float) ($branchRequest->price ?? 0);
                $addedPrice = $this->getAddedPriceFromRemarks($branchRequest->remarks ?? '');
                $priceType = $this->getPriceTypeFromRemarks($branchRequest->remarks ?? '');
                $lineTotal = ($receivedQty * $price) + $addedPrice;

                $this->reconcileSenderStockForReceivedQuantity($branchRequest, $approvedQty, $receivedQty);
                $this->increaseDestinationStockForReceivedQuantity($transaction, $branchRequest, $receivedQty);

                DB::table('branch_requests')
                    ->where('id', $branchRequest->id)
                    ->update(array_merge([
                        'quantity' => $receivedQty,
                        'qty_received' => $receivedQty,
                        'total_price' => $lineTotal,
                        'updated_at' => now(),
                    ], $this->makeLocationTransferMetadataFields($priceType, $addedPrice, $quantityBreakdown, $breakdownStatuses, 'RECEIVED')));

                $totalAmount += $lineTotal;
            }

            $metadata = [
                'prepared_by' => $request->prepared_by ?: 'Unknown',
                'checked_by' => $request->checked_by,
                'received_by' => $request->received_by,
                'date_delivered' => now()->format('Y-m-d H:i:s'),
                'remarks' => $request->remarks ?? '',
            ];

            DB::table('branch_transaction_requests')
                ->where('id', $transaction->id)
                ->update([
                    'total' => $totalAmount,
                    'status' => 'RECEIVED',
                    'order_status' => 'RECEIVED',
                    'order_remarks' => $this->buildReceiveRemarks($metadata),
                    'updated_at' => now(),
                ]);

            $log = new Log();
            $log->trans_id = $transaction->id;
            $log->classification = 'Branch Transfer';
            $log->info = 'Request Received';
            $log->user_id = $user->id;
            $log->event = 'Receive';
            $log->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Location transfer received successfully.',
            ]);
        } catch (ValidationException $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?? $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (Exception $exception) {
            DB::rollBack();

            Logger::error('Error receiving location transfer:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'transfer_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function getLocationTransferPrefix(?string $branchName): string
    {
        $normalizedName = Str::upper(trim((string) $branchName));

        foreach (self::LOCATION_TRANSFER_PREFIXES as $branchKeyword => $prefix) {
            if (Str::contains($normalizedName, $branchKeyword)) {
                return $prefix;
            }
        }

        return 'LT';
    }

    private function getCurrentUserBranch($user)
    {
        return Setting::where('is_deleted', 0)
            ->where(function ($query) use ($user) {
                $query->where('store_code', $user->store_code);

                if (!empty($user->branch_code)) {
                    $query->orWhere('branch_code', $user->branch_code);
                }
            })
            ->first();
    }

    private function getUserLocationTransferCodes($user): array
    {
        if (!$user) {
            return [];
        }

        $currentBranch = $this->getCurrentUserBranch($user);

        return array_values(array_unique(array_filter([
            $user->store_code ?? null,
            $user->branch_code ?? null,
            $currentBranch?->store_code,
            $currentBranch?->branch_code,
        ])));
    }

    private function userIsMainLocationTransferBranch($user): bool
    {
        if (!$user || !Schema::hasColumn('settings', 'is_main_branch')) {
            return false;
        }

        $userCodes = $this->getUserLocationTransferCodes($user);
        if (empty($userCodes)) {
            return false;
        }

        return Setting::where('is_deleted', 0)
            ->where('is_main_branch', 1)
            ->where(function ($query) use ($userCodes) {
                $query->whereIn('store_code', $userCodes)
                    ->orWhereIn('branch_code', $userCodes);
            })
            ->exists();
    }

    private function getLocationTransferBranchFilterOptions()
    {
        return Setting::where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'store_code', 'branch_code']);
    }

    private function getBranchScopeCodes(?string $branchId): array
    {
        if (!$branchId) {
            return [];
        }

        $branch = Setting::where('is_deleted', 0)->find($branchId);
        if (!$branch) {
            return [];
        }

        return array_values(array_unique(array_filter([
            $branch->store_code,
            $branch->branch_code,
        ])));
    }

    private function scopeLocationTransferPendingQuery($query, $user, ?string $selectedBranch = null): void
    {
        if ($this->userIsMainLocationTransferBranch($user)) {
            $branchCodes = $this->getBranchScopeCodes($selectedBranch);
            if (!empty($branchCodes)) {
                $query->where(function ($scope) use ($branchCodes) {
                    $scope->whereIn('branch_transaction_requests.branch_code', $branchCodes)
                        ->orWhereIn('users.store_code', $branchCodes)
                        ->orWhereIn('users.branch_code', $branchCodes);
                });
            }

            return;
        }

        $userCodes = $this->getUserLocationTransferCodes($user);
        $query->where(function ($scope) use ($userCodes, $user) {
            $scope->whereIn('users.store_code', $userCodes)
                ->orWhereIn('users.branch_code', $userCodes)
                ->orWhere('branch_transaction_requests.cashier', $user->id);
        });
    }

    private function scopeLocationTransferReceivedQuery($query, $user, ?string $selectedBranch = null): void
    {
        if ($this->userIsMainLocationTransferBranch($user)) {
            $branchCodes = $this->getBranchScopeCodes($selectedBranch);
            if (!empty($branchCodes)) {
                $query->where(function ($scope) use ($branchCodes) {
                    $scope->whereIn('branch_transaction_requests.branch_code', $branchCodes)
                        ->orWhereIn('users.store_code', $branchCodes)
                        ->orWhereIn('users.branch_code', $branchCodes);
                });
            }

            return;
        }

        $userCodes = $this->getUserLocationTransferCodes($user);
        $query->whereIn('branch_transaction_requests.branch_code', $userCodes);
    }

    private function getSenderCodesForLocationTransfer($transaction): array
    {
        $codes = array_filter([
            $transaction->sender_store_code ?? null,
            $transaction->sender_branch_code ?? null,
        ]);

        if ((!$codes || empty($codes)) && !empty($transaction->cashier)) {
            $sender = DB::table('users')->where('id', $transaction->cashier)->first();
            $codes = array_filter([
                $sender->store_code ?? null,
                $sender->branch_code ?? null,
            ]);
        }

        return array_values(array_unique($codes));
    }

    private function userCanManagePendingLocationTransfer($transaction, $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->userIsMainLocationTransferBranch($user)) {
            return true;
        }

        return !empty(array_intersect(
            $this->getSenderCodesForLocationTransfer($transaction),
            $this->getUserLocationTransferCodes($user)
        ));
    }

    private function userCanViewLocationTransfer($transaction, $user): bool
    {
        if ($this->userCanManagePendingLocationTransfer($transaction, $user)) {
            return true;
        }

        $status = $this->normalizeLocationTransferStatus($transaction->order_status ?? $transaction->status ?? '');

        return in_array($status, ['approved', 'checking', 'received'], true)
            && $this->userCanReceiveLocationTransfer($transaction, $user);
    }

    private function getNextLocationTransferNumber(string $prefix): string
    {
        $latestNumber = null;

        try {
            $latestNumber = DB::table('branch_transaction_requests')
                ->where('number', 'like', $prefix . '-%')
                ->orderByDesc('id')
                ->value('number');
        } catch (Exception $exception) {
            Logger::warning('Unable to read latest branch transfer transaction number.', [
                'prefix' => $prefix,
                'message' => $exception->getMessage(),
            ]);
        }

        $nextNumber = 1;
        if ($latestNumber && preg_match('/(\d+)$/', $latestNumber, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    private function getMenuPriceByType(Menu $menu, string $priceType): float
    {
        return match ($priceType) {
            'wholesale' => (float) ($menu->wholesale_price ?? 0),
            'retail' => (float) ($menu->retail_price ?? 0),
            'slab' => (float) ($menu->slab_price ?? 0),
            'buying_price' => (float) ($menu->food_cost ?? 0),
            default => 0,
        };
    }

    private function getMenuCurrentStock(Menu $menu): float
    {
        return (float) ($menu->current_quantity ?? $menu->quantity ?? 0);
    }

    private function assertQuantityWithinStock(float $quantity, float $currentStock, string $itemName): void
    {
        if ($quantity <= $currentStock + 0.0001) {
            return;
        }

        throw ValidationException::withMessages([
            'items' => sprintf(
                '%s only has %s units available. Requested quantity is %s.',
                $itemName,
                number_format($currentStock, 3, '.', ','),
                number_format($quantity, 3, '.', ',')
            ),
        ]);
    }

    private function makeUniqueSlug(string $table): string
    {
        do {
            $slug = Str::random(60);
        } while (DB::table($table)->where('slug', $slug)->exists());

        return $slug;
    }

    private function formatLocationTransferOrder($order)
    {
        $status = $this->normalizeLocationTransferStatus($order->order_status ?? $order->status ?? 'PENDING');

        $order->reference_number = $order->number;
        $order->sender_branch_name = $this->getLocationTransferSenderBranchName($order);
        $order->branch_name = $this->getLocationTransferBranchName($order->branch_code ?? '');
        $order->sales_invoice = $order->number;
        $order->order_date = $order->created_at;
        $order->total_amount = $this->getLocationTransferTotal((int) $order->id, (float) ($order->total ?? 0));
        $order->status = $status;
        $order->remarks = $order->order_remarks ?? '';
        $order->can_manage = $this->userCanManagePendingLocationTransfer($order, Auth::user());
        $order->approved_by_name = $status === 'approved' ? ($order->user_name ?? 'Unknown') : null;
        $order->approved_at = $status === 'approved' ? $order->updated_at : null;
        $order->rejected_by_name = $status === 'rejected' ? ($order->user_name ?? 'Unknown') : null;
        $order->rejected_at = $status === 'rejected' ? $order->updated_at : null;

        return $order;
    }

    private function formatReceivedLocationTransferOrder($order)
    {
        $metadata = $this->getReceiveMetadataFromRemarks($order->order_remarks ?? '');
        $receiveStatus = $this->normalizeReceivedLocationTransferStatus($order->order_status ?? $order->status ?? 'APPROVED');

        $order->sales_invoice = $order->number;
        $order->sender_branch_name = $this->getLocationTransferSenderBranchName($order);
        $order->branch_name = $this->getLocationTransferBranchName($order->branch_code ?? '');
        $order->prepared_by = $metadata['prepared_by'] ?: ($order->user_name ?? 'Unknown');
        $order->checked_by = $metadata['checked_by'] ?: ($receiveStatus === 'checking' ? 'Checking' : 'N/A');
        $order->received_by = $metadata['received_by'] ?: 'N/A';
        $order->date_delivered = $metadata['date_delivered'] ?: null;
        $order->receive_status = $receiveStatus;
        $order->receive_status_label = match ($receiveStatus) {
            'received' => 'Received',
            'checking' => 'Checking',
            default => 'Not Received',
        };

        return $order;
    }

    private function normalizeReceivedLocationTransferStatus(?string $status): string
    {
        return match (Str::upper((string) $status)) {
            'RECEIVED' => 'received',
            'CHECKING' => 'checking',
            default => 'not_received',
        };
    }

    private function userCanReceiveLocationTransfer($transaction, $user): bool
    {
        if (!$user || empty($transaction->branch_code)) {
            return false;
        }

        $destinationBranch = Setting::where('branch_code', $transaction->branch_code)
            ->orWhere('store_code', $transaction->branch_code)
            ->first();

        $destinationCodes = array_filter([
            $transaction->branch_code,
            $destinationBranch?->branch_code,
            $destinationBranch?->store_code,
        ]);

        $currentUserBranch = $this->getCurrentUserBranch($user);
        $userCodes = array_filter([
            $user->branch_code ?? null,
            $user->store_code ?? null,
            $currentUserBranch?->branch_code,
            $currentUserBranch?->store_code,
        ]);

        return !empty(array_intersect($destinationCodes, $userCodes));
    }

    private function getCurrentUserDisplayName($user): string
    {
        $name = trim(implode(' ', array_filter([
            $user->firstname ?? null,
            $user->middlename ?? null,
            $user->lastname ?? null,
        ])));

        return $name ?: ($user->username ?? $user->email ?? 'Unknown');
    }

    private function getReceiveMetadataFromRemarks(?string $remarks): array
    {
        $metadata = [
            'prepared_by' => '',
            'checked_by' => '',
            'received_by' => '',
            'date_delivered' => '',
            'remarks' => '',
        ];

        if (!$remarks) {
            return $metadata;
        }

        $decoded = json_decode($remarks, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_merge($metadata, array_intersect_key($decoded, $metadata));
        }

        foreach ($metadata as $key => $value) {
            $label = str_replace('_', ' ', $key);
            if (preg_match('/' . preg_quote($label, '/') . ':\s*([^;]+)/i', $remarks, $matches)) {
                $metadata[$key] = trim($matches[1]);
            }
        }

        return $metadata;
    }

    private function buildReceiveRemarks(array $metadata): string
    {
        return json_encode([
            'prepared_by' => $metadata['prepared_by'] ?? '',
            'checked_by' => $metadata['checked_by'] ?? '',
            'received_by' => $metadata['received_by'] ?? '',
            'date_delivered' => $metadata['date_delivered'] ?? '',
            'remarks' => $metadata['remarks'] ?? '',
        ]);
    }

    private function reconcileSenderStockForReceivedQuantity($branchRequest, float $approvedQty, float $receivedQty): void
    {
        $quantityDelta = $receivedQty - $approvedQty;
        if (abs($quantityDelta) < 0.0001) {
            return;
        }

        $menu = Menu::find($branchRequest->inv_id);
        if (!$menu) {
            throw ValidationException::withMessages([
                'items' => $branchRequest->name . ' could not be found in the sending branch inventory.',
            ]);
        }

        $currentStock = $this->getMenuCurrentStock($menu);
        if ($quantityDelta > 0) {
            $this->assertQuantityWithinStock($quantityDelta, $currentStock, $menu->name);
        }

        $newStock = $currentStock - $quantityDelta;
        $menu->update([
            'current_quantity' => $newStock,
            'quantity' => $newStock,
            'updated_at' => now(),
        ]);
    }

    private function increaseDestinationStockForReceivedQuantity($transaction, $branchRequest, float $receivedQty): void
    {
        if ($receivedQty <= 0) {
            return;
        }

        $destinationBranch = Setting::where('branch_code', $transaction->branch_code ?? '')
            ->orWhere('store_code', $transaction->branch_code ?? '')
            ->first();
        $destinationStoreCode = $destinationBranch?->store_code ?: ($transaction->branch_code ?? '');

        if (!$destinationStoreCode) {
            throw ValidationException::withMessages([
                'items' => 'Destination branch could not be identified for receiving stock.',
            ]);
        }

        $destinationMenu = null;
        if (!empty($branchRequest->item_code)) {
            $destinationMenu = Menu::where('store_code', $destinationStoreCode)
                ->where('is_deleted', 0)
                ->where('item_code', $branchRequest->item_code)
                ->first();
        }

        if (!$destinationMenu) {
            $destinationMenu = Menu::where('store_code', $destinationStoreCode)
                ->where('is_deleted', 0)
                ->where('name', $branchRequest->name)
                ->first();
        }

        if (!$destinationMenu) {
            $destinationMenu = $this->createDestinationMenuForReceivedTransfer($destinationBranch, $destinationStoreCode, $branchRequest);
        }

        $newStock = $this->getMenuCurrentStock($destinationMenu) + $receivedQty;
        $updateData = [
            'current_quantity' => $newStock,
            'quantity' => $newStock,
            'updated_at' => now(),
        ];

        if (empty($destinationMenu->item_code) && !empty($branchRequest->item_code)) {
            $updateData['item_code'] = $branchRequest->item_code;
        }

        $destinationMenu->update($updateData);
    }

    private function createDestinationMenuForReceivedTransfer(?Setting $destinationBranch, string $destinationStoreCode, $branchRequest): Menu
    {
        $sourceMenu = Menu::find($branchRequest->inv_id);

        $data = [
            'name' => $branchRequest->name,
            'category_id' => $sourceMenu?->category_id,
            'brand_id' => $sourceMenu?->brand_id,
            'brand_code' => $sourceMenu?->brand_code,
            'current_quantity' => 0,
            'quantity' => 0,
            'price' => $sourceMenu?->price ?? $branchRequest->price ?? 0,
            'item_code' => $branchRequest->item_code ?: $sourceMenu?->item_code,
            'grab_price' => $sourceMenu?->grab_price ?? 0,
            'panda_price' => $sourceMenu?->panda_price ?? 0,
            'wholesale_price' => $sourceMenu?->wholesale_price ?? 0,
            'slab_price' => $sourceMenu?->slab_price ?? 0,
            'retail_price' => $sourceMenu?->retail_price ?? 0,
            'image' => $sourceMenu?->image,
            'slug' => $this->makeUniqueSlug('menus'),
            'info' => $sourceMenu?->info,
            'food_cost' => $sourceMenu?->food_cost ?? 0,
            'department' => $sourceMenu?->department ?? $branchRequest->department,
            'branch_id' => $destinationBranch?->id,
            'servings' => $sourceMenu?->servings ?? 1,
            'is_combo' => $sourceMenu?->is_combo ?? 0,
            'unit' => $branchRequest->unit ?? $sourceMenu?->unit,
            'is_discount' => $sourceMenu?->is_discount ?? 0,
            'is_hidden' => $sourceMenu?->is_hidden ?? 0,
            'reorder_point' => $sourceMenu?->reorder_point ?? $branchRequest->reorder_point ?? 0,
            'sku' => $sourceMenu?->sku,
            'barcode' => $sourceMenu?->barcode,
            'is_deleted' => 0,
            'is_uuid' => $sourceMenu?->is_uuid ?? 0,
            'client_token' => $sourceMenu?->client_token,
            'is_sync' => 0,
            'branch_code' => $destinationBranch?->branch_code ?? $destinationStoreCode,
            'local_id' => null,
            'store_code' => $destinationBranch?->store_code ?? $destinationStoreCode,
            'branch' => $destinationBranch?->name,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $menuId = DB::table('menus')->insertGetId($data);

        return Menu::findOrFail($menuId);
    }

    private function getLocationTransferTotal(int $transactionId, float $fallbackTotal): float
    {
        $items = DB::table('branch_requests')
            ->where('tran_id', $transactionId)
            ->get();

        if ($items->isEmpty()) {
            return $fallbackTotal;
        }

        $itemsTotal = $items->sum(function ($item) {
            $quantity = (float) ($item->quantity ?? 0);
            $price = (float) ($item->price ?? 0);
            $addedPrice = $this->getAddedPriceFromRemarks($item->remarks ?? '');

            return isset($item->total_price) ? (float) $item->total_price : (($quantity * $price) + $addedPrice);
        });

        return (float) $itemsTotal;
    }

    private function getLocationTransferBranchName(?string $branchCode): string
    {
        if (!$branchCode) {
            return 'N/A';
        }

        $branch = Setting::where('branch_code', $branchCode)
            ->orWhere('store_code', $branchCode)
            ->first();

        return $branch?->name ?? $branchCode;
    }

    private function getLocationTransferSenderBranchName($order): string
    {
        $senderCodes = $this->getSenderCodesForLocationTransfer($order);
        foreach ($senderCodes as $code) {
            $branchName = $this->getLocationTransferBranchName($code);
            if ($branchName !== 'N/A' && $branchName !== $code) {
                return $branchName;
            }
        }

        return $order->user_name ?? 'Unknown';
    }

    private function normalizeLocationTransferStatus(?string $status): string
    {
        return match (Str::upper((string) $status)) {
            'APPROVED', 'APPROVE-LOG' => 'approved',
            'REJECTED', 'REJECT' => 'rejected',
            'RECEIVED' => 'approved',
            default => 'pending',
        };
    }

    private function getAddedPriceFromRemarks(?string $remarks): float
    {
        if ($remarks && preg_match('/(?:^|;)\s*AP:([0-9]+(?:\.[0-9]+)?)/i', $remarks, $matches)) {
            return (float) $matches[1];
        }

        if (!$remarks || !preg_match('/Added Price:\s*([0-9]+(?:\.[0-9]+)?)/i', $remarks, $matches)) {
            return 0;
        }

        return (float) $matches[1];
    }

    private function getPriceTypeFromRemarks(?string $remarks): string
    {
        if ($remarks && preg_match('/(?:^|;)\s*PT:([^;]+)/i', $remarks, $matches)) {
            return trim($matches[1]);
        }

        if (!$remarks || !preg_match('/Price Type:\s*([^;]+)/i', $remarks, $matches)) {
            return '';
        }

        return trim($matches[1]);
    }

    private function makeLocationTransferRemarks(
        string $priceType,
        float $addedPrice,
        array $quantityBreakdown,
        array $breakdownStatuses = [],
        string $itemStatus = ''
    ): string
    {
        $breakdown = $this->normalizeQuantityBreakdown($quantityBreakdown, 0);
        $statuses = $this->normalizeQuantityBreakdownStatuses($breakdownStatuses, count($breakdown));
        $encodedBreakdown = implode(',', array_map(fn ($value) => rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.'), $breakdown));
        $encodedStatuses = $this->encodeQuantityBreakdownStatuses($statuses);

        return 'PT:' . $priceType .
            ';AP:' . rtrim(rtrim(number_format($addedPrice, 2, '.', ''), '0'), '.') .
            ';QB:' . $encodedBreakdown .
            ($encodedStatuses ? ';BS:' . $encodedStatuses : '') .
            ($itemStatus ? ';IS:' . $itemStatus : '');
    }

    private function makeLocationTransferMetadataFields(
        string $priceType,
        float $addedPrice,
        array $quantityBreakdown,
        array $breakdownStatuses = [],
        string $itemStatus = ''
    ): array {
        $breakdown = $this->normalizeQuantityBreakdown($quantityBreakdown, 0);
        $statuses = $this->normalizeQuantityBreakdownStatuses($breakdownStatuses, count($breakdown));
        $fields = [
            'remarks' => $this->makeLocationTransferRemarks($priceType, $addedPrice, $breakdown, $statuses, $itemStatus),
        ];

        if ($this->branchRequestsHasColumn('quantity_breakdown')) {
            $fields['quantity_breakdown'] = json_encode($breakdown);
        }

        if ($this->branchRequestsHasColumn('breakdown_statuses')) {
            $fields['breakdown_statuses'] = json_encode($statuses);
        }

        return $fields;
    }

    private function branchRequestsHasColumn(string $column): bool
    {
        static $columns = null;

        if ($columns === null) {
            $columns = Schema::hasTable('branch_requests')
                ? array_flip(Schema::getColumnListing('branch_requests'))
                : [];
        }

        return isset($columns[$column]);
    }

    private function normalizeQuantityBreakdown($breakdown, float $fallbackQuantity): array
    {
        if (is_string($breakdown)) {
            $decoded = json_decode($breakdown, true);
            $breakdown = is_array($decoded) ? $decoded : [$breakdown];
        }

        if (!is_array($breakdown)) {
            $breakdown = [];
        }

        $values = array_values(array_filter(array_map(function ($value) {
            if ($value === '' || $value === null || !is_numeric($value)) {
                return null;
            }

            $quantity = (float) $value;

            return $quantity > 0 ? round($quantity, 3) : null;
        }, $breakdown), fn ($value) => $value !== null));

        if (empty($values) && $fallbackQuantity > 0) {
            return [round($fallbackQuantity, 3)];
        }

        return $values;
    }

    private function getQuantityBreakdownFromRemarks(?string $remarks, float $fallbackQuantity): array
    {
        if ($remarks && preg_match('/(?:^|;)\s*QB:([^;]+)/i', $remarks, $matches)) {
            return $this->normalizeQuantityBreakdown(explode(',', $matches[1]), $fallbackQuantity);
        }

        if (!$remarks || !preg_match('/Quantity Breakdown:\s*(\[[^\]]*\])/i', $remarks, $matches)) {
            return $this->normalizeQuantityBreakdown([], $fallbackQuantity);
        }

        return $this->normalizeQuantityBreakdown($matches[1], $fallbackQuantity);
    }

    private function getQuantityBreakdownFromItem($item, float $fallbackQuantity): array
    {
        if (isset($item->quantity_breakdown) && $item->quantity_breakdown !== null && $item->quantity_breakdown !== '') {
            return $this->normalizeQuantityBreakdown($item->quantity_breakdown, $fallbackQuantity);
        }

        return $this->getQuantityBreakdownFromRemarks($item->remarks ?? '', $fallbackQuantity);
    }

    private function normalizeQuantityBreakdownStatuses($statuses, int $count): array
    {
        if (is_string($statuses)) {
            $decoded = json_decode($statuses, true);
            $statuses = is_array($decoded) ? $decoded : [$statuses];
        }

        if (!is_array($statuses)) {
            $statuses = [];
        }

        $allowed = ['unchecked', 'received', 'rejected'];
        $normalized = [];

        for ($index = 0; $index < $count; $index++) {
            $status = $statuses[$index] ?? 'unchecked';
            $normalized[] = in_array($status, $allowed, true) ? $status : 'unchecked';
        }

        return $normalized;
    }

    private function getQuantityBreakdownStatusesFromRemarks(?string $remarks, int $count): array
    {
        if ($remarks && preg_match('/(?:^|;)\s*BS:([^;]+)/i', $remarks, $matches)) {
            return $this->decodeQuantityBreakdownStatuses($matches[1], $count);
        }

        if (!$remarks || !preg_match('/Breakdown Statuses:\s*(\[[^\]]*\])/i', $remarks, $matches)) {
            return $this->normalizeQuantityBreakdownStatuses([], $count);
        }

        return $this->normalizeQuantityBreakdownStatuses($matches[1], $count);
    }

    private function getQuantityBreakdownStatusesFromItem($item, int $count): array
    {
        if (isset($item->breakdown_statuses) && $item->breakdown_statuses !== null && $item->breakdown_statuses !== '') {
            return $this->normalizeQuantityBreakdownStatuses($item->breakdown_statuses, $count);
        }

        return $this->getQuantityBreakdownStatusesFromRemarks($item->remarks ?? '', $count);
    }

    private function encodeQuantityBreakdownStatuses(array $statuses): string
    {
        if (empty($statuses) || count(array_unique($statuses)) === 1 && ($statuses[0] ?? 'unchecked') === 'unchecked') {
            return '';
        }

        return implode('', array_map(function ($status) {
            return match ($status) {
                'received' => 'g',
                'rejected' => 'x',
                default => 'u',
            };
        }, $statuses));
    }

    private function decodeQuantityBreakdownStatuses(string $encodedStatuses, int $count): array
    {
        $statuses = [];
        $characters = str_split($encodedStatuses);

        for ($index = 0; $index < $count; $index++) {
            $statuses[] = match ($characters[$index] ?? 'u') {
                'g' => 'received',
                'x' => 'rejected',
                default => 'unchecked',
            };
        }

        return $statuses;
    }

    private function assertQuantityBreakdownMatches(array $breakdown, float $quantity): void
    {
        $breakdownTotal = array_sum($breakdown);

        if (abs($breakdownTotal - $quantity) <= 0.0001) {
            return;
        }

        throw ValidationException::withMessages([
            'items' => sprintf(
                'Quantity breakdown total (%s) must match quantity (%s).',
                number_format($breakdownTotal, 3, '.', ''),
                number_format($quantity, 3, '.', '')
            ),
        ]);
    }

    private function getLocationTransferItems(int $transactionId): array
    {
        return DB::table('branch_requests')
            ->where('tran_id', $transactionId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item) {
                $menu = Menu::with('brand')->find($item->inv_id);
                $quantity = (float) ($item->quantity ?? 0);
                $price = (float) ($item->price ?? 0);
                $addedPrice = $this->getAddedPriceFromRemarks($item->remarks ?? '');
                $lineTotal = isset($item->total_price) ? (float) $item->total_price : (($quantity * $price) + $addedPrice);
                $quantityBreakdown = $this->getQuantityBreakdownFromItem($item, $quantity);

                return [
                    'id' => $item->id,
                    'menu_id' => $item->inv_id,
                    'menu_name' => $item->name,
                    'brand_name' => $menu?->brand?->name ?? 'N/A',
                    'quantity' => $quantity,
                    'approved_quantity' => (float) ($item->qty_approved ?? $quantity),
                    'price' => $price,
                    'added_price' => $addedPrice,
                    'quantity_breakdown' => $quantityBreakdown,
                    'breakdown_statuses' => $this->getQuantityBreakdownStatusesFromItem($item, count($quantityBreakdown)),
                    'unit' => $item->unit ?? $menu?->unit ?? '',
                    'current_quantity' => $item->current_stock ?? $menu?->current_quantity ?? 0,
                    'sender_current_quantity' => $menu?->current_quantity ?? 0,
                    'line_total' => $lineTotal,
                ];
            })
            ->all();
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

    // DAHON API
    public function branchRequest()
    {
        $apiLink = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/apiInv";


        $response = Http::get($apiLink);

        if (!$response->successful()) {
            return redirect()->back()->with('error', 'Failed to fetch data from API.');
        }

        $data = $response->json(); 

        $apimenus = collect($data['menus']);
        $categories = collect($data['categories'])->pluck('name', 'id');
        $collectCat = $data['categories'];
        $apiMsg = "Wrong Item Code!";
        $user = auth()->user(); 

        // Role-specific filtering
        if ($user->role == 3) {
            $apimenus = $apimenus->whereIn('department', [1, 2])
                ->sortBy([['department', 'asc'], ['id', 'asc']])
                ->values();

            $dahonMenu = DB::table('inventories')
                ->whereNotNull('item_code')
                ->where('is_deleted', 0)
                ->whereIn('department', [1, 2])
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('item_code'); 

        } elseif ($user->role == 4) {
            $apimenus = $apimenus->where('department', $user->department)
                ->sortBy([['department', 'asc'], ['id', 'asc']])
                ->values();

            $dahonMenu = DB::table('inventories')
                ->whereNotNull('item_code')
                ->where('is_deleted', 0)
                ->where('department', $user->department)
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('item_code'); 
        } else {
            $dahonMenu = DB::table('inventories')
                ->whereNotNull('item_code')
                ->where('is_deleted', 0)
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('item_code'); 
        }

        // Enhance apimenus with category and quantity
        $apimenus = $apimenus->map(function ($menu) use ($categories, $dahonMenu) {
            $menu['category'] = $categories[$menu['category_id']] ?? 'Unknown';
            $menu['quantity'] = $dahonMenu[$menu['item_code']]->quantity ?? 0;
            return $menu;
        });

        return view('user.inventory.branch_transfer.branch-request', compact('apimenus', 'collectCat', 'dahonMenu', 'apiMsg', 'user'));
    }

    public function ApiForecast(Request $request) {

        $slug = $request->slug;
        $qty = $request->qty;

        $menus = db::table('inventories')->where('item_code' , $slug)->first();
        $menuprice = (!empty($menus->price)) ? $menus->price : 0;
        $tprice =  $menuprice * $qty;

        $reqCheck = db::table('branch_order_requests')->where('slug' , $slug)->where('cashier' , auth()->id())->count();


        if ($reqCheck > 0){
            DB::table('branch_order_requests')->where('slug' , $slug)->where('cashier' , auth()->id())->update([
                'quantity' => $qty,
                'total_price' => $tprice,

            ]);
        } else {

            $ordReq = new BranchOrderRequest();
            $ordReq->inv_id = $menus->id;
            $ordReq->item_code = $menus->item_code;
            $ordReq->masterlist_id = $menus->masterlist_id;
            $ordReq->name = $menus->name;
            $ordReq->unit = $menus->unit;
            $ordReq->quantity = $qty;
            $ordReq->price = (!empty($menus->price)) ? $menus->price : 0;
            $ordReq->total_price =$tprice;
            $ordReq->current_stock = $menus->quantity;
            $ordReq->reorder_point = $menus->reorder_point;
            $ordReq->remarks = 'n/a';
            $ordReq->cashier = auth()->id();
            $ordReq->role = auth()->user()->role;
            $ordReq->department = $menus->department;
            $ordReq->branch = $menus->branch;
            $ordReq->slug = $slug;
            $ordReq->save();

        }
        
        $response = array(
            'name' => $menus->name,
            'item_code' => $menus->item_code,
            'reorder' => $menus->reorder_point,
            'qty' => $menus->quantity,
            'reqQty' => $qty,
            'reqQtyCount' => $reqCheck,
        );

        echo json_encode($response);
    }

    public function branchSubmit(Request $request) {
        
        $total = 0;

        $ordreqs = db::table('branch_order_requests')->where('cashier' , auth()->id())->get();
        foreach ($ordreqs as $order){
            $total += $order->total_price;
        }
        $id = BranchTransactionRequest::latest()->pluck('id')->first();
        if ($id == null) {
            $id = 1;
        } else {
            $id++;
        }
        $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

        $branding = "D";
        $number = $branding . '-' . date('mdY') . '-' . $nid;
        $branch_code = DB::table('branches')->value('branch_code');



        $tranSlug = Str::random(60);
        $ntranSlug = $tranSlug;
        $check_slug = DB::table('branch_transaction_requests')->where('slug', $ntranSlug)->get();
        while (count($check_slug) > 0) {
            $ntranSlug = Str::random(60);
            $check_slug = DB::table('branch_transaction_requests')->where('slug', $ntranSlug)->get();
        }
        $tranSlug = $ntranSlug;

        $tran = new BranchTransactionRequest();
        $tran->number = $number;
        $tran->branch_code = $branch_code;
        $tran->total = $total;
        $tran->status = 'PENDING';
        $tran->order_status = 'PENDING';
        $tran->order_remarks = '';
        $tran->cashier = auth()->id();
        $tran->slug = $tranSlug;
        $tran->save();

        $tran_id = $tran->id;


        

        foreach ($ordreqs as $order){
            $orderSlug = Str::random(60);
            $norderSlug = $orderSlug;
            $check_slug = DB::table('branch_requests')->where('slug', $norderSlug)->get();
            while (count($check_slug) > 0) {
                $norderSlug = Str::random(60);
                $check_slug = DB::table('branch_requests')->where('slug', $norderSlug)->get();
            }
            $orderSlug = $norderSlug;

            $ordReq = new BranchRequest();
            $ordReq->tran_id = $tran_id;
            $ordReq->inv_id = $order->inv_id;
            $ordReq->item_code = $order->item_code;
            $ordReq->masterlist_id = $order->masterlist_id;
            $ordReq->name = $order->name;
            $ordReq->unit = $order->unit;
            $ordReq->quantity = $order->quantity;
            $ordReq->qty_approved = $order->quantity;
            $ordReq->qty_received = $order->quantity;
            $ordReq->price = $order->price;
            $ordReq->total_price =$order->total_price;
            $ordReq->current_stock = $order->current_stock;
            $ordReq->reorder_point = $order->reorder_point;
            $ordReq->remarks = $order->remarks;
            $ordReq->cashier = auth()->id();
            $ordReq->role = auth()->user()->role;
            $ordReq->department = $order->department;
            $ordReq->branch = $order->branch;
            $ordReq->slug = $orderSlug;
            $ordReq->save();

            DB::table('branch_order_requests')->where('id', $order->id)->delete();
        }

        
        $log = new Log();
        $log->trans_id = $tran_id;
        $log->classification = 'Branch Transfer';
        $log->info = 'Request Submit';
        $log->user_id = auth()->id();
        $log->event = 'Add';
        $log->save();

        

        $response = array(
            'name' => $total,
            'tranid' => $tran_id ,
            'qty' => $total,
            'reqQty' => $total,
            'reqQtyCount' => $total,
     
        );

        echo json_encode($response);
    }

    // CHANGE QTY BEFORE PENDING TO APPROVE STATUS
    // public function appqty(Request $request) {

    //     $id = $request->id;
    //     // dd('THE ID:' . $id);
    //     $qty = $request->qty;
    //     // dd('THE QUANTITY:' . $qty);

    //     $menus = db::table('branch_requests')->where('id' , $id)->first();
    //     $tprice = $menus->price * $qty;
        

    //         DB::table('branch_requests')->where('id' , $id)->update([
    //             'qty_approved' => $qty,
    //             'qty_received' => $qty,
    //             'total_price' => $tprice,

    //         ]);
        
        

        

    //     $response = array(
    //         'name' => $menus->name,
    //         'reorder' => $menus->reorder_point,
    //         'qty' => $menus->quantity,
    //         'reqQty' => $qty,
    //         'reqQtyCount' => $tprice,
    //     );

    //     echo json_encode($response);
    // }
    // END OF CHANGE QTY BEFORE PENDING TO APPROVE STATUS


    // FROM PENDING TO APPROVED STATUS
    // public function apiTransApproved(Request $request)
    // {
    //     $slug = $request->slug;
    //     $id = $request->id;
    //     // dd('THE ID:' . $id);
    //     $qty = $request->qty;
    //     // dd('THE QUANTITY:' . $qty);

    //     $apiLink = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApiApproval/{$slug}";

    //     $response = Http::get($apiLink);

    //     if ($response->successful()) {
    //         $data = $response->json();
    //         // dd($data);
    //         $ordered = $data['transactionDetails'];
    //         $transaction = $data['transaction'];

    //         $total = 0;

    //         $ordreqs = db::table('branch_requests')->where('cashier' , auth()->id())->get();
    //         foreach ($ordreqs as $order){
    //             $total += $order->total_price;
    //         }

    //         foreach ($ordered as $order) {
                
    //             $warehouse = DB::table('inventories')
    //                 ->where('item_code', $order['item_code'])
    //                 // ->where('department', 1)
    //                 ->first();

                
    //             $newQty = $warehouse->quantity - $order['qty_approved'];

    //             DB::table('inventories')
    //                 ->where('item_code', $order['item_code'])
    //                 // uncomment if needs to limit to a certain department
    //                 // ->where('department', 1)
    //                 ->update([
    //                     'quantity' => $newQty,
    //                 ]);
                
                
    //             DB::table('branch_requests')->insert([
    //                 'tran_id' => $transaction['id'], 
    //                 'inv_id' => $order['inv_id'],
    //                 'item_code' => $order['item_code'],
    //                 'masterlist_id' => $order['masterlist_id'],
    //                 'name' => $order['name'],
    //                 'unit' => $order['unit'],
    //                 'quantity' => $order['quantity'],
    //                 'qty_approved' => $order['qty_approved'],
    //                 'qty_received' => $order['qty_received'],
    //                 'price' => 0,
    //                 'total_price' => $total,
    //                 'current_stock' => $order['current_stock'],
    //                 'reorder_point' => $order['reorder_point'],
    //                 'remarks' => 'APPROVE-LOG',
    //                 'cashier' => $order['cashier'],
    //                 'role' => $order['role'],
    //                 'department' => $order['department'],
    //                 'branch' => $order['branch'],
    //                 'slug' => $slug,
    //                 'created_at' => date('Y-m-d h:i:s'),
    //                 'updated_at' => date('Y-m-d h:i:s')
                    
    //             ]);
    //         }

    //         $id = BranchTransactionRequest::latest()->pluck('id')->first();
    //                 if ($id == null) {
    //                     $id = 1;
    //                 } else {
    //                     $id++;
    //                 }
    //         $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

    //         $branding = "P";
    //         $number = $branding . "-" . date('mdY') . "-" . $nid;

    //         DB::table('branch_transaction_requests')->insert([
    //             'number' => $number,
    //             'total' => $total,
    //             'slug' => $slug,
    //             'status' => 'APPROVE-LOG',
    //             'order_status' => 'APPROVE-LOG',
    //             'order_remarks' => $transaction['order_remarks'],
    //             'cashier' => $transaction['cashier'],
    //             'created_at' => date('Y-m-d h:i:s'),
    //             'updated_at' => date('Y-m-d h:i:s')
    //         ]);

    //         $log = new Log();
    //         $log->trans_id = $id;
    //         $log->classification = 'Branch Transfer';
    //         $log->info = 'Request Approval';
    //         $log->user_id = auth()->id();
    //         $log->event = 'Approval';
    //         $log->save();
            
            
            
    //         $ApproveApi = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApprovalApi/{$slug}";
    //         Http::get($ApproveApi);


    //         $menus = db::table('branch_requests')->where('id' , $id)->first();
    //         $tprice = $menus->price * $qty;
        

    //         DB::table('branch_requests')->where('id' , $id)->update([
    //             'qty_approved' => $qty,
    //             'qty_received' => $qty,
    //             'total_price' => $tprice,

    //         ]);
        
        

        

    //         $response = array(
    //             'name' => $menus->name,
    //             'reorder' => $menus->reorder_point,
    //             'qty' => $menus->quantity,
    //             'reqQty' => $qty,
    //             'reqQtyCount' => $tprice,
    //         );

    //         echo json_encode($response);

            
            
    //         return response()->json(['name' => $warehouse->quantity]);
    //     }

    //     return response()->json(['error' => 'Failed to fetch data from main'], 500);
    // }

    public function apiTransApproved(Request $request)
    {
        $slug = $request->slug;
        $branch_code = $request->branch_code;
        $adjustedQty = $request->adjusted_qty ?? [];
        
        // return response()->json([
        //     'message' => $slug,
        //     'total_price' => $branch_code,
        // ]);

        // Base API2 URL from settings
        $apiBase = rtrim(DB::table('branches')->where('branch_code', $branch_code)->value('api'), '/');

        // Fetch data from API2
        $apiLink = "{$apiBase}/ApiApproval/{$slug}";
        $response = Http::get($apiLink);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch data from API1'], 500);
        }

        $data = $response->json();
        $ordered = $data['transactionDetails'];
        $transaction = $data['transaction'];

        $total = 0;

        foreach ($ordered as $order) {
            $itemCode = $order['item_code'];
            $adjustment = $adjustedQty[$itemCode] ?? null;

            $approvedQty = $adjustment['qty'] ?? $order['qty_approved'];
            $totalPrice = $adjustment['total_price'] ?? ($approvedQty * ($order['price'] ?? 0));

            // Update local inventory //Adjust to menu
/*             $warehouse = DB::table('menus')->where('item_code', $itemCode)->first();
            if ($warehouse) {
                $newQty = $warehouse->quantity - $approvedQty;
                DB::table('menus')->where('item_code', $itemCode)->update([
                    'quantity' => $newQty,
                ]);
            }
 */         //menus 6/18/25
            $menu = DB::table('menus')->where('item_code', $itemCode)->first();

            if ($menu->is_combo == 1) {
                $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                    foreach ($ings as $ing) {
                    $decq =  $approvedQty * $ing->computed_quantity;
                    $curq = DB::table('menus')->where('id', $ing->inventory_id)->first();
                    $newq = $curq->quantity - $decq;

                   DB::table('menus')->where('id', $ing->inventory_id)
                    ->update([
                    'quantity' => $newq
                    ]);

                    /* dd([
                    'approvedQty' => $approvedQty,
                    'ingredientQty' => $ing->computed_quantity,
                    'decq' => $decq,
                    'currentQty' => $curq,
                    'NewQty' => $newq,
                    'curcode' =>$itemCode,
                    'invIngre' => $ing->inventory_id,
                    
                ]); */
                    }

            } else {
                DB::table('menus')
                    ->where('item_code', $itemCode)
                    ->decrement('quantity', $approvedQty);
            }
            DB::table('orders')->where('id', $order['id'])->delete();
            


            // Insert into local branch_requests
            DB::table('branch_requests')->insert([
                'tran_id' => $transaction['id'],
                'inv_id' => $order['inv_id'],
                'item_code' => $itemCode,
                'masterlist_id' => $order['masterlist_id'],
                'name' => $order['name'],
                'unit' => $order['unit'],
                'quantity' => $order['quantity'],
                'qty_approved' => $approvedQty,
                'qty_received' => $approvedQty,
                'price' => $order['price'] ?? 0,
                'total_price' => $totalPrice,
                'current_stock' => $order['current_stock'],
                'reorder_point' => $order['reorder_point'],
                'remarks' => 'APPROVE-LOG',
                'cashier' => $order['cashier'],
                'role' => $order['role'],
                'department' => $order['department'],
                'branch' => $order['branch'],
                'slug' => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $total += $totalPrice;

            // Send received_qty to API2 via GET
            $approveQtyApi = "{$apiBase}/ApproveQtyUpdate?item_code={$itemCode}&received_qty={$approvedQty}&slug={$slug}";
            // dd($receiveQtyApi);
            Http::get($approveQtyApi);
        }

        // Generate new transaction number
        $id = BranchTransactionRequest::latest()->value('id') ?? 0;
        $id++;
        $nid = str_pad($id, 7, '0', STR_PAD_LEFT);
        $number = "P-" . date('mdY') . "-" . $nid;

        // Insert into local branch_transaction_requests
        DB::table('branch_transaction_requests')->insert([
            'number' => $number,
            'total' => $total,
            'slug' => $slug,
            'status' => 'APPROVE-LOG',
            'order_status' => 'APPROVE-LOG',
            'order_remarks' => $transaction['order_remarks'],
            'cashier' => $transaction['cashier'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Logging
        $log = new Log();
        $log->trans_id = $id;
        $log->classification = 'Branch Transfer';
        $log->info = 'Request Approval';
        $log->user_id = auth()->id();
        $log->event = 'Approval';
        $log->save();

        // Final status update to API2
        $approveApi = "{$apiBase}/ApprovalApi/{$slug}";
        Http::get($approveApi);

        return response()->json([
            'message' => 'Approval successful with adjusted quantities.',
            'total_price' => $total,
        ]);
    }


    // END OF FROM PENDING TO APPROVED STATUS

    // FOR APPROVAL STATUS APPROVED TO RECEIVED SUBMIT
    public function apiTransReceived(Request $request)
    {
        $slug = $request->slug;
        $qty = $request->qty;
        $apiLink = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApiApprovalChecker";

        $response = Http::get($apiLink);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch data from main'], 500);
        }

        $data = $response->json();
        // $ordered = $data['transactionDetails'];
        // dd($data['transactionDetails']);
        $apiApproval = $data['branchTransactionRequests']['data'];

        $tran = db::table('branch_transaction_requests')->where('slug' , $slug)->first();

        $ordered = db::table('branch_requests')->where('tran_id' , $tran->id)->get();

        $apiBase = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/');
        
        foreach($ordered as $order){
            $inv = db::table('inventories')
                ->where('item_code' , $order->item_code)
                // ->where('branch', $order->branch)
                ->first();

            $qtyReceived = (float) $order->qty_received;
            $invQty = (float) $inv->quantity;

            $newQty = $invQty + $qtyReceived;
            // ->where('branch', $order->branch)
            DB::table('inventories')->where('item_code' , $order->item_code)->update([
                'quantity' => $newQty,
            ]);
            DB::table('branch_requests')->where('id', $order->id)->update([
                'remarks' => 'RECEIVED',
            ]);

            $receiveQtyApi = "{$apiBase}/ReceiveQtyUpdate?slug={$slug}&item_code={$order->item_code}&received_qty={$qtyReceived}";
            Http::get($receiveQtyApi);
        }

        DB::table('branch_transaction_requests')->where('slug' , $slug)->update([
            'order_status' => 'RECEIVED',
            'status' => 'RECEIVED'
        ]);

        $log = new Log();
        $log->trans_id = $tran->id;
        $log->classification = 'Branch Transfer';
        $log->info = 'Request Receiving';
        $log->user_id = auth()->id();
        $log->event = 'Receiving';
        $log->save();

        foreach ($apiApproval as $item) 
        {
            if ($item['order_status'] === 'APPROVE-LOG') {
                $itemSlug = $item['trSlug'];

                $ApproveApi = "{$apiBase}/ReceivedApi/{$itemSlug}";

                Http::get($ApproveApi);
            }
        }

        $response = array(
            'name' => $slug,
        );
        return response()->json(['message' => 'Transaction successfully received.']);
        // echo json_encode($response);

        
    }

    // END OF FOR APPROVAL STATUS APPROVED TO RECEIVED SUBMIT


    //SHOW ALL
    public function pamahawApiTransfer()
    {
        /* $apiLink = rtrim(DB::table('settings')->whereIn('id', 2)->value('api_link'), '/') . "/ApiTransferIndex";

        $response = Http::get($apiLink);

        if ($response->successful()) {
            $data = $response->json(); */

            $ids = [2, 5, 7];
            // $apiLinks = DB::table('settings')
            //     ->whereIn('id', $ids)
            //     ->pluck('api_link');
            
            $apiLinks = DB::table('branches')
                ->where('is_deleted', 0)
                ->pluck('api');

            $apiApproval = []; // Final combined list

            foreach ($apiLinks as $link) {
                $apiLink = rtrim($link, '/') . "/ApiTransferIndex";
                $response = Http::get($apiLink);

                if ($response->successful()) {
                    $json = $response->json();

                    // Make sure key exists
                    if (isset($json['branchTransactionRequests']['data'])) {
                        $apiApproval = array_merge($apiApproval, $json['branchTransactionRequests']['data']);
                    }
                }
            }
            $page = 1;
            $search = "";
            //leo add
            $branchNames = DB::table('branches')->pluck('name', 'branch_code');

            foreach ($apiApproval as &$item) {
                $code = $item['branch_code'] ?? null;
                $item['branch_name'] = $branchNames[$code] ?? 'Unknown Branch';
            }


            // Count pending requests
            $Prequestcount = collect($apiApproval)
                                ->where('order_status', 'PENDING')
                                ->count();

            
            // foreach ($apiApproval as $item) {
            //     if ($item['order_status'] === 'PENDING') {
            //         $slug = $item['trSlug'];

                    
            //         $ApproveApi = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApprovalApi/{$slug}";

                    
            //         Http::get($ApproveApi);
            //     }
            // }
        
            return view('user.inventory.branch_transfer.transfer-index', compact('apiApproval', 'page', 'search', 'Prequestcount'));
        
    }

    public function pamahawApiEdit($slug,$code)
    {
        // return response()->json([
        //     'message' => $slug,
        //     'total_price' => $code,
        // ]);
        
        $apiLink = rtrim(DB::table('branches')->where('branch_code', $code)->value('api'), '/') . "/ApiTransferEdit/{$slug}";
        
        
        
        //$apiLink = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApiTransferEdit/{$slug}";

        $response = Http::get($apiLink);
        
        //$response = Http::get("http://127.0.0.1:8005/api/ApiTransferEdit/$slug");

        
        if ($response->successful()) {
            $data = $response->json();
            
            // dd($data);
            $dahonMenu = DB::table('menus') //Change to MENUS
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get()
            ->keyBy('item_code'); 

            $apimenus = $data['transactionDetails'];
            $apiTrans = $data['transaction'];
            $reqNumber = $data['reqNumber'];

            foreach ($apimenus as &$apimenu) {
                if (isset($apimenu['item_code']) && isset($dahonMenu[$apimenu['item_code']])) {
                    $apimenu['inv_quantity'] = $dahonMenu[$apimenu['item_code']]->quantity;
                } else {
                    $apimenu['inv_quantity'] = 'Not Available';
                }
            }

            return view('user.inventory.branch_transfer.transfer-edit', compact('apimenus', 'dahonMenu','apiTrans', 'reqNumber'));
        }
        
        return response()->json([
            'message' => $slug,
            'total_price' => $apiLink,
        ]);
        
    }

    // public function ApiReceivedIndex()
    // {
    //     $apiLink = DB::table('api_links')->where('id', 6)->value('link');

    //     $response = Http::get($apiLink);

    //     if ($response->successful()) {
    //         $data = $response->json();
    //         // dd($data);
    //         $apiApproved = $data['orderRequest']['data'];
    //         // dd($data['orderRequest']);
    //         $page = 1;
    //         $search = "";
    
    //         $requestcount = $data['requestcount'];
    //         // dd($data['requestcount']);
    
    //         return view('user.inventory.branch_transfer.transfer-received_index', compact('apiApproved', 'page', 'search', 'requestcount'));
    //     } else {
    //         echo 'here on else';
    //     }
    // }

    // FOR APPROVAL INDEX VIEW
    public function transferApprovedIndex()
    {
        // $apiLink = DB::table('api_links')->where('id', 7)->value('link');

        // $response = Http::get($apiLink);

        // if ($response->successful()) {
        //     $data = $response->json();
        //     // dd($data);
        //     $apiApproved = $data['orderRequest']['data'];
        //     // dd($data['orderRequest']);
        //     $page = 1;
        //     $search = "";
    
        //     $Prequestcount = $data['requestcount'];
        //     // dd($data['requestcount']);
    
        //     return view('user.inventory.branch_transfer.transfer-approved', compact('apiApproved', 'page', 'search', 'Prequestcount'));
        // } else {
        //     echo 'here on else';
        // }

        $apiApproved = DB::table('branch_transaction_requests')
            ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
            ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
            ->where('branch_transaction_requests.order_status','APPROVED')
            ->orderBy('branch_transaction_requests.id', 'asc')->paginate(100);
        $Prequestcount = DB::table('branch_transaction_requests')->where('order_status','APPROVED')->get()->count();
        $page = 1;
        $search = "";
        return view('user.inventory.branch_transfer.transfer-approved', compact('apiApproved', 'Prequestcount', 'page', 'search'));
    }
    // END OF FOR APPROVAL INDEX

    // FOR RECQTY
    public function recqty(Request $request) {

        $id = $request->id;
        $qty = $request->qty;

            DB::table('branch_requests')->where('id' , $id)->update([
                'qty_received' => $qty,
            ]);
        $response = array(
            'name' => $id,
        );

        echo json_encode($response);
    }
    // END OF RECQTY

    // FOR APPROVAL VIEW
    public function pamahawApiReceived($slug)
    {
       
        // $apiLink = DB::table('api_links')->where('id', 8)->value('link');

        // $response = Http::get($apiLink . "/" . $slug); 

        
        // if ($response->successful()) {
        //     $data = $response->json(); 
        //     // Get all local inventory
        //     // dd($data);
        //     $dahonMenu = DB::table('inventories')
        //     ->where('is_deleted', 0)
        //     ->orderBy('name', 'asc')
        //     ->get()
        //     ->keyBy('item_code'); 

        //     $apimenus = $data['transactionDetails'];
        //     $apiTrans = $data['transaction'];
        //     $reqNumber = $data['reqNumber'];
        //     // dd($data['transactionDetails']);

        //     foreach ($apimenus as &$apimenu) {
        //         if (isset($apimenu['item_code']) && isset($dahonMenu[$apimenu['item_code']])) {
        //             $apimenu['inv_quantity'] = $dahonMenu[$apimenu['item_code']]->quantity;
        //         } else {
        //             $apimenu['inv_quantity'] = 'Not Available';
        //         }
        //     }

        //     return view('user.inventory.branch_transfer.transfer-received', compact('apimenus', 'dahonMenu','apiTrans', 'reqNumber'));
        // }

        $ordchecking = DB::table('order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('branch_transaction_requests')->where('slug',$slug)->first();

        $orders = DB::table('branch_requests')->where('tran_id', $trans->id)->get();
        $totalprice = 0;

        $requestTransaction = DB::table('branch_transaction_requests as tr')
            ->leftJoin('branch_requests as or','or.tran_id', '=', 'tr.id')
            ->where('tr.slug', $slug)->get();
        $reqNumber = DB::table('branch_transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('branch_transaction_requests')->where('order_status','APPROVED')->get()->count();


        return view('user.inventory.branch_transfer.transfer-received', compact('requestTransaction','reqNumber','search','page','requestcount'));
    }
    // END OF FOR APPROVAL VIEW

    // public function ApiTransferReceivedReview($slug)
    // {
    //     // Make an API request to the main branch to fetch transaction data by slug
    //     $apiLink = DB::table('api_links')->where('id', 9)->value('link');


    //     $response = Http::get($apiLink . "/" . $slug); 

        
    //     if ($response->successful()) {
    //         $data = $response->json(); // This gives you ['transaction' => ..., 'transactionDetails' => ...]
    //         // Get all local inventory
    //         // dd($data);
    //         $dahonMenu = DB::table('inventories')
    //         ->where('is_deleted', 0)
    //         ->orderBy('name', 'asc')
    //         ->get()
    //         ->keyBy('item_code'); // index by item_code for easier matching

    //         $apimenus = $data['transactionDetails'];
    //         $apiTrans = $data['transaction'];
    //         $reqNumber = $data['reqNumber'];
    //         // dd($data['transactionDetails']);
    //         // dd($data['transaction']);
    //         foreach ($apimenus as &$apimenu) {
    //             if (isset($apimenu['item_code']) && isset($dahonMenu[$apimenu['item_code']])) {
    //                 $apimenu['inv_quantity'] = $dahonMenu[$apimenu['item_code']]->quantity;
    //             } else {
    //                 $apimenu['inv_quantity'] = 'Not Available';
    //             }
    //         }

    //         return view('user.inventory.branch_transfer.transfer-received_view', compact('apimenus', 'dahonMenu','apiTrans', 'reqNumber'));
    //     }
    // }



    // received cross department to department
    public function ApiCrossReceivedIndexView(){
        $apiLink = DB::table('api_links')->where('id', 6)->value('link');

        $orderRequest = collect(); // Initialize empty collection
        $requestcount = 0;

        $response = Http::get($apiLink);

        if ($response->successful()) {
            $data = $response->json();
            $apiApproved = $data['orderRequest']['data'];

            // Check if any item has order_status == 'RECEIVED'
            $hasReceived = collect($apiApproved)->contains(function ($item) {
                return $item['order_status'] === 'RECEIVED';
            });

            if ($hasReceived) {
                $orderRequest = DB::table('branch_transaction_requests')
                    ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
                    ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
                    ->where('branch_transaction_requests.order_status', 'APPROVED')
                    ->orderBy('branch_transaction_requests.id', 'asc')
                    ->paginate(100);

                $requestcount = DB::table('branch_transaction_requests')
                    ->where('order_status','APPROVED')
                    ->count();
            }
        }

        $page = 1;
        $search = "";

        return view('user.inventory.branch_transfer.transfer-cross-received_index', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    // end of received cross department to department

    // received cross view
    public function ApiCrossReceivedView($slug)
    {
        // Clear previous approval if any
        $ordchecking = DB::table('order_approvals')->where('cashier', auth()->id())->first();
        if ($ordchecking != null) {
            DB::table('order_approvals')->where('cashier', auth()->id())->delete();
        }

        // Fetch transaction using slug
        $trans = DB::table('branch_transaction_requests')->where('slug', $slug)->first();

        // Fetch request details
        $requestTransaction = DB::table('branch_requests')->where('tran_id', $trans->id)->get();

        // Get all local inventory (keyed by item_code)
        $dahonMenu = DB::table('inventories')
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get()
            ->keyBy('item_code');

        // Add inv_quantity to each order
        foreach ($requestTransaction as &$order) {
            if (isset($order->item_code) && isset($dahonMenu[$order->item_code])) {
                $order->inv_quantity = $dahonMenu[$order->item_code]->quantity;
            } else {
                $order->inv_quantity = 'Not Available';
            }
        }

        // Fetch transaction for displaying request number, etc.
        $reqNumber = $trans;
        $search = '';
        $page = 1;
        $requestcount = DB::table('branch_transaction_requests')->where('order_status', 'RECEIVED')->count();

        return view('user.inventory.branch_transfer.transfer-cross-received_view', compact(
            'requestTransaction',
            'reqNumber',
            'search',
            'page',
            'requestcount'
        ));
    }
    // end of received cross view

    // api cross received
    public function ApiCrossReceived(Request $request) {
        $slug = $request->slug;

        // Fetch from API using link ID 3
        $apiLink = DB::table('api_links')->where('id', 3)->value('link');
        $response = Http::get($apiLink . "/" . $slug); 

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch data from main'], 500);
        }

        $data = $response->json();
        $ordered = $data['transactionDetails']; // items from API
        $transaction = $data['transaction'];

        // Local transaction for matching
        $tran = DB::table('branch_transaction_requests')->where('slug', $slug)->first();
        if (!$tran) {
            return response()->json(['error' => 'Transaction not found locally.'], 404);
        }

        foreach ($ordered as $order) {
            // Use API-provided masterlist_id and item_code to find matching inventory
            $inv = DB::table('inventories')
                ->where('item_code', $order['item_code'])
                // ->where('branch', $tran->branch_id ?? $order['branch'] ?? 1) 
                ->first();

            if (!$inv) continue;

            // Add qty_received from API to inventory
            $newQty = $inv->quantity + $order['qty_received'];
            DB::table('inventories')
                ->where('item_code', $order['item_code'])
                // ->where('branch', $inv->branch)
                ->update(['quantity' => $newQty]);

            // Update branch_requests remarks
            DB::table('branch_requests')
                ->where('tran_id', $tran->id)
                ->where('item_code', $order['item_code'])
                ->update(['remarks' => 'RECEIVED']);
        }

        // Update transaction status
        DB::table('branch_transaction_requests')
            ->where('slug', $slug)
            ->update([
                'order_status' => 'RECEIVED',
                'status' => 'RECEIVED'
            ]);

        return response()->json(['name' => $slug]);
    }

    // end of api cross received


    // received viewing only
    public function ApiReceivedIndex(){
        $orderRequest = DB::table('branch_transaction_requests')
        ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
        ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
        ->where('branch_transaction_requests.order_status', 'RECEIVED')

        ->orderBy('branch_transaction_requests.id', 'asc')->paginate(100);
        $requestcount = DB::table('branch_transaction_requests')->where('order_status','RECEIVED')->get()->count();
        $page = 1;
        $search = "";
        return view('user.inventory.branch_transfer.transfer-received_index', compact('orderRequest', 'requestcount', 'page', 'search'));
    }
    // end of received department to department

    // received view
    public function ApiReceivedIndexView($slug){

        $ordchecking = DB::table('order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('branch_transaction_requests')->where('slug',$slug)->first();

        $orders = DB::table('branch_requests')->where('tran_id', $trans->id)->get();
        $totalprice = 0;
        /* foreach ($orders as $order) {
            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            $totalprice = $menu->price * $order->quantity;

            DB::table('order_approvals')->insert([
                'menu_id' => $order->menu_id,
                'name' => $menu->name,
                'quantity' => $order->quantity,
                'price' => $menu->price,
                'total_price' => $totalprice,
                'current_stock' => $menu->current_quantity,
                'type' => 0,
                'cashier' => auth()->id(),
                'slug' => $order->slug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);
        } */



        $requestTransaction = DB::table('branch_transaction_requests as tr')
        ->leftJoin('ordered_requests as or','or.tran_id', '=', 'tr.id')
        ->where('tr.slug', $slug)->get();
        $reqNumber = DB::table('branch_transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('branch_transaction_requests')->where('order_status','RECEIVED')->get()->count();


        return view('user.inventory.branch_transfer.transfer-received_view', compact('requestTransaction','reqNumber','search','page','requestcount'));
    }
    // end of received view

    // end of received viewing only

    

    // END OF DAHON API

}
    
