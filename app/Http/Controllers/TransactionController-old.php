<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Log;

class TransactionController extends Controller
{
    public function index(Request $request){
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $eendDate = date('Y-m-d', strtotime('+1 day'));
        $selectedBranch = $request->get('branch', 'all');

        $settings = DB::table('settings')->where('id',  1)->first();
        
        // Get branches for dropdown
        $branches = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Base query
        $query = DB::table('transactions')
            ->select('transactions.id', 'transactions.number as nn', 'transactions.total as amount', 
                    'transactions.mode_of_payment','transactions.slug', 'transactions.type', 
                    'transactions.created_at as date','transactions.sales_type', 'tables.name as table',
                    'transactions.local_id','transactions.branch_code','settings.name as branchName', 'transactions.status as tstatus', 'transactions.order_status as ostatus') // Already has branch_code
            ->join('tables', 'transactions.table', '=', 'tables.id')
            ->leftJoin('settings', function ($join) {
                $join
                    ->on('transactions.store_code', '=', 'settings.store_code');
            })
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            //->where('transactions.status', 'PAID')
            //->where('transactions.order_status', '!=', 'CANCELLED')
            ->where('transactions.store_code', auth()->user()->store_code);

        // Apply branch filter if not 'all'
        if ($selectedBranch != 'all') {
            $query->where('transactions.branch', $selectedBranch);
        }

        $results = $query->orderBy('transactions.id', 'desc')->get();

        $resultsCount = $query->count();

        /* Start of Audited total */
        // $auditedQuery = DB::table('transactions')
        //     ->where('order_status', '!=', 'CANCELLED')
        //     ->where('transactions.status', 'PAID')
        //     ->whereDate('created_at', $startDate)
        //     ->where('store_code', auth()->user()->store_code);

        // $nonAuditedQuery = DB::table('transactions')
        //     ->where('order_status', '!=', 'CANCELLED')
        //     ->where('transactions.status', 'UNPAID')
        //     ->whereDate('created_at', $startDate)
        //     ->where('store_code', auth()->user()->store_code);

        // // Apply branch filter to audit totals
        // if ($selectedBranch != 'all') {
        //     $auditedQuery->where('branch_code', $selectedBranch);
        //     $nonAuditedQuery->where('branch_code', $selectedBranch);
        // }

        // $audited = number_format($auditedQuery->sum('total'), 2, '.', ',');
        // $nonAudited = number_format($nonAuditedQuery->sum('total'), 2, '.', ',');
        
        
        $auditedQuery = DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            // ->where('transactions.status', 'PAID')
            ->whereDate('created_at', $startDate)
            ->where('store_code', auth()->user()->store_code);

        $nonAuditedQuery = DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('transactions.status', 'UNPAID')
            ->whereDate('created_at', $startDate)
            ->where('store_code', auth()->user()->store_code);

        // Apply branch filter to audit totals
        if ($selectedBranch != 'all') {
            $auditedQuery->where('branch_code', $selectedBranch);
            $nonAuditedQuery->where('branch_code', $selectedBranch);
        }

        // $audited = number_format($auditedQuery->sum('amount'), 2, '.', ',');
          $audited = number_format(
            $auditedQuery
                ->selectRaw('SUM(CASE WHEN amount > total THEN total ELSE amount END) as adjusted_total')
                ->value('adjusted_total'),
            2, '.', ','
        );
        $nonAuditedTotal = (clone $nonAuditedQuery)->sum('total');
        $nonAuditedPaid = (clone $nonAuditedQuery)->sum('amount');
        
        $nonAudited = number_format($nonAuditedTotal - $nonAuditedPaid, 2, '.', ',');
        
        
        /* end of Audited total */
        
        

        return view('user.cashier.transactions', compact('results', 'settings', 'resultsCount', 
                    'startDate', 'endDate', 'audited', 'nonAudited', 'branches', 'selectedBranch'));
    }

    public function generate(Request $request){
        $startDate = date('Y-m-d', strtotime($request->startDate));
        $endDate = date('Y-m-d', strtotime($request->endDate));
        $eendDate = date('Y-m-d', strtotime($request->endDate . '+1 day'));
        $selectedBranch = $request->branch ?? 'all';

        $settings = DB::table('settings')->where('id', 1)->first();
        
        // Get branches for dropdown
        $branches = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Base query
        $query = DB::table('transactions')
            ->select('transactions.id', 'transactions.number as nn', 'transactions.total as amount', 
                    'transactions.mode_of_payment', 'transactions.type', 'transactions.created_at as date',
                    'transactions.slug','transactions.sales_type', 'tables.name as table','transactions.local_id', 'transactions.status as tstatus', 'transactions.order_status as ostatus')
            ->join('tables', 'transactions.table', '=', 'tables.id')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            //->where('transactions.status', 'PAID')
            //->where('transactions.order_status', '!=', 'CANCELLED')
            ->where('transactions.store_code', auth()->user()->store_code);

        // Apply branch filter if not 'all'
        if ($selectedBranch != 'all') {
            $query->where('transactions.branch_code', $selectedBranch);
        }

        $results = $query->orderBy('transactions.id', 'desc')->get();
        $resultsCount = $query->count();
            
        /* Start of Audited total */
        // $auditedQuery = DB::table('transactions')
        //     ->where('order_status', '!=', 'CANCELLED')
        //     ->where('sales_type', 1)
        //     ->whereBetween('created_at', [$startDate, $eendDate])
        //     ->where('store_code', auth()->user()->store_code);

        // $nonAuditedQuery = DB::table('transactions')
        //     ->where('order_status', '!=', 'CANCELLED')
        //     ->where('sales_type', 0)
        //     ->whereBetween('created_at', [$startDate, $eendDate])
        //     ->where('store_code', auth()->user()->store_code);

        // // Apply branch filter to audit totals
        // if ($selectedBranch != 'all') {
        //     $auditedQuery->where('branch_code', $selectedBranch);
        //     $nonAuditedQuery->where('branch_code', $selectedBranch);
        // }

        // $audited = number_format($auditedQuery->sum('total'), 2, '.', ',');
        // $nonAudited = number_format($nonAuditedQuery->sum('total'), 2, '.', ',');
        /* end of Audited total */
        
        $auditedQuery = DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            // ->where('transactions.status', 'PAID')
            ->whereDate('created_at', $startDate)
            ->where('store_code', auth()->user()->store_code);

        $nonAuditedQuery = DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('transactions.status', 'UNPAID')
            ->whereDate('created_at', $startDate)
            ->where('store_code', auth()->user()->store_code);

        // Apply branch filter to audit totals
        if ($selectedBranch != 'all') {
            $auditedQuery->where('branch_code', $selectedBranch);
            $nonAuditedQuery->where('branch_code', $selectedBranch);
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

        return view('user.cashier.transactions', compact('results', 'settings', 'resultsCount', 
                    'startDate', 'endDate', 'audited', 'nonAudited', 'branches', 'selectedBranch'));
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

    // 2. Use the transaction's ACTUAL branch_code
    $branch_code = $tran->branch_code; // This is "branch-012026001-000001"

    // 3. Get ordered items for this transaction
    // Don't filter by branch_code since the transaction and ordered tables may use different formats
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

        // Debug: Check what's in ordered table
        $orderedItems = DB::table('ordered')
            ->where('tran_id', $id)
            ->where('store_code', auth()->user()->store_code)
            ->get();

        \Log::info('Transaction #' . $id . ' - Ordered items raw:', $orderedItems->toArray());
        \Log::info('Transaction branch_code:', $tran->branch_code);

        // Debug: Check menus table for the menu_ids found
        $menuIds = $orderedItems->pluck('menu_id')->toArray();
        if(!empty($menuIds)) {
            $menus = DB::table('menus')
                ->where('store_code', auth()->user()->store_code)
                ->whereIn('local_id', $menuIds)
                ->get();
            \Log::info('Matching menus for transaction #' . $id . ':', $menus->toArray());
        }
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

            foreach($branchOrders as $order){
                $viewOrder .= '
                    <div class="flex w-full mb-2 ml-4">
                        <div class="w-1/5 font-bold">' . $order->quantity . 'x</div>
                        <div class="w-4/5">' . $order->name . '</div>
                    </div>';
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

    public function print($id){
        
        DB::table('transactions')->where('id', $id)
            ->update([
                'print' => 0,
            ]);
            
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
}
