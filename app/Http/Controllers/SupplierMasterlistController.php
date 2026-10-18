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

class SupplierMasterlistController extends Controller
{
    public function index(){
        $supp_accreg = DB::table('supplier_accreditation')
        ->leftJoin('supplier_masterlist', 'supplier_accreditation.id', '=', 'supplier_masterlist.id') 
        ->select('supplier_accreditation.*', 'supplier_masterlist.masterlist_status')
        ->where('supplier_accreditation.pamahaw_approval', '=', 'APPROVED')
        ->where('supplier_accreditation.is_deleted', 0)
        ->where('supplier_masterlist.is_deleted', 0)
        ->orderBy('name', 'asc')
        ->paginate(100);

        $supplier_accregCount = DB::table('supplier_accreditation')->get()->count();
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $page = 1;
        $search = "";
        return view('user.supplier-masterlist.index', compact('supp_accreg', 'supplier_accregCount', 'page', 'search','depts'));
    }

    public function paginate($page){
        $page = (int) $page;
        $supp_accreg = DB::table('supplier_accreditation')
            ->leftJoin('supplier_masterlist', 'supplier_accreditation.id', '=', 'supplier_masterlist.id')
            ->select('supplier_accreditation.*', 'supplier_masterlist.masterlist_status')
            ->where('supplier_accreditation.pamahaw_approval', '=', 'APPROVED')
            ->where('supplier_accreditation.is_deleted', 0)
            ->orderBy('supplier_accreditation.name', 'asc')
            ->paginate(100,'*','page',$page);
        $supplier_accregCount = DB::table('supplier_accreditation')->get()->count();
        $search = "";
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        return view('user.supplier-masterlist.index', compact('supp_accreg', 'supplier_accregCount', 'page', 'search','depts'));
    }

        public function search($page, $search){
        $supp_accreg = DB::table('supplier_accreditation')
            ->leftJoin('supplier_masterlist', 'supplier_accreditation.id', '=', 'supplier_masterlist.id')
            ->select('supplier_accreditation.*', 'supplier_masterlist.masterlist_status')
            ->where('supplier_accreditation.pamahaw_approval', '=', 'APPROVED')
            ->where('supplier_accreditation.is_deleted', 0)
            ->whereRaw("CONCAT_WS(' ', supplier_accreditation.name, supplier_accreditation.username) LIKE '%{$search}%'")
            ->orderBy('supplier_accreditation.name', 'asc')
            ->paginate(100, '*', 'page', $page);

        $depts = DB::table('departments')
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->get();

        $supplier_accregCount = DB::table('supplier_accreditation')
            ->leftJoin('supplier_masterlist', 'supplier_accreditation.id', '=', 'supplier_masterlist.id')
            ->select('supplier_accreditation.*', 'supplier_masterlist.masterlist_status') 
            ->where('supplier_accreditation.pamahaw_approval', '=', 'APPROVED')
            ->where('supplier_accreditation.is_deleted', 0)
            ->whereRaw("CONCAT_WS(' ', supplier_accreditation.name, supplier_accreditation.username) LIKE '%{$search}%'")
            ->count();

        return view('user.supplier-masterlist.index', compact('supp_accreg', 'supplier_accregCount', 'page', 'search', 'depts'));
    }

    public function masterlist_status(Request $request, $id) {

        
        $status = $request->input('masterlist_status'); // will be 0 or 1

        DB::table('supplier_masterlist')
            ->where('id', $id)
            ->update(['masterlist_status' => $status]);


        $tran_id = $id ?? null;

        $log = new Log();
        $log->trans_id = $tran_id;
        $log->classification = 'Pamahaw Approval';
        $log->info = 'Pamahaw Approval';
        $log->user_id = auth()->id();
        $log->event = 'Approve / Decline';
        $log->save();
   

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    public function delete($slug){
        DB::table('supplier_masterlist')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('supplier_masterlist.index')->withInput()->with('message', 'Successfully Deleted');
    }
}
