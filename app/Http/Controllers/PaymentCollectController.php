<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class PaymentCollectController extends Controller
{
    /**
     * =========================================================
     *  SALE PAYMENT
     *  Extract of the payment summary / MOP breakdown logic
     *  from ReportController::generate() -> category = 'dailySR'
     * =========================================================
     *
     * Returns:
     *  - $dailySRRows          : sales rows with per-MOP payment breakdown
     *  - $dailySRPaymentColumns: ordered MOP columns
     *  - $dailyTotals          : totals for sales side
     */
    public function salePayment(Request $request)
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date', 'after_or_equal:start'],
        ]);

        $startDate = date('Y-m-d 00:00:00', strtotime($request->start));
        $endDate   = date('Y-m-d 00:00:00', strtotime($request->end . ' +1 day'));

        $user         = Auth::user();
        $userBranch   = $this->resolveUserBranch($request, $user);
        $resultsCount = 0;

        // ===== 1. GET MOP COLUMNS =====
        $mopColumn = Schema::hasTable('mode_of_payment')
            ? (Schema::hasColumn('mode_of_payment', 'mop_name') ? 'mop_name' : 'name')
            : null;

        $dailySRPaymentColumns = [];
        if ($userBranch && $mopColumn) {
            $dailySRPaymentColumns = DB::table('mode_of_payment')
                ->where('store_code', $userBranch)
                ->where('is_deleted', 0)
                ->orderBy('id', 'asc')
                ->pluck($mopColumn)
                ->map(fn($value) => strtoupper(preg_replace('/\s*\/\s*/', ' / ', trim((string) $value))))
                ->filter()
                ->values()
                ->toArray();
        }

        if (empty($dailySRPaymentColumns)) {
            $dailySRPaymentColumns = ['CASH'];
        }

        // ===== 2. PAYMENT SUMMARY QUERY =====
        $paymentSummaryQuery = DB::table('payment_logs as pl')
            ->select('pl.tran_id', DB::raw('SUM(pl.amount) as paid_amount'))
            ->where('pl.store_code', $userBranch)
            ->where('pl.created_at', '>=', $startDate)
            ->where('pl.created_at', '<', $endDate)
            ->where('pl.is_deleted', '!=', 1);

        if (Schema::hasColumn('payment_logs', 'mop_id') && Schema::hasTable('mode_of_payment')) {
            $paymentSummaryQuery->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                ->addSelect(DB::raw('UPPER(TRIM(COALESCE(mop.name, pl.label, "CASH"))) as mop'))
                ->groupBy('pl.tran_id', DB::raw('UPPER(TRIM(COALESCE(mop.name, pl.label, "CASH")))'));
        } elseif (Schema::hasColumn('payment_logs', 'mop')) {
            $paymentSummaryQuery->addSelect(DB::raw('UPPER(TRIM(pl.mop)) as mop'))
                ->groupBy('pl.tran_id', DB::raw('UPPER(TRIM(pl.mop))'));
        } elseif (Schema::hasColumn('payment_logs', 'label')) {
            $paymentSummaryQuery->addSelect(DB::raw('UPPER(TRIM(COALESCE(pl.label, "CASH"))) as mop'))
                ->groupBy('pl.tran_id', DB::raw('UPPER(TRIM(COALESCE(pl.label, "CASH")))'));
        } else {
            $paymentSummaryQuery->addSelect(DB::raw('"CASH" as mop'))
                ->groupBy('pl.tran_id');
        }

        $paymentSummaryRows = $paymentSummaryQuery->get()->groupBy('tran_id');

        // ===== 3. MAIN SALES QUERY =====
        $salesQuery = DB::table('transactions as t')
            ->select(
                't.id', 't.local_id',
                't.number as ref',
                DB::raw("
                    COALESCE(
                        t.payor_name,
                        t.payor_number,
                        tables.name,
                        CONCAT('Table ', t.table),
                        'Walk-in'
                    ) as customer_name
                "),
                DB::raw('COALESCE(t.total, 0) as amount'),
                DB::raw('COALESCE(t.service_charge, 0) as service_charge'),
                't.status',
                't.mode_of_payment',
                't.order_status',
                't.store_code',
                't.branch',
                't.amount as amount_paid',
                't.total as total_amount',
                't.table',
                'tables.name as table_name',
                DB::raw('"SALES" as report_type')
            )
            ->leftJoin('tables', 't.table', '=', 'tables.id')
            ->leftJoinSub(
                DB::table('payment_logs as pl')
                    ->select('pl.tran_id', DB::raw('SUM(pl.amount) as paid_amount'))
                    ->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                    ->where('pl.store_code', $userBranch)
                    ->where('pl.created_at', '>=', $startDate)
                    ->where('pl.created_at', '<', $endDate)
                    ->groupBy('pl.tran_id'),
                'payment_totals',
                fn($join) => $join->on('payment_totals.tran_id', '=', 't.local_id')
            )
            ->selectRaw('COALESCE(payment_totals.paid_amount, 0) as paid_amount')
            ->selectRaw('GREATEST(COALESCE(t.total, 0) + COALESCE(t.service_charge, 0) - COALESCE(payment_totals.paid_amount, 0), 0) as ar_balance')
            ->selectRaw('CASE WHEN GREATEST(COALESCE(t.total, 0) + COALESCE(t.service_charge, 0) - COALESCE(payment_totals.paid_amount, 0), 0) > 0 THEN 1 ELSE 0 END as highlight_ar_in_sales')
            ->where('t.created_at', '>=', $startDate)
            ->where('t.created_at', '<', $endDate)
            ->where('t.order_status', '!=', 'CANCELLED')
            ->where('t.store_code', $userBranch);

        $dailySRRows = $salesQuery->orderBy('t.id', 'desc')->get();

        // ===== 4. PROCESS RESULTS =====
        $dailySRRows = $dailySRRows->map(function ($row) use ($paymentSummaryRows, $dailySRPaymentColumns) {
            $row->display_number   = $row->ref;
            $row->row_amount       = $row->amount ?? 0;
            $row->ar_display_amount= $row->ar_balance ?? 0;
            $row->form2307         = $row->service_charge ?? 0;
            $row->status           = $row->status ?? '';

            $row->is_ar_transaction = ($row->order_status === 'UNPAID' || (float) $row->ar_balance > 0);
            $row->payments = array_fill_keys($dailySRPaymentColumns, 0);

            $rowPayments = $paymentSummaryRows[$row->local_id] ?? collect();

            foreach ($rowPayments as $paymentRow) {
                $paymentColumn = strtoupper(preg_replace('/\s*\/\s*/', ' / ', trim((string) ($paymentRow->mop ?? ''))));
                if ($paymentColumn === '') {
                    $paymentColumn = 'CASH';
                }
                if (!array_key_exists($paymentColumn, $row->payments)) {
                    $row->payments[$paymentColumn] = 0;
                }
                $row->payments[$paymentColumn] += (float) ($paymentRow->paid_amount ?? 0);
            }

            return $row;
        });

        // ===== 5. ADD MISSING PAYMENT COLUMNS =====
        foreach ($dailySRRows as $row) {
            foreach (array_keys($row->payments ?? []) as $paymentColumn) {
                if (!in_array($paymentColumn, $dailySRPaymentColumns, true)) {
                    $dailySRPaymentColumns[] = $paymentColumn;
                }
            }
        }

        $preferredOrder = ['CASH', 'GCASH', 'MAYA', 'BANK TRANSFER', 'CHECK', 'ONLINE BANKING'];
        $orderedColumns = [];
        foreach ($preferredOrder as $preferredColumn) {
            if (in_array($preferredColumn, $dailySRPaymentColumns, true)) {
                $orderedColumns[] = $preferredColumn;
            }
        }
        foreach ($dailySRPaymentColumns as $paymentColumn) {
            if (!in_array($paymentColumn, $orderedColumns, true)) {
                $orderedColumns[] = $paymentColumn;
            }
        }
        $dailySRPaymentColumns = array_values(array_diff($orderedColumns, ['MULTIPLE']));

        // ===== 6. CALCULATE TOTALS =====
        $dailyTotals = [
            'amount'         => (float) ($dailySRRows->sum('amount') + $dailySRRows->sum('service_charge')),
            'service_charge' => (float) $dailySRRows->sum('service_charge'),
            'ar'             => (float) $dailySRRows->sum('ar_balance'),
            'payments'       => [],
        ];

        foreach ($dailySRPaymentColumns as $paymentColumn) {
            $dailyTotals['payments'][$paymentColumn] = (float) $dailySRRows->sum(
                fn($row) => (float) ($row->payments[$paymentColumn] ?? 0)
            );
        }

        $resultsCount = $dailySRRows->count();

        return response()->json([
            'dailySRRows'           => $dailySRRows,
            'dailySRPaymentColumns' => $dailySRPaymentColumns,
            'dailyTotals'           => $dailyTotals,
            'resultsCount'          => $resultsCount,
        ]);
    }

    /**
     * =========================================================
     *  AR COLLECTION
     *  Extract of the AR collection logic from
     *  ReportController::generate() -> category = 'dailySR'
     * =========================================================
     *
     * Returns:
     *  - $arCollectionRows        : grouped AR collections per ref
     *  - $dailySRPaymentColumns   : MOP columns (updated with AR MOPs)
     *  - $arTotals                : totals for AR side
     */
    public function arCollection(Request $request)
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date', 'after_or_equal:start'],
        ]);

        $startDate = date('Y-m-d 00:00:00', strtotime($request->start));
        $endDate   = date('Y-m-d 00:00:00', strtotime($request->end . ' +1 day'));

        $user       = Auth::user();
        $userBranch = $this->resolveUserBranch($request, $user);

        // Seed the MOP columns with what exists in mode_of_payment
        $mopColumn = Schema::hasTable('mode_of_payment')
            ? (Schema::hasColumn('mode_of_payment', 'mop_name') ? 'mop_name' : 'name')
            : null;

        $dailySRPaymentColumns = [];
        if ($userBranch && $mopColumn) {
            $dailySRPaymentColumns = DB::table('mode_of_payment')
                ->where('store_code', $userBranch)
                ->where('is_deleted', 0)
                ->orderBy('id', 'asc')
                ->pluck($mopColumn)
                ->map(fn($value) => strtoupper(preg_replace('/\s*\/\s*/', ' / ', trim((string) $value))))
                ->filter()
                ->values()
                ->toArray();
        }

        if (empty($dailySRPaymentColumns)) {
            $dailySRPaymentColumns = ['CASH'];
        }

        // ===== AR COLLECTION QUERY =====
        $arCollectionQuery = DB::table('payment_logs as pl')
            ->select(
                'pl.id as payment_id',
                'pl.tran_number',
                'pl.total as total_amount_paid',
                'pl.amount as payment_amount',
                'pl.created_at as payment_date',
                't.id as transaction_id',
                't.number as ref',
                't.total as amount',
                't.service_charge',
                't.status',
                't.order_status',
                't.store_code',
                't.branch',
                DB::raw("
                    COALESCE(
                        t.payor_name,
                        t.payor_number,
                        tables.name,
                        CONCAT('Table ', t.table),
                        'Walk-in'
                    ) as customer_name
                "),
                't.table',
                'tables.name as table_name',
                DB::raw('"AR_COLLECTION" as report_type')
            )
            ->leftJoin('transactions as t', function ($join) use ($userBranch) {
                $join->on('pl.tran_id', '=', 't.local_id')
                    ->where('t.store_code', $userBranch);
            })
            ->leftJoin('tables', 't.table', '=', 'tables.id')
            ->where('pl.store_code', $userBranch)
            ->where('pl.created_at', '>=', $startDate)
            ->where('pl.created_at', '<', $endDate)
            ->where('pl.type', 1)
            ->where('pl.is_deleted', '!=', 1)
            ->where('pl.amount', '>', 0);

        $arCollectionRows = $arCollectionQuery
            ->orderBy('pl.created_at', 'desc')
            ->get();

        // Process AR Collection rows
        $arCollectionRows = $arCollectionRows->map(function ($row) use ($dailySRPaymentColumns) {
            $totalAmount    = (float) ($row->amount ?? 0) + (float) ($row->service_charge ?? 0);
            $arPaymentAmount= $row->payment_amount ?? 0;
            $arTotalPaid    = $row->total_amount_paid ?? 0;
            $remarks        = $row->payment_date ?? '';

            $row->display_number     = $row->ref ?? $row->tran_number;
            $row->row_amount         = $arTotalPaid;
            $row->total_amount       = $totalAmount;
            $row->ar_display_amount  = 0;
            $row->paid_amount        = $arPaymentAmount;
            $row->form2307           = $row->service_charge ?? 0;
            $row->is_ar_transaction  = true;
            $row->status             = $remarks;

            $paymentMethod = DB::table('payment_logs as pl')
                ->select(DB::raw('UPPER(TRIM(COALESCE(mop.name, pl.label, "CASH"))) as mop'))
                ->leftJoin('mode_of_payment as mop', 'pl.mop_id', '=', 'mop.id')
                ->where('pl.id', $row->payment_id)
                ->where('pl.store_code', $row->store_code)
                ->where('pl.type', 1)
                ->first();

            $paymentColumn = strtoupper(preg_replace('/\s*\/\s*/', ' / ', trim((string) ($paymentMethod->mop ?? 'CASH'))));
            if ($paymentColumn === '') {
                $paymentColumn = 'CASH';
            }

            $row->payments = array_fill_keys($dailySRPaymentColumns, 0);

            if (!array_key_exists($paymentColumn, $row->payments)) {
                $row->payments[$paymentColumn] = 0;
            }

            $row->payments[$paymentColumn] = $arPaymentAmount;

            return $row;
        });

        // ===== MERGE ROWS THAT SHARE THE SAME REF/TRAN_NUMBER =====
        $arCollectionRows = $arCollectionRows
            ->groupBy(function ($row) {
                return $row->display_number ?? $row->ref ?? $row->tran_number;
            })
            ->map(function ($group) use ($dailySRPaymentColumns) {
                $merged = clone $group->first();

                $merged->row_amount  = (float) $group->sum('row_amount');
                $merged->paid_amount = (float) $group->sum('paid_amount');
                $merged->form2307    = (float) $group->sum('form2307');

                $merged->ar_display_amount = max(0, $merged->row_amount - $merged->paid_amount);

                $mergedPayments = array_fill_keys($dailySRPaymentColumns, 0);
                foreach ($group as $groupRow) {
                    foreach ($groupRow->payments as $col => $amt) {
                        if (!array_key_exists($col, $mergedPayments)) {
                            $mergedPayments[$col] = 0;
                        }
                        $mergedPayments[$col] += (float) $amt;
                    }
                }
                $merged->payments = $mergedPayments;

                return $merged;
            })
            ->values();

        // Add missing payment columns from AR collections
        foreach ($arCollectionRows as $row) {
            foreach (array_keys($row->payments ?? []) as $paymentColumn) {
                if (!in_array($paymentColumn, $dailySRPaymentColumns, true)) {
                    $dailySRPaymentColumns[] = $paymentColumn;
                }
            }
        }

        // Re-order payment columns after adding AR columns
        $preferredOrder = ['CASH', 'GCASH', 'MAYA', 'BANK TRANSFER', 'CHECK', 'ONLINE BANKING'];
        $orderedColumns = [];
        foreach ($preferredOrder as $preferredColumn) {
            if (in_array($preferredColumn, $dailySRPaymentColumns, true)) {
                $orderedColumns[] = $preferredColumn;
            }
        }
        foreach ($dailySRPaymentColumns as $paymentColumn) {
            if (!in_array($paymentColumn, $orderedColumns, true)) {
                $orderedColumns[] = $paymentColumn;
            }
        }
        $dailySRPaymentColumns = array_values(array_diff($orderedColumns, ['MULTIPLE']));

        // ===== AR COLLECTION TOTALS =====
        $arTotals = [
            'amount'         => (float) ($arCollectionRows->sum('row_amount') + $arCollectionRows->sum('form2307')),
            'service_charge' => (float) $arCollectionRows->sum('form2307'),
            'ar'             => (float) $arCollectionRows->sum('ar_display_amount'),
            'payments'       => [],
        ];

        foreach ($dailySRPaymentColumns as $paymentColumn) {
            $arTotals['payments'][$paymentColumn] = (float) $arCollectionRows->sum(
                fn($row) => (float) ($row->payments[$paymentColumn] ?? 0)
            );
        }

        return response()->json([
            'arCollectionRows'      => $arCollectionRows,
            'dailySRPaymentColumns' => $dailySRPaymentColumns,
            'arTotals'              => $arTotals,
        ]);
    }

    /**
     * Resolve the branch (store_code) the request is scoped to,
     * mirroring the logic in ReportController::generate().
     */
    private function resolveUserBranch(Request $request, $user): ?string
    {
        if ($user->role == 1) {
            $selectedBranch = $request->input('branch');

            if (!$selectedBranch) {
                abort(422, 'Please select a branch.');
            }

            $branchExists = DB::table('settings')
                ->where('store_code', $selectedBranch)
                ->where('is_deleted', 0)
                ->exists();

            if (!$branchExists) {
                abort(404, 'Selected branch was not found.');
            }

            return $selectedBranch;
        }

        return $user->store_code;
    }

    public function salePaymentPage()
    {
        $user = Auth::user();

        $branchs = DB::table('settings')
            ->orderBy('name', 'asc')
            ->where('is_deleted', 0)
            ->when($user->role != 1, fn($q) => $q->where('store_code', $user->store_code))
            ->get();

        return view('admin.accounting-reports.sale-payment', compact('branchs'));
    }

    public function arCollectionPage()
    {
        $user = Auth::user();

        $branchs = DB::table('settings')
            ->orderBy('name', 'asc')
            ->where('is_deleted', 0)
            ->when($user->role != 1, fn($q) => $q->where('store_code', $user->store_code))
            ->get();

        return view('admin.accounting-reports.ar-collection', compact('branchs'));
    }  
}