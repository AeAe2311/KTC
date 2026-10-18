<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\SupplierRequirementFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use App\Models\Log;

class ItemLogsController extends Controller
{
    public function index(){

        $logs = DB::table('item_logs')
        ->leftJoin('users as supplier', 'item_logs.user_id', '=', 'supplier.id') // supplier
        ->leftJoin('users as admin', 'item_logs.admin_id', '=', 'admin.id') // admin
        ->select('item_logs.*', 'supplier.name as user_name', 'supplier.company_name', 'admin.name as admin_name')
        ->where('item_logs.is_deleted', 0)
        ->where('item_logs.commi_approved', 'APPROVED')
        ->where('supplier.is_deleted', 0)
        ->orderBy('item_logs.created_at', 'desc')
        ->paginate(100);    

        $logsCount = DB::table('item_logs')->get()->count();
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $page = 1;
        $search = "";
        return view('user.item-logs.index', compact('logs', 'logsCount', 'page', 'search','depts'));
    }

    public function paginate($page){
        $page = (int) $page;
        $logs = DB::table('item_logs')
        ->leftJoin('users as supplier', 'item_logs.user_id', '=', 'supplier.id') // supplier
        ->leftJoin('users as admin', 'item_logs.admin_id', '=', 'admin.id') // admin
        ->select('item_logs.*', 'supplier.name as user_name', 'supplier.company_name', 'admin.name as admin_name')
        ->where('item_logs.is_deleted', 0)
        ->where('item_logs.commi_approved', 'APPROVED')
        ->where('supplier.is_deleted', 0)
        ->orderBy('item_logs.created_at', 'desc')
        ->paginate(100,'*','page',$page);
        $logsCount = DB::table('item_logs')->get()->count();
        $search = "";
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        return view('user.item-logs.index', compact('logs', 'logsCount', 'page', 'search','depts'));
    }

    public function search($page, $search){
        $logs = DB::table('item_logs')
        ->leftJoin('users as supplier', 'item_logs.user_id', '=', 'supplier.id') // supplier
        ->leftJoin('users as admin', 'item_logs.admin_id', '=', 'admin.id') // admin
        ->select('item_logs.*', 'supplier.name as user_name', 'supplier.company_name', 'admin.name as admin_name')
        ->where('item_logs.is_deleted', 0)
        ->where('item_logs.commi_approved', 'APPROVED')
        ->orderBy('item_logs.created_at', 'desc')
        ->where('supplier.is_deleted', 0)
        ->whereRaw("CONCAT_WS(' ', item_logs.item_name) LIKE '%{$search}%'")
        ->paginate(100, '*', 'page', $page);

        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $logsCount = DB::table('item_logs')
            ->leftJoin('users as supplier', 'item_logs.user_id', '=', 'supplier.id') // supplier
            ->leftJoin('users as admin', 'item_logs.admin_id', '=', 'admin.id') // admin
            ->select('item_logs.*', 'supplier.name as user_name', 'supplier.company_name', 'admin.name as admin_name')
            ->where('supplier.is_deleted', 0)
            ->where('item_logs.commi_approved', 'APPROVED')
            ->where('item_logs.is_deleted', 0)
            ->whereRaw("CONCAT_WS(' ', item_logs.item_name) LIKE '%{$search}%'")
            ->orderBy('item_logs.created_at', 'desc')
            ->count();

            
        return view('user.item-logs.index', compact('logs', 'logsCount', 'page', 'search','depts'));
    }

    public function add() {
        $categories = DB::table('categories')->orderBy('name', 'asc')->where('slug', '!=', 'menu')->where('is_deleted', 0)->get();

        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();
        $masterlists = DB::table('masterlists')->orderBy('name', 'asc')->where('is_deleted', 0)->get();

        return view('user.item.add', compact('categories','depts','branches','masterlists'));
    }

    public function store(Request $request) {
        $item_code = $request->item_code;
        //$name = $request->itemName;
        $masterlistID = $request->itemID;
        //$category_id = $request->category_id;
        // $quantity = $request->quantity;
        $reorder_point = $request->reorder_point;
        //$unit = $request->unit;
        $price = $request->price;
        $image = $request->image;
        $department = $request->department;
        $branch = $request->branch;
        
        $mlist = DB::table('masterlists')->where('id', $masterlistID)->first();

        $nameExist = Inventory::where('name', $mlist->name)->where('department', $department)->where('branch', $branch)->first();
        if ($nameExist) {
            return back()->withInput()->with('error', 'Item already exist!');
        }

        $slug = Str::slug($mlist->name, '-');
        $check_slug = DB::table('inventories')->where('slug', $slug)->get();
        $x = 1;
        $nslug = $slug;
        while (count($check_slug) > 0) {
            $nslug = $slug . '-' . $x;
            $check_slug = DB::table('inventories')->where('slug', $nslug)->get();
            $x++;
        }
        $slug = $nslug;


        $request->validate([
            'itemID' => 'required',
            'reorder_point' => 'required'
        ]);

        $item = new Inventory();
        $item->item_code = $item_code;
        $item->name = $mlist->name;
        $item->category_id = $mlist->category_id;
        $item->quantity = 0;
        $item->reorder_point = $reorder_point;
        $item->unit = $mlist->unit;
        $item->price = $mlist->price;
        $item->masterlist_id = $mlist->id;
        $item->slug = $slug;
        $item->department = $department;
        $item->branch = $branch;
        $item->save();

        return redirect()->route('item.index')->withInput()->with('message', 'Successfully Added');
    }

    
    public function view(Request $request)
    {
        $item = DB::table('item_masterlist')->where('id', $request->user_id)->first();

        if (!$item) {
            return response()->json([
                'item' => '<p class="text-red-500 text-sm">Item not found.</p>'
            ]);
        }

        $items = DB::table('item_masterlist')
            ->leftJoin('users', 'item_masterlist.user_id', '=', 'users.id')
            ->select('item_masterlist.*', 'users.name as user_name', 'users.company_name', 'item_masterlist.item_name')
            ->where('item_masterlist.is_deleted', 0)
            ->where('item_masterlist.status', 'PENDING')
            ->where('item_masterlist.user_id', $item->user_id)
            ->orderBy('users.name', 'asc')
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'item' => '<p class="text-red-500 text-sm">No items found for this user.</p>'
            ]);
        }

        $user = $items->first();

        $viewUser = '
            <p><span class="text-gray-600 text-sm">Name:</span> <span class="text-sm">'.$user->user_name.'</span></p>
            <p><span class="text-gray-600 text-sm">Company:</span> <span class="text-sm">'.$user->company_name.'</span></p>
            <p class="text-gray-600 text-sm mt-2">Item(s):</p>
            <ul class="list-disc ml-6 text-sm text-gray-700">';

        foreach ($items as $itemRow) {
            $viewUser .= '<li>'.$itemRow->item_name.'</li>';
        }

        $viewUser .= '</ul>';

        return response()->json(['item' => $viewUser]);
    }

    public function item_approval(Request $request, $id) {

        $userId = $request->input('user_id');
        $item_approval = $request->input('item_approval'); 

        

        $items = DB::table('item_temp')
        ->where('user_id', $userId)
        ->where('status', 'PENDING')
        ->where('is_deleted', 0)
        ->get();

        if ($items->isEmpty()) {
        return redirect()->route('pending_item.index')->withErrors('No pending items found for this user.');
        }
   
        DB::table('item_temp')
        ->where('user_id', $userId)
        ->where('status', 'PENDING')
        ->where('is_deleted', 0)
        ->update([
            'item_approval' => $item_approval,
            'updated_at' => now()
        ]);


        foreach ($items as $item) {
        $exists = DB::table('item_masterlist')->where('id', $item->id)->exists();

        if (!$exists) {
            DB::table('item_masterlist')->insert([
                'id' => $item->id,
                'item_name' => $item->item_name,
                'inv_slug' => $item->inv_slug,
                'inv_slug' => $item->inv_slug,
                'item_category' => $item->item_category,
                'item_department' => $item->item_department,
                'user_id' => $item->user_id,
                'status' => $item->status,
                'item_approval' => $item_approval,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }



        return redirect()->route('item.index')->withInput()->with('message', 'Item ' . ucfirst(strtolower($item_approval)) . ' successfully.');
    }


    public function delete($userId){

        $authID = auth()->user()->id;

        $items = DB::table('item_masterlist')
            ->where('user_id', $userId)
            ->where('is_deleted', 0)
            ->get();


        DB::table('item_masterlist')
            ->where('user_id', $userId)
            ->update([
                'is_deleted' => 1,
            ]);


        foreach ($items as $item) {
            DB::table('item_temp')->insert([
                'inv_slug' => $item->inv_slug, 
                'inv_id' => $item->inv_id,
                'user_id' => $item->user_id,
                'item_name' => $item->item_name,
                'item_category' => $item->item_category,
                'item_department' => $item->item_department,
                'for_delete' => 'For-Delete',
                'admin_id' => $authID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('item_logs')->insert([
                'user_id' => $item->user_id,
                'inv_slug' => $item->inv_slug, 
                'inv_id' => $item->inv_id,
                'item_name' => $item->item_name,
                'item_category' => $item->item_category,
                'item_department' => $item->item_department,
                'status' => 'Delete',
                'admin_id' => $authID,
                'created_at' => now(),
                'updated_at' => now(),
        ]);
        }

        

        return redirect()->route('item.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
