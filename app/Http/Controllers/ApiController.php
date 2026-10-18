<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function kitchen(){
        
        $ord = DB::table('prints')
        ->where('status', 0)->limit(1)->first();
        if (!empty($ord)){
        $orderResult = DB::table('ordered')
        ->select('ordered.id','ordered.tran_id','ordered.quantity','ordered.cashier','ordered.menu_id','departments.name as dname','menus.name as mname','menus.name as mname','users.name as cname','transactions.total_customer as pax','transactions.number as tnumber','tables.name as tname','ordered.remarks as mremarks','ordered.department')
        ->leftjoin('menus', 'menus.id','=','ordered.menu_id')
        ->leftjoin('users', 'users.id','=','ordered.cashier')
        ->leftjoin('transactions', 'transactions.id','=','ordered.tran_id')
        ->leftjoin('tables', 'transactions.table','=','tables.id')
        ->leftjoin('departments', 'departments.id','=','ordered.department')
        ->where('ordered.print', 0)
        ->whereIn('ordered.department', [1,2,4])
        ->orderBy('ordered.department', 'ASC')
        ->where('ordered.tran_id',$ord->tran_id)->get();
        

        /* $response = array(
            'orders' => $orderResult,
        ); 
        ->select('ordered.id','ordered.tran_id','ordered.quantity','ordered.cashier','ordered.menu_id','menus.name as mname',,'menus.name as mname')
        */

        return $orderResult;
        }
    }
    
    public function bar(){
        
        $ord = DB::table('ordered')
        ->where('print', 0)->where('ordered.department', 2)->limit(1)->first();
        if (!empty($ord)){
           $orderResult = DB::table('ordered')
            ->select('ordered.id','ordered.tran_id','ordered.quantity','ordered.cashier','ordered.menu_id','departments.name as dname','menus.name as mname','menus.name as mname','users.name as cname','transactions.total_customer as pax','transactions.number as tnumber','tables.name as tname','ordered.remarks as mremarks','ordered.department')
            ->leftjoin('menus', 'menus.id','=','ordered.menu_id')
            ->leftjoin('users', 'users.id','=','ordered.cashier')
            ->leftjoin('transactions', 'transactions.id','=','ordered.tran_id')
            ->leftjoin('tables', 'transactions.table','=','tables.id')
            ->leftjoin('departments', 'departments.id','=','ordered.department')
            ->where('ordered.print', 0)
            ->where('ordered.department', 2)
            ->where('ordered.tran_id',$ord->tran_id)->get();
        

        /* $response = array(
            'orders' => $orderResult,
        ); 
        ->select('ordered.id','ordered.tran_id','ordered.quantity','ordered.cashier','ordered.menu_id','menus.name as mname',,'menus.name as mname')
        */

            return $orderResult; 
        }
        
        
    }
    
    public function trans(){
        
        
        
        $trans = DB::table('transactions')
                    ->select('transactions.*', 'tables.name as table_name')
                    ->join('tables', 'transactions.table', '=', 'tables.id')
                    ->where('transactions.print', 0)
                    ->first();
        if(!empty($trans)){
            $response = DB::table('transactions')
                    ->select('transactions.*', 'tables.name as table_name')
                    ->join('tables', 'transactions.table', '=', 'tables.id')
                    ->where('transactions.id', $trans->id)
                    ->get();
            
            return $response;
        }
        
        
        
        
    }
    
    public function ordered($id){
        $response = DB::table('ordered')
                    ->select('ordered.*', 'menus.name','users.name as fname')
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->join('users', 'ordered.cashier', '=', 'users.id')
                    ->where('tran_id', $id)
                    ->get();
        return $response;
    }
    public function payment($id){
        $response = DB::table('payment_orders')->where('tran_id', $id)->get();
        return $response;
    }
    public function senior($id){
        $response = DB::table('seniors')->where('tran_id', $id)->get();
        return $response;
    }
    public function setting(){
        $response = DB::table('settings')->where('id', '!=', 1)->get();
        return $response;
    }

    public function paginate($page){
        $tables = DB::table('tables')->orderBy('id', 'asc')->where('is_deleted', 0)->paginate(100,'*','page',$page);
        $tableCount = DB::table('tables')->get()->count();
        $search = "";
        return view('admin.system-management.tables.index', compact('tables', 'tableCount', 'page', 'search'));
    }

    public function search($page, $search){
        $tables = DB::table('tables')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->paginate(100,'*','page',$page);

        $tableCount = DB::table('tables')
            ->select('*')
            ->whereRaw("CONCAT_WS(' ', name, username) LIKE '%{$search}%'")
            ->where('is_deleted', 0)
            ->orderBy('id', 'asc')
            ->count();
        return view('admin.system-management.tables.index', compact('tables', 'tableCount', 'page', 'search'));
    }

    public function add(){

        $branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();

        return view('admin.system-management.tables.add', compact('branches'));
    }

    public function store(Request $request){
        $name = strtoupper($request->name);
        $sc = $request->sc;
        $branch = $request->branch;
        
        $service_charge =  ($sc == 'on') ? 0 : 1;

        //echo $service_charge;
        $request->validate([
            'name' => ['required'],
        ]);

        $table = new Table();
        $table->name = $name;
        $table->category = 1;
        $table->is_sc = $service_charge;
        $table->branch = $branch;
        $table->slug = Str::random(60);
        $table->save();

        return redirect()->route('table.index')->withInput()->with('message', 'Successfully Added');
    }

    public function edit($id){

        $ordered = DB::table('ordered')->where('tran_id', $id)->first();
        DB::table('ordered')->where('tran_id', $id)
            ->update([
                'print' => 1,
            ]);

        DB::table('prints')->where('tran_id', $id)
            ->update([
                'status' => 1,
            ]);
        return $ordered;
        //$branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        //return view('admin.system-management.tables.edit', compact('table','branches'));
    }
    
    public function editReceipt($id){

        $ordered = DB::table('transactions')->where('id', $id)->first();
        DB::table('transactions')->where('id', $id)
            ->update([
                'print' => 1,
            ]);
        return $ordered;
        //$branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        //return view('admin.system-management.tables.edit', compact('table','branches'));
    }

    public function update(Request $request){
        $name = strtoupper($request->name);
        $slug = $request->slug;
        $sc = $request->sc;
        $branch = $request->branch;
        
        $service_charge =  ($sc == 'on') ? 0 : 1;

        $request->validate([
            'name' => ['required'],
        ]);

        DB::table('tables')->where('slug', $slug)
            ->update([
                'name' => $name,
                'category' => 1,
                'is_sc' => $service_charge,
                'branch' => $branch,
            ]);

        return redirect()->route('table.index')->withInput()->with('message', 'Successfully Updated');
    }

    public function delete($slug){
        DB::table('tables')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
            ]);

        return redirect()->route('table.index')->withInput()->with('message', 'Successfully Deleted');
    }
    
    public function printzreading(Request $request) {

        $settings = DB::table('settings')->where('id', 1)->first();
        // $startDate = date('Y-m-d H:i:s', strtotime(date('2025-03-29 00:00:00')));
        // $endDate = date('Y-m-d H:i:s', strtotime( date('2025-03-29 23:59:59')));
        
        $startDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d 00:00:00')));
        $endDate = date('Y-m-d H:i:s', strtotime( date('Y-m-d 23:59:59')));

        $feCDate = strtotime($startDate);
        $feEDate = strtotime($endDate);
        /* $start = $request->start;
        $end = $request->end;

        $startDate = date('Y-m-d H:i:s', strtotime($start));
        $endDate = date('Y-m-d H:i:s', strtotime($end)); */

        $numberwithdiscount = 0;
        // leo start 4-9 
        $results = DB::table('transactions')
        ->select('transactions.created_at as date','transactions.*'
        )
        ->where('transactions.created_at', '>=', $startDate)
        ->where('transactions.created_at', '<', $endDate)
        ->where('transactions.status', '=','PAID')
        ->where('transactions.order_status', '!=', 'CANCELLED')
        ->orderBy('transactions.id', 'desc')
        ->get();

        $auditted = DB::table('transactions')
        ->select('transactions.created_at as date','transactions.*'
        )
        ->where('transactions.created_at', '>=', $startDate)
        ->where('transactions.created_at', '<', $endDate)
        ->where('transactions.status', 'PAID')
        ->where('transactions.sales_type', '1')
        ->where('transactions.order_status', '!=', 'CANCELLED')
        ->orderBy('transactions.id', 'desc')
        ->get();

        $notAuditted = DB::table('transactions')
        ->select('transactions.created_at as date','transactions.*'
        )
        ->where('transactions.created_at', '>=', $startDate)
        ->where('transactions.created_at', '<', $endDate)
        ->where('transactions.status', 'PAID')
        ->where('transactions.sales_type', '0')
        ->where('transactions.order_status', '!=', 'CANCELLED')
        ->orderBy('transactions.id', 'desc')
        ->get();

        // food finds
        $foodFinds = DB::table('transactions')
            ->select('transactions.created_at as date','transactions.*'
            )
            ->where('transactions.created_at', '>=', $startDate)
            ->where('transactions.created_at', '<', $endDate)
            ->where('transactions.status', 'PAID')
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->orderBy('transactions.id', 'desc')
            ->get();

        // studio
        $studio = DB::table('transactions')
            ->select('transactions.created_at as date','transactions.*'
            )
            ->where('transactions.created_at', '>=', $startDate)
            ->where('transactions.created_at', '<', $endDate)
            ->where('transactions.status', 'PAID')
            ->where('table', '=', 60)
            ->where('transactions.order_status', '!=', 'CANCELLED')
            ->orderBy('transactions.id', 'desc')
            ->get();

        // complimentaries
        $foodCost = DB::table('ordered as ord')
            ->leftJoin('transactions as tr', 'tr.id', '=', 'ord.tran_id')
            ->select('tr.created_at as date','tr.id as transid', 'tr.created_at', 'tr.total', 'tr.amount', 'tr.status', 'tr.order_status', 
                'ord.id as ordid', 'ord.tran_id', 'ord.type', 'ord.food_cost', 'ord.created_at', 'ord.quantity')
            ->where('tr.status', '=','PAID')
            ->where('tr.order_status', '!=', 'CANCELLED')
            ->where('ord.type', '=', 1)
            ->where('tr.created_at', '>=', $startDate)
            ->where('tr.created_at', '<', $endDate)
            

            ->orderBy('tr.id', 'desc')
            ->get();


        $MtransactionIds = DB::table('transactions')
            ->select('id')
            ->where('status', 'PAID')
            ->where('sales_type', '0')
            ->where('mode_of_payment', 'MULTIPLE')
            ->where('order_status', '!=', 'CANCELLED')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('id');
    
        $multiplepayment = DB::table('payment_orders')
            ->select('payment_orders.created_at as date', 'payment_orders.id as mpid', 'payment_orders.amount', 'payment_orders.tran_id', 'payment_orders.mop')
            ->whereIn('tran_id', $MtransactionIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $voidorder = DB::table('ordered_remove')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

        $ordered = DB::table('ordered')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

                


                $tAmount =  0;
                $tAmountNot =  0;
                $tDinein = 0;
                $tTakeout = 0;
                $iCount = 0;

                $discount = $pwd = $Vat = $vatSales = $VatExempt = $lessVat = $sc = $net = 0;
                foreach($results as $result){
                    $fResDate = date("Y-m-d", strtotime($result->date));
                    $fCurDate = date("Y-m-d", $feCDate);
                    if($fResDate == $fCurDate){
                        $tAmount += $result->total;
                        //echo json_encode($result->total);
                        $iCount++;
                        
                            if (isset($result->Id_value)) {
                                $discount += $result->Id_value;
                                }
                            $pwd += $result->pwd;
                            $Vat += $result->vat;
                            $vatSales += $result->vatSales;
                            $VatExempt += $result->vatExemptSales;
                            $lessVat += $result->lessVatDeduction;
                            $sc += $result->service_charge;
                            

                        
                    } 
                }
                $Vat = $Vat;
                $vatSales = $vatSales;
                $VatExempt = $VatExempt;
                $lessVat = $lessVat;
                $sc =   $sc;                                       
                
                $net = $tAmount - ($sc + $Vat);


                
                $tcash = 0;
                $tccard = 0;
                $tvisa = 0;
                $tgcash = 0;
                $tamex = 0;
                $tdiners = 0; 
                $tdownpayment = 0;
                $tgiftcert = 0;
                $tjcb = 0;
                $tpaymaya = 0;
                
                
                $twcp = 0;
                $tunionpay = 0;
                $tbcard = 0;
                $tbdopay = 0;
                $tgrabpay = 0;
                $tgrab = 0;
                $tpanda = 0;
                $tqrph = 0;


                $tdiscount = 0;
                


                // <!--  harold 3-16 changes-->
                $tchange = 0;
                
                $Mtcash = 0 ;
                $multichange = 0;
                $multichange2 = 0;                                   
                $Mtcashtotal = 0;

                foreach ($results as $resultg) 
                {
                        
                    if ($resultg->mode_of_payment == 'CASH') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tcash += $resultg->total;                                                                                    
                                }

                            //echo $resultg->total ."</br> "  ;  
                        }                                                
                    if ($resultg->mode_of_payment == 'MULTIPLE') 
                        {
                
                    
                        $multiplenotcash = 0; 
                        $Mtcash1 = 0;
                        
                                    foreach ($multiplepayment as $multiplepaymentresult) 
                                    {
                                    
                                        if ($multiplepaymentresult->tran_id == $resultg->id ) 
                                        {
                                            
                                                if ($multiplepaymentresult->mop  != 'CASH')    
                                                        {        
                                                                                        
                                                    $multiplenotcash += $multiplepaymentresult->amount;                                                                              
                                                    }
                                        
                                                if ($multiplepaymentresult->mop == 'CASH') 
                                                    {
                                                    $Mtcash1 += $multiplepaymentresult->amount;
                                                    }                                                                                                                                                   
                                        }    
                                                                                                                                                            
                                    }        
                        


                        $multichange = $resultg->total - $multiplenotcash ;
                        $multichange2 =  $Mtcash1 -  $multichange;
                        $Mtcash = $Mtcash1 -  $multichange2 ;
                        $Mtcashtotal += $Mtcash;                                                        
                            }     
                                    
                                    
                        
                        

                        
                            // <!--  harold 3-16 end-->                 
                    


                
            

                        if ($resultg->mode_of_payment == 'CREDIT CARD - MASTERCARD') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tccard += $resultg->total;
                                }
                        }
                        
                        if ($resultg->mode_of_payment == 'BANCNET') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tbcard += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'GCASH') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tgcash += $resultg->total;
                                }
                        }


                        if ($resultg->mode_of_payment == 'BDO PAY') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tbdopay += $resultg->total;
                                }
                        }
                        

                        if ($resultg->mode_of_payment == 'AMEX') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tamex += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'WE CHAT PAY') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $twcp += $resultg->total;
                                }
                        }
                        
                        

                        

                        if ($resultg->mode_of_payment == 'VISA') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tvisa += $resultg->total;
                                }
                        }
                        
                        
                        if ($resultg->mode_of_payment == 'DINERS') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tdiners += $resultg->total;
                                }
                        }
                        
                        if ($resultg->mode_of_payment == 'DOWN PAYMENT') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tdownpayment += $resultg->total;
                                }
                        }


                        if ($resultg->mode_of_payment == 'GIFT CERT') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tgiftcert += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'JCB') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tjcb += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'PAYMAYA') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tpaymaya += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'GRAB PAY') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tgrabpay += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'UNION PAY') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tunionpay += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'GRAB FOOD') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tgrab += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'FOOD PANDA') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tpanda += $resultg->total;
                                }
                        }

                        if ($resultg->mode_of_payment == 'QR PH') 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tqrph += $resultg->total;
                                }
                        }



                    

                        if ($resultg->id) 
                        {
                            $fResDate = date("Y-m-d", strtotime($resultg->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $tdiscount += $resultg->ld_value;
                                }
                        }
                        

                    }


            

                /* $Mtcash = $Mtcash;
                $ttdiscount =  $tdiscount;
                $tcash = $tcash ;
                $tccard = $tccard;
                $tvisa = $tvisa;
                $tgcash = $tgcash;
                $tamex =   $tamex;
                $tdiners = $tdiners; 
                $tdownpayment = $tdownpayment;
                $tgiftcert = $tgiftcert;
                $tjcb =   $tjcb;
                $tpaymaya = $tpaymaya;

                $twcp = $twcp;
                $tunionpay =  $tunionpay;
                $tgrabpay = $tgrabpay;
                $tbdopay = $tbdopay;
                $tbcard =  $tbcard;
                $tqrph = $tqrph;
                $tgrab = $tgrab;
                $tpanda = $tpanda; */

                
// <!--3-16 harold   $Mtcash = 0 ;  -->
                $Mtccard = 0;
                $Mtvisa = 0;
                $Mtgcash = 0; 
                $Mtamex =  0 ;
                $Mtdiners = 0; 
                $Mtdownpayment = 0;
                $Mtgiftcert = 0;
                $Mtjcb =   0;
                $Mtpaymaya = 0;

                $Mtwcp = 0;
                $Mtunionpay =  0;
                $Mtbdopay = 0;
                $Mtbcard =  0;
                $Mtgrabpay = 0;
                $Mtqrph = 0;
                $Mtgrab = 0;
                $Mtpanda = 0;




                foreach ($multiplepayment as $multiplepaymentresult) {


                    // <!-- harold changes
                    //   if ($multiplepaymentresult->mop == 'CASH') 
                    //    {
                    //       $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                    //       $fCurDate = date("Y-m-d", $feCDate);
                            
                    //      if ($fResDate == $fCurDate) 
                    //          {
                        //         $Mtcash += $multiplepaymentresult->amount;
                    //          }
                    //   }
                        
                    //  --> 

                        if ($multiplepaymentresult->mop == 'CREDIT CARD - MASTERCARD') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtccard += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'BANCNET') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtbcard += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'GCASH') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtgcash += $multiplepaymentresult->amount;
                                }
                        }


                        if ($multiplepaymentresult->mop == 'BDO PAY') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtbdopay += $multiplepaymentresult->amount;
                                }
                        }
                        

                        if ($multiplepaymentresult->mop == 'AMEX') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtamex += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'WE CHAT PAY') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtwcp += $multiplepaymentresult->amount;
                                }
                        }
                        
                        

                        

                        if ($multiplepaymentresult->mop == 'VISA') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtvisa += $multiplepaymentresult->amount;
                                }
                        }
                        
                        
                        if ($multiplepaymentresult->mop == 'DINERS') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtdiners += $multiplepaymentresult->amount;
                                }
                        }
                        
                        if ($multiplepaymentresult->mop == 'DOWN PAYMENT') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtdownpayment += $multiplepaymentresult->amount;
                                }
                        }


                        if ($multiplepaymentresult->mop == 'GIFT CERT') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtgiftcert += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'JCB') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtjcb += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'PAYMAYA') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtpaymaya += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'GRAB PAY') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtgrabpay += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'UNION PAY') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtunionpay += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'QR PH') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtqrph += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'GRAB FOOD') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtgrab += $multiplepaymentresult->amount;
                                }
                        }

                        if ($multiplepaymentresult->mop == 'FOOD PANDA') 
                        {
                            $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                            $fCurDate = date("Y-m-d", $feCDate);
                            
                            if ($fResDate == $fCurDate) 
                                {
                                $Mtpanda += $multiplepaymentresult->amount;
                                }
                        }


                        
                }

                /* $Mtcash = $Mtcash ;
                $Mtccard = $Mtccard;
                $Mtvisa = $Mtvisa;
                $Mtgcash = $Mtgcash;
                $Mtamex =   $Mtamex;
                $Mtdiners = $Mtdiners; 
                $Mtdownpayment = $Mtdownpayment;
                $Mtgiftcert = $Mtgiftcert;
                $Mtjcb =   $Mtjcb;
                $Mtpaymaya = $Mtpaymaya;

                $Mtwcp = $Mtwcp;
                $Mtunionpay =  $Mtunionpay;
                $Mtgrabpay = $Mtgrabpay;
                $Mtbcard =  $Mtbcard;
                $Mtbdopay = $Mtbdopay;
                $Mtqrph = $Mtqrph;
                $Mtgrab = $Mtgrab;
                $Mtpanda = $Mtpanda; */

    
                
                
                $totalCountvoidorder = count($voidorder); 
                $sumvoidorder = 0;
                foreach ($voidorder as $voidorderdisplay) {
                    $sumvoidorder += ($voidorderdisplay->quantity * $voidorderdisplay->amount );
                }
                $sumvoidorder  =  $sumvoidorder;
                 
                    

                $totalcounttranasction = count($results); 
                $totalquantitysold = 0;    
                $customercount = $customercount_buffet = 0; 
                foreach($ordered as $orderscount){
                    $totalquantitysold += $orderscount->quantity;
                }
                foreach($results as $resultcount){
                    $customercount += $resultcount->total_customer;
                }
                

                $grossSales= number_format($tAmount  + $tdiscount + $pwd +$lessVat , 2, '.', ',');
                $totalNet = number_format($tAmount ,2);
                $totalDiscount = number_format($tdiscount ,2);
                $totalPwd = number_format($pwd ,2);
                $totalLessvat = number_format($lessVat,2);
                $totalVatsales = number_format($vatSales,2);
                $totalVatexempt = number_format($VatExempt,2);
                $totalVat = number_format($Vat,2);
                $totalCash = number_format( $tcash + $Mtcashtotal ,2) ;
                // $totalGcash = number_format($tgcash + $Mtgcash ,2);
                // $totalGcredit = number_format($tgcashcredit + $Mtgcashcredit + $tgcashcredit_buffet + $Mtgcashcredit_buffet,2);
                $totalCreditcard = number_format($tccard + $Mtccard,2);
                $totalBancnet = number_format($tbcard + $Mtbcard,2);
                $totalVisa = number_format($tvisa  +  $Mtvisa,2);
                
                $totalJcb = number_format($tjcb + $Mtjcb,2);
                $totalDiners = number_format($tdiners + $Mtdiners,2);
                $totalAmex = number_format($tamex + $Mtamex,2);
                $totalPaymaya = number_format($tpaymaya + $Mtpaymaya,2);
                $totalFoodpanda = number_format($tpanda + $Mtpanda,2);
                $totalGrabfood = number_format($tgrab + $Mtgrab,2);
                $totalGrabpay = number_format($tgrabpay + $Mtgrabpay,2);
                $totalBdopay = number_format($tbdopay + $Mtbdopay,2);
                $totalWechat = number_format($twcp + $Mtwcp,2);
                $totalQrph = number_format($tqrph + $Mtqrph,2);
                $totalDp = number_format($tdownpayment + $Mtdownpayment,2);
                $totalGc = number_format($tgiftcert + $Mtgiftcert,2);
                $totalGcash = number_format($tgcash + $Mtgcash,2);
                $totalUnionpay = number_format($tunionpay + $Mtunionpay,2);
                
                $totalCountvoidorder = number_format($totalCountvoidorder,2);
                $sumvoidorder = number_format($sumvoidorder,2);
                $iCount = number_format($iCount ,2);
                $total_Amount = number_format($tAmount,2);
                $totalnumberwithdiscount = number_format($numberwithdiscount ,2);
                $total_sc = number_format($sc,2);
                $totalquantitysold = number_format($totalquantitysold,2);
                $tranasctioncount = number_format($totalcounttranasction,2);
                $totalcustomercount = number_format($customercount + $customercount_buffet,2);
                
                $alacarte_gross = $tAmount;
                

                $response = array(
                    /* start total */
                    'grossSales' => $grossSales,
                    'totalDiscount' => $totalDiscount,
                    'totalPwd' => $totalPwd,
                    'totalLessvat' => $totalLessvat,
                    'totalVatsales' => $totalVatsales,
                    'totalVatexempt' => $totalVatexempt,
                    'totalVat' => $totalVat,
                    'totalCash' => $totalCash,
                    'totalBancnet' => $totalBancnet ,
                    'totalVisa' => $totalVisa ,
                    'totalCreditcard' => $totalCreditcard,
                    'totalJcb' => $totalJcb,
                    'totalDiners' => $totalDiners,
                    'totalAmex' => $totalAmex,
                    'totalPaymaya' => $totalPaymaya,
                    'totalFoodpanda' => $totalFoodpanda,
                    'totalGrabfood' => $totalGrabfood,
                    'totalGrabpay' => $totalGrabpay,
                    'totalBdopay' => $totalBdopay,
                    'totalWechat' => $totalWechat,
                    'totalQrph' => $totalQrph,
                    'totalDp' => $totalDp,
                    'totalGc' => $totalGc,
                    'totalNet' => $totalNet,
                    'totalCountvoidorder' => $totalCountvoidorder,
                    'sumvoidorder' => $sumvoidorder,
                    'TransactionCount' => $iCount,
                    'tAmount' => $total_Amount,
                    'numberwithdiscount' => $totalnumberwithdiscount,
                    'sc' => $total_sc,
                    'totalquantitysold' => $totalquantitysold,
                    'totalcounttranasction' => $tranasctioncount,
                    'customercount' => $totalcustomercount,
                    'start' => $startDate,
                    'end' => $endDate,
                    'totalGcash' => $totalGcash,
                    'totalUnionpay' => $totalUnionpay,
                );
                /*  */
                
        
                return $response;
                //return view('admin.reports.printzreading', compact('multiplepayment','results', 'settings', 'startDate', 'endDate', 'report','auditted','notAuditted', 'foodFinds', 'studio', 'foodCost'));
            } 
            
    public function zreadingDiscount(){
        $settings = DB::table('settings')->where('id', 1)->first();
        $startDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d 00:00:00')));
        $endDate = date('Y-m-d H:i:s', strtotime('+1 day', strtotime(date('Y-m-d 11:59:59'))));


        $results = DB::table('transactions')
                ->select('transactions.created_at as date','transactions.*'
                )
                ->where('transactions.created_at', '>=', $startDate)
                ->where('transactions.created_at', '<', $endDate)
                ->where('transactions.status', '=','PAID')
                ->where('transactions.order_status', '!=', 'CANCELLED')
                ->orderBy('transactions.id', 'desc')
                ->get();
       
        $discountCounts = $results->groupBy('lime_discount')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_value' => $group->sum('ld_value')
            ];
        });
        $i = 1;
        $response = collect();
        foreach ($discountCounts as $discount => $data)  {
           
                if ( $data['total_value']  != 0){
                    $response->push([
                        'id' => $i,
                        'percent' => $discount,
                        'count' => $data['count'],
                        'value' => $data['total_value'],
                    ]);
                    /* $response['data' . $discount] = $data['count'];  // Store count in response array
                    $response['dataValue' . $discount] = $data['total_value']; */
                    $i++;
                }
            
        }
        /* $response = array(
            'grossSales' => $grossSales,
        ); */
        /*  */
        

        return $response;
    }
    public function zreadingstatus(){
        $response = DB::table('report_forprints')->where('id', 1)->where('print', 0)->first();
        return $response;
    }

    public function editzRead($id){

        $ordered = DB::table('report_forprints')->where('id', $id)->first();
        DB::table('report_forprints')->where('id', $id)
            ->update([
                'print' => 1,
            ]);
            if (!empty($ordered)){
                return $ordered;
            }
        
        //$branches = DB::table('settings')->orderBy('name', 'asc')->where('is_deleted', 0)->get();
        //return view('admin.system-management.tables.edit', compact('table','branches'));
    }
    
    public function apiIndex(){
        $today = date('Y-m-d');
    
        $sales = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->whereDate('created_at', $today)
            ->sum('total'), 2, '.', ',');
    
        $expenses = number_format(DB::table('inventory_transactions')
            ->where('type', 'INCOMING')
            ->whereDate('created_at', $today)
            ->sum('amount'), 2, '.', ',');
    
        // Service charge logic
        $serviceCharge = number_format(DB::table('transactions')
            ->where('order_status', '!=', 'CANCELLED')
            ->where('status', 'PAID')
            ->whereDate('created_at', $today)
            ->sum('service_charge'), 2, '.', ',');
    
        // 4% of service charge added to expenses
        $serviceCharge = (float) str_replace([','], '', $serviceCharge) / 2; 
        $expenses = number_format((float) str_replace([','], '', $expenses) + $serviceCharge, 2, '.', ',');
    
        // Arrays for chart data (last 7 days)
        $daysArray = [];
        $salesArray = [];
        $expensesArray = [];
        $profitArray = [];
    
        for ($i = 0; $i < 7; $i++) {
            $dayTimestamp = strtotime("-$i days");
            $dayLabel = date('M d', $dayTimestamp);
            $seDate = date('Y-m-d', $dayTimestamp);
            
            $s = DB::table('transactions')
                    ->whereDate('created_at', $seDate)
                    ->sum('total');
            
            $e = DB::table('inventory_transactions')
                    ->where('type', 'INCOMING')
                    ->whereDate('created_at', $seDate)
                    ->sum('amount');
            // 4% service charge 
            $sc = DB::table('transactions')
                    ->whereDate('created_at', $seDate)
                    ->sum('service_charge');
            $sc = (float) $sc / 2;
            $e = $e + $sc;
    
            array_push($daysArray, $dayLabel);
            array_push($salesArray, $s);
            array_push($expensesArray, $e);
            array_push($profitArray, ($s - $e));
        }
    
        // Prepare data for chart and API response
        $labels = json_encode(array_reverse($daysArray));
        $salesArray = json_encode(array_reverse($salesArray));
        $expensesArray = json_encode(array_reverse($expensesArray));
        $profitArray = json_encode(array_reverse($profitArray));
    
        // Profit calculation
        $profit = number_format(((float) str_replace([','], '', $sales) - (float) str_replace([','], '', $expenses)), 2, '.', ',');
    
        // Get table data
        $table = DB::table('tables')->where('status', 1)->count();
    
        // Get low stock inventory count
        $LSICount = DB::table('inventories')->whereColumn('inventories.quantity', '<', 'inventories.reorder_point')->count();
        
        // Return the data as JSON (for API)
        return response()->json([
            'sales' => $sales,
            'expenses' => $expenses,
            'profit' => $profit,
            'labels' => $labels,
            'salesArray' => $salesArray,
            'expensesArray' => $expensesArray,
            'profitArray' => $profitArray,
            'table' => $table,
            'LSICount' => $LSICount
        ]);
    }

    public function apichange(Request $request){
        $timeframe = $request->input('timeframe');
        
        $today = date('Y-m-d');

        if($timeframe == '1'){
            $sales = number_format(DB::table('transactions')
                ->where('order_status', '!=', 'CANCELLED')
                ->where('status', 'PAID')
                ->whereDate('created_at', $today)
                ->sum('total'), 2, '.', ',');
    
            $expenses = number_format(DB::table('inventory_transactions')
                ->where('type', 'INCOMING')
                ->whereDate('created_at', $today)
                ->sum('amount'), 2, '.', ',');

            //Service Charge
            $serviceCharge = number_format(DB::table('transactions')
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                ->whereDate('created_at', $today)
                ->sum('service_charge'), 2, '.', ',');

            $serviceCharge = (float) str_replace([','], '', $serviceCharge) / 2; 
            $expenses = number_format((float) str_replace([','], '', $expenses) + $serviceCharge,2,'.',','); 

            $daysArray = array();
            $salesArray = array();
            $expensesArray = array();
            $profitArray = array();

            for ($i = 0; $i < 7; $i++) {
                $dayTimestamp = strtotime("-$i days");
                $dayLabel = date('M d', $dayTimestamp);
                $seDate = date('Y-m-d', $dayTimestamp);
                
                $s = DB::table('transactions')
                        ->whereDate('created_at', $seDate)
                        ->sum('total');
                
                $e = DB::table('inventory_transactions')
                        ->where('type', 'INCOMING')
                        ->whereDate('created_at', $seDate)
                        ->sum('amount');
                //4% service charge 
                $sc = DB::table('transactions')
                        ->whereDate('created_at', $seDate)
                        ->sum('service_charge');
                $sc = (float)$sc / 2 ;
                $e = $e + $sc;

                array_push($daysArray, $dayLabel);
                array_push($salesArray, $s);
                array_push($expensesArray, $e);
                array_push($profitArray, ($s - $e));
            }
            $labels = array_reverse($daysArray);
            $salesArray = array_reverse($salesArray);
            $expensesArray = array_reverse($expensesArray);
            $profitArray = array_reverse($profitArray);
        }elseif($timeframe == '2'){
            $sales = number_format(DB::table('transactions')
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                ->whereRaw("WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())")
                ->sum('total'), 2, '.', ',');

            $expenses = number_format(DB::table('inventory_transactions')
                ->whereRaw("WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())")
                ->sum('amount'), 2, '.', ',');

            //service charge
            $serviceCharge = number_format(DB::table('transactions')
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                ->whereRaw("WEEK(created_at) = WEEK(NOW()) AND YEAR(created_at) = YEAR(NOW())")
                ->sum('service_charge'), 2, '.', ',');

            $serviceCharge = (float) str_replace([','], '', $serviceCharge) / 2;
            $expenses = number_format((float) str_replace([','], '', $expenses) + $serviceCharge,2,'.',',');  

            $currentWeek = date('W');
            $currentYear = date('Y');

            $labels = array();
            $salesArray = array();
            $expensesArray = array();
            $profitArray = array();

            for ($i = 0; $i < 7; $i++) {
                $weekStart = strtotime($currentYear . "W" . str_pad($currentWeek - $i, 2, "0", STR_PAD_LEFT));
                $weekEnd = strtotime('+6 days', $weekStart);
                $weekLabel = date('M d', $weekStart);
                    
                $s = DB::table('transactions')
                        ->where('order_status', '!=', 'CANCELLED')
                        ->whereBetween('created_at', [date('Y-m-d H:i:s', $weekStart), date('Y-m-d H:i:s', $weekEnd)])
                        ->sum('total');
                
                $e = DB::table('inventory_transactions')
                        ->where('type', 'INCOMING')
                        ->whereBetween('created_at', [date('Y-m-d H:i:s', $weekStart), date('Y-m-d H:i:s', $weekEnd)])
                        ->sum('amount');

                //4% service charge 
                $sc = DB::table('transactions')
                        ->where('order_status', '!=', 'CANCELLED')
                        ->whereBetween('created_at', [date('Y-m-d H:i:s', $weekStart), date('Y-m-d H:i:s', $weekEnd)])
                        ->sum('service_charge');
                $sc = (float)$sc / 2 ;
                $e = $e + $sc;
    
                array_push($salesArray, $s);
                array_push($expensesArray, $e);
                array_push($profitArray, ($s - $e));

                array_push($labels, $weekLabel);
            }
            
            $labels = array_reverse($labels);
            $salesArray = array_reverse($salesArray);
            $expensesArray = array_reverse($expensesArray);
            $profitArray = array_reverse($profitArray);

        }elseif($timeframe == '3'){
            $sales = number_format(DB::table('transactions')
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                ->whereRaw("MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")
                ->sum('total'), 2, '.', ',');

            $expenses = number_format(DB::table('inventory_transactions')
                ->whereRaw("MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")
                ->sum('amount'), 2, '.', ',');

            $serviceCharge = number_format(DB::table('transactions')
                ->where('status', 'PAID')
                ->where('order_status', '!=', 'CANCELLED')
                ->whereRaw("MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")
                ->sum('service_charge'), 2, '.', ',');

            $serviceCharge = (float) str_replace([','], '', $serviceCharge) / 2;
                $expenses = number_format((float) str_replace([','], '', $expenses) + $serviceCharge,2,'.',','); 

            $currentMonth = Carbon::now()->format('Y-m');

            $labels = array();
            $salesArray = array();
            $expensesArray = array();
            $profitArray = array();

            for ($i = 0; $i < 7; $i++) {
                $monthStart = Carbon::parse($currentMonth)->subMonths($i);
                $monthEnd = $monthStart->copy()->endOfMonth();
                $months[] = [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')];
                    
                $s = DB::table('transactions')
                        ->where('status', 'PAID')
                        ->where('order_status', '!=', 'CANCELLED')
                        ->whereBetween('created_at', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                        ->sum('total');
                
                $e = DB::table('inventory_transactions')
                        ->where('type', 'INCOMING')
                        ->whereBetween('created_at', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                        ->sum('amount');

                $sc = DB::table('transactions')
                        ->where('status', 'PAID')
                        ->where('order_status', '!=', 'CANCELLED')
                        ->whereBetween('created_at', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                        ->sum('service_charge');
                $sc = (float)$sc / 2 ;
                $e = $e + $sc;
    
                array_push($salesArray, $s);
                array_push($expensesArray, $e);
                array_push($profitArray, ($s - $e));

                array_push($labels, $monthStart->format('M'));
            }
            
            $labels = array_reverse($labels);
            $salesArray = array_reverse($salesArray);
            $expensesArray = array_reverse($expensesArray);
            $profitArray = array_reverse($profitArray);

        }

        $profit = number_format(((float) str_replace([','], '', $sales) - (float) str_replace([','], '', $expenses)), 2, '.', ',');
        //$serviceCharge = (int)$serviceCharge / 2;
        //$serviceCharge = 0;


        
        return response()->json([
            'sales' => $sales,
            'expenses' => $expenses,
            'profit' => $profit,
            'labels' => $labels,
            's' => $salesArray,
            'e' => $expensesArray,
            'sc' => $serviceCharge,
            'p' => $profitArray
        ]);

    }
    
    // API TO PAMAHAW

    //  api request
    public function apiInv() {
        // $this->updateCurrentQuantity();
    
        $tables = DB::table('departments')
            ->orderBy('id', 'asc')
            ->where('is_deleted', 0)
            ->get();
    
        $orders = DB::table('branch_order_requests')
            ->where('cashier', auth()->id())
            ->orderBy('id', 'desc')
            ->get();
    
        // $user = auth()->user();
    
        // if ($user->role == 9) {
        //     $userDepartment = $user->department;
        //     $menus = DB::table('inventories')
        //         ->where('department', $userDepartment)
        //         ->whereIn('branch', [1, 2, 3])
        //         ->where('is_deleted', 0)
        //         ->orderBy('name', 'asc')
        //         ->get();
        // } elseif ($user->role == 3) {
        //     $menus = DB::table('inventories')
        //         ->whereIn('department', [1, 2])
        //         ->where('branch', 2)
        //         ->where('is_deleted', 0)
        //         ->orderBy('name', 'asc')
        //         ->get();
        // } else {
        //     $menus = DB::table('inventories')
        //         ->where('is_deleted', 0)
        //         ->orderBy('name', 'asc')
        //         ->get();
        // }

        $menus = DB::table('menus')
                ->where('is_deleted', 0)
                ->orderBy('name', 'asc')
                ->get();
    
        $categories = DB::table('categories')
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();
    
        // Return the data as a JSON response
        return response()->json([
            'tables' => $tables,
            'orders' => $orders,
            'menus' => $menus,
            'categories' => $categories,
        ]);
    }
    // end of api request

    // api transfer index
    public function ApiTransferIndex() {
        $branchTransactionRequests = DB::table('branch_transaction_requests')
            ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
            ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
            ->where('branch_transaction_requests.order_status','PENDING')
            ->orderBy('branch_transaction_requests.id', 'desc')->paginate(100);
        $branchTransactionCount = DB::table('branch_transaction_requests')->where('order_status','PENDING')->get()->count();
        // $page = 1;
        // $search = "";

        return response()->json([
            'branchTransactionRequests' => $branchTransactionRequests,
            'branchTransactionCount' => $branchTransactionCount,
        ]);
    }
    // end of api transfer index

    // api edit
    public function ApiTransferEdit($slug) {
        $branchTransaction = DB::table('branch_transaction_requests')
            ->where('slug', $slug)
            ->first();
    
        if (!$branchTransaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }
    
        $branchRequests = DB::table('branch_requests')
            ->leftJoin('inventories', 'branch_requests.inv_id', '=', 'inventories.id') 
            ->where('tran_id', $branchTransaction->id)
            ->where('branch_code', $branchTransaction->branch_code)
            ->select('branch_requests.*', 'inventories.item_code', 'inventories.name as inventory_name', 'inventories.quantity as inv_qty') 
            ->get();

        $reqNumber = DB::table('branch_transaction_requests')->where('slug',$slug)->first();

        
    
        return response()->json([
            'transaction' => $branchTransaction,
            'transactionDetails' => $branchRequests,
            'reqNumber' => $reqNumber
        ]);
    }
    // end of api edit

    // api approval
    public function ApiApproval($slug) {
        $branchTransaction = DB::table('branch_transaction_requests')
            ->where('slug', $slug)
            ->first();
    
        if (!$branchTransaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }
    
        $branchRequests = DB::table('branch_requests')
            ->leftJoin('inventories', 'branch_requests.inv_id', '=', 'inventories.id') 
            ->where('tran_id', $branchTransaction->id)
            ->select('branch_requests.*', 'inventories.item_code', 'inventories.name as inventory_name', 'inventories.quantity as inv_qty') 
            ->get();

        $reqNumber = DB::table('branch_transaction_requests')->where('slug',$slug)->first();

        return response()->json([
            'transaction' => $branchTransaction,
            'transactionDetails' => $branchRequests,
            'reqNumber' => $reqNumber
        ]);
    }
    // end of api approval

    // api approved index function
    public function transferApprovedIndex(){
        $orderRequest = DB::table('branch_transaction_requests')
        
            ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
            ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
            ->where('branch_transaction_requests.order_status','APPROVED')
            ->orderBy('branch_transaction_requests.id', 'desc')->paginate(100);
        $requestcount = DB::table('branch_transaction_requests')->where('order_status','APPROVED')->get()->count();
        $page = 1;
        $search = "";

        return response()->json([
            'orderRequest' => $orderRequest,
            'requestcount' => $requestcount,
            'page' => $page,
            'search' => $search
        ]);
    }
    // end of api approved index function

    // api received
    public function ApiTransferReceived($slug) {
        $branchTransaction = DB::table('branch_transaction_requests')
            ->where('slug', $slug)
            ->first();

        if (!$branchTransaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        $branchRequests = DB::table('branch_requests')
            ->leftJoin('inventories', 'branch_requests.inv_id', '=', 'inventories.id') 
            ->where('tran_id', $branchTransaction->id)
            ->select(
                'branch_requests.qty_approved',
                'branch_requests.total_price',
                'branch_requests.remarks',
                'branch_requests.quantity',
                'branch_requests.name',
                'branch_requests.tran_id',
                'branch_requests.current_stock',
                'branch_requests.cashier',
                'branch_requests.department',
                'branch_requests.reorder_point',
                'branch_requests.id',
                'branch_requests.qty_received',
                'branch_requests.slug',
                
                'inventories.item_code',
                'inventories.name as inventory_name',
                'inventories.quantity as inv_qty'
            )
            ->get();
        
        $reqNumber = DB::table('branch_transaction_requests')->where('slug',$slug)->first();

        return response()->json([
            'transaction' => $branchTransaction,
            'transactionDetails' => $branchRequests,
            'reqNumber' => $reqNumber
        ]);
    }
    // end of api received


    // api received view
    public function ApiReceivedIndex(){
        $orderRequest = DB::table('branch_transaction_requests')
            ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
            ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
            ->where('branch_transaction_requests.order_status','RECEIVED')
            ->orderBy('branch_transaction_requests.id', 'desc')->paginate(100);
        $requestcount = DB::table('branch_transaction_requests')->where('order_status','RECEIVED')->get()->count();

        $page = 1;
        $search = "";

        return response()->json([
            'orderRequest' => $orderRequest,
            'requestcount' => $requestcount,
            'page' => $page,
            'search' => $search
        ]);
    }
    // end of api received view

    // api received view
    public function ApiTransferReceivedReview($slug) {
        $branchTransaction = DB::table('branch_transaction_requests')
            ->where('slug', $slug)
            ->first();
    
        if (!$branchTransaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }
        
        $branchRequests = DB::table('branch_requests')
            ->leftJoin('inventories', 'branch_requests.inv_id', '=', 'inventories.id') 
            ->where('tran_id', $branchTransaction->id)
            ->select('branch_requests.*', 'inventories.item_code', 'inventories.name as inventory_name', 'inventories.quantity as inv_qty') 
            ->get();

        $reqNumber = DB::table('branch_transaction_requests')->where('slug',$slug)->first();

        
    
        return response()->json([
            'transaction' => $branchTransaction,
            'transactionDetails' => $branchRequests,
            'reqNumber' => $reqNumber
        ]);
    }
    // end of api received view
    

    
    // END OF API TO PAMAHAW



    // NEW API TO PAMAHAW
    public function ApprovalApi($slug){

        DB::table('branch_transaction_requests')->where('slug', $slug)
            ->update([
                'status' => 'APPROVED',
                'order_status' => 'APPROVED'
            ]);
        // echo 'trans # : '. $id;
        
    }

    public function ApiApprovalChecker() {
        $branchTransactionRequests = DB::table('branch_transaction_requests')
            ->select('branch_transaction_requests.*','users.*','branch_transaction_requests.slug as trSlug')
            ->leftJoin('users','branch_transaction_requests.cashier','=','users.id')
            ->where('branch_transaction_requests.order_status','APPROVE-LOG')
            ->orderBy('branch_transaction_requests.id', 'asc')->paginate(100);
        $branchTransactionCount = DB::table('branch_transaction_requests')->where('order_status','APPROVE-LOG')->get()->count();
        // $page = 1;
        // $search = "";

        return response()->json([
            'branchTransactionRequests' => $branchTransactionRequests,
            'branchTransactionCount' => $branchTransactionCount,
        ]);
    }
    

    public function ReceivedApi($slug){

        DB::table('branch_transaction_requests')->where('slug', $slug)
            ->update([
                'status' => 'RECEIVED',
                'order_status' => 'RECEIVED'
            ]); 
    }

    public function ApproveIncQty($slug){
        $tran = db::table('branch_transaction_requests')->where('slug' , $slug)->first();

        $ordered = db::table('branch_requests')->where('tran_id' , $tran->id)->get();
        
        foreach($ordered as $order){
            $inv = db::table('inventories')->where('masterlist_id' , $order->masterlist_id)->where('branch', $order->branch)->first();

            $newQty = $inv->quantity + $order->qty_received;
            DB::table('inventories')->where('masterlist_id' , $order->masterlist_id)->where('branch', $order->branch)->update([
                'quantity' => $newQty,
            ]);
            DB::table('branch_requests')->where('id', $order->id)->update([
                'remarks' => 'APPROVED',
            ]);
            
        }

        // DB::table('branch_transaction_requests')->where('slug', $slug)
        //     ->update([
        //         'status' => 'RECEIVED',
        //         'order_status' => 'RECEIVED'
        //     ]); 
    }
    // END OF NEW API TO PAMAHAW
}
