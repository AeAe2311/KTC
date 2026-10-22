<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Receipt</title>

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
        header, footer {
            display: block;
        }

        @media print {
            header, footer {
                display: none;
            }
        }
        
        .receipt-item {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 16px;
        }
        
        .receipt-item .qty {
            min-width: 60px;
        }
        
        .receipt-item .name {
            flex: 1;
            padding: 0 10px;
        }
        
        .receipt-item .price {
            min-width: 100px;
            text-align: right;
        }
        
        .receipt-item .total {
            min-width: 100px;
            text-align: right;
        }
        
        .receipt-item .qty-breakdown {
            min-width: 60px;
            font-size: 14px;
            color: #666;
            padding-left: 10px;
        }
        
        .receipt-item .name-breakdown {
            flex: 1;
            padding: 0 2px;
            font-size: 14px;
            color: #666;
        }
        
        .receipt-divider {
            border: none;
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        
        .receipt-total {
            font-size: 20px;
            font-weight: bold;
        }
        
        .receipt-label {
            font-weight: bold;
        }
        
        .receipt-header {
            text-align: center;
        }
        
        .receipt-header h2 {
            font-size: 24px;
            font-weight: bold;
        }
        
        .receipt-header p {
            font-size: 16px;
            margin: 2px 0;
        }

        .receipt-column-header {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        
        .receipt-column-header .qty-header {
            min-width: 80px;
        }
        
        .receipt-column-header .item-header {
            flex: 1;
            padding: 0 10px;
        }
        
        .receipt-column-header .price-header {
            min-width: 100px;
            text-align: right;
        }
        
        .receipt-column-header .total-header {
            min-width: 100px;
            text-align: right;
        }

        .price-type {
            font-size: 10px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            margin-left: 4px;
        }
        
        .price-type.wholesale {
            color: #000000;
            background: #dbeafe;
        }
        
        .price-type.slab {
            color: #000000;
            background: #dbeafe;
        }
        
        .price-type.retail {
            color: #000000;
            background: #dbeafe;
        }

        .price-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 16px;
        }
        
        .summary-line .label {
            font-weight: bold;
        }
        
        .summary-line .value {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div id="billContent" class="flex flex-col items-center pt-14">
        <div class="w-[90%] max-w-[400px]">
            {{-- Header --}}
            <div class="receipt-header pb-1 mb-2">
                <h2>{{ $settings->name ?? 'N/A' }}</h2>
                <p>{{ $settings->address ?? 'N/A' }}</p>
                {{-- <p>TIN # : {{ $settings->number ?? 'N/A' }}</p> --}}
            </div>
            <p>CHARGE NO.: {{ $trans->number ?? 'N/A' }}</p>
            <p>ENCODER: {{ $trans->cashier_name ?? 'N/A' }}</p>
            
            <hr class="receipt-divider">

            {{-- Column Headers --}}
            <div class="receipt-column-header">
                <span class="qty-header">QTY</span>
                <span class="item-header">ITEM</span>
                <span class="price-header">PRICE</span>
                <span class="total-header">TOTAL</span>
            </div>

            <hr class="receipt-divider">

            {{-- Items --}}
            <div class="mt-2">
                @php
                    $totalLineItems = 0;
                    $totalQuantity = 0;
                    $unit = 'kg'; // Default unit, you can change this based on your data
                @endphp

                @forelse($receiptItems as $item)
                    @php
                        $totalLineItems++;
                        $totalQuantity += $item['total_quantity'];
                        // If you have unit in your item data, use it, otherwise keep default
                        if (isset($item['unit'])) {
                            $unit = $item['unit'];
                        }
                    @endphp

                    {{-- Main item line --}}
                    <div class="receipt-item">
                        <span class="qty">{{ number_format($item['total_quantity'], 3) }} {{ $unit }}</span>
                        <span class="name">
                            @if($item['brand_name'] || $item['brand_code'])
                                <small style="font-size: 16px; color: #666;">
                                    {{ $item['brand_code'] }}
                                </small>
                            @endif                            
                        </span>
                        <span class="price">
                            <span class="price-wrapper">
                                ₱ {{ number_format($item['menu_price'], 2, '.', ',') }}
                            </span>
                        </span>
                        <span class="total">₱ {{ number_format($item['total_amount'], 2, '.', ',') }}</span>
                    </div>
                    <div class="receipt-item" style="padding-left: 60px;">
                        {{ $item['name'] }} - {{ $item['brand_name'] }}
                    </div>

                    {{-- Quantity breakdown --}}
                    @if($item['item_count'] > 1)
                        <div class="receipt-item" style="padding-left: 60px;">
                            <span class="qty-breakdown">{{ $item['quantity_breakdown'] }}</span>
                            <span class="name-breakdown"></span>
                            <span class="price"></span>
                            <span class="total"></span>
                        </div>
                    @endif
                @empty
                    <div class="text-center text-gray-500 py-4">No items found</div>
                @endforelse
            </div>

            <hr class="receipt-divider">
            <p>Customer Name : {{ $trans->table_name ?? 'N/A' }}</p>
            
            {{-- LINE ITEM and QUANTITY summary --}}
            <div class="summary-line flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="label">LINE ITEM:</span>
                    <span class="value">{{ $totalLineItems }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="label">QUANTITY:</span>
                    <span class="value">{{ number_format($totalQuantity, 3) }} </span> {{-- {{ $unit }} --}}
                </div>
            </div>
            
            <hr class="receipt-divider">

            {{-- Summary --}}
            <div class="mt-2">
                <div class="receipt-item">
                    <span class="receipt-label">Subtotal</span>
                    <span class="total">₱ {{ number_format($subtotal, 2, '.', ',') }}</span>
                </div>
                @if(($pwdseniorDiscount ?? 0) > 0)
                    <div class="receipt-item">
                        <span class="receipt-label">
                            PWD/Senior Discount: ({{ number_format($numberOfSeniors, 0) }})
                        </span>
                        <span class="total">
                            ₱ {{ number_format($pwdseniorDiscount, 2, '.', ',') }}
                        </span>
                    </div>
                @endif

                @if(($discount ?? 0) > 0)
                    <div class="receipt-item">
                        <span class="receipt-label">
                            Discount: ({{ number_format($percentDiscount, 2, '.', ',') }}%)
                        </span>
                        <span class="total">
                            ₱ {{ number_format($discount, 2, '.', ',') }}
                        </span>
                    </div>
                @endif
                <div class="receipt-item">
                    <span class="receipt-label">2307:({{ number_format($percent2307, 2, '.', ',') }}%)</span>
                    <span class="total">₱ {{ number_format($value2307, 2, '.', ',') }}</span>
                </div>
                <div class="receipt-item">
                    <span class="receipt-label">TOTAL DUE:</span>
                    <span class="total">₱ {{ number_format($overalltotal, 2, '.', ',') }}</span>
                </div>
                <hr class="receipt-divider">

            {{-- MODE OF PAYMENT --}}
            <div class="receipt-item">
                <span class="receipt-label">MODE OF PAYMENT</span>
            </div>

            @if($trans->mode_of_payment === 'MULTIPLE')

                {{-- Payment Breakdown --}}
                @foreach($paymentBreakdown as $payment)
                    <div class="receipt-item" style="font-size: 16px;">
                        <span>{{ strtoupper($payment->method) }}</span>
                        <span class="total">
                            ₱ {{ number_format($payment->amount, 2, '.', ',') }}
                        </span>
                    </div>
                @endforeach

            @else

                {{-- Single Payment Method --}}
                <div class="receipt-item" style="font-size: 16px;">
                    <span>{{ strtoupper($trans->mode_of_payment ?? 'N/A') }}</span>
                    <span class="total">
                        ₱ {{ number_format($trans->amount ?? 0, 2, '.', ',') }}
                    </span>
                </div>

            @endif


                <hr class="receipt-divider">
                <div class="receipt-item">
                    <span class="receipt-label">CHANGE:</span>
                    <span class="total">
                        ₱ {{ number_format(($trans->amount ?? 0) - ($trans->total ?? 0), 2, '.', ',') }}
                    </span>
                </div>

                <hr class="receipt-divider">
                <p>ENCODER : {{ $trans->cashier_name ?? 'N/A' }}</p>
                <p>DATE/TIME : {{ isset($trans->created_at) ? date('Y-m-d H:i', strtotime($trans->created_at)) : 'N/A' }}</p>                
                <p>SO NO: {{ $trans->number ?? 'N/A' }}</p>                    
                <hr class="receipt-divider">

                <div>
                    <span>Return Policy:</span>
                    <p>
                        *24 hours of purchase<br>
                        *Once signed, confirms items received in good condition (delivery or pick-up)<br>
                        *Full details in-store or contact us via our Facebook Page (Kumpadres Meats Cavite)
                    </p>
                </div> 

                <div class="mt-2">
                    <p>----------------------</p>                    
                    <span>RECEIVED BY</span>
                </div>                 
                <div class="text-center mt-2">                
                    <span>------ORDER SLIP------</span>
                </div>  

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