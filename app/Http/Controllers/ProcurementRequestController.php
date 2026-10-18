<?php

namespace App\Http\Controllers;

use App\Mail\MailNotify;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\OrderRequest;
use App\Models\ProcurementOrderedRequest;
use App\Models\Transaction;
use App\Models\ProcurementTransactionRequest;
use App\Models\ProcurementOrderRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

 
class ProcurementRequestController extends Controller {
    
    public function index() {

        $userDepartment = auth()->user()->department;   
        $tables = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
       
        if (auth()->user()->role == 6)
            {
                $userDepartment = auth()->user()->department;          
                $menus = DB::table('inventories')
                    ->whereIn('department', [1, 2, 3,6])
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
                
            } 
        
        elseif (auth()->user()->role == 3)
            {
                $menus = DB::table('inventories')
                ->whereIn('department', [1,2,6])
                ->where('branch', 2)
                ->where('is_deleted', 0)
                ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                ->orderBy('name', 'asc')
                ->get();
            } 
        elseif (auth()->user()->role == 4 ||  auth()->user()->role == 9 )
            {
                    $menus = DB::table('inventories')
                    ->whereIn('department', [1, 2, 3,6])
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
            } 


        else 
        {
            $menus = DB::table('inventories')->where('is_deleted', 0)
            ->orderByRaw('CASE WHEN quantity <= reorder_point  THEN 0 ELSE 1 END ')
            ->orderBy('name', 'asc')
            ->get();
        }
        

        $categories = DB::table('categories')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();

        return view('user.inventory.procurementtransfer.procurementrequest', compact('supplierlist','tables', 'orders', 'menus', 'categories' ));
    }

    public function procurementadd(Request $request) {
        $this->updateCurrentQuantity();
        $slug = $request->slug;
        $menu = DB::table('menus')->where('slug', $slug)->first();
        $ord = DB::table('procurement_order_requests')->where('menu_id', $menu->id)->where('cashier', auth()->id())->first();
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

        if ($menu->current_quantity > 0) {
            if ($ord != null) {
                if ($ord->quantity < $menu->current_quantity ) {
                    $quantity = $ord->quantity + 1;
                    $total_price = $menu->price * $quantity;
                    $current_quantity = $menu->current_quantity;
                    DB::table('procurement_order_requests')->where('id', $ord->id)->delete();

                    $order = new OrderRequest();
                    $order->menu_id = $menu->id;
                    $order->name = $menu->name;
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
                $current_quantity = $menu->current_quantity;

                $order = new OrderRequest();
                $order->menu_id = $menu->id;
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

        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('id', 1)->first();
        $discount = 0;
        $ordercount = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->type == 0){
                    $checkStatus = '';
                } else {
                    $checkStatus = 'checked';
                }
                $orderResult .= '
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        
                                    <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2">
                                            <input type="text" data-slug="'.$order->slug.'" id="'.$order->id.'"  class="orderqty w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            '. number_format($order->current_stock,2) .'
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
                $ordercount += $order->quantity;
            }
            if ($discountRow->total_customer > 0) {
                $x = $subTotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = $x * $y;
            }
            
            $total = round(($subTotal)  - $discount,2);
            $SCVal = ($SCPercent / 100) * $subTotal;
            $limeVal = ($limePercent / 100) * $subTotal;
            $total = $total + $SCVal - $limeVal;
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
            'ordersCount' => $orders->count(),
            'qty' => number_format($ordercount, 2, '.', ',')
        );

        echo json_encode($response);
    }

    public function addqty(Request $request){
        $this->updateCurrentQuantity();
        $slug = $request->slug;
        $qty = (float)$request->qty;
        $corder = DB::table('procurement_order_requests')->where('slug', $slug)->first();

        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;
        $notif = '';

        
        if ($qty <= $menu->quantity) {
            $quantity = $qty;
            $total_price = $corder->price * $quantity;
            $current_stock = $menu->quantity;
            DB::table('procurement_order_requests')->where('slug', $slug)->update([
                'quantity' => (float)$quantity,
                'total_price' => $total_price,
                'current_stock' => (float)$current_stock,
            ]);

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

        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('id', 1)->first();
        $discount = 0;
        $ordercount = 0;
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
                                        <div class="flex items-center justify-center col-span-2">
                                            <input type="text" data-slug="'.$order->slug.'" id="'.$order->id.'"  class="orderqty w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            '. number_format($order->current_stock,2) .'
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
                $subTotal = $subTotal + $order->total_price;
                $ordercount += $order->quantity;
            }
            if ($discountRow->total_customer > 0) {
                $x = $subTotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = $x * $y;
            }
            //$subTotal = $subTotal + $sc;
            $total = round(($subTotal)  - $discount,2);
            $SCVal = ($SCPercent / 100) * $subTotal;
            $total = $total + $SCVal;
        }

        $amount = $total;

        $this->updateCurrentQuantity();

        $response = array(
            'thisNotif' => $notif,
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'ServiceCharge' => number_format($SCVal, 2, '.', ','),
            'ordersCount' => $orders->count(),
            'qty' => number_format($ordercount, 2, '.', ',')
        );

        echo json_encode($response);
    }
    public function inc(Request $request) {
        $this->updateCurrentQuantity();
        $slug = $request->slug;
        $corder = DB::table('procurement_order_requests')->where('slug', $slug)->first();

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
        
        if ($corder->quantity < $menu->current_quantity) {
            $quantity = $corder->quantity + 1;
            $total_price = $corder->price * $quantity;
            $current_stock = $menu->current_quantity;
            DB::table('procurement_order_requests')->where('slug', $slug)->update([
                'quantity' => $quantity,
                'total_price' => $total_price,
                'current_stock' => $current_stock,
            ]);

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

        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('id', 1)->first();
        $discount = 0;
        $ordercount = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                if ($order->type == 0){
                    $checkStatus = '';
                } else {
                    $checkStatus = 'checked';
                }
                $orderResult .= '
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        
                                    <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2">
                                            <input type="text" data-slug="'.$order->slug.'" id="'.$order->id.'"  class="orderqty w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            '. number_format($order->current_stock,2) .'
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
                $ordercount += $order->quantity;
            }
            if ($discountRow->total_customer > 0) {
                $x = $subTotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = $x * $y;
            }
            //$subTotal = $subTotal + $sc;
            $total = round(($subTotal)  - $discount,2);
            $SCVal = ($SCPercent / 100) * $subTotal;
            $limeVal = ($limePercent / 100) * $subTotal;
            $total = $total + $SCVal - $limeVal;
        }

        $amount = $total;

        $this->updateCurrentQuantity();

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
            'ordersCount' => $orders->count(),
            'qty' => number_format($ordercount, 2, '.', ',')
        );

        echo json_encode($response);
    }

    public function desc(Request $request) {
        $this->updateCurrentQuantity();
        $slug = $request->slug;
        $corder = DB::table('procurement_order_requests')->where('slug', $slug)->first();
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
            $current_quantity = $corder->current_stock;
            DB::table('procurement_order_requests')->where('slug', $slug)->update([
                'quantity' => $quantity,
                'total_price' => $total_price,
                'current_stock' => $current_quantity,
            ]);

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

        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('id', 1)->first();
        $discount = 0;
        $ordercount = 0;
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
                                        <div class="flex items-center justify-center col-span-2">
                                            <input type="text" data-slug="'.$order->slug.'" id="'.$order->id.'"  class="orderqty w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            '. number_format($order->current_stock,2) .'
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
                $ordercount += $order->quantity;
            }
            if ($discountRow->total_customer > 0) {
                $x = $subTotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = $x * $y;
            }
            //$subTotal = $subTotal + $sc;
            $total = round(($subTotal)  - $discount,2);
            $SCVal = ($SCPercent / 100) * $subTotal;
            $limeVal = ($limePercent / 100) * $subTotal;
            $total = $total + $SCVal - $limeVal;
        }

        $amount = $total;

        $this->updateCurrentQuantity();

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'ServiceCharge' => number_format($SCVal, 2, '.', ','),
            'limeDiscountPercent' => $limePercent,
            'limeDiscountVal' => number_format($limeVal, 2, '.', ',') ,
            'ordersCount' => $orders->count(),
            'qty' => number_format($ordercount, 2, '.', ',')
        );

        echo json_encode($response);
    }



    public function comp(Request $request) {
        $this->updateCurrentQuantity();
        $slug = $request->slug;
        $checkbox = $request->checkbox;
        $corder = DB::table('procurement_order_requests')->where('slug', $slug)->first();
        if ($checkbox == "true"){
            DB::table('procurement_order_requests')->where('slug', $slug)->update([
                'type' => 1,
            ]);
        } else {
            $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
            $total_price = $menu->price * $corder->quantity;
            DB::table('procurement_order_requests')->where('slug', $slug)->update([
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
            

        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('id', 1)->first();
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
                                        
                                    <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2">
                                            <input type="text" data-slug="'.$order->slug.'" id="'.$order->id.'"  class="orderqty w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            '. number_format($order->current_stock,2) .'
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
            if ($discountRow->total_customer > 0) {
                $x = $subTotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = $x * $y;
            }
            //$subTotal = $subTotal + $sc;
            $total = round(($subTotal)  - $discount,2);
            $SCVal = ($SCPercent / 100) * $subTotal;
            $limeVal = ($limePercent / 100) * $subTotal;
            $total = $total + $SCVal - $limeVal;
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
            'ordersCount' => $orders->count()
        );



            echo json_encode($response);

        
    }


    public function remove(Request $request) {
        $this->updateCurrentQuantity();
        $slug = $request->slug;
        $ord = DB::table('procurement_order_requests')->where('slug', $slug)->first();
        $mid = $ord->menu_id;
        $menu = DB::table('menus')->where('id', $mid)->first();
        $qty = $ord->quantity;

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
        }
 */
        // $oqty = $menu->current_quantity;
        // $nqty = $oqty + $qty;

        // DB::table('menus')->where('id', $mid)->update([
        //     'current_quantity' => $nqty,
        // ]);

        DB::table('procurement_order_requests')->where('slug', $slug)->delete();

        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $discountRow = DB::table('discounts')->where('id', 1)->first();
        $discount = 0;
        $ordercount = 0;
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
                                        <div class="flex items-center justify-center col-span-2">
                                            <input type="text" data-slug="'.$order->slug.'" id="'.$order->id.'"  class="orderqty w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            '. number_format($order->current_stock,2) .'
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
                $subTotal = $subTotal + $order->total_price;
                $ordercount += $order->quantity;
            }
            if ($discountRow->total_customer > 0) {
                $x = $subTotal / $discountRow->total_customer;
                $y = 0.2 * $discountRow->customer_with_discount;
                $discount = $x * $y;
            }
            //$subTotal = $subTotal + $sc;
            $total = round(($subTotal)  - $discount,2);
        }

        $amount = $total;
        $this->updateCurrentQuantity();

        $response = array(
            'orders' => $orderResult,
            'subTotal' => number_format($subTotal, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount' => $amount,
            'discount' => number_format($discount, 2, '.', ','),
            'ordersCount' => $orders->count(),
            'qty' => number_format($ordercount, 2, '.', ',')
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


        //discount section 1-13-24 by mackey
        $discountRow = DB::table('discounts')->where('id', 1)->first();
                
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

        $id = TransactionRequest::latest()->pluck('id')->first();
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
        $check_slug = DB::table('transaction_requests')->where('slug', $ntranSlug)->get();
        while (count($check_slug) > 0) {
            $ntranSlug = Str::random(60);
            $check_slug = DB::table('transaction_requests')->where('slug', $ntranSlug)->get();
        }
        $tranSlug = $ntranSlug;

        $tran = new TransactionRequest();
        $tran->number = $number;
        $tran->total = $amount;
        
        $tran->mode_of_payment = $mop;
        $tran->amount = $amountInput;
        $tran->payor_name = ucfirst($payor_name);
        $tran->payor_number = $payor_number;
        $tran->type = $type;
        $tran->order_remarks = $order_remarks;
        $tran->table = $table;
        $tran->status = 'UNPAID';
        $tran->order_status = 'REQUESTING';
        $tran->cashier = auth()->id();
        $tran->slug = $tranSlug;
        $tran->customer_with_discount = $discountRow->customer_with_discount;
        $tran->total_customer = $discountRow->total_customer;
        $tran->pwd = $discountTotal;
        $tran->service_charge = $sc_val;
        $tran->sc_percent = $SCPercent;
        $tran->lime_discount = $limePercent;
        $tran->ld_value = $limediscountTotal;
        $tran->save();

        $tran_id = $tran->id;

        


        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->get();
        foreach ($orders as $order) {
            $orderSlug = Str::random(60);
            $norderSlug = $orderSlug;
            $check_slug = DB::table('ordered_request')->where('slug', $norderSlug)->get();
            while (count($check_slug) > 0) {
                $norderSlug = Str::random(60);
                $check_slug = DB::table('ordered_request')->where('slug', $norderSlug)->get();
            }
            $orderSlug = $norderSlug;
            $subtotal = $order->total_price;
            $discount = 0;
            if ($discountRow->total_customer > 0) {
                $x = $subtotal / $discountRow->total_customer;
                $y = 0.2 * round($discountRow->customer_with_discount,2);
                $discount = round($x * $y,2);
            }
            $SCVal = ($SCPercent / 100) * $subtotal;
            $limeVal = ($limePercent / 100) * $subtotal;
            
            DB::table('ordered_request')->insert([
                'tran_id' => $tran_id,
                'menu_id' => $order->menu_id,
                'quantity' => $order->quantity,
                'amount' => $order->total_price,
                'cashier' => $order->cashier,
                'discount_amount' => $discount,
                'lime_discount' => $limeVal,
                'type' => $order->type,
                'service_charge' => $SCVal,
                'status' => 'REQUESTING',
                'slug' => $orderSlug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);

            // $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            /* if ($menu->is_combo == 1) {
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
            } */

            DB::table('order_requests')->where('id', $order->id)->delete();
        }

        $orders = DB::table('procurement_order_requests')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
        $orderResult = '';
        $subTotal = 0;
        $total = 0;
        $ordercount = 0;
        foreach ($orders as $order) {
            $orderResult .= '
                                    <div class="grid content-center w-full grid-cols-12 px-4 text-center h-14">
                                        
                                    <div class="flex items-center col-span-5 pr-2 text-xs font-semibold text-left">
                                            ' . $order->name . '
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="text-xl text-red-900 uil uil-minus"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2">
                                            <input type="text" data-slug="'.$order->slug.'" id="'.$order->id.'"  class="orderqty w-full text-center text-sm font-semibold border-0 h-7 focus:border-gray-500" value="' . $order->quantity . '">
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="text-xl uil uil-plus text-emerald-900"></i></button>
                                        </div>
                                        <div class="flex items-center justify-center col-span-2 text-sm font-semibold">
                                            '. number_format($order->current_stock,2) .'
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <button data-slug="' . $order->slug . '" data-name="' . $order->name . '" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="text-xl text-red-200 uil uil-times"></i></button>
                                        </div>
                                    </div>
                                ';
            $ordercount += $order->quantity;
            $subTotal = $subTotal + $order->total_price;
            $total = $total + $order->total_price;
            $amount = $total + $order->total_price;
        }

        if ($table > 1) {
            DB::table('departments')->where('id', $table)->update([
                'status' => 1
            ]);
        }

        $amount = $total;

        DB::table('discounts')->where('id', 1)->update([
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
            'amount' => $amount,
            'qty' => number_format($ordercount, 2, '.', ',')
        );

        echo json_encode($response);
    }

    

    public function forecast(Request $request) {

        $slug = $request->slug;
        $qty = $request->qty;

        $menus = db::table('inventories')->where('slug' , $slug)->first();
        $tprice = $menus->price * $qty;

        $reqCheck = db::table('procurement_order_requests')->where('slug' , $slug)->where('cashier' , auth()->id())->count();


        if ($reqCheck > 0){
            DB::table('procurement_order_requests')->where('slug' , $slug)->where('cashier' , auth()->id())->update([
                'quantity' => $qty,
                'total_price' => $tprice,

            ]);
        } else {  
            $ordReq = new ProcurementOrderRequest();
            $ordReq->inv_id = $menus->id;
            $ordReq->masterlist_id = $menus->masterlist_id;
            $ordReq->name = $menus->name;
            $ordReq->unit = $menus->unit;
            $ordReq->quantity = $qty;
            $ordReq->price = $menus->price;
            $ordReq->total_price =$tprice;
            $ordReq->current_stock = $menus->quantity;
            $ordReq->reorder_point = $menus->reorder_point;
            $ordReq->remarks = 'n/a';
            $ordReq->cashier = auth()->id();
            $ordReq->role = auth()->user()->role;
            $ordReq->department = $menus->department;
            $ordReq->branch = $menus->branch;
            $ordReq->slug = $slug;
            $ordReq->save();

            
        }
        

        

        $response = array(
            'name' => $menus->name,
            'reorder' => $menus->reorder_point,
            'qty' => $menus->quantity,
            'reqQty' => $qty,
            'reqQtyCount' => $reqCheck,
        );

        echo json_encode($response);
    }

    public function submit(Request $request) {
        
        $request->validate([
            'supplier' => 'required|string|max:255',
        ]);


        $total = 0;

        $ordreqs = db::table('procurement_order_requests')->where('cashier' , auth()->id())->get();
        foreach ($ordreqs as $order){
            $total += $order->total_price;
        }
        $id = ProcurementTransactionRequest::latest()->pluck('id')->first();
        if ($id == null) {
            $id = 1;
        } else {
            $id++;
        }
        $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

        $number = 'P' . date('mdY') . '-' . $nid;

        $tranSlug = Str::random(60);
        $ntranSlug = $tranSlug;
        $check_slug = DB::table('transaction_requests')->where('slug', $ntranSlug)->get();
        while (count($check_slug) > 0) {
            $ntranSlug = Str::random(60);
            $check_slug = DB::table('transaction_requests')->where('slug', $ntranSlug)->get();
        }
        $tranSlug = $ntranSlug;

        $tran = new ProcurementTransactionRequest();
        $tran->number = $number;
        $tran->supplier_name = $request->supplier;
        $tran->total = $total;
        $tran->status = 'PENDING';
        $tran->order_status = 'PENDING';
        $tran->order_remarks = '';
        $tran->cashier = auth()->id();
        $tran->slug = $tranSlug;
        $tran->save();

        $tran_id = $tran->id;

        foreach ($ordreqs as $order){
            $orderSlug = Str::random(60);
            $norderSlug = $orderSlug;
            $check_slug = DB::table('ordered_requests')->where('slug', $norderSlug)->get();
            while (count($check_slug) > 0) {
                $norderSlug = Str::random(60);
                $check_slug = DB::table('ordered_requests')->where('slug', $norderSlug)->get();
            }
            $orderSlug = $norderSlug;

            $ordReq = new ProcurementOrderedRequest();
            $ordReq->tran_id = $tran_id;
            $ordReq->inv_id = $order->inv_id;
            $ordReq->masterlist_id = $order->masterlist_id;
            $ordReq->name = $order->name;
            $ordReq->unit = $order->unit;
            $ordReq->quantity = $order->quantity;
            $ordReq->qty_approved = $order->quantity;
            $ordReq->qty_received = $order->quantity;
            $ordReq->price = $order->price;
            $ordReq->total_price =$order->total_price;
            $ordReq->current_stock = $order->current_stock;
            $ordReq->reorder_point = $order->reorder_point;
            $ordReq->remarks = $order->remarks;
            $ordReq->cashier = auth()->id();
            $ordReq->role = auth()->user()->role;
            $ordReq->department = $order->department;
            $ordReq->branch = $order->branch;
            $ordReq->slug = $orderSlug;
            $ordReq->save();

            DB::table('procurement_order_requests')->where('id', $order->id)->delete();
        }

        

        $response = array(
            'name' => $total,
            'tranid' => $tran_id ,
            'qty' => $total,
            'reqQty' => $total,
            'reqQtyCount' => $total,
        );

        echo json_encode($response);
    }

    public function appqty(Request $request) {

        $id = $request->id;
        $qty = $request->qty;

        $menus = db::table('procurement_ordered_requests')->where('id' , $id)->first();
        $tprice = $menus->price * $qty;

            DB::table('procurement_ordered_requests')->where('id' , $id)->update([
                'qty_approved' => $qty,
                'qty_received' => $qty,
                'total_price' => $tprice,

            ]);
        
        

        

        $response = array(
            'name' => $menus->name,
            'reorder' => $menus->reorder_point,
            'qty' => $menus->quantity,
            'reqQty' => $qty,
            'reqQtyCount' => $tprice,
        );

        echo json_encode($response);
    }
    public function recqty(Request $request) {

        $id = $request->id;
        $qty = $request->qty;

            DB::table('procurement_ordered_requests')->where('id' , $id)->update([
                'qty_received' => $qty,
            ]);
        $response = array(
            'name' => $id,
        );

        echo json_encode($response);
    }


    public function approved(Request $request) {

        $slug = $request->slug;
        $qty = $request->qty;

        $tran = db::table('procurement_transaction_requests')->where('slug' , $slug)->first();

        $ordered = db::table('procurement_ordered_requests')->where('tran_id' , $tran->id)->get();
        
        foreach($ordered as $order){
        //     $warehouse = db::table('inventories')->where('masterlist_id' , $order->masterlist_id)->where('department', 3)->first();

        //     $newQty = $warehouse->quantity - $order->qty_approved;
        //     DB::table('inventories')->where('masterlist_id' , $order->masterlist_id)->where('department', 3)->update([
        //         'quantity' => $newQty,
        //     ]);
           DB::table('procurement_ordered_requests')->where('id', $order->id)->update([
                'remarks' => 'APPROVED',
            ]);
            
        }

        DB::table('procurement_transaction_requests')->where('slug' , $slug)->update([
            'status' => 'APPROVED',
            'order_status' => 'APPROVED',

        ]);
        
        

        

        $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    }


    public function received(Request $request) {

        $slug = $request->slug;
        $qty = $request->qty;

        $tran = db::table('procurement_transaction_requests')->where('slug' , $slug)->first();

        $ordered = db::table('procurement_ordered_requests')->where('tran_id' , $tran->id)->get();
        
        foreach($ordered as $order){
            $inv = db::table('inventories')->where('id' , $order->inv_id)->where('branch', $order->branch)->first();

            $newQty = $inv->quantity + $order->qty_received;
            DB::table('inventories')->where('id' , $order->inv_id)->where('branch', $order->branch)->update([
                'quantity' => $newQty,
            ]);
            DB::table('procurement_ordered_requests')->where('id', $order->id)->update([
                'remarks' => 'RECEIVED',
            ]);
            
        }

        DB::table('procurement_transaction_requests')->where('slug' , $slug)->update([
            'order_status' => 'RECEIVED',

        ]);
        
        

        

        $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    }


    public function delete(Request $request)

    {
        $id = $request->input('id');
    
        DB::table('procurement_order_requests')->where('inv_id', $id)->delete();
    
        return response()->json(['status' => 'success']);
    }


    public function resetAll(Request $request)

    {
    
        DB::table('procurement_order_requests')->delete();
    
        return response()->json(['status' => 'success']);
    }


}
