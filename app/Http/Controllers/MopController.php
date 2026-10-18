<?php

namespace App\Http\Controllers;

use App\Models\ModeOfPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MopController extends Controller
{
    public function index()
    {
        $mops = DB::table('mode_of_payment')
            ->orderBy('id', 'asc')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->paginate(100);

        $mopsCount = DB::table('mode_of_payment')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->count();

        $page = 1;
        $search = "";

        return view('admin.system-management.mop.index', compact('mops', 'mopsCount', 'page', 'search'));
    }

    public function paginate($page)
    {
        $page = (int) $page;

        $mops = DB::table('mode_of_payment')
            ->orderBy('id', 'asc')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->paginate(100, '*', 'page', $page);

        $mopsCount = DB::table('mode_of_payment')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->count();

        $search = "";

        return view('admin.system-management.mop.index', compact('mops', 'mopsCount', 'page', 'search'));
    }

    public function search($page, $search)
    {
        $page = (int) $page;
        $search = trim((string) $search);

        $baseQuery = DB::table('mode_of_payment')
            ->where('is_deleted', 0)
            ->where('store_code', Auth::user()->store_code);

        if ($search !== '') {
            $baseQuery->where('name', 'like', '%' . $search . '%');
        }

        $mops = (clone $baseQuery)
            ->orderBy('id', 'asc')
            ->paginate(100, '*', 'page', $page);

        $mopsCount = (clone $baseQuery)->count();

        return view('admin.system-management.mop.index', compact('mops', 'mopsCount', 'page', 'search'));
    }

    public function add()
    {
        return view('admin.system-management.mop.add');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $name = strtoupper($validated['name']);

        do {
            $slug = Str::random(60);
            $exists = ModeOfPayment::where('slug', $slug)->exists();
        } while ($exists);

        $mop = new ModeOfPayment();
        $mop->name = $name;
        $mop->store_code = Auth::user()->store_code;
        $mop->branch_code = Auth::user()->branch_code;
        $mop->slug = $slug;
        $mop->is_deleted = 0;
        $mop->save();

        return redirect()->route('mode-of-payment.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug)
    {
        $mop = DB::table('mode_of_payment')
            ->where('store_code', Auth::user()->store_code)
            ->where('slug', $slug)
            ->where('is_deleted', 0)
            ->first();

        if (!$mop) {
            return redirect()->route('mode-of-payment.index')->withInput()->with('message', 'Record not found');
        }

        return view('admin.system-management.mop.edit', compact('mop'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'slug' => ['required', 'string'],
        ]);

        $name = strtoupper($validated['name']);
        $slug = $validated['slug'];

        DB::table('mode_of_payment')
            ->where('slug', $slug)
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->update([
                'name' => $name,
                'updated_at' => now(),
            ]);

        return redirect()->route('mode-of-payment.index')->withInput()->with('message', 'Successfully Updated');
    }

    // Note: delete only sets is_deleted to 1
    public function delete($slug)
    {
        DB::table('mode_of_payment')
            ->where('slug', $slug)
            ->where('store_code', Auth::user()->store_code)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('mode-of-payment.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
