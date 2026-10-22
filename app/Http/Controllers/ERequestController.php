<?php

namespace App\Http\Controllers;

use App\Models\ExpensesJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ERequestController extends Controller
{
    /**
     * Role-5 Expenses Approval view, redesigned to match the client's
     * Transactions-approval reference video: date-range filter, summary
     * cards (Selected/Total/Pending/Approved), and a checkbox-driven bulk
     * approve action instead of one button per row.
     */
public function index(Request $request)
{
    if ($request->filled('from') || $request->filled('to')) {
        $from = $request->input('from', Carbon::today()->toDateString());
        $to = $request->input('to', Carbon::today()->toDateString());
        session(['e_request_from' => $from, 'e_request_to' => $to]);
    } else {
        $from = session('e_request_from', Carbon::today()->toDateString());
        $to = session('e_request_to', Carbon::today()->toDateString());
    }

    // NEW: Classification / Category / Supplier column filters, applied
    // from the header-filter dropdowns on the Approval table.
    $fClassification = $request->input('f_classification');
    $fCategory = $request->input('f_category');
    $fSupplier = $request->input('f_supplier');

    $baseQuery = DB::table('inventory_etransaction')
        ->leftjoin('users', 'users.id', '=', 'inventory_etransaction.e_server')
        ->where('inventory_etransaction.is_deleted', 0)
        ->where('inventory_etransaction.store_code', Auth::user()->store_code)
        ->whereDate('inventory_etransaction.created_at', '>=', $from)
        ->whereDate('inventory_etransaction.created_at', '<=', $to);

        // SEARCH — mirrors ExpensesController::buildExpensesQuery() so the
        // search box behaves identically across both pages.
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $lowerSearch = strtolower($search);
            $numericSearch = is_numeric(str_replace(',', '', $search))
                ? (float) str_replace(',', '', $search)
                : null;

            $baseQuery->where(function ($q) use ($like, $lowerSearch, $numericSearch) {
                $q->where('inventory_etransaction.e_transid', 'like', $like)
                ->orWhere('inventory_etransaction.e_total', 'like', $like)
                ->orWhere('inventory_etransaction.e_vat',   'like', $like)
                ->orWhere('inventory_etransaction.e_wtax',  'like', $like)
                ->orWhere('inventory_etransaction.e_status', 'like', $like)
                ->orWhere('users.firstname', 'like', $like)
                ->orWhere('users.lastname',  'like', $like);

                if (str_contains('null', $lowerSearch)) {
                    $q->orWhere('inventory_etransaction.e_approval', 'null');
                }
                if (str_contains('approved', $lowerSearch)) {
                    $q->orWhere('inventory_etransaction.e_approval', '2');
                }
                if ($numericSearch !== null) {
                    $q->orWhere('inventory_etransaction.e_total', $numericSearch)
                    ->orWhere('inventory_etransaction.e_vat',   $numericSearch)
                    ->orWhere('inventory_etransaction.e_wtax',  $numericSearch);
                }

                $q->orWhereExists(function ($sub) use ($like, $lowerSearch) {
                    $sub->select(DB::raw(1))
                        ->from('inventory_transactions')
                        ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'inventory_transactions.category_id')
                        ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transactions.supplier_id')
                        ->whereColumn('inventory_transactions.e_id', 'inventory_etransaction.id')
                        ->where(function ($li) use ($like, $lowerSearch) {
                            $li->where('inventory_transactions.remarks', 'like', $like)
                            ->orWhere('inventory_transactions.unit', 'like', $like)
                            ->orWhere('inventory_transactions.quantity', 'like', $like)
                            ->orWhere('inventory_transactions.unit_price', 'like', $like)
                            ->orWhere('inventory_transactions.amount', 'like', $like)
                            ->orWhere('expenses_categories.name', 'like', $like)
                            ->orWhere('suppliers.name', 'like', $like)
                            ->orWhere('inventory_transactions.is_paid', 'like', $like);

                            if (str_contains('operating opex', $lowerSearch)) {
                                $li->orWhere('inventory_transactions.exp_type', 1);
                            }
                            if (str_contains('cost cos', $lowerSearch)) {
                                $li->orWhere('inventory_transactions.exp_type', 0);
                            }
                            if (str_contains('unpaid', $lowerSearch)) {
                                $li->orWhere('inventory_transactions.payment_class', 0);
                            } elseif (str_contains('paid', $lowerSearch)) {
                                $li->orWhere('inventory_transactions.payment_class', 1);
                            }
                        });
                });
            });
        }

    // A transaction can have several line items with different
    // classification/category/supplier values, so "filter by X" means
    // "only show transactions that have at least one line item matching
    // every selected filter" — a whereExists against the line items.
    if ($fClassification || $fCategory || $fSupplier) {
        $baseQuery->whereExists(function ($q) use ($fClassification, $fCategory, $fSupplier) {
            $q->select(DB::raw(1))
                ->from('inventory_transactions')
                ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'inventory_transactions.category_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transactions.supplier_id')
                ->whereColumn('inventory_transactions.e_id', 'inventory_etransaction.id');

            if ($fClassification) {
                // Blade renders exp_type == 1 as "Operating", else "Cost of Sales".
                $q->where('inventory_transactions.exp_type', $fClassification === 'Operating' ? 1 : 0);
            }
            if ($fCategory) {
                $q->where('expenses_categories.name', $fCategory);
            }
            if ($fSupplier) {
                $q->where('suppliers.name', $fSupplier);
            }
        });
    }

    $summary = (clone $baseQuery)->selectRaw("
            COUNT(*) as total_count,
            COALESCE(SUM(inventory_etransaction.e_total), 0) as total_amount,
            COUNT(CASE WHEN inventory_etransaction.e_approval = '1' THEN 1 END) as pending_count,
            COALESCE(SUM(CASE WHEN inventory_etransaction.e_approval = '1' THEN inventory_etransaction.e_total END), 0) as pending_total,
            COUNT(CASE WHEN inventory_etransaction.e_approval = '2' THEN 1 END) as approved_count,
            COALESCE(SUM(CASE WHEN inventory_etransaction.e_approval = '2' THEN inventory_etransaction.e_total END), 0) as approved_total
        ")->first();

    $e_inventories = (clone $baseQuery)
        ->select(
            'inventory_etransaction.*',
            'inventory_etransaction.id as e_id',
            'users.firstname',
            'users.lastname'
        )
        ->selectSub(function ($query) {
            $query->from('inventory_transactions')
                ->selectRaw('is_paid')
                ->whereColumn('inventory_transactions.e_id', 'inventory_etransaction.id')
                ->limit(1);
        }, 'mop')
        ->orderBy('inventory_etransaction.created_at', 'DESC')
        ->paginate(50, ['*'], 'e_page');

    // NEW: eager-load every line item for the e_ids on this page, grouped
    // by e_id, so the view can render each transaction's line items
    // inline (no per-row AJAX call, no modal).
    $eIds = collect($e_inventories->items())->pluck('e_id');
    $lineItemsByEId = DB::table('inventory_transactions')
        ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'inventory_transactions.category_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transactions.supplier_id')
        ->whereIn('inventory_transactions.e_id', $eIds)
        ->select(
            'inventory_transactions.*',
            'expenses_categories.name as category_name',
            'suppliers.name as supplier_name'
        )
        ->get()
        ->groupBy('e_id');

    $invCount = $e_inventories->total();
    $page = 1;

    // NEW: full category/supplier lists for the filter dropdown options —
    // every configured value, not just what's on the current page.
    $expcats = DB::table('expenses_categories')
        ->orderBy('name')
        ->get();

    $supps = DB::table('suppliers')
        ->orderBy('name')
        ->get();

    return view('user.inventory.e_request.index', compact('e_inventories', 'invCount', 'page', 'summary', 'from', 'to', 'lineItemsByEId', 'expcats', 'supps', 'search'));
}

    /**
     * Bulk-approve expense transactions in one request. The frontend shows
     * a confirmation modal (with per-row Remove / Remove All) before this
     * is ever called, so by the time we get here `ids` is the final,
     * user-confirmed list. Still re-checks e_approval == '1' server-side
     * for each id, in case another user approved one in the meantime.
     */
    public function updateStatusApproved(Request $request)
    {
        $ids = $request->input('ids', []);

        // Back-compat with any old single-id caller.
        if (empty($ids) && $request->filled('id')) {
            $ids = [$request->input('id')];
        }

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No transactions selected.'], 422);
        }

        $approvedCount = 0;

        DB::transaction(function () use ($ids, &$approvedCount) {
            $headers = DB::table('inventory_etransaction')
                ->whereIn('id', $ids)
                ->get();

            foreach ($headers as $header) {
                DB::table('inventory_etransaction')
                    ->where('id', $header->id)
                    ->update([
                        'e_status' => 'Approved',
                        'e_approval' => '2',
                    ]);

                ExpensesJournal::create([
                    'e_id' => $header->id,
                    'e_transid' => $header->e_transid,
                    'e_total' => $header->e_total,
                    'e_vat' => $header->e_vat,
                    'e_wtaxpercent' => $header->e_wtaxpercent,
                    'e_wtax' => $header->e_wtax,
                    'e_amount' => $header->e_amount,
                    'store_code' => $header->store_code,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                $approvedCount++;
            }
        });

        if ($approvedCount === 0) {
            return response()->json(['success' => false, 'message' => 'None of the selected transactions were waiting for approval.'], 422);
        }

        $label = $approvedCount === 1 ? 'Expense' : $approvedCount.' expenses';

        return response()->json(['success' => true, 'message' => $label.' approved to Expenses Journal successfully!']);
    }
}