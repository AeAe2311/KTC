<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Log;


class SyncApiAndroidController extends Controller
{




     // ----------------------------------- Transaction -----------------------------//
    public function sync_transaction(Request $request)
    { 

        try{

        
        $data = $request->all(); 
        $id = $data['id']; 
        
        $user_uuid = $data['user_uuid'];
        $user_cloud_id = $data['user_cloud_id'];
        $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
        if (!$check_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ]);
        }
        
        $get_store_code = $data['store_code'];
        $get_created_at = $data['created_at'];

        // $get_uuid = $check_user->is_uuid;    // remove 8/14 harold for sync checking
        // if($get_uuid !== $user_uuid){
        //     return response()->json([
        //      'status' => 'error',
        //      'message' => 'wrong is_uuid',
        //     ], 500); 
        // }
            //  if($get_store_code == 'store-012026006-000007'){
                //   Log::info("Sync Reach --------- $get_store_code");
            //  }

           $getdata = DB::table('transactions')->where('is_uuid', $data['is_uuid'])->first();
            if ($getdata != null) {
                DB::table('transactions')->where('is_uuid', $data['is_uuid'])->update([
                    'number'                 => $data['number'],
                    'total'                  => $data['total'],
                    'mode_of_payment'        => $data['mode_of_payment'],
                    'amount'                 => $data['amount'],
                    'fee'                 => $data['fee'],
                    'service_charge'              => $data['sc_amount'],
                    'sc_percent'             => $data['sc_percent'] ?? 0,
                    'pwd'             => $data['pwd_amount'],
                    'customer_with_discount' => $data['customer_with_discount'],
                    'total_customer'         => $data['total_customer'],
                    'lime_discount' => $data['owner_discount_percent'] ?? 0,
                    'ld_value'   => $data['owner_discount_value'] ?? 0.00,
                    'payor_name'             => $data['payor_name'],
                    'payor_number'           => $data['payor_number'],
                    'lessVatDeduction'       => $data['lessVatDeduction'] ?? 0.00,
                    'vat'                    => $data['vat'] ?? 0.00,
                    'vatSales'               => $data['vatSales'] ?? 0.00,
                    'vatExemptSales'         => $data['vatExemptSales'] ?? 0.00,
                    'type'                   => $data['type'],
                    'is_vat'                 => $data['is_vat'],
                    'table'                  => $data['table_id'],
                    'status'                 => $data['status'],
                    'order_status'           => $data['order_status'],
                    'order_remarks'          => $data['order_remarks'] ?? null,
                    'cashier'                => $data['cashier'],
                    'staff_id'               => $data['staff_id'],
                    'slug'                   => $data['slug'],
                    'is_app'                 => 1,
                    'created_at'             => $data['created_at'],
                    'updated_at'             => now(),
                    'is_uuid'                => $data['is_uuid'],
                    'is_sync'                => 1,
                    'is_branch'              => '1',
                    
                ]);
            } else {              
                DB::table('transactions')->insert([
                    'number'                 => $data['number'],
                    'total'                  => $data['total'],
                    'mode_of_payment'        => $data['mode_of_payment'],
                    'amount'                 => $data['amount'],
                    'fee'                 => $data['fee'],
                    'service_charge'              => $data['sc_amount'],
                    'sc_percent'             => $data['sc_percent'] ?? 0,
                    'pwd'             => $data['pwd_amount'],
                    'customer_with_discount' => $data['customer_with_discount'],
                    'total_customer'         => $data['total_customer'],
                    'lime_discount' => $data['owner_discount_percent'] ?? 0,
                    'ld_value'   => $data['owner_discount_value'] ?? 0.00,
                    'payor_name'             => $data['payor_name'],
                    'payor_number'           => $data['payor_number'],
                    'lessVatDeduction'       => $data['lessVatDeduction'] ?? 0.00,
                    'vat'                    => $data['vat'] ?? 0.00,
                    'vatSales'               => $data['vatSales'] ?? 0.00,
                    'vatExemptSales'         => $data['vatExemptSales'] ?? 0.00,
                    'type'                   => $data['type'],
                    'is_vat'                 => $data['is_vat'],
                    'table'                  => $data['table_id'],
                    'status'                 => $data['status'],
                    'order_status'           => $data['order_status'],
                    'order_remarks'          => $data['order_remarks'] ?? null,
                    'cashier'                => $data['cashier'],
                    'staff_id'               => $data['staff_id'],
                    'slug'                   => $data['slug'],
                    'is_app'                 => 1,
                    'created_at'             => $data['created_at'],
                    'updated_at'             => now(),
                    'is_uuid'                => $data['is_uuid'],
                    'is_sync'                => 1,
                    'is_branch'              => '1',
                    'branch_code' => $data['branch_code'],
                    'store_code' => $data['store_code'],
                    'local_id'    => $id,
                ]);
            }
            //  if($get_store_code == 'store-012026006-000007'){
                //   Log::info("Sync successfully Created At: $get_created_at Id: $id --------- $get_store_code");
            //  }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]);   
        }catch (\Exception $e) {
        // Return error details
        // if($get_store_code == 'store-012026006-000007'){
                $message = $e->getMessage();
                $line = $e->getTraceAsString();
                
               Log::info("$e --------- $get_store_code");
               Log::info("$line ------------ $get_store_code");
        // }

          
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }

    }
    
    // -------------------------------- payment_logs ------------------------------ //
    
     public function sync_payment_logs(Request $request)
    { 

        try{

        
        $data = $request->all(); 
        $id = $data['id']; 
        
        $user_uuid = $data['user_uuid'];
        $user_cloud_id = $data['user_cloud_id'];
        $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
        if (!$check_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ]);
        }

        // $get_uuid = $check_user->is_uuid;    // remove 8/14 harold for sync checking
        // if($get_uuid !== $user_uuid){
        //     return response()->json([
        //      'status' => 'error',
        //      'message' => 'wrong is_uuid',
        //     ], 500); 
        // }

           $getdata = DB::table('payment_logs')->where('is_uuid', $data['is_uuid'])->first();
            if ($getdata != null) {
                DB::table('payment_logs')->where('is_uuid', $data['is_uuid'])->update([
                        'tran_id'         => $data['tran_id'],
                        'tran_number'     => $data['tran_number'],
                        'mop_id'          => $data['mop_id'],
                        'label'           => $data['label'],
                        'total'           => $data['total'],
                        'amount'          => $data['amount'],
                        'balance'         => $data['balance'],
                        'cashier_id'      => $data['cashier_id'],
                        'type'            => $data['type'],
                        'created_at'      => $data['created_at'],
                        'updated_at'      => $data['updated_at'],
                        'is_deleted'      => $data['is_deleted'],
                        'is_uuid'         => $data['is_uuid'],
                        'is_sync'         => $data['is_sync'],
                        'store_code'      => $data['store_code'],
                        'branch_code'     => $data['branch_code'],
                        'local_id'     => $data['id'],
                        'ref_no'         => $data['ref_no'] ?? '',
                        'bank_name'         => $data['bank_name'] ?? '',
                        'client_name'      => $data['client_name'] ?? '',
                        'payment_date'     => $data['payment_date'] ?? '',
                        'is_void'     => $data['id'] ?? 0,
                                        
                ]);
            } else {              
                    DB::table('payment_logs')->insert([
                        'tran_id'         => $data['tran_id'],
                        'tran_number'     => $data['tran_number'],
                        'mop_id'          => $data['mop_id'],
                        'label'           => $data['label'],
                        'total'           => $data['total'],
                        'amount'          => $data['amount'],
                        'balance'         => $data['balance'],
                        'cashier_id'      => $data['cashier_id'],
                        'type'            => $data['type'],
                        'created_at'      => $data['created_at'],
                        'updated_at'      => $data['updated_at'],
                        'is_deleted'      => $data['is_deleted'],
                        'is_uuid'         => $data['is_uuid'],
                        'is_sync'         => $data['is_sync'],
                        'store_code'      => $data['store_code'],
                        'branch_code'     => $data['branch_code'],
                        'local_id'     => $data['id'],
                        'ref_no'         => $data['ref_no'] ?? '',
                        'bank_name'         => $data['bank_name'] ?? '',
                        'client_name'      => $data['client_name'] ?? '',
                        'payment_date'     => $data['payment_date'] ?? '',
                        'is_void'     => $data['id'] ?? 0,
                    ]);
                }
                return response()->json([
                'status' => 'success', 'id' => $id
                ]);   
            }catch (\Exception $e) {
            // Return error details
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }

    }
    
    
    
    // -------------------------------- payment_logs end------------------------------ //



    
    // harold 3-28 
     // ----------------------------------- Ar  -----------------------------//
    public function sync_account_receivables(Request $request)
    { 

        try{

        
        $data = $request->all(); 
        $id = $data['id']; 
        
        $user_uuid = $data['user_uuid'];
        $user_cloud_id = $data['user_cloud_id'];
        $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
        if (!$check_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ]);
        }

        $get_uuid = $check_user->is_uuid;
        if($get_uuid !== $user_uuid){
            return response()->json([
             'status' => 'error',
             'message' => 'wrong is_uuid',
            ], 500); 
        }

           $getdata = DB::table('account_receivables')->where('is_uuid', $data['is_uuid'])->first();
            if ($getdata != null) {
                DB::table('account_receivables')->where('is_uuid', $data['is_uuid'])->update([
                    'tran_id'                => $data['tran_id'],
                        'number'                 => $data['number'],
                        'total'                  => $data['total'],
                        'balance'                  => $data['balance'],
                        'mode_of_payment'        => $data['mode_of_payment'],
                        'amount'                 => $data['amount'],
                        'service_charge'              => $data['sc_amount'],
                        'sc_percent'             => $data['sc_percent'] ?? 0,
                        'pwd'             => $data['pwd_amount'],
                        'customer_with_discount' => $data['customer_with_discount'],
                        'total_customer'         => $data['total_customer'],
                        'lime_discount' => $data['owner_discount_percent'] ?? 0,
                        'ld_value'   => $data['owner_discount_value'] ?? 0.00,
                        'payor_name'             => $data['payor_name'],
                        'payor_number'           => $data['payor_number'],
                        'lessVatDeduction'       => $data['lessVatDeduction'] ?? 0.00,
                        'vat'                    => $data['vat'] ?? 0.00,
                        'vatSales'               => $data['vatSales'] ?? 0.00,
                        'vatExemptSales'         => $data['vatExemptSales'] ?? 0.00,
                        'type'                   => $data['type'],
                       
                        'table'                  => $data['table_id'],
                        'status'                 => $data['status'],
                        'order_status'           => $data['order_status'],
                        'order_remarks'          => $data['order_remarks'] ?? null,
                        'cashier'                => $data['cashier'],
                        'slug'                   => $data['slug'],
                        'tran_date'              => $data['created_at'],
                        'is_app'                 => 1,
                        'created_at'             => $data['created_at'],
                        'updated_at'             => now(),
                        'is_uuid'                => $data['is_uuid'],
                        'is_sync'                => 1,
                        'branch'              => '1',
                        'branch_code' => $data['branch_code'],
                        'store_code' => $data['store_code'],
                        'local_id'    => $id,
                    
                ]);
            } else {              
                    DB::table('account_receivables')->insert([
                        'tran_id'                => $data['tran_id'],
                        'number'                 => $data['number'],
                        'total'                  => $data['total'],
                        'balance'                  => $data['balance'],
                        'mode_of_payment'        => $data['mode_of_payment'],
                        'amount'                 => $data['amount'],
                        'service_charge'              => $data['sc_amount'],
                        'sc_percent'             => $data['sc_percent'] ?? 0,
                        'pwd'             => $data['pwd_amount'],
                        'customer_with_discount' => $data['customer_with_discount'],
                        'total_customer'         => $data['total_customer'],
                        'lime_discount' => $data['owner_discount_percent'] ?? 0,
                        'ld_value'   => $data['owner_discount_value'] ?? 0.00,
                        'payor_name'             => $data['payor_name'],
                        'payor_number'           => $data['payor_number'],
                        'lessVatDeduction'       => $data['lessVatDeduction'] ?? 0.00,
                        'vat'                    => $data['vat'] ?? 0.00,
                        'vatSales'               => $data['vatSales'] ?? 0.00,
                        'vatExemptSales'         => $data['vatExemptSales'] ?? 0.00,
                        'type'                   => $data['type'],
                        
                        'table'                  => $data['table_id'],
                        'status'                 => $data['status'],
                        'order_status'           => $data['order_status'],
                        'order_remarks'          => $data['order_remarks'] ?? null,
                        'cashier'                => $data['cashier'],
                        'slug'                   => $data['slug'],
                        'tran_date'              => $data['created_at'],
                        'is_app'                 => 1,
                        'created_at'             => $data['created_at'],
                        'updated_at'             => now(),
                        'is_uuid'                => $data['is_uuid'],
                        'is_sync'                => 1,
                        'branch'              => '1',
                        'branch_code' => $data['branch_code'],
                        'store_code' => $data['store_code'],
                        'local_id'    => $id,
                    ]);
                }
                return response()->json([
                'status' => 'success', 'id' => $id
                ]);   
            }catch (\Exception $e) {
            // Return error details
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }

    }
     // ----------------------------------- Ar end  -----------------------------//
          // harold 3-28 end
    
          // harold 2-24
        // ----------------------------------- Transaction  Refund -----------------------------//
    public function sync_transaction_refund(Request $request)
    { 

        try{

        
        $data = $request->all(); 
        $id = $data['id']; 
        
        $user_uuid = $data['user_uuid'];
        $user_cloud_id = $data['user_cloud_id'];
        $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
        if (!$check_user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ]);
        }

        $get_uuid = $check_user->is_uuid;
        if($get_uuid !== $user_uuid){
            return response()->json([
             'status' => 'error',
             'message' => 'wrong is_uuid',
            ], 500); 
        }

           $getdata = DB::table('transactions_refund')->where('is_uuid', $data['is_uuid'])->first();
            if ($getdata != null) {
                DB::table('transactions_refund')->where('is_uuid', $data['is_uuid'])->update([
                    'transaction_id'         => $data['transaction_id'],
                    'number'                 => $data['number'],
                    'total'                  => $data['total'],
                    'mode_of_payment'        => $data['mode_of_payment'] ?? 0,
                    'amount'                 => $data['amount'] ?? 0,
                    'fee'                 => $data['fee'] ?? 0,
                    'service_charge'              => $data['sc_amount'] ?? 0,
                    'sc_percent'             => $data['sc_percent'] ?? 0,
                    'pwd'             => $data['pwd_amount'] ?? 0,
                    'customer_with_discount' => $data['customer_with_discount'] ?? 0,
                    'total_customer'         => $data['total_customer'] ?? 0,
                    'lime_discount' => $data['owner_discount_percent'] ?? 0,
                    'ld_value'   => $data['owner_discount_value'] ?? 0.00,
                    'payor_name'             => $data['payor_name'] ?? 0,
                    'payor_number'           => $data['payor_number'] ?? 0,
                    'lessVatDeduction'       => $data['lessVatDeduction'] ?? 0.00,
                    'vat'                    => $data['vat'] ?? 0.00,
                    'vatSales'               => $data['vatSales'] ?? 0.00,
                    'vatExemptSales'         => $data['vatExemptSales'] ?? 0.00,
                    'type'                   => $data['type'] ?? 0,
                    'is_vat'                 => $data['is_vat'] ?? 0,
                    'table'                  => $data['table_id'] ?? 0,
                    'status'                 => $data['status'] ?? 0,
                    'order_status'           => $data['order_status'],
                    'order_remarks'          => $data['order_remarks'] ?? null,
                    'cashier'                => $data['cashier'],
                    'staff_id'               => $data['staff_id'],
                    'slug'                   => $data['slug'],
                    'is_app'                 => 1,
                    'created_at'             => $data['created_at'],
                    'updated_at'             => now(),
                    'is_uuid'                => $data['is_uuid'],
                    'is_sync'                => 1,
                    'is_branch'              => '1',
                    
                ]);
            } else {              
                DB::table('transactions_refund')->insert([
                    'transaction_id'         => $data['transaction_id'],
                    'number'                 => $data['number'],
                    'total'                  => $data['total'] ?? 0,
                    'mode_of_payment'        => $data['mode_of_payment'] ?? 0,
                    'amount'                 => $data['amount'] ?? 0,
                    'fee'                 => $data['fee'],
                    'service_charge'              => $data['sc_amount'],
                    'sc_percent'             => $data['sc_percent'] ?? 0,
                    'pwd'             => $data['pwd_amount']  ?? 0,
                    'customer_with_discount' => $data['customer_with_discount']  ?? 0,
                    'total_customer'         => $data['total_customer']  ?? 0,
                    'lime_discount' => $data['owner_discount_percent'] ?? 0,
                    'ld_value'   => $data['owner_discount_value'] ?? 0.00,
                    'payor_name'             => $data['payor_name'],
                    'payor_number'           => $data['payor_number'],
                    'lessVatDeduction'       => $data['lessVatDeduction'] ?? 0.00,
                    'vat'                    => $data['vat'] ?? 0.00,
                    'vatSales'               => $data['vatSales'] ?? 0.00,
                    'vatExemptSales'         => $data['vatExemptSales'] ?? 0.00,
                    'type'                   => $data['type']  ?? 0,
                    'is_vat'                 => $data['is_vat']  ?? 0,
                    'table'                  => $data['table_id']  ?? 0,
                    'status'                 => $data['status']  ?? 0,
                    'order_status'           => $data['order_status'],
                    'order_remarks'          => $data['order_remarks'] ?? null,
                    'cashier'                => $data['cashier']  ?? 0,
                    'staff_id'               => $data['staff_id']  ?? 0,
                    'slug'                   => $data['slug']  ?? 0,
                    'is_app'                 => 1,
                    'created_at'             => $data['created_at'],
                    'updated_at'             => now(),
                    'is_uuid'                => $data['is_uuid'],
                    'is_sync'                => 1,
                    'is_branch'              => '1',
                    'branch_code' => $data['branch_code'],
                    'store_code' => $data['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]);   
        }catch (\Exception $e) {
        // Return error details
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }

    }
   // harold 2-24 end

      // ----------------------------------- Transaction -----------------------------//
      
      
      
    public function sync_ordered(Request $request)
    { 

        try{

      
            $data = $request->all(); 
            $id = $data['id']; 
            
            $user_uuid = $data['user_uuid'];
            $user_cloud_id = $data['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            if (!$check_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ]);
            }
    
            // $get_uuid = $check_user->is_uuid;   // remove 8/14 harold for sync checking
            // if($get_uuid !== $user_uuid){
            //     return response()->json([
            //      'status' => 'error',
            //      'message' => 'wrong is_uuid',
            //     ], 500); 
            // }


           $getdata = DB::table('ordered')->where('is_uuid', $data['is_uuid'])->first();
            if ($getdata != null) {
                DB::table('ordered')->where('is_uuid', $data['is_uuid'])->update([
                    'tran_id'         => $data['tran_id'],
                    'menu_id'         => $data['menu_id'],
                    'quantity'        => $data['quantity'],
                    // 'price'           => $data['price'],
                    'amount'          => $data['amount'],
                    'discount_amount' => $data['discount_amount'],
                    'service_charge'  => $data['service_charge'],
                    'status'          => $data['status'],
                    'food_cost'       => $data['food_cost'],
                    // 'is_gift'         => $data['is_gift'],
                    // 'gift_amount'     => $data['gift_amount'],
                    // 'gift_type'       => $data['gift_type'],
                    'type'            => $data['type'],
                    'slug'            => $data['slug'],
                    'created_at'      => $data['created_at'],
                    'menu_cloud_id' => $data['cloud_id'],  // harold 9-5
                    'updated_at'      => now(),
                    // 'is_deduct'    => 0,  // harold 12-18
                    
                    

                ]);
            } else {              
                DB::table('ordered')->insert([
                    'tran_id'         => $data['tran_id'],
                    'menu_id'         => $data['menu_id'],
                    'quantity'        => $data['quantity'],
                    // 'price'           => $data['price'],
                    'amount'          => $data['amount'],
                    'discount_amount' => $data['discount_amount'],
                    'service_charge'  => $data['service_charge'],
                    'status'          => $data['status'],
                    'food_cost'       => $data['food_cost'],
                    // 'is_gift'         => $data['is_gift'],
                    // 'gift_amount'     => $data['gift_amount'],
                    // 'gift_type'       => $data['gift_type'],
                    'type'            => $data['type'],
                    'slug'            => $data['slug'],
                    'created_at'      => $data['created_at'],
                    'updated_at'      => now(),
                    'is_uuid'         => $data['is_uuid'],
                    'is_sync'         => 1,
                    
                    'price_type'      => '0',
                    'cashier'      => 0,
                    'role'      => 0,
                    'lime_discount'   => '0',
                    'is_pwd'      => 0,
                    'is_odisc'      => 0,
                    'remarks'      => '',
                    'print'      => '0',
                    'department'  => 0,
                    'branch'  => 0,
                    'for_delete'  => 0,
                    'is_split'  => 0,
                    'menu_uuid' => $data['menu_uuid'],  // harold 12-18       
                    'menu_cloud_id' => $data['cloud_id'],  // harold 9-5
                    'branch_code' => $data['branch_code'],
                    'store_code' => $data['store_code'],
                    'local_id'    => $id,
                    'is_deduct'    => 0,  // harold 12-18               
                    
                ]);
            }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]);          
          } catch (\Exception $e) {
        // Return error details
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
          
    }
    
    
    // harold 2-24
    ///  ----------------  Ordered Refund ----------------------- ///
    
     public function sync_ordered_refund(Request $request)
    { 

        try{

            $data = $request->all(); 
            $id = $data['id']; 
            
            $user_uuid = $data['user_uuid'];
            $user_cloud_id = $data['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            if (!$check_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ]);
            }
    
            $get_uuid = $check_user->is_uuid;
            if($get_uuid !== $user_uuid){
                return response()->json([
                 'status' => 'error',
                 'message' => 'wrong is_uuid',
                ], 500); 
            }


           $getdata = DB::table('ordered_refund')->where('is_uuid', $data['is_uuid'])->first();
            if ($getdata != null) {
                DB::table('ordered_refund')->where('is_uuid', $data['is_uuid'])->update([
                    'tran_id'         => $data['tran_id'],
                    'tran_id_refund'       => $data['tran_id_refund'],
                    'menu_id'         => $data['menu_id'],
                    'quantity'        => $data['quantity'],
                    'price'           => $data['price'],
                    'amount'          => $data['amount'] ?? 0,
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'service_charge'  => $data['service_charge'] ?? 0,
                    'status'          => $data['status'] ?? 0,
                    'label'          => $data['label'] ?? 0,
                    'food_cost'       => $data['food_cost'] ?? 0,
                    // 'is_gift'         => $data['is_gift'],
                    // 'gift_amount'     => $data['gift_amount'],
                    // 'gift_type'       => $data['gift_type'],
                    'type'            => $data['type'] ?? 0,
                    'slug'            => $data['slug'],
                    'created_at'      => $data['created_at'],
                    'updated_at'      => now(),
                    // 'is_deduct'    => 0,  // harold 12-18
                    
                    

                ]);
            } else {              
                DB::table('ordered_refund')->insert([
                    'tran_id'         => $data['tran_id'],
                    'tran_id_refund'       => $data['tran_id_refund'],
                    'menu_id'         => $data['menu_id'],
                    'quantity'        => $data['quantity'],
                    'price'           => $data['price'],
                    'amount'          => $data['amount'] ?? 0,
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'service_charge'  => $data['service_charge']?? 0,
                    'status'          => $data['status']?? 0,
                    'label'          => $data['label'] ?? 0,
                    'food_cost'       => $data['food_cost'] ?? 0,
                    // 'is_gift'         => $data['is_gift'],
                    // 'gift_amount'     => $data['gift_amount'],
                    // 'gift_type'       => $data['gift_type'],
                    'type'            => $data['type'] ?? 0,
                    'slug'            => $data['slug'],
                    'created_at'      => $data['created_at'],
                    'updated_at'      => now(),
                    'is_uuid'         => $data['is_uuid'],
                    'is_sync'         => 1,
                    
                    'price_type'      => '0',
                    'cashier'      => 0,
                    'role'      => 0,
                    'lime_discount'   => '0',
                    'is_pwd'      => 0,
                    'is_odisc'      => 0,
                    'remarks'      => '',
                    'print'      => '0',
                    'department'  => 0,
                    'branch'  => 0,
                    'for_delete'  => 0,
                    'is_split'  => 0,
                    'menu_uuid' => $data['menu_uuid'],  // harold 12-18        
                    'branch_code' => $data['branch_code'],
                    'store_code' => $data['store_code'],
                    'local_id'    => $id,
                    'is_return'    => 0,  // harold 12-18               
                    
                ]);
            }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]);          
          } catch (\Exception $e) {
        // Return error details
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
          
    }
    
    // ---------------- ordered refund end --------------------- //
    // harold 2-24 end
    
    
    
   //// --------------------- void item ----------------------------///   2-9 
   
      public function sync_void_item(Request $request)
    { 
        try{

            $data = $request->all(); 
            $id = $data['id']; 
            
            $user_uuid = $data['user_uuid'];
            $user_cloud_id = $data['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            // if (!$check_user) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'User not found',
            //     ]);
            // }
    
            // $get_uuid = $check_user->is_uuid;
            // if($get_uuid !== $user_uuid){
            //     return response()->json([
            //      'status' => 'error',
            //      'message' => 'wrong is_uuid',
            //     ], 500); 
            // }


           $getdata = DB::table('void_item')->where('is_uuid', $data['is_uuid'])->first();
            if ($getdata != null) {
                DB::table('void_item')->where('is_uuid', $data['is_uuid'])->update([
                    'tran_id'         => $data['tran_id'],
                    'menu_id'         => $data['menu_id'],
                    'quantity'        => $data['quantity'],
                    'price'           => $data['price'],
                    'amount'          => $data['amount'],
                    'status'          => $data['status'],
                    'menu_uuid'       => $data['menu_uuid'],    
                    'branch_code'     => $data['branch_code'],
                    'created_at'      => $data['created_at'],
                    'updated_at'      => now(),
                    'is_uuid'         => $data['is_uuid'],
                    'is_sync'         => 1,
                    'cloud_id'        => $data['cloud_id'],
                    'branch_code'     => $data['branch_code'],
                    'store_code'      => $data['store_code'],
                    'ordered_uuid'      => $data['ordered_uuid'],   // harold 9-5
                    'is_return'       => 0,  
                ]);
            } else {              
                DB::table('void_item')->insert([
                    'tran_id'         => $data['tran_id'],
                    'menu_id'         => $data['menu_id'],
                    'quantity'        => $data['quantity'],
                    'price'           => $data['price'],
                    'amount'          => $data['amount'],
                    'status'          => $data['status'],
                    'menu_uuid' => $data['menu_uuid'],    
                    'branch_code' => $data['branch_code'],
                    'created_at'      => $data['created_at'],
                    'updated_at'      => now(),
                    'is_uuid'         => $data['is_uuid'],
                    'is_sync'         => 1,
                    'cloud_id'         => $data['cloud_id'],
                    'branch_code' => $data['branch_code'],
                    'store_code' => $data['store_code'],
                    'ordered_uuid'      => $data['ordered_uuid'],   // harold 9-5
                    'is_return'    => 0,           
                    
                ]);
            }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]);          
          } catch (\Exception $e) {
        // Return error details
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
          
    }
   //// --------------------- void item  end----------------------------///



    // ----------------------------------- MENUS -----------------------------//
    public function sync_menus(Request $request)
    { 



        try {
        $data = $request->all(); 
        $id = $data['id']; 

            $filename = $data['image_name'] ?? 'menu_'.$data['id'].'.jpg';
            $path = public_path('images/menu/');
            if (!file_exists($path)) mkdir($path, 0755, true);
            file_put_contents($path . $filename, base64_decode($data['image']));

           $getmenus = DB::table('menus')->where('is_uuid', $data['is_uuid'])->first();
            if ($getmenus != null) {
                DB::table('menus')->where('is_uuid', $data['is_uuid'])->update([
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                    'department' => $data['department_id'],
                    // 'current_quantity' => $data['current_quantity'],
                    // 'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    // 'image' => 'images/menu/' . $filename,
                    'slug' => $data['slug'],
                    'created_at' => $data['created_at'],
                    'updated_at' => now(),
                    'servings' => $data['servings'],
                    'is_combo' => $data['is_combo'],
                    // 'is_gift' => $data['is_gift'],
                    // 'gift_amount' => $data['gift_amount'],
                    // 'gift_type' => $data['gift_type'],
                    'unit' => $data['unit'],
                    'reorder_point' => $data['reorder_point'],
                    'is_deleted' => $data['is_deleted'],
                    'is_sync' => 1,
                    'branch_code' => $data['branch_code'],  //12-14 harold
                    'local_id'    => $id,   //12-14 harold
                ]);
            } else {              
                DB::table('menus')->insert([
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                    'department' => $data['department_id'],
                    'current_quantity' => $data['current_quantity'],
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    'image' => 'images/menu/' . $filename,
                    'slug' => $data['slug'],
                    'created_at' => $data['created_at'],
                    'updated_at' => now(),
                    'servings' => $data['servings'],
                    'is_combo' => $data['is_combo'],
                    // 'is_gift' => $data['is_gift'],
                    // 'gift_amount' => $data['gift_amount'],
                    // 'gift_type' => $data['gift_type'],
                    'unit' => $data['unit'],
                    'reorder_point' => $data['reorder_point'],
                    'is_deleted' => $data['is_deleted'],
                    'is_uuid' => $data['is_uuid'],
                    'is_sync' => 1,
                    
                    'grab_price' => 0.0,
                    'panda_price' => 0.0,
                    'branch_id' => 0,
                    'is_discount' => 0,
                    'food_cost' => 0,
                    'client_token' => $data['token'],
                    'branch_code' => $data['branch_code'],
                    'store_code' => $data['store_code'],
                    'local_id'    => $id,
                    
                ]);
            }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]);   
             } catch (\Exception $e) {
                        // \Log::error('❌ Quantity Sync Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }           	
    }


 // ----------------------------------- MENUS -----------------------------//
    public function sync_menus_groceries(Request $request)
    { 



        try {
        $data = $request->all(); 
        $id = $data['id']; 

            $filename = $data['image_name'] ?? 'menu_'.$data['id'].'.jpg';
            $path = public_path('images/menu/');
            if (!file_exists($path)) mkdir($path, 0755, true);
            file_put_contents($path . $filename, base64_decode($data['image']));

           $getmenus = DB::table('menus_baguio')->where('is_uuid', $data['is_uuid'])->first();
            if ($getmenus != null) {
                DB::table('menus_baguio')->where('is_uuid', $data['is_uuid'])->update([
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                    'department' => $data['department_id'],
                    'current_quantity' => $data['current_quantity'],
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    'image' => 'images/menu/' . $filename,
                    'slug' => $data['slug'],
                    'created_at' => $data['created_at'],
                    'updated_at' => now(),
                    'servings' => $data['servings'],
                    'is_combo' => $data['is_combo'],
                    // 'is_gift' => $data['is_gift'],
                    // 'gift_amount' => $data['gift_amount'],
                    // 'gift_type' => $data['gift_type'],
                    'unit' => $data['unit'],
                    'reorder_point' => $data['reorder_point'],
                    'is_deleted' => $data['is_deleted'],
                    'is_sync' => 1,
                    'branch_code' => $data['branch_code'],  //12-14 harold
                    'local_id'    => $id,   //12-14 harold
                ]);
            } else {              
                DB::table('menus_baguio')->insert([
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                    'department' => $data['department_id'],
                    'current_quantity' => $data['current_quantity'],
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    'image' => 'images/menu/' . $filename,
                    'slug' => $data['slug'],
                    'created_at' => $data['created_at'],
                    'updated_at' => now(),
                    'servings' => $data['servings'],
                    'is_combo' => $data['is_combo'],
                    // 'is_gift' => $data['is_gift'],
                    // 'gift_amount' => $data['gift_amount'],
                    // 'gift_type' => $data['gift_type'],
                    'unit' => $data['unit'],
                    'reorder_point' => $data['reorder_point'],
                    'is_deleted' => $data['is_deleted'],
                    'is_uuid' => $data['is_uuid'],
                    'is_sync' => 1,
                    
                    'grab_price' => 0.0,
                    'panda_price' => 0.0,
                    'branch_id' => 0,
                    'is_discount' => 0,
                    'food_cost' => 0,
                    'client_token' => $data['token'],
                    'branch_code' => $data['branch_code'],
                    'store_code' => $data['store_code'],
                    'local_id'    => $id,
                    
                ]);
            }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]);   
             } catch (\Exception $e) {
                        // \Log::error('❌ Quantity Sync Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }           	
    }
    
  
    // ----------------------------------- USER -----------------------------//
    public function sync_users(Request $request)
    {
        $data = $request->all(); 
        $id = $data['id']; 
           $user = DB::table('users')->where('is_uuid', $data['is_uuid'])->first();
            if ($user != null) {
                DB::table('users')->where('is_uuid', $data['is_uuid'])->update([
                    'firstname' => $data['name'],
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'role' => $data['role'],
                    'slug' => $data['slug'],
                    'is_deleted' => $data['is_deleted'],
                    'created_at' => $data['created_at'],
                    'updated_at' => now(),
                    'is_sync' => 1,
                ]);
            } else {              
                DB::table('users')->insert([
                    'firstname' => $data['name'],
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'role' => $data['role'],
                    'slug' => $data['slug'],
                    'is_deleted' => $data['is_deleted'],
                    'created_at' => $data['created_at'],
                    'updated_at' => now(),
                    'is_uuid' => $data['is_uuid'],
                    'is_sync' => 1,
                    'branch_code'              =>$data['branch_code'],
                    'store_code' => $data['store_code'],
                    'local_id'              =>$data['id'],
                ]);
            }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]); 
    }


    
    // ----------------------------------- MENUCATEGORY-----------------------------//
    public function sync_menucategory(Request $request)
    {      
    try{
        
        $getdata = $request->all(); 
        $id = $getdata['id']; 
           $data = DB::table('menu_categories')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('menu_categories')->where('is_uuid', $getdata['is_uuid'])->update([
                    'name' => $getdata['name'],
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_hidden' => $getdata['is_hidden'],
                    'is_deleted' => $getdata['is_deleted'],
                    'is_sync' => 1,
                ]);
            } else {              
                DB::table('menu_categories')->insert([
                   'name' => $getdata['name'],
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'branch' => 1,
                    'updated_at' => now(),
                    'is_hidden' => $getdata['is_hidden'],
                    'is_deleted' => $getdata['is_deleted'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_sync' => 1,
                    'branch_code' =>$getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
            'status' => 'success', 'id' => $id
            ]); 
            
    } catch (\Exception $e) {
        // \Log::error('❌ Menu Category Sync Error: '.$e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
      }  
    }


    // ----------------------------------- DEPARTMENT-----------------------------//
    public function sync_department(Request $request)
    {
            $getdata = $request->all(); 
            $id = $getdata['id']; 

            $data = DB::table('departments')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('departments')->where('is_uuid', $getdata['is_uuid'])->update([
                    'name' => $getdata['name'],
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    // 'is_hidden' => $getdata['is_hidden'],
                    'is_deleted' => $getdata['is_deleted'],
                    'is_sync' => 1,
                ]);
            } else {
                DB::table('departments')->insert([
                    'name' => $getdata['name'],
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    // 'is_hidden' => $getdata['is_hidden'],
                    'is_deleted' => $getdata['is_deleted'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);
        
    }

    // ----------------------------------- TABLES-----------------------------//
    public function sync_tables(Request $request)
    {
            $getdata = $request->all(); 
            $id = $getdata['id']; 

            $data = DB::table('tables')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('tables')->where('is_uuid', $getdata['is_uuid'])->update([
                    'name' => $getdata['name'],
                    'status' => $getdata['status'],
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_deleted' => $getdata['is_deleted'],
                    'is_sync' => 1,
                ]);
            } else {
                DB::table('tables')->insert([
                    'name' => $getdata['name'],
                    'status' => $getdata['status'],
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_deleted' => $getdata['is_deleted'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);
        
    }



    // ----------------------------------- POSITION -----------------------------//
    public function sync_position(Request $request)
    {
            $getdata = $request->all(); 
            $id = $getdata['id']; 

            $data = DB::table('positions')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('positions')->where('is_uuid', $getdata['is_uuid'])->update([
                    'name' => $getdata['name'],
                    'status' => 0,
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_deleted' => $getdata['is_deleted'],
                    'is_sync' => 1,
                ]);
            } else {
                DB::table('positions')->insert([
                    'name' => $getdata['name'],
                    'status' => 0,
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_deleted' => $getdata['is_deleted'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);
        
    }



       // ----------------------------------- STAFF -----------------------------//
    public function sync_staff(Request $request)
    {
            $getdata = $request->all(); 
            $id = $getdata['id']; 

            $data = DB::table('staff')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('staff')->where('is_uuid', $getdata['is_uuid'])->update([
                    'fname' => $getdata['fname'],
                    'mname' => $getdata['mname'],
                    'lname' => $getdata['lname'],
                    'sec_id' => $getdata['sec_id'],
                    'position_id' => $getdata['position_id'],
                    'salary' => $getdata['salary'],
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_deleted' => $getdata['is_deleted'],
                    'is_sync' => 1,
                ]);
            } else {
                DB::table('staff')->insert([
                    'fname' => $getdata['fname'],
                    'mname' => $getdata['mname'],
                    'lname' => $getdata['lname'],
                    'sec_id' => $getdata['sec_id'],
                    'position_id' => $getdata['position_id'],
                    'salary' => $getdata['salary'],
                    'slug' => $getdata['slug'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_deleted' => $getdata['is_deleted'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);
        
    }



    // -------------------------------------- EXPENSES --------------------------------------//

    public function sync_expenses(Request $request)
    {
            $getdata = $request->all(); 
            $id = $getdata['id']; 
            
            $user_uuid = $getdata['user_uuid'];
            $user_cloud_id = $getdata['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            if (!$check_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ]);
            }
    
            $get_uuid = $check_user->is_uuid;
            if($get_uuid !== $user_uuid){
                return response()->json([
                 'status' => 'error',
                 'message' => 'wrong is_uuid',
                ], 500); 
            }
        

            $data = DB::table('inventory_transactions')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('inventory_transactions')->where('is_uuid', $getdata['is_uuid'])->update([
                    'inv_id' => 0,
                    'category_id' => 1,
                    'quantity' => $getdata['quantity'],
                    'unit_price' => $getdata['amount'],
                    'amount' => $getdata['total_amount'],
                    'remarks' => $getdata['name'],
                    'user_id' => $getdata['user_id'],
                    'is_paid' => $getdata['is_paid'],
                    
                    'updated_at' => now(),
                    'is_deleted' => $getdata['is_deleted'],
                    'is_sync' => 1,
                ]);
            } else {
                DB::table('inventory_transactions')->insert([
                    'inv_id' => 0,
                    'type' => 'INCOMING',
                    'category_id' => 1,
                    'quantity_before' => 0,
                    'quantity' => $getdata['quantity'],
                    'quantity_after' => 0,
                    'unit_price' => $getdata['amount'],
                    'amount' => $getdata['total_amount'],
                    'remarks' => $getdata['name'],
                    'user_id' => $getdata['user_id'],
                    'is_paid' => $getdata['is_paid'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_uuid' => $getdata['is_uuid'],
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'is_deleted' => $getdata['is_deleted'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);
        
    }




     // ----------------------------------- SETTINGS -----------------------------//
    public function sync_settings(Request $request)
    {
             try {
            $getdata = $request->all(); 
            $id = $getdata['id']; 

            $data = DB::table('settings')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('settings')->where('is_uuid', $getdata['is_uuid'])->update([
                    'name' => $getdata['name'],
                    'address' => $getdata['address'],
                    'number' => $getdata['number'],
                    'logo' => $getdata['logo'] ?? '',
                    'footer' => $getdata['footer'],
                    'email' => $getdata['email'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => $getdata['updated_at'],
                    'buffer_margin' => $getdata['buffer_margin'],
                    'markup' => $getdata['markup'],
                    'price_adjustment' =>$getdata['price_adjustment'],
                    'staff_incentives' => $getdata['staff_incentives'],     
                    'vat' =>$getdata['tax'],
                    'discount' =>$getdata['discount'],
                    'sc' => $getdata['sc'],
                    'staff' => $getdata['staff'],
                    'api_link' => $getdata['apilik'] ?? '',
                    'void_code' => $getdata['code'] ?? 0,
                    'is_sync' => 1,

                ]);
            } else {
                DB::table('settings')->insert([
                    'name' => $getdata['name'],
                    'address' => $getdata['address'],
                    'number' => $getdata['number'],
                    'logo' => $getdata['logo'] ?? '',
                    'footer' => $getdata['footer'],
                    'email' => $getdata['email'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => $getdata['updated_at'],
                    'buffer_margin' => $getdata['buffer_margin'],
                    'markup' => $getdata['markup'],
                    'price_adjustment' =>$getdata['price_adjustment'],
                    'staff_incentives' => $getdata['staff_incentives'],     
                    'vat' =>$getdata['tax'],
                    'discount' =>$getdata['discount'],
                    'sc' => $getdata['sc'],
                    'staff' => $getdata['staff'],
                    'api_link' => $getdata['apilik'] ?? '',
                    'void_code' => $getdata['code'] ?? 0,
                    'is_uuid' => $getdata['is_uuid'],
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);


               } catch (\Exception $e) {
                        // \Log::error('❌ Department Sync Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }        
    }




//  // ----------------------------------- INGREDIENTS -----------------------------//
//       public function sync_ingredients(Request $request)
//     {
//              try {
//             $getdata = $request->all(); 
//             $id = $getdata['id']; 

//             $data = DB::table('ingredients')->where('is_uuid', $getdata['is_uuid'])->first();
//             if ($data != null) {
//                 DB::table('ingredients')->where('is_uuid', $getdata['is_uuid'])->update([
//                     'menu_id' => $getdata['menu_id'],
//                     'inventory_id' => $getdata['inventory_id'],
//                     'quantity' => $getdata['quantity'] ?? '',
//                     'unit' => $getdata['unit'],
//                     'updated_at' =>  now(),
//                     'computed_quantity' => $getdata['quantity'],
//                     'is_menu' => 1,
//                     'is_deleted' =>$getdata['is_deleted'], 
//                     'is_sync' => 1,

//                 ]);
//             } else {
//                 DB::table('ingredients')->insert([
//                     'menu_id' => $getdata['menu_id'],
//                     'inventory_id' => $getdata['inventory_id'],
//                     'quantity' => $getdata['quantity'] ?? '',
//                     'unit' => 'ea',
//                     'created_at' => $getdata['created_at'],
//                     'updated_at' =>  now(),
//                     'computed_quantity' => $getdata['quantity'],
//                     'is_menu' => 1,
//                     'is_deleted' =>$getdata['is_deleted'],
//                     'is_uuid' => $getdata['is_uuid'],     
//                     'is_sync' => 1,
//                     'branch_code' => $getdata['branch_code'],
//                     'local_id'    => $id,
//                 ]);
//             }
//             return response()->json([
//                 'status' => 'success',
//                 'id' => $id
//             ]);


//                } catch (\Exception $e) {
//                         \Log::error('❌ ingredients Sync Error: '.$e->getMessage());
//                         return response()->json([
//                             'status' => 'error',
//                             'message' => $e->getMessage(),
//                         ], 500);
//                     }        
//     }




 // ----------------------------------- INGREDIENTS -----------------------------//
   public function sync_ingredients(Request $request)
    {
            try {
            $getdata = $request->all(); 
            $id = $getdata['id']; 

            $data = DB::table('ingredients')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('ingredients')->where('is_uuid', $getdata['is_uuid'])->update([
                    'menu_id' => $getdata['menu_id'],
                    'inventory_id' => $getdata['inventory_id'],
                    'quantity' => $getdata['quantity'] ?? '',
                    'unit' => $getdata['unit'],
                    'updated_at' =>  now(),
                    'computed_quantity' => $getdata['quantity'],
                    'is_menu' => 1,
                    'is_deleted' =>$getdata['is_deleted'], 
                    'is_sync' => 1,

                ]);
            } else {
                DB::table('ingredients')->insert([
                    'menu_id' => $getdata['menu_id'],
                    'inventory_id' => $getdata['inventory_id'],
                    'quantity' => $getdata['quantity'] ?? '',
                    'unit' => 'ea',
                    'created_at' => $getdata['created_at'],
                    'updated_at' =>  now(),
                    'computed_quantity' => $getdata['quantity'],
                    'is_menu' => 1,
                    'is_deleted' =>$getdata['is_deleted'],
                    'is_uuid' => $getdata['is_uuid'],     
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);


               } catch (\Exception $e) {
                        // \Log::error('❌ ingredients Sync Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }        
    }


    

// ----------------------------------- ONLINE BANKING -----------------------------//
   public function sync_online_banking(Request $request)
    {
            try {
            $getdata = $request->all(); 
            $id = $getdata['id']; 
            
            $user_uuid = $getdata['user_uuid'];
            $user_cloud_id = $getdata['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            if (!$check_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ]);
            }
    
            $get_uuid = $check_user->is_uuid;
            if($get_uuid !== $user_uuid){
                return response()->json([
                 'status' => 'error',
                 'message' => 'wrong is_uuid',
                ], 500); 
            }

            $data = DB::table('online_banking_trans')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('online_banking_trans')->where('is_uuid', $getdata['is_uuid'])->update([
                    'trans' => $getdata['trans'],
                    'bank' => $getdata['bank'],
                    'amount' => $getdata['amount'],
                    'fee' => $getdata['fee'],
                    'function' => $getdata['function'],
                    'updated_at' =>  now(),
                    'is_refund' => $getdata['is_refund'], 
                    'is_deleted' =>$getdata['is_deleted'],    
                    'is_sync' => 1,

                ]);
            } else {
                DB::table('online_banking_trans')->insert([
                    'trans' => $getdata['trans'],
                    'bank' => $getdata['bank'],
                    'amount' => $getdata['amount'],
                    'fee' => $getdata['fee'],
                    'function' => $getdata['function'],
                    'cashier' => $getdata['cashier'],
                    'with_fee' => $getdata['cashier'],
                    'created_at' =>  $getdata['created_at'],
                    'updated_at' =>  now(),
                    'is_refund' => $getdata['is_refund'], 
                    'is_deleted' =>$getdata['is_deleted'],
                    'is_uuid' => $getdata['is_uuid'],     
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);


               } catch (\Exception $e) {
                        // \Log::error('❌ Online Banking Sync Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }        
    }

// ----------------------------------- ONLINE BANKING  REFUND-----------------------------//
 public function sync_online_banking_refund(Request $request)
    {
            try {
            $getdata = $request->all(); 
            $id = $getdata['id']; 
            
            $user_uuid = $getdata['user_uuid'];
            $user_cloud_id = $getdata['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            if (!$check_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ]);
            }
    
            $get_uuid = $check_user->is_uuid;
            if($get_uuid !== $user_uuid){
                return response()->json([
                 'status' => 'error',
                 'message' => 'wrong is_uuid',
                ], 500); 
            }

            $data = DB::table('online_banking_trans_refund')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('online_banking_trans_refund')->where('is_uuid', $getdata['is_uuid'])->update([
                    'trans_number' => $getdata['trans_number'],
                    'bank' => $getdata['bank'],
                    'amount' => $getdata['amount'],
                    'fee' => $getdata['fee'],
                    'function' => $getdata['function'],
                    'updated_at' =>  now(),
                    'is_refund' => $getdata['is_refund'], 
                    'is_deleted' =>$getdata['is_deleted'],    
                    'is_sync' => 1,

                ]);
            } else {
                DB::table('online_banking_trans_refund')->insert([
                    'trans_id' => $getdata['trans_id'],
                    'trans_number' => $getdata['trans_number'],
                    'bank' => $getdata['bank'],
                    'amount' => $getdata['amount'],
                    'fee' => $getdata['fee'],
                    'function' => $getdata['function'],
                    'cashier' => $getdata['cashier'],
                    'with_fee' => $getdata['cashier'],
                    'created_at' =>  $getdata['created_at'],
                    'updated_at' =>  now(),
                    'is_refund' => $getdata['is_refund'], 
                    'is_deleted' =>$getdata['is_deleted'],
                    'is_uuid' => $getdata['is_uuid'],     
                    'is_sync' => 1,
                    'branch_code' => $getdata['branch_code'],
                    'store_code' => $getdata['store_code'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);


               } catch (\Exception $e) {
                        // \Log::error('❌ Online Banking Sync Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }        
    }


    // ----------------------------------- RETURN BOTTLE -----------------------------//
   public function sync_return_bottle_trans(Request $request)
    {
            try {
            $getdata = $request->all(); 
            $id = $getdata['id']; 
            
            $user_uuid = $getdata['user_uuid'];
            $user_cloud_id = $getdata['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            if (!$check_user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ]);
            }
    
            $get_uuid = $check_user->is_uuid;
            if($get_uuid !== $user_uuid){
                return response()->json([
                 'status' => 'error',
                 'message' => 'wrong is_uuid',
                ], 500); 
            }
        

            $data = DB::table('return_bottle_trans')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('return_bottle_trans')->where('is_uuid', $getdata['is_uuid'])->update([
                    'tran_id' => $getdata['tran_id'],
                    'trans_number' => $getdata['trans_number'],
                    'quantity' => $getdata['quantity'] ?? '',
                    'quantity_return' => $getdata['quantity_return'] ?? '',
                    'amount_paid' => $getdata['amount_paid'],
                    'remarks' => $getdata['remarks'],
                    'status' => $getdata['status'],
                    'user_id' => $getdata['user_id'],
                    'created_at' =>  $getdata['created_at'],
                    'updated_at' => now(),
                    'is_hidden' =>$getdata['is_hidden'],   
                    'is_deleted' =>$getdata['is_deleted'], 
                    'is_uuid' =>$getdata['is_uuid'], 
                    'is_sync' => 1,    
                    'store_code' =>$getdata['store_code'], 
                    'branch_code' =>$getdata['branch_code'], 
                ]);
            } else {
                DB::table('return_bottle_trans')->insert([
                    'tran_id' => $getdata['tran_id'],
                    'trans_number' => $getdata['trans_number'],
                    'quantity' => $getdata['quantity'] ?? '',
                    'quantity_return' => $getdata['quantity_return'] ?? '',
                    'amount_paid' => $getdata['amount_paid'],
                    'remarks' => $getdata['remarks'],
                    'status' => $getdata['status'],
                    'user_id' => $getdata['user_id'],
                    'created_at' =>  $getdata['created_at'],
                    'updated_at' => now(),
                    'is_hidden' =>$getdata['is_hidden'],   
                    'is_deleted' =>$getdata['is_deleted'], 
                    'is_uuid' =>$getdata['is_uuid'], 
                    'is_sync' => 1,    
                    'store_code' =>$getdata['store_code'], 
                    'branch_code' =>$getdata['branch_code'], 
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);


               } catch (\Exception $e) {
                        // \Log::error('❌ Return Bottle Sync Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }        
    }

    //  public function sync_quantity(Request $request)
    // { 
    // try {
    //     $data = $request->all(); 
    //     $id = $data['id']; 
    //       $getmenus = DB::table('menus')->where('is_uuid', $data['is_uuid'])->first();
    //         if ($getmenus != null) {
    //             DB::table('menus')->where('is_uuid', $data['is_uuid'])->update([
    //                 'current_quantity' => $data['current_quantity'],
    //                 'quantity' => $data['quantity'],
    //                 'is_sync' => 1,
    //             ]);
    //         } else {              
    //             return response()->json([
    //             'status' => 'notexist'
    //             ]);     
    //         }

    //         return response()->json([
    //         'status' => 'success', 'id' => $id
    //         ]);       
            
    //          } catch (\Exception $e) {
    //                     \Log::error('❌ Quantity Sync Error: '.$e->getMessage());
    //                     return response()->json([
    //                         'status' => 'error',
    //                         'message' => $e->getMessage(),
    //                     ], 500);
    //                 }

    // }





// 7 - 18 harold sync report 
 public function sync_sales_report_transaction(Request $request)
    {
            try {
            $getdata = $request->all(); 
            $id = $getdata['id']; 
            
            $user_uuid = $getdata['user_uuid'];
            $user_cloud_id = $getdata['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            // if (!$check_user) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'User not found',
            //     ]);
            // }
    
            // $get_uuid = $check_user->is_uuid;
            // if($get_uuid !== $user_uuid){
            //     return response()->json([
            //      'status' => 'error',
            //      'message' => 'wrong is_uuid',
            //     ], 500); 
            // }
        

            $data = DB::table('sales_report_transaction')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('sales_report_transaction')->where('is_uuid', $getdata['is_uuid'])->update([
                    'trans_number' => $getdata['trans_number'],
                    'completed' => $getdata['completed'],
                    'ar' => $getdata['ar'],
                    'cancelled' => $getdata['cancelled'],
                    'paid_sales' => $getdata['paid_sales'],
                    'ar_unpaid' => $getdata['ar_unpaid'],
                    'sales_report_form' => $getdata['sales_report_form'],
                    'total_sales' => $getdata['total_sales'],
                    'ar_collection_total' => $getdata['ar_collection_total'],
                    'ar_collection_Form' => $getdata['ar_collection_Form'],
                    'is_user' => $getdata['is_user'],
                    'start_date' => $getdata['start_date'],
                    'end_date' => $getdata['end_date'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_hidden' => $getdata['is_hidden'],
                    'is_deleted' => $getdata['is_deleted'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_sync' => 1,
                    'store_code' => $getdata['store_code'],
                    'branch_code' => $getdata['branch_code'],
                    //  'branch_code' => $getdata['branch_code'],
                    'count_1000' => $getdata['count_1000'],
                    'count_500' => $getdata['count_500'],
                    'count_200' => $getdata['count_200'],
                    'count_100' => $getdata['count_100'],
                    'count_50' => $getdata['count_50'],
                    'count_20' => $getdata['count_20'],
                    'count_10' => $getdata['count_10'],
                    'count_5' => $getdata['count_5'],
                    'count_1' => $getdata['count_1'],
                    'count_50cents' => $getdata['count_50cents'],
                    'count_10cents' => $getdata['count_10cents'],
                    'count_5cents' => $getdata['count_5cents'],
                    'cash_variance' => $getdata['cash_variance'],
                    'remarks' => $getdata['remarks'],
                    'local_id'    => $id,
                ]);
            } else {
                DB::table('sales_report_transaction')->insert([
                    'trans_number' => $getdata['trans_number'],
                    'completed' => $getdata['completed'],
                    'ar' => $getdata['ar'],
                    'cancelled' => $getdata['cancelled'],
                    'paid_sales' => $getdata['paid_sales'],
                    'ar_unpaid' => $getdata['ar_unpaid'],
                    'sales_report_form' => $getdata['sales_report_form'],
                    'total_sales' => $getdata['total_sales'],
                    'ar_collection_total' => $getdata['ar_collection_total'],
                    'ar_collection_Form' => $getdata['ar_collection_Form'],
                    'is_user' => $getdata['is_user'],
                    'start_date' => $getdata['start_date'],
                    'end_date' => $getdata['end_date'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_hidden' => $getdata['is_hidden'],
                    'is_deleted' => $getdata['is_deleted'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_sync' => 1,
                    'store_code' => $getdata['store_code'],
                    'branch_code' => $getdata['branch_code'],
                    //  'branch_code' => $getdata['branch_code'],
                    'count_1000' => $getdata['count_1000'],
                    'count_500' => $getdata['count_500'],
                    'count_200' => $getdata['count_200'],
                    'count_100' => $getdata['count_100'],
                    'count_50' => $getdata['count_50'],
                    'count_20' => $getdata['count_20'],
                    'count_10' => $getdata['count_10'],
                    'count_5' => $getdata['count_5'],
                    'count_1' => $getdata['count_1'],
                    'count_50cents' => $getdata['count_50cents'],
                    'count_10cents' => $getdata['count_10cents'],
                    'count_5cents' => $getdata['count_5cents'],
                    'cash_variance' => $getdata['cash_variance'],
                    'remarks' => $getdata['remarks'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);


               } catch (\Exception $e) {
                        // \Log::error('❌ Sync Sales Report  Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }        
    }




 public function sync_sales_report_mop(Request $request)
    {
            try {
            $getdata = $request->all(); 
            $id = $getdata['id']; 
            
            $user_uuid = $getdata['user_uuid'];
            $user_cloud_id = $getdata['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
            // if (!$check_user) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'User not found',
            //     ]);
            // }
    
            // $get_uuid = $check_user->is_uuid;
            // if($get_uuid !== $user_uuid){
            //     return response()->json([
            //      'status' => 'error',
            //      'message' => 'wrong is_uuid',
            //     ], 500); 
            // }
        

            $data = DB::table('sales_report_mop')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('sales_report_mop')->where('is_uuid', $getdata['is_uuid'])->update([
                    'trans_id' => $getdata['trans_id'],
                    'trans_number' => $getdata['trans_number'],
                    'label' => $getdata['label'],
                    'amount' => $getdata['amount'],
                    'is_ar' => $getdata['is_ar'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_hidden' => $getdata['is_hidden'],
                    'is_deleted' => $getdata['is_deleted'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_sync' => 1,
                    'store_code' => $getdata['store_code'],
                    'branch_code' => $getdata['branch_code'],
                    'report_tran_uuid' => $getdata['report_tran_uuid'],
                    'local_id'    => $id,
                ]);
            } else {
                DB::table('sales_report_mop')->insert([
                    'trans_id' => $getdata['trans_id'],
                    'trans_number' => $getdata['trans_number'],
                    'label' => $getdata['label'],
                    'amount' => $getdata['amount'],
                    'is_ar' => $getdata['is_ar'],
                    'is_uuid' => $getdata['is_uuid'],
                    'is_hidden' => $getdata['is_hidden'],
                    'is_deleted' => $getdata['is_deleted'],
                    'created_at' => $getdata['created_at'],
                    'updated_at' => now(),
                    'is_sync' => 1,
                    'store_code' => $getdata['store_code'],
                    'branch_code' => $getdata['branch_code'],
                    'report_tran_uuid' => $getdata['report_tran_uuid'],
                    'local_id'    => $id,
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);


               } catch (\Exception $e) {
                        // \Log::error('❌ Sync Sales Report MOP Error: '.$e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ], 500);
                    }        
    }
    
    
    
     public function sync_transactions_duplicate(Request $request)
    {
            try {
            $getdata = $request->all(); 
            $id = $getdata['id']; 
            
            $user_uuid = $getdata['user_uuid'];
            $user_cloud_id = $getdata['user_cloud_id'];
            $check_user = DB::table('users')->where('id', $user_cloud_id)->first();

            $data = DB::table('sales_report_transactions_duplicate')->where('is_uuid', $getdata['is_uuid'])->first();
            if ($data != null) {
                DB::table('sales_report_transactions_duplicate')->where('is_uuid', $getdata['is_uuid'])->update([
                    'report_id'              => $getdata['report_id'],
                    'number'                 => $getdata['number'],
                    'total'                  => $getdata['total'],
                    'mode_of_payment_id'        => $getdata['mode_of_payment_id'],
                    'mode_of_payment'        => $getdata['mode_of_payment'],
                    'amount'                 => $getdata['amount'],
                    'fee'                 => $getdata['fee'],
                    'sc_amount'              => $getdata['sc_amount'],
                    'sc_percent'             => $getdata['sc_percent'] ?? 0,
                    'pwd_amount'             => $getdata['pwd_amount'],
                    'customer_with_discount' => $getdata['customer_with_discount'],
                    'total_customer'         => $getdata['total_customer'],
                    'owner_discount_percent' => $getdata['owner_discount_percent'] ?? 0,
                    'owner_discount_value'   => $getdata['owner_discount_value'] ?? 0.00,
                    'payor_name'             => $getdata['payor_name'],
                    'payor_number'           => $getdata['payor_number'],
                    'lessVatDeduction'       => $getdata['lessVatDeduction'] ?? 0.00,
                    'vat'                    => $getdata['vat'] ?? 0.00,
                    'vatSales'               => $getdata['vatSales'] ?? 0.00,
                    'vatExemptSales'         => $getdata['vatExemptSales'] ?? 0.00,
                    'type'                   => $getdata['type'],
                    'is_vat'                 => $getdata['is_vat'],
                    'table_id'                  => $getdata['table_id'],
                    'status'                 => $getdata['status'],
                    'order_status'           => $getdata['order_status'],
                    'order_remarks'          => $getdata['order_remarks'] ?? null,
                    'cashier'                => $getdata['cashier'],
                    'staff_id'               => $getdata['staff_id'],
                    'slug'                   => $getdata['slug'],
                    'created_at'             => $getdata['created_at'],
                    'updated_at'             => now(),
                    'is_uuid'                => $getdata['is_uuid'],
                    'is_sync'                => 1,
                    'report_tran_uuid' => $getdata['report_tran_uuid'],
                    'local_id'    => $id,
                    'store_code' => $getdata['store_code'],
                    'branch_code' => $getdata['branch_code'],
                    'orig_local_id' => $getdata['orig_local_id'],   // 8-29 harold
                ]);
            } else {
                DB::table('sales_report_transactions_duplicate')->insert([
                   'report_id'              => $getdata['report_id'],
                    'number'                 => $getdata['number'],
                    'total'                  => $getdata['total'],
                    'mode_of_payment_id'        => $getdata['mode_of_payment_id'],
                    'mode_of_payment'        => $getdata['mode_of_payment'],
                    'amount'                 => $getdata['amount'],
                    'fee'                 => $getdata['fee'],
                    'sc_amount'              => $getdata['sc_amount'],
                    'sc_percent'             => $getdata['sc_percent'] ?? 0,
                    'pwd_amount'             => $getdata['pwd_amount'],
                    'customer_with_discount' => $getdata['customer_with_discount'],
                    'total_customer'         => $getdata['total_customer'],
                    'owner_discount_percent' => $getdata['owner_discount_percent'] ?? 0,
                    'owner_discount_value'   => $getdata['owner_discount_value'] ?? 0.00,
                    'payor_name'             => $getdata['payor_name'],
                    'payor_number'           => $getdata['payor_number'],
                    'lessVatDeduction'       => $getdata['lessVatDeduction'] ?? 0.00,
                    'vat'                    => $getdata['vat'] ?? 0.00,
                    'vatSales'               => $getdata['vatSales'] ?? 0.00,
                    'vatExemptSales'         => $getdata['vatExemptSales'] ?? 0.00,
                    'type'                   => $getdata['type'],
                    'is_vat'                 => $getdata['is_vat'],
                    'table_id'                  => $getdata['table_id'],
                    'status'                 => $getdata['status'],
                    'order_status'           => $getdata['order_status'],
                    'order_remarks'          => $getdata['order_remarks'] ?? null,
                    'cashier'                => $getdata['cashier'],
                    'staff_id'               => $getdata['staff_id'],
                    'slug'                   => $getdata['slug'],
                    'created_at'             => $getdata['created_at'],
                    'updated_at'             => now(),
                    'is_uuid'                => $getdata['is_uuid'],
                    'is_sync'                => 1,
                    'report_tran_uuid' => $getdata['report_tran_uuid'],
                    'local_id'    => $id,
                    'store_code' => $getdata['store_code'],
                    'branch_code' => $getdata['branch_code'],
                    'orig_local_id' => $getdata['orig_local_id'],  // 8-29 harold
                ]);
            }
            return response()->json([
                'status' => 'success',
                'id' => $id
            ]);


               } catch (\Exception $e) {
                \Log::error('❌ Sync Sales Report Duplicate Error', [
                    'message' => $e->getMessage(),
                     'data' => $data,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            
                return response()->json([
                    'status' => 'error',
                     'data' => $data,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 500);
            }    
    }
    
  // end 7-18 harold
  
  
// 8-29 harold

        public function sync_ordered_duplicate(Request $request)
        { 
    
            try{
    
          
                $data = $request->all(); 
                $id = $data['id']; 
                
                $user_uuid = $data['user_uuid'];
                $user_cloud_id = $data['user_cloud_id'];
                $check_user = DB::table('users')->where('id', $user_cloud_id)->first();
                if (!$check_user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User not found',
                    ]);
                }

    
               $getdata = DB::table('ordered_duplicate')->where('is_uuid', $data['is_uuid'])->first();
                if ($getdata != null) {
                    DB::table('ordered_duplicate')->where('is_uuid', $data['is_uuid'])->update([
                        'tran_id'         => $data['tran_id'],
                        'menu_id'         => $data['menu_id'],
                        'quantity'        => $data['quantity'],
                        // 'price'           => $data['price'],
                        'amount'          => $data['amount'],
                        'discount_amount' => $data['discount_amount'],
                        'service_charge'  => $data['service_charge'],
                        'status'          => $data['status'],
                        'food_cost'       => $data['food_cost'],
                        // 'is_gift'         => $data['is_gift'],
                        // 'gift_amount'     => $data['gift_amount'],
                        // 'gift_type'       => $data['gift_type'],
                        'type'            => $data['type'],
                        'slug'            => $data['slug'],
                        'created_at'      => $data['created_at'],
                        'updated_at'      => now(),
                        // 'is_deduct'    => 0,  // harold 12-18
                        
                        
    
                    ]);
                } else {              
                    DB::table('ordered_duplicate')->insert([
                        'tran_id'         => $data['tran_id'],
                        'menu_id'         => $data['menu_id'],
                        'quantity'        => $data['quantity'],
                        // 'price'           => $data['price'],
                        'amount'          => $data['amount'],
                        'discount_amount' => $data['discount_amount'],
                        'service_charge'  => $data['service_charge'],
                        'status'          => $data['status'],
                        'food_cost'       => $data['food_cost'],
                        // 'is_gift'         => $data['is_gift'],
                        // 'gift_amount'     => $data['gift_amount'],
                        // 'gift_type'       => $data['gift_type'],
                        'type'            => $data['type'],
                        'slug'            => $data['slug'],
                        'created_at'      => $data['created_at'],
                        'updated_at'      => now(),
                        'is_uuid'         => $data['is_uuid'],
                        'is_sync'         => 1,
                        
                        'price_type'      => '0',
                        'cashier'      => 0,
                        'role'      => 0,
                        'lime_discount'   => '0',
                        'is_pwd'      => 0,
                        'is_odisc'      => 0,
                        'remarks'      => '',
                        'print'      => '0',
                        'department'  => 0,
                        'branch'  => 0,
                        'for_delete'  => 0,
                        'is_split'  => 0,
                        'menu_uuid' => $data['menu_uuid'],  // harold 12-18        
                        'branch_code' => $data['branch_code'],
                        'store_code' => $data['store_code'],
                        'local_id'    => $id,
                        'is_deduct'    => 1,  // harold 12-18               
                        
                    ]);
                }
                return response()->json([
                'status' => 'success', 'id' => $id
                ]);          
              } catch (\Exception $e) {
            // Return error details
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
              
        }
        



//8-29 harold end





}
