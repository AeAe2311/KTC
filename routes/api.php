<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Menu;
use App\Http\Controllers\ApiController;


// harold 
use App\Http\Controllers\SyncApiAndroidController;  // harold 12-29
use App\Http\Controllers\ApiAndroidtokenController; // harold 12-29
use App\Http\Controllers\ApiAndroidGetDataController; // harold 12-29
// harold End
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/kitchen', [ApiController::class, 'kitchen'])->name('api.kitchen');
Route::get('/bar', [ApiController::class, 'bar'])->name('api.bar');

Route::get('/zreading', [ApiController::class, 'printzreading'])->name('api.printzreading');
Route::get('/zreadingDiscount', [ApiController::class, 'zreadingDiscount'])->name('api.zreadingDiscount');
Route::get('/zreadingstatus', [ApiController::class, 'zreadingstatus'])->name('api.zreadingstatus');
Route::get('/zread/edit/{slug}', [ApiController::class, 'editzRead']);

Route::get('/ordered/edit/{slug}', [ApiController::class, 'edit']);
Route::get('/ordered/{slug}', [ApiController::class, 'ordered']);
Route::get('/payment/{slug}', [ApiController::class, 'payment']);
Route::get('/senior/{slug}', [ApiController::class, 'senior']);
Route::get('/setting', [ApiController::class, 'setting']);
Route::get('/trans', [ApiController::class, 'trans']);
Route::get('/trans/edit/{slug}', [ApiController::class, 'editReceipt']);

Route::get('/dashboard-apiIndex', [ApiController::class, 'apiIndex']);
Route::get('/dashboard-apichange', [ApiController::class, 'apichange']);

// API dahon to Pamahaw
Route::get('/apiInv', [ApiController::class, 'apiInv']);
Route::get('/ApiTransferIndex', [ApiController::class, 'ApiTransferIndex']);
Route::get('/ApiTransferEdit/{slug}', [ApiController::class, 'ApiTransferEdit']);
Route::get('/ApiApproval/{slug}', [ApiController::class, 'ApiApproval']);
// Route::get('/apiInvView', [ApiController::class, 'apiInvView']);
Route::get('/transferApprovedIndex', [ApiController::class, 'transferApprovedIndex']);
Route::get('/ApiTransferReceived/{slug}', [ApiController::class, 'ApiTransferReceived']);
Route::get('/ApiReceivedIndex', [ApiController::class, 'ApiReceivedIndex']);
Route::get('/ApiTransferReceivedReview/{slug}', [ApiController::class, 'ApiTransferReceivedReview']);
// end of API dahon to Pamahaw

// AUTOMATIC API
Route::get('/ApprovalApi/{slug}', [ApiController::class, 'ApprovalApi']);
Route::get('/ApiApprovalChecker', [ApiController::class, 'ApiApprovalChecker']);
Route::get('/ReceivedApi/{slug}', [ApiController::class, 'ReceivedApi']);
Route::get('/ApproveQtyUpdate', [ApiController::class, 'ApproveQtyUpdate']);
Route::get('/ReceiveQtyUpdate', [ApiController::class, 'ReceiveQtyUpdate']);
// Route::get('/ApproveIncQty/{slug}', [ApiController::class, 'ApproveIncQty']);
// END OF AUTOMATIC API







//harold API 12-29
//Sync
Route::post('/sync_transaction', [SyncApiAndroidController::class, 'sync_transaction']);
Route::post('/sync_account_receivables', [SyncApiAndroidController::class, 'sync_account_receivables']); // 2 - 28 harold
Route::post('/sync_payment_logs', [SyncApiAndroidController::class, 'sync_payment_logs']); // 4 - 19 harold
Route::post('/sync_ordered', [SyncApiAndroidController::class, 'sync_ordered']);
Route::post('/sync_menus', [SyncApiAndroidController::class, 'sync_menus']);
Route::post('/sync_users', [SyncApiAndroidController::class, 'sync_users']);
Route::post('/sync_menucategory', [SyncApiAndroidController::class, 'sync_menucategory']);
Route::post('/sync_department', [SyncApiAndroidController::class, 'sync_department']);
Route::post('/sync_tables', [SyncApiAndroidController::class, 'sync_tables']);
Route::post('/sync_position', [SyncApiAndroidController::class, 'sync_position']);
Route::post('/sync_staff', [SyncApiAndroidController::class, 'sync_staff']);
Route::post('/sync_settings', [SyncApiAndroidController::class, 'sync_settings']);
Route::post('/sync_expenses', [SyncApiAndroidController::class, 'sync_expenses']);
Route::post('/sync_ingredients', [SyncApiAndroidController::class, 'sync_ingredients']);
Route::post('/sync_online_banking', [SyncApiAndroidController::class, 'sync_online_banking']);  
Route::post('/sync_online_banking_refund', [SyncApiAndroidController::class, 'sync_online_banking_refund']);  // 2 - 21 harold
Route::post('/sync_return_bottle_trans', [SyncApiAndroidController::class, 'sync_return_bottle_trans']);  
Route::post('/sync_void_item', [SyncApiAndroidController::class, 'sync_void_item']);   // 2 - 21 harold
Route::post('/sync_transaction_refund', [SyncApiAndroidController::class, 'sync_transaction_refund']);   // 2 - 24 harold
Route::post('/sync_ordered_refund', [SyncApiAndroidController::class, 'sync_ordered_refund']);   // 2 - 24 harold


Route::post('/sync_sales_report_transaction', [SyncApiAndroidController::class, 'sync_sales_report_transaction']);   // 7-18 harold
Route::post('/sync_sales_report_mop', [SyncApiAndroidController::class, 'sync_sales_report_mop']);   // 7-18 harold
Route::post('/sync_transactions_duplicate', [SyncApiAndroidController::class, 'sync_transactions_duplicate']);   // 7-18 harold
Route::post('/sync_ordered_duplicate', [SyncApiAndroidController::class, 'sync_ordered_duplicate']);   // 8-29 harold
// Token
Route::get('/keytoken', [ApiAndroidtokenController::class, 'keytoken']);  
Route::get('/updatetoken', [ApiAndroidtokenController::class, 'updatetoken']);  
Route::get('/get_username', [ApiAndroidtokenController::class, 'get_username']);
Route::get('/update_username', [ApiAndroidtokenController::class, 'update_username']);
Route::get('/updateaccinfo', [ApiAndroidtokenController::class, 'updateaccinfo']);  // 2 - 21 



//get Data
Route::get('/getData_menu', [ApiAndroidGetDataController::class, 'getData_menu']); 
Route::get('/getData_menu_update', [ApiAndroidGetDataController::class, 'getData_menu_update']); 

Route::get('/refund_qty', [ApiAndroidGetDataController::class, 'refund_qty']); // harold 2-28

Route::get('/get_branch', [ApiAndroidGetDataController::class, 'get_branch']); 
Route::get('/get_online_banking_list', [ApiAndroidGetDataController::class, 'get_online_banking_list']); 


Route::get('/getData_table', [ApiAndroidGetDataController::class, 'getData_table']); 
Route::get('/getData_categories', [ApiAndroidGetDataController::class, 'getData_categories']); 
Route::get('/getData_department', [ApiAndroidGetDataController::class, 'getData_department']); 
Route::get('/getData_brand', [ApiAndroidGetDataController::class, 'getData_brand']); 
Route::get('/getData_mop', [ApiAndroidGetDataController::class, 'getData_mop']); 


//harold API End

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


