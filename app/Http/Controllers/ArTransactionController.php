<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ArTransactionController extends Controller
{
    public function index(){
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $eendDate = date('Y-m-d', strtotime('+1 day'));
        $status = 'unpaid';

        $settings = DB::table('settings')->where('id', 1)->first();


        $results = DB::table('account_receivables')
            ->select('account_receivables.id', 'account_receivables.payor_name', 'account_receivables.total as amount', 'account_receivables.mode_of_payment','account_receivables.slug', 'account_receivables.type', 'account_receivables.created_at as date','account_receivables.sales_type','account_receivables.status','account_receivables.balance','account_receivables.number' , 'tables.name as table')
            ->join('tables', 'account_receivables.table', '=', 'tables.id')
            ->where('account_receivables.status', 'UNPAID')
            ->where('account_receivables.order_status', '!=', 'CANCELLED')
            ->orderBy('account_receivables.id', 'desc')
            ->get();

        $resultsCount = DB::table('account_receivables')
            ->where('account_receivables.status', 'UNPAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->orderBy('id', 'desc')
            ->get()->count();

        /* Start of Audited total */
        $today = date('Y-m-d');
        $audited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 1)
            ->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');

        $nonAudited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 0)
            ->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');
        /* end of Audited total */

        return view('user.cashier.ar.ar', compact('results', 'settings', 'resultsCount', 'startDate', 'endDate','audited','nonAudited','status'));
    }

    public function indexpaid(){
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $eendDate = date('Y-m-d', strtotime('+1 day'));
        $status = 'paid';

        $settings = DB::table('settings')->where('id', 1)->first();


        $results = DB::table('account_receivables')
            ->select('account_receivables.id', 'account_receivables.payor_name', 'account_receivables.total as amount', 'account_receivables.mode_of_payment','account_receivables.slug', 'account_receivables.type', 'account_receivables.created_at as date','account_receivables.sales_type','account_receivables.status','account_receivables.balance','account_receivables.number' , 'tables.name as table')
            ->join('tables', 'account_receivables.table', '=', 'tables.id')
            ->where('account_receivables.status', 'PAID')
            ->where('account_receivables.order_status', '!=', 'CANCELLED')
            ->orderBy('account_receivables.id', 'desc')
            ->get();

        $resultsCount = DB::table('account_receivables')
            ->where('account_receivables.status', 'PAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->orderBy('id', 'desc')
            ->get()->count();

        /* Start of Audited total */
        $today = date('Y-m-d');
        $audited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 1)
            ->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');

        $nonAudited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 0)
            ->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');
        /* end of Audited total */

        return view('user.cashier.ar.ar', compact('results', 'settings', 'resultsCount', 'startDate', 'endDate','audited','nonAudited','status'));
    }

    public function generate(Request $request){
        $startDate = date('Y-m-d', strtotime($request->startDate));
        $endDate = date('Y-m-d', strtotime($request->endDate));
        $eendDate = date('Y-m-d', strtotime($request->endDate . '+1 day'));

        $settings = DB::table('settings')->where('id', 1)->first();

        $results = DB::table('transactions')
            ->select('transactions.id', 'transactions.number as nn', 'transactions.total as amount', 'transactions.mode_of_payment', 'transactions.type', 'transactions.created_at as date','transactions.slug','transactions.sales_type', 'tables.name as table')
            ->join('tables', 'transactions.table', '=', 'tables.id')
            ->whereBetween('transactions.created_at', [$startDate, $eendDate])
            ->where('transactions.status', 'PAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->orderBy('transactions.id', 'desc')
            ->get();

        $resultsCount = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $eendDate])
            ->where('status', 'PAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->orderBy('id', 'desc')
            ->get()->count();
            
            /* Start of Audited total */
        $today = date('Y-m-d');
        $audited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 1)
            ->whereBetween('created_at', [$startDate, $eendDate])
            ->sum('total'), 2, '.', ',');

        $nonAudited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 0)
            ->whereBetween('created_at', [$startDate, $eendDate])
            ->sum('total'), 2, '.', ',');
        /* end of Audited total */

        return view('user.cashier.transactions', compact('results', 'settings', 'resultsCount', 'startDate', 'endDate','audited','nonAudited'));
        
    }

    public function updatestatus(Request $request){
        $startDate = $request->startDate;
        $endDate = date('Y-m-d', strtotime($request->endDate . '+1 day'));
        $slug = $request->slug;
        $status ='';
        $trans = DB::table('transactions')->where('slug', $slug)->first();
        if ($trans->sales_type == 1){
            DB::table('transactions')->where('slug', $slug)
            ->update([
                'sales_type' => 0,
            ]);
            $status = '1';
            
        } else {
            DB::table('transactions')->where('slug', $slug)
            ->update([
                'sales_type' => 1,
            ]);
            $status = '0';
        }
        


        $response = array(
            'status' => $status
        );

        echo json_encode($response);
         //return redirect()->route('transactions.index');
    }

    public function view(Request $request){
        $id = $request->id;

        $orders = DB::table('ordered')
        ->join('menus', 'ordered.menu_id', '=', 'menus.id')
        ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.slug', 'ordered.status')
        ->where('ordered.tran_id', $id)
        ->get();

        $tran = DB::table('transactions')->where('id', $id)->first();

        $viewOrder = '';

        foreach($orders as $order){
            $viewOrder .= '
                <div class="flex w-full">
                    <div class="w-1/5">'.$order->quantity.'x</div><div class="">'.$order->name.'</div>
                </div>';
        }

        $printUrl = url('/transactions/print/'.$id);

        $response = array(
            'number' => $tran->number,
            'ordered' => $viewOrder,
            'url' => $printUrl,
        );

        echo json_encode($response);
    }

    public function print($id){
        $trans = DB::table('transactions')
                    ->select('transactions.*', 'tables.name as table_name')
                    ->join('tables', 'transactions.table', '=', 'tables.id')
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


        $settings = DB::table('settings')->where('id', 1)->first();


        return view('user.cashier.bill', compact('orders', 'trans', 'settings', 'seniors'));
    }

    public function paid(Request $request){
        $slug = $request->slug;
        $mop = $request->mop;
        $amountInput = $request->amountInput;
        
        $ars = DB::table('account_receivables')->where('slug', $slug)->first();

        $bal = $ars->balance - $amountInput;
        $bal = ($bal < 0) ? 0 : $bal;
        
        $arSlug = Str::random(60);
        $narSlug = $arSlug;
        $check_slug = DB::table('ar_logs')->where('slug', $narSlug)->get();
        while (count($check_slug) > 0) {
            $narSlug = Str::random(60);
            $check_slug = DB::table('ar_logs')->where('slug', $narSlug)->get();
        }
        $arSlug = $narSlug;

        DB::table('ar_logs')->insert([
            'tran_id' => $ars->tran_id,
            'number' => $ars->number,
            'mode_of_payment' => $mop,
            'total' => $ars->total,
            'amount' => $amountInput,
            'balance' => $bal,
            'cashier' => auth()->id(),
            'slug' => $arSlug,
            'created_at' => date('Y-m-d h:i:s'),
            'is_deleted' => 0
        ]);

        $status = ($bal == 0) ? 'PAID' : 'UNPAID';
        $ord_status = ($bal == 0) ? 'COMPLETEd' : 'PREPARING';
        DB::table('account_receivables')->where('slug', $slug)
        ->update([
            'balance' => $bal,
            'status' => $status,
            'order_status' => $ord_status,
        ]);


        $response = array(
            'slug' => $slug,
            'tranid' => $ars->tran_id,
        );

        echo json_encode($response);



        
    }

    
}
