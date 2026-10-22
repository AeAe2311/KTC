<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function CreateQrcodeAction(){

        // ob_start ();
        
        // echo url()->current();
        
        $url = url(''); // Get the current url
        
        // dd($url);
        
        $http = $url .'/api/login/mobile/scan/qrcode'; // Verify the url method of scanning code
        
        $key = Str::random(30);//$this->getRandom(30); // The key value stored in Memcached, a random 32-bit string
        
        $random = mt_rand(1000000000, 9999999999);//random integer
        
        $_SESSION['qrcode_name'] = $key ; // Save the key as the name of the picture in the session
        
        $forhash=substr( $random,0,2);
        
        $sgin_data = HashUserID($forhash); // The basic algorithm for generating the sign string
        
        $sgin =strrev(substr($key,0,2)).$sgin_data ; // Intercept the first two digits and reverse them
        
        $value = $http .'?key='. $key .'&type=1'; // Two-dimensional Code content
        
        $pngImage = QrCode::format('png')
        
        // ->merge(public_path('frontend/img/streamly-logo.png'), 0.3, true)
        
        ->size(300)->errorCorrection('H')
        
        ->generate($value, public_path('assets/img/qrcodeimg/'. $key .'.png'));
        
        $return = array ('status'=>0,'msg'=>'' );
        
        $qr = public_path('assets/img/qrcodeimg/'. $key .'.png');
        
        // $qr = asset('assets/img/qrcodeimg/'. $key .'.png');
        
        // dump($qr);
        
        if (!file_exists($qr)) {
        
        $return = array ('status'=>0,'msg'=>'' );
        
        return response()->json($return, 404);
        
        // return "no found qr img";
        
        }
        
        $qr = asset('assets/img/qrcodeimg/'. $key .'.png');
        
        $mem = new \Memcached();
        
        $mem->addServer('127.0.0.1',11211 );
        
        $res=json_encode(array('sign'=> $sgin ,'type'=>0 ));
        
        // store in Memcached, expiration time is three minutes
        
        $mem->set($key,$res ,180);// 180
        
        $return = array('status'=>1,'msg'=> $qr,'key'=>$key);
        
        return response()->json($return, 200);
        
        // return $this ->createJsonResponse( $return );
        
        }

        public function qrcodeDoLoginAction(Request $request ){

 

            $login=$_GET['login'];//jwt or passcode
            
            $key = $_GET['key'];
            
            $sign = $_GET['sign'];
            
            $mem = new \Memcached();
            
            $mem->addServer('127.0.0.1',11211);
            
            $data = json_decode($mem->get($key),true); // Remove the value of Memcached
            
            if (empty($data)){
            
            $return = array ('status'=>2,'msg'=>'expired' );
            
            return response()->json($return, 200);
            
            } else {
            
            if (!isset($data['sign'])){
            
            $return = array('status'=>0,'msg'=>'Sign notset' );
            
            }
            
            if ($data['sign']!= $sign ){ // Verify delivery Sign
            
            $return = array('status'=>0,'msg'=>'Verification Error' );
            
            // return $this ->createJsonResponse( $return );
            
            return response()->json($return, 403);
            
            }

    
}
