<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use App\Models\Log;

class SupplierAccreditationController extends Controller
{
    public function index(){
        $users = DB::table('users')
        ->leftJoin('supplier_mops', 'users.mode_of_payment', '=', 'supplier_mops.id')
        ->leftJoin('terms', 'users.terms', '=', 'terms.id')
        ->leftJoin('supplier_accreditation', 'users.id', '=', 'supplier_accreditation.id')
        ->select('users.*', 'supplier_mops.name as mop_name', 'terms.name as term_name' ,'supplier_accreditation.pamahaw_approval')
        ->whereNull('supplier_accreditation.pamahaw_approval')
        ->where('users.status', '=', 'PENDING')
        ->where('users.is_deleted', 0)
        ->orderBy('name', 'asc')
        ->paginate(100);

        $supplier_accregCount = DB::table('supplier_accreditation')->get()->count();
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $page = 1;
        $search = "";
        return view('user.supplier-accreditation.index', compact('users', 'supplier_accregCount', 'page', 'search','depts'));
    }

    public function paginate($page){
        $page = (int) $page;
        $users = DB::table('users')->orderBy('name', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $supplier_accregCount = DB::table('supplier_accreditation')->get()->count();
        $search = "";
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        return view('user.supplier-accreditation.index', compact('users', 'supplier_accregCount', 'page', 'search','depts'));
    }

    public function search($page, $search){
        $users = DB::table('users')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->paginate(100,'*','page',$page);

        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $supplier_accregCount = DB::table('users')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->count();
        return view('user.supplier-accreditation.index', compact('users', 'supplier_accregCount', 'page', 'search','depts'));
    }

    /* public function indexPamahaw(){
        $users = DB::table('users')
        ->leftJoin('supplier_accreditation', 'users.id', '=', 'supplier_accreditation.id')
        ->select('users.*', 'supplier_accreditation.helpline_approval','supplier_accreditation.pamahaw_approval')
        ->where('supplier_accreditation.helpline_approval', '=', 'APPROVED')
        ->whereNull('supplier_accreditation.pamahaw_approval')
        ->where('supplier_accreditation.is_deleted', 0)
        ->paginate(100);
        $supplier_accregCount = DB::table('supplier_accreditation')->get()->count();
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $page = 1;
        $search = "";
        return view('user.supplier-accreditation.indexPamahaw', compact('users', 'supplier_accregCount', 'page', 'search','depts'));
    } */

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


   
   /*  public function helpline_approval(Request $request, $id) {
        
        $helpline_approval = $request->input('helpline_approval'); 
        $helpline_remarks = $request->input('helpline_remarks'); 

        $status = 'pending';
        $review_status = null;

        if ($helpline_approval === 'APPROVED') {
            $review_status = 'FOR-REVIEW';
            $status = 'APPROVED';
        } elseif ($helpline_approval === 'DECLINED') {
            $review_status = 'DECLINED';
            $status = 'DECLINED';
        }

        DB::table('supplier_accreditation')
            ->where('id', $id)
            ->update([
                'helpline_approval' => $helpline_approval,
                'review_status' => $review_status,
                'helpline_remarks' => $helpline_remarks,
                'updated_at' => now()
            ]);

        DB::table('users')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'updated_at' => now()
            ]);

      
        $tran_id = $id ?? null;

        $log = new Log();
        $log->trans_id = $tran_id;
        $log->classification = 'Helpline Approval';
        $log->info = 'Helpline Approval';
        $log->user_id = auth()->id();
        $log->event = 'Approve / Decline';
        $log->save();
   

        return redirect()->route('supplier.index')->withInput()->with('message', 'Helpline ' . ucfirst(strtolower($helpline_approval)) . ' successfully.');
    } */

    public function pamahaw_approval(Request $request, $id) {

        
        $pamahaw_approval = $request->input('pamahaw_approval'); 
        $pamahaw_remarks = $request->input('pamahaw_remarks'); 

        $status = 'pending';

        DB::table('users')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'updated_at' => now()
            ]);

        
        DB::table('supplier_accreditation')
            ->where('id', $id)
            ->update([
                'pamahaw_approval' => $pamahaw_approval,
                'pamahaw_remarks' => $pamahaw_remarks,
                'updated_at' => now()
            ]);

        $supplier_accreditation = DB::table('supplier_accreditation')->where('id', $id)->first();

        $exists = DB::table('supplier_masterlist')->where('id', $id)->exists();

       
        if (!$exists) {
            DB::table('supplier_masterlist')->insert([
                'id' => $supplier_accreditation->id,
                'name' => $supplier_accreditation->name,
                'username' => $supplier_accreditation->username,
                'password' => $supplier_accreditation->password,
                'role' => $supplier_accreditation->role,
                'slug' => $supplier_accreditation->slug,
                'table_id' => $supplier_accreditation->table_id,
                'department' => $supplier_accreditation->department,
                'branch' => $supplier_accreditation->branch,
                'status' => $supplier_accreditation->status,
                'company_name' => $supplier_accreditation->company_name,
                'company_address' => $supplier_accreditation->company_address,
                'company_tin' => $supplier_accreditation->company_tin,
                'company_email' => $supplier_accreditation->company_email,
                'company_no' => $supplier_accreditation->company_no,
                'contact_person' => $supplier_accreditation->contact_person,
                'contact_person_no' => $supplier_accreditation->contact_person_no,
                'contact_person_email' => $supplier_accreditation->contact_person_email,
                'terms' => $supplier_accreditation->terms,
                'mode_of_payment' => $supplier_accreditation->mode_of_payment,
                'pamahaw_approval' => $supplier_accreditation->pamahaw_approval,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

      
        $tran_id = $id ?? null;

        $log = new Log();
        $log->trans_id = $tran_id;
        $log->classification = 'Pamahaw Approval';
        $log->info = 'Pamahaw Approval';
        $log->user_id = auth()->id();
        $log->event = 'Approve / Decline';
        $log->save();
   

        return redirect()->route('supplier.index')->withInput()->with('message', 'Pamahaw ' . ucfirst(strtolower($pamahaw_approval)) . ' successfully.');
    }

    /* public function delete($slug){
        DB::table('users')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('user.index')->withInput()->with('message', 'Successfully Deleted');
    } */
}
