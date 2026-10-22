<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Log;

class SalesJournalController extends Controller
{
    public function index(Request $request)
    {
        // Allow filtering via query parameters (PRG friendly)
        if ($request->has('startDate')) {
            $startDate = date('Y-m-d', strtotime($request->get('startDate')));
        } else {
            $startDate = date('Y-m-d');
        }

        if ($request->has('endDate')) {
            $endDate = date('Y-m-d', strtotime($request->get('endDate')));
        } else {
            $endDate = date('Y-m-d');
        }

        $eendDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
        $selectedBranch = $request->get('branch', 'all');

        $settings = DB::table('settings')->where('id', 1)->first();
        
        // Get branches for dropdown
        $branches = DB::table('settings')
            ->where('store_code', auth()->user()->store_code)
            ->orderBy('name')
            ->get();

        // Base query
        $query = DB::table('sale_journal')
            ->select(
                'sale_journal.id', 
                'sale_journal.number as nn', 
                'sale_journal.total',
                'sale_journal.amount',
                'sale_journal.service_charge',
                'sale_journal.mode_of_payment',
                'sale_journal.payor_name',
                'sale_journal.payor_number',
                'sale_journal.slug', 
                'sale_journal.type', 
                'sale_journal.created_at as date',
                'sale_journal.sales_type', 
                'tables.name as table',
                'sale_journal.local_id',
                'sale_journal.branch_code',
                'settings.name as branchName', 
                'sale_journal.status as tstatus', 
                'sale_journal.order_status as ostatus'
            )
            ->join('tables', 'sale_journal.table', '=', 'tables.id')
            ->leftJoin('settings', function ($join) {
                $join->on('sale_journal.store_code', '=', 'settings.store_code');
            })
            ->whereBetween('sale_journal.created_at', [$startDate, $eendDate])
            ->where('sale_journal.store_code', auth()->user()->store_code);

        // Apply branch filter if not 'all' (Standardized to branch_code)
        if ($selectedBranch != 'all') {
            $query->where('sale_journal.branch_code', $selectedBranch);
        }

        $results = $query->orderBy('sale_journal.id', 'desc')->get();
        $resultsCount = $query->count();

        /* Start of Audited total */
        $auditedQuery = DB::table('sale_journal')
            ->where('order_status', '!=', 'CANCELLED')
            ->whereDate('created_at', $startDate)
            ->where('store_code', auth()->user()->store_code);

        $nonAuditedQuery = DB::table('sale_journal')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sale_journal.status', 'UNPAID')
            ->whereDate('created_at', $startDate)
            ->where('store_code', auth()->user()->store_code);

        // Apply branch filter to audit totals
        if ($selectedBranch != 'all') {
            $auditedQuery->where('branch_code', $selectedBranch);
            $nonAuditedQuery->where('branch_code', $selectedBranch);
        }

        $audited = number_format(
            $auditedQuery
                ->selectRaw('SUM(CASE WHEN amount > total THEN total ELSE amount END) as adjusted_total')
                ->value('adjusted_total') ?? 0,
            2, '.', ','
        );
        
        $nonAuditedTotal = (clone $nonAuditedQuery)->sum('total');
        $nonAuditedPaid = (clone $nonAuditedQuery)->sum('amount');
        
        $nonAudited = number_format($nonAuditedTotal - $nonAuditedPaid, 2, '.', ',');
        /* end of Audited total */

        return view('admin.accounting-reports.sale-journal', compact(
            'results', 'settings', 'resultsCount', 'startDate', 'endDate', 'audited', 'nonAudited', 'branches', 'selectedBranch'
        ));
    }

    public function generate(Request $request)
    {
        // Use PRG: redirect to index (GET) with filter params to avoid form resubmission warnings
        $selectedBranch = $request->branch ?? 'all';

        return redirect()->route('sales-journal.index', [
            'startDate' => $request->startDate ?? date('m/d/Y'),
            'endDate' => $request->endDate ?? date('m/d/Y'),
            'branch' => $selectedBranch,
        ]);
            
        /* Start of Audited total */
        $auditedQuery = DB::table('sale_journal')
            ->where('order_status', '!=', 'CANCELLED')
            ->whereBetween('created_at', [$startDate, $eendDate]) // Fixed date constraint logic
            ->where('store_code', auth()->user()->store_code);

        $nonAuditedQuery = DB::table('sale_journal')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sale_journal.status', 'UNPAID')
            ->whereBetween('created_at', [$startDate, $eendDate]) // Fixed date constraint logic
            ->where('store_code', auth()->user()->store_code);

        // Apply branch filter to audit totals
        if ($selectedBranch != 'all') {
            $auditedQuery->where('branch_code', $selectedBranch);
            $nonAuditedQuery->where('branch_code', $selectedBranch);
        }

        $audited = number_format(
            $auditedQuery
                ->selectRaw('SUM(CASE WHEN amount > total THEN total ELSE amount END) as adjusted_total')
                ->value('adjusted_total') ?? 0,
            2, '.', ','
        );
        
        $nonAuditedTotal = (clone $nonAuditedQuery)->sum('total');
        $nonAuditedPaid = (clone $nonAuditedQuery)->sum('amount');
        
        $nonAudited = number_format($nonAuditedTotal - $nonAuditedPaid, 2, '.', ',');

        return view('admin.accounting-reports.sale-journal', compact(
            'results', 'settings', 'resultsCount', 'startDate', 'endDate', 'audited', 'nonAudited', 'branches', 'selectedBranch'
        ));
    }

    public function updatestatus(Request $request)
    {
        $selectedBranch = $request->branch ?? 'all';
        $slug = $request->slug;
        $status ='';
        
        $trans = DB::table('transactions')->where('slug', $slug)->first();
        if (!$trans) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if ($trans->sales_type == 1){
            DB::table('transactions')->where('slug', $slug)
            ->update([
                'sales_type' => 0,
                'print' => 1,
            ]);
            $status = '1';
        } else {
            DB::table('transactions')->where('slug', $slug)
            ->update([
                'sales_type' => 1,
                'print' => 0,
            ]);
            $status = '0';
        }

        return response()->json([
            'status' => $status,
            'branch' => $selectedBranch
        ]);
    }

    public function view(Request $request)
    {
        $id = $request->id;

        // 1. First, get the transaction to know its branch_code
        $tran = DB::table('transactions')
            ->where('local_id', $id)
            ->where('store_code', auth()->user()->store_code)
            ->first();

        if(!$tran) {
            return response()->json([
                'number' => 'N/A',
                'ordered' => '<div class="text-red-500">Transaction not found</div>',
                'url' => '#',
                'printOnly' => '#'
            ]);
        }

        // 2. Use the transaction's ACTUAL branch_code
        $branch_code = $tran->branch_code; 

        // 3. Get ordered items for this transaction
        $orders = DB::table('ordered')
            ->join('menus', function($join) {
                $join->on('ordered.menu_uuid', '=', 'menus.is_uuid')
                     ->on('ordered.store_code', '=', 'menus.store_code');
            })
            ->select('ordered.quantity', 'menus.name', 'ordered.menu_id', 'ordered.branch_code as ordered_branch')
            ->where('ordered.tran_id', $id)
            ->where('ordered.store_code', auth()->user()->store_code)
            ->get();

        $viewOrder = '';

        if($orders->isEmpty()) {
            $viewOrder .= '<div class="text-red-500">No ordered items found</div>';

            // Debug: Check what's in ordered table
            $orderedItems = DB::table('ordered')
                ->where('tran_id', $id)
                ->where('store_code', auth()->user()->store_code)
                ->get();

            \Log::info('Transaction #' . $id . ' - Ordered items raw:', $orderedItems->toArray());
            \Log::info('Transaction branch_code:', $tran->branch_code);

            // Debug: Check menus table for the menu_ids found
            $menuIds = $orderedItems->pluck('menu_id')->toArray();
            if(!empty($menuIds)) {
                $menus = DB::table('menus')
                    ->where('store_code', auth()->user()->store_code)
                    ->whereIn('local_id', $menuIds)
                    ->get();
                \Log::info('Matching menus for transaction #' . $id . ':', $menus->toArray());
            }
        } else {
            // Group orders by branch
            $ordersByBranch = $orders->groupBy('ordered_branch');

            foreach($ordersByBranch as $branchCode => $branchOrders) {
                // Get branch name
                $branchName = DB::table('settings')
                    ->where('store_code', auth()->user()->store_code)
                    ->where('branch_code', $branchCode)
                    ->value('name') ?? $branchCode;

                $viewOrder .= '<div class="mb-4">';
                $viewOrder .= '<h4 class="font-bold text-lg text-blue-600 mb-2">Branch: ' . $branchName . '</h4>';

                foreach($branchOrders as $order){
                    $viewOrder .= '
                        <div class="flex w-full mb-2 ml-4">
                            <div class="w-1/5 font-bold">' . $order->quantity . 'x</div>
                            <div class="w-4/5">' . $order->name . '</div>
                        </div>';
                }
                $viewOrder .= '</div>';
            }
        }

        $printUrl = url('/transactions/print/'.$id);
        $printOnlyUrl = url('/transactions/printonly/'.$id);

        return response()->json([
            'number' => $tran->number,
            'ordered' => $viewOrder,
            'url' => $printUrl,
            'printOnly' => $printOnlyUrl,
        ]);
    }

    public function print($id)
    {
        DB::table('transactions')->where('id', $id)->update(['print' => 0]);
        return $this->renderBillView($id);
    }

    public function printonly($id)
    {
        return $this->renderBillView($id);
    }

    // Extracted logic to keep printing actions DRY
    private function renderBillView($id)
    {
        $trans = DB::table('transactions')
            ->select('transactions.*', 'tables.name as table_name', 'users.name')
            ->join('tables', 'transactions.table', '=', 'tables.id')
            ->join('users', 'transactions.cashier', '=', 'users.id')
            ->where('transactions.id', $id)
            ->first();

        $seniors = DB::table('seniors')->where('tran_id', $trans->id)->get();
        
        $orders = DB::table('ordered')
            ->select('ordered.*', 'menus.name')
            ->join('menus', 'ordered.menu_id', '=', 'menus.id')
            ->where('tran_id', $trans->id)
            ->get();

        $payments = DB::table('payment_orders')->where('tran_id', $trans->id)->get();
        $settings = DB::table('settings')->where('id', 2)->first();

        $orderdineincount = DB::table('ordered') 
            ->where('tran_id', $trans->id)
            ->where('item_takeout', '0')
            ->count();

        $ordertakeoutcount = DB::table('ordered') 
            ->where('tran_id', $trans->id)
            ->where('item_takeout', '1')
            ->count();

        return view('user.cashier.bill', compact(
            'orders', 'trans', 'settings', 'seniors', 'payments', 'ordertakeoutcount', 'orderdineincount'
        ));
    }
    
    public function returnTable(Request $request)
    {
        $slug = $request->slug;
        $trans = DB::table('transactions')->where('slug', $slug)->first();
        
        if (!$trans) {
            return response()->json(['error' => 'Transaction not found.'], 404);
        }

        $table = DB::table('tables')->where('id', $trans->table)->first();
            
        if ($table->status == 0) {
            DB::table('transactions')->where('slug', $slug)->update([
                'status' => 'UNPAID',
                'order_status' => 'PREPARING',
                'mode_of_payment' => NULL,
                'amount' => 0,
            ]);
            DB::table('payment_orders')->where('tran_id', $trans->id)->delete();   
            
            DB::table('tables')->where('id', $trans->table)->update([
                'status' => 1,
            ]);

            $log = new Log();
            $log->trans_id = $trans->id;
            $log->classification = 'Transactions';
            $log->info = 'Return Order to Table';
            $log->user_id = auth()->id();
            $log->event = 'Add';
            $log->save();

            return response()->json(['message' => 'Transaction updated successfully!']);
        } else {
            $transCount = DB::table('transactions')->where('table', $trans->table)->where('status', 'UNPAID')->count();
            if ($transCount > 0 || $table->status == 1) {
                return response()->json(['error' => "Unable to process: There is still a PENDING TRANSACTION that needs to be processed or the TABLE IS STILL OCCUPIED."]);
            } else {
                DB::table('transactions')->where('slug', $slug)->update([
                    'status' => 'UNPAID',
                    'order_status' => 'PREPARING',
                ]);

                $log = new Log();
                $log->trans_id = $trans->id;
                $log->classification = 'Transactions';
                $log->info = 'Return Order to Table';
                $log->user_id = auth()->id();
                $log->event = 'Add';
                $log->save();

                return response()->json(['message' => 'Transaction updated successfully!']);
            }
        }
    }

    public function bulkApprove(Request $request)
    {
        return $this->approve($request);
    }

    public function approve(Request $request)
    {
        $slugs = $request->slugs ?? [];

        if (!$slugs && $request->slug) {
            $slugs = $request->slug;
        }

        if (is_string($slugs)) {
            $slugs = [$slugs];
        }

        if (empty($slugs) || !is_array($slugs)) {
            return response()->json(['success' => false, 'error' => 'No transactions selected']);
        }

        $approvedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach($slugs as $slug){
                \Log::info('processing slug', ['slug' => $slug]);
                
                $trans = DB::table('transactions')
                    ->where('slug', $slug)
                    ->where('store_code', auth()->user()->store_code)
                    ->first();

                if(!$trans) {
                    \Log::warning('transaction not found', ['slug' => $slug]);
                    $errors[] = "Transaction with slug {$slug} not found";
                    continue;
                }

                \Log::info('transaction found for approval', [
                    'slug' => $slug, 
                    'trans_id' => $trans->id, 
                    'store_code' => $trans->store_code
                ]);

                // Check if already approved
                $exists = DB::table('sale_journal')
                    ->where('slug', $trans->slug)
                    ->where('store_code', $trans->store_code)
                    ->exists();

                if($exists) {
                    \Log::warning('transaction already approved', ['slug' => $slug]);
                    $errors[] = "Transaction with slug {$slug} is already approved";
                    continue;
                }

                // Prepare insert data with ALL required fields
                $insert = [
                    // REQUIRED FIELDS (NOT NULL)
                    'tran_id' => $trans->id,
                    'number' => $trans->number,
                    'total' => $trans->total,
                    'service_charge' => $trans->service_charge ?? '0',
                    'sc_percent' => $trans->sc_percent ?? '0',
                    'pwd' => $trans->pwd ?? '0',
                    'customer_with_discount' => $trans->customer_with_discount ?? '0',
                    'total_customer' => $trans->total_customer ?? '0',
                    'lime_discount' => $trans->lime_discount ?? '0',
                    'ld_value' => $trans->ld_value ?? '0',
                    'lessVatDeduction' => $trans->lessVatDeduction ?? 0,
                    'vat' => $trans->vat ?? 0,
                    'vatSales' => $trans->vatSales ?? 0,
                    'vatExemptSales' => $trans->vatExemptSales ?? 0,
                    'type' => $trans->type ?? '',
                    'is_vat' => $trans->is_vat ?? 0,
                    'table' => $trans->table ?? '',
                    'status' => $trans->status ?? '',
                    'order_status' => $trans->order_status ?? '',
                    'cashier' => $trans->cashier ?? '',
                    'slug' => $trans->slug,
                    'store_code' => $trans->store_code ?? auth()->user()->store_code,
                    'approved_by' => auth()->id(),
                    
                    // OPTIONAL FIELDS (DEFAULT NULL or have defaults)
                    'mode_of_payment' => $trans->mode_of_payment ?? null,
                    'amount' => $trans->amount ?? '0',
                    'fee' => $trans->fee ?? 0,
                    'payor_name' => $trans->payor_name ?? null,
                    'payor_number' => $trans->payor_number ?? null,
                    'order_remarks' => $trans->order_remarks ?? null,
                    'sales_type' => $trans->sales_type ?? 0,
                    'staff_id' => $trans->staff_id ?? null,
                    'print' => $trans->print ?? 1,
                    'created_at' => $trans->created_at ?? now(),
                    'updated_at' => $trans->updated_at ?? now(),
                    'is_uuid' => $trans->is_uuid ?? null,
                    'is_sync' => $trans->is_sync ?? 0,
                    'is_app' => $trans->is_app ?? 0,
                    'is_branch' => $trans->is_branch ?? null,
                    'branch_code' => $trans->branch_code ?? null,
                    'local_id' => $trans->local_id ?? null,
                    'branch' => $trans->branch ?? null,
                ];

                // Insert into sale_journal
                $insertId = DB::table('sale_journal')->insertGetId($insert);
                
                \Log::info('inserted sale_journal', [
                    'insert_id' => $insertId, 
                    'slug' => $trans->slug,
                    'tran_id' => $trans->id
                ]);

                // Mark original transaction as approved
                DB::table('transactions')
                    ->where('slug', $trans->slug)
                    ->update([
                        'is_approved' => 1,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                // Optional: Insert into approved_logs if needed
                // Make sure your approved_logs table has proper structure
                try {
                    DB::table('approved_logs')->insert([
                        'trans_id' => $trans->id,
                        'trans_number' => $trans->number,
                        'approve_by' => auth()->id(),
                        'approved_at' => now(),
                        'store_code' => $trans->store_code,
                        'branch_code' => $trans->branch_code,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to insert approved_logs', [
                        'error' => $e->getMessage(),
                        'trans_id' => $trans->id
                    ]);
                    // Don't fail the whole transaction if logging fails
                }

                $approvedCount++;
            }

            DB::commit();
            
            $message = $approvedCount . ' transaction(s) approved.';
            if (!empty($errors)) {
                $message .= ' Errors: ' . implode(', ', $errors);
            }
            
            return response()->json([
                'success' => true, 
                'message' => $message,
                'approved_count' => $approvedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('SaleJournal approve error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false, 
                'error' => 'Failed to approve transactions: ' . $e->getMessage()
            ]);
        }
    }
}