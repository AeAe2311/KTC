<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Ramsey\Uuid\v1;

class OrderedController extends Controller
{
    public function index(){
        $tables = DB::table('tables')->orderBy('id', 'asc')->where('id', '!=', '0')->get();

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

        return view('user.cashier.orders', compact('tables','audited','nonAudited','SCRow','discountRow','limeDiscountRow'));
    }

    public function getMenu(Request $request){
        $id = $request->id;
        $trans = DB::table('transactions'
        )->where('table', $id)
        ->where('order_status', '!=', 'CANCELLED')
        ->where('order_status', '!=', 'COMPLETED')
        ->get();
        $res = $res1 = '';
        $co = '0';
        $pd = '1';
        $disc = '';
        $transStatus = '';
        if($trans->count() < 1){
            $co = '1';
        }

        foreach($trans as $tran){
            if($tran->status == 'PAID'){
                $sColor = 'rgb(16 185 129)';
            }else{
                $sColor = 'rgb(240 82 82)';
                $co = 0;
                $pd = 0;
            }

            $m = '';
            $orders = DB::table('ordered')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.is_pwd', 'ordered.type')
                ->where('ordered.tran_id', $tran->id)
                ->get();
            $subTotal = $subTotalWDisc = 0;
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
            foreach($orders as $order){
                if ($order->type == 0){
                    $checkStatus = '';
                } else {
                    $checkStatus = 'checked';
                }

                if ($order->is_pwd == 0){
                    $is_pwdCheck = '';
                } else {
                    $is_pwdCheck = 'checked';
                }
                if($order->quantity > 1){
                    
                    if($order->status != 'SERVED'){

                        $m .= '
                            <div class="grid grid-cols-7 gap-1  mb-5">
                            <div class="max-w-[50px]">';
                        if ($tran->status != 'PAID'){
                        $m .= '
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary max-w-xs " '.$checkStatus .'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="orderSelection max-w-xs " id="orderSelection'. $order->slug .'" '. $is_pwdCheck.'>
                                ';
                        }
                        $m .= '</div>
                                <div class="col-span-3">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden text-left">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                                <div class="justify-self-center">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">₱ '.$order->amount.'.00</h1>
                                </div>
                                <div style="margin-right: 20px;" class="justify-self-end col-span-2">
                                    <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="reduceButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50 mr-4"><i class="uil uil-minus text-xl text-red-500 hover:text-red-600 "></i></button>
                                    <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="removeButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50"><i class="uil uil-multiply text-xl text-red-500 hover:text-red-600 "></i></button>
                                </div>
                            </div>
                            
                        ';
                        //$co = 1;
                    }else{
                        $m .= '
                            <div class="grid grid-cols-7 mb-5 items-center">
                            <div class="max-w-[50px]">';
                        if ($tran->status != 'PAID'){
                            $m .= '
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary max-w-xs " '.$checkStatus .'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="orderSelection max-w-xs " id="orderSelection'. $order->slug .'" '. $is_pwdCheck.'>
                                ';
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
                        $co = 1;
                    }
                }else{
                    if($order->status != 'SERVED'){
                        $m .= '
                            <div class="grid grid-cols-7 gap-1 mb-5 items-center">
                            <div class="max-w-[50px]">';
                        if ($tran->status != 'PAID'){
                            $m .= '
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary max-w-xs " '.$checkStatus .'>
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="orderSelection max-w-xs " id="orderSelection'. $order->slug .'" '. $is_pwdCheck.'>
                                ';
                        }
                        $m .= '</div>
                                <div class="col-span-3">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                                <div class="justify-self-center">
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">₱ '.$order->amount.'.00</h1>
                                </div>
                                <div style="margin-right: 20px;" class="justify-self-end col-span-2">
                                    <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="removeButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50"><i class="uil uil-multiply text-xl text-red-500 hover:text-red-600 "></i></button>
                                </div>
                            </div>
                        ';
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
                        $co = 1;
                    }
                }
                

                if ($order->type != 1){
                    if ($order->is_pwd == 1){
                        $subTotalWDisc += $order->amount;
                    } else {
                        $subTotal +=  $order->amount;
                    }
                    
                }
                    
            }
            
            $subTotal = $subTotal + $subTotalWDisc;
            //echo json_encode($tran->status);
            if ($tran->status != 'PAID'){
                //subtotal
                $disc .= '
                <div class="grid grid-cols-7 items-center">
                    <div class="col-span-2">
                        
                    </div>
                    
                    <div style="margin-right: 20px;" class="text-left col-span-3">
                            <strong >Subtotal</strong>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            <span id="orderedSubtotal"> ₱ '.number_format($subTotal,2).'</span>
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
                            <button id="DiscountButton" type="button" data-table="'.$id.'" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                            <button  id="deleteDiscountButton" type="button" data-table="'.$id.'" class="text-red-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M261-120q-24.75 0-42.375-17.625T201-180v-570h-41v-60h188v-30h264v30h188v60h-41v570q0 24-18 42t-42 18H261Zm438-630H261v570h438v-570ZM367-266h60v-399h-60v399Zm166 0h60v-399h-60v399ZM261-750v570-570Z"/></svg></span></button>
                        </div>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            <span id="orderedPwd">₱ '.number_format($tran->pwd,2).'</span>
                    </div>
                </div>';
                $pwds = DB::table('seniors')
                ->select('id','name', 'id_no')
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
                                    <th class="w-1/2 text-left">Name</th>
                                    <th> </th>
                                    <th class="w-1/2 text-left">ID #.</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                ';
                    foreach($pwds as $pwd){
                        $disc .= '
                                <tr class="text-gray-400 whitespace-nowrap">   
                                    <td class="w-1/2">'.$pwd->name.'</td>
                                    <td> </td>
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
                            <strong >Discount</strong> ['.$tran->lime_discount.' %]
                            <button id="LimeDiscountButton" type="button" data-table="'.$id.'" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                        </div>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            <span id="orderedOwnerDisc">₱ '.number_format($tran->ld_value,2).'</span>
                    </div>
                </div>';

                //SC section
                $disc .= '
                <div class="grid grid-cols-7 items-center ">
                    <div class="col-span-2">
                        
                    </div>
                    <div style="margin-right: 20px;" class="justify-self col-span-3">
                        <div class="justify-self-start flex items-center">
                            <strong >Service Charge</strong> ['.$tran->sc_percent.' %]
                            <button id="scBtn" type="button" data-table="'.$id.'" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                        </div>
                    </div>
                    <div class="col-span-2 text-right mr-4">
                            <span id="orderedSc">₱ '.number_format($tran->service_charge,2).'</span>
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
                            <span id="orderedTotal">₱ '.number_format($tran->total,2).'</span>
                    </div>
                </div>';
                $co = '0';
            } else {
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
                    <span style="border-color: rgb(209 213 219 / var(--tw-border-opacity)); margin-left: 20px;" class="relative text-gray-500 font-bold text-xl border px-4 py-2 w-auto bg-white z-50 rounded-lg">'.$tran->number.' - <span style="color: '.$sColor.';"> '.$tran->status.'</span>
                    <span><button data-slug="'.$tran->slug.'" class="btnTransfer bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded"><i class="uil uil-arrow-to-right text-xl mr-1"></i></button>
                    </span></span>
                    <div style="top: -13px; border-color: rgb(209 213 219 / var(--tw-border-opacity));" class="relative border border-gray-500 z-10 w-full pt-6 pl-2 text-xl font-semibold text-gray-500 tracking-wide rounded-lg"><span class="col-span-8 text-xs flex">
                                <span class="text-lg mr-2"><i class="uil uil-gift"></i></span>
                                
                                <span class="text-lg"><i class="uil uil-percentage"></i></span>
                                
                            </span>'.$m.'<span style="color: rgb(240 82 82);">Remarks : </span>'.
                    $OrderRemarks.'</span>'.$disc.'</div>
                </div>
            ';
           $transStatus =  $tran->status;
        }

        $response = array(
            'allOrders' => $res,
            'co' => $co,
            'pd' => $pd,
            'disc' => $disc,
            'payment' =>$transStatus ,
        );

        echo json_encode($response);
    }
    public function OrderupdateDiscount(Request $request) {
        $id = $request->tableId;
        $customer_with_discount = $request->cust_with_discount;
        //$total_customer = $request->totalcus;
        $service_charge = $request->service_charge;
        $lime_discount = $request->lime_discount;

        DB::table('discounts')->where('user_id', auth()->id())->update([
            'customer_with_discount' => $customer_with_discount,
        ]);

        DB::table('service_charge')->where('user_id', auth()->id())->update([
            'percent' => $service_charge,
        ]);

        DB::table('lime_discount')->where('user_id', auth()->id())->update([
            'percent' => $lime_discount,
        ]);

        $subTotal = $subTotalWDisc = 0;
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

        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $SCPercent = $SCRow->percent;
        

        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('status', '!=', 'PAID')->get();
        

        foreach($trans as $tran){
            
            $orders = DB::table('ordered')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status', 'ordered.type', 'ordered.is_pwd')
                ->where('ordered.tran_id', $tran->id)
                ->get();

            foreach($orders as $order){
                $qty = $order->quantity;
                $amount = $order->amount;
                //$price = $qty * $amount;
                if ($order->type != 1){
                    if ($order->is_pwd == 1){
                        $subTotalWDisc += $order->amount;
                        $discountPerItem = 0.2 * round($amount,2);
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
            $SCPercent = $SCRow->percent;
            $limePercent = $limeDiscountRow->percent;
            //$subTotal = $subTotal +$sc;
            $limeVal = ($limePercent / 100) * $subTotal;
            $net = ($subTotal <= 0 ) ? 0 : round(($subTotal- $limeVal)/1.12,2); // 500
            $netWithDiscount = ($subTotalWDisc <= 0 ) ? 0 : round(($subTotalWDisc- $limeVal)/1.12,2); // 500
            
            if ($discountRow->customer_with_discount > 0) {
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
            
            $total = round(($subTotal+ $subTotalWDisc + $SCVal -($LessVatDeduction+$discount+$limeVal)),2);
            $subTotal = $subTotal + $subTotalWDisc;

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

        $subTotal = 0;
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
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status')
                ->where('ordered.tran_id', $tran->id)
                ->get();

            foreach($orders as $order){
                $qty = $order->quantity;
                $amount = $order->amount;
                //$price = $qty * $amount;
                $subTotal = $subTotal + $amount;
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
            $SCPercent = $SCRow->percent;
            $limePercent = $limeDiscountRow->percent;
            //$subTotal = $subTotal +$sc;
            $limeVal = ($limePercent / 100) * $subTotal;
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
            
            $total = round(($subTotal + $SCVal -($LessVatDeduction+$discount+$limeVal)),2);

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
        /* $id = $request->id;

        $qty = $amount = $subtotal = $price = $total = $SCPercent = $SCVal = $SCValperItem = $discount = $discountPerItem = 0;
        // discount section
        //$discountRow = DB::table('discounts')->where('id', 1)->first();
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;
        // $trans = DB::table('transactions')
        //     ->where('table', $id)
        //     ->where('status', 'UNPAID')
        //     ->where('order_status', '!=', 'CANCELLED')
        //     ->where('order_status', '!=', 'COMPLETED')
        //     ->first();

        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->where('status', '!=', 'PAID')->get();
        // DB::table('discounts')->where('id', 1)->update([
        //     'customer_with_discount' => 0,
        //     'total_customer' => 0,
        // ]);
        foreach($trans as $tran){
            
            $orders = DB::table('ordered')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.amount', 'ordered.slug', 'ordered.status')
                ->where('ordered.tran_id', $tran->id)
                ->get();

            foreach($orders as $order){
                $qty = $order->quantity;
                $amount = $order->amount;
                //$price = $qty * $amount;
                $subtotal = $subtotal + $amount;
                //compute discount per item
                
                //compute service charge per item
                $SCValperItem = ($SCPercent / 100) * $amount;
                DB::table('ordered')->where('slug', $order->slug)->update([
                    'discount_amount' => $discountPerItem,
                    'service_charge' =>  $SCValperItem,
                ]);
                //mackey version
                
                // DB::table('discounts')->where('id', 1)->update([
                //     'customer_with_discount' => 0,
                //     'total_customer' => 0,
                // ]);

            }
            
            
            $total = round(($subtotal),2);
            $SCVal = ($SCPercent / 100) * $subtotal;
            $total = $total + $SCVal;
            DB::table('transactions')->where('id',$tran->id)->update([
                'total' => $total,
                'service_charge' =>  $SCVal,
            ]);
        } */
        // return redirect()->route('orders.index');
        //echo $id;
    }
    public function reduce(Request $request){
        $slug = $request->slug;

        DB::table('ordered')->where('slug', $slug)->decrement('quantity', 1);

        $menu_id = (DB::table('ordered')->where('slug', $slug)->first())->menu_id;

        $menu = DB::table('menus')->where('id', $menu_id)->first();
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
        /* DB::table('menus')->where('id', $menu_id)->increment('current_quantity', 1);
        DB::table('menus')->where('id', $menu_id)->increment('quantity', 1); */

        $id = $request->table;
        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->get();
        $res = '';
        $co = '0';

        foreach($trans as $tran){
            $m = '';
            $orders = DB::table('ordered')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.slug', 'ordered.status')
                ->where('ordered.tran_id', $tran->id)
                ->get();

            foreach($orders as $order){
                if($order->quantity > 1){
                    if($order->status != 'SERVED'){
                        $m .= '
                            <div class="flex justify-between items-center mb-5">
                                <div>
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                                <div style="margin-right: 20px;" class="">
                                    <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="reduceButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50 mr-4"><i class="uil uil-minus text-xl text-red-500 hover:text-red-600 mr-2"></i>Reduce</button>
                                    <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="removeButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50"><i class="uil uil-multiply text-xl text-red-500 hover:text-red-600 mr-2"></i>Remove</button>
                                </div>
                            </div>
                        ';
                    }else{
                        $m .= '
                            <div class="flex justify-between items-center mb-5">
                                <div>
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                            </div>
                        ';
                        $co = 1;
                    }
                }else{
                    if($order->status != 'SERVED'){
                        $m .= '
                            <div class="flex justify-between items-center mb-5">
                                <div>
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                                <div style="margin-right: 20px;" class="">
                                    <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="removeButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50"><i class="uil uil-multiply text-xl text-red-500 hover:text-red-600 mr-2"></i>Remove</button>
                                </div>
                            </div>
                        ';
                    }else{
                        $m .= '
                            <div class="flex justify-between items-center mb-5">
                                <div>
                                    <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                </div>
                            </div>
                        ';
                        $co = 1;
                    }
                }
            }

            if($tran->status == 'PAID'){
                $sColor = 'rgb(16 185 129)';
            }else{
                $sColor = 'rgb(240 82 82)';
            }

            $res .= '
                <div class="relative h-auto mb-5">
                    <span style="border-color: rgb(209 213 219 / var(--tw-border-opacity)); margin-left: 20px;" class="relative text-gray-500 font-bold text-xl border px-4 py-2 w-auto bg-white z-50 rounded-lg">'.$tran->number.' - <span style="color: '.$sColor.';"> '.$tran->status.'</span></span>
                    <div style="top: -13px; border-color: rgb(209 213 219 / var(--tw-border-opacity));" class="relative border border-gray-500 z-10 w-full pt-6 pl-2 text-xl font-semibold text-gray-500 tracking-wide rounded-lg">'.$m.'</div>
                </div>
            ';
        }

        $response = array(
            'allOrders' => $res,
            'co' => $co,
        );

        echo json_encode($response);
    }

    public function remove(Request $request){
        $slug = $request->slug;

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


        

        //edited mackey 1-13-24
        $orderedAmount = $ordered->amount;
        $orderDiscount = $ordered->discount_amount;
        $orderSC = $ordered->service_charge;
        $subtotalAmount = $orderedAmount - $orderDiscount;
        $tid = $ordered->tran_id;
        $transcancel = DB::table('transactions')->where('id', $tid)->first();

        //$SCRow = DB::table('service_charge')->where('id', 1)->first();
        $total_amount = $transcancel->total;
        $totalSC = $transcancel->service_charge;
        // $SCPercent = $SCRow->percent;
        // //new sc value
        // $SCVal = ($SCPercent / 100) * $orderedAmount;

        //set new total SC
        $NewSC = $totalSC - $orderSC;

        $NewAmount = $total_amount - ($subtotalAmount + $orderSC);

        DB::table('transactions')->where('id', $transcancel->id )->update(['total' => $NewAmount, 'service_charge'  => $NewSC]);
        


        DB::table('ordered')->where('slug', $slug)->delete();
        $id = $request->table;
        
        $trans = DB::table('transactions')->where('table', $id)->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->get();

        
        $res = '';
        $co = '0';

        foreach($trans as $tran){
            $m = '';
            $orders = DB::table('ordered')
                ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                ->select('ordered.tran_id', 'ordered.menu_id', 'menus.name', 'ordered.quantity', 'ordered.slug', 'ordered.status')
                ->where('ordered.tran_id', $tran->id)
                ->get();

            if($orders->count() < 1){
                DB::table('transactions')->where('id', $tran->id)->update(['order_status' => 'CANCELLED']);
            }else{
                foreach($orders as $order){
                    if($order->quantity > 1){
                        if($order->status != 'SERVED'){
                            $m .= '
                                <div class="flex justify-between items-center mb-5">
                                    <div>
                                        <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                    </div>
                                    <div style="margin-right: 20px;" class="">
                                        <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="reduceButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50 mr-4"><i class="uil uil-minus text-xl text-red-500 hover:text-red-600 mr-2"></i>Reduce</button>
                                        <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="removeButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50"><i class="uil uil-multiply text-xl text-red-500 hover:text-red-600 mr-2"></i>Remove</button>
                                    </div>
                                </div>
                            ';
                        }else{
                            $m .= '
                                <div class="flex justify-between items-center mb-5">
                                    <div>
                                        <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                    </div>
                                </div>
                            ';
                            $co = 1;
                        }
                    }else{
                        if($order->status != 'SERVED'){
                            $m .= '
                                <div class="flex justify-between items-center mb-5">
                                    <div>
                                        <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                    </div>
                                    <div style="margin-right: 20px;" class="">
                                        <button data-slug="'.$order->slug.'" data-table="'.$id.'" class="removeButton border border-gray-300 shadow py-2 px-4 rounded-lg hover:bg-gray-50"><i class="uil uil-multiply text-xl text-red-500 hover:text-red-600 mr-2"></i>Remove</button>
                                    </div>
                                </div>
                            ';
                        }else{
                            $m .= '
                                <div class="flex justify-between items-center mb-5">
                                    <div>
                                        <h1 class="whitespace-nowrap max-w-xs overflow-hidden">&nbsp;&nbsp;'.$order->quantity.'x&nbsp;&nbsp;&nbsp;<span>'.$order->name.'</span>&nbsp;&nbsp;</h1>
                                    </div>
                                </div>
                            ';
                            $co = 1;
                        }
                    }
                }
    
                if($tran->status == 'PAID'){
                    $sColor = 'rgb(16 185 129)';
                }else{
                    $sColor = 'rgb(240 82 82)';
                }
    
                $res .= '
                    <div class="relative h-auto mb-5">
                        <span style="border-color: rgb(209 213 219 / var(--tw-border-opacity)); margin-left: 20px;" class="relative text-gray-500 font-bold text-xl border px-4 py-2 w-auto bg-white z-50 rounded-lg">'.$tran->number.' - <span style="color: '.$sColor.';"> '.$tran->status.'</span></span>
                        <div style="top: -13px; border-color: rgb(209 213 219 / var(--tw-border-opacity));" class="relative border border-gray-500 z-10 w-full pt-6 pl-2 text-xl font-semibold text-gray-500 tracking-wide rounded-lg">'.$m.'</div>
                    </div>
                ';
            }
        }

        $response = array(
            'allOrders' => $res,
            'co' => $co,
        );

        echo json_encode($response);
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
            ->where('status', 'UNPAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('order_status', '!=', 'COMPLETED')
            ->first();

        echo $trans->total;
    }

    public function paid(Request $request){
        $id = $request->id;
        $mop = $request->mop;
        $amountInput = $request->amountInput;


        DB::table('transactions')
            ->where('table', $id)
            ->where('status', 'UNPAID')
            ->update([
                'mode_of_payment' => $mop,
                'amount' => $amountInput,
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
    }

    public function print($id){
        $trans = DB::table('transactions')
                    ->select('transactions.*', 'tables.name as table_name')
                    ->join('tables', 'transactions.table', '=', 'tables.id')
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


        $settings = DB::table('settings')->where('id', 1)->first();


        return view('user.cashier.bill', compact('orders', 'trans', 'settings'));
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

    public function addpwd(Request $request){
        $pwdName = strtoupper($request->pwdName);
        $pwdIdNumber = strtoupper($request->pwdIdNumber);
        $slug = strtoupper($request->slug);
        

        DB::table('seniors')->insert([
            'tran_id' => $slug,
            'name' => $pwdName,
            'id_no' => $pwdIdNumber
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


        $tables = DB::table('tables')->where('status', 0)->where('is_deleted', 0)->get();
            
        foreach($tables as $table){
            $t .= '<option value="'. $table->id .'">'.  $table->name .'</option>';
        }
        $res .= '
                        <p class="text-base leading-relaxed text-gray-500">
                            Select Table
                        </p>
                        <select name="tableOption" id="tableOption" data-id="'.$trans->id.'" class="block w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        
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
}
