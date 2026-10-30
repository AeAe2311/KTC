<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use Illuminate\Support\Str;

use function Ramsey\Uuid\v1;

class OrderedController extends Controller
{
    public function index(){
        $tables = DB::table('tables')->orderBy('id', 'asc')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $table_cat = DB::table('table_categories')
        ->orderBy('id', 'asc')
        ->orderBy('name', 'asc')
        //->where('store_code', auth()->user()->store_code)
        ->where('is_deleted', 0)
        ->get();
        $branches = DB::table('settings')//->where('store_code', auth()->user()->store_code)
        ->where('id', '!=',1)->get();

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

        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();

        $arTransactions = DB::table('transactions')
            ->leftJoin('tables', 'transactions.table', '=', 'tables.id')
            ->select('transactions.*', 'tables.name as client_table_name', 'tables.credit_limit')
            //->where('tables.store_code', auth()->user()->store_code)
            ->where(function ($query) {
                $query->where('transactions.store_code', auth()->user()->store_code)
                    ->orWhereNull('transactions.store_code');
            })
            ->where('transactions.status', 'UNPAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->where(function ($query) {
                $query->where('transactions.mode_of_payment', 'AR')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('transactions.payor_name')
                            ->where('transactions.payor_name', '!=', '');
                    });
            })
            ->orderBy('transactions.created_at', 'asc')
            ->get();

        $transactionIds = $arTransactions->pluck('id');
        $transactionUuids = $arTransactions->pluck('is_uuid')->filter();
        $arPayments = DB::table('payment_logs')
            ->whereIn('tran_id', $transactionIds)
            ->where('is_deleted', 0)
            ->where(function ($query) {
                $query->whereNull('is_void')->orWhere('is_void', 0);
            })
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get()
            ->groupBy('tran_id');
        $arItems = DB::table('ordered')
            ->leftJoin('menus', 'ordered.menu_id', '=', 'menus.id')
            ->select('ordered.*', 'menus.name as menu_name')
            ->where(function ($query) use ($transactionIds, $transactionUuids) {
                $query->whereIn('ordered.tran_id', $transactionIds)
                    ->orWhereIn('ordered.tran_uuid', $transactionUuids);
            })
            ->orderBy('ordered.id')
            ->get();

        foreach ($arTransactions as $transaction) {
            $transaction->payments = $arPayments->get($transaction->id, collect());
            $transaction->items = $arItems->filter(function ($item) use ($transaction) {
                return $item->tran_id == $transaction->id || $item->tran_uuid == $transaction->is_uuid;
            });
            $transaction->paid_amount = round((float) $transaction->payments->sum('amount'), 2);
            $transaction->balance_due = round(max(0, (float) $transaction->total - $transaction->paid_amount), 2);
        }

        $arTransactions = $arTransactions
            ->filter(function ($transaction) {
                return $transaction->balance_due > 0;
            })
            ->values();

        $arClients = $arTransactions->groupBy('table')->map(function ($transactions, $tableId) {
            $firstTransaction = $transactions->first();
            $namedTransaction = $transactions->first(function ($transaction) {
                return !empty($transaction->payor_name);
            });

            return (object) [
                'table_id' => $tableId,
                'name' => $firstTransaction->client_table_name ?? $namedTransaction->payor_name ?? 'Unnamed client',
                'credit_limit' => (float) ($firstTransaction->credit_limit ?? 0),
                'transactions' => $transactions,
                'transaction_count' => $transactions->count(),
                'paid_total' => round((float) $transactions->sum('paid_amount'), 2),
                'unpaid_total' => round((float) $transactions->sum('balance_due'), 2),
            ];
        })->sortBy('name')->values();

        $arPaidTotal = (float) DB::table('payment_logs')
            ->join('transactions', 'payment_logs.tran_id', '=', 'transactions.id')
            ->join('tables', 'transactions.table', '=', 'tables.id')
            //->where('tables.store_code', auth()->user()->store_code)
            ->where(function ($query) {
                $query->where('transactions.store_code', auth()->user()->store_code)
                    ->orWhereNull('transactions.store_code');
            })
            ->where('transactions.mode_of_payment', 'AR')
            ->where('payment_logs.is_deleted', 0)
            ->where(function ($query) {
                $query->whereNull('payment_logs.is_void')->orWhere('payment_logs.is_void', 0);
            })
            ->sum('payment_logs.amount');
        $arUnpaidTotal = round((float) $arTransactions->sum('balance_due'), 2);

        return view('user.cashier.orders', compact('tables','audited','nonAudited','SCRow','discountRow','limeDiscountRow','branches','table_cat','arClients','arPaidTotal','arUnpaidTotal'));
    }

    public function indexAdmin(){
        $tables = DB::table('tables')->orderBy('id', 'asc')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $table_cat = DB::table('table_categories')
        ->orderBy('id', 'asc')
        ->orderBy('name', 'asc')
        ->where('is_deleted', 0)
        ->get();
        $branches = DB::table('settings')
        ->where('id', '!=',1)->get();

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

        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();

        $arTransactions = DB::table('transactions')
            ->leftJoin('tables', 'transactions.table', '=', 'tables.id')
            ->select('transactions.*', 'tables.name as client_table_name', 'tables.credit_limit')
            //->where('tables.store_code', auth()->user()->store_code)
            ->where(function ($query) {
                $query->where('transactions.store_code', auth()->user()->store_code)
                    ->orWhereNull('transactions.store_code');
            })
            ->where('transactions.status', 'UNPAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->where(function ($query) {
                $query->where('transactions.mode_of_payment', 'AR')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('transactions.payor_name')
                            ->where('transactions.payor_name', '!=', '');
                    });
            })
            ->orderBy('transactions.created_at', 'asc')
            ->get();

        $transactionIds = $arTransactions->pluck('id');
        $transactionUuids = $arTransactions->pluck('is_uuid')->filter();
        $arPayments = DB::table('payment_logs')
            ->whereIn('tran_id', $transactionIds)
            ->where('is_deleted', 0)
            ->where(function ($query) {
                $query->whereNull('is_void')->orWhere('is_void', 0);
            })
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get()
            ->groupBy('tran_id');
        $arItems = DB::table('ordered')
            ->leftJoin('menus', 'ordered.menu_id', '=', 'menus.id')
            ->select('ordered.*', 'menus.name as menu_name')
            ->where(function ($query) use ($transactionIds, $transactionUuids) {
                $query->whereIn('ordered.tran_id', $transactionIds)
                    ->orWhereIn('ordered.tran_uuid', $transactionUuids);
            })
            ->orderBy('ordered.id')
            ->get();

        foreach ($arTransactions as $transaction) {
            $transaction->payments = $arPayments->get($transaction->id, collect());
            $transaction->items = $arItems->filter(function ($item) use ($transaction) {
                return $item->tran_id == $transaction->id || $item->tran_uuid == $transaction->is_uuid;
            });
            $transaction->paid_amount = round((float) $transaction->payments->sum('amount'), 2);
            $transaction->balance_due = round(max(0, (float) $transaction->total - $transaction->paid_amount), 2);
        }

        $arTransactions = $arTransactions
            ->filter(function ($transaction) {
                return $transaction->balance_due > 0;
            })
            ->values();

        $arClients = $arTransactions->groupBy('table')->map(function ($transactions, $tableId) {
            $firstTransaction = $transactions->first();
            $namedTransaction = $transactions->first(function ($transaction) {
                return !empty($transaction->payor_name);
            });

            return (object) [
                'table_id' => $tableId,
                'name' => $firstTransaction->client_table_name ?? $namedTransaction->payor_name ?? 'Unnamed client',
                'credit_limit' => (float) ($firstTransaction->credit_limit ?? 0),
                'transactions' => $transactions,
                'transaction_count' => $transactions->count(),
                'paid_total' => round((float) $transactions->sum('paid_amount'), 2),
                'unpaid_total' => round((float) $transactions->sum('balance_due'), 2),
            ];
        })->sortBy('name')->values();

        $arPaidTotal = (float) DB::table('payment_logs')
            ->join('transactions', 'payment_logs.tran_id', '=', 'transactions.id')
            ->join('tables', 'transactions.table', '=', 'tables.id')
            //->where('tables.store_code', auth()->user()->store_code)
            ->where(function ($query) {
                $query->where('transactions.store_code', auth()->user()->store_code)
                    ->orWhereNull('transactions.store_code');
            })
            ->where('transactions.mode_of_payment', 'AR')
            ->where('payment_logs.is_deleted', 0)
            ->where(function ($query) {
                $query->whereNull('payment_logs.is_void')->orWhere('payment_logs.is_void', 0);
            })
            ->sum('payment_logs.amount');
        $arUnpaidTotal = round((float) $arTransactions->sum('balance_due'), 2);

        return view('user.cashier.orders-admin', compact('tables','audited','nonAudited','SCRow','discountRow','limeDiscountRow','branches','table_cat','arClients','arPaidTotal','arUnpaidTotal'));
    }

    public function getMenu(Request $request){
        $id = $request->id;

        $trans = DB::table('transactions'
        )->where('table', $id)
        ->where('order_status', '!=', 'CANCELLED')
        ->where('order_status', '!=', 'COMPLETED')
        ->orderby('id', 'desc')
        ->get();
        
        $this->DisplayTableMenu($trans);

        
    }
    public function OrderupdateDiscount(Request $request) {
        $id = $request->tableId;
        $customer_with_discount = $request->cust_with_discount;
        //$total_customer = $request->totalcus;
        $service_charge = $request->service_charge;
        $lime_discount = $request->lime_discount;

        

        $subTotal = $subTotalWDisc = $subTotalODisc = 0;
        $total = 0;
        $sc = 50;
        $vatsales = 0;
        $vat = 0;
        $net = $netWithDiscount = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        
        $discount = $SCPercent = $SCVal = $limePercent = $limeVal = 0;


        $qty = $amount = $price = $total = $SCPercent = $SCVal = $SCValperItem = $discount = $discountPerItem = 0;

        
        

        $transDiscount = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('status', '!=', 'PAID')->first();
        

        DB::table('transactions')->where('slug', $transDiscount->slug)->update([
            'customer_with_discount' => $customer_with_discount,
            'sc_percent' => $service_charge,
            'lime_discount' => $lime_discount,
        ]);

        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('status', '!=', 'PAID')->get();

        
        /* DB::table('discounts')->where('user_id', auth()->id())->update([
            'customer_with_discount' => $customer_with_discount,
        ]);

        DB::table('service_charge')->where('user_id', auth()->id())->update([
            'percent' => $service_charge,
        ]);

        DB::table('lime_discount')->where('user_id', auth()->id())->update([
            'percent' => $lime_discount,
        ]); */

        foreach($trans as $tran){
            
            

            $orders = DB::table('ordered')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.type', 'ordered.is_pwd', 'ordered.is_odisc')
                ->where('ordered.tran_id', $tran->id)
                ->get();

            foreach($orders as $order){
                $qty = $order->quantity;
                $amount = $order->amount;
                //$price = $qty * $amount;
                if ($order->type != 1){
                    if ($order->is_pwd == 1){
                        $subTotalWDisc += $order->amount;
                    } else {
                        if ($order->is_odisc == 1){
                            $subTotalODisc +=  $order->amount;
                        } else {
                            $subTotal +=  $order->amount;
                        }
                        
                    }
                    
                } else {
                    if ($order->is_odisc == 1){
                        $subTotalODisc +=  $order->amount;
                    } else {
                        $subTotal +=  $order->amount;
                    }
                }
                
                DB::table('ordered')->where('slug', $order->slug)->update([
                    'discount_amount' => $discountPerItem,
                ]);
                //mackey version
                
            }
            /* if ($total_customer > 0) {
                $x = $subtotal / $total_customer;
                $y = 0.2 * round($customer_with_discount,2);
                $discount = $x * $y;
            }
            
            $total = round(($subtotal) - $discount,2);
            $SCVal = ($SCPercent / 100) * $subtotal;
            $total = $total + $SCVal; */
            

            //new 6-23
            $tables = DB::table('tables')
                    ->where('id', $tran->table)
                    ->first();

            $SCPercent = ($tables->is_sc == 1) ? 0 : $tran->sc_percent;
            $limePercent = $tran->lime_discount;
            //$subTotal = $subTotal +$sc;
            /* $limeVal = ($limePercent / 100) * $subTotal;
            $net = ($subTotal <= 0 ) ? 0 : round(($subTotal- $limeVal)/1.12,2); // 500
            $netWithDiscount = ($subTotalWDisc <= 0 ) ? 0 : round(($subTotalWDisc- $limeVal)/1.12,2); // 500 */
            

            /* $SCPercent = $SCRow->percent;
            $limePercent = $limeDiscountRow->percent; */
            //$subTotal = $subTotal +$sc;
            //$limeVal = ($limePercent / 100) * $subTotal;
            //$net = ($subTotal <= 0 ) ? 0 : round(($subTotal- $limeVal)/1.12,2); // 500
            //$netWithDiscount = ($subTotalWDisc <= 0 ) ? 0 : round(($subTotalWDisc- $limeVal)/1.12,2); // 500
            

            $limeVal = ($limePercent / 100) * $subTotalODisc; 
            $net =  $subTotalWDisc + $subTotal + $subTotalODisc;
            //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
            
            // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
            if ($tran->customer_with_discount > 0) {
                $discount = 0.2 * $subTotalWDisc;

            }

            $SubtotalLessDiscount = $net - $discount - $limeVal;
            $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            
            //discount default is 20% of the net value ex net = 100, then discount = 20
            $total = ($SubtotalLessDiscount + $SCVal);
            
            //$subTotal = $subTotal + $subTotalWDisc + $subTotalWDisc;


            /* if ($tran->customer_with_discount > 0) {
                // $x = round($netWithDiscount / $discountRow->total_customer,2);
                // $y = 0.2 * round($discountRow->customer_with_discount,2);
                $VatExemptSales = round($netWithDiscount,2) ;
                $discount = 0.2 * $netWithDiscount;
                $LessVatDeduction =round($VatExemptSales*0.12,2);
                $SubtotalLessDiscount = round($net + $netWithDiscount - $discount,2);
                $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            } else {
                
                $SCVal = ($SCPercent / 100) * $net + (($SCPercent / 100) * $netWithDiscount);
                
            }

            


            $vatsales = round($net,2);

            $vat = round($vatsales * 0.12,2);
            
            $total = round(($subTotal+ $subTotalWDisc + $SCVal -($LessVatDeduction+$discount+$limeVal)),2); */
            $subTotal = $net;

            DB::table('transactions')->where('id',$tran->id)->update([
                'total' => $total,
                'service_charge' =>  $SCVal,
                'sc_percent' =>  $SCPercent,
                'pwd' =>  $discount,
                'customer_with_discount' =>  $customer_with_discount,
                'lime_discount' =>  $limePercent,
                'ld_value' =>  $limeVal,
                'lessVatDeduction' =>  $LessVatDeduction,
                'vat' =>  $vat,
                'vatSales' =>  $vatsales,
                'vatExemptSales' =>  $VatExemptSales,
            ]);
        }

        $response = array(
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount,2),
            'ServiceCharge' => number_format($SCVal,2),
            'scPercent' => $SCPercent,
            'customer_with_discount' =>  $customer_with_discount,
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => number_format($limeVal,2),
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'ordersCount' => $orders->count()
        );
        echo json_encode($response);


    

        //return redirect()->route('orders.index');
    }
    public function OrderdeleteDiscount(Request $request) {

        $id = $request->tableId;
        $customer_with_discount = 0;
        $total_customer = 0;
        $service_charge = $request->service_charge;
        $lime_discount = 0;

        DB::table('discounts')->where('user_id', auth()->id())->update([
            'customer_with_discount' => $customer_with_discount,
            'total_customer' => $total_customer,
        ]);

        DB::table('service_charge')->where('user_id', auth()->id())->update([
            'percent' => $service_charge,
        ]);

        DB::table('lime_discount')->where('user_id', auth()->id())->update([
            'percent' => $lime_discount,
        ]);

        $subTotal = $subTotalWDisc = $subTotalODisc = 0;
        $total = 0;
        $sc = 50;
        $vatsales = 0;
        $vat = 0;
        $net = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        $discount = $SCPercent = $SCVal = $limePercent = $limeVal = 0;


        $qty = $amount = $price = $total = $SCPercent = $SCVal = $SCValperItem = $discount = $discountPerItem = 0;

        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $SCPercent = $SCRow->percent;
        

        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('status', '!=', 'PAID')->get();
        

        foreach($trans as $tran){
            
            $orders = DB::table('ordered')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.is_odisc')
                ->where('ordered.tran_id', $tran->id)
                ->get();

            foreach($orders as $order){
                $qty = $order->quantity;
                $amount = $order->amount;
                //$price = $qty * $amount;
                if ($order->type != 1){
                    if ($order->is_pwd == 1){
                        $subTotalWDisc += $order->amount;
                    } else {
                        if ($order->is_odisc == 1){
                            $subTotalODisc +=  $order->amount;
                        } else {
                            $subTotal +=  $order->amount;
                        }
                        
                    }
                    
                } else {
                    if ($order->is_odisc == 1){
                        $subTotalODisc +=  $order->amount;
                    } else {
                        $subTotal +=  $order->amount;
                    }
                }
                //compute discount per item
                if ($total_customer > 0) {
                    $x = $amount / $total_customer;
                    $y = 0.2 * round($customer_with_discount,2);
                    $discountPerItem = $x * $y;
                }
                
                DB::table('ordered')->where('slug', $order->slug)->update([
                    'discount_amount' => $discountPerItem,
                ]);
                //mackey version
                
            }
            /* if ($total_customer > 0) {
                $x = $subtotal / $total_customer;
                $y = 0.2 * round($customer_with_discount,2);
                $discount = $x * $y;
            }
            
            $total = round(($subtotal) - $discount,2);
            $SCVal = ($SCPercent / 100) * $subtotal;
            $total = $total + $SCVal; */
            

            //new 6-23
            $SCPercent = $tran->percent;
            $limePercent = $tran->percent;
            //$subTotal = $subTotal +$sc;
            /* $limeVal = ($limePercent / 100) * $subTotal;
            $net = round(($subTotal- $limeVal)/1.12,2); // 500
            if ($discountRow->total_customer > 0) {
                $x = round($net / $discountRow->total_customer,2);
                $y = 0.2 * round($discountRow->customer_with_discount,2);
                $VatExemptSales = round($x,2) * round($discountRow->customer_with_discount,2);
                $discount = 0.2 * $VatExemptSales;
                $LessVatDeduction =round($VatExemptSales*0.12,2);
                $SubtotalLessDiscount = round($subTotal-($discount+$LessVatDeduction),2);
                $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            } else {
                
                $SCVal = ($SCPercent / 100) * $net;
                
            }

            $vatsales = round(($net) - ($VatExemptSales),2);

            $vat = round($vatsales * 0.12,2);
            
            $total = round(($subTotal + $SCVal -($LessVatDeduction+$discount+$limeVal)),2); */

            $net =  $subTotalWDisc + $subTotal ;

            $limeVal = ($limePercent / 100) * $net; 
            //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
            
            // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
            if ($tran->customer_with_discount > 0) {
                $discount = 0.2 * $subTotalWDisc;

            }

            $SubtotalLessDiscount = $net - $discount - $limeVal;
            $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            
            //discount default is 20% of the net value ex net = 100, then discount = 20
            $total = ($SubtotalLessDiscount + $SCVal);

            DB::table('transactions')->where('id',$tran->id)->update([
                'total' => $total,
                'service_charge' =>  $SCVal,
                'sc_percent' =>  $SCPercent,
                'pwd' =>  $discount,
                'customer_with_discount' =>  $customer_with_discount,
                'total_customer' =>  $total_customer,
                'lime_discount' =>  $limePercent,
                'ld_value' =>  $limeVal,
                'lessVatDeduction' =>  $LessVatDeduction,
                'vat' =>  $vat,
                'vatSales' =>  $vatsales,
                'vatExemptSales' =>  $VatExemptSales,
            ]);
        }

        $response = array(
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => $discount,
            'ServiceCharge' => $SCVal,
            'scPercent' => $SCPercent,
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => $limeVal,
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'ordersCount' => $orders->count()
        );
        echo json_encode($response);
        
    }
    public function reduce(Request $request){
        $slug = $request->slug;
        //$voidCode = $request->voidCode;
        
        $settings = DB::table('settings')->where('id', 1)->first();

        $menu_id = (DB::table('ordered')->where('slug', $slug)->first())->menu_id;

        $menu = DB::table('menus')->where('id', $menu_id)->first();

        //if ($settings->void_code == $voidCode){
            
            DB::table('ordered')->where('slug', $slug)->decrement('quantity', 1);
            DB::table('ordered')->where('slug', $slug)->decrement('amount', $menu->price);
                //echo $menu;
                if ($menu->is_combo == 1) {
                    $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                    foreach ($ings as $ing) {
                        //$decq = 1 * $ing->computed_quantity;
                        //$curq = Menu::where('id', $ing->inventory_id)->first()->current_quantity;
                        DB::table('menus')->where('id', $ing->inventory_id)->increment('current_quantity', $ing->quantity);
                        DB::table('menus')->where('id', $ing->inventory_id)->increment('quantity', $ing->quantity);
                    }
                } else {
                    DB::table('menus')->where('id', $menu_id)->increment('current_quantity', 1);
                    DB::table('menus')->where('id', $menu_id)->increment('quantity', 1);
                }
    
            $id = $request->table;
            
            $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('status', '!=', 'PAID')->get();
            
            
    
            $this->DisplayTableMenu($trans);

        // } else {
        //         $msg = 'error';
        //         $response = array(
                    
        //             'msg' => $msg,
                    
        //         );
        //         echo json_encode($response);
        // }
    
    }

    public function remove(Request $request){
        $slug = $request->slug;
        $voidCode = $request->voidCode;
        
        $settings = DB::table('settings')->where('id', 1)->first();

        if ($settings->void_code == $voidCode){
            $ordered = DB::table('ordered')->where('slug', $slug)->first();

            $menu = DB::table('menus')->where('id', $ordered->menu_id)->first();
                //echo $menu;
                if ($menu->is_combo == 1) {
                    $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                    foreach ($ings as $ing) {
                        $newQty = $ing->quantity * $ordered->quantity;
                        DB::table('menus')->where('id', $ing->inventory_id)->increment('current_quantity', $newQty);
                        DB::table('menus')->where('id', $ing->inventory_id)->increment('quantity', $newQty);
                    }
                } else {
                    DB::table('menus')->where('id', $ordered->menu_id)->increment('current_quantity', $ordered->quantity);
                    DB::table('menus')->where('id', $ordered->menu_id)->increment('quantity', $ordered->quantity);
                }
    
                DB::table('ordered_remove')->insert([
                    'id' => $ordered->id,
                    'slug' => $ordered->slug,
                    'menu_id' => $ordered->menu_id,
                    'quantity' => $ordered->quantity,
                    'tran_id' => $ordered->tran_id,
                    'amount' => $ordered->amount, 
                    'food_cost' => $ordered->food_cost,
                    'cashier' => $ordered->cashier,
                    'role' => $ordered->role,
                    'discount_amount' => $ordered->discount_amount,
                    'lime_discount' => $ordered->lime_discount,
                    'type' => $ordered->type,
                    'is_pwd' => $ordered->is_pwd,
                    'is_odisc' => $ordered->is_odisc,
                    'remarks' => $ordered->remarks,
                    'status' => $ordered->status,
                    'serve_status' => 'CANCELLED',
                    'print' => 0,
                    'for_delete' => 0,
                    'is_split' => 0,
                    'department' => $ordered->department,
                    'branch' => $ordered->branch,
                    'service_charge' => $ordered->service_charge,
                    'void_code' => $voidCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            DB::table('ordered')->where('slug', $slug)->delete();
            
            $id = $request->table;
            
            $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('status', '!=', 'PAID')->get();
            
            $orderedCheck = DB::table('ordered')->where('tran_id', $ordered->tran_id)->count();
            if ($orderedCheck > 0){
                $this->DisplayTableMenu($trans);
            } else {
                DB::table('transactions')->where('id',$ordered->tran_id)->update([
                    'total' => 0,
                    'service_charge' =>  0,
                    'sc_percent' =>  0,
                    'pwd' =>  0,
                    'lime_discount' =>  0,
                    'ld_value' =>  0,
                    'lessVatDeduction' =>  0,
                    'vat' =>  0,
                    'vatSales' =>  0,
                    'vatExemptSales' =>  0,
                    'order_status' =>  'CANCELLED',
                ]);
                $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('status', '!=', 'PAID')->get();
                $this->DisplayTableMenu($trans);
            }
            

        } else {
                $msg = 'error';
                $response = array(
                    
                    'msg' => $msg,
                    
                );
                echo json_encode($response);
        }
        
    }

    public function cancel(Request $request){
        $table = $request->id;

        $trans = DB::table('transactions')->where('table', $table)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('order_status', '!=', 'SERVED')->get();

        foreach($trans as $tran){
            $ordereds = DB::table('ordered')->where('tran_id', $tran->id)->get();
    
            foreach($ordereds as $ordered){
                DB::table('menus')->where('id', $ordered->menu_id)->increment('current_quantity', $ordered->quantity);
                DB::table('menus')->where('id', $ordered->menu_id)->increment('quantity', $ordered->quantity);
            }
    
            DB::table('ordered')->where('tran_id', $tran->id)->delete();
            DB::table('transactions')->where('id', $tran->id)->update([
                'order_status' => 'CANCELLED'
            ]);
        }
        
        $trans = DB::table('transactions')->where('table', $table)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->get();

        foreach($trans as $tran){
            DB::table('transactions')->where('id', $tran->id)->update(['order_status' => 'COMPLETED']);
            $ordered = DB::table('ordered')->where('tran_id', $tran->id)->update([ 'status' => 'COMPLETED' ]);
        }

        DB::table('tables')->where('id', $table)->update([
            'status' => 0
        ]);
    }

    public function open(Request $request){
        $table = $request->id;

        $trans = DB::table('transactions')->where('table', $table)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->get();

        foreach($trans as $tran){
            DB::table('transactions')->where('id', $tran->id)->update(['order_status' => 'COMPLETED']);
            $ordered = DB::table('ordered')->where('tran_id', $tran->id)->update([ 'status' => 'COMPLETED' ]);
        }

        DB::table('tables')->where('id', $table)->update([
            'status' => 0
        ]);
    }

    public function occupy(Request $request){
        $id = $request->id;

        DB::table('tables')->where('id', $id)->update(['status' => '1']);
    }

    public function getAmount(Request $request){
        $id = $request->id;
        $trans = DB::table('transactions')
            ->where('table', $id)
            ->where('transactions.status', 'UNPAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->where('order_status', '!=', 'COMPLETED')
            ->first();

            $response = array(
                'formattedAmount' => number_format($trans->total,2),
                'amountnoFormat' => $trans->total,
            );
    
            echo json_encode($response);

        //echo number_format($trans->total,2);
    }

    public function paid(Request $request){
        if ($request->filled('ar_transaction_id')) {
            return $this->payArTransaction($request);
        }

        $id = $request->id;
        $mop = $request->mop;
        $amountInput = $request->amountInput;

        /* Start MOP */

        $cashMop = $request->cashMop;
        $ccMop = $request->ccMop;
        $visaMop = $request->visaMop;
        $gcashMop = $request->gcashMop;
        $wcpMop = $request->wcpMop;
        $amexMop = $request->amexMop;
        $bdoMop = $request->bdoMop;
        $bancnetMop = $request->bancnetMop;
        $dinersMop = $request->dinersMop;
        $dpMop = $request->dpMop;
        $giftMop = $request->giftMop;
        $jcbMop = $request->jcbMop;
        $mayaMop = $request->mayaMop;
        $grabPayMop = $request->grabPayMop;
        $unionMop = $request->unionMop;
        $grabMop = $request->grabMop;
        $pandaMop = $request->pandaMop;
        $qrphMop = $request->qrphMop;

        /* End MOP */
        $trans = DB::table('transactions')
        ->where('table', $id)
        ->where('status', 'UNPAID')
        ->where('order_status', '!=', 'CANCELLED')
        ->where('order_status', '!=', 'COMPLETED')
        ->first();

        


            /* Start of Saving Multiple MOP */

        $cashier = auth()->id();
        $tran_id = $trans->id;
        
        if ($cashMop > 0 || $trans->total == 0){
            $mop = 'CASH';
            $amount = $cashMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($ccMop > 0){
            $mop = 'CREDIT CARD - MASTERCARD';
            $amount = $ccMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($visaMop > 0){
            $mop = 'VISA';
            $amount = $visaMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($gcashMop > 0){
            $mop = 'GCASH';
            $amount = $gcashMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($wcpMop > 0){
            $mop = 'WE CHAT PAY';
            $amount = $wcpMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($amexMop > 0){
            $mop = 'AMEX';
            $amount = $amexMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($bdoMop > 0){
            $mop = 'BDO PAY';
            $amount = $bdoMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($bancnetMop > 0){
            $mop = 'BANCNET';
            $amount = $bancnetMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($dinersMop > 0){
            $mop = 'DINERS';
            $amount = $dinersMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($dpMop > 0){
            $mop = 'DOWN PAYMENT';
            $amount = $dpMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($giftMop > 0){
            $mop = 'GIFT CERT';
            $amount = $giftMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($jcbMop > 0){
            $mop = 'JCB';
            $amount = $jcbMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($mayaMop > 0){
            $mop = 'PAYMAYA';
            $amount = $mayaMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($grabPayMop > 0){
            $mop = 'GRAB PAY';
            $amount = $grabPayMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($unionMop > 0){
            $mop = 'UNION PAY';
            $amount = $unionMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($grabMop > 0){
            $mop = 'GRAB FOOD';
            $amount = $grabMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($pandaMop > 0){
            $mop = 'FOOD PANDA';
            $amount = $pandaMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        if ($qrphMop > 0){
            $mop = 'QR PH';
            $amount = $qrphMop;
            $this->saveMOP($tran_id, $mop, $amount , $cashier);
        }

        DB::table('transactions')
            ->where('table', $id)
            ->where('status', 'UNPAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('order_status', '!=', 'COMPLETED')
            ->update([
                'mode_of_payment' => $mop,
                'amount' => $amountInput,
                'cashier' => auth()->id(),
                'order_status' => 'COMPLETED', 
                'created_at' => date('Y-m-d H:i:s'),
                'print' => 1,
                'status' => 'PAID'
            ]);
            
            DB::table('discounts')->where('user_id', auth()->id())->update([
                'customer_with_discount' => 0,
                'total_customer' => 0,
            ]);
    
            DB::table('service_charge')->where('user_id', auth()->id())->update([
                'percent' => 10,
            ]);
    
            DB::table('lime_discount')->where('user_id', auth()->id())->update([
                'percent' => 0,
            ]);

        $mopCheck = DB::table('payment_orders')->where('tran_id', $tran_id)->first();
        $mopCheckCount = DB::table('payment_orders')->where('tran_id', $tran_id)->count();
        if ($mopCheckCount > 1){
            DB::table('transactions')->where('id', $tran_id)->update([
                'mode_of_payment' => 'MULTIPLE',
            ]);
        } else {
            DB::table('transactions')->where('id', $tran_id)->update([
                'mode_of_payment' => $mopCheck->mop,
            ]);
        }

        /* End of Saving Multiple MOP */
        
        DB::table('tables')
        ->where('id',  $id)
        ->update(['status' => 0]);
    }

    public function print($id){
        
        DB::table('transactions')
            ->where('table', $id)
            ->where('status', '=', 'UNPAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('order_status', '!=', 'COMPLETED')
            ->update([
                'print' => 0,
            ]);
            
        //leo start 4/29/25
        DB::table('tables')
            ->where('id', $id)
            ->update([
                'status' => 2,
            ]);
        //leo end 4/29/25
        
        $trans = DB::table('transactions')
                    ->select('transactions.*', 'tables.name as table_name','users.name')
                    ->join('tables', 'transactions.table', '=', 'tables.id')
                    ->join('users', 'transactions.cashier', '=', 'users.id')
                    ->where('transactions.table', $id)
                    ->where('transactions.status', '=', 'UNPAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->where('transactions.order_status', '!=', 'COMPLETED')
                    ->first();

        $orders = DB::table('ordered')
                    ->select('ordered.*', 'menus.name')
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->where('tran_id', $trans->id)
                    ->get();

        $payments = DB::table('payment_orders')->where('tran_id', $trans->id)->get();

        $settings = DB::table('settings')->where('id', 2)->first();

        $seniors = DB::table('seniors')->where('tran_id', $trans->id)->get();
        
        $orderdineincount = DB::table('ordered') 
        ->where('tran_id', $trans->id)
        ->where('item_takeout','0')
        ->count();

        $ordertakeoutcount = DB::table('ordered') 
                ->where('tran_id', $trans->id)
                ->where('item_takeout','1')
                ->count();

        return view('user.cashier.bill', compact('orders', 'trans', 'settings', 'seniors','payments','ordertakeoutcount','orderdineincount'));
    }
    
    public function print_server($id){

            
        $trans = DB::table('transactions')
                    ->select('transactions.*', 'tables.name as table_name','users.name')
                    ->join('tables', 'transactions.table', '=', 'tables.id')
                    ->join('users', 'transactions.cashier', '=', 'users.id')
                    ->where('transactions.table', $id)
                    ->where('transactions.status', '=', 'UNPAID')
                    ->where('transactions.order_status', '!=', 'CANCELLED')
                    ->where('transactions.order_status', '!=', 'COMPLETED')
                    ->first();

        $orders = DB::table('ordered')
                    ->select('ordered.*', 'menus.name')
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->where('tran_id', $trans->id)
                    ->get();

        $payments = DB::table('payment_orders')->where('tran_id', $trans->id)->get();

        $settings = DB::table('settings')->where('id', 1)->first();

        $seniors = DB::table('seniors')->where('tran_id', $trans->id)->get();
        
        $orderdineincount = DB::table('ordered') 
        ->where('tran_id', $trans->id)
        ->where('item_takeout','0')
        ->count();

        $ordertakeoutcount = DB::table('ordered') 
                ->where('tran_id', $trans->id)
                ->where('item_takeout','1')
                ->count();

        return view('user.cashier.bill', compact('orders', 'trans', 'settings', 'seniors','payments','ordertakeoutcount','orderdineincount'));
    }

    public function discountSelected(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $checkbox = $request->checkbox;
        $corder = DB::table('ordered')->where('slug', $slug)->first();
        if ($checkbox == "true"){
            DB::table('ordered')->where('slug', $slug)->update([
                'is_pwd' => 1,
            ]);
        } else {
            $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
            //$total_price = $menu->price * $corder->quantity;
            DB::table('ordered')->where('slug', $slug)->update([
                'is_pwd' => 0,
            ]);
        }
        $response = array(
            'ordered' => $slug,
            'subTotal' => $checkbox
        );

        echo json_encode($response);
    }


    public function ownerdiscSelected(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $checkbox = $request->checkbox;
        $corder = DB::table('ordered')->where('slug', $slug)->first();
        if ($checkbox == "true"){
            DB::table('ordered')->where('slug', $slug)->update([
                'is_odisc' => 1,
            ]);
        } else {
            $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
            //$total_price = $menu->price * $corder->quantity;
            DB::table('ordered')->where('slug', $slug)->update([
                'is_odisc' => 0,
            ]);
        }
        $response = array(
            'ordered' => $slug,
            'subTotal' => $checkbox
        );

        echo json_encode($response);
    }

    public function addpwd(Request $request){
        $pwdName = strtoupper($request->pwdName);
        $pwdIdNumber = strtoupper($request->pwdIdNumber);
        $slug = strtoupper($request->slug);
        $category = $request->category;

        DB::table('seniors')->insert([
            'tran_id' => $slug,
            'name' => $pwdName,
            'id_no' => $pwdIdNumber,
            'category' => $category
        ]);
        echo json_encode('success');
        //return redirect()->route('table.index')->withInput()->with('message', 'Successfully Added');
    }
    public function delpwd(Request $request){
        
        $slug = strtoupper($request->slug);
        

        DB::table('seniors')->where('id', $slug)->delete();

        echo json_encode('success');
        //return redirect()->route('table.index')->withInput()->with('message', 'Successfully Added');
    }

    public function transferTable(Request $request){
        $id = $request->id;
        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->first();
        $res  = '';
        $t = '';


        $tables = DB::table('tables')->where('status', 0)->where('is_deleted', 0)->orderBy('name', 'ASC')->get();
            
        foreach($tables as $table){
            $t .= '<option value="'. $table->id .'">'.  $table->name .'</option>';
        }
        $res .= '
                        <p class="text-base leading-relaxed text-gray-500">
                            Select Table
                        </p>
                        <select data-id="'.$trans->id.'" id="tableOption" class=" block w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        
                            '.$t.'
                        </select>
        ';

        $response = array(
            'tables' => $res,
        );

        echo json_encode($response);
    }

    public function updateTable(Request $request){
        $tranID = $request->tranID;
        $NewTableId = $request->NewTableId;


        DB::table('transactions')->where('id', $tranID)->update(['table' => $NewTableId]);
        DB::table('tables')->where('id', $NewTableId)->update(['status' => '1']);

        $response = array(
            'tables' => $NewTableId,
        );

        echo json_encode($response);
    }

    public function movetoAr(Request $request){
        $id = $request->id;
        $arCode = $request->arCode;
        $customer_name_ar = $request->customer_name_ar;

        $msg = '';

        $settings = DB::table('settings')->where('id', 1)->first();

        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->first();

        // if ($settings->void_code == $arCode){
            DB::table('account_receivables')->insert([
                'tran_id' => $trans->id,
                'number' => $trans->number,
                'balance' => $trans->total,
                'total' => $trans->total,
                'amount' => 0,
                'service_charge' => $trans->service_charge,
                'sc_percent' => $trans->sc_percent,
                'pwd' => $trans->pwd,
                'customer_with_discount' => $trans->customer_with_discount,
                'total_customer' => $trans->total_customer,
                'lime_discount' => $trans->lime_discount,
                'ld_value' => $trans->ld_value,
                'payor_name' => $customer_name_ar,
                'payor_number' => $trans->payor_number,
                'lessVatDeduction' => $trans->lessVatDeduction,
                'vat' => $trans->vat,
                'vatSales' => $trans->vatSales,
                'vatExemptSales' => $trans->vatExemptSales,
                'type' => $trans->type,
                'table' => $trans->table,
                'status' => $trans->status,
                'order_status' => 'COMPLETED',
                'order_remarks' => $trans->order_remarks,
                'sales_type' => $trans->sales_type,
                'cashier' => auth()->id(),
                'slug' => $trans->slug,
                'tran_date' => $trans->created_at,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            DB::table('transactions')
            ->where('id', $trans->id)
            ->update([
                'status' => 'PAID',
                'order_status' => 'COMPLETED',
                'mode_of_payment' => 'AR',
                'cashier' => auth()->id(),
            ]);

            $msg = 'success';


        /* } else {


            $msg = 'error';
        } */
        
        $response = array(
            'tables' => $id,
            'msg' => $msg,
        );

        echo json_encode($response);
    }

    public function comp(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $checkbox = $request->checkbox;
        $corder = DB::table('ordered')->where('slug', $slug)->first();
        if ($checkbox == "true"){
            DB::table('ordered')->where('slug', $slug)->update([
                'type' => 1,
            ]);
        } else {
            $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
            $total_price = $menu->price * $corder->quantity;
            DB::table('ordered')->where('slug', $slug)->update([
                'type' => 0,
            ]);
        }

        $subTotal = $subTotalWDisc = $subTotalODisc = 0;
        $total = 0;
        $sc = 50;
        $vatsales = 0;
        $vat = 0;
        $net = $netWithDiscount = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();

        $discount = $SCPercent = $SCVal = $limePercent = $limeVal = 0;
        $qty = $amount = $price = $total = $SCPercent = $SCVal = $SCValperItem = $discount = $discountPerItem = 0;

        $trans = DB::table('transactions')->where('id', $corder->tran_id)->first();
        
        $orders = DB::table('ordered')
        ->join('menus', 'ordered.menu_id', '=', 'menus.id')
        ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.type', 'ordered.is_pwd', 'ordered.role', 'ordered.cashier', 'ordered.for_delete', 'ordered.is_odisc')
        ->where('ordered.tran_id', $trans->id)
        ->get();

        foreach($orders as $order){
            $qty = $order->quantity;
            $amount = $order->amount;
            //$price = $qty * $amount;
            if ($order->type != 1){
                if ($order->is_pwd == 1){
                    $subTotalWDisc += $order->amount;
                } else {
                    if ($order->is_odisc == 1){
                        $subTotalODisc +=  $order->amount;
                    } else {
                        $subTotal +=  $order->amount;
                    }
                    
                }
                
            } else {
                if ($order->is_odisc == 1){
                    $subTotalODisc +=  $order->amount;
                } else {
                    $subTotal +=  $order->amount;
                }
            }
            //mackey version
            
        }
        /* Start of computation update */   

        $tables = DB::table('tables')
                    ->where('id', $trans->table)
                    ->first();

        $SCPercent = ($tables->is_sc == 1) ? 0 : $trans->sc_percent;
        $limePercent = $trans->lime_discount;
        //$subTotal = $subTotal +$sc;
        $limeVal = ($limePercent / 100) * $subTotalODisc; 
        $net =  $subTotalWDisc + $subTotal + $subTotalODisc;
        //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
        
        // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
        if ($trans->customer_with_discount > 0) {
            $discount = 0.2 * $subTotalWDisc;

        }

        $SubtotalLessDiscount = ($net) - $discount - $limeVal;
        $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
        
        //discount default is 20% of the net value ex net = 100, then discount = 20
        $total = ($SubtotalLessDiscount + $SCVal);
        
        $subTotal = $subTotal + $subTotalWDisc;
        $amount = $total;

        DB::table('transactions')->where('id',$trans->id)->update([
            'total' => $total,
            'service_charge' =>  $SCVal,
            'sc_percent' =>  $SCPercent,
            'pwd' =>  $discount,
            'lime_discount' =>  $limePercent,
            'ld_value' =>  $limeVal,
            'lessVatDeduction' =>  $LessVatDeduction,
            'vat' =>  $vat,
            'vatSales' =>  $vatsales,
            'vatExemptSales' =>  $VatExemptSales,
        ]);
        /* End of computation update */

        $trans = DB::table('transactions')->where('id', $corder->tran_id)->get();

        $this->DisplayTableMenu($trans);
        
    }

    public function multivoidcheck(Request $request){

        $slug = $request->slug;
        $checkbox = $request->checkbox;
        $ordered = DB::table('ordered')
            ->where('slug', $slug)
            ->where('store_code', auth()->user()->store_code)
            ->exists();

        abort_unless($ordered, 404);

        if ($checkbox == 'true'){
            DB::table('ordered')->where('slug', $slug)->update([
                'for_delete' => 1,
            ]);
        } else {
            DB::table('ordered')->where('slug', $slug)->update([
                'for_delete' => 0,
            ]);
        }
        


    }

    public function multivoid(Request $request){

        $slug = $request->slug;
        $voidCode = $request->voidCode;
        //$id = $request->id;
        $tran = DB::table('transactions')
            ->join('tables', 'transactions.table', '=', 'tables.id')
            ->select('transactions.*')
            ->where('transactions.slug', $slug)
            ->where(function ($query) {
                $query->where('transactions.store_code', auth()->user()->store_code)
                    ->orWhereNull('transactions.store_code');
            })
            ->first();

        if (!$tran) {
            return response()->json(['msg' => 'error', 'error' => 'Transaction not found.'], 404);
        }

        $settings = DB::table('settings')->where('id', 1)->first();
        $isArTransaction = $tran->mode_of_payment === 'AR'
            || ($tran->status === 'UNPAID' && !empty($tran->payor_name));
        $previousPayments = $isArTransaction
            ? (float) DB::table('payment_logs')
                ->where('tran_id', $tran->id)
                ->where('is_deleted', 0)
                ->where(function ($query) {
                    $query->whereNull('is_void')->orWhere('is_void', 0);
                })
                ->sum('amount')
            : 0;
        $previousBalance = $isArTransaction
            ? max(0, round((float) $tran->total - $previousPayments, 2))
            : 0;
        $orderedForTransaction = function () use ($tran) {
            return DB::table('ordered')->where(function ($query) use ($tran) {
                $query->where('tran_id', $tran->id);

                if (!empty($tran->is_uuid)) {
                    $query->orWhere('tran_uuid', $tran->is_uuid);
                }
            });
        };

        if ($settings->void_code == $voidCode){
            if ($request->boolean('void_transaction')) {
                $orderedForTransaction()->update([
                    'for_delete' => 1,
                ]);
            }
            
         $orderedItems = $orderedForTransaction()->where('for_delete', 1)->get();


            

            foreach ($orderedItems as $ordered) {

                $menu = DB::table('menus')->where('id', $ordered->menu_id)->first();
                //echo $menu;
                if ($menu->is_combo == 1) {
                    $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                    foreach ($ings as $ing) {
                        $newQty = $ing->quantity * $ordered->quantity;
                        DB::table('menus')->where('id', $ing->inventory_id)->increment('current_quantity', $newQty);
                        DB::table('menus')->where('id', $ing->inventory_id)->increment('quantity', $newQty);
                    }
                } else {
                    DB::table('menus')->where('id', $ordered->menu_id)->increment('current_quantity', $ordered->quantity);
                    DB::table('menus')->where('id', $ordered->menu_id)->increment('quantity', $ordered->quantity);
                }
                
                DB::table('ordered_remove')->insert([
                    'id' => $ordered->id,
                    'slug' => $ordered->slug,
                    'menu_id' => $ordered->menu_id,
                    'quantity' => $ordered->quantity,
                    'tran_id' => $tran->id,
                    'amount' => $ordered->amount, 
                    'food_cost' => $ordered->food_cost,
                    'cashier' => $ordered->cashier,
                    'role' => $ordered->role,
                    'discount_amount' => $ordered->discount_amount,
                    'lime_discount' => $ordered->lime_discount,
                    'type' => $ordered->type,
                    'is_pwd' => $ordered->is_pwd,
                    'is_odisc' => $ordered->is_odisc,
                    'remarks' => $ordered->remarks,
                    'status' => $ordered->status,
                    'serve_status' => 'CANCELLED',
                    'print' => 0,
                    'for_delete' => 0,
                    'is_split' => 0,
                    'department' => $ordered->department,
                    'branch' => $ordered->branch,
                    'service_charge' => $ordered->service_charge,
                    'void_code' => $voidCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }   
        $orderedForTransaction()->where('for_delete', 1)->delete();


        $subTotal = $subTotalWDisc = $subTotalODisc = 0;
        $total = 0;
        $sc = 50;
        $vatsales = 0;
        $vat = 0;
        $net = $netWithDiscount = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;

        /* $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first(); */

        $discount = $SCPercent = $SCVal = $limePercent = $limeVal = 0;
        $qty = $amount = $price = $total = $SCPercent = $SCVal = $SCValperItem = $discount = $discountPerItem = 0;

        $orderResult = '';

        //$trans = DB::table('transactions')->where('id', $corder->tran_id)->first();
        
        $orders = $orderedForTransaction()
        ->join('menus', 'ordered.menu_id', '=', 'menus.id')
        ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.type', 'ordered.is_pwd', 'ordered.is_odisc')
        ->get();

        $ordersCount = $orderedForTransaction()
        ->join('menus', 'ordered.menu_id', '=', 'menus.id')
        ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.type', 'ordered.is_pwd')
        ->count();
        if ($ordersCount > 0){

            foreach($orders as $order){
                $qty = $order->quantity;
                $amount = $order->amount;
                //$price = $qty * $amount;
                if ($order->type != 1){
                    if ($order->is_pwd == 1){
                        $subTotalWDisc += $order->amount;
                    } else {
                        if ($order->is_odisc == 1){
                            $subTotalODisc +=  $order->amount;
                        } else {
                            $subTotal +=  $order->amount;
                        }
                        
                    }
                    
                } else {
                    if ($order->is_odisc == 1){
                        $subTotalODisc +=  $order->amount;
                    } else {
                        $subTotal +=  $order->amount;
                    }
                }
                //mackey version
                
            }
            /* Start of computation update */   
            $SCPercent = $tran->sc_percent;
            $limePercent = $tran->lime_discount;
            //$subTotal = $subTotal +$sc;
            $limeVal = ($limePercent / 100) * $subTotalODisc; 
            $net =  $subTotalWDisc + $subTotal + $subTotalODisc;
            //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
            
            // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
            if ($tran->customer_with_discount > 0) {
                $discount = 0.2 * $subTotalWDisc;

            }

            $SubtotalLessDiscount = ($net) - $discount - $limeVal;
            $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            
            //discount default is 20% of the net value ex net = 100, then discount = 20
            $total = ($SubtotalLessDiscount + $SCVal);
            
            $subTotal = $subTotal + $subTotalWDisc;
            $amount = $total;
            DB::table('transactions')->where('id',$tran->id)->update([
                'total' => $total,
                'service_charge' =>  $SCVal,
                'sc_percent' =>  $SCPercent,
                'pwd' =>  $discount,
                'lime_discount' =>  $limePercent,
                'ld_value' =>  $limeVal,
                'lessVatDeduction' =>  $LessVatDeduction,
                'vat' =>  $vat,
                'vatSales' =>  $vatsales,
                'vatExemptSales' =>  $VatExemptSales,
            ]);
        } else {
            DB::table('transactions')->where('id',$tran->id)->update([
                'total' => 0,
                'service_charge' =>  0,
                'sc_percent' =>  0,
                'pwd' =>  0,
                'lime_discount' =>  0,
                'ld_value' =>  0,
                'lessVatDeduction' =>  0,
                'vat' =>  0,
                'vatSales' =>  0,
                'vatExemptSales' =>  0,
                'order_status' =>  'CANCELLED',
            ]);
        }
        

        
        /* End of computation update */
            $msg = 'success';
        } else {
            $msg = 'error';
        }

        if ($msg === 'success' && $isArTransaction) {
            $updatedTransaction = DB::table('transactions')->where('id', $tran->id)->first();
            $updatedPayments = (float) DB::table('payment_logs')
                ->where('tran_id', $tran->id)
                ->where('is_deleted', 0)
                ->where(function ($query) {
                    $query->whereNull('is_void')->orWhere('is_void', 0);
                })
                ->sum('amount');
            $updatedBalance = max(0, round((float) $updatedTransaction->total - $updatedPayments, 2));
            $creditToRestore = round(max(0, $previousBalance - $updatedBalance), 2);

            if ($creditToRestore > 0) {
                DB::table('tables')
                    ->where('id', $tran->table)
                    ->where('store_code', auth()->user()->store_code)
                    ->update([
                        'credit_limit' => DB::raw('COALESCE(credit_limit, 0) + ' . number_format($creditToRestore, 2, '.', '')),
                    ]);
            }
        }

        $response = array(
            'msg' => $msg,
            
        );
        echo json_encode($response);
        


    }
    
     public function bar(Request $request){
        $slug = $request->slug;


        $tran =DB::table('transactions')
            ->where('id', $slug)
            ->first();
            
        DB::table('prints')->where('tran_id', $tran->id)->orderBy('created_at', 'DESC')->limit(1)->update([
            'status' => 0,
        ]);

        DB::table('ordered')->where('tran_id', $tran->id)->where('department', 2)->update([
            'print' => 0,
        ]);

        echo json_encode('success');
    }

    public function kitchen(Request $request){
        $slug = $request->slug;


        $tran =DB::table('transactions')
            ->where('id', $slug)
            ->first();
            
        DB::table('prints')->where('tran_id', $tran->id)->orderBy('created_at', 'DESC')->limit(1)->update([
            'status' => 0,
        ]);

        DB::table('ordered')->where('tran_id', $tran->id)->where('department', 1)->update([
            'print' => 0,
        ]);

        echo json_encode('success');
    }


    public function DisplayTableMenu ($trans){

        $res = $res1 = '';
        $co = '0';
        $pd = '1';
        
        
        $disc = '';
        $transStatus = '';
        if($trans->count() < 1){
            $co = '1';
        }

        $subTotal = $subTotalWDisc = $subTotalODisc = 0;
        $total = 0;
        $sc = 50;
        $vatsales = 0;
        $vat = 0;
        $net = $netWithDiscount = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        $customer_with_discount = 0;
        
        $discount = $SCPercent = $SCVal = $limePercent = $limeVal = 0;

        if (!empty($trans)){
            foreach($trans as $tran){
                $subTotal = $subTotalWDisc = $subTotalNoDisc = $subTotalODisc = 0;
                $total = 0;
                $sc = 50;
                $vatsales = 0;
                $vat = 0;
                $net = $netWithDiscount = 0;
                $VatExemptSales = 0;
                $LessVatDeduction =0;
                $SubtotalLessDiscount = 0;
                $customer_with_discount = 0;
                
                $discount = $SCPercent = $SCVal = $limePercent = $limeVal = 0;

                
                $orders = DB::table('ordered')
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.is_pwd', 'ordered.type', 'ordered.role', 'ordered.cashier', 'ordered.for_delete', 'ordered.is_odisc', 'ordered.is_discount')
                    ->where('ordered.tran_id', $tran->id)
                    ->get();

                /* Start of new Computation 01-23-2025 */
                /* Start of new Computation 01-23-2025 */
                /* Start of new Computation 01-23-2025 */
                /* Start of new Computation 01-23-2025 */
                $tables = DB::table('tables')
                ->where('id', $tran->table)
                ->first();
            
                $SCPercent = ($tables->is_sc == 1) ? 0 : $tran->sc_percent;

                $limePercent = $tran->lime_discount;

                if ($orders->count() > 0) {
                    foreach ($orders as $order) {
                        if ($order->type != 1){
                            if ($order->is_discount == 0){
                                $subTotalNoDisc += $order->amount;
                            } else {
                                $subTotal +=  $order->amount;
                            }
                            
                        }
                        
                    }
        
            

                     $limeVal = ($limePercent / 100) * $subTotal; 
                    $net =  $subTotal + $subTotalNoDisc;
                    //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
                    $lessVatSubtotal = $subTotal / 1.12;
                    $lessVatSubtotalNoDisc = $subTotalNoDisc / 1.12;
                    // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
                    $x =0;
                    if ($tran->customer_with_discount > 0) {
                        $x = $lessVatSubtotal / $tran->total_customer;
                        $y = 0.2 * $tran->customer_with_discount;
                        $discount = round($x * $y,2);
        
                        $VatExemptSales = round($x * $tran->customer_with_discount,2);
                        $LessVatDeduction = $VatExemptSales * 0.12;
        
                        $SubtotalLessDiscount = round($lessVatSubtotal+$lessVatSubtotalNoDisc - $discount - $limeVal,2);
                        $SCVal = round(($SCPercent / 100) * $SubtotalLessDiscount,2);
                        $vatsales = round(($lessVatSubtotal + $lessVatSubtotalNoDisc) - $VatExemptSales,2);
                    } else {
                        $SubtotalLessDiscount = ($net - $limeVal)/1.12;
                        $SCVal = round(($SCPercent / 100) * $SubtotalLessDiscount,2);
                        $vatsales = round($SubtotalLessDiscount,2);
                    }
                    
                    
                    
                    $vat = round($vatsales * 0.12,2);
                    
                    $total = round(($net + $SCVal) - ($LessVatDeduction + $discount + $limeVal),2);
                    
                    $subTotal = $net;
                }

                /* end of new Computation 01-23-2025 */
                /* end of new Computation 01-23-2025 */
                /* end of new Computation 01-23-2025 */
                /* end of new Computation 01-23-2025 */

                
                if ($tran->status != 'PAID'){
                    DB::table('transactions')->where('id',$tran->id)->update([
                        'total' => $total,
                        'service_charge' =>  $SCVal,
                        'sc_percent' =>  $SCPercent,
                        'pwd' =>  $discount,
                        'lime_discount' =>  $limePercent,
                        'ld_value' =>  $limeVal,
                        'lessVatDeduction' =>  $LessVatDeduction,
                        'vat' =>  $vat,
                        'vatSales' =>  $vatsales,
                        'vatExemptSales' =>  $VatExemptSales,
                    ]);
                }
                

                /* Start Computation */

                // foreach($orders as $order){
                    
                //     if ($order->type != 1){
                //         if ($order->is_pwd == 1){
                //             $subTotalWDisc += $order->amount;
                //         } else {
                //             if ($order->is_odisc == 1){
                //                 $subTotalODisc +=  $order->amount;
                //             } else {
                //                 $subTotal +=  $order->amount;
                //             }
                            
                //         }
                        
                //     } else {
                //         if ($order->is_odisc == 1){
                //             $subTotalODisc +=  $order->amount;
                //         } else {
                //             $subTotal +=  $order->amount;
                //         }
                //     }
                // }
                // //$subTotal = $subTotal +$sc;
                // $limeVal = ($limePercent / 100) * $subTotalODisc; 
                // $net =  $subTotalWDisc + $subTotal + $subTotalODisc;
                // //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
                
                // // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
                // if ($tran->customer_with_discount > 0) {
                //     $discount = 0.2 * $subTotalWDisc;

                // }

                // $SubtotalLessDiscount = ($net) - $discount - $limeVal;
                // $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;

                // /* start of dp section */
                // $downpayments = DB::table('payment_dps')
                //     ->where('tran_id', $tran->id)
                //     ->get();

                // $dp_amount = 0;

                // foreach ($downpayments as $dp){
                //     $dp_amount += $dp->amount;
                // }

                // /* end of dp section */


                // //discount default is 20% of the net value ex net = 100, then discount = 20
                // $total = ($SubtotalLessDiscount + $SCVal)-$dp_amount;
                
                // $subTotal = $subTotal + $subTotalWDisc;
                /* End of computation */
                
            }
            
        }
        $trans_id = (!empty($tran->table)) ? $tran->table : 0;
        
        /* $trans = DB::table('transactions'
        )->where('id', $trans_id)
        ->orderby('id', 'desc')
        ->get(); */

        $trans = DB::table('transactions'
        )->where('table', $trans_id)
        ->where('order_status', '!=', 'CANCELLED')
        ->where('order_status', '!=', 'COMPLETED')
        ->orderby('id', 'desc')
        ->get();

        if (!empty($trans)){
        foreach($trans as $tran){
            $subTotal = $subTotalWDisc = $subTotalODisc = 0;
            if($tran->status == 'PAID'){
                $sColor = 'rgb(16 185 129)';
            }else{
                $sColor = 'rgb(240 82 82)';
                $co = 0;
                if (auth()->user()->role != 11){
                    $pd = '0';
                } else {
                    $pd = '1';
                }
            }

            $m = $disc = '';

            $downpayments = DB::table('payment_dps')
                    ->where('tran_id', $tran->id)
                    ->get();

            $orders = DB::table('ordered')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.is_pwd', 'ordered.type', 'ordered.role', 'ordered.cashier', 'ordered.for_delete', 'ordered.is_split', 'ordered.is_odisc')
                ->where('ordered.tran_id', $tran->id)
                ->get();
            
            foreach($orders as $order){
                
                $checkStatus = ($order->type == 0) ? '' : 'checked' ;
                $is_pwdCheck = ($order->is_pwd == 0) ? '' : 'checked' ;
                $is_del = ($order->for_delete == 0) ? '' : 'checked' ;
                $is_split = ($order->is_split == 0) ? '' : 'checked' ;
                $is_odisc = ($order->is_odisc == 0) ? '' : 'checked' ;
                
                // if ($order->role == 2 || $order->role == 1){
                $serverName = 'N/A';

                if (!empty($order->cashier)) {
                    $staff = DB::table('users')->where('id', $order->cashier)->first();

                    if ($staff) {
                        $serverName = $staff->name;
                    } else {
                        // fallback: maybe it's a staff record, not a user
                        $staffRec = DB::table('staff')->where('id', $order->cashier)->first();
                        if ($staffRec) {
                            $serverName = trim($staffRec->fname . ' ' . $staffRec->lname);
                        }
                    }
                }

                // Optional: honor the original Guest logic
                if ($order->role == 7) {
                    $serverName = 'Guest';
                }
                // } elseif($order->role == 7) {
                    
                //     $serverName = 'Guest';
                // } else {
                //     $staff = DB::table('staff')
                //     ->Where('id', $order->cashier)
                //     ->first();
                //     $serverName = $staff->fname . ' ' . $staff->lname;
                // } 
                

                /* start of multiple void checkbox */
                $multipleCheckBox = '<input type="checkbox" id="multiVoid'.$order->slug.'" data-slug="'. $order->slug .'" class="delorderedBtn max-w-xs ml-2" '. $is_del .'>';
                /* end of multiple void checkbox */

                if($order->quantity > 1){
                    
                    if($order->status != 'SERVED'){

                        $m .= '
                            <div class="grid grid-cols-7 gap-1  mb-5">
                            <div class="max-w-[70px]">';
                        if ($tran->status != 'PAID'){
                            if (auth()->user()->role != 7){
                                $m .= '
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary max-w-xs " '.$checkStatus .' id="'.$order->slug.'">
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="orderSelection max-w-xs " id="orderSelection'. $order->slug .'" '. $is_pwdCheck.'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="spliSelect max-w-xs " id="split'. $order->slug .'" '. $is_split.'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="odiscSelect max-w-xs " id="odiscSelect'. $order->slug .'" '. $is_odisc.'>
                                ';
                                // <input type="checkbox" data-slug="'. $order->slug .'" class="spliSelect max-w-xs " id="split'. $order->slug .'" '. $is_split.'>
                            }
                        }
                               $m .= '</div>
                                <div class="col-span-3">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden text-left">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                                <div class="justify-self-center">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">₱ '.$order->amount.'.00</h1>
                                </div>';
                                if (auth()->user()->role != 7 && $tran->status != 'PAID'){
                               $m .=' <div style="margin-right: 20px;" class="justify-self-end col-span-2">';
                               
                               $m .='<button data-slug="'.$order->slug.'" data-table="'.$tran->table.'" class="reduceButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50 mr-4"><i class="uil uil-minus text-xl text-red-500 hover:text-red-600 "></i></button>
                                    <button data-slug="'.$order->slug.'" data-table="'.$tran->table.'" class="removeButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50"><i class="uil uil-multiply text-xl text-red-500 hover:text-red-600 "></i></button>';
                               
                                $m .= $multipleCheckBox;
                                
                                $m .= '</div>';
                                    }
                                $m .= '</div>';
                                $m .= '<div class="grid grid-cols-7  items-center">
                                   <div class="max-w-[50px]"></div><div class="col-span-5 text-sm"> Server : <span class="text-blue-600"> '.
                                   $serverName . ' <span></div></div>';
                        //$co = 1;
                    }else{
                        $m .= '
                            <div class="grid grid-cols-7 mb-5 items-center">
                            <div class="max-w-[70px]">';
                        if ($tran->status != 'PAID'){
                            if (auth()->user()->role != 7){
                                $m .= '
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary max-w-xs " '.$checkStatus .' id="'.$order->slug.'">
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="orderSelection max-w-xs " id="orderSelection'. $order->slug .'" '. $is_pwdCheck.'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="spliSelect max-w-xs " id="split'. $order->slug .'" '. $is_split.'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="odiscSelect max-w-xs " id="odiscSelect'. $order->slug .'" '. $is_odisc.'>
                                    
                                ';
                                // <input type="checkbox" data-slug="'. $order->slug .'" class="spliSelect max-w-xs " id="split'. $order->slug .'" '. $is_split.'>
                            }
                        }
                        $m .= '</div>
                                <div class="col-span-3">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                                <div class="justify-self-center">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">₱ '.$order->amount.'.00</h1>
                                </div>
                            </div>
                        ';
                        $m .= '<div class="grid grid-cols-7  items-center">
                                   <div class="max-w-[50px]"></div><div class="col-span-5 text-sm"> Server : <span class="text-blue-600"> '.
                                   $serverName . ' <span></div></div>';
                        $co = 1;
                    }
                }else{
                    if($order->status != 'SERVED'){
                        $m .= '
                            <div class="grid grid-cols-7 gap-1 mb-5 items-center">
                            <div class="max-w-[70px]">';
                        if ($tran->status != 'PAID'){
                            if (auth()->user()->role != 7){
                            $m .= '
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary max-w-xs " '.$checkStatus .' id="'.$order->slug.'">
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="orderSelection max-w-xs " id="orderSelection'. $order->slug .'" '. $is_pwdCheck.'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="spliSelect max-w-xs " id="split'. $order->slug .'" '. $is_split.'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="odiscSelect max-w-xs " id="odiscSelect'. $order->slug .'" '. $is_odisc.'>
                                    
                                ';
                                // <input type="checkbox" data-slug="'. $order->slug .'" class="spliSelect max-w-xs " id="split'. $order->slug .'" '. $is_split.'>
                            }
                        }
                        $m .= '</div>
                                <div class="col-span-3">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                                <div class="justify-self-center">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">₱ '.$order->amount.'.00</h1>
                                </div>';
                                if (auth()->user()->role != 7 && $tran->status != 'PAID'){
                                $m .= '<div style="margin-right: 20px;" class="justify-self-end col-span-2">';
                                $m .='<button data-slug="'.$order->slug.'" data-table="'.$tran->table.'" class="removeButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50"><i class="uil uil-multiply text-xl text-red-500 hover:text-red-600 "></i></button>';
                                $m .= $multipleCheckBox;
                                $m .='</div>';
                                }

                            $m.='</div>
                        ';
                        $m .= '<div class="grid grid-cols-7  items-center">
                                   <div class="max-w-[50px]"></div><div class="col-span-5 text-sm"> Server : <span class="text-blue-600"> '.
                                   $serverName . ' <span></div></div>';
                        //$co = 1;
                    }else{
                        $m .= '
                            <div class="grid grid-cols-6 mb-5 items-center">
                                
                                <div class="col-span-2 mb-5">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                                <div class="justify-self-center">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">₱ '.$order->amount.'.00</h1>
                                </div>
                            </div>
                        ';
                        $m .= '<div class="grid grid-cols-7  items-center">
                                   <div class="max-w-[50px]"></div><div class="col-span-5 text-sm"> Server : <span class="text-blue-600"> '.
                                   $serverName . ' <span></div></div>';
                        $co = 1;
                    }
                }
                

                if ($order->type != 1){
                    if ($order->is_pwd == 1){
                        $subTotalWDisc += $order->amount;
                    } else {
                        if ($order->is_odisc == 1){
                            $subTotalODisc +=  $order->amount;
                        } else {
                            $subTotal +=  $order->amount;
                        }
                        
                    }
                    
                } else {
                    if ($order->is_odisc == 1){
                        $subTotalODisc +=  $order->amount;
                    } else {
                        $subTotal +=  $order->amount;
                    }
                }
                    
            }
            $subTotal = $subTotalODisc + $subTotal + $subTotalWDisc;// ;
            $disc .= '
                <div class="grid grid-cols-7 items-center">
                    <div class="col-span-2">
                        
                    </div>
                    
                    <div style="margin-right: 20px;" class="text-left col-span-3">
                            <strong ># of Pax</strong>
                            <button id="" type="button" data-tran="'.$tran->id.'" class="editPaxButton text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            <span id=""> '.$tran->total_customer.'</span>
                    </div>
            </div>';
            $ctr = 1;
            foreach ($downpayments as $dp){
            $disc .= '
                <div class="grid grid-cols-7 items-center">
                    <div class="col-span-2">
                        
                    </div>
                    
                    <div style="margin-right: 20px;" class="text-left col-span-3">
                            <strong >Down payment '.$ctr.'</strong>
                    </div>';
            $disc .= '<div class="col-span-2 text-right mr-4">
                            <span id="">₱ '.number_format($dp->amount,2).'</span>
                            </div>';
                    
                    
            $disc .= '</div>';
            $ctr++;
            }
            //echo json_encode($tran->status);
            if ($tran->status != 'PAID'){
                if (auth()->user()->role != 7){
                //subtotal
                $disc .= '
                <div class="grid grid-cols-7 items-center">
                    <div class="col-span-2">
                        
                    </div>
                    
                    <div style="margin-right: 20px;" class="text-left col-span-3">
                            <strong >Subtotal</strong>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                           ₱ <span id="orderedSubtotal">  '.number_format($subTotal,2).'</span>
                    </div>
                </div>';
                // pwd section
                $disc .= '
                <div class="grid grid-cols-7 items-center ">
                    <div class="col-span-2 text-right">
                        <button data-slug="'.$tran->id.'" class="addPwdButton mr-2 border border-gray-300 shadow py-1 px-2 rounded-lg hover:bg-gray-50"><i class="uil uil-plus text-lg text-blue-500 hover:text-blue-600 "></i></button>
                    </div>
                    <div style="margin-right: 20px;" class="justify-self col-span-3">
                        <div class="justify-self-start flex gap-x-2 items-center">
                            <strong >PWD/SrCitizen</strong>
                            <button id="DiscountButton" type="button" data-table="'.$tran->table.'" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                            
                        </div>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            ₱ <span id="orderedPwd"> '.number_format($tran->pwd,2).'</span>
                    </div>
                </div>';
                $pwds = DB::table('seniors')
                ->select('id','name', 'id_no','category')
                ->where('tran_id', $tran->id)
                ->get();
                if ($pwds->count() > 0){
                    $disc .= '
                    <div class="grid grid-cols-7 items-center ">
                        <div class="col-span-2">
                            
                        </div>
                        <div style="margin-right: 20px;" class="col-span-5">
                            <table class="grid  mr-1 border-2 rounded-0 w-full">
                                <thead class="mr-5">
                                <tr class="">   
                                    <th class="w-1/2 text-left">Category</th>
                                    <th class="w-1/2 text-left">Name</th>
                                    <th class="w-1/2 text-left">ID #.</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                ';
                    foreach($pwds as $pwd){
                        $cat = ($pwd->category == 'sr') ? "Senior" : "Pwd";
                        $disc .= '
                                <tr class="text-gray-400 whitespace-nowrap">   
                                    <td class="w-1/2"> '.$cat.'</td>  
                                    <td class="w-1/2">'.$pwd->name.'</td>
                                    
                                    <td class="w-1/2">'.$pwd->id_no.'</td>
                                    <td class="text-center">
                                    <button type="button" data-slug="'.$pwd->id.'" class="deletePwdBtn text-red-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M261-120q-24.75 0-42.375-17.625T201-180v-570h-41v-60h188v-30h264v30h188v60h-41v570q0 24-18 42t-42 18H261Zm438-630H261v570h438v-570ZM367-266h60v-399h-60v399Zm166 0h60v-399h-60v399ZM261-750v570-570Z"/></svg></span></button>
                                    </td>
                                </tr>
                                ';
                    }
                    $disc .= '
                        
                                </thead>
                            </table>
                        </div>
                    </div>';
                }
                
                // owner discount section
                $disc .= '
                <div class="grid grid-cols-7 items-center ">
                    <div class="col-span-2">
                        
                    </div>
                    <div style="margin-right: 20px;" class="justify-self col-span-3">
                        <div class="justify-self-start flex gap-x-2 items-center">
                            <strong >Discount [<span id="limeDiscountSpan">'.$tran->lime_discount.'</span>%]</strong> 
                            <button id="LimeDiscountButton" type="button" data-table="'.$tran->table.'" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                        </div>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            ₱ <span id="orderedOwnerDisc"> '.number_format($tran->ld_value,2).'</span>
                    </div>
                </div>';

                //SC section
                $disc .= '
                <div class="grid grid-cols-7 items-center ">
                    <div class="col-span-2">
                        
                    </div>
                    <div style="margin-right: 20px;" class="justify-self col-span-3">
                        <div class="justify-self-start flex items-center">
                            <strong >Service Charge </strong> [<span id="scSpan">'.$tran->sc_percent.' </span>%] 
                            <button id="scBtn" type="button" data-table="'.$tran->table.'" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                        </div>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            ₱ <span id="orderedSc"> '.number_format($tran->service_charge,2).'</span>
                    </div>
                </div>';
                //total
                $disc .= '
                <div class="grid grid-cols-7 items-center">
                    <div class="col-span-2">
                        
                    </div>
                    <div style="margin-right: 20px;" class="justify-self col-span-3">
                        <div class="justify-self-start flex gap-x-2 items-center">
                            <strong >Total</strong>
                        </div>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            ₱ <span id="orderedTotal"> '.number_format($tran->total,2).'</span>
                    </div>
                </div>';
                }

                if (auth()->user()->role != 7){
            
                    // AR ADDED
                    
                    $btnTransfer = '
                    <button data-slug="'.$tran->slug.'" class="btnTransfer bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">
                        <i class="uil uil-arrow-to-right text-xl"></i>
                    </button> 
                    <button class="btnAr bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                        <i class="uil uil-money-insert text-xl"></i> 
                    </button>
                    <button class="btnSplit bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded">
                        <i class="uil uil-align-center-h text-xl"></i> 
                    </button>
                    
                    <button class="btnReprintBar bg-gray-300 hover:bg-gray-600 text-white font-bold py-1 px-2 rounded ml-12" data-slug="'.$tran->id.'">
                        <i class="uil uil-coffee text-xl"></i> 
                    </button>
                    <button class="btnReprintKitchen bg-emerald-400 hover:bg-emerald-500 text-white font-bold py-1 px-2 rounded" data-slug="'.$tran->id.'">
                        <i class="uil uil-utensils text-xl"></i>
                    </button>
                    ';
                    /* <button data-slug="'.$tran->slug.'" class="btnDp bg-orange-500 hover:bg-orange-300 text-white font-bold py-1 px-2 rounded">
                        <i class="uil uil-transaction text-xl"></i> 
                    </button>
                    <button class="btnSplit bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded">
                        <i class="uil uil-align-center-h text-xl"></i> 
                    </button><button class="btnSplit bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded">
                        <i class="uil uil-align-center-h text-xl"></i> 
                    </button>
                    <button class="btnReprintBar bg-gray-300 hover:bg-gray-600 text-white font-bold py-1 px-2 rounded ml-12" data-slug="'.$tran->id.'">
                        <i class="uil uil-coffee text-xl"></i> 
                    </button>
                    <button class="btnReprintKitchen bg-emerald-400 hover:bg-emerald-500 text-white font-bold py-1 px-2 rounded" data-slug="'.$tran->id.'">
                        <i class="uil uil-utensils text-xl"></i>
                    </button>
                    <button class=" bg-orange-500 hover:bg-orange-700 text-white font-bold py-1 px-2 rounded ml-12">
                        <i class="uil uil-glass-martini-alt-slash text-xl"></i> 
                    </button>
                     */
                } else {
                    $btnTransfer = '';  
                }

                $co = '0';
            } else {

                if (auth()->user()->role != 7){
            
                    // AR ADDED
                    
                    $btnTransfer = '
                    <button data-slug="'.$tran->slug.'" class="btnTransfer bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">
                        <i class="uil uil-arrow-to-right text-xl"></i>
                    </button> ';
                } else {
                    $btnTransfer = '';  
                }
                $co = '1';
                $disc = '';
            }
            // $res1 .= '
            // <div class="h-auto mb-5">
            // <span style="">'..' </span>
                
            // </div>
            // ';
            //href="{{ route(\'order.OrderdeleteDiscount\') }}"
            if (empty($tran->order_remarks)){
                $OrderRemarks = 'None';
            } else {
                $OrderRemarks =$tran->order_remarks;
            }
            
           $res .= '
                <div class="relative h-auto mb-5">
                    <span style="border-color: rgb(209 213 219 / var(--tw-border-opacity)); margin-left: 20px;" class="relative text-gray-500 font-bold text-xl border px-4 py-2 w-auto bg-white z-50 rounded-lg">'.$tran->number.' - <span style="color: '.$sColor.';"> '.$tran->status.'</span> '.$btnTransfer.'
                    <span>
                    </span></span>
                    <div style="top: -13px; border-color: rgb(209 213 219 / var(--tw-border-opacity));" class="relative border border-gray-500 z-10 w-full pt-6 pl-2 text-xl font-semibold text-gray-500 tracking-wide rounded-lg">
                    <div class="flex justify-between mb-2">
                                <div class="col-span-1">
                                <span class="text-base "><i class="uil uil-gift"></i></span>
                                <span class="text-base"><i class="uil uil-percentage"></i></span>
                                <span class="text-base"><i class="uil uil-align-center-h"></i></span>
                                <span class="text-base"><i class="uil uil-pricetag-alt"></i></span>
                                </div>
                                
                                <div class="col-span-1 text-right ">';
                                if (auth()->user()->role != 7 && $tran->status != 'PAID'){
                                $res .= ' <button data-slug="'.$tran->slug.'" class="btnMultipleVoid bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded mr-3">
                                                        <i class="uil uil-trash text-lg"></i>
                                                    </button>';
                                /* <span class="text-lg"><i class="uil uil-align-center-h"></i></span> */
                                }
                    $res .= '</div>
                    </div>
                    <span class="col-span-8 text-xs flex">
                                
                            </span>'.$m.'<span style="color: rgb(240 82 82);">Remarks : </span>'.
                    $OrderRemarks.'</span>'.$disc.'</div>
                </div>
            ';
           $transStatus =  $tran->status;
           
           
        }

        

        } // end of !empty trans

        $unpaidTrans = DB::table('transactions')
            ->where('table', $trans_id)
            ->where('order_status', '!=', 'CANCELLED')
            ->where('order_status', '!=', 'COMPLETED')
            ->where('status', '!=', 'PAID')
            ->count();
        if ($unpaidTrans > 0){
            $co = 0;
        }

        $response = array(
            'allOrders' => $res,
            'co' => $co,
            'pd' => $pd,
            'msg' => 'success',
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'discount' => number_format($discount,2),
            'ServiceCharge' => number_format($SCVal,2),
            'scPercent' => $SCPercent,
            'customer_with_discount' =>  $customer_with_discount,
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => number_format($limeVal,2),
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
        );
        echo json_encode($response);
    }

    public function saveMOP($tran_id, $mop, $amount , $cashier){
        DB::table('payment_orders')->insert([
            'tran_id' => $tran_id,
            'mop' => $mop,
            'amount' => $amount,
            'cashier' => $cashier,
            'created_at' => date('Y-m-d h:i:s'),
            'updated_at' => date('Y-m-d h:i:s')
        ]);
    }

    private function payArTransaction(Request $request)
    {
        $paymentMethods = [
            'cashMop' => 'CASH',
            'ccMop' => 'CREDIT CARD - MASTERCARD',
            'visaMop' => 'VISA',
            'gcashMop' => 'GCASH',
            'wcpMop' => 'WE CHAT PAY',
            'amexMop' => 'AMEX',
            'bdoMop' => 'BDO PAY',
            'bancnetMop' => 'BANCNET',
            'dinersMop' => 'DINERS',
            'dpMop' => 'DOWN PAYMENT',
            'giftMop' => 'GIFT CERT',
            'jcbMop' => 'JCB',
            'mayaMop' => 'PAYMAYA',
            'grabPayMop' => 'GRAB PAY',
            'unionMop' => 'UNION PAY',
            'grabMop' => 'GRAB FOOD',
            'pandaMop' => 'FOOD PANDA',
            'qrphMop' => 'QR PH',
        ];
        $payments = [];
        $totalPayment = 0;

        foreach ($paymentMethods as $field => $method) {
            $inputAmount = $request->input($field, 0);

            if (!is_numeric($inputAmount)) {
                return response()->json(['error' => 'Payment amounts must be numeric.'], 422);
            }

            $amount = round((float) $inputAmount, 2);

            if ($amount < 0) {
                return response()->json(['error' => 'Payment amounts cannot be negative.'], 422);
            }

            if ($amount > 0) {
                $payments[$method] = $amount;
                $totalPayment += $amount;
            }
        }

        $totalPayment = round($totalPayment, 2);

        if ($totalPayment <= 0) {
            return response()->json(['error' => 'Enter a payment amount.'], 422);
        }

        return DB::transaction(function () use ($request, $payments, $totalPayment) {
            $transaction = DB::table('transactions')
                ->join('tables', 'transactions.table', '=', 'tables.id')
                ->select('transactions.*')
                ->where('transactions.id', $request->input('ar_transaction_id'))
                ->where(function ($query) {
                    $query->where('transactions.store_code', auth()->user()->store_code)
                        ->orWhereNull('transactions.store_code');
                })
                ->where('transactions.status', 'UNPAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->lockForUpdate()
                ->first();

            if (!$transaction || empty($transaction->payor_name)) {
                return response()->json(['error' => 'The unpaid client transaction was not found.'], 404);
            }

            $previousPayments = (float) DB::table('payment_logs')
                ->where('tran_id', $transaction->id)
                ->where('is_deleted', 0)
                ->where(function ($query) {
                    $query->whereNull('is_void')->orWhere('is_void', 0);
                })
                ->sum('amount');
            $balanceDue = round(max(0, (float) $transaction->total - $previousPayments), 2);

            if ($totalPayment > $balanceDue) {
                return response()->json(['error' => 'Payment cannot exceed the remaining balance.'], 422);
            }

            $paymentDate = now();
            $paymentType = $paymentDate->toDateString() > date('Y-m-d', strtotime($transaction->created_at)) ? 1 : 0;
            $remainingBalance = round(max(0, $balanceDue - $totalPayment), 2);

            foreach ($payments as $method => $amount) {
                DB::table('payment_orders')->insert([
                    'tran_id' => $transaction->id,
                    'mop' => $method,
                    'amount' => $amount,
                    'cashier' => auth()->id(),
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);

                $paymentMethod = DB::table('mode_of_payment')
                    ->where('store_code', auth()->user()->store_code)
                    ->where('name', $method)
                    ->first();

                DB::table('payment_logs')->insert([
                    'tran_id' => $transaction->id,
                    'tran_number' => $transaction->number,
                    'tran_uuid' => $transaction->is_uuid,
                    'mop_id' => $paymentMethod->id ?? null,
                    'label' => $method,
                    'total' => $transaction->total,
                    'amount' => $amount,
                    'balance' => $remainingBalance,
                    'cashier_id' => auth()->id(),
                    'type' => $paymentType,
                    'is_deleted' => 0,
                    'is_void' => 0,
                    'client_name' => $transaction->payor_name,
                    'payment_date' => $paymentDate->toDateString(),
                    'store_code' => auth()->user()->store_code,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);
            }

            $newBalance = round(max(0, $balanceDue - $totalPayment), 2);
            DB::table('transactions')
                ->where('id', $transaction->id)
                ->update([
                    'amount' => DB::raw('COALESCE(amount, 0) + ' . number_format($totalPayment, 2, '.', '')),
                    'status' => $newBalance <= 0 ? 'PAID' : 'UNPAID',
                    'mode_of_payment' => 'AR',
                    'cashier' => auth()->id(),
                    'updated_at' => $paymentDate,
                ]);
            DB::table('tables')
                ->where('id', $transaction->table)
                ->where('store_code', auth()->user()->store_code)
                ->update([
                    'credit_limit' => DB::raw('COALESCE(credit_limit, 0) + ' . number_format($totalPayment, 2, '.', '')),
                ]);

            return response()->json([
                'msg' => 'success',
                'balance' => $newBalance,
                'paid' => $totalPayment,
            ]);
        });
    }

    public function dp(Request $request){

        $slug = $request->slug;
        $dpAmount = $request->dpAmount;
        $mop_val = $request->mop_val;
        $branch = $request->branch;

        $transaction = DB::table('transactions')->where('slug', $slug)->first();

        DB::table('payment_dps')->insert([
            'tran_id' => $transaction->id,
            'mop' => $mop_val,
            'amount' => $dpAmount,
            'branch' => $branch,
            'cashier' => auth()->id(),
            'created_at' => date('Y-m-d h:i:s'),
            'updated_at' => date('Y-m-d h:i:s')
        ]);

        $response = array(
            'dp' => $mop_val,
            'mop' => $dpAmount,
            'tranid' => $transaction->id,
        );
        echo json_encode($response);
    }

    public function splitcheck(Request $request){

        $slug = $request->slug;
        $checkbox = $request->checkbox;

        if ($checkbox == 'true'){
            DB::table('ordered')->where('slug', $slug)->update([
                'is_split' => 1,
            ]);
        } else {
            DB::table('ordered')->where('slug', $slug)->update([
                'is_split' => 0,
            ]);
        }
    }

    public function split(Request $request){
        $tranID = $request->tranID;
        $NewTableId = $request->NewTableId;

        $transaction = DB::table('transactions')->where('id', $tranID)->first();

        $checkTransItem = DB::table('ordered')->where('tran_id', $tranID)->get();
        $checkTransItemCount =  $checkTransItem->count();
        $forSplit = DB::table('ordered')->where('tran_id', $tranID)->where('is_split', 1)->get();
        $forSplitCount = $forSplit->count();

        if ($checkTransItemCount  == $forSplitCount){

            DB::table('transactions')->where('id', $tranID)->update(['table' => $NewTableId]);
            DB::table('tables')->where('id', $transaction->table)->update(['status' => '0']);
            DB::table('tables')->where('id', $NewTableId)->update(['status' => '1']);

            DB::table('ordered')->where('tran_id', $tranID)->where('is_split', 1)->update(['is_split' => 0]);

            $msg = 'success';
        } else {
            if ($forSplitCount > 0) {

                $id = Transaction::latest()->pluck('id')->first();
                //$newid  = DB::table('transactions')->latest('created_at')->first();

                if ($id == null) {
                    $id = 1;
                } else {
                    $id++;
                }
                $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

                $number = date('mdY') . '-' . $nid;

                $tranSlug = Str::random(60);
                $ntranSlug = $tranSlug;
                $check_slug = DB::table('transactions')->where('slug', $ntranSlug)->get();
                while (count($check_slug) > 0) {
                    $ntranSlug = Str::random(60);
                    $check_slug = DB::table('transactions')->where('slug', $ntranSlug)->get();
                }
                $tranSlug = $ntranSlug;

                $tran = new Transaction();
                $tran->number = $number;
                $tran->total = 0;
                $tran->payor_name = ucfirst($transaction->payor_name);
                $tran->payor_number = $transaction->payor_number;
                $tran->type = $transaction->type;
                $tran->service_charge =$transaction->service_charge;
                $tran->order_remarks = $transaction->order_remarks;
                $tran->table = $NewTableId;
                $tran->status = 'UNPAID';
                $tran->order_status = 'PREPARING';
                $tran->cashier = auth()->id();
                $tran->slug = $tranSlug;
                $tran->customer_with_discount = $transaction->customer_with_discount;
                $tran->total_customer = $transaction->total_customer;
                $tran->pwd = $transaction->pwd;
                $tran->service_charge = $transaction->service_charge;
                $tran->sc_percent = $transaction->sc_percent;
                $tran->lime_discount = $transaction->lime_discount;
                $tran->ld_value = $transaction->ld_value;
    
                $tran->lessVatDeduction = $transaction->lessVatDeduction;
                $tran->vatExemptSales = $transaction->vatExemptSales;
                $tran->vatSales = $transaction->vatSales;
                $tran->vat = $transaction->vat;
                $tran->save();

                $tran_id = $tran->id;

                DB::table('ordered')->where('tran_id', $tranID)->where('is_split', 1)->update([
                    'tran_id' => $tran_id,
                    'is_split' => 0,
                ]);
                
                DB::table('tables')->where('id', $NewTableId)->update(['status' => '1']);

                $msg = 'success';
            } else {
                $msg = 'error';
            }
        }
        /*  */
        //$msg = 'error';
        /* DB::table('transactions')->where('id', $tranID)->update(['table' => $NewTableId]);
        DB::table('tables')->where('id', $NewTableId)->update(['status' => '1']); */

        $response = array(
            'msg' => $msg,
            'tranID' => $tranID,
            'NewTable' => $NewTableId,
            'count' => $forSplitCount,
        );

        echo json_encode($response);
    }

    public function splitTable(Request $request){
        $id = $request->id;
        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->first();
        $res  = '';
        $t = '';


        $tables = DB::table('tables')->where('status', 0)->where('is_deleted', 0)->orderBy('name', 'ASC')->get();
            
        foreach($tables as $table){
            $t .= '<option value="'. $table->id .'">'.  $table->name .'</option>';
        }
        $res .= '
                        <p class="text-base leading-relaxed text-gray-500">
                            Select Table
                        </p>
                        <select data-id="'.$trans->id.'" name="tableOptionSplit" id="tableOptionSplit" class=" block w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        
                            '.$t.'
                        </select>
        ';

        $response = array(
            'tables' => $res,
        );

        echo json_encode($response);
    }
    
    public function editPax(Request $request)
    {
        // Retrieve table ID and total customer count from the request
        $id = $request->tranID;
        $edit_pax = $request->edit_pax;
    
        DB::table('transactions')->where('id', $id)->update(['total_customer' => $edit_pax]);
        
    
        // Prepare a response message
        $response = [
            'status' => 'success',
            
            'total_customer' => $edit_pax
        ];
    
        // Return a JSON response
        echo json_encode($response);
    }
}
