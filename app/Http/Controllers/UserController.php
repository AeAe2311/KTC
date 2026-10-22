<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(){
        $users = DB::table('users')
        ->leftJoin('supplier_mops', 'users.mode_of_payment', '=', 'supplier_mops.id') //leo added
        ->leftJoin('terms', 'users.terms', '=', 'terms.id') //leo added
        ->select('users.*', 'supplier_mops.name as mop_name', 'terms.name as term_name') //leo added
        ->orderBy('users.lastname', 'asc')
        ->where('users.is_deleted', 0)
        ->where('users.store_code', Auth::user()->store_code) 
        ->paginate(100);
        $userCount = DB::table('users')->get()->count();
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $page = 1;
        $search = "";
        return view('admin.system-management.users.index', compact('users', 'userCount', 'page', 'search','depts'));
    }

    public function paginate($page){
        $users = DB::table('users')
        ->leftJoin('supplier_mops', 'users.mode_of_payment', '=', 'supplier_mops.id') //leo added
        ->leftJoin('terms', 'users.terms', '=', 'terms.id') //leo added
        ->select('users.*', 'supplier_mops.name as mop_name', 'terms.name as term_name') //leo added
        ->orderBy('users.lastname', 'asc')
        ->where('users.is_deleted', 0)
        ->paginate(100,'*','page',$page);
        $userCount = DB::table('users')
        ->get()->count();
        $search = "";
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        return view('admin.system-management.users.index', compact('users', 'userCount', 'page', 'search','depts'));
    }

    public function search($page, $search){
        $users = DB::table('users')
            ->leftJoin('supplier_mops', 'users.mode_of_payment', '=', 'supplier_mops.id') //leo added
            ->leftJoin('terms', 'users.terms', '=', 'terms.id') //leo added
            ->select('users.*', 'supplier_mops.name as mop_name', 'terms.name as term_name') //leo added
            ->whereRaw("CONCAT_WS(' ', firstname, lastname) LIKE '%{$search}%'")
            ->where('users.is_deleted', 0)
            ->orderBy('users.lastname', 'asc')
            ->paginate(100,'*','page',$page);

        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $userCount = DB::table('users')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', firstname, lastname) LIKE '%{$search}%'")
            ->where('users.is_deleted', 0)
            ->orderBy('users.lastname', 'asc')
            ->count();
        return view('admin.system-management.users.index', compact('users', 'userCount', 'page', 'search','depts'));
    }

    public function add(){
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        $branchs = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $tables = DB::table('tables')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $terms = DB::table('terms')->orderBy('id', 'asc')->where('is_deleted', 0)->get(); //leo added 

        return view('admin.system-management.users.add', compact('depts','branchs','tables','terms'));
    }

    public function store(Request $request){
         $firstname = $request->firstname;
        $middlename = $request->middlename;
        $lastname = $request->lastname;
        $suffix = $request->suffix;
        $username = $request->username;
        $role = $request->role;
        $password = $request->password;

        $request->validate([
            'firstname' => ['required'],
            'username' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        /* $userCount = User::where('store_code', auth()->user()->store_code)->where('role', '!=',1)->count();
        
        if ($userCount >= auth()->user()->user_limit) {
            //return redirect()->back()->withInput()->with('error', '');
            return back()->withErrors(['error' => 'User limit reached for your company.'])->withInput();
        } */
        $storeCode = auth()->user()->store_code;
        $userLimit = auth()->user()->user_limit;
        $role = request('role'); // or $request->role
        $rolename = ($role == 2) ? 'Cashier' : 'Inventory Staff';

        // only apply limit to role 2 and 3
        if (in_array($role, [2, 3])) {

            $roleUserCount = User::where('store_code', $storeCode)
                ->where('role', $role)
                ->count();

            if ($roleUserCount >= $userLimit) {
                return back()
                    ->withErrors(['error' => "User limit reached for role {$rolename}."])
                    ->withInput();
            }
        }
        
        do {
                $tranSlug = Str::random(60);
                $exists = User::where('slug', $tranSlug)->exists();
            } while ($exists);

        $user = new User();
        $user->firstname = strtoupper($firstname);
        $user->middlename = strtoupper($middlename);
        $user->lastname = strtoupper($lastname);
        $user->suffix = $suffix;
        $user->username = $username;
        $user->role = $role;
        $user->password = Hash::make($password);
        $user->slug = $tranSlug;
        $user->email_verified_at = now();
        $user->expired_at = now()->addMonth();
        $user->store_code = auth()->user()->store_code;
        $user->save();

        DB::table('discounts')->insert([
            'customer_with_discount' => '0',
            'total_customer' => '0',
            'user_id' => $user->id
        ]);

        DB::table('lime_discount')->insert([
            'percent' => '0',
            'user_id' => $user->id
        ]);

        DB::table('service_charge')->insert([
            'percent' => '10',
            'user_id' => $user->id
        ]);
        DB::table('servers')->insert([
            'cashier' => '0',
            'user_id' => $user->id
        ]);
        
        //leo added
        $tran_id = $user->id ?? null;

        $log = new Log();
        $log->trans_id = $tran_id;
        $log->classification = 'User Added';
        $log->info = 'User Added';
        $log->user_id = auth()->id();
        $log->event = 'Add';
        $log->save();
        //leo added
        

        return redirect()->route('user.index')->withInput()->with('message', 'Successfully Added');
    }

    //leo added
    public function view(Request $request)
    {
        $user = DB::table('users')
        ->leftJoin('terms', 'users.terms', '=', 'terms.id')
        ->leftJoin('supplier_mops', 'users.mode_of_payment', '=', 'supplier_mops.id')
        ->select('users.*', 'terms.name as term_name', 'supplier_mops.name as mop_name')
        ->where('users.id', $request->id)
        ->first(); 

        $requirementPhotos = DB::table('requirements_photo')
        ->join('supplier_requirements', 'requirements_photo.name', '=', 'supplier_requirements.id')
        ->select(
            'requirements_photo.*',
            'supplier_requirements.name as requirement_name'
        )
        ->where('requirements_photo.supplier_req_files_id', $user->id)
        ->where('requirements_photo.is_deleted', 0)
        ->get();


    $viewUser = '
        <p><span class="text-gray-600 text-sm">Name:</span> <span class="text-sm"> '.$user->name.' </span></p>
        <p><span class="text-gray-600 text-sm">Username:</span> <span class="text-sm"> '.$user->username.' </span></p>
        <p><span class="text-gray-600 text-sm">Company Name:</span> <span class="text-sm"> '.$user->company_name.' </span></p>
        <p><span class="text-gray-600 text-sm">Company Address:</span> <span class="text-sm"> '.$user->company_address.' </span></p>
        <p><span class="text-gray-600 text-sm">Company TIN:</span> <span class="text-sm"> '.$user->company_tin.' </span></p>
        <p><span class="text-gray-600 text-sm">Company Email:</span> <span class="text-sm"> '.$user->company_email.' </span></p>
        <p><span class="text-gray-600 text-sm">Company Number:</span> <span class="text-sm"> '.$user->company_no.' </span></p>
        <p><span class="text-gray-600 text-sm">Contact Person:</span> <span class="text-sm"> '.$user->contact_person.' </span></p>
        <p><span class="text-gray-600 text-sm">Contact Person Number:</span> <span class="text-sm"> '.$user->contact_person_no.' </span></p>
        <p><span class="text-gray-600 text-sm">Contact Person Email:</span> <span class="text-sm"> '.$user->contact_person_email.' </span></p>
        <p><span class="text-gray-600 text-sm">Payment Terms:</span> <span class="text-sm"> '.$user->term_name.' </span></p>
        <p><span class="text-gray-600 text-sm">Mode of Payment:</span> <span class="text-sm"> '.$user->mop_name.' </span></p>
        ';

        if (!$requirementPhotos->isEmpty()) {
            $viewUser .= '<div class="mt-4"><span class="text-gray-600 text-sm">Requirements Photos:</span>';
            foreach ($requirementPhotos as $photo) {
                $fileUrl = asset('storage/' . $photo->file);
                $viewUser .= '
                    <div class="mt-2">
                        <p class="text-sm font-semibold">'.($photo->requirement_name).'</p>
                        <img src="'.$fileUrl.'" onclick="get_imgsrc(\''.$fileUrl.'\')" class="w-40 h-auto border rounded mb-2 cursor-pointer" alt="Requirement">
                        <p class="text-sm italic text-gray-500">'.($photo->remarks).'</p>
                    </div>
                ';
            }
            $viewUser .= '</div>';
        }
    return response()->json(['users' => $viewUser]);


    }
    
    // leo added 
    public function detail($slug){
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        $branchs = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $tables = DB::table('tables')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $terms = DB::table('terms')->orderBy('id', 'asc')->where('is_deleted', 0)->get(); //leo added 7/3/25
        $mops = DB::table('supplier_mops')->orderBy('id', 'asc')->where('is_deleted', 0)->get(); //leo added 7/8/25

        $user = DB::table('users')->where('slug', $slug)->first();
        return view('admin.system-management.users.detail', compact('user','depts','branchs','tables','terms','mops'));

    }
    
    public function detail_update(Request $request){
        $slug = $request->slug;
        $company_name = $request->company_name;
        $company_address = $request->company_address;
        $company_tin = $request->company_tin;
        $company_email = $request->company_email;
        $company_no = $request->company_no;
        $contact_person = $request->contact_person;
        $contact_person_no = $request->contact_person_no;
        $contact_person_email = $request->contact_person_email;
        $terms = $request->terms;
        $mops = $request->mode_of_payment;

       

            DB::table('users')->where('slug', $slug)
                ->update([
                    
                    'company_name' => $company_name,
                    'company_address' => $company_address,
                    'company_tin' => $company_tin,
                    'company_email' => $company_email,
                    'company_no' => $company_no,
                    'contact_person' => $contact_person,
                    'contact_person_no' => $contact_person_no,
                    'contact_person_email' => $contact_person_email,
                    'terms' => $terms,
                    'mode_of_payment' => $mops ,
                    'status' => 'PENDING',

                   
                ]);
        

            $user = DB::table('users')->where('slug', $slug)->first();

                    DB::table('supplier_accreditation')->insert([
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'password' => $user->password,
                        'role' => $user->role,
                        'slug' => $user->slug,
                        'table_id' => $user->table_id,
                        'department' => $user->department,
                        'branch' => $user->branch,
                        'status' => $user->status,
                        'company_name' => $user->company_name,
                        'company_address' => $user->company_address,
                        'company_tin' => $user->company_tin,
                        'company_email' => $user->company_email,
                        'company_no' => $user->company_no,
                        'contact_person' => $user->contact_person,
                        'contact_person_no' => $user->contact_person_no,
                        'contact_person_email' => $user->contact_person_email,
                        'terms' => $user->terms,
                        'mode_of_payment' => $user->mode_of_payment,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                
            

        $log = new Log();
        $log->trans_id = $user->id;
        $log->classification = 'Supplier Details';
        $log->info = 'Details Added';
        $log->user_id = auth()->id();
        $log->event = 'Register';
        $log->save();

        return redirect()->route('user.index')->withInput()->with('message', 'Supplier Information Successfully Added');
    }
    
    //leo added
    public function requirement($slug){
        $user = DB::table('users')->where('slug', $slug)->first();
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        $branchs = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $tables = DB::table('tables')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $terms = DB::table('terms')->orderBy('id', 'asc')->where('is_deleted', 0)->get(); //leo added 7/3/25
        $mops = DB::table('supplier_mops')->orderBy('id', 'asc')->where('is_deleted', 0)->get(); //leo added 7/8/25
        


        $suppreq = DB::table('supplier_requirements')->where('is_deleted', 0)->get();

        
        return view('admin.system-management.users.requirement', compact('user','depts','branchs','tables','terms','mops','suppreq'));

    }

    //leo added
    public function requirement_update(Request $request) {
        
        $supplierid = $request->input('supplierid');
        $names = $request->input('name'); 
        $remarks = $request->input('remarks') ?? ""; 
        $files = $request->file('file');  
        $slug = $request->slug;

        foreach ($names as $index => $name) {
            if (isset($files[$index])) {
                $file = $files[$index];
                

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $safeName = Str::slug($originalName);
                $filename = $safeName . '.' . $extension;
                $path = 'images/supplier-requirement/';
                $imagePath = $path . $filename;
                $file->move(public_path('storage/' . $path), $filename);

                
                DB::table('requirements_photo')->insert([
                    'name' => $name,
                    'file' => $imagePath,
                    'slug' => $slug,
                    'remarks' => $remarks,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_deleted' => 0,
                    'supplier_req_files_id' => $supplierid 
                ]);
            }
        }

        $log = new Log();
        $log->trans_id = $supplierid;
        $log->classification = 'Supplier Requirement';
        $log->info = 'Requirement Files Uploaded';
        $log->user_id = auth()->id();
        $log->event = 'Add';
        $log->save();


        return redirect()->route('user.index')->with('message', 'Requirements Successfully Uploaded');


    }
    
    //leo added
    public function itempaginate($page, $slug) {
        $user = DB::table('users')->where('slug', $slug)->first();

        $getitemcheck = DB::table('item_cart')->where('user_id', $user->id)->get();

        $menu_cat = Category::where('slug', 'menu')->first();
        if ($menu_cat) {
            $menu_id = $menu_cat->id;
        } else {
            $menu_id = 0;
        }
        $catVal = '';
        if (auth()->user()->role == 9) {
            $userDepartment = auth()->user()->department;
            
            $inventories = DB::table('inventories')
                ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch', 'departments.name as dname','inventories.id','inventories.brand_id')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->where('inventories.is_deleted', 0)
                ->where('inventories.department',  '=', $userDepartment) 
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100, '*', 'page', $page);
        } else if (auth()->user()->role == 1) {
            
            $inventories = DB::table('inventories')
                ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch', 'departments.name as dname','inventories.id','inventories.brand_id')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->where('inventories.department', 6)
                ->where('inventories.is_deleted', 0)
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100, '*', 'page', $page);
        } else {
            if (auth()->user()->department == 1){
                $inventories = DB::table('inventories')
                ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch','departments.name as dname','inventories.id','inventories.brand_id')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->where('inventories.is_deleted', 0)
                ->whereIn('inventories.department', [1,2])
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100, '*', 'page', $page);
            } else {
                $inventories = DB::table('inventories')
                ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch','departments.name as dname','inventories.id','inventories.brand_id')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->where('inventories.is_deleted', 0)
                ->where('inventories.department', auth()->user()->department)
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100, '*', 'page', $page);
            }
            
        }
        $categories = DB::table('categories')
            ->orderBy('name', 'asc')
            ->get();
        

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();

        $invCount = $inventories->total();
        $search = "";
        $category = 'All';

        $brands = DB::table('item_brands')->where('is_deleted', 0)->get();


        return view('admin.system-management.users.additem', compact('inventories', 'invCount', 'page', 'search', 'menu_id','branches','categories','catVal','user','getitemcheck','brands'));
    }
    //leo added
    public function itemsearch($page, $search, $slug) {
        $user = DB::table('users')->where('slug', $slug)->first();

        $getitemcheck = DB::table('item_cart')->where('user_id', $user->id)->get();
        
        $menu_cat = Category::where('slug', 'menu')->first();
        if ($menu_cat) {
            $menu_id = $menu_cat->id;
        } else {
            $menu_id = 0;
        }
        $catVal = '';
        if (auth()->user()->role == 9) {
            $userDepartment = auth()->user()->department;
            
            $inventories = DB::table('inventories')
                ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch', 'departments.name as dname','inventories.id','inventories.brand_id')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->whereRaw("CONCAT_WS(' ', inventories.item_code, inventories.name, categories.name) LIKE '%{$search}%'")
                ->where('inventories.is_deleted', 0)
                ->where('inventories.department',  '=', $userDepartment) 
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100, '*', 'page', $page);
        }else if (auth()->user()->role == 1) {
            
            $inventories = DB::table('inventories')
                ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch', 'departments.name as dname','inventories.id','inventories.brand_id')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->whereRaw("CONCAT_WS(' ', inventories.item_code, inventories.name, categories.name) LIKE '%{$search}%'")
                ->where('inventories.department', 6)
                ->where('inventories.is_deleted', 0)
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100, '*', 'page', $page);
        }  else {
            if (auth()->user()->department == 1){
                $inventories = DB::table('inventories')
                ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch','departments.name as dname','inventories.id','inventories.brand_id')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->whereRaw("CONCAT_WS(' ', inventories.item_code, inventories.name, categories.name) LIKE '%{$search}%'")
                ->where('inventories.is_deleted', 0)
                ->whereIn('inventories.department', [1,2])
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100, '*', 'page', $page);  
            } else {
                $inventories = DB::table('inventories')
                ->select('inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch','departments.name as dname','inventories.id','inventories.brand_id')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->whereRaw("CONCAT_WS(' ', inventories.item_code, inventories.name, categories.name) LIKE '%{$search}%'")
                ->where('inventories.is_deleted', 0)
                ->where('inventories.department', auth()->user()->department)
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100, '*', 'page', $page);  
            }
            
        }
        $categories = DB::table('categories')
            ->orderBy('name', 'asc')
            ->get();

        $invCount = $inventories->total();

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();
        $brands = DB::table('item_brands')->where('is_deleted', 0)->get();


        return view('admin.system-management.users.additem', compact('inventories', 'invCount', 'page', 'search', 'menu_id','branches','categories','catVal','user','getitemcheck','brands'));
    }
    //leo added
    public function searchcategory(Request $request, $page, $category, $slug) {

        $user = DB::table('users')->where('slug', $slug)->first();

        $getitemcheck = DB::table('item_cart')->where('user_id', $user->id)->get();
       
        $menu_cat = Category::where('slug', 'menu')->first();
        if ($menu_cat) {
            $menu_id = $menu_cat->id;
        } else {
            $menu_id = 0;
        }
        $catVal = '';
        
        
        if ($category != 'All'){
            $catVal = $category;
            $inventories = Inventory::with('category')
            ->select('categories.name AS cat_name','departments.name as dname','inventories.name', 'inventories.slug','inventories.department','inventories.category_id','inventories.id','inventories.brand_id')
            ->join('categories', 'inventories.category_id', 'categories.id')
            ->join('departments', 'inventories.department', 'departments.id')
            ->where('category_id', $category)
            ->where('inventories.is_deleted', 0)
            ->where('inventories.department', 6)
            ->orderBy('departments.name', 'asc')
            ->orderBy('inventories.name', 'asc')
            ->paginate(100, '*', 'page', $page);
        } else {
            $inventories = Inventory::with('category')
            ->select('categories.name AS cat_name', 'departments.name as dname', 'inventories.name','inventories.slug','inventories.department','inventories.category_id','inventories.id','inventories.brand_id')
            ->join('categories', 'inventories.category_id', 'categories.id')
            ->join('departments', 'inventories.department', 'departments.id')
            ->where('inventories.department', 6)
            ->where('inventories.is_deleted', 0)
            ->orderBy('departments.name', 'asc')
            ->orderBy('inventories.name', 'asc')
            ->paginate(100, '*', 'page', $page);
        }
        
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $items = DB::table('inventories')->where('is_deleted', 0)->get();

        $page = $page;
        $category = $category;
        
        $search = "";
        //$categories = $categories;

        $categories = DB::table('categories')
            ->orderBy('name', 'asc')
            ->get();
        
            $invCount = $inventories->total();

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();
        $brands = DB::table('item_brands')->where('is_deleted', 0)->get();

        
   
    return view('admin.system-management.users.additem', compact('inventories', 'invCount', 'page', 'search', 'menu_id','branches','categories','catVal','user','getitemcheck','brands'));
        
    }
    //leo added
    public function item ($slug){
        $user = DB::table('users')->where('slug', $slug)->first();

        $brands = DB::table('item_brands')->where('is_deleted', 0)->orderBy('name')->get();

        $menu_cat = Category::where('slug', 'menu')->first();
        if ($menu_cat) {
            $menu_id = $menu_cat->id;
        } else {
            $menu_id = 0;
        }
        $catVal = '';
        
        $getitemcheck = DB::table('item_cart')->where('user_id', $user->id)->get();
        //leo start 3-12-2024
        if (auth()->user()->role == 9) {

            $userDepartment = auth()->user()->department;
            
            $inventories = DB::table('inventories')
                ->select('inventories.brand_id','inventories.id','inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch', 'departments.name as dname')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->where('inventories.is_deleted', 0)
                ->where('inventories.department',  '=', $userDepartment) 
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100);
                
        }else if (auth()->user()->role == 1) {
            
            $inventories = DB::table('inventories')
                ->select('inventories.brand_id','inventories.id','inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch', 'departments.name as dname')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->where('inventories.department', 6)
                ->where('inventories.is_deleted', 0)
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100);
        } else {
            
            if (auth()->user()->department == 1){
                $inventories = DB::table('inventories')
                ->select('inventories.brand_id','inventories.id','inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch','departments.name as dname')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->where('inventories.is_deleted', 0)
                ->whereIn('inventories.department', [1,2])
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100);
            } else {
                $inventories = DB::table('inventories')
                ->select('inventories.brand_id','inventories.id','inventories.item_code', 'inventories.name', 'categories.name AS cat_name', 'inventories.category_id', 'inventories.quantity', 'inventories.reorder_point', 'inventories.unit', 'inventories.price', 'inventories.image', 'inventories.slug', 'inventories.department', 'inventories.branch','departments.name as dname')
                ->join('categories', 'inventories.category_id', 'categories.id')
                ->join('departments', 'inventories.department', 'departments.id')
                ->where('inventories.is_deleted', 0)
                ->where('inventories.department', auth()->user()->department)
                ->orderBy('departments.name', 'asc')
                ->orderBy('inventories.name', 'asc')
                ->paginate(100);
            }
            
        }
        $categories = DB::table('categories')
            ->orderBy('name', 'asc')
            ->get();

        $invCount = $inventories->total();
        $page = 1;
        $search = "";
        $category = 'All';

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->where('id', '!=', 1)->get();
        $brands = DB::table('item_brands')->where('is_deleted', 0)->get();

        return view('admin.system-management.users.additem', compact('getitemcheck','inventories', 'invCount', 'page', 'search', 'menu_id','branches','categories','catVal','user','brands'));
    }
    //leo added
    public function itemAdding(Request $request)
    {   


        $request->validate([
            'item_name' => 'required|string',
            'item_category' => 'required|exists:categories,id',
            'item_department' => 'required|exists:departments,id',
            'inv_slug' => 'required|string',
            'inv_id' => 'required|int',
            'is_checked' => 'required|int',
            'userid'=> 'required|int',
            'brand_id'=> 'required|int'
        ]);

        $userId = $request->userid;

        $count = DB::table('item_cart')
        ->where('inv_slug', $request->inv_slug)
        ->where('brand_id', $request->brand_id)
        ->count();

           if ($request->is_checked == 1)
           {
            DB::table('item_cart')->insert([
                'item_name' => $request->item_name,
                'inv_slug' => $request->inv_slug,
                'inv_id' => $request->inv_id,
                'item_category' => $request->item_category,
                'item_department' => $request->item_department,
                'user_id' => $userId,
                'brand_id' => $request->brand_id,
                'is_deleted' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['success' => 'add']);
           } else {
         if ($count > 0) {
            DB::table('item_cart')->where('inv_slug',$request->inv_slug)->delete(); }
            return response()->json(['success' => 'delete']);
        }    
    }
    
    public function getCheckedItems(Request $request)
    {
        $request->validate([
            'userid' => 'required|int'
        ]);

        $checkedItems = DB::table('item_cart')
            ->leftJoin('item_brands', 'item_cart.brand_id', '=', 'item_brands.id')
            ->select('item_cart.id','item_cart.item_name','item_cart.inv_slug','item_cart.inv_id','item_cart.item_category','item_cart.item_department','item_cart.user_id','item_cart.brand_id','item_brands.name as brand_name')
            ->where('item_cart.user_id', $request->userid)
            ->where('item_cart.is_deleted', 0)
            ->get();

        return response()->json($checkedItems);
    }
    
    //leo added
    public function storeTemp(Request $request)
    {
        
        $userID = $request->userId;
        $authID = auth()->user()->id;

        $items = DB::table('item_cart')->where('user_id', $userID)->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No items in cart.']);
        }

        foreach ($items as $item) {

                DB::table('item_temp')->insert([
                    'user_id' => $item->user_id,
                    'item_name' => $item->item_name,
                    'item_category' => $item->item_category,
                    'item_department' => $item->item_department,
                    'inv_slug' => $item->inv_slug,
                    'inv_id' => $item->inv_id,
                    'brand_id' => $item->brand_id,
                    'status' => 'Pending',
                    'admin_id' => $authID,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                DB::table('item_logs')->insert([
                    'user_id' => $item->user_id,
                    'item_name' => $item->item_name,
                    'item_category' => $item->item_category,
                    'item_department' => $item->item_department,
                    'inv_slug' => $item->inv_slug,
                    'inv_id' => $item->inv_id,
                    'brand_id' => $item->brand_id,
                    'status' => 'Add',
                    'admin_id' => $authID,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        // Delete from cart after processing
        DB::table('item_cart')->where('user_id', $userID)->delete();

        if ($authID == 1) {
            return response()->json(['message' => 'Item approved.']);
        } else {
            return response()->json(['message' => 'Items saved as pending.']);
        }

      
    }
    
    public function edit($slug){
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        $branchs = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $tables = DB::table('tables')->orderBy('name', 'asc')->where('is_deleted', 0)->get();

        $user = DB::table('users')->where('slug', $slug)->first();
        return view('admin.system-management.users.edit', compact('user','depts','branchs','tables'));
    }
    

    public function update(Request $request){
        $firstname = $request->firstname;
        $middlename = $request->middlename;
        $lastname = $request->lastname;
        $suffix = $request->suffix;
        $role = $request->role;
        $password = $request->password;
        $slug = $request->slug;

        $storeCode = auth()->user()->store_code;
        $userLimit = auth()->user()->user_limit;
        $role = request('role'); // or $request->role
        $rolename = ($role == 2) ? 'Cashier' : 'Inventory Staff';

        // only apply limit to role 2 and 3
        if (in_array($role, [2,3])) {

            $roleUserCount = User::where('store_code', $storeCode)
                ->where('role', $role)
                ->count();

            if ($roleUserCount >= $userLimit) {
                return back()
                    ->withErrors(['error' => "User limit reached for role {$rolename}."])
                    ->withInput();
            }
        }

        if($password != null){
            $request->validate([
                'firstname' => ['required'],
                'lastname' => ['required'],
                'password' => ['required', 'min:8', 'confirmed', Rules\Password::defaults()],
            ]);

            DB::table('users')->where('slug', $slug)
                ->update([
                    'firstname' => $firstname,
                    'middlename' => $middlename,
                    'lastname' => $lastname,
                    'suffix' => $suffix,
                    'role' => $role,
                    'updated_at' =>date('Y-m-d h:i:s'),
                    'password' => Hash::make($password)
                ]);
        }else{
            $request->validate([
                'firstname' => ['required'],
                'lastname' => ['required'],
                'role' => ['required'],
            ]);

            DB::table('users')->where('slug', $slug)
                ->update([
                    'firstname' => $firstname,
                    'middlename' => $middlename,
                    'lastname' => $lastname,
                    'suffix' => $suffix,
                    'role' => $role,
                ]);
        }

        return redirect()->route('user.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('users')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('user.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
