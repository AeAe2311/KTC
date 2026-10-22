<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class SupplierController extends Controller
{
    public function index(){
        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $suppCount = DB::table('suppliers')->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.suppliers.index', compact('supps', 'suppCount', 'page', 'search'));
    }

    public function paginate($page){
        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $suppCount = DB::table('suppliers')->get()->count();
        $search = "";
        return view('admin.system-management.suppliers.index', compact('supps', 'suppCount', 'page', 'search'));
    }

    public function search($page, $search){
        $supps = DB::table('suppliers')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $suppCount = DB::table('suppliers')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.suppliers.index', compact('supps', 'suppCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.suppliers.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $table = new Supplier();
        $table->name = $name;
        $table->slug = Str::random(60);
        $table->save();

        return redirect()->route('supp.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $table = DB::table('suppliers')->where('slug', $slug)->first();
        return view('admin.system-management.suppliers.edit', compact('table'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('suppliers')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('supp.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('suppliers')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('supp.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
