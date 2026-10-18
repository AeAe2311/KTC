<?php
namespace App\Http\Controllers;

use App\Mail\MailNotify;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Ordered;
use App\Models\Transaction;
use App\Models\GuestTransaction;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;    
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class ApiAndroidGetDataController extends Controller {


    


        // public function getData_menu(Request $request)
        // {
            
        //     $store_code = $request->input('store_code');  
        //     $branch_code = $request->input('branch_code'); 
        //     $get_data = DB::transaction(function () use ($store_code, $branch_code,) {
        //         // Get all undeducted orders
        //         $check_ordered = DB::table('ordered')
        //                             // ->where('is_deleted', 0)
        //                             ->where('store_code', $store_code)
        //                             ->where('branch_code', $branch_code)
        //                             ->where('is_return', '!=', 1)
        //                             ->get();
                                    
        //           $check_void = DB::table('void_item')
        //                             // ->where('status', '!=', 'CANCELLED')
        //                             // ->where('is_deleted', 0)
        //                             ->where('store_code', $store_code)
        //                             ->where('branch_code', $branch_code)
        //                             ->where('is_deduct', '!=', 1)
        //                             ->get();                    
                                    
                                    

        //         if ($check_ordered->isEmpty() && $check_void->isEmpty()) {
        //             return DB::table('menus')
        //                     // ->where('is_sync', '!=', 1)
        //                     ->where('store_code', $store_code)
        //                     ->where('branch_code', $branch_code)
        //                     ->get();
        //         }

             
        //         foreach ($check_ordered as $ordered) {
        //                     $menu = DB::table('menus')
        //                     ->where('is_uuid', $ordered->menu_uuid)
        //                     ->first();

        //                       DB::table('menus')
        //                         ->where('is_uuid', $ordered->menu_uuid)
        //                         ->update([
        //                             'quantity' => $menu->quantity - $ordered->quantity,
        //                             'current_quantity' => $menu->current_quantity - $ordered->quantity,
        //                             'updated_at' => now(),
        //                         ]);

        //             DB::table('ordered')
        //             ->where('id', $ordered->id)
        //             ->update(['is_deduct' => 1]);
        //         }

        //         // Return unsynced menus
        //         return DB::table('menus')
        //                 ->where('is_sync', '!=', 1)
        //                 ->where('store_code', $store_code)
        //                 ->where('branch_code', $branch_code)
        //                 ->get();
        //     });

        //     return response()->json([
        //         'message' => $get_data->isNotEmpty() ? 'success' : 'failed',
        //         'getdata' => $get_data
        //     ]);
        // }


//   public function getData_menu(Request $request)
//         {
            
//             $store_code = $request->input('store_code');  
//             $branch_code = $request->input('branch_code'); 
//             $get_data = DB::transaction(function () use ($store_code, $branch_code,) {
             
//               $check_ordered = DB::table('ordered')
//                                 // ->where('is_deleted', 0)
//                                 ->where('store_code', $store_code)
//                                 ->where('branch_code', $branch_code)
//                                 ->where('is_deduct', '!=', 1)
//                                 ->get();
                                
//                 if ($check_ordered->isEmpty()){
//                      return DB::table('menus')
//                         // ->where('is_sync', '!=', 1)
//                         ->where('store_code', $store_code)
//                         ->where('branch_code', $branch_code)
//                         ->get();
//                 }

//                      foreach ($check_ordered as $ordered) {
//                             $menu = DB::table('menus')
//                             ->where('is_uuid', $ordered->menu_uuid)
//                             ->where('is_deleted', '!=', 1)
//                             ->first();
                            
                                
                            
//                             $current_quantity =  $menu->current_quantity;
//                             $new_current_quantity = $menu->current_quantity - $ordered->quantity;
//                             $new_quantity = $menu->quantity - $ordered->quantity;
                            
//                             //  dd([
//                             //     '$ordered' => $ordered->quantity,
//                             //     'current_quantity' => $current_quantity,
//                             //     'new_current_quantity' => $new_current_quantity,
//                             //     'new_quantity' => $new_quantity,
//                             //     ]);

//                           $updated = DB::table('menus')
//                                 ->where('is_uuid', $ordered->menu_uuid)
//                                 ->where('is_deleted', '!=', 1)
//                                 ->update([
//                                     'quantity' => $new_quantity,
//                                     'current_quantity' => $new_current_quantity,
//                                     'updated_at' => now(),
//                                 ]);
                  
//                      DB::table('ordered')->where('id', $ordered->id)->update(['is_deduct' => 1]);
                     
//                     }
             


//                 // Return unsynced menus
//                 return DB::table('menus')
//                         // ->where('is_sync', '!=', 1)
//                         ->where('store_code', $store_code)
//                         ->where('branch_code', $branch_code)
//                         ->get();
//             });

//             return response()->json([
//                 'message' => $get_data->isNotEmpty() ? 'success' : 'failed',
//                 'getdata' => $get_data
//             ]);
//         }


//   public function getData_menu_void(Request $request)
//         {
//             try {
                
         
//             $store_code = $request->input('store_code');  
//             $branch_code = $request->input('branch_code'); 
                                    
//               $check_void = DB::table('void_item')
//                         ->where('store_code', $store_code)
//                         ->where('branch_code', $branch_code)
//                         ->where('is_return', 0)
//                         ->get();                    
                                    
              
                                    
//                  if ($check_void->isNotEmpty() ) {
//                      foreach ($check_void as $void) {
//                             $menu = DB::table('menus')
//                             ->where('id', $void->cloud_id)
//                             ->first();
                            
//                             $current_quantity =  $menu->current_quantity;
//                             $new_current_quantity = $menu->current_quantity + $void->quantity;
//                             $new_quantity = $menu->quantity + $void->quantity;
                         

//                              $updated =  DB::table('menus')
//                                 ->where('id', $menu->id)
//                                 ->update([
//                                     'quantity' => $new_quantity,
//                                     'current_quantity' => $new_current_quantity,
//                                     'updated_at' => now(),
//                                 ]);

           
//                             DB::table('void_item')->where('id', $void->id)->update(['is_return' => 1]);

//                      }
                    
                   
//                  }
 
//                 return DB::table('menus')
                   
//                         ->where('store_code', $store_code)
//                         ->where('branch_code', $branch_code)
//                         ->get();
//             }catch (\Exception $e) {
     
//                     return response()->json([
//                         'message' => 'error',
//                         'error' => $e->getMessage()
//                     ], 500); // HTTP 500 = server error
//                 }
            
//         }

public function getData_menu(Request $request)
        {
            $store_code = $request->input('store_code');  
            $branch_code = $request->input('branch_code'); 
            $get_data = DB::transaction(function () use ($store_code, $branch_code,) {
             
              $check_ordered = DB::table('ordered')
                                // ->where('is_deleted', 0)
                                ->where('store_code', $store_code)
                                // ->where('branch_code', $branch_code)
                                ->where('is_deduct', '!=', 1)
                                ->get();
                                
                if ($check_ordered->isNotEmpty()){
                     foreach ($check_ordered as $ordered) {
                         
                            $get_Trans = DB::table('transactions')
                            ->where('local_id', $ordered->tran_id)
                            ->first();
                            
                            $getclient = DB::table('tables')
                            ->where('id', $get_Trans->table)
                            ->first();
                            
                            
                            $uuid = Str::uuid()->toString();
                            $ordered_id = $ordered->id;
                            $trans_number = $get_Trans->number;
                            $trans_id = $get_Trans->local_id;
                            $type = 'DELIVERY NOTE';
                            $client = $getclient->name;
                            $qty_out =  $ordered->quantity;
                            
                            $sold_amount =  $ordered->amount;
                            $sold_price = $sold_amount / $qty_out;
                            
                            $menu = DB::table('menus')
                            ->where('is_uuid', $ordered->menu_uuid)
                            ->where('is_deleted', '!=', 1)
                            ->first();
                            
                            
                            $current_quantity =  $menu->current_quantity;
                            $new_current_quantity = $menu->current_quantity - $ordered->quantity;
                            $new_quantity = $menu->quantity - $ordered->quantity;
                            $cose =  $menu->food_cost;
                           
         
                        
                            $menu = DB::table('menus')
                            ->where('is_uuid', $ordered->menu_uuid)
                            ->where('is_deleted', '!=', 1)
                            ->first();
                        
                            $menu_id = $menu->id;
                            $previous_quantity = $menu->quantity ?? 0;
                            $new_quantity = $previous_quantity - $ordered->quantity;
                                
                            $updated = DB::table('menus')
                                ->where('is_uuid', $ordered->menu_uuid)
                                ->where('is_deleted', '!=', 1)
                                ->update([
                                    'quantity' => $new_quantity,
                                    'current_quantity' => $new_current_quantity,
                                    'updated_at' => now(),
                                ]);

                            DB::table('ordered')->where('id', $ordered->id)->update(['is_deduct' => 1]);
                         
                         
                         $check_movement_exist = DB::table('inventory_movement')->where('ordered_id', $ordered_id)->first();
                          if ($check_movement_exist){
                              DB::table('inventory_movement')->where('ordered_id', $ordered_id)
                                ->update([
                                    'type' => $type,
                                    'ordered_id' => $ordered_id,
                                    'menu_id' => $menu_id,
                                    'trans_id' => $trans_id,
                                    'trans_number' => $trans_number,
                                    'reference' => 	$trans_number,
                                    'detail' => $client,
                                    'previous_qty' => $previous_quantity,
                                    'qty_in' => 0.0,
                                    'qty_out' => $qty_out,
                                    'remaining' => $new_quantity,
                                    'sold_price' => $sold_price,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                    'branch_code' => $store_code,
                                    'store_code' => $branch_code,
                                    'is_uuid' => $uuid,
                                    'is_sync' => 1,
                                ]);
                          }else{
                               DB::table('inventory_movement')
                                ->insert([
                                    'type' => $type,
                                    'ordered_id' => $ordered_id,
                                    'menu_id' => $menu_id,
                                    'trans_id' => $trans_id,
                                    'trans_number' => $trans_number,
                                    'reference' => 	$trans_number,
                                    'detail' => $client,
                                    'previous_qty' => $previous_quantity,
                                    'qty_in' => 0.0,
                                    'qty_out' => $qty_out,
                                    'remaining' => $new_quantity,
                                    'sold_price' => $sold_price,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                    'branch_code' => $store_code,
                                    'store_code' => $branch_code,
                                    'is_uuid' => $uuid,
                                    'is_sync' => 1,
                                ]);
                               
                          }
                    }
                }
                
                $check_void = DB::table('void_item')
                        ->where('store_code', $store_code)
                        // ->where('branch_code', $branch_code)
                        ->where('is_return', 0)
                        ->get();                    
                                    
                 if ($check_void->isNotEmpty() ) {
                     foreach ($check_void as $void) {
                         
                             $get_Trans = DB::table('transactions')
                            ->where('local_id', $void->tran_id)
                            ->first();
                            
                             $getclient = DB::table('tables')
                            ->where('id', $get_Trans->table)
                            ->first();
                         
                         
                            $uuid = Str::uuid()->toString();
                            $void_id = $void->id;
                            $trans_number = $get_Trans->number;
                            $trans_id = $get_Trans->local_id;
                            $type = 'VOID';
                            $client = $getclient->name;
                            $qty_in =  $void->quantity;
                            $qty_out =  0.0;
                         
                            $sold_amount = 0;
                            $sold_price = 0;
                         
                            $menu = DB::table('menus')
                            ->where('id', $void->cloud_id)
                            ->where('is_deleted', '!=', 1)
                            ->first();
                            
                            $menu_id = $menu->id;
                            $current_quantity =  $menu->quantity;
                            $new_current_quantity = $menu->current_quantity + $void->quantity;
                            $new_quantity = $menu->quantity + $void->quantity;
                            $cost =  $menu->food_cost;
                         
                            DB::table('menus')
                                ->where('id', $menu->id)
                                ->where('is_deleted', '!=', 1)
                                ->update([
                                    'quantity' => $new_quantity,
                                    'current_quantity' => $new_current_quantity,
                                    'updated_at' => now(),
                                ]);

                            DB::table('void_item')->where('id', $void->id)->update(['is_return' => 1]);
                            
                        $check_movement_exist = DB::table('inventory_movement')->where('void_id', $void_id)->first();
                          if ($check_movement_exist){
                              DB::table('inventory_movement')->where('void_id', $void_id)
                                ->update([
                                    'type' => $type,
                                    // 'ordered_id' => $ordered_id,
                                    'void_id' => $void_id,
                                    'menu_id' => $menu_id,
                                    'trans_id' => $trans_id,
                                    'trans_number' => $trans_number,
                                    'reference' => 	$trans_number,
                                    'detail' => $client,
                                    'previous_qty' => $current_quantity,
                                    'qty_in' => $qty_in,
                                    'qty_out' => 0.0,
                                    'remaining' => $new_quantity,
                                    'sold_price' => $sold_price,
                                    'cost' => $cost,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                    'branch_code' => $store_code,
                                    'store_code' => $branch_code,
                                    'is_uuid' => $uuid,
                                    'is_sync' => 1,
                                ]);
                          }else{
                               DB::table('inventory_movement')
                                ->insert([
                                    'type' => $type,
                                    // 'ordered_id' => $ordered_id,
                                    'void_id' => $void_id,
                                    'menu_id' => $menu_id,
                                    'trans_id' => $trans_id,
                                    'trans_number' => $trans_number,
                                    'reference' => 	$trans_number,
                                    'detail' => $client,
                                    'previous_qty' => $current_quantity,
                                    'qty_in' => $qty_in,
                                    'qty_out' => 0.0,
                                    'remaining' => $new_quantity,
                                    'sold_price' => $sold_price,
                                    'cost' => $cost,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                    'branch_code' => $store_code,
                                    'store_code' => $branch_code,
                                    'is_uuid' => $uuid,
                                    'is_sync' => 1,
                                ]);
                               
                          }
                            
                     }
                 }
                 
            // Return unsynced menus
                return DB::table('menus')
                        // ->where('is_sync', '!=', 1)
                        ->where('store_code', $store_code)
                        // ->where('branch_code', $branch_code)
                        ->get();
            });

            return response()->json([
                'message' => $get_data->isNotEmpty() ? 'success' : 'failed',
                'getdata' => $get_data
            ]);
        }
//   

                            // harold 2-28   --------------- refund
            public function refund_qty(Request $request)
                {
                    try{
                    $store_code = $request->input('store_code');  
                    $branch_code = $request->input('branch_code'); 
                    $get_data_refund = DB::transaction(function () use ($store_code, $branch_code,) {
                     
                      $check_ordered_refund = DB::table('ordered_refund')
                                        // ->where('is_deleted', 0)
                                        ->where('store_code', $store_code)
                                        ->where('branch_code', $branch_code)
                                        ->where('is_return', '!=', 1)
                                        ->get();
                                        
                        if ($check_ordered_refund->isNotEmpty()){
                             foreach ($check_ordered_refund as $ordered) {
                                    $menu = DB::table('menus')
                                    ->where('is_uuid', $ordered->menu_uuid)
                                    ->where('is_deleted', '!=', 1)
                                    ->first();
                                    
                                    
                                    if($ordered->label == 1){
                                        $insert_waste = DB::table('wastes')->insert([
                                               'on' => 'MENU',
                                               'iid' => $menu->id,
                                               'quantity' => $ordered->quantity,
                                               'cost' => $ordered->amount,
                                               'waste_remarks' => 'Damage Product',
                                                'store_code' => $ordered->store_code,
                                                'branch_code' => $ordered->branch_code,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ]);
                                        if($insert_waste){
                                            DB::table('ordered_refund')->where('id', $ordered->id)->update(['is_return' => 1]);
                                        }
                                    }else if($ordered->label == 2){
                                        
                                        $current_quantity =  $menu->current_quantity;
                                        $new_current_quantity = $menu->current_quantity + $ordered->quantity;
                                        $new_quantity = $menu->quantity + $ordered->quantity;
                                        DB::table('ordered_refund')->where('id', $ordered->id)->update(['is_return' => 1]);
                                        
                                        $update_menus = DB::table('menus')
                                            ->where('id', $menu->id)
                                            ->where('is_deleted', '!=', 1)
                                            ->update([
                                                'quantity' => $new_quantity,
                                                'current_quantity' => $new_current_quantity,
                                                'updated_at' => now(),
                                            ]);

                                       if($update_menus){
                                          DB::table('ordered_refund')->where('id', $ordered->id)->update(['is_return' => 1]);
                                       }
                                        
                                    }else if($ordered->label == 3){
                                        
                                        $current_quantity =  $menu->current_quantity;
                                        $new_current_quantity = $menu->current_quantity + $ordered->quantity;
                                        $new_quantity = $menu->quantity + $ordered->quantity;
                                        DB::table('ordered_refund')->where('id', $ordered->id)->update(['is_return' => 1]);
                                        
                                        $update_menus = DB::table('menus')
                                            ->where('id', $menu->id)
                                            ->where('is_deleted', '!=', 1)
                                            ->update([
                                                'quantity' => $new_quantity,
                                                'current_quantity' => $new_current_quantity,
                                                'updated_at' => now(),
                                            ]);

                                       if($update_menus){
                                          DB::table('ordered_refund')->where('id', $ordered->id)->update(['is_return' => 1]);
                                       }
                                   }
                            }
                        }
    
                        });
                          return response()->json([
                                'message' =>  'success' ,
                            ],200);
                        
                    }catch (\Throwable $e) {
                        return response()->json([
                            'message' => 'error',
                            'error'   => $e->getMessage(),
                        ], 500);
                    }
                }
                
                
                   // harold 2-28   --------------- refund end
                   
                   
                   
                   
                   
                   
                   
                   
                   
                   
                   

        // public function getData_menu_update(Request $request)
        // {
        //     $current_uuid = $request->input('current_uuid');  

        //     $update = Db::table('menus')
        //                 ->where('is_uuid', $current_uuid)
        //                 ->update(['is_sync' => 1]);

        //     if (!$update){
        //         return response()->json(['message'=> 'success',]);
        //     }else{
        //             return response()->json([
        //                 'message' => 'failed',  
        //                 ]);
        //     }
            
        // }
            public function get_branch(Request $request)
        {
            $store_code = $request->input('store_code');  

            $get_data = Db::table('settings')
                        ->where('store_code', $store_code)->get();

            return response()->json(['message'=> 'success',
                                        'data' => $get_data]);
        
        }
        
         public function get_online_banking_list(Request $request)
        {
            $store_code = $request->input('store_code');  

            $get_data = Db::table('online_banking_list')
                        ->where('store_code', $store_code)->get();

            return response()->json(['message'=> 'success',
                                        'data' => $get_data]);
        
        }

        
             public function getData_table(Request $request)
        {
            $store_code = $request->input('store_code');  

            $get_data = Db::table('tables')
                        ->where('store_code', $store_code)->get();

            return response()->json(['message'=> 'success',
                                        'getdata' => $get_data]);
        
        }
        
        
              public function getData_categories(Request $request)
        {
            $store_code = $request->input('store_code');  
            $branch_code = $request->input('branch_code'); 
            $get_data = Db::table('menu_categories')
                        ->where('store_code', $store_code)->get();

            return response()->json(['message'=> 'success',
                                        'getdata' => $get_data]);
        
        }
        
             public function getData_department(Request $request)
        {
            $store_code = $request->input('store_code');  
            $branch_code = $request->input('branch_code'); 
            $get_data = Db::table('departments')
                        ->where('store_code', $store_code)->get();

            return response()->json(['message'=> 'success',
                                        'getdata' => $get_data]);
        
        }
        
        
              public function getData_brand(Request $request)
        {
            $store_code = $request->input('store_code');  
            $branch_code = $request->input('branch_code'); 
            $get_data = Db::table('brands')
                        ->where('store_code', $store_code)->get();

            return response()->json(['message'=> 'success',
                                        'getdata' => $get_data]);
        
        }
        
        
              public function getData_mop(Request $request)
        {
            $store_code = $request->input('store_code');  
            $branch_code = $request->input('branch_code'); 
            $get_data = Db::table('mode_of_payment')
                        ->where('store_code', $store_code)->get();

            return response()->json(['message'=> 'success',
                                        'getdata' => $get_data]);
        
        }



}
?>