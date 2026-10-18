<?php

namespace App\Http\Controllers;

use App\Models\ItemBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ItemBrandController extends Controller
{
    public function index(){
        $brand = DB::table('item_brands')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $brandCount = DB::table('item_brands')->get()->count();
        $page = 1;
        $search = "";
        return view('user.item-brand.index', compact('brand', 'brandCount', 'page', 'search'));
    }

    public function paginate($page){
        $brand = DB::table('item_brands')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $brandCount = DB::table('item_brands')->get()->count();   
        $search = "";
        return view('user.item-brand.index', compact('brand', 'brandCount', 'page', 'search'));
    }

    public function search($page, $search){
        $brand = DB::table('item_brands')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $brandCount = DB::table('item_brands')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('user.item-brand.index', compact('brand', 'brandCount', 'page', 'search'));
    }

    public function add(){
        return view('user.item-brand.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $brands = new ItemBrand();
        $brands->name = $name;
        $brands->slug = Str::random(60);
        $brands->save();

        return redirect()->route('item_brand.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $brands = DB::table('item_brands')->where('slug', $slug)->first();
        return view('user.item-brand.edit', compact('brands'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('item_brands')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('item_brand.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('item_brands')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('item_brand.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
