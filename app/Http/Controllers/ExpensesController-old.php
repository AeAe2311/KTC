<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryCartTransaction;
use App\Models\ExpensesCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExpensesController extends Controller
{
    public function index(){
        $today = Carbon::today();
        $inventories = InventoryTransaction::with('ExpensesCategory','supplier')
       // ->whereDate('created_at', $today)
       ->where('inventory_transactions.store_code', Auth::user()->store_code)
       
       ->orderBy('created_at', 'DESC')
       ->where('inv_id', 0)
       ->paginate(100);

       $e_inventories = DB::table('inventory_etransaction')
       ->leftjoin('users', 'users.id','=','inventory_etransaction.e_server')
       ->select('*', 'inventory_etransaction.id as e_id')
       ->orderBy('inventory_etransaction.created_at', 'DESC')
        ->where('inventory_etransaction.is_deleted',0)
        ->where('inventory_etransaction.store_code', Auth::user()->store_code)
       ->paginate(50, ['*'], 'e_page');

        $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        // Fetch suppliers
        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $servername = DB::table('users')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        
        // $inventories = DB::table('inventories')
        //     ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug')
        //     ->join('categories', 'inventories.category_id', 'categories.id')
        //     ->where('is_expenses', 1)
        //     ->orderBy('name', 'asc')
        //     ->paginate(100);
        $invCount = $inventories->total();
        // $invCount = DB::table('inventories')->where('is_expenses', 1)->get()->count();
        $page = 1;
        $search = "";

        /* Start of Audited total */
        $today = date('Y-m-d');
        $audited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 1)
            //->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');

        $nonAudited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 0)
            //->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');
        /* end of Audited total */
        
        return view('user.inventory.expenses.index', compact('servername','expcats', 'supps', 'e_inventories','inventories', 'invCount', 'page', 'search','audited','nonAudited'));
    }

    public function paginate($page){
        $today = Carbon::today();
        $inventories = InventoryTransaction::with('ExpensesCategory','supplier')
       // ->whereDate('created_at', $today)
       ->orderBy('created_at', 'DESC')
       ->where('inv_id', 0)
        ->paginate(100,['*'],'page',$page);
        //dd($page);
        $e_inventories = DB::table('inventory_etransaction')
       ->leftjoin('users', 'users.id','=','inventory_etransaction.e_server')
       ->select('*', 'inventory_etransaction.id as e_id')
       ->orderBy('inventory_etransaction.created_at', 'DESC')
       ->where('inventory_etransaction.store_code', Auth::user()->store_code)
        ->where('inventory_etransaction.is_deleted',0)
       ->paginate(50,['*'],'e_page',$page);
        // $inventories = DB::table('inventories')
        //     ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug')
        //     ->join('categories', 'inventories.category_id', 'categories.id')
        //     ->where('is_expenses', 1)
        //     ->orderBy('name', 'asc')
        //     ->paginate(100,'*','page',$page);

        $servername = DB::table('users')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $invCount = $inventories->total();
        // $invCount = DB::table('inventories')->where('is_expenses', 1)->get()->count();

        $search = "";

        return view('user.inventory.expenses.index', compact('inventories', 'invCount', 'page', 'search','e_inventories','servername'));
    }

    public function search($page, $search){
        $today = Carbon::today();
        $inventories = InventoryTransaction::with('ExpensesCategory','supplier')
            ->whereDate('created_at', $today)
            ->whereRaw("CONCAT_WS(' ', remarks) LIKE '%{$search}%'")
            ->where('inv_id', 0)
            ->paginate(100,'*','page',$page);

        $invCount = InventoryTransaction::with('ExpensesCategory','supplier')
            ->whereDate('created_at', $today)
            ->whereRaw("CONCAT_WS(' ', remarks) LIKE '%{$search}%'")
            ->where('inv_id', 0)
            ->count();
            
        $e_inventories = DB::table('inventory_etransaction')
       ->leftjoin('users', 'users.id','=','inventory_etransaction.e_server')
       ->select('*', 'inventory_etransaction.id as e_id')
       ->orderBy('inventory_etransaction.created_at', 'DESC')
       ->where('inventory_etransaction.store_code', Auth::user()->store_code)
       ->whereRaw("CONCAT_WS(' ', e_transid) LIKE '%{$search}%'")
        ->where('inventory_etransaction.is_deleted',0)
       ->paginate(50);

        // $inventories = DB::table('inventories')
        //     ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug')
        //     ->join('categories', 'inventories.category_id', 'categories.id')
        //     ->where('is_expenses', 1)
        //     ->whereRaw("CONCAT_WS(' ', inventories.item_code, inventories.name, categories.name) LIKE '%{$search}%'")
        //     ->orderBy('name', 'asc')
        //     ->paginate(100,'*','page',$page);

        // $invCount = DB::table('inventories')
        //     ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug')
        //     ->join('categories', 'inventories.category_id', 'categories.id')
        //     ->where('is_expenses', 1)
        //     ->whereRaw("CONCAT_WS(' ', inventories.item_code, inventories.name, categories.name) LIKE '%{$search}%'")
        //     ->orderBy('name', 'asc')
        //     ->count();

        return view('user.inventory.expenses.index', compact('inventories', 'invCount', 'page', 'search','e_inventories'));
    }

    public function add(Request $request)
    {
        // Get the form type from the query parameter; default to 'cost'
        $formType = $request->query('form', 'cost');

        // Fetch expense categories
        $expcats = DB::table('expenses_categories')
        //->where('store_code', Auth::user()->store_code)
        ->orderBy('id', 'asc')
        ->where('is_deleted', 0)
        ->get();

        // Fetch suppliers
        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        // $supps1 = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        
        // Fetch today's inventory transactions
        // $today = Carbon::today(); //comment out by leo - 8/30/26
        $inventories = InventoryCartTransaction::with('ExpensesCategory', 'supplier')   
            ->where('user_id', auth()->id())
            ->where('inv_id', 0)
            ->get();
        
        $inventoriesCount = InventoryCartTransaction::with('ExpensesCategory', 'supplier')
            ->where('user_id', auth()->id())
            // ->whereDate('created_at', $today) //comment out by leo - 8/30/26
            ->where('inv_id', 0)
            ->count();

        $dept = DB::table('departments')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', '0')
        ->orderBy('id', 'asc')
        ->get();
        $dept1 = DB::table('departments')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', '0')
        ->orderBy('id', 'asc')
        ->get();

        // Pass the form type to the view
        return view('user.inventory.expenses.add', compact('dept1','dept','expcats', 'supps','inventories', 'inventoriesCount', 'formType'));
        
        
    }


    public function store(Request $request) {
        $name = $request->name;
        $other_name = $request->other_name;
        $status = $request->status;
        $payment_class = $request->payment_class;
        $unit = $request->unit;
        $exp_type = $request->exp_type;
        $unit_price = str_replace(',', '', $request->unit_price);
        $quantity = str_replace(',', '', $request->quantity);
        $price = str_replace(',', '', $request->price);
        $department = $request->department;
    
        // category
        $categoryArray = explode(",", $request->category);
        $is_cat_name = $categoryArray[0];
        $is_cat_id = $categoryArray[1];
    
        // supplier
        $SupplierArray = explode(",", $request->supplier);
        $is_supp_name = $SupplierArray[0];
        $is_supp_id = $SupplierArray[1];
    
        $request->validate([
            'name' => 'required',
            'quantity' => 'required',
            'price' => 'required'
            
        ]);
    
        $item = new InventoryCartTransaction();
        $item->inv_id = 0;
        $item->type = strtoupper($is_cat_name);
        $item->category_id = $is_cat_id;
        $item->supplier_id = $is_supp_id;
        $item->quantity_before = 0;
        $item->quantity = $quantity;
        $item->quantity_after = 0;
        $item->amount = $price;
        $item->remarks = $name;
        $item->unit = $unit;
        $item->unit_price = $unit_price;
        $item->other_remarks = $other_name;
        $item->is_paid = $status;
        $item->payment_class = $payment_class;
        $item->exp_type = $exp_type;
        $item->user_id = Auth::user()->id;
        $item->store_code = Auth::user()->store_code;
        $item->department_id = $department;
    
        if(Auth::user()->role == 1 || Auth::user()->role == 5){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
            $item->created_at = date('Y/m/d H:i:s', strtotime($dateAdd));
        }
    
        $item->save();
    
        // Return the latest inventories as JSON response
        $inventories = InventoryCartTransaction::latest()->get(); // You can adjust the query as needed
    
        return response()->json(['inventories' => $inventories]);
    }

    // for the second form
    public function storeOther(Request $request) {
        $name1 = $request->name1;
        $other_name1 = $request->other_name1;
        $status1 = $request->status1;
        $payment_class1 = $request->payment_class1;
        $unit = $request->unit;
        $exp_type1 = $request->exp_type1;
        $unit_price1 = str_replace(',', '', $request->unit_price1);
        $quantity1 = str_replace(',', '', $request->quantity1);
        $price1 = str_replace(',', '', $request->price1);
        $department1 = $request->department1;
    
        // category
        $categoryArray1 = explode(",", $request->category1);
        $is_cat_name = $categoryArray1[0];
        $is_cat_id = $categoryArray1[1];
    
        // supplier
        $SupplierArray1 = explode(",", $request->supplier1);
        $is_supp_name = $SupplierArray1[0];
        $is_supp_id = $SupplierArray1[1];
    
        $request->validate([
            'name1' => 'required',
            'quantity1' => 'required',
            'price1' => 'required'
            
        ]);
    
        $item = new InventoryCartTransaction();
        $item->inv_id = 0;
        $item->type = strtoupper($is_cat_name);
        $item->category_id = $is_cat_id;
        $item->supplier_id = $is_supp_id;
        $item->quantity_before = 0;
        $item->quantity = $quantity1;
        $item->quantity_after = 0;
        $item->amount = $price1;
        $item->remarks = $name1;
        $item->unit = $unit;
        $item->unit_price = $unit_price1;
        $item->other_remarks = $other_name1;
        $item->is_paid = $status1;
        $item->payment_class = $payment_class1;
        $item->exp_type = $exp_type1;
        $item->user_id = Auth::user()->id;
        $item->store_code = Auth::user()->store_code;
        $item->department_id = $department1;
    
        if(Auth::user()->role == 1 || Auth::user()->role == 5){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
            $item->created_at = date('Y/m/d H:i:s', strtotime($dateAdd));
        }
    
        $item->save();
    
        // Return the latest inventories as JSON response
        $inventories = InventoryCartTransaction::latest()->get(); // You can adjust the query as needed
    
        return response()->json(['inventories' => $inventories]);
    }
    // end of second form

    public function cart_store(Request $request)
    {
        $validated = $request->validate([
            'e_total' => 'required|numeric',
            'e_vat' => 'required|numeric',
            'e_wtaxpercent' => 'required|numeric',
            'e_wtax' => 'required|numeric',
            'e_amount' => 'required|numeric',
        ]);
        
        $eTotal = $validated['e_total'];
        $eVat = $validated['e_vat'];
        $eWtaxperncentage = $validated['e_wtaxpercent'];
        $eWtax = $validated['e_wtax'];
        $eAmount = $validated['e_amount'];
        
        $currentDate = now()->format('Ymd');
        $count = DB::table('inventory_etransaction')
        ->whereDate('created_at', now()->toDateString())  // Filter transactions by today's date
        ->count();
        $transactionId = $currentDate . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        DB::beginTransaction();
        try {
            
            $eTransactionId = DB::table('inventory_etransaction')->insertGetId([
             'e_transid' => $transactionId,   
            'e_server'  => Auth::user()->id,
            'e_total' =>  $eTotal,
            'e_vat' => $eVat,
            'e_wtaxpercent' => $eWtaxperncentage,
            'e_wtax' => $eWtax,
            'e_amount' => $eAmount,
            'e_server' => Auth::user()->id,
            'store_code' => Auth::user()->store_code,
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);



            // Retrieve all data from the inventory_cart_transactions table
            $transactions = DB::table('inventory_cart_transactions')->where('user_id', auth()->id())->get();

            // Insert the retrieved data into the inventory_expenses_transactions table
            foreach ($transactions as $transaction) {
                DB::table('inventory_transactions')->insert([
                    'e_id' => $eTransactionId,
                    'inv_id' => $transaction->inv_id,
                    'type' => $transaction->type,
                    'category_id' => $transaction->category_id,
                    'supplier_id' => $transaction->supplier_id,
                    'quantity_before' => $transaction->quantity_before,
                    'quantity' => $transaction->quantity,
                    'quantity_after' => $transaction->quantity_after,
                    'amount' => $transaction->amount,
                    'remarks' => $transaction->remarks,
                    'unit' => $transaction->unit,
                    'unit_price' => $transaction->unit_price,
                    'exp_type' => $transaction->exp_type,
                    'other_remarks' => $transaction->other_remarks,
                    'user_id' => $transaction->user_id,
                    'is_paid' => $transaction->is_paid,
                    'payment_class' => $transaction->payment_class,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                    'store_code' => Auth::user()->store_code,
                    'ap_payment' => "",
                    'department_id' => $transaction->department_id,
                ]);
            }

            // Delete all data from the inventory_cart_transactions table after the transfer
            DB::table('inventory_cart_transactions')->where('user_id', auth()->id())->delete();

            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Data transferred successfully.']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function cart_delete(Request $request){
        $slug = $request->slug;
        DB::table('inventory_cart_transactions')->where('id', $slug)->delete();
        
        return response()->json(['success' => true, 'message' => 'Data deleted successfully.']);
    }
    
    


    public function edit($id){
        $item = InventoryTransaction::with('ExpensesCategory','supplier')->where('id', $id)->first();
        
        $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        return view('user.inventory.expenses.edit', compact('item','expcats', 'supps'));
    }

    public function update(Request $request){
        $id = $request->id;
        $name = $request->name;
        $status = $request->status;
        //$type_txt = $request->type_txt;
        //$type = $request->type;


        $categoryArray = explode(",", $request->category);
        $is_cat_name = $categoryArray[0];
        $is_cat_id = $categoryArray[1];

        $SupplierArray = explode(",", $request->supplier);
        $is_supp_name = $SupplierArray[0];
        $is_supp_id = $SupplierArray[1];

        // if (strtoupper($type) != 'OTHERS'){
        //    $type_txt = '';
        // }
        if(Auth::user()->role == 1){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
        }else{
            $dateAdd = date('Y/m/d H:i:s');
        }
        $quantity = str_replace(',', '', $request->quantity);
        $price = str_replace(',', '', $request->price);

        $request->validate([
            'name' => 'required',
            'quantity' => 'required',
            'price' => 'required',
        ]);
        DB::table('inventory_transactions')->where('id', $id)
            ->update([
                'remarks' => $name,
                'quantity' => $quantity,
                'amount' => $price,
                'type' => $is_cat_name,
                'category_id' => $is_cat_id,
                'supplier_id' => $is_supp_id,
                'is_paid' => $status,
                'created_at' => date('Y/m/d H:i:s', strtotime($dateAdd))
            ]);

        return redirect()->route('expenses.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($id){
        DB::table('inventory_transactions')->where('id', $id)->delete();

        return redirect()->route('expenses.index')->withInput()->with('message', 'Successfully Deleted');
    }

    public function addqty(Request $request){
        $slug = $request->addSlug;
        $quantity = 1;
        $price = $request->price;
        $dateAdd = $request->dateAdd;
        $inv = DB::table('inventories')->where('slug', $slug)->first();
        $qb = $inv->quantity;
        $qf = $qb + $quantity;

        DB::table('inventories')->where('slug', $slug)->update([
            'quantity' => $qf,
            'price' => $price,
        ]);

        $it = new InventoryTransaction();
        $it->inv_id = $inv->id;
        $it->type = 'INCOMING';
        $it->quantity_before = $qb;
        $it->quantity = $quantity;
        $it->quantity_after = $qf;
        $it->amount = $price;
        $it->remarks = 'N/A';
        $it->user_id = Auth::id();
        $it->created_at = $dateAdd;
        $it->save();

        return redirect()->route('expenses.index')->withInput()->with('message', 'Quantity Successfully Increased');
    }

    // public function minusqty(Request $request){
    //     $slug = $request->minSlug;
    //     $quantity = $request->minQuantity;
    //     $remarks = $request->remarks;
    //     $dateMinus = $request->dateMinus;
    //     $inv = DB::table('inventories')->where('slug', $slug)->first();
    //     $qb = $inv->quantity;
    //     $qf = $qb - $quantity;

    //     if($qf < 0){
    //         return redirect()->route('inventory.index')->withInput()->with('error', 'Please Enter a valid Quantity.');
    //     }else{
    //         DB::table('inventories')->where('slug', $slug)->update([
    //             'quantity' => $qf,
    //         ]);
    
    //         $it = new InventoryTransaction();
    //         $it->inv_id = $inv->id;
    //         $it->type = 'OUTGOING';
    //         $it->quantity_before = $qb;
    //         $it->quantity = $quantity;
    //         $it->quantity_after = $qf;
    //         $it->remarks = $remarks;
    //         $it->user_id = Auth::id();
    //         $it->created_at = $dateMinus;
    //         $it->save();
    
    //         return redirect()->route('inventory.index')->withInput()->with('message', 'Quantity Successfully Decreased');
    //     }
    // }



    public function updateTaxValues(Request $request)
    {
        $total = $request->input('total');
        $vatChecked = $request->input('vatChecked');
        $taxChecked = $request->input('taxChecked');
        $wtaxPercent = $request->input('wtaxPercent');
    
        $vatAmount = 0;
        $wtaxAmount = 0;
        $totalWithTax = $total;
    
        if ($vatChecked) {
            $vatAmount = $total - ($total / 1.12);
            
        }
    
        if ($taxChecked && $wtaxPercent > 0) {
            $wtaxAmount = $total * ($wtaxPercent / 100);
            $totalWithTax -= $wtaxAmount;
        }
    
        return response()->json([
            'total' => $total,
            'vatAmount' => $vatAmount,
            'wtaxAmount' => $wtaxAmount,
            'totalWithTax' => $totalWithTax,
           
        ]);
    }

   // Controller method to fetch inventory transaction by e_id
   public function getInventoryTransaction($e_id)
   {
       // Get all transactions for the given e_id
       $transactions = InventoryTransaction::where('e_id', $e_id)->get();
   
       // Create an array to hold the transaction data
       $transactionData = [];
   
       // Loop through all transactions and format the data
       foreach ($transactions as $transaction) {
           $transactionData[] = [
               'exp_type' => $transaction->exp_type,
               'quantity' => $transaction->quantity,
               'unit' => $transaction->unit,
               'remarks' => $transaction->remarks,
               'unit_price' => $transaction->unit_price,
               'amount' => $transaction->amount,
               'category_name' => $transaction->ExpensesCategory->name ?? 'N/A',
               'supplier_name' => $transaction->supplier->name ?? 'N/A',
               
               'payment_status' => $transaction->payment_class,
           ];
       }
   
       // Return all transactions data in a JSON response
       return response()->json($transactionData);
   }
   

   public function updateStatus(Request $request)
   {
       $eid = $request->input('id');
       $eapp = $request->input('app');
       
       
       if ($eapp == 1) {
           // Update status and approval if app is 1
           $updated = DB::table('inventory_etransaction')
               ->where('id', $eid)
               ->update([
                   'e_status' => null,
                   'e_approval' => '0',
               ]);
           
           return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
       } else {
           // Update status and approval if app is not 1
           $updated = DB::table('inventory_etransaction')
               ->where('id', $eid)
               ->update([
                   'e_status' => 'Waiting for Approval',
                   'e_approval' => '1',
               ]);
           
           return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
       }
   }


   public function edit2($id,Request $request){

 // Get the form type from the query parameter; default to 'cost'
                $formType = $request->query('cost');

                // Fetch expense categories
                $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

                // Fetch suppliers
                $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
                // $supps1 = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

                // Fetch today's inventory transactions
                $today = Carbon::today();
                $inventories = DB::table('inventory_transactions')
                ->orderBy('id', 'asc')
                ->where('e_id', $id)
                ->get();



                $inventoriesCount =  DB::table('inventory_transactions')
                ->orderBy('id', 'asc')
                ->where('e_id', $id)
                ->count();

                $etax = DB::table('inventory_etransaction')
                ->where('id', $id)
                ->get();

               
                return view('user.inventory.expenses.edit2', compact('etax','expcats', 'supps','inventories', 'inventoriesCount', 'formType'));
                    

                }
                public function store2(Request $request) {
                    // Retrieving and cleaning the request data
                    $edit_id = $request->edit_id;
                    $name = $request->name;
                    $other_name = $request->other_name;
                    $status = $request->status;
                    $payment_class = $request->payment_class;
                    $unit = $request->unit;
                    $exp_type = $request->exp_type;
                    $unit_price = str_replace(',', '', $request->unit_price);
                    $quantity = str_replace(',', '', $request->quantity);
                    $price = str_replace(',', '', $request->price);
                
                    // Category processing
                    $categoryArray = explode(",", $request->category);
                    $is_cat_name = $categoryArray[0];
                    $is_cat_id = $categoryArray[1];
                
                    // Supplier processing
                    $SupplierArray = explode(",", $request->supplier);
                    $is_supp_name = $SupplierArray[0];
                    $is_supp_id = $SupplierArray[1];
                
                    // Validation
                    $request->validate([
                        'name' => 'required',
                        'quantity' => 'required',
                        'price' => 'required'
                    ]);
                
                    // Directly inserting into the inventory_transactions table
                    $item = DB::table('inventory_transactions')->insert([
                        'e_id' => $edit_id,
                        'inv_id' => 0, // You can adjust this according to your requirement
                        'type' => strtoupper($is_cat_name),
                        'category_id' => $is_cat_id,
                        'supplier_id' => $is_supp_id,
                        'quantity_before' => 0,
                        'quantity' => $quantity,
                        'quantity_after' => 0,
                        'amount' => $price,
                        'remarks' => $name,
                        'unit' => $unit,
                        'unit_price' => $unit_price,
                        'other_remarks' => $other_name,
                        'is_paid' => $status,
                        'payment_class' => $payment_class,
                        'exp_type' => $exp_type,
                        'user_id' => Auth::user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'ap_payment' => "" // You can leave this empty or update as required
                    ]);
                
                    // Fetch the latest inserted records
                    $inventories = DB::table('inventory_transactions')->latest()->get();
                
                    // Return the latest inventory transactions as JSON
                    return response()->json(['inventories' => $inventories]);
                }
                
               
                


                public function cart_store2(Request $request) {
                    // Validate incoming data
                    $validated = $request->validate([
                        'e_edit_id' => 'required|numeric',
                        'e_total' => 'required|numeric',
                        'e_vat' => 'required|numeric',
                        'e_wtaxpercent' => 'required|numeric',
                        'e_wtax' => 'required|numeric',
                        'e_amount' => 'required|numeric',
                    ]);
                
                    // Get validated data
                    $edit_id = $validated['e_edit_id'];
                    $eTotal = $validated['e_total'];
                    $eVat = $validated['e_vat'];
                    $eWtaxperncentage = $validated['e_wtaxpercent'];
                    $eWtax = $validated['e_wtax'];
                    $eAmount = $validated['e_amount'];
                    
                    // Start the transaction process
                    DB::beginTransaction();
                
                    try {
                        // Update the existing record in inventory_etransaction
                        $updated = DB::table('inventory_etransaction')
                            ->where('id', $edit_id)
                            ->update([
                                'e_total' => $eTotal,
                                'e_vat' => $eVat,
                                'e_wtaxpercent' => $eWtaxperncentage,
                                'e_wtax' => $eWtax,
                                'e_amount' => $eAmount,
                                'updated_at' => now(), // Update timestamp
                            ]);
                
                        // Check if the update was successful
                        if ($updated) {
                            DB::commit();
                            return response()->json(['success' => true, 'message' => 'Record updated successfully.']);
                        } else {
                            DB::rollBack();
                            return response()->json(['success' => false, 'message' => 'Failed to update the record.']);
                        }
                    } catch (\Exception $e) {
                        // Rollback transaction if an error occurs
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
                    }
                }

              
public function editCart($id)
{
    $inventory = InventoryCartTransaction::find($id);

    // Fetch expense categories
    $expcats = DB::table('expenses_categories')
                 ->orderBy('id', 'asc')
                 ->where('is_deleted', 0)
                 ->get();

    // Check if the item exists
    if (!$inventory) {
        return response()->json(['error' => 'Inventory not found'], 404);
    }

    // Transform the exp_type value
    $inventory->exp_type = $inventory->exp_type == 0 ? 'Cost of Sales' : 'Other Expenses';

    // Match the category_id with the category name
    $selectedCategory = $expcats->firstWhere('id', $inventory->category_id);
    $inventory->category_name = $selectedCategory ? $selectedCategory->name : 'Unknown Category';

    // Fetch suppliers
    $suppliers = DB::table('suppliers')
                   ->orderBy('id', 'asc')
                   ->where('is_deleted', 0)
                   ->get();

    // Transform the payment_class value
    $inventory->payment_class = $inventory->payment_class == 0 ? 'Unpaid' : 'Paid';

    // Return the modified inventory data along with categories and suppliers
    return response()->json([
        'inventory' => $inventory,
        'expenses_categories' => $expcats,
        'suppliers' => $suppliers
    ]);
}

public function updateEdit(Request $request, $inventoryId)
{
    // Validate incoming data
    $validated = $request->validate([
        'exp_type' => 'required|in:0,1',
        'supplier_id' => 'required|exists:suppliers,id',
        'category_id' => 'required|exists:expenses_categories,id',
        'name' => 'required|string|max:255',
        'unit' => 'required|string|max:50',
        'quantity' => 'required|numeric|min:1',
        'unit_price' => 'required|numeric|min:0',
        'amount' => 'required|numeric|min:0',
        'payment_class' => 'required|in:0,1',
    ]);

    // Update the database record
    $updated = DB::table('inventory_cart_transactions')
        ->where('id', $inventoryId)
        ->update([
            'exp_type' => $validated['exp_type'],
            'supplier_id' => $validated['supplier_id'],
            'category_id' => $validated['category_id'],
            'remarks' => $validated['name'],
            'unit' => $validated['unit'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'amount' => $validated['amount'],
            'payment_class' => $validated['payment_class'],
        ]);

    if ($updated) {
        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully'
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Failed to update inventory']);
    }
}


public function updateEdit2(Request $request, $inventoryId)
{
    // Validate incoming data
    $validated = $request->validate([
        'exp_type' => 'required|in:0,1',
        'supplier_id' => 'required|exists:suppliers,id',
        'category_id' => 'required|exists:expenses_categories,id',
        'name' => 'required|string|max:255',
        'unit' => 'required|string|max:50',
        'quantity' => 'required|numeric|min:1',
        'unit_price' => 'required|numeric|min:0',
        'amount' => 'required|numeric|min:0',
        'payment_class' => 'required|in:0,1',
    ]);

    // Update the database record
    $updated = DB::table('inventory_transactions')
        ->where('id', $inventoryId)
        ->update([
            'exp_type' => $validated['exp_type'],
            'supplier_id' => $validated['supplier_id'],
            'category_id' => $validated['category_id'],
            'remarks' => $validated['name'],
            'unit' => $validated['unit'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'amount' => $validated['amount'],
            'payment_class' => $validated['payment_class'],
        ]);

    if ($updated) {
        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully'
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Failed to update inventory']);
    }
}


public function tran_remove(Request $request)
{
    $slug = $request->slug;

    // Check if the record exists
    $transaction = DB::table('inventory_etransaction')->where('id', $slug)->first();

    if ($transaction) {
        // Perform the update if the record exists
        $updated = DB::table('inventory_etransaction')
            ->where('id', $slug)
            ->update([
                'is_deleted' => 1,  // Storing as an integer, not as a string '3'
                'updated_at' => now(), // Update timestamp
            ]);

        // Optionally, return a response or success message
        return response()->json(['success' => true, 'message' => 'Transaction Remove successfully.']);
    } else {
        // Handle case when the transaction is not found
        return response()->json(['success' => false, 'message' => 'Transaction not found.']);
    }
}



public function cart_delete2(Request $request){
    $slug = $request->slug;
    DB::table('inventory_transactions')->where('id', $slug)->delete();
    
    return response()->json(['success' => true, 'message' => 'Data deleted successfully.']);
}
                
public function editCart2($id)
{
    $inventory = InventoryTransaction::find($id);

    // Fetch expense categories
    $expcats = DB::table('expenses_categories')
                 ->orderBy('id', 'asc')
                 ->where('is_deleted', 0)
                 ->get();

    // Check if the item exists
    if (!$inventory) {
        return response()->json(['error' => 'Inventory not found'], 404);
    }

    // Transform the exp_type value
    $inventory->exp_type = $inventory->exp_type == 0 ? 'Cost of Sales' : 'Other Expenses';

    // Match the category_id with the category name
    $selectedCategory = $expcats->firstWhere('id', $inventory->category_id);
    $inventory->category_name = $selectedCategory ? $selectedCategory->name : 'Unknown Category';

    // Fetch suppliers
    $suppliers = DB::table('suppliers')
                   ->orderBy('id', 'asc')
                   ->where('is_deleted', 0)
                   ->get();

    // Transform the payment_class value
    $inventory->payment_class = $inventory->payment_class == 0 ? 'Unpaid' : 'Paid';

    // Return the modified inventory data along with categories and suppliers
    return response()->json([
        'inventory' => $inventory,
        'expenses_categories' => $expcats,
        'suppliers' => $suppliers
    ]);
}




}
