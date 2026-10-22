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
        <header class="flex flex-col items-center  w-[90%] pb-1">
            <!-- <img src="{{ asset('storage/'.$settings->logo) }}" class="w-1/2" alt=""> -->
            <h2 class="text-3xl">{{ $settings->name }}</h2>
            <h2 class="text-2xl">owned & operated by: GOLDEN MANAO INC.</h2>
            <p class="text-2xl">{{ $settings->address }}</p>
            <h2 class="text-2xl">VAT Registered TIN: 601-931-000-000</h2>
            <h2 class="text-2xl">SN: M22A04460  MIN: 23010916424154276</h2>
            {{--<p class="text-2xl">{{ $settings->number }}</p>--}}

            <hr class="w-full my-2 border-black border-2 border-dashed" >

            <h2 class="text-2xl text-center tracking-widest">SERVICE INVOICE</h2>

            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >

            <p class="w-full text-2xl font-bold text-right">DATE : {{ date('m/d/Y H:i:s A', strtotime($trans->created_at)) }}</p>
            <p class="w-full text-2xl font-bold text-right">LOGDATE : {{ date('m/d/Y', strtotime($trans->created_at)) }}</p>

            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >

            <h2 class="text-3xl font-bold">TABLE# {{$trans->table_name}}</h2>

            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >


            <div class="flex justify-between flex-row w-full">
                <p class="text-1xl font-bold">TERMINAL : 002</p>
                <p class="text-1xl font-bold">CASHIER: {{ Auth::user()->name }}</p>
            </div>
            
            <div class="flex justify-between flex-row w-full">
                <p class="text-1xl font-bold">TRANS#{{$trans->number}}</p>
                <p class="text-1xl font-bold">ORDER#000000{{ $trans->id }}</p>
            </div>
            <div class="flex justify-between flex-row w-full">
                <p class="text-1xl font-bold">NUMBER OF PAX : {{$trans->total_customer}}</p>
                <p class="text-1xl font-bold">MOP : {{$trans->mode_of_payment}}</p>
            </div>
            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >

            <div class="flex justify-between w-full">
                <p class="text-1xl font-bold">CUST. NAME : <span class="underline ">{{$trans->payor_name}}<span></p>
            </div>

            <div class="flex justify-between w-full">
                <p class="text-1xl font-bold">Address :</p>
            </div>
            <div class="flex justify-between w-full mt-6">
                <p class="text-1xl font-bold">TIN :</p>
            </div>
            <div class="flex justify-between w-full ">
                <p class="text-1xl font-bold">Bus. Style :</p>
            </div>

            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >

            <div class="w-full flex justify-between">
                <p class="text-2xl">QTY.</p>   
                <p class="text-2xl text-center">PARTICULAR</p>
                <p class="text-2xl">AMOUNT</p>
            </div>
            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >
        </header>
        <div class="w-[90%] mt-5 ">
            <div class="w-full">
                @php
                    $subtotal = 0;
                @endphp
                @foreach ($orders as $order)
                    @if ($order->type==0)
                    
                    
                    @php

                        $isPwd = ($order->is_pwd == 0) ? 'x' : 's';
                    @endphp
                    <div class="w-full flex justify-between">
                        <p class="text-2xl">{{$order->quantity}}</p>
                        <p class="text-2xl text-center">{{$order->name}}</p>
                        <!-- <i class="uil uil-times mr-1"></i> -->
                        <p class="whitespace-nowrap pl-3 text-3xl"> {{ ($order->type == 0) ? number_format($order->amount, 2, '.', ',') : '0' }} {{ $isPwd }}</p>
                    </div>
                    @php

                        $subtotal = $subtotal + (($order->type == 0) ? $order->amount : 0 );
                    @endphp
                    @endif
                @endforeach
                @php
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
                    $net = round($subtotal/1.12,2);
                    if ($trans->total_customer > 0) {
                        $x = round($net / $trans->total_customer,2);
                        $y = 0.2 * round($trans->customer_with_discount,2);
                        $VatExemptSales = round($x,2) * round($trans->customer_with_discount,2);
                        $discount = 0.2 * $VatExemptSales;
                        $LessVatDeduction =round($VatExemptSales*0.12,2);
                    }
                    $SubtotalLessDiscount = round($subtotal-($discount+$LessVatDeduction),2);
                    $SCVal = $trans->service_charge ;

                    //$limeVal = ($limePercent / 100) * $SubtotalLessDiscount;


                    $vatsales = round(($net) - ($VatExemptSales),2);

                    $vat = round($vatsales * 0.12,2);
                    
                    $total = round(($subtotal + $SCVal -($LessVatDeduction+$discount)),2);


                    //$vatsales = round($net - ($trans->ld_value + $trans->pwd),2);
                    //$vat = round($vatsales * 0.12,2);
                @endphp
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >
            </div>
            <div class="w-full mt-5 mb-3">
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>SUB-TOTAL</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($subtotal, 2, '.', ',')}}</p>
                </div>
                <!-- ₱ -->
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>LESS VAT DEDUCTION :</p>
                    <p class="whitespace-nowrap pl-3">- {{number_format($trans->lessVatDeduction, 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>{{ $trans->lime_discount }}% DISCOUNT [{{ $trans->lime_discount }}%]</p>
                    <p class="whitespace-nowrap pl-3">- {{number_format($trans->ld_value, 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>20%PWD/SrCitizen[20%]</p>
                    <p class="whitespace-nowrap pl-3">- {{number_format($trans->pwd, 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>SERVICE CHARGE: [{{ $trans->sc_percent }}%]</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($trans->service_charge, 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p></p>
                    <hr class="w-40 my-1 border-black border-2 border-dashed" >
                </div>
                <div class="w-full flex justify-between text-2xl font-bold">
                    <p class="text-3xl">TOTAL</p>
                    <p class="whitespace-nowrap pl-3 text-3xl"> {{number_format($trans->total, 2, '.', ',')}}</p>
                </div>


                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>VAT SALES : </p>
                    <p class="whitespace-nowrap pl-3">{{number_format($trans->vatSales , 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>NON-VAT SALES :</p>
                    <p class="whitespace-nowrap pl-3">0.00</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>VAT EXEMPT SALES : </p>
                    <p class="whitespace-nowrap pl-3">{{number_format($trans->vatExemptSales , 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>VAT : </p>
                    <p class="whitespace-nowrap pl-3">{{number_format($trans->vat, 2, '.', ',')}}</p>
                </div>
                <div class="w-full flex justify-between text-1xl font-bold">
                    <p>ZERO-RATED SALES : </p>
                    <p class="whitespace-nowrap pl-3">{{number_format(0, 2, '.', ',')}}</p>
                </div>
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                <div class="w-full flex justify-between text-2xl font-bold">
                    <p>TOTAL</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($trans->total, 2, '.', ',')}}</p>
                </div>
                @if ($trans->mode_of_payment == 'MULTIPLE')
                
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                    <div class="w-full flex justify-center text-1xl font-bold">
                    <p>Mode of Payments</p>
                    </div>
                    @foreach ($payments as $pay)
                    
                    <div class="w-full flex justify-between text-1xl font-bold">
                        
                        <p> {{ $pay->mop }} </p>
                        <p class="whitespace-nowrap pl-3"> {{number_format($pay->amount, 2, '.', ',')}}</p>
                    </div>

                    @endforeach
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                    <div class="w-full flex justify-between text-2xl font-bold">
                        <p>Received Amount</p>
                        <p class="whitespace-nowrap pl-3"> {{number_format($trans->amount, 2, '.', ',')}}</p>
                    </div>
                @else
                <div class="w-full flex justify-between text-2xl font-bold">
                    <p>Received Amount</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($trans->amount, 2, '.', ',')}}</p>
                </div>
                @endif
                

                <div class="w-full flex justify-between text-2xl font-bold">
                    <p>Change</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($change, 2, '.', ',')}}</p>
                </div>
                
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
                {{--<div class="w-full flex justify-between text-2xl font-bold">
                    <p >Remarks</p>
                </div>
                <div class="w-full flex text-wrap text-1xl font-bold">
                    <p class="">{{$trans->order_remarks}}</p>
                </div>--}}
            </div>
            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >
        </div>
        <p class="text-center text-1xl mt-10">*** END OF BILLING ***</p>
        <div class="font-semibold text-2xl ">{{ $settings->footer }}</div>
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