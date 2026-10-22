<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Clocking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class StaffController extends Controller
{
    public function index(){
        $staffs = Staff::with('position')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100);
        $staffCount = DB::table('staff')->get()->count();
        $page = 1;
        $search = "";
        return view('user.staff.index', compact('staffs', 'staffCount', 'page', 'search'));
    }

    public function paginate($page){
        $staffs = Staff::with('position')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        echo $staffs;
        $staffCount = DB::table('staff')->get()->count();
        $search = "";
        return view('user.staff.index', compact('staffs', 'staffCount', 'page', 'search'));
    }

    public function search($page, $search){
        $staffs = Staff::with('position')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', lname, fname) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $staffCount = DB::table('staff')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', lname, fname) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('user.staff.index', compact('staffs', 'staffCount', 'page', 'search'));
    }

    public function add(){

        $positions = DB::table('positions')->orderBy('name', 'asc')->get();

        return view('user.staff.add', compact('positions'));
    }

    public function clocking(){
        return view('user.staff.clocking');
    }

    public function store(Request $request){
        $firstname = strtoupper($request->firstname);
        $middlename = strtoupper($request->middlename);
        $lastname = strtoupper($request->lastname);
        $sec_id = strtoupper($request->sec_id);
        $position = strtoupper($request->position);
        $salary = (!empty($request->salary)) ? $request->salary : 0;

        $sec_id_exist = DB::table('staff')->where('sec_id', $sec_id)->first();
        if ($sec_id_exist) {
            return back()->withInput()->with('error', 'Secure Code already exists!');
        }
        
        $request->validate([
            'firstname' => ['required'],
            'lastname' => ['required'],
            'sec_id' => ['required'],
            'position' => ['required'],
        ]);

        $staff = new Staff();
        $staff->fname = $firstname;
        $staff->mname = $middlename;
        $staff->lname = $lastname;
        $staff->sec_id = $sec_id;
        $staff->position_id = $position;  // Changed from $staff = auth()->user()->store_code;
        $staff->store_code = auth()->user()->store_code;  // Add this if you need to store user's store_code
        $staff->salary = $salary;
        $staff->slug = Str::random(60);
        $staff->save();

        return redirect()->route('staff.index')->withInput()->with('message', 'Successfully Added');
    }

    public function StoreClocking(Request $request){
        $id = strtoupper($request->staff_id);
        $dateToday = date('Y-m-d H:i:s');
        $imageSlug = date('Y-m-d_H-i-s');
        //$middlename = strtoupper($request->middlename);
        $manager = new ImageManager(new Driver());
        $filename =  $imageSlug.'_'.$id . '.' . $request->file('imageUploadbtn')->getClientOriginalExtension();
        $path = "images/clock/";
        $imagePath = $path . $filename;
        $request->file('imageUploadbtn')->move(public_path('storage/' . $path), $filename);
        $image = $manager->read(public_path('storage/' . $imagePath));
        $image->resize(1024, 1024);
        $image->save(public_path('storage/' . $imagePath));

        $status = DB::table('clockings')
        ->where('staff_id', $id)
        ->orderBy('id', 'DESC')
        ->first();
        if($status != null){
            if ($status->status == 0){
                DB::table('clockings')->where('id', $status->id)
                ->update([
                    'out_time' => $dateToday,
                    'out_image' => $imagePath,
                    'status' => 1,
                    'updated_at' => $dateToday
                ]);
            } else {
                $staff = new Clocking();
                $staff->staff_id = $id;
                $staff->in_image = $imagePath;
                $staff->in_time = $dateToday;
                $staff->slug = Str::random(60);
                $staff->status = 0;
                $staff->save();
            }
        } else {
            $staff = new Clocking();
            $staff->staff_id = $id;
            $staff->in_image = $imagePath;
            $staff->in_time = $dateToday;
            $staff->slug = Str::random(60);
            $staff->status = 0;
            $staff->save();
        }
        

        return redirect()->route('clock.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($slug){
        $positions = DB::table('positions')->orderBy('name', 'asc')->get();
        $staff = DB::table('staff')->where('slug', $slug)->first();
        return view('user.staff.edit', compact('staff','positions'));
    }

    public function check(Request $request){
        $staff = DB::table('staff')->where('sec_id', $request->sec)->first();
        $response = '';
        if (!empty($staff)){
            DB::table('servers')->where('id', 1)->update([
                'cashier' => $staff->id,
            ]);
            $response = array(
                'lname' => $staff->lname,
                'fname' => $staff->fname,
                'id' => $staff->id,
                'message' => 'success'
            );
            
        } else {
            $response = array(
                'message' => 'error'
            );
        }

        echo json_encode($response);
    }

    public function serverout(Request $request){
        
        DB::table('servers')->where('user_id', auth()->id())->update([
            'cashier' => 0,
        ]);
        echo json_encode('success');
    }


    public function update(Request $request){
        $firstname = strtoupper($request->firstname);
        $middlename = strtoupper($request->middlename);
        $lastname = strtoupper($request->lastname);
        $sec_id = strtoupper($request->sec_id);
        $slug = $request->slug;
        $position = $request->position;
        $salary = $request->salary;

        $sec_id_exist = DB::table('staff')
        ->where('slug','!=', $slug)
        ->where('sec_id', $sec_id)
        ->first();
        if ($sec_id_exist) {
            return back()->withInput()->with('error', 'Secure Code already exist!');
        }
        $request->validate([
            'firstname' => ['required'],
            'lastname' => ['required'],
            'sec_id' => ['required'],
        ]);

        DB::table('staff')->where('slug', $slug)
            ->update([
                'fname' => $firstname,
                'mname' => $middlename,
                'lname' => $lastname,
                'sec_id' => $sec_id,
                'position_id' => $position,
                'salary' => $salary,
            ]);

        return redirect()->route('staff.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('staff')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('staff.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
