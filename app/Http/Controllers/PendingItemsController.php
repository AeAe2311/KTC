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

class PendingItemsController extends Controller
{
    public function index(){

        $item = DB::table('item_temp')
        ->leftJoin('users', 'item_temp.user_id', '=', 'users.id')
        ->leftJoin('item_masterlist', 'item_temp.user_id', '=', 'item_masterlist.user_id')
        ->select('item_temp.*', 'users.name as user_name', 'users.company_name', 'item_masterlist.is_deleted')
        ->where('item_temp.is_deleted', 0)
        ->whereNull('item_temp.item_approval')
        ->where('item_temp.status', '=' ,'PENDING')
        ->orderBy('users.name', 'asc')
        ->paginate(100);    

        $itemCount = DB::table('item_temp')->get()->count();
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $page = 1;
        $search = "";
        return view('user.pending-items.index', compact('item', 'itemCount', 'page', 'search','depts'));
    }

    public function paginate($page){
        $page = (int) $page;
        $item = DB::table('item_temp')
        ->select('item_temp.*')
        ->where('item_temp.is_deleted', 0)
        ->orderBy('item_name', 'asc')
        ->paginate(100,'*','page',$page);
        $itemCount = DB::table('item_temp')->get()->count();
        $search = "";
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        return view('admin.system-management.users.index', compact('item', 'itemCount', 'page', 'search','depts'));
    }

    public function search($page, $search){
        $item = DB::table('item_temp')
        ->select('item_temp.*')
        ->where('item_temp.is_deleted', 0)
        ->orderBy('item_name', 'asc')
        ->whereRaw("CONCAT_WS(' ', item.item_name) LIKE '%{$search}%'")
        ->paginate(100, '*', 'page', $page);

        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $itemCount = DB::table('item_temp')
            ->where('users.is_deleted', 0)
            ->whereRaw("CONCAT_WS(' ', item.item_name) LIKE '%{$search}%'")
            ->orderBy('item_name', 'asc')
            ->count();

            
        return view('admin.system-management.users.index', compact('item', 'itemCount', 'page', 'search','depts'));
    }

    
    public function view(Request $request)
    {
       $item = DB::table('item_temp')->where('id', $request->user_id)->first();

    if (!$item) {
        return response()->json([
            'users' => '<p class="text-red-500 text-sm">Item not found.</p>'
        ]);
    }

    $items = DB::table('item_temp')
        ->leftJoin('users', 'item_temp.user_id', '=', 'users.id')
        ->leftJoin('item_brands', 'item_temp.brand_id', '=', 'item_brands.id')
        ->select('item_temp.*', 'users.name as user_name', 'users.company_name', 'item_temp.item_name','item_brands.name as brand_name')
        ->where('item_temp.is_deleted', 0)
        ->where('item_temp.user_id', $item->user_id)
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

        foreach ($items as $item) {
            $viewUser .= '<li>'.$item->item_name. ' - ' . $item->brand_name . '</li>';
        }

        $viewUser .= '</ul>';

        return response()->json(['item' => $viewUser]);
    }

    public function item_approval(Request $request, $id) {

        $userId = $request->input('user_id');
        $item_approval = $request->input('item_approval'); 

        $items = DB::table('item_temp')
        ->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('is_deleted', 0)
                ->where(function ($q) {
                    $q->where('status', 'PENDING')
                    ->orWhere('for_delete', 'For-Delete');
                });
        })
        ->get();

        if ($items->isEmpty()) {
        return redirect()->route('pending_item.index')->withErrors('No pending items found for this user.');
        }
   
        DB::table('item_temp')
        ->where('user_id', $userId)
        ->where('is_deleted', 0)
        ->update([
            'item_approval' => $item_approval,
            'updated_at' => now()
        ]);

        DB::table('item_logs')
        ->where('user_id', $userId)
        ->update([
            'commi_approved' => $item_approval,
            'updated_at' => now()
        ]);

        

        DB::table('item_temp')->where('user_id', $userId)->delete();


        foreach ($items as $item) {

        $exists = DB::table('item_masterlist')->where('id', $item->id)->exists();

        if (!$exists) {
            DB::table('item_masterlist')->insert([
                'item_name' => $item->item_name,
                'inv_slug' => $item->inv_slug,
                'inv_id' => $item->inv_id,
                'item_category' => $item->item_category,
                'item_department' => $item->item_department,
                'user_id' => $item->user_id,
                'brand_id' => $item->brand_id,
                'status' => $item->status,
                'item_approval' => $item_approval,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
                }
            }
        

        return redirect()->route('pending_item.index')->withInput()->with('message', 'Item ' . ucfirst(strtolower($item_approval)) . ' successfully.');
    }


    public function delete($slug){
        DB::table('users')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('user.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
