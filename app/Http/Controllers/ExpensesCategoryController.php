<?php

namespace App\Http\Controllers;

use App\Models\ExpensesCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ExpensesCategoryController extends Controller
{
    public function index(){
        $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $expcatCount = DB::table('expenses_categories')->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.expenses-categories.index', compact('expcats', 'expcatCount', 'page', 'search'));
    }

    public function paginate($page){
        $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $expcatCount = DB::table('expenses_categories')->get()->count();
        $search = "";
        return view('admin.system-management.expenses-categories.index', compact('expcats', 'expcatCount', 'page', 'search'));
    }

    public function search($page, $search){
        $expcats = DB::table('expenses_categories')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $expcatCount = DB::table('expenses_categories')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.expenses-categories.index', compact('expcats', 'expcatCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.expenses-categories.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $table = new ExpensesCategory();
        $table->name = $name;
        $table->slug = Str::random(60);
        $table->save();

        return redirect()->route('exp.cat.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $table = DB::table('expenses_categories')->where('slug', $slug)->first();
        return view('admin.system-management.expenses-categories.edit', compact('table'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('expenses_categories')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('exp.cat.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('expenses_categories')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('exp.cat.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
