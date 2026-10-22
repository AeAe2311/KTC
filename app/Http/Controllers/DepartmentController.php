<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index(){
        $depts = DB::table('departments')->orderBy('id', 'asc')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)->paginate(100);
        $deptsCount = DB::table('departments')->get()->count();
        $page = 1;
        $search = "";
        $branches = DB::table('settings')
        ->where('store_code', Auth::user()->store_code)
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        return view('admin.system-management.departments.index', compact('branches','depts', 'deptsCount', 'page', 'search'));
    }

    public function paginate($page){
        $depts = DB::table('departments')->orderBy('id', 'asc')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $deptsCount = DB::table('departments')->get()->count();
        $search = "";
        return view('admin.system-management.departments.index', compact('depts', 'deptsCount', 'page', 'search'));
    }

    public function search($page, $search){
        $depts = DB::table('departments')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->where('store_code', Auth::user()->store_code)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $deptsCount = DB::table('departments')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->where('store_code', Auth::user()->store_code)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.departments.index', compact('depts', 'deptsCount', 'page', 'search'));
    }

    public function add(){
        $branches = DB::table('settings')->where('store_code', Auth::user()->store_code)
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        return view('admin.system-management.departments.add', compact('branches'));
    }

    public function store(Request $request){
        $name = strtoupper($request->name);
        $cname = strtoupper($request->cname);

        $request->validate([
            'name' => ['required'],
        ]);

        $slug = Str::random(60);
        $table = new Department();
        $table->name = $name;
        $table->company_name = $cname;
        $table->store_code = Auth::user()->store_code;
        $table->slug = $slug;
        $table->is_uuid = $slug;
        $table->save();

        return redirect()->route('department.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $depts = DB::table('departments')
        ->where('store_code', Auth::user()->store_code)
        ->where('slug', $slug)->first();
        $branches = DB::table('settings')
        ->where('store_code', Auth::user()->store_code)
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        return view('admin.system-management.departments.edit', compact('depts','branches'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;
        $cname = strtoupper($request->cname);
        //$address = strtoupper($request->address);
        //$tin = strtoupper($request->tin);
        //$ptype = strtoupper($request->ptype);
        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('departments')->where('slug', $slug)
            ->update([
                'name' => $name,
                'company_name' => $cname,
                'updated_at' => now(),
                //'address' => $address,
                //'tin' => $tin,
               // 'price_type' => $ptype,
            ]);

        return redirect()->route('department.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('departments')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
            ]);

        return redirect()->route('department.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
