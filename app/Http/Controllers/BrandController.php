<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    public function index(){
        $depts = DB::table('brands')->orderBy('id', 'asc')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)->paginate(100);
        $deptsCount = DB::table('brands')->get()->count();
        $page = 1;
        $search = "";
        $branches = DB::table('menu_categories')
        ->where('store_code', Auth::user()->store_code)
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        return view('admin.system-management.brand.index', compact('branches','depts', 'deptsCount', 'page', 'search'));
    }

    public function paginate($page){
        $depts = DB::table('brands')->orderBy('id', 'asc')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $deptsCount = DB::table('brands')->get()->count();
        $search = "";
        return view('admin.system-management.brand.index', compact('depts', 'deptsCount', 'page', 'search'));
    }

    public function search($page, $search){
        $depts = DB::table('brands')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->where('store_code', Auth::user()->store_code)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $deptsCount = DB::table('brands')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->where('store_code', Auth::user()->store_code)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.brand.index', compact('depts', 'deptsCount', 'page', 'search'));
    }

    public function add(){
        $branches = DB::table('menu_categories')->where('store_code', Auth::user()->store_code)
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        return view('admin.system-management.brand.add', compact('branches'));
    }

    public function store(Request $request){
        // Support both `category_id` (preferred) and legacy `cname` from older forms.
        $request->merge([
            'category_id' => $request->input('category_id', $request->input('cname')),
        ]);

        // Convert empty string to NULL so MySQL (strict) accepts nullable integer columns.
        if ($request->input('category_id') === '') {
            $request->merge(['category_id' => null]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string'],
            'category_id' => ['nullable', 'integer'],
        ]);

        $name = strtoupper($validated['name']);

        $slug = Str::random(60);
        $table = new Brand();
        $table->name = $name;
        $table->category_id = $validated['category_id'] ?? null;
        $table->store_code = Auth::user()->store_code;
        $table->slug = $slug;
        $table->is_uuid = $slug;
        $table->save();

        return redirect()->route('brand.index')->withInput()->with('message', 'Successfully Added');
    }

    // public function edit($slug){
    //     $depts = DB::table('brands')
    //     ->where('store_code', Auth::user()->store_code)
    //     ->where('slug', $slug)->first();
    //     $branches = DB::table('menu_categories')
    //     ->where('store_code', Auth::user()->store_code)
    //     ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
    //     return view('admin.system-management.brand.edit', compact('depts','branches'));
    // }

    // public function update(Request $request){
    //     $name = strtoupper($request->name);
    //     $slug = $request->slug;
    //     $cname = strtoupper($request->cname);
    //     //$address = strtoupper($request->address);
    //     //$tin = strtoupper($request->tin);
    //     //$ptype = strtoupper($request->ptype);
    //     $request->validate([
    //         'name' => ['required'],
    //     ]);

    //     DB::table('brands')->where('slug', $slug)
    //         ->update([
    //             'name' => $name,
    //             'category_id' => $cname,
    //             'updated_at' => now(),
    //             //'address' => $address,
    //             //'tin' => $tin,
    //           // 'price_type' => $ptype,
    //         ]);

    //     return redirect()->route('brand.index')->withInput()->with('message', 'Successfully Updated');
    // }


   // ken updated start - 252306001  --- 3-29-30
    public function edit($slug){
        $depts = DB::table('brands')
        ->where('store_code', Auth::user()->store_code)
        ->where('slug', $slug)->first();
        $branches = DB::table('menu_categories')
        ->where('store_code', Auth::user()->store_code)
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        return view('admin.system-management.brand.edit', compact('depts','branches'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;
        //$cname = strtoupper($request->cname);
        //$address = strtoupper($request->address);
        //$tin = strtoupper($request->tin);
        //$ptype = strtoupper($request->ptype);
        $request->validate([
            'name' => ['required'],
            //'cname' => ['required'],
        ]);

        // ensure the record belongs to the current store before updating
        DB::table('brands')
            ->where('slug', $slug)
            ->where('store_code', Auth::user()->store_code)
            ->update([
                'name' => $name,
                //'category_id' => $cname,
                'updated_at' => now(),
                //'address' => $address,
                //'tin' => $tin,
               // 'price_type' => $ptype,
            ]);

        return redirect()->route('brand.index')->withInput()->with('message', 'Successfully Updated');
    }
    // ken end of update
    
    
    public function delete($slug){
        DB::table('brands')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
            ]);

        return redirect()->route('brand.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
