<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderedController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiverReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WasteController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ExpensesCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ArTransactionController;
use App\Http\Controllers\ArLogsTransactionController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\MasterlistController;
use App\Http\Controllers\RevolvingController;
use App\Http\Controllers\ERequestController;
use App\Http\Controllers\PortionController;
use App\Http\Controllers\TableCategoryController;
use App\Http\Controllers\BranchTransferController;
use App\Http\Controllers\TransferProcurementController;  // 4-6 harold
use App\Http\Controllers\ProcurementRequestController;   // 4-6 harold
use App\Http\Controllers\OnlineBankController;  // ken added 7-3-26
use App\Http\Controllers\TermsController; // leo added 7-3-25
use App\Http\Controllers\SupplierAccreditationController; // leo added 7-3-25
use App\Http\Controllers\SupplierMasterlistController; // leo added 7-3-25
use App\Http\Controllers\SupplierMopController; // leo added 7-8-25
use App\Http\Controllers\SupplierRequirementController;
use App\Http\Controllers\PendingItemsController;
use App\Http\Controllers\ItemTableController;
use App\Http\Controllers\ItemLogsController;
use App\Http\Controllers\DeleteItemTableController;
use App\Http\Controllers\SupplierBiddingController;
use App\Http\Controllers\ItemBrandController;
use App\Http\Controllers\ProcurementRequestBiddingController;
use App\Http\Controllers\OrderingEventsController;
use App\Http\Controllers\ReceivingEventsController;
use App\Http\Controllers\ReturnBottleController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\MopController; // ken added 03-29-2026
use App\Http\Controllers\TransformController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\AddStockController;
use App\Http\Controllers\SalesJournalController;
use App\Http\Controllers\SalesReportController; // ken added 8-16-2026
use App\Http\Controllers\CustomerBalanceController; // ken added 8-16-2026
use App\Http\Controllers\CashBreakdownController; // LINE 60: ken added 9-3-2026
use App\Http\Controllers\SawdustController; // LINE 61: ken added new controller 9-30-2026
use App\Http\Controllers\CashTurnoverApprovalController; // LINE 62: ken added new controller 9-30-2026
use App\Http\Controllers\ExpensesJournalController; // LINE 63: ken added new controller 9-30-2026
use App\Http\Controllers\DeletionApprovalController;
use App\Http\Controllers\IncomeStatementController; // LINE 61: KENADDED 9-10-2026
use App\Http\Controllers\ChartOfAccountsController; //LINE 63: NEW ADDED BY KEN 07-29-2026
use App\Http\Controllers\AccountingReportController; // LINE 64: NEW ADDED BY KEN 07-29-2026
use App\Http\Controllers\TrialBalanceController; // LINE 68: NEW ADDED BY KEN 07-29-2026
use App\Http\Controllers\SaveSaleReportController; // LINE 69: NEW ADDED BY KEN 10-22-2026
use App\Http\Controllers\PaymentCollectController; // LINE 70: NEW ADDED BY KEN 10-29-2026

use App\Http\Middleware\ValidateRole;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if(!auth()->user()){
        $settings = DB::table('settings')->where('id', 1)->first();
        return view('auth.login', compact('settings'));
    }else{
        if(auth()->user()->role == '1'){
            return redirect()->route('dashboard');
        } else if(auth()->user()->role == '2'){
            return redirect()->route('pos.index');
        } else if(auth()->user()->role == '3' ){
            return redirect()->route('inventory.index');
        } else if(auth()->user()->role == '4' ){
            return redirect()->route('inventory.index');
        } else if(auth()->user()->role == '5' ){
            return redirect()->route('dashboard');
        } else if(auth()->user()->role == '6' ){
            return redirect()->route('pos.index');
        } else if(auth()->user()->role == '7' ){
            return redirect()->route('pos.index');
        } else if(auth()->user()->role == '8' ){
            return redirect()->route('dashboard');
        } else if(auth()->user()->role == '9' ){
            return redirect()->route('request.index');
        } /* else if(auth()->user()->role == '5' ){
            return redirect()->route('inventory.index');
        } */
    }
});

Route::middleware("role:1,2,3,4,5,6,8,9")->group(function(){
    // TRANSFER API
    Route::get('/breq/branchIndex', [BranchTransferController::class, 'branchRequest'])->name('breq.branch-index');
    Route::post('/request/branchSubmit', [BranchTransferController::class, 'branchSubmit'])->name('request.branchSubmit');
    Route::post('/request/apiTransApproved', [BranchTransferController::class, 'apiTransApproved'])->name(name: 'request.apiTransApproved');
    Route::post('/request/apiTransReceived', [BranchTransferController::class, 'apiTransReceived'])->name(name: 'request.apiTransReceived');
    Route::get('/ttrans/apitransferIndex', [BranchTransferController::class, 'pamahawApiTransfer'])->name('transfer.transfer-index');
    Route::get('/ttrans/ApiTransferEdit/{slug}/{code}', [BranchTransferController::class, 'pamahawApiEdit'])->name('transfer.transfer-edit');
    Route::get('/bapproved/transferApprovedIndex', [BranchTransferController::class, 'transferApprovedIndex'])->name('transfer.transfer-approved');
    Route::get('/ttrans/ApiTransferReceived/{slug}', [BranchTransferController::class, 'pamahawApiReceived'])->name('transfer.transfer-received');
    Route::get('/ApiReceivedIndex', [BranchTransferController::class, 'ApiReceivedIndex'])->name('transfer.transfer-received_index');
    Route::get('/ttrans/ApiTransferReceivedReview/{slug}', [BranchTransferController::class, 'ApiTransferReceivedReview'])->name('transfer.transfer-received_view');
    // new added
    Route::get('/ApiCrossReceivedIndexView', [BranchTransferController::class, 'ApiCrossReceivedIndexView'])->name('transfer.transfer-cross-received_index');
    Route::get('/ApiCrossReceivedView/{slug}', [BranchTransferController::class, 'ApiCrossReceivedView'])->name('transfer.ApiCrossReceivedView');
    Route::post('/ApiCrossReceived', [BranchTransferController::class, 'ApiCrossReceived'])->name(name: 'request.ApiCrossReceived');
    Route::get('/ApiReceivedIndex', [BranchTransferController::class, 'ApiReceivedIndex'])->name('transfer.ApiReceivedIndex');
    Route::get('/ApiReceivedIndexView/{slug}', [BranchTransferController::class, 'ApiReceivedIndexView'])->name('transfer.ApiReceivedIndexView');
    Route::post('/ApiForecast', [BranchTransferController::class, 'ApiForecast'])->name('request.ApiForecast');
    Route::post('/ApiAppqty', [BranchTransferController::class, 'appqty'])->name(name: 'ApiAppqty.appqty');
    Route::post('/Apirecqty', [BranchTransferController::class, 'recqty'])->name(name: 'Apirecqty.recqty');

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/change-timeframe', [DashboardController::class, 'change'])->name('dashboard.change');

    // REPORTS
    Route::get('/reports', [ReportController::class, 'index'])->name('areport.index');
    Route::get('/report/trial-balance', [TrialBalanceController::class, 'index'])->name('report.trial_balance');  // LINE 140: NEW ADDED BY KEN 07-29-2026 trialbalance
    Route::get('/report/trial-balance/breakdown', [TrialBalanceController::class, 'getBreakdown'])->name('report.trial_balance.breakdown'); // LINE 141: NEW ADDED BY KEN 07-29-2026 trialbalance    
    Route::post('/report/generate', [ReportController::class, 'generate'])->name('areport.generate');
    Route::get('/report/print/{start}/{end}/{category}/{report}', [ReportController::class, 'centralizedPrint'])->name('areport.print');   
    Route::post('/report/generate/pay-expenses', [ReportController::class, 'payExpenses'])->name('payExpenses');
    Route::post('/report/generate/update-expenses', [ReportController::class, 'updateExpenses'])->name('updateExpenses');
    Route::post('/report/generate/payment-expenses', [ReportController::class, 'paymentExpenses'])->name('paymentExpenses');
    Route::post('/report/generate/delete-expenses', [ReportController::class, 'deleteExpenses'])->name('deleteExpenses');
    Route::post('/report/generate/update-sales', [ReportController::class, 'updateSales'])->name('updateSales');
    Route::post('/report/generate/delete-sales', [ReportController::class, 'deleteSales'])->name('deleteSales');
    Route::get('/report/printzreading/{start}/{end}/{report}', [ReportController::class, 'printzreading']);
    //Route::get('/report/print/{start}/{end}/{category}/{report}', [ReportController::class, 'print']);
    
    Route::get('/pricing-report', [ReportController::class, 'pricingReport'])->name('pricingReport');
    Route::post('/view-pricing-report', [ReportController::class, 'viewPricingReport'])->name('viewPricingReport');
    
    Route::get('/financial-report', [ReportController::class, 'financialReport'])->name('financialReport');
    Route::post('/financial-report', [ReportController::class, 'generateFinancialReport'])->name('generateFinancialReport');
    Route::post('/get-actual', [ReportController::class, 'getActual'])->name('getActual');
    Route::post('/update-actual', [ReportController::class, 'updateActual'])->name('updateActual');
    // LINE 162: START OF INCOME STATEMENT REPORT ROUTES added by KEN 9-10-2026
    Route::get('/reports/income-statement',  [IncomeStatementController::class, 'index'])->name('reports.income-statement');
    Route::post('/reports/income-statement', [IncomeStatementController::class, 'index'])->name('reports.income-statement.generate');
    // LINE 165: END OF INCOME STATEMENT REPORT ROUTES added by KEN 9-10-2026
    // USERS
    Route::get('/system-management/user', [UserController::class, 'index'])->name('user.index');
    Route::get('/system-management/user/add', [UserController::class, 'add'])->name('user.add');
    Route::post('/system-management/user/store', [UserController::class, 'store'])->name('user.store');
    Route::post('/system-management/user/view', [UserController::class, 'view'])->name('user.view'); //leo added
    Route::get('/system-management/user/detail/{slug}', [UserController::class, 'detail']); // leo added 7-3-25
    Route::post('/system-management/user/detail_update', [UserController::class, 'detail_update'])->name('user.detail_update'); // leo added 7-3-25
    Route::get('/system-management/user/requirement/{slug}', [UserController::class, 'requirement']); // leo added 7-3-25
    Route::post('/system-management/user/requirement_update', [UserController::class, 'requirement_update'])->name('user.requirement_update'); // leo added 7-3-25
    Route::get('/system-management/user/item/{slug}', [UserController::class, 'item']); // leo added 7-3-25
    Route::get('/user/item/add', [ItemTableController::class, 'add'])->name('item.add'); //leo added
    Route::post('/system-management/user/itemAdding', [UserController::class, 'itemAdding'])->name('user.itemAdding'); //leo added
    Route::post('/system-management/user/itemCheck', [UserController::class, 'getCheckedItems'])->name('user.itemCheck'); //leo added
    Route::post('/system-management/user/storeTemp', [UserController::class, 'storeTemp'])->name('item.storeTemp'); //leo added
    Route::get('/system-management/user/edit/{slug}', [UserController::class, 'edit']);
    Route::post('/system-management/user/update', [UserController::class, 'update'])->name('user.update');
    Route::get('/system-management/user/delete/{slug}', [UserController::class, 'delete'])->name('user.delete');
    Route::get('/system-management/user/{page}', [UserController::class, 'paginate']);
    Route::get('/system-management/user/{page}/{search}', [UserController::class, 'search']);
    Route::get('/system-management/user/additem/{page}/{slug}', [UserController::class, 'itempaginate']); //leo added
    Route::get('/system-management/user/additem/{page}/{search}/{slug}', [UserController::class, 'itemsearch']); //leo added
    Route::get('/system-management/user/additem/category/search/{page}/{category}/{slug}', [UserController::class, 'searchcategory']); //leo added

    // TABLE
    Route::get('/system-management/table', [TableController::class, 'index'])->name('table.index');
    Route::get('/system-management/table/add', [TableController::class, 'add'])->name('table.add');
    Route::post('/system-management/table/store', [TableController::class, 'store'])->name('table.store');
    Route::get('/system-management/table/edit/{slug}', [TableController::class, 'edit']);
    Route::post('/system-management/table/update', [TableController::class, 'update'])->name('table.update');
    Route::get('/system-management/table/delete/{slug}', [TableController::class, 'delete'])->name('table.delete');
    Route::get('/system-management/table/{page}', [TableController::class, 'paginate']);
    Route::get('/system-management/table/{page}/{search}', [TableController::class, 'search']);
    
    
    // PORTION
    Route::get('/system-management/portion', [PortionController::class, 'index'])->name('portion.index');

    // SUPPLIER REQUIREMENTS     // leo added 7-9-25
    Route::get('/system-management/supplier-requirements', [SupplierRequirementController::class, 'index'])->name('supplier-requirements.index');
    Route::get('/system-management/supplier-requirements/add', [SupplierRequirementController::class, 'add'])->name('supplier-requirements.add');
    Route::post('/system-management/supplier-requirements/store', [SupplierRequirementController::class, 'store'])->name('supplier-requirements.store');
    Route::get('/system-management/supplier-requirements/edit/{slug}', [SupplierRequirementController::class, 'edit']);
    Route::post('/system-management/supplier-requirements/update', [SupplierRequirementController::class, 'update'])->name('supplier-requirements.update');
    Route::get('/system-management/supplier-requirements/delete/{slug}', [SupplierRequirementController::class, 'delete'])->name('supplier-requirementss.delete');
    Route::get('/system-management/supplier-requirements/{page}', [SupplierRequirementController::class, 'paginate']);
    Route::get('/system-management/supplier-requirements/{page}/{search}', [SupplierRequirementController::class, 'search']);
    
    // SUPPLIER
    Route::get('/system-management/supp', [SupplierController::class, 'index'])->name('supp.index');
    Route::get('/system-management/supp/add', [SupplierController::class, 'add'])->name('supp.add');
    Route::post('/system-management/supp/store', [SupplierController::class, 'store'])->name('supp.store');
    Route::get('/system-management/supp/edit/{slug}', [SupplierController::class, 'edit']);
    Route::post('/system-management/supp/update', [SupplierController::class, 'update'])->name('supp.update');
    Route::get('/system-management/supp/delete/{slug}', [SupplierController::class, 'delete'])->name('supp.delete');
    Route::get('/system-management/supp/{page}', [SupplierController::class, 'paginate']);
    Route::get('/system-management/supp/{page}/{search}', [SupplierController::class, 'search']);

    // Departments
    Route::get('/system-management/department', [DepartmentController::class, 'index'])->name('department.index');
    Route::get('/system-management/department/add', [DepartmentController::class, 'add'])->name('department.add');
    Route::post('/system-management/department/store', [DepartmentController::class, 'store'])->name('department.store');
    Route::get('/system-management/department/edit/{slug}', [DepartmentController::class, 'edit']);
    Route::post('/system-management/department/update', [DepartmentController::class, 'update'])->name('department.update');
    Route::get('/system-management/department/delete/{slug}', [DepartmentController::class, 'delete'])->name('department.delete');
    Route::get('/system-management/department/{page}', [DepartmentController::class, 'paginate']);
    Route::get('/system-management/department/{page}/{search}', [DepartmentController::class, 'search']);


    // Brand
    Route::get('/system-management/brand', [BrandController::class, 'index'])->name('brand.index');
    Route::get('/system-management/brand/add', [BrandController::class, 'add'])->name('brand.add');
    Route::post('/system-management/brand/store', [BrandController::class, 'store'])->name('brand.store');
    Route::get('/system-management/brand/edit/{slug}', [BrandController::class, 'edit']);
     Route::get('/system-management/brand/edit/{slug}', [BrandController::class, 'edit'])->name('brand.edit'); // ken added name for edit route 3-29-26
    Route::post('/system-management/brand/update', [BrandController::class, 'update'])->name('brand.update');
    Route::get('/system-management/brand/delete/{slug}', [BrandController::class, 'delete'])->name('brand.delete');
    Route::get('/system-management/brand/{page}', [BrandController::class, 'paginate']);
    Route::get('/system-management/brand/{page}/{search}', [BrandController::class, 'search']);
    
    
     // ken added start here for MODE OF PAYMENT (Transaction MOP) - 252306001 - 3-29-2026
    Route::get('/system-management/mode-of-payment', [MopController::class, 'index'])->name('mode-of-payment.index');
    Route::get('/system-management/mode-of-payment', [MopController::class, 'index'])->name('mode-of-payment.index');
    Route::get('/system-management/mode-of-payment/add', [MopController::class, 'add'])->name('mode-of-payment.add');
    Route::post('/system-management/mode-of-payment/store', [MopController::class, 'store'])->name('mode-of-payment.store');
    Route::get('/system-management/mode-of-payment/edit/{slug}', [MopController::class, 'edit'])->name('mode-of-payment.edit');
    Route::post('/system-management/mode-of-payment/update', [MopController::class, 'update'])->name('mode-of-payment.update');
    Route::get('/system-management/mode-of-payment/delete/{slug}', [MopController::class, 'delete'])->name('mode-of-payment.delete');
    Route::get('/system-management/mode-of-payment/{page}', [MopController::class, 'paginate'])->whereNumber('page');
    Route::get('/system-management/mode-of-payment/{page}/{search}', [MopController::class, 'search'])->whereNumber('page');
    //end here for MODE OF PAYMENT (Transaction MOP) - 252306001 - 3-29-2026

    
    // SUPPLIER TERMS     // leo added 7-3-25
    Route::get('/system-management/terms', [TermsController::class, 'index'])->name('terms.index');
    Route::get('/system-management/terms/add', [TermsController::class, 'add'])->name('terms.add');
    Route::post('/system-management/terms/store', [TermsController::class, 'store'])->name('terms.store');
    Route::get('/system-management/terms/edit/{slug}', [TermsController::class, 'edit']);
    Route::post('/system-management/terms/update', [TermsController::class, 'update'])->name('terms.update');
    Route::get('/system-management/terms/delete/{slug}', [TermsController::class, 'delete'])->name('terms.delete');
    Route::get('/system-management/terms/{page}', [TermsController::class, 'paginate']);
    Route::get('/system-management/terms/{page}/{search}', [TermsController::class, 'search']);
    
    // SUPPLIER ACCREDITATION // leo added
    Route::get('/supplier-accreditation', [SupplierAccreditationController::class, 'index'])->name('supplier.index');
    Route::post('/supplier-accreditation/view', [SupplierAccreditationController::class, 'view'])->name('user.view');
    Route::post('/supplier-accreditation/pamahaw-approval/{id}', [SupplierAccreditationController::class, 'pamahaw_approval'])->name('supplier.pamahaw_approval');
    Route::get('/supplier-accreditation/{page}', [SupplierAccreditationController::class, 'paginate']);
    Route::get('/supplier-accreditation/{page}/{search}', [SupplierAccreditationController::class, 'search']);
    
    //SUPPLIER MASTERLIST
    Route::get('/supplier-masterlist', [SupplierMasterlistController::class, 'index'])->name('supplier_masterlist.index');
    Route::post('/supplier-accreditation/view', [SupplierAccreditationController::class, 'view'])->name('supplier.view');
    Route::post('/supplier-masterlist/masterlist_status/{id}', [SupplierMasterlistController::class, 'masterlist_status'])->name('supplier_masterlist.masterlist_status');
    Route::get('/supplier-masterlist/delete/{slug}', [SupplierMasterlistController::class, 'delete'])->name('supplier_masterlist.delete');
    Route::get('/supplier-masterlist/{page}', [SupplierMasterlistController::class, 'paginate']);
    Route::get('/supplier-masterlist/{page}/{search}', [SupplierMasterlistController::class, 'search']);
    
    // SUPPLIER MOP   // leo added 7-8-25
    Route::get('/system-management/mop', [SupplierMopController::class, 'index'])->name('mop.index');
    Route::get('/system-management/mop/add', [SupplierMopController::class, 'add'])->name('mop.add');
    Route::post('/system-management/mop/store', [SupplierMopController::class, 'store'])->name('mop.store');
    Route::get('/system-management/mop/edit/{slug}', [SupplierMopController::class, 'edit']);
    Route::post('/system-management/mop/update', [SupplierMopController::class, 'update'])->name('mop.update');
    Route::get('/system-management/mop/delete/{slug}', [SupplierMopController::class, 'delete'])->name('mop.delete');
    Route::get('/system-management/mop/{page}', [SupplierMopController::class, 'paginate']);
    Route::get('/system-management/mop/{page}/{search}', [SupplierMopController::class, 'search']);
    
    // ORDERING EVENTS     
    Route::get('/system-management/ordering-events', [OrderingEventsController::class, 'index'])->name('ordering-events.index');
    Route::get('/system-management/ordering-events/add', [OrderingEventsController::class, 'add'])->name('ordering-events.add');
    Route::post('/system-management/ordering-events/store', [OrderingEventsController::class, 'store'])->name('ordering-events.store');
    Route::get('/system-management/ordering-events/edit/{slug}', [OrderingEventsController::class, 'edit']);
    Route::post('/system-management/ordering-events/update', [OrderingEventsController::class, 'update'])->name('ordering-events.update');
    Route::get('/system-management/ordering-events/delete/{slug}', [OrderingEventsController::class, 'delete'])->name('ordering-events.delete');
    Route::get('/system-management/ordering-events/{page}', [OrderingEventsController::class, 'paginate']);
    Route::get('/system-management/ordering-events/{page}/{search}', [OrderingEventsController::class, 'search']);

    // RECEIVING EVENTS     
    Route::get('/system-management/receiving-events', [ReceivingEventsController::class, 'index'])->name('receiving-events.index');
    Route::get('/system-management/receiving-events/add', [ReceivingEventsController::class, 'add'])->name('receiving-events.add');
    Route::post('/system-management/receiving-events/store', [ReceivingEventsController::class, 'store'])->name('receiving-events.store');
    Route::get('/system-management/receiving-events/edit/{slug}', [ReceivingEventsController::class, 'edit']);
    Route::post('/system-management/receiving-events/update', [ReceivingEventsController::class, 'update'])->name('receiving-events.update');
    Route::get('/system-management/receiving-events/delete/{slug}', [ReceivingEventsController::class, 'delete'])->name('receiving-events.delete');
    Route::get('/system-management/receiving-events/{page}', [ReceivingEventsController::class, 'paginate']);
    Route::get('/system-management/receiving-events/{page}/{search}', [ReceivingEventsController::class, 'search']);
    
    
    // ITEM LOGS
    Route::get('/item-logs', [ItemLogsController::class, 'index'])->name('item_logs.index');
    Route::get('/item-logs/{page}/{search}', [ItemLogsController::class, 'search']);
    Route::get('/item-logs/{page}', [ItemLogsController::class, 'paginate']);
    
    // PENDING ITEM
    Route::get('/pending-item', [PendingItemsController::class, 'index'])->name('pending_item.index');
    Route::post('/pending-item/view', [PendingItemsController::class, 'view'])->name('pending_item.view');
    Route::post('/pending-item/item-approval/{id}', [PendingItemsController::class, 'item_approval'])->name('pending_item.item_approval');
    Route::get('/prending-item/{page}/{search}', [PendingItemsController::class, 'search']);
    Route::get('/prending-item/{page}', [PendingItemsController::class, 'paginate']);

    // DELETE ITEM
    Route::get('/delete-item', [DeleteItemTableController::class, 'index'])->name('delete_item.index');
    Route::post('/delete-item/view', [DeleteItemTableController::class, 'view'])->name('delete_item.view');
    Route::post('/delete-item/item-approval/{id}', [DeleteItemTableController::class, 'item_approval'])->name('delete_item.item_approval');
    Route::get('/delete-item/{page}/{search}', [DeleteItemTableController::class, 'search']);
    Route::get('/delete-item/{page}', [DeleteItemTableController::class, 'paginate']);

    // ITEM TABLE
    Route::get('/item', [ItemTableController::class, 'index'])->name('item.index');
    Route::get('/item/add', [ItemTableController::class, 'add'])->name('item.add');
    Route::post('/item/store', [ItemTableController::class, 'store'])->name('item.store');
    Route::post('/item/view', [ItemTableController::class, 'view'])->name('item.view');
    Route::post('/item/item-approval/{id}', [ItemTableController::class, 'item_approval'])->name('item.item_approval');
    Route::get('/item/delete/{userId}', [ItemTableController::class, 'delete'])->name('item.delete');
    Route::get('/item/{page}/{search}', [ItemTableController::class, 'search']);
    Route::get('/item/{page}', [ItemTableController::class, 'paginate']);
    Route::get('/item/category/{page}/{category}', [ItemTableController::class, 'searchcategory']);
    
    // ITEM BRAND
    Route::get('/item-brand', [ItemBrandController::class, 'index'])->name('item_brand.index');
    Route::get('/item-brand/brands/add', [ItemBrandController::class, 'add'])->name('item_brand.add');
    Route::post('/item-brand/brands/store', [ItemBrandController::class, 'store'])->name('item_brand.store');
    Route::get('/item-brand/brands/edit/{slug}', [ItemBrandController::class, 'edit']);
    Route::post('/item-brand/brands/update', [ItemBrandController::class, 'update'])->name('item_brand.update');
    Route::get('/item-brand/brands/delete/{slug}', [ItemBrandController::class, 'delete'])->name('item_brand.delete');
    Route::get('/item-brand/brands/{page}', [ItemBrandController::class, 'paginate']);
    Route::get('/item-brand/brands/{page}/{search}', [ItemBrandController::class, 'search']);
    
    


    // TRANSFER
    Route::get('/transfer', [TransferController::class, 'index'])->name('transfer.index');
    Route::get('/Approved', [TransferController::class, 'approvedIndex'])->name('transfer.approvedIndex');
    Route::get('/transfer/add', [TransferController::class, 'add'])->name('transfer.add');
    Route::post('/transfer/store', [TransferController::class, 'store'])->name('transfer.store');
    Route::post('/transfer/approve', [TransferController::class, 'approve'])->name('transfer.approve');
    Route::post('/transfer/vat', [TransferController::class, 'vat'])->name('transfer.vat');
    Route::get('/transfer/edit/{slug}', [TransferController::class, 'edit']);
    Route::get('/transfer/received/{slug}', [TransferController::class, 'received']);
    Route::post('/transfer/update', [TransferController::class, 'update'])->name('transfer.update');
    Route::get('/transfer/delete/{slug}', [TransferController::class, 'delete'])->name('transfer.delete');
    Route::get('/transfer/{page}', [TransferController::class, 'paginate']);
    Route::get('/transfer/{page}/{search}', [TransferController::class, 'search']);
    Route::get('/Approved/{page}', [TransferController::class, 'approvedPaginate']);
    Route::get('/Approved/{page}/{search}', [TransferController::class, 'approvedSearch']);
    
    // PROCUREMENT TRANSFER  //
    // 4-6 harold
    Route::get('/procurementtransfer', [TransferProcurementController::class, 'index'])->name('procurementtransfer.index');          
    Route::get('/procurementtransfer/edit/{slug}', [TransferProcurementController::class, 'edit']);
    Route::get('/procurementApproved', [TransferProcurementController::class, 'approvedIndex'])->name('procurementtransfer.approvedIndex');
    Route::get('/procurementApproved/{page}/{search}', [TransferProcurementController::class, 'approvedSearch']);
    Route::get('/procurementtransfer/received/{slug}', [TransferProcurementController::class, 'received']);
    Route::get('/procurementmodal', [TransferProcurementController::class, 'showBySlug'])->name('procurementmodal');
    Route::get('/procurementreceived', [TransferProcurementController::class, 'receivedIndex'])->name('procurementreceived.receivedIndex');
    Route::get('/procurementreceived/receivedcompleted/{slug}', [TransferProcurementController::class, 'receivedcompleted']);
    Route::get('/procurementreceived/{page}/{search}', [TransferProcurementController::class, 'receivedSearch']);
    Route::post('/procurementreceive/appqtyreceived', [TransferProcurementController::class, 'appqtyreceived'])->name(name: 'procurementreceive.appqtyreceived');
    Route::get('/procurementtransfer/{page}/{search}', [TransferProcurementController::class, 'search']);

    // Procurement  Request
    Route::get('/procurementrequest', [ProcurementRequestController::class, 'index'])->name('procurementrequest.index');
    Route::post('/procurementrequest/forecast', [ProcurementRequestController::class, 'forecast'])->name('procurementrequest.forecast');
    Route::post('/procurementrequest/submit', [ProcurementRequestController::class, 'submit'])->name('procurementrequest.submit');
    Route::post('/procurementrequest/approved', [ProcurementRequestController::class, 'approved'])->name(name: 'procurementrequest.approved');
    Route::post('/procurementrequest/received', [ProcurementRequestController::class, 'received'])->name(name: 'procurementrequest.received');
    Route::post('/procurementrequest/appqty', [ProcurementRequestController::class, 'appqty'])->name(name: 'procurementrequest.appqty');
    Route::post('/procurementrequest/recqty', [ProcurementRequestController::class, 'recqty'])->name(name: 'procurementrequest.recqty');
    Route::post('/procurementrequest/delete', [ProcurementRequestController::class, 'delete'])->name(name: 'procurementrequest.delete');
    Route::post('/procurementrequest/resetAll', [ProcurementRequestController::class, 'resetAll'])->name('procurementrequest.resetAll');
    Route::post('/procurementrequest/procurementadd', [ProcurementRequestController::class, 'procurementadd'])->name('procurementrequest.add');
    // 4-6 harold END
    
    // procurement bidding
    Route::get('/procurementrequest', [ProcurementRequestBiddingController::class, 'index'])->name('procurementrequest.index');
    Route::post('/procurementrequest/forecast', [ProcurementRequestBiddingController::class, 'forecast'])->name('procurementrequest.forecast');
    Route::post('/procurementrequest/submit', [ProcurementRequestBiddingController::class, 'submit'])->name('procurementrequest.submit');
    Route::post('/procurementrequest/approved', [ProcurementRequestBiddingController::class, 'approved'])->name(name: 'procurementrequest.approved');
    Route::post('/procurementrequest/received', [ProcurementRequestBiddingController::class, 'received'])->name(name: 'procurementrequest.received');
    Route::post('/procurementrequest/appqty', [ProcurementRequestBiddingController::class, 'appqty'])->name(name: 'procurementrequest.appqty');
    Route::post('/procurementrequest/recqty', [ProcurementRequestBiddingController::class, 'recqty'])->name(name: 'procurementrequest.recqty');
    Route::post('/procurementrequest/delete', [ProcurementRequestBiddingController::class, 'delete'])->name(name: 'procurementrequest.delete');
    Route::post('/procurementrequest/resetAll', [ProcurementRequestBiddingController::class, 'resetAll'])->name('procurementrequest.resetAll');
    Route::post('/procurementrequest/procurementadd', [ProcurementRequestBiddingController::class, 'procurementadd'])->name('procurementrequest.add');
    Route::post('/procurementbidding/price', [ProcurementRequestBiddingController::class, 'price'])->name('procurementbidding.price'); // leo added
    Route::post('/procurementrequest/orders', [ProcurementRequestBiddingController::class, 'orders'])->name(name: 'procurementrequest.orders'); // leo added
    Route::post('/procurementrequest/ordersconfirm', [ProcurementRequestBiddingController::class, 'ordersconfirm'])->name(name: 'procurementrequest.ordersconfirm'); // leo added
    Route::post('/procurementrequest/delivery', [ProcurementRequestBiddingController::class, 'delivery'])->name(name: 'procurementrequest.delivery'); // leo added
    Route::post('/procurementrequest/decreceived', [ProcurementRequestBiddingController::class, 'decreceived'])->name(name: 'procurementrequest.decreceived'); // leo added
    Route::post('/procurementrequest/ordersremarks', [ProcurementRequestBiddingController::class, 'ordersremarks'])->name(name: 'procurementrequest.ordersremarks'); // leo added
    Route::post('/procurementrequest/deliveryremarks', [ProcurementRequestBiddingController::class, 'deliveryremarks'])->name(name: 'procurementrequest.deliveryremarks'); // leo added
    Route::post('/procurementrequest/receivingqty', [ProcurementRequestBiddingController::class, 'receivingqty'])->name(name: 'procurementrequest.receivingqty'); // leo added
    Route::post('/procurementrequest/receiving', [ProcurementRequestBiddingController::class, 'receiving'])->name(name: 'procurementrequest.receiving'); // leo added
    Route::post('/procurementrequest/receivingremarks', [ProcurementRequestBiddingController::class, 'receivingremarks'])->name(name: 'procurementrequest.receivingremarks'); // leo added
    Route::post('/procurementrequest/receivingconfirm', [ProcurementRequestBiddingController::class, 'receivingconfirm'])->name(name: 'procurementrequest.receivingconfirm'); // leo added

    // SUPPLIER BIDDING
    Route::get('/procurementbidding', [SupplierBiddingController::class, 'index'])->name('procurementbidding.index');  
    Route::get('/procurementbidding/edit/{slug}', [SupplierBiddingController::class, 'edit']);
    Route::get('/procurementbiddingApproved', [SupplierBiddingController::class, 'approvedIndex'])->name('procurementbidding.approvedIndex');
    Route::get('/procurementbiddingApproved/{page}/{search}', [SupplierBiddingController::class, 'approvedSearch']);
    Route::get('/procurementbidding/received/{slug}', [SupplierBiddingController::class, 'received']);
    Route::get('/procurementmodal', [SupplierBiddingController::class, 'showBySlug'])->name('procurementmodal');
    Route::get('/procurementreceived', [SupplierBiddingController::class, 'receivedIndex'])->name('procurementreceived.receivedIndex');
    Route::get('/procurementreceived/receivedcompleted/{slug}', [SupplierBiddingController::class, 'receivedcompleted']);
    Route::get('/procurementreceived/{page}/{search}', [SupplierBiddingController::class, 'receivedSearch']);
    Route::post('/procurementreceive/appqtyreceived', [SupplierBiddingController::class, 'appqtyreceived'])->name(name: 'procurementreceive.appqtyreceived');
    Route::get('/procurementtransfer/{page}/{search}', [SupplierBiddingController::class, 'search']);
    Route::post('/procurementbidding/update-status', [SupplierBiddingController::class, 'updateStatus'])->name('procurementbidding.updateStatus');
    Route::get('/procurementreceiving', [SupplierBiddingController::class, 'receivingIndex'])->name('procurementreceiving.receivingIndex');
    Route::get('/procurementreceiving/receivingcompleted/{slug}', [SupplierBiddingController::class, 'receivingcompleted']);
    Route::post('/procurementreceive/supplierqty', [SupplierBiddingController::class, 'supplierqty'])->name(name: 'procurementreceive.supplierqty');
    
    // Ordering routes
    Route::get('/procurementbiddingOrdering', [SupplierBiddingController::class, 'orderingIndex'])->name('procurementbidding.orderingIndex');
    Route::get('/procurementbiddingOrdering/orderingview/{slug}', [SupplierBiddingController::class, 'orderingview']);
    Route::get('/procurementbiddingOrdering/{page}/{search}', [SupplierBiddingController::class, 'orderingSearch']);

    // Delivery routes
    Route::get('/procurementbiddingDelivery', [SupplierBiddingController::class, 'deliveryIndex'])->name('procurementbidding.deliveryIndex');
    Route::get('/procurementbiddingDelivery/deliveryview/{slug}', [SupplierBiddingController::class, 'deliveryview']);
    Route::get('/procurementbiddingDelivery/{page}/{search}', [SupplierBiddingController::class, 'deliverySearch']);
   
    // EXPENSES CATEGORY
    Route::get('/system-management/exp-cat', [ExpensesCategoryController::class, 'index'])->name('exp.cat.index');
    Route::get('/system-management/exp-cat/add', [ExpensesCategoryController::class, 'add'])->name('exp.cat.add');
    Route::post('/system-management/exp-cat/store', [ExpensesCategoryController::class, 'store'])->name('exp.cat.store');
    Route::get('/system-management/exp-cat/edit/{slug}', [ExpensesCategoryController::class, 'edit']);
    Route::post('/system-management/exp-cat/update', [ExpensesCategoryController::class, 'update'])->name('exp.cat.update');
    Route::get('/system-management/exp-cat/delete/{slug}', [ExpensesCategoryController::class, 'delete'])->name('supp.cat.delete');
    Route::get('/system-management/exp-cat/{page}', [ExpensesCategoryController::class, 'paginate']);
    Route::get('/system-management/exp-cat/{page}/{search}', [ExpensesCategoryController::class, 'search']);
    
    // TABLE CATEGORY ADDED - LEO
    Route::get('/system-management/table-cat', [TableCategoryController::class, 'index'])->name('tbl.cat.index');
    Route::get('/system-management/table-cat/add', [TableCategoryController::class, 'add'])->name('tbl.cat.add');
    Route::post('/system-management/table-cat/store', [TableCategoryController::class, 'store'])->name('tbl.cat.store');
    Route::get('/system-management/table-cat/edit/{slug}', [TableCategoryController::class, 'edit']);
    Route::post('/system-management/table-cat/update', [TableCategoryController::class, 'update'])->name('tbl.cat.update');
    Route::get('/system-management/table-cat/delete/{slug}', [TableCategoryController::class, 'delete'])->name('tbl.cat.delete');
    Route::get('/system-management/table-cat/{page}', [TableCategoryController::class, 'paginate']);
    Route::get('/system-management/table-cat/{page}/{search}', [TableCategoryController::class, 'search']);

    // INVENTORY CATEGORY
    Route::get('/system-management/inventory-category', [CategoryController::class, 'index'])->name('inventory.category.index');
    Route::get('/system-management/inventory-category/add', [CategoryController::class, 'add'])->name('inventory.category.add');
    Route::post('/system-management/inventory-category/store', [CategoryController::class, 'store'])->name('inventory.category.store');
    Route::get('/system-management/inventory-category/edit/{slug}', [CategoryController::class, 'edit']);
    Route::post('/system-management/inventory-category/update', [CategoryController::class, 'update'])->name('inventory.category.update');
    Route::get('/system-management/inventory-category/delete/{slug}', [CategoryController::class, 'delete'])->name('inventory.category.delete');
    Route::get('/system-management/inventory-category/{page}', [CategoryController::class, 'paginate']);
    Route::get('/system-management/inventory-category/{page}/{search}', [CategoryController::class, 'search']);

    // MENU CATEGORY
    Route::get('/system-management/menu-category', [MenuCategoryController::class, 'index'])->name('menu.category.index');
    Route::get('/system-management/menu-category/add', [MenuCategoryController::class, 'add'])->name('menu.category.add');
    Route::post('/system-management/menu-category/store', [MenuCategoryController::class, 'store'])->name('menu.category.store');
    Route::get('/system-management/menu-category/edit/{slug}', [MenuCategoryController::class, 'edit']);
    Route::post('/system-management/menu-category/update', [MenuCategoryController::class, 'update'])->name('menu.category.update');
    Route::get('/system-management/menu-category/delete/{slug}', [MenuCategoryController::class, 'delete'])->name('menu.category.delete');
    Route::get('/system-management/menu-category/{page}', [MenuCategoryController::class, 'paginate']);
    Route::get('/system-management/menu-category/{page}/{search}', [MenuCategoryController::class, 'search']);

    // SETTINGS
    Route::get('/system-management/settings', [SettingController::class, 'settings'])->name('settings.index');
    Route::post('/system-management/settings/store', [SettingController::class, 'store'])->name('settings.store');
    Route::get('/system-management/settings/add', [SettingController::class, 'add'])->name('settings.add');
    Route::get('/system-management/settings/edit/{id}', [SettingController::class, 'edit']);
    Route::post('/system-management/settings/update', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/system-management/settings/delete/{id}', [SettingController::class, 'delete'])->name('settings.delete');
    Route::get('/system-management/settings/{page}', [SettingController::class, 'paginate']);
    Route::get('/system-management/settings/{page}/{search}', [SettingController::class, 'search']);
    // INVENTORY MOVEMENT new route added by ken 8-13-2026
        Route::get('/inventory/movement', [InventoryMovementController::class, 'index'])->name('inventory.movement.index');
        Route::get('/inventory/movement/popup/{id}', [InventoryMovementController::class, 'getPopupData'])->name('inventory.movement.popup');
        Route::get('/inventory/movement/print', [InventoryMovementController::class, 'printReport'])->name('inventory.movement.print'); 
        Route::get('/inventory/movement/print-all', [InventoryMovementController::class, 'printAllFilteredReport'])->name('inventory.movement.printAll');
        Route::get('/inventory/movement/export', [InventoryMovementController::class, 'exportAllFilteredReport'])->name('inventory.movement.export');
        Route::get('/inventory/export', [InventoryMovementController::class, 'exportReport'])->name('inventory.export');   
        //END INVENTORY MOVEMENT new route added by ken 8-13-2026
        //NEW ADDED BY KEN 8-12-2026
    // ONLINE BANKING ADDED START HERE BY KEN 20250623001  -- 1-11-26
    Route::get('/system-management/onlinebank', [OnlineBankController::class, 'onlinebank'])->name('onlinebank.index');
    Route::post('/system-management/onlinebank/store', [OnlineBankController::class, 'store'])->name('onlinebank.store');
    Route::get('/system-management/onlinebank/add', [OnlineBankController::class, 'add'])->name('onlinebank.add');
    Route::get('/system-management/onlinebank/edit/{id}', [OnlineBankController::class, 'edit']);
    Route::post('/system-management/onlinebank/update', [OnlineBankController::class, 'update'])->name('onlinebank.update');
    Route::get('/system-management/onlinebank/delete/{id}', [OnlineBankController::class, 'delete'])->name('onlinebank.delete');
    Route::get('/system-management/onlinebank/{page}', [OnlineBankController::class, 'paginate']);
    Route::get('/system-management/onlinebank/{page}/{search}', [OnlineBankController::class, 'search']);
    // ONLINE BANKING ADDED END HERE BY KEN 20250623001  -- 1-11-26

    // STAFF
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/clock', [StaffController::class, 'clocking'])->name('staff.clocking');
    Route::post('/staff/check', [StaffController::class, 'check'])->name('staff.check');
    Route::post('/staff/serverout', [StaffController::class, 'serverout'])->name('staff.serverout');
    Route::get('/staff/add', [StaffController::class, 'add'])->name('staff.add');
    Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');
    Route::post('/staff/storeClocking', [StaffController::class, 'StoreClocking'])->name('staff.StoreClocking');
    Route::get('/staff/edit/{slug}', [StaffController::class, 'edit']);
    Route::post('/staff/update', [StaffController::class, 'update'])->name('staff.update');
    Route::get('/staff/delete/{slug}', [StaffController::class, 'delete'])->name('staff.delete');
    Route::get('/staff/{page}', [StaffController::class, 'paginate']);
    Route::get('/staff/{page}/{search}', [StaffController::class, 'search']);
    
    
    // RETURN BOTTLE ADDED START HERE BY KEN 20250623001  -- 1-11-26
    Route::get('/system-management/returnbottle', [ReturnBottleController::class, 'index'])->name('returnbottle.index');
    Route::post('/system-management/returnbottle/store', [ReturnBottleController::class, 'store'])->name('returnbottle.store');
    Route::get('/system-management/returnbottle/add', [ReturnBottleController::class, 'add'])->name('returnbottle.add');
    Route::get('/system-management/returnbottle/edit/{id}', [ReturnBottleController::class, 'edit']);
    Route::post('/system-management/returnbottle/update', [ReturnBottleController::class, 'update'])->name('returnbottle.update');
    Route::get('/system-management/returnbottle/delete/{id}', [ReturnBottleController::class, 'delete'])->name('returnbottle.delete');
    Route::get('/system-management/returnbottle/complete/{id}', [ReturnBottleController::class, 'complete'])->name('returnbottle.complete');
    Route::get('/system-management/returnbottle/restore/{id}', [ReturnBottleController::class, 'restore'])->name('returnbottle.restore');
    Route::get('/system-management/returnbottle/{page}', [ReturnBottleController::class, 'paginate']);
    Route::get('/system-management/returnbottle/{page}/{search}', [ReturnBottleController::class, 'search']);
    // RETURN BOTTLE ADDED END HERE BY KEN 20250623001  -- 1-11-26
    
    // start updated route ken updated 7-19-2026
        Route::get('/inventory/transform', [TransformController::class, 'index'])->name('transform.index');
        Route::get('/transform/{slug}/details', [TransformController::class, 'getDetails'])->name('transform.details');
        Route::get('/inventory/transform/create', [TransformController::class, 'create'])->name('transform.create');
        Route::post('/inventory/transform/store', [TransformController::class, 'store'])->name('transform.store');
        Route::get('/inventory/transform/{slug}/edit', [TransformController::class, 'edit'])->name('transform.edit');
        Route::post('/inventory/transform/update', [TransformController::class, 'update'])->name('transform.update');
        Route::post('/inventory/transform/approve', [TransformController::class, 'approve'])->name('transform.approve');
        Route::post('/transform/reject', [TransformController::class, 'reject'])->name('transform.reject');    
        Route::post('/transform/revert', [TransformController::class, 'revert'])->name('transform.revert');    
        Route::get('/inventory/transform/{slug}/delete', [TransformController::class, 'delete'])->name('transform.delete');
        Route::get('/inventory/transform/{slug}', [TransformController::class, 'show'])->name('transform.show');
        // TRANSFORM UPDATED END
        // INVENTORY MOVEMENT
        Route::get('/inventory/movement', [InventoryMovementController::class, 'index'])->name('inventory.movement.index');
        Route::get('/inventory/movement/popup/{id}', [InventoryMovementController::class, 'getPopupData'])->name('inventory.movement.popup');

        // Add Stock (Create Purchase Order)
        Route::get('/inventory/add-stock', [AddStockController::class, 'create'])->name('stock.add');
        Route::post('/inventory/add-stock/store', [AddStockController::class, 'store'])->name('stock.store');
        Route::post('/inventory/add-stock/cart', [AddStockController::class, 'syncCart'])->name('stock.cart.sync');
        // AJAX: Get menus by brand
        Route::get('/inventory/add-stock/menus-by-brand/{brandId}', [AddStockController::class, 'getMenusByBrand'])->name('stock.menus-by-brand');
        // START LINE 574: Branch Transfer 20260528001 JET START LINES 574 - 590
        Route::get('/inventory/branch-transfer', [BranchTransferController::class, 'create'])->name('branch-transfer.add');
        Route::post('/inventory/branch-transfer/store', [BranchTransferController::class, 'store'])->name('branch-transfer.store');
        Route::get('/inventory/branch-transfer/menus-by-brand/{brandId}', [BranchTransferController::class, 'getMenusByBrand'])->name('branch-transfer.menus-by-brand');
        Route::get('/inventory/branch-transfer/menus/search', [BranchTransferController::class, 'searchMenus'])->name('branch-transfer.menus.search');
        Route::get('/inventory/branch-transfer/menus/details/{menuId}', [BranchTransferController::class, 'getMenuDetails'])->name('branch-transfer.menus.details');
        Route::get('/inventory/location-transfer/pending', [BranchTransferController::class, 'pendingApproval'])->name('branch-transfer.pending');
        Route::get('/inventory/location-transfer/pending/view/{id}/json', [BranchTransferController::class, 'viewPendingApprovalJson'])->name('branch-transfer.pending.view.json');
        Route::post('/inventory/location-transfer/pending/update/{id}/json', [BranchTransferController::class, 'updatePendingApprovalJson'])->name('branch-transfer.pending.update.json');
        Route::post('/inventory/location-transfer/pending/approve/{id}', [BranchTransferController::class, 'approvePendingTransfer'])->name('branch-transfer.pending.approve');
        Route::post('/inventory/location-transfer/pending/reject/{id}', [BranchTransferController::class, 'rejectPendingTransfer'])->name('branch-transfer.pending.reject');
        Route::get('/inventory/location-transfer/received', [BranchTransferController::class, 'receivedTransfers'])->name('branch-transfer.received');
        Route::get('/inventory/location-transfer/received/view/{id}/json', [BranchTransferController::class, 'viewReceivedTransferJson'])->name('branch-transfer.received.view.json');
        Route::post('/inventory/location-transfer/received/checking/{id}/json', [BranchTransferController::class, 'markReceivedTransferChecking'])->name('branch-transfer.received.checking.json');
        Route::post('/inventory/location-transfer/received/cancel-checking/{id}/json', [BranchTransferController::class, 'cancelReceivedTransferChecking'])->name('branch-transfer.received.cancel-checking.json');
        Route::post('/inventory/location-transfer/received/confirm/{id}/json', [BranchTransferController::class, 'confirmReceivedTransfer'])->name('branch-transfer.received.confirm.json'); 
        // END LINE 590: 20260528001 JET END
        // Main approval page
        Route::get('/inventory/pending-purchase-orders', [AddStockController::class, 'pendingApproval'])->name('pending.purchase.orders.index');

        Route::get('/inventory/pending-purchase-orders/view/{id}/json', [AddStockController::class, 'viewPendingApprovalJson'])->name('pending.purchase.orders.view.json');
        Route::get('/inventory/pending-purchase-orders/edit/{id}/json', [AddStockController::class, 'editPendingApprovalJson'])->name('pending.purchase.orders.edit.json');
        Route::post('/inventory/pending-purchase-orders/update/{id}/json', [AddStockController::class, 'updatePendingApprovalJson'])->name('pending.purchase.orders.update.json');
        // Action routes
        Route::post('/inventory/pending-purchase-orders/approve/{id}', [AddStockController::class, 'approvePurchaseOrder'])->name('pending.purchase.orders.approve');
        Route::post('/inventory/pending-purchase-orders/reject/{id}', [AddStockController::class, 'rejectPurchaseOrder'])->name('pending.purchase.orders.reject');
        Route::delete('/inventory/pending-purchase-orders/delete/{id}', [AddStockController::class, 'deletePendingApproval'])->name('pending.purchase.orders.delete');
        // Additional AJAX routes for menu search
        Route::get('/inventory/menus/search', [AddStockController::class, 'searchMenus'])->name('menus.search');
        Route::get('/inventory/menus/details/{menuId}', [AddStockController::class, 'getMenuDetails'])->name('menus.details');
    //kend end of updated route:  07-19-2026
});


Route::middleware("role:1,2,3,4,5,6,8,9")->group(function(){
    // DASHBOARD
    Route::get('/kitchen-display', [KitchenController::class, 'index'])->name('kitchen.display');
    Route::post('/kitchen-display/change-status', [KitchenController::class, 'change'])->name('kitchen.change');
    Route::post('/kitchen-display/check', [KitchenController::class, 'check'])->name('kitchen.check');
    Route::post('/kitchen-display/serve', [KitchenController::class, 'serve'])->name('kitchen.serve');

    // MENU
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::get('/menu/add', [MenuController::class, 'add'])->name('menu.add');
    Route::post('/menus/generate-brand-code', [MenuController::class, 'genBrandCode'])->name('menus.generateBrandCode');
    Route::post('/menus/generate-item-code', [MenuController::class, 'genItemCode'])->name('menus.generateItemCode'); // LINE 618: ADDED BY KEN 10-26-26
    Route::get('/menu/edit/{slug}', [MenuController::class, 'edit']);
    //start ken added route for import, export, dowload template 12-13-2025
    Route::get('/menu/export/{branch?}', [MenuController::class, 'exportMenusCSV'])->name('menu.export');
    Route::get('/menu/template/{branch?}', [MenuController::class, 'downloadMenuTemplate'])->name('menu.template');
    Route::post('/menu/validate-csv', [MenuController::class, 'validateMenusCSV'])->name('menu.validate-csv');
    Route::post('/menu/import', [MenuController::class, 'importMenusCSV'])->name('menu.import');   
    //end ken added route for import, export, dowload template 12-13-2025     
    Route::POST('/menu/add/change-ing', [MenuController::class, 'changeIng'])->name('menu.changeIng');
    Route::POST('/menu/add/add-ing', [MenuController::class, 'addIng'])->name('menu.addIng');
    Route::post('/menu/store', [MenuController::class, 'store'])->name('menu.store');
    
    Route::post('/menu/update', [MenuController::class, 'update'])->name('menu.update');
    Route::post('/menu/update-category', [MenuController::class, 'updateCategory'])->name('menu.updateCategory');
    Route::post('/menu/view', [MenuController::class, 'view'])->name('menu.view');
    Route::get('/menu/get-brands', [MenuController::class, 'getBrands'])->name('menu.getBrands');

    // Toast endpoint: accept toast push requests
    Route::post('/toast/send', [\App\Http\Controllers\ToastController::class, 'send'])->name('toast.send');
    Route::post('/menu/move', [MenuController::class, 'move'])->name('menu.move');
    Route::post('/menu/compute-quantity', [MenuController::class, 'computeqty'])->name('menu.computeqty');
    Route::post('/menu/change-quantity', [MenuController::class, 'changeqty'])->name('menu.changeqty');
    Route::get('/menu/delete/{slug}', [MenuController::class, 'delete'])->name('menu.delete');
    Route::post('/menu/dispose', [MenuController::class, 'dispose'])->name('menu.dispose');
    Route::get('/menu/{page}', [MenuController::class, 'paginate']);
    Route::get('/menu/{page}/{search}', [MenuController::class, 'search']);
    Route::get('/menu/category/{page}/{category}', [MenuController::class, 'searchcategory']);
});


Route::middleware('role:1,2,3,4,5,6,8,9')->group(function(){

    // MASTERLIST
    Route::get('/masterlist', [MasterlistController::class, 'index'])->name('masterlist.index');
    Route::get('/masterlist/add', [MasterlistController::class, 'add'])->name('masterlist.add');
    Route::post('/masterlist/store', [MasterlistController::class, 'store'])->name('masterlist.store');
    Route::post('/masterlist/add-qty', [MasterlistController::class, 'addqty'])->name('masterlist.addqty');
    Route::post('/masterlist/minus-qty', [MasterlistController::class, 'minusqty'])->name('masterlist.minusqty');
    Route::get('/masterlist/edit/{slug}', [MasterlistController::class, 'edit']);
    Route::post('/masterlist/update', [MasterlistController::class, 'update'])->name('masterlist.update');
    Route::get('/masterlist/delete/{slug}', [MasterlistController::class, 'delete'])->name('masterlist.delete');
    Route::post('/masterlist/dispose', [MasterlistController::class, 'dispose'])->name('masterlist.dispose');
    Route::get('/masterlist/{page}', [MasterlistController::class, 'paginate']);
    Route::get('/masterlist/{page}/{search}', [MasterlistController::class, 'search']);
    
    // LINE634: START of ROUTE SAWDUST sept 30, 2026
    Route::get('/inventory/sawdust', [SawdustController::class, 'index'])->name('sawdust.index');
    Route::post('/inventory/sawdust/store', [SawdustController::class, 'store'])->name('sawdust.store');
    // LINE 638: END OF NEW ROUTE 9-30-2026

    // INVENTORY
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/add', [InventoryController::class, 'add'])->name('inventory.add');
    Route::post('/inventory/portion', [InventoryController::class, 'portion'])->name('inventory.portion');
    Route::post('/inventory/checking', [InventoryController::class, 'checking'])->name('inventory.checking');
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/add-qty', [InventoryController::class, 'addqty'])->name('inventory.addqty');
    Route::post('/inventory/minus-qty', [InventoryController::class, 'minusqty'])->name('inventory.minusqty');
    Route::get('/inventory/edit/{slug}', [InventoryController::class, 'edit']);
    Route::post('/inventory/update', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/delete/{slug}', [InventoryController::class, 'delete'])->name('inventory.delete');
    Route::post('/inventory/dispose', [InventoryController::class, 'dispose'])->name('inventory.dispose');
    Route::get('/inventory/{page}', [InventoryController::class, 'paginate']);
    Route::get('/inventory/{page}/{search}', [InventoryController::class, 'search']);


     // EXPENSES
    Route::get('/expenses', [ExpensesController::class, 'index'])->middleware('role:1,2')->name('expenses.index');
    Route::get('/expenses/add', [ExpensesController::class, 'add'])->name('expenses.add');
    // FIX (item 5): was '/expenses/edit2{id}' (missing slash before {id}).
    // route('expenses.edit2', ...) usage in the Blade views builds the URL
    // from this definition, so fixing it here is sufficient.
    Route::get('/expenses/edit2/{id}', [ExpensesController::class, 'edit2'])->name('expenses.edit2');
    Route::get('/expenses/{id}/editcart', [ExpensesController::class, 'editCart'])->name('expenses.editcart');
    // FIX: editcart2/updateEdit2/store2/cart_store2 power the "Edit Inventory
    // Transaction Details" modal on the expenses.edit2 page itself, and that
    // page is reachable by the whole role:1,2,3,4,5,6,8,9 group (e.g. the
    // Edit button on /expenses, which is role:1,2). A previous change locked
    // these four AJAX endpoints down to role:5 only, which broke the modal
    // ("Error fetching data") for every other role that can legitimately
    // open the edit2 page. Widened back to match expenses.edit2's own role
    // group instead of leaving it fully open.
    Route::get('/expenses/{id}/editcart2', [ExpensesController::class, 'editCart2'])->name('expenses.editcart2');
    Route::put('/expenses/{inventoryId}/updateEdit', [ExpensesController::class, 'updateEdit'])->name('expenses.updateEdit');
    Route::put('/expenses/{inventoryId}/updateEdit2', [ExpensesController::class, 'updateEdit2'])->name('expenses.updateEdit2');
    Route::post('/expenses/store', [ExpensesController::class, 'store'])->name('expenses.store');
    Route::post('/expenses/store2', [ExpensesController::class, 'store2'])->name('expenses.store2');
    Route::post('/expenses/storeOther', [ExpensesController::class, 'storeOther'])->name('expenses.storeOther');
    Route::post('/expenses/cart_store', [ExpensesController::class, 'cart_store'])->name('expenses.cart_store');
    Route::post('/expenses/cart_store2', [ExpensesController::class, 'cart_store2'])->name('expenses.cart_store2');
    Route::post('/expenses/cart_delete', [ExpensesController::class, 'cart_delete'])->name('expenses.cart_delete');
    Route::post('/expenses/tran_remove', [ExpensesController::class, 'tran_remove'])->name('expenses.tran_remove');
    Route::post('/expenses/cart_delete2', [ExpensesController::class, 'cart_delete2'])->name('expenses.cart_delete2');
    Route::post('/expenses/add-qty', [ExpensesController::class, 'addqty'])->name('expenses.addqty');
    Route::post('/expenses/minus-qty', [ExpensesController::class, 'minusqty'])->name('expenses.minusqty');
    Route::get('/expenses/edit/{id}', [ExpensesController::class, 'edit']);
    Route::post('/expenses/update', [ExpensesController::class, 'update'])->name('expenses.update');
    Route::get('/expenses/delete/{id}', [ExpensesController::class, 'delete'])->name('expenses.delete');
    Route::get('/expenses/{page}', [ExpensesController::class, 'paginate']);
    Route::get('/expenses/{page}/{search}', [ExpensesController::class, 'search']);
    Route::post('/update-tax-values', [ExpensesController::class, 'updateTaxValues'])->name('update-tax-values');
    Route::get('/inventories/{id}/edit2', [InventoryController::class, 'edit2']);
    Route::post('/inventories/{id}/update', [InventoryController::class, 'update']);
    Route::get('/get-inventory-transaction/{e_id}', [ExpensesController::class, 'getInventoryTransaction'])->name('get-inventory-transaction');;
    // Controller method renamed updateStatus() -> cancelOrResubmitSelf() for clarity (item 4). Route name/URL unchanged.
    Route::get('/e_request', [ERequestController::class, 'index'])->middleware('role:5')->name('e_request.index');
    // FIX (item 2): this endpoint actually writes to expenses_journal and
    // had no role middleware at all — anyone in the role:1,2,3,4,5,6,8,9
    // group could hit it directly. Locked down to role:5.
    Route::post('/update-expenses-approve', [ERequestController::class, 'updateStatusApproved'])->middleware('role:5')->name('update-expenses-approve');
    // FIX (item 3): was unrestricted (open to the whole role:1,2,3,4,5,6,8,9
    // group). Restricted to the roles that actually touch the expenses
    // module: 1,2 (create) and 5 (approve).
    Route::get('/expenses-journal', [ExpensesJournalController::class, 'index'])->middleware('role:1,2,5')->name('expenses-journal.index');
    
        // DELETION APPROVAL (new — role 5 only)
    Route::get('/deletion-approval', [DeletionApprovalController::class, 'index'])->middleware('role:5')->name('deletion-approval.index');
    Route::post('/deletion-approval/approve', [DeletionApprovalController::class, 'approve'])->middleware('role:5')->name('deletion-approval.approve');
    Route::post('/deletion-approval/reject', [DeletionApprovalController::class, 'reject'])->middleware('role:5')->name('deletion-approval.reject');
    // LINE 736: START OF NEW ROUTE CHART OF ACCOUNT BY KEN 250623001 7-29-2026
    Route::prefix('report/chart-of-accounts')->name('report.chart-of-accounts.')->group(function () {
        Route::get('/', [ChartOfAccountsController::class, 'index'])->name('index');

        // Chart Category
        Route::post('/chart-category/store', [ChartOfAccountsController::class, 'storeChartCategory'])->name('chart-category.store');
        Route::post('/chart-category/update', [ChartOfAccountsController::class, 'updateChartCategory'])->name('chart-category.update');
        Route::get('/chart-category/delete/{id}', [ChartOfAccountsController::class, 'deleteChartCategory'])->name('chart-category.delete');
        Route::post('/chart-category/formula', [ChartOfAccountsController::class, 'saveChartCategoryFormula'])->name('chart-category.formula');

        // Industry
        Route::post('/industry/store', [ChartOfAccountsController::class, 'storeIndustry'])->name('industry.store');
        Route::post('/industry/update', [ChartOfAccountsController::class, 'updateIndustry'])->name('industry.update');
        Route::get('/industry/delete/{slug}', [ChartOfAccountsController::class, 'deleteIndustry'])->name('industry.delete');

        // Sub Account
        Route::post('/subaccount/store', [ChartOfAccountsController::class, 'storeSubaccount'])->name('subaccount.store');
        Route::post('/subaccount/update', [ChartOfAccountsController::class, 'updateSubaccount'])->name('subaccount.update');
        Route::get('/subaccount/delete/{slug}', [ChartOfAccountsController::class, 'deleteSubaccount'])->name('subaccount.delete');

        // Classification / Journal
        Route::post('/classification/store', [ChartOfAccountsController::class, 'storeClassification'])->name('classification.store');
        Route::post('/classification/update', [ChartOfAccountsController::class, 'updateClassification'])->name('classification.update');
        Route::get('/classification/delete/{slug}', [ChartOfAccountsController::class, 'deleteClassification'])->name('classification.delete');

        // Account Code
        Route::post('/account-code/store', [ChartOfAccountsController::class, 'storeAccountCode'])->name('account-code.store');
        Route::post('/account-code/update', [ChartOfAccountsController::class, 'updateAccountCode'])->name('account-code.update');
        Route::get('/account-code/delete/{slug}', [ChartOfAccountsController::class, 'deleteAccountCode'])->name('account-code.delete');

        // AJAX
        Route::get('/expense-categories', [ChartOfAccountsController::class, 'getExpenseCategories'])->name('expense-categories');
        Route::post('/update-field', [ChartOfAccountsController::class, 'updateField'])->name('update-field');
    });
    // LINE 768: END OF NEW ROUTE CHART OF ACCOUNT BY KEN 250623001 7-29-2026
    // REVOLVING
    Route::get('/revolving', [RevolvingController::class, 'index'])->name('revolving.index');
    Route::get('/revolving/budget_index', [RevolvingController::class, 'budget_index'])->name('revolving.budget_index');
    Route::get('/revolving/add', [RevolvingController::class, 'add'])->name('revolving.add');
    Route::get('/revolving/addNewBudget', [RevolvingController::class, 'addNewBudget'])->name('revolving.addNewBudget');
    Route::get('/revolving/budget/{id}', [RevolvingController::class, 'budget']);
    Route::get('/revolving/add_budget/{id}', [RevolvingController::class, 'add_budget']);
    Route::post('/revolving/store', [RevolvingController::class, 'store'])->name('revolving.store');
    Route::post('/revolving/budget_store', [RevolvingController::class, 'budget_store'])->name('revolving.budget_store');
    Route::post('/revolving/cart_store', [RevolvingController::class, 'cart_store'])->name('revolving.cart_store');
    Route::post('/revolving/cart_delete', [RevolvingController::class, 'cart_delete'])->name('revolving.cart_delete');
    Route::post('/revolving/add-qty', [RevolvingController::class, 'addqty'])->name('revolving.addqty');
    Route::post('/revolving/minus-qty', [RevolvingController::class, 'minusqty'])->name('revolving.minusqty');
    Route::get('/revolving/edit/{id}', [RevolvingController::class, 'edit']);
    Route::post('/revolving/update', [RevolvingController::class, 'update'])->name('revolving.update');
    Route::post('/revolving/update_budget', [RevolvingController::class, 'update_budget'])->name('revolving.update_budget');
    Route::post('/revolving/addition_budget', [RevolvingController::class, 'addition_budget'])->name('revolving.addition_budget');
    Route::get('/revolving/delete/{id}', [RevolvingController::class, 'delete'])->name('revolving.delete');
    Route::get('/revolving/{page}', [RevolvingController::class, 'paginate']);
    Route::get('/revolving/{page}/{search}', [RevolvingController::class, 'search']);




    Route::get('/report/inventory', [ReceiverReportController::class, 'invIndex'])->name('report.inventory.index');
    Route::get('/report/inventory/print', [ReceiverReportController::class, 'invPrint'])->name('report.inventory.print');
    Route::get('/report/inventory/{page}', [ReceiverReportController::class, 'invPaginate']);
    Route::get('/report/inventory/{page}/{search}', [ReceiverReportController::class, 'invSearch']);



    Route::get('/report/menu', [ReceiverReportController::class, 'menuIndex'])->name('report.menu.index');
    Route::get('/report/menu/print', [ReceiverReportController::class, 'menuPrint'])->name('report.menu.print');
    Route::get('/report/menu/{page}', [ReceiverReportController::class, 'menuPaginate']);
    Route::get('/report/menu/{page}/{search}', [ReceiverReportController::class, 'menuSearch']);
    
    
    // 4-27 harold
    Route::get('/deletedmenu', [MenuController::class, 'deletedmenu'])->name('deletedmenu.index');
    Route::post('/deletedmenu', [MenuController::class, 'menurestore'])->name('menu.restore');
    Route::get('/deletedmenu/{page}/{search}', [MenuController::class, 'searchdeletedmenu']);

    
    Route::get('/deletedraw', [MenuController::class, 'deletedraw'])->name('deletedraw.index');
    Route::post('/deletedraw', [MenuController::class, 'rawrestore'])->name('raw.restore');
    Route::get('/deletedraw/{page}/{search}', [MenuController::class, 'searchdeletedraw']);
    
    // 4-27 harold end
     
});


Route::middleware('role:1,5,6,2,7,8,9,3')->group(function(){

    // REQUEST
    Route::get('/request', [RequestController::class, 'index'])->name('request.index');
    Route::post('/request/forecast', [RequestController::class, 'forecast'])->name('request.forecast');
    Route::post('/request/submit', [RequestController::class, 'submit'])->name('request.submit');
    Route::post('/request/appqty', [RequestController::class, 'appqty'])->name(name: 'request.appqty');
    Route::post('/request/recqty', [RequestController::class, 'recqty'])->name(name: 'request.recqty');
    Route::post('/request/approved', [RequestController::class, 'approved'])->name(name: 'request.approved');
    Route::post('/request/received', [RequestController::class, 'received'])->name(name: 'request.received');
    Route::post('/request/add', [RequestController::class, 'add'])->name('request.add');
    Route::post('/request/addqty', [RequestController::class, 'addqty'])->name('request.addqty');
    Route::post('/request/inc', [RequestController::class, 'inc'])->name('request.inc');
    Route::post('/request/desc', [RequestController::class, 'desc'])->name('request.desc');
    Route::post('/request/comp', [RequestController::class, 'comp'])->name('request.comp');

    Route::post('/request/remove', [RequestController::class, 'remove'])->name('request.remove');
    Route::post('/request/pay', [RequestController::class, 'pay'])->name('request.pay');
    Route::post('/request/paylater', [RequestController::class, 'paylater'])->name('request.paylater');
    Route::post('/request/sc/update', [RequestController::class, 'updateServiceCharge'])->name('request.updateServiceCharge');
    Route::post('/request/ld/update', [RequestController::class, 'updateLimeDiscount'])->name('request.updateLimeDiscount');
    Route::post('/request/discount/update', [RequestController::class, 'updateDiscount'])->name('request.updateDiscount');
    Route::get('/request/discount/delete', [RequestController::class, 'deleteDiscount'])->name('request.deleteDiscount');
    Route::get('/request/sales/send', [RequestController::class, 'send'])->name('request.send');
    Route::get('/request/print/{id}', [RequestController::class, 'print']);


    // POS
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos-admin', [POSController::class, 'indexAdmin'])->name('pos.indexAdmin');
    Route::post('/pos/add', [POSController::class, 'add'])->name('pos.add');
    Route::post('/pos/addqty', [POSController::class, 'addqty'])->name('pos.addqty');
    Route::post('/pos/inc', [POSController::class, 'inc'])->name('pos.inc');
    Route::post('/pos/desc', [POSController::class, 'desc'])->name('pos.desc');
    Route::post('/pos/comp', [POSController::class, 'comp'])->name('pos.comp');
    Route::post('/pos/discountSelected', [POSController::class, 'discountSelected'])->name('pos.discountSelected');
    Route::post('/pos/grab', [POSController::class, 'grab'])->name('pos.grab');
    Route::post('/pos/ownerdiscSelected', [POSController::class, 'ownerdiscSelected'])->name('pos.ownerdiscSelected');
    Route::post('/pos/menuRemarks', [POSController::class, 'menuRemarks'])->name('pos.menuRemarks');
    Route::post('/pos/remove', [POSController::class, 'remove'])->name('pos.remove');
    Route::post('/pos/change-price-type', [POSController::class, 'changePriceType'])->name('pos.changePriceType');
    Route::post('/pos/change-brand', [POSController::class, 'changeBrand'])->name('pos.changeBrand');
    Route::post('/pos/pay', [POSController::class, 'pay'])->name('pos.pay');
    Route::post('/pos/displayOrderNotif', [POSController::class, 'displayOrderNotif'])->name('pos.displayOrderNotif');
    Route::post('/pos/orderApprove', [POSController::class, 'orderApprove'])->name('pos.orderApprove');
    Route::post('/pos/orderDelete', [POSController::class, 'orderDelete'])->name('pos.orderDelete');
    Route::post('/pos/paylater', [POSController::class, 'paylater'])->name('pos.paylater');
    Route::post('/pos/callwaiter', [POSController::class, 'callwaiter'])->name('pos.callwaiter');
    Route::post('/pos/displayCallWaiterNotif', [POSController::class, 'displayCallWaiterNotif'])->name('pos.displayCallWaiterNotif');
    Route::post('/pos/assist', [POSController::class, 'assist'])->name('pos.assist');
    Route::post('/pos/commit', [POSController::class, 'commit'])->name('pos.commit');
    Route::post('/pos/sc/update', [POSController::class, 'updateServiceCharge'])->name('pos.updateServiceCharge');
    Route::post('/pos/ld/update', [POSController::class, 'updateLimeDiscount'])->name('pos.updateLimeDiscount');
    Route::post('/pos/discount/update', [POSController::class, 'updateDiscount'])->name('pos.updateDiscount');
    Route::get('/pos/discount/delete', [POSController::class, 'deleteDiscount'])->name('pos.deleteDiscount');
    Route::get('/pos/sales/send', [POSController::class, 'send'])->name('pos.send');
    Route::get('/pos/print/{id}', [POSController::class, 'print']);
    Route::post('/pos/check', [POSController::class, 'check'])->name('pos.check');

    // ORDERS
    Route::get('/orders', [OrderedController::class, 'index'])->name('orders.index');
    Route::get('/orders-admin', [OrderedController::class, 'indexAdmin'])->name('orders.indexAdmin');
    Route::post('/orders/reduce', [OrderedController::class, 'reduce'])->name('orders.reduce');
    Route::post('/orders/remove', [OrderedController::class, 'remove'])->name('orders.remove');
    Route::post('/orders/discountSelected', [OrderedController::class, 'discountSelected'])->name('orders.discountSelected');
    Route::post('/orders/cancel', [OrderedController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/open', [OrderedController::class, 'open'])->name('orders.open');
    Route::post('/orders/addpwd', [OrderedController::class, 'addpwd'])->name('orders.addpwd');
    Route::post('/orders/comp', [OrderedController::class, 'comp'])->name('orders.comp');
    Route::post('/orders/dp', [OrderedController::class, 'dp'])->name('orders.dp');
    Route::post('/orders/ownerdiscSelected', [OrderedController::class, 'ownerdiscSelected'])->name('orders.ownerdiscSelected');
    Route::post('/orders/multivoidcheck', [OrderedController::class, 'multivoidcheck'])->name('orders.multivoidcheck');
    Route::post('/orders/splitcheck', [OrderedController::class, 'splitcheck'])->name('orders.splitcheck');
    Route::post('/orders/split', [OrderedController::class, 'split'])->name('orders.split');
    Route::post('/orders/splitTable', [OrderedController::class, 'splitTable'])->name('orders.splitTable');
    Route::post('/orders/multivoid', [OrderedController::class, 'multivoid'])->name('orders.multivoid');
    Route::post('/orders/delpwd', [OrderedController::class, 'delpwd'])->name('orders.delpwd');
    Route::post('/orders/transferTable', [OrderedController::class, 'transferTable'])->name('orders.transferTable');
    Route::post('/orders/updateTable', [OrderedController::class, 'updateTable'])->name('orders.updateTable');
    Route::post('/orders/moveToAr', [OrderedController::class, 'moveToAr'])->name('orders.moveToAr');
    Route::post('/orders/occupy', [OrderedController::class, 'occupy'])->name('orders.occupy');
    Route::post('/orders/bar', [OrderedController::class, 'bar'])->name('orders.bar');
    Route::post('/orders/kitchen', [OrderedController::class, 'kitchen'])->name('orders.kitchen');
    Route::post('/orders/paid', [OrderedController::class, 'paid'])->name('orders.paid');
    Route::post('/orders/get-menu', [OrderedController::class, 'getMenu'])->name('orders.getMenu');
    Route::post('/orders/OrderupdateDiscount', [OrderedController::class, 'OrderupdateDiscount'])->name('orders.OrderupdateDiscount');
    Route::post('/orders/OrderdeleteDiscount', [OrderedController::class, 'OrderdeleteDiscount'])->name('orders.OrderdeleteDiscount');
    Route::post('/orders/get-amount', [OrderedController::class, 'getAmount'])->name('orders.getAmount');
    Route::post('/orders/editPax', [OrderedController::class, 'editPax'])->name('orders.editPax');
    Route::get('/orders/print/{id}', [OrderedController::class, 'print']);
    Route::get('/orders/print-server/{id}', [OrderedController::class, 'print_server']);

    // TRANSACTIONS
    // Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    // Route::post('/transactions/view', [TransactionController::class, 'view'])->name('transactions.view');
    // Route::post('/transactions/returnTable', [TransactionController::class, 'returnTable'])->name('transactions.returnTable');
    // Route::post('/transactions/updatestatus', [TransactionController::class, 'updatestatus'])->name('transactions.updatestatus');
    // Route::post('/transactions/generate', [TransactionController::class, 'generate'])->name('transactions.generate');
    // Route::get('/transactions/print/{id}', [TransactionController::class, 'print']);
    // Route::get('/transactions/printonly/{id}', [TransactionController::class, 'printonly']);
    
    // TRANSACTIONS updated ken 7-3-26
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions/view', [TransactionController::class, 'view'])->name('transactions.view');
    Route::post('/transactions/search-by-number', [TransactionController::class, 'searchByNumber'])->name('transactions.searchByNumber'); // LINE 927: added by ken for search feature of trans 9-30-2026
    Route::get('/transactions/{slug}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::post('transactions/update-payments', [TransactionController::class, 'updatePayments'])->name('transactions.updatePayments');    
    Route::post('/transactions/update', [TransactionController::class, 'update'])->name('transactions.update');// ken added update route  7/22/2026
    Route::post('/transactions/returnTable', [TransactionController::class, 'returnTable'])->name('transactions.returnTable');
    Route::post('/transactions/updatestatus', [TransactionController::class, 'updatestatus'])->name('transactions.updatestatus');
    Route::post('/transactions/generate', [TransactionController::class, 'generate'])->name('transactions.generate');
    Route::post('/transactions/toggle-approval-cart', [TransactionController::class, 'toggleApprovalCart'])->name('transactions.toggleApprovalCart');
    Route::post('/sales-journal/bulk-approve', [TransactionController::class, 'bulkApprove'])->name('sales-journal.bulkApprove');
    Route::post('/transactions/bulk-approve', [TransactionController::class, 'bulkApprove'])->name('transactions.bulkApprove');
    Route::post('/sales-journal/approve', [TransactionController::class, 'approve'])->name('sales-journal.approve');
    Route::get('/transactions/print/{id}', [TransactionController::class, 'print']);
    Route::get('/transactions/printonly/{id}', [TransactionController::class, 'printonly']);
    Route::post('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export'); //EXPORT TO EXCEL ADDED BY KEN 7-16-2026
    
    // SALE JOURNAL
    Route::get('/sales-journal', [SalesJournalController::class, 'index'])->name('sales-journal.index');
    Route::post('/sales-journal/view', [SalesJournalController::class, 'view'])->name('sales-journal.view');
    Route::post('/sales-journal/returnTable', [SalesJournalController::class, 'returnTable'])->name('sales-journal.returnTable');
    Route::post('/sales-journal/updatestatus', [SalesJournalController::class, 'updatestatus'])->name('sales-journal.updatestatus');
    Route::post('/sales-journal/generate', [SalesJournalController::class, 'generate'])->name('sales-journal.generate');

    Route::get('/sales-journal/print/{id}', [SalesJournalController::class, 'print']);
    Route::get('/sales-journal/printonly/{id}', [SalesJournalController::class, 'printonly']);

    // AR
    Route::get('/ar', [ArTransactionController::class, 'index'])->name('ar.index');
    Route::get('/ar/indexpaid', [ArTransactionController::class, 'indexpaid'])->name('ar.indexpaid');
    Route::post('/ar/view', [ArTransactionController::class, 'view'])->name('ar.view');
    Route::post('/ar/paid', [ArTransactionController::class, 'paid'])->name('ar.paid');
    Route::post('/ar/updatestatus', [ArTransactionController::class, 'updatestatus'])->name('ar.updatestatus');
    Route::post('/ar/generate', [ArTransactionController::class, 'generate'])->name('ar.generate');
    Route::get('/ar/print/{id}', [ArTransactionController::class, 'print']);

    // AR LOGS
    Route::get('/arlogs', [ArLogsTransactionController::class, 'index'])->name('arlogs.index');
    Route::post('/arlogs/view', [ArLogsTransactionController::class, 'view'])->name('arlogs.view');
    Route::post('/arlogs/paid', [ArLogsTransactionController::class, 'paid'])->name('arlogs.paid');
    Route::post('/arlogs/updatestatus', [ArLogsTransactionController::class, 'updatestatus'])->name('arlogs.updatestatus');
    Route::post('/arlogs/generate', [ArLogsTransactionController::class, 'generate'])->name('arlogs.generate');
    Route::get('/arlogs/print/{id}', [ArLogsTransactionController::class, 'print']);

    // WASTE
        // INVENTORY
        Route::get('/waste/inventory', [WasteController::class, 'inventoryIndex'])->name('waste.inventory.index');
        Route::get('/waste/inventory/restore/{id}', [WasteController::class, 'inventoryRestore'])->name('waste.inventory.restore');
        Route::get('/waste/inventory/{page}', [WasteController::class, 'inventoryPaginate']);
        Route::get('/waste/inventory/{page}/{search}', [WasteController::class, 'inventorySearch']);

        // MENU
        Route::get('/waste/menu', [WasteController::class, 'menuIndex'])->name('waste.menu.index');
        Route::get('/waste/menu/restore/{id}', [WasteController::class, 'menuRestore'])->name('waste.menu.restore');
        Route::get('/waste/menu/{page}', [WasteController::class, 'menuPaginate']);
        Route::get('/waste/menu/{page}/{search}', [WasteController::class, 'menuSearch']);
    // WASTE END
    
    //CUSTOMER BALANCE added by KEN 08-23-2026
    Route::get('/customer-balance', [CustomerBalanceController::class, 'index'])->name('ar.customer-balance');
    Route::get('/clients/search', [CustomerBalanceController::class, 'searchClients'])->name('clients.search');
    Route::get('/transactions/search', [CustomerBalanceController::class, 'searchTransactions'])->name('transactions.search');
    //CUSTOMER BALANCE END added by KEN 08-23-2026
    Route::post('/ar/customer-balance/toggle-pin', [CustomerBalanceController::class, 'togglePin'])->name('ar.toggle-pin');
    Route::get('/customer-balance/print-single/{clientId}', [CustomerBalanceController::class, 'printSingleClient'])->name('ar.customer-balance.print-single');  // new route added by ken 08-26-2026
    Route::get('/customer-balance/print-all', [CustomerBalanceController::class, 'printAllClients'])->name('ar.customer-balance.print-all');  // new route added by ken 08-26-2026
    
    //start SALES DETAILED REPORT added by KEN 08-16-2026
    Route::get('/sales-report', [SalesReportController::class, 'index'])->name('sales-report.index');
    Route::post('/sales-report/generate', [SalesReportController::class, 'generate'])->name('sales-report.generate');
    Route::post('/sales-report/transactions', [SalesReportController::class, 'getTransactions'])->name('sales-report.transactions');
    Route::post('/sales-report/cash-breakdown', [CashBreakdownController::class, 'getCashBreakdown'])->name('sales-report.cash-breakdown'); // LINE 906: ken added new route 9-10-2026
    Route::get('/sales-report/print', [SalesReportController::class, 'printReport'])->name('sales-report.print');
    Route::post('/sales-report/export', [SalesReportController::class, 'export'])->name('sales-report.export');
    //end SALES DETAILED REPORT added by KEN 08-16-2026
    // LINE 999: start SUBMIT SALES REPORT added by ken 10-22-2026
    Route::get('/submit-salesreport', [SaveSaleReportController::class, 'index'])->name('submit-salesreport.index');
    Route::post('/submit-salesreport', [SaveSaleReportController::class, 'store'])->name('submit-salesreport.store');
    // LINE 1002: end SUBMIT SALES REPORT added by ken 10-22-2026
    // LINE 910: start CASH BREAKDOWN REPORT added by KEN 9-10-2026
    Route::get('/cash-breakdown', [CashBreakdownController::class, 'index'])->name('cash-breakdown.index');
    Route::post('/cash-breakdown/generate', [CashBreakdownController::class, 'generate'])->name('cash-breakdown.generate');
    Route::post('/cash-breakdown/transactions', [CashBreakdownController::class, 'getCashBreakdown'])->name('cash-breakdown.transactions');
    // LINE 914:end CASH BREAKDOWN REPORT added by KEN 9-10-2026
    
    // CASH TURNOVER
    Route::middleware('role:1,2,5,6')->group(function () {
        Route::prefix('cash-turnover-approval')->name('cash-turnover-approval.')->group(function () {
            Route::get('/', [CashTurnoverApprovalController::class, 'index'])->name('index');
            Route::get('/breakdown', [CashTurnoverApprovalController::class, 'getBreakdown'])->name('breakdown');
            Route::post('/approve', [CashTurnoverApprovalController::class, 'approve'])->name('approve');
            Route::post('/reject', [CashTurnoverApprovalController::class, 'reject'])->name('reject');
            Route::post('/save-counts', [CashTurnoverApprovalController::class, 'saveCounts'])->name('save-counts');
        });
    });

    // Sale Payment page + data endpoint
    Route::get('/admin/accounting-reports/sale-payment', [PaymentCollectController::class, 'salePaymentPage'])
        ->name('admin.accounting-reports.sale-payment');
    Route::get('/admin/accounting-reports/sale-payment/data', [PaymentCollectController::class, 'salePayment'])
        ->name('admin.accounting-reports.sale-payment.data');

    // AR Collection page + data endpoint
    Route::get('/admin/accounting-reports/ar-collection', [PaymentCollectController::class, 'arCollectionPage'])
        ->name('admin.accounting-reports.ar-collection');
    Route::get('/admin/accounting-reports/ar-collection/data', [PaymentCollectController::class, 'arCollection'])
        ->name('admin.accounting-reports.ar-collection.data');  
});

Route::view('error', 'error');

require __DIR__.'/auth.php';
