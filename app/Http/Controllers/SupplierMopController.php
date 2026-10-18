<?php

namespace App\Http\Controllers;

use App\Models\SupplierMop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class SupplierMopController extends Controller
{
    public function index(){
        $mops = DB::table('supplier_mops')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $mopsCount = DB::table('supplier_mops')->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.supplier-mop.index', compact('mops', 'mopsCount', 'page', 'search'));
    }

    public function paginate($page){
        $mops = DB::table('supplier_mops')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $mopsCount = DB::table('supplier_mops')->get()->count();   
        $search = "";
        return view('admin.system-management.supplier-mop.index', compact('mops', 'mopsCount', 'page', 'search'));
    }

    public function search($page, $search){
        $mops = DB::table('supplier_mops')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $mopsCount = DB::table('supplier_mops')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.supplier-mop.index', compact('mops', 'mopsCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.supplier-mop.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $mop = new SupplierMop();
        $mop->name = $name;
        $mop->slug = Str::random(60);
        $mop->save();

        return redirect()->route('mop.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $mop = DB::table('supplier_mops')->where('slug', $slug)->first();
        return view('admin.system-management.supplier-mop.edit', compact('mop'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('supplier_mops')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('mop.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('supplier_mops')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('mop.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
