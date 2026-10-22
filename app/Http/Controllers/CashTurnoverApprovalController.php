<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CashTurnoverApprovalController extends Controller
{
    private const PER_PAGE = 100;

    public function index(Request $request)
    {
        $storeCode      = Auth::user()->store_code;
        $startDate      = $request->get('startDate', date('Y-m-d'));
        $endDate        = $request->get('endDate', date('Y-m-d'));
        $selectedBranch = $request->get('branch', 'all');
        $statusFilter   = $request->get('status', 'all');
        $page           = max(1, (int) $request->get('page', 1));

        $branches = DB::table('settings')
            ->where('is_deleted', '=', 0)
            ->orderBy('name')
            ->get();

        $query = DB::table('sales_report_transaction as srt')
            ->leftJoin('users as u', 'srt.is_user', '=', 'u.id')
            ->leftJoin('approved_cash_turnover as act', function ($join) {
                $join->on('act.report_id', '=', 'srt.id');
            })
            ->leftJoin('users as approver', 'act.approved_by', '=', 'approver.id')
            ->where('srt.is_deleted', 0)
            ->select([
                'srt.*',
                'act.status as approval_status',
                'act.remarks as approval_remarks',
                'act.approve_at as approved_at',
                'act.approved_by as approved_by_id',
                DB::raw("CONCAT(COALESCE(u.firstname, ''), ' ', COALESCE(u.lastname, '')) as submitted_by_name"),
                DB::raw("CONCAT(COALESCE(approver.firstname, ''), ' ', COALESCE(approver.lastname, '')) as approved_by_name"),
            ]);

        if ($selectedBranch !== 'all') {
            $query->where('srt.store_code', $selectedBranch);
        }

        if ($startDate && $endDate) {
            $endDatePlusOne = date('Y-m-d', strtotime($endDate . ' +1 day'));
            $query->whereBetween('srt.created_at', [$startDate, $endDatePlusOne]);
        }

        if ($statusFilter !== 'all') {
            if ($statusFilter === 'PENDING') {
                $query->whereNull('act.id');
            } else {
                $query->where('act.status', $statusFilter);
            }
        }

        $totalCount = (clone $query)->count();

        $reports = $query
            ->orderBy('srt.id', 'desc')
            ->skip(($page - 1) * self::PER_PAGE)
            ->take(self::PER_PAGE)
            ->get();

        $denominationValues = [
            'count_1000' => 1000, 'count_500' => 500, 'count_200' => 200,
            'count_100' => 100, 'count_50' => 50, 'count_20' => 20,
            'count_10' => 10, 'count_5' => 5, 'count_1' => 1,
            'count_50cents' => 0.50, 'count_10cents' => 0.10, 'count_5cents' => 0.05,
        ];

        $reports->each(function ($report) use ($denominationValues) {
            $total = 0;
            foreach ($denominationValues as $key => $value) {
                $total += ($report->$key ?? 0) * $value;
            }
            $report->total_cash = $total;
            $report->approval_status = $report->approval_status ?? 'PENDING';
            $report->submitted_by_name = trim($report->submitted_by_name) ?: '—';
            $report->approved_by_name = trim($report->approved_by_name) ?: '—';
        });

        return view('user.cashier.cash-turnover-approval', [
            'reports'        => $reports,
            'branches'       => $branches,
            'selectedBranch' => $selectedBranch,
            'statusFilter'   => $statusFilter,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'page'           => $page,
            'totalCount'     => $totalCount,
            'perPage'        => self::PER_PAGE,
        ]);
    }

    public function getBreakdown(Request $request)
    {
        try {
            $reportId = $request->report_id;

            $report = DB::table('sales_report_transaction')
                ->where('is_deleted', 0)
                ->where(function ($q) use ($reportId) {
                    $q->where('id', $reportId)
                      ->orWhere('local_id', $reportId)
                      ->orWhere('is_uuid', $reportId);
                })
                ->first();

            if (!$report) {
                return response()->json(['success' => false, 'message' => 'Report not found']);
            }

            $reportStoreCode = $report->store_code;

            $denominationValues = [
                'count_1000' => 1000, 'count_500' => 500, 'count_200' => 200,
                'count_100' => 100, 'count_50' => 50, 'count_20' => 20,
                'count_10' => 10, 'count_5' => 5, 'count_1' => 1,
                'count_50cents' => 0.50, 'count_10cents' => 0.10, 'count_5cents' => 0.05,
            ];

            // Check if there is a saved pending record in approved_cash_turnover
            $pending = DB::table('approved_cash_turnover')
                ->where('report_id', $report->id)
                ->where('status', 'PENDING')
                ->first();

            // If pending exists, use its counts instead of the original report's counts
            $source = $pending ?: $report;

            $denominationTotals = [];
            $totalCash = 0;
            foreach ($denominationValues as $key => $value) {
                $count = $source->$key ?? 0;
                $denominationTotals[$key] = [
                    'count' => $count,
                    'total' => $count * $value,
                    'value' => $value,
                ];
                $totalCash += $count * $value;
            }

            $startDate = date('Y-m-d 00:00:00', strtotime($report->created_at));
            $endDate   = date('Y-m-d 23:59:59', strtotime($report->created_at));

            $cashMop = DB::table('sales_report_mop')
                ->where('created_at', '>=', $report->created_at)
                ->where('store_code', $reportStoreCode)
                ->where('trans_id', $report->local_id)
                ->where('label', 'CASH')
                ->where('is_deleted', '!=', 1)
                ->first();

            $arCollectionCash = DB::table('payment_logs')
                ->where('store_code', $reportStoreCode)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('is_deleted', '!=', 1)
                ->where('label', 'CASH')
                ->where('type', '=', 1)
                ->sum('amount');

            $cashSales = (object) [
                'amount' => (float) ($cashMop->amount ?? 0) + (float) $arCollectionCash,
            ];

            $approval = DB::table('approved_cash_turnover')
                ->leftJoin('users as approver', 'approved_cash_turnover.approved_by', '=', 'approver.id')
                ->select([
                    'approved_cash_turnover.*',
                    DB::raw("CONCAT(
                        COALESCE(approver.firstname, ''),
                        ' ',
                        COALESCE(approver.lastname, '')
                    ) as approved_by_name"),
                ])
                ->where('approved_cash_turnover.report_id', $report->id)
                ->whereIn('approved_cash_turnover.status', ['APPROVED', 'REJECTED'])
                ->first();

            // Compute variance dynamically (Total Cash - Cash Sale)
            $cashBreakdown = (array) $source;
            $cashBreakdown['cash_variance'] = $totalCash - $cashSales->amount;

            $html = view('user.cashier.partials.cash_turnover_modal_content', [
                'report'             => $report,
                'cashBreakdown'      => $cashBreakdown,
                'denominationTotals' => $denominationTotals,
                'totalCash'          => $totalCash,
                'cashSales'          => $cashSales,
                'totalSales'         => 0,
                'approval'           => $approval,
            ])->render();

            return response()->json([
                'success'       => true,
                'html'          => $html,
                'report_number' => $report->trans_number,
                'is_approved'   => $approval ? true : false,
            ]);
        } catch (\Exception $e) {
            \Log::error('CashTurnoverApprovalController::getBreakdown error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save edited denomination counts as a PENDING record.
     */
    public function saveCounts(Request $request)
    {
        $request->validate([
            'report_id' => 'required',
            'counts'    => 'required|array',
            'remarks'   => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        $report = DB::table('sales_report_transaction')
            ->where('is_deleted', 0)
            ->where('id', $request->report_id)
            ->first();

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found']);
        }

        // Block editing if already processed
        $existing = DB::table('approved_cash_turnover')
            ->where('report_id', $report->id)
            ->whereIn('status', ['APPROVED', 'REJECTED'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Record already processed. Cannot edit counts.']);
        }

        $counts = $request->input('counts', []);

        $denominationValues = [
            'count_1000' => 1000, 'count_500' => 500, 'count_200' => 200,
            'count_100' => 100, 'count_50' => 50, 'count_20' => 20,
            'count_10' => 10, 'count_5' => 5, 'count_1' => 1,
            'count_50cents' => 0.50, 'count_10cents' => 0.10, 'count_5cents' => 0.05,
        ];

        $totalCash = 0;
        $updateData = [];
        foreach ($denominationValues as $key => $value) {
            $count = isset($counts[$key]) ? (float) $counts[$key] : 0;
            $updateData[$key] = $count;
            $totalCash += $count * $value;
        }

        // Compute cash sale (same logic as getBreakdown)
        $reportStoreCode = $report->store_code;
        $startDate = date('Y-m-d 00:00:00', strtotime($report->created_at));
        $endDate   = date('Y-m-d 23:59:59', strtotime($report->created_at));

        $cashMop = DB::table('sales_report_mop')
            ->where('created_at', '>=', $report->created_at)
            ->where('store_code', $reportStoreCode)
            ->where('trans_id', $report->local_id)
            ->where('label', 'CASH')
            ->where('is_deleted', '!=', 1)
            ->first();

        $arCollectionCash = DB::table('payment_logs')
            ->where('store_code', $reportStoreCode)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('is_deleted', '!=', 1)
            ->where('label', 'CASH')
            ->where('type', '=', 1)
            ->sum('amount');

        $cashSales = (float) ($cashMop->amount ?? 0) + (float) $arCollectionCash;

        // Variance = total cash - cash sale
        $cashVariance = $totalCash - $cashSales;

        $pending = DB::table('approved_cash_turnover')
            ->where('report_id', $report->id)
            ->where('status', 'PENDING')
            ->first();

        $payload = array_merge($updateData, [
            'total_cash'    => $totalCash,
            'cash_variance' => $cashVariance,
            'remarks'       => $request->remarks,
            'submitted_by'  => $report->is_user ?? $userId,
            'status'        => 'PENDING',
            'approved_by'   => null,
            'approve_at'    => null,
            'store_code'    => $reportStoreCode,
            'report_id'     => $report->id,
            'updated_at'    => now(),
        ]);

        if ($pending) {
            DB::table('approved_cash_turnover')
                ->where('id', $pending->id)
                ->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('approved_cash_turnover')->insert($payload);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Denomination counts saved successfully.',
            'total_cash' => $totalCash,
            'variance'   => $cashVariance,
        ]);
    }

    public function approve(Request $request)
    {
        $request->validate([
            'report_id' => 'required',
            'remarks'   => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        $report = DB::table('sales_report_transaction')
            ->where('is_deleted', 0)
            ->where('id', $request->report_id)
            ->first();

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found']);
        }

        $existing = DB::table('approved_cash_turnover')
            ->where('report_id', $report->id)
            ->whereIn('status', ['APPROVED', 'REJECTED'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already processed.']);
        }

        $denominationValues = [
            'count_1000' => 1000, 'count_500' => 500, 'count_200' => 200,
            'count_100' => 100, 'count_50' => 50, 'count_20' => 20,
            'count_10' => 10, 'count_5' => 5, 'count_1' => 1,
            'count_50cents' => 0.50, 'count_10cents' => 0.10, 'count_5cents' => 0.05,
        ];

        // Prefer the saved PENDING record if it exists
        $pending = DB::table('approved_cash_turnover')
            ->where('report_id', $report->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pending) {
            $counts = [
                'count_1000'    => $pending->count_1000,
                'count_500'     => $pending->count_500,
                'count_200'     => $pending->count_200,
                'count_100'     => $pending->count_100,
                'count_50'      => $pending->count_50,
                'count_20'      => $pending->count_20,
                'count_10'      => $pending->count_10,
                'count_5'       => $pending->count_5,
                'count_1'       => $pending->count_1,
                'count_50cents' => $pending->count_50cents,
                'count_10cents' => $pending->count_10cents,
                'count_5cents'  => $pending->count_5cents,
            ];
            $totalCash = $pending->total_cash;
            $cashVariance = $pending->cash_variance;
        } else {
            $counts = [];
            $totalCash = 0;
            foreach ($denominationValues as $key => $value) {
                $c = $report->$key ?? 0;
                $counts[$key] = $c;
                $totalCash += $c * $value;
            }
            $cashVariance = $report->cash_variance ?? 0;
        }

        // If a PENDING exists, update it to APPROVED instead of inserting
        if ($pending) {
            DB::table('approved_cash_turnover')
                ->where('id', $pending->id)
                ->update([
                    'status'      => 'APPROVED',
                    'remarks'     => $request->remarks,
                    'approved_by' => $userId,
                    'approve_at'  => now(),
                    'updated_at'  => now(),
                ]);
        } else {
            DB::table('approved_cash_turnover')->insert(array_merge($counts, [
                'total_cash'    => $totalCash,
                'cash_variance' => $cashVariance,
                'remarks'       => $request->remarks,
                'submitted_by'  => $report->is_user ?? $userId,
                'status'        => 'APPROVED',
                'approved_by'   => $userId,
                'approve_at'    => now(),
                'store_code'    => $report->store_code,
                'report_id'     => $report->id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]));
        }

        return response()->json(['success' => true, 'message' => 'Cash turnover approved.']);
    }

    public function reject(Request $request)
    {
        $request->validate([
            'report_id' => 'required',
            'remarks'   => 'required|string|max:1000',
        ]);

        $userId = Auth::id();

        $report = DB::table('sales_report_transaction')
            ->where('is_deleted', 0)
            ->where('id', $request->report_id)
            ->first();

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'Report not found']);
        }

        $existing = DB::table('approved_cash_turnover')
            ->where('report_id', $report->id)
            ->whereIn('status', ['APPROVED', 'REJECTED'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already processed.']);
        }

        $denominationValues = [
            'count_1000' => 1000, 'count_500' => 500, 'count_200' => 200,
            'count_100' => 100, 'count_50' => 50, 'count_20' => 20,
            'count_10' => 10, 'count_5' => 5, 'count_1' => 1,
            'count_50cents' => 0.50, 'count_10cents' => 0.10, 'count_5cents' => 0.05,
        ];

        // Prefer the saved PENDING record if it exists
        $pending = DB::table('approved_cash_turnover')
            ->where('report_id', $report->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pending) {
            $counts = [
                'count_1000'    => $pending->count_1000,
                'count_500'     => $pending->count_500,
                'count_200'     => $pending->count_200,
                'count_100'     => $pending->count_100,
                'count_50'      => $pending->count_50,
                'count_20'      => $pending->count_20,
                'count_10'      => $pending->count_10,
                'count_5'       => $pending->count_5,
                'count_1'       => $pending->count_1,
                'count_50cents' => $pending->count_50cents,
                'count_10cents' => $pending->count_10cents,
                'count_5cents'  => $pending->count_5cents,
            ];
            $totalCash = $pending->total_cash;
            $cashVariance = $pending->cash_variance;
        } else {
            $counts = [];
            $totalCash = 0;
            foreach ($denominationValues as $key => $value) {
                $c = $report->$key ?? 0;
                $counts[$key] = $c;
                $totalCash += $c * $value;
            }
            $cashVariance = $report->cash_variance ?? 0;
        }

        if ($pending) {
            DB::table('approved_cash_turnover')
                ->where('id', $pending->id)
                ->update([
                    'status'      => 'REJECTED',
                    'remarks'     => $request->remarks,
                    'approved_by' => $userId,
                    'approve_at'  => now(),
                    'updated_at'  => now(),
                ]);
        } else {
            DB::table('approved_cash_turnover')->insert(array_merge($counts, [
                'total_cash'    => $totalCash,
                'cash_variance' => $cashVariance,
                'remarks'       => $request->remarks,
                'submitted_by'  => $report->is_user ?? $userId,
                'status'        => 'REJECTED',
                'approved_by'   => $userId,
                'approve_at'    => now(),
                'store_code'    => $report->store_code,
                'report_id'     => $report->id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]));
        }

        return response()->json(['success' => true, 'message' => 'Cash turnover rejected.']);
    }
}