<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SaveSaleReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->date('start_date')?->startOfDay() ?? now()->startOfDay();
        $endDate = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();
        $summary = $this->summary($startDate, $endDate);

        return view('user.cashier.submit-salesreport', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = now()->parse($validated['start_date'])->startOfDay();
        $endDate = now()->parse($validated['end_date'])->endOfDay();
        $summary = $this->summary($startDate, $endDate);
        $now = now();

        if (!Schema::hasTable('sales_report_transactions_duplicate') || !Schema::hasTable('ordered_duplicate')) {
            abort(500, 'Sales report duplicate tables are not available.');
        }

        DB::transaction(function () use ($startDate, $endDate, $summary, $now): void {
            $reportUuid = (string) Str::uuid();
            $reportId = DB::table('sales_report_transaction')->insertGetId([
            'trans_number' => 'SR-' . $now->format('YmdHis'),
            'completed' => $summary['completed'],
            'ar' => $summary['ar'],
            'cancelled' => $summary['cancelled'],
            'paid_sales' => $summary['paid_sales'],
            'ar_unpaid' => $summary['ar_unpaid'],
            'sales_report_form' => $summary['sales_report_form'],
            'total_sales' => $summary['total_sales'],
            'ar_collection_total' => $summary['total_collections'],
            'ar_collection_Form' => $summary['ar_2307'],
            'is_user' => Auth::id(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_uuid' => $reportUuid,
            'is_hidden' => 0,
            'is_deleted' => 0,
            'created_at' => $now,
            'updated_at' => $now,
            'is_sync' => 1,
            'store_code' => Auth::user()->store_code,
            'branch_code' => Auth::user()->store_code,
            ]);

            $transactions = DB::table('transactions')
                ->where('store_code', Auth::user()->store_code)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $duplicateColumns = array_flip(Schema::getColumnListing('sales_report_transactions_duplicate'));
            $orderedColumns = array_flip(Schema::getColumnListing('ordered_duplicate'));

            foreach ($transactions as $transaction) {
                $transactionUuid = (string) Str::uuid();
                $duplicate = $this->copyColumns((array) $transaction, $duplicateColumns, [
                    'report_id' => $reportId,
                    'number' => $transaction->number ?? null,
                    'table_id' => $transaction->table ?? null,
                    'report_tran_uuid' => $reportUuid,
                    'is_uuid' => $transactionUuid,
                    'is_sync' => 1,
                    'local_id' => $transaction->local_id ?? $transaction->id,
                    'orig_local_id' => $transaction->local_id ?? $transaction->id,
                    'updated_at' => $now,
                    'store_code' => Auth::user()->store_code,
                ]);
                DB::table('sales_report_transactions_duplicate')->insert($duplicate);

                $orderedItems = DB::table('ordered')
                    ->where('tran_id', $transaction->local_id ?? $transaction->id)
                    ->where('store_code', Auth::user()->store_code)
                    ->get();

                foreach ($orderedItems as $item) {
                    $orderedUuid = (string) Str::uuid();
                    $ordered = $this->copyColumns((array) $item, $orderedColumns, [
                        'is_uuid' => $orderedUuid,
                        'is_sync' => 1,
                        'local_id' => $item->local_id ?? $item->id,
                        'updated_at' => $now,
                        'store_code' => Auth::user()->store_code,
                        'branch_code' => $transaction->branch_code ?? Auth::user()->store_code,
                        'price_type' => 0,
                        'cashier' => 0,
                        'role' => 0,
                        'lime_discount' => 0,
                        'is_pwd' => 0,
                        'is_odisc' => 0,
                        'remarks' => '',
                        'print' => 0,
                        'department' => 0,
                        'branch' => 0,
                        'for_delete' => 0,
                        'is_split' => 0,
                        'is_deduct' => 1,
                    ]);
                    DB::table('ordered_duplicate')->insert($ordered);
                }
            }
        });

        return redirect()->route('submit-salesreport.index')->with('message', 'Sales report submitted successfully.');
    }

    private function summary($startDate, $endDate): array
    {
        $empty = [
            'completed' => 0, 'ar' => 0, 'cancelled' => 0,
            'paid_sales' => 0, 'ar_unpaid' => 0, 'sales_report_form' => 0,
            'total_sales' => 0, 'total_collections' => 0, 'ar_2307' => 0,
        ];

        if (!Schema::hasTable('transactions')) {
            return $empty;
        }

        $transactions = DB::table('transactions')
            ->where('store_code', Auth::user()->store_code)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get(['status', 'order_status', 'total', 'amount', 'lessVatDeduction']);

        $summary = $empty;
        foreach ($transactions as $transaction) {
            $status = strtoupper((string) ($transaction->status ?? ''));
            $orderStatus = strtoupper((string) ($transaction->order_status ?? ''));
            $amount = (float) ($transaction->total ?? $transaction->amount ?? 0);
            $form2307 = (float) ($transaction->lessVatDeduction ?? 0);

            if ($orderStatus === 'CANCELLED') {
                $summary['cancelled']++;
                continue;
            }

            if ($status === 'PAID') {
                $summary['completed']++;
                $summary['paid_sales'] += $amount;
                $summary['total_collections'] += $amount;
            } else {
                $summary['ar']++;
                $summary['ar_unpaid'] += $amount;
            }

            $summary['sales_report_form'] += $form2307;
            $summary['total_sales'] += $amount;
            $summary['ar_2307'] += $status === 'PAID' ? 0 : $form2307;
        }

        return $summary;
    }

    private function copyColumns(array $source, array $columns, array $overrides = []): array
    {
        $data = array_intersect_key($source, $columns);

        foreach ($overrides as $column => $value) {
            if (isset($columns[$column])) {
                $data[$column] = $value;
            }
        }

        unset($data['id']);

        return $data;
    }
}