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

use Illuminate\Support\Facades\Log;



class ApiAndroidtokenController extends Controller {


 public function keytoken(Request $request)
{
    $companyname = $request->input('companyname');  
    $companyaddress = $request->input('companyaddress');  
    $contactnumber = $request->input('contactnumber');  
    $token = $request->input('token');  
    $model = $request->input('model');  
    $version = $request->input('version');  
    $buildnumber = $request->input('buildnumber');  


    $key_checking = DB::table('verification_keys')->where('token',$token)->first();
    $months =  $key_checking->duration_months;
    $days =  $key_checking->duration_days;
    $hrs =  $key_checking->duration_hrs;
    $mins =  $key_checking->duration_mins;


    $expire = Carbon::now()
            ->addMonths($months)
            ->addDays($days)
            ->addHours($hrs)
            ->addMinutes($mins);

    if($key_checking == null){
    return response()->json(['message'=> 'failed']);
    }
    else{
        if($key_checking->status == 1){
             return response()->json(['message'=> 'used']);
        }
        else{
            $update_status = DB::table('verification_keys')->where('id', $key_checking->id)
            ->update(['status' => 1, 
                    'phone_model' => $model,
                    'phone_osversion' => $version,
                    'phone_buildnumber' => $buildnumber,
                    'business_name' =>  $companyname,
                    'business_address' => $companyaddress,
                    'business_number' => $contactnumber,
                    'expired_at' => $expire                  
            ]);
            if($update_status){
            return response()->json([         
              'message'=> 'success',
              'months'=>  $months,
              'days'=>  $days,
              'hrs'=>  $hrs,
              'mins'=>  $mins
             ]);
            }else{
               return response()->json(['message'=> 'failed',]);
            }
        }

    }
        
}


     public function updatetoken(Request $request){
    
        $apptoken = $request->apptoken;
        $checktoken = Db::table('users')->where('username', $apptoken)->first(); 
        
        Log::info('Token: ' . $apptoken);
        Log::info('checktoken:', ['data' => $checktoken]);
        
        if($checktoken != null){
            $getupdatedtoken = $checktoken->expired_at;
            return response()->json([
              'message' => 'success', 
              'tokendate' => $getupdatedtoken
            ]);
        }else{
            return response()->json([
              'message' => 'failed',  
            ]);
        }
    }
    
    // publi
    
    
    
 
    
    // public function get_username(Request $request)
    // {
      
    //     $username = $request->input('username'); 
    //     $password = $request->input('password');
    
    //     $user = DB::table('users')->where('username', $username)->first();
    

    //     if($user != null){
    //     if (!$user || !Hash::check($password, $user->password)) {
    //         return response()->json([
    //             'message' => 'failed1'
    //         ],);
       
    //     }else{
    //          return response()->json([
    //         'message' => 'success',
    //         'user' => $user,
    //         'token' => null 
    //          ]);

    //     }
       
         
    //     }else{
    //          return response()->json([
    //         'message' => 'failed',      
    //          ]);
    //     }
        
    // }


    public function get_username(Request $request)
    {
        $username = $request->query('username'); // get from query string
        $password = $request->query('password');
        $userCount = $request->query('userCount');
        
        $phoneModel = $request->query('phoneModel');
        $phoneVersion = $request->query('phoneVersion');
        $phoneId = $request->query('phoneId');
        
    
    $user = DB::table('users')->where('username', $username)->first();
    


    if($user != null){
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'failed1'
            ],);
        }
        
        if ($user->is_used == 1 ) {
            return response()->json([
                'message' => 'already_used'
            ],);
        }
        
        
        if($userCount === '0'){
             $key_checking = DB::table('users')->where('username',$username)->first();
                $months =  $key_checking->duration_months ?? 0;
                $days =  $key_checking->duration_days ?? 0;
                $hrs =  $key_checking->duration_hrs ?? 0;
                $mins =  $key_checking->duration_mins ?? 0;

                
                 if($months == 0 && $days == 0 && $hrs == 0 && $mins == 0){
                     
                     try{
                        $update_status = DB::table('users')->where('username', $username)
                          ->update(['phone_model' => $phoneModel,
                                    'phone_osversion' => $phoneVersion,
                                    'phone_buildnumber' => $phoneId,
                        ]);
                        
                            if($update_status){
                                return response()->json([
                                'message' => 'success',
                                'user' => $user,
                                'token' => null ]);
                            }
                     }catch (QueryException $e) {
                        return response()->json([
                            'message' => false,
                            'sql' => $e->getSql(),
                        ], 500);
                    }
                }

                $expire = Carbon::now()
                        ->addMonths($months)
                        ->addDays($days)
                        ->addHours($hrs)
                        ->addMinutes($mins);
                $update_status = DB::table('users')->where('username', $username)
                  ->update(['expired_at' => $expire,
                            'phone_model' => $phoneModel,
                            'phone_osversion' => $phoneVersion,
                            'phone_buildnumber' => $phoneId,
                ]);
                
                $update_user = DB::table('users')->where('username', $username)->first();
                if($update_status){
                    return response()->json([
                    'message' => 'success',
                    'user' => $update_user,
                    'token' => null ]);
                }else{
                     return response()->json([
                    'message' => 'failed2']);
                }
                
        }else{
            return response()->json([
            'message' => 'success',
            'user' => $user,
            'token' => null ]);
        }
          
    }else{
        return response()->json([
        'message' => 'failed3']);
    }

        
       
    }
    
    public function updateaccinfo(Request $request){
        $username = $request->apptoken;
        $data = Db::table('users')->where('username', $username)->first();
        return response()->json([
          'message' => 'success', 
          'data' => $data, 
        ],200);
    }
    
    
    
    
    
       public function update_username(Request $request){
    
        $user_uuid = $request->user_uuid;
        $cloud_id = $request->cloud_id;
        
        
        $update = Db::table('users')->where('id', $cloud_id)
                 ->update(['is_uuid' => $user_uuid,
                           'is_used' => 0]); // CHANGE 1 TO 0 (FOR DEV PURPOSE)  4-6-2026
        
        if($update){
            return response()->json([
              'message' => 'success', 
            ]);
        }else{
            return response()->json([
              'message' => 'failed',  
            ]);
        }
    }
   





}
?>