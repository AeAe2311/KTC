<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class TableController extends Controller
{
    public function index(){
        $tables = $this->clientListQuery()
        ->orderBy('tables.name', 'asc')
        ->paginate(100);
        $tableCount = DB::table('tables')
        ->where('store_code', auth()->user()->store_code)
        ->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.tables.index', compact('tables', 'tableCount', 'page', 'search'));
    }

    public function paginate($page){
        $tables = $this->clientListQuery()
        ->orderBy('tables.id', 'asc')
        ->paginate(100,'*','page',$page);
        $tableCount = DB::table('tables')
        ->where('store_code', auth()->user()->store_code)
        ->get()->count();
        $search = "";
        return view('admin.system-management.tables.index', compact('tables', 'tableCount', 'page', 'search'));
    }

    public function search($page, $search){
        $tables = $this->clientListQuery()
            ->whereRaw("CONCAT_WS(' ', tables.name) LIKE '%{$search}%'")
            ->orderBy('tables.id', 'asc')
            ->paginate(100,'*','page',$page);

        $tableCount = DB::table('tables')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.tables.index', compact('tables', 'tableCount', 'page', 'search'));
    }

    public function add(){

        $branches = DB::table('settings')
        ->where('store_code', auth()->user()->store_code)
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $tblcats = DB::table('table_categories')->where('store_code', auth()->user()->store_code)
        ->orderBy('name', 'asc')->where('is_deleted', 0)->get(); // leo update 4-22
        return view('admin.system-management.tables.add', compact('branches','tblcats'));
    }

    public function store(Request $request){
        $name = strtoupper($request->name);
        $sc = $request->sc;
        $branch = $request->branch;
        $tblcats = $request ->tblcats; // leo update 4-22
        $service_charge =  ($sc == 'on') ? 0 : 1;

        //echo $service_charge;
        $request->validate([
            'name' => ['required'],
        ]);

        $table = new Table();
        $table->name = $name;
        $table->category = 1;
        $table->is_sc = $service_charge;
        $table->branch = 1;
        $table->store_code = auth()->user()->store_code;
        $table->tblcats = $tblcats; // leo update 4-22
        $table->slug = Str::random(60);
        $table->save();

        return redirect()->route('table.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){

        $table = DB::table('tables')->where('slug', $slug)->first();
        $tblcats = DB::table('table_categories')->orderBy('name', 'asc')->where('is_deleted', 0)->get(); // leo update 4-22
        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        return view('admin.system-management.tables.edit', compact('table','branches','tblcats'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;
        $sc = $request->sc;
        $branch = $request->branch;
        $tblcats = $request ->tblcats; // leo update 4-22
        $creditLimit = (float) ($request->credit_limit ?? 0);
        $service_charge =  ($sc == 'on') ? 0 : 1;

        $request->validate([
            'name' => ['required'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::table('tables')->where('slug', $slug)
            ->update([
                'name' => $name,
                'credit_limit' => $creditLimit,
                'category' => 1,
                'is_sc' => $service_charge,
                'branch' => 1,
                'tblcats'=> $tblcats, // leo update 4-22
                'updated_at'=> now(),
            ]);

        return redirect()->route('table.index')->withInput()->with('message', 'Successfully Updated');
    }

    private function clientListQuery()
    {
        $outstandingBalances = DB::table('account_receivables')
            ->select('table', DB::raw('SUM(balance) as outstanding_balance'))
            ->where('store_code', auth()->user()->store_code)
            ->groupBy('table');

        return DB::table('tables')
            ->leftJoinSub($outstandingBalances, 'client_balances', function ($join) {
                $join->on('tables.id', '=', 'client_balances.table');
            })
            ->select(
                'tables.*',
                DB::raw('COALESCE(client_balances.outstanding_balance, 0) as outstanding_balance'),
                DB::raw('(COALESCE(tables.credit_limit, 0) - COALESCE(client_balances.outstanding_balance, 0)) as credits_remaining')
            )
            ->where('tables.store_code', auth()->user()->store_code)
            ->where('tables.is_deleted', 0);
    }

    public function delete($slug){
        DB::table('tables')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('table.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
