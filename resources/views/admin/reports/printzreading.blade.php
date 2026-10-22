<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reciept</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/solid.css">
    <link rel="shortcut icon" href="{{ url('logo.ico') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    
    <style>
      /* Default styles */
      header, footer {
        display: block;
      }
  
      /* Styles for printing */
      @media print {
        header, footer {
          display: none;
        }
      }
    </style>
</head>
<body>





    <div class="flex flex-col items-center pt-14">
        <div class="flex flex-col items-center text-center w-full">
        <header class="flex flex-col items-center  w-[90%] pb-1">
            <h2 class="text-1xl items-center">{{ $settings->name }}</h2>
            <h2 class="text-1xl items-center">FINE DAHON CORPORATION</h2>
            <p class="text-1xl items-center">{{ $settings->address }}</p>
            <h2 class="text-1xl items-center">VAT Registered TIN: 010-413-834-00001</h2>
            <h2 class="text-1xl items-center">MSN: 8812622080413</h2>
            {{--<p class="text-2xl">{{ $settings->number }}</p>--}}
            <h2 class="text-1xl">Z-READING (END OF THE DAY REPORT)</h2>
            <!--<hr class="w-full my-2 border-black border-2 border-dashed" >-->

            <h2 class="text-2xl text-center tracking-widest"></h2>
            </div>
            <h1 class="flex items-center">{{ date('M j, Y', strtotime($startDate)).' - '.date('M j, Y', strtotime('-1 day', strtotime($endDate))) }}</h1>
            <hr class="w-full my-1 border-black border-2 border-dashed" >
            

            @php
                                            $feCDate = strtotime($startDate);
                                            $feEDate = strtotime($endDate);
                                            
                                        @endphp
                                        @while ($feCDate < $feEDate)
                                            @php
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
                                                $tgcashcredit = 0;

                                                $tamex = 0;
                                                $tbanktransfer = 0;
                                                $tdcard = 0;
                                                $tdiners = 0; 

                                                $tdownpayment = 0;
                                                $tgiftcert = 0;
                                                $tjcb = 0;
                                                $tpaymaya = 0;
                                                $tpaymayaqr = 0;
                                                $tunionbank = 0;

                                                $tdiscount = 0;


                                                // <!--  harold 3-16 changes-->
                                                $tchange = 0;
                                             
                                                $Mtcash = 0 ;
                                                $multichange = 0;
                                                $multichange2 = 0;                                   
                                                $Mtcashtotal = 0;
                                                $numberwithdiscount = 0;
                                                foreach ($results as $resultg) 
                                                {
                                                      
                                                    $numberwithdiscount += $resultg->customer_with_discount;
                                                    if ($resultg->mode_of_payment == 'CASH') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($resultg->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                                $tcash += $resultg->total;                                                                                    
                                                              }


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

                                                      if ($resultg->mode_of_payment == 'DEBIT') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($resultg->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                               $tdcard += $resultg->total;
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


                                                      if ($resultg->mode_of_payment == 'GCASH CREDIT GIVES') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($resultg->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                              $tgcashcredit += $resultg->total;
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

                                                      if ($resultg->mode_of_payment == 'BANK TRANSFER') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($resultg->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                              $tbanktransfer += $resultg->total;
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

                                                      if ($resultg->mode_of_payment == 'PAYMAYA QR') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($resultg->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                               $tpaymayaqr += $resultg->total;
                                                              }
                                                      }

                                                      if ($resultg->mode_of_payment == 'UNION BANK') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($resultg->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                               $tunionbank += $resultg->total;
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


                                          

                                                $Mtcash = $Mtcash;
                                                $ttdiscount =  $tdiscount;
                                                $tcash = $tcash ;
                                                $tccard = $tccard;
                                                $tvisa = $tvisa;

                                                $tgcash = $tgcash;
                                                $tgcashcredit = $tgcashcredit;

                                                $tamex =   $tamex;
                                                $tbanktransfer = $tbanktransfer;
                                                $tdcard =  $tdcard;
                                                $tdiners = $tdiners; 

                                                $tdownpayment = $tdownpayment;
                                                $tgiftcert = $tgiftcert;
                                                $tjcb =   $tjcb;
                                                $tpaymaya = $tpaymaya;
                                                $tpaymayaqr = $tpaymayaqr;
                                                $tunionbank =  $tunionbank;

                                              
                           // <!--3-16 harold   $Mtcash = 0 ;  -->
                                                $Mtccard = 0;
                                                $Mtvisa = 0;

                                                $Mtgcash = 0;
                                                $Mtgcashcredit = 0;

                                                $Mtamex =  0 ;
                                                $Mtbanktransfer = 0;
                                                $Mtdcard =  0;
                                                $Mtdiners = 0; 

                                                $Mtdownpayment = 0;
                                                $Mtgiftcert = 0;
                                                $Mtjcb =   0;
                                                $Mtpaymaya = 0;
                                                $Mtpaymayaqr = 0;
                                                $Mtunionbank =  0;


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

                                                      if ($multiplepaymentresult->mop == 'DEBIT') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                               $Mtdcard += $multiplepaymentresult->amount;
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


                                                      if ($multiplepaymentresult->mop == 'GCASH CREDIT GIVES') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                              $Mtgcashcredit += $multiplepaymentresult->amount;
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

                                                      if ($multiplepaymentresult->mop == 'BANK TRANSFER') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                              $Mtbanktransfer += $multiplepaymentresult->amount;
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

                                                      if ($multiplepaymentresult->mop == 'PAYMAYA QR') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                               $Mtpaymayaqr += $multiplepaymentresult->amount;
                                                              }
                                                      }

                                                      if ($multiplepaymentresult->mop == 'UNION BANK') 
                                                      {
                                                          $fResDate = date("Y-m-d", strtotime($multiplepaymentresult->date));  
                                                          $fCurDate = date("Y-m-d", $feCDate);
                                                          
                                                          if ($fResDate == $fCurDate) 
                                                              {
                                                               $Mtunionbank += $multiplepaymentresult->amount;
                                                              }
                                                      }


                                                     
                                                }

                                                $Mtcash = $Mtcash ;
                                                $Mtccard = $Mtccard;
                                                $Mtvisa = $Mtvisa;

                                                $Mtgcash = $Mtgcash;
                                                $Mtgcashcredit = $Mtgcashcredit;

                                                $Mtamex =   $Mtamex;
                                                $Mtbanktransfer = $Mtbanktransfer;
                                                $Mtdcard =  $Mtdcard;
                                                $Mtdiners = $Mtdiners; 

                                                $Mtdownpayment = $Mtdownpayment;
                                                $Mtgiftcert = $Mtgiftcert;
                                                $Mtjcb =   $Mtjcb;
                                                $Mtpaymaya = $Mtpaymaya;
                                                $Mtpaymayaqr = $Mtpaymayaqr;
                                                $Mtunionbank =  $Mtunionbank;

                                 
                                                $totalcreditcard = ($Mtvisa +  $Mtamex +   $Mtdiners + $Mtjcb +   $Mtunionbank +  $Mtccard) 
                                                            + ($tvisa +  $tamex +   $tdiners + $tjcb +   $tunionbank + + $tccard);
                                            @endphp
                                            
            
                                            

            

            <div class="w-full mt-5 mb-3">
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>Gross Sales</p>
                    <p class="whitespace-nowrap pl-3">  ₱ {{ number_format($tAmount  + $tdiscount + $pwd +$lessVat , 2, '.', ',') }}</p>
                </div>
             
                <div class="w-full flex justify-between text-2xl font-bold">
                    <p>Deduction Due To: </p>
                    <p class="whitespace-nowrap pl-3"></p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>DISCOUNT</p>
                    <p class="whitespace-nowrap pl-3"> ₱   {{ number_format($tdiscount ,2) }}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>PWD/SrCitizen</p>
                    <p class="whitespace-nowrap pl-3"> ₱   {{ number_format($pwd,2) }}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p> Less Vat </p>
                    <p class="whitespace-nowrap pl-3">   ₱   {{ number_format($lessVat,2) }} </p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>Net Sales</p>
                    <p class="whitespace-nowrap pl-3">   ₱   {{ number_format($tAmount,2) }} </p>           
                </div>
                <hr class="w-full my-1 border-black border-2 border-dashed" >


                <div class="w-full flex justify-between text-1xl font-bold">
                   <p>Vatable Sales</p>
                   <p class="whitespace-nowrap pl-3"> ₱   {{ number_format($vatSales,2) }}</p>
                </div>

                <div class="w-full flex justify-between text-1xl font-bold">
                   <p>Vatable Exempt Sales</p>
                   <p class="whitespace-nowrap pl-3">  ₱   {{ number_format($VatExempt,2) }}</p>
                </div>

                <div class="w-full flex justify-between text-1xl font-bold">
                   <p> Vat Amount</p>
                   <p class="whitespace-nowrap pl-3">  ₱   {{ number_format($Vat,2) }}</p>
                </div>

                <div class="w-full flex justify-between text-1xl font-bold">
                   <p> Zero Rated</p>
                   <p class="whitespace-nowrap pl-3">₱ 0.00</p>
                </div>



                <hr class="w-full my-1 border-black border-2 border-dashed" >
               
                
               
                <div class="w-full flex justify-between text-1xl font-bold">
                   <p> Cash</p>
                   <p class="whitespace-nowrap pl-3"> ₱   {{ number_format( $tcash + $Mtcashtotal,2) }}</p> <!-- 3-16 harold changes -->
                </div>

                <div class="w-full flex justify-between text-1xl font-bold">
                   <p> Gcash</p>
                   <p class="whitespace-nowrap pl-3"> ₱   {{ number_format($tgcash + $Mtgcash,2) }}</p>
                </div>


                <div class="w-full flex justify-between text-1xl font-bold">
                   <p>  Gcash Credit</p>
                   <p class="whitespace-nowrap pl-3">  ₱   {{ number_format($tgcashcredit + $Mtgcashcredit,2) }}</p>
                </div>


                <div class="w-full flex justify-between text-1xl font-bold">
                   <p>  Credit Card</p>
                   <p class="whitespace-nowrap pl-3">    ₱   {{ number_format($totalcreditcard,2) }}</p>
                </div>


                <div class="w-full flex justify-between text-1xl font-bold">
                   <p> Debit Card</p>
                   <p class="whitespace-nowrap pl-3"> ₱   {{ number_format($tdcard + $Mtdcard,2) }} </p>
                </div>

                <div class="w-full flex justify-between text-1xl font-bold">
                   <p> Paymaya</p>
                   <p class="whitespace-nowrap pl-3"> ₱   {{ number_format($tpaymaya  +  $Mtpaymaya,2) }}</p>
                </div>


                <div class="w-full flex justify-between text-1xl font-bold">
                   <p>  Paymaya Qr</p>
                   <p class="whitespace-nowrap pl-3"> ₱   {{ number_format($tpaymayaqr + $Mtpaymayaqr,2) }}</p>
                </div>

                <div class="w-full flex justify-between text-1xl font-bold">
                   <p>  Downpayment</p>
                   <p class="whitespace-nowrap pl-3">    ₱   {{ number_format($tdownpayment + $Mtdownpayment,2) }}</p>
                </div>

                <div class="w-full flex justify-between text-1xl font-bold">
                   <p>  Gift Cert</p>
                   <p class="whitespace-nowrap pl-3">  ₱   {{ number_format($tgiftcert + $Mtgiftcert,2) }}</p>
                    </div>
          
            <hr class="w-full my-1 border-black border-2 border-dashed" >


            @php
                    $totalCountvoidorder = count($voidorder); 
                    $sumvoidorder = 0;
                @endphp

                @foreach ($voidorder as $voidorderdisplay) 
                @php
                 $sumvoidorder += ($voidorderdisplay->quantity * $voidorderdisplay->amount );
                 @endphp
                @endforeach  

                @php
                $sumvoidorder  =  $sumvoidorder;
                @endphp
                <div class="w-full flex justify-between relative text-1xl font-bold">
                <p> Item Void </p>
                <p class="text-center absolute left-1/2 transform -translate-x-1/2">{{ $totalCountvoidorder }} </p>
                <p class="whitespace-nowrap pl-3">₱ {{ $sumvoidorder,2 }}</p>
                </div>


            <div class="w-full flex justify-between relative text-1xl font-bold">
                   <p>No. of Trans.</p>
                   <p class="text-center absolute left-1/2 transform -translate-x-1/2"> {{  $iCount}}</p>  
                   <p class="whitespace-nowrap pl-3">   ₱  {{  $tAmount}}</p>  
                </div>

                <div class="w-full flex justify-between relative text-1xl font-bold">
                   <p>No. PWD/SrC</p>
                   <p class="text-center absolute left-1/2 transform -translate-x-1/2"> {{  $numberwithdiscount}}</p>  
                   <p class="whitespace-nowrap pl-3">  ₱   {{ number_format($pwd,2) }}</p>  
                </div>

                @php
                    $discountCounts = $results->groupBy('lime_discount')->map(function ($group) {
                        return [
                            'count' => $group->count(),
                            'total_value' => $group->sum('ld_value')
                        ];
                    });
                @endphp

                @foreach ($discountCounts as $discount => $data)            
                    @if ($fResDate == $fCurDate) 
                         <div class="w-full flex justify-between relative text-1xl font-bold">
                            @if ( $data['total_value']  != 0)
                            <p>{{ $discount }}% Discount</p>
                            <p class="text-center absolute left-1/2 transform -translate-x-1/2">{{ $data['count'] }}</p>
                            <p class="whitespace-nowrap pl-3">₱ {{ $data['total_value'],2 }}</p>
                            
                            @else
                            @endif
                        </div>
                    @endif
                @endforeach                   
                
                <div class="w-full flex justify-between relative text-1xl font-bold">
                   <p>Service Charge</p>
                   <p class="text-center absolute left-1/2 transform -translate-x-1/2"> </p>  
                   <p class="whitespace-nowrap pl-3">  ₱   {{ number_format($sc,2) }}</p>  
                </div>

                <hr class="w-full my-1 border-black border-2 border-dashed" >                
              
                @php 
                $totalcounttranasction = count($results); 
                $totalquantitysold = 0;    
                $customercount = 0;       
                @endphp

                @foreach($ordered as $orderscount)
                @php
                $totalquantitysold += $orderscount->quantity;
                @endphp
                @endforeach

                @foreach($results as $resultcount)
                @php
                $customercount += $resultcount->total_customer;
                @endphp
                @endforeach  

                <div class="w-full flex justify-between relative text-1xl font-bold">
                   <p>Item Sold</p>
                   <p class="text-center absolute left-1/2 transform -translate-x-1/2"> </p>  
                   <p class="whitespace-nowrap pl-3"> {{$totalquantitysold}}</p>  
                </div>

                <div class="w-full flex justify-between relative text-1xl font-bold">
                   <p>Transaction Count</p>
                   <p class="text-center absolute left-1/2 transform -translate-x-1/2"> </p>  
                   <p class="whitespace-nowrap pl-3"> {{$totalcounttranasction}}</p>  
                </div>

                <div class="w-full flex justify-between relative text-1xl font-bold">
                   <p>Total Guest</p>
                   <p class="text-center absolute left-1/2 transform -translate-x-1/2"> </p>  
                   <p class="whitespace-nowrap pl-3"> {{$customercount}}</p>  
                </div>

                <hr class="w-full my-1 border-black border-2 border-dashed" >   
                 <div class="w-full flex justify-between relative text-1xl font-bold">
                   <p>Total Sales</p>
                   <p class="text-center absolute left-1/2 transform -translate-x-1/2"> </p>  
                   <p class="whitespace-nowrap pl-3"> ₱   {{ number_format($tAmount,2) }}</p>  
                </div>
                <hr class="w-full my-1 border-black border-2 border-dashed" >   

        </div>















        <p class="text-center text-1xl mt-10">*** END OF BILLING ***</p>
        <div class="font-semibold text-2xl "></div>
    </div>
    
    @php
                $feCDate = strtotime('+1', $feEDate);
                                        @endphp
                                        @endwhile


    <script>
        $(document).ready(function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        });
    </script>
</body>
</html>