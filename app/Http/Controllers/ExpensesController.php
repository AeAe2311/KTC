<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryCartTransaction;
use App\Models\ExpensesCategory;
use App\Models\Supplier;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExpensesController extends Controller
{
/**
 * Bank Transfer is settled immediately, so any line item whose Mode of
 * Payment (is_paid) is a bank transfer is forced to Paid (payment_class = 1).
 * Matches "Bank Transfer", "bank_transfer", "bank-transfer", etc.
 */
private function isBankTransfer($value): bool
{
    return strtolower(preg_replace('/[^a-z]/i', '', (string) $value)) === 'banktransfer';
}

/**
 * Shared query builder so index / paginate / search behave identically.
 */
private function buildExpensesQuery(Request $request, $dateFrom = null, $dateTo = null)
{
    $query = DB::table('inventory_etransaction')
        ->leftJoin('users', 'users.id', '=', 'inventory_etransaction.e_server')
        ->select(
            'inventory_etransaction.*',
            'inventory_etransaction.id as e_id',
            'users.firstname',
            'users.lastname',

            DB::raw('(select is_paid from inventory_transactions
                    where inventory_transactions.e_id = inventory_etransaction.id
                    limit 1) as mop')
        )
        ->where('inventory_etransaction.store_code', Auth::user()->store_code)
        ->where('inventory_etransaction.is_deleted', 0)
        ->orderBy('inventory_etransaction.created_at', 'DESC');

    if ($dateFrom) {
        $query->whereDate('inventory_etransaction.created_at', '>=', $dateFrom);
    }
    if ($dateTo) {
        $query->whereDate('inventory_etransaction.created_at', '<=', $dateTo);
    }

                $search = trim((string) $request->query('search', ''));
                if ($search !== '') {
                    $like = '%' . $search . '%';

                    // Normalize the search once for smart comparisons (e.g. amounts,
                    // status keywords, classification keywords).
                    $lowerSearch = strtolower($search);
                    $numericSearch = is_numeric(str_replace(',', '', $search))
                        ? (float) str_replace(',', '', $search)
                        : null;

                    $query->where(function ($q) use ($like, $lowerSearch, $numericSearch) {
                        // ---- Parent transaction columns ----
                        $q->where('inventory_etransaction.e_transid', 'like', $like)
                        ->orWhere('inventory_etransaction.e_total', 'like', $like)
                        ->orWhere('inventory_etransaction.e_vat',   'like', $like)
                        ->orWhere('inventory_etransaction.e_wtax',  'like', $like)
                        ->orWhere('inventory_etransaction.e_status', 'like', $like);

                        // ---- Server / Cashier ----
                        $q->orWhere('users.firstname', 'like', $like)
                        ->orWhere('users.lastname',  'like', $like);

                        // ---- Status keyword shortcuts ----
                        // e_approval: 1 = Waiting, 2 = Approved
                        if (str_contains('waiting pending', $lowerSearch)) {
                            $q->orWhere('inventory_etransaction.e_approval', '1');
                        }
                        if (str_contains('approved', $lowerSearch)) {
                            $q->orWhere('inventory_etransaction.e_approval', '2');
                        }

                        // ---- Numeric shortcuts (exact match on money fields) ----
                        if ($numericSearch !== null) {
                            $q->orWhere('inventory_etransaction.e_total', $numericSearch)
                            ->orWhere('inventory_etransaction.e_vat',   $numericSearch)
                            ->orWhere('inventory_etransaction.e_wtax',  $numericSearch);
                        }

                        // ---- Line items (via a correlated subquery) ----
                        // Any line item matching the search term promotes the whole
                        // parent transaction into the result set.
                        $q->orWhereExists(function ($sub) use ($like, $lowerSearch) {
                            $sub->select(DB::raw(1))
                                ->from('inventory_transactions')
                                ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'inventory_transactions.category_id')
                                ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transactions.supplier_id')
                                ->whereColumn('inventory_transactions.e_id', 'inventory_etransaction.id')
                                ->where(function ($li) use ($like, $lowerSearch) {
                                    $li->where('inventory_transactions.remarks',    'like', $like)
                                    ->orWhere('inventory_transactions.unit',     'like', $like)
                                    ->orWhere('inventory_transactions.quantity', 'like', $like)
                                    ->orWhere('inventory_transactions.unit_price', 'like', $like)
                                    ->orWhere('inventory_transactions.amount',   'like', $like)
                                    ->orWhere('expenses_categories.name',        'like', $like)
                                    ->orWhere('suppliers.name',                  'like', $like)
                                    ->orWhere('inventory_transactions.is_paid',  'like', $like);

                                    // Classification keyword shortcut: "operating" or "cost"
                                    if (str_contains('operating opex', $lowerSearch)) {
                                        $li->orWhere('inventory_transactions.exp_type', 1);
                                    }
                                    if (str_contains('cost cos', $lowerSearch)) {
                                        $li->orWhere('inventory_transactions.exp_type', 0);
                                    }

                                    // Payment status keyword: "paid" / "unpaid"
                                    if (str_contains('unpaid', $lowerSearch)) {
                                        $li->orWhere('inventory_transactions.payment_class', 0);
                                    } elseif (str_contains('paid', $lowerSearch)) {
                                        $li->orWhere('inventory_transactions.payment_class', 1);
                                    }
                                });
                        });
                    });
                }

    // ---- Column filters (only the 5 columns that have a chevron) ----

    // Status — the dropdown sends the badge label, not the raw code.
    if ($request->filled('f_e_approval')) {
        $statusMap = ['Waiting' => '1', 'Approved' => '2'];
        $val = $request->query('f_e_approval');
        if (isset($statusMap[$val])) {
            $query->where('inventory_etransaction.e_approval', $statusMap[$val]);
        } else {
            // Anything else (e.g. "Draft") falls back to matching e_status text directly.
            $query->where('inventory_etransaction.e_status', $val);
        }
    }

    // Classification / Category / Supplier all live on inventory_transactions
    // (the line items), so filter parent transactions down to only those
    // that have at least one matching line item.
    if ($request->filled('f_classification') || $request->filled('f_category') || $request->filled('f_supplier')) {
        $query->whereIn('inventory_etransaction.id', function ($sub) use ($request) {
            $sub->select('inventory_transactions.e_id')
                ->from('inventory_transactions')
                ->leftJoin('expenses_categories', 'expenses_categories.id', '=', 'inventory_transactions.category_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'inventory_transactions.supplier_id');

            if ($request->filled('f_classification')) {
                $classMap = ['Operating' => 1, 'Cost of Sales' => 0];
                $val = $request->query('f_classification');
                if (isset($classMap[$val])) {
                    $sub->where('inventory_transactions.exp_type', $classMap[$val]);
                }
            }
            if ($request->filled('f_category')) {
                $sub->where('expenses_categories.name', $request->query('f_category'));
            }
            if ($request->filled('f_supplier')) {
                $sub->where('suppliers.name', $request->query('f_supplier'));
            }
        });
    }

    return $query;
}

/**
 * Eager-load all line items for the given parent transaction ids,
 * grouped by e_id.
 */
private function lineItemsFor($eIds)
{
    if ($eIds->isEmpty()) {
        return collect();
    }

    return DB::table('inventory_transactions')
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
}

public function index(Request $request, $page = 1)
{
    // Treat "" the same as "not provided" (the filter form submits empty
    // inputs as blank strings, not null).
    $dateFrom = $request->filled('from') ? $request->query('from') : null;
    $dateTo   = $request->filled('to')   ? $request->query('to')   : null;
    $search   = trim((string) $request->query('search', ''));

    // First visit → default to today
    if ($dateFrom === null && $dateTo === null && $search === '') {
        $dateFrom = now()->toDateString();
        $dateTo   = now()->toDateString();
    }

    $e_inventories = $this->buildExpensesQuery($request, $dateFrom, $dateTo)
        ->paginate(50, ['*'], 'page', $page);

    // Build the line-items map for every e_id on this page
    $eIds = collect($e_inventories->items())->pluck('e_id');
    $lineItemsByEId = $this->lineItemsFor($eIds);

    // Inventory list
    $inventories = InventoryTransaction::with('ExpensesCategory', 'supplier')
        ->where('inventory_transactions.store_code', Auth::user()->store_code)
        ->orderBy('created_at', 'DESC')
        ->where('inv_id', 0)
        ->paginate(100);

    $expcats    = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
    $supps      = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
    $servername = DB::table('users')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

    $invCount = $e_inventories->total();

    $audited = number_format(
        DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 1)
            ->sum('total'),
        2, '.', ','
    );
    $nonAudited = number_format(
        DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('sales_type', 0)
            ->sum('total'),
        2, '.', ','
    );

    return view('user.inventory.expenses.index', compact(
        'servername', 'expcats', 'supps',
        'e_inventories', 'inventories',
        'invCount', 'page', 'search',
        'audited', 'nonAudited',
        'lineItemsByEId'
    ) + ['from' => $dateFrom, 'to' => $dateTo]);
}


public function paginate(Request $request, $page = 1)
{
    return $this->index($request, $page);
}

public function search(Request $request, $page = 1, $search = '')
{
    // Normalize legacy /expenses/{page}/{search} URLs into query params
    if ($search !== '' && !$request->query('search')) {
        $request->merge(['search' => $search]);
    }
    return $this->index($request, $page);
}
    public function add(Request $request)
    {
        $formType = $request->query('form', 'cost');

        $expcats = DB::table('expenses_categories')
        //->where('store_code', Auth::user()->store_code)
        ->orderBy('id', 'asc')
        ->where('is_deleted', 0)
        ->get();

        // Fetch suppliers
        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        // $supps1 = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        
        // Fetch today's inventory transactions
        // $today = Carbon::today(); //comment out by leo - 8/30/26
        $inventories = InventoryCartTransaction::with('ExpensesCategory', 'supplier')   
            ->where('user_id', auth()->id())
            ->where('inv_id', 0)
            ->get();
        
        $inventoriesCount = InventoryCartTransaction::with('ExpensesCategory', 'supplier')
            ->where('user_id', auth()->id())
            // ->whereDate('created_at', $today) //comment out by leo - 8/30/26
            ->where('inv_id', 0)
            ->count();

        $dept = DB::table('departments')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', '0')
        ->orderBy('id', 'asc')
        ->get();
        $dept1 = DB::table('departments')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', '0')
        ->orderBy('id', 'asc')
        ->get();

        // Pass the form type to the view
        return view('user.inventory.expenses.add', compact('dept1','dept','expcats', 'supps','inventories', 'inventoriesCount', 'formType'));
        
        
    }


    public function store(Request $request) {
        $request->validate([
            'name' => 'required',
            'quantity' => 'required',
            'price' => 'required',
            'category' => 'required',
            'supplier' => 'required',
        ]);

        $name = $request->name;
        $other_name = $request->other_name;
        $status = $request->status;
        // Bank Transfer is always Paid, regardless of what the form sent.
        $payment_class = $this->isBankTransfer($status) ? 1 : $request->payment_class;
        $unit = $request->unit;
        $exp_type = $request->exp_type;
        $unit_price = str_replace(',', '', $request->unit_price);
        $quantity = str_replace(',', '', $request->quantity);
        $price = str_replace(',', '', $request->price);
        $department = $request->department;

        // category
        $categoryArray = explode(",", $request->category);
        $is_cat_name = $categoryArray[0] ?? '';
        $is_cat_id = $categoryArray[1] ?? null;

        // supplier
        $SupplierArray = explode(",", $request->supplier);
        $is_supp_name = $SupplierArray[0] ?? '';
        $is_supp_id = $SupplierArray[1] ?? null;

        $item = new InventoryCartTransaction();
        $item->inv_id = 0;
        $item->type = strtoupper($is_cat_name);
        $item->category_id = $is_cat_id;
        $item->supplier_id = $is_supp_id;
        $item->quantity_before = 0;
        $item->quantity = $quantity;
        $item->quantity_after = 0;
        $item->amount = $price;
        $item->remarks = $name;
        $item->unit = $unit;
        $item->unit_price = $unit_price;
        $item->other_remarks = $other_name;
        $item->is_paid = $status;
        $item->payment_class = $payment_class;
        $item->exp_type = $exp_type;
        $item->user_id = Auth::user()->id;
        $item->store_code = Auth::user()->store_code;
        $item->department_id = $department;

        if(Auth::user()->role == 1 || Auth::user()->role == 5){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
            $item->created_at = date('Y/m/d H:i:s', strtotime($dateAdd));
        }

        $item->save();

        // Return the latest inventories as JSON response
        $inventories = InventoryCartTransaction::latest()->get(); // You can adjust the query as needed

        return response()->json(['inventories' => $inventories]);
    }

    // for the second form
    public function storeOther(Request $request) {
        $request->validate([
            'name1' => 'required',
            'quantity1' => 'required',
            'price1' => 'required',
            'category1' => 'required',
            'supplier1' => 'required',
        ]);

        $name1 = $request->name1;
        $other_name1 = $request->other_name1;
        $status1 = $request->status1;
        // Bank Transfer is always Paid, regardless of what the form sent.
        $payment_class1 = $this->isBankTransfer($status1) ? 1 : $request->payment_class1;
        $unit1 = $request->unit1;
        $exp_type1 = $request->exp_type1;
        $unit_price1 = str_replace(',', '', $request->unit_price1);
        $quantity1 = str_replace(',', '', $request->quantity1);
        $price1 = str_replace(',', '', $request->price1);
        $department1 = $request->department1;

        // category
        $categoryArray1 = explode(",", $request->category1);
        $is_cat_name = $categoryArray1[0] ?? '';
        $is_cat_id = $categoryArray1[1] ?? null;

        // supplier
        $SupplierArray1 = explode(",", $request->supplier1);
        $is_supp_name = $SupplierArray1[0] ?? '';
        $is_supp_id = $SupplierArray1[1] ?? null;

        $item = new InventoryCartTransaction();
        $item->inv_id = 0;
        $item->type = strtoupper($is_cat_name);
        $item->category_id = $is_cat_id;
        $item->supplier_id = $is_supp_id;
        $item->quantity_before = 0;
        $item->quantity = $quantity1;
        $item->quantity_after = 0;
        $item->amount = $price1;
        $item->remarks = $name1;
        $item->unit = $unit1;
        $item->unit_price = $unit_price1;
        $item->other_remarks = $other_name1;
        $item->is_paid = $status1;
        $item->payment_class = $payment_class1;
        $item->exp_type = $exp_type1;
        $item->user_id = Auth::user()->id;
        $item->store_code = Auth::user()->store_code;
        $item->department_id = $department1;

        if(Auth::user()->role == 1 || Auth::user()->role == 5){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
            $item->created_at = date('Y/m/d H:i:s', strtotime($dateAdd));
        }

        $item->save();

        // Return the latest inventories as JSON response
        $inventories = InventoryCartTransaction::latest()->get(); // You can adjust the query as needed

        return response()->json(['inventories' => $inventories]);
    }
    // end of second form 


    public function cart_store(Request $request)
    {
        $validated = $request->validate([
            'e_total' => 'required|numeric',
            'e_vat' => 'required|numeric',
            'e_wtaxpercent' => 'required|numeric',
            'e_wtax' => 'required|numeric',
            'e_amount' => 'required|numeric',
        ]);
        
        $eTotal = $validated['e_total'];
        $eVat = $validated['e_vat'];
        $eWtaxperncentage = $validated['e_wtaxpercent'];
        $eWtax = $validated['e_wtax'];
        $eAmount = $validated['e_amount'];
        
        $currentDate = now()->format('Ymd');
        $count = DB::table('inventory_etransaction')
        ->whereDate('created_at', now()->toDateString())  // Filter transactions by today's date
        ->count();
        $transactionId = $currentDate . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        DB::beginTransaction();
        try {
            
            $eTransactionId = DB::table('inventory_etransaction')->insertGetId([
             'e_transid' => $transactionId,   
            'e_server'  => Auth::user()->id,
            'e_total' =>  $eTotal,
            'e_vat' => $eVat,
            'e_wtaxpercent' => $eWtaxperncentage,
            'e_wtax' => $eWtax,
            'e_amount' => $eAmount,
            'e_server' => Auth::user()->id,
            'store_code' => Auth::user()->store_code,
            'is_deleted' => 0,
            // OPTION A: auto-submit for approval at creation time. The task
            // spec goes straight from "role 1,2 creates it" to "role 5
            // approves it" with no separate submission step described, and
            // says role 1,2's view should be "without checkbox or
            // approval" — so there's no Draft stage for them to manage.
            // Every transaction is immediately visible/selectable on the
            // role 5 approval screen.
            'e_status' => 'Waiting for Approval',
            'e_approval' => '1',
            'created_at' => now(),
            'updated_at' => now()
        ]);



            // Retrieve all data from the inventory_cart_transactions table
            $transactions = DB::table('inventory_cart_transactions')->where('user_id', auth()->id())->get();

            // Insert the retrieved data into the inventory_expenses_transactions table
            foreach ($transactions as $transaction) {
                DB::table('inventory_transactions')->insert([
                    'e_id' => $eTransactionId,
                    'inv_id' => $transaction->inv_id,
                    'type' => $transaction->type,
                    'category_id' => $transaction->category_id,
                    'supplier_id' => $transaction->supplier_id,
                    'quantity_before' => $transaction->quantity_before,
                    'quantity' => $transaction->quantity,
                    'quantity_after' => $transaction->quantity_after,
                    'amount' => $transaction->amount,
                    'remarks' => $transaction->remarks,
                    'unit' => $transaction->unit,
                    'unit_price' => $transaction->unit_price,
                    'exp_type' => $transaction->exp_type,
                    'other_remarks' => $transaction->other_remarks,
                    'user_id' => $transaction->user_id,
                    'is_paid' => $transaction->is_paid,
                    // Safety net: even if a cart row slipped through unpaid,
                    // a Bank Transfer line item is always saved as Paid.
                    'payment_class' => $this->isBankTransfer($transaction->is_paid) ? 1 : $transaction->payment_class,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                    'store_code' => Auth::user()->store_code,
                    'ap_payment' => "",
                    'department_id' => $transaction->department_id,
                ]);
            }

            // Delete all data from the inventory_cart_transactions table after the transfer
            DB::table('inventory_cart_transactions')->where('user_id', auth()->id())->delete();

            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Data transferred successfully.']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    public function cart_delete(Request $request){
        $slug = $request->slug;
        DB::table('inventory_cart_transactions')->where('id', $slug)->delete();
        
        return response()->json(['success' => true, 'message' => 'Data deleted successfully.']);
    }
    
    


    public function edit($id){
        $item = InventoryTransaction::with('ExpensesCategory','supplier')->where('id', $id)->first();
        
        $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        return view('user.inventory.expenses.edit', compact('item','expcats', 'supps'));
    }

    public function update(Request $request){
        $id = $request->id;
        $name = $request->name;
        $status = $request->status;
        //$type_txt = $request->type_txt;
        //$type = $request->type;


        $categoryArray = explode(",", $request->category);
        $is_cat_name = $categoryArray[0];
        $is_cat_id = $categoryArray[1];

        $SupplierArray = explode(",", $request->supplier);
        $is_supp_name = $SupplierArray[0];
        $is_supp_id = $SupplierArray[1];

        // if (strtoupper($type) != 'OTHERS'){
        //    $type_txt = '';
        // }
        if(Auth::user()->role == 1){
            $dateAdd = $request->dateAdd.' '.date('H:i:s');
        }else{
            $dateAdd = date('Y/m/d H:i:s');
        }
        $quantity = str_replace(',', '', $request->quantity);
        $price = str_replace(',', '', $request->price);

        $request->validate([
            'name' => 'required',
            'quantity' => 'required',
            'price' => 'required',
        ]);
        DB::table('inventory_transactions')->where('id', $id)
            ->update([
                'remarks' => $name,
                'quantity' => $quantity,
                'amount' => $price,
                'type' => $is_cat_name,
                'category_id' => $is_cat_id,
                'supplier_id' => $is_supp_id,
                'is_paid' => $status,
                'created_at' => date('Y/m/d H:i:s', strtotime($dateAdd))
            ]);

        return redirect()->route('expenses.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($id){
        DB::table('inventory_transactions')->where('id', $id)->delete();

        return redirect()->route('expenses.index')->withInput()->with('message', 'Successfully Deleted');
    }

    public function addqty(Request $request){
        $slug = $request->addSlug;
        $quantity = 1;
        $price = $request->price;
        $dateAdd = $request->dateAdd;
        $inv = DB::table('inventories')->where('slug', $slug)->first();
        $qb = $inv->quantity;
        $qf = $qb + $quantity;

        DB::table('inventories')->where('slug', $slug)->update([
            'quantity' => $qf,
            'price' => $price,
        ]);

        $it = new InventoryTransaction();
        $it->inv_id = $inv->id;
        $it->type = 'INCOMING';
        $it->quantity_before = $qb;
        $it->quantity = $quantity;
        $it->quantity_after = $qf;
        $it->amount = $price;
        $it->remarks = 'N/A';
        $it->user_id = Auth::id();
        $it->created_at = $dateAdd;
        $it->save();

        return redirect()->route('expenses.index')->withInput()->with('message', 'Quantity Successfully Increased');
    }

    // public function minusqty(Request $request){
    //     $slug = $request->minSlug;
    //     $quantity = $request->minQuantity;
    //     $remarks = $request->remarks;
    //     $dateMinus = $request->dateMinus;
    //     $inv = DB::table('inventories')->where('slug', $slug)->first();
    //     $qb = $inv->quantity;
    //     $qf = $qb - $quantity;

    //     if($qf < 0){
    //         return redirect()->route('inventory.index')->withInput()->with('error', 'Please Enter a valid Quantity.');
    //     }else{
    //         DB::table('inventories')->where('slug', $slug)->update([
    //             'quantity' => $qf,
    //         ]);
    
    //         $it = new InventoryTransaction();
    //         $it->inv_id = $inv->id;
    //         $it->type = 'OUTGOING';
    //         $it->quantity_before = $qb;
    //         $it->quantity = $quantity;
    //         $it->quantity_after = $qf;
    //         $it->remarks = $remarks;
    //         $it->user_id = Auth::id();
    //         $it->created_at = $dateMinus;
    //         $it->save();
    
    //         return redirect()->route('inventory.index')->withInput()->with('message', 'Quantity Successfully Decreased');
    //     }
    // }



    public function updateTaxValues(Request $request)
    {
        $request->validate([
            'total' => 'required|numeric|min:0',
            'vatChecked' => 'nullable|boolean',
            'taxChecked' => 'nullable|boolean',
            'wtaxPercent' => 'nullable|numeric|min:0',
        ]);

        $total = (float) $request->input('total', 0);
        $vatChecked = $request->boolean('vatChecked');
        $taxChecked = $request->boolean('taxChecked');
        $wtaxPercent = (float) $request->input('wtaxPercent', 0);

        // BUG FIX (Withholding Tax Base): Net Sales is the VAT-exclusive
        // base used for every downstream tax computation. When VAT is not
        // toggled on for this transaction, the entered total already IS
        // the net base — there's nothing to strip out.
        $netSales = $vatChecked ? ($total / 1.12) : $total;

        // 12% VAT = Gross (VAT-inclusive) Total - Net Sales.
        $vatAmount = $vatChecked ? ($total - $netSales) : 0.0;

        // BUG FIX: Withholding Tax must be computed on Net Sales
        // (VAT-exclusive), never on the gross VAT-inclusive total. The old
        // code used `$total * ($wtaxPercent / 100)`, which over-withholds
        // whenever VAT is also applied.
        $wtaxAmount = ($taxChecked && $wtaxPercent > 0)
            ? ($netSales * ($wtaxPercent / 100))
            : 0.0;

        // Total Amount Due = VAT-inclusive Total - Withholding Tax.
        // VAT itself is not subtracted again here — it's already baked
        // into $total; it's only broken out for display/reporting.
        $totalWithTax = $total - $wtaxAmount;

        // BUG FIX (Precision): round every monetary value to 2 decimal
        // places server-side (standard "round half up", matching BIR
        // centavo rounding) before it ever reaches the frontend, instead
        // of returning raw unrounded floats like 557.142857...
        return response()->json([
            'total' => round($total, 2),
            'netSales' => round($netSales, 2),
            'vatAmount' => round($vatAmount, 2),
            'wtaxAmount' => round($wtaxAmount, 2),
            'totalWithTax' => round($totalWithTax, 2),
        ]);
    }

   // Controller method to fetch inventory transaction by e_id
   public function getInventoryTransaction($e_id)
   {
       // Get all transactions for the given e_id
       $transactions = InventoryTransaction::where('e_id', $e_id)->get();
   
       // Create an array to hold the transaction data
       $transactionData = [];
   
       // Loop through all transactions and format the data
       foreach ($transactions as $transaction) {
           $transactionData[] = [
               'exp_type' => $transaction->exp_type,
               'quantity' => $transaction->quantity,
               'unit' => $transaction->unit,
               'remarks' => $transaction->remarks,
               'unit_price' => $transaction->unit_price,
               'amount' => $transaction->amount,
               'category_name' => $transaction->ExpensesCategory->name ?? 'N/A',
               'supplier_name' => $transaction->supplier->name ?? 'N/A',
               
               'payment_status' => $transaction->payment_class,
           ];
       }
   
       // Return all transactions data in a JSON response
       return response()->json($transactionData);
   }
   
    

   public function edit2($id,Request $request){

 // Get the form type from the query parameter; default to 'cost'
                $formType = $request->query('cost');

                // Fetch expense categories
                $expcats = DB::table('expenses_categories')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

                // Fetch suppliers
                $supps = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
                // $supps1 = DB::table('suppliers')->orderBy('id', 'asc')->where('is_deleted', 0)->get();

                // Fetch today's inventory transactions
                $today = Carbon::today();
                $inventories = DB::table('inventory_transactions')
                ->orderBy('id', 'asc')
                ->where('e_id', $id)
                ->get();



                $inventoriesCount =  DB::table('inventory_transactions')
                ->orderBy('id', 'asc')
                ->where('e_id', $id)
                ->count();

                $etax = DB::table('inventory_etransaction')
                ->where('id', $id)
                ->get();

                // Deletion Approval guard: block loading the edit page at all
                // for a transaction that's pending deletion (1) or already
                // deleted (2) — mirrors the write-side check in updateEdit2().
                $etaxHeader = $etax->first();
                if ($etaxHeader && $etaxHeader->is_deleted != 0) {
                    return redirect()->route('e_request.index')
                        ->with('error', 'This transaction is pending deletion and cannot be edited.');
                }
               
                return view('user.inventory.expenses.edit2', compact('etax','expcats', 'supps','inventories', 'inventoriesCount', 'formType'));
                    

                }
                public function store2(Request $request) {
                    // Retrieving and cleaning the request data
                    $edit_id = $request->edit_id;
                    $name = $request->name;
                    $other_name = $request->other_name;
                    $status = $request->status;
                    // Bank Transfer is always Paid, regardless of what the form sent.
                    $payment_class = $this->isBankTransfer($status) ? 1 : $request->payment_class;
                    $unit = $request->unit;
                    $exp_type = $request->exp_type;
                    $unit_price = str_replace(',', '', $request->unit_price);
                    $quantity = str_replace(',', '', $request->quantity);
                    $price = str_replace(',', '', $request->price);
                
                    // Category processing
                    $categoryArray = explode(",", $request->category);
                    $is_cat_name = $categoryArray[0];
                    $is_cat_id = $categoryArray[1];
                
                    // Supplier processing
                    $SupplierArray = explode(",", $request->supplier);
                    $is_supp_name = $SupplierArray[0];
                    $is_supp_id = $SupplierArray[1];
                
                    // Validation
                    $request->validate([
                        'name' => 'required',
                        'quantity' => 'required',
                        'price' => 'required'
                    ]);
                
                    // Directly inserting into the inventory_transactions table
                    $item = DB::table('inventory_transactions')->insert([
                        'e_id' => $edit_id,
                        'inv_id' => 0, // You can adjust this according to your requirement
                        'type' => strtoupper($is_cat_name),
                        'category_id' => $is_cat_id,
                        'supplier_id' => $is_supp_id,
                        'quantity_before' => 0,
                        'quantity' => $quantity,
                        'quantity_after' => 0,
                        'amount' => $price,
                        'remarks' => $name,
                        'unit' => $unit,
                        'unit_price' => $unit_price,
                        'other_remarks' => $other_name,
                        'is_paid' => $status,
                        'payment_class' => $payment_class,
                        'exp_type' => $exp_type,
                        'user_id' => Auth::user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'ap_payment' => "" // You can leave this empty or update as required
                    ]);
                
                    // Fetch the latest inserted records
                    $inventories = DB::table('inventory_transactions')->latest()->get();
                
                    // Return the latest inventory transactions as JSON
                    return response()->json(['inventories' => $inventories]);
                }
                
               
                


                public function cart_store2(Request $request) {
                    // Validate incoming data
                    $validated = $request->validate([
                        'e_edit_id' => 'required|numeric',
                        'e_total' => 'required|numeric',
                        'e_vat' => 'required|numeric',
                        'e_wtaxpercent' => 'required|numeric',
                        'e_wtax' => 'required|numeric',
                        'e_amount' => 'required|numeric',
                    ]);
                
                    // Get validated data
                    $edit_id = $validated['e_edit_id'];
                    $eTotal = $validated['e_total'];
                    $eVat = $validated['e_vat'];
                    $eWtaxperncentage = $validated['e_wtaxpercent'];
                    $eWtax = $validated['e_wtax'];
                    $eAmount = $validated['e_amount'];
                    
                    // Start the transaction process
                    DB::beginTransaction();
                
                    try {
                        // Update the existing record in inventory_etransaction
                        $updated = DB::table('inventory_etransaction')
                            ->where('id', $edit_id)
                            ->update([
                                'e_total' => $eTotal,
                                'e_vat' => $eVat,
                                'e_wtaxpercent' => $eWtaxperncentage,
                                'e_wtax' => $eWtax,
                                'e_amount' => $eAmount,
                                'updated_at' => now(), // Update timestamp
                            ]);
                
                        // Check if the update was successful
                        if ($updated) {
                            DB::commit();
                            return response()->json(['success' => true, 'message' => 'Record updated successfully.']);
                        } else {
                            DB::rollBack();
                            return response()->json(['success' => false, 'message' => 'Failed to update the record.']);
                        }
                    } catch (\Exception $e) {
                        // Rollback transaction if an error occurs
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
                    }
                }

              
public function editCart($id)
{
    $inventory = InventoryCartTransaction::find($id);

    // Fetch expense categories
    $expcats = DB::table('expenses_categories')
                 ->orderBy('id', 'asc')
                 ->where('is_deleted', 0)
                 ->get();

    // Check if the item exists
    if (!$inventory) {
        return response()->json(['error' => 'Inventory not found'], 404);
    }

    // Transform the exp_type value
    $inventory->exp_type = $inventory->exp_type == 0 ? 'Cost of Sales' : 'Other Expenses';

    // Match the category_id with the category name
    $selectedCategory = $expcats->firstWhere('id', $inventory->category_id);
    $inventory->category_name = $selectedCategory ? $selectedCategory->name : 'Unknown Category';

    // Fetch suppliers
    $suppliers = DB::table('suppliers')
                   ->orderBy('id', 'asc')
                   ->where('is_deleted', 0)
                   ->get();

    // Transform the payment_class value
    $inventory->payment_class = $inventory->payment_class == 0 ? 'Unpaid' : 'Paid';

    // Return the modified inventory data along with categories and suppliers
    return response()->json([
        'inventory' => $inventory,
        'expenses_categories' => $expcats,
        'suppliers' => $suppliers
    ]);
}

public function updateEdit(Request $request, $inventoryId)
{
    // Validate incoming data
    $validated = $request->validate([
        'exp_type' => 'required|in:0,1',
        'supplier_id' => 'required|exists:suppliers,id',
        'category_id' => 'required|exists:expenses_categories,id',
        'name' => 'required|string|max:255',
        'unit' => 'required|string|max:50',
        'quantity' => 'required|numeric|min:1',
        'unit_price' => 'required|numeric|min:0',
        'amount' => 'required|numeric|min:0',
        'payment_class' => 'required|in:0,1',
    ]);

    // This endpoint doesn't change is_paid, so look at the row's stored
    // Mode of Payment: a Bank Transfer line item always stays Paid.
    $cartItem = DB::table('inventory_cart_transactions')->where('id', $inventoryId)->first();
    $paymentClass = ($cartItem && $this->isBankTransfer($cartItem->is_paid))
        ? 1
        : $validated['payment_class'];

    // Update the database record
    $updated = DB::table('inventory_cart_transactions')
        ->where('id', $inventoryId)
        ->update([
            'exp_type' => $validated['exp_type'],
            'supplier_id' => $validated['supplier_id'],
            'category_id' => $validated['category_id'],
            'remarks' => $validated['name'],
            'unit' => $validated['unit'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'amount' => $validated['amount'],
            'payment_class' => $paymentClass,
        ]);

    if ($updated) {
        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully'
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Failed to update inventory']);
    }
}


public function updateEdit2(Request $request, $inventoryId)
{
    // FIX (item 6): line-item edits after the parent transaction has been
    // approved (e_approval = '2') were previously allowed, silently
    // drifting inventory_transactions out of sync with the frozen
    // e_total/e_amount snapshot already written to expenses_journal.
    // Product decision: lock edits once approved, rather than re-syncing
    // the journal row on save.
    $lineItem = DB::table('inventory_transactions')->where('id', $inventoryId)->first();

    if (!$lineItem) {
        return response()->json(['success' => false, 'message' => 'Inventory not found'], 404);
    }

    $parent = DB::table('inventory_etransaction')->where('id', $lineItem->e_id)->first();

    if ($parent && $parent->is_deleted != 0) {
        return response()->json([
            'success' => false,
            'message' => 'This transaction has already been approved and can no longer be edited.',
        ], 422);
    }

    // Validate incoming data
    $validated = $request->validate([
        'exp_type' => 'required|in:0,1',
        'supplier_id' => 'required|exists:suppliers,id',
        'category_id' => 'required|exists:expenses_categories,id',
        'name' => 'required|string|max:255',
        'unit' => 'required|string|max:50',
        'quantity' => 'required|numeric|min:1',
        'unit_price' => 'required|numeric|min:0',
        'amount' => 'required|numeric|min:0',
        'payment_class' => 'required|in:0,1',
    ]);

    // Update the database record
    $updated = DB::table('inventory_transactions')
        ->where('id', $inventoryId)
        ->update([
            'exp_type' => $validated['exp_type'],
            'supplier_id' => $validated['supplier_id'],
            'category_id' => $validated['category_id'],
            'remarks' => $validated['name'],
            'unit' => $validated['unit'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'amount' => $validated['amount'],
            // Bank Transfer line items always stay Paid.
            'payment_class' => $this->isBankTransfer($lineItem->is_paid) ? 1 : $validated['payment_class'],
        ]);

    if ($updated) {
        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully'
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Failed to update inventory']);
    }
}


public function tran_remove(Request $request)
{
    $slug = $request->slug;

    // A reason is mandatory before a deletion request can be submitted —
    // the accountant reviewing the queue needs it to make an approve/reject
    // decision.
    $request->validate([
        'reason' => ['required', 'string', 'min:5', 'max:2000'],
    ], [
        'reason.required' => 'Please provide a reason for this deletion request.',
    ]);

    $transaction = DB::table('inventory_etransaction')->where('id', $slug)->first();

    if (!$transaction) {
        return response()->json(['success' => false, 'message' => 'Transaction not found.']);
    }

    // Defense in depth: only Waiting-for-Approval rows can be sent for
    // deletion. Approved (e_approval == 2) rows can never be deleted, and
    // the Delete button won't even render for them in the view — but we
    // still guard here in case of a stale page or a direct request.
    if ($transaction->e_approval != 1) {
        return response()->json(['success' => false, 'message' => 'This transaction can no longer be deleted.']);
    }

    // Already pending or already deleted — nothing to do.
    if ($transaction->is_deleted != 0) {
        return response()->json(['success' => false, 'message' => 'This transaction has already been submitted for deletion.']);
    }

    // Write the deletion request to the transaction itself. No supporting
    // files are collected — the reason is the only thing the reviewer
    // needs to make a decision.
    DB::table('inventory_etransaction')
        ->where('id', $slug)
        ->update([
            'is_deleted'              => 1, // pending deletion, not final
            'deletion_requested_by'   => Auth::id(),
            'deletion_requested_at'   => now(),
            'deletion_reason'         => $request->input('reason'),
            // Clear out any comment from a prior rejected attempt so the
            // General Expenses page doesn't keep showing a stale reason
            // for a brand-new request.
            'deletion_reject_comment' => null,
            'updated_at'              => now(),
        ]);

    $log = new Log();
    $log->trans_id = $slug;
    $log->classification = 'Expense Transaction';
    $log->info = 'Deletion Requested: ' . $request->input('reason');
    $log->user_id = Auth::id();
    $log->event = 'Deletion Request';
    $log->save();

    return response()->json(['success' => true, 'message' => 'Deletion request submitted for approval.']);
}


public function cart_delete2(Request $request){
    $slug = $request->slug;
    DB::table('inventory_transactions')->where('id', $slug)->delete();
    
    return response()->json(['success' => true, 'message' => 'Data deleted successfully.']);
}
                
public function editCart2($id)
{
    $inventory = InventoryTransaction::find($id);

    // Fetch expense categories
    $expcats = DB::table('expenses_categories')
                 ->orderBy('id', 'asc')
                 ->where('is_deleted', 0)
                 ->get();

    // Check if the item exists
    if (!$inventory) {
        return response()->json(['error' => 'Inventory not found'], 404);
    }

    // Transform the exp_type value
    $inventory->exp_type = $inventory->exp_type == 0 ? 'Cost of Sales' : 'Other Expenses';

    // Match the category_id with the category name
    $selectedCategory = $expcats->firstWhere('id', $inventory->category_id);
    $inventory->category_name = $selectedCategory ? $selectedCategory->name : 'Unknown Category';

    // Fetch suppliers
    $suppliers = DB::table('suppliers')
                   ->orderBy('id', 'asc')
                   ->where('is_deleted', 0)
                   ->get();

    // Transform the payment_class value
    $inventory->payment_class = $inventory->payment_class == 0 ? 'Unpaid' : 'Paid';

    // Return the modified inventory data along with categories and suppliers
    return response()->json([
        'inventory' => $inventory,
        'expenses_categories' => $expcats,
        'suppliers' => $suppliers
    ]);
}




}