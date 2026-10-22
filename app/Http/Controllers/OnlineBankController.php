<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlineBankController extends Controller
{
    public function onlinebank(Request $request){
        // Get current user's store_code
        $storeCode = auth()->user()->store_code;
        
        // Check if we're showing transactions
        $showTransactions = $request->get('transactions') == 1;
        
        if ($showTransactions) {
            
            $transactions = DB::table('online_banking_trans')
                ->leftJoin('users', 'online_banking_trans.cashier', '=', 'users.id')
                ->where('online_banking_trans.store_code', $storeCode) // Filter by user's store_code
                ->orderBy('online_banking_trans.created_at', 'desc')
                ->where('online_banking_trans.is_deleted', 0)
                ->select('online_banking_trans.*', DB::raw("CONCAT_WS(' ', users.firstname, users.middlename, users.lastname) as cashier")) // ken 250623001 update to  show cashier name -02-28-2026
                ->paginate(100);
                
            $transactionsCount = DB::table('online_banking_trans')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->where('is_deleted', 0)
                ->count();
            
            $page = $transactions->currentPage();
            $search = "";
            
            return view('admin.system-management.onlinebank.index', compact('transactions', 'transactionsCount', 'page', 'search', 'showTransactions'));
        } else {
            // Original online banking list for the user's store_code only
            $onlinebank = DB::table('online_banking_list')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->orderBy('bank_name', 'asc')
                ->where('is_deleted', 0)
                ->paginate(100);
                
            $onlinebankCount = DB::table('online_banking_list')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->where('is_deleted', 0)
                ->count();
                
            $page = $onlinebank->currentPage();
            $search = "";

            return view('admin.system-management.onlinebank.index', compact('onlinebank', 'onlinebankCount', 'page', 'search', 'showTransactions'));
        }
    }

    public function paginate($page, Request $request){
        $page = (int)$page;
        $showTransactions = $request->get('transactions') == 1;
        $storeCode = auth()->user()->store_code; // Get current user's store_code
        
        if ($showTransactions) {
            $transactions = DB::table('online_banking_trans')
                ->leftJoin('users', 'online_banking_trans.cashier', '=', 'users.id')
                ->where('online_banking_trans.store_code', $storeCode) // Filter by user's store_code
                ->orderBy('online_banking_trans.created_at', 'desc')
                ->select('online_banking_trans.*', DB::raw("CONCAT_WS(' ', users.firstname, users.middlename, users.lastname) as cashier_name"))
                ->paginate(100, '*', 'page', $page);
                
            $transactionsCount = DB::table('online_banking_trans')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->count();
                
            $search = "";
            $page = $transactions->currentPage();
            
            return view('admin.system-management.onlinebank.index', compact('transactions', 'transactionsCount', 'page', 'search', 'showTransactions'));
        } else {
            $onlinebank = DB::table('online_banking_list')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->orderBy('id', 'asc')
                ->paginate(100, '*', 'page', $page);
                
            $onlinebankCount = DB::table('online_banking_list')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->count();
                
            $search = "";
            $page = $onlinebank->currentPage();
            
            return view('admin.system-management.onlinebank.index', compact('onlinebank', 'onlinebankCount', 'page', 'search', 'showTransactions'));
        }
    }

    public function search($page, $search, Request $request){
        $page = (int)$page;
        $showTransactions = $request->get('transactions') == 1;
        $storeCode = auth()->user()->store_code; // Get current user's store_code
        
        if ($showTransactions) {
            $transactions = DB::table('online_banking_trans')
                ->leftJoin('users', 'online_banking_trans.cashier', '=', 'users.id')
                ->where('online_banking_trans.store_code', $storeCode) // Filter by user's store_code
                ->where(function($query) use ($search) {
                    $query->where('trans', 'LIKE', "%{$search}%")
                        ->orWhere('bank', 'LIKE', "%{$search}%")
                        ->orWhere('function', 'LIKE', "%{$search}%")
                        ->orWhere(DB::raw("CONCAT_WS(' ', users.firstname, users.middlename, users.lastname)"), 'LIKE', "%{$search}%");
                })
                ->orderBy('online_banking_trans.created_at', 'desc')
                ->select('online_banking_trans.*', DB::raw("CONCAT_WS(' ', users.firstname, users.middlename, users.lastname) as cashier_name"))
                ->paginate(100, '*', 'page', $page);

            $transactionsCount = DB::table('online_banking_trans')
                ->leftJoin('users', 'online_banking_trans.cashier', '=', 'users.id')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->where(function($query) use ($search) {
                    $query->where('trans', 'LIKE', "%{$search}%")
                        ->orWhere('bank', 'LIKE', "%{$search}%")
                        ->orWhere('function', 'LIKE', "%{$search}%")
                        ->orWhere(DB::raw("CONCAT_WS(' ', users.firstname, users.middlename, users.lastname)"), 'LIKE', "%{$search}%");
                })
                ->count();
            
            $page = $transactions->currentPage();
            
            return view('admin.system-management.onlinebank.index', compact('transactions', 'transactionsCount', 'page', 'search', 'showTransactions'));
        } else {
            $onlinebank = DB::table('online_banking_list')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->where('bank_name', 'LIKE', "%{$search}%")
                ->orderBy('id', 'asc')
                ->paginate(100, '*', 'page', $page);

            $onlinebankCount = DB::table('online_banking_list')
                ->where('store_code', $storeCode) // Filter by user's store_code
                ->where('bank_name', 'LIKE', "%{$search}%")
                ->count();
            
            $page = $onlinebank->currentPage();
            
            return view('admin.system-management.onlinebank.index', compact('onlinebank', 'onlinebankCount', 'page', 'search', 'showTransactions'));
        }
    }

    public function add(){
        return view('admin.system-management.onlinebank.add');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'fee' => 'required|numeric',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $bankName = $request->bank_name;
        $fee = $request->fee;
        $logo = $request->logo;
        
        // Get store_code from logged in user
        $storeCode = auth()->user()->store_code;

        // Generate UUID for new record
        $uuid = $this->generateUUID();

        $imagePath = null;
        if($logo != null){
            $filename = 'bank_logo_' . time() . '.' . $request->file('logo')->getClientOriginalExtension();
            $path = "images/bank_logos/";
            $imagePath = $path.$filename;
            $request->file('logo')->move(public_path('storage/'.$path), $filename);

            DB::table('online_banking_list')->insert([
                'bank_name' => $bankName,
                'fee' => $fee,
                'logo' => $imagePath,
                'is_uuid' => $uuid,
                'is_deleted' => 0,
                'is_sync' => 0,
                'store_code' => $storeCode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('online_banking_list')->insert([
                'bank_name' => $bankName,
                'fee' => $fee,
                'is_uuid' => $uuid,
                'is_deleted' => 0,
                'is_sync' => 0,
                'store_code' => $storeCode,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect()->route('onlinebank.index')->with('success', 'Bank added successfully!');
    }

    public function edit($id){
        // Get current user's store_code
        $storeCode = auth()->user()->store_code;
        
        // Note: Routes show {slug} but our table doesn't have slug anymore
        // We'll treat the {slug} parameter as ID for now
        $onlinebank = DB::table('online_banking_list')
            ->where('id', $id)
            ->where('store_code', $storeCode) // Ensure user can only edit their own store's banks
            ->first();
        
        if(!$onlinebank) {
            return redirect()->route('onlinebank.index')->with('error', 'Bank not found or you do not have permission to edit this bank!');
        }
        
        return view('admin.system-management.onlinebank.edit', compact('onlinebank'));
    }

    public function update(Request $request){
        $validated = $request->validate([
            'id' => 'required|integer',
            'bank_name' => 'required|string|max:255',
            'fee' => 'required|numeric',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $id = $request->id;
        $bankName = $request->bank_name;
        $fee = $request->fee;
        $logo = $request->logo;
        
        // Get current user's store_code
        $storeCode = auth()->user()->store_code;

        $updateData = [
            'bank_name' => $bankName,
            'fee' => $fee,
            'updated_at' => now(),
            'is_sync' => 0, // Mark as not synced when updated
        ];

        if($logo != null){
            $filename = 'bank_logo_' . time() . '.' . $request->file('logo')->getClientOriginalExtension();
            $path = "images/bank_logos/";
            $imagePath = $path.$filename;
            $request->file('logo')->move(public_path('storage/'.$path), $filename);
            $updateData['logo'] = $imagePath;
        }

        // Update only if the bank belongs to the user's store_code
        DB::table('online_banking_list')
            ->where('id', $id)
            ->where('store_code', $storeCode)
            ->update($updateData);

        return redirect()->route('onlinebank.index')->with('success', 'Bank updated successfully!');
    }

    public function delete($id){
        // Get current user's store_code
        $storeCode = auth()->user()->store_code;
        
        // Note: Routes show {slug} but we'll use it as ID
        // Soft delete the bank only if it belongs to the user's store_code
        DB::table('online_banking_list')
            ->where('id', $id)
            ->where('store_code', $storeCode)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
                'is_sync' => 0,
            ]);

        return redirect()->route('onlinebank.index')->with('success', 'Bank deleted successfully!');
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

    // Optional: Add restore method (not in routes but might be useful)
    public function restore($id)
    {
        // Get current user's store_code
        $storeCode = auth()->user()->store_code;
        
        // Restore soft-deleted bank only if it belongs to the user's store_code
        DB::table('online_banking_list')
            ->where('id', $id)
            ->where('store_code', $storeCode)
            ->update([
                'is_deleted' => 0,
                'updated_at' => now(),
                'is_sync' => 0,
            ]);

        return redirect()->route('onlinebank.index')->with('success', 'Bank restored successfully!');
    }
}