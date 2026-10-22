<?php

namespace App\Http\Controllers;

use App\Models\RevolvingTransaction;
use App\Models\RevolvingCartTransaction;
use App\Models\RevolvingBudget;
use App\Models\ExpensesCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevolvingController extends Controller
{
    /**
     * Shared query builder so index / paginate / search behave identically.
     * Mirrors ExpensesController::buildExpensesQuery, adapted to
     * revolving_transactions (which has no e_id grouping — each row is
     * its own line item, filtered against the current store).
     */
    private function buildRevolvingQuery(Request $request, $dateFrom = null, $dateTo = null)
    {
        $query = DB::table('revolving_transactions')
            ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'revolving_transactions.category_id')
            ->leftJoin('revolving_budgets', 'revolving_budgets.id', '=', 'revolving_transactions.rev_id')
            ->select(
                'revolving_transactions.*',
                'expenses_categories.name as cat_name',
                'revolving_budgets.classification as budget_name'
            )
            ->where('revolving_transactions.store_code', Auth::user()->store_code)
            ->where('revolving_transactions.is_deleted', 0)
            ->orderBy('revolving_transactions.created_at', 'DESC');

        if ($dateFrom) {
            $query->whereDate('revolving_transactions.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('revolving_transactions.created_at', '<=', $dateTo);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('revolving_transactions.particulars', 'like', $like)
                  ->orWhere('revolving_transactions.description', 'like', $like)
                  ->orWhere('expenses_categories.name', 'like', $like)
                  ->orWhere('revolving_budgets.classification', 'like', $like);
            });
        }

        // Column header filters (the chevron dropdowns in the table header) —
        // mirrors the f_* query-param pattern from the General Expenses page.
        $fCategory = trim((string) $request->query('f_category', ''));
        if ($fCategory !== '') {
            $query->where('expenses_categories.name', $fCategory);
        }
        $fBudget = trim((string) $request->query('f_budget', ''));
        if ($fBudget !== '') {
            $query->where('revolving_budgets.classification', $fBudget);
        }

        return $query;
    }

    public function index(Request $request, $page = 1)
    {
        // Default the date filter to today when the user hasn't picked a
        // range yet, instead of showing every transaction ever recorded.
        $today    = now()->toDateString();
        $dateFrom = $request->filled('from') ? $request->query('from') : $today;
        $dateTo   = $request->filled('to')   ? $request->query('to')   : $today;
        $search   = trim((string) $request->query('search', ''));

        $inventories = $this->buildRevolvingQuery($request, $dateFrom, $dateTo)
            ->paginate(100, ['*'], 'page', $page);

        // DB::table() rows are plain stdClass, so created_at comes back as a
        // raw string — cast it to Carbon here since the view calls ->format() on it.
        $inventories->getCollection()->transform(function ($row) {
            $row->created_at = \Carbon\Carbon::parse($row->created_at);
            return $row;
        });

        $invCount = $inventories->total();

        // Option lists for the column-filter dropdowns (Category / Budget).
        $expcats = DB::table('expenses_categories')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        $budgetList = DB::table('revolving_budgets')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('classification', 'asc')
            ->get();

        return view('user.inventory.revolving.index', compact(
            'inventories', 'invCount', 'page', 'search', 'expcats', 'budgetList'
        ) + ['from' => $dateFrom, 'to' => $dateTo]);
    }

    public function paginate(Request $request, $page = 1)
    {
        return $this->index($request, $page);
    }

    public function search(Request $request, $page = 1, $search = '')
    {
        // Normalize legacy /revolving/{page}/{search} URLs into query params
        if ($search !== '' && !$request->query('search')) {
            $request->merge(['search' => $search]);
        }
        return $this->index($request, $page);
    }

    /**
     * "Add - Revolving Expenses" page: a cart-style form on the left,
     * the current user's not-yet-submitted cart on the right.
     */
    public function add(Request $request)
    {
        $expcats = DB::table('expenses_categories')
            ->orderBy('id', 'asc')
            ->where('is_deleted', 0)
            ->get();

        $budget = DB::table('revolving_budgets')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->get();

        $inventories = RevolvingCartTransaction::where('user_id', auth()->id())->get();

        $inventoriesCount = RevolvingCartTransaction::where('user_id', auth()->id())->count();

        return view('user.inventory.revolving.add', compact('expcats', 'budget', 'inventories', 'inventoriesCount'));
    }

    /**
     * Adds one line item to the current user's cart (revolving_cart_transactions).
     * Matches the fields posted by revolving/add.blade.php's form JS.
     */
    public function store(Request $request)
    {
        $request->validate([
            'particulars'  => 'required',
            'description'  => 'required',
            'quantity'     => 'required',
            'unit_amount'  => 'required',
            'price'        => 'required',
            'list'         => 'required',
        ]);

        $item = new RevolvingCartTransaction();
        $item->inv_id       = 0;
        $item->category_id  = $request->category_id;
        $item->particulars  = $request->particulars;
        $item->description  = $request->description;
        $item->quantity     = str_replace(',', '', $request->quantity);
        $item->unit_amount  = str_replace(',', '', $request->unit_amount);
        $item->amount       = str_replace(',', '', $request->price);
        $item->other_remarks = $request->other_name;
        $item->rev_id       = $request->list;
        $item->purchased_from = now();
        $item->user_id      = Auth::user()->id;

        if (Auth::user()->role == 1 || Auth::user()->role == 5) {
            $dateAdd = $request->dateAdd . ' ' . date('H:i:s');
            $item->created_at = date('Y/m/d H:i:s', strtotime($dateAdd));
        }

        $item->save();

        $inventories = RevolvingCartTransaction::where('user_id', auth()->id())->latest()->get();

        return response()->json(['inventories' => $inventories]);
    }

    public function cart_delete(Request $request)
    {
        $slug = $request->slug;
        RevolvingCartTransaction::where('id', $slug)->where('user_id', auth()->id())->delete();

        return response()->json(['success' => true, 'message' => 'Data deleted successfully.']);
    }

    /**
     * Moves the current user's cart into revolving_transactions and
     * deducts the spent amount from each affected budget's current_budget
     * (grouped by rev_id, since a cart can span more than one fund).
     */
    public function cart_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $cartItems = DB::table('revolving_cart_transactions')
                ->where('user_id', auth()->id())
                ->get();

            $totalsByRevId = [];

            foreach ($cartItems as $cartItem) {
                DB::table('revolving_transactions')->insert([
                    'department_id'   => $cartItem->department_id,
                    'inv_id'          => $cartItem->inv_id,
                    'type'            => $cartItem->type,
                    'category_id'     => $cartItem->category_id,
                    'supplier_id'     => $cartItem->supplier_id,
                    'quantity_before' => $cartItem->quantity_before,
                    'quantity'        => $cartItem->quantity,
                    'quantity_after'  => $cartItem->quantity_after,
                    'amount'          => $cartItem->amount,
                    'remarks'         => $cartItem->remarks,
                    'unit'            => $cartItem->unit,
                    'unit_price'      => $cartItem->unit_price,
                    'exp_type'        => $cartItem->exp_type,
                    'other_remarks'   => $cartItem->other_remarks,
                    'user_id'         => $cartItem->user_id,
                    'is_paid'         => $cartItem->is_paid,
                    'payment_class'   => $cartItem->payment_class,
                    'particulars'     => $cartItem->particulars,
                    'description'     => $cartItem->description,
                    'unit_amount'     => $cartItem->unit_amount,
                    'rev_id'          => $cartItem->rev_id,
                    'purchased_from'  => $cartItem->purchased_from,
                    'store_code'      => Auth::user()->store_code,
                    'created_at'      => $cartItem->created_at,
                    'updated_at'      => $cartItem->updated_at,
                ]);

                $totalsByRevId[$cartItem->rev_id] = ($totalsByRevId[$cartItem->rev_id] ?? 0) + (float) $cartItem->amount;
            }

            foreach ($totalsByRevId as $revId => $spent) {
                DB::table('revolving_budgets')->where('id', $revId)
                    ->decrement('current_budget', $spent);
            }

            DB::table('revolving_cart_transactions')->where('user_id', auth()->id())->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data transferred successfully.']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $item = RevolvingTransaction::with('ExpensesCategory')->where('id', $id)->first();

        $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        return view('user.inventory.revolving.edit', compact('item', 'expcats'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'unit_amount'  => 'required',
            'total_amount' => 'required',
        ]);

        DB::table('revolving_transactions')->where('id', $request->id)
            ->update([
                'category_id'   => $request->category,
                'unit_amount'   => str_replace(',', '', $request->unit_amount),
                'amount'        => str_replace(',', '', $request->total_amount),
                'other_remarks' => $request->remarks,
            ]);

        return redirect()->route('revolving.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($id)
    {
        DB::table('revolving_transactions')->where('id', $id)->delete();

        return redirect()->route('revolving.index')->withInput()->with('message', 'Successfully Deleted');
    }

    // Not wired to any current view (no matching route call in the revolving
    // blade files) — kept as harmless no-ops so the routes still resolve.
    public function addqty(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented.']);
    }

    public function minusqty(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Not implemented.']);
    }

    /**
     * "Cash Funds" — list of revolving_budgets for this store.
     */
    public function budget_index(Request $request, $page = 1)
    {
        $search = trim((string) $request->query('search', ''));

        $query = DB::table('revolving_budgets')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc');

        if ($search !== '') {
            $query->where('classification', 'like', '%' . $search . '%');
        }

        $revolving_budget = $query->paginate(100, ['*'], 'page', $page);
        $invCount = $revolving_budget->total();

        return view('user.inventory.revolving.budget_index', compact('revolving_budget', 'search', 'page', 'invCount'));
    }

    public function addNewBudget()
    {
        return view('user.inventory.revolving.add_new_budget');
    }

    public function budget_store(Request $request)
    {
        $request->validate([
            'new_budget' => 'required',
        ]);

        DB::table('revolving_budgets')->insert([
            'classification'  => $request->new_budget,
            'current_budget'  => 0,
            'is_deleted'      => 0,
            'user_id'         => Auth::user()->id,
            'branch_code'     => Auth::user()->branch_code,
            'store_code'      => Auth::user()->store_code,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('revolving.budget_index')->with('message', 'Budget added successfully');
    }

    public function add_budget($id)
    {
        $budget = DB::table('revolving_budgets')->where('id', $id)->first();
        $item = null;

        return view('user.inventory.revolving.add_budget', compact('budget', 'item'));
    }

    public function budget($id)
    {
        $budget = DB::table('revolving_budgets')->where('id', $id)->first();
        $item = null;

        return view('user.inventory.revolving.budget', compact('budget', 'item'));
    }

    /**
     * "Update Budget" — sets current_budget to the submitted value outright.
     */
    public function update_budget(Request $request)
    {
        DB::table('revolving_budgets')->where('id', $request->id)
            ->update([
                'current_budget' => str_replace(',', '', $request->current_budget),
                'updated_at'     => now(),
            ]);

        return redirect()->route('revolving.index')->with('message', 'Budget updated successfully');
    }

    /**
     * "Add Budget" — adds the submitted amount on top of current_budget.
     */
    public function addition_budget(Request $request)
    {
        DB::table('revolving_budgets')->where('id', $request->id)
            ->increment('current_budget', str_replace(',', '', $request->current_budget));

        return redirect()->route('revolving.index')->with('message', 'Budget updated successfully');
    }
}