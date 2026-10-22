<?php

namespace App\Http\Controllers;

use App\Models\ReceivingEvents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class ReceivingEventsController extends Controller
{
    public function index(){
        $receiving_events = DB::table('receiving_events')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $receiving_eventsCount = DB::table('receiving_events')->get()->count();
        $page = 1;
        $search = "";
        return view('admin.system-management.receiving-events.index', compact('receiving_events', 'receiving_eventsCount', 'page', 'search'));
    }

    public function paginate($page){
        $receiving_events = DB::table('receiving_events')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $receiving_eventsCount = DB::table('receiving_events')->get()->count();   
        $search = "";
        return view('admin.system-management.receiving-events.index', compact('receiving_events', 'receiving_eventsCount', 'page', 'search'));
    }

    public function search($page, $search){
        $receiving_events = DB::table('receiving_events')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $receiving_eventsCount = DB::table('receiving_events')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.receiving-events.index', compact('receiving_events', 'receiving_eventsCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.receiving-events.add');
    }

    public function store(Request $request){
        $name = strtoupper($request->name);

        $request->validate([
            'name' => ['required'],
        ]);

        $rec_events = new ReceivingEvents();
        $rec_events->name = $name;
        $rec_events->slug = Str::random(60);
        $rec_events->save();

        return redirect()->route('receiving-events.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $rec_events = DB::table('receiving_events')->where('slug', $slug)->first();
        return view('admin.system-management.receiving-events.edit', compact('rec_events'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('receiving_events')->where('slug', $slug)
            ->update([
                'name' => $name,
            ]);

        return redirect()->route('receiving-events.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('receiving_events')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('receiving-events.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
