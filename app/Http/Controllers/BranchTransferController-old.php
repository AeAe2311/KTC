<?php

namespace App\Http\Controllers;

use App\Mail\MailNotify;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\OrderRequest;
use App\Models\BranchOrderRequest;
use App\Models\OrderedRequest;
use App\Models\BranchRequest;
use App\Models\Transaction;
use App\Models\TransactionRequest;
use App\Models\BranchTransactionRequest;
use App\Models\Log;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Models\Transfer;



 
class BranchTransferController extends Controller {

    // DAHON API
    public function branchRequest()
    {
        $apiLink = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/apiInv";


        $response = Http::get($apiLink);

        if (!$response->successful()) {
            return redirect()->back()->with('error', 'Failed to fetch data from API.');
        }

        $data = $response->json(); 

        $apimenus = collect($data['menus']);
        $categories = collect($data['categories'])->pluck('name', 'id');
        $collectCat = $data['categories'];
        $apiMsg = "Wrong Item Code!";
        $user = auth()->user(); 

        // Role-specific filtering
        if ($user->role == 3) {
            $apimenus = $apimenus->whereIn('department', [1, 2])
                ->sortBy([['department', 'asc'], ['id', 'asc']])
                ->values();

            $dahonMenu = DB::table('inventories')
                ->whereNotNull('item_code')
                ->where('is_deleted', 0)
                ->whereIn('department', [1, 2])
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('item_code'); 

        } elseif ($user->role == 4) {
            $apimenus = $apimenus->where('department', $user->department)
                ->sortBy([['department', 'asc'], ['id', 'asc']])
                ->values();

            $dahonMenu = DB::table('inventories')
                ->whereNotNull('item_code')
                ->where('is_deleted', 0)
                ->where('department', $user->department)
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('item_code'); 
        } else {
            $dahonMenu = DB::table('inventories')
                ->whereNotNull('item_code')
                ->where('is_deleted', 0)
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('item_code'); 
        }

        // Enhance apimenus with category and quantity
        $apimenus = $apimenus->map(function ($menu) use ($categories, $dahonMenu) {
            $menu['category'] = $categories[$menu['category_id']] ?? 'Unknown';
            $menu['quantity'] = $dahonMenu[$menu['item_code']]->quantity ?? 0;
            return $menu;
        });

        return view('user.inventory.branch_transfer.branch-request', compact('apimenus', 'collectCat', 'dahonMenu', 'apiMsg', 'user'));
    }

    public function ApiForecast(Request $request) {

        $slug = $request->slug;
        $qty = $request->qty;

        $menus = db::table('inventories')->where('item_code' , $slug)->first();
        $menuprice = (!empty($menus->price)) ? $menus->price : 0;
        $tprice =  $menuprice * $qty;

        $reqCheck = db::table('branch_order_requests')->where('slug' , $slug)->where('cashier' , auth()->id())->count();


        if ($reqCheck > 0){
            DB::table('branch_order_requests')->where('slug' , $slug)->where('cashier' , auth()->id())->update([
                'quantity' => $qty,
                'total_price' => $tprice,

            ]);
        } else {

            $ordReq = new BranchOrderRequest();
            $ordReq->inv_id = $menus->id;
            $ordReq->item_code = $menus->item_code;
            $ordReq->masterlist_id = $menus->masterlist_id;
            $ordReq->name = $menus->name;
            $ordReq->unit = $menus->unit;
            $ordReq->quantity = $qty;
            $ordReq->price = (!empty($menus->price)) ? $menus->price : 0;
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
            'item_code' => $menus->item_code,
            'reorder' => $menus->reorder_point,
            'qty' => $menus->quantity,
            'reqQty' => $qty,
            'reqQtyCount' => $reqCheck,
        );

        echo json_encode($response);
    }

    public function branchSubmit(Request $request) {
        
        $total = 0;

        $ordreqs = db::table('branch_order_requests')->where('cashier' , auth()->id())->get();
        foreach ($ordreqs as $order){
            $total += $order->total_price;
        }
        $id = BranchTransactionRequest::latest()->pluck('id')->first();
        if ($id == null) {
            $id = 1;
        } else {
            $id++;
        }
        $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

        $branding = "D";
        $number = $branding . '-' . date('mdY') . '-' . $nid;
        $branch_code = DB::table('branches')->value('branch_code');



        $tranSlug = Str::random(60);
        $ntranSlug = $tranSlug;
        $check_slug = DB::table('branch_transaction_requests')->where('slug', $ntranSlug)->get();
        while (count($check_slug) > 0) {
            $ntranSlug = Str::random(60);
            $check_slug = DB::table('branch_transaction_requests')->where('slug', $ntranSlug)->get();
        }
        $tranSlug = $ntranSlug;

        $tran = new BranchTransactionRequest();
        $tran->number = $number;
        $tran->branch_code = $branch_code;
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
            $check_slug = DB::table('branch_requests')->where('slug', $norderSlug)->get();
            while (count($check_slug) > 0) {
                $norderSlug = Str::random(60);
                $check_slug = DB::table('branch_requests')->where('slug', $norderSlug)->get();
            }
            $orderSlug = $norderSlug;

            $ordReq = new BranchRequest();
            $ordReq->tran_id = $tran_id;
            $ordReq->inv_id = $order->inv_id;
            $ordReq->item_code = $order->item_code;
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

            DB::table('branch_order_requests')->where('id', $order->id)->delete();
        }

        
        $log = new Log();
        $log->trans_id = $tran_id;
        $log->classification = 'Branch Transfer';
        $log->info = 'Request Submit';
        $log->user_id = auth()->id();
        $log->event = 'Add';
        $log->save();

        

        $response = array(
            'name' => $total,
            'tranid' => $tran_id ,
            'qty' => $total,
            'reqQty' => $total,
            'reqQtyCount' => $total,
     
        );

        echo json_encode($response);
    }

    // CHANGE QTY BEFORE PENDING TO APPROVE STATUS
    // public function appqty(Request $request) {

    //     $id = $request->id;
    //     // dd('THE ID:' . $id);
    //     $qty = $request->qty;
    //     // dd('THE QUANTITY:' . $qty);

    //     $menus = db::table('branch_requests')->where('id' , $id)->first();
    //     $tprice = $menus->price * $qty;
        

    //         DB::table('branch_requests')->where('id' , $id)->update([
    //             'qty_approved' => $qty,
    //             'qty_received' => $qty,
    //             'total_price' => $tprice,

    //         ]);
        
        

        

    //     $response = array(
    //         'name' => $menus->name,
    //         'reorder' => $menus->reorder_point,
    //         'qty' => $menus->quantity,
    //         'reqQty' => $qty,
    //         'reqQtyCount' => $tprice,
    //     );

    //     echo json_encode($response);
    // }
    // END OF CHANGE QTY BEFORE PENDING TO APPROVE STATUS


    // FROM PENDING TO APPROVED STATUS
    // public function apiTransApproved(Request $request)
    // {
    //     $slug = $request->slug;
    //     $id = $request->id;
    //     // dd('THE ID:' . $id);
    //     $qty = $request->qty;
    //     // dd('THE QUANTITY:' . $qty);

    //     $apiLink = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApiApproval/{$slug}";

    //     $response = Http::get($apiLink);

    //     if ($response->successful()) {
    //         $data = $response->json();
    //         // dd($data);
    //         $ordered = $data['transactionDetails'];
    //         $transaction = $data['transaction'];

    //         $total = 0;

    //         $ordreqs = db::table('branch_requests')->where('cashier' , auth()->id())->get();
    //         foreach ($ordreqs as $order){
    //             $total += $order->total_price;
    //         }

    //         foreach ($ordered as $order) {
                
    //             $warehouse = DB::table('inventories')
    //                 ->where('item_code', $order['item_code'])
    //                 // ->where('department', 1)
    //                 ->first();

                
    //             $newQty = $warehouse->quantity - $order['qty_approved'];

    //             DB::table('inventories')
    //                 ->where('item_code', $order['item_code'])
    //                 // uncomment if needs to limit to a certain department
    //                 // ->where('department', 1)
    //                 ->update([
    //                     'quantity' => $newQty,
    //                 ]);
                
                
    //             DB::table('branch_requests')->insert([
    //                 'tran_id' => $transaction['id'], 
    //                 'inv_id' => $order['inv_id'],
    //                 'item_code' => $order['item_code'],
    //                 'masterlist_id' => $order['masterlist_id'],
    //                 'name' => $order['name'],
    //                 'unit' => $order['unit'],
    //                 'quantity' => $order['quantity'],
    //                 'qty_approved' => $order['qty_approved'],
    //                 'qty_received' => $order['qty_received'],
    //                 'price' => 0,
    //                 'total_price' => $total,
    //                 'current_stock' => $order['current_stock'],
    //                 'reorder_point' => $order['reorder_point'],
    //                 'remarks' => 'APPROVE-LOG',
    //                 'cashier' => $order['cashier'],
    //                 'role' => $order['role'],
    //                 'department' => $order['department'],
    //                 'branch' => $order['branch'],
    //                 'slug' => $slug,
    //                 'created_at' => date('Y-m-d h:i:s'),
    //                 'updated_at' => date('Y-m-d h:i:s')
                    
    //             ]);
    //         }

    //         $id = BranchTransactionRequest::latest()->pluck('id')->first();
    //                 if ($id == null) {
    //                     $id = 1;
    //                 } else {
    //                     $id++;
    //                 }
    //         $nid = str_pad($id, 7, '0', STR_PAD_LEFT);

    //         $branding = "P";
    //         $number = $branding . "-" . date('mdY') . "-" . $nid;

    //         DB::table('branch_transaction_requests')->insert([
    //             'number' => $number,
    //             'total' => $total,
    //             'slug' => $slug,
    //             'status' => 'APPROVE-LOG',
    //             'order_status' => 'APPROVE-LOG',
    //             'order_remarks' => $transaction['order_remarks'],
    //             'cashier' => $transaction['cashier'],
    //             'created_at' => date('Y-m-d h:i:s'),
    //             'updated_at' => date('Y-m-d h:i:s')
    //         ]);

    //         $log = new Log();
    //         $log->trans_id = $id;
    //         $log->classification = 'Branch Transfer';
    //         $log->info = 'Request Approval';
    //         $log->user_id = auth()->id();
    //         $log->event = 'Approval';
    //         $log->save();
            
            
            
    //         $ApproveApi = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApprovalApi/{$slug}";
    //         Http::get($ApproveApi);


    //         $menus = db::table('branch_requests')->where('id' , $id)->first();
    //         $tprice = $menus->price * $qty;
        

    //         DB::table('branch_requests')->where('id' , $id)->update([
    //             'qty_approved' => $qty,
    //             'qty_received' => $qty,
    //             'total_price' => $tprice,

    //         ]);
        
        

        

    //         $response = array(
    //             'name' => $menus->name,
    //             'reorder' => $menus->reorder_point,
    //             'qty' => $menus->quantity,
    //             'reqQty' => $qty,
    //             'reqQtyCount' => $tprice,
    //         );

    //         echo json_encode($response);

            
            
    //         return response()->json(['name' => $warehouse->quantity]);
    //     }

    //     return response()->json(['error' => 'Failed to fetch data from main'], 500);
    // }

    public function apiTransApproved(Request $request)
    {
        $slug = $request->slug;
        $branch_code = $request->branch_code;
        $adjustedQty = $request->adjusted_qty ?? [];
        
        // return response()->json([
        //     'message' => $slug,
        //     'total_price' => $branch_code,
        // ]);

        // Base API2 URL from settings
        $apiBase = rtrim(DB::table('branches')->where('branch_code', $branch_code)->value('api'), '/');

        // Fetch data from API2
        $apiLink = "{$apiBase}/ApiApproval/{$slug}";
        $response = Http::get($apiLink);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch data from API1'], 500);
        }

        $data = $response->json();
        $ordered = $data['transactionDetails'];
        $transaction = $data['transaction'];

        $total = 0;

        foreach ($ordered as $order) {
            $itemCode = $order['item_code'];
            $adjustment = $adjustedQty[$itemCode] ?? null;

            $approvedQty = $adjustment['qty'] ?? $order['qty_approved'];
            $totalPrice = $adjustment['total_price'] ?? ($approvedQty * ($order['price'] ?? 0));

            // Update local inventory //Adjust to menu
/*             $warehouse = DB::table('menus')->where('item_code', $itemCode)->first();
            if ($warehouse) {
                $newQty = $warehouse->quantity - $approvedQty;
                DB::table('menus')->where('item_code', $itemCode)->update([
                    'quantity' => $newQty,
                ]);
            }
 */         //menus 6/18/25
            $menu = DB::table('menus')->where('item_code', $itemCode)->first();

            if ($menu->is_combo == 1) {
                $ings = DB::table('ingredients')->where('menu_id', $menu->id)->get();
                    foreach ($ings as $ing) {
                    $decq =  $approvedQty * $ing->computed_quantity;
                    $curq = DB::table('menus')->where('id', $ing->inventory_id)->first();
                    $newq = $curq->quantity - $decq;

                   DB::table('menus')->where('id', $ing->inventory_id)
                    ->update([
                    'quantity' => $newq
                    ]);

                    /* dd([
                    'approvedQty' => $approvedQty,
                    'ingredientQty' => $ing->computed_quantity,
                    'decq' => $decq,
                    'currentQty' => $curq,
                    'NewQty' => $newq,
                    'curcode' =>$itemCode,
                    'invIngre' => $ing->inventory_id,
                    
                ]); */
                    }

            } else {
                DB::table('menus')
                    ->where('item_code', $itemCode)
                    ->decrement('quantity', $approvedQty);
            }
            DB::table('orders')->where('id', $order['id'])->delete();
            


            // Insert into local branch_requests
            DB::table('branch_requests')->insert([
                'tran_id' => $transaction['id'],
                'inv_id' => $order['inv_id'],
                'item_code' => $itemCode,
                'masterlist_id' => $order['masterlist_id'],
                'name' => $order['name'],
                'unit' => $order['unit'],
                'quantity' => $order['quantity'],
                'qty_approved' => $approvedQty,
                'qty_received' => $approvedQty,
                'price' => $order['price'] ?? 0,
                'total_price' => $totalPrice,
                'current_stock' => $order['current_stock'],
                'reorder_point' => $order['reorder_point'],
                'remarks' => 'APPROVE-LOG',
                'cashier' => $order['cashier'],
                'role' => $order['role'],
                'department' => $order['department'],
                'branch' => $order['branch'],
                'slug' => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $total += $totalPrice;

            // Send received_qty to API2 via GET
            $approveQtyApi = "{$apiBase}/ApproveQtyUpdate?item_code={$itemCode}&received_qty={$approvedQty}&slug={$slug}";
            // dd($receiveQtyApi);
            Http::get($approveQtyApi);
        }

        // Generate new transaction number
        $id = BranchTransactionRequest::latest()->value('id') ?? 0;
        $id++;
        $nid = str_pad($id, 7, '0', STR_PAD_LEFT);
        $number = "P-" . date('mdY') . "-" . $nid;

        // Insert into local branch_transaction_requests
        DB::table('branch_transaction_requests')->insert([
            'number' => $number,
            'total' => $total,
            'slug' => $slug,
            'status' => 'APPROVE-LOG',
            'order_status' => 'APPROVE-LOG',
            'order_remarks' => $transaction['order_remarks'],
            'cashier' => $transaction['cashier'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Logging
        $log = new Log();
        $log->trans_id = $id;
        $log->classification = 'Branch Transfer';
        $log->info = 'Request Approval';
        $log->user_id = auth()->id();
        $log->event = 'Approval';
        $log->save();

        // Final status update to API2
        $approveApi = "{$apiBase}/ApprovalApi/{$slug}";
        Http::get($approveApi);

        return response()->json([
            'message' => 'Approval successful with adjusted quantities.',
            'total_price' => $total,
        ]);
    }


    // END OF FROM PENDING TO APPROVED STATUS

    // FOR APPROVAL STATUS APPROVED TO RECEIVED SUBMIT
    public function apiTransReceived(Request $request)
    {
        $slug = $request->slug;
        $qty = $request->qty;
        $apiLink = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApiApprovalChecker";

        $response = Http::get($apiLink);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch data from main'], 500);
        }

        $data = $response->json();
        // $ordered = $data['transactionDetails'];
        // dd($data['transactionDetails']);
        $apiApproval = $data['branchTransactionRequests']['data'];

        $tran = db::table('branch_transaction_requests')->where('slug' , $slug)->first();

        $ordered = db::table('branch_requests')->where('tran_id' , $tran->id)->get();

        $apiBase = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/');
        
        foreach($ordered as $order){
            $inv = db::table('inventories')
                ->where('item_code' , $order->item_code)
                // ->where('branch', $order->branch)
                ->first();

            $qtyReceived = (float) $order->qty_received;
            $invQty = (float) $inv->quantity;

            $newQty = $invQty + $qtyReceived;
            // ->where('branch', $order->branch)
            DB::table('inventories')->where('item_code' , $order->item_code)->update([
                'quantity' => $newQty,
            ]);
            DB::table('branch_requests')->where('id', $order->id)->update([
                'remarks' => 'RECEIVED',
            ]);

            $receiveQtyApi = "{$apiBase}/ReceiveQtyUpdate?slug={$slug}&item_code={$order->item_code}&received_qty={$qtyReceived}";
            Http::get($receiveQtyApi);
        }

        DB::table('branch_transaction_requests')->where('slug' , $slug)->update([
            'order_status' => 'RECEIVED',
            'status' => 'RECEIVED'
        ]);

        $log = new Log();
        $log->trans_id = $tran->id;
        $log->classification = 'Branch Transfer';
        $log->info = 'Request Receiving';
        $log->user_id = auth()->id();
        $log->event = 'Receiving';
        $log->save();

        foreach ($apiApproval as $item) 
        {
            if ($item['order_status'] === 'APPROVE-LOG') {
                $itemSlug = $item['trSlug'];

                $ApproveApi = "{$apiBase}/ReceivedApi/{$itemSlug}";

                Http::get($ApproveApi);
            }
        }

        $response = array(
            'name' => $slug,
        );
        return response()->json(['message' => 'Transaction successfully received.']);
        // echo json_encode($response);

        
    }

    // END OF FOR APPROVAL STATUS APPROVED TO RECEIVED SUBMIT


    //SHOW ALL
    public function pamahawApiTransfer()
    {
        /* $apiLink = rtrim(DB::table('settings')->whereIn('id', 2)->value('api_link'), '/') . "/ApiTransferIndex";

        $response = Http::get($apiLink);

        if ($response->successful()) {
            $data = $response->json(); */

            $ids = [2, 5, 7];
            // $apiLinks = DB::table('settings')
            //     ->whereIn('id', $ids)
            //     ->pluck('api_link');
            
            $apiLinks = DB::table('branches')
                ->where('is_deleted', 0)
                ->pluck('api');

            $apiApproval = []; // Final combined list

            foreach ($apiLinks as $link) {
                $apiLink = rtrim($link, '/') . "/ApiTransferIndex";
                $response = Http::get($apiLink);

                if ($response->successful()) {
                    $json = $response->json();

                    // Make sure key exists
                    if (isset($json['branchTransactionRequests']['data'])) {
                        $apiApproval = array_merge($apiApproval, $json['branchTransactionRequests']['data']);
                    }
                }
            }
            $page = 1;
            $search = "";
            //leo add
            $branchNames = DB::table('branches')->pluck('name', 'branch_code');

            foreach ($apiApproval as &$item) {
                $code = $item['branch_code'] ?? null;
                $item['branch_name'] = $branchNames[$code] ?? 'Unknown Branch';
            }


            // Count pending requests
            $Prequestcount = collect($apiApproval)
                                ->where('order_status', 'PENDING')
                                ->count();

            
            // foreach ($apiApproval as $item) {
            //     if ($item['order_status'] === 'PENDING') {
            //         $slug = $item['trSlug'];

                    
            //         $ApproveApi = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApprovalApi/{$slug}";

                    
            //         Http::get($ApproveApi);
            //     }
            // }
        
            return view('user.inventory.branch_transfer.transfer-index', compact('apiApproval', 'page', 'search', 'Prequestcount'));
        
    }

    public function pamahawApiEdit($slug,$code)
    {
        // return response()->json([
        //     'message' => $slug,
        //     'total_price' => $code,
        // ]);
        
        $apiLink = rtrim(DB::table('branches')->where('branch_code', $code)->value('api'), '/') . "/ApiTransferEdit/{$slug}";
        
        
        
        //$apiLink = rtrim(DB::table('settings')->where('id', 2)->value('api_link'), '/') . "/ApiTransferEdit/{$slug}";

        $response = Http::get($apiLink);
        
        //$response = Http::get("http://127.0.0.1:8005/api/ApiTransferEdit/$slug");

        
        if ($response->successful()) {
            $data = $response->json();
            
            // dd($data);
            $dahonMenu = DB::table('menus') //Change to MENUS
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get()
            ->keyBy('item_code'); 

            $apimenus = $data['transactionDetails'];
            $apiTrans = $data['transaction'];
            $reqNumber = $data['reqNumber'];

            foreach ($apimenus as &$apimenu) {
                if (isset($apimenu['item_code']) && isset($dahonMenu[$apimenu['item_code']])) {
                    $apimenu['inv_quantity'] = $dahonMenu[$apimenu['item_code']]->quantity;
                } else {
                    $apimenu['inv_quantity'] = 'Not Available';
                }
            }

            return view('user.inventory.branch_transfer.transfer-edit', compact('apimenus', 'dahonMenu','apiTrans', 'reqNumber'));
        }
        
        return response()->json([
            'message' => $slug,
            'total_price' => $apiLink,
        ]);
        
    }

    // public function ApiReceivedIndex()
    // {
    //     $apiLink = DB::table('api_links')->where('id', 6)->value('link');

    //     $response = Http::get($apiLink);

    //     if ($response->successful()) {
    //         $data = $response->json();
    //         // dd($data);
    //         $apiApproved = $data['orderRequest']['data'];
    //         // dd($data['orderRequest']);
    //         $page = 1;
    //         $search = "";
    
    //         $requestcount = $data['requestcount'];
    //         // dd($data['requestcount']);
    
    //         return view('user.inventory.branch_transfer.transfer-received_index', compact('apiApproved', 'page', 'search', 'requestcount'));
    //     } else {
    //         echo 'here on else';
    //     }
    // }

    // FOR APPROVAL INDEX VIEW
    public function transferApprovedIndex()
    {
        // $apiLink = DB::table('api_links')->where('id', 7)->value('link');

        // $response = Http::get($apiLink);

        // if ($response->successful()) {
        //     $data = $response->json();
        //     // dd($data);
        //     $apiApproved = $data['orderRequest']['data'];
        //     // dd($data['orderRequest']);
        //     $page = 1;
        //     $search = "";
    
        //     $Prequestcount = $data['requestcount'];
        //     // dd($data['requestcount']);
    
        //     return view('user.inventory.branch_transfer.transfer-approved', compact('apiApproved', 'page', 'search', 'Prequestcount'));
        // } else {
        //     echo 'here on else';
        // }

        $apiApproved = DB::table('branch_transaction_requests')
            ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
            ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
            ->where('branch_transaction_requests.order_status','APPROVED')
            ->orderBy('branch_transaction_requests.id', 'asc')->paginate(100);
        $Prequestcount = DB::table('branch_transaction_requests')->where('order_status','APPROVED')->get()->count();
        $page = 1;
        $search = "";
        return view('user.inventory.branch_transfer.transfer-approved', compact('apiApproved', 'Prequestcount', 'page', 'search'));
    }
    // END OF FOR APPROVAL INDEX

    // FOR RECQTY
    public function recqty(Request $request) {

        $id = $request->id;
        $qty = $request->qty;

            DB::table('branch_requests')->where('id' , $id)->update([
                'qty_received' => $qty,
            ]);
        $response = array(
            'name' => $id,
        );

        echo json_encode($response);
    }
    // END OF RECQTY

    // FOR APPROVAL VIEW
    public function pamahawApiReceived($slug)
    {
       
        // $apiLink = DB::table('api_links')->where('id', 8)->value('link');

        // $response = Http::get($apiLink . "/" . $slug); 

        
        // if ($response->successful()) {
        //     $data = $response->json(); 
        //     // Get all local inventory
        //     // dd($data);
        //     $dahonMenu = DB::table('inventories')
        //     ->where('is_deleted', 0)
        //     ->orderBy('name', 'asc')
        //     ->get()
        //     ->keyBy('item_code'); 

        //     $apimenus = $data['transactionDetails'];
        //     $apiTrans = $data['transaction'];
        //     $reqNumber = $data['reqNumber'];
        //     // dd($data['transactionDetails']);

        //     foreach ($apimenus as &$apimenu) {
        //         if (isset($apimenu['item_code']) && isset($dahonMenu[$apimenu['item_code']])) {
        //             $apimenu['inv_quantity'] = $dahonMenu[$apimenu['item_code']]->quantity;
        //         } else {
        //             $apimenu['inv_quantity'] = 'Not Available';
        //         }
        //     }

        //     return view('user.inventory.branch_transfer.transfer-received', compact('apimenus', 'dahonMenu','apiTrans', 'reqNumber'));
        // }

        $ordchecking = DB::table('order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('branch_transaction_requests')->where('slug',$slug)->first();

        $orders = DB::table('branch_requests')->where('tran_id', $trans->id)->get();
        $totalprice = 0;

        $requestTransaction = DB::table('branch_transaction_requests as tr')
            ->leftJoin('branch_requests as or','or.tran_id', '=', 'tr.id')
            ->where('tr.slug', $slug)->get();
        $reqNumber = DB::table('branch_transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('branch_transaction_requests')->where('order_status','APPROVED')->get()->count();


        return view('user.inventory.branch_transfer.transfer-received', compact('requestTransaction','reqNumber','search','page','requestcount'));
    }
    // END OF FOR APPROVAL VIEW

    // public function ApiTransferReceivedReview($slug)
    // {
    //     // Make an API request to the main branch to fetch transaction data by slug
    //     $apiLink = DB::table('api_links')->where('id', 9)->value('link');


    //     $response = Http::get($apiLink . "/" . $slug); 

        
    //     if ($response->successful()) {
    //         $data = $response->json(); // This gives you ['transaction' => ..., 'transactionDetails' => ...]
    //         // Get all local inventory
    //         // dd($data);
    //         $dahonMenu = DB::table('inventories')
    //         ->where('is_deleted', 0)
    //         ->orderBy('name', 'asc')
    //         ->get()
    //         ->keyBy('item_code'); // index by item_code for easier matching

    //         $apimenus = $data['transactionDetails'];
    //         $apiTrans = $data['transaction'];
    //         $reqNumber = $data['reqNumber'];
    //         // dd($data['transactionDetails']);
    //         // dd($data['transaction']);
    //         foreach ($apimenus as &$apimenu) {
    //             if (isset($apimenu['item_code']) && isset($dahonMenu[$apimenu['item_code']])) {
    //                 $apimenu['inv_quantity'] = $dahonMenu[$apimenu['item_code']]->quantity;
    //             } else {
    //                 $apimenu['inv_quantity'] = 'Not Available';
    //             }
    //         }

    //         return view('user.inventory.branch_transfer.transfer-received_view', compact('apimenus', 'dahonMenu','apiTrans', 'reqNumber'));
    //     }
    // }



    // received cross department to department
    public function ApiCrossReceivedIndexView(){
        $apiLink = DB::table('api_links')->where('id', 6)->value('link');

        $orderRequest = collect(); // Initialize empty collection
        $requestcount = 0;

        $response = Http::get($apiLink);

        if ($response->successful()) {
            $data = $response->json();
            $apiApproved = $data['orderRequest']['data'];

            // Check if any item has order_status == 'RECEIVED'
            $hasReceived = collect($apiApproved)->contains(function ($item) {
                return $item['order_status'] === 'RECEIVED';
            });

            if ($hasReceived) {
                $orderRequest = DB::table('branch_transaction_requests')
                    ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
                    ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
                    ->where('branch_transaction_requests.order_status', 'APPROVED')
                    ->orderBy('branch_transaction_requests.id', 'asc')
                    ->paginate(100);

                $requestcount = DB::table('branch_transaction_requests')
                    ->where('order_status','APPROVED')
                    ->count();
            }
        }

        $page = 1;
        $search = "";

        return view('user.inventory.branch_transfer.transfer-cross-received_index', compact('orderRequest', 'requestcount', 'page', 'search'));
    }

    // end of received cross department to department

    // received cross view
    public function ApiCrossReceivedView($slug)
    {
        // Clear previous approval if any
        $ordchecking = DB::table('order_approvals')->where('cashier', auth()->id())->first();
        if ($ordchecking != null) {
            DB::table('order_approvals')->where('cashier', auth()->id())->delete();
        }

        // Fetch transaction using slug
        $trans = DB::table('branch_transaction_requests')->where('slug', $slug)->first();

        // Fetch request details
        $requestTransaction = DB::table('branch_requests')->where('tran_id', $trans->id)->get();

        // Get all local inventory (keyed by item_code)
        $dahonMenu = DB::table('inventories')
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get()
            ->keyBy('item_code');

        // Add inv_quantity to each order
        foreach ($requestTransaction as &$order) {
            if (isset($order->item_code) && isset($dahonMenu[$order->item_code])) {
                $order->inv_quantity = $dahonMenu[$order->item_code]->quantity;
            } else {
                $order->inv_quantity = 'Not Available';
            }
        }

        // Fetch transaction for displaying request number, etc.
        $reqNumber = $trans;
        $search = '';
        $page = 1;
        $requestcount = DB::table('branch_transaction_requests')->where('order_status', 'RECEIVED')->count();

        return view('user.inventory.branch_transfer.transfer-cross-received_view', compact(
            'requestTransaction',
            'reqNumber',
            'search',
            'page',
            'requestcount'
        ));
    }
    // end of received cross view

    // api cross received
    public function ApiCrossReceived(Request $request) {
        $slug = $request->slug;

        // Fetch from API using link ID 3
        $apiLink = DB::table('api_links')->where('id', 3)->value('link');
        $response = Http::get($apiLink . "/" . $slug); 

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch data from main'], 500);
        }

        $data = $response->json();
        $ordered = $data['transactionDetails']; // items from API
        $transaction = $data['transaction'];

        // Local transaction for matching
        $tran = DB::table('branch_transaction_requests')->where('slug', $slug)->first();
        if (!$tran) {
            return response()->json(['error' => 'Transaction not found locally.'], 404);
        }

        foreach ($ordered as $order) {
            // Use API-provided masterlist_id and item_code to find matching inventory
            $inv = DB::table('inventories')
                ->where('item_code', $order['item_code'])
                // ->where('branch', $tran->branch_id ?? $order['branch'] ?? 1) 
                ->first();

            if (!$inv) continue;

            // Add qty_received from API to inventory
            $newQty = $inv->quantity + $order['qty_received'];
            DB::table('inventories')
                ->where('item_code', $order['item_code'])
                // ->where('branch', $inv->branch)
                ->update(['quantity' => $newQty]);

            // Update branch_requests remarks
            DB::table('branch_requests')
                ->where('tran_id', $tran->id)
                ->where('item_code', $order['item_code'])
                ->update(['remarks' => 'RECEIVED']);
        }

        // Update transaction status
        DB::table('branch_transaction_requests')
            ->where('slug', $slug)
            ->update([
                'order_status' => 'RECEIVED',
                'status' => 'RECEIVED'
            ]);

        return response()->json(['name' => $slug]);
    }

    // end of api cross received


    // received viewing only
    public function ApiReceivedIndex(){
        $orderRequest = DB::table('branch_transaction_requests')
        ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
        ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
        ->where('branch_transaction_requests.order_status', 'RECEIVED')

        ->orderBy('branch_transaction_requests.id', 'asc')->paginate(100);
        $requestcount = DB::table('branch_transaction_requests')->where('order_status','RECEIVED')->get()->count();
        $page = 1;
        $search = "";
        return view('user.inventory.branch_transfer.transfer-received_index', compact('orderRequest', 'requestcount', 'page', 'search'));
    }
    // end of received department to department

    // received view
    public function ApiReceivedIndexView($slug){

        $ordchecking = DB::table('order_approvals')->where('cashier',auth()->id())->first();
        if ($ordchecking != null){
            DB::table('order_approvals')->where('cashier', auth()->id())->delete();
        }
        $trans = DB::table('branch_transaction_requests')->where('slug',$slug)->first();

        $orders = DB::table('branch_requests')->where('tran_id', $trans->id)->get();
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



        $requestTransaction = DB::table('branch_transaction_requests as tr')
        ->leftJoin('ordered_requests as or','or.tran_id', '=', 'tr.id')
        ->where('tr.slug', $slug)->get();
        $reqNumber = DB::table('branch_transaction_requests')->where('slug',$slug)->first();
        $search = '';
        $page = 1;

        $requestcount = DB::table('branch_transaction_requests')->where('order_status','RECEIVED')->get()->count();


        return view('user.inventory.branch_transfer.transfer-received_view', compact('requestTransaction','reqNumber','search','page','requestcount'));
    }
    // end of received view

    // end of received viewing only

    

    // END OF DAHON API

}
    
