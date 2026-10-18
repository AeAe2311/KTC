<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeletionApprovalController extends Controller
{
    /**
     * Role-5-only queue of expenses pending deletion. Mirrors the
     * date-range-filter + paginated-table style of ERequestController@index,
     * but is otherwise fully independent of that approval flow.
     */
    public function index(Request $request)
    {
        $from = $request->input('from') ?: Carbon::today()->toDateString();
        $to   = $request->input('to')   ?: Carbon::today()->toDateString();

        $query = DB::table('inventory_etransaction')
            ->leftjoin('users', 'users.id', '=', 'inventory_etransaction.deletion_requested_by')
            ->where('inventory_etransaction.is_deleted', 1)
            ->where('inventory_etransaction.store_code', Auth::user()->store_code)
            ->whereDate('inventory_etransaction.deletion_requested_at', '>=', $from)
            ->whereDate('inventory_etransaction.deletion_requested_at', '<=', $to);

        $pending = (clone $query)
            ->select('inventory_etransaction.*', 'inventory_etransaction.id as e_id',
                'users.firstname', 'users.lastname')
            ->orderBy('inventory_etransaction.deletion_requested_at', 'DESC')
            ->paginate(50, ['*'], 'e_page');

        $pendingCount = (clone $query)->count();

        // Line items for every transaction on this page — same shape as
        // ExpensesController::lineItemsFor(), grouped by e_id so the card's
        // nested sub-table can look it up with $lineItemsByEId->get($eId).
        $eIds = collect($pending->items())->pluck('id');
        $lineItemsByEId = $eIds->isEmpty() ? collect() : DB::table('inventory_transactions')
            ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'inventory_transactions.category_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transactions.supplier_id')
            ->whereIn('inventory_transactions.e_id', $eIds)
            ->select('inventory_transactions.*', 'expenses_categories.name as category_name', 'suppliers.name as supplier_name')
            ->get()
            ->groupBy('e_id');

        return view('user.inventory.deletion_approval.index', compact(
            'pending', 'pendingCount', 'from', 'to', 'lineItemsByEId'
        ));
    }

    /**
     * Approve a pending deletion: moves it to final deleted status (2).
     * The row stays in the DB for audit purposes — it's just excluded
     * from every existing `is_deleted = 0` query from here on.
     */
    public function approve(Request $request)
    {
        $id = $request->input('id');

        $transaction = DB::table('inventory_etransaction')->where('id', $id)->first();

        if (!$transaction || $transaction->is_deleted != 1) {
            return response()->json(['success' => false, 'message' => 'This transaction is not pending deletion.'], 422);
        }

        DB::table('inventory_etransaction')
            ->where('id', $id)
            ->update([
                'is_deleted' => 2,
                'deletion_reviewed_by' => Auth::id(),
                'deletion_reviewed_at' => now(),
                'updated_at' => now(),
            ]);

        $log = new Log();
        $log->trans_id = $id;
        $log->classification = 'Expense Transaction';
        $log->info = 'Deletion Approved';
        $log->user_id = Auth::id();
        $log->event = 'Deletion Approval';
        $log->save();

        return response()->json(['success' => true, 'message' => 'Deletion approved.']);
    }

    /**
     * Reject a pending deletion: reverts to normal (0), so the transaction
     * reappears in the General Expenses list exactly as it was before the
     * delete was requested. Its e_approval status (still 1, Waiting for
     * Approval — approved rows can never reach this queue in the first
     * place) is untouched.
     *
     * No comment is required — the reviewer only needs to confirm the
     * rejection in the UI warning modal.
     */
    public function reject(Request $request)
    {
        $id = $request->input('id');

        $request->validate([
            'id' => ['required', 'integer'],
        ]);

        $transaction = DB::table('inventory_etransaction')->where('id', $id)->first();

        if (!$transaction || $transaction->is_deleted != 1) {
            return response()->json(['success' => false, 'message' => 'This transaction is not pending deletion.'], 422);
        }

        DB::table('inventory_etransaction')
            ->where('id', $id)
            ->update([
                'is_deleted' => 0,
                'deletion_reviewed_by' => Auth::id(),
                'deletion_reviewed_at' => now(),
                'deletion_reject_comment' => null,
                'updated_at' => now(),
            ]);

        $log = new Log();
        $log->trans_id = $id;
        $log->classification = 'Expense Transaction';
        $log->info = 'Deletion Rejected';
        $log->user_id = Auth::id();
        $log->event = 'Deletion Rejection';
        $log->save();

        return response()->json(['success' => true, 'message' => 'Deletion rejected. Transaction restored.']);
    }
}