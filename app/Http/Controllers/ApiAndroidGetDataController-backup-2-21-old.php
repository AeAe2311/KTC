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


    


        public function getData_menu(Request $request)
        {
            
            $store_code = $request->input('store_code');  
            $branch_code = $request->input('branch_code'); 
            $get_data = DB::transaction(function () use ($store_code, $branch_code,) {
                // Get all undeducted orders
                $check_ordered = DB::table('ordered')
                                    ->where('status', '!=', 'CANCELLED')
                                    // ->where('is_deleted', 0)
                                    ->where('store_code', $store_code)
                                    ->where('branch_code', $branch_code)
                                    ->where('is_deduct', '!=', 1)
                                    ->get();

                if ($check_ordered->isEmpty()) {
                    return DB::table('menus')
                            // ->where('is_sync', '!=', 1)
                            ->where('store_code', $store_code)
                            ->where('branch_code', $branch_code)
                            ->get();
                }

             
                foreach ($check_ordered as $ordered) {
                            $menu = DB::table('menus')
                            ->where('is_uuid', $ordered->menu_uuid)
                            ->first();

                              DB::table('menus')
                                ->where('is_uuid', $ordered->menu_uuid)
                                ->update([
                                    'quantity' => $menu->quantity - $ordered->quantity,
                                    'current_quantity' => $menu->current_quantity - $ordered->quantity,
                                    'updated_at' => now(),
                                ]);

                    DB::table('ordered')
                    ->where('id', $ordered->id)
                    ->update(['is_deduct' => 1]);
                }

                // Return unsynced menus
                return DB::table('menus')
                        ->where('is_sync', '!=', 1)
                        ->where('store_code', $store_code)
                        ->where('branch_code', $branch_code)
                        ->get();
            });

            return response()->json([
                'message' => $get_data->isNotEmpty() ? 'success' : 'failed',
                'getdata' => $get_data
            ]);
        }




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



}
?>