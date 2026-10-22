<?php

namespace App\Http\Controllers;

use App\Models\Terms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class TermsController extends Controller
{
    public function index(){
        $terms = DB::table('terms')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $termsCount = DB::table('terms')->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.terms.index', compact('terms', 'termsCount', 'page', 'search'));
    }

    public function paginate($page){
        $terms = DB::table('terms')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $termsCount = DB::table('terms')->get()->count();   
        $search = "";
        return view('admin.system-management.terms.index', compact('terms', 'termsCount', 'page', 'search'));
    }

    public function search($page, $search){
        $terms = DB::table('terms')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $termsCount = DB::table('terms')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.terms.index', compact('terms', 'termsCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.terms.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $term = new Terms();
        $term->name = $name;
        $term->slug = Str::random(60);
        $term->save();

        return redirect()->route('terms.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $term = DB::table('terms')->where('slug', $slug)->first();
        return view('admin.system-management.terms.edit', compact('term'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('terms')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('terms.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('terms')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('terms.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
