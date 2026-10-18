<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Convertion;
use App\Models\Masterlist;
use App\Models\InventoryTransaction;
use App\Models\Inventory;
use App\Models\Waste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterlistController extends Controller {
    public function index() {
        $menu_cat = Category::where('slug', 'menu')->first();
        if ($menu_cat) {
            $menu_id = $menu_cat->id;
        } else {
            $menu_id = 0;
        }
        /* if (auth()->user()->role != 4 && auth()->user()->role != 2){ */
            $inventories = DB::table('masterlists')
            ->select( 'masterlists.name', 'categories.name AS cat_name', 'masterlists.category_id', 'masterlists.unit',  'masterlists.slug','masterlists.price')
            ->join('categories', 'masterlists.category_id', 'categories.id')
            ->where('masterlists.is_deleted', 0)
            ->orderBy('name', 'asc')
            ->paginate(100);
        /* } else {
            $inventories = DB::table('masterlists')
            ->select('masterlists.name', 'categories.name AS cat_name', 'masterlists.category_id', 'masterlists.unit',  'masterlists.slug')
            ->join('categories', 'masterlists.category_id', 'categories.id')
            ->where('masterlists.is_deleted', 0)
            ->where('department',1)
            ->orwhere('department',2)
            ->orwhere('department',3)
            ->orwhere('department',4)
            ->orwhere('department',5)
            ->orderBy('name', 'asc')
            ->paginate(100);
        } */
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $invCount = $inventories->total();
        $page = 1;
        $search = "";

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();

        return view('user.reciever.masterlist.index', compact('inventories', 'invCount', 'page', 'search', 'menu_id','depts','branches'));
    }

    public function paginate($page) {
        $menu_cat = Category::where('slug', 'menu')->first();
        if ($menu_cat) {
            $menu_id = $menu_cat->id;
        } else {
            $menu_id = 0;
        }
        /* if (auth()->user()->role != 4 && auth()->user()->role != 2){ */
            $inventories = DB::table('masterlists')
            ->select('masterlists.name', 'categories.name AS cat_name', 'masterlists.category_id', 'masterlists.unit',  'masterlists.slug','masterlists.price')
            ->join('categories', 'masterlists.category_id', 'categories.id')
            ->where('masterlists.is_deleted', 0)
            ->orderBy('name', 'asc')
            ->paginate(100, '*', 'page', $page);
        /* } else {
            $inventories = DB::table('inventories')
            ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department')
            ->join('categories', 'inventories.category_id', 'categories.id')
            ->where('inventories.is_deleted', 0)
            ->where('department',1)
            ->orwhere('department',2)
            ->orwhere('department',3)
            ->orwhere('department',4)
            ->orwhere('department',5)
            ->orderBy('name', 'asc')
            ->paginate(100, '*', 'page', $page);
        } */
        
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();

        $invCount = $inventories->total();
        $search = "";


        return view('user.reciever.masterlist.index', compact('inventories', 'invCount', 'page', 'search', 'menu_id','depts','branches'));
    }

    public function search($page, $search) {
        $menu_cat = Category::where('slug', 'menu')->first();
        if ($menu_cat) {
            $menu_id = $menu_cat->id;
        } else {
            $menu_id = 0;
        }
        /* if (auth()->user()->role != 4 && auth()->user()->role != 2){ */
            $inventories = DB::table('masterlists')
            ->select('masterlists.name', 'categories.name AS cat_name', 'masterlists.category_id', 'masterlists.unit',  'masterlists.slug','masterlists.price')
            ->join('categories', 'masterlists.category_id', 'categories.id')
            ->whereRaw("masterlists.name LIKE '%{$search}%'")
            ->where('masterlists.is_deleted', 0)
            ->orderBy('masterlists.name', 'asc')
            ->paginate(100, '*', 'page', $page);
        /* } else {
            $inventories = DB::table('inventories')
            ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department')
            ->join('categories', 'inventories.category_id', 'categories.id')
            ->whereRaw("CONCAT_WS(' ', inventories.item_code, inventories.name, categories.name) LIKE '%{$search}%'")
            ->where('inventories.is_deleted', 0)
            ->where('department',1)
            ->orwhere('department',2)
            ->orwhere('department',3)
            ->orwhere('department',4)
            ->orwhere('department',5)
            ->orderBy('name', 'asc')
            ->paginate(100, '*', 'page', $page);  
        } */
        $invCount = $inventories->total();

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();

        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        return view('user.reciever.masterlist.index', compact('inventories', 'invCount', 'page', 'search', 'menu_id','depts','branches'));
    }

    public function add() {
        $categories = DB::table('categories')->orderBy('name', 'asc')->where('slug', '!=', 'menu')->where('is_deleted', 0)->get();

        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();

        return view('user.reciever.masterlist.add', compact('categories','depts','branches'));
    }

    public function store(Request $request) {
        $item_code = $request->item_code;
        $name = 'RW-' . $request->name;
        $category_id = $request->category_id;
        // $quantity = $request->quantity;
        //$reorder_point = $request->reorder_point;
        $unit = $request->unit;
        $price = $request->price;
        //$image = $request->image;
        //$department = $request->department;
        
        $nameExist = Masterlist::where('name', $name)->first();
        if ($nameExist) {
            return back()->withInput()->with('error', 'Item already exist!');
        }

        $slug = Str::slug($name, '-');
        $check_slug = DB::table('masterlists')->where('slug', $slug)->get();
        $x = 1;
        $nslug = $slug;
        while (count($check_slug) > 0) {
            $nslug = $slug . '-' . $x;
            $check_slug = DB::table('masterlists')->where('slug', $nslug)->get();
            $x++;
        }
        $slug = $nslug;
        /* 
        $imagePath = null;
        if ($image != null) {
            $imagePath = $request->file('image')->storeAs('images/items/' . $slug . '.' . $request->file('image')->getClientOriginalExtension(), 'public');
        } */

        $request->validate([
            'name' => 'required',
        ]);

        $MasterList = new Masterlist();
        $MasterList->item_code = $item_code;
        $MasterList->name = $name;
        $MasterList->category_id = $category_id;
        $MasterList->unit = $unit;
        $MasterList->price = $price;
        $MasterList->slug = $slug;
        $MasterList->save();

        $item = new Inventory();
        $item->item_code = $item_code;
        $item->name = $name;
        $item->category_id = $category_id;
        $item->quantity = 0;
        $item->reorder_point = 0;
        $item->unit = $unit;
        $item->price = $price;
        $item->masterlist_id = $MasterList->id;
        $item->slug = $slug;
        $item->department = 6;
        $item->branch = 1;
        $item->save();

        return redirect()->route('masterlist.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug) {
        $item = DB::table('masterlists')->where('slug', $slug)->first();
        $categories = DB::table('categories')->orderBy('name', 'asc')->where('slug', '!=', 'menu')->where('is_deleted', 0)->get();
        
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        return view('user.reciever.masterlist.edit', compact('item', 'categories','depts'));
    }

    public function update(Request $request) {
        $oldSlug = $request->slug;
        $item_code = $request->item_code;
        $name = $request->name;
        $price = $request->price;
        //$reorder_point = $request->reorder_point;
        $unit = $request->unit;
        //$department = $request->department;

        $slug = Str::slug($name, '-');
        $check_slug = DB::table('masterlists')->where('slug', $slug)->get();
        $x = 1;
        $nslug = $slug;
        while (count($check_slug) > 0) {
            $nslug = $slug . '-' . $x;
            $check_slug = DB::table('masterlists')->where('slug', $nslug)->get();
            $x++;
        }
        $slug = $nslug;


        DB::table('masterlists')->where('slug', $oldSlug)
            ->update([
                'name' => $name,
                'unit' => $unit,
                'price' => $price,
                'slug' => $slug,
            ]);

        return redirect()->route('masterlist.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug) {
        $invID = (DB::table('masterlists')->where('slug', $slug)->first())->id;

        DB::table('masterlists')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        /* DB::table('ingredients')->where('is_menu', 0)->where('inventory_id', $invID)
            ->update([
                'is_deleted' => 1,
            ]); */

        return redirect()->route('masterlist.index')->withInput()->with('message', 'Successfully Deleted');
    }

    public function dispose(Request $request) {
        $inv = DB::table('inventories')->where('slug', $request->disposeSlug)->first();
        $quantity = $request->quantity;
        $date = date('Y-m-d H:i:s', strtotime($request->disposeDate . ' ' . date('H:i:s')));
        $waste_remarks = $request->waste_remarks;

        if ($inv->quantity < $quantity) {
            return redirect()->route('inventory.index')->withInput()->with('error', 'Please Enter a valid Quantity.');
        }

        $inv_tran = DB::table('inventory_transactions')
            ->select('amount', 'quantity')
            ->where('type', 'INCOMING')
            ->where('inv_id', $inv->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($inv_tran != null) {
            $cost = ($inv_tran->amount * $quantity) / $inv_tran->quantity;
        } else {
            $cost = 0;
        }

        $waste = new Waste();
        $waste->on = 'INVENTORY';
        $waste->iid = $inv->id;
        $waste->quantity = $quantity;
        $waste->created_at = $date;
        $waste->cost = $cost;
        $waste->waste_remarks = $waste_remarks;
        $waste->save();

        DB::table('inventories')->where('slug', $request->disposeSlug)->decrement('quantity', $quantity);

        return redirect()->route('inventory.index')->withInput()->with('message', 'Item Successfully Disposed');
    }

    public function addqty(Request $request) {
        $slug = $request->addSlug;
        $status = $request->status;
        $quantity = $request->quantity;
        $unit = $request->unit;
        if ($quantity < 1 || $quantity == '' || $quantity == null) {
            return redirect()->route('inventory.index')->withInput()->with('error', 'Please Enter a valid Quantity.');
        }
        $price = $request->price;
        $inv = DB::table('inventories')->where('slug', $slug)->first();
        $qb = $inv->quantity;

        if ($unit == $inv->unit) {
            $qf = $qb + $quantity;
        } else {
            $conv = Convertion::where('from', $unit)->where('to', $inv->unit)->first();
            $qf = $qb + ($quantity * $conv->value);
        }

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
        $it->is_paid = $status;
        $it->user_id = Auth::id();
        if (Auth::user()->role == 1) {
            $dateAdd = $request->dateAdd;
            $it->created_at = $dateAdd . ' ' . date('H:i:s');
        }
        $it->save();

        return redirect()->route('inventory.index')->withInput()->with('message', 'Quantity Successfully Increased');
    }

    public function minusqty(Request $request) {
        $slug = $request->minSlug;
        $quantity = $request->minQuantity;
        $unit = $request->unit;
        $remarks = $request->remarks;
        $dateMinus = $request->dateMinus;
        $inv = DB::table('inventories')->where('slug', $slug)->first();
        $qb = $inv->quantity;

        if ($unit == $inv->unit) {
            $qf = $qb + $quantity;
        } else {
            $conv = Convertion::where('from', $unit)->where('to', $inv->unit)->first();
            $qf = $qb - ($quantity * $conv->value);
        }

        if ($qf < 0) {
            return redirect()->route('inventory.index')->withInput()->with('error', 'Please Enter a valid Quantity.');
        } else {
            DB::table('inventories')->where('slug', $slug)->update([
                'quantity' => $qf,
            ]);

            $it = new InventoryTransaction();
            $it->inv_id = $inv->id;
            $it->type = 'OUTGOING';
            $it->quantity_before = $qb;
            $it->quantity = $quantity;
            $it->quantity_after = $qf;
            $it->remarks = $remarks;
            $it->user_id = Auth::id();
            if (Auth::user()->role == 1) {
                $dateMinus = $request->dateMinus;
                $it->created_at = $dateMinus . ' ' . date('H:i:s');
            }
            $it->save();

            return redirect()->route('inventory.index')->withInput()->with('message', 'Quantity Successfully Decreased');
        }
    }
}
