<?php

namespace App\Http\Controllers;

use App\Models\OrderingEvents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class OrderingEventsController extends Controller
{
    public function index(){
        $ordering_events = DB::table('ordering_events')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $ordering_eventsCount = DB::table('ordering_events')->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.ordering-events.index', compact('ordering_events', 'ordering_eventsCount', 'page', 'search'));
    }

    public function paginate($page){
        $ordering_events = DB::table('ordering_events')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $ordering_eventsCount = DB::table('ordering_events')->get()->count();   
        $search = "";
        return view('admin.system-management.ordering-events.index', compact('ordering_events', 'ordering_eventsCount', 'page', 'search'));
    }

    public function search($page, $search){
        $ordering_events = DB::table('ordering_events')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $ordering_eventsCount = DB::table('ordering_events')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.ordering-events.index', compact('ordering_events', 'ordering_eventsCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.ordering-events.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $ord_events = new OrderingEvents();
        $ord_events->name = $name;
        $ord_events->slug = Str::random(60);
        $ord_events->save();

        return redirect()->route('ordering-events.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $ord_events = DB::table('ordering_events')->where('slug', $slug)->first();
        return view('admin.system-management.ordering-events.edit', compact('ord_events'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('ordering_events')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('ordering-events.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('ordering_events')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('ordering-events.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
