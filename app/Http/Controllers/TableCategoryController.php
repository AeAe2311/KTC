<?php

namespace App\Http\Controllers;

use App\Models\TableCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class TableCategoryController extends Controller
{
    public function index(){
        $tblcats = DB::table('table_categories')
        ->where('store_code', auth()->user()->store_code)
        ->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $tblcatCount = DB::table('table_categories')->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.table-categories.index', compact('tblcats', 'tblcatCount', 'page', 'search'));
    }

    public function paginate($page){
        $tblcats = DB::table('table_categories')
        ->where('store_code', auth()->user()->store_code)
        ->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $tblcatCount = DB::table('table_categories')->get()->count();
        $search = "";
        return view('admin.system-management.table-categories.index', compact('tblcats', 'tblcatCount', 'page', 'search'));
    }

    public function search($page, $search){
        $tblcats = DB::table('table_categories')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $tblcatCount = DB::table('table_categories')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.table-categories.index', compact('tblcats', 'tblcatCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.table-categories.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $table = new TableCategory();
        $table->name = $name;
        $table->store_code = auth()->user()->store_code;
        $table->slug = Str::random(60);
        $table->save();

        return redirect()->route('tbl.cat.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $table = DB::table('table_categories')->where('slug', $slug)->first();
        return view('admin.system-management.table-categories.edit', compact('table'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('table_categories')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('tbl.cat.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('table_categories')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('tbl.cat.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
