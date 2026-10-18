<?php

namespace App\Http\Controllers;

use App\Models\SupplierRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class SupplierRequirementController extends Controller
{
    public function index(){
        $suppreq = DB::table('supplier_requirements')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $suppreqCount = DB::table('supplier_requirements')->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.supplier-requirements.index', compact('suppreq', 'suppreqCount', 'page', 'search'));
    }

    public function paginate($page){
        $suppreq = DB::table('supplier_requirements')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $suppreqCount = DB::table('supplier_requirements')->get()->count();   
        $search = "";
        return view('admin.system-management.supplier-requirements.index', compact('suppreq', 'suppreqCount', 'page', 'search'));
    }

    public function search($page, $search){
        $suppreq = DB::table('supplier_requirements')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $suppreqCount = DB::table('supplier_requirements')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.supplier-requirements.index', compact('suppreq', 'suppreqCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.supplier-requirements.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $suppreq = new SupplierRequirement();
        $suppreq->name = $name;
        $suppreq->slug = Str::random(60);
        $suppreq->save();

        return redirect()->route('supplier-requirements.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $suppreq = DB::table('supplier_requirements')->where('slug', $slug)->first();
        return view('admin.system-management.supplier-requirements.edit', compact('term'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('supplier_requirements')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('supplier-requirements.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('supplier_requirements')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('supplier-requirements.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
