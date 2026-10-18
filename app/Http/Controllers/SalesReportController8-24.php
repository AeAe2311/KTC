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

    public function getTransactions(Request $request)
    {
        try {
            $reportId = $request->report_id;

            // Match both the local web id and the synced local_id / UUID values.
            $report = DB::table('sales_report_transaction')
                ->where(function ($query) use ($reportId) {
                    $query->where('id', $reportId)
                        ->orWhere('local_id', $reportId)
                        ->orWhere('is_uuid', $reportId);
                })
                ->where('store_code', auth()->user()->store_code)
                ->where('is_deleted', 0)
                ->first();

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found'
                ]);
            }

            $reportLookupIds = collect([
                $reportId,
                $report->id,
                $report->local_id,
                $report->is_uuid,
                $report->trans_number,
            ])->filter(function ($value) {
                return $value !== null && $value !== '';
            })->unique()->values()->all();

            // Get transactions from duplicate table with all necessary joins
            $transactions = DB::table('sales_report_transactions_duplicate')
                ->select(
                    'sales_report_transactions_duplicate.id',
                    'sales_report_transactions_duplicate.number as number',
                    'sales_report_transactions_duplicate.total as amount',
                    'sales_report_transactions_duplicate.total as total',
                    'sales_report_transactions_duplicate.mode_of_payment',
                    'sales_report_transactions_duplicate.slug',
                    'sales_report_transactions_duplicate.type',
                    'sales_report_transactions_duplicate.created_at as date',
                    //'sales_report_transactions_duplicate.sales_type',
                    'tables.name as table_name',
                    'sales_report_transactions_duplicate.local_id',
                    'sales_report_transactions_duplicate.branch_code',
                    'settings.name as branchName',
                    'sales_report_transactions_duplicate.status as tstatus',
                    'sales_report_transactions_duplicate.order_status as ostatus',
                    //'sales_report_transactions_duplicate.is_approved',
                    'sales_report_transactions_duplicate.cashier as cashier_id',
                    'sales_report_transactions_duplicate.table_id as client_id',
                    'users.firstname as cashier_firstname',
                    'users.lastname as cashier_lastname',
                    'transactions.local_id as tx_local_id'
                )
                ->leftJoin('tables', function($join) {
                    $join->on('sales_report_transactions_duplicate.table_id', '=', 'tables.id');
                })
                ->leftJoin('settings', function($join) {
                    $join->on('sales_report_transactions_duplicate.store_code', '=', 'settings.store_code')
                        ->where('settings.is_deleted', '=', 0);
                })
                ->leftJoin('users', function($join) {
                    $join->on('sales_report_transactions_duplicate.cashier', '=', 'users.id')
                        ->where('users.store_code', '=', auth()->user()->store_code);
                })
                ->leftJoin('transactions', function($join) {
                    $join->on('sales_report_transactions_duplicate.slug', '=', 'transactions.slug')
                        ->where('transactions.status', '!=', 'CANCELLED') // ken added filter 08-24-2026
                        ->where('transactions.store_code', '=', auth()->user()->store_code);
                })
                ->where('sales_report_transactions_duplicate.store_code', auth()->user()->store_code)
                ->where(function ($query) use ($reportLookupIds, $report) {
                    $query->whereIn('sales_report_transactions_duplicate.report_id', $reportLookupIds);

                    if (!empty($report->is_uuid)) {
                        $query->orWhere('sales_report_transactions_duplicate.report_tran_uuid', $report->is_uuid);
                    }

                    if (!empty($report->local_id)) {
                        $query->orWhere('sales_report_transactions_duplicate.report_id', $report->local_id);
                    }
                })
                ->where('sales_report_transactions_duplicate.order_status', '!=', 'CANCELLED') // ken added filter 08-24-2026
                ->orderBy('sales_report_transactions_duplicate.id', 'desc')
                ->get();

            // If no transactions found, return empty
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

            // Get approval cart slugs if table exists
            $approvalCartSlugs = [];
            if (Schema::hasTable('approval_cart')) {
                $approvalCartSlugs = DB::table('approval_cart')
                    ->where('store_code', auth()->user()->store_code)
                    ->pluck('slug')
                    ->toArray();
            }

            // ---------------------------------------------------------------
            // FIX: batch-fetch transaction-level discount fields (service_charge,
            // ld_value, pwd) up front instead of querying per-row inside the map.
            // These live on `transactions`, not on `sales_report_transactions_duplicate`,
            // which is why the modal was missing the discount that the main
            // transactions table / export correctly display.
            // ---------------------------------------------------------------
            $resolvedLocalIds = $transactions->map(function ($transaction) {
                return $transaction->tx_local_id ?? $transaction->local_id ?? $transaction->id;
            })->filter()->unique()->values()->all();

            $discountRows = DB::table('transactions')
                ->whereIn('local_id', $resolvedLocalIds)
                ->where('store_code', auth()->user()->store_code)
                ->select('local_id', 'service_charge', 'ld_value', 'pwd')
                ->get()
                ->keyBy('local_id');

            // Format transactions with payment breakdown and ordered items
            $formattedTransactions = $transactions->map(function($transaction) use ($approvalCartSlugs, $discountRows) {
                // Prefer the authoritative local_id sourced from `transactions`, fall back to the
                // duplicate table's own local_id, then finally the row id.
                $resolvedLocalId = $transaction->tx_local_id ?? $transaction->local_id ?? $transaction->id;

                // Get payment logs if available
                $paymentBreakdown = collect();
                if ($transaction->mode_of_payment === 'MULTIPLE') {
                    $paymentBreakdown = DB::table('payment_logs')
                        ->where('tran_id', $resolvedLocalId)
                        ->where('tran_number', $transaction->number)
                        ->where('is_deleted', 0)
                        ->where('store_code', auth()->user()->store_code)
                        ->get()
                        ->groupBy('label')
                        ->map(function ($items, $method) {
                            return (object) [
                                'method' => $method,
                                'amount' => $items->sum('amount'),
                            ];
                        })
                        ->values();
                }

                // Get ordered items with all necessary joins (matching index method exactly)
                $orders = DB::table('ordered')
                    ->select(
                        'ordered.id as ordered_id',
                        'ordered.quantity as ordered_quantity',
                        'ordered.amount as ordered_amount',
                        'ordered.discount_amount as ordered_discount_amount',
                        'menus.food_cost as ordered_food_cost',
                        'ordered.price_type',
                        'ordered.cashier',
                        'ordered.status as ordered_status',
                        'ordered.created_at as ordered_created_at',
                        'menus.name as menu_name',
                        'menus.item_code',
                        'menus.price as menu_price',
                        'menus.retail_price',
                        'menus.wholesale_price',
                        'menus.brand_code as brand_code',
                        'brands.name as brand_name'
                    )
                    ->leftJoin('menus', function($join) {
                        $join->on('ordered.menu_uuid', '=', 'menus.is_uuid')
                            ->where('menus.store_code', '=', auth()->user()->store_code);
                    })
                    ->leftJoin('brands', function($join) {
                        $join->on('menus.brand_id', '=', 'brands.id');
                    })
                    ->where('ordered.tran_id', $resolvedLocalId)
                    ->where('ordered.status', '!=', 'CANCELLED') // ken added filter 08-24-2026
                    ->where('ordered.store_code', auth()->user()->store_code)
                    // Scope ordered items to the transaction's own date, same as print()/export()/index()
                    ->whereBetween('ordered.created_at', [
                        date('Y-m-d 00:00:00', strtotime($transaction->date ?? $transaction->created_at)),
                        date('Y-m-d 23:59:59', strtotime($transaction->date ?? $transaction->created_at)),
                    ])
                    ->get();

                // Check if we have ordered items
                $hasItems = $orders->isNotEmpty();

                // ---------------------------------------------------------------
                // FIX: proportionally allocate transaction-level discounts
                // (service_charge + ld_value + pwd) across items by their share
                // of total sales amount — mirrors TransactionController::index()
                // and the transactions-sales Blade view, so the modal now shows
                // the same discount figures as the main table / export.
                // ---------------------------------------------------------------
                $discountRow = $discountRows->get($resolvedLocalId);
                $serviceCharge = floatval($discountRow->service_charge ?? 0);
                $ldValue = floatval($discountRow->ld_value ?? 0);
                $pwd = floatval($discountRow->pwd ?? 0);
                $totalTransactionDiscount = $serviceCharge + $ldValue + $pwd;

                // Group items by name (matching index method grouping)
                $groupedItems = $orders->groupBy('menu_name');

                // Build per-item amount list (needed for proportional allocation)
                $itemAmounts = [];
                foreach ($groupedItems as $name => $group) {
                    $itemAmounts[] = [
                        'name' => $name,
                        'group' => $group,
                        'amount' => $group->sum('ordered_amount'),
                    ];
                }
                // Sort descending by amount so any rounding remainder lands on the
                // largest item, same convention used elsewhere in this codebase.
                usort($itemAmounts, function ($a, $b) {
                    return $b['amount'] <=> $a['amount'];
                });
                $totalItemAmount = array_sum(array_column($itemAmounts, 'amount'));

                $displayItems = [];

                if ($hasItems && $totalItemAmount > 0 && $totalTransactionDiscount > 0) {
                    // Allocate discount proportionally to each item's share of sales
                    $allocatedDiscounts = [];
                    $runningTotal = 0;

                    foreach ($itemAmounts as $itemData) {
                        $itemDiscount = ($itemData['amount'] / $totalItemAmount) * $totalTransactionDiscount;
                        $roundedDiscount = round($itemDiscount, 2);
                        $allocatedDiscounts[] = [
                            'name' => $itemData['name'],
                            'group' => $itemData['group'],
                            'discount' => $roundedDiscount,
                        ];
                        $runningTotal += $roundedDiscount;
                    }

                    // Push any rounding remainder onto the last (smallest) item
                    $roundingDiff = $totalTransactionDiscount - $runningTotal;
                    if (abs($roundingDiff) > 0.001) {
                        $lastIndex = count($allocatedDiscounts) - 1;
                        $allocatedDiscounts[$lastIndex]['discount'] = round(
                            $allocatedDiscounts[$lastIndex]['discount'] + $roundingDiff,
                            2
                        );
                    }

                    foreach ($allocatedDiscounts as $allocated) {
                        $group = $allocated['group'];
                        $salesAmount = $group->sum('ordered_amount');
                        $foodCost = $group->sum('ordered_food_cost');

                        $quantityBreakdownParts = [];
                        foreach ($group as $row) {
                            $qty = floatval($row->ordered_quantity ?? 0);
                            if ($qty > 0) {
                                $quantityBreakdownParts[] = number_format($qty, 3);
                            }
                        }

                        $displayItems[] = [
                            'name' => $allocated['name'] ?: 'Unknown Item',
                            'brand' => $group->first()->brand_name ?? '',
                            'brand_code' => $group->first()->brand_code ?? '',
                            'item_code' => $group->first()->item_code ?? 'N/A',
                            'total_quantity' => $group->sum('ordered_quantity'),
                            'sales_amount' => $salesAmount,
                            'food_cost' => $foodCost,
                            'profit' => $salesAmount - $foodCost,
                            'discount_amount' => $allocated['discount'],
                            'count' => $group->count(),
                            'quantity_breakdown' => implode(' | ', $quantityBreakdownParts),
                        ];
                    }
                } elseif ($hasItems && $totalItemAmount > 0) {
                    // No transaction-level discount to allocate — show items as-is
                    foreach ($itemAmounts as $itemData) {
                        $group = $itemData['group'];
                        $salesAmount = $group->sum('ordered_amount');
                        $foodCost = $group->sum('ordered_food_cost');

                        $quantityBreakdownParts = [];
                        foreach ($group as $row) {
                            $qty = floatval($row->ordered_quantity ?? 0);
                            if ($qty > 0) {
                                $quantityBreakdownParts[] = number_format($qty, 3);
                            }
                        }

                        $displayItems[] = [
                            'name' => $itemData['name'] ?: 'Unknown Item',
                            'brand' => $group->first()->brand_name ?? '',
                            'brand_code' => $group->first()->brand_code ?? '',
                            'item_code' => $group->first()->item_code ?? 'N/A',
                            'total_quantity' => $group->sum('ordered_quantity'),
                            'sales_amount' => $salesAmount,
                            'food_cost' => $foodCost,
                            'profit' => $salesAmount - $foodCost,
                            'discount_amount' => 0,
                            'count' => $group->count(),
                            'quantity_breakdown' => implode(' | ', $quantityBreakdownParts),
                        ];
                    }
                }

                // Format payment summary (matching index method)
                $paymentSummary = $this->formatTransactionPaymentSummary(
                    $transaction,
                    auth()->user()->store_code
                );

                // Add all the necessary fields (matching index method)
                $transaction->ordered_items = $hasItems ? $orders : collect();
                $transaction->displayItems = $displayItems;
                $transaction->paymentBreakdown = $paymentBreakdown;
                $transaction->payment_display = $paymentSummary['display'];
                $transaction->payment_summary = $paymentSummary['breakdown'];
                $transaction->ar_amount = $paymentSummary['ar_amount'];
                $transaction->is_in_approval_cart = in_array($transaction->slug, $approvalCartSlugs, true);
                $transaction->cashier_name = trim(($transaction->cashier_firstname ?? '') . ' ' . ($transaction->cashier_lastname ?? ''));
                if (empty($transaction->cashier_name)) {
                    $transaction->cashier_name = 'N/A';
                }
                $transaction->is_cancelled = ($transaction->ostatus ?? '') === 'CANCELLED';
                $transaction->date = $transaction->date ?? $transaction->created_at ?? now();
                $transaction->resolved_local_id = $resolvedLocalId;

                return $transaction;
            });

            // Build HTML for the modal
            $html = view('user.cashier.partials.sales_report_modal_content', [
                'report' => $report,
                'transactions' => $formattedTransactions
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'report_number' => $report->trans_number
            ]);

        } catch (\Exception $e) {
            \Log::error('SalesReportController::getTransactions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

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