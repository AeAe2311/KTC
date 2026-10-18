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

class ERequestController extends Controller
{
    public function index(){
        $today = Carbon::today();
        $inventories = InventoryTransaction::with('ExpensesCategory','supplier')
       // ->whereDate('created_at', $today)
       ->where('inv_id', 0)->paginate(100);

       $e_inventories = DB::table('inventory_etransaction')
       ->leftjoin('users', 'users.id','=','inventory_etransaction.e_server')
        ->select('*', 'inventory_etransaction.id as e_id')
        ->where('e_approval', '!=', 0)
        ->where('e_approval', '!=', 3)
        ->orderBy('inventory_etransaction.id', 'desc')
        ->get();
        
        $servername = DB::table('users')->orderBy('id', 'asc')->get();

        // $inventories = DB::table('inventories')
        //     ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug')
        //     ->join('categories', 'inventories.category_id', 'categories.id')
        //     ->where('is_expenses', 1)
        //     ->orderBy('name', 'asc')
        //     ->paginate(100);
        $invCount = InventoryTransaction::with('ExpensesCategory','supplier')->whereDate('created_at', $today)->where('inv_id', 0)->get()->count();
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
        
        return view('user.inventory.e_request.index', compact('servername','e_inventories','inventories', 'invCount', 'page', 'search','audited','nonAudited'));
    }




    public function paginate($page){
        $today = Carbon::today();
        $inventories = InventoryTransaction::with('ExpensesCategory','supplier')->whereDate('created_at', $today)->where('inv_id', 0)->paginate(100,'*','page',$page);

        // $inventories = DB::table('inventories')
        //     ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug')
        //     ->join('categories', 'inventories.category_id', 'categories.id')
        //     ->where('is_expenses', 1)
        //     ->orderBy('name', 'asc')
        //     ->paginate(100,'*','page',$page);

        $invCount = InventoryTransaction::with('ExpensesCategory','supplier')
        ->whereDate('created_at', $today)
        ->where('inv_id', 0)->get()->count();
        // $invCount = DB::table('inventories')->where('is_expenses', 1)->get()->count();

        $search = "";

        return view('user.inventory.e_request.index', compact('inventories', 'invCount', 'page', 'search'));
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

        return view('user.inventory.e_request.index', compact('inventories', 'invCount', 'page', 'search'));
    }




    public function updateStatusApproved(Request $request)
    { 
        
        $validated = $request->validate([
        'id' => 'required|numeric',
        'app' => 'required|numeric',
        ]);

        $eid = $validated['id'];
       
            // Update status and approval if app is 1
            $updated = DB::table('inventory_etransaction')
                ->where('id', $eid)
                ->update([
                    'e_status' => 'Approved',
                    'e_approval' => '2',
                    'is_deleted' => 1,   // harold 9-13
                ]);
                
                // harold 9-13
        $updated2 = DB::table('inventory_transactions')
                ->where('e_id', $eid)
                ->update([
                    'is_deleted' => 1,   // harold 9-13
                ]);
                // harold 9-13
            
            return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
        
           
    }




    



}
