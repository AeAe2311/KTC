<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Log;

class TransactionController extends Controller
{
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
    // LINE 135: start of adding by ken 9-30-2026
    private function applySearchedNumberFilter($query, string $searchedNumber, string $column = 'transactions.number')
    {
        if ($searchedNumber !== '') {
            $query->where($column, 'LIKE', '%' . $searchedNumber . '%');
        }
        return $query;
    }
    // LINE 143: end of update by ken 9-30-2026
    private function formatPaymentAmount(float $amount): string
    {
        if (abs($amount - round($amount)) < 0.000001) {
            return '₱ ' . number_format(round($amount), 0, '.', ',');
        }

        return '₱ ' . number_format($amount, 2, '.', ',');
    }

    private function buildApprovalCartPayload($transaction): array
    {
        $storeCode = (string) ($transaction->store_code ?? auth()->user()?->store_code ?? '');

        return [
            'number' => (string) ($transaction->number ?? ''),
            'total' => (string) ($transaction->total ?? 0),
            'mode_of_payment' => (string) ($transaction->mode_of_payment ?? ''),
            'amount' => (string) ($transaction->amount ?? 0),
            'fee' => (float) ($transaction->fee ?? 0),
            'service_charge' => (string) ($transaction->service_charge ?? 0),
            'sc_percent' => (string) ($transaction->sc_percent ?? 0),
            'pwd' => (string) ($transaction->pwd ?? ''),
            'customer_with_discount' => (string) ($transaction->customer_with_discount ?? ''),
            'total_customer' => (string) ($transaction->total_customer ?? ''),
            'lime_discount' => (string) ($transaction->lime_discount ?? ''),
            'ld_value' => (string) ($transaction->ld_value ?? ''),
            'payor_name' => (string) ($transaction->payor_name ?? ''),
            'payor_number' => (string) ($transaction->payor_number ?? ''),
            'lessVatDeduction' => (float) ($transaction->lessVatDeduction ?? 0),
            'vat' => (float) ($transaction->vat ?? 0),
            'vatSales' => (float) ($transaction->vatSales ?? 0),
            'vatExemptSales' => (float) ($transaction->vatExemptSales ?? 0),
            'type' => (string) ($transaction->type ?? ''),
            'is_vat' => (int) ($transaction->is_vat ?? 0),
            'table' => (string) ($transaction->table ?? ''),
            'status' => (string) ($transaction->status ?? ''),
            'order_status' => (string) ($transaction->order_status ?? ''),
            'sales_type' => (int) ($transaction->sales_type ?? 0),
            'cashier' => (string) ($transaction->cashier ?? ''),
            'staff_id' => (int) ($transaction->staff_id ?? 0),
            'slug' => (string) ($transaction->slug ?? ''),
            'print' => (int) ($transaction->print ?? 1),
            'created_at' => $transaction->created_at ?? now(),
            'updated_at' => $transaction->updated_at ?? now(),
            'is_uuid' => (string) ($transaction->is_uuid ?? ''),
            'is_sync' => (int) ($transaction->is_sync ?? 0),
            'is_app' => (int) ($transaction->is_app ?? 0),
            'is_branch' => (string) ($transaction->is_branch ?? ''),
            'branch_code' => (string) ($transaction->branch_code ?? ''),
            'local_id' => (int) ($transaction->local_id ?? 0),
            'store_code' => $storeCode,
            'branch' => (int) ($transaction->branch ?? 0),
            'approved_by' => 0,
        ];
    }
    // LINE 199: Start of new method for search filter added by ken 9-30-2026
    private function applyTransactionFilters($query, $selectedBranch, $selectedClient, $selectedStatus, $searchedNumber, $tablePrefix = 'transactions')
    {
        if ($selectedBranch != 'all') {
            $query->where($tablePrefix . '.branch_code', $selectedBranch);
        }
        
        if ($selectedClient != 'all') {
            $query->where($tablePrefix . '.table', $selectedClient);
        }
        
        if ($selectedStatus != 'all') {
            if ($selectedStatus === 'CANCELLED') {
                $query->where($tablePrefix . '.order_status', 'CANCELLED');
            } elseif ($selectedStatus === 'PAID') {
                $query->where($tablePrefix . '.status', 'PAID')
                    ->where($tablePrefix . '.order_status', '!=', 'CANCELLED');
            } elseif ($selectedStatus === 'UNPAID') {
                $query->where($tablePrefix . '.status', 'UNPAID')
                    ->where($tablePrefix . '.order_status', '!=', 'CANCELLED');
            }
        }
        
        if ($searchedNumber !== '') {
            $query->where($tablePrefix . '.number', 'LIKE', '%' . $searchedNumber . '%');
        }
        
        return $query;
    }
    // LINE 228: End of new method for search filter added by ken 9-30-2026
    //ken start updated the index na method  8/12/2026 - 250623001
    public function index(Request $request){
        // Allow filtering via query parameters (PRG friendly)
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

        $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
        $selectedBranch = $request->get('branch', 'all');
        $selectedClient = $request->get('client', 'all');
        $selectedStatus = $request->get('status', 'all');   // LINE 245: New added filter for refund/void by ken 9-30-2026
        $searchedNumber  = trim((string) $request->get('searchedNumber', ''));  // LINE 246: New added filter for search trans by ken 9-30-2026
        // Get current page from request, default to 1
        $page = (int) $request->get('page', 1);
        $perPage = 100;

        $settings = DB::table('settings')->where('id', 1)->first();
        
        // Get branches for dropdown
        $branches = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Get clients for dropdown
        $clients = DB::table('tables')
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        // LINE 230: start of update by ken 9-30-2026
        $countQuery = DB::table('transactions')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.store_code', auth()->user()->store_code);

        if ($selectedBranch != 'all') {
            $countQuery->where('transactions.branch_code', $selectedBranch);
        }
        if ($selectedClient != 'all') {
            $countQuery->where('transactions.table', $selectedClient);
        }
        if ($selectedStatus != 'all') {
            if ($selectedStatus === 'CANCELLED') {
                $countQuery->where('transactions.order_status', 'CANCELLED');
            } elseif ($selectedStatus === 'PAID') {
                $countQuery->where('transactions.status', 'PAID')
                        ->where('transactions.order_status', '!=', 'CANCELLED');
            } elseif ($selectedStatus === 'UNPAID') {
                $countQuery->where('transactions.status', 'UNPAID')
                        ->where('transactions.order_status', '!=', 'CANCELLED');
            }
        }
        // NEW: apply the searched number
        $this->applySearchedNumberFilter($countQuery, $searchedNumber);

        $menuCount = $countQuery->count();
        // LINE 255: end of update by ken 9-30-2026
        // Base query with all joins
        $query = DB::table('transactions')
            ->select(
                'transactions.id', 
                'transactions.number as nn', 
                'transactions.total as amount', 
                'transactions.total as total', 
                'transactions.mode_of_payment',
                'transactions.slug', 
                'transactions.type', 
                'transactions.created_at as date',
                'transactions.sales_type', 
                'tables.name as table_name',
                'transactions.local_id',
                'transactions.branch_code',
                'settings.name as branchName', 
                'transactions.status as tstatus', 
                'transactions.order_status as ostatus', 
                'transactions.is_approved',
                'transactions.cashier as cashier_id',
                'transactions.table as client_id',
                // Ordered table columns
                'ordered.id as ordered_id',
                'ordered.quantity as ordered_quantity',
                'ordered.amount as ordered_amount',
                'ordered.discount_amount as ordered_discount_amount',
                'menus.food_cost as ordered_food_cost',
                'ordered.price_type',
                'ordered.cashier',
                'ordered.status as ordered_status',
                'ordered.created_at as ordered_created_at',
                // Menus table columns
                'menus.name as menu_name',
                'menus.item_code',
                'menus.price as menu_price',
                'menus.retail_price',
                'menus.wholesale_price',
                'menus.brand_code as brand_code',
                'brands.name as brand_name', 
                // Client columns
                'tables.name as client_name',
                // User columns
                'users.firstname as cashier_firstname',
                'users.lastname as cashier_lastname'
            )
            ->join('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id');
            })
            ->leftJoin('ordered', function($join) use ($startDate, $eendDate) {
                $join->on('transactions.local_id', '=', 'ordered.tran_id')
                    ->where('ordered.store_code', '=', auth()->user()->store_code)
                    ->whereBetween('ordered.created_at', [$startDate, $eendDate]); // Filter ordered items by date range
            })
            ->leftJoin('menus', function($join) {
                $join->on('ordered.menu_uuid', '=', 'menus.is_uuid')
                    ->where('menus.store_code', '=', auth()->user()->store_code);
            })
            ->leftJoin('brands', function($join) {
                $join->on('menus.brand_id', '=', 'brands.id');
            })
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('users', function($join) {
                $join->on('transactions.cashier', '=', 'users.id')
                    ->where('users.store_code', '=', auth()->user()->store_code);
            })
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.store_code', auth()->user()->store_code);

        // Apply branch filter if not 'all'
        if ($selectedBranch != 'all') {
            $query->where('transactions.branch_code', $selectedBranch);
        }

        // Apply client filter if not 'all'
        if ($selectedClient != 'all') {
            $query->where('transactions.table', $selectedClient);
        }

        $approvalCartSlugs = [];
        if (Schema::hasTable('approval_cart')) {
            $approvalCartSlugs = DB::table('approval_cart')
                ->where('store_code', auth()->user()->store_code)
                ->pluck('slug')
                ->toArray();
        }

        // LINE 345: Start of update by ken 9-30-2026
        $transactionIds = DB::table('transactions')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.store_code', auth()->user()->store_code)
            ->when($selectedBranch != 'all', function($q) use ($selectedBranch) {
                return $q->where('transactions.branch_code', $selectedBranch);
            })
            ->when($selectedClient != 'all', function($q) use ($selectedClient) {
                return $q->where('transactions.table', $selectedClient);
            })
            ->when($selectedStatus != 'all', function($q) use ($selectedStatus) {
                if ($selectedStatus === 'CANCELLED') {
                    return $q->where('transactions.order_status', 'CANCELLED');
                } elseif ($selectedStatus === 'PAID') {
                    return $q->where('transactions.status', 'PAID')
                            ->where('transactions.order_status', '!=', 'CANCELLED');
                } elseif ($selectedStatus === 'UNPAID') {
                    return $q->where('transactions.status', 'UNPAID')
                            ->where('transactions.order_status', '!=', 'CANCELLED');
                }
                return $q;
            })
            // NEW: apply the searched number
            ->when($searchedNumber !== '', function($q) use ($searchedNumber) {
                return $q->where('transactions.number', 'LIKE', '%' . $searchedNumber . '%');
            })
            ->orderBy('transactions.id', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->pluck('id')
            ->toArray();
        // LINE 372: end of update by ken 9-30-2026
        // If no transactions on this page, redirect to page 1
        if (empty($transactionIds) && $page > 1) {
            return redirect()->route('transactions.index', [
                'startDate' => date('m/d/Y', strtotime($startDate)),
                'endDate' => date('m/d/Y', strtotime($endDate)),
                'branch' => $selectedBranch,
                'table' => $selectedClient,
                'status' => $selectedStatus,     // LINE 420: New added by ken 9-30-2026
                'page' => 1
            ]);
        }

        // LINE 385: Start of update by ken 9-30-2026
        $allResults = $query
            ->whereIn('transactions.id', $transactionIds)
            ->when($selectedStatus != 'all', function($q) use ($selectedStatus) {
                if ($selectedStatus === 'CANCELLED') {
                    return $q->where('transactions.order_status', 'CANCELLED');
                } elseif ($selectedStatus === 'PAID') {
                    return $q->where('transactions.status', 'PAID')
                            ->where('transactions.order_status', '!=', 'CANCELLED');
                } elseif ($selectedStatus === 'UNPAID') {
                    return $q->where('transactions.status', 'UNPAID')
                            ->where('transactions.order_status', '!=', 'CANCELLED');
                }
                return $q;
            })
            // NEW: apply the searched number
            ->when($searchedNumber !== '', function($q) use ($searchedNumber) {
                return $q->where('transactions.number', 'LIKE', '%' . $searchedNumber . '%');
            })
            ->orderBy('transactions.id', 'desc')
            ->get();
        // LINE 402: end of update by ken 9-30-2026
        // Group by transaction ID
        $groupedResults = $allResults->groupBy('id');

        // Map results
        $results = $groupedResults->map(function ($items) use ($approvalCartSlugs) {
            $first = $items->first();

            // Check if we have ordered items
            $hasItems = $items->contains(function ($item) {
                return !is_null($item->ordered_id);
            });

            // Store ordered items (only those within the date range)
            $first->ordered_items = $hasItems ? $items : collect();

            // Optional: Add a count of how many ordered items are filtered out
            // You could add a separate query here if needed

            // Payment summary
            $paymentSummary = $this->formatTransactionPaymentSummary(
                $first,
                auth()->user()->store_code
            );

            $first->payment_display = $paymentSummary['display'];
            $first->payment_summary = $paymentSummary['breakdown'];
            $first->ar_amount = $paymentSummary['ar_amount'];
            $first->is_in_approval_cart = in_array($first->slug, $approvalCartSlugs, true);
            $first->cashier_name = trim(($first->cashier_firstname ?? '') . ' ' . ($first->cashier_lastname ?? ''));
            if (empty($first->cashier_name)) {
                $first->cashier_name = 'N/A';
            }
            
            $first->is_cancelled = ($first->ostatus ?? '') === 'CANCELLED';
            
            return $first;
        })->values();

        $resultsCount = $results->count();

        // LINE 487: start of update by ken 10-12-2026
        $auditedQuery = $this->applyTransactionFilters(
            DB::table('transactions')
                ->whereBetween('created_at', [$startDate, $eendDate])
                ->where('store_code', auth()->user()->store_code),
            $selectedBranch, $selectedClient, $selectedStatus, $searchedNumber
        );

        $nonAuditedQuery = $this->applyTransactionFilters(
            DB::table('transactions')
                ->where('status', 'UNPAID')
                ->whereBetween('created_at', [$startDate, $eendDate])
                ->where('store_code', auth()->user()->store_code),
            $selectedBranch, $selectedClient, $selectedStatus, $searchedNumber
        );
            
        $checkingQuery = $this->applyTransactionFilters(
            DB::table('transactions')
                ->whereBetween('created_at', [$startDate, $eendDate])
                ->where('store_code', auth()->user()->store_code),
            $selectedBranch, $selectedClient, $selectedStatus, $searchedNumber
        );
            
        $cancelledQuery = $this->applyTransactionFilters(
            DB::table('transactions')
                ->where('order_status', 'CANCELLED')
                ->whereBetween('created_at', [$startDate, $eendDate])
                ->where('store_code', auth()->user()->store_code),
            $selectedBranch, $selectedClient, $selectedStatus, $searchedNumber
        );

        $audited = number_format(
            $auditedQuery
                ->selectRaw('SUM(CASE WHEN amount > total THEN total ELSE amount END) as adjusted_total')
                ->value('adjusted_total') ?? 0,
            2, '.', ','
        );
        // LINE 624: end of update by ken 10-12-2026
        $nonAuditedTotal = (clone $nonAuditedQuery)->sum('total') ?? 0;
        $nonAuditedPaid = (clone $nonAuditedQuery)->sum('amount') ?? 0;
        
        $nonAudited = number_format($nonAuditedTotal - $nonAuditedPaid, 2, '.', ',');
        $checkingTotal = number_format((clone $checkingQuery)->sum('total') ?? 0, 2, '.', ',');
        $cancelled = number_format((clone $cancelledQuery)->sum('total') ?? 0, 2, '.', ',');
        
        if (auth()->user()->role != 5){
            return view('user.cashier.transactions-sales', compact(
                'results', 'settings', 'resultsCount', 
                'startDate', 'endDate', 'audited', 'nonAudited', 
                'checkingTotal', 'cancelled', 'branches', 'selectedBranch',
                'clients', 'selectedClient','selectedStatus', 'searchedNumber',// LINE 467: new defined variable by ken 9-30-2026
                'page', 'menuCount', 'perPage'
            ));
        } else {
            return view('user.cashier.transactions', compact(
                'results', 'settings', 'resultsCount', 
                'startDate', 'endDate', 'audited', 'nonAudited', 
                'checkingTotal', 'cancelled', 'branches', 'selectedBranch',
                'clients', 'selectedClient','selectedStatus', 'searchedNumber', // LINE 505: new defined variable by ken 9-30-2026
                'page', 'menuCount', 'perPage'
            ));            
        }
    }
    //ken end updated the index na method  8/12/2026 - 250623001

public function edit($slug)
{
    $transaction = DB::table('transactions')->where('slug', $slug)->first();
    
    if (!$transaction) {
        return response()->json(['error' => 'Transaction not found.'], 404);
    }
    
    // Get payment modes for dropdown
    $paymentModes = DB::table('mode_of_payment')
        ->where('store_code', auth()->user()->store_code)
        ->where('is_deleted', 0)
        ->orderBy('name', 'asc')
        ->get(['name']);
    
    // Return JSON for AJAX request
    if (request()->ajax()) {
        return response()->json([
            'transaction' => $transaction,
            'payment_modes' => $paymentModes
        ]);
    }
    
    // For non-AJAX requests, return the view
    $branches = DB::table('settings')
        ->where('store_code', auth()->user()->store_code)
        ->where('is_deleted', 0)
        ->orderBy('name', 'asc')
        ->get();
    
    return view('user.cashier.transactions-edit', compact('transaction', 'paymentModes', 'branches'));
}
// LINE 584: Start of searchbynumber method added by ken 9-30-2026
public function searchByNumber(Request $request)
{
    $request->validate([
        'transaction_number' => 'required|string|max:255',
    ]);

    $number = trim($request->transaction_number);
    $storeCode = auth()->user()->store_code;

    // Find all matching transactions (partial match on number)
    $matches = DB::table('transactions')
        ->where('store_code', $storeCode)
        ->where('number', 'LIKE', '%' . $number . '%')
        ->orderBy('created_at', 'asc')
        ->get(['id', 'number', 'created_at', 'branch_code']);

    if ($matches->isEmpty()) {
        return redirect()->route('transactions.index')
            ->with('message', "No transaction found matching: {$number}");
    }

    // Use the earliest and latest dates of the matches as the range
    $startDate = date('m/d/Y', strtotime($matches->first()->created_at));
    $endDate   = date('m/d/Y', strtotime($matches->last()->created_at));

    // If all matches are the same date, use that single date
    if ($startDate === $endDate) {
        // already same
    }

    // Preserve other filters if present
    $selectedBranch = $request->get('branch', 'all');
    $selectedStatus = $request->get('status', 'all');
    $selectedClient = $request->get('client', 'all');

    // If the matched transaction has a branch, filter to it
    if ($matches->count() === 1 && $matches->first()->branch_code) {
        $selectedBranch = $matches->first()->branch_code;
    }

    return redirect()->route('transactions.index', [
        'startDate'        => $startDate,
        'endDate'          => $endDate,
        'branch'           => $selectedBranch,
        'status'           => $selectedStatus,
        'client'           => $selectedClient,
        'searchedNumber'   => $number,
        'page'             => 1,
    ])->with('message', "Found " . $matches->count() . " transaction(s) matching: {$number}");
}
// LINE 635: End of searchbynumber method added by ken 9-30-2026
public function updatePayments(Request $request)
{
    $request->validate([
        'slug' => 'required|string|exists:transactions,slug',
        'payments' => 'required|array|min:1',
        'payments.*.method' => 'required|string|max:255',
        'payments.*.amount' => 'required|numeric|min:0.01',
        'total_amount' => 'required|numeric|min:0'
    ]);

    $transaction = DB::table('transactions')->where('slug', $request->slug)->first();
    
    if (!$transaction) {
        return response()->json(['success' => false, 'message' => 'Transaction not found.'], 404);
    }

    DB::beginTransaction();

    $tranId = $transaction->local_id ?? $transaction->id;
    $storeCode = $transaction->store_code ?? auth()->user()->store_code;

    // Delete existing payment logs
    DB::table('payment_logs')->where('tran_id', $tranId)->delete();

    // Insert new payment logs
    foreach ($request->payments as $payment) {
        $mop = DB::table('mode_of_payment')
            ->where('name', $payment['method'])
            ->where('store_code', $storeCode)
            ->first();

        DB::table('payment_logs')->insert([
            'tran_id' => $tranId,
            'tran_number' => $transaction->number,
            'amount' => $payment['amount'],
            'mop_id' => $mop ? $mop->id : null,
            'label' => $payment['method'],
            'store_code' => $storeCode,
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Update transaction
    DB::table('transactions')->where('slug', $request->slug)->update([
        'total' => $request->total_amount,
        'amount' => $request->total_amount,
        'mode_of_payment' => $request->payments[0]['method'],
        'updated_at' => now(),
    ]);

    // Mark as paid if fully paid
    $totalPaid = array_sum(array_column($request->payments, 'amount'));
    if ($totalPaid >= $transaction->total) {
        DB::table('transactions')->where('slug', $request->slug)->update([
            'status' => 'PAID',
            'order_status' => 'COMPLETED',
        ]);
    }

    DB::commit();

    return response()->json(['success' => true, 'message' => 'Transaction updated successfully!']);
}

public function update(Request $request)
{
    // 1. Validate
    $request->validate([
        'slug' => ['required', 'string'],
        'amount' => ['required', 'numeric', 'min:0.01'],
        'mode_of_payment' => ['nullable', 'string', 'max:255'],
    ]);

    // 2. Get transaction
    $transaction = DB::table('transactions')->where('slug', $request->slug)->first();
    
    if (!$transaction) {
        return response()->json(['success' => false, 'message' => 'Transaction not found.']);
    }

    if (strtoupper($transaction->order_status) === 'CANCELLED') {
        return response()->json(['success' => false, 'message' => 'Cannot edit a cancelled transaction.']);
    }

    // 3. Save old values
    $oldAmount = $transaction->total;
    $oldPaymentMode = $transaction->mode_of_payment;
    $oldStatus = $transaction->status;

    // 4. Update transactions table
    $updateData = [
        'total' => $request->amount,
        'amount' => $request->amount,
        'updated_at' => now(),
    ];

    if ($request->filled('mode_of_payment')) {
        $updateData['mode_of_payment'] = $request->mode_of_payment;
    }

    DB::table('transactions')
        ->where('slug', $request->slug)
        ->update($updateData);

    // 5. Update payment_logs table if payment mode changed
    if ($request->filled('mode_of_payment') && $request->mode_of_payment !== $oldPaymentMode) {
        if (Schema::hasTable('payment_logs')) {
            // IMPORTANT: Use local_id instead of id for payment_logs
            $tranId = $transaction->local_id ?? $transaction->id;
            
            // Get the mop_id from mode_of_payment table
            $mop = DB::table('mode_of_payment')
                ->where('name', $request->mode_of_payment)
                ->where('store_code', auth()->user()->store_code)
                ->where('is_deleted', 0)
                ->first();

            if ($mop) {
                // Update existing payment_logs
                $updated = DB::table('payment_logs')
                    ->where('tran_id', $tranId)
                    ->where('is_deleted', 0)
                    ->update([
                        'mop_id' => $mop->id,
                        'label' => $request->mode_of_payment,
                        'updated_at' => now(),
                    ]);

                \Log::info('Payment logs update:', [
                    'tran_id' => $tranId,
                    'rows_updated' => $updated,
                    'old_mop' => $oldPaymentMode,
                    'new_mop' => $request->mode_of_payment
                ]);

                // If no payment_logs exist, create one
                if ($updated === 0) {
                    DB::table('payment_logs')->insert([
                        'tran_id' => $tranId,
                        'tran_number' => $transaction->number,
                        'amount' => $request->amount,
                        'mop_id' => $mop->id,
                        'label' => $request->mode_of_payment,
                        'store_code' => $transaction->store_code,
                        'is_deleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    \Log::info('Created new payment_log entry');
                }
            } else {
                \Log::warning('MOP not found:', ['mode_of_payment' => $request->mode_of_payment]);
            }
        }
    }

    // 6. Log the update
    if (class_exists('App\Models\Log')) {
        $log = new \App\Models\Log();
        $log->trans_id = $transaction->id;
        $log->classification = 'Transactions';
        $log->info = "Updated amount from {$oldAmount} to {$request->amount}";
        
        if ($request->filled('mode_of_payment') && $request->mode_of_payment !== $oldPaymentMode) {
            $log->info .= " and payment mode from {$oldPaymentMode} to {$request->mode_of_payment}";
        }
        
        $log->user_id = auth()->id();
        $log->event = 'Update';
        $log->save();
    }

    // 7. Return response
    return response()->json([
        'success' => true,
        'message' => 'Transaction updated successfully!',
        'new_amount' => $request->amount,
        'old_amount' => $oldAmount,
        'mode_of_payment' => $request->mode_of_payment ?? $oldPaymentMode
    ]);
}
    public function generate(Request $request){
        // Use PRG: redirect to index (GET) with filter params to avoid form resubmission warnings
        $selectedBranch = $request->branch ?? 'all';
        $selectedClient = $request->client ?? 'all';   // LINE 824: NEw filter added by ken 9-30-2026
        $selectedStatus = $request->status ?? 'all';   // LINE 825: NEw filter added by ken 9-30-2026
        $page = $request->page ?? 1; // Preserve page number

        return redirect()->route('transactions.index', [
            'startDate' => $request->startDate ?? date('m/d/Y'),
            'endDate' => $request->endDate ?? date('m/d/Y'),
            'branch' => $selectedBranch,
            'client' => $selectedClient,   // LINE 832: NEw filter added by ken 9-30-2026
            'status' => $selectedStatus,   // LINE 833: NEw filter added by ken 9-30-2026
            'page' => $page,
        ]);

        // The code below won't execute because of the return statement above,
        // but I'm fixing it anyway in case you decide to remove the redirect
        
        $settings = DB::table('settings')->where('id', 1)->first();
        
        // Get branches for dropdown
        $branches = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Get clients for dropdown
        $clients = DB::table('tables')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Process dates
        $startDate = date('Y-m-d', strtotime($request->startDate ?? date('Y-m-d')));
        $endDate = date('Y-m-d', strtotime($request->endDate ?? date('Y-m-d')));
        $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));

        // Base query
        $query = DB::table('transactions')
            ->select(
                'transactions.id', 
                'transactions.number as nn', 
                'transactions.total as amount',
                'transactions.mode_of_payment', 
                'transactions.type', 
                'transactions.created_at as date',
                'transactions.slug',
                'transactions.sales_type', 
                'tables.name as table',
                'transactions.local_id', 
                'transactions.status as tstatus', 
                'transactions.order_status as ostatus',
                'transactions.table as client_id',
                'tables.name as client_name',
                // Ordered table columns
                'ordered.quantity as ordered_quantity',
                'ordered.amount as ordered_amount',
                'ordered.discount_amount as ordered_discount_amount',
                'ordered.food_cost as ordered_food_cost',
                'ordered.price_type',
                'ordered.cashier',
                'ordered.status as ordered_status',
                // Menus table columns
                'menus.name as menu_name',
                'menus.item_code',
                'menus.price as menu_price',
                'menus.retail_price',
                'menus.wholesale_price',
                'brands.name as brand_name'
            )
            ->join('tables', 'transactions.table', '=', 'tables.id')
            ->leftJoin('ordered', 'transactions.id', '=', 'ordered.tran_id')
            ->leftJoin('brands', 'menus.brand_id', '=', 'brands.id') 
            ->leftJoin('menus', 'ordered.menu_id', '=', 'menus.id')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.store_code', auth()->user()->store_code);

        // Apply branch filter if not 'all'
        if ($selectedBranch != 'all') {
            $query->where('transactions.branch_code', $selectedBranch);
        }

        // Apply client filter if not 'all' (using table column instead of client_id)
        if ($selectedClient != 'all') {
            $query->where('transactions.table', $selectedClient);
        }

        $results = $query->orderBy('transactions.id', 'desc')->get();
        $resultsCount = $query->count();
            
        /* Start of Audited total */
        $auditedQuery = DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->whereBetween('created_at', [$startDate, $eendDate])
            ->where('store_code', auth()->user()->store_code);

        $nonAuditedQuery = DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('transactions.status', 'UNPAID')
            ->whereBetween('created_at', [$startDate, $eendDate])
            ->where('store_code', auth()->user()->store_code);
            
        $checkingQuery = DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->whereBetween('created_at', [$startDate, $eendDate])
            ->where('store_code', auth()->user()->store_code);
            
        $cancelledQuery = DB::table('transactions')
            ->where('order_status', 'CANCELLED')
            ->whereBetween('created_at', [$startDate, $eendDate])
            ->where('store_code', auth()->user()->store_code);  
            
        // Apply branch filter to audit totals
        if ($selectedBranch != 'all') {
            $auditedQuery->where('branch_code', $selectedBranch);
            $nonAuditedQuery->where('branch_code', $selectedBranch);
            $checkingQuery->where('branch_code', $selectedBranch);
            $cancelledQuery->where('branch_code', $selectedBranch);
        }

        // Apply client filter to audit totals (using table column instead of client_id)
        if ($selectedClient != 'all') {
            $auditedQuery->where('table', $selectedClient);
            $nonAuditedQuery->where('table', $selectedClient);
            $checkingQuery->where('table', $selectedClient);
            $cancelledQuery->where('table', $selectedClient);
        }

        $audited = number_format(
            $auditedQuery
                ->selectRaw('SUM(CASE WHEN amount > total THEN total ELSE amount END) as adjusted_total')
                ->value('adjusted_total'),
            2, '.', ','
        );
        $nonAuditedTotal = (clone $nonAuditedQuery)->sum('total');
        $nonAuditedPaid = (clone $nonAuditedQuery)->sum('amount');
        
        $nonAudited = number_format($nonAuditedTotal - $nonAuditedPaid, 2, '.', ',');
        $checkingTotal = number_format((clone $checkingQuery)->sum('total'), 2, '.', ',');
        $cancelled = number_format((clone $cancelledQuery)->sum('total'), 2, '.', ',');
        
        return view('user.cashier.transactions-sales', compact('results', 'settings', 'resultsCount', 
                    'startDate', 'endDate', 'audited', 'nonAudited', 'checkingTotal', 'cancelled', 
                    'branches', 'selectedBranch', 'clients', 'selectedClient'));
    }
    public function updatestatus(Request $request){
        $startDate = date('Y-m-d', strtotime($request->startDate));
        $endDate = date('Y-m-d', strtotime($request->endDate . '+1 day'));
        $selectedBranch = $request->branch ?? 'all';
        $slug = $request->slug;
        $status ='';
        
        $trans = DB::table('transactions')->where('slug', $slug)->first();
        if ($trans->sales_type == 1){
            DB::table('transactions')->where('slug', $slug)
            ->update([
                'sales_type' => 0,
                'print' => 1,
            ]);
            $status = '1';
        } else {
            DB::table('transactions')->where('slug', $slug)
            ->update([
                'sales_type' => 1,
                'print' => 0,
            ]);
            $status = '0';
        }

        // Return the current filter parameters to maintain state
        $response = array(
            'status' => $status,
            'branch' => $selectedBranch
        );

        echo json_encode($response);
    }

    public function view(Request $request){
        $id = $request->id;

        // 1. First, get the transaction to know its branch_code
        $tran = DB::table('transactions')
            ->where('local_id', $id)
            ->where('store_code', auth()->user()->store_code)
            ->first();

        if(!$tran) {
            return response()->json([
                'number' => 'N/A',
                'ordered' => '<div class="text-red-500">Transaction not found</div>',
                'url' => '#',
                'printOnly' => '#'
            ]);
        }

        // 2. Get ordered items for this transaction
        $orders = DB::table('ordered')
            ->join('menus', function($join) {
                $join->on('ordered.menu_uuid', '=', 'menus.is_uuid')
                    ->on('ordered.store_code', '=', 'menus.store_code');
            })
            ->select('ordered.quantity', 'menus.name', 'ordered.menu_id', 'ordered.branch_code as ordered_branch')
            ->where('ordered.tran_id', $id)
            ->where('ordered.store_code', auth()->user()->store_code)
            ->get();

        $viewOrder = '';

        if($orders->isEmpty()) {
            $viewOrder .= '<div class="text-red-500">No ordered items found</div>';
        } else {
            // Group orders by branch
            $ordersByBranch = $orders->groupBy('ordered_branch');

            foreach($ordersByBranch as $branchCode => $branchOrders) {
                // Get branch name
                $branchName = DB::table('settings')
                    ->where('store_code', auth()->user()->store_code)
                    ->where('branch_code', $branchCode)
                    ->value('name') ?? $branchCode;

                $viewOrder .= '<div class="mb-4">';
                $viewOrder .= '<h4 class="font-bold text-lg text-blue-600 mb-2">Branch: ' . $branchName . '</h4>';

                // Group items by name
                $groupedItems = $branchOrders->groupBy('name');
                $totalItems = $branchOrders->count();

                foreach($groupedItems as $itemName => $items) {
                    $itemCount = $items->count();
                    $totalQuantity = $items->sum('quantity');
                    $totalAmount = $items->sum('amount');

                    if($itemCount > 1) {
                        // Multiple entries of the same item - show grouped format
                        $viewOrder .= '
                            <div class="flex flex-col mb-2 ml-4">
                                <div class="flex w-full">
                                    <div class="w-1/5 font-bold"></div>
                                    <div class="w-4/5">' . htmlspecialchars($itemName) . '</div>
                                </div>
                                <div class="flex w-full">
                                    <div class="w-1/5 font-bold text-gray-600">' . $itemCount . '/' . $itemCount . '</div>
                                    <div class="w-4/5">' . number_format($totalQuantity, 3) . '</div>
                                </div>
                            </div>';
                    } else {
                        // Single entry - show normally
                        $viewOrder .= '
                            <div class="flex w-full mb-2 ml-4">
                                <div class="w-1/5 font-bold">' . number_format($items->first()->quantity, 3) . 'x</div>
                                <div class="w-4/5">' . htmlspecialchars($itemName) . '</div>
                            </div>';
                    }
                }
                $viewOrder .= '</div>';
            }
        }

        $printUrl = url('/transactions/print/'.$id);
        $printOnlyUrl = url('/transactions/printonly/'.$id);

        $response = [
            'number' => $tran->number,
            'ordered' => $viewOrder,
            'url' => $printUrl,
            'printOnly' => $printOnlyUrl,
        ];

        echo json_encode($response);
    }

    //ken update 08-12-2026 update sa print ng resibo
    public function print($id){
        // Get transaction with related data
        $trans = DB::table('transactions')
            ->select(
                'transactions.*', 
                'tables.name as table_name',
                DB::raw("CONCAT(users.firstname, ' ', users.lastname) as cashier_name")
            )
            ->join('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id');
                    //->where('tables.store_code', '=', auth()->user()->store_code);
            })
            ->leftJoin('users', function($join) {
                $join->on('transactions.cashier', '=', 'users.id')
                    ->where('users.store_code', '=', auth()->user()->store_code);
            })
            ->where('transactions.id', $id)
            ->where('transactions.store_code', auth()->user()->store_code)
            ->first();

        if(!$trans) {
            abort(404, 'Transaction not found');
        }
        // Get payment breakdown for MULTIPLE mode
        $paymentBreakdown = collect();

        if ($trans->mode_of_payment === 'MULTIPLE') {
            $paymentBreakdown = DB::table('payment_logs')
                ->where('tran_id', $trans->local_id)
                ->where('tran_number', $trans->number)
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
        // Get seniors data if exists
        $seniors = DB::table('seniors')
            ->where('tran_id', $trans->local_id)
            ->get();
            
        // Get orders with price from menus table - FILTER BY TRANSACTION DATE
        $orders = DB::table('ordered')
            ->select(
                'ordered.*', 
                'menus.name',
                'menus.item_code',
                'menus.brand_code',
                'brands.name as brand_name',
                'ordered.discount_amount',
                'ordered.food_cost',
                DB::raw("COALESCE(
                    NULLIF(menus.wholesale_price, 0),
                    NULLIF(menus.slab_price, 0),
                    NULLIF(menus.retail_price, 0)
                ) as menu_price"),
                'menus.wholesale_price',
                'menus.slab_price',
                'menus.retail_price',
                'menus.unit',
            )
            ->leftJoin('menus', function($join) {
                $join->on('ordered.menu_uuid', '=', 'menus.is_uuid')
                    ->on('ordered.store_code', '=', 'menus.store_code');
            })
            ->leftJoin('brands', function($join) {
                $join->on('menus.brand_id', '=', 'brands.id');                
            })
            ->where('ordered.tran_id', $trans->local_id)
            ->where('ordered.store_code', auth()->user()->store_code)
            // ADD DATE RANGE FILTER TO MATCH TRANSACTION DATE
            ->whereBetween('ordered.created_at', [
                date('Y-m-d 00:00:00', strtotime($trans->created_at)), 
                date('Y-m-d 23:59:59', strtotime($trans->created_at))
            ])
            ->get();

        // === ALL COMPUTATIONS HERE ===
        
        // Group items by name
        $groupedItems = $orders->groupBy('name');
        
        // Prepare receipt items with all computed data
        $receiptItems = [];
        $subtotal = 0;
        $totaldue = 0;
        $servicecharge = 0;
        $totalDiscount = 0;
        $allUnits = []; // Track all units used
        
        foreach($groupedItems as $name => $group) {
            $totalQuantity = $group->sum('quantity');
            $totalAmount = $group->sum('amount');
            $totalItemDiscount = $group->sum('discount_amount');
            $totalDiscount += $totalItemDiscount;
            $subtotal += $totalAmount;
            $servicecharge += $group->sum('service_charge');
            $totaldue += $totalAmount + $servicecharge;
            $itemCount = $group->count();
            $brandName = $group->first()->brand_name ?? '';
            $brandCode = $group->first()->brand_code ?? '';
            $unit = $group->first()->unit ?? 'kg'; // Get unit from first item
            
            // Store unit for this item
            if (!in_array($unit, $allUnits)) {
                $allUnits[] = $unit;
            }
            
            $quantityArray = $group->pluck('quantity')->toArray();
            $quantityBreakdown = implode('/', array_map(function($qty) {
                return number_format($qty, 3);
            }, $quantityArray));
            
            // Get price type
            $firstItem = $group->first();
            $menuPrice = $firstItem->menu_price ?? 0;
            $priceType = '';
            $priceTypeLabel = '';
            $priceTypeClass = '';
            
            if(isset($firstItem->wholesale_price) && $firstItem->wholesale_price > 0) {
                $priceType = 'wholesale';
                $priceTypeLabel = 'WS';
                $priceTypeClass = 'wholesale';
            } elseif(isset($firstItem->slab_price) && $firstItem->slab_price > 0) {
                $priceType = 'slab';
                $priceTypeLabel = 'SLAB';
                $priceTypeClass = 'slab';
            } elseif(isset($firstItem->retail_price) && $firstItem->retail_price > 0) {
                $priceType = 'retail';
                $priceTypeLabel = 'RTL';
                $priceTypeClass = 'retail';
            }
            
            $receiptItems[] = [
                'name' => $name,
                'brand_name' => $brandName,
                'brand_code' => $brandCode,
                'total_quantity' => $totalQuantity,
                'total_amount' => $totalAmount,
                'total_item_discount' => $totalItemDiscount,
                'item_count' => $itemCount,
                'quantity_breakdown' => $quantityBreakdown,
                'menu_price' => $menuPrice,
                'price_type' => $priceType,
                'price_type_label' => $priceTypeLabel,
                'price_type_class' => $priceTypeClass,
                'wholesale_price' => $firstItem->wholesale_price ?? 0,
                'slab_price' => $firstItem->slab_price ?? 0,
                'retail_price' => $firstItem->retail_price ?? 0,
                'unit' => $unit
            ];
        }

        // Calculate change
        $change = ($trans->amount ?? 0) - ($trans->total ?? 0);
        if ($change < 0) $change = 0;

        // Get settings
        $settings = DB::table('settings')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->first();

        // Determine total unit display
        $totalUnit = !empty($allUnits) ? implode('/', $allUnits) : 'kg';

        // GET THE 2307 VALUE FROM TRANSACTIONS TABLE
        $value2307 = $trans->service_charge ?? 0; // or whatever column name you use for 2307
        $percent2307 = $trans->sc_percent ?? 0; // Get the percentage discount if applicable
        $pwdseniorDiscount = $trans->pwd ?? 0; // Get PWD/Senior discount if applicable
        $numberOfSeniors = $trans->customer_with_discount ?? 0;// Count the number of seniors
        $discount = $trans->ld_value ?? 0; // Get any other discount if applicable
        $percentDiscount = $trans->lime_discount ?? 0; // Get discount percent if applicable
        $overalltotal = $trans->total ?? 0; // Get overall total

        return view('user.cashier.bill', compact(
            'orders', 
            'trans', 
            'settings', 
            'seniors', 
            'change',
            'receiptItems',
            'subtotal',
            'servicecharge',
            'totaldue',
            'totalDiscount',
            'totalUnit',
            'value2307',
            'percent2307',
            'pwdseniorDiscount',
            'discount',
            'percentDiscount',
            'numberOfSeniors',
            'overalltotal',
            'paymentBreakdown'
        ));
    }
    //end of update ng print resibo 08-12-2026

    public function printonly($id){
     
        $trans = DB::table('transactions')
                    ->select('transactions.*', 'tables.name as table_name','users.name')
                    ->join('tables', 'transactions.table', '=', 'tables.id')
                    ->join('users', 'transactions.cashier', '=', 'users.id')
                    ->where('transactions.id', $id)
                    ->first();

        $seniors = DB::table('seniors')
                ->where('tran_id', $trans->id)
                ->get();
                
        $orders = DB::table('ordered')
                    ->select('ordered.*', 'menus.name')
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->where('tran_id', $trans->id)
                    ->get();


        $payments = DB::table('payment_orders')->where('tran_id', $trans->id)->get();

        $settings = DB::table('settings')->where('id', 2)->first();

        $orderdineincount = DB::table('ordered') 
        ->where('tran_id', $trans->id)
        ->where('item_takeout','0')
        ->count();

        $ordertakeoutcount = DB::table('ordered') 
                ->where('tran_id', $trans->id)
                ->where('item_takeout','1')
                ->count();

        return view('user.cashier.bill', compact('orders', 'trans', 'settings', 'seniors', 'payments','ordertakeoutcount','orderdineincount'));
    }
    
    public function returnTable(Request $request)
    {
        
        $slug = $request->slug;

        $trans = DB::table('transactions')->where('slug', $slug)->first();
        $table = DB::table('tables')->where('id', $trans->table)->first();
                
        // 3-16 harold changes
        if ($table->status == 0) {
            DB::table('transactions')->where('slug', $slug)->update([
                'status' => 'UNPAID',
                'order_status' => 'PREPARING',
                'mode_of_payment' => NULL,
                'amount' => 0,
            ]);
            DB::table('payment_orders')->where('tran_id', $trans->id)->delete();   
            
             // 3-16 harold end

            DB::table('tables')->where('id', $trans->table)->update([
                'status' => 1,
            ]);


            // Log the event inline
            $log = new Log();
            $log->trans_id = $trans->id;
            $log->classification = 'Transactions';
            $log->info = 'Return Order to Table';
            $log->user_id = auth()->id();
            $log->event = 'Add';
            $log->save();
            // $status = '1';
            //echo('sarado ang pinto');
            return response()->json(['message' => 'Transaction updated successfully!']);
        } else {
            $transCount = DB::table('transactions')->where('table', $trans->table)->where('status', 'UNPAID')->count();
            if ($transCount > 0 || $table->status == 1) {
                return response()->json(['error' => "Unable to process: There is still a PENDING TRANSACTION that needs to be processed or the TABLE IS STILL OCCUPIED."]);
            } else {
                DB::table('transactions')->where('slug', $slug)->update([
                    'status' => 'UNPAID',
                    'order_status' => 'PREPARING',
                ]);

                $log = new Log();
                $log->trans_id = $trans->id;
                $log->classification = 'Transactions';
                $log->info = 'Return Order to Table';
                $log->user_id = auth()->id();
                $log->event = 'Add';
                $log->save();

                return response()->json(['message' => 'Transaction updated successfully!']);
            }
            
        }

        
            
            
        

        

        

        // Return a JSON response
        // return response()->json([
        //     'status' => $status,
        // ]);
        
    }
    public function bulkApprove(Request $request)
    {
        return $this->approve($request);
    }

    public function toggleApprovalCart(Request $request)
    {
        $slug = $request->slug;
        $selected = (bool) $request->boolean('selected');

        if (!$slug) {
            return response()->json(['success' => false, 'message' => 'No transaction selected']);
        }

        $transaction = DB::table('transactions')
            ->where('slug', $slug)
            ->where('store_code', auth()->user()->store_code)
            ->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction not found']);
        }

        $storeCode = $transaction->store_code ?? auth()->user()->store_code;

        if ($selected) {
            if (Schema::hasTable('approval_cart')) {
                $payload = $this->buildApprovalCartPayload($transaction);
                $payload['id'] = (int) (DB::table('approval_cart')->max('id') ?? 0) + 1;
                DB::table('approval_cart')
                    ->where('slug', $slug)
                    ->where(function ($query) use ($storeCode) {
                        $query->where('store_code', $storeCode)
                            ->orWhereNull('store_code');
                    })
                    ->delete();
                DB::table('approval_cart')->insert($payload);
            }

            return response()->json(['success' => true, 'message' => 'Transaction added to approval cart']);
        }

        if (Schema::hasTable('approval_cart')) {
            DB::table('approval_cart')
                ->where('slug', $slug)
                ->where(function ($query) use ($storeCode) {
                    $query->where('store_code', $storeCode)
                        ->orWhereNull('store_code');
                })
                ->delete();
        }

        return response()->json(['success' => true, 'message' => 'Transaction removed from approval cart']);
    }

    public function approve(Request $request)
    {
        $slugs = $request->slugs ?? [];

        if (!$slugs && $request->slug) {
            $slugs = $request->slug;
        }

        if (is_string($slugs)) {
            $slugs = [$slugs];
        }

        if (empty($slugs) || !is_array($slugs)) {
            return response()->json(['success' => false, 'error' => 'No transactions selected']);
        }

        $approvedCount = 0;

        DB::beginTransaction();
        try {
            \Log::info('approve() called', ['slugs_raw' => $request->slugs ?? null, 'slug_single' => $request->slug ?? null, 'user' => auth()->id()]);
            foreach($slugs as $slug){
                \Log::info('processing slug', ['slug' => $slug]);

                $trans = DB::table('transactions')
                    ->where('slug', $slug)
                    ->where('is_approved', 0)
                    ->where('store_code', auth()->user()->store_code)
                    ->first();

                if(!$trans) continue;

                \Log::info('transaction found for approval', ['slug' => $slug, 'trans_id' => $trans->id ?? null, 'store_code' => $trans->store_code ?? null]);

                $exists = DB::table('sale_journal')
                    ->where('slug', $trans->slug)
                    ->where('store_code', $trans->store_code)
                    ->exists();

                if($exists) continue;

                $insert = $this->buildApprovalCartPayload($trans);
                $insert['approved_by'] = auth()->id();

                $insertId = DB::table('sale_journal')->insertGetId($insert);
                \Log::info('inserted sale_journal', ['insert_id' => $insertId, 'slug' => $slug]);

                if (Schema::hasTable('approved_logs')) {
                    $approvedLogExists = DB::table('approved_logs')
                        ->where('trans_id', $trans->id)
                        ->where('is_deleted', 0)
                        ->exists();

                    if (!$approvedLogExists) {
                        DB::table('approved_logs')->insert([
                            'trans_id' => $trans->id,
                            'trans_number' => $trans->number,
                            'approve_by' => auth()->id(),
                            'approved_at' => now(),
                            'store_code' => $trans->store_code,
                            'branch_code' => $trans->branch_code,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if (Schema::hasTable('approval_cart')) {
                    DB::table('approval_cart')
                        ->where('slug', $trans->slug)
                        ->where('store_code', $trans->store_code ?? auth()->user()->store_code)
                        ->delete();
                }

                // mark transaction as approved
                $updated = DB::table('transactions')
                    ->where('slug', $trans->slug)
                    ->update([
                        'is_approved' => 1,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                \Log::info('transaction updated as approved', ['slug' => $slug, 'updated_rows' => $updated]);

                $approvedCount++;
                
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction approved to Sale Journal successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('SaleJournal approve error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to approve transactions']);
        }
    }    
    // ken export to excel 08-12-2026 (as of now, ongoing dev to change table to sales_report_transaction)
    public function export(Request $request)
    {
        // Get filter parameters
        $startDate = $request->has('startDate') 
            ? date('Y-m-d', strtotime($request->get('startDate'))) 
            : date('Y-m-d');
        
        $endDate = $request->has('endDate') 
            ? date('Y-m-d', strtotime($request->get('endDate'))) 
            : date('Y-m-d');
        
        $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
        $selectedBranch = $request->get('branch', 'all');
        $selectedClient = $request->get('client', 'all');

        // Get branches and clients for filter display
        $branches = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        $clients = DB::table('tables')
            //->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Get all transactions within date range with proper joins
        $transactions = DB::table('transactions')
            ->select(
                'transactions.*',
                'transactions.number as nn', // Alias for receipt number
                'tables.name as table_name',
                DB::raw("CONCAT(users.firstname, ' ', users.lastname) as cashier_name")
            )
            ->join('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id');
            })
            ->leftJoin('users', function($join) {
                $join->on('transactions.cashier', '=', 'users.id')
                    ->where('users.store_code', '=', auth()->user()->store_code);
            })
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->when($selectedBranch != 'all', function($query) use ($selectedBranch) {
                return $query->where('transactions.branch_code', $selectedBranch);
            })
            ->when($selectedClient != 'all', function($query) use ($selectedClient) {
                return $query->where('transactions.table', $selectedClient);
            })
            ->orderBy('transactions.id', 'desc')
            ->get();

        // Process each transaction to get ordered items with proper filtering
        $processedTransactions = $transactions->map(function($trans) {
            // Ensure nn is set
            if (!isset($trans->nn) || empty($trans->nn)) {
                $trans->nn = $trans->number ?? 'N/A';
            }

            // Get orders with price from menus table - FILTER BY TRANSACTION DATE
            $orders = DB::table('ordered')
                ->select(
                    'ordered.*', 
                    'menus.name as menu_name',
                    'menus.item_code',
                    'menus.brand_code',
                    'brands.name as brand_name',
                    'ordered.discount_amount as ordered_discount_amount',
                    'ordered.food_cost as ordered_food_cost',
                    'ordered.quantity as ordered_quantity',
                    'ordered.amount as ordered_amount',
                    DB::raw("COALESCE(
                        NULLIF(menus.wholesale_price, 0),
                        NULLIF(menus.slab_price, 0),
                        NULLIF(menus.retail_price, 0)
                    ) as menu_price"),
                    'menus.wholesale_price',
                    'menus.slab_price',
                    'menus.retail_price',
                    'menus.unit',
                )
                ->leftJoin('menus', function($join) {
                    $join->on('ordered.menu_uuid', '=', 'menus.is_uuid')
                        ->on('ordered.store_code', '=', 'menus.store_code');
                })
                ->leftJoin('brands', function($join) {
                    $join->on('menus.brand_id', '=', 'brands.id');                
                })
                ->where('ordered.tran_id', $trans->local_id)
                ->where('ordered.store_code', auth()->user()->store_code)
                // ADD DATE RANGE FILTER TO MATCH TRANSACTION DATE
                ->whereBetween('ordered.created_at', [
                    date('Y-m-d 00:00:00', strtotime($trans->created_at)), 
                    date('Y-m-d 23:59:59', strtotime($trans->created_at))
                ])
                ->get();

            // Get payment breakdown for MULTIPLE mode
            $paymentBreakdown = collect();
            if ($trans->mode_of_payment === 'MULTIPLE') {
                $paymentBreakdown = DB::table('payment_logs')
                    ->where('tran_id', $trans->local_id)
                    ->where('tran_number', $trans->number)
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

            // Get seniors data if exists
            $seniors = DB::table('seniors')
                ->where('tran_id', $trans->local_id)
                ->get();

            // Group items by name (like in print method)
            $groupedItems = $orders->groupBy('menu_name');
            
            $receiptItems = [];
            $subtotal = 0;
            $totaldue = 0;
            $servicecharge = 0;
            $totalDiscount = 0;
            $allUnits = [];
            
            foreach($groupedItems as $name => $group) {
                $totalQuantity = $group->sum('ordered_quantity');
                $totalAmount = $group->sum('ordered_amount');
                $totalItemDiscount = $group->sum('ordered_discount_amount');
                $totalDiscount += $totalItemDiscount;
                $subtotal += $totalAmount;
                $servicecharge += $group->sum('service_charge');
                $totaldue += $totalAmount + $servicecharge;
                $itemCount = $group->count();
                $brandName = $group->first()->brand_name ?? '';
                $brandCode = $group->first()->brand_code ?? '';
                $unit = $group->first()->unit ?? 'kg';
                
                if (!in_array($unit, $allUnits)) {
                    $allUnits[] = $unit;
                }
                
                $quantityArray = $group->pluck('ordered_quantity')->toArray();
                $quantityBreakdown = implode('/', array_map(function($qty) {
                    return number_format($qty, 3);
                }, $quantityArray));
                
                $firstItem = $group->first();
                $menuPrice = $firstItem->menu_price ?? 0;
                $priceType = '';
                $priceTypeLabel = '';
                $priceTypeClass = '';
                
                if(isset($firstItem->wholesale_price) && $firstItem->wholesale_price > 0) {
                    $priceType = 'wholesale';
                    $priceTypeLabel = 'WS';
                    $priceTypeClass = 'wholesale';
                } elseif(isset($firstItem->slab_price) && $firstItem->slab_price > 0) {
                    $priceType = 'slab';
                    $priceTypeLabel = 'SLAB';
                    $priceTypeClass = 'slab';
                } elseif(isset($firstItem->retail_price) && $firstItem->retail_price > 0) {
                    $priceType = 'retail';
                    $priceTypeLabel = 'RTL';
                    $priceTypeClass = 'retail';
                }
                
                $receiptItems[] = (object) [
                    'name' => $name,
                    'brand_name' => $brandName,
                    'brand_code' => $brandCode,
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'total_item_discount' => $totalItemDiscount,
                    'item_count' => $itemCount,
                    'quantity_breakdown' => $quantityBreakdown,
                    'menu_price' => $menuPrice,
                    'price_type' => $priceType,
                    'price_type_label' => $priceTypeLabel,
                    'price_type_class' => $priceTypeClass,
                    'wholesale_price' => $firstItem->wholesale_price ?? 0,
                    'slab_price' => $firstItem->slab_price ?? 0,
                    'retail_price' => $firstItem->retail_price ?? 0,
                    'unit' => $unit,
                    'ordered_quantity' => $totalQuantity,
                    'ordered_amount' => $totalAmount,
                    'ordered_discount_amount' => $totalItemDiscount,
                    'ordered_food_cost' => $group->sum('ordered_food_cost'),
                    'item_code' => $firstItem->item_code ?? 'N/A',
                    'menu_name' => $name,
                ];
            }

            // Calculate change
            $change = ($trans->amount ?? 0) - ($trans->total ?? 0);
            if ($change < 0) $change = 0;

            // Determine total unit display
            $totalUnit = !empty($allUnits) ? implode('/', $allUnits) : 'kg';

            // Add all computed data to transaction object
            $trans->receiptItems = $receiptItems;
            $trans->ordered_items = collect($receiptItems); // For compatibility with export class
            $trans->subtotal = $subtotal;
            $trans->servicecharge = $servicecharge;
            $trans->totaldue = $totaldue;
            $trans->totalDiscount = $totalDiscount;
            $trans->totalUnit = $totalUnit;
            $trans->change = $change;
            $trans->paymentBreakdown = $paymentBreakdown;
            $trans->seniors = $seniors;
            $trans->value2307 = $trans->service_charge ?? 0;
            $trans->percent2307 = $trans->sc_percent ?? 0;
            $trans->pwdseniorDiscount = $trans->pwd ?? 0;
            $trans->numberOfSeniors = $trans->customer_with_discount ?? 0;
            $trans->discount = $trans->ld_value ?? 0;
            $trans->percentDiscount = $trans->lime_discount ?? 0;
            $trans->overalltotal = $trans->total ?? 0;
            $trans->amount = $trans->total ?? 0; // For compatibility
            $trans->date = $trans->created_at; // For compatibility
            $trans->tstatus = $trans->status ?? 'N/A'; // For compatibility

            return $trans;
        });

        // Get the current user's full name for "Exported By"
        $exportedBy = auth()->user()->firstname . ' ' . auth()->user()->lastname;

        // Generate filename with date and filters
        $filename = 'Sales_Detail_Report_';
        $filename .= date('Y-m-d');
        if ($selectedBranch != 'all') {
            $branchName = $branches->firstWhere('branch_code', $selectedBranch)->name ?? '';
            $filename .= '_' . str_replace(' ', '_', $branchName);
        }
        $filename .= '.xlsx';

        // Return Excel download
        return Excel::download(
            new TransactionsExport(
                $processedTransactions, 
                $startDate, 
                $endDate, 
                $selectedBranch, 
                $selectedClient, 
                $branches, 
                $clients, 
                $exportedBy
            ),
            $filename
        );
    }
    // END NG EXPORT EXCEL 8-12-2026
}
