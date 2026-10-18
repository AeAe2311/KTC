<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReturnBottleController extends Controller
{
    public function index(Request $request)
    {
        $storeCode = auth()->user()->store_code;
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $deleted = $request->get('deleted', 0);
        
        // Build main query
        $query = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode);
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('trans_number', 'LIKE', "%{$search}%")
                  ->orWhere('remarks', 'LIKE', "%{$search}%")
                  ->orWhere('user_id', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply deleted filter
        if ($deleted) {
            $query->where('is_deleted', 1);
        } else {
            $query->where('is_deleted', 0);
        }
        
        // Order and paginate
        $returnbottle = $query->orderBy('created_at', 'desc')->paginate(100);
        $returnbottleCount = $returnbottle->total();
        $page = $returnbottle->currentPage();
        
        // Get summary statistics
        $totalCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('is_deleted', 0)
            ->count();
            
        $pendingCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('status', 'pending')
            ->where('is_deleted', 0)
            ->count();
            
        $returnedCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('status', 'returned')
            ->where('is_deleted', 0)
            ->count();
            
        $totalAmount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('is_deleted', 0)
            ->sum('amount_paid');
        
        return view('admin.system-management.returnbottle.index', compact(
            'returnbottle', 
            'returnbottleCount', 
            'page', 
            'search', 
            'status', 
            'deleted',
            'totalCount',
            'pendingCount',
            'returnedCount',
            'totalAmount'
        ));
    }

    public function paginate($page, Request $request)
    {
        $page = (int)$page;
        $storeCode = auth()->user()->store_code;
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $deleted = $request->get('deleted', 0);
        
        // Build main query
        $query = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode);
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('trans_number', 'LIKE', "%{$search}%")
                  ->orWhere('remarks', 'LIKE', "%{$search}%")
                  ->orWhere('user_id', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply deleted filter
        if ($deleted) {
            $query->where('is_deleted', 1);
        } else {
            $query->where('is_deleted', 0);
        }
        
        // Order and paginate
        $returnbottle = $query->orderBy('created_at', 'desc')->paginate(100, '*', 'page', $page);
        $returnbottleCount = $returnbottle->total();
        
        // Get summary statistics
        $totalCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('is_deleted', 0)
            ->count();
            
        $pendingCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('status', 'pending')
            ->where('is_deleted', 0)
            ->count();
            
        $returnedCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('status', 'returned')
            ->where('is_deleted', 0)
            ->count();
            
        $totalAmount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('is_deleted', 0)
            ->sum('amount_paid');
        
        return view('admin.system-management.returnbottle.index', compact(
            'returnbottle', 
            'returnbottleCount', 
            'page', 
            'search', 
            'status', 
            'deleted',
            'totalCount',
            'pendingCount',
            'returnedCount',
            'totalAmount'
        ));
    }

    public function search($page, $search, Request $request)
    {
        $page = (int)$page;
        $storeCode = auth()->user()->store_code;
        $status = $request->get('status', '');
        $deleted = $request->get('deleted', 0);
        
        // Build main query
        $query = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where(function($q) use ($search) {
                $q->where('trans_number', 'LIKE', "%{$search}%")
                  ->orWhere('remarks', 'LIKE', "%{$search}%")
                  ->orWhere('user_id', 'LIKE', "%{$search}%");
            });
        
        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply deleted filter
        if ($deleted) {
            $query->where('is_deleted', 1);
        } else {
            $query->where('is_deleted', 0);
        }
        
        // Order and paginate
        $returnbottle = $query->orderBy('created_at', 'desc')->paginate(100, '*', 'page', $page);
        $returnbottleCount = $returnbottle->total();
        
        // Get summary statistics
        $totalCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('is_deleted', 0)
            ->count();
            
        $pendingCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('status', 'pending')
            ->where('is_deleted', 0)
            ->count();
            
        $returnedCount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('status', 'returned')
            ->where('is_deleted', 0)
            ->count();
            
        $totalAmount = DB::table('return_bottle_trans')
            ->where('store_code', $storeCode)
            ->where('is_deleted', 0)
            ->sum('amount_paid');
        
        return view('admin.system-management.returnbottle.index', compact(
            'returnbottle', 
            'returnbottleCount', 
            'page', 
            'search', 
            'status', 
            'deleted',
            'totalCount',
            'pendingCount',
            'returnedCount',
            'totalAmount'
        ));
    }

    public function add()
    {
        // Get users for dropdown
        $users = DB::table('users')
            ->where('store_code', auth()->user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('firstname')
            ->get();
            
        return view('admin.system-management.returnbottle.add', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'trans_number' => 'nullable|string|max:255',
            'quantity_return' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'nullable|in:pending,returned',
        ]);

        $storeCode = auth()->user()->store_code;
        $branchCode = auth()->user()->branch_code;
        
        DB::table('return_bottle_trans')->insert([
            'user_id' => $request->user_id,
            'trans_number' => $request->trans_number,
            'quantity_return' => $request->quantity_return,
            'amount_paid' => $request->amount_paid,
            'remarks' => $request->remarks,
            'status' => $request->status ?? 'pending',
            'is_uuid' => $this->generateUUID(),
            'is_deleted' => 0,
            'is_sync' => 0,
            'store_code' => $storeCode,
            'branch_code' => $branchCode,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.system-management.returnbottle.index')->with('success', 'Return bottle record added successfully!');
    }

    public function edit($id)
    {
        $storeCode = auth()->user()->store_code;
        
        $returnbottle = DB::table('return_bottle_trans')
            ->where('id', $id)
            ->where('store_code', $storeCode)
            ->first();
        
        if(!$returnbottle) {
            return redirect()->route('admin.system-management.returnbottle.index')->with('error', 'Return bottle record not found!');
        }
        
        // Get users for dropdown
        $users = DB::table('users')
            ->where('store_code', auth()->user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('firstname')
            ->get();
        
        return view('admin.system-management.returnbottle.edit', compact('returnbottle', 'users'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:return_bottle_trans,id',
            'user_id' => 'required|exists:users,id',
            'trans_number' => 'nullable|string|max:255',
            'quantity_return' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'required|in:pending,returned',
        ]);

        $storeCode = auth()->user()->store_code;
        
        DB::table('return_bottle_trans')
            ->where('id', $request->id)
            ->where('store_code', $storeCode)
            ->update([
                'user_id' => $request->user_id,
                'trans_number' => $request->trans_number,
                'quantity_return' => $request->quantity_return,
                'amount_paid' => $request->amount_paid,
                'remarks' => $request->remarks,
                'status' => $request->status,
                'updated_at' => now(),
                'is_sync' => 0,
            ]);

        return redirect()->route('admin.system-management.returnbottle.index')->with('success', 'Return bottle record updated successfully!');
    }

    public function delete($id)
    {
        $storeCode = auth()->user()->store_code;
        
        DB::table('return_bottle_trans')
            ->where('id', $id)
            ->where('store_code', $storeCode)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
                'is_sync' => 0,
            ]);

        return redirect()->route('returnbottle.index')->with('success', 'Return bottle record deleted successfully!');
    }

    public function restore($id)
    {
        $storeCode = auth()->user()->store_code;
        
        DB::table('return_bottle_trans')
            ->where('id', $id)
            ->where('store_code', $storeCode)
            ->update([
                'is_deleted' => 0,
                'updated_at' => now(),
                'is_sync' => 0,
            ]);

        return redirect()->route('admin.system-management.returnbottle.index')->with('success', 'Return bottle record restored successfully!');
    }

    public function complete($id)
    {
        $storeCode = auth()->user()->store_code;
        
        DB::table('return_bottle_trans')
            ->where('id', $id)
            ->where('store_code', $storeCode)
            ->update([
                'status' => 'returned',
                'updated_at' => now(),
                'is_sync' => 0,
            ]);

        return redirect()->route('admin.system-management.returnbottle.index')->with('success', 'Return bottle marked as completed!');
    }

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
        while (DB::table('return_bottle_trans')->where('is_uuid', $uuid)->exists()) {
            $uuid = (string) Str::uuid();
        }
        
        return $uuid;
    }
}