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
    @foreach ($settings as $set)
    @php

        $ordersCount = DB::table('ordered')
        ->select('ordered.*', 'menus.name')
        ->join('menus', 'ordered.menu_id', '=', 'menus.id')
        ->where('branch', $set->id)
        ->where('tran_id', $trans->id)
        ->count();
    @endphp
    @if ($ordersCount > 0)
    
    <div class="flex flex-col items-center mt-14">
    </div>
    <div class="flex flex-col items-center pt-14">
        <header class="flex flex-col items-center  w-[90%] pb-1">
            {{-- <img src="{{ asset('storage/'.$settings->logo) }}" class="w-1/2" alt=""> --}}
            <h2 class="text-6xl ">{{ $set->name }}</h2>
            <h2 class="text-4xl mb-4">{{ $set->address }}</h5>

            <h2 class="text-4xl text-center tracking-widest">ORDER SLIP</h2>

            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >

            <p class="w-full text-4xl font-bold text-left">TYPE : {{ $trans->type }}</p>
            <p class="w-full text-4xl font-bold text-left">DATE : {{ date('m/d/Y H:i:s A') }}</p>

            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >

            <h2 class="text-4xl font-bold">TABLE# {{$trans->table_name}}</h2>

            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >


            <div class="flex justify-between flex-row w-full">
                <p class="text-4xl font-bold">CASHIER: {{ Auth::user()->name }}</p>
            </div>
            
            <div class="flex justify-between flex-row w-full">
                <p class="text-4xl font-bold">TRANS#{{$trans->number}}</p>
            </div>
            <div class="flex justify-between flex-row w-full">
                <p class="text-4xl font-bold">MOP : {{$trans->mode_of_payment}}</p>
            </div>
            <hr class="w-full my-1 border-black border-2 border-dashed" >
            <hr class="w-full my-1 border-black border-2 border-dashed" >

        </header>
        <div class="w-[90%] mt-5 ">
            <div class="w-full">
                @php
                    $subtotal = $subTotalWDisc = $discount = 0;
            
                    $orders = DB::table('ordered')
                    ->select('ordered.*', 'menus.name')
                    ->join('menus', 'ordered.menu_id', '=', 'menus.id')
                    ->where('branch', $set->id)
                    ->where('tran_id', $trans->id)
                    ->get();
                @endphp
                @foreach ($orders as $order)
                    @if ($order->type==0)
                    
                    
                    @php

                        $isPwd = ($order->is_pwd == 0) ? 'x' : 's';
                    @endphp
                    <div class="w-full flex justify-between">
                        <p class="text-6xl text-center">{{$order->name}}</p>
                        <!-- <i class="uil uil-times mr-1"></i> -->
                        
                    </div>
                    <div class="w-full flex justify-between">
                        <p class="text-6xl">{{$order->quantity}} x </p>
                        <!-- <i class="uil uil-times mr-1"></i> -->
                        <p class="whitespace-nowrap pl-3 text-8xl"> {{ ($order->type == 0) ? number_format($order->amount, 2, '.', ',') : '0' }} {{ $isPwd }}</p>
                    </div>
                    @php

                        if ($order->type != 1){
                            if ($order->is_pwd == 1){
                                $subTotalWDisc += $order->amount;
                            } else {
                                $subtotal +=  $order->amount;
                            }
                            
                        }
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
                    $SCPercent = $trans->sc_percent;
                    $limePercent = $trans->lime_discount;
                    $net =  $subTotalWDisc + $subtotal ;
                    if ($trans->customer_with_discount > 0) {
                        $discount = 0.2 * $subTotalWDisc;

                    }
                    $limeVal = ($limePercent / 100) * $net;
                    $SubtotalLessDiscount = $net - $discount - $limeVal;
                    $SC = ($SCPercent / 100) * $SubtotalLessDiscount;
                    
                    //discount default is 20% of the net value ex net = 100, then discount = 20
                    $total = ($SubtotalLessDiscount + $SC);

                    //$SC = ($trans->sc_percent/100 ) * $subtotal;
                    //$total = $SC + $subtotal;
                @endphp
                    
                <hr class="w-full my-1 mt-5 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >
            </div>
            <div class="w-full mt-5 mb-3">
                <div class="w-full flex justify-between text-6xl font-bold">
                    <p>SUB-TOTAL</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($net, 2, '.', ',')}}</p>
                </div>
                <!-- ₱ -->
                
                
                <div class="w-full flex justify-between text-6xl font-bold">
                    <p>DISCOUNT:</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($discount, 2, '.', ',')}}</p>
                </div>
                
                <div class="w-full flex justify-between text-6xl font-bold">
                    <p>SERVICE CHARGE: [{{ $trans->sc_percent }}%]</p>
                    <p class="whitespace-nowrap pl-3"> {{number_format($SC, 2, '.', ',')}}</p>
                </div>
                
                <div class="w-full flex justify-center text-8xl font-bold">
                    <p >TOTAL</p>
                    
                </div>
                <div class="w-full flex justify-center text-8xl font-bold">
                    <p class="whitespace-nowrap pl-3"> {{number_format($total, 2, '.', ',')}}</p>
                </div>

                <hr class="w-full my-1 mt-5 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >
                
             
                
                {{--@if ($trans->mode_of_payment == 'MULTIPLE')
                
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
            --}}
                <div class="w-full flex justify-between text-3xl font-bold">
                    <p>Mandate Discount Details</p>
                </div>
                @foreach ($seniors as $senior)
                    <div class="w-full flex justify-between text-3xl font-bold">
                    <p>{{ $senior->id_no}}</p>
                    <p>{{ $senior->name}}</p>
                    </div>
                    <div class="w-full flex justify-between text-3xl font-bold">
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
        
        
        
        
        
        
        
    </div>
    @endif
    @endforeach
    <div class="flex flex-col items-center">
        <div class="w-[90%] text-center">
            <p class="text-6xl font-bold">Grand Total</p>
        </div>
    </div>
    <div class="flex flex-col items-center pt-3 mb-10">
        <div class="w-[90%] mt-2">
            <div class="w-full flex justify-between text-6xl font-bold">
                <p>DISCOUNT</p>
                <p class="whitespace-nowrap pl-3"> {{number_format($trans->pwd, 2, '.', ',')}}</p>
            </div>
            <div class="w-full flex justify-between text-6xl font-bold">
                <p>TOTAL</p>
                <p class="whitespace-nowrap pl-3"> {{number_format($trans->total, 2, '.', ',')}}</p>
            </div>
            <div class="w-full flex justify-between text-6xl font-bold">
                <p>Amount</p>
                <p class="whitespace-nowrap pl-3"> {{number_format($trans->amount, 2, '.', ',')}}</p>
            </div>
            <div class="w-full flex justify-between text-6xl font-bold">
                <p>Change</p>
                <p class="whitespace-nowrap pl-3"> {{number_format($change, 2, '.', ',')}}</p>
            </div>
                <hr class="w-full my-1 mt-5 border-black border-2 border-dashed" >
                <hr class="w-full my-1 border-black border-2 border-dashed" >
        </div>
    </div>

    <div class="flex flex-col items-center">
        <div class="w-[90%] font-semibold text-4xl ">This is not an official receipt.</div>
        <div class="w-[90%] font-semibold text-4xl ">Please fill up details below for the issuance of official receipt.</div> 
        
        
        <div class="w-[90%] font-semibold text-4xl ">
            <p>Name: ________________________</p>
            <p>Address: ________________________</p>
            <p>TIN No.: ________________________</p>
            <p>Senior ID No.: ________________________</p>
            <p>PWD ID No.: ________________________</p>
        </div>

        <p class="text-center text-4xl mt-10">*** END OF BILLING ***</p>
        <p class="text-center text-4xl ">*** Thank you and see you again! ***</p>
    </div>

    <div class="flex flex-col items-center pt-3 mb-10">
        <span class="mb-20">&nbsp</span>
        <span class="mb-20">&nbsp</span>
        <span class="mb-20">&nbsp</span>
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