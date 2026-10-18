<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function settings(){
        $settings = DB::table('settings')
        ->where('store_code', Auth::user()->store_code) 
        ->orderBy('name', 'asc')
        ->where('is_deleted', 0)->paginate(100);

        $settingsCount = DB::table('settings')->where('store_code', Auth::user()->store_code) 
        ->get()->count();
        $page = 1;
        $search = "";

        return view('admin.system-management.settings.index', compact('settings', 'settingsCount', 'page', 'search'));
    }

    public function paginate($page){
        $page = (int)$page; // Cast to integer
        $settings = DB::table('settings')
        ->where('store_code', Auth::user()->store_code) 
        ->orderBy('id', 'asc')->paginate(100,'*','page',$page);
        $settingsCount = DB::table('settings')
        ->where('store_code', Auth::user()->store_code) 
        ->get()->count();
        $search = "";
        return view('admin.system-management.settings.index', compact('settings', 'settingsCount', 'page', 'search'));
    }

    public function search($page, $search){
        $page = (int)$page; // Cast to integer
        $settings = DB::table('settings')
            ->select('*')
            ->whereRaw("name LIKE '%{$search}%'")
            ->where('store_code', Auth::user()->store_code) 
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $settingsCount = DB::table('settings')
            ->select('*')
            ->where('store_code', Auth::user()->store_code) 
            ->whereRaw("name LIKE '%{$search}%'")
            ->orderBy('id', 'asc')
            ->count();
            
        return view('admin.system-management.settings.index', compact('settings', 'settingsCount', 'page', 'search'));
    }

    public function add(){
        return view('admin.system-management.settings.add');
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'number' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'footer' => 'nullable|string',
            'email' => 'nullable|email',
            'voidCode' => 'nullable|string',
        ]);

        $name = $request->name;
        $address = $request->address;
        $number = $request->number;
        $logo = $request->file('logo');
        $footer = $request->footer;
        $email = $request->email;
        $voidCode = $request->voidCode;
        
        // Get store_code from logged in user
        $storeCode = auth()->user()->store_code;  // added by 20250623001 ken 01-09-2026

        // Generate branch_code
        $branchcode = $this->generateBranchCode(); // added by 20250623001 ken 01-09-2026
        // Generate UUID for new menu
        $uuid = $this->generateUUID();  // added by 20250623001 ken 01-09-2026

        $imagePath = null;
        if($logo != null){
            $extension = $logo->getClientOriginalExtension();
            $filename = 'logo-' . time() . '.' . $extension;
            $path = "images/ico/";
            $imagePath = $path.$filename;
            $logo->move(public_path('storage/'.$path), $filename);

            DB::table('settings')->insert([
                'name' => $name,
                'address' => $address,
                'number' => $number,
                'logo' => $imagePath,
                'footer' => $footer,
                'void_code' => $voidCode,
                'is_uuid' => $uuid,
                'store_code' => $storeCode, // added by 20250623001 ken 01-09-2026
                'branch_code' => $branchcode, // added by 20250623001 ken 01-09-2026
                'slug' => Str::random(60),
                'email' => $email,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            DB::table('settings')->insert([
                'name' => $name,
                'address' => $address,
                'number' => $number,
                'footer' => $footer,
                'void_code' => $voidCode,
                'is_uuid' => $uuid,
                'store_code' => $storeCode, // added by 20250623001 ken 01-09-2026
                'branch_code' => $branchcode, // added by 20250623001 ken 01-09-2026
                'slug' => Str::random(60),
                'email' => $email,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->route('settings.index');
    }
     
    // added start here by 20250623001 ken 01-09-2026
    // generate Branch code method added by ken 1-09-2026
    private function generateBranchCode(): string
    {
        return DB::transaction(function () {
            $branchType = '01'; // Could come from configuration
            $year = date('mdYHis');
            $storeCode = auth()->user()->store_code;
                
            // Lock the users table to prevent race conditions
            // $lastBranch = User::where('branch_code', 'like', "branch-{$branchType}{$year}{$month}-%")
            //     ->lockForUpdate()
            //     ->orderBy('branch_code', 'desc')
            //     ->first();
                
            // if ($lastBranch) {
            //     $parts = explode('-', $lastBranch->branch_code);
            //     $lastSequence = intval(end($parts));
            //     $sequence = str_pad($lastSequence + 1, 6, '0', STR_PAD_LEFT);
            // } else {
            //     $sequence = '000001';
            // }
                
            return "branch-{$branchType}{$year}";
        });
    }  
    
    //is_uuid auto generate method added by ken 01-09-2026
    public function generateUUID(?string $existingUUID = null): string
    {
        // If provided UUID exists and is valid format, use it
        if (!empty($existingUUID) && preg_match('/^[0-9a-f\-]{36}$/i', $existingUUID)) {
            $uuid = $existingUUID;
        } else {
            // Generate new UUID
            $uuid = (string) Str::uuid();
        }
        
        // Ensure uniqueness in the database
        while (Menu::where('is_uuid', $uuid)->exists()) {
            $uuid = (string) Str::uuid();
        }
        
        return $uuid;
    }
    // added end here by 20250623001 ken 01-09-2026
    
    public function edit($id){
        $settings = DB::table('settings')->where('id', $id)->first();
        return view('admin.system-management.settings.edit', compact('settings'));
    }

    public function update(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'number' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'footer' => 'nullable|string',
            'email' => 'nullable|email',
            'voidCode' => 'nullable|string',
            'slug' => 'required|string',
        ]);

        $name = $request->name;
        $address = $request->address;
        $number = $request->number;
        $logo = $request->file('logo');
        $footer = $request->footer;
        $email = $request->email;
        $slug = $request->slug;
        $voidCode = $request->voidCode;
        
        $updateData = [
            'name' => $name,
            'address' => $address,
            'number' => $number,
            'footer' => $footer,
            'void_code' => $voidCode,
            'email' => $email,
            'updated_at' => now()->toDateTimeString(),
        ];
        
        if($logo != null){
            $extension = $logo->getClientOriginalExtension();
            $filename = 'logo-' . time() . '.' . $extension;
            $path = "images/ico/";
            $imagePath = $path.$filename;
            $logo->move(public_path('storage/'.$path), $filename);
            
            $logo = $imagePath;
        }

        DB::table('settings')->where('id', $slug)->update([
            'name' => $name,
            'address' => $address,
            'number' => $number,
            'footer' => $footer,
            'void_code' => $voidCode,
            'email' => $email,
            'updated_at' => now()->toDateTimeString(),
            'logo' => $logo,
            ]);

        return redirect()->route('settings.index')->withInput()->with('message', 'Successfully Updated branch');
    }

    public function delete($id){
        DB::table('settings')->where('id', $id)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now()->toDateTimeString()
            ]);

        return redirect()->route('settings.index')->withInput()->with('message', 'Successfully Deleted');
    }
}