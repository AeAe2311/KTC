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
        <header class="flex flex-col  w-[90%] pb-1">
            <h2 class="text-3xl">{{ $settings->name }}</h2>
            <p class="text-2xl">{{ $settings->address }}</p>
            <h2 class="text-2xl">TIN # : {{ $settings->tin }}</h2>
            <h2 class="text-2xl">Trans # : {{$trans->number}}</h2>
            <h2 class="text-2xl">Table : {{$trans->table_name}}   </h2>
            <h2 class="text-2xl">Server : {{$trans->name}}   </h2>
            <h2 class="text-2xl">DATE : {{ date('m/d/Y H:i:s A', strtotime($trans->created_at)) }}   </h2>

            <hr class="w-full my-1 border-black border-2 border-dashed" >

            <h2 class="text-3xl mt-2 mb-2">Order Slip</h2>

            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >
        </header>

        <!-- -----------------------------------------------   -->
        @if($orderdineincount > 0)
        <!--<div class="w-full flex justify-between">-->
        <!--<p class="text-2xl"></p>   -->
        <!--<p class="text-3xl  items-center font-bold">Dine-in</p>-->
        <!--<p class="text-2xl"></p>-->
        <!--</div>-->
        @else

        @endif

        <div class="w-[90%] mt-1 ">
            <div class="w-full">
           
                @php
                    $subtotal1 = 0;
                @endphp
                @foreach ($orders as $order)
                    @if ($order->type==0)                  
                    @php

                        $isPwd = ($order->is_pwd == 0) ? 'x' : 's';
                    @endphp
                    @if ($order->item_takeout == 0)
                    <div class="w-full flex justify-between">
                        <p class="text-2xl">{{$order->quantity}}</p>
                        
                        <!-- Centered element with absolute positioning -->
                        <p class="text-2xl text-center px-3">
                            {{$order->name}}
                        </p>

                        <p class="whitespace-nowrap pl-3 text-3xl">
                            {{ ($order->type == 0) ? number_format($order->amount, 2, '.', ',') : '0' }} {{ $isPwd }}
                        </p>
                        </div>
                    @php
                    $subtotal1 = $subtotal1 + (($order->type == 0) ? $order->amount : 0 );                
                    @endphp
                    @else
                    @endif
                   
                    @endif
                @endforeach
                
                @if($orderdineincount > 0)
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                @else
                @endif


                @if($ordertakeoutcount> 0)
                <!--<p class="text-3xl  text-center font-bold">Take-Out</p>-->
                @else
                @endif

                @php
                    $subtotal2 = 0;
                @endphp
                @foreach ($orders as $order)
                    @if ($order->type==0)                  
                    @php

                        $isPwd = ($order->is_pwd == 0) ? 'x' : 's';
                    @endphp
                    @if ($order->item_takeout == 1)
                    
                    <div class="w-full flex justify-between">
                        <p class="text-2xl">{{$order->quantity}}</p>
                        
                        <!-- Centered element with absolute positioning -->
                        <p class="text-2xl text-center px-3">
                            {{$order->name}}
                        </p>

                        <p class="whitespace-nowrap pl-3 text-3xl">
                            {{ ($order->type == 0) ? number_format($order->amount, 2, '.', ',') : '0' }} {{ $isPwd }}
                        </p>
                        </div>
                    @php

                    $subtotal2 = $subtotal2 + (($order->type == 0) ? $order->amount : 0 );
                    @endphp
                    @else
                    @endif
              

                   
                    @endif
                @endforeach




                @php
                $subtotal_NoSC =  $subtotal1 + $subtotal2 ;
                $subtotal = $subtotal1;
                    //$discount = ($subtotal+$trans->service_charge) - $trans->total;
                    //if ($discount <= 0){
                    //    $discount = 0;
                    // }
                    //$SCVal = $trans->total - ($subtotal + $discount);
                    $change = $trans->amount - $trans->total;
                    if ($change < 0 ){
                        $change = 0;
                    }
                @endphp
                @php
         
                    $vatsales = 0;
                    $vat = 0;
                    $net = 0;
                    $discount = 0;
                    $VatExemptSales = 0;
                    $LessVatDeduction =0;
                    $SubtotalLessDiscount = 0;
                    $net = round( ($subtotal + $subtotal2) /1.12,2);
                    if ($trans->total_customer > 0) {
                        $x = round($net / $trans->total_customer,2);
                        $y = 0.2 * round($trans->customer_with_discount,2);
                        $VatExemptSales = round($x,2) * round($trans->customer_with_discount,2);
                        $discount = 0.2 * $VatExemptSales;
                        $LessVatDeduction =round($VatExemptSales*0.12,2);
                    }
                    $SubtotalLessDiscount = round(($subtotal + $subtotal2 )-($discount+$LessVatDeduction),2);
                    $SCVal = $trans->service_charge ;

                    //$limeVal = ($limePercent / 100) * $SubtotalLessDiscount;


                    $vatsales = round(($net) - ($VatExemptSales),2);

                    $vat = round($vatsales * 0.12,2);
                    
                    $total = round(( ($subtotal + $subtotal2 )  + $SCVal -($LessVatDeduction+$discount)),2);


                    //$vatsales = round($net - ($trans->ld_value + $trans->pwd),2);
                    //$vat = round($vatsales * 0.12,2);
                @endphp




                @if($ordertakeoutcount> 0)
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                @else
                @endif
            </div>

            <div class="w-full mt-5 mb-3">
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>Subtotal</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($subtotal_NoSC, 2, '.', ',')}}</p>
                </div>
                <!-- ₱ -->
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>Less vat deduction :</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($trans->lessVatDeduction, 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>Discount</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($trans->ld_value, 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>Pwd/Sr ( {{ $trans->customer_with_discount}} )</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($trans->pwd, 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>Service charge:</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($trans->service_charge, 2, '.', ',')}}</p>
                </div>
                
                <div class="w-full flex justify-between text-2xl font-bold">
                    <p class="text-4xl">TOTAL</p>
                    <p class="whitespace-nowrap pl-3 text-4xl"> {{number_format($trans->total, 2, '.', ',')}}</p>
                </div>

                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >

                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>VAT SALES : </p>
                    <p class="whitespace-nowrap pl-3">{{number_format($trans->vatSales , 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>VAT EXEMPT SALES : </p>
                    <p class="whitespace-nowrap pl-3">{{number_format($trans->vatExemptSales , 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>VAT : </p>
                    <p class="whitespace-nowrap pl-3">{{number_format($trans->vat, 2, '.', ',')}}</p>
                </div>

                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >

                <div class="w-full flex justify-between text-4xl font-bold">
                    <p>TOTAL</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($trans->total, 2, '.', ',')}}</p>
                </div>
                @if ($trans->amount > 0)
                
                
                @if ($trans->mode_of_payment == 'MULTIPLE')
                
                    <hr class="w-full my-1 border-black border-2 border-dashed" >
                    <hr class="w-full my-1 border-black border-2 border-dashed" >
                        <div class="w-full flex justify-center text-3xl ">
                        <p>Mode of Payments</p>
                        </div>
                        @foreach ($payments as $pay)
                        
                        <div class="w-full flex justify-between text-4xl ">
                            
                            <p> {{ $pay->mop }} </p>
                            <p class="whitespace-nowrap pl-3"> {{number_format($pay->amount, 2, '.', ',')}}</p>
                        </div>

                        @endforeach
                    <hr class="w-full my-1 border-black border-2 border-dashed" >
                        <div class="w-full flex justify-between text-1xl ">
                            <p>Received Amount</p>
                            <p class="whitespace-nowrap pl-3"> {{number_format($trans->amount, 2, '.', ',')}}</p>
                        </div>
                @else
                    <div class="w-full flex justify-between text-1xl">
                        <p>Received Amount</p>
                        <p class="whitespace-nowrap pl-3"> {{number_format($trans->amount, 2, '.', ',')}}</p>
                    </div>
                @endif
                <div class="w-full flex justify-between text-1xl ">
                    <p>Change</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($change, 2, '.', ',')}}</p>
                </div>
                @if ($seniors->count() > 0)
                    <hr class="w-full my-1 border-black border-2 border-dashed" >
                    <hr class="w-full my-1 border-black border-2 border-dashed" >
                
                    <div class="w-full flex justify-between text-2xl font-bold">
                        <p>Mandate Discount Details</p>
                    </div>
                    @foreach ($seniors as $senior)
                        <div class="w-full flex justify-between text-2xl font-bold">
                        <p>{{ $senior->id_no}}</p>
                        <p>{{ $senior->name}}</p>
                        </div>
                        <div class="w-full flex justify-between text-2xl font-bold">
                        <p>Signature : </p>
                        <p>__________________________</p>
                        </div>
                    @endforeach
                @endif
                
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >

                <div class="w-full flex justify-between text-1xl ">
                    <p># of Guest</p>
                    <p class="whitespace-nowrap pl-3"> {{ $trans->total_customer}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl ">
                    <p>with Discount</p>
                    <p class="whitespace-nowrap pl-3"> {{ $trans->customer_with_discount}}</p>
                </div>

                <h2 class="text-2xl mt-2 mb-2">Payment Details</h2>
                @foreach ($payments as $pay)
                <h2 class="text-1xl ">{{ $pay->mop }}</h2>
                <h2 class="text-1xl ">{{ number_format($pay->amount,2) }}</h2>
                @endforeach

                <div class=" text-1xl ">{{ $settings->footer }}</div>


                @endif
                
                {{--<div class="w-full flex justify-between text-2xl font-bold">
                    <p >Remarks</p>
                </div>
                <div class="w-full flex text-wrap text-1xl font-bold">
                    <p class="">{{$trans->order_remarks}}</p>
                </div>--}}
            </div>
            
        </div>
        
    </div>
    


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