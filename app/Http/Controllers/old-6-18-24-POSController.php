<?php

namespace App\Http\Controllers;

use App\Mail\MailNotify;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Ordered;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

 
class POSController extends Controller {
    
    public function index() {

        $this->updateCurrentQuantity();
        $tables = DB::table('tables')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $menus = DB::table('menus')
        ->where('current_quantity', '>=', 0)
        ->whereIn('department',[1,2,3,4,5])
        ->orderBy('name', 'asc')->get();

        $categories = DB::table('menu_categories')->where('is_hidden', 0)->where('is_deleted', 0)->orderBy('name', 'asc')->get();
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
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        $discount = $SCPercent = $SCVal = $limePercent = $limeVal = 0;
        
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->type != 1){
                    $subTotal = $subTotal + $order->total_price;
                }
                
            }
            
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
        }
        /* $response = array(
            'sub' => $SubtotalLessDiscount,
            'vat' => $vat,
            'vatsales' => $vatsales,
            'discount' => number_format($discount, 2, '.', ','),
            'VatExemptSales' => number_format($VatExemptSales, 2, '.', ','),
            'LessVatDeduction' => $LessVatDeduction
        );

        echo json_encode($response); */


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

        return view('user.cashier.pos', compact('tables', 'orders', 'discountRow', 'menus', 'categories', 'subTotal', 'total', 'discount', 'SCRow', 'SCPercent','SCVal','limeDiscountRow', 'limePercent', 'limeVal','LessVatDeduction','VatExemptSales','vatsales','vat','audited','nonAudited'));
    }

    public function add(Request $request) {
        $this->updateCurrentQuantity();
        $slug = $request->slug;
        $menu = DB::table('menus')->where('slug', $slug)->first();
        $ord = DB::table('orders')->where('menu_id', $menu->id)->where('cashier', auth()->id())->first();
        $notif = '';
        //sc part
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;

        //lime discount part
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        $limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;


        $orderSlug = Str::random(60);
        $norderSlug = $orderSlug;
        $check_slug = DB::table('inventories')->where('slug', $norderSlug)->get();
        while (count($check_slug) > 0) {
            $norderSlug = Str::random(60);
            $check_slug = DB::table('inventories')->where('slug', $norderSlug)->get();
        }
        $orderSlug = $norderSlug;

        if ($menu->current_quantity >= 0) {
            if ($ord != null) {
                if ($menu->current_quantity > 0) {
                    $quantity = $ord->quantity + 1;
                    $total_price = $menu->price * $quantity;
                    $current_quantity = $menu->current_quantity - 1;
                    DB::table('orders')->where('id', $ord->id)->delete();

                    $order = new Order();
                    $order->menu_id = $menu->id;
                    $order->name = $menu->name;
                    $order->department = $menu->department;
                    $order->quantity = $quantity;
                    $order->price = $menu->price;
                    $order->price = $menu->price;
                    $order->total_price = $total_price;
                    $order->current_stock = $current_quantity;
                    $order->cashier = auth()->id();
                    $order->slug = $orderSlug;
                    $order->save();

                    /* if ($menu->is_combo == 1) {
                        $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                        foreach ($ings as $ing) {
                            $decq = 1 * $ing->computed_quantity;
                            $curq = Menu::where('id', $ing->inventory_id)->first()->current_quantity;
                            $newq = $curq - $decq;

                            $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                            $updateMenu->current_quantity = $newq;
                            $updateMenu->save();
                        }
                    } else {
                        DB::table('menus')->where('id', $menu->id)->update([
                            'current_quantity' => $current_quantity,
                        ]);
                    } */
                } else {
                    $notif .= '
                    <div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">
                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            <span class="sr-only">Error icon</span>
                        </div>
                        <div class="pr-10 ml-3 text-base font-medium">This menu is already out of stock.</div>
                        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-danger" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>';
                }
            } else {
                $quantity = 1;
                $total_price = $menu->price * $quantity;
                $current_quantity = $menu->current_quantity - 1;

                $order = new Order();
                $order->menu_id = $menu->id;
                $order->department = $menu->department;
                $order->name = $menu->name;
                $order->quantity = $quantity;
                $order->price = $menu->price;
                $order->total_price = $total_price;
                $order->current_stock = $current_quantity;
                $order->cashier = auth()->id();
                $order->slug = $orderSlug;
                $order->save();

                /* if ($menu->is_combo == 1) {
                    $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                    foreach ($ings as $ing) {
                        $decq = $order->quantity * $ing->computed_quantity;
                        $curq = Menu::where('id', $ing->inventory_id)->first()->current_quantity;
                        $newq = $curq - $decq;

                        $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                        $updateMenu->current_quantity = $newq;
                        $updateMenu->save();
                    }
                } else {
                    DB::table('menus')->where('id', $menu->id)->update([
                        'current_quantity' => $current_quantity,
                    ]);
                } */
            }
        } else {
            $notif = '
            <div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    <span class="sr-only">Error icon</span>
                </div>
                <div class="pr-10 ml-3 text-base font-medium">This menu is already out of stock.</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-danger" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>';
        }

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $discount = 0;
        $vatsales = 0;
        $vat = 0;
        $net = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->type == 0){
                    $checkStatus = '';
                } else {
                    $checkStatus = 'checked';
                }
                $orderResult .= '
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        <div class="col-span-1 flex items-center ">
                                            <input type="checkbox" data-slug="' . $order->slug . '" id="' . $order->slug . '" class="complimentary" '.$checkStatus.'>
                                        </div>
                                    <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-1">
                                            <p class="w-full text-sm font-semibold leading-7 text-center border-0 h-7">' . $order->quantity . '</p>
                                            <input type="hidden" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            ' . number_format($order->total_price, 2, ".", ",") . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
                if ($order->type != 1){
                    $subTotal = $subTotal + $order->total_price;
                }
                //$subTotal = $subTotal + $order->total_price;
            }
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

        }

        $amount = $total;
        $this->updateCurrentQuantity();

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'thisNotif' => $notif,
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'ServiceCharge' => number_format($SCVal, 2, '.', ','),
            'scPercent' => $SCPercent,
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => number_format($limeVal, 2, '.', ',') ,
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'ordersCount' => $orders->count()
        );
        echo json_encode($response);
    }


    public function addqty(Request $request){
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $qty = (float)$request->qty;
        $corder = DB::table('orders')->where('slug', $slug)->first();

        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;
        $notif = '';
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        
        if ($qty <= $menu->quantity) {
            $quantity = $qty;
            $total_price = $corder->price * $quantity;
            $current_stock = $menu->quantity - $qty;
            DB::table('orders')->where('slug', $slug)->update([
                'quantity' => (float)$quantity,
                'total_price' => $total_price,
                
            ]);
            //'current_stock' => (float)$current_stock,
            /* if ($menu->is_combo == 1) {
                $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                foreach ($ings as $ing) {
                    $decq = 1 * $ing->computed_quantity;
                    $curq = Menu::where('id', $ing->inventory_id)->first()->current_quantity;
                    $newq = $curq - $decq;

                    $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                    $updateMenu->current_quantity = $newq;
                    $updateMenu->save();
                }
            } else {
                DB::table('menus')->where('id', $corder->menu_id)->update([
                    'current_quantity' => $current_stock,
                ]);
            } */
        } else {
            $notif = '<div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">
                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            <span class="sr-only">Error icon</span>
                        </div>
                        <div class="pr-10 ml-3 text-base font-medium">This menu is already out of stock.</div>
                        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-danger" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>';
        }

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $discount = 0;
        $vatsales = 0;
        $vat = 0;
        $net = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                $orderResult .= '
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-3">
                            
                                            <input type="text" data-slug="'.$order->slug.'" id="orderqty"  class="w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-1 text-sm font-semibold">
                                            
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
                $subTotal = $subTotal + $order->total_price;
            }
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

        }

        $amount = $total;

        //$this->updateCurrentQuantity();

        $response = array(
            'thisNotif' => $notif,
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'limediscount' => number_format($limeVal, 2, '.', ','),
            'ServiceCharge' => number_format($SCVal, 2, '.', ','),
            'ordersCount' => $orders->count(),
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'qty' => $qty
        );

        echo json_encode($response);
    }
    
    public function inc(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $corder = DB::table('orders')->where('slug', $slug)->first();

        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
        //service charge section
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;
        //lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        $limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;

        $notif = '';
        
        if ($menu->current_quantity > 0) {
            $quantity = $corder->quantity + 1;
            $total_price = $corder->price * $quantity;
            $current_stock = $menu->current_quantity - 1;
            DB::table('orders')->where('slug', $slug)->update([
                'quantity' => $quantity,
                'total_price' => $total_price,
                
            ]);
            //'current_stock' => $current_stock,
            /* if ($menu->is_combo == 1) {
                $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                foreach ($ings as $ing) {
                    $decq = 1 * $ing->computed_quantity;
                    $curq = Menu::where('id', $ing->inventory_id)->first()->current_quantity;
                    $newq = $curq - $decq;

                    $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                    $updateMenu->current_quantity = $newq;
                    $updateMenu->save();
                }
            } else {
                DB::table('menus')->where('id', $corder->menu_id)->update([
                    'current_quantity' => $current_stock,
                ]);
            } */
        } else {
            $notif = '<div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">
                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            <span class="sr-only">Error icon</span>
                        </div>
                        <div class="pr-10 ml-3 text-base font-medium">This menu is already out of stock.</div>
                        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-danger" aria-label="Close">
                            <span class="sr-only">Close</span>
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>';
        }

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $vatsales = 0;
        $vat = 0;
        $net = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $discount = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->type == 0){
                    $checkStatus = '';
                } else {
                    $checkStatus = 'checked';
                }
                $orderResult .= '
                                    
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        <div class="col-span-1 flex items-center ">
                                            <input type="checkbox" data-slug="' . $order->slug . '" id="' . $order->slug . '" class="complimentary" '.$checkStatus.'>
                                        </div>
                                        <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-1">
                                            <p class="w-full text-sm font-semibold leading-7 text-center border-0 h-7">' . $order->quantity . '</p>
                                            <input type="hidden" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            ' . number_format($order->total_price, 2, ".", ",") . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
                if ($order->type != 1){
                    $subTotal = $subTotal + $order->total_price;
                }
                //$subTotal = $subTotal + $order->total_price;
            }
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

        }

        $amount = $total;

        //$this->updateCurrentQuantity();

        $response = array(
            'thisNotif' => $notif,
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'ServiceCharge' => number_format($SCVal, 2, '.', ','),
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => number_format($limeVal, 2, '.', ',') ,
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'ordersCount' => $orders->count()
        );

        echo json_encode($response);
    }

    public function desc(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $corder = DB::table('orders')->where('slug', $slug)->first();
        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
        //Service charge section
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;

        //Lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        $limePercent = $limeVal = 0; 
        $limePercent = $limeDiscountRow->percent;   

        if ($corder->quantity > 1) {
            $quantity = $corder->quantity - 1;
            $total_price = $corder->price * $quantity;
            $current_quantity = $corder->current_stock + 1;
            DB::table('orders')->where('slug', $slug)->update([
                'quantity' => $quantity,
                'total_price' => $total_price,
                
            ]);
            //'current_stock' => $current_quantity,
            /* if ($menu->is_combo == 1) {
                $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                foreach ($ings as $ing) {
                    $decq = 1 * $ing->computed_quantity;
                    $curq = Menu::where('id', $ing->inventory_id)->first()->current_quantity;
                    $newq = $curq + $decq;

                    $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                    $updateMenu->current_quantity = $newq;
                    $updateMenu->save();
                }
            } else {
                DB::table('menus')->where('id', $corder->menu_id)->update([
                    'current_quantity' => $current_quantity,
                ]);
            } */
        }

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $discount = 0;
        $vatsales = 0;
        $vat = 0;
        $net = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->type == 0){
                    $checkStatus = '';
                } else {
                    $checkStatus = 'checked';
                }
                $orderResult .= '
                                    
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        <div class="col-span-1 flex items-center ">
                                            <input type="checkbox" data-slug="' . $order->slug . '"  id="' . $order->slug . '" class="complimentary" '.$checkStatus.'>
                                        </div>    
                                        <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-1">
                                            <p class="w-full text-sm font-semibold leading-7 text-center border-0 h-7">' . $order->quantity . '</p>
                                            <input type="hidden" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            ' . number_format($order->total_price, 2, ".", ",") . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
                //$subTotal = $subTotal + $order->total_price;
                if ($order->type != 1){
                    $subTotal = $subTotal + $order->total_price;
                }
            }
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

        }

        $amount = $total;

        //$this->updateCurrentQuantity();

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'ServiceCharge' => number_format($SCVal, 2, '.', ','),
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => number_format($limeVal, 2, '.', ',') ,
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'ordersCount' => $orders->count()
        );

        echo json_encode($response);
    }



    public function comp(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $checkbox = $request->checkbox;
        $corder = DB::table('orders')->where('slug', $slug)->first();
        if ($checkbox == "true"){
            DB::table('orders')->where('slug', $slug)->update([
                'type' => 1,
            ]);
        } else {
            $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
            $total_price = $menu->price * $corder->quantity;
            DB::table('orders')->where('slug', $slug)->update([
                'type' => 0,
            ]);
        }
        
        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();

        //Service charge section
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;

        //Lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        $limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;
            

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $vatsales = 0;
        $vat = 0;
        $net = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $discount = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->type == 0){
                    $checkStatus = '';
                } else {
                    $checkStatus = 'checked';
                }
                $orderResult .= '
                                    
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        <div class="col-span-1 flex items-center ">
                                            <input type="checkbox" data-slug="' . $order->slug . '" id="' . $order->slug . '" class="complimentary" '.$checkStatus.'>
                                        </div>    
                                        <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-1">
                                            <p class="w-full text-sm font-semibold leading-7 text-center border-0 h-7">' . $order->quantity . '</p>
                                            <input type="hidden" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            ' . number_format($order->total_price, 2, ".", ",") . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
                //$subTotal = $subTotal + $order->total_price;
                if ($order->type != 1){
                    $subTotal = $subTotal + $order->total_price;
                }
            }
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

        }

        $amount = $total;

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'ServiceCharge' => number_format($SCVal, 2, '.', ','),
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => number_format($limeVal, 2, '.', ',') ,
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'ordersCount' => $orders->count()
        );



            echo json_encode($response);

        
    }


    public function remove(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $ord = DB::table('orders')->where('slug', $slug)->first();
        $mid = $ord->menu_id;
        $menu = DB::table('menus')->where('id', $mid)->first();
        $qty = $ord->quantity;


        //Service charge section
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;

        //Lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        $limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;

        /* if ($menu->is_combo == 1) {
            $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
            foreach ($ings as $ing) {
                $decq = $qty * $ing->computed_quantity;
                $curq = Menu::where('id', $ing->inventory_id)->first()->current_quantity;
                $newq = $curq + $decq;

                $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                $updateMenu->current_quantity = $newq;
                $updateMenu->save();
            }
        } else {
            DB::table('menus')
                ->where('id', $mid)
                ->increment('current_quantity', $qty);
        } */

        // $oqty = $menu->current_quantity;
        // $nqty = $oqty + $qty;

        // DB::table('menus')->where('id', $mid)->update([
        //     'current_quantity' => $nqty,
        // ]);

        DB::table('orders')->where('slug', $slug)->delete();

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $vatsales = 0;
        $vat = 0;
        $net = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $discount = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                $orderResult .= '
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="w-full bg-red-200 rounded-lg descQty aspect-square"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-1">
                                            <p class="w-full text-sm font-semibold leading-7 text-center border-0 h-7">' . $order->quantity . '</p>
                                            <input type="hidden" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="w-full rounded-lg incQty aspect-square bg-emerald-200"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-3 text-sm font-semibold">
                                            ' . number_format($order->total_price, 2, ".", ",") . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="w-full bg-red-600 rounded-lg removeButton aspect-square"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
                $subTotal = $subTotal + $order->total_price;
            }
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

        }

        $amount = $total;
        //$this->updateCurrentQuantity();

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'ordersCount' => $orders->count()
        );

        echo json_encode($response);
    }

    public function pay(Request $request) {
        $sc_val = $request->sc_val;
        $amount = $request->amount;
        $table = $request->table;
        $order_remarks = $request->order_remarks;
        $amountInput = $request->amountInput;
        $mop = $request->mop;
        $payor_name = $request->payor_name;
        $payor_number = $request->payor_number;
        $sc_val = $request->sc_val;
        $discountTotal = $request->discountTotal;
        // $limepercent = $request->limepercent;
        $limediscountTotal = $request->limediscountTotal;

        $LessVatDeduction = $request->LessVatDeduction;
        $VatExemptSales = $request->VatExemptSales;
        $vatsales = $request->vatsales;
        $vat = $request->vat;

        //discount section 1-13-24 by mackey
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
                
        //service charge
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;

        //lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        //$limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;

        if ($table == 6) {
            $type = 'TAKE OUT';
        } else {
            $type = 'DINE-IN';
        }

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
        $tran->total = $amount;
        
        $tran->mode_of_payment = $mop;
        $tran->amount = $amountInput;
        $tran->payor_name = ucfirst($payor_name);
        $tran->payor_number = $payor_number;
        $tran->type = $type;
        $tran->order_remarks = $order_remarks;
        $tran->table = $table;
        $tran->status = 'PAID';
        $tran->order_status = 'PREPARING';
        $tran->cashier = auth()->id();
        $tran->slug = $tranSlug;
        $tran->customer_with_discount = $discountRow->customer_with_discount;
        $tran->total_customer = $discountRow->total_customer;
        $tran->pwd = $discountTotal;
        $tran->service_charge = $sc_val;
        $tran->sc_percent = $SCPercent;
        $tran->lime_discount = $limePercent;
        $tran->ld_value = $limediscountTotal;

        $tran->lessVatDeduction = $LessVatDeduction;
        $tran->vatExemptSales = $VatExemptSales;
        $tran->vatSales = $vatsales;
        $tran->vat = $vat;

        $tran->save();

        $tran_id = $tran->id;

        


        $orders = DB::table('orders')->where('cashier', auth()->id())->get();
        foreach ($orders as $order) {
            $orderSlug = Str::random(60);
            $norderSlug = $orderSlug;
            $check_slug = DB::table('ordered')->where('slug', $norderSlug)->get();
            while (count($check_slug) > 0) {
                $norderSlug = Str::random(60);
                $check_slug = DB::table('ordered')->where('slug', $norderSlug)->get();
            }
            $vatsales = 0;
            $vat = 0;
            $net = 0;
            $orderSlug = $norderSlug;
            $subtotal = $order->total_price;
            $discount = 0;
            /* if ($discountRow->total_customer > 0) {
                $x = $subtotal / $discountRow->total_customer;
                $y = 0.2 * round($discountRow->customer_with_discount,2);
                $discount = round($x * $y,2);
            }
            $SCVal = ($SCPercent / 100) * $subtotal;
            $limeVal = ($limePercent / 100) * $subtotal; */
            
            //$subTotal = $subTotal +$sc;
            $net = round(($subtotal)/1.12,2); // 500
            if ($discountRow->total_customer > 0) {
                $x = $net / $discountRow->total_customer;
                $y = 0.2 * round($discountRow->customer_with_discount,2);
                $discount = $x * $y;
            }
            $limeVal = ($limePercent / 100) * $net;
            $vatsales = round(($net) - ($discount+$limeVal),2);
            $vat = round($vatsales * 0.12,2);
            $SCVal = ($SCPercent / 100) * $vatsales;
            $total = round(($vatsales + $SCVal + $vat),2);


            DB::table('ordered')->insert([
                'tran_id' => $tran_id,
                'menu_id' => $order->menu_id,
                'quantity' => $order->quantity,
                'department' => $order->department,
                'cashier' => $order->cashier,
                'amount' => $order->total_price,
                'discount_amount' => $discount,
                'lime_discount' => $limeVal,
                'type' => $order->type,
                'service_charge' => $SCVal,
                'status' => 'PREPARING',
                'slug' => $orderSlug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);

            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            if ($menu->is_combo == 1) {
                $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                foreach ($ings as $ing) {
                    $decq = $order->quantity * $ing->computed_quantity;
                    $curq = Menu::where('id', $ing->inventory_id)->first()->quantity;
                    $newq = $curq - $decq;

                    $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                    $updateMenu->quantity = $newq;
                    $updateMenu->save();
                }
            } else {
                DB::table('menus')
                    ->where('id', $order->menu_id)
                    ->decrement('quantity', $order->quantity);
            }

            DB::table('orders')->where('id', $order->id)->delete();
        }

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        foreach ($orders as $order) {
            $orderResult .= '
                                <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                    <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                        ' . $order->name . '
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <button data-slug="' . $order->slug . '" class="w-full bg-red-200 rounded-lg descQty aspect-square"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                    </div>
                                    <div class="flex items-center justify-center col-span-1">
                                        <p class="w-full text-sm font-semibold leading-7 text-center border-0 h-7">' . $order->quantity . '</p>
                                        <input type="hidden" value="' . $order->quantity . '">
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <button data-slug="' . $order->slug . '" class="w-full rounded-lg incQty aspect-square bg-emerald-200"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                    </div>
                                    <div class="flex items-center justify-center col-span-3 text-sm font-semibold">
                                        ' . number_format($order->total_price, 2, ".", ",") . '
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="w-full bg-red-600 rounded-lg removeButton aspect-square"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                    </div>
                                </div>
                            ';

            $subTotal = $subTotal + $order->total_price;
            $total = $total + $order->total_price;
            $amount = $total + $order->total_price;
        }

        if ($table > 1) {
            DB::table('tables')->where('id', $table)->update([
                'status' => 1
            ]);
        }

        $amount = $total;

        DB::table('discounts')->where('user_id', auth()->id())->update([
            'customer_with_discount' => 0,
            'total_customer' => 0,
        ]);

        DB::table('lime_discount')->where('id', 1)->update([
            'percent' => 0,
        ]);

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount
        );

        echo json_encode($response);
    }

    public function paylater(Request $request) {
        $amount = $request->amount;
        $table = $request->table;
        $sc_val = $request->sc_val;
        $payor_name = $request->payor_name;
        $payor_number = $request->payor_number;
        $order_remarks = $request->order_remarks;
        //$sc_val = $request->sc_val;
        $discountTotal = $request->discountTotal;
        // $limepercent = $request->limepercent;
        $limediscountTotal = $request->limediscountTotal;

        $LessVatDeduction = $request->LessVatDeduction;
        $VatExemptSales = $request->VatExemptSales;
        $vatsales = $request->vatsales;
        $vat = $request->vat;

        if ($table == 6) {
            $type = 'TAKE OUT';
        } else {
            $type = 'DINE-IN';
        }


        $this_tran = DB::table('transactions')->where('table', $table)->where('status', 'UNPAID')->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->first();
        if ($this_tran) {
            $tran_id = $this_tran->id;

            DB::table('transactions')->where('id', $tran_id)->increment('total', $amount);
            DB::table('transactions')->where('id', $tran_id)->increment('service_charge', $sc_val);
            DB::table('transactions')->where('id', $tran_id)->increment('pwd', $discountTotal);
            DB::table('transactions')->where('id', $tran_id)->increment('ld_value', $limediscountTotal);
            DB::table('transactions')->where('id', $tran_id)->update([
                'order_remarks' => $order_remarks
            ]);
        } else {

            $id = Transaction::latest()->pluck('id')->first();
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

            $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
            $SCRow = DB::table('service_charge')->where('id', 1)->first();
            $SCPercent = $SCRow->percent;
            $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
            //$limePercent = $limeVal = 0;
            $limePercent = $limeDiscountRow->percent;

            $tranSlug = $ntranSlug;

            $tran = new Transaction();
            $tran->number = $number;
            $tran->total = $amount;
            $tran->payor_name = ucfirst($payor_name);
            $tran->payor_number = $payor_number;
            $tran->type = $type;
            $tran->service_charge =$sc_val;
            $tran->order_remarks = $order_remarks;
            $tran->table = $table;
            $tran->status = 'UNPAID';
            $tran->order_status = 'PREPARING';
            $tran->cashier = auth()->id();
            $tran->slug = $tranSlug;
            $tran->customer_with_discount = $discountRow->customer_with_discount;
            $tran->total_customer = $discountRow->total_customer;
            $tran->pwd = $discountTotal;
            $tran->service_charge = $sc_val;
            $tran->sc_percent = $SCPercent;
            $tran->lime_discount = $limePercent;
            $tran->ld_value = $limediscountTotal;

            $tran->lessVatDeduction = $LessVatDeduction;
            $tran->vatExemptSales = $VatExemptSales;
            $tran->vatSales = $vatsales;
            $tran->vat = $vat;
            $tran->save();

            $tran_id = $tran->id;
        }

        //discount section 1-13-24 by mackey
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        
        //service charge
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('id', 1)->first();

        //lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('id', 1)->first();
        //$limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;

        $orders = DB::table('orders')->where('cashier', auth()->id())->get();
        foreach ($orders as $order) {
            $orderSlug = Str::random(60);
            $norderSlug = $orderSlug;
            $check_slug = DB::table('ordered')->where('slug', $norderSlug)->get();
            while (count($check_slug) > 0) {
                $norderSlug = Str::random(60);
                $check_slug = DB::table('ordered')->where('slug', $norderSlug)->get();
            }
            $orderSlug = $norderSlug;

            $SCPercent = $SCRow->percent;
            $subtotal =  $order->total_price;
            $discount = 0;
            if ($discountRow->total_customer > 0) {
                $x = $subtotal / $discountRow->total_customer;
                $y = 0.2 * round($discountRow->customer_with_discount,2);
                $discount = round($x * $y,2);
            }
            $SCVal = ($SCPercent / 100) * $subtotal;
            $limeVal = ($limePercent / 100) * $subtotal;

            DB::table('ordered')->insert([
                'tran_id' => $tran_id,
                'menu_id' => $order->menu_id,
                'quantity' => $order->quantity,
                'amount' => $order->total_price,
                'department' => $order->department,
                'cashier' => $order->cashier,
                'discount_amount' => $discount,
                'service_charge' => $SCVal,
                'lime_discount' => $limeVal,
                'type' => $order->type,
                'status' => 'PREPARING',
                'slug' => $orderSlug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);

            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            if ($menu->is_combo == 1) {
                $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                foreach ($ings as $ing) {
                    $decq = $order->quantity * $ing->computed_quantity;
                    $curq = Menu::where('id', $ing->inventory_id)->first()->quantity;
                    $newq = $curq - $decq;

                    $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                    $updateMenu->quantity = $newq;
                    $updateMenu->save();
                }
            } else {
                DB::table('menus')
                    ->where('id', $order->menu_id)
                    ->decrement('quantity', $order->quantity);
            }

            DB::table('orders')->where('id', $order->id)->delete();
        }

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        foreach ($orders as $order) {
            $orderResult .= '
                                <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                    <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                        ' . $order->name . '
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <button data-slug="' . $order->slug . '" class="w-full bg-red-200 rounded-lg descQty aspect-square"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                    </div>
                                    <div class="flex items-center justify-center col-span-1">
                                        <p class="w-full text-sm font-semibold leading-7 text-center border-0 h-7">' . $order->quantity . '</p>
                                        <input type="hidden" value="' . $order->quantity . '">
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <button data-slug="' . $order->slug . '" class="w-full rounded-lg incQty aspect-square bg-emerald-200"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                    </div>
                                    <div class="flex items-center justify-center col-span-3 text-sm font-semibold">
                                        ' . number_format($order->total_price, 2, ".", ",") . '
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="w-full bg-red-600 rounded-lg removeButton aspect-square"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                    </div>
                                </div>
                            ';

            $subTotal = $subTotal + $order->total_price;
            $total = $total + $order->total_price;
            $amount = $total + $order->total_price;
        }

        DB::table('tables')->where('id', $table)->update([
            'status' => 1
        ]);

        $amount = $total;

        DB::table('discounts')->where('user_id', auth()->id())->update([
            'customer_with_discount' => 0,
            'total_customer' => 0,
        ]);

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount
        );

        echo json_encode($response);
    }
    public function updateServiceCharge(Request $request) {
        $service_charge = ltrim($request->service_charge);

        DB::table('service_charge')->where('id', 1)->update([
            'percent' => $service_charge,
        ]);

        return redirect()->route('pos.index');
    }

    public function updateLimeDiscount(Request $request) {
        $lime_discount = ltrim($request->lime_discount);

        DB::table('lime_discount')->where('id', 1)->update([
            'percent' => $lime_discount,
        ]);

        return redirect()->route('pos.index');
    }
    public function updateDiscount(Request $request) {
        $customer_with_discount = ltrim($request->customer_with_discount, '0');
        $total_customer = ltrim($request->total_customer, '0');

        DB::table('discounts')->where('user_id', auth()->id())->update([
            'customer_with_discount' => $customer_with_discount,
            'total_customer' => $total_customer,
        ]);

        return redirect()->route('pos.index');
    }

    public function deleteDiscount() {
        DB::table('discounts')->where('id', 1)->update([
            'customer_with_discount' => 0,
            'total_customer' => 0,
        ]);

        return redirect()->route('pos.index');
    }

    public function send() {
        $emailTo = (DB::table('settings')->where('id', 1)->first())->email;

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+1 day'));

        $salesQuery = DB::table('transactions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as stotal'),
                DB::raw('0 as etotal')
            )
            ->where('status', 'PAID')
            ->where('order_status', '!=', 'CANCELLED')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date');

        $expensesQuery = DB::table('inventory_transactions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('0 as stotal'),
                DB::raw('SUM(amount) as etotal')
            )
            ->where('type', 'INCOMING')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date');

        $data = $salesQuery->union($expensesQuery)->orderBy('date')->get();

        Mail::to($emailTo)
            ->send(new MailNotify($data));

        return redirect()->route('pos.index');
    }

    public function print($id) {
        $trans = DB::table('transactions')
            ->select('transactions.*', 'tables.name as table_name')
            ->join('tables', 'transactions.table', '=', 'tables.id')
            ->where('transactions.table', $id)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->where('transactions.order_status', '!=', 'COMPLETED')
            ->orderBy('id', 'desc')
            ->first();

        $orders = DB::table('ordered')
            ->select('ordered.*', 'menus.name')
            ->join('menus', 'ordered.menu_id', '=', 'menus.id')
            ->where('tran_id', $trans->id)
            ->get();


        $settings = DB::table('settings')->where('id', 1)->first();


        return view('user.cashier.bill', compact('orders', 'trans', 'settings'));
    }

    public function updateCurrentQuantity() {

        $comboMenus = Menu::where('is_combo', 1)->where('is_deleted', 0)->get();

        foreach ($comboMenus as $comboMenu) {
            $ingredients = Ingredient::where('menu_id', $comboMenu->id)->where('is_deleted', 0)->get();

            if ($ingredients->count() == 0) {
                $minQuantity = 0;
                $cminQuantity = 0;
            } else {
                $minQuantity = PHP_INT_MAX;
                $cminQuantity = PHP_INT_MAX;
            }

            foreach ($ingredients as $ingredient) {
                $inventoryQuantity = $ingredient->menu->quantity;
                $requiredQuantity = $ingredient->quantity;
                $availableCombos = floor($inventoryQuantity / $requiredQuantity);
                $minQuantity = min($minQuantity, $availableCombos);

                $cinventoryQuantity = $ingredient->menu->current_quantity;
                $crequiredQuantity = $ingredient->quantity;
                $cavailableCombos = floor($cinventoryQuantity / $crequiredQuantity);
                $cminQuantity = min($cminQuantity, $cavailableCombos);
            }

            $comboMenu->update([
                'quantity' => $minQuantity,
                'current_quantity' => $cminQuantity,
            ]);
        }
    }
}
