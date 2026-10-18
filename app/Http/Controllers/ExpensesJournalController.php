<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
 
class ExpensesJournalController extends Controller
{
    /**
     * Read-only list of approved expenses. expenses_journal already stores
     * a snapshot of the header at approval time, so no join back to
     * inventory_etransaction is needed for the listing itself — only to
     * users, to show who approved each entry.
     *
     * Styled after the client's Sale Journal reference: a date-range
     * filter (Generate/Reset) and a GRAND TOTAL row computed across the
     * *entire* filtered range, not just the current page.
     *
     * Dr/Cr breakdown: exp_type on inventory_transactions splits the
     * Debit side (Cost of Sales vs Operating Expenses); is_paid (the
     * Mode of Payment field: cash / bank transfer / 7|15|30 days) splits
     * the Credit side (Cash vs Accounts Payable). Cash and Bank Transfer
     * are both settled immediately, so both count as the Cash credit;
     * everything else (credit terms) is Accounts Payable. VAT and W/Tax
     * are only captured at the whole-transaction level on expenses_journal,
     * so each line-item split is scaled by the entry's own Dr/Cr ratio
     * (netSales/e_total and totalWithTax/e_total) rather than requiring
     * per-item VAT/WHT data that doesn't exist.
     */
public function index(Request $request)
{
    // Persist the date filter in session so it survives navigating away
    // and back, without resetting to "today" on every plain visit.
    // Resets naturally when the session resets (logout).
    if ($request->filled('from') || $request->filled('to')) {
        $from = $request->input('from', Carbon::today()->toDateString());
        $to = $request->input('to', Carbon::today()->toDateString());
        session(['expenses_journal_from' => $from, 'expenses_journal_to' => $to]);
    } else {
        $from = session('expenses_journal_from', Carbon::today()->toDateString());
        $to = session('expenses_journal_to', Carbon::today()->toDateString());
    }

    // NEW: Classification / Category / Supplier filters, applied from the
    // filter-bar dropdowns on the Journal page.
    $fClassification = $request->input('f_classification');
    $fCategory = $request->input('f_category');
    $fSupplier = $request->input('f_supplier');

        $query = DB::table('expenses_journal')
            ->where('expenses_journal.store_code', Auth::user()->store_code)
            ->whereDate('expenses_journal.approved_at', '>=', $from)
            ->whereDate('expenses_journal.approved_at', '<=', $to);

        // Same "at least one matching line item" semantics as the
        // Approval page — a journal entry can bundle several line items
        // with different classification/category/supplier values.
        if ($fClassification || $fCategory || $fSupplier) {
            $query->whereExists(function ($q) use ($fClassification, $fCategory, $fSupplier) {
                $q->select(DB::raw(1))
                    ->from('inventory_transactions')
                    ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'inventory_transactions.category_id')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transactions.supplier_id')
                    ->whereColumn('inventory_transactions.e_id', 'expenses_journal.e_id');

                if ($fClassification) {
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

        $totals = (clone $query)->selectRaw("
                COUNT(*) as entry_count,
                COALESCE(SUM(e_total), 0) as grand_subtotal,
                COALESCE(SUM(e_vat), 0) as grand_vat,
                COALESCE(SUM(e_wtax), 0) as grand_wtax,
                COALESCE(SUM(e_amount), 0) as grand_total
            ")->first();

        $journal = $query
            ->leftjoin('users', 'users.id', '=', 'expenses_journal.approved_by')
            ->select('expenses_journal.*', 'users.firstname', 'users.lastname')
            ->orderBy('expenses_journal.approved_at', 'DESC')
            ->paginate(50);

        $eIds = $journal->pluck('e_id');

        // Normalized Mode of Payment: lowercase with spaces, underscores and
        // hyphens stripped, so "Bank Transfer" / "bank_transfer" / "bank-transfer"
        // all compare equal to 'banktransfer'.
        $mop = "LOWER(REPLACE(REPLACE(REPLACE(is_paid, ' ', ''), '_', ''), '-', ''))";

        $lineTotals = DB::table('inventory_transactions')
            ->select('e_id',
                DB::raw("SUM(CASE WHEN exp_type = 0 THEN amount ELSE 0 END) as cos_gross"),
                DB::raw("SUM(CASE WHEN exp_type = 1 THEN amount ELSE 0 END) as opex_gross"),
                DB::raw("SUM(CASE WHEN {$mop} IN ('cash', 'banktransfer') THEN amount ELSE 0 END) as cash_gross"),
                DB::raw("SUM(CASE WHEN {$mop} NOT IN ('cash', 'banktransfer') THEN amount ELSE 0 END) as ap_gross")
            )
            ->whereIn('e_id', $eIds)
            ->groupBy('e_id')
            ->get()
            ->keyBy('e_id');

        $lineItemsByEId = DB::table('inventory_transactions')
            ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'inventory_transactions.category_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transactions.supplier_id')
            ->select(
                'inventory_transactions.*',
                'expenses_categories.name as category_name',
                'suppliers.name as supplier_name'
            )
            ->whereIn('inventory_transactions.e_id', $eIds)
            ->get()
            ->groupBy('e_id');

        $grand = ['cos' => 0, 'opex' => 0, 'vat' => 0, 'cash' => 0, 'ap' => 0, 'wtax' => 0];

        foreach ($journal as $entry) {
            $line = $lineTotals->get($entry->e_id);

            $debitRatio = $entry->e_total > 0 ? (($entry->e_total - $entry->e_vat) / $entry->e_total) : 0;
            $creditRatio = $entry->e_total > 0 ? (($entry->e_total - $entry->e_wtax) / $entry->e_total) : 0;

            $entry->cos_debit = $line ? round($line->cos_gross * $debitRatio, 2) : 0;
            $entry->opex_debit = $line ? round($line->opex_gross * $debitRatio, 2) : 0;
            $entry->cash_credit = $line ? round($line->cash_gross * $creditRatio, 2) : 0;
            $entry->ap_credit = $line ? round($line->ap_gross * $creditRatio, 2) : 0;

            $grand['cos'] += $entry->cos_debit;
            $grand['opex'] += $entry->opex_debit;
            $grand['vat'] += $entry->e_vat;
            $grand['cash'] += $entry->cash_credit;
            $grand['ap'] += $entry->ap_credit;
            $grand['wtax'] += $entry->e_wtax;
        }

        // NEW: full category/supplier lists for the filter dropdown
        // options — every configured value, not just what's on this page.
        $expcats = DB::table('expenses_categories')
            ->orderBy('name')
            ->get();

        $supps = DB::table('suppliers')
            ->orderBy('name')
            ->get();

        return view('user.inventory.expenses-journal.index', compact('journal', 'from', 'to', 'totals', 'grand', 'lineItemsByEId', 'expcats', 'supps'));
    }
}