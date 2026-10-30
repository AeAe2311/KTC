<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class InventoryMovementController extends Controller
{
    //ken updated 7-15-2026 idex 
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get user's store_code
        $userStoreCode = $user->store_code;

        // Get all distinct movement types (keep all including waste for dropdown)
        $movementTypes = DB::table('inventory_movement as im')
            ->select('im.type')
            ->where('im.store_code', $userStoreCode)
            ->distinct()
            ->orderBy('im.type', 'asc')
            ->pluck('type')
            ->toArray();

        // Get all items for the dropdown (keep all including those with only waste)
        $allItems = DB::table('inventory_movement as im')
            ->leftJoin('menus as m', 'im.menu_id', '=', 'm.id')
            ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
            ->where('im.store_code', $userStoreCode)
            ->select(
                'm.item_code',
                'm.name as item_name',
                'b.name as brand_name',
                'm.id as menu_id'
            )
            ->distinct()
            ->orderBy('m.item_code')
            ->get();

        $baseQuery = DB::table('inventory_movement as im')
            ->leftJoin('menus as m', 'im.menu_id', '=', 'm.id')
            ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
            ->where('im.store_code', $userStoreCode)
            ->selectRaw(
                "im.id,
                im.type,
                im.ordered_id,
                im.void_id,
                im.menu_id,
                im.trans_id,
                im.trans_number,
                im.reference,
                im.detail,
                im.previous_qty,
                im.qty_in,
                im.qty_out,
                im.remaining,
                im.sold_price,
                im.cost,
                im.created_at,
                im.updated_at,
                im.branch_code,
                im.store_code,
                im.is_uuid,
                im.is_sync,
                m.name as item_name,
                m.item_code,
                CASE 
                    WHEN LOWER(im.type) = 'waste' THEN im.unit 
                    ELSE m.unit 
                END as unit,
                b.name as brand_name,
                m.price as menu_price,
                im.previous_qty as quantity_before,
                im.remaining as quantity_after,
                im.sold_price as amount,
                im.qty_in as quantity_in,
                im.qty_out as quantity_out,
                im.detail as remarks,
                CASE
                    WHEN LOWER(im.detail) LIKE '%transform%' THEN
                        CASE WHEN im.type = 'INCOMING' THEN 'Transform In' ELSE 'Transform Out' END
                    WHEN LOWER(im.detail) LIKE '%waste%' OR LOWER(im.detail) LIKE '%scrap%' THEN 'Waste'
                    WHEN LOWER(im.detail) LIKE '%purchase%' OR LOWER(im.detail) LIKE '%receiving%' THEN 'Purchase'
                    WHEN LOWER(im.detail) LIKE '%sale%' OR LOWER(im.detail) LIKE '%sold%' OR LOWER(im.detail) LIKE '%invoice%' THEN 'Sold'
                    WHEN LOWER(im.detail) LIKE '%adjust%' THEN 'Adjustment'
                    WHEN LOWER(im.detail) LIKE '%transfer%' THEN 'Transfer'
                    ELSE CASE WHEN im.type = 'INCOMING' THEN 'Stock In' ELSE 'Stock Out' END
                END as movement_type_label"
            );

        $query = clone $baseQuery;

        // Filter by item code
        if ($request->filled('item_code')) {
            $itemCode = $request->item_code;
            
            if (str_starts_with($itemCode, 'NO_CODE_')) {
                $menuId = str_replace('NO_CODE_', '', $itemCode);
                $query->where('im.menu_id', $menuId)
                    ->where(function($q) {
                        $q->whereNull('m.item_code')
                            ->orWhere('m.item_code', '');
                    });            
            } else {
                $query->where('m.item_code', $itemCode);
            }
        }

        // Enhanced Search filter - EXCLUDE WASTE RECORDS ONLY (not items)
        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerms = explode(' ', $search);
            
            Log::info('Inventory Movement Search - Term: ' . $search);
            Log::info('Search Terms: ' . json_encode($searchTerms));
            
            $query->where(function ($q) use ($search, $searchTerms) {
                $q->where('im.reference', 'like', '%' . $search . '%')
                    ->orWhere('im.trans_number', 'like', '%' . $search . '%')
                    ->orWhere('im.detail', 'like', '%' . $search . '%')
                    ->orWhere('im.type', 'like', '%' . $search . '%')
                    ->orWhere('im.id', 'like', '%' . $search . '%')
                    ->orWhere('im.qty_in', 'like', '%' . $search . '%')
                    ->orWhere('im.qty_out', 'like', '%' . $search . '%')
                    ->orWhere('im.previous_qty', 'like', '%' . $search . '%')
                    ->orWhere('im.remaining', 'like', '%' . $search . '%')
                    ->orWhere('im.sold_price', 'like', '%' . $search . '%')
                    ->orWhere('im.cost', 'like', '%' . $search . '%')
                    ->orWhere('m.name', 'like', '%' . $search . '%')
                    ->orWhere('m.item_code', 'like', '%' . $search . '%')
                    ->orWhere('m.unit', 'like', '%' . $search . '%')
                    ->orWhere('b.name', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("CONCAT(COALESCE(b.name, ''), ' ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("CONCAT(COALESCE(m.item_code, ''), ' ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("CONCAT(COALESCE(m.item_code, ''), ' - ', COALESCE(b.name, ''), ' - ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%');
                
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 2) {
                        $q->orWhere('m.name', 'like', '%' . $term . '%')
                        ->orWhere('b.name', 'like', '%' . $term . '%')
                        ->orWhere('m.item_code', 'like', '%' . $term . '%')
                        ->orWhere('im.reference', 'like', '%' . $term . '%')
                        ->orWhere('im.trans_number', 'like', '%' . $term . '%')
                        ->orWhere('im.detail', 'like', '%' . $term . '%');
                    }
                }
            })
            ->where('im.type', '!=', 'waste');
        }

        // Filter by movement type
        if ($request->filled('movement_type')) {
            $query->where('im.type', $request->movement_type);
        }

        // Filter by date range - ALWAYS apply date filters
        // Use default date range if not provided in request
        $dateFrom = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : Carbon::today()->startOfDay(); // Default to today's start
        
        $dateTo = $request->filled('date_to') 
            ? Carbon::parse($request->date_to)->endOfDay() 
            : Carbon::today()->endOfDay(); // Default to today's end

        // Always apply date range filter (no fallback)
        $query->where('im.created_at', '>=', $dateFrom)
            ->where('im.created_at', '<=', $dateTo);

        // LINE 177: START OF SORTING DIRECTION added by ken 10-15-2026
        $sortDirection = $request->input('sort_direction', 'desc');
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

        // Get paginated results
        $transactions = $query
            ->orderBy('im.created_at', $sortDirection)
            ->paginate(100)
            ->withQueryString();
        // LINE 186: END OF SORTING DIRECTION added by ken 10-15-2026
        // Log the SQL query for debugging
        if ($request->filled('search')) {
            Log::info('Search SQL: ' . $query->toSql());
            Log::info('Search Bindings: ' . json_encode($query->getBindings()));
            Log::info('Results count: ' . $transactions->total());
        }

        // Check if no results found and add a helpful message
        $noResultsMessage = null;
        if ($request->has('search') && $transactions->total() == 0) {
            $noResultsMessage = 'No results found for "' . $request->search . '". Try different keywords or adjust your filters.';
        } elseif ($request->has('item_code') && $transactions->total() == 0) {
            $noResultsMessage = 'No results found for the selected item. Try a different item or adjust your filters.';
        } elseif ($transactions->total() == 0 && ($request->has('date_from') || $request->has('date_to'))) {
            $noResultsMessage = 'No results found in the selected date range. Try adjusting your date filters.';
        }

        return view('user.inventory.movement.index', compact('transactions', 'movementTypes', 'allItems', 'noResultsMessage'));
    }
    //ken end of uppdated
    public function getPopupData($id)
    {
        try {
            $user = Auth::user();
            
            // Get user's branch_code and store_code for security check
            //$userBranchCode = $user->branch_code;
            $userStoreCode = $user->store_code;
            
            Log::info('Fetching popup data for transaction ID: ' . $id);
            
            // Get the transaction with related data - WITH BRANCH/STORE SECURITY CHECK
            $transaction = DB::table('inventory_movement as im')
                ->leftJoin('menus as m', 'im.menu_id', '=', 'm.id')
                ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
                ->select(
                    'im.*',
                    'm.name as item_name',
                    'm.item_code',
                    'm.wholesale_price',
                    'm.slab_price',
                    'm.retail_price',
                    DB::raw("CASE WHEN LOWER(im.type) = 'waste' THEN im.unit ELSE m.unit END as unit"),
                    'b.name as brand_name'
                )
                ->where('im.id', $id)
                //->where('im.branch_code', $userBranchCode) // SECURITY: Must match user's branch
                ->where('im.store_code', $userStoreCode)   // SECURITY: Must match user's store
                ->first();

            if (!$transaction) {
                Log::warning('Transaction not found or access denied for ID: ' . $id);
                return response()->json(['error' => 'Transaction not found or access denied'], 404);
            }

            Log::info('Transaction found: ' . $transaction->reference . ' | Type: ' . $transaction->type);

            // Get all items for this transaction with brand information - WITH BRANCH/STORE SECURITY
            $items = DB::table('inventory_movement as im')
                ->leftJoin('menus as m', 'im.menu_id', '=', 'm.id')
                ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
                ->select(
                    'im.*',
                    'm.name as item_name',
                    'm.item_code',
                    'm.wholesale_price',
                    'm.slab_price',
                    'm.retail_price',
                    DB::raw("CASE WHEN LOWER(im.type) = 'waste' THEN im.unit ELSE m.unit END as unit"),
                    'b.name as brand_name'
                )
                //->where('im.branch_code', $userBranchCode) // SECURITY: Branch filter
                ->where('im.store_code', $userStoreCode)   // SECURITY: Store filter
                ->where(function($query) use ($transaction) {
                    $query->where('im.reference', $transaction->reference)
                        ->orWhere('im.trans_number', $transaction->trans_number);
                })
                ->get();

            Log::info('Found ' . count($items) . ' items for this transaction');

            // Check if this is a TRANSFORMATION type
            $isTransformation = (strtoupper($transaction->type) === 'TRANSFORMATION') || 
                               (stripos($transaction->detail ?? '', 'transform') !== false);
            
            // Determine the display type for the popup
            $displayType = $transaction->type ?? 'N/A';
            if ($isTransformation && strtoupper($displayType) !== 'TRANSFORMATION') {
                $displayType = 'TRANSFORMATION';
            }

            // Determine sales type
            $salesType = 'N/A';
            if ($transaction->sold_price) {
                $soldPrice = (float)$transaction->sold_price;
                
                $firstItem = $items->first();
                if ($firstItem) {
                    $wholesale = (float)($firstItem->wholesale_price ?? 0);
                    $slab = (float)($firstItem->slab_price ?? 0);
                    $retail = (float)($firstItem->retail_price ?? 0);

                    if ($wholesale > 0 && abs($soldPrice - $wholesale) < 0.01) {
                        $salesType = 'Wholesale';
                    } elseif ($slab > 0 && abs($soldPrice - $slab) < 0.01) {
                        $salesType = 'Slab';
                    } elseif ($retail > 0 && abs($soldPrice - $retail) < 0.01) {
                        $salesType = 'Retail';
                    }
                }
            }

            // Prepare items data with brand
            $itemsData = [];
            foreach ($items as $item) {
                $quantityIn = (float)($item->qty_in ?? 0);
                $quantityOut = (float)($item->qty_out ?? 0);
                $quantity = $quantityOut > 0 ? $quantityOut : $quantityIn;
                
                $soldPrice = (float)($item->sold_price ?? 0);
                $cost = (float)($item->cost ?? 0);
                
                $wholesalePrice = (float)($item->wholesale_price ?? 0);
                $slabPrice = (float)($item->slab_price ?? 0);
                $retailPrice = (float)($item->retail_price ?? 0);
                
                $itemDescription = $item->item_name ?? 'N/A';
                if (!empty($item->brand_name)) {
                    $itemDescription = $item->brand_name . ' - ' . $itemDescription;
                }
                
                // Determine item type for TRANSFORMATION
                $itemType = $item->type ?? 'N/A';
                if ($isTransformation) {
                    if (stripos($item->detail ?? '', 'waste') !== false || stripos($item->detail ?? '', 'scrap') !== false) {
                        $itemType = 'WASTE';
                    }
                }
                
                $itemsData[] = [
                    'item_code' => $item->item_code ?? 'N/A',
                    'item_description' => $itemDescription,
                    'quantity' => $quantity,
                    'quantity_in' => $quantityIn,
                    'quantity_out' => $quantityOut,
                    'qty_in' => $quantityIn,
                    'qty_out' => $quantityOut,
                    'unit' => $item->unit ?? 'pcs',
                    'sold_price' => $soldPrice,
                    'cost' => $cost,
                    'standard_cost' => $cost,
                    'unit_cost' => $cost,
                    'total' => $quantity * $soldPrice,
                    'brand_name' => $item->brand_name ?? 'N/A',
                    'item_name' => $item->item_name ?? 'N/A',
                    'total_cost' => $cost,
                    // NEW FIELDS for TRANSFORMATION popup
                    'type' => $itemType,
                    'detail' => $item->detail ?? '',
                    'remarks' => $item->detail ?? '',
                    'previous_qty' => $item->previous_qty ?? 0,
                    'remaining' => $item->remaining ?? 0,
                    'quantity_before' => $item->previous_qty ?? 0,
                    'quantity_after' => $item->remaining ?? 0,
                ];
            }
            
            // Prepare response data
            $response = [
                'id' => $transaction->id,
                'type' => $displayType,
                'reference' => $transaction->reference ?? 'N/A',
                'detail' => $transaction->detail ?? 'N/A',
                'charge_to' => $transaction->detail ?? 'N/A',
                'charge_branch' => $transaction->detail ?? 'N/A',
                'sales_type' => $salesType,
                'dispatch_date' => $transaction->created_at ? Carbon::parse($transaction->created_at)->format('Y-m-d H:i') : 'N/A',
                'items' => $itemsData,
                //LINE 365: 20260528001 JET START lines 365 - 370
                'user_name' => $locationTransferInfo['user_name'] ?? $this->getCurrentInventoryMovementUserName($user),
                'prepared_by' => $locationTransferInfo['prepared_by'] ?? '',
                'checked_by' => $locationTransferInfo['checked_by'] ?? '',
                'received_by' => $locationTransferInfo['received_by'] ?? '',
                'approved_by' => $locationTransferInfo['approved_by'] ?? '', // LINE 370: 20260528001 JET END
                // NEW FIELDS for popup
                'shipping_company' => '',
                'delivered_to' => '',
                'shipping_cost' => 0,
            ];

            Log::info('Popup data prepared successfully for transaction ID: ' . $id . ' | Display Type: ' . $displayType);
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Popup data error for ID ' . $id . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to load popup data: ' . $e->getMessage()
            ], 500);
        }
    }
    // START LINE 388: START OF LOCATION TRANSFER INFO added by ken FROM JET DEV TASK 
    //20260528001 JET START lines 387 - 429
    private function getLocationTransferMovementInfo($movement): array
    {
        $empty = [
            'prepared_by' => '',
            'checked_by' => '',
            'received_by' => '',
            'approved_by' => '',
            'user_name' => '',
        ];

        if (!str_contains(strtoupper((string) ($movement->type ?? '')), 'LOCATION TRANSFER')) {
            return $empty;
        }

        $reference = $movement->reference ?: $movement->trans_number;
        if (!$reference) {
            return $empty;
        }

        $transfer = DB::table('branch_transaction_requests')
            ->leftJoin('users', 'branch_transaction_requests.cashier', '=', 'users.id')
            ->select(
                'branch_transaction_requests.*',
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT_WS(' ', users.firstname, users.middlename, users.lastname)), ''), users.username, 'Unknown') as prepared_name")
            )
            ->where('branch_transaction_requests.number', $reference)
            ->first();

        if (!$transfer) {
            return $empty;
        }

        $metadata = $this->decodeLocationTransferReceiveMetadata($transfer->order_remarks ?? '');

        return [
            'prepared_by' => $metadata['prepared_by'] ?: ($transfer->prepared_name ?? ''),
            'checked_by' => $metadata['checked_by'] ?? '',
            'received_by' => $metadata['received_by'] ?? '',
            'approved_by' => $metadata['checked_by'] ?? '',
            'user_name' => $metadata['received_by'] ?: ($metadata['checked_by'] ?: ($transfer->prepared_name ?? '')),
        ];
    } //20260528001 JET END

    //20260528001 JET START lines 431 - 441
    private function getCurrentInventoryMovementUserName($user): string
    {
        $name = trim(implode(' ', array_filter([
            $user->firstname ?? null,
            $user->middlename ?? null,
            $user->lastname ?? null,
        ])));

        return $name ?: ($user->username ?? $user->email ?? 'N/A');
    } //20260528001 JET END

    //20260528001 JET START lines 443 - 464
    private function decodeLocationTransferReceiveMetadata(?string $remarks): array
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

        return $metadata;
    } //20260528001 JET END
    // END LINE 391: END OF LOCATION TRANSFER INFO added by ken FROM JET DEV TASK

    //ken START updated 8-18-2026 print pdfreport
    public function printReport(Request $request)
    {
        try {
            $user = Auth::user();
            $userStoreCode = $user->store_code;
            $printedBy = $user->firstname . ' ' . $user->lastname;
            
            $storeName = 'N/A';
            $storeAddress = 'N/A';
            $settings = DB::table('settings')
                ->where('store_code', $userStoreCode)
                ->first();
            
            if ($settings) {
                $storeName = $settings->name ?? 'N/A';
                $storeAddress = $settings->address ?? 'N/A';
            }

            // Build the query with filters
            $query = DB::table('inventory_movement as im')
                ->leftJoin('menus as m', 'im.menu_id', '=', 'm.id')
                ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
                ->leftJoin('menu_categories as mc', 'm.category_id', '=', 'mc.id')
                /* ->where('m.is_deleted', '!=', 1) */
                ->where('m.store_code', $userStoreCode)
                ->where('im.store_code', $userStoreCode)
                ->where('im.type', '!=', 'VOID')
                ->where('im.type', '!=', 'WASTE')                 
                ->where('im.type', '=', 'DELIVERY NOTE')
                // LINE 415: ken added 9-3-2026 to exclude VOID references from the report
                ->whereNotIn('im.trans_id', function($subQuery) use ($userStoreCode) {  // Line 416: ken updated the wrong reference to trans_id 9-10-2026
                    $subQuery->select('trans_id') // Line 417: ken updated the wrong reference to trans_id 9-10-2026
                        ->from('inventory_movement')
                        ->where('store_code', $userStoreCode)
                        ->where('type', 'VOID');
                })    
                // LINE 417: ken end of added 9-3-2026 to exclude VOID references from the report           
                ->selectRaw(
                    "im.id,
                    im.type,
                    im.reference,
                    im.detail,
                    im.previous_qty as quantity_before,
                    im.qty_in as quantity_in,
                    im.qty_out as quantity_out,
                    im.remaining,
                    im.sold_price as amount,
                    im.cost,
                    im.created_at,
                    m.name as item_name,
                    m.item_code,
                    m.brand_code,
                    b.name as brand_name,
                    mc.name as category_name,
                    mc.id as category_id"
                );

            // Apply filters
            if ($request->filled('item_code')) {
                $itemCode = $request->item_code;
                if (str_starts_with($itemCode, 'NO_CODE_')) {
                    $menuId = str_replace('NO_CODE_', '', $itemCode);
                    $query->where('im.menu_id', $menuId)
                        ->where(function($q) {
                            $q->whereNull('m.item_code')
                                ->orWhere('m.item_code', '');
                        });
                } else {
                    $query->where('m.item_code', $itemCode);
                }
            }

            // Filter by brand
            if ($request->filled('brand_id')) {
                $query->where('m.brand_id', $request->brand_id);
            }

            // Alternative: Filter by brand name from search
            if ($request->filled('brand_name')) {
                $query->where('b.name', 'like', '%' . $request->brand_name . '%');
            }

            // Enhanced Search filter - include brand search
            if ($request->filled('search')) {
                $search = $request->search;
                $searchTerms = explode(' ', $search);
                
                $query->where(function ($q) use ($search, $searchTerms) {
                    $q->where('im.reference', 'like', '%' . $search . '%')
                        ->orWhere('im.trans_number', 'like', '%' . $search . '%')
                        ->orWhere('im.detail', 'like', '%' . $search . '%')
                        ->orWhere('im.type', 'like', '%' . $search . '%')
                        ->orWhere('m.name', 'like', '%' . $search . '%')
                        ->orWhere('m.item_code', 'like', '%' . $search . '%')
                        ->orWhere('m.brand_code', 'like', '%' . $search . '%')
                        ->orWhere('b.name', 'like', '%' . $search . '%')
                        ->orWhere('mc.name', 'like', '%' . $search . '%')
                        ->orWhere(DB::raw("CONCAT(COALESCE(b.name, ''), ' ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%');
                    
                    foreach ($searchTerms as $term) {
                        if (strlen($term) > 2) {
                            $q->orWhere('b.name', 'like', '%' . $term . '%')
                                ->orWhere('m.brand_code', 'like', '%' . $term . '%')
                                ->orWhere('m.name', 'like', '%' . $term . '%')
                                ->orWhere('m.item_code', 'like', '%' . $term . '%');
                        }
                    }
                });
            }

            if ($request->filled('movement_type')) {
                $query->where('im.type', $request->movement_type);
            }

            if ($request->filled('date_from')) {
                $query->where('im.created_at', '>=', Carbon::parse($request->date_from));
            }

            if ($request->filled('date_to')) {
                $query->where('im.created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
            }

            $transactions = $query
                ->orderBy('mc.name')  // ken commented out the sorting 
                // ->orderBy('b.name')   // ken commented out the sorting 
                // ->orderBy('m.name')   // ken commented out the sorting
                ->orderBy('m.brand_code')  // ken commented out the sorting
                ->get();

            // Group transactions by category and by brand_name + item_name combination
            $groupedTransactions = [];
            $grandTotalQty = 0;
            $grandTotalSales = 0;
            $grandTotalCost = 0;
            $grandTotalProfit = 0;

            foreach ($transactions as $transaction) {
                $categoryName = trim($transaction->category_name ?? 'Uncategorized');
                if ($categoryName === '') {
                    $categoryName = 'Uncategorized';
                }

                $itemName = trim($transaction->item_name ?? 'N/A');
                if ($itemName === '') {
                    $itemName = 'N/A';
                }

                $brandName = trim($transaction->brand_name ?? 'N/A');
                if ($brandName === '') {
                    $brandName = 'N/A';
                }

                $brandCode = trim($transaction->brand_code ?? '');
                if ($brandCode === '') {
                    $brandCode = 'N/A';
                }
                
                // Create display name as "BRAND_NAME - ITEM_NAME"
                $displayName = strtoupper($brandName) . ' - ' . strtoupper($itemName);
                
                // Create a unique key using brand_name + item_name combination
                $itemKey = strtoupper($brandName) . '|' . strtoupper($itemName);

                $qtyOut = (float)($transaction->quantity_out ?? 0);
                $amount = (float)($transaction->amount ?? 0);
                $cost = (float)($transaction->cost ?? 0);
                
                $sales = $amount * $qtyOut;
                $totalCost = $cost * $qtyOut;
                $profit = $sales - $totalCost;
                
                if (!isset($groupedTransactions[$categoryName])) {
                    $groupedTransactions[$categoryName] = [
                        'category_id' => $transaction->category_id ?? 0,
                        'category_name' => $categoryName,
                        'items' => [],
                        'total_qty' => 0,
                        'total_sales' => 0,
                        'total_cost' => 0,
                        'total_profit' => 0
                    ];
                }

                // Group by brand_name + item_name combination
                if (!isset($groupedTransactions[$categoryName]['items'][$itemKey])) {
                    $groupedTransactions[$categoryName]['items'][$itemKey] = [
                        'display_name' => $displayName,
                        'item_name' => strtoupper($itemName),
                        'brand_name' => strtoupper($brandName),
                        'brand_code' => strtoupper($brandCode),
                        'total_qty' => 0,
                        'total_sales' => 0,
                        'total_cost' => 0,
                        'total_profit' => 0,
                        'transaction_count' => 0
                    ];
                }

                $groupedTransactions[$categoryName]['items'][$itemKey]['total_qty'] += $qtyOut;
                $groupedTransactions[$categoryName]['items'][$itemKey]['total_sales'] += $sales;
                $groupedTransactions[$categoryName]['items'][$itemKey]['total_cost'] += $totalCost;
                $groupedTransactions[$categoryName]['items'][$itemKey]['total_profit'] += $profit;
                $groupedTransactions[$categoryName]['items'][$itemKey]['transaction_count'] += 1;

                $groupedTransactions[$categoryName]['total_qty'] += $qtyOut;
                $groupedTransactions[$categoryName]['total_sales'] += $sales;
                $groupedTransactions[$categoryName]['total_cost'] += $totalCost;
                $groupedTransactions[$categoryName]['total_profit'] += $profit;
                
                $grandTotalQty += $qtyOut;
                $grandTotalSales += $sales;
                $grandTotalCost += $totalCost;
                $grandTotalProfit += $profit;
            }

            // Sort items within each category by total sales (high to low)
            // foreach ($groupedTransactions as $categoryName => &$categoryData) {
            //     usort($categoryData['items'], function($a, $b) {
            //         return $b['total_sales'] <=> $a['total_sales'];
            //     });
            // }

            // Sort categories by total sales (high to low)
            // uasort($groupedTransactions, function($a, $b) {
            //     return $b['total_sales'] <=> $a['total_sales'];
            // });

            // Prepare filter info for display
            $category = 'All';
            if ($request->filled('item_code') && $request->item_code !== 'All') {
                $category = 'Item: ' . $request->item_code;
            }
            if ($request->filled('brand_id')) {
                $brandName = DB::table('brands')->where('id', $request->brand_id)->value('name');
                $category .= ' | Brand: ' . ($brandName ?? $request->brand_id);
            }
            if ($request->filled('brand_name')) {
                $category .= ' | Brand: ' . $request->brand_name;
            }
            if ($request->filled('search')) {
                $category .= ' | Search: ' . $request->search;
            }
            if ($request->filled('movement_type')) {
                $category .= ' | Type: ' . $request->movement_type;
            }

            $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->format('Y-m-d H:i') : 'Start';
            $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->format('Y-m-d H:i') : 'End';

            $grandTotalCount = array_sum(array_map(fn($categoryData) => count($categoryData['items']), $groupedTransactions));

            return view('user.inventory.movement.print', compact(
                'groupedTransactions',
                'category',
                'dateFrom',
                'dateTo',
                'grandTotalQty',
                'grandTotalSales',
                'grandTotalCost',
                'grandTotalProfit',
                'printedBy',
                'storeName',
                'storeAddress',
                'grandTotalCount'
            ));

        } catch (\Exception $e) {
            Log::error('Print report error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate print report: ' . $e->getMessage());
        }
    }
    //ken END updated 8-18-2026 print pdf report
    //ken START updated 8-18-2026 EXPORT(CSV OR EXCEL) report
    public function exportReport(Request $request)
    {
        try {
            $user = Auth::user();
            $userStoreCode = $user->store_code;
            $printedBy = $user->firstname . ' ' . $user->lastname;
            
            $storeName = 'N/A';
            $storeAddress = 'N/A';
            $settings = DB::table('settings')
                ->where('store_code', $userStoreCode)
                ->first();
            
            if ($settings) {
                $storeName = $settings->name ?? 'N/A';
                $storeAddress = $settings->address ?? 'N/A';
            }
            // Build the query with filters
            $query = DB::table('inventory_movement as im')
                ->leftJoin('menus as m', 'im.menu_id', '=', 'm.id')
                ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
                ->leftJoin('menu_categories as mc', 'm.category_id', '=', 'mc.id')
                ->where('im.store_code', $userStoreCode)
                /* ->where('m.is_deleted', '!=', 1) */
                ->where('im.type', '!=', 'VOID')
                ->where('im.type', '!=', 'WASTE')                  
                ->where('im.type', '=', 'DELIVERY NOTE')
                // LINE686: ken added 9-3-2026 to exclude VOID references from the report
                ->whereNotIn('im.trans_id', function($subQuery) use ($userStoreCode) { // Line 687: ken updated the wrong reference to trans_id 9-10-2026
                    $subQuery->select('trans_id') // Line 688: ken updated the wrong reference to trans_id 9-10-2026
                        ->from('inventory_movement')
                        ->where('store_code', $userStoreCode)
                        ->where('type', 'VOID');
                })    
                // LINE693: ken end of added 9-3-2026 to exclude VOID references from the report                  
                ->selectRaw(
                    "im.id,
                    im.type,
                    im.reference,
                    im.detail,
                    im.previous_qty as quantity_before,
                    im.qty_in as quantity_in,
                    im.qty_out as quantity_out,
                    im.remaining,
                    im.sold_price as amount,
                    im.cost,
                    im.created_at,
                    m.name as item_name,
                    m.item_code,
                    m.brand_code,
                    b.name as brand_name,
                    mc.name as category_name,
                    mc.id as category_id"
                );
            // Apply filters
            if ($request->filled('item_code')) {
                $itemCode = $request->item_code;
                if (str_starts_with($itemCode, 'NO_CODE_')) {
                    $menuId = str_replace('NO_CODE_', '', $itemCode);
                    $query->where('im.menu_id', $menuId)
                        ->where(function($q) {
                            $q->whereNull('m.item_code')
                                ->orWhere('m.item_code', '');
                        });
                } else {
                    $query->where('m.item_code', $itemCode);
                }
            }
            // Filter by brand
            if ($request->filled('brand_id')) {
                $query->where('m.brand_id', $request->brand_id);
            }
            // Alternative: Filter by brand name from search
            if ($request->filled('brand_name')) {
                $query->where('b.name', 'like', '%' . $request->brand_name . '%');
            }
            // Enhanced Search filter - include brand search
            if ($request->filled('search')) {
                $search = $request->search;
                $searchTerms = explode(' ', $search);
                
                $query->where(function ($q) use ($search, $searchTerms) {
                    $q->where('im.reference', 'like', '%' . $search . '%')
                        ->orWhere('im.trans_number', 'like', '%' . $search . '%')
                        ->orWhere('im.detail', 'like', '%' . $search . '%')
                        ->orWhere('im.type', 'like', '%' . $search . '%')
                        ->orWhere('m.name', 'like', '%' . $search . '%')
                        ->orWhere('m.item_code', 'like', '%' . $search . '%')
                        ->orWhere('m.brand_code', 'like', '%' . $search . '%')
                        ->orWhere('b.name', 'like', '%' . $search . '%')
                        ->orWhere('mc.name', 'like', '%' . $search . '%')
                        ->orWhere(DB::raw("CONCAT(COALESCE(b.name, ''), ' ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%');
                    
                    foreach ($searchTerms as $term) {
                        if (strlen($term) > 2) {
                            $q->orWhere('b.name', 'like', '%' . $term . '%')
                                ->orWhere('m.brand_code', 'like', '%' . $term . '%')
                                ->orWhere('m.name', 'like', '%' . $term . '%')
                                ->orWhere('m.item_code', 'like', '%' . $term . '%');
                        }
                    }
                });
            }
            if ($request->filled('movement_type')) {
                $query->where('im.type', $request->movement_type);
            }
            if ($request->filled('date_from')) {
                $query->where('im.created_at', '>=', Carbon::parse($request->date_from));
            }
            if ($request->filled('date_to')) {
                $query->where('im.created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
            }
            $transactions = $query
                ->orderBy('mc.name')
                //->orderBy('b.name')
                //->orderBy('m.name')
                ->orderBy('m.brand_code')
                ->get();
            // Group transactions by category and by brand_name + item_name combination
            $groupedTransactions = [];
            $grandTotalQty = 0;
            $grandTotalSales = 0;
            $grandTotalCost = 0;
            $grandTotalProfit = 0;
            foreach ($transactions as $transaction) {
                $categoryName = trim($transaction->category_name ?? 'Uncategorized');
                if ($categoryName === '') {
                    $categoryName = 'Uncategorized';
                }
                $itemName = trim($transaction->item_name ?? 'N/A');
                if ($itemName === '') {
                    $itemName = 'N/A';
                }
                $brandName = trim($transaction->brand_name ?? 'N/A');
                if ($brandName === '') {
                    $brandName = 'N/A';
                }
                $brandCode = trim($transaction->brand_code ?? '');
                if ($brandCode === '') {
                    $brandCode = 'N/A';
                }
                
                // Create display name as "BRAND_NAME - ITEM_NAME"
                $displayName = strtoupper($itemName) . ' - ' . strtoupper($brandName);
                
                // Create a unique key using brand_name + item_name combination
                $itemKey = strtoupper($brandName) . '|' . strtoupper($itemName);
                $qtyOut = (float)($transaction->quantity_out ?? 0);
                $amount = (float)($transaction->amount ?? 0);
                $cost = (float)($transaction->cost ?? 0);
                
                $sales = $amount * $qtyOut;
                $totalCost = $cost * $qtyOut;
                $profit = $sales - $totalCost;
                
                if (!isset($groupedTransactions[$categoryName])) {
                    $groupedTransactions[$categoryName] = [
                        'category_id' => $transaction->category_id ?? 0,
                        'category_name' => $categoryName,
                        'items' => [],
                        'total_qty' => 0,
                        'total_sales' => 0,
                        'total_cost' => 0,
                        'total_profit' => 0
                    ];
                }
                // Group by brand_name + item_name combination
                if (!isset($groupedTransactions[$categoryName]['items'][$itemKey])) {
                    $groupedTransactions[$categoryName]['items'][$itemKey] = [
                        'display_name' => $displayName,
                        'item_name' => strtoupper($itemName),
                        'brand_name' => strtoupper($brandName),
                        'brand_code' => strtoupper($brandCode),
                        'total_qty' => 0,
                        'total_sales' => 0,
                        'total_cost' => 0,
                        'total_profit' => 0,
                        'transaction_count' => 0
                    ];
                }
                $groupedTransactions[$categoryName]['items'][$itemKey]['total_qty'] += $qtyOut;
                $groupedTransactions[$categoryName]['items'][$itemKey]['total_sales'] += $sales;
                $groupedTransactions[$categoryName]['items'][$itemKey]['total_cost'] += $totalCost;
                $groupedTransactions[$categoryName]['items'][$itemKey]['total_profit'] += $profit;
                $groupedTransactions[$categoryName]['items'][$itemKey]['transaction_count'] += 1;
                $groupedTransactions[$categoryName]['total_qty'] += $qtyOut;
                $groupedTransactions[$categoryName]['total_sales'] += $sales;
                $groupedTransactions[$categoryName]['total_cost'] += $totalCost;
                $groupedTransactions[$categoryName]['total_profit'] += $profit;
                
                $grandTotalQty += $qtyOut;
                $grandTotalSales += $sales;
                $grandTotalCost += $totalCost;
                $grandTotalProfit += $profit;
            }
            // Sort items within each category by total sales (high to low)
            //ken update order by brand_code 8-17-2026
            foreach ($groupedTransactions as $categoryName => &$categoryData) {
                usort($categoryData['items'], function($a, $b) {
                    return strcmp($a['brand_code'], $b['brand_code']);
                });
            }
            unset($categoryData);
            //end of update ni ken 8-17-2026
            // Sort categories by total sales (high to low)
            uasort($groupedTransactions, function($a, $b) {
                return $b['total_sales'] <=> $a['total_sales'];
            });
            $grandTotalCount = array_sum(array_map(fn($categoryData) => count($categoryData['items']), $groupedTransactions));
            // Prepare export data
            $exportData = [];
            
            // Add header information
            $exportData[] = ['INVENTORY SALES SUMMARY REPORT'];
            $exportData[] = [];
            $exportData[] = ['Store:', $storeName];
            $exportData[] = ['Address:', $storeAddress];
            $exportData[] = ['Period:', $request->filled('date_from') ? Carbon::parse($request->date_from)->format('Y-m-d') : 'Start' . ' - ' . ($request->filled('date_to') ? Carbon::parse($request->date_to)->format('Y-m-d') : 'End')];
            
            // Add brand filter info
            if ($request->filled('brand_id')) {
                $brandName = DB::table('brands')->where('id', $request->brand_id)->value('name');
                $exportData[] = ['Brand:', $brandName ?? $request->brand_id];
            } elseif ($request->filled('brand_name')) {
                $exportData[] = ['Brand:', $request->brand_name];
            }
            
            $exportData[] = ['Category:', $request->filled('category') ? $request->category : 'All'];
            $exportData[] = ['Printed By:', $printedBy];
            $exportData[] = ['Print Date:', now()->format('Y-m-d H:i:s')];
            $exportData[] = [];
            $exportData[] = [];
            // Add table headers - include Brand Code and Description
            $exportData[] = ['Brand Code', 'Description', 'Qty', 'Sales']; // 'Category', 'Brand Name',, 'Cost', 'Profit'
            $exportData[] = [];
            // Add data rows with brand information
            foreach ($groupedTransactions as $categoryName => $categoryData) {
                // Category header
                $exportData[] = [
                    count($categoryData['items']),
                    //'',
                    $categoryName, 
                    '', 
                    //'', 
                    '', 
                    ''
                ];
                
                // Items
                foreach ($categoryData['items'] as $itemData) {
                    $displayName = $itemData['display_name'] ?? 'N/A';
                    $brandCode = $itemData['brand_code'] ?? 'N/A';
                    
                    $exportData[] = [
                        $brandCode,
                        $displayName,
                        number_format((float)($itemData['total_qty'] ?? 0), 3),
                        number_format((float)($itemData['total_sales'] ?? 0), 2)
                        //number_format((float)($itemData['total_cost'] ?? 0), 2),
                        //number_format((float)($itemData['total_profit'] ?? 0), 2)
                    ];
                }
                // Subtotal row
                $exportData[] = [
                    //'SUBTOTAL',
                    //'',
                    '', 
                    'SUBTOTAL',
                    number_format((float)$categoryData['total_qty'], 3),
                    number_format((float)$categoryData['total_sales'], 2)
                    //number_format((float)$categoryData['total_cost'], 2),
                    //number_format((float)$categoryData['total_profit'], 2)
                ];
                // Empty row between categories
                $exportData[] = [];
            }
            // Grand Total row
            $exportData[] = [
                //'GRAND TOTAL',
                //'',
                '', 
                'GRAND TOTAL',
                number_format((float)$grandTotalQty, 3),
                number_format((float)$grandTotalSales, 2)
                //number_format((float)$grandTotalCost, 2),
                //number_format((float)$grandTotalProfit, 2)
            ];
            // Footer
            $exportData[] = [];
            $exportData[] = ['*** End of Report ***'];
            // Generate filename
            $filename = 'Inventory_Sales_Summary_' . date('Y-m-d_His');
            // Check if user wants CSV or Excel
            $format = $request->input('format', 'xlsx');
            
            if ($format === 'csv') {
                return $this->exportCsv($exportData, $filename);
            } else {
                return $this->exportExcel($exportData, $filename);
            }
        } catch (\Exception $e) {
            Log::error('Export report error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export report: ' . $e->getMessage());
        }
    }
    //ken END updated 8-18-2026 EXPORT(CSV OR EXCEL) report
    
    public function printAllFilteredReport(Request $request)
    {
        $user = Auth::user();
        $userStoreCode = $user->store_code;

        $query = DB::table('inventory_movement as im')
            ->leftJoin('menus as m', 'im.menu_id', '=', 'm.id')
            ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
            ->where('im.store_code', $userStoreCode)
            ->selectRaw(
                "im.id,
                im.type,
                im.ordered_id,
                im.void_id,
                im.menu_id,
                im.trans_id,
                im.trans_number,
                im.reference,
                im.detail,
                im.previous_qty,
                im.qty_in,
                im.qty_out,
                im.remaining,
                im.sold_price,
                im.cost,
                im.created_at,
                im.updated_at,
                im.branch_code,
                im.store_code,
                im.is_uuid,
                im.is_sync,
                m.name as item_name,
                m.item_code,
                CASE 
                    WHEN LOWER(im.type) = 'waste' THEN im.unit 
                    ELSE m.unit 
                END as unit,
                b.name as brand_name,
                m.price as menu_price,
                im.previous_qty as quantity_before,
                im.remaining as quantity_after,
                im.sold_price as amount,
                im.qty_in as quantity_in,
                im.qty_out as quantity_out,
                im.detail as remarks,
                CASE
                    WHEN LOWER(im.detail) LIKE '%transform%' THEN
                        CASE WHEN im.type = 'INCOMING' THEN 'Transform In' ELSE 'Transform Out' END
                    WHEN LOWER(im.detail) LIKE '%waste%' OR LOWER(im.detail) LIKE '%scrap%' THEN 'Waste'
                    WHEN LOWER(im.detail) LIKE '%purchase%' OR LOWER(im.detail) LIKE '%receiving%' THEN 'Purchase'
                    WHEN LOWER(im.detail) LIKE '%sale%' OR LOWER(im.detail) LIKE '%sold%' OR LOWER(im.detail) LIKE '%invoice%' THEN 'Sold'
                    WHEN LOWER(im.detail) LIKE '%adjust%' THEN 'Adjustment'
                    WHEN LOWER(im.detail) LIKE '%transfer%' THEN 'Transfer'
                    ELSE CASE WHEN im.type = 'INCOMING' THEN 'Stock In' ELSE 'Stock Out' END
                END as movement_type_label"
            );

        if ($request->filled('item_code')) {
            $itemCode = $request->item_code;
            if (str_starts_with($itemCode, 'NO_CODE_')) {
                $menuId = str_replace('NO_CODE_', '', $itemCode);
                $query->where('im.menu_id', $menuId)
                    ->where(function ($q) {
                        $q->whereNull('m.item_code')
                          ->orWhere('m.item_code', '');
                    });
            } else {
                $query->where('m.item_code', $itemCode);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerms = explode(' ', $search);

            $query->where(function ($q) use ($search, $searchTerms) {
                $q->where('im.reference', 'like', '%' . $search . '%')
                  ->orWhere('im.trans_number', 'like', '%' . $search . '%')
                  ->orWhere('im.detail', 'like', '%' . $search . '%')
                  ->orWhere('im.type', 'like', '%' . $search . '%')
                  ->orWhere('im.id', 'like', '%' . $search . '%')
                  ->orWhere('im.qty_in', 'like', '%' . $search . '%')
                  ->orWhere('im.qty_out', 'like', '%' . $search . '%')
                  ->orWhere('im.previous_qty', 'like', '%' . $search . '%')
                  ->orWhere('im.remaining', 'like', '%' . $search . '%')
                  ->orWhere('im.sold_price', 'like', '%' . $search . '%')
                  ->orWhere('im.cost', 'like', '%' . $search . '%')
                  ->orWhere('m.name', 'like', '%' . $search . '%')
                  ->orWhere('m.item_code', 'like', '%' . $search . '%')
                  ->orWhere('m.unit', 'like', '%' . $search . '%')
                  ->orWhere('b.name', 'like', '%' . $search . '%')
                  ->orWhere(DB::raw("CONCAT(COALESCE(b.name, ''), ' ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%')
                  ->orWhere(DB::raw("CONCAT(COALESCE(m.item_code, ''), ' ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%')
                  ->orWhere(DB::raw("CONCAT(COALESCE(m.item_code, ''), ' - ', COALESCE(b.name, ''), ' - ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%');

                foreach ($searchTerms as $term) {
                    if (strlen($term) > 2) {
                        $q->orWhere('m.name', 'like', '%' . $term . '%')
                          ->orWhere('b.name', 'like', '%' . $term . '%')
                          ->orWhere('m.item_code', 'like', '%' . $term . '%')
                          ->orWhere('im.reference', 'like', '%' . $term . '%')
                          ->orWhere('im.trans_number', 'like', '%' . $term . '%')
                          ->orWhere('im.detail', 'like', '%' . $term . '%');
                    }
                }
            })
            ->where('im.type', '!=', 'waste');
        }

        if ($request->filled('movement_type')) {
            $query->where('im.type', $request->movement_type);
        }

        if ($request->filled('date_from')) {
            $query->where('im.created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('im.created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }
        // LINE 1090: START OF SORTING DIRECTION added by ken 10-15-2026
        $sortDirection = $request->input('sort_direction', 'desc');
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

        $transactions = $query
            ->orderBy('im.created_at', $sortDirection)
            ->get();
        // LINE 1097: END OF SORTING DIRECTION added by ken 10-15-2026
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->format('Y-m-d H:i') : Carbon::today()->startOfDay()->format('Y-m-d H:i');
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay()->format('Y-m-d H:i') : Carbon::today()->endOfDay()->format('Y-m-d H:i');

        return view('user.inventory.movement.print_all', compact('transactions', 'dateFrom', 'dateTo'));
    }

    public function exportAllFilteredReport(Request $request)
    {
        $user = Auth::user();
        $userStoreCode = $user->store_code;

        $query = DB::table('inventory_movement as im')
            ->leftJoin('menus as m', 'im.menu_id', '=', 'm.id')
            ->leftJoin('brands as b', 'm.brand_id', '=', 'b.id')
            ->where('im.store_code', $userStoreCode)
            ->selectRaw(
                "im.id,
                im.type,
                im.ordered_id,
                im.void_id,
                im.menu_id,
                im.trans_id,
                im.trans_number,
                im.reference,
                im.detail,
                im.previous_qty,
                im.qty_in,
                im.qty_out,
                im.remaining,
                im.sold_price,
                im.cost,
                im.created_at,
                im.updated_at,
                im.branch_code,
                im.store_code,
                im.is_uuid,
                im.is_sync,
                m.name as item_name,
                m.item_code,
                CASE 
                    WHEN LOWER(im.type) = 'waste' THEN im.unit 
                    ELSE m.unit 
                END as unit,
                b.name as brand_name,
                m.price as menu_price,
                im.previous_qty as quantity_before,
                im.remaining as quantity_after,
                im.sold_price as amount,
                im.qty_in as quantity_in,
                im.qty_out as quantity_out,
                im.detail as remarks,
                CASE
                    WHEN LOWER(im.detail) LIKE '%transform%' THEN
                        CASE WHEN im.type = 'INCOMING' THEN 'Transform In' ELSE 'Transform Out' END
                    WHEN LOWER(im.detail) LIKE '%waste%' OR LOWER(im.detail) LIKE '%scrap%' THEN 'Waste'
                    WHEN LOWER(im.detail) LIKE '%purchase%' OR LOWER(im.detail) LIKE '%receiving%' THEN 'Purchase'
                    WHEN LOWER(im.detail) LIKE '%sale%' OR LOWER(im.detail) LIKE '%sold%' OR LOWER(im.detail) LIKE '%invoice%' THEN 'Sold'
                    WHEN LOWER(im.detail) LIKE '%adjust%' THEN 'Adjustment'
                    WHEN LOWER(im.detail) LIKE '%transfer%' THEN 'Transfer'
                    ELSE CASE WHEN im.type = 'INCOMING' THEN 'Stock In' ELSE 'Stock Out' END
                END as movement_type_label"
            );

        if ($request->filled('item_code')) {
            $itemCode = $request->item_code;
            if (str_starts_with($itemCode, 'NO_CODE_')) {
                $menuId = str_replace('NO_CODE_', '', $itemCode);
                $query->where('im.menu_id', $menuId)
                    ->where(function ($q) {
                        $q->whereNull('m.item_code')
                          ->orWhere('m.item_code', '');
                    });
            } else {
                $query->where('m.item_code', $itemCode);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $searchTerms = explode(' ', $search);

            $query->where(function ($q) use ($search, $searchTerms) {
                $q->where('im.reference', 'like', '%' . $search . '%')
                  ->orWhere('im.trans_number', 'like', '%' . $search . '%')
                  ->orWhere('im.detail', 'like', '%' . $search . '%')
                  ->orWhere('im.type', 'like', '%' . $search . '%')
                  ->orWhere('im.id', 'like', '%' . $search . '%')
                  ->orWhere('im.qty_in', 'like', '%' . $search . '%')
                  ->orWhere('im.qty_out', 'like', '%' . $search . '%')
                  ->orWhere('im.previous_qty', 'like', '%' . $search . '%')
                  ->orWhere('im.remaining', 'like', '%' . $search . '%')
                  ->orWhere('im.sold_price', 'like', '%' . $search . '%')
                  ->orWhere('im.cost', 'like', '%' . $search . '%')
                  ->orWhere('m.name', 'like', '%' . $search . '%')
                  ->orWhere('m.item_code', 'like', '%' . $search . '%')
                  ->orWhere('m.unit', 'like', '%' . $search . '%')
                  ->orWhere('b.name', 'like', '%' . $search . '%')
                  ->orWhere(DB::raw("CONCAT(COALESCE(b.name, ''), ' ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%')
                  ->orWhere(DB::raw("CONCAT(COALESCE(m.item_code, ''), ' ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%')
                  ->orWhere(DB::raw("CONCAT(COALESCE(m.item_code, ''), ' - ', COALESCE(b.name, ''), ' - ', COALESCE(m.name, ''))"), 'like', '%' . $search . '%');

                foreach ($searchTerms as $term) {
                    if (strlen($term) > 2) {
                        $q->orWhere('m.name', 'like', '%' . $term . '%')
                          ->orWhere('b.name', 'like', '%' . $term . '%')
                          ->orWhere('m.item_code', 'like', '%' . $term . '%')
                          ->orWhere('im.reference', 'like', '%' . $term . '%')
                          ->orWhere('im.trans_number', 'like', '%' . $term . '%')
                          ->orWhere('im.detail', 'like', '%' . $term . '%');
                    }
                }
            })
            ->where('im.type', '!=', 'waste');
        }

        if ($request->filled('movement_type')) {
            $query->where('im.type', $request->movement_type);
        }

        if ($request->filled('date_from')) {
            $query->where('im.created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('im.created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }
        // LINE 1224: START OF SORTING DIRECTION added by ken 10-15-2026
        $sortDirection = $request->input('sort_direction', 'desc');
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

        $transactions = $query
            ->orderBy('im.created_at', $sortDirection)
            ->get();
        // LINE 1231: END OF SORTING DIRECTION added by ken 10-15-2026
        $exportData = [];
        $exportData[] = ['Type', 'Reference', 'Date', 'Item / Description / Detail', 'Before', 'Qty In', 'Qty Out', 'Qty On Hand', 'Sold Price', 'Standard Cost'];

        foreach ($transactions as $transaction) {
            $parts = [];
            if (!empty($transaction->item_code)) {
                $parts[] = $transaction->item_code;
            }

            $itemLabel = trim((!empty($transaction->brand_name) ? $transaction->brand_name . ' - ' : '') . ($transaction->item_name ?? 'N/A'));
            if (!empty($itemLabel)) {
                $parts[] = $itemLabel;
            }

            if (!empty($transaction->detail)) {
                $parts[] = $transaction->detail;
            }

            $exportData[] = [
                $transaction->type ?? 'N/A',
                $transaction->reference ?? 'N/A',
                $transaction->created_at ? Carbon::parse($transaction->created_at)->format('Y-m-d H:i') : 'N/A',
                implode(' - ', $parts),
                number_format((float)($transaction->previous_qty ?? 0), 3),
                number_format((float)($transaction->qty_in ?? 0), 3),
                number_format((float)($transaction->qty_out ?? 0), 3),
                number_format((float)($transaction->remaining ?? 0), 3),
                '₱ ' . number_format((float)($transaction->sold_price ?? 0), 2),
                '₱ ' . number_format((float)($transaction->cost ?? 0), 2),
            ];
        }

        $filename = 'Inventory_Movement_' . date('Y-m-d_His');
        $format = $request->input('format', 'csv');

        if ($format === 'xlsx') {
            return $this->exportExcel($exportData, $filename);
        }

        return $this->exportCsv($exportData, $filename);
    }

    // KEN START updated EXPORT HELPER 8-18-2026 EXPORT(CSV OR EXCEL)
    private function exportCsv($data, $filename)
    {
        $handle = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            // Skip empty arrays for CSV formatting
            if (empty($row)) {
                fputcsv($handle, ['']);
                continue;
            }
            fputcsv($handle, $row);
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
    }
    private function exportExcel($data, $filename)
    {
        // Create a new Excel file using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rowIndex = 1;
        $maxCols = 0;

        foreach ($data as $row) {
            if (empty($row)) {
                $rowIndex++;
                continue;
            }

            $colIndex = 1;

            foreach ($row as $value) {

                // Convert numeric column index to Excel column letter
                // 1 = A, 2 = B, 3 = C, etc.
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

                $cellAddress = $columnLetter . $rowIndex;

                // Prevent values beginning with "=" or "-" from being
                // interpreted as formulas by Excel.
                if (
                    is_string($value) &&
                    (
                        str_starts_with($value, '=') ||
                        str_starts_with($value, '-')
                    )
                ) {
                    $sheet->setCellValue(
                        $cellAddress,
                        "'" . $value
                    );
                } else {
                    $sheet->setCellValue(
                        $cellAddress,
                        $value
                    );
                }

                $colIndex++;
            }

            $maxCols = max($maxCols, count($row));
            $rowIndex++;
        }

        // Apply styling to the first row
        if ($maxCols > 0) {

            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxCols);

            $sheet->getStyle("A1:{$lastColumn}1")
                ->getFont()
                ->setBold(true)
                ->setSize(14);

            // Format all columns as text
            for ($col = 1; $col <= $maxCols; $col++) {

                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

                $sheet->getStyle($columnLetter . ':' . $columnLetter)
                    ->getNumberFormat()
                    ->setFormatCode(
                        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT
                    );
            }
        }

        // Auto-size columns
        for ($col = 1; $col <= $maxCols; $col++) {

            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

            $sheet->getColumnDimension($columnLetter)
                ->setAutoSize(true);
        }

        // Create XLSX writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'export_');

        $writer->save($tempFile);

        // Return as download
        return response()
            ->download(
                $tempFile,
                $filename . '.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(true);
    }
    // KEN END updated EXPORT HELPER 8-18-2026 EXPORT(CSV OR EXCEL)
}