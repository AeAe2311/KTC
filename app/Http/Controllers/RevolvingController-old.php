<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryCartTransaction;
use App\Models\RevolvingCartTransaction;
use App\Models\RevolvingTransaction;
use App\Models\RevolvingBudget;
use App\Models\ExpensesCategory;
use App\Models\Supplier;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RevolvingController extends Controller
{
    public function index(){
        $today = Carbon::today();
        $budget = DB::table('revolving_budgets')->where('id', 1)->first();
        $inventories = RevolvingTransaction::leftJoin('expenses_categories as ec','revolving_transactions.category_id','=','ec.id')
        ->leftJoin('suppliers as s','revolving_transactions.supplier_id','=','s.id')
        ->select('revolving_transactions.*', 'ec.name as cat_name', 's.name as sup_name')
       // ->whereDate('created_at', $today)
       ->orderBy('revolving_transactions.created_at', 'DESC')
       ->where('revolving_transactions.store_code', Auth::user()->store_code)
        ->where('revolving_transactions.inv_id', 0)
        ->where('revolving_transactions.is_deleted', 0)
        ->paginate(100);
       // dd($inventories);
        
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
            ->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');

        $nonAudited = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 0)
            ->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');
        /* end of Audited total */

        $servername = DB::table('users')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        
        return view('user.inventory.revolving.index', compact('servername','budget','inventories', 'invCount', 'page', 'search','audited','nonAudited'));
    }

    public function paginate($page){
        $today = Carbon::today();
        $inventories = RevolvingTransaction::leftJoin('expenses_categories as ec','revolving_transactions.category_id','=','ec.id')
        ->leftJoin('suppliers as s','revolving_transactions.supplier_id','=','s.id')
        ->select('revolving_transactions.*', 'ec.name as cat_name', 's.name as sup_name')
       // ->whereDate('created_at', $today)
       ->orderBy('revolving_transactions.created_at', 'DESC')
       ->where('revolving_transactions.store_code', Auth::user()->store_code)
        ->where('revolving_transactions.inv_id', 0)
        ->where('revolving_transactions.is_deleted', 0)
        ->paginate(100,'*','page',$page);

        // $inventories = DB::table('inventories')
        //     ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug')
        //     ->join('categories', 'inventories.category_id', 'categories.id')
        //     ->where('is_expenses', 1)
        //     ->orderBy('name', 'asc')
        //     ->paginate(100,'*','page',$page);

        
        $invCount = $inventories->total();
        // $invCount = DB::table('inventories')->where('is_expenses', 1)->get()->count();

        $servername = DB::table('users')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        $search = "";

        return view('user.inventory.revolving.index', compact('inventories', 'invCount', 'page', 'search','servername'));
    }

    public function budget_index(){
        $today = Carbon::today();
        $inventories = RevolvingTransaction::with('ExpensesCategory','supplier')
        ->whereDate('created_at', $today)
        ->where('inv_id', 0)
        ->where('revolving_transactions.store_code', Auth::user()->store_code)
        ->paginate(100);

        
        // $inventories = DB::table('inventories')
        //     ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug')
        //     ->join('categories', 'inventories.category_id', 'categories.id')
        //     ->where('is_expenses', 1)
        //     ->orderBy('name', 'asc')
        //     ->paginate(100);
        $invCount = $inventories->count();
        // $invCount = DB::table('inventories')->where('is_expenses', 1)->get()->count();
        $page = 1;
        $search = "";

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

        // revolving budget
        $revolving_budget = DB::table('revolving_budgets')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0) //240506001 - leo - 7/22/2026
        ->orderBy('id', 'asc')->paginate(100);
        $tables = DB::table('tables')->orderBy('name', 'asc')->where('is_deleted', 0)->paginate(100);
        // end of revolving budget
        
        return view('user.inventory.revolving.budget_index', compact('revolving_budget','inventories', 'invCount', 'page', 'search','audited','nonAudited'));
    }


    public function search($page, $search){
        $today = Carbon::today();
        $inventories = RevolvingTransaction::leftJoin('expenses_categories as ec','revolving_transactions.category_id','=','ec.id')
            ->leftJoin('suppliers as s','revolving_transactions.supplier_id','=','s.id')
            ->select(
                'revolving_transactions.*',
                'ec.name as cat_name',
                's.name as sup_name'
            )
            ->where('revolving_transactions.store_code', Auth::user()->store_code)
            //->whereDate('revolving_transactions.created_at', $today)
            ->where(function ($query) use ($search) {
                $query->where('revolving_transactions.remarks', 'like', "%{$search}%")
                ->orWhere('revolving_transactions.particulars', 'like', "%{$search}%")
                ->orWhere('revolving_transactions.description', 'like', "%{$search}%")
                ->orWhere('ec.name', 'like', "%{$search}%");
                    
            })
            //->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('revolving_transactions.inv_id', 0)
            ->paginate(100, '*', 'page', $page);

        //echo json_encode($inventories);
        $invCount = $inventories->count();

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
        
         return view('user.inventory.revolving.index', compact('inventories', 'invCount', 'page', 'search'));
    }

    public function add(){
        $budget = DB::table('revolving_budgets')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0) //240506001 - leo - 7/22/2026
        ->orderBy('id', 'asc')->get();
        $expcats = DB::table('expenses_categories')->orderBy('name', 'asc')->where('is_deleted', 0)->get();

        $supps = DB::table('suppliers')->orderBy('id', 'asc')
        //->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)->get();

        $today = Carbon::today();
        $inventories = RevolvingCartTransaction::with('ExpensesCategory','supplier')
        ->where('inv_id', 0)
        //->where('revolving_transactions.store_code', Auth::user()->store_code)
        ->get();
        $inventoriesCount = RevolvingCartTransaction::with('ExpensesCategory','supplier')
        ->where('inv_id', 0)->count();

        // note for inventories, i removed wheredate so it will show everything

        // for inventoriesCount incase of bug
        // ->whereDate('created_at', $today)

        return view('user.inventory.revolving.add', compact('budget','expcats', 'supps', 'inventories', 'inventoriesCount'));
    }
    
    
    public function addNewBudget(){
        $budget = DB::table('revolving_budgets')
        ->where('store_code', Auth::user()->store_code)
        ->orderBy('id', 'asc')->get();
        $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $today = Carbon::today();
        $inventories = RevolvingCartTransaction::with('ExpensesCategory','supplier')
        ->whereDate('created_at', $today)->where('inv_id', 0)->get();
        $inventoriesCount = RevolvingCartTransaction::with('ExpensesCategory','supplier')
        ->whereDate('created_at', $today)->where('inv_id', 0)->count();


        return view('user.inventory.revolving.add_new_budget', compact('budget','expcats', 'supps', 'inventories', 'inventoriesCount'));
    }

    public function budget_store(Request $request) {
        $new_budget = $request->new_budget;
        //dd(Auth::user()->branch_code);
    
        $item = new RevolvingBudget();
        $item->classification = $new_budget;
        $item->current_budget = 0;
        $item->is_deleted = 0;
        $item->user_id = Auth::user()->id;
        $item->branch_code = Auth::user()->branch_code;
        $item->store_code = Auth::user()->store_code;

        
    
        if(Auth::user()->role == 1){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
            $item->created_at = date('Y/m/d H:i:s', strtotime($dateAdd));
        }
    
        $item->save();
    
        return redirect()->route('revolving.budget_index')->withInput()->with('message', 'Successfully Added');
    }


    

    public function store(Request $request) {
        $name = $request->name;
        $status = $request->status;
        // $list = $request->list;
        $price = str_replace(',', '', $request->price);

        
        
        // $budget = DB::table('revolving_budgets')->where('id', $list)->first();
        
        // if (!$budget){
        //     return response()->json(['error' => 'Selected budget not found.'], 404);
        // }

        // $calculatedBudget = $budget->current_budget - $price;
        // DB::table('revolving_budgets')->where('id', $budget->id)->update([
        //     'current_budget' => $calculatedBudget,
        // ]);
        // revolving
        $particulars = $request->particulars;
        $description = $request->description;
        $quantity = str_replace(',', '', $request->quantity);
        $unit_amount = str_replace(',', '', $request->unit_amount);
        $rev_id = $request->list;
        $category_id = $request->category_id;
        $other_name = $request->other_name;
    
        $item = new RevolvingCartTransaction();
        $item->inv_id = 0;
        $item->quantity_before = 0;
        $item->quantity = $quantity;
        $item->quantity_after = 0;
        $item->amount = $price;
        $item->other_remarks = $other_name;
        $item->user_id = Auth::user()->id;
        $item->particulars = $particulars;
        $item->description = $description;
        $item->unit_amount = $unit_amount;
        $item->category_id = $category_id;
        $item->rev_id = $rev_id;

        
    
        if(Auth::user()->role == 1){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
            $item->created_at = date('Y/m/d H:i:s', strtotime($dateAdd));
        }
    
        $item->save();
    
        // Return the latest inventories as JSON response
        $inventories = RevolvingCartTransaction::latest()->get(); // You can adjust the query as needed
    
        return response()->json(['inventories' => $inventories]);
        // , 'budgets' => $budget
    }

    public function cart_store(Request $request)
{
    DB::beginTransaction();

    try {
        // Retrieve all transactions for the current user
        $transactions = DB::table('revolving_cart_transactions')->where('user_id', auth()->id())->get();

        foreach ($transactions as $transaction) {
            $rev_id = $transaction->rev_id;

            // Get the corresponding budget for the rev_id
            $budget = DB::table('revolving_budgets')->where('id', $rev_id)->first();
            if (!$budget) {
                return response()->json(['error' => "Budget with ID {$rev_id} not found."], 404);
            }

            // Calculate the new budget
            $calculatedBudget = $budget->current_budget - $transaction->amount;

            // Update the budget
            DB::table('revolving_budgets')->where('id', $budget->id)->update([
                'current_budget' => $calculatedBudget,
            ]);

            // Insert the transaction into the revolving_transactions table
            DB::table('revolving_transactions')->insert([
                'inv_id' => $transaction->inv_id,
                'category_id' => $transaction->category_id,
                'quantity_before' => $transaction->quantity_before,
                'quantity' => $transaction->quantity,
                'quantity_after' => $transaction->quantity_after,
                'amount' => $transaction->amount,
                'other_remarks' => $transaction->other_remarks,
                'user_id' => $transaction->user_id,
                'particulars' => $transaction->particulars,
                'description' => $transaction->description,
                'unit_amount' => $transaction->unit_amount,
                'rev_id' => $transaction->rev_id,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
                'branch_code' => Auth::user()->branch_code,
                'store_code' => Auth::user()->store_code,
                
            ]);
        }

        // Delete all transactions from the revolving_cart_transactions table
        DB::table('revolving_cart_transactions')->where('user_id', auth()->id())->delete();

        DB::commit();

        return response()->json(['success' => true, 'message' => 'Data transferred and budgets updated successfully.']);
    } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

    public function cart_delete(Request $request){
        $slug = $request->slug;
        DB::table('revolving_cart_transactions')->where('id', $slug)->delete();
        
        return response()->json(['success' => true, 'message' => 'Data deleted successfully.']);
    }

    


    public function edit($id){
        
        $item = RevolvingTransaction::where('id', $id)->first();
        
        $expcats = DB::table('expenses_categories')->orderBy('name', 'asc')->where('is_deleted', 0)->get();

        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        return view('user.inventory.revolving.edit', compact('item','expcats', 'supps'));
    }

    public function budget($id){
        $budget = RevolvingBudget::where('id', $id)->first();

        return view('user.inventory.revolving.budget', compact('budget'));
    }

    

    public function update(Request $request){
        $id = $request->id;
        $trans = DB::table('revolving_transactions')->where('id', $id)->first();

        $category_id = $request->category;
        //dd($category_id);
        if(Auth::user()->role == 1){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
        }else{
            $dateAdd = date('Y/m/d H:i:s');
        }
        $unit_amount = $request->unit_amount;
        $total_amount = $request->total_amount;
        $remarks = $request->remarks;
        DB::table('revolving_transactions')->where('id', $id)
            ->update([
                'category_id' => $category_id,
                'unit_amount' => $unit_amount,
                'amount' => $total_amount,
                'other_remarks' => $remarks,
                'updated_at' => date('Y/m/d H:i:s', strtotime($dateAdd))
            ]);

            $log = new Log();
            $log->trans_id = $trans->id;
            $log->classification = 'Expenses';
            $log->info = 'Updated Revolving Amount';
            $log->user_id = auth()->id();
            $log->event = 'Update';
            $log->save();

        return redirect()->route('revolving.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function update_budget(Request $request){
        $id = $request->id;
        $current_budget = $request->current_budget;
        $trans = DB::table('revolving_budgets')->where('id', $id)->first();
        

        // $budget = RevolvingBudget::where('id', $id)->get();
        

        // if (strtoupper($type) != 'OTHERS'){
        //    $type_txt = '';
        // }
        if(Auth::user()->role == 1){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
        }else{
            $dateAdd = date('Y/m/d H:i:s');
        }
        
        DB::table('revolving_budgets')->where('id', $id)
            ->update([
                'current_budget' => $current_budget,
                'user_id' => Auth::user()->id,
                'created_at' => date('Y/m/d H:i:s', strtotime($dateAdd))
            ]);

            $log = new Log();
            $log->trans_id = $trans->id;
            $log->classification = 'Expenses';
            $log->info = 'Updated Budget Amount';
            $log->user_id = auth()->id();
            $log->event = 'Update';
            $log->save();

        return redirect()->route('revolving.budget_index')->withInput()->with('message', 'Successfully Updated');
    }

    public function add_budget($id){
        $budget = RevolvingBudget::where('id', $id)->first();

        return view('user.inventory.revolving.add_budget', compact('budget'));
    }

    public function addition_budget(Request $request){
        $id = $request->id;
        $new_budget = $request->current_budget;
        $trans = DB::table('revolving_budgets')->where('id', $id)->first();
    
        // Retrieve the current budget value from the database
        $current_budget = DB::table('revolving_budgets')->where('id', $id)->value('current_budget');
    
        // Check if the current budget is numeric
        if (!is_numeric($current_budget)) {
            return redirect()->route('revolving.budget_index')
                ->with('error', 'Invalid current budget value');
        }
    
        // Calculate the new budget
        $comp_budget = $new_budget + $current_budget;
    
        // Determine the dateAdd value based on the user's role
        if (Auth::user()->role == 1) {
            $dateAdd = $request->dateAdd . ' ' . date('H:i:s');
        } else {
            $dateAdd = date('Y/m/d H:i:s');
        }
    
        // Update the database
        DB::table('revolving_budgets')->where('id', $id)
            ->update([
                'current_budget' => $comp_budget, // Correctly add to the budget
                'user_id' => Auth::user()->id,
                'created_at' => date('Y/m/d H:i:s', strtotime($dateAdd)),
            ]);

            $log = new Log();
            $log->trans_id = $trans->id;
            $log->classification = 'Expenses';
            $log->info = 'Added Budget Amount';
            $log->user_id = auth()->id();
            $log->event = 'Add';
            $log->save();
    
        // Redirect with a success message
        return redirect()->route('revolving.budget_index')
            ->withInput()
            ->with('message', 'Successfully Updated');
    }
    

    public function delete($id)
{
    // Retrieve the transaction first
    $trans = DB::table('revolving_transactions')->where('id', $id)->first();

    if ($trans) {
        
        $rev_trans = DB::table('revolving_budgets')->where('id', $trans->rev_id)->first();
        
        $new_budget = $rev_trans->current_budget + $trans->amount;
        DB::table('revolving_budgets')->where('id', $rev_trans->id)
            ->update([
                'current_budget' => $new_budget,
                'updated_at' => now()
            ]);
        //dd($new_budget);    
        // Delete the transaction
        DB::table('revolving_transactions')->where('id', $id)
            ->update([
                'is_deleted' => 1
            ]);
            
        //DB::table('revolving_transactions')->where('id', $id)->delete();
        // Log the deletion
        $log = new Log();
        $log->trans_id = $trans->id;
        $log->classification = 'Revolving Expenses';
        $log->info = 'Deleted Expenses';
        $log->user_id = auth()->id();
        $log->event = 'Delete';
        $log->save();

        return redirect()->route('revolving.index')->with('message', 'Successfully Deleted');
    } else {
        // Handle the case where the transaction doesn't exist
        return redirect()->route('revolving.index')->with('error', 'Transaction not found');
    }
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

        $it = new RevolvingTransaction();
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

        return redirect()->route('revolving.index')->withInput()->with('message', 'Quantity Successfully Increased');
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
}
