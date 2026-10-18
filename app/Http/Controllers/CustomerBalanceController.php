<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CustomerBalanceController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $startDate = $request->has('startDate') 
            ? date('Y-m-d', strtotime($request->get('startDate')))
            : date('Y-m-d');
        
        $endDate = $request->has('endDate')
            ? date('Y-m-d', strtotime($request->get('endDate')))
            : date('Y-m-d');
        
        $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
        $selectedStatus = $request->get('status', 'all');
        $selectedType = $request->get('type', 'all');
        $selectedOrderStatus = $request->get('order_status', 'all');
        $clientName = $request->input('client_name', '');
        $selectedClient = $request->get('client_filter', '');
        $page = (int) $request->get('page', 1);
        $perPage = 100;

        // Get valid table IDs
        $validTableIds = DB::table('tables')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->pluck('id')
            ->toArray();

        // Get matching table IDs for client name search
        $matchingTableIds = [];
        if (!empty($clientName)) {
            $matchingTableIds = DB::table('tables')
                ->where('name', 'LIKE', '%' . $clientName . '%')
                ->where('is_deleted', 0)
                ->where('store_code', auth()->user()->store_code)
                ->pluck('id')
                ->toArray();
        }

        // Helper to apply common filters to a query
        $applyFilters = function($query) use ($selectedStatus, $selectedType, $selectedOrderStatus, $selectedClient, $clientName, $matchingTableIds, $validTableIds) {
            $query->whereIn('transactions.table', $validTableIds);
            
            if ($selectedStatus != 'all') {
                $query->where('transactions.status', $selectedStatus);
            }
            if ($selectedType != 'all') {
                $query->where('transactions.type', $selectedType);
            }
            if ($selectedOrderStatus != 'all') {
                $query->where('transactions.order_status', $selectedOrderStatus);
            }
            if ($selectedClient) {
                $query->where('transactions.table', $selectedClient);
            }
            if (!empty($clientName)) {
                $query->where(function($q) use ($clientName, $matchingTableIds) {
                    $q->where('transactions.number', 'LIKE', '%' . $clientName . '%')
                      ->orWhere('transactions.payor_name', 'LIKE', '%' . $clientName . '%')
                      ->orWhereIn('transactions.table', $matchingTableIds);
                });
            }
        };

        // 1. OPEN BALANCE (ALL UNPAID transactions BEFORE start date - IGNORES status filter)
        $openBalanceQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                'transactions.total as charges',
                'transactions.total as credits',
                'transactions.amount as allocated',
                'transactions.mode_of_payment',
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Open Balance' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("1 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.order_status', '!=', 'CANCELLED')  // ken update aug. 26, 2026
            ->where('transactions.created_at', '<', $startDate)  // ALL transactions BEFORE start date
            ->whereIn('transactions.table', $validTableIds);

        // Apply ONLY client and search filters (EXCLUDE status filter)
        if ($selectedClient) {
            $openBalanceQuery->where('transactions.table', $selectedClient);
        }

        if (!empty($clientName)) {
            $matchingTableIds = DB::table('tables')
                ->where('name', 'LIKE', '%' . $clientName . '%')
                ->where('is_deleted', 0)
                ->where('store_code', auth()->user()->store_code)
                ->pluck('id')
                ->toArray();

            $openBalanceQuery->where(function($q) use ($clientName, $matchingTableIds) {
                $q->where('transactions.number', 'LIKE', '%' . $clientName . '%')
                ->orWhere('transactions.payor_name', 'LIKE', '%' . $clientName . '%')
                ->orWhereIn('transactions.table', $matchingTableIds);
            });
        }

        // 2. SALES INVOICES (within date range)
        $salesQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                'transactions.total as charges',
                DB::raw('0 as credits'),
                DB::raw('COALESCE(transactions.amount, 0) as allocated'),
                'transactions.mode_of_payment',
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Sales Invoice' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.order_status', '!=', 'CANCELLED')  // ken update aug. 26, 2026
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($salesQuery);

        // 3. PAYMENT LOGS (from payment_logs table)
        $paymentQuery = DB::table('payment_logs')
            ->select(
                'payment_logs.id',
                'transactions.number',
                DB::raw('0 as charges'),
                'payment_logs.total as credits',
                DB::raw('payment_logs.amount as allocated'),
                DB::raw("'AR' as mode_of_payment"),
                'payment_logs.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'payment_logs.created_at',
                'payment_logs.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("1 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('transactions', function($join) {
                $join->on('payment_logs.tran_id', '=', 'transactions.local_id')
                    ->on('payment_logs.tran_number', '=', 'transactions.number');
            })
            ->leftJoin('settings', 'transactions.store_code', '=', 'settings.store_code')
            ->leftJoin('tables', 'transactions.table', '=', 'tables.id')
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('payment_logs.is_deleted', 0)
            ->where('transactions.order_status', '!=', 'CANCELLED')  // ken update aug. 26, 2026
            ->whereBetween('payment_logs.created_at', [$startDate, $eendDate])
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($paymentQuery);

        // 4. PAID DIRECT (transactions with direct payment, no payment_logs)
        $paidDirectQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                DB::raw('0 as charges'),
                DB::raw('COALESCE(transactions.amount, 0) as credits'),
                DB::raw('COALESCE(transactions.amount, 0) as allocated'),
                DB::raw("'AR' as mode_of_payment"),
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.status', 'PAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')  // ken update aug. 26, 2026
            ->where('transactions.amount', '>', 0)
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->whereIn('transactions.table', $validTableIds)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('payment_logs')
                    ->whereColumn('payment_logs.tran_id', 'transactions.local_id')
                    ->whereColumn('payment_logs.tran_number', 'transactions.number')
                    ->where('payment_logs.is_deleted', 0);
            });

        $applyFilters($paidDirectQuery);

        // 5. UNPAID PAYMENTS (unpaid transactions shown as payments)
        /* LINE 268: start of removed code block by ken 9-2-2026  $unpaidPaymentQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                DB::raw('0 as charges'),
                'transactions.total as credits',
                'transactions.amount as allocated',                
                DB::raw("'AR' as mode_of_payment"),
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.status', 'UNPAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')  // ken update aug. 26, 2026
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($unpaidPaymentQuery); LINE 308: end of removed code block by ken 9-2-2026*/

        // Combine all queries and paginate
        $results = DB::query()
            ->fromSub(
                $openBalanceQuery
                    ->unionAll($salesQuery)
                    ->unionAll($paymentQuery)
                    ->unionAll($paidDirectQuery),
                    /* ->unionAll($unpaidPaymentQuery), LINE 317: ken remove this line 9-2-2026*/
                'combined'
            )
            ->orderBy('combined.table', 'asc')
            ->orderByDesc('combined.is_open_balance')
            ->orderByDesc('combined.created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        // Get dropdown data
        $settings = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->where('is_deleted', 0)
            ->first();

        $branches = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        $clients = DB::table('tables')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Return view
        return view('user.cashier.ar.customer-balance', [
            'results' => $results,
            'settings' => $settings,
            'resultsCount' => $results->count(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branches' => $branches,
            'selectedStatus' => $selectedStatus,
            'selectedType' => $selectedType,
            'selectedOrderStatus' => $selectedOrderStatus,
            'clients' => $clients,
            'clientName' => $clientName,
            'selectedClient' => $selectedClient,
            'page' => $page,
            'menuCount' => $results->total(),
            'perPage' => $perPage
        ]);
    }
    // ken start added print method Aug. 26 2026
    public function printSingleClient(Request $request, $clientId)
    {
        // Get filter parameters
        $startDate = $request->has('startDate') 
            ? date('Y-m-d', strtotime($request->get('startDate')))
            : date('Y-m-d');
        
        $endDate = $request->has('endDate')
            ? date('Y-m-d', strtotime($request->get('endDate')))
            : date('Y-m-d');
        
        $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
        $selectedStatus = $request->get('status', 'all');
        $selectedType = $request->get('type', 'all');
        $selectedOrderStatus = $request->get('order_status', 'all');
        $clientName = $request->input('client_name', '');
        
        // Get valid table IDs
        $validTableIds = DB::table('tables')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->pluck('id')
            ->toArray();

        // Helper to apply common filters
        $applyFilters = function($query) use ($selectedStatus, $selectedType, $selectedOrderStatus, $clientId, $validTableIds) {
            $query->whereIn('transactions.table', $validTableIds)
                ->where('transactions.table', $clientId); // Filter by specific client
            
            if ($selectedStatus != 'all') {
                $query->where('transactions.status', $selectedStatus);
            }
            if ($selectedType != 'all') {
                $query->where('transactions.type', $selectedType);
            }
            if ($selectedOrderStatus != 'all') {
                $query->where('transactions.order_status', $selectedOrderStatus);
            }
        };

        // 1. OPEN BALANCE
        $openBalanceQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                'transactions.total as charges',
                'transactions.total as credits',
                'transactions.amount as allocated',
                'transactions.mode_of_payment',
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Open Balance' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("1 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->where('transactions.created_at', '<', $startDate)
            ->where('transactions.table', $clientId)
            ->whereIn('transactions.table', $validTableIds);

        // 2. SALES INVOICES
        $salesQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                'transactions.total as charges',
                DB::raw('0 as credits'),
                DB::raw('COALESCE(transactions.amount, 0) as allocated'),
                'transactions.mode_of_payment',
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Sales Invoice' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.table', $clientId)
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($salesQuery);

        // 3. PAYMENT LOGS
        $paymentQuery = DB::table('payment_logs')
            ->select(
                'payment_logs.id',
                'transactions.number',
                DB::raw('0 as charges'),
                'payment_logs.total as credits',
                DB::raw('payment_logs.amount as allocated'),
                DB::raw("'AR' as mode_of_payment"),
                'payment_logs.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'payment_logs.created_at',
                'payment_logs.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("1 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('transactions', function($join) {
                $join->on('payment_logs.tran_id', '=', 'transactions.local_id')
                    ->on('payment_logs.tran_number', '=', 'transactions.number');
            })
            ->leftJoin('settings', 'transactions.store_code', '=', 'settings.store_code')
            ->leftJoin('tables', 'transactions.table', '=', 'tables.id')
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('payment_logs.is_deleted', 0)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->whereBetween('payment_logs.created_at', [$startDate, $eendDate])
            ->where('transactions.table', $clientId)
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($paymentQuery);

        // 4. PAID DIRECT
        $paidDirectQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                DB::raw('0 as charges'),
                DB::raw('COALESCE(transactions.amount, 0) as credits'),
                DB::raw('COALESCE(transactions.amount, 0) as allocated'),
                DB::raw("'AR' as mode_of_payment"),
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.status', 'PAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->where('transactions.amount', '>', 0)
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.table', $clientId)
            ->whereIn('transactions.table', $validTableIds)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('payment_logs')
                    ->whereColumn('payment_logs.tran_id', 'transactions.local_id')
                    ->whereColumn('payment_logs.tran_number', 'transactions.number')
                    ->where('payment_logs.is_deleted', 0);
            });

        $applyFilters($paidDirectQuery);

        // 5. UNPAID PAYMENTS
        /* LINE 581: start of removed code block by ken 9-2-2026   $unpaidPaymentQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                DB::raw('0 as charges'),
                'transactions.total as credits',
                'transactions.amount as allocated',                
                DB::raw("'AR' as mode_of_payment"),
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.status', 'UNPAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.table', $clientId)
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($unpaidPaymentQuery); LINE 622: end of removed code block by ken 9-2-2026*/

        // Combine all queries (NO pagination - get ALL results for this client)
        $allResults = DB::query()
            ->fromSub(
                $openBalanceQuery
                    ->unionAll($salesQuery)
                    ->unionAll($paymentQuery)
                    ->unionAll($paidDirectQuery),
                    /* ->unionAll($unpaidPaymentQuery), LINE 631: ken remove this line 9-2-2026*/
                'combined'
            )
            ->orderBy('combined.table', 'asc')
            ->orderByDesc('combined.is_open_balance')
            ->orderByDesc('combined.created_at')
            ->get();

        // Get settings and client info
        $settings = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->where('is_deleted', 0)
            ->first();

        $client = DB::table('tables')
            ->where('id', $clientId)
            ->where('store_code', auth()->user()->store_code)
            ->first();

        // Return print view with client data
        return view('user.cashier.ar.customer-balance-print-single', [
            'results' => $allResults,
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'client' => $client,
            'clientId' => $clientId,
        ]);
    }

    public function printAllClients(Request $request)
    {
        // Get filter parameters (same as index)
        $startDate = $request->has('startDate') 
            ? date('Y-m-d', strtotime($request->get('startDate')))
            : date('Y-m-d');
        
        $endDate = $request->has('endDate')
            ? date('Y-m-d', strtotime($request->get('endDate')))
            : date('Y-m-d');
        
        $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
        $selectedStatus = $request->get('status', 'all');
        $selectedType = $request->get('type', 'all');
        $selectedOrderStatus = $request->get('order_status', 'all');
        $clientName = $request->input('client_name', '');
        $selectedClient = $request->get('client_filter', '');

        // Get valid table IDs
        $validTableIds = DB::table('tables')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->pluck('id')
            ->toArray();

        // Get matching table IDs for client name search
        $matchingTableIds = [];
        if (!empty($clientName)) {
            $matchingTableIds = DB::table('tables')
                ->where('name', 'LIKE', '%' . $clientName . '%')
                ->where('is_deleted', 0)
                ->where('store_code', auth()->user()->store_code)
                ->pluck('id')
                ->toArray();
        }

        // Helper to apply common filters
        $applyFilters = function($query) use ($selectedStatus, $selectedType, $selectedOrderStatus, $selectedClient, $clientName, $matchingTableIds, $validTableIds) {
            $query->whereIn('transactions.table', $validTableIds);
            
            if ($selectedStatus != 'all') {
                $query->where('transactions.status', $selectedStatus);
            }
            if ($selectedType != 'all') {
                $query->where('transactions.type', $selectedType);
            }
            if ($selectedOrderStatus != 'all') {
                $query->where('transactions.order_status', $selectedOrderStatus);
            }
            if ($selectedClient) {
                $query->where('transactions.table', $selectedClient);
            }
            if (!empty($clientName)) {
                $query->where(function($q) use ($clientName, $matchingTableIds) {
                    $q->where('transactions.number', 'LIKE', '%' . $clientName . '%')
                    ->orWhere('transactions.payor_name', 'LIKE', '%' . $clientName . '%')
                    ->orWhereIn('transactions.table', $matchingTableIds);
                });
            }
        };

        // 1. OPEN BALANCE
        $openBalanceQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                'transactions.total as charges',
                'transactions.total as credits',
                'transactions.amount as allocated',
                'transactions.mode_of_payment',
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Open Balance' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("1 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->where('transactions.created_at', '<', $startDate)
            ->whereIn('transactions.table', $validTableIds);

        if ($selectedClient) {
            $openBalanceQuery->where('transactions.table', $selectedClient);
        }
        if (!empty($clientName)) {
            $matchingTableIds = DB::table('tables')
                ->where('name', 'LIKE', '%' . $clientName . '%')
                ->where('is_deleted', 0)
                ->where('store_code', auth()->user()->store_code)
                ->pluck('id')
                ->toArray();

            $openBalanceQuery->where(function($q) use ($clientName, $matchingTableIds) {
                $q->where('transactions.number', 'LIKE', '%' . $clientName . '%')
                ->orWhere('transactions.payor_name', 'LIKE', '%' . $clientName . '%')
                ->orWhereIn('transactions.table', $matchingTableIds);
            });
        }

        // 2. SALES INVOICES
        $salesQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                'transactions.total as charges',
                DB::raw('0 as credits'),
                DB::raw('COALESCE(transactions.amount, 0) as allocated'),
                'transactions.mode_of_payment',
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Sales Invoice' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($salesQuery);

        // 3. PAYMENT LOGS
        $paymentQuery = DB::table('payment_logs')
            ->select(
                'payment_logs.id',
                'transactions.number',
                DB::raw('0 as charges'),
                'payment_logs.total as credits',
                DB::raw('payment_logs.amount as allocated'),
                DB::raw("'AR' as mode_of_payment"),
                'payment_logs.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'payment_logs.created_at',
                'payment_logs.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("1 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('transactions', function($join) {
                $join->on('payment_logs.tran_id', '=', 'transactions.local_id')
                    ->on('payment_logs.tran_number', '=', 'transactions.number');
            })
            ->leftJoin('settings', 'transactions.store_code', '=', 'settings.store_code')
            ->leftJoin('tables', 'transactions.table', '=', 'tables.id')
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('payment_logs.is_deleted', 0)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->whereBetween('payment_logs.created_at', [$startDate, $eendDate])
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($paymentQuery);

        // 4. PAID DIRECT
        $paidDirectQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                DB::raw('0 as charges'),
                DB::raw('COALESCE(transactions.amount, 0) as credits'),
                DB::raw('COALESCE(transactions.amount, 0) as allocated'),
                DB::raw("'AR' as mode_of_payment"),
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.status', 'PAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->where('transactions.amount', '>', 0)
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->whereIn('transactions.table', $validTableIds)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('payment_logs')
                    ->whereColumn('payment_logs.tran_id', 'transactions.local_id')
                    ->whereColumn('payment_logs.tran_number', 'transactions.number')
                    ->where('payment_logs.is_deleted', 0);
            });

        $applyFilters($paidDirectQuery);

        // 5. UNPAID PAYMENTS
        /* LINE 915: start of removed code block by ken 9-2-2026  $unpaidPaymentQuery = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                DB::raw('0 as charges'),
                'transactions.total as credits',
                'transactions.amount as allocated',                
                DB::raw("'AR' as mode_of_payment"),
                'transactions.amount as payment_amount',
                'transactions.payor_name',
                'transactions.payor_number',
                'transactions.type',
                'transactions.table',
                'transactions.status',
                'transactions.order_status',
                'transactions.order_remarks',
                'transactions.created_at',
                'transactions.updated_at',
                'transactions.store_code',
                'transactions.branch',
                'settings.name as branch_name',
                'tables.name as table_name',
                DB::raw("'Customer Payment' as trans_type"),
                DB::raw("0 as is_payment_log"),
                DB::raw("0 as is_open_balance")
            )
            ->leftJoin('settings', function($join) {
                $join->on('transactions.store_code', '=', 'settings.store_code')
                    ->where('settings.is_deleted', '=', 0);
            })
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->where('transactions.store_code', auth()->user()->store_code)
            ->where('transactions.status', 'UNPAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->whereIn('transactions.table', $validTableIds);

        $applyFilters($unpaidPaymentQuery); LINE 955: start of removed code block by ken 9-2-2026 */

        // Combine all queries (NO pagination - get ALL results)
        $allResults = DB::query()
            ->fromSub(
                $openBalanceQuery
                    ->unionAll($salesQuery)
                    ->unionAll($paymentQuery)
                    ->unionAll($paidDirectQuery),
                    /* ->unionAll($unpaidPaymentQuery), LINE 964: ken remove this line 9-2-2026*/
                'combined'
            )
            ->orderBy('combined.table', 'asc')
            ->orderByDesc('combined.is_open_balance')
            ->orderByDesc('combined.created_at')
            ->get();

        // Get settings
        $settings = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->where('is_deleted', 0)
            ->first();

        // Return print view with all results
        return view('user.cashier.ar.customer-balance-print-all', [
            'results' => $allResults,
            'settings' => $settings,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedStatus' => $selectedStatus,
            'selectedType' => $selectedType,
            'selectedOrderStatus' => $selectedOrderStatus,
            'clientName' => $clientName,
            'selectedClient' => $selectedClient,
        ]);
    }
    // ken end added print method Aug. 26 2026
    /**
     * Search clients method (AJAX)
     */
    public function searchClients(Request $request)
    {
        $search = $request->get('q', '');
        $clients = DB::table('tables')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->where('name', 'LIKE', '%' . $search . '%')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name as text']);
        
        return response()->json(['results' => $clients]);
    }

    /**
     * Search transactions method (AJAX)
     */
    public function searchTransactions(Request $request)
    {
        $search = $request->get('q', '');
        $startDate = $request->get('startDate', date('Y-m-d'));
        $endDate = $request->get('endDate', date('Y-m-d'));
        $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
        
        $validTableIds = DB::table('tables')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->pluck('id')
            ->toArray();
        
        $transactions = DB::table('transactions')
            ->select(
                'transactions.id',
                'transactions.number',
                'transactions.total',
                'transactions.amount',
                'transactions.payor_name',
                'transactions.table',
                'transactions.status',
                'transactions.created_at',
                'tables.name as table_name'
            )
            ->leftJoin('tables', function($join) {
                $join->on('transactions.table', '=', 'tables.id')
                    ->where('tables.is_deleted', '=', 0);
            })
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.store_code', auth()->user()->store_code)
            ->whereIn('transactions.table', $validTableIds)
            ->where(function($q) use ($search) {
                $q->where('transactions.number', 'LIKE', '%' . $search . '%')
                  ->orWhere('transactions.payor_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('tables.name', 'LIKE', '%' . $search . '%');
            })
            ->orderByDesc('transactions.id')
            ->get();
        
        return response()->json(['results' => $transactions]);
    }
}