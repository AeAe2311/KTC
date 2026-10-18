<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        // Allow filtering via query parameters
        if ($request->has('startDate')) {
            $startDate = date('Y-m-d', strtotime($request->get('startDate')));
        } else {
            $startDate = date('Y-m-d');
        }

        if ($request->has('endDate')) {
            $endDate = date('Y-m-d', strtotime($request->get('endDate')));
        } else {
            $endDate = date('Y-m-d');
        }

        $selectedBranch = $request->get('branch', 'all');
        $page = (int) $request->get('page', 1);
        $perPage = 20;

        // Get branches for dropdown
        $branches = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Base query for sales reports
        $query = DB::table('sales_report_transaction')
            ->where('store_code', auth()->user()->store_code)
            ->where('is_deleted', 0);

        // Apply branch filter
        if ($selectedBranch != 'all') {
            $query->where('branch_code', $selectedBranch);
        }

        // Apply date range filter
        if ($startDate && $endDate) {
            $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
            $query->whereBetween('created_at', [$startDate, $eendDate]);
        }

        $menuCount = $query->count();

        // Get paginated results
        $salesReports = $query
            ->orderBy('id', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return view('user.cashier.sales-report', compact(
            'salesReports', 
            'branches', 
            'selectedBranch',
            'startDate', 
            'endDate',
            'page', 
            'menuCount', 
            'perPage'
        ));
    }

    public function generate(Request $request)
    {
        $selectedBranch = $request->branch ?? 'all';
        $page = $request->page ?? 1;

        return redirect()->route('sales-report.index', [
            'startDate' => $request->startDate ?? date('m/d/Y'),
            'endDate' => $request->endDate ?? date('m/d/Y'),
            'branch' => $selectedBranch,
            'page' => $page,
        ]);
    }
    public function formatTransactionPaymentSummary($transaction, $storeCode = null): array
    {
        $amount = (float) ($transaction->amount ?? $transaction->total ?? 0);
        $total = (float) ($transaction->total ?? $transaction->amount ?? 0);
        $rawPaymentType = trim((string) ($transaction->mode_of_payment ?? ''));
        $transactionStatus = strtoupper(trim((string) (isset($transaction->status) ? $transaction->status : (isset($transaction->tstatus) ? $transaction->tstatus : ''))));
        $transactionOrderStatus = strtoupper(trim((string) (isset($transaction->order_status) ? $transaction->order_status : (isset($transaction->ostatus) ? $transaction->ostatus : ''))));
        $isPendingArNoLogs = $rawPaymentType === '' && $transactionStatus === 'UNPAID' && in_array($transactionOrderStatus, ['PREPARING'], true);

        $breakdown = [];
        $arAmount = 0.0;
        $totalPaidAmount = 0.0;
        $maxBalance = 0.0;

        $paymentLogs = $this->getPaymentLogsForTransaction($transaction, $storeCode);

        if ($paymentLogs->isNotEmpty()) {
            $groupedAmounts = [];

            foreach ($paymentLogs as $paymentLog) {
                $label = trim((string) ($paymentLog->mop_name ?? $paymentLog->label ?? ''));
                if ($label === '') {
                    $label = $rawPaymentType !== '' ? $rawPaymentType : 'CASH';
                }

                $normalizedLabel = $this->normalizePaymentLabel($label);
                $logAmount = max(0.0, (float) ($paymentLog->amount ?? 0));
                $logBalance = max(0.0, (float) ($paymentLog->balance ?? 0));

                if ($logAmount > 0) {
                    $groupedAmounts[$normalizedLabel] = ($groupedAmounts[$normalizedLabel] ?? 0.0) + $logAmount;
                    $totalPaidAmount += $logAmount;
                }

                $maxBalance = max($maxBalance, $logBalance);
            }

            foreach ($groupedAmounts as $label => $totalAmount) {
                $breakdown[] = [
                    'label' => $label,
                    'amount' => $totalAmount,
                ];
            }

            $arAmount = max(0.0, $total - $totalPaidAmount);
            if ($maxBalance > 0) {
                $arAmount = max($arAmount, $maxBalance);
            }
        }

        if (empty($breakdown)) {
            if ($isPendingArNoLogs) {
                $arAmount = $total;
            } else {
                $label = $rawPaymentType !== '' ? $rawPaymentType : 'CASH';
                $breakdown[] = [
                    'label' => $this->normalizePaymentLabel($label),
                    'amount' => $amount > 0 ? $amount : $total,
                ];
            }
        }

        $displayLines = [];
        foreach ($breakdown as $row) {
            $displayLines[] = $row['label'] . ': ' . $this->formatPaymentAmount((float) $row['amount']);
        }

        return [
            'breakdown' => $breakdown,
            'display' => !empty($displayLines) ? implode(PHP_EOL, $displayLines) : '-',
            'total' => $total > 0 ? $total : $amount,
            'ar_amount' => $arAmount,
        ];
    }

    private function getPaymentLogsForTransaction($transaction, $storeCode = null)
    {
        if (!Schema::hasTable('payment_logs')) {
            return collect();
        }

        $transactionId = $transaction->id ?? $transaction->tran_id ?? null;
        $transactionNumber = trim((string) ($transaction->number ?? $transaction->nn ?? $transaction->number ?? ''));

        $paymentLogsQuery = DB::table('payment_logs as pl')
            ->where('pl.is_deleted', 0);

        if ($storeCode) {
            $paymentLogsQuery->where(function ($query) use ($storeCode): void {
                $query->where('pl.store_code', $storeCode)
                    ->orWhereNull('pl.store_code');
            });
        }

        $paymentLogsQuery->where(function ($query) use ($transactionId, $transactionNumber): void {
            if ($transactionId !== null && $transactionId !== '') {
                $query->orWhere('pl.tran_id', (int) $transactionId);
            }

            if ($transactionNumber !== '') {
                $query->orWhere('pl.tran_number', $transactionNumber);
            }
        });

        if (Schema::hasTable('mode_of_payment')) {
            $paymentLogsQuery->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                ->select('pl.label', 'pl.amount', 'pl.balance', 'pl.type', 'mop.name as mop_name');
        } else {
            $paymentLogsQuery->select('pl.label', 'pl.amount', 'pl.balance', 'pl.type');
        }

        return $paymentLogsQuery->get();
    }

    private function normalizePaymentLabel(string $rawLabel): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', strtoupper((string) $rawLabel)));

        return $normalized !== '' ? $normalized : 'CASH';
    }

    private function formatPaymentAmount(float $amount): string
    {
        if (abs($amount - round($amount)) < 0.000001) {
            return '₱ ' . number_format(round($amount), 0, '.', ',');
        }

        return '₱ ' . number_format($amount, 2, '.', ',');
    }
    // LINE 215: start ken update of SDR 09-17-2026
    public function getTransactions(Request $request)
    {
        try {
            $reportId  = $request->report_id;
            $storeCode = auth()->user()->store_code;

            // Get the report with basic validation
            $report = DB::table('sales_report_transaction')
                ->where('store_code', $storeCode)
                ->where('is_deleted', 0)
                ->where(function ($query) use ($reportId) {
                    $query->where('id', $reportId)
                        ->orWhere('local_id', $reportId)
                        ->orWhere('is_uuid', $reportId);
                })
                ->first();

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found'
                ]);
            }
            // Fallback to created_at only if the report has no start_date/end_date
            $startDate = $report->start_date
                ? date('Y-m-d 00:00:00', strtotime($report->start_date))
                : date('Y-m-d 00:00:00', strtotime($report->created_at));

            $endDate = $report->end_date
                ? date('Y-m-d 23:59:59', strtotime($report->end_date))
                : date('Y-m-d 23:59:59', strtotime($report->created_at));

            // Build report lookup IDs
            $reportLookupIds = array_filter([
                $reportId,
                $report->id,
                $report->local_id,
                $report->is_uuid,
                $report->trans_number,
            ]);

            // Get valid report IDs (not deleted)
            $validReportIds = DB::table('sales_report_transaction')
                ->where('store_code', $storeCode)
                ->where('is_deleted', 0)
                ->pluck('local_id')
                ->filter()
                ->toArray();
            if ($startDate < '2026-09-04 00:00:00') {
                // Get transactions — filtered by the report's start_date / end_date window
                $transactions = DB::table('transactions as srd')
                    ->select(
                        'srd.id',
                        'srd.number',
                        'srd.total as amount',
                        'srd.total',
                        'srd.mode_of_payment',
                        'srd.slug',
                        'srd.type',
                        'srd.created_at as date',
                        'srd.local_id',
                        'srd.branch_code',
                        'srd.status as tstatus',
                        'srd.order_status as ostatus',
                        'srd.cashier as cashier_id',
                        'srd.table as client_id',
                        /* 'srd.orig_local_id', */
                        /* 'srd.report_id', */
                        /* 'srd.report_tran_uuid', */
                        'tables.name as table_name',
                        'settings.name as branchName',
                        'users.firstname as cashier_firstname',
                        'users.lastname as cashier_lastname',
                        DB::raw('CONCAT_WS(" ", users.firstname, users.lastname) as cashier_name')
                    )
                    ->leftJoin('tables', 'srd.table', '=', 'tables.id')
                    ->leftJoin('settings', function ($join) use ($storeCode) {
                        $join->on('srd.store_code', '=', 'settings.store_code')
                            ->where('settings.is_deleted', 0);
                    })
                    ->leftJoin('users', function ($join) use ($storeCode) {
                        $join->on('srd.cashier', '=', 'users.id')
                            ->where('users.store_code', '=', $storeCode);
                    })
                    ->where('srd.store_code', '=', $storeCode)
                    /* ->where(function ($query) use ($reportLookupIds, $report) {
                        $query->whereIn('srd.report_id', $reportLookupIds);

                        if (!empty($report->is_uuid)) {
                            $query->orWhere('srd.report_tran_uuid', '=', $report->is_uuid);
                        }

                        if (!empty($report->local_id)) {
                            $query->orWhere('srd.report_id', '=', $report->local_id);
                        }
                    })
                    ->where(function ($query) use ($validReportIds) {
                        if (!empty($validReportIds)) {
                            $query->whereIn('srd.report_id', $validReportIds)
                                ->orWhereNull('srd.report_id');
                        }
                    }) */
                    // Use the resolved start_date / end_date from sales_report_transaction
                    ->whereBetween('srd.created_at', [$startDate, $endDate])
                    ->orderBy('srd.id', 'desc')
                    ->get();
            }else{
                // Get transactions — filtered by the report's start_date / end_date window
                $transactions = DB::table('sales_report_transactions_duplicate as srd')
                    ->select(
                        'srd.id',
                        'srd.number',
                        'srd.total as amount',
                        'srd.total',
                        'srd.mode_of_payment',
                        'srd.slug',
                        'srd.type',
                        'srd.created_at as date',
                        'srd.local_id',
                        'srd.branch_code',
                        'srd.status as tstatus',
                        'srd.order_status as ostatus',
                        'srd.cashier as cashier_id',
                        'srd.table_id as client_id',
                        'srd.orig_local_id',
                        'srd.report_id',
                        'srd.report_tran_uuid',
                        'tables.name as table_name',
                        'settings.name as branchName',
                        'users.firstname as cashier_firstname',
                        'users.lastname as cashier_lastname',
                        DB::raw('CONCAT_WS(" ", users.firstname, users.lastname) as cashier_name')
                    )
                    ->leftJoin('tables', 'srd.table_id', '=', 'tables.id')
                    ->leftJoin('settings', function ($join) use ($storeCode) {
                        $join->on('srd.store_code', '=', 'settings.store_code')
                            ->where('settings.is_deleted', 0);
                    })
                    ->leftJoin('users', function ($join) use ($storeCode) {
                        $join->on('srd.cashier', '=', 'users.id')
                            ->where('users.store_code', '=', $storeCode);
                    })
                    ->where('srd.store_code', '=', $storeCode)
                    ->where(function ($query) use ($reportLookupIds, $report) {
                        $query->whereIn('srd.report_id', $reportLookupIds);

                        if (!empty($report->is_uuid)) {
                            $query->orWhere('srd.report_tran_uuid', '=', $report->is_uuid);
                        }

                        if (!empty($report->local_id)) {
                            $query->orWhere('srd.report_id', '=', $report->local_id);
                        }
                    })
                    ->where(function ($query) use ($validReportIds) {
                        if (!empty($validReportIds)) {
                            $query->whereIn('srd.report_id', $validReportIds)
                                ->orWhereNull('srd.report_id');
                        }
                    })
                    // Use the resolved start_date / end_date from sales_report_transaction
                    ->whereBetween('srd.created_at', [$startDate, $endDate])
                    ->orderBy('srd.id', 'desc')
                    ->get();                
            }
            if ($startDate < '2026-09-04 00:00:00') {
                $transactions->each(function ($t) {
                    $t->orig_local_id    = $t->local_id;  // synthesize from local_id
                    $t->report_id        = null;          // not selected in branch 1
                    $t->report_tran_uuid = null;
                });
            }            
            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'html' => '<div class="flex justify-center items-center h-64 text-gray-400">
                                <div class="text-center">
                                    <i class="uil uil-file-times text-4xl block"></i>
                                    <span class="text-sm mt-2 block">No transactions found for this report</span>
                                </div>
                            </div>',
                    'report_number' => $report->trans_number
                ]);
            }

            // Get approval cart slugs
            $approvalCartSlugs = Schema::hasTable('approval_cart')
                ? DB::table('approval_cart')
                    ->where('store_code', '=', $storeCode)
                    ->pluck('slug')
                    ->toArray()
                : [];

            // Get all transaction IDs for this report
            if ($startDate < '2026-09-04 00:00:00') {
                $origLocalIds = $transactions->pluck('local_id')->filter()->unique()->values()->toArray();
            }else{
                $origLocalIds = $transactions->pluck('orig_local_id')->filter()->unique()->values()->toArray();
            }
                $localIds     = $transactions->pluck('local_id')->filter()->unique()->values()->toArray();
            $allTranIds   = array_merge($origLocalIds, $localIds);
            $allTranIds   = array_unique(array_filter($allTranIds));

            // Get the report_id that these transactions belong to
            $reportLocalId = $report->local_id;

            // Batch fetch all orders - FILTER BY report_id ONLY
            // DO NOT add a created_at filter here — report_id already scopes orders
            // to the correct report, and adding a date filter breaks item matching.
            if ($startDate < '2026-09-04 00:00:00') {
                    $allOrders = DB::table('ordered')
                        ->select(
                            'ordered.*',
                            'menus.name as menu_name',
                            'menus.item_code',
                            'menus.retail_price',
                            'menus.wholesale_price',
                            'menus.brand_code as brand_code',
                            'menus.food_cost as menu_food_cost',
                            'brands.name as brand_name'
                        )
                        ->leftJoin('menus', function ($join) use ($storeCode) {
                            $join->on('ordered.menu_uuid', '=', 'menus.is_uuid')
                                ->where('menus.store_code', '=', $storeCode)
                                ->where('menus.is_deleted', 0);
                        })
                        ->leftJoin('brands', 'menus.brand_id', '=', 'brands.id')
                        ->whereIn('ordered.tran_id', $allTranIds)
                        ->where('ordered.store_code', '=', $storeCode)
                        /* ->where('ordered.report_id', '=', $reportLocalId) // CRITICAL: Filter by report_id */
                        ->get()
                        ->groupBy('tran_id');
            }else{
                    $allOrders = DB::table('ordered_duplicate')
                        ->select(
                            'ordered_duplicate.*',
                            'menus.name as menu_name',
                            'menus.item_code',
                            'menus.retail_price',
                            'menus.wholesale_price',
                            'menus.brand_code as brand_code',
                            'menus.food_cost as menu_food_cost',
                            'brands.name as brand_name'
                        )
                        ->leftJoin('menus', function ($join) use ($storeCode) {
                            $join->on('ordered_duplicate.menu_uuid', '=', 'menus.is_uuid')
                                ->where('menus.store_code', '=', $storeCode)
                                ->where('menus.is_deleted', 0);
                        })
                        ->leftJoin('brands', 'menus.brand_id', '=', 'brands.id')
                        ->whereIn('ordered_duplicate.tran_id', $allTranIds)
                        ->where('ordered_duplicate.status', '!=', 'CANCELLED')
                        ->where('ordered_duplicate.store_code', '=', $storeCode)
                        ->where('ordered_duplicate.report_id', '=', $reportLocalId) // CRITICAL: Filter by report_id
                        ->get()
                        ->groupBy('tran_id');                
            }
            // Log how many orders were found
            $totalOrders = $allOrders->sum(function ($group) { return $group->count(); });
            \Log::info('Total orders found for report ' . $reportLocalId . ': ' . $totalOrders);

            // Get all payment logs
            $allPaymentLogs = DB::table('payment_logs')
                ->whereIn('tran_id', $origLocalIds)
                ->where('is_deleted', 0)
                ->where('store_code', $storeCode)
                ->get()
                ->groupBy('tran_id');

            // Process transactions
            $formattedTransactions = $transactions->map(function ($transaction) use ($approvalCartSlugs, $storeCode, $allOrders, $allPaymentLogs, $report) {
                $isCancelled = ($transaction->ostatus ?? '') === 'CANCELLED';

                $displayItems = [];

                // Get orders for this transaction
                $orders = collect();

                // Try with orig_local_id first
                if (isset($allOrders[$transaction->orig_local_id])) {
                    $orders = $allOrders[$transaction->orig_local_id];
                } elseif (isset($allOrders[$transaction->local_id])) {
                    $orders = $allOrders[$transaction->local_id];
                }

                if ($orders->isNotEmpty()) {
                    // Group items by a unique key (use item_code to separate different items)
                    $groupedItems = $orders->groupBy(function ($item) {
                        return ($item->menu_name ?? 'Unknown') . '||' . ($item->item_code ?? 'N/A');
                    });

                    foreach ($groupedItems as $key => $group) {
                        // Extract name and code from the key
                        list($name, $itemCode) = explode('||', $key);

                        $salesAmount = $group->sum('amount');
                        $foodCost    = $group->sum('menu_food_cost');

                        $quantityBreakdownParts = [];
                        foreach ($group as $row) {
                            $qty = floatval($row->quantity ?? 0);
                            if ($qty > 0) {
                                $quantityBreakdownParts[] = number_format($qty, 3);
                            }
                        }

                        // If cancelled, set sales amount to 0
                        $finalSalesAmount = $isCancelled ? 0 : $salesAmount;

                        $displayItems[] = [
                            'name'               => $name ?: 'Unknown Item',
                            'brand'              => $group->first()->brand_name ?? '',
                            'brand_code'         => $group->first()->brand_code ?? '',
                            'item_code'          => $itemCode ?: 'N/A',
                            'total_quantity'     => $group->sum('quantity'),
                            'sales_amount'       => $finalSalesAmount,
                            'food_cost'          => $foodCost,
                            'profit'             => $finalSalesAmount - $foodCost,
                            'discount_amount'    => $group->sum('discount_amount'),
                            'count'              => $group->count(),
                            'quantity_breakdown' => implode(' | ', $quantityBreakdownParts),
                            'is_cancelled'       => $isCancelled
                        ];
                    }
                } else {
                    // No orders found for this report
                    if ($isCancelled) {
                        $displayItems[] = [
                            'name'               => 'CANCELLED TRANSACTION',
                            'brand'              => '',
                            'brand_code'         => '---',
                            'item_code'          => 'N/A',
                            'total_quantity'     => 0,
                            'sales_amount'       => 0,
                            'food_cost'          => 0,
                            'profit'             => 0,
                            'discount_amount'    => 0,
                            'amount'             => 0, // LINE 453: added variable amount to 0 9-10-2026
                            'count'              => 1,
                            'quantity_breakdown' => '0.000',
                            'is_cancelled'       => true
                        ];
                    } else {
                        // Check if orders exist for a different report (for debugging)
                        $ordersOtherReport = DB::table('ordered_duplicate')
                            ->where('tran_id', $transaction->orig_local_id)
                            ->where('store_code', $storeCode)
                            ->where('status', '!=', 'CANCELLED')
                            ->where('report_id', '!=', $report->local_id)
                            ->count();

                        if ($ordersOtherReport > 0) {
                            \Log::warning('Found ' . $ordersOtherReport . ' orders for transaction ' . $transaction->id . ' with different report_id');
                        }

                        $displayItems[] = [
                            'name'               => 'No items found for this report',
                            'brand'              => '',
                            'brand_code'         => '---',
                            'item_code'          => 'N/A',
                            'total_quantity'     => 0,
                            'sales_amount'       => $transaction->total ?? 0,
                            'food_cost'          => 0,
                            'profit'             => 0,
                            'discount_amount'    => 0,
                            'amount'             => 0, // LINE 481: added variable amount to 0 9-10-2026
                            'count'              => 1,
                            'quantity_breakdown' => '0.000',
                            'is_cancelled'       => $isCancelled
                        ];
                    }
                }

                // Get payment logs
                $paymentBreakdown = collect();
                if (!$isCancelled && $transaction->mode_of_payment === 'MULTIPLE' && isset($allPaymentLogs[$transaction->orig_local_id])) {
                    $paymentLogs = $allPaymentLogs[$transaction->orig_local_id]
                        ->groupBy('label')
                        ->map(function ($items, $method) {
                            return (object) [
                                'method' => $method,
                                'amount' => $items->sum('amount'),
                            ];
                        })
                        ->values();

                    $paymentBreakdown = $paymentLogs;
                }

                // Format payment summary
                $paymentSummary = $this->formatTransactionPaymentSummary($transaction, $storeCode);

                // Set transaction properties
                $transaction->ordered_items       = $orders;
                $transaction->displayItems        = $displayItems;
                $transaction->paymentBreakdown    = $paymentBreakdown;
                $transaction->payment_display     = $paymentSummary['display'];
                $transaction->payment_summary     = $paymentSummary['breakdown'];
                $transaction->ar_amount           = $paymentSummary['ar_amount'];
                $transaction->is_in_approval_cart = in_array($transaction->slug, $approvalCartSlugs, true);
                $transaction->cashier_name        = trim(($transaction->cashier_firstname ?? '') . ' ' . ($transaction->cashier_lastname ?? ''));
                if (empty($transaction->cashier_name)) {
                    $transaction->cashier_name = 'N/A';
                }
                $transaction->is_cancelled      = $isCancelled;
                $transaction->date              = $transaction->date ?? $transaction->created_at ?? now();
                $transaction->resolved_local_id = $transaction->local_id ?? $transaction->id;

                return $transaction;
            });

            // Render view
            $html = view('user.cashier.partials.sales_report_modal_content', [
                'report'       => $report,
                'transactions' => $formattedTransactions
            ])->render();

            return response()->json([
                'success'       => true,
                'html'          => $html,
                'report_number' => $report->trans_number
            ]);

        } catch (\Exception $e) {
            \Log::error('SalesReportController::getTransactions error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // LINE 646: end ken update of SDR 09-17-2026

    private function getCashierName($cashierId)
    {
        if (!$cashierId) return 'N/A';
        
        $user = DB::table('users')
            ->where('id', $cashierId)
            ->first();
            
        if ($user) {
            return trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
        }
        
        return 'N/A';
    }

    public function export(Request $request)
    {
        // Your export logic here
    }
}