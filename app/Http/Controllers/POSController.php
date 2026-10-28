<?php

namespace App\Http\Controllers;

use App\Mail\MailNotify;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Ordered;
use App\Models\Transaction;
use App\Models\GuestTransaction;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

 
class POSController extends Controller {
    //ken updted index method start here - ken 252306001 -- 3-19-26
    public function index() {

        $this->updateCurrentQuantity();
        $tables = DB::table('tables')
        ->orderBy('id', 'asc')
        ->where('is_deleted', 0)
        ->where('store_code', auth()->user()->store_code)
        ->get();
        

        $cashiers = DB::table('servers')
        ->leftJoin('staff', 'staff.id', '=', 'servers.cashier' )
        ->where('servers.user_id', auth()->id())
        ->where('store_code', auth()->user()->store_code)
        ->first();

        
        $cashierId = auth()->id();
        

        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        
        //$TakeOutBox = DB::table('menus')->where('id', 679)->get();
        
        // Determine allowed departments for this user's store
        $deptIds = DB::table('departments')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->pluck('id')
            ->toArray();
        if (empty($deptIds)) {
            // fallback to previous default departments
            $deptIds = [1,2,3,4,5,11,15,36,47];
        }

        //ken added start here - ken 252306001 -- 3-19-26
        // Join brand name for display in menu tiles
        $menus = DB::table('menus')
        ->leftJoin('brands', 'brands.id', '=', 'menus.brand_id')
        ->where('menus.quantity', '>', 0)
        ->where('menus.is_deleted', 0)
        ->whereIn('menus.department', $deptIds)
        ->where('menus.store_code', auth()->user()->store_code)
        ->select('menus.*', 'brands.name as brand_name')
        ->orderBy('menus.name', 'asc')->get();
        //ken added end here - ken 252306001 -- 3-19-26

        $categories = DB::table('menu_categories')
        ->where('is_hidden', 0)
        ->where('is_deleted', 0)
        ->orderBy('name', 'asc')
        ->where('store_code', auth()->user()->store_code)
        ->get();

        $modeOfPayments = DB::table('mode_of_payment')
            ->where('store_code', auth()->user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('id')
            ->pluck('name');

        $table_cat = DB::table('table_categories') //leo added 4/22/25
        ->orderBy('id', 'asc')
        ->orderBy('name', 'asc')
        ->where('is_deleted', 0)
        ->where('store_code', auth()->user()->store_code)
        ->get(); //leo end 4/22/25

        $subTotal = $subTotalNoDisc =  $subTotalODisc = 0;
        $total = 0;
        $sc = 50;
        $vatsales = 0;
        $lessVatSubtotal = $lessVatSubtotalNoDisc = 0;
        $vat = 0;
        $net = $netWithDiscount = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        $discount = $SCPercent = $SCVal = $limePercent = $limeVal = 0;
        $priceType = '';
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->price_type == 'panda'){
                    $priceType = 'Food Panda';
                } else {
                    $priceType = $order->price_type;
                }
                if ($order->type != 1){
                    if ($order->is_discount == 0){
                        $subTotalNoDisc += $order->total_price;
                    } else {
                        $subTotal +=  $order->total_price;
                    }
                    
                }
                
            }

            $SCPercent = $SCRow->percent ?? 0;
            $limePercent = $limeDiscountRow->percent;
            

            $limeVal = ($limePercent / 100) * $subTotal; 
            $net =  $subTotal + $subTotalNoDisc;
            //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
            $lessVatSubtotal = $subTotal / 1.12;
            $lessVatSubtotalNoDisc = $subTotalNoDisc / 1.12;
            // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
            $x =0;
            if ($discountRow->total_customer > 0) {
                $x = $lessVatSubtotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = round($x * $y,2);

                $VatExemptSales = round($x * $discountRow->customer_with_discount,2);
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
            //echo $LessVatDeduction;
            $total = round(($net + $SCVal) - ($LessVatDeduction + $discount + $limeVal),2);
            
            $subTotal = $net;
        }

        /* $response = array(
            'sub' => $SubtotalLessDiscount,
            'vat' => $discountRow->total_customer,
            'vatsales' => $subTotalWDisc,
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
        // if (auth()->user()->role == 2 || auth()->user()->role == 7){
            return view('user.cashier.pos', compact('tables', 'orders', 'discountRow', 'menus', 'categories', 'subTotal', 'total', 'discount', 'SCRow', 'SCPercent','SCVal','limeDiscountRow', 'limePercent', 'limeVal','LessVatDeduction','VatExemptSales','vatsales','vat','audited','nonAudited','cashiers','priceType','table_cat', 'modeOfPayments'));
        // } else {
        //     return view('user.cashier.pos-admin', compact('tables', 'orders', 'discountRow', 'menus', 'categories', 'subTotal', 'total', 'discount', 'SCRow', 'SCPercent','SCVal','limeDiscountRow', 'limePercent', 'limeVal','LessVatDeduction','VatExemptSales','vatsales','vat','audited','nonAudited','cashiers','priceType','table_cat'));
        // }
        
    }
    //ken updted index method end here - ken 252306001 -- 3-19-26
    public function indexAdmin() {

        $this->updateCurrentQuantity();
        $tables = DB::table('tables')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $cashiers = DB::table('servers')
        ->leftJoin('staff', 'staff.id', '=', 'servers.cashier' )
        ->where('servers.user_id', auth()->id())->first();

        
        $cashierId = auth()->id();
        

        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        
        //$TakeOutBox = DB::table('menus')->where('id', 679)->get();
        
        // For admin index, restrict departments to those belonging to the admin's store as well
        $adminDeptIds = DB::table('departments')
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->pluck('id')
            ->toArray();
        if (empty($adminDeptIds)) {
            $adminDeptIds = [1,2,3,4,5,36,47];
        }

        $menus = DB::table('menus')
        ->leftJoin('brands', 'brands.id', '=', 'menus.brand_id')
        ->where('menus.quantity', '>', 0)
        ->where('menus.is_deleted', 0)
        ->whereIn('menus.department', $adminDeptIds)
        ->select('menus.*', 'brands.name as brand_name')
        ->orderBy('menus.name', 'asc')->get();

        $categories = DB::table('menu_categories')->where('is_hidden', 0)->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        $subTotal = $subTotalWDisc = $subTotalNoDisc = 0;
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
        
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->type != 1){
                    if ($order->is_discount == 0){
                        $subTotalNoDisc += $order->total_price;
                    } else {
                        $subTotal +=  $order->total_price;
                    }
                    
                }
                
            }

            $SCPercent = $SCRow->percent ?? 0;
            $limePercent = $limeDiscountRow->percent;
            

            $limeVal = ($limePercent / 100) * $subTotal; 
            $net =  $subTotal + $subTotalNoDisc;
            //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
            $lessVatSubtotal = $subTotal / 1.12;
            $lessVatSubtotalNoDisc = $subTotalNoDisc / 1.12;
            // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
            $x =0;
            if ($discountRow->total_customer > 0) {
                $x = $lessVatSubtotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = round($x * $y,2);

                $VatExemptSales = $x * $discountRow->customer_with_discount;
                $LessVatDeduction = $VatExemptSales * 0.12;

                $SubtotalLessDiscount = $lessVatSubtotal+$lessVatSubtotalNoDisc - $discount - $limeVal;
                $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            } else {
                $SubtotalLessDiscount = $lessVatSubtotal+$lessVatSubtotalNoDisc - $discount - $limeVal;
                $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            }
            
            $vatsales = ($lessVatSubtotal + $lessVatSubtotalNoDisc) - $VatExemptSales;
            
            $vat = $vatsales * 0.12;
            
            $total = ($net + $SCVal) - ($LessVatDeduction + $discount + $limeVal);
            
            $subTotal = $net;
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

        return view('user.cashier.pos-admin', compact('tables', 'orders', 'discountRow', 'menus', 'categories', 'subTotal', 'total', 'discount', 'SCRow', 'SCPercent','SCVal','limeDiscountRow', 'limePercent', 'limeVal','LessVatDeduction','VatExemptSales','vatsales','vat','audited','nonAudited','cashiers'));
    }

    public function add(Request $request) {
        $this->updateCurrentQuantity();

        $slug = $request->slug;
        $userRole = $request->userRole;
        $priceType = $request->priceType;
        $qty = intval($request->qty ?? 1);
        if ($qty < 1) {
            $qty = 1;
        }

        $cashierId = auth()->id();
        $notif = '';

        $menu = DB::table('menus')->where('slug', $slug)->first();
        if (!$menu) {
            $notif = '<div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">'
                . '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">'
                . '<svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>'
                . '<span class="sr-only">Error icon</span></div>'
                . '<div class="pr-10 ml-3 text-base font-medium">Menu item not found.</div>'
                . '</div>';

            $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
            $this->displayOrders($orders,$notif);
            return;
        }

        $SCRow = DB::table('service_charge')->where('user_id', $cashierId)->first();
        $SCPercent = $SCRow->percent ?? 0;

        $limeDiscountRow = DB::table('lime_discount')->where('user_id', $cashierId)->first();
        $limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent ?? 0;

        // generate unique order slug
        $orderSlug = Str::random(60);
        $check_slug = DB::table('inventories')->where('slug', $orderSlug)->get();
        while (count($check_slug) > 0) {
            $orderSlug = Str::random(60);
            $check_slug = DB::table('inventories')->where('slug', $orderSlug)->get();
        }

        // Determine price based on selected type
        $price = (float) ($menu->retail_price ?? 0) > 0 ? $menu->retail_price : $menu->price;
        $priceTypeDB = 'regular';
        $priceTypeNormalized = strtolower(trim($priceType ?? ''));

        if ($priceTypeNormalized === 'grab') {
            $price = $menu->grab_price;
            $priceTypeDB = 'grab';
        } elseif ($priceTypeNormalized === 'panda' || $priceTypeNormalized === 'food panda') {
            $price = $menu->panda_price;
            $priceTypeDB = 'panda';
        } elseif ($priceTypeNormalized === 'wholesale') {
            $price = $menu->wholesale_price;
            $priceTypeDB = 'wholesale';
        } elseif ($priceTypeNormalized === 'slab') {
            $price = $menu->slab_price;
            $priceTypeDB = 'slab';
        }

        // Prevent adding more than available stock
        $availableQty = (float) $menu->quantity;
        if ($availableQty < $qty) {
            $notif = '<div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">'
                . '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">'
                . '<svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>'
                . '<span class="sr-only">Error icon</span></div>'
                . '<div class="pr-10 ml-3 text-base font-medium">Not enough stock for the requested quantity.</div>'
                . '</div>';

            $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
            $this->displayOrders($orders,$notif);
            return;
        }

        $total_price = $price * $qty;
        $current_quantity = $availableQty - $qty;

        $order = new Order();
        $order->menu_id = $menu->id;
        $order->department = $menu->department;
        $order->name = $menu->name;
        $order->quantity = $qty;
        $order->price = $price;
        $order->price_type = $priceTypeDB;
        // Ensure non-null values for required DB columns
        $order->food_cost = $menu->food_cost ?? 0;
        $order->branch = $menu->branch_id ?? 0;
        $order->is_pwd = 0;
        $order->is_odisc = 0;
        $order->is_discount = $menu->is_discount;
        $order->remarks = '';
        $order->total_price = $total_price;
        $order->current_stock = $current_quantity;
        $order->cashier = $cashierId;
        $order->role = $userRole;
        $order->slug = $orderSlug;
        $order->save();

        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        $this->displayOrders($orders,$notif);
    }


    public function addqty(Request $request){
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $qty = (float)$request->qty;
        $corder = DB::table('orders')->where('slug', $slug)->first();

        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $SCPercent = $SCRow->percent ?? 0;
        $notif = '';
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        
        if ($qty <= $menu->quantity) {
            $quantity = $qty;
            $total_price = $corder->price * $quantity;
            $current_stock = $menu->quantity - $qty;
            DB::table('orders')->where('slug', $slug)->update([
                'quantity' => (float)$quantity,
                'total_price' => $total_price,
                
            ]);
            
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
        
        $this->displayOrders($orders,$notif);

    }
    
    public function inc(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $corder = DB::table('orders')->where('slug', $slug)->first();

        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
        

        $notif = '';
        
        if ($menu->quantity > 0) {
            $quantity = $corder->quantity + 1;
            $price = 0;
            if ($corder->price_type == "grab"){
                $price = $menu->grab_price;
            } elseif ($corder->price_type == "panda") {
                $price = $menu->panda_price;
            } else {
                $price = (float) ($menu->retail_price ?? 0) > 0 ? $menu->retail_price : $menu->price;
            }
            $total_price = $price * $quantity;
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
        $cashiers = DB::table('servers')->where('user_id', auth()->id())->first();
        
        $cashierId = auth()->id();
        
        
        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        
        $this->displayOrders($orders,$notif);
    }

    public function changePriceType(Request $request) {
        $slug = $request->slug;
        $priceType = strtolower(trim($request->priceType ?? ''));

        $order = DB::table('orders')->where('slug', $slug)->first();
        if (!$order) {
            $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
            $this->displayOrders($orders, '<div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">' .
                '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">' .
                '<svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>' .
                '<span class="sr-only">Error icon</span></div>' .
                '<div class="pr-10 ml-3 text-base font-medium">Order not found.</div>' .
            '</div>');
            return;
        }

        $menu = DB::table('menus')->where('id', $order->menu_id)->first();
        if (!$menu) {
            $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
            $this->displayOrders($orders, '<div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">' .
                '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">' .
                '<svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>' .
                '<span class="sr-only">Error icon</span></div>' .
                '<div class="pr-10 ml-3 text-base font-medium">Menu item not found.</div>' .
            '</div>');
            return;
        }

        $price = (float) ($menu->retail_price ?? 0) > 0 ? $menu->retail_price : $menu->price;
        if ($priceType === 'wholesale') {
            $price = $menu->wholesale_price;
        } elseif ($priceType === 'slab') {
            $price = $menu->slab_price;
        } elseif ($priceType === 'grab') {
            $price = $menu->grab_price;
        } elseif ($priceType === 'panda' || $priceType === 'food panda') {
            $price = $menu->panda_price;
        }

        $quantity = $order->quantity;
        $total_price = $price * $quantity;

        DB::table('orders')->where('slug', $slug)->update([
            'price_type' => $priceType,
            'price' => $price,
            'total_price' => $total_price,
        ]);

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $this->displayOrders($orders, '');
    }

    public function changeBrand(Request $request) {
        $slug = $request->slug;
        $menuId = intval($request->menu_id ?? 0);

        $order = DB::table('orders')->where('slug', $slug)->first();
        if (!$order) {
            $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
            $this->displayOrders($orders, '<div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">' .
                '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">' .
                '<svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>' .
                '<span class="sr-only">Error icon</span></div>' .
                '<div class="pr-10 ml-3 text-base font-medium">Order not found.</div>' .
            '</div>');
            return;
        }

        $newMenu = DB::table('menus')->where('id', $menuId)->first();
        if (!$newMenu) {
            $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
            $this->displayOrders($orders, '<div id="toast-danger" class="flex items-center w-full p-4 mb-4 text-gray-500 bg-red-200 border border-red-200 rounded-lg shadow-lg" role="alert">' .
                '<div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-200 rounded-lg">' .
                '<svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>' .
                '<span class="sr-only">Error icon</span></div>' .
                '<div class="pr-10 ml-3 text-base font-medium">Brand item not found.</div>' .
            '</div>');
            return;
        }

        // Determine price based on current price type selection
        $priceType = strtolower(trim($order->price_type ?? ''));
        $price = (float) ($newMenu->retail_price ?? 0) > 0 ? $newMenu->retail_price : $newMenu->price;
        if ($priceType === 'wholesale') {
            $price = $newMenu->wholesale_price;
        } elseif ($priceType === 'slab') {
            $price = $newMenu->slab_price;
        } elseif ($priceType === 'grab') {
            $price = $newMenu->grab_price;
        } elseif ($priceType === 'panda' || $priceType === 'food panda') {
            $price = $newMenu->panda_price;
        }

        $quantity = $order->quantity;
        $total_price = $price * $quantity;

        DB::table('orders')->where('slug', $slug)->update([
            'menu_id' => $newMenu->id,
            'name' => $newMenu->name,
            'price' => $price,
            'price_type' => $priceType,
            'total_price' => $total_price,
            'food_cost' => $newMenu->food_cost ?? 0,
            'branch' => $newMenu->branch_id ?? 0,
            'is_discount' => $newMenu->is_discount ?? 0,
        ]);

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $this->displayOrders($orders, '');
    }

    public function grab(Request $request) {
        //$this->updateCurrentQuantity();
        $pricetype = $request->slug;
        $corders = DB::table('orders')->where('cashier', auth()->id())->get();
        
        foreach ($corders as $orderUpdate){
            DB::table('orders')->where('slug', $orderUpdate->slug)->update([
                'price_type' => $pricetype,
                
            ]);
        }

        $notif = '';
        $corders = DB::table('orders')->where('cashier', auth()->id())->get();
        foreach ($corders as $order){
            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            /* start of computation */
            $price = 0;
            $quantity = $order->quantity;
            if ($order->price_type == "grab"){
                $price = $menu->grab_price;
            } elseif ($order->price_type == "panda") {
                $price = $menu->panda_price;
            } else {
                $price = (float) ($menu->retail_price ?? 0) > 0 ? $menu->retail_price : $menu->price;
            }
            //echo $price;
            $total_price = $price * $quantity;
            DB::table('orders')->where('slug', $order->slug)->update([
                'total_price' => $total_price,
                
            ]);
            
            /* end of computation */
        }
        $cashierId = auth()->id();
        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        $this->displayOrders($orders,$notif);
        
        

        
    }

    public function desc(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $corder = DB::table('orders')->where('slug', $slug)->first();
        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
        //Service charge section
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $SCPercent = $SCRow->percent ?? 0;

        //Lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        $limePercent = $limeVal = 0; 
        $limePercent = $limeDiscountRow->percent;   

        if ($corder->quantity > 1) {
            $quantity = $corder->quantity - 1;
            $price = 0;
            if ($corder->price_type == "grab"){
                $price = $menu->grab_price;
            } elseif ($corder->price_type == "panda") {
                $price = $menu->panda_price;
            } else {
                $price = (float) ($menu->retail_price ?? 0) > 0 ? $menu->retail_price : $menu->price;
            }
            $total_price = $price * $quantity;
            $current_quantity = $corder->current_stock + 1;
            DB::table('orders')->where('slug', $slug)->update([
                'quantity' => $quantity,
                'total_price' => $total_price,
                
            ]);
            
        }
        $cashiers = DB::table('servers')->where('user_id', auth()->id())->first();
        
        $cashierId = auth()->id();
        
        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        $notif = '';
        $this->displayOrders($orders,$notif);
        
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
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $SCPercent = $SCRow->percent ?? 0;

        //Lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        $limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;
            
        $cashiers = DB::table('servers')->where('user_id', auth()->id())->first();
        
        $cashierId = auth()->id();
        
        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();

        $notif = '';
        
        $this->displayOrders($orders,$notif);

        
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
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $SCPercent = $SCRow->percent ?? 0;

        //Lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
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
        $cashiers = DB::table('servers')->where('user_id', auth()->id())->first();
        
        $cashierId = auth()->id();
        
        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $vatsales = 0;
        $vat = 0;
        $net = $netWithDiscount = $subTotalWDisc = 0;
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

                if ($order->is_pwd == 0){
                    $is_pwdCheck = '';
                } else {
                    $is_pwdCheck = 'checked';
                }
                $orderResult .= '
                                    <div class="grid grid-cols-12 content-center h-14 w-full text-center px-4">
                                            <div class="col-span-1 flex items-center ">
                                                <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary mr-4" id="'. $order->slug .'" '. $checkStatus .'> ';
                
                $orderResult .= '</div>
                                            <div class="col-span-1 flex items-center ">
                                            <button  data-slug="'. $order->slug .'" class="myMsg aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-comment-alt-dots text-xl"></i></button>
                                            </div>
                                            <div class="col-span-3 text-xs font-semibold text-left flex items-center pr-2">
                                                '. $order->name .'
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-minus text-xl text-red-900"></i></button>
                                            </div>
                                            <div class="col-span-1 flex items-center justify-center px-1">
                                                <input type="text" id="orderqty" data-slug="'. $order->slug .'" class="w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="'. $order->quantity .'">
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="uil uil-plus text-xl text-emerald-900"></i></button>
                                            </div>
                                            <div class="col-span-2 flex items-center text-sm font-semibold justify-center">
                                                '. number_format($order->total_price, 2, '.', ',') .'
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" data-name="'. $order->name .'" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200"></i></button>
                                            </div>
                                        </div>
                                ';
                $subTotal = $subTotal + $order->total_price;
            }
            $SCPercent = $SCRow->percent ?? 0;
            $limePercent = $limeDiscountRow->percent;
            //$subTotal = $subTotal +$sc;
            $limeVal = ($limePercent / 100) * $subTotal;
            $net =  $subTotalWDisc + $subTotal ;

            $limeVal = ($limePercent / 100) * $net; 
            //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
            
            // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
            if ($discountRow->customer_with_discount > 0) {
                $discount = 0.2 * $subTotalWDisc;

            }

            $SubtotalLessDiscount = $net - $discount - $limeVal;
            $SCVal = ($SCPercent / 100) * $net;
            
            //discount default is 20% of the net value ex net = 100, then discount = 20
            $total = ($SubtotalLessDiscount + $SCVal);
            
            $subTotal = $subTotal + $subTotalWDisc;

        }

        $amount = $total;
        //$this->updateCurrentQuantity();

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => $discount,
            'LessVatDeduction' => $LessVatDeduction,
            'VatExemptSales' => $VatExemptSales ,
            'vatsales' => $vatsales ,
            'vat' => $vat ,
            'ordersCount' => $orders->count()
        );

        echo json_encode($response);
    }

    public function pay(Request $request) {
        $sc_val = $request->input('sc_val', 0);
        $amount = $request->amount;
        $table = $request->table;
        $order_remarks = $request->order_remarks;
        $amountInput = $request->amountInput;
        $mop = $request->mop;
        $payor_name = $request->payor_name;
        $payor_number = $request->payor_number;
        $sc_val = $request->input('sc_val', 0);
        $discountTotal = $request->discountTotal;
        // $limepercent = $request->limepercent;
        $limediscountTotal = $request->limediscountTotal;

        $LessVatDeduction = $request->LessVatDeduction;
        $VatExemptSales = $request->VatExemptSales;
        $vatsales = $request->vatsales;
        $vat = $request->vat;
        $numPacks = $request->numPacks;
        
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
        $paymentAmounts = json_decode($request->input('paymentAmounts', '[]'), true);
        $paymentDetails = [
            'ref_no' => $request->input('ref_no'),
            'bank_name' => $request->input('bank_name'),
            'client_name' => $request->input('client_name'),
            'payment_date' => $request->input('payment_date'),
        ];

        /* End MOP */
        
        //discount section 1-13-24 by mackey
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
                
        //service charge
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $SCPercent = $SCRow->percent ?? 0;

        //lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        //$limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;

        if ($table == 6) {
            $type = 'TAKE OUT';
        } else {
            $type = 'DINE-IN';
        }

        $number = $this->generateTransactionNumber();

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
        $tran->order_status = 'COMPLETED';
        $tran->cashier = auth()->id();
        $tran->slug = $tranSlug;
        $tran->customer_with_discount = $discountRow->customer_with_discount;
        $tran->total_customer = $numPacks;
        $tran->pwd = $discountTotal;
        $tran->service_charge = $sc_val;
        $tran->sc_percent = $SCPercent;
        $tran->lime_discount = $limePercent;
        $tran->ld_value = $limediscountTotal;
        $tran->print = 1;
        $tran->lessVatDeduction = $LessVatDeduction;
        $tran->vatExemptSales = $VatExemptSales;
        $tran->vatSales = $vatsales;
        $tran->vat = $vat;
        $tran->fee = 0;
        $tran->is_vat = 1;
        $tran->sales_type = 0;
        $tran->is_uuid = (string) Str::uuid();
        $tran->local_id = null;
        $tran->staff_id = auth()->id();

        // Ensure transactions are scoped to the current user/store/branch.
        $tran->store_code = auth()->user()->store_code;

        // If the user doesn't have a branch_code (common for some users), fall back to the store's default branch_code.
        $tran->branch_code = auth()->user()->branch_code ?? DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->value('branch_code');

        $tran->branch = auth()->user()->branch;

        $tran->save();

        $tran_id = $tran->id;

        
        

        /* Start of Saving Multiple MOP */

        $cashier = auth()->id();

        foreach ($paymentAmounts as $payment) {
            $paymentAmount = (float) ($payment['amount'] ?? 0);
            $paymentMop = trim((string) ($payment['mop'] ?? ''));
            if ($paymentAmount > 0 && $paymentMop !== '') {
                $this->saveMOP($tran_id, $paymentMop, $paymentAmount, $cashier, $paymentDetails, $tran->number, $tran);
            }
        }

        if (empty($paymentAmounts) && ($cashMop > 0 || $amount == 0)){
            $mop = 'CASH';
            $amount = $cashMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($ccMop > 0){
            $mop = 'CREDIT CARD - MASTERCARD';
            $amount = $ccMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($visaMop > 0){
            $mop = 'VISA';
            $amount = $visaMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($gcashMop > 0){
            $mop = 'GCASH';
            $amount = $gcashMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($wcpMop > 0){
            $mop = 'WE CHAT PAY';
            $amount = $wcpMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($amexMop > 0){
            $mop = 'AMEX';
            $amount = $amexMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($bdoMop > 0){
            $mop = 'BDO PAY';
            $amount = $bdoMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($bancnetMop > 0){
            $mop = 'BANCNET';
            $amount = $bancnetMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($dinersMop > 0){
            $mop = 'DINERS';
            $amount = $dinersMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($dpMop > 0){
            $mop = 'DOWN PAYMENT';
            $amount = $dpMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($giftMop > 0){
            $mop = 'GIFT CERT';
            $amount = $giftMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($jcbMop > 0){
            $mop = 'JCB';
            $amount = $jcbMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($mayaMop > 0){
            $mop = 'PAYMAYA';
            $amount = $mayaMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($grabPayMop > 0){
            $mop = 'GRAB PAY';
            $amount = $grabPayMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($unionMop > 0){
            $mop = 'UNION PAY';
            $amount = $unionMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($grabMop > 0){
            $mop = 'GRAB FOOD';
            $amount = $grabMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($pandaMop > 0){
            $mop = 'FOOD PANDA';
            $amount = $pandaMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

        if ($qrphMop > 0){
            $mop = 'QR PH';
            $amount = $qrphMop;
            $this->saveMOP($tran_id, $mop, $amount, $cashier, $paymentDetails, $tran->number, $tran);
        }

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


        $orders = DB::table('orders')->where('cashier', auth()->id())->get();
        $menus = DB::table('menus') ->where('store_code', auth()->user()->store_code) ->get();
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
            $net = $netWithDiscount = $subTotalWDisc = 0;
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


            $menu = DB::table('menus')->where('id', $order->menu_id)->first();
            $orderedUuid = (string) Str::uuid();
            $orderedId = DB::table('ordered')->insertGetId([
                'tran_id' => null,
                'tran_uuid' => $tran->is_uuid,
                'is_uuid' => $orderedUuid,
                'menu_id' => $order->menu_id,
                'menu_cloud_id' => $order->menu_id,
                'menu_uuid' => $menu->is_uuid,
                'quantity' => $order->quantity,
                'department' => $order->department,
                'branch' => $order->branch,
                'cashier' => $order->cashier,
                'role' => $order->role,
                'amount' => $order->total_price,
                'price_type' => $order->price_type,
                'food_cost' => $order->food_cost,
                'is_pwd' => $order->is_pwd,
                'is_odisc' => $order->is_odisc,
                'remarks' => $order->remarks,
                'discount_amount' => $discount,
                'lime_discount' => $limeVal,
                'type' => $order->type,
                'print' => 0,
                'for_delete' => 0,
                'is_split' => 0,
                'is_discount' => $order->is_discount,
                'service_charge' => $SCVal,
                'status' => 'PREPARING',
                'slug' => $orderSlug,
                'store_code' => auth()->user()->store_code,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);

            $previousQuantity = (float) $menu->quantity;
            $remainingQuantity = $previousQuantity - (float) $order->quantity;

            DB::table('inventory_movement')->insert([
                'type' => 'DELIVERY NOTE',
                'ordered_id' => $orderedId,
                'menu_id' => $order->menu_id,
                'trans_id' => $tran->id,
                'trans_number' => $tran->number,
                'reference' => $tran->number,
                'detail' => $tran->payor_name,
                'unit' => $menu->unit,
                'previous_qty' => $previousQuantity,
                'qty_in' => 0,
                'qty_out' => $order->quantity,
                'remaining' => $remainingQuantity,
                'sold_price' => $order->price,
                'cost' => $order->food_cost,
                'created_at' => now(),
                'updated_at' => now(),
                'branch_code' => $tran->branch_code,
                'store_code' => $tran->store_code,
                'is_uuid' => (string) Str::uuid(),
                'is_sync' => 1,
            ]);

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

            $this->recordPosCogs($tran, (int) $order->menu_id, (int) $order->quantity, $orderedUuid);

            DB::table('orders')->where('id', $order->id)->delete();
        }

        $orders = DB::table('orders')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        foreach ($orders as $order) {
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
            $orderResult .= '
                                    <div class="grid grid-cols-12 content-center h-14 w-full text-center px-4">
                                            <div class="col-span-1 flex items-center ">
                                                <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary mr-4" id="'. $order->slug .'" '. $checkStatus .'> ';
                
                $orderResult .= '</div>
                                            <div class="col-span-1 flex items-center ">
                                            <button  data-slug="'. $order->slug .'" class="myMsg aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-comment-alt-dots text-xl"></i></button>
                                            </div>
                                            <div class="col-span-3 text-xs font-semibold text-left flex items-center pr-2">
                                                '. $order->name .'
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-minus text-xl text-red-900"></i></button>
                                            </div>
                                            <div class="col-span-1 flex items-center justify-center px-1">
                                                <input type="text" id="orderqty" data-slug="'. $order->slug .'" class="w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="'. $order->quantity .'">
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="uil uil-plus text-xl text-emerald-900"></i></button>
                                            </div>
                                            <div class="col-span-2 flex items-center text-sm font-semibold justify-center">
                                                '. number_format($order->total_price, 2, '.', ',') .'
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" data-name="'. $order->name .'" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200"></i></button>
                                            </div>
                                        </div>
                                ';

            $subTotal = $subTotal + $order->total_price;
            $total = $total + $order->total_price;
            $amount = $total + $order->total_price;
        }

        // if ($table > 1) {
        //     DB::table('tables')->where('id', $table)->update([
        //         'status' => 1
        //     ]);
        // }

        $amount = $total;

        DB::table('discounts')->where('user_id', auth()->id())->update([
            'customer_with_discount' => 0,
            'total_customer' => 0,
        ]);

        DB::table('lime_discount')->where('id', auth()->id())->update([
            'percent' => 0,
        ]);
        
         DB::table('service_charge')->where('user_id', auth()->id())->update([
            'percent' => 0,
        ]);

        DB::table('prints')->insert([
            'tran_id' => $tran_id,
            'status' => 0,
            'created_at' => date('Y-m-d h:i:s')
        ]);

        /* DB::table('prints_bar')->insert([
            'tran_id' => $tran_id,
            'status' => 0,
            'created_at' => date('Y-m-d h:i:s')
        ]);

        DB::table('prints_cashier')->insert([
            'tran_id' => $tran_id,
            'status' => 0,
            'created_at' => date('Y-m-d h:i:s')
        ]); */

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'tran_id' => $tran_id
        );

        echo json_encode($response);
    }

    public function paylater(Request $request) {
        $amount = $request->amount;
        $table = $request->table;
        $sc_val = $request->input('sc_val', 0);
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
        $numPacks = $request->numPacks;


        
        $cashierId = auth()->id();
        


        if ($table == 1) {
            $type = 'TAKE OUT';
        } else {
            $type = 'DINE-IN';
        }


        $this_tran = DB::table('transactions')->where('table', $table)->where('status', 'UNPAID')->where('order_status', '!=', 'CANCELLED')->where('order_status', '!=', 'COMPLETED')->first();
        if ($this_tran) {
            $tran_id = $this_tran->id;

            DB::table('transactions')->where('id', $tran_id)->update([
                'total' => DB::raw('total + '.$amount),
                'service_charge' => DB::raw('service_charge + '.$sc_val),
                'pwd' => DB::raw('pwd + '.$discountTotal),
                'ld_value' => DB::raw('ld_value + '.$limediscountTotal),
                'lessVatDeduction' => DB::raw('lessVatDeduction + '.$LessVatDeduction),
                'vat' => DB::raw('vat + '.$vat),
                'vatSales' => DB::raw('vatSales + '.$vatsales),
                'vatExemptSales' => DB::raw('vatExemptSales + '.$VatExemptSales),
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

            $number = $this->generateTransactionNumber();

            $tranSlug = Str::random(60);
            $ntranSlug = $tranSlug;
            $check_slug = DB::table('transactions')->where('slug', $ntranSlug)->get();
            while (count($check_slug) > 0) {
                $ntranSlug = Str::random(60);
                $check_slug = DB::table('transactions')->where('slug', $ntranSlug)->get();
            }

            $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
            $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
            $SCPercent = $SCRow->percent ?? 0;
            $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
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
            $tran->cashier = $cashierId;
            $tran->slug = $tranSlug;
            $tran->customer_with_discount = $discountRow->customer_with_discount;
            $tran->total_customer = $numPacks;
            $tran->pwd = $discountTotal;
            $tran->service_charge = $sc_val;
            $tran->sc_percent = $SCPercent;
            $tran->lime_discount = $limePercent;
            $tran->ld_value = $limediscountTotal;

            $tran->lessVatDeduction = $LessVatDeduction;
            $tran->vatExemptSales = $VatExemptSales;
            $tran->vatSales = $vatsales;
            $tran->vat = $vat;
            $tran->is_uuid = (string) Str::uuid();
            $tran->local_id = null;
            $tran->staff_id = auth()->id();
            $tran->save();

            $tran_id = $tran->id;
        }

        
        //discount section 1-13-24 by mackey
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        
        //service charge
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();

        //lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        //$limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;

        //ken update start here - ken 252306001 -- 3-19-26
        // Batch insert to reduce query count and avoid slow per-row operations
        $orders = DB::table('orders')->where('cashier', $cashierId)->get();
        $orderedInserts = [];
        $cogsAllocations = [];

        foreach ($orders as $order) {
            $orderSlug = (string) Str::uuid();
            $orderedUuid = (string) Str::uuid();
            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            $SCPercent = $SCRow->percent ?? 0;
            $subtotal = $order->total_price;
            $discount = 0;
            if ($discountRow->total_customer > 0) {
                $x = $subtotal / $discountRow->total_customer;
                $y = 0.2 * round($discountRow->customer_with_discount, 2);
                $discount = round($x * $y, 2);
            }
            $SCVal = ($SCPercent / 100) * $subtotal;
            $limeVal = ($limePercent / 100) * $subtotal;

            $orderedInserts[] = [
                'tran_id' => null,
                'tran_uuid' => $tran->is_uuid,
                'is_uuid' => $orderedUuid,
                'menu_id' => $order->menu_id,
                'menu_cloud_id' => $order->menu_id,
                'menu_uuid' => $menu->is_uuid,
                'quantity' => $order->quantity,
                'amount' => $order->total_price,
                'price_type' => $order->price_type,
                'department' => $order->department,
                'branch' => $order->branch,
                'cashier' => $order->cashier,
                'role' => $order->role,
                'food_cost' => $order->food_cost,
                'is_pwd' => $order->is_pwd,
                'is_odisc' => $order->is_odisc,
                'remarks' => $order->remarks,
                'discount_amount' => $discount,
                'service_charge' => $SCVal,
                'lime_discount' => $limeVal,
                'type' => $order->type,
                'print' => 0,
                'for_delete' => 0,
                'is_split' => 0,
                'is_discount' => $order->is_discount,
                'status' => 'PREPARING',
                'slug' => $orderSlug,
                'store_code' => auth()->user()->store_code,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $cogsAllocations[] = [
                'menu_id' => (int) $order->menu_id,
                'quantity' => (int) $order->quantity,
                'ordered_uuid' => $orderedUuid,
            ];

            if ($menu && $menu->is_combo == 1) {
                $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                foreach ($ings as $ing) {
                    $decq = $order->quantity * $ing->computed_quantity;
                    DB::table('menus')->where('id', $ing->inventory_id)->decrement('quantity', $decq);
                }
            } else {
                DB::table('menus')->where('id', $order->menu_id)->decrement('quantity', $order->quantity);
            }
        }

        if (!empty($orderedInserts)) {
            DB::table('ordered')->insert($orderedInserts);
        }

        foreach ($cogsAllocations as $allocation) {
            $this->recordPosCogs(
                $tran,
                $allocation['menu_id'],
                $allocation['quantity'],
                $allocation['ordered_uuid']
            );
        }

        DB::table('orders')->where('cashier', $cashierId)->delete();
        //ken update end here - ken 252306001 -- 3-19-26

        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        foreach ($orders as $order) {
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
            $orderResult .= '
                                    <div class="grid grid-cols-12 content-center h-14 w-full text-center px-4">
                                            <div class="col-span-1 flex items-center ">
                                                <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary mr-4" id="'. $order->slug .'" '. $checkStatus .'> ';
                
                $orderResult .= '</div>
                                            <div class="col-span-1 flex items-center ">
                                            <button  data-slug="'. $order->slug .'" class="myMsg aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-comment-alt-dots text-xl"></i></button>
                                            </div>
                                            <div class="col-span-3 text-xs font-semibold text-left flex items-center pr-2">
                                                '. $order->name .'
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-minus text-xl text-red-900"></i></button>
                                            </div>
                                            <div class="col-span-1 flex items-center justify-center px-1">
                                                <input type="text" id="orderqty" data-slug="'. $order->slug .'" class="w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="'. $order->quantity .'">
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="uil uil-plus text-xl text-emerald-900"></i></button>
                                            </div>
                                            <div class="col-span-2 flex items-center text-sm font-semibold justify-center">
                                                '. number_format($order->total_price, 2, '.', ',') .'
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" data-name="'. $order->name .'" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200"></i></button>
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
        
        DB::table('lime_discount')->where('id', auth()->id())->update([
            'percent' => 0,
        ]);
        DB::table('service_charge')->where('user_id', auth()->id())->update([
            'percent' => 0,
        ]);
        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'tran_id' => $tran_id
        );


        // DB::table('servers')->where('user_id', auth()->id())->update([
        //     'cashier' => 0,
        // ]);
        
         

       /*  DB::table('ordered')->where('tran_id', $tran_id)->update([
            'print' => 0,
        ]); */
        DB::table('prints')->insert([
            'tran_id' => $tran_id,
            'status' => 0,
            'created_at' => date('Y-m-d h:i:s')
        ]);

        /* DB::table('prints_bar')->insert([
            'tran_id' => $tran_id,
            'status' => 0,
            'created_at' => date('Y-m-d h:i:s')
        ]);

        DB::table('prints_cashier')->insert([
            'tran_id' => $tran_id,
            'status' => 0,
            'created_at' => date('Y-m-d h:i:s')
        ]); */

        echo json_encode($response);
    }
    public function updateServiceCharge(Request $request) {
        $service_charge = trim((string) $request->input('service_charge', ''));
        $service_charge = $service_charge === '' ? 0 : (float) $service_charge;

        DB::table('service_charge')->where('user_id', auth()->id())->update([
            'percent' => $service_charge,
        ]);

        return redirect()->route('pos.index');
    }

    public function updateLimeDiscount(Request $request) {
        $lime_discount = ltrim($request->lime_discount);

        DB::table('lime_discount')->where('user_id', auth()->id())->update([
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
        DB::table('discounts')->where('user_id', auth()->id())->update([
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
        $baseQuery = DB::table('transactions')
            ->select('transactions.*', 'tables.name as table_name', 'users.lastname as lastname')
            ->leftJoin('tables', 'transactions.table', '=', 'tables.id')
            ->leftJoin('users', 'transactions.cashier', '=', 'users.id')
            ->where('transactions.order_status', '!=', 'CANCELLED');

        $trans = null;

        // If a table ID is provided in numeric form, prefer the latest transaction on that table.
        if (is_numeric($id) && DB::table('tables')->where('id', $id)->exists()) {
            $trans = (clone $baseQuery)
                ->where('transactions.table', $id)
                ->orderBy('transactions.id', 'desc')
                ->first();
        }

        if (!$trans && is_numeric($id)) {
            // If not table-based or no table transaction found, try exact transaction ID.
            $trans = (clone $baseQuery)
                ->where('transactions.id', $id)
                ->first();
        }

        if (!$trans) {
            $trans = (clone $baseQuery)
                ->where('transactions.slug', $id)
                ->orWhere('transactions.number', $id)
                ->first();
        }

        if (!$trans && is_numeric($id)) {
            // fallback: table lookup if id matches table id and table transaction wasn't used above.
            $trans = (clone $baseQuery)
                ->where('transactions.table', $id)
                ->orderBy('transactions.id', 'desc')
                ->first();
        }

        if (!$trans) {
            $trans = (clone $baseQuery)
                ->where('transactions.cashier', auth()->id())
                ->orderBy('transactions.id', 'desc')
                ->first();
        }

        if (!$trans) {
            $trans = (clone $baseQuery)
                ->orderBy('transactions.id', 'desc')
                ->first();
        }

        if (!$trans) {
            return redirect()->route('pos.index')->with('error', 'No transaction found to print.');
        }

        $orders = DB::table('ordered')
            ->select(
                'ordered.*',
                'menus.name',
                'menus.brand_code',
                'menus.unit',
                'brands.name as brand_name'
            )
            ->join('menus', 'ordered.menu_id', '=', 'menus.id')
            ->leftJoin('brands', 'menus.brand_id', '=', 'brands.id')
            ->where('tran_id', $trans->id)
            ->get();

        $seniors = DB::table('seniors')->where('tran_id', $trans->id)->get();
        $payments = DB::table('payment_orders')->where('tran_id', $trans->id)->get();
        $settings = DB::table('settings')->where('id', 2)->first();
        $paymentBreakdown = $payments->map(function ($payment) {
            return (object) [
                'method' => $payment->mop,
                'amount' => $payment->amount,
            ];
        });

        $orderdineincount = DB::table('ordered')
            ->where('tran_id', $trans->id)
            ->where('item_takeout','0')
            ->count();

        $ordertakeoutcount = DB::table('ordered')
            ->where('tran_id', $trans->id)
            ->where('item_takeout','1')
            ->count();

        $trans->computed_total = $orders->sum('amount');
        $trans->computed_quantity = $orders->sum('quantity');

        $receiptItems = [];
        foreach ($orders->groupBy('name') as $name => $group) {
            $quantities = $group->pluck('quantity')->map(function ($quantity) {
                return number_format((float) $quantity, 3);
            })->implode('/');
            $firstOrder = $group->first();
            $quantity = $group->sum('quantity');
            $amount = $group->sum('amount');

            $receiptItems[] = [
                'name' => $name,
                'brand_name' => $firstOrder->brand_name ?? '',
                'brand_code' => $firstOrder->brand_code ?? '',
                'total_quantity' => $quantity,
                'total_amount' => $amount,
                'item_count' => $group->count(),
                'quantity_breakdown' => $quantities,
                'menu_price' => $quantity > 0 ? $amount / $quantity : 0,
                'unit' => $firstOrder->unit ?? 'kg',
            ];
        }

        $subtotal = $orders->sum('amount');
        $servicecharge = $trans->service_charge ?? 0;
        $totaldue = $trans->total ?? ($subtotal + $servicecharge);
        $totalDiscount = 0;
        $change = max(0, ($trans->amount ?? 0) - ($trans->total ?? 0));
        $value2307 = $trans->service_charge ?? 0;
        $percent2307 = $trans->sc_percent ?? 0;
        $pwdseniorDiscount = $trans->pwd ?? 0;
        $numberOfSeniors = $trans->customer_with_discount ?? $seniors->count();
        $discount = $trans->ld_value ?? 0;
        $percentDiscount = $trans->lime_discount ?? 0;
        $overalltotal = $trans->total ?? $totaldue;

        return view('user.cashier.bill', compact(
            'orders',
            'trans',
            'settings',
            'seniors',
            'payments',
            'ordertakeoutcount',
            'orderdineincount',
            'receiptItems',
            'subtotal',
            'servicecharge',
            'totaldue',
            'totalDiscount',
            'change',
            'value2307',
            'percent2307',
            'pwdseniorDiscount',
            'numberOfSeniors',
            'discount',
            'percentDiscount',
            'overalltotal',
            'paymentBreakdown'
        ));
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
                'current_quantity' => $minQuantity,
            ]);
        }
    }

    public function discountSelected(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $checkbox = $request->checkbox;
        $corder = DB::table('orders')->where('slug', $slug)->first();
        if ($checkbox == "true"){
            DB::table('orders')->where('slug', $slug)->update([
                'is_pwd' => 1,
            ]);
        } else {
            $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
            //$total_price = $menu->price * $corder->quantity;
            DB::table('orders')->where('slug', $slug)->update([
                'is_pwd' => 0,
            ]);
        }
        $response = array(
            'orders' => $slug,
            'subTotal' => $checkbox
        );

        echo json_encode($response);
    }


    public function ownerdiscSelected(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $checkbox = $request->checkbox;
        $corder = DB::table('orders')->where('slug', $slug)->first();
        if ($checkbox == "true"){
            DB::table('orders')->where('slug', $slug)->update([
                'is_odisc' => 1,
            ]);
        } else {
            $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
            //$total_price = $menu->price * $corder->quantity;
            DB::table('orders')->where('slug', $slug)->update([
                'is_odisc' => 0,
            ]);
        }
        $response = array(
            'orders' => $slug,
            'subTotal' => $checkbox
        );

        echo json_encode($response);
    }

    public function menuRemarks(Request $request) {
        //$this->updateCurrentQuantity();
        $slug = $request->slug;
        $msgtxt = $request->msgtxt;
        
        DB::table('orders')->where('slug', $slug)->update([
            'remarks' => $msgtxt,
        ]);
         
        $response = array(
            'orders' => $slug,
            'subTotal' => $msgtxt
        );

        echo json_encode($response);
    }

    public function check(Request $request){
        $staff = DB::table('staff')->where('sec_id', $request->sec_code)->first();
        $response = '';
        if (!empty($staff)){
            $servers = DB::table('servers')->get();
            foreach ($servers as $server){
                if ($staff->id == $server->cashier){
                    return redirect()->route('pos.index')->withInput()->with('message', 'error_2');
                }
            }
            DB::table('servers')->where('user_id', auth()->id())->update([
                'cashier' => $staff->id,
            ]);
            $response = array(
                'lname' => $staff->lname,
                'fname' => $staff->fname,
                'id' => $staff->id,
                'message' => 'success'
            );
            return redirect()->route('pos.index');
        } else {
            $response = array(
                'message' => 'error'
            );
            return redirect()->route('pos.index')->withInput()->with('message', 'error');
        }

        
    }

    private function generateTransactionNumber(): string
    {
        $storeCode = auth()->user()->store_code;
        $prefix = DB::table('settings')
            ->where('store_code', $storeCode)
            ->value('tr_number_1st') ?: 'LTC';
        $datePart = now()->format('mdy');
        $sequence = DB::table('transactions')
            ->where('store_code', $storeCode)
            ->where('number', 'like', $prefix . $datePart . '%')
            ->lockForUpdate()
            ->count() + 1;

        return $prefix . $datePart . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function recordPosCogs(object $transaction, int $menuId, int $quantity, string $orderedUuid): void
    {
        $quantityRemaining = max(0, $quantity);
        if ($quantityRemaining === 0) {
            return;
        }

        $storeCode = auth()->user()->store_code;
        $branchCode = $transaction->branch_code ?? auth()->user()->branch_code ?? DB::table('settings')
            ->where('store_code', $storeCode)
            ->value('branch_code');

        DB::transaction(function () use (
            $transaction,
            $menuId,
            $quantityRemaining,
            $orderedUuid,
            $storeCode,
            $branchCode
        ) {
            $purchaseLots = DB::table('purchase_ordered')
                ->where('menu_id', $menuId)
                ->where('store_code', $storeCode)
                ->where('remaining_stocks', '>', 0)
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($purchaseLots as $purchaseLot) {
                if ($quantityRemaining <= 0) {
                    break;
                }

                $allocatedQuantity = min($quantityRemaining, (int) $purchaseLot->remaining_stocks);
                if ($allocatedQuantity <= 0) {
                    continue;
                }

                $remainingStocks = (int) $purchaseLot->remaining_stocks - $allocatedQuantity;
                DB::table('purchase_ordered')
                    ->where('id', $purchaseLot->id)
                    ->update([
                        'remaining_stocks' => $remainingStocks,
                        'updated_at' => now(),
                    ]);

                $poPrice = (float) $purchaseLot->price;
                DB::table('ordered_cogs')->insert([
                    'tran_local_id' => (string) ($transaction->local_id ?? $transaction->id),
                    'tran_uuid' => $transaction->is_uuid,
                    'menu_id' => $menuId,
                    'ordered_uuid' => $orderedUuid,
                    'po_id' => $purchaseLot->id,
                    'si_number' => $purchaseLot->sales_invoice,
                    'quantity' => $allocatedQuantity,
                    'po_price' => $poPrice,
                    'po_total' => $allocatedQuantity * $poPrice,
                    'status' => 'SALES',
                    'store_code' => $storeCode,
                    'branch_code' => $branchCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $quantityRemaining -= $allocatedQuantity;
            }
        });
    }

    public function saveMOP($tran_id, $mop, $amount, $cashier, array $details, $tranNumber, Transaction $tran) {
        DB::table('payment_orders')->insert([
            'tran_id' => $tran_id,
            'mop' => $mop,
            'amount' => $amount,
            'cashier' => $cashier,
            'created_at' => date('Y-m-d h:i:s'),
            'updated_at' => date('Y-m-d h:i:s')
        ]);

        $paymentMethod = DB::table('mode_of_payment')
            ->where('store_code', auth()->user()->store_code)
            ->where('name', $mop)
            ->first();

        DB::table('payment_logs')->insert([
            'tran_id' => $tran_id,
            'tran_number' => $tranNumber,
            'tran_uuid' => $tran->is_uuid,
            'mop_id' => $paymentMethod->id ?? null,
            'label' => $mop,
            'total' => $amount,
            'amount' => $amount,
            'balance' => 0,
            'cashier_id' => $cashier,
            'type' => 0,
            'is_deleted' => 0,
            'ref_no' => $details['ref_no'] ?? null,
            'bank_name' => $details['bank_name'] ?? null,
            'client_name' => $details['client_name'] ?? null,
            'payment_date' => $details['payment_date'] ?? null,
            'store_code' => auth()->user()->store_code,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function displayOrders($orders,$notif){

        
        //service charge section
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
        $SCPercent = $SCRow->percent ?? 0;
        //lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        $limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent ?? 0;
        
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $vatsales = 0;
        $vat = 0;
        $net = $netWithDiscount = $subTotalWDisc = $subTotalNoDisc = 0;
        $VatExemptSales = 0;
        $LessVatDeduction =0;
        $SubtotalLessDiscount = 0;
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        if (!$discountRow) {
            $discountRow = (object) [
                'total_customer' => 0,
                'customer_with_discount' => 0,
            ];
        }
        $discount = 0;
        $priceType = '';
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->price_type == 'panda'){
                    $priceType = 'Food Panda';
                } else {
                    $priceType = $order->price_type;
                }
                
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
                $orderResult .= '
                                    <div class="grid grid-cols-12 content-center h-14 w-full text-center px-4">
                                    ';
                $nameSpan = 'col-span-3';
                if(auth()->user()->role != 7){
                    $orderResult .= ' 
                                    <div class="col-span-3 flex items-center mr-1">
                                    <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary mr-1" id="'. $order->slug .'" '. $checkStatus .'>
                                    
                                    <button  data-slug="'. $order->slug .'" class="myMsg aspect-square w-full max-w-[30px] bg-red-200 rounded-lg"><i class="uil uil-comment-alt-dots text-lg"></i></button>
                                    </div>';
                    $nameSpan = 'col-span-3';
                    
                    // ethan added march-26,2025
                } elseif (auth()->user()->role == 7) {
                    $orderResult .= ' 
                                    <div class="col-span-1 flex items-center mr-1">
                                    
                                        <button  data-slug="'. $order->slug .'" class="myMsg aspect-square w-full max-w-[30px] bg-red-200 rounded-lg"><i class="uil uil-comment-alt-dots text-lg"></i></button>
                                    </div>';
                    $nameSpan = 'col-span-5';
                    // end of ethan added march-26,2025
                } else {
                    $nameSpan = 'col-span-5';
                }
                
                $menu = DB::table('menus')->where('id', $order->menu_id)->first();
                $brandSelect = '';
                if ($menu) {
                    $brandOptions = DB::table('menus as m')
                        ->join('brands as b', 'm.brand_id', '=', 'b.id')
                        ->select(
                            'm.id as menu_id',
                            'b.name as brand_name',
                            'm.price',
                            'm.retail_price',
                            'm.wholesale_price',
                            'm.slab_price',
                            'm.grab_price',
                            'm.panda_price'
                        )
                        ->where('m.name', $menu->name)
                        ->where('m.store_code', $menu->store_code)
                        ->where('m.is_deleted', 0)
                        ->orderBy('b.name')
                        ->get();

                    $priceType = strtolower(trim($order->price_type ?? ''));

                    if ($brandOptions->count() > 0) {
                        $brandSelect = '<select class="brandSelect text-xs border border-gray-300 rounded px-2 py-1" data-slug="'. $order->slug .'">';
                        foreach ($brandOptions as $bOpt) {
                            $price = (float) ($bOpt->retail_price ?? 0) > 0 ? $bOpt->retail_price : $bOpt->price;
                            if ($priceType === 'wholesale') {
                                $price = $bOpt->wholesale_price;
                            } elseif ($priceType === 'slab') {
                                $price = $bOpt->slab_price;
                            } elseif ($priceType === 'grab') {
                                $price = $bOpt->grab_price;
                            } elseif ($priceType === 'panda' || $priceType === 'food panda') {
                                $price = $bOpt->panda_price;
                            }

                            $formatted = number_format($price, 2, '.', ',');
                            $label = $bOpt->brand_name . ' (₱ ' . $formatted . ')';

                            $selected = ($bOpt->menu_id == $order->menu_id) ? ' selected' : '';
                            $brandSelect .= '<option value="'.$bOpt->menu_id.'"'.$selected.'>'.$label.'</option>';
                        }
                        $brandSelect .= '</select>';
                    }
                }

                $orderResult .= '
                        <div class="'.$nameSpan.' text-xs font-semibold text-left flex flex-col pr-2">
                            <span>'. $order->name .'</span>
                            <span class="mt-1">'. $brandSelect .'</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <button data-slug="'. $order->slug .'" class="descQty aspect-square w-full max-w-[30px] bg-red-200 rounded-lg"><i class="uil uil-minus text-lg text-red-900"></i></button>
                        </div>
                        <div class="col-span-1 flex items-center justify-center px-1">
                            <input type="text"
                                data-slug="'. $order->slug .'"
                                class="orderQtyInput w-full text-center text-sm font-semibold border border-gray-300 rounded-md h-7 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                value="'. $order->quantity .'"
                                autocomplete="off">
                        </div>
                        <div class="flex items-center justify-center">
                            <button data-slug="'. $order->slug .'" class="incQty aspect-square w-full max-w-[30px] bg-emerald-200 rounded-lg"><i class="uil uil-plus text-lg text-emerald-900"></i></button>
                        </div>
                        <div class="col-span-2 flex items-center text-sm font-semibold justify-center">
                            '. number_format($order->total_price, 2, '.', ',') .'
                        </div>
                        <div class="flex items-center justify-center">
                            <button data-slug="'. $order->slug .'" data-name="'. $order->name .'" class="removeButton aspect-square w-full max-w-[30px] bg-red-600 rounded-lg"><i class="uil uil-times text-lg text-red-200"></i></button>
                        </div>
                    </div>
                                ';
                
                //$subTotal = $subTotal + $order->total_price;
            }
            
            foreach ($orders as $order) {
                if ($order->type != 1){
                    if ($order->is_discount == 0){
                        $subTotalNoDisc += $order->total_price;
                    } else {
                        $subTotal +=  $order->total_price;
                    }
                    
                }
                
            }

            $SCPercent = $SCRow->percent ?? 0;
            $limePercent = $limeDiscountRow->percent ?? 0;
            

            $limeVal = ($limePercent / 100) * $subTotal; 
            $net =  $subTotal + $subTotalNoDisc;
            //Owner Discount ex subTotal = 100 and ownerpercent = 5% , ownerVal = 5php
            $lessVatSubtotal = $subTotal / 1.12;
            $lessVatSubtotalNoDisc = $subTotalNoDisc / 1.12;
            // Compute net of Subtotal ex. st = 100 ownerval = 5, net = 95
            $x =0;
            if ($discountRow->total_customer > 0) {
                $x = $lessVatSubtotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = round($x * $y,2);

                $VatExemptSales = $x * $discountRow->customer_with_discount;
                $LessVatDeduction = $VatExemptSales * 0.12;

                $SubtotalLessDiscount = $lessVatSubtotal+$lessVatSubtotalNoDisc - $discount - $limeVal;
                $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            } else {
                $SubtotalLessDiscount = $lessVatSubtotal+$lessVatSubtotalNoDisc - $discount - $limeVal;
                $SCVal = ($SCPercent / 100) * $SubtotalLessDiscount;
            }
            
            $vatsales = ($lessVatSubtotal + $lessVatSubtotalNoDisc) - $VatExemptSales;
            
            $vat = $vatsales * 0.12;
            
            $total = ($net + $SCVal) - ($LessVatDeduction + $discount + $limeVal);
            
            $subTotal = $net;

        }

        $amount = $total;

        //$this->updateCurrentQuantity();

        $response = array(
            'thisNotif' => $notif,
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => round($amount,2),
            'discount' => round($discount,2),
            'ServiceCharge' => round($SCVal,2),
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => round($limeVal,2),
            'LessVatDeduction' => round($LessVatDeduction,2),
            'VatExemptSales' => round($VatExemptSales,2) ,
            'vatsales' => round($vatsales,2) ,
            'vat' => round($vat,2) ,
            'ordersCount' => $orders->count(),
            'cashier' => auth()->id(),
            'priceType' => ucwords($priceType),
             
        );

        echo json_encode($response);
    }



    

    public function commit(Request $request) {
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
        $numPacks = 0;

        $cashierId = auth()->id();
        


        if ($table == 1) {
            $type = 'TAKE OUT';
        } else {
            $type = 'DINE-IN';
        }


        

            $id = GuestTransaction::latest()->pluck('id')->first();
            if ($id == null) {
                $id = 1;
            } else {
                $id++;
            }
            $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

            $number = $this->generateTransactionNumber();

            $tranSlug = Str::random(60);
            $ntranSlug = $tranSlug;
            $check_slug = DB::table('guest_transactions')->where('slug', $ntranSlug)->get();
            while (count($check_slug) > 0) {
                $ntranSlug = Str::random(60);
                $check_slug = DB::table('guest_transactions')->where('slug', $ntranSlug)->get();
            }

            $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
            $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();
            $SCPercent = $SCRow->percent;
            $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
            //$limePercent = $limeVal = 0;
            $limePercent = $limeDiscountRow->percent;

            $tranSlug = $ntranSlug;

            $tran = new GuestTransaction();
            $tran->number = $number;
            $tran->total = $amount;
            $tran->payor_name = ucfirst($payor_name);
            $tran->payor_number = $payor_number;
            $tran->type = $type;
            $tran->service_charge =$sc_val;
            $tran->order_remarks = $order_remarks;
            $tran->table = $table;
            $tran->status = 'UNPAID';
            $tran->order_status = 'PENDING';
            $tran->cashier = $cashierId;
            $tran->slug = $tranSlug;
            $tran->customer_with_discount = $discountRow->customer_with_discount;
            $tran->total_customer = $numPacks;
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
        

        
        //discount section 1-13-24 by mackey
        $discountRow = DB::table('discounts')->where('user_id', auth()->id())->first();
        
        //service charge
        $SCPercent = $SCVal = 0;
        $SCRow = DB::table('service_charge')->where('user_id', auth()->id())->first();

        //lime discount section
        $limeDiscountRow = DB::table('lime_discount')->where('user_id', auth()->id())->first();
        //$limePercent = $limeVal = 0;
        $limePercent = $limeDiscountRow->percent;

        $orders = DB::table('orders')->where('cashier', $cashierId)->get();
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

            DB::table('guest_ordered')->insert([
                'tran_id' => $tran_id,
                'menu_id' => $order->menu_id,
                'quantity' => $order->quantity,
                'amount' => $order->total_price,
                'department' => $order->department,
                'cashier' => $order->cashier,
                'role' => $order->role,
                'food_cost' => $order->food_cost,
                'is_pwd' => $order->is_pwd,
                'remarks' => $order->remarks,
                'discount_amount' => $discount,
                'service_charge' => $SCVal,
                'lime_discount' => $limeVal,
                'type' => $order->type,
                'print' => 0,
                'for_delete' => 0,
                'is_split' => 0,
                'is_discount' => $order->is_discount,
                'status' => 'PENDING',
                'slug' => $orderSlug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);

            

            DB::table('orders')->where('id', $order->id)->delete();
        }

        $orders = DB::table('orders')->where('cashier', $cashierId)->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        foreach ($orders as $order) {
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
            $orderResult .= '
                                    <div class="grid grid-cols-12 content-center h-14 w-full text-center px-4">
                                            <div class="col-span-1 flex items-center ">
                                                <input type="checkbox" data-slug="'. $order->slug .'" class="complimentary mr-4" id="'. $order->slug .'" '. $checkStatus .'> ';
                
                $orderResult .= '</div>
                                            <div class="col-span-1 flex items-center ">
                                            <button  data-slug="'. $order->slug .'" class="myMsg aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-comment-alt-dots text-xl"></i></button>
                                            </div>
                                            <div class="col-span-3 text-xs font-semibold text-left flex items-center pr-2">
                                                '. $order->name .'
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-minus text-xl text-red-900"></i></button>
                                            </div>
                                            <div class="col-span-1 flex items-center justify-center px-1">
                                                <input type="text" id="orderqty" data-slug="'. $order->slug .'" class="w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="'. $order->quantity .'">
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="uil uil-plus text-xl text-emerald-900"></i></button>
                                            </div>
                                            <div class="col-span-2 flex items-center text-sm font-semibold justify-center">
                                                '. number_format($order->total_price, 2, '.', ',') .'
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="'. $order->slug .'" data-name="'. $order->name .'" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200"></i></button>
                                            </div>
                                        </div>
                                ';

            $subTotal = $subTotal + $order->total_price;
            $total = $total + $order->total_price;
            $amount = $total + $order->total_price;
        }

        /* DB::table('tables')->where('id', $table)->update([
            'status' => 1
        ]); */

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


        DB::table('servers')->where('user_id', auth()->id())->update([
            'cashier' => 0,
        ]);

        echo json_encode($response);
    }


    public function displayOrderNotif(){
        
        $orderResult = '';
        $trans = DB::table('guest_transactions')->where('order_status', 'PENDING')->get();
        $transCount = DB::table('guest_transactions')->where('order_status', 'PENDING')->count();
        //$this->updateCurrentQuantity();

        if(!empty($trans)){
            foreach ($trans as $tran){
            $orders = DB::table('guest_ordered')
            ->select('menus.name','guest_ordered.quantity','guest_ordered.amount')
            ->leftjoin('menus', 'menus.id', 'guest_ordered.menu_id')
            ->where('tran_id', $tran->id)->get();
            $table = DB::table('tables')->where('id', $tran->table)->first();
            $orderResult .='
                <div class="rounded-lg shadow p-2 border-2 border-gray-800 mb-5">

                <div class="w-full border-dashed border-2 border-gray-300 rounded-lg">
                    <div class="mb-1 p-2">
                    <span class="text-3xl">'. $tran->number .'</span> - <span class="text-2xl text-blue-500">'.$table->name.'</span>
                    </div>
                    
                </div>
                <div class="grid grid-cols-12 mb-1 font-bold ">
                    <div class="col-span-2">
                        <span class="text-2xl">Quantity</span>
                    </div>
                    <div class="col-span-5">
                        <span class="text-2xl">Name</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-2xl">Amount</span>
                    </div>
                </div> 
                            ';

                foreach ($orders as $order){
                    $orderResult .='
                            <div class="grid grid-cols-12 mb-1 ">
                                <div class="col-span-2">
                                    <span class="text-2xl">'. intval($order->quantity) .'X </span>
                                </div>
                                <div class="col-span-5">
                                    <span class="text-2xl">'. $order->name .'</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-2xl">'. number_format($order->amount,2) .'</span>
                                </div>
                            </div>      
                    ';
                }
                $orderResult .= '
                    <div class="w-full flex justify-end mt-2">
                                        <button  data-slug="'.$tran->slug.'" type="button" class="approvedOrderBtn flex justify-center items-center text-white bg-emerald-600 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-1 py-1 hover:text-gray-900 focus:z-10 w-1/5 mr-3">
                                        <i class="uil uil-check text-3xl mr-3"></i> <span>Approved</span> 
                                        </button>
                                        <button  data-slug="'.$tran->slug.'" type="button" class="deleteOrderBtn flex items-center justify-center text-white bg-red-400 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-1 py-1 hover:text-gray-900 focus:z-10 w-1/5">
                                        <i class="uil uil-multiply  text-3xl mr-3"></i><span>Delete</span> 
                                        </button>
                    </div>
                </div>
                ';
            }
        }
        

        $response = array(
            'orders' => $orderResult,
            'trans' => $trans,
            'transCount' => $transCount
        );

        echo json_encode($response);
    }

    public function orderApprove(Request $request){
        $slug = $request->slug;

        $Guesttrans = DB::table('guest_transactions')->where('slug', $slug)->first();

        $tableStatus = DB::table('tables')->where('id', $Guesttrans->table)->first();

        if ($tableStatus->status != 1){
            $id = Transaction::latest()->pluck('id')->first();
            if ($id == null) {
                $id = 1;
            } else {
                $id++;
            }
            $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

            $number = $this->generateTransactionNumber();

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
            $tran->total = $Guesttrans->total;
            $tran->payor_name = ucfirst($Guesttrans->payor_name);
            $tran->payor_number = $Guesttrans->payor_number;
            $tran->type = $Guesttrans->type;
            $tran->service_charge =$Guesttrans->service_charge;
            $tran->order_remarks = $Guesttrans->order_remarks;
            $tran->table = $Guesttrans->table;
            $tran->status = 'UNPAID';
            $tran->order_status = 'PREPARRING';
            $tran->cashier = auth()->id();
            $tran->slug = $tranSlug;
            $tran->customer_with_discount = $Guesttrans->customer_with_discount;
            $tran->total_customer = $Guesttrans->total_customer;
            $tran->pwd = $Guesttrans->pwd;
            $tran->sc_percent = $Guesttrans->sc_percent;
            $tran->lime_discount = $Guesttrans->lime_discount;
            $tran->ld_value = $Guesttrans->ld_value;

            $tran->lessVatDeduction = $Guesttrans->lessVatDeduction;
            $tran->vatExemptSales = $Guesttrans->vatExemptSales;
            $tran->vatSales = $Guesttrans->vatSales;
            $tran->vat = $Guesttrans->vat;
            $tran->is_uuid = (string) Str::uuid();
            $tran->local_id = null;
            $tran->staff_id = auth()->id();
            $tran->save();

            $tran_id = $tran->id;
            
            DB::table('prints')->insert([
                'tran_id' => $tran_id,
                'status' => 0,
                'created_at' => date('Y-m-d h:i:s')
            ]);
    
            DB::table('prints_bar')->insert([
                'tran_id' => $tran_id,
                'status' => 0,
                'created_at' => date('Y-m-d h:i:s')
            ]);
    
            DB::table('prints_cashier')->insert([
                'tran_id' => $tran_id,
                'status' => 0,
                'created_at' => date('Y-m-d h:i:s')
            ]);

            $guestOrdered =  DB::table('guest_ordered')->where('tran_id', $Guesttrans->id)->get();
                foreach ($guestOrdered as $gOrder){
                    DB::table('ordered')->insert([
                        'tran_id' => null,
                        'tran_uuid' => $tran->is_uuid,
                        'is_uuid' => (string) Str::uuid(),
                        'menu_id' => $gOrder->menu_id,
                        'menu_cloud_id' => $gOrder->menu_id,
                        'quantity' => $gOrder->quantity,
                        'amount' => $gOrder->amount,
                        'department' => $gOrder->department,
                        'branch' => $gOrder->branch,
                        'cashier' => $gOrder->cashier,
                        'role' => $gOrder->role,
                        'food_cost' => $gOrder->food_cost,
                        'is_pwd' => $gOrder->is_pwd,
                        'is_odisc' => 0,
                        'remarks' => $gOrder->remarks,
                        'discount_amount' => $gOrder->discount_amount,
                        'service_charge' => $gOrder->service_charge,
                        'lime_discount' => $gOrder->lime_discount,
                        'type' => $gOrder->type,
                        'print' => 0,
                        'for_delete' => 0,
                        'is_split' => 0,
                        'is_discount' => $gOrder->is_discount,
                        'status' => 'PENDING',
                        'slug' => $gOrder->slug,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);

                    $menu = DB::table('menus')->where('id', $gOrder->menu_id)->first();

                    if ($menu->is_combo == 1) {
                        $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                        foreach ($ings as $ing) {
                            $decq = $gOrder->quantity * $ing->computed_quantity;
                            $curq = Menu::where('id', $ing->inventory_id)->first()->quantity;
                            $newq = $curq - $decq;

                            $updateMenu = Menu::where('id', $ing->inventory_id)->first();
                            $updateMenu->quantity = $newq;
                            $updateMenu->save();
                        }
                    } else {
                        DB::table('menus')
                            ->where('id', $gOrder->menu_id)
                            ->decrement('quantity', $gOrder->quantity);
                    }

                }

                DB::table('guest_transactions')->where('slug', $slug)->update([
                    'cashier' => auth()->id(),
                    'order_status' => 'APPROVED',
                    
                ]);

                DB::table('tables')->where('id', $Guesttrans->table)->update([
                    'status' => '1',
                    
                ]);

                
            $msg = 'success';
        } else {
            $msg = 'error';
        }
        
        
        $response = array(
            'trans' => $Guesttrans->id,
            'msg' => $msg,
        );

        echo json_encode($response);
    }

    public function orderDelete(Request $request){
        $slug = $request->slug;
        $voidCode = $request->code;

        $settings = DB::table('settings')->where('id', 1)->first();

        if ($settings->void_code == $voidCode){

        $trans = DB::table('guest_transactions')->where('slug', $slug)->first();

        DB::table('guest_ordered')->where('tran_id', $trans->id)->delete();
        DB::table('guest_transactions')->where('id', $trans->id)->delete();


        $msg = 'success';
        $transID = $trans->id;

        } else {
            $msg = 'error';
            $transID = '';
        }


        $response = array(
            'trans' => $transID,
            'msg' => $msg,
        );

        echo json_encode($response);
    }

    public function callwaiter(Request $request){
        $id = $request->id;
        $name = $request->name;

        DB::table('callwaiters')->insert([
            'reason_id' => $id,
            'reason_name' => $name,
            'status' => 'WAITING',
            'table_id' => auth()->user()->table_id,
            'created_at' => date('Y-m-d h:i:s'),
            'updated_at' => date('Y-m-d h:i:s'),
            'is_deleted' => 0
        ]);

        
        $response = array(
            'reason_name' => $name,
        );

        echo json_encode($response);
    }

    public function displayCallWaiterNotif(){
        
        $orderResult = '';
        $trans = DB::table('callwaiters')->where('status', 'WAITING')->get();
        $transCount = DB::table('callwaiters')->where('status', 'WAITING')->count();
        //$this->updateCurrentQuantity();

        if(!empty($trans)){
            foreach ($trans as $tran){
            
            $table = DB::table('tables')->where('id', $tran->table_id)->first();
            $orderResult .='
                <div class="rounded-lg shadow p-2 border-2 border-gray-800 mb-5">

                <div class="w-full border-dashed border-2 border-gray-300 rounded-lg">
                    <div class="mb-1 p-2">
                    <span class="text-3xl">'. $table->name .'</span> - <span class="text-2xl text-blue-500">'.$tran->reason_name.'</span>
                    </div>
                    
                </div>
                 
                            ';

                
                $orderResult .= '
                    <div class="w-full flex justify-end mt-2">
                                        <button  data-slug="'.$tran->id.'" type="button" class="assistTableBtn flex justify-center items-center text-white bg-emerald-600 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-1 py-1 hover:text-gray-900 focus:z-10 w-1/5 mr-3">
                                        <i class="uil uil-check text-3xl mr-3"></i> <span>Assist</span> 
                                        </button>
                                        
                    </div>
                </div>
                ';
            }
        }
        

        $response = array(
            'orders' => $orderResult,
            'trans' => $trans,
            'transCount' => $transCount
        );

        echo json_encode($response);
    }

    public function assist(Request $request){
        $slug = $request->slug;

        DB::table('callwaiters')->where('id', $slug)->update([
            'status' => 'ASSISTED',
            
        ]);

        $this->displayCallWaiterNotif();

        /* $response = array(
            'trans' => $slug,
        );
        
        echo json_encode($response); */
    }

}
