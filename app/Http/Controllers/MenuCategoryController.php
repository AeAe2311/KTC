<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MenuCategoryController extends Controller
{
    public function index(){
        $categories = DB::table('menu_categories')
        ->where('is_deleted', 0)
        ->where('store_code', Auth::user()->store_code) 
        ->orderBy('name', 'asc')
        ->paginate(100);
        $categoryCount = $categories->total();
        $page = 1;
        $search = "";
        return view('admin.system-management.menu-categories.index', compact('categories', 'categoryCount', 'page', 'search'));
    }

    public function paginate($page){
        $categories = DB::table('menu_categories'
        ->where('store_code', Auth::user()->store_code) 
        )->where('is_deleted', 0)
        ->orderBy('name', 'asc')->paginate(100,'*','page',$page);
        $categoryCount = $categories->total();
        $search = "";
        return view('admin.system-management.menu-categories.index', compact('categories', 'categoryCount', 'page', 'search'));
    }

    public function search($page, $search){
        $categories = DB::table('menu_categories')
            ->select('*')
            ->where('is_deleted', 0)
            ->where('store_code', Auth::user()->store_code) 
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->orderBy('name', 'asc')
            ->paginate(100,'*','page',$page);

        $categoryCount = $categories->total();
        return view('admin.system-management.menu-categories.index', compact('categories', 'categoryCount', 'page', 'search'));
    }

    public function add(){
        $branches = DB::table('settings')
            ->where('store_code', Auth::user()->store_code) // Only branches for this store
            ->orderBy('branch_code', 'asc')
            ->where('is_deleted', 0)
            ->get();

        return view('admin.system-management.menu-categories.add', compact('branches'));
    }

    public function store(Request $request){
        $name = $request->name;
        $branch = $request->branch;

        if($request->hidden != null){
            $hidden = 1;
        }else{
            $hidden = 0;
        }

        $request->validate([
            'name' => ['required'],
        ]);

        $slug = Str::slug($name, '-');
        $check_slug = DB::table('menu_categories')->where('slug', $slug)->get();
        $x = 1;
        $nslug = $slug;
        while(count($check_slug) > 0){
            $nslug = $slug.'-'.$x;
            $check_slug = DB::table('menu_categories')->where('slug', $nslug)->get();
            $x++;
        }
        $slug = $nslug;

        $category = new MenuCategory();
        $category->name = ucwords($name);
        $category->slug = $slug;
        $category->is_uuid = $slug;
        $category->branch = $branch;
        $category->is_hidden = $hidden;
        $category->store_code = Auth::user()->store_code;
        $category->save();

        return redirect()->route('menu.category.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $branches = DB::table('settings')
        ->where('store_code', Auth::user()->store_code) 
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $category = DB::table('menu_categories')
        ->where('store_code', Auth::user()->store_code) 
        ->where('slug', $slug)->first();
        return view('admin.system-management.menu-categories.edit', compact('category', 'branches'));
    }

    public function update(Request $request){
        $name = $request->name;
        $slug = $request->slug;
        $branch = $request->branch;

        if($request->hidden != null){
            $hidden = 1;
        }else{
            $hidden = 0;
        }

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('menu_categories')->where('slug', $slug)
            ->update([
                'name' => ucwords($name),
                'branch' => $branch,
                'is_hidden' => $hidden,
                'updated_at' => now(),
            ]);

        return redirect()->route('menu.category.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('menu_categories')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
            ]);

        return redirect()->route('menu.category.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
