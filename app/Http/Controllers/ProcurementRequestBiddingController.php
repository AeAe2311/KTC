<?php

namespace App\Http\Controllers;

use App\Mail\MailNotify;
use App\Models\BiddingCart;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\OrderRequest;
use App\Models\ProcurementOrderedRequest;
use App\Models\BiddingOrderedRequest;
use App\Models\TransactionRequest;
use App\Models\BiddingTransactionRequest;
use App\Models\ProcurementTransactionRequest;
// use App\Models\ProcurementOrderRequest;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

 
class ProcurementRequestBiddingController extends Controller {
    
    public function index() {

        $userDepartment = auth()->user()->department;   
        $tables = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
       
        if (auth()->user()->role == 6)
            {
                $userDepartment = auth()->user()->department;          
                $menus = DB::table('inventories')
                    ->where('department',  '=', $userDepartment) 
                    ->whereIn('branch', [1, 2, 3])
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
                
            } 
        
        elseif (auth()->user()->role == 3)
            {
                $menus = DB::table('inventories')
                ->whereIn('department', [1,2])
                ->where('branch', 2)
                ->where('is_deleted', 0)
                ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                ->orderBy('name', 'asc')
                ->get();
            } 
        elseif (auth()->user()->role == 4 ||  auth()->user()->role == 9 )
            {
                if ($userDepartment == 1)
                    {
                    $menus = DB::table('inventories')
                    ->where('department', 1)
                    ->where('branch', 2)
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
                    }
                elseif ($userDepartment == 2)
                    {
                    $menus = DB::table('inventories')
                    ->where('department', 2)
                    ->where('branch', 2)
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
                    }
                elseif ($userDepartment == 3)
                    {
                    $menus = DB::table('inventories')
                    ->where('department', 3)
                    ->where('branch', 2)
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
                    }
                elseif ($userDepartment == 4)
                    {
                    $menus = DB::table('inventories')
                    ->where('department', 4)
                    ->where('branch', 2)
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
                    }
                elseif ($userDepartment == 5 )
                    {
                    $menus = DB::table('inventories')
                    ->where('department', 5)
                    ->where('branch', 2)
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
                    }
                elseif ($userDepartment == 6 )
                    {
                    $menus = DB::table('inventories')
                    ->where('department', 6)
                    ->where('branch', 2)
                    ->where('is_deleted', 0)
                    ->orderByRaw('CASE WHEN quantity <= reorder_point THEN 0 ELSE 1 END')
                    ->orderBy('name', 'asc')
                    ->get();
                    }
            } 


        else 
        {
            $menus = DB::table('inventories')->where('is_deleted', 0)
            ->orderByRaw('CASE WHEN quantity <= reorder_point  THEN 0 ELSE 1 END ')
            ->where('department', 6)
            ->orderBy('name', 'asc')
            ->get();
        }
        

        $categories = DB::table('categories')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();

        return view('user.inventory.procurementbidding.procurementrequest', compact('supplierlist','tables', 'orders', 'menus', 'categories' ));
    }

    public function procurementadd(Request $request) {
        $this->updateCurrentQuantity();
        $slug = $request->slug;
        $menu = DB::table('menus')->where('slug', $slug)->first();
        $ord = DB::table('bidding_carts')->where('menu_id', $menu->id)->where('cashier', auth()->id())->first();
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
                    DB::table('bidding_carts')->where('id', $ord->id)->delete();

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

        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
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
        $corder = DB::table('bidding_carts')->where('slug', $slug)->first();

        $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
        $SCRow = DB::table('service_charge')->where('id', 1)->first();
        $SCPercent = $SCRow->percent;
        $notif = '';

        
        if ($qty <= $menu->quantity) {
            $quantity = $qty;
            $total_price = $corder->price * $quantity;
            $current_stock = $menu->quantity;
            DB::table('bidding_carts')->where('slug', $slug)->update([
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

        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
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
        $corder = DB::table('bidding_carts')->where('slug', $slug)->first();

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
            DB::table('bidding_carts')->where('slug', $slug)->update([
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

        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
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
        $corder = DB::table('bidding_carts')->where('slug', $slug)->first();
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
            DB::table('bidding_cart')->where('slug', $slug)->update([
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

        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
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
        $corder = DB::table('bidding_carts')->where('slug', $slug)->first();
        if ($checkbox == "true"){
            DB::table('bidding_carts')->where('slug', $slug)->update([
                'type' => 1,
            ]);
        } else {
            $menu = DB::table('menus')->where('id', $corder->menu_id)->first();
            $total_price = $menu->price * $corder->quantity;
            DB::table('bidding_carts')->where('slug', $slug)->update([
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
            

        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
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
        $ord = DB::table('bidding_carts')->where('slug', $slug)->first();
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

        DB::table('bidding_carts')->where('slug', $slug)->delete();

        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
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

        


        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->get();
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

        $orders = DB::table('bidding_carts')->where('cashier', auth()->id())->orderBy('id', 'desc')->get();
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

        $dateTime = $request->delivery_datetime;
        $dateTimeFormatted = $dateTime ? Carbon::parse($dateTime)->format('Y-m-d H:i:s') : now();

        $menus = db::table('inventories')->where('slug' , $slug)->first(); 

        $pricing = $menus->price ?? 0;
        $tprice = $pricing * $qty;

        /* $reqCheck = db::table('bidding_carts')->where('slug' , $slug)->where('cashier' , auth()->id())->count(); */
        $reqCheck = DB::table('bidding_carts')
            ->where('inv_id', $menus->id)
            ->where('cashier', auth()->id())
            ->exists();


        if ($reqCheck > 0){
            DB::table('bidding_carts')->where('inv_id', $menus->id)->where('cashier' , auth()->id())->update([
                'quantity' => $qty,
                'total_price' => $tprice,
                'delivery_datetime'   => $dateTimeFormatted

            ]);
        } else {  
            $ordReq = new BiddingCart();
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
            $ordReq->brand_id = $menus->brand_id;
            $ordReq->delivery_datetime = $dateTimeFormatted;
            $ordReq->bidding_end = now()->addDay();
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
        
        // $request->validate([
        //     'supplier' => 'required|string|max:255',
        // ]);


        $total = 0;

        // $ordreqs = db::table('bidding_carts')->where('cashier' , auth()->id())->get();

        $ordreqs = DB::table('bidding_carts')
            ->join('item_masterlist', 'bidding_carts.inv_id', '=', 'item_masterlist.inv_id')
            ->where('bidding_carts.cashier', auth()->id())
            ->select(
                'bidding_carts.*',
                'item_masterlist.user_id',
                'item_masterlist.brand_id'
            )
            ->get();

        foreach ($ordreqs as $order){
            $total += $order->total_price;
        }
        $id = BiddingTransactionRequest::latest()->pluck('id')->first();
        if ($id == null) {
            $id = 1;
        } else {
            $id++;
        }
        $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

        $number = 'P' . date('mdY') . '-' . $nid;

        $deliveryDatetime = $ordreqs->first()->delivery_datetime ?? null;

        $tranSlug = Str::random(60);
        $ntranSlug = $tranSlug;
        $check_slug = DB::table('transaction_requests')->where('slug', $ntranSlug)->get();
        while (count($check_slug) > 0) {
            $ntranSlug = Str::random(60);
            $check_slug = DB::table('transaction_requests')->where('slug', $ntranSlug)->get();
        }
        $tranSlug = $ntranSlug;

        $tran = new BiddingTransactionRequest();
        $tran->number = $number;
        $tran->supplier_name = '';
        $tran->total = $total;
        $tran->status = 'PENDING';
        $tran->order_status = 'PENDING';
        $tran->order_remarks = 'PENDING';
        $tran->cashier = auth()->id();
        $tran->slug = $tranSlug;
        $tran->delivery_datetime = $deliveryDatetime;
        $tran->bidding_end = now()->addDay();
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

            $ordReq = new BiddingOrderedRequest();
            $ordReq->tran_id = $tran_id;
            $ordReq->inv_id = $order->inv_id;
            $ordReq->masterlist_id = $order->masterlist_id;
            $ordReq->name = $order->name;
            $ordReq->unit = $order->unit;
            $ordReq->quantity = $order->quantity;
            $ordReq->qty_approved = $order->quantity;
            $ordReq->qty_received = $order->quantity;
            $ordReq->supplier_quantity = $order->quantity;
            $ordReq->receiving_quantity = $order->quantity;
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
            $ordReq->supplier_id = $order->user_id; 
            $ordReq->brand_id = $order->brand_id; 
            $ordReq->supplier_price = $order->supplier_price ?? 0;
            $ordReq->supplier_total = $order->supplier_total ?? 0;
            $ordReq->delivery_datetime = $order->delivery_datetime;
            $ordReq->bidding_end = $order->bidding_end;
            $ordReq->save();

            DB::table('bidding_carts')->where('id', $order->id)->delete();
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

    public function price(Request $request) {

        $id = $request->id;
        $supp_price = $request->supp_price;
        $qty = $request->qty;
        


        $totalprice = $qty * $supp_price;
        // $menus = db::table('item_masterlist')->where('id' , $id)->first();
        // // $tprice = $menus->price * $qty;

        //     DB::table('item_masterlist')->where('id' , $id)->update([
        //         'supplier_price' => $supp_price
        //         // 'qty_received' => $qty,
        //         // 'total_price' => $tprice,
        //     ]);


             DB::table('bidding_ordered_requests')
             ->where('id',$id)->update([ 
                'supplier_price' => $supp_price,
                'supplier_total' => $totalprice
                // 'qty_received' => $qty,
                // 'total_price' => $tprice,
            ]);

            

        $response = array(
            // 'name' => $menus->name,
            // 'reorder' => $menus->reorder_point,
            //  'qty' => $menus->quantity,
            // 'reqQty' => $qty,
            //  'reqQtyCount' => $tprice,
        );

        echo json_encode($response);
    }

    
    public function recqty(Request $request) {

        $id = $request->id;
        $qty = $request->qty;

            DB::table('bidding_ordered_requests')->where('id' , $id)->update([
                'qty_received' => $qty,
            ]);
        $response = array(
            'name' => $id,
        );

        echo json_encode($response);
    }

        // SUPPLIER BIDDING
    public function approved(Request $request) {

        $slug = $request->slug;
        $qty = $request->qty;

        $tran = db::table('bidding_transaction_requests')->where('slug' , $slug)->first();

        $supplierId = auth()->id();


        $noBidding = DB::table('bidding_ordered_requests')
        ->where('tran_id', $tran->id)
        ->where('is_bid', 0)
        ->whereNotNull('bidding_end')
        ->where('bidding_end', '<=', now())
        ->update([
        'supplier_price' => 0.00,
        'supplier_total' => 0.00
        ]);
        
        // foreach ($noBidding as $order) {
        //     // DB::table('bidding_ordered_requests')
        //     //     ->where('tran_id',$tran->id)
        //     //     ->update(['supplier_approved' => 1]);

        //     DB::table('bidding_price')->insert([
        //         'tran_id'               => $tran->id,
        //         'inv_id'                => $order->inv_id,
        //         'name'                  => $order->name,
        //         'quantity'              => $order->quantity,
        //         'supplier_price'        => $order->supplier_price,
        //         'supplier_total_amount' => $order->supplier_price * $order->quantity,
        //         'remarks'               => 'NO RESPONSE / BIDDING',
        //         'supplier_id'           => $order->supplier_id,
        //         'slug'                  => $order->slug,
        //         'brand_id'              => $order->brand_id,
        //         'created_at'            => now(),
        //         'updated_at'            => now(),
        //     ]);
        // }
        
        $approved = DB::table('bidding_ordered_requests')
        ->where('supplier_id', $supplierId)
        ->where('tran_id',$tran->id)
        ->get();

        DB::table('bidding_ordered_requests')
        ->where('tran_id',$tran->id)
        ->where('supplier_id', $supplierId)->update([
            'is_bid'=> '1',
        ]);
        
        // foreach($approved as $order){
        //     $warehouse = db::table('inventories')->where('masterlist_id' , $order->masterlist_id)->where('department', 3)->first();

        //     $newQty = $warehouse->quantity - $order->qty_approved;
        //     DB::table('inventories')->where('masterlist_id' , $order->masterlist_id)->where('department', 3)->update([
        //         'quantity' => $newQty,
        //     ]);
        //    DB::table('bidding_ordered_requests')->where('id', $order->id)->update([
        //         'remarks' => 'APPROVED',
        //     ]);
            
        // }

        // DB::table('bidding_transaction_requests')->where('slug' , $slug)->update([
        //     'status' => 'APPROVED',
        //     'order_status' => 'APPROVED',

        // ]);

        // $PendingSupplierApproval = DB::table('bidding_ordered_requests')
        // ->where('tran_id', $tran->id)
        // ->where(function($q) {
        //     $q->where('supplier_approved', 0)
        //       ->orWhereNull('supplier_approved');
        // })
        // ->exists();

        
        // if (!$PendingSupplierApproval) {
        //     DB::table('bidding_transaction_requests')
        //         ->where('id', $tran->id)
        //         ->update([
        //             'status' => 'APPROVED',
        //             'order_status' => 'APPROVED',
        //         ]);
        // }


        
        $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    }

        // SIR CHARLES APPROVAL
    public function received(Request $request) {

        $slug = $request->slug;
        $qty = $request->qty;
        $order_remarks = $request->order_remarks;
        $overall_remarks = $request->overall_remarks;

         
        $table = '';
        $checkifhave = DB::table('bidding_transaction_requests')->where('slug',$slug)->count();
        if($checkifhave > 0){
             $table = 'bidding_transaction_requests';
        }
        else{
            $table = 'declined_transaction_requests';
        }



        $tran = db::table($table)->where('slug' , $slug)->first();

        $ordered = db::table('bidding_ordered_requests')->where('tran_id' , $tran->id)
        ->get();

        
        foreach($ordered as $order){
            if ($order->order_approval === 'Approved' && $order->order_event_status !== 'TRANSFER') {
            DB::table('bidding_approved_requests')->insert([
            'bidding_ordered_requests_id' => $order->id,
            'tran_id'               => $tran->id,
            'inv_id'                => $order->inv_id,
            'name'                  => $order->name,
            'quantity'              => $order->quantity,
            'qty_received'          => $order->qty_received,
            'qty_approved'          => $order->qty_approved,
            'supplier_quantity'     => $order->supplier_quantity,
            'receiving_quantity'    => $order->receiving_quantity,
            'supplier_price'        => $order->supplier_price,
            'supplier_total'        => $order->supplier_total,
            'override_total'        => $order->supplier_price * $order->qty_received,
            'supplier_id'           => $order->supplier_id,
            'cashier'               => auth()->id(),
            'role'                  => $order->role,
            'department'            => $order->department,
            'branch'                => $order->branch,
            'slug'                  => $order->slug,
            'brand_id'              => $order->brand_id,
            'is_bid'                => $order->is_bid,
            'delivery_datetime'     => $order->delivery_datetime,
            'bidding_end'           => $order->bidding_end,
            'order_approval'        => $order->order_approval, 
            'order_remarks'         => $order_remarks,
            'overall_remarks'       => $overall_remarks,
            'created_at'            => now(),
            'updated_at'            => now(),
            ]);

            DB::table('bidding_ordered_requests')
                ->where('id', $order->id)
                ->update([
                    'order_remarks' => $order_remarks,
                    'overall_remarks' => $overall_remarks,
                    'order_event_status' => 'TRANSFER',
                    'updated_at'    => now(),
                ]);

        }
        }
        
        // foreach($ordered as $order){
        //     $inv = db::table('inventories')->where('id' , $order->inv_id)->where('branch', $order->branch)->first();

        //     $newQty = $inv->quantity + $order->qty_received;
        //     DB::table('inventories')->where('id' , $order->inv_id)->where('branch', $order->branch)->update([
        //         'quantity' => $newQty,
        //     ]);
        //     DB::table('bidding_ordered_requests')->where('id', $order->id)->update([
        //         'remarks' => 'RECEIVED',
        //     ]);
            
        // }

        DB::table($table)->where('slug' , $slug)->update([
            'order_status' => 'APPROVED',
            'updated_at'    => now(),
        ]);
        
        

        

        $response = array(
            'name' => $slug,
            'order_remarks' => $order_remarks,
        );

        echo json_encode($response);

    }

    public function orders(Request $request) {


        $id = $request->id;
        $slug = $request->slug;
        $qty = $request->qty;
        $ord_events = $request->ord_events;
        $ord_remarks = $request->ord_remarks;


        $tran = db::table('bidding_transaction_requests')->where('slug' , $slug)->first();

        $supplierId = auth()->id();


        DB::table('bidding_approved_requests')
        ->where('id', $id)
        ->update([
        'ord_events' => $ord_events,
        'updated_at'    => now(),
        ]);


        $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    }

    public function ordersremarks(Request $request) {


        $id = $request->id;
        // $slug = $request->slug;
        $qty = $request->qty;
        $ord_remarks = $request->ord_remarks;
        


        // $tran = db::table('bidding_transaction_requests')->where('slug' , $slug)->first();

        // $supplierId = auth()->id();


        DB::table('bidding_approved_requests')
        ->where('id', $id)
        ->update([
        'ord_remarks' => $ord_remarks,
        'updated_at'    => now(),
        ]);


        $response = array(
            'id' => $id,
        );

        echo json_encode($response);

    }


    // ORDERING SUBMIT 
    public function ordersconfirm(Request $request) {
 
        $id = $request->id;
        $slug = $request->slug;
        $ord_overall_remarks = $request->ord_overall_remarks;


        DB::table('bidding_transaction_requests')->where('slug' , $slug)->update([
            'order_remarks' => 'ORDERED',
        ]);

      

        $table = '';
        $value_togo = '';
        $checkifhave = DB::table('bidding_transaction_requests')->where('slug',$slug)->count();
        if($checkifhave > 0){
             $table = 'bidding_transaction_requests';
        }
        else{
            $table = 'declined_transaction_requests';
        }


        $gettr = db::table($table)->where('slug' , $slug)->first();

        $getuser = db::table('bidding_approved_requests')->where('tran_id',  $gettr->id)->get();
        foreach($getuser as $user){
        $userchecking = Db::table('users')->where('id',$user->supplier_id)->first();
            if($userchecking->is_account === 1){
                $value_togo = "DELIVERY";
               
            }else{
                $value_togo = "RECEIVING";
                 
            }
              Db::table('bidding_approved_requests')->where('id',$user->id)->update(['go_to' =>  $value_togo]);
           
        }

        
        DB::table('bidding_approved_requests')
        ->where('tran_id', $gettr->id)
        ->update([
        'ord_overall_remarks' => $ord_overall_remarks,
        'updated_at'    => now(),
        ]);



        
        
        $getordeclined = DB::table('bidding_approved_requests')->where('tran_id', $gettr->id)->where('ord_events', '2')->get();
         if ($getordeclined->count() > 0) {

        $declined_id = DB::table('declined_transaction_requests')
        ->latest('id')  
        ->value('id'); 

        if ($declined_id == null) {
            $declined_id = 1;
        } else {
            $declined_id++;
        }
        
        $newdeclinedid = str_pad($declined_id, 3, '0', STR_PAD_LEFT);

        $decnumber = $gettr->number . '-'. 'D' . $newdeclinedid;




        $tranSlug = Str::random(60);
        $ntranSlug = $tranSlug;
        $check_slug = DB::table('bidding_ordered_requests')->where('slug', $ntranSlug)->get();
        while (count($check_slug) > 0) {
            $ntranSlug = Str::random(60);
            $check_slug = DB::table('bidding_ordered_requests')->where('slug', $ntranSlug)->get();
        }
        $tranSlug = $ntranSlug;

        
           $declinewdID =  DB::table('declined_transaction_requests')->insertGetId([
            'number'   => $decnumber,
            'previous_transaction_number'   =>  $gettr->number,
            'supplier_name'   => $gettr->supplier_name,
            'total'   => $gettr->total,      
            'status'   => 'PENDING',
            'order_status'    => 'PENDING',
            'order_remarks'   => 'PENDING ',
            'order_delivery'   => $gettr->order_delivery,
            'cashier'   => $gettr->cashier,
            'slug'   =>   $tranSlug,
            'delivery_datetime'    => $gettr->delivery_datetime,
            'bidding_end'   => $gettr->bidding_end,
            'auto_approve'   => $gettr->auto_approve,
            'created_at'   => $gettr->created_at,
            'updated_at'   => $gettr->updated_at,
        ]);

        
        $getordeclined2 = DB::table('bidding_approved_requests')->where('tran_id', $gettr->id)->where('ord_events', '2')->get();
       $getremaining = DB::table('bidding_ordered_requests')
        ->where('tran_id', $gettr->id)
        ->whereIn('inv_id', $getordeclined2->pluck('inv_id')) // include only matching inv_id
        ->whereNotIn('id', $getordeclined2->pluck('bidding_ordered_requests_id')) // exclude by IDs
        ->get();
        foreach($getremaining as $remaining){
            DB::table('bidding_ordered_requests')->insert([
                'tran_id'            => $declinewdID,
                'inv_id'             => $remaining->inv_id,
                'masterlist_id'      => $remaining->masterlist_id,
                'name'               => $remaining->name,
                'unit'               => $remaining->unit,
                'quantity'           => $remaining->quantity,
                'qty_approved'       => $remaining->qty_approved,
                'qty_received'       => $remaining->qty_received,
                'supplier_quantity'  => $remaining->supplier_quantity,
                'receiving_quantity' => $remaining->receiving_quantity,
                'price'              => $remaining->price,
                'total_price'        => $remaining->total_price,
                'current_stock'      => $remaining->current_stock,
                'reorder_point'      => $remaining->reorder_point,
                'remarks'            => $remaining->remarks,
                'cashier'            => $remaining->cashier,
                'role'               => $remaining->role,
                'department'         => $remaining->department,
                'branch'             => $remaining->branch,
                'slug'               => $remaining->slug,
                'supplier_price'     => $remaining->supplier_price,
                'supplier_total'     => $remaining->supplier_total,
                'supplier_id'        => $remaining->supplier_id,
                'is_bid'             => $remaining->is_bid,
                'brand_id'           => $remaining->brand_id,
                'delivery_datetime'  => $remaining->delivery_datetime,
                'bidding_end'        => $remaining->bidding_end,
                'order_approval'     => $remaining->order_approval,
                'order_remarks'      => $remaining->order_remarks,
                'overall_remarks'    => $remaining->overall_remarks,
                'order_event_status' => $remaining->order_event_status,
                'created_at'         => $remaining->created_at,
                'updated_at'         => $remaining->updated_at,
            ]);
        
    
     }
    }

        DB::table($table)->where('slug' , $slug)->update([
            'order_remarks' => 'ORDERED',
        ]);
     
          $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    
}


    public function delivery(Request $request) {

        $id = $request->id;
        $slug = $request->slug;
        $qty = $request->qty;
        $supplier_remarks = $request->supplier_remarks;
        $supplier_overall_remarks = $request->supplier_overall_remarks;

        $tran = db::table('bidding_transaction_requests')->where('slug' , $slug)->first();

        $supplierId = auth()->id();

        
        DB::table('bidding_approved_requests')
        ->where('id', $id)
        ->where('supplier_id',$supplierId)
        ->update([
        'go_to' => 'RECEIVING',
        'supplier_remarks' => $supplier_remarks,
        'supplier_overall_remarks' => $supplier_overall_remarks,
        'updated_at'    => now(),
        ]);
       


        
        $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    }

    public function deliveryremarks(Request $request) {


        $slug = $request->slug;
        $qty = $request->qty;
        $supplier_overall_remarks = $request->supplier_overall_remarks;

        $tran = db::table('bidding_transaction_requests')->where('slug' , $slug)->first();

        $supplierId = auth()->id();

        
        DB::table('bidding_approved_requests')
        ->where('tran_id', $tran->id)
        ->where('supplier_id',$supplierId)
        ->update([
        'go_to' => 'RECEIVING',
        'supplier_overall_remarks' => $supplier_overall_remarks,
        'updated_at'    => now(),
        ]);
       


        
        $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    }

    public function receivingqty(Request $request) {

        $id = $request->id;
        $qty = $request->qty;

            DB::table('bidding_approved_requests')->where('id' , $id)->update([
                'receiving_quantity' => $qty,
            ]);
        $response = array(
            'name' => $id,
        );

        echo json_encode($response);
    }

    public function receiving(Request $request) {


        $id = $request->id;
        $slug = $request->slug;
        $qty = $request->qty;
        $rec_events = $request->rec_events;

        $table = '';
        $checkifhave = DB::table('bidding_transaction_requests')->where('slug',$slug)->count();
        if($checkifhave > 0){
             $table = 'bidding_transaction_requests';
        }
        else{
            $table = 'declined_transaction_requests';
        }

        $tran = db::table($table)->where('slug' , $slug)->first();


        $ordered = DB::table('bidding_approved_requests')->where('tran_id', $tran->id)->where('ord_events','!=', '2')->whereNull('receiving_confirm')->get();
        foreach($ordered as $order){
            $inv = db::table('inventories')->where('id' , $order->inv_id)->first();

            $newQty = $inv->quantity + $order->receiving_quantity;
            DB::table('inventories')->where('id' , $order->inv_id)->update([
                'quantity' => $newQty,
            ]);   
        }


        DB::table('bidding_approved_requests')
        ->where('id', $id)
        ->update([
        'rec_events' => $rec_events,
        'updated_at'    => now(),
        ]);

        DB::table($table)
        ->where('slug' , $slug)
        ->update([
        'order_received' => 'RECEIVED',
        ]);


        $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    }
    public function receivingremarks(Request $request) {


        $id = $request->id;
        $slug = $request->slug;
        $qty = $request->qty;
        $rec_remarks = $request->rec_remarks;


        $tran = db::table('bidding_transaction_requests')->where('slug' , $slug)->first();

        $supplierId = auth()->id();


        DB::table('bidding_approved_requests')
        ->where('id', $id)
        ->update([
        'rec_remarks' => $rec_remarks,
        'updated_at'    => now(),
        ]);


        $response = array(
            'name' => $slug,
        );

        echo json_encode($response);

    }

    public function receivingconfirm(Request $request) {

        $id = $request->id;
        $tranid = $request->tranid;
 

        $table = '';
        $checkifhave = DB::table('bidding_transaction_requests')->where('id',$tranid)->count();
        if($checkifhave > 0){
             $table = 'bidding_transaction_requests';
        }
        else{
            $table = 'declined_transaction_requests';
        }

        $tran = db::table($table)->where('id' , $tranid)->first();

        $ordered = DB::table('bidding_approved_requests')->where('tran_id', $tran->id)->where('ord_events','!=', '2')->get();
        foreach($ordered as $order){
            $inv = db::table('inventories')->where('id' , $order->inv_id)->first();

            $newQty = $inv->quantity + $order->receiving_quantity;
            DB::table('inventories')->where('id' , $order->inv_id)->update([
                'quantity' => $newQty,
            ]);   
        }
        // dd( $table, $tran->id,  $ordered );


        DB::table('bidding_approved_requests')
        ->where('id', $id)
        ->update([
        'receiving_confirm' => 1,
        ]);


        $response = 'success';

        echo json_encode($response);

    }


    

    public function delete(Request $request)

    {
        $id = $request->input('id');
    
        DB::table('bidding_carts')->where('inv_id', $id)->delete();
    
        return response()->json(['status' => 'success']);
    }


    public function resetAll(Request $request)

    {
    
        DB::table('bidding_carts')->delete();
    
        return response()->json(['status' => 'success']);
    }


}
