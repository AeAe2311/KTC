<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class IncomeStatementController extends Controller
{
    public function index(Request $request)
    {
        // ============================================
        // INPUTS
        // ============================================
        $startDate      = $request->input('start', Carbon::now()->startOfMonth()->toDateTimeString());
        $endDate        = $request->input('end', Carbon::now()->endOfMonth()->toDateTimeString());
        $selectedBranch = $request->input('branch');

        $category = 'accounting';
        $report   = 'incomeState';

        // ============================================
        // BRANCHES — Matches ReportController
        // Branch records live in `settings`, NOT `branches`
        // ============================================
        $branchs = DB::table('settings')
            ->orderBy('name', 'asc')
            ->where('is_deleted', 0)
            ->where('id', '!=', 1) // Exclude head office
            ->get();

        $settings = DB::table('settings')->where('id', 1)->first();

        // ============================================
        // BRANCH FILTERS
        // ============================================
        $userBranch  = null;
        $branchCodes = [];

        if (auth()->check()) {
            if (auth()->user()->role == 1) {
                if ($selectedBranch) {
                    $branchExists = DB::table('settings')
                        ->where('store_code', $selectedBranch)
                        ->where('is_deleted', 0)
                        ->exists();

                    if ($branchExists) {
                        $userBranch = $selectedBranch;
                    } else {
                        abort(404, 'Selected branch was not found.');
                    }
                }
            } else {
                $userBranch     = auth()->user()->store_code;
                $selectedBranch = $userBranch;
            }
        }

        // ============================================
        // BUILD MONTH RANGE
        // ============================================
        $monthStart = Carbon::parse($startDate)->startOfMonth();
        $monthEnd   = Carbon::parse($endDate)->startOfMonth();

        $months      = [];
        $monthLabels = [];

        while ($monthStart <= $monthEnd) {
            $key           = $monthStart->format('Y-m');
            $label         = $monthStart->format('F');
            $months[$key]  = $label;
            $monthLabels[] = $label;
            $monthStart->addMonth();
        }

        // Placeholders so Blade never hits undefined vars
        $results      = collect([]);
        $resultsCount = 0;
        $cogsValue    = 0;

        // ============================================
        // SALES BY MONTH
        // ============================================
        $salesByMonth         = array_fill_keys(array_keys($months), 0);
        $serviceChargeByMonth = array_fill_keys(array_keys($months), 0);

        if (Schema::hasTable('sale_journal')) {
            $salesQuery = DB::table('sale_journal')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(total) as total'))
                ->where('order_status', '!=', 'CANCELLED')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->groupBy('month');

            if ($userBranch) {
                $salesQuery->where('store_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $salesQuery->whereIn('store_code', $branchCodes);
            }

            foreach ($salesQuery->get() as $row) {
                if (isset($salesByMonth[$row->month])) {
                    $salesByMonth[$row->month] = (float) $row->total;
                }
            }

            $serviceChargeQuery = DB::table('sale_journal')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(service_charge) as total'))
                ->where('order_status', '!=', 'CANCELLED')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<', $endDate)
                ->groupBy('month');

            if ($userBranch) {
                $serviceChargeQuery->where('store_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $serviceChargeQuery->whereIn('store_code', $branchCodes);
            }

            foreach ($serviceChargeQuery->get() as $row) {
                if (isset($serviceChargeByMonth[$row->month])) {
                    $serviceChargeByMonth[$row->month] = (float) $row->total;
                }
            }
        }

        $netSalesTotal = array_sum($salesByMonth) + array_sum($serviceChargeByMonth);

        $salesPctByMonth = [];
        foreach ($months as $monthKey => $label) {
            $salesPctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0 ? 100.0 : 0.0;
        }

        // ============================================
        // SALES BREAKDOWN — one row per transaction
        // Label = "<trans number> - <table name>"
        // Amount = sale_journal.total (+ service_charge as separate row)
        // ============================================
        $salesBreakdown         = []; // [monthKey => [ ['item' => ..., 'amount' => ...], ... ]]
        $serviceChargeBreakdown = []; // [monthKey => [ ['item' => ..., 'amount' => ...] ]]

        if (Schema::hasTable('sale_journal')) {
            // --- Auto-detect the transaction-number column on sale_journal ---
            $transNoColumn = null;
            foreach (['trans_no', 'transaction_no', 'invoice_no', 'receipt_no', 'ref_no', 'reference_no', 'order_no'] as $candidate) {
                if (Schema::hasColumn('sale_journal', $candidate)) {
                    $transNoColumn = $candidate;
                    break;
                }
            }

            $salesTxnQuery = DB::table('sale_journal as sj')
                ->leftJoin('tables as t', 'sj.table', '=', 't.id')
                ->select(
                    DB::raw('DATE_FORMAT(sj.created_at, "%Y-%m") as month'),
                    'sj.number as sale_id',
                    $transNoColumn ? ('sj.' . $transNoColumn . ' as trans_no') : DB::raw('NULL as trans_no'),
                    DB::raw('COALESCE(t.name, "Walk-in / No Table") as table_name'),
                    'sj.total as total',
                    'sj.service_charge as service_charge',
                    'sj.created_at as created_at'
                )
                ->where('sj.order_status', '!=', 'CANCELLED')
                ->where('sj.created_at', '>=', $startDate)
                ->where('sj.created_at', '<', $endDate)
                ->orderBy('sj.created_at')
                ->orderBy('sj.id');

            if ($userBranch) {
                $salesTxnQuery->where('sj.store_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $salesTxnQuery->whereIn('sj.store_code', $branchCodes);
            }

            foreach ($salesTxnQuery->get() as $row) {
                // --- Build "<number> - <table name>" label ---
                $numberPart = !empty($row->trans_no)
                    ? (string) $row->trans_no
                    : (str_pad((string) $row->sale_id, 6, '0', STR_PAD_LEFT));

                $tablePart = !empty($row->table_name)
                    ? trim((string) $row->table_name)
                    : 'Walk-in / No Table';

                $transLabel = $numberPart . ' - ' . $tablePart;

                $salesBreakdown[$row->month][] = [
                    'item'   => $transLabel,
                    'amount' => (float) $row->total,
                ];

                // Service charge row per transaction (NOT aggregated again below)
                if (!empty($row->service_charge) && (float) $row->service_charge > 0) {
                    $serviceChargeBreakdown[$row->month][] = [
                        'item'   => $transLabel . ' (Service Charge)',
                        'amount' => (float) $row->service_charge,
                    ];
                }
            }
        }

        // ❌ REMOVED: the monthly "Service Charge" aggregate block that was double-counting.


        // ============================================
        // REVOLVING BUDGET
        // ============================================
        $revolvingRows = collect([]);

        if (Schema::hasTable('revolving_transactions')) {
            $revolvingQuery = DB::table('revolving_transactions as rt')
                ->leftJoin('expenses_categories as ec', 'rt.category_id', '=', 'ec.id')
                ->select(
                    DB::raw('DATE_FORMAT(rt.created_at, "%Y-%m") as month'),
                    DB::raw('SUM(rt.amount) as amount'),
                    DB::raw('COALESCE(ec.name, "Uncategorized Expenses") as category'),
                    DB::raw('COALESCE(rt.particulars, "Revolving Budget") as item')
                )
                ->where('rt.created_at', '>=', $startDate)
                ->where('rt.created_at', '<', $endDate)
                ->where('rt.is_deleted', 0)
                ->whereNotNull('rt.category_id')
                ->groupBy(
                    DB::raw('DATE_FORMAT(rt.created_at, "%Y-%m")'),
                    'rt.category_id',
                    'ec.name',
                    'rt.particulars'
                )
                ->orderBy('ec.name')
                ->orderBy('rt.particulars');

            if ($userBranch && Schema::hasColumn('revolving_transactions', 'store_code')) {
                $revolvingQuery->where('rt.store_code', $userBranch);
            } elseif (!empty($branchCodes) && Schema::hasColumn('revolving_transactions', 'store_code')) {
                $revolvingQuery->whereIn('rt.store_code', $branchCodes);
            }

            $revolvingRows = $revolvingQuery->get();
        }

        // ============================================
        // COGS FROM ORDERED + MENUS (food cost)
        // (unchanged — this feeds the statement's food-cost COGS aggregation)
        // ============================================
        $cogsByMonth = array_fill_keys(array_keys($months), 0);

        if (Schema::hasTable('ordered') && Schema::hasTable('menus')) {
            $cogsQuery = DB::table('ordered')
                ->join('sale_journal', 'ordered.tran_id', '=', 'sale_journal.id')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select(
                    DB::raw('DATE_FORMAT(ordered.created_at, "%Y-%m") as month'),
                    DB::raw('SUM(ordered.quantity * menus.food_cost) as total')
                )
                ->where('sale_journal.status', 'PAID')
                ->where('sale_journal.order_status', '!=', 'CANCELLED')
                ->where('ordered.created_at', '>=', $startDate)
                ->where('ordered.created_at', '<', $endDate)
                ->groupBy('month');

            if ($userBranch) {
                $cogsQuery->where('sale_journal.store_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $cogsQuery->whereIn('sale_journal.store_code', $branchCodes);
            }

            foreach ($cogsQuery->get() as $row) {
                if (isset($cogsByMonth[$row->month])) {
                    $cogsByMonth[$row->month] = (float) $row->total;
                }
            }
        }

        $cogsTotal      = array_sum($cogsByMonth);
        $cogsPctByMonth = [];

        // 20260528001 JET START LINES 286 - 350
        $purchaseRowsByMonth = array_fill_keys(array_keys($months), []);
        $purchasesByMonth    = array_fill_keys(array_keys($months), 0);

        if (Schema::hasTable('ordered_cogs')) {
            $purchaseQuery = DB::table('ordered_cogs as oc')
                ->leftJoin('menus', 'menus.id', '=', 'oc.menu_id')
                ->leftJoin('brands', 'brands.id', '=', 'menus.brand_id')
                ->leftJoin('purchase_ordered as po', 'po.id', '=', 'oc.po_id')
                ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
                ->select(
                    'oc.id',
                    'oc.po_id',
                    'oc.si_number',
                    'oc.po_total',
                    'oc.status',
                    'oc.created_at',                    
                    'menus.name as menu_name',
                    'brands.name as brand_name',
                    DB::raw('COALESCE(NULLIF(TRIM(po.supplier_name), ""), s.name) as supplier_name')
                )
                ->where('oc.created_at', '>=', $startDate)
                ->where('oc.created_at', '<', $endDate)
                ->orderBy('oc.created_at')
                ->orderBy('oc.po_id')
                ->orderBy('oc.id');

            if ($userBranch) {
                $purchaseQuery->where('oc.store_code', $userBranch);
            } elseif (!empty($branchCodes)) {
                $purchaseQuery->whereIn('oc.store_code', $branchCodes);
            }

            $orders = []; // po_id => ['month', 'po_number', 'amount', 'lines']
            foreach ($purchaseQuery->get() as $line) {
                $orderId = $line->po_id;

                if (!isset($orders[$orderId])) {
                    $orders[$orderId] = [
                        'month'     => Carbon::parse($line->created_at)->format('Y-m'),
                        'po_number' => trim((string) ($line->si_number)),
                        'supplier_name' => trim((string) ($line->supplier_name ?? '')),
                        'amount'    => 0,
                        'lines'     => [],
                    ];
                }

                $menu   = preg_replace('/\s+/', ' ', trim((string) $line->menu_name));
                $brand  = trim((string) $line->brand_name);
                $amount = round((float) $line->po_total, 2);
                if (strtoupper((string) $line->status) === 'VOID') {
                $amount = -abs($amount);
                }

                $label = $brand !== '' ? $menu . ' – ' . $brand : $menu;
                if ($amount < 0) {
                    $label .= ' (VOID)';
                }

                $orders[$orderId]['lines'][] = [
                    'label'  => $label,
                    'amount' => $amount,
                ];
                $orders[$orderId]['amount'] += $amount;
            }

            $usedLabels = []; // the modal merges rows by label, so each P.O. label must be unique
            foreach ($orders as $order) {
                $monthKey = $order['month'];

                if (!isset($purchasesByMonth[$monthKey])) {
                    continue;
                }

                if (round($order['amount'], 2) == 0 && empty(array_filter($order['lines'], fn ($l) => $l['amount'] != 0))) {
                    continue; // every line voided out; nothing to show
                }


                $poLabel = $order['po_number'] !== '' ? $order['po_number'] : '—';
                $supplierName = $order['supplier_name'] !== '' ? $order['supplier_name'] : '—';

                // Create the display label
                $label = $supplierName . ' — S.I. # ' . $poLabel;

                // Count duplicate labels using an array
                $usedLabels[$label] = ($usedLabels[$label] ?? 0) + 1;

                // Append the duplicate count when greater than 1
                if ($usedLabels[$label] > 1) {
                    $label .= ' (' . $usedLabels[$label] . ')';
                }

                $purchaseRowsByMonth[$monthKey][] = [
                    'item'   => $label,
                    'amount' => round($order['amount'], 2),
                    'lines'  => $order['lines'],
                ];

                $purchasesByMonth[$monthKey] += round($order['amount'], 2);
            }
        } // 20260528001 JET END

        // ============================================
        // COGS BREAKDOWN — from inventory_transactions (exp_type = 0)
        // Same source as the COGS value on the statement, so totals reconcile.
        // ============================================
        $cogsBreakdown = []; // [monthKey => [ ['item' => ..., 'amount' => ...], ... ]]

        if (Schema::hasTable('inventory_transactions') && Schema::hasTable('expenses_categories')) {
            $cogsBreakdownQuery = DB::table('inventory_transactions as it')
                ->leftJoin('expenses_categories as ec', 'it.category_id', '=', 'ec.id')
                ->select(
                    DB::raw('DATE_FORMAT(it.created_at, "%Y-%m") as month'),
                    DB::raw('COALESCE(NULLIF(TRIM(it.remarks), ""), "Unnamed COGS Item") as item_name'),
                    DB::raw('SUM(it.amount) as total')
                )
                ->where('it.is_deleted', 0)
                ->where('it.exp_type', 0)
                ->where('it.created_at', '>=', $startDate)
                ->where('it.created_at', '<', $endDate);

            // Branch filter — same logic as the COGS value query
            if ($userBranch && Schema::hasColumn('inventory_transactions', 'branch')) {
                $cogsBreakdownQuery->where('it.branch', $userBranch);
            } elseif (!empty($branchCodes) && Schema::hasColumn('inventory_transactions', 'branch')) {
                $cogsBreakdownQuery->whereIn('it.branch', $branchCodes);
            }

            $cogsBreakdownQuery
                ->groupBy('month', 'item_name')
                ->orderBy('item_name');

            foreach ($cogsBreakdownQuery->get() as $row) {
                $cogsBreakdown[$row->month][] = [
                    'item'   => $row->item_name,
                    'amount' => (float) $row->total,
                ];
            }

            // ============================================
            // PURCHASES — from ordered_cogs
            // Lines that belong to one purchase order share a po_id. Each order becomes one row
            // ("P.O. # <si_number>") whose `lines` list its items ("menu_name – brand_name", po_total).
            // SALES rows add to the order; VOID rows are shown negative and subtract from it.
            // Added to COGS between Beginning and Ending Inventory.
            // 20260528001 JET START LINES 406 - 439
            // ============================================
            $is_field = DB::table('income_statement')->where('id', 2)->first();

            foreach ($months as $monthKey => $label) {
                if ($is_field && !empty($is_field->beginning)) {
                    $cogsBreakdown[$monthKey][] = [
                        'item'   => 'Beginning Inventory',
                        'amount' => (float) $is_field->beginning,
                    ];
                }
                // Purchases sit between Beginning and Ending Inventory
                foreach ($purchaseRowsByMonth[$monthKey] as $purchaseRow) {
                    $cogsBreakdown[$monthKey][] = $purchaseRow;
                }
                if ($is_field && !empty($is_field->scrap)) {
                    $cogsBreakdown[$monthKey][] = [
                        'item'   => 'Less: Scrap',
                        'amount' => -1 * (float) $is_field->scrap,
                    ];
                }
                // if ($is_field && !empty($is_field->ending)) {
                //     $cogsBreakdown[$monthKey][] = [
                //         'item'   => 'Less: Ending Inventory',
                //         'amount' => -1 * (float) $is_field->ending,
                //     ];
                // }
            } // 20260528001 JET END
        }

        // ============================================
        // OPERATING EXPENSES / COGS VALUE
        // ============================================
        $expenseCategoryData    = [];
        $totalOperatingExpenses = 0;
        $revolvingBudgetTotal   = 0;
        $cogsValueByMonth       = array_fill_keys(array_keys($months), 0);

        $grossProfitByMonth    = [];
        $grossProfitPctByMonth = [];

        if (Schema::hasTable('inventory_transactions') && Schema::hasTable('expenses_categories')) {
            $categoryIndex = [];
            $itemIndex     = [];

            $allExpenseCategories = DB::table('expenses_categories')
                ->where('is_deleted', 0)
                ->where('is_is', 0)
                ->pluck('name')
                ->toArray();

            foreach ($allExpenseCategories as $categoryLabel) {
                $categoryIndex[$categoryLabel] = count($expenseCategoryData);
                $itemIndex[$categoryLabel]     = [];
                $expenseCategoryData[] = [
                    'category'       => $categoryLabel,
                    'total'          => 0,
                    'total_by_month' => array_fill_keys(array_keys($months), 0),
                    'items'          => [],
                ];
            }

            $expenseRows = DB::table('inventory_transactions as it')
                ->select(
                    'ec.name as category',
                    'it.remarks as item',
                    DB::raw('DATE_FORMAT(it.created_at, "%Y-%m") as month'),
                    DB::raw('SUM(it.amount) as amount'),
                    DB::raw('COUNT(it.id) as txn_count'),
                    DB::raw('MIN(it.created_at) as first_txn'),
                    DB::raw('MAX(it.created_at) as last_txn')
                )
                ->join('expenses_categories as ec', 'it.category_id', '=', 'ec.id')
                ->where('ec.is_is', 0)
                ->where('it.is_deleted', 0)
                ->where('ec.is_deleted', 0)
                ->where('it.exp_type', 1)
                ->where('it.created_at', '>=', $startDate)
                ->where('it.created_at', '<', $endDate)
                ->where('it.store_code', $userBranch)
                ->groupBy('ec.name', 'it.remarks', 'month')
                ->orderBy('ec.name')
                ->orderBy('it.remarks')
                ->get();

            foreach ($months as $monthKey => $label) {
                foreach ($cogsBreakdown[$monthKey] ?? [] as $breakdownRow) {
                    $cogsValueByMonth[$monthKey] += (float) ($breakdownRow['amount'] ?? 0);
                }
            }

            $cogsValue = array_sum($cogsValueByMonth);

            foreach ($months as $monthKey => $label) {
                $cogsPctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0
                    ? ($cogsValueByMonth[$monthKey] / $salesByMonth[$monthKey]) * 100
                    : 0.0;
            }

            foreach ($expenseRows as $row) {
                $categoryLabel = trim($row->category) ?: 'Uncategorized';
                $rawItemLabel  = trim($row->item) ?: 'Unnamed Item';

                $cleanItemLabel = $this->cleanItemLabel($rawItemLabel);

                $monthKey = $row->month;
                $amount   = (float) $row->amount;
                $txnCount = (int) $row->txn_count;

                $totalOperatingExpenses += $amount;

                if (!isset($categoryIndex[$categoryLabel])) {
                    $categoryIndex[$categoryLabel] = count($expenseCategoryData);
                    $itemIndex[$categoryLabel]     = [];
                    $expenseCategoryData[] = [
                        'category'       => $categoryLabel,
                        'total'          => 0,
                        'total_by_month' => array_fill_keys(array_keys($months), 0),
                        'items'          => [],
                    ];
                }

                $categoryPos = $categoryIndex[$categoryLabel];
                $expenseCategoryData[$categoryPos]['total'] += $amount;
                if (isset($expenseCategoryData[$categoryPos]['total_by_month'][$monthKey])) {
                    $expenseCategoryData[$categoryPos]['total_by_month'][$monthKey] += $amount;
                }

                if (!isset($itemIndex[$categoryLabel][$cleanItemLabel])) {
                    $itemIndex[$categoryLabel][$cleanItemLabel] = count($expenseCategoryData[$categoryPos]['items']);
                    $expenseCategoryData[$categoryPos]['items'][] = [
                        'item'            => $cleanItemLabel,
                        'raw_item'        => $rawItemLabel,
                        'total'           => 0,
                        'amount_by_month' => array_fill_keys(array_keys($months), 0),
                        'txn_count'       => 0,
                        'txn_count_by_month' => array_fill_keys(array_keys($months), 0),
                        'percent'         => 0,
                        'first_txn'       => $row->first_txn,
                        'last_txn'        => $row->last_txn,
                    ];
                }

                $itemPos = $itemIndex[$categoryLabel][$cleanItemLabel];
                $expenseCategoryData[$categoryPos]['items'][$itemPos]['total'] += $amount;
                $expenseCategoryData[$categoryPos]['items'][$itemPos]['txn_count'] += $txnCount;
                if (isset($expenseCategoryData[$categoryPos]['items'][$itemPos]['amount_by_month'][$monthKey])) {
                    $expenseCategoryData[$categoryPos]['items'][$itemPos]['amount_by_month'][$monthKey] += $amount;
                }
                if (isset($expenseCategoryData[$categoryPos]['items'][$itemPos]['txn_count_by_month'][$monthKey])) {
                    $expenseCategoryData[$categoryPos]['items'][$itemPos]['txn_count_by_month'][$monthKey] += $txnCount;
                }
            }

            foreach ($revolvingRows as $row) {
                $categoryLabel = trim($row->category) ?: 'Uncategorized';
                $itemLabel     = trim($row->item) ?: 'Revolving Budget';
                $monthKey      = $row->month;
                $amount        = (float) $row->amount;

                $totalOperatingExpenses += $amount;
                $revolvingBudgetTotal   += $amount;

                if (!isset($categoryIndex[$categoryLabel])) {
                    $categoryIndex[$categoryLabel] = count($expenseCategoryData);
                    $itemIndex[$categoryLabel]     = [];
                    $expenseCategoryData[] = [
                        'category'       => $categoryLabel,
                        'total'          => 0,
                        'total_by_month' => array_fill_keys(array_keys($months), 0),
                        'items'          => [],
                    ];
                }

                $categoryPos = $categoryIndex[$categoryLabel];
                $expenseCategoryData[$categoryPos]['total'] += $amount;
                if (isset($expenseCategoryData[$categoryPos]['total_by_month'][$monthKey])) {
                    $expenseCategoryData[$categoryPos]['total_by_month'][$monthKey] += $amount;
                }

                if (!isset($itemIndex[$categoryLabel][$itemLabel])) {
                    $itemIndex[$categoryLabel][$itemLabel] = count($expenseCategoryData[$categoryPos]['items']);
                    $expenseCategoryData[$categoryPos]['items'][] = [
                        'item'            => $itemLabel,
                        'raw_item'        => $itemLabel,
                        'total'           => 0,
                        'amount_by_month' => array_fill_keys(array_keys($months), 0),
                        'txn_count'       => 0,
                        'txn_count_by_month' => array_fill_keys(array_keys($months), 0),
                        'percent'         => 0,
                        'first_txn'       => null,
                        'last_txn'        => null,
                    ];
                }

                $itemPos = $itemIndex[$categoryLabel][$itemLabel];
                $expenseCategoryData[$categoryPos]['items'][$itemPos]['total'] += $amount;
                if (isset($expenseCategoryData[$categoryPos]['items'][$itemPos]['amount_by_month'][$monthKey])) {
                    $expenseCategoryData[$categoryPos]['items'][$itemPos]['amount_by_month'][$monthKey] += $amount;
                }
            }

            usort($expenseCategoryData, function ($a, $b) {
                return strcasecmp($a['category'], $b['category']);
            });

            foreach ($expenseCategoryData as &$categoryData) {
                usort($categoryData['items'], function ($a, $b) {
                    return strcasecmp($a['item'], $b['item']);
                });

                $categoryTotal = 0;
                foreach ($categoryData['items'] as $it) {
                    $categoryTotal += $it['total'];
                }

                foreach ($categoryData['items'] as &$itemData) {
                    $itemData['percent'] = $totalOperatingExpenses > 0
                        ? ($itemData['total'] / $totalOperatingExpenses) * 100
                        : 0.0;

                    $itemData['percent_of_category'] = $categoryTotal > 0
                        ? ($itemData['total'] / $categoryTotal) * 100
                        : 0.0;
                }
                unset($itemData);
            }
            unset($categoryData);
        }

        // ============================================
        // OPERATING TOTALS BY MONTH
        // ============================================
        $operatingByMonth = array_fill_keys(array_keys($months), 0);
        foreach ($expenseCategoryData as $expense) {
            foreach ($expense['total_by_month'] as $monthKey => $value) {
                $operatingByMonth[$monthKey] += $value;
            }
        }

        $totalOperatingExpenses = array_sum($operatingByMonth);

        $operatingPctByMonth = [];
        foreach ($months as $monthKey => $label) {
            $operatingPctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0
                ? ($operatingByMonth[$monthKey] / $salesByMonth[$monthKey]) * 100
                : 0.0;
        }

        // ============================================
        // CASH OVERAGE
        // ============================================
        $cashOverageByMonth = array_fill_keys(array_keys($months), 0);
        $cashOverage        = 0;

        foreach ($expenseCategoryData as $expense) {
            if (strtolower(trim($expense['category'])) === 'cash overage') {
                $cashOverageByMonth = $expense['total_by_month'];
                $cashOverage        = array_sum($cashOverageByMonth);
                break;
            }
        }

        // ============================================
        // GROSS PROFIT
        // ============================================
        foreach ($months as $monthKey => $label) {
            $grossProfitByMonth[$monthKey] =
                ($salesByMonth[$monthKey] ?? 0) +
                ($serviceChargeByMonth[$monthKey] ?? 0) -
                ($cogsValueByMonth[$monthKey] ?? 0) +
                ($cashOverageByMonth[$monthKey] ?? 0);

            $grossProfitPctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0
                ? ($grossProfitByMonth[$monthKey] / $salesByMonth[$monthKey]) * 100
                : 0.0;
        }

        $grossProfitTotal = array_sum($grossProfitByMonth);

        // ============================================
        // NET INCOME
        // ============================================
        $netIncomeByMonth    = [];
        $netIncomePctByMonth = [];

        foreach ($months as $monthKey => $label) {
            $netIncomeByMonth[$monthKey] =
                ($grossProfitByMonth[$monthKey] ?? 0) -
                ($operatingByMonth[$monthKey] ?? 0);

            $netIncomePctByMonth[$monthKey] = ($salesByMonth[$monthKey] ?? 0) > 0
                ? ($netIncomeByMonth[$monthKey] / $salesByMonth[$monthKey]) * 100
                : 0.0;
        }

        $netIncomeTotal = $grossProfitTotal - $totalOperatingExpenses;

        // ============================================
        // RETURN VIEW
        // ============================================
        return view('admin.reports.income-statement', compact(
            'branchs',
            'settings',
            'category',
            'report',
            'startDate',
            'endDate',
            'results',
            'resultsCount',
            'selectedBranch',
            'months',
            'monthLabels',
            'salesByMonth',
            'serviceChargeByMonth',
            'salesPctByMonth',
            'netSalesTotal',
            'cogsValueByMonth',
            'cogsPctByMonth',
            'cogsValue',
            'cogsTotal',
            'grossProfitByMonth',
            'grossProfitPctByMonth',
            'grossProfitTotal',
            'expenseCategoryData',
            'operatingByMonth',
            'operatingPctByMonth',
            'totalOperatingExpenses',
            'netIncomeByMonth',
            'netIncomePctByMonth',
            'netIncomeTotal',
            'revolvingBudgetTotal',
            'cashOverage',
            'cashOverageByMonth',
            // Breakdowns for modals
            'salesBreakdown',
            'serviceChargeBreakdown',
            'cogsBreakdown'
        ));
    }

    /**
     * Turn a raw `remarks` string into a cleaner display label.
     */
    private function cleanItemLabel(string $raw): string
    {
        $label = preg_replace('/\s+/', ' ', trim($raw));

        if ($label === '') {
            return 'Unnamed Item';
        }

        preg_match_all('/\b\d{3}-\d{3}-\d{3}-\d{5}\b/', $label, $matches);
        $accountCount = count($matches[0]);

        $cleaned = preg_replace('/\b\d{3}-\d{3}-\d{3}-\d{5}\b/', '', $label);
        $cleaned = preg_replace('/\b\d{1,3}(?:,\d{3})*(?:\.\d{2})?\b/', '', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));

        if ($cleaned === '') {
            return $label;
        }

        if (strlen($label) <= 80) {
            return $label;
        }

        if ($accountCount > 1) {
            return $cleaned . ' (' . $accountCount . ' entries)';
        }

        return $cleaned;
    }
}