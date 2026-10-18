<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class SupplierBiddingController extends Controller
{
    public function index(){
        $userId = auth()->id();

        $transactionIds = DB::table('bidding_ordered_requests as or')
        ->join('item_masterlist as im', 'or.inv_id', '=', 'im.inv_id')
        ->where('im.user_id', $userId)
        ->where('im.item_approval', '=', 'APPROVED')
        ->pluck('or.tran_id'); 

       $orderRequest = DB::table('bidding_transaction_requests as tr')
        ->select('tr.*', 'users.*', 'tr.slug as trSlug')
        ->leftJoin('users', 'tr.cashier', '=', 'users.id')
        ->join('bidding_ordered_requests as bor', 'bor.tran_id', '=', 'tr.id')
        ->where('tr.order_status', 'PENDING')
        ->whereIn('tr.id', $transactionIds)
        ->where('bor.is_bid', 0)
        ->where('bor.supplier_id', auth()->id())
        ->orderBy('tr.created_at', 'desc')
        ->distinct()
        ->paginate(100);

        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','PENDING')->get()->count();
        $page = 1;
        $search = "";
        
        $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
      

        return view('user.inventory.procurementbidding.index', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));

    }

    public function paginate($page){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
        ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
        ->where('bidding_transaction_requests.order_status','PENDING')
        ->orderBy('bidding_transaction_requests.id', 'asc')->paginate(100,'*','page',$page);
        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','PENDING')->get()->count();
        $search = "";
        return view('user.inventory.procurementbidding.index', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    public function search($page, $search){
        $orderRequest = DB::table('bidding_transaction_requests')
            ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
            ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
            ->where('bidding_transaction_requests.order_status','PENDING')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','PENDING')
            ->orderBy('bidding_transaction_requests.id', 'asc')
            ->paginate(100,'*','page',$page); 

          

        $requestcount = DB::table('bidding_transaction_requests')
            ->select('*')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','PENDING')
            ->orderBy('id', 'asc')
            ->count();

            $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.index', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }

    // Approved - Sir Charles Index
    public function approvedIndex(){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
        ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
        
        ->where('bidding_transaction_requests.auto_approve', '=', 1)
        ->where('bidding_transaction_requests.bidding_end', '<=', now())
        ->where('bidding_transaction_requests.order_status', 'PENDING')
        ->orderBy('bidding_transaction_requests.id', 'desc')->paginate(100);
        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','PENDING')->get()->count();

        $declinedRequest = DB::table('declined_transaction_requests')
        ->select('declined_transaction_requests.*','users.*','declined_transaction_requests.slug as drSlug')
        ->leftJoin('users','declined_transaction_requests.cashier','=','users.id')
        ->where('declined_transaction_requests.order_status','=', 'PENDING')
        ->orderBy('declined_transaction_requests.id','desc')
        ->paginate(100); 

        $page = 1;
        $search = "";
         $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.approved', compact('supplierlist','declinedRequest', 'orderRequest', 'requestcount', 'page', 'search'));
    }

    public function approvedPaginate($page){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
        ->leftJoin('users','transaction_requests.cashier','=','users.id')
        ->where('bidding_transaction_requests.bidding_end', '<=', now())
        ->orderBy('transaction_requests.id', 'asc')->paginate(100,'*','page',$page);
        $requestcount = DB::table('transaction_requests')->where('order_status','PENDING')->get()->count();
        $search = "";
        $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.approved', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }

    public function approvedSearch($page, $search){
        $orderRequest = DB::table('bidding_transaction_requests')
            ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
            ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('bidding_transaction_requests.bidding_end', '<=', now())
            ->orderBy('bidding_transaction_requests.id', 'asc')
            ->paginate(100,'*','page',$page);

        $requestcount = DB::table('bidding_transaction_requests')
            ->select('*')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','PENDING')
            ->orderBy('id', 'asc')
            ->count();

            $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();

        return view('user.inventory.procurementbidding.approved', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }


    //End of Approved

    
    public function receivedIndex(){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
        ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
        ->join('bidding_approved_requests','bidding_transaction_requests.id','=','bidding_approved_requests.tran_id')
        ->whereNotNull('bidding_approved_requests.rec_events')
        ->where('bidding_transaction_requests.order_received','RECEIVED')
        ->orderBy('bidding_transaction_requests.created_at', 'desc')->paginate(100);
        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','RECEIVED')->get()->count();
        $page = 1;
        $search = "";
         $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.completed', compact('supplierlist', 'orderRequest', 'requestcount', 'page', 'search'));
    }

    public function receivedPaginate($page){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
        ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
        ->where('bidding_transaction_requests.order_status','RECEIVED')
        ->orderBy('bidding_transaction_requests.id', 'asc')->paginate(100,'*','page',$page);
        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','RECEIVED')->get()->count();
        $search = "";
        return view('user.inventory.procurementbidding.completed', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    public function receivedSearch($page, $search){
        $orderRequest = DB::table('bidding_transaction_requests')
            ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
            ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','RECEIVED')
            ->orderBy('bidding_transaction_requests.id', 'asc')
            ->paginate(100,'*','page',$page);

        $requestcount = DB::table('bidding_transaction_requests')
            ->select('*')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','RECEIVED')
            ->orderBy('id', 'asc')
            ->count();
            $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.completed', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }

    // Commi Ordering Index
     public function orderingIndex(){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
        ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
        ->where('bidding_transaction_requests.order_status','APPROVED')
        ->where('bidding_transaction_requests.order_remarks','=', 'PENDING')
        ->orderBy('bidding_transaction_requests.created_at', 'desc')->paginate(100);

        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','APPROVED')->get()->count();

        $declinedRequest = DB::table('declined_transaction_requests')
        ->select('declined_transaction_requests.*','users.*','declined_transaction_requests.slug as drSlug')
        ->leftJoin('users','declined_transaction_requests.cashier','=','users.id')
        ->where('declined_transaction_requests.order_remarks','=', 'PENDING')
        ->orderBy('declined_transaction_requests.id','desc')
        ->paginate(100); 

        $page = 1;
        $search = "";
         $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.orderingIndex', compact('supplierlist', 'orderRequest', 'requestcount', 'page', 'search','declinedRequest'));
    }

    public function orderingPaginate($page){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
        ->leftJoin('users','transaction_requests.cashier','=','users.id')
        ->where('bidding_transaction_requests.order_status','APPROVED')
        ->where('bidding_transaction_requests.order_remarks','=', 'PENDING')
        ->orderBy('transaction_requests.id', 'asc')->paginate(100,'*','page',$page);
        $requestcount = DB::table('transaction_requests')->where('order_status','APPROVED')->get()->count();
        $search = "";
        $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.orderingIndex', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }

    public function orderingSearch($page, $search){
        $orderRequest = DB::table('bidding_transaction_requests')
            ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
            ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('bidding_transaction_requests.order_status','APPROVED')
            ->where('bidding_transaction_requests.order_remarks','=', 'PENDING')
            ->orderBy('bidding_transaction_requests.id', 'asc')
            ->paginate(100,'*','page',$page);

        $requestcount = DB::table('bidding_transaction_requests')
            ->select('*')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','APPROVED')
            ->orderBy('id', 'asc')
            ->count();

            $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();

        return view('user.inventory.procurementbidding.orderingIndex', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }

    public function deliveryIndex(){
        $userId = auth()->id();

        $transactionIds = DB::table('bidding_approved_requests')
        ->join('item_masterlist', 'bidding_approved_requests.inv_id', '=', 'item_masterlist.inv_id')
        ->where('item_masterlist.user_id', $userId)
        ->where('item_masterlist.item_approval', '=', 'APPROVED')
        ->where('bidding_approved_requests.go_to','=', 'DELIVERY')
        ->pluck('bidding_approved_requests.tran_id'); 

        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
        ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
        ->join('bidding_approved_requests','bidding_transaction_requests.id','=','bidding_approved_requests.tran_id')
        ->where('bidding_approved_requests.order_approval','Approved')
        ->where('bidding_approved_requests.go_to','DELIVERY')
        ->where('bidding_approved_requests.supplier_id', auth()->id())
        ->where('bidding_transaction_requests.order_remarks','ORDERED')
        ->whereIn('bidding_transaction_requests.id', $transactionIds)
        ->distinct()
        ->orderBy('bidding_transaction_requests.created_at', 'desc')->paginate(100);
        $requestcount = DB::table('bidding_transaction_requests')->where('order_remarks','ORDERED')->get()->count();
        $page = 1;
        $search = "";
        $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.deliveryIndex', compact('supplierlist', 'orderRequest', 'requestcount', 'page', 'search'));
    }

    public function deliveryPaginate($page){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('transaction_requests.*','users.*','transaction_requests.slug as trSlug')
        ->leftJoin('users','transaction_requests.cashier','=','users.id')
        ->where('bidding_transaction_requests.order_remarks','ORDERED')
        ->orderBy('transaction_requests.id', 'asc')->paginate(100,'*','page',$page);
        $requestcount = DB::table('transaction_requests')->where('order_status','APPROVED')->get()->count();
        $search = "";
        $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.deliveryIndex', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }

    public function deliverySearch($page, $search){
        $orderRequest = DB::table('bidding_transaction_requests')
            ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
            ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('bidding_transaction_requests.order_remarks','ORDERED')
            ->orderBy('bidding_transaction_requests.id', 'asc')
            ->paginate(100,'*','page',$page);

        $requestcount = DB::table('bidding_transaction_requests')
            ->select('*')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_remarks','ORDERED')
            ->orderBy('id', 'asc')
            ->count();

            $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();

        return view('user.inventory.procurementbidding.deliveryIndex', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }

    public function receivingIndex(){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug','bidding_transaction_requests.id as tr_id')
        ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
        ->join('bidding_approved_requests','bidding_transaction_requests.id','=','bidding_approved_requests.tran_id')
        ->whereNull('bidding_approved_requests.rec_events')
        ->where('bidding_approved_requests.go_to','=','RECEIVING')
        ->where('bidding_transaction_requests.order_status','APPROVED')
        ->orderBy('bidding_transaction_requests.created_at', 'desc')->distinct()->paginate(100);
        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','RECEIVED')->get()->count();


        $supplier = db::table('users')->get();


        $item = db::table('bidding_approved_requests')
        ->leftJoin('users','bidding_approved_requests.supplier_id','=','users.id')
        ->select('users.company_name','bidding_approved_requests.name','bidding_approved_requests.tran_id')
        ->get();

        $declinedRequest = DB::table('declined_transaction_requests')
        ->select('declined_transaction_requests.*','users.*','declined_transaction_requests.slug as drSlug')
        ->leftJoin('users','declined_transaction_requests.cashier','=','users.id')
        ->where('declined_transaction_requests.order_status','=', 'APPROVED')
        ->orderBy('declined_transaction_requests.id','desc')
        ->paginate(100); 
        $page = 1;
        $search = "";
         $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.receiving', compact('supplierlist', 'orderRequest', 'requestcount', 'page', 'search','declinedRequest','supplier','item'));
    }

    public function receivingPaginate($page){
        $orderRequest = DB::table('bidding_transaction_requests')
        ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
        ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
        ->where('bidding_transaction_requests.order_status','APPROVED')
        ->orderBy('bidding_transaction_requests.id', 'asc')->paginate(100,'*','page',$page);
        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','APPROVED')->get()->count();
        $search = "";
        return view('user.inventory.procurementbidding.receiving', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    public function receivingSearch($page, $search){
        $orderRequest = DB::table('bidding_transaction_requests')
            ->select('bidding_transaction_requests.*','users.*','bidding_transaction_requests.slug as trSlug')
            ->leftJoin('users','bidding_transaction_requests.cashier','=','users.id')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','APPROVED')
            ->orderBy('bidding_transaction_requests.id', 'asc')
            ->paginate(100,'*','page',$page);

        $requestcount = DB::table('bidding_transaction_requests')
            ->select('*')
            ->whereRaw("number LIKE '%{$search}%'")
            ->where('order_status','APPROVED')
            ->orderBy('id', 'asc')
            ->count();
            $supplierlist = DB::table('suppliers')->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        return view('user.inventory.procurementbidding.receiving', compact('supplierlist','orderRequest', 'requestcount', 'page', 'search'));
    }
    

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
    //Supplier Viewing
    public function edit($slug){

        $ordchecking = DB::table('procurement_order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('procurement_order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('bidding_transaction_requests')->where('slug',$slug)->first();

        

        $orders = DB::table('bidding_ordered_requests')->where('tran_id', $trans->id)->get();
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

        $userId = auth()->id();

        $requestTransaction = DB::table('bidding_transaction_requests as tr')
            ->leftJoin('bidding_ordered_requests as or', 'or.tran_id', '=', 'tr.id')
            ->select('or.name','or.quantity','or.brand_id','or.id as or_id')
            ->where('tr.slug', $slug)
            ->where('or.supplier_id', $userId)
            ->distinct()
            ->get();


        $reqNumber = DB::table('bidding_transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','PENDING')->get()->count();
        $itembrand =  DB::table('item_brands')->get();


        return view('user.inventory.procurementbidding.edit', compact('itembrand','requestTransaction','reqNumber','search','page','requestcount'));
    }
    
    public function received($slug){

        $ordchecking = DB::table('procurement_order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('procurement_order_approvals')->where('cashier', auth()->id())->delete();
        }


        $table = '';
        $checkifhave = DB::table('bidding_transaction_requests')->where('slug',$slug)->count();
        if($checkifhave > 0){
             $table = 'bidding_transaction_requests';
        }
        else{
            $table = 'declined_transaction_requests';
        }



        $trans = DB::table($table)->where('slug',$slug)->first();

        $orders = DB::table('bidding_ordered_requests')->where('tran_id', $trans->id)->get();
        $totalprice = 0;
     
    
      
        $gettrans = DB::table($table)->where('slug',$slug)->first();
        $gettransid =  $gettrans->id;

        if( $gettrans->auto_approve != 1) {
        $invIds = DB::table('bidding_ordered_requests')
        ->where('tran_id', $gettransid)
        ->pluck('inv_id')
        ->unique();

        foreach ($invIds as $invId) {
        $lowestRow = DB::table('bidding_ordered_requests')
        ->where('tran_id', $gettransid)
        ->where('inv_id', $invId)
        ->where('supplier_price', '!=', 0)
        ->orderBy('supplier_price', 'asc')
        ->first();

        if ($lowestRow) {
        DB::table('bidding_ordered_requests')
            ->where('id', $lowestRow->id)
            ->update(['order_approval' => 'Approved']);
        }    
        }
           DB::table($table)
            ->where('id', $gettransid)
            ->update(['auto_approve' => 1]);
        }       
        

        // $checkdecline = DB::table('bidding_ordered_requests')->where('tran_id', $gettransid)->where('order_event_status', '=', 'DECLINED')->distinct()->pluck('inv_id');
     
       
       $requestTransaction = DB::table("$table as tr")
        ->leftJoin('bidding_ordered_requests as or', 'or.tran_id', '=', 'tr.id')
        ->leftJoin('users as u', 'or.supplier_id', '=', 'u.id')
        ->leftJoin('item_brands as b', 'or.brand_id', '=', 'b.id')
        ->select('or.id','or.inv_id','or.name','or.quantity','or.current_stock','or.qty_approved','or.qty_received','u.id as supplier_id','u.company_name as supplier_name','b.id as brand_id','b.name as brand_name','or.supplier_price','or.supplier_total','or.slug','or.order_approval',)
        ->where('tr.slug', $slug)
        // ->whereIn('or.inv_id', $checkdecline)
       
        ->orderByRaw("CASE WHEN or.supplier_price = 0 THEN 1 ELSE 0 END, or.supplier_price ASC")
        ->get()
        ->groupBy('name');
        $reqNumber = DB::table($table)->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table($table)->where('order_status','PENDING')->get()->count();
        
        $itembrand = DB::table('item_brands')
        ->join('bidding_ordered_requests', 'item_brands.id', '=', 'bidding_ordered_requests.brand_id')
        ->where('bidding_ordered_requests.tran_id', $trans->id)
        ->select('item_brands.*')
        ->distinct()
        ->get();

        

        $approvedOrders = DB::table('bidding_ordered_requests')
        ->leftJoin('users', 'bidding_ordered_requests.supplier_id', '=', 'users.id')
        ->leftJoin('item_brands', 'bidding_ordered_requests.brand_id', '=', 'item_brands.id')
        ->select('users.name as supplier_name', 'item_brands.name as brand_name', 'bidding_ordered_requests.name', 'bidding_ordered_requests.qty_received', 'bidding_ordered_requests.supplier_price')
        ->where('bidding_ordered_requests.tran_id', $reqNumber->id)
        ->where('bidding_ordered_requests.order_approval', '=', 'Approved')
        
        ->get();



        return view('user.inventory.procurementbidding.received', compact('requestTransaction','reqNumber','search','page','requestcount','itembrand','approvedOrders'));
    }


    //Commi Viewing
    public function orderingview($slug){

        $ordchecking = DB::table('procurement_order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('procurement_order_approvals')->where('cashier', auth()->id())->delete();
        }
        $table = '';
        $checkifhave = DB::table('bidding_transaction_requests')->where('slug',$slug)->count();
        if($checkifhave > 0){
             $table = 'bidding_transaction_requests';
        }
        else{
            $table = 'declined_transaction_requests';
        }



        $trans = DB::table($table)->where('slug',$slug)->first();
        

        $orders = DB::table('bidding_ordered_requests')->where('tran_id', $trans->id)->get();
        $totalprice = 0;
        /* foreach ($orders as $order) {
            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            $totalprice = $menu->price * $order->quantity;

            DB::table('order_approvals')->insert([
                'menu_id' => $order->menu_id,
                'name' => $menu->name,
                'quantity' => $order->quantity,
                'price' => $menu->price,ORDERED
                'total_price' => $totalprice,
                'current_stock' => $menu->current_quantity,
                'type' => 0,
                'cashier' => auth()->id(),
                'slug' => $order->slug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);
        } */

        

       $orderingTransaction = DB::table($table)
            ->leftJoin('bidding_approved_requests', 'bidding_approved_requests.tran_id', '=', $table . '.id')
            ->leftJoin('users', 'bidding_approved_requests.supplier_id', '=', 'users.id')
            ->leftJoin('item_brands', 'bidding_approved_requests.brand_id', '=', 'item_brands.id')
            ->select('bidding_approved_requests.name','bidding_approved_requests.qty_received','bidding_approved_requests.brand_id','bidding_approved_requests.id as bidding_approved_requests_id','users.company_name as supplier_name', 'item_brands.name as brand_name','bidding_approved_requests.supplier_price','bidding_approved_requests.override_total','bidding_approved_requests.ord_events','bidding_approved_requests.ord_remarks','users.contact_person as contact_person','users.contact_person_no as contact_number','users.is_account as is_account')
            ->where($table . '.slug', $slug)
            ->where('bidding_approved_requests.order_approval', '=', 'Approved')
            ->distinct()
            ->get();


        $reqNumber = DB::table($table)->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table($table)->where('order_status','APPROVED')->get()->count();
        $itembrand =  DB::table('item_brands')->get();
        $ordering_events = DB::table('ordering_events')->get();

        $approvedOrders = DB::table('bidding_approved_requests')
        ->leftJoin('users', 'bidding_approved_requests.supplier_id', '=', 'users.id')
        ->leftJoin('item_brands', 'bidding_approved_requests.brand_id', '=', 'item_brands.id')
        ->leftJoin('ordering_events', 'bidding_approved_requests.ord_events', '=', 'ordering_events.id')
        ->select('users.name as supplier_name', 'item_brands.name as brand_name', 'bidding_approved_requests.name', 'bidding_approved_requests.qty_received', 'bidding_approved_requests.supplier_price','users.contact_person as contact_person','users.contact_person_no as contact_number', 'ordering_events.name as ord_name')
        ->where('bidding_approved_requests.tran_id', $reqNumber->id)
        ->where('bidding_approved_requests.order_approval', '=', 'Approved')
        
        ->get();
        



        return view('user.inventory.procurementbidding.orderingview', compact('itembrand','orderingTransaction','reqNumber','search','page','requestcount','ordering_events','approvedOrders'));
    }

    public function deliveryview($slug){

        $ordchecking = DB::table('procurement_order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('procurement_order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('bidding_transaction_requests')->where('slug',$slug)->first();

        

        $orders = DB::table('bidding_ordered_requests')->where('tran_id', $trans->id)->get();
        $totalprice = 0;
        /* foreach ($orders as $order) {
            $menu = DB::table('menus')->where('id', $order->menu_id)->first();

            $totalprice = $menu->price * $order->quantity;

            DB::table('order_approvals')->insert([
                'menu_id' => $order->menu_id,
                'name' => $menu->name,
                'quantity' => $order->quantity,
                'price' => $menu->price,ORDERED
                'total_price' => $totalprice,
                'current_stock' => $menu->current_quantity,
                'type' => 0,
                'cashier' => auth()->id(),
                'slug' => $order->slug,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s')
            ]);
        } */

        $userId = auth()->id();

       $deliveryTransaction = DB::table('bidding_transaction_requests')
            ->leftJoin('bidding_approved_requests', 'bidding_approved_requests.tran_id', '=', 'bidding_transaction_requests.id')
            ->leftJoin('users', 'bidding_approved_requests.supplier_id', '=', 'users.id')
            ->leftJoin('item_brands', 'bidding_approved_requests.brand_id', '=', 'item_brands.id')
            ->select('bidding_approved_requests.name','bidding_approved_requests.qty_received as qty_received','bidding_approved_requests.brand_id','bidding_approved_requests.id as bidding_approved_requests_id','users.company_name as supplier_name', 'item_brands.name as brand_name','bidding_approved_requests.supplier_price','bidding_approved_requests.override_total','bidding_approved_requests.supplier_quantity')
            ->where('bidding_transaction_requests.slug', $slug)
            ->where('bidding_approved_requests.order_approval', '=', 'Approved')
            ->where('bidding_approved_requests.supplier_id',$userId)
            ->distinct()
            ->get();


        $reqNumber = DB::table('bidding_transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','PENDING')->get()->count();
        $itembrand =  DB::table('item_brands')->get();

        $supplierId = auth()->id();
        $approvedOrders = DB::table('bidding_approved_requests')
        ->leftJoin('users', 'bidding_approved_requests.supplier_id', '=', 'users.id')
        ->leftJoin('item_brands', 'bidding_approved_requests.brand_id', '=', 'item_brands.id')
        ->select('users.company_name as supplier_name', 'item_brands.name as brand_name', 'bidding_approved_requests.name', 'bidding_approved_requests.qty_received', 'bidding_approved_requests.supplier_price', 'bidding_approved_requests.supplier_quantity')
        ->where('bidding_approved_requests.tran_id', $reqNumber->id)
        ->where('bidding_approved_requests.order_approval', '=', 'Approved')
        ->where('bidding_approved_requests.supplier_id', $supplierId)
        ->get();
        



        return view('user.inventory.procurementbidding.deliveryview', compact('itembrand','deliveryTransaction','reqNumber','search','page','requestcount','approvedOrders'));
    }

    public function receivingcompleted($slug){

        $ordchecking = DB::table('procurement_order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('procurement_order_approvals')->where('cashier', auth()->id())->delete();
        }
        
        $table = '';
        $checkifhave = DB::table('bidding_transaction_requests')->where('slug',$slug)->count();
        if($checkifhave > 0){
             $table = 'bidding_transaction_requests';
        }
        else{
            $table = 'declined_transaction_requests';
        }

        $trans = DB::table($table)->where('slug',$slug)->first();
       
        $orders = DB::table('bidding_approved_requests')->where('tran_id', $trans->id)->get();
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

        


        $requestTransaction = DB::table($table)
        ->leftJoin('bidding_approved_requests','bidding_approved_requests.tran_id', '=', $table . '.id')
        ->leftJoin('users', 'bidding_approved_requests.supplier_id', '=', 'users.id')
        ->leftJoin('item_brands', 'bidding_approved_requests.brand_id', '=', 'item_brands.id')
        ->leftJoin('receiving_events', 'bidding_approved_requests.rec_events', '=', 'receiving_events.id')
        ->select('users.company_name as supplier_name','bidding_approved_requests.name','bidding_approved_requests.quantity','bidding_approved_requests.qty_approved','bidding_approved_requests.qty_received','item_brands.name as brand_name','bidding_approved_requests.supplier_price','bidding_approved_requests.supplier_quantity','bidding_approved_requests.id as bar_id','bidding_approved_requests.receiving_confirm','bidding_approved_requests.go_to','bidding_approved_requests.ord_events', 'bidding_approved_requests.tran_id','bidding_approved_requests.slug as bar_slug')
        ->where('bidding_approved_requests.ord_events', '!=', '2')
        ->where($table . '.slug', $slug)->get();
        $reqNumber = DB::table($table)->where('slug',$slug)->first();

 
        $supplierId = auth()->id();

        $item = db::table('bidding_approved_requests')
        ->leftJoin('users','bidding_approved_requests.supplier_id','=','users.id')
        ->select('users.company_name','bidding_approved_requests.name','bidding_approved_requests.tran_id as tr_id','bidding_approved_requests.supplier_id')
        ->where('bidding_approved_requests.tran_id', $reqNumber->id) 
        ->where('bidding_approved_requests.supplier_id', $supplierId)
        ->get();

        $declinedRequest = DB::table('declined_transaction_requests')
        ->select('declined_transaction_requests.*','users.*','declined_transaction_requests.slug as drSlug')
        ->leftJoin('users','declined_transaction_requests.cashier','=','users.id')
        ->where('declined_transaction_requests.order_status','=', 'APPROVED')
        ->orderBy('declined_transaction_requests.id','desc')
        ->paginate(100);
        $search = '';
        $page = 1;

        $requestcount = DB::table($table)->where('order_status','APPROVED')->get()->count();


        $approvedOrders = DB::table('bidding_approved_requests')
        ->leftJoin('users', 'bidding_approved_requests.supplier_id', '=', 'users.id')
        ->leftJoin('item_brands', 'bidding_approved_requests.brand_id', '=', 'item_brands.id')
        ->select('users.company_name as supplier_name', 'item_brands.name as brand_name', 'bidding_approved_requests.name', 'bidding_approved_requests.receiving_quantity', 'bidding_approved_requests.supplier_price', 'bidding_approved_requests.supplier_quantity')
        ->where('bidding_approved_requests.tran_id', $reqNumber->id)
        ->where('bidding_approved_requests.order_approval', '=', 'Approved')
        ->where('bidding_approved_requests.supplier_id', $supplierId)
        ->where('bidding_approved_requests.ord_events', '!=', '2')
        ->get();

        $itembrand =  DB::table('item_brands')->get();
        $receiving_events = DB::table('receiving_events')->get();


        return view('user.inventory.procurementbidding.receivingcompleted', compact('requestTransaction','reqNumber','search','page','requestcount','receiving_events','declinedRequest','approvedOrders','item'));
    }


    



    public function receivedcompleted($slug){

        $ordchecking = DB::table('procurement_order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('procurement_order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('bidding_transaction_requests')->where('slug',$slug)->first();

        $orders = DB::table('bidding_ordered_requests')->where('tran_id', $trans->id)->get();
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



        $requestTransaction = DB::table('bidding_transaction_requests')
        ->leftJoin('bidding_approved_requests','bidding_approved_requests.tran_id', '=', 'bidding_transaction_requests.id')
        ->leftJoin('item_brands', 'bidding_approved_requests.brand_id', '=', 'item_brands.id')
        ->leftJoin('users', 'bidding_approved_requests.supplier_id', '=', 'users.id')
        ->select('item_brands.name as brand_name', 'bidding_approved_requests.name', 'bidding_approved_requests.receiving_quantity', 'bidding_approved_requests.supplier_price', 'bidding_approved_requests.supplier_quantity', 'bidding_approved_requests.qty_received', 'bidding_approved_requests.quantity','users.company_name as supplier_name')
        ->where('bidding_approved_requests.order_approval', 'Approved')
        ->where('bidding_transaction_requests.slug', $slug)->get();
        $reqNumber = DB::table('bidding_transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('bidding_transaction_requests')->where('order_status','PENDING')->get()->count();

        $itembrand =  DB::table('item_brands')->get();


        return view('user.inventory.procurementbidding.receivedcompleted', compact('requestTransaction','reqNumber','search','page','requestcount'));
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

        return redirect()->route('procurementtransfer.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function approve(Request $request){
        //$name = strtoupper($request->name);
        $slug = $request->slug;

        $transReq = DB::table('bidding_transaction_requests')->where('slug',$slug)->first();

        //$ord = DB::table('transaction_requests')->where('slug',$slug)->first();
       /*  $request->validate([
            'name' => ['required'],
        ]);
        */
        DB::table('bidding_transaction_requests')->where('slug', $transReq->slug)
            ->update([
                'order_status' => 'APPROVED',
            ]);

        DB::table('ordered_request')->where('tran_id', $transReq->id)
            ->update([
                'status' => 'APPROVED',
            ]); 

        return redirect()->route('procurementtransfer.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('tables')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('procurementtransfer.index')->withInput()->with('message', 'Successfully Deleted');
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


    public function showBySlug()
    {
        $item = DB::table('bidding_carts')->get();
    
        if (!$item) {
            return response()->json(['error' => 'Menu item not found'], 404);
        }
    
        return response()->json($item);
    }


      public function appqtyreceived(Request $request) {

        $id = $request->id;
        $qty = $request->qty;

        $menus = db::table('bidding_ordered_requests')->where('id' , $id)->first();
        $tprice = $menus->price * $qty;

            DB::table('bidding_ordered_requests')->where('id' , $id)->update([
                'qty_received' => $qty,
                'total_price' => $tprice,

            ]);
        }

        public function supplierqty(Request $request) {

        $id = $request->id;
        $qty = $request->qty;

        $menus = db::table('bidding_approved_requests')->where('id' , $id)->first();


            DB::table('bidding_approved_requests')->where('id' , $id)->update([
                'supplier_quantity' => $qty,

            ]);
        }

    public function updateStatus(Request $request)
    {
        DB::table('bidding_ordered_requests')
        ->where('tran_id', $request->id)
        ->where('inv_id', $request->inv_id)
        ->update([
            'order_approval' => 'Change Supplier / Brand',
            'updated_at'     => now(),
        ]);

    
        DB::table('bidding_ordered_requests')
        ->where('tran_id', $request->id)
        ->where('inv_id', $request->inv_id)
        ->where('supplier_id', $request->supplier_id)
        ->where('brand_id', $request->brand_id)
        ->update([
            'order_approval' => $request->order_approval,
            'updated_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'status'  => $request->order_approval
        ]);
    }

    

    

    

    

}
