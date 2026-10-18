<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class TransferController extends Controller
{
    public function index(){
        $orderRequest = DB::table('transaction_requests')
        ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
        ->leftJoin('users','transaction_requests.cashier','=','users.id')
        ->where('transaction_requests.order_status','PENDING')
        ->orderBy('transaction_requests.id', 'asc')->paginate(100);
        $requestcount = DB::table('transaction_requests')->where('order_status','PENDING')->get()->count();
        $page = 1;
        $search = "";
        return view('user.inventory.transfer.index', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    public function paginate($page){
        $orderRequest = DB::table('transaction_requests')
        ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
        ->leftJoin('users','transaction_requests.cashier','=','users.id')
        ->where('transaction_requests.order_status','PENDING')
        ->orderBy('transaction_requests.id', 'asc')->paginate(100,'*','page',$page);
        $requestcount = DB::table('transaction_requests')->where('order_status','PENDING')->get()->count();
        $search = "";
        return view('user.inventory.transfer.index', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    public function search($page, $search){
        $orderRequest = DB::table('transaction_requests')
            ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
            ->leftJoin('users','transaction_requests.cashier','=','users.id')
            ->where('transaction_requests.order_status','PENDING')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','PENDING')
            ->orderBy('transaction_requests.id', 'asc')
            ->paginate(100,'*','page',$page);

        $requestcount = DB::table('transaction_requests')
            ->select('*')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','PENDING')
            ->orderBy('id', 'asc')
            ->count();
        return view('user.inventory.transfer.index', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    // Approved
    public function approvedIndex(){
        $orderRequest = DB::table('transaction_requests')
        ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
        ->leftJoin('users','transaction_requests.cashier','=','users.id')
        ->where('transaction_requests.order_status','APPROVED')
        ->orderBy('transaction_requests.id', 'asc')->paginate(100);
        $requestcount = DB::table('transaction_requests')->where('order_status','APPROVED')->get()->count();
        $page = 1;
        $search = "";
        return view('user.inventory.transfer.approved', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    public function approvedPaginate($page){
        $orderRequest = DB::table('transaction_requests')
        ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
        ->leftJoin('users','transaction_requests.cashier','=','users.id')
        ->where('transaction_requests.order_status','APPROVED')
        ->orderBy('transaction_requests.id', 'asc')->paginate(100,'*','page',$page);
        $requestcount = DB::table('transaction_requests')->where('order_status','APPROVED')->get()->count();
        $search = "";
        return view('user.inventory.transfer.approved', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    public function approvedSearch($page, $search){
        $orderRequest = DB::table('transaction_requests')
            ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
            ->leftJoin('users','transaction_requests.cashier','=','users.id')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','APPROVED')
            ->orderBy('transaction_requests.id', 'asc')
            ->paginate(100,'*','page',$page);

        $requestcount = DB::table('transaction_requests')
            ->select('*')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','APPROVED')
            ->orderBy('id', 'asc')
            ->count();
        return view('user.inventory.transfer.approved', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    //End of Approved
    public function add(){
        return view('user.inventory.transfer.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $table = new Transfer();
        $table->name = $name;
        $table->slug = Str::random(60);
        $table->save();

        return redirect()->route('table.transfer')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){

        $ordchecking = DB::table('order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('transaction_requests')->where('slug',$slug)->first();

        $orders = DB::table('ordered_requests')->where('tran_id', $trans->id)->get();
        $totalprice = 0;
        /* foreach ($orders as $order) {
            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            $totalprice = $menu->price * $order->quantity;

            DB::table('order_approvals')->insert([
                'menu_id' => $order->menu_id,
                'name' => $menu->name,
                'quantity' => $order->quantity,
                'price' => $menu->price,
                'total_price' => $totalprice,
                'current_stock' => $menu->current_quantity,
                'type' => 0,
                'cashier' => auth()->id(),
                'slug' => $order->slug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);
        } */



        $requestTransaction = DB::table('transaction_requests as tr')
        ->leftJoin('ordered_requests as or','or.tran_id', '=', 'tr.id')
        ->where('tr.slug', $slug)->get();
        $reqNumber = DB::table('transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('transaction_requests')->where('order_status','PENDING')->get()->count();


        return view('user.inventory.transfer.edit', compact('requestTransaction','reqNumber','search','page','requestcount'));
    }

    public function received($slug){

        $ordchecking = DB::table('order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('transaction_requests')->where('slug',$slug)->first();

        $orders = DB::table('ordered_requests')->where('tran_id', $trans->id)->get();
        $totalprice = 0;
        /* foreach ($orders as $order) {
            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            $totalprice = $menu->price * $order->quantity;

            DB::table('order_approvals')->insert([
                'menu_id' => $order->menu_id,
                'name' => $menu->name,
                'quantity' => $order->quantity,
                'price' => $menu->price,
                'total_price' => $totalprice,
                'current_stock' => $menu->current_quantity,
                'type' => 0,
                'cashier' => auth()->id(),
                'slug' => $order->slug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);
        } */



        $requestTransaction = DB::table('transaction_requests as tr')
        ->leftJoin('ordered_requests as or','or.tran_id', '=', 'tr.id')
        ->where('tr.slug', $slug)->get();
        $reqNumber = DB::table('transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('transaction_requests')->where('order_status','PENDING')->get()->count();


        return view('user.inventory.transfer.received', compact('requestTransaction','reqNumber','search','page','requestcount'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('tables')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('transfer.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function approve(Request $request){
        //$name = strtoupper($request->name);
        $slug = $request->slug;

        $transReq = DB::table('transaction_requests')->where('slug',$slug)->first();

        //$ord = DB::table('transaction_requests')->where('slug',$slug)->first();
       /*  $request->validate([
            'name' => ['required'],
        ]);
        */
        DB::table('transaction_requests')->where('slug', $transReq->slug)
            ->update([
                'order_status' => 'APPROVED',
            ]);

        DB::table('ordered_request')->where('tran_id', $transReq->id)
            ->update([
                'status' => 'APPROVED',
            ]); 

        return redirect()->route('transfer.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('tables')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('transfer.index')->withInput()->with('message', 'Successfully Deleted');
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
                'current_stock' => $current_stock,
            ]);

            if ($menu->is_combo == 1) {
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
            }
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
        $discountRow = DB::table('discounts')->where('id', 1)->first();
        $discount = 0;
        

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
            'ordersCount' => $orders->count()
        );

        echo json_encode($response);
    }


    
}
