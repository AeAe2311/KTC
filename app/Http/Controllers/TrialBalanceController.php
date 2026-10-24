<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrialBalanceController extends Controller
{
    protected array $priorityAccounts = ['cash on hand', 'cash on bank', 'Trade Receivables', 'Cheque in Transit', 'Revolving Funds', 'Inventories', 'sales', 'Cost of Goods Sold'];

    protected bool $cumulativeOpening = false;

    /* =====================================================================
     *  INDEX
     * ===================================================================== */
    public function index(Request $request)
    {
        [$selectedMonth, $selectedYear, $fromDate, $toDate] = $this->resolvePeriod($request);

        $search              = $request->input('search');
        $dimensionA          = $request->input('dimension_a', 'all');
        $dimensionB          = $request->input('dimension_b', 'all');
        $showResults         = $request->filled('generate');
        $includeZeroBalances = $request->boolean('include_zero_balances');
        $detailByAccount     = $request->boolean('detail_by_account');
        $showClosed          = $request->boolean('show_closed');

        $trialBalances = [];
        $totals        = $this->emptyTotals();

        if ($showResults) {
            $trialBalances = $this->buildTrialBalances(
                $search, $dimensionA, $dimensionB, $showClosed,
                $fromDate, $toDate, $includeZeroBalances
            );
            $totals = $this->calculateTotals($trialBalances);
        }

        $currentYear = (int) date('Y');
        $years = range($currentYear - 5, $currentYear + 1);
        rsort($years);

        $months = [
            1  => 'January',
            2  => 'February',
            3  => 'March',
            4  => 'April',
            5  => 'May',
            6  => 'June',
            7  => 'July',
            8  => 'August',
            9  => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        return view('admin.reports.trial_balance', compact(
            'fromDate', 'toDate', 'selectedMonth', 'selectedYear',
            'months', 'years', 'search', 'dimensionA', 'dimensionB',
            'showResults', 'includeZeroBalances', 'detailByAccount',
            'showClosed', 'trialBalances', 'totals'
        ));
    }

    /* =====================================================================
     *  SHARED HELPERS
     * ===================================================================== */
    protected function resolvePeriod(Request $request): array
    {
        $month = $request->input('month');
        $year  = $request->input('year');

        if ($month && $year) {
            [$fromDate, $toDate] = $this->monthRange($month, $year);
            return [$month, $year, $fromDate, $toDate];
        }

        return [$month, $year, null, null];
    }

    protected function monthRange($month, $year): array
    {
        $first = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';

        return [
            date('Y-m-01', strtotime($first)),
            date('Y-m-t', strtotime($first)),
        ];
    }

    protected function emptyTotals(): array
    {
        return [
            'brought_forward_debit'  => 0,
            'brought_forward_credit' => 0,
            'beginning_balance'      => 0,
            'period_debit'           => 0,
            'period_credit'          => 0,
            'balance_debit'          => 0,
            'balance_credit'         => 0,
            'balance'                => 0,
        ];
    }

    /* =====================================================================
     *  CHART OF ACCOUNT CATEGORY → NORMAL BALANCE
     * ===================================================================== */
    protected function getChartCategoryMap(): array
    {
        static $cache = null;

        if ($cache === null) {
            $cache = DB::table('chart_categories')
                ->pluck('name', 'id')
                ->map(fn ($name) => strtolower(trim((string) $name)))
                ->toArray();
        }

        return $cache;
    }

    protected function getNormalBalance(int $chartCategoryId): string
    {
        $map  = $this->getChartCategoryMap();
        $name = $map[$chartCategoryId] ?? '';

        if (str_contains($name, 'asset') || str_contains($name, 'expense')) {
            return 'debit';
        }

        if (
            str_contains($name, 'liabilit') ||
            str_contains($name, 'equity') ||
            str_contains($name, 'revenue') ||
            str_contains($name, 'income') ||
            str_contains($name, 'sales')
        ) {
            return 'credit';
        }

        return 'debit';
    }

    protected function usesDebitMinusCredit(int $chartCategoryId): bool
    {
        $name = $this->getChartCategoryMap()[$chartCategoryId] ?? '';

        return str_contains($name, 'revenue')
            || str_contains($name, 'expense');
    }

    protected function isContraAccount(string $accountName, int $chartCategoryId): bool
    {
        $key = strtolower(trim($accountName));

        if (
            str_contains($key, 'sales return') ||
            str_contains($key, 'sales discount') ||
            str_contains($key, 'allowance') ||
            str_contains($key, 'accumulated depreciation')
        ) {
            return true;
        }

        if (str_contains($key, 'dividend') || str_contains($key, 'drawing')) {
            return true;
        }

        return false;
    }

    /* =====================================================================
     *  BUILD TRIAL BALANCES
     * ===================================================================== */
    protected function buildTrialBalances(
        ?string $search,
        string $dimensionA,
        string $dimensionB,
        bool $showClosed,
        ?string $fromDate,
        ?string $toDate,
        bool $excludeZeroBalances
    ): array {
        $accountList = $this->getAccountList($search, $dimensionA, $dimensionB, $showClosed);

        $beginStart = null;
        $beginEnd   = null;

        if ($fromDate) {
            $beginStart = $this->cumulativeOpening
                ? null
                : date('Y-m-01', strtotime($fromDate . ' -1 month'));
            $beginEnd   = date('Y-m-t', strtotime($fromDate . ' -1 month'));
        }

        $periodStart = $fromDate;
        $periodEnd   = $toDate;

        $trialBalances = $accountList->map(function ($account) use (
            $beginStart, $beginEnd, $periodStart, $periodEnd
        ) {
            $accountName = $account->account_name ?? '';
            $isRouted    = $this->isRoutedAccount($accountName);

            $fetchRows = function (?string $start, ?string $end) use ($isRouted, $account, $accountName) {
                return $isRouted
                    ? $this->getRoutedBreakdownRows($accountName, $start, $end)
                    : $this->getPlainBreakdownRows($account->id, $start, $end);
            };

            $beginRows  = ($beginEnd !== null)   ? $fetchRows($beginStart, $beginEnd)   : collect();
            $periodRows = ($periodEnd !== null)  ? $fetchRows($periodStart, $periodEnd) : collect();

            $bfDebit  = (float) $beginRows->sum('debit');
            $bfCredit = (float) $beginRows->sum('credit');

            $pdDebit  = (float) $periodRows->sum('debit');
            $pdCredit = (float) $periodRows->sum('credit');

            $chartCategoryId = (int) ($account->cc_id ?? 0);
            $normalBalance   = $this->getNormalBalance($chartCategoryId);
            $debitMinusCredit = $this->usesDebitMinusCredit($chartCategoryId);

            if ($debitMinusCredit) {
                $beginningBalance = $bfCredit - $bfDebit;
                $balance = $pdDebit - $pdCredit;
            } else {
                $beginningBalance = $bfCredit - $bfDebit;
                $periodNet        = $pdCredit - $pdDebit;
                $balance          = $beginningBalance + $periodNet;
            }

            if ($this->isContraAccount($accountName, $chartCategoryId)) {
                $normalBalance = $normalBalance === 'debit' ? 'credit' : 'debit';
            }

            if ($debitMinusCredit) {
                $balanceDebit  = $balance >= 0 ? $balance : 0;
                $balanceCredit = $balance < 0  ? abs($balance) : 0;
            } elseif ($normalBalance === 'debit') {
                $balanceDebit  = $balance >= 0 ? $balance : 0;
                $balanceCredit = $balance < 0  ? abs($balance) : 0;
            } else {
                $balanceDebit  = $balance < 0  ? abs($balance) : 0;
                $balanceCredit = $balance >= 0 ? $balance : 0;
            }

            return (object) [
                'id'             => $account->id,
                'industry'       => $account->industry,
                'sub_account'    => $account->sub_account,
                'classification' => $account->classification,
                'account_code'   => $account->account_code,
                'account_name'   => $account->account_name,
                'cc_id'          => $chartCategoryId,
                'chart_category' => $account->chart_category,
                'normal_balance' => $normalBalance,

                'brought_forward_debit'  => $bfDebit,
                'brought_forward_credit' => $bfCredit,

                'period_debit'  => $pdDebit,
                'period_credit' => $pdCredit,

                'beginning_balance' => $beginningBalance,
                'balance'           => $balance,

                'balance_debit'  => $balanceDebit,
                'balance_credit' => $balanceCredit,
            ];
        })->toArray();

        if ($excludeZeroBalances) {
            $trialBalances = array_filter($trialBalances, function ($item) {
                return $item->balance_debit != 0
                    || $item->balance_credit != 0
                    || $this->isCostOfGoodsAccount((string) ($item->account_name ?? ''));
            });
        }

        return $this->sortByCategorySequence(array_values($trialBalances));
    }

    /**
     * Order accounts by category sequence:
     * Asset → Liability → Equity → Expenses → Revenue → Expenses (catch-all).
     * Priority accounts (Cash, AR, etc.) surface first within each bucket.
     */
    protected function sortByCategorySequence(array $trialBalances): array
    {
        $sequence = [
            'asset'    => 1,
            'liabilit' => 2,
            'equity'   => 3,
            'expense'  => 4,
            'revenue'  => 5,
            'income'   => 6,
            'sales'    => 7,
        ];

        $priorityMap = [];
        foreach ($this->priorityAccounts as $index => $name) {
            $priorityMap[strtolower($name)] = $index;
        }

        usort($trialBalances, function ($a, $b) use ($sequence, $priorityMap) {
            $catA = strtolower(trim((string) ($a->chart_category ?? '')));
            $catB = strtolower(trim((string) ($b->chart_category ?? '')));

            $rankA = 99;
            $rankB = 99;

            foreach ($sequence as $needle => $rank) {
                if (str_contains($catA, $needle)) { $rankA = $rank; break; }
            }
            foreach ($sequence as $needle => $rank) {
                if (str_contains($catB, $needle)) { $rankB = $rank; break; }
            }

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            $nameA = strtolower(trim((string) ($a->account_name ?? '')));
            $nameB = strtolower(trim((string) ($b->account_name ?? '')));

            $pA = $priorityMap[$nameA] ?? PHP_INT_MAX;
            $pB = $priorityMap[$nameB] ?? PHP_INT_MAX;

            if ($pA !== $pB) {
                return $pA <=> $pB;
            }

            return strcmp($nameA, $nameB);
        });

        return $trialBalances;
    }

    /* =====================================================================
     *  ACCOUNT LIST
     * ===================================================================== */
    protected function getAccountList(?string $search = null, string $dimensionA = 'all', string $dimensionB = 'all', bool $showClosed = false)
    {
        $query = DB::table('expenses_categories as codes')
            ->leftJoin('chart_classifications as clss', 'codes.classification_id', '=', 'clss.id')
            ->leftJoin('chart_industries as inds', 'codes.industry_id', '=', 'inds.id')
            ->leftJoin('chart_sub_acc as sub', 'codes.category_id', '=', 'sub.id')
            ->leftJoin('chart_categories as cc', 'codes.cc_id', '=', 'cc.id')
            ->where('codes.is_deleted', 0)
            ->select(
                'codes.id',
                'codes.code as account_code',
                'codes.name as account_name',
                'codes.cc_id',
                'cc.name as chart_category',
                'inds.name as industry',
                'sub.name as sub_account',
                'clss.name as classification'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('codes.code', 'like', "%{$search}%")
                  ->orWhere('codes.name', 'like', "%{$search}%");
            });
        }

        if ($dimensionA === 'balance_sheet') {
            $query->where('clss.name', 'Balance Sheet');
        } elseif ($dimensionA === 'profit_and_loss') {
            $query->where('clss.name', 'Profit & Loss');
        }

        if ($dimensionB === 'corporate') {
            $query->where('inds.name', 'Corporate');
        } elseif ($dimensionB === 'retail') {
            $query->where('inds.name', 'Retail');
        }

        return $query->orderBy('codes.name', 'asc')->get();
    }

    /* =====================================================================
     *  ROUTED / VIRTUAL ACCOUNT DETECTION
     * ===================================================================== */
    protected function getAccountName($accountId): ?string
    {
        static $cache = [];

        if (!array_key_exists($accountId, $cache)) {
            $cache[$accountId] = DB::table('expenses_categories')
                ->where('id', $accountId)
                ->value('name');
        }

        return $cache[$accountId];
    }

    protected function isRevolvingFunds(string $accountName): bool
    {
        $key = strtolower(trim($accountName));
        return str_contains($key, 'revolving fund');
    }

    protected function isInventories(string $accountName): bool
    {
        $key = strtolower(trim($accountName));
        return $key === 'inventories' || str_contains($key, 'inventory');
    }

    protected function isRoutedAccount(string $accountName): bool
    {
        $key = strtolower(trim($accountName));

        if (str_contains($key, 'cash on hand') || str_contains($key, 'cash on bank')) {
            return true;
        }
        if ($this->isRevolvingFunds($accountName)) {
            return true;
        }
        if ($this->isInventories($accountName)) {
            return true;
        }
        if ($this->isTradePayable($accountName)) {
            return true;
        }
        if ($this->isAccountReceivable($accountName)) {
            return true;
        }
        if ($this->isSalesAccount($accountName)) {
            return true;
        }
        if ($this->isCostOfGoodsAccount($accountName)) {
            return true;
        }
        if ($this->isChequeInTransit($accountName)) {
            return true;
        }
        return false;
    }

    protected function isCostOfGoodsAccount(string $accountName): bool
    {
        $key = strtolower(trim($accountName));

        return str_contains($key, 'cost of goods')
            || str_contains($key, 'cogs')
            || str_contains($key, 'cost of sales');
    }

    protected function isChequeInTransit(string $accountName): bool
    {
        $key = strtolower(trim($accountName));

        return str_contains($key, 'cheque in transit')
            || str_contains($key, 'check in transit');
    }

    protected function isSalesAccount(string $accountName): bool
    {
        $key = strtolower(trim($accountName));

        if (
            str_contains($key, 'sales return') ||
            str_contains($key, 'sales discount') ||
            str_contains($key, 'sales allowance') ||
            str_contains($key, 'return and allowance') ||
            str_contains($key, 'returns and allowances') ||
            str_contains($key, 'cost of goods') ||
            str_contains($key, 'cogs') ||
            str_contains($key, 'cost of sales')
        ) {
            return false;
        }

        return $key === 'sales'
            || str_contains($key, 'sales revenue')
            || str_contains($key, 'gross sales')
            || str_contains($key, 'net sales')
            || str_contains($key, 'service revenue');
    }

    protected function isTradePayable(string $accountName): bool
    {
        $key = strtolower(trim($accountName));

        if (str_contains($key, 'non-trade') || str_contains($key, 'non trade')) {
            return false;
        }

        return str_contains($key, 'trade payable')
            || str_contains($key, 'trade payables');
    }

    protected function isAccountReceivable(string $accountName): bool
    {
        $key = strtolower(trim($accountName));

        if (str_contains($key, 'non-trade') || str_contains($key, 'non trade')) {
            return false;
        }

        return str_contains($key, 'trade receivable')
            || str_contains($key, 'trade receivables');
    }

    /* =====================================================================
     *  BREAKDOWN (AJAX)
     * ===================================================================== */
    public function getBreakdown(Request $request)
    {
        $accountId = $request->input('account_id');
        $month     = $request->input('month');
        $year      = $request->input('year');

        if (!$accountId || !$month || !$year) {
            return response()->json(['error' => 'Missing required parameters.'], 400);
        }

        [$fromDate, $toDate] = $this->monthRange($month, $year);

        $beginStart = $this->cumulativeOpening
            ? null
            : date('Y-m-01', strtotime($fromDate . ' -1 month'));
        $beginEnd   = date('Y-m-t', strtotime($fromDate . ' -1 month'));

        $beginLabel = $this->cumulativeOpening
            ? 'All transactions before ' . date('F Y', strtotime($fromDate))
            : date('F Y', strtotime($beginEnd));
        $periodLabel = date('F Y', strtotime($fromDate));

        $accountName = $this->getAccountName($accountId);
        $isRouted    = $accountName !== null && $this->isRoutedAccount($accountName);

        $fetchRows = function (?string $start, string $end) use ($isRouted, $accountId, $accountName) {
            return $isRouted
                ? $this->getRoutedBreakdownRows($accountName, $start, $end)
                : $this->getPlainBreakdownRows($accountId, $start, $end);
        };

        $beginningRows = $fetchRows($beginStart, $beginEnd);
        $periodRows    = $fetchRows($fromDate, $toDate);

        $bfDebit  = (float) $beginningRows->sum('debit');
        $bfCredit = (float) $beginningRows->sum('credit');
        $accountCategoryId = (int) DB::table('expenses_categories')
            ->where('id', $accountId)
            ->value('cc_id');
        $debitMinusCredit = $this->usesDebitMinusCredit($accountCategoryId);
        $beginningBalance = $bfCredit - $bfDebit;

        return response()->json([
            'beginning' => [
                'label'        => $beginLabel,
                'rows'         => $beginningRows->values(),
                'total_debit'  => $bfDebit,
                'total_credit' => $bfCredit,
            ],
            'period' => [
                'label'        => $periodLabel,
                'rows'         => $periodRows->values(),
                'total_debit'  => (float) $periodRows->sum('debit'),
                'total_credit' => (float) $periodRows->sum('credit'),
            ],
            'beginning_balance' => $beginningBalance,
            'balance_mode' => $debitMinusCredit ? 'debit_minus_credit' : 'credit_minus_debit',
        ]);
    }

    /* =====================================================================
     *  ROW FETCHERS
     * ===================================================================== */
    protected function applyRange($query, $columns, ?string $start, ?string $end): void
    {
        foreach ((array) $columns as $column) {
            if ($start) {
                $query->where($column, '>=', $start . ' 00:00:00');
            }
            if ($end) {
                $query->where($column, '<=', $end . ' 23:59:59');
            }
        }
    }

    /**
     * Join payment_logs (alias `pl`) to transactions (alias `tr`).
     *
     * transactions.local_id is NOT unique: it is a per-store/terminal counter
     * from the sync setup, so joining on local_id alone attaches payments from
     * other stores/branches to the wrong bill. The join is therefore scoped by
     * store_code + branch_code (null-safe), and voided/deleted logs are ignored.
     *
     * @param string $method 'join' (inner) or 'leftJoin'
     */
    protected function joinPaymentLogs($query, string $method = 'join'): void
    {
        $query->{$method}('payment_logs as pl', function ($j) {
            $j->on('pl.tran_id', '=', 'tr.local_id')
              ->whereRaw('pl.store_code  <=> tr.store_code')
              ->whereRaw('pl.branch_code <=> tr.branch_code')
              ->whereRaw('COALESCE(pl.is_deleted, 0) = 0')
              ->whereRaw('COALESCE(pl.is_void, 0) = 0');
        });
    }

    protected function getPlainBreakdownRows($accountId, ?string $rangeStart, string $rangeEnd)
    {
        $rows = collect();

        $invQuery = DB::table('inventory_transactions as t')
            ->leftJoin('inventory_etransaction as e', 't.e_id', '=', 'e.id')
            ->leftJoin('users as u', 't.user_id', '=', 'u.id')
            ->where('t.category_id', $accountId)
            ->where('t.is_deleted', 0)
            ->whereNotNull('t.category_id')
            ->whereNotNull('t.payment_class')
            ->whereNotNull('t.exp_type')
            ->select(
                't.id',
                't.created_at',
                't.amount',
                't.payment_class',
                't.remarks',
                'e.e_transid as reference_no',
                'u.lastname as user_name'
            );

        $this->applyRange($invQuery, 't.created_at', $rangeStart, $rangeEnd);

        foreach ($invQuery->orderBy('t.created_at', 'asc')->get() as $tx) {
            $rows->push([
                'id'           => 'IT-' . $tx->id,
                'date_raw'     => $tx->created_at,
                'date'         => date('M d, Y', strtotime($tx->created_at)),
                'time'         => date('h:i A', strtotime($tx->created_at)),
                'reference_no' => $tx->reference_no ?? '—',
                'remarks'      => $tx->remarks ?? '—',
                'user_name'    => $tx->user_name ?? '—',
                'debit'        => (float) $tx->amount,
                'credit'       => 0.0,
                'balance'      => 0.0,
            ]);
        }

        $revQuery = DB::table('revolving_transactions as t')
            ->leftJoin('users as u', 'u.id', '=', DB::raw('CAST(t.user_id AS UNSIGNED)'))
            ->where('t.category_id', $accountId)
            ->where('t.is_deleted', 0)
            ->select(
                't.id',
                't.created_at',
                't.amount',
                't.payment_class',
                't.remarks',
                't.particulars',
                't.description',
                't.inv_id as reference_no',
                'u.lastname as user_name'
            );

        $this->applyRange($revQuery, 't.created_at', $rangeStart, $rangeEnd);

        foreach ($revQuery->orderBy('t.created_at', 'asc')->get() as $tx) {
            $remarks = trim((string) ($tx->particulars ?? ''));
            if ($remarks === '') $remarks = trim((string) ($tx->remarks ?? ''));
            if ($remarks === '') $remarks = trim((string) ($tx->description ?? ''));
            if ($remarks === '') $remarks = '—';

            $rows->push([
                'id'           => 'RT-' . $tx->id,
                'date_raw'     => $tx->created_at,
                'date'         => date('M d, Y', strtotime($tx->created_at)),
                'time'         => date('h:i A', strtotime($tx->created_at)),
                'reference_no' => $tx->reference_no ?? '—',
                'remarks'      => $remarks,
                'user_name'    => $tx->user_name ?? '—',
                'debit'        => (float) $tx->amount,
                'credit'       => 0.0,
                'balance'      => 0.0,
            ]);
        }

        return $rows->sortBy('date_raw')->values()->map(function ($r) {
            unset($r['date_raw']);
            $r['balance'] = 0.0;
            return $r;
        });
    }

    protected function getRoutedBreakdownRows(string $accountName, ?string $rangeStart, string $rangeEnd)
    {
        $key  = strtolower(trim($accountName));
        $rows = collect();

        $isTradePayable      = $this->isTradePayable($accountName);
        $isAccountReceivable = $this->isAccountReceivable($accountName);
        $isCogs              = $this->isCostOfGoodsAccount($accountName);
        $isInventories       = $this->isInventories($accountName);

        /* 0) SALES — CREDIT only when order_status = PAID */
        if ($this->isSalesAccount($accountName)) {
            $salesQuery = DB::table('transactions as tr')
                ->leftJoin('tables as tb', 'tb.id', '=', 'tr.table')
                ->where('tr.is_approved', 1)
                ->whereRaw("UPPER(TRIM(COALESCE(tr.order_status, ''))) != 'CANCELLED'")
                ->select(
                    'tr.id as transaction_id',
                    'tr.local_id',
                    'tr.created_at',
                    'tr.total as amount',
                    'tr.number as reference_no',
                    'tr.mode_of_payment',
                    'tr.cashier as user_name',
                    'tb.name as table_name'
                );

            $this->applyRange($salesQuery, 'tr.created_at', $rangeStart, $rangeEnd);

            foreach ($salesQuery->orderBy('tr.created_at', 'asc')->get() as $s) {
                $remarks = !empty($s->table_name)
                    ? $s->table_name
                    : 'Sale • ' . ($s->mode_of_payment ?? '—');

                $rows->push([
                    'id'           => 'S-' . $s->transaction_id,
                    'date_raw'     => $s->created_at,
                    'date'         => date('M d, Y', strtotime($s->created_at)),
                    'time'         => date('h:i A', strtotime($s->created_at)),
                    'reference_no' => $s->reference_no ?? '—',
                    'remarks'      => $remarks,
                    'user_name'    => $s->user_name ?? '—',
                    'debit'        => 0.0,
                    'credit'       => (float) $s->amount,
                ]);
            }

            return $rows->sortBy('date_raw')->values()->map(function ($r) {
                unset($r['date_raw']);
                $r['balance'] = 0.0;
                return $r;
            });
        }

        /* 0.5) COST OF GOODS SOLD — mirrors IncomeStatementController
         *
         *   COGS = Beginning Inventory                       (income_statement id=2)
         *        + inventory_transactions (exp_type = 0)    (COGS movements)
         *        + ordered_cogs purchases  (per po_id)       (VOIDs negative)
         *        - Scrap                                    (income_statement id=2)
         *        - Ending Inventory                         (income_statement id=2)
         *
         * COGS is an expense (debit-normal); reductions appear as credits so that
         * `balance = periodDebit - periodCredit` matches `$cogsValueByMonth` in IS.
         *
         * Each P.O. row carries its `lines[]` (menu – brand, amount, void flag)
         * so the breakdown modal can expand it just like the IncomeStatement modal.
         */
        if ($isCogs) {

            /* 1) Purchases — ordered_cogs, grouped per P.O. */
            if (Schema::hasTable('ordered_cogs')) {
                $purchaseQuery = DB::table('ordered_cogs as oc')
                    ->leftJoin('menus',  'menus.id',  '=', 'oc.menu_id')
                    ->leftJoin('brands', 'brands.id', '=', 'menus.brand_id')
                    ->leftJoin('purchase_ordered as po', 'po.id', '=', 'oc.po_id')
                    ->leftJoin('suppliers as s', 's.id', '=', 'po.supplier_id')
                    ->select(
                        'oc.id', 'oc.po_id', 'oc.si_number', 'oc.po_total',
                        'oc.status', 'oc.created_at',
                        'menus.name  as menu_name',
                        'brands.name as brand_name',
                        DB::raw('COALESCE(NULLIF(TRIM(po.supplier_name), ""), s.name) as supplier_name')
                    );

                $this->applyRange($purchaseQuery, 'oc.created_at', $rangeStart, $rangeEnd);

                $usedLabels = [];
                $orders = [];
                foreach ($purchaseQuery
                            ->orderBy('oc.created_at')
                            ->orderBy('oc.po_id')
                            ->orderBy('oc.id')
                            ->get() as $line) {

                    $orderId = $line->po_id;

                    if (!isset($orders[$orderId])) {
                        $orders[$orderId] = [
                            'created_at' => $line->created_at,
                            'po_number'  => trim((string) $line->si_number),
                            'supplier_name' => trim((string) ($line->supplier_name ?? '')),
                            'amount'     => 0,
                            'lines'      => [],
                        ];
                    }

                    $isVoid = strtoupper((string) $line->status) === 'VOID';
                    $amount = round((float) $line->po_total, 2);
                    if ($isVoid) {
                        $amount = -abs($amount);
                    }

                    $menu  = preg_replace('/\s+/', ' ', trim((string) $line->menu_name));
                    $brand = trim((string) $line->brand_name);

                    $label = $brand !== '' ? $menu . ' – ' . $brand : $menu;
                    if ($label === '') $label = 'Unnamed Item';
                    if ($isVoid)     $label .= ' (VOID)';

                    $orders[$orderId]['lines'][] = [
                        'item'   => $label,
                        'amount' => $amount,
                        'void'   => $isVoid,
                    ];
                    $orders[$orderId]['amount'] += $amount;
                }

                foreach ($orders as $order) {
                    // Match the income-statement rule: retain a P.O. when it
                    // has item activity, even if SALE and VOID lines net to zero.
                    if (
                        round($order['amount'], 2) == 0
                        && empty(array_filter($order['lines'], fn ($line) => (float) ($line['amount'] ?? 0) != 0))
                    ) {
                        continue;
                    }

                    $poLabel = $order['po_number'] !== '' ? $order['po_number'] : '—';
                    $supplierName = $order['supplier_name'] !== '' ? $order['supplier_name'] : '—';
                    $displayLabel = $supplierName . ' — S.I. # ' . $poLabel;
                    $usedLabels[$displayLabel] = ($usedLabels[$displayLabel] ?? 0) + 1;
                    if ($usedLabels[$displayLabel] > 1) {
                        $displayLabel .= ' (' . $usedLabels[$displayLabel] . ')';
                    }

                    $rows->push([
                        'id'           => 'PO-' . md5($poLabel . '_' . $order['created_at']),
                        'date_raw'     => $order['created_at'],
                        'date'         => date('M d, Y', strtotime($order['created_at'])),
                        'time'         => date('h:i A', strtotime($order['created_at'])),
                        'reference_no' => $poLabel,
                        'remarks'      => $displayLabel,
                        'user_name'    => '—',
                        'debit'        => round($order['amount'], 2),
                        'credit'       => 0.0,
                        // Detail lines for the modal expand/collapse
                        'lines'        => $order['lines'],
                    ]);
                }
            }

            /* 2) Raw COGS movements — inventory_transactions where exp_type = 0 */
            $cogsQuery = DB::table('inventory_transactions as t')
                ->leftJoin('inventory_etransaction as e', 't.e_id', '=', 'e.id')
                ->leftJoin('users as u', 't.user_id', '=', 'u.id')
                ->where('t.is_deleted', 0)
                ->where('t.exp_type', 0)
                ->select(
                    't.id', 't.created_at', 't.amount', 't.remarks',
                    'e.e_transid as reference_no', 'u.lastname as user_name'
                );

            $this->applyRange($cogsQuery, 't.created_at', $rangeStart, $rangeEnd);

            foreach ($cogsQuery->orderBy('t.created_at', 'asc')->get() as $ex) {
                $rows->push([
                    'id'           => 'COGS-' . $ex->id,
                    'date_raw'     => $ex->created_at,
                    'date'         => date('M d, Y', strtotime($ex->created_at)),
                    'time'         => date('h:i A', strtotime($ex->created_at)),
                    'reference_no' => $ex->reference_no ?? '—',
                    'remarks'      => $ex->remarks ?? 'COGS',
                    'user_name'    => $ex->user_name ?? '—',
                    'debit'        => (float) $ex->amount,
                    'credit'       => 0.0,
                ]);
            }

            /* 3) Adjustments — Beginning / Scrap / Ending (income_statement id = 2) */
            $isField = DB::table('income_statement')->where('id', 2)->first();
            if ($isField) {
                $anchorDate = $rangeEnd ?: date('Y-m-t');
                $anchorTs   = strtotime($anchorDate);

                if (!empty($isField->beginning)) {
                    $rows->push([
                        'id'           => 'COGS-BEGIN',
                        'date_raw'     => $anchorDate,
                        'date'         => date('M d, Y', $anchorTs),
                        'time'         => '—',
                        'reference_no' => '—',
                        'remarks'      => 'Beginning Inventory',
                        'user_name'    => '—',
                        'debit'        => (float) $isField->beginning,
                        'credit'       => 0.0,
                    ]);
                }

                if (!empty($isField->scrap)) {
                    $rows->push([
                        'id'           => 'COGS-SCRAP',
                        'date_raw'     => $anchorDate,
                        'date'         => date('M d, Y', $anchorTs),
                        'time'         => '—',
                        'reference_no' => '—',
                        'remarks'      => 'Less: Scrap',
                        'user_name'    => '—',
                        'debit'        => 0.0,
                        'credit'       => (float) $isField->scrap,
                    ]);
                }

                if (!empty($isField->ending)) {
                    $rows->push([
                        'id'           => 'COGS-ENDING',
                        'date_raw'     => $anchorDate,
                        'date'         => date('M d, Y', $anchorTs),
                        'time'         => '—',
                        'reference_no' => '—',
                        'remarks'      => 'Less: Ending Inventory',
                        'user_name'    => '—',
                        'debit'        => 0.0,
                        'credit'       => (float) $isField->ending,
                    ]);
                }
            }

            return $rows->sortBy('date_raw')->values()->map(function ($r) {
                unset($r['date_raw']);
                $r['balance'] = 0.0;
                return $r;
            });
        }

        /* 0.55) INVENTORIES — Debit when payment_class = 0, Credit when payment_class = 1 */
        if ($isInventories) {
            $invQuery = DB::table('inventory_transactions as t')
                ->leftJoin('inventory_etransaction as e', 't.e_id', '=', 'e.id')
                ->leftJoin('tables as client', 'client.id', '=', 't.supplier_id')
                ->leftJoin('users as u', 't.user_id', '=', 'u.id')
                ->where('t.is_deleted', 0)
                ->where('t.type', 'TRADE PAYABLE')
                ->whereNotNull('t.department_id')
                ->whereNotNull('t.e_id')
                ->whereNotNull('t.inv_id')
                ->whereNotNull('t.category_id')
                ->whereNotNull('t.supplier_id')
                ->whereNotNull('t.payment_class')
                ->select(
                    't.id', 't.created_at', 't.amount', 't.payment_class', 't.is_paid',
                    'e.e_transid as reference_no',
                    'u.lastname as user_name',
                    'client.name as supplier_name',
                    't.remarks'
                );

            $this->applyRange($invQuery, 't.created_at', $rangeStart, $rangeEnd);

            foreach ($invQuery->orderBy('t.created_at', 'asc')->get() as $tx) {
                $isPaid = (int) $tx->payment_class === 1;

                $remarks = trim((string) ($tx->supplier_name ?? ''));
                if ($remarks === '') $remarks = trim((string) ($tx->remarks ?? ''));
                if ($remarks === '') $remarks = $isPaid ? 'Inventory (Paid)' : 'Inventory (Unpaid)';

                $rows->push([
                    'id'           => 'INV-' . $tx->id,
                    'date_raw'     => $tx->created_at,
                    'date'         => date('M d, Y', strtotime($tx->created_at)),
                    'time'         => date('h:i A', strtotime($tx->created_at)),
                    'reference_no' => $tx->reference_no ?? '—',
                    'remarks'      => $remarks,
                    'user_name'    => $tx->user_name ?? '—',
                    'debit'        => $isPaid ? 0.0 : (float) $tx->amount,
                    'credit'       => $isPaid ? (float) $tx->amount : 0.0,
                ]);
            }

            return $rows->sortBy('date_raw')->values()->map(function ($r) {
                unset($r['date_raw']);
                $r['balance'] = 0.0;
                return $r;
            });
        }

        /* 0.6) TRADE PAYABLES — Debit when payment_class = 1, Credit when payment_class = 0 */
        if ($isTradePayable) {
            $apQuery = DB::table('inventory_transactions as t')
                ->leftJoin('inventory_etransaction as e', 't.e_id', '=', 'e.id')
                ->leftJoin('tables as client', 'client.id', '=', 't.supplier_id')
                ->leftJoin('users as u', 't.user_id', '=', 'u.id')
                ->where('t.is_deleted', 0)
                ->where('t.type', 'TRADE PAYABLE')
                ->whereNotNull('t.department_id')
                ->whereNotNull('t.e_id')
                ->whereNotNull('t.inv_id')
                ->whereNotNull('t.category_id')
                ->whereNotNull('t.supplier_id')
                ->whereNotNull('t.payment_class')
                ->select(
                    't.id', 't.created_at', 't.amount', 't.payment_class', 't.is_paid',
                    'e.e_transid as reference_no',
                    'u.lastname as user_name',
                    'client.name as supplier_name',
                    't.remarks'
                );

            $this->applyRange($apQuery, 't.created_at', $rangeStart, $rangeEnd);

            foreach ($apQuery->orderBy('t.created_at', 'asc')->get() as $ap) {
                $isPaid = (int) $ap->payment_class === 1;

                $remarks = trim((string) ($ap->supplier_name ?? ''));
                if ($remarks === '') $remarks = trim((string) ($ap->remarks ?? ''));
                if ($remarks === '') $remarks = $isPaid ? 'Trade Payable (Paid)' : 'Trade Payable (Unpaid)';

                $rows->push([
                    'id'           => 'AP-' . $ap->id,
                    'date_raw'     => $ap->created_at,
                    'date'         => date('M d, Y', strtotime($ap->created_at)),
                    'time'         => date('h:i A', strtotime($ap->created_at)),
                    'reference_no' => $ap->reference_no ?? '—',
                    'remarks'      => $remarks,
                    'user_name'    => $ap->user_name ?? '—',
                    'debit'        => $isPaid ? (float) $ap->amount : 0.0,
                    'credit'       => $isPaid ? 0.0 : (float) $ap->amount,
                ]);
            }

            return $rows->sortBy('date_raw')->values()->map(function ($r) {
                unset($r['date_raw']);
                $r['balance'] = 0.0;
                return $r;
            });
        }

        /* 0.7) CHEQUE IN TRANSIT — cheque sales (DEBIT) + paid inventory cheque (CREDIT) */
        if ($this->isChequeInTransit($accountName)) {

            $salesQuery = DB::table('transactions as tr')
                ->leftJoin('tables as tb', 'tb.id', '=', 'tr.table')
                ->where('tr.is_approved', 1)
                ->whereRaw("UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) IN ('CHECK', 'CHEQUE')")
                ->select(
                    'tr.id as transaction_id', 'tr.created_at', 'tr.amount',
                    'tr.number as reference_no', 'tr.mode_of_payment',
                    'tr.cashier as user_name', 'tb.name as table_name'
                );

            $this->excludeCancelled($salesQuery, 'tr');
            $this->applyRange($salesQuery, 'tr.created_at', $rangeStart, $rangeEnd);

            foreach ($salesQuery->orderBy('tr.created_at', 'asc')->get() as $s) {
                $remarks = !empty($s->table_name) ? $s->table_name : 'Sale • ' . ($s->mode_of_payment ?? '—');
                $rows->push([
                    'id' => 'CIT-' . $s->transaction_id, 'date_raw' => $s->created_at,
                    'date' => date('M d, Y', strtotime($s->created_at)),
                    'time' => date('h:i A', strtotime($s->created_at)),
                    'reference_no' => $s->reference_no ?? '—', 'remarks' => $remarks,
                    'user_name' => $s->user_name ?? '—',
                    'debit' => (float) $s->amount, 'credit' => 0.0,
                ]);
            }

            // FIX: payment_logs joined by local_id + store_code + branch_code
            $splitQuery = DB::table('transactions as tr');
            $this->joinPaymentLogs($splitQuery);
            $splitQuery
                ->join('mode_of_payment as mop', 'mop.id', '=', 'pl.mop_id')
                ->leftJoin('tables as tb', 'tb.id', '=', 'tr.table')
                ->where('tr.is_approved', 1)
                ->whereNotNull('pl.mop_id')
                ->whereRaw("UPPER(TRIM(COALESCE(mop.name, ''))) IN ('CHECK', 'CHEQUE')")
                ->select(
                    'pl.id as pl_id', 'tr.id as transaction_id',
                    'tr.created_at as tr_created_at', 'pl.created_at as pl_created_at',
                    'pl.amount as split_amount', 'tr.number as reference_no',
                    'tr.cashier as user_name', 'tb.name as table_name', 'mop.name as mop_name'
                );

            $this->excludeCancelled($splitQuery, 'tr');
            $this->applyRange($splitQuery, 'pl.created_at', $rangeStart, $rangeEnd);

            foreach ($splitQuery->orderBy('pl.created_at', 'asc')->get() as $sp) {
                $displayDate = $sp->pl_created_at ?? $sp->tr_created_at;
                $remarks = !empty($sp->table_name) ? $sp->table_name : 'Split • ' . ($sp->mop_name ?? '—');
                $rows->push([
                    'id' => 'CIT-SP-' . $sp->pl_id, 'date_raw' => $displayDate,
                    'date' => date('M d, Y', strtotime($displayDate)),
                    'time' => date('h:i A', strtotime($displayDate)),
                    'reference_no' => $sp->reference_no ?? '—', 'remarks' => $remarks,
                    'user_name' => $sp->user_name ?? '—',
                    'debit' => (float) $sp->split_amount, 'credit' => 0.0,
                ]);
            }

            $invCITQuery = DB::table('inventory_transactions as t')
                ->leftJoin('inventory_etransaction as e', 't.e_id', '=', 'e.id')
                ->leftJoin('users as u', 't.user_id', '=', 'u.id')
                ->where('t.is_deleted', 0)
                ->where('t.payment_class', 1)
                ->whereRaw("UPPER(TRIM(COALESCE(t.is_paid, ''))) IN ('CHECK', 'CHEQUE')")
                ->select(
                    't.id', 't.created_at', 't.amount', 't.is_paid', 't.remarks',
                    'e.e_transid as reference_no', 'u.lastname as user_name'
                );

            $this->applyRange($invCITQuery, 't.created_at', $rangeStart, $rangeEnd);

            foreach ($invCITQuery->orderBy('t.created_at', 'asc')->get() as $ex) {
                $rows->push([
                    'id' => 'CIT-INV-' . $ex->id, 'date_raw' => $ex->created_at,
                    'date' => date('M d, Y', strtotime($ex->created_at)),
                    'time' => date('h:i A', strtotime($ex->created_at)),
                    'reference_no' => $ex->reference_no ?? '—',
                    'remarks' => $ex->remarks ?? 'Cheque Expense',
                    'user_name' => $ex->user_name ?? '—',
                    'debit' => 0.0, 'credit' => (float) $ex->amount,
                ]);
            }

            $revCITQuery = DB::table('revolving_transactions as t')
                ->leftJoin('users as u', 'u.id', '=', DB::raw('CAST(t.user_id AS UNSIGNED)'))
                ->where('t.is_deleted', 0)
                ->whereRaw("UPPER(TRIM(COALESCE(t.is_paid, ''))) IN ('CHECK', 'CHEQUE')")
                ->select(
                    't.id', 't.created_at', 't.amount', 't.is_paid', 't.remarks',
                    't.particulars', 't.description', 't.inv_id as reference_no',
                    'u.lastname as user_name'
                );

            $this->applyRange($revCITQuery, 't.created_at', $rangeStart, $rangeEnd);

            foreach ($revCITQuery->orderBy('t.created_at', 'asc')->get() as $ex) {
                $remarks = trim((string) ($ex->particulars ?? ''));
                if ($remarks === '') $remarks = trim((string) ($ex->remarks ?? ''));
                if ($remarks === '') $remarks = trim((string) ($ex->description ?? ''));
                if ($remarks === '') $remarks = 'Cheque Expense';

                $rows->push([
                    'id' => 'CIT-REV-' . $ex->id, 'date_raw' => $ex->created_at,
                    'date' => date('M d, Y', strtotime($ex->created_at)),
                    'time' => date('h:i A', strtotime($ex->created_at)),
                    'reference_no' => $ex->reference_no ?? '—', 'remarks' => $remarks,
                    'user_name' => $ex->user_name ?? '—',
                    'debit' => 0.0, 'credit' => (float) $ex->amount,
                ]);
            }

            return $rows->sortBy('date_raw')->values()->map(function ($r) {
                unset($r['date_raw']); $r['balance'] = 0.0; return $r;
            });
        }

        /* 0.9) TRADE RECEIVABLES — status = UNPAID → CREDIT (full amount) */
        if ($isAccountReceivable) {

            $arQuery = DB::table('transactions as tr')
                ->leftJoin('tables as tb', 'tb.id', '=', 'tr.table')
                ->where('tr.is_approved', 1)
                ->where('tr.order_status', '!=', 'CANCELLED')
                ->where('tr.status', '=', 'UNPAID')
                ->select(
                    'tr.id as transaction_id',
                    'tr.local_id',
                    'tr.created_at',
                    'tr.total',
                    'tr.amount',
                    'tr.number as reference_no',
                    'tr.mode_of_payment',
                    'tr.cashier as user_name',
                    'tb.name as table_name'
                );

            $this->applyRange($arQuery, 'tr.created_at', $rangeStart, $rangeEnd);

            foreach ($arQuery->orderBy('tr.created_at', 'asc')->get() as $s) {
                $remarks = !empty($s->table_name)
                    ? $s->table_name
                    : 'Unpaid • ' . ($s->mode_of_payment ?? '—');

                $amount = (float) ($s->total ?? 0);
                if ($amount == 0.0) {
                    $amount = (float) ($s->amount ?? 0);
                }

                $rows->push([
                    'id'           => 'AR-' . $s->transaction_id,
                    'date_raw'     => $s->created_at,
                    'date'         => date('M d, Y', strtotime($s->created_at)),
                    'time'         => date('h:i A', strtotime($s->created_at)),
                    'reference_no' => $s->reference_no ?? '—',
                    'remarks'      => $remarks,
                    'user_name'    => $s->user_name ?? '—',
                    'debit'        => $amount,
                    'credit'       => 0.0,
                ]);
            }

            return $rows->sortBy('date_raw')->values()->map(function ($r) {
                unset($r['date_raw']); $r['balance'] = 0.0; return $r;
            });
        }

        /* 0.8) REVOLVING FUNDS — credit for every revolving-funded expense */
        if ($this->isRevolvingFunds($accountName)) {

            $revFundQuery = DB::table('revolving_transactions as t')
                ->leftJoin('users as u', 'u.id', '=', DB::raw('CAST(t.user_id AS UNSIGNED)'))
                ->leftJoin('revolving_budgets as rb', 'rb.id', '=', 't.rev_id')
                ->where('t.is_deleted', 0)
                ->whereNotNull('t.rev_id')
                ->select(
                    't.id', 't.created_at', 't.amount', 't.is_paid', 't.remarks',
                    't.particulars', 't.description', 't.inv_id as reference_no',
                    't.rev_id', 'u.lastname as user_name', 'rb.classification as budget_name'
                );

            $this->applyRange($revFundQuery, 't.created_at', $rangeStart, $rangeEnd);

            foreach ($revFundQuery->orderBy('t.created_at', 'asc')->get() as $ex) {
                $remarks = trim((string) ($ex->particulars ?? ''));
                if ($remarks === '') $remarks = trim((string) ($ex->remarks ?? ''));
                if ($remarks === '') $remarks = trim((string) ($ex->description ?? ''));
                if ($remarks === '') $remarks = 'Revolving Fund Expense';
                if (!empty($ex->budget_name)) $remarks .= ' • ' . $ex->budget_name;

                $rows->push([
                    'id' => 'RF-' . $ex->id, 'date_raw' => $ex->created_at,
                    'date' => date('M d, Y', strtotime($ex->created_at)),
                    'time' => date('h:i A', strtotime($ex->created_at)),
                    'reference_no' => $ex->reference_no ?? '—', 'remarks' => $remarks,
                    'user_name' => $ex->user_name ?? '—',
                    'debit' => 0.0, 'credit' => (float) $ex->amount,
                ]);
            }

            return $rows->sortBy('date_raw')->values()->map(function ($r) {
                unset($r['date_raw']); $r['balance'] = 0.0; return $r;
            });
        }

        /* 1a) SALES — non-split rows → DEBIT to cash/bank based on mode_of_payment */
        $salesQuery = DB::table('transactions as tr')
            ->leftJoin('tables as tb', 'tb.id', '=', 'tr.table')
            ->where('tr.is_approved', 1)
            ->whereRaw("UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) NOT IN ('MULTIPLE', 'AR')")
            ->whereRaw("UPPER(TRIM(COALESCE(tr.order_status, ''))) <> 'CANCELLED'")
            ->select(
                'tr.id as transaction_id', 'tr.local_id', 'tr.created_at', 'tr.amount',
                'tr.number as reference_no', 'tr.mode_of_payment',
                'tr.cashier as user_name', 'tb.name as table_name'
            );

        $this->excludeCancelled($salesQuery, 'tr');
        $this->applyRange($salesQuery, 'tr.created_at', $rangeStart, $rangeEnd);

        if (str_contains($key, 'cash on hand')) {
            $salesQuery->whereRaw("UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) = 'CASH'");
        } elseif (str_contains($key, 'cash on bank')) {
            $salesQuery->whereRaw(
                "(
                    UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%BANK TRANSFER%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%TRANSFER%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%GCASH%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%PAYMAYA%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%MAYA%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%ONLINE%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%E-WALLET%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%EWALLET%'
                )"
            );
        } else {
            $salesQuery->whereRaw('1 = 0');
        }

        foreach ($salesQuery->orderBy('tr.created_at', 'asc')->get() as $s) {
            $remarks = !empty($s->table_name) ? $s->table_name : 'Sale • ' . ($s->mode_of_payment ?? '—');
            $rows->push([
                'id' => 'S-' . $s->transaction_id, 'date_raw' => $s->created_at,
                'date' => date('M d, Y', strtotime($s->created_at)),
                'time' => date('h:i A', strtotime($s->created_at)),
                'reference_no' => $s->reference_no ?? '—', 'remarks' => $remarks,
                'user_name' => $s->user_name ?? '—',
                'debit' => (float) $s->amount, 'credit' => 0.0,
            ]);
        }

        /* 1b) SALES — split rows → DEBIT by mop name
         *     FIX: payment_logs joined by local_id + store_code + branch_code
         *     (local_id alone is not unique across stores/terminals). */
        $splitQuery = DB::table('transactions as tr');
        $this->joinPaymentLogs($splitQuery);
        $splitQuery
            ->join('mode_of_payment as mop', 'mop.id', '=', 'pl.mop_id')
            ->leftJoin('tables as tb', 'tb.id', '=', 'tr.table')
            ->where('tr.is_approved', 1)
            ->whereNotNull('pl.mop_id')
            ->whereRaw("UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) IN ('MULTIPLE', 'AR')")
            ->select(
                'pl.id as pl_id', 'tr.id as transaction_id', 'tr.local_id',
                'tr.created_at as tr_created_at', 'pl.created_at as pl_created_at',
                'pl.amount as split_amount', 'tr.number as reference_no',
                'tr.cashier as user_name', 'tb.name as table_name', 'mop.name as mop_name'
            );

        $this->excludeCancelled($splitQuery, 'tr');
        $this->applyRange($splitQuery, 'pl.created_at', $rangeStart, $rangeEnd);

        if (str_contains($key, 'cash on hand')) {
            $splitQuery->whereRaw("UPPER(TRIM(COALESCE(mop.name, ''))) = 'CASH'");
        } elseif (str_contains($key, 'cash on bank')) {
            $splitQuery->whereRaw(
                "(
                    UPPER(TRIM(COALESCE(mop.name, ''))) LIKE '%BANK%'
                    OR UPPER(TRIM(COALESCE(mop.name, ''))) LIKE '%TRANSFER%'
                    OR UPPER(TRIM(COALESCE(mop.name, ''))) LIKE '%GCASH%'
                    OR UPPER(TRIM(COALESCE(mop.name, ''))) LIKE '%PAYMAYA%'
                    OR UPPER(TRIM(COALESCE(mop.name, ''))) LIKE '%MAYA%'
                    OR UPPER(TRIM(COALESCE(mop.name, ''))) LIKE '%ONLINE%'
                    OR UPPER(TRIM(COALESCE(mop.name, ''))) LIKE '%E-WALLET%'
                    OR UPPER(TRIM(COALESCE(mop.name, ''))) LIKE '%EWALLET%'
                )"
            );
        } else {
            $splitQuery->whereRaw('1 = 0');
        }

        foreach ($splitQuery->orderBy('pl.created_at', 'asc')->get() as $sp) {
            $displayDate = $sp->pl_created_at ?? $sp->tr_created_at;
            $remarks = !empty($sp->table_name) ? $sp->table_name : 'Split • ' . ($sp->mop_name ?? '—');
            $rows->push([
                'id' => 'SP-' . $sp->pl_id, 'date_raw' => $displayDate,
                'date' => date('M d, Y', strtotime($displayDate)),
                'time' => date('h:i A', strtotime($displayDate)),
                'reference_no' => $sp->reference_no ?? '—', 'remarks' => $remarks,
                'user_name' => $sp->user_name ?? '—',
                'debit' => (float) $sp->split_amount, 'credit' => 0.0,
            ]);
        }

        /* 1c) ORPHAN MULTIPLE/AR sales (no payment_logs at all)
         *     FIX: scoped left join + restricted to MULTIPLE/AR so normal
         *     bank/cash sales already taken by 1a are not counted twice. */
        $orphanQuery = DB::table('transactions as tr');
        $this->joinPaymentLogs($orphanQuery, 'leftJoin');
        $orphanQuery
            ->leftJoin('tables as tb', 'tb.id', '=', 'tr.table')
            ->where('tr.is_approved', 1)
            ->whereNull('pl.id')
            ->whereRaw("UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) IN ('MULTIPLE', 'AR')")
            ->select(
                'tr.id as transaction_id', 'tr.local_id', 'tr.created_at', 'tr.amount',
                'tr.number as reference_no', 'tr.mode_of_payment',
                'tr.cashier as user_name', 'tb.name as table_name'
            );

        $this->excludeCancelled($orphanQuery, 'tr');
        $this->applyRange($orphanQuery, 'tr.created_at', $rangeStart, $rangeEnd);

        if (str_contains($key, 'cash on hand')) {
            $orphanQuery->whereRaw("UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) = 'CASH'");
        } elseif (str_contains($key, 'cash on bank')) {
            $orphanQuery->whereRaw(
                "(
                    UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%BANK%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%TRANSFER%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%GCASH%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%PAYMAYA%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%MAYA%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%ONLINE%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%E-WALLET%'
                    OR UPPER(TRIM(COALESCE(tr.mode_of_payment, ''))) LIKE '%EWALLET%'
                )"
            );
        } else {
            $orphanQuery->whereRaw('1 = 0');
        }

        foreach ($orphanQuery->orderBy('tr.created_at', 'asc')->get() as $s) {
            $remarks = !empty($s->table_name) ? $s->table_name : 'Sale • ' . ($s->mode_of_payment ?? '—');
            $rows->push([
                'id' => 'OS-' . $s->transaction_id, 'date_raw' => $s->created_at,
                'date' => date('M d, Y', strtotime($s->created_at)),
                'time' => date('h:i A', strtotime($s->created_at)),
                'reference_no' => $s->reference_no ?? '—', 'remarks' => $remarks,
                'user_name' => $s->user_name ?? '—',
                'debit' => (float) $s->amount, 'credit' => 0.0,
            ]);
        }

        /* 2) EXPENSES — inventory_transactions (payment_class = 1) → CREDIT to cash/bank/CIT */
        $expenseQuery = DB::table('inventory_transactions as t')
            ->leftJoin('inventory_etransaction as e', 't.e_id', '=', 'e.id')
            ->leftJoin('users as u', 't.user_id', '=', 'u.id')
            ->where('t.is_deleted', 0)
            ->where('t.payment_class', 1)
            ->where('t.type', '<>', 'TRADE PAYABLE')
            ->whereNotNull('t.category_id')
            ->whereNotNull('t.payment_class')
            ->whereNotNull('t.exp_type')
            ->select(
                't.id', 't.created_at', 't.amount', 't.is_paid', 't.remarks',
                'e.e_transid as reference_no', 'u.lastname as user_name'
            );

        $this->applyRange($expenseQuery, 't.created_at', $rangeStart, $rangeEnd);
        $expenseQuery->whereRaw("UPPER(TRIM(COALESCE(t.is_paid, ''))) <> ''");

        if (str_contains($key, 'cash on hand')) {
            $expenseQuery->whereRaw("UPPER(TRIM(COALESCE(t.is_paid, ''))) = 'CASH'");
        } elseif (str_contains($key, 'cash on bank')) {
            $expenseQuery->whereRaw(
                "(
                    UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%BANK%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%TRANSFER%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%DEPOSIT%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%GCASH%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%PAYMAYA%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%MAYA%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%ONLINE%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%E-WALLET%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%EWALLET%'
                )"
            );
        } elseif ($this->isChequeInTransit($accountName)) {
            $expenseQuery->whereRaw("UPPER(TRIM(COALESCE(t.is_paid, ''))) IN ('CHECK', 'CHEQUE')");
        } else {
            $expenseQuery->whereRaw('1 = 0');
        }

        foreach ($expenseQuery->orderBy('t.created_at', 'asc')->get() as $ex) {
            $rows->push([
                'id' => 'E-' . $ex->id, 'date_raw' => $ex->created_at,
                'date' => date('M d, Y', strtotime($ex->created_at)),
                'time' => date('h:i A', strtotime($ex->created_at)),
                'reference_no' => $ex->reference_no ?? '—',
                'remarks' => $ex->remarks ?? '—',
                'user_name' => $ex->user_name ?? '—',
                'debit' => 0.0, 'credit' => (float) $ex->amount,
            ]);
        }

        /* 3) EXPENSES — revolving_transactions (non-revolving-funded) → CREDIT cash/bank */
        $revQuery = DB::table('revolving_transactions as t')
            ->leftJoin('users as u', 'u.id', '=', DB::raw('CAST(t.user_id AS UNSIGNED)'))
            ->where('t.is_deleted', 0)
            ->whereNull('t.rev_id')
            ->select(
                't.id', 't.created_at', 't.amount', 't.is_paid', 't.remarks',
                't.particulars', 't.description', 't.inv_id as reference_no',
                'u.lastname as user_name'
            );

        $this->applyRange($revQuery, 't.created_at', $rangeStart, $rangeEnd);
        $revQuery->whereRaw("UPPER(TRIM(COALESCE(t.is_paid, ''))) <> ''");

        if (str_contains($key, 'cash on hand')) {
            $revQuery->whereRaw("UPPER(TRIM(COALESCE(t.is_paid, ''))) = 'CASH'");
        } elseif (str_contains($key, 'cash on bank')) {
            $revQuery->whereRaw(
                "(
                    UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%BANK%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%TRANSFER%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%DEPOSIT%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%GCASH%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%PAYMAYA%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%MAYA%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%ONLINE%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%E-WALLET%'
                    OR UPPER(TRIM(COALESCE(t.is_paid, ''))) LIKE '%EWALLET%'
                )"
            );
        } elseif ($this->isChequeInTransit($accountName)) {
            $revQuery->whereRaw("UPPER(TRIM(COALESCE(t.is_paid, ''))) IN ('CHECK', 'CHEQUE')");
        } else {
            $revQuery->whereRaw('1 = 0');
        }

        foreach ($revQuery->orderBy('t.created_at', 'asc')->get() as $ex) {
            $remarks = trim((string) ($ex->particulars ?? ''));
            if ($remarks === '') $remarks = trim((string) ($ex->remarks ?? ''));
            if ($remarks === '') $remarks = trim((string) ($ex->description ?? ''));
            if ($remarks === '') $remarks = '—';

            $rows->push([
                'id' => 'REV-' . $ex->id, 'date_raw' => $ex->created_at,
                'date' => date('M d, Y', strtotime($ex->created_at)),
                'time' => date('h:i A', strtotime($ex->created_at)),
                'reference_no' => $ex->reference_no ?? '—',
                'remarks' => $remarks,
                'user_name' => $ex->user_name ?? '—',
                'debit' => 0.0, 'credit' => (float) $ex->amount,
            ]);
        }

        /* 3b) REVOLVING FUND EXPENSES → CREDIT Cash on Bank */
        if (str_contains($key, 'cash on bank')) {

            $revFundBankQuery = DB::table('revolving_transactions as t')
                ->leftJoin('users as u', 'u.id', '=', DB::raw('CAST(t.user_id AS UNSIGNED)'))
                ->leftJoin('revolving_budgets as rb', 'rb.id', '=', 't.rev_id')
                ->where('t.is_deleted', 0)
                ->whereNotNull('t.rev_id')
                ->select(
                    't.id', 't.created_at', 't.amount', 't.is_paid', 't.remarks',
                    't.particulars', 't.description', 't.inv_id as reference_no',
                    't.rev_id', 'u.lastname as user_name', 'rb.classification as budget_name'
                );

            $this->applyRange($revFundBankQuery, 't.created_at', $rangeStart, $rangeEnd);

            foreach ($revFundBankQuery->orderBy('t.created_at', 'asc')->get() as $ex) {
                $remarks = trim((string) ($ex->particulars ?? ''));
                if ($remarks === '') $remarks = trim((string) ($ex->remarks ?? ''));
                if ($remarks === '') $remarks = trim((string) ($ex->description ?? ''));
                if ($remarks === '') $remarks = 'Revolving Fund Expense';
                if (!empty($ex->budget_name)) $remarks .= ' • ' . $ex->budget_name;

                $rows->push([
                    'id' => 'RF-BANK-' . $ex->id, 'date_raw' => $ex->created_at,
                    'date' => date('M d, Y', strtotime($ex->created_at)),
                    'time' => date('h:i A', strtotime($ex->created_at)),
                    'reference_no' => $ex->reference_no ?? '—',
                    'remarks' => $remarks,
                    'user_name' => $ex->user_name ?? '—',
                    'debit' => 0.0, 'credit' => (float) $ex->amount,
                ]);
            }
        }

        return $rows->sortBy('date_raw')->values()->map(function ($r) {
            unset($r['date_raw']);
            $r['balance'] = 0.0;
            return $r;
        });
    }

    protected function excludeCancelled($query, string $alias = ''): void
    {
        $col = $alias !== '' ? "{$alias}.order_status" : 'order_status';

        $query->whereRaw(
            "UPPER(TRIM(COALESCE({$col}, ''))) <> 'CANCELLED'"
        );
    }

    /* =====================================================================
     *  TOTALS
     * ===================================================================== */
    protected function calculateTotals(array $trialBalances)
    {
        $totals = $this->emptyTotals();

        foreach ($trialBalances as $item) {
            $totals['brought_forward_debit']  += (float) ($item->brought_forward_debit ?? 0);
            $totals['brought_forward_credit'] += (float) ($item->brought_forward_credit ?? 0);
            $totals['beginning_balance']      += (float) ($item->beginning_balance ?? 0);
            $totals['period_debit']           += (float) ($item->period_debit ?? 0);
            $totals['period_credit']          += (float) ($item->period_credit ?? 0);
            $totals['balance_debit']          += (float) ($item->balance_debit ?? 0);
            $totals['balance_credit']         += (float) ($item->balance_credit ?? 0);
            $totals['balance']                += (float) ($item->balance ?? 0);
        }

        return $totals;
    }

    /* =====================================================================
     *  EXPORTS
     * ===================================================================== */
    public function exportCsv(Request $request)
    {
        [, , $fromDate, $toDate] = $this->resolvePeriod($request);

        $search              = $request->input('search');
        $dimensionA          = $request->input('dimension_a', 'all');
        $dimensionB          = $request->input('dimension_b', 'all');
        $includeZeroBalances = $request->boolean('include_zero_balances');
        $showClosed          = $request->boolean('show_closed');

        $trialBalances = $this->buildTrialBalances(
            $search, $dimensionA, $dimensionB, $showClosed,
            $fromDate, $toDate, $includeZeroBalances
        );
        $totals = $this->calculateTotals($trialBalances);

        $filename = 'Trial_Balance_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($handle, [
            'Account Code', 'Account Name', 'Category', 'Classification',
            'Brought Forward Debit', 'Brought Forward Credit',
            'Period Debit', 'Period Credit',
            'Balance Debit', 'Balance Credit'
        ]);

        foreach ($trialBalances as $item) {
            fputcsv($handle, [
                $item->account_code,
                $item->account_name,
                $item->chart_category ?? '',
                $item->classification ?? '',
                number_format($item->brought_forward_debit, 2),
                number_format($item->brought_forward_credit, 2),
                number_format($item->period_debit, 2),
                number_format($item->period_credit, 2),
                number_format($item->balance_debit, 2),
                number_format($item->balance_credit, 2)
            ]);
        }

        fputcsv($handle, [
            'TOTAL', '', '', '',
            number_format($totals['brought_forward_debit'], 2),
            number_format($totals['brought_forward_credit'], 2),
            number_format($totals['period_debit'], 2),
            number_format($totals['period_credit'], 2),
            number_format($totals['balance_debit'], 2),
            number_format($totals['balance_credit'], 2)
        ]);

        fclose($handle);
        exit;
    }

    public function exportPdf(Request $request)
    {
        [, , $fromDate, $toDate] = $this->resolvePeriod($request);

        $search              = $request->input('search');
        $dimensionA          = $request->input('dimension_a', 'all');
        $dimensionB          = $request->input('dimension_b', 'all');
        $includeZeroBalances = $request->boolean('include_zero_balances');
        $showClosed          = $request->boolean('show_closed');

        $trialBalances = $this->buildTrialBalances(
            $search, $dimensionA, $dimensionB, $showClosed,
            $fromDate, $toDate, $includeZeroBalances
        );
        $totals = $this->calculateTotals($trialBalances);

        return view('admin.reports.trial_balance_pdf', compact(
            'fromDate', 'toDate', 'trialBalances', 'totals',
            'dimensionA', 'dimensionB'
        ));
    }

    public function exportExcel(Request $request)
    {
        return response()->json(['message' => 'Excel export not implemented yet']);
    }
}