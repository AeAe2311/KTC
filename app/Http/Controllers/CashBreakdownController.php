<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CashBreakdownController extends Controller
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

        return view('user.cashier.cash-breakdown', compact(
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

        return redirect()->route('cash-breakdown.index', [
            'startDate' => $request->startDate ?? date('m/d/Y'),
            'endDate' => $request->endDate ?? date('m/d/Y'),
            'branch' => $selectedBranch,
            'page' => $page,
        ]);
    }

    // LINE 86: START of UPDATE of CAsh breakdown in Zreading 9-27-2026
    public function getCashBreakdown(Request $request)
    {
        try {
            $reportId = $request->report_id;
            $storeCode = auth()->user()->store_code;

            // Get the report with all cash denomination columns
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
            // Derive start/end dates — prefer request, fall back to the report's created_at
            $startDateInput = $request->input('start_date', $report->created_at);
            $endDateInput   = $request->input('end_date',   $report->created_at);

            $startDateOnly = date('Y-m-d', strtotime($startDateInput));
            $endDateOnly   = date('Y-m-d', strtotime($endDateInput));

            $startDate = $startDateOnly . ' 00:00:00';
            $endDate   = $endDateOnly   . ' 23:59:59';
            // Get cash breakdown data from the report
            $cashBreakdown = [
                'count_1000' => $report->count_1000 ?? 0,
                'count_500' => $report->count_500 ?? 0,
                'count_200' => $report->count_200 ?? 0,
                'count_100' => $report->count_100 ?? 0,
                'count_50' => $report->count_50 ?? 0,
                'count_20' => $report->count_20 ?? 0,
                'count_10' => $report->count_10 ?? 0,
                'count_5' => $report->count_5 ?? 0,
                'count_1' => $report->count_1 ?? 0,
                'count_50cents' => $report->count_50cents ?? 0,
                'count_10cents' => $report->count_10cents ?? 0,
                'count_5cents' => $report->count_5cents ?? 0,
                'cash_variance' => $report->cash_variance ?? 0,
            ];

            // Calculate total cash
            $totalCash = 0;
            $denominationValues = [
                'count_1000' => 1000,
                'count_500' => 500,
                'count_200' => 200,
                'count_100' => 100,
                'count_50' => 50,
                'count_20' => 20,
                'count_10' => 10,
                'count_5' => 5,
                'count_1' => 1,
                'count_50cents' => 0.50,
                'count_10cents' => 0.10,
                'count_5cents' => 0.05,
            ];

            $denominationTotals = [];
            foreach ($denominationValues as $key => $value) {
                $count = $cashBreakdown[$key] ?? 0;
                $denominationTotals[$key] = [
                    'count' => $count,
                    'total' => $count * $value,
                    'value' => $value
                ];
                $totalCash += $count * $value;
            }

            // Get cash sales (completed transactions with cash payment)

        // In your getCashBreakdown method, add these debug lines:

            $cashMop = DB::table('sales_report_mop')
                ->where('created_at', '>=', $report->created_at)
                ->where('store_code', $storeCode)
                ->where('trans_id', $report->local_id)
                ->where('label', 'CASH')
                ->where('is_deleted', '!=', 1)
                ->first();

            // Get AR collection cash within the date range
            $arCollectionCash = DB::table('payment_logs')
                ->where('store_code', $storeCode)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('is_deleted', '!=', 1)
                ->where('label', 'CASH')
                ->where('type', '=', 1)
                ->sum('amount');

            $cashSales = (object) [
                'amount' => (float) ($cashMop->amount ?? 0) + (float) $arCollectionCash,
            ];

            
            // Get total sales (all completed transactions)
            $totalSales = DB::table('sales_report_transactions_duplicate')
                ->where('store_code', $storeCode)
                ->where('report_id', $report->local_id)
                ->where('status', 'COMPLETED')
                ->sum('total');

            // Render the cash breakdown view
            $html = view('user.cashier.partials.cash_breakdown_modal_content', [
                'report' => $report,
                'cashBreakdown' => $cashBreakdown,
                'denominationTotals' => $denominationTotals,
                'totalCash' => $totalCash,
                'cashSales' => $cashSales,
                'totalSales' => $totalSales,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'report_number' => $report->trans_number
            ]);

        } catch (\Exception $e) {
            \Log::error('CashBreakdownController::getCashBreakdown error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // LINE 224: END OF UPDATE for Cashbreakdown in ZREADING 9-27-2026
}