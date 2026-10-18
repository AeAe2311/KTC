@php
    $totalSales = 0;
    $totalDiscount = 0;
    $totalCost = 0;
    $totalProfit = 0;
    $grandTotal = 0;
    $rowColorIndex = 0;
    $transactionCounter = 0;
@endphp

@if($transactions && $transactions->count() > 0)
    <table class="w-full text-xs text-left text-gray-500">
        <thead>
            <tr class="text-slate-700 font-semibold bg-slate-100">
                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-calendar-alt mr-0.5"></i>Date
                </th>

                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-receipt mr-0.5"></i>Receipt
                </th>

                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-barcode mr-0.5"></i>Code
                </th>

                <th class="px-1 py-2 text-left sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-package mr-0.5"></i>Description
                </th>

                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-sort-amount-up mr-0.5"></i>Qty
                </th>

                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-percentage mr-0.5"></i>Disc
                </th>

                <th class="px-1 py-2 text-left sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-money-withdraw mr-0.5"></i>Sales
                </th>

                <th class="px-1 py-2 text-left sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-tag-alt mr-0.5"></i>Cost
                </th>

                <th class="px-1 py-2 text-left sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-chart-line mr-0.5"></i>Profit
                </th>

                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-credit-card mr-0.5"></i>Payment
                </th>

                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                    <i class="uil uil-user mr-0.5"></i>Cashier
                </th>
            </tr>                                        
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                @php
                    $transactionCounter++;
                    $displayItems = $transaction->displayItems ?? [];
                    $hasItems = count($displayItems) > 0;
                    $totalDisplayRows = max(1, $hasItems ? count($displayItems) : 1);
                    $first = true;
                    
                    // Calculate transaction totals
                    $transTotalSales = 0;
                    $transTotalDiscount = 0;
                    $transTotalCost = 0;
                    $transTotalProfit = 0;
                    
                    foreach ($displayItems as $item) {
                        $transTotalSales += $item['sales_amount'] ?? 0;
                        $transTotalDiscount += $item['discount_amount'] ?? 0;
                        $transTotalCost += $item['food_cost'] ?? 0;
                        $transTotalProfit += $item['profit'] ?? 0;
                    }
                    
                    $totalSales += $transTotalSales;
                    $totalDiscount += $transTotalDiscount;
                    $totalCost += $transTotalCost;
                    $totalProfit += $transTotalProfit;
                    $grandTotal += $transaction->amount ?? 0;
                    
                    $statusColor = 'text-red-600';
                    $displayStatus = strtoupper($transaction->status ?? 'UNPAID');
                    
                    if (strtoupper($transaction->status ?? '') == 'PAID') {
                        $statusColor = 'text-green-600';
                    } elseif (strtoupper($transaction->order_status ?? '') == 'CANCELLED') {
                        $statusColor = 'text-gray-600';
                        $displayStatus = 'CANCELLED';
                    }
                    
                    // Format payment display
                    $paymentDisplay = [];
                    if ($transaction->mode_of_payment == 'MULTIPLE' && isset($transaction->paymentBreakdown) && $transaction->paymentBreakdown->count() > 0) {
                        foreach ($transaction->paymentBreakdown as $pmt) {
                            $paymentDisplay[] = $pmt->method . ': ₱' . number_format($pmt->amount, 2);
                        }
                    } else {
                        $paymentDisplay[] = ($transaction->mode_of_payment ?? 'CASH') . ': ₱' . number_format($transaction->amount ?? $transaction->total ?? 0, 2);
                    }
                @endphp

                <tbody class="transaction-group">
                    
                    @if($hasItems)
                        @foreach($displayItems as $displayIndex => $displayItem)
                            @php
                                $rowClass = ($rowColorIndex % 2 == 0) ? 'row-colored' : 'row-white';
                                $rowColorIndex++;
                            @endphp
                            <tr class="resultRow {{ $rowClass }}">
                                @if($first)
                                    <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $totalDisplayRows + 1 }}">
                                        {{ date('Y-m-d', strtotime($transaction->created_at ?? now())) }}
                                    </td>
                                    <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $totalDisplayRows + 1 }}">
                                        {{ $transaction->number ?? 'N/A' }}
                                    </td>
                                    @php $first = false; @endphp
                                @endif

                                <td class="px-1 py-0.5 text-center text-[10px]">{{ $displayItem['brand_code'] ?? 'N/A' }}</td>
                                <td class="px-1 py-0.5 text-left text-[12px]">
                                    <div class="whitespace-normal"> 
                                        <span class="font-bold text-gray-800">{{ strtoupper($displayItem['name'] ?? 'Unknown') }}</span>
                                        @if(!empty($displayItem['brand']))
                                            - <span class="font-semibold text-gray-500">{{ strtoupper($displayItem['brand']) }}</span>
                                            {{-- <span class="text-gray-500">({{ strtoupper($displayItem['brand_code'] ?? '') }})</span> --}}
                                        @endif
                                    </div>
                                    @if($displayItem['count'] > 1)
                                        <div class="text-[10px] text-gray-500 font-normal">
                                        {{ $displayItem['quantity_breakdown'] }}
                                        </div>
                                    @endif                                    
                                </td>
                                <td class="px-1 py-0.5 text-center text-[10px]">{{ number_format($displayItem['total_quantity'] ?? 0, 3) }}</td>
                                <td class="px-1 py-0.5 text-center text-[10px]">- ₱ {{ number_format($displayItem['discount_amount'] ?? 0, 2) }}</td>
                                <td class="px-1 py-0.5 text-left text-[10px]">₱ {{ number_format($displayItem['sales_amount'] ?? 0, 2) }}</td>
                                <td class="px-1 py-0.5 text-left text-[10px]">₱ {{ number_format($displayItem['food_cost'] ?? 0, 2) }}</td>
                                <td class="px-1 py-0.5 text-left text-[10px]">₱ {{ number_format($displayItem['profit'] ?? 0, 2) }}</td>
                               
                                @if($displayIndex === 0)
                                    <td class="px-1 py-0.5 text-center text-[10px]">
                                        ₱ {{ number_format($transaction->amount, 2) }}
                                    </td>                                 
                                    <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $totalDisplayRows + 1 }}">
                                        {{ $transaction->cashier_name ?? 'N/A' }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr class="resultRow row-white">
                            <td class="px-1 py-0.5 text-center text-[10px]" rowspan="2">
                                {{ date('Y-m-d', strtotime($transaction->created_at ?? now())) }}
                            </td>
                            <td class="px-1 py-0.5 text-center text-[10px]" rowspan="2">
                                {{ $transaction->number ?? 'N/A' }}
                            </td>
                            <td class="px-1 py-0.5 text-center text-[10px]" colspan="7">
                                <span class="text-gray-400 italic">No items found</span>
                            </td>
                            <td class="px-1 py-0.5 text-center text-[10px]">
                                ₱ {{ number_format($transaction->amount, 2) }}
                            </td>                             
                            <td class="px-1 py-0.5 text-center text-[10px]" rowspan="2">
                                {{ $transaction->cashier_name ?? 'N/A' }}
                            </td>
                        </tr>
                    @endif

                    {{-- Grand Total row for this transaction --}}
                    <tr class="total-row">
                        <td colspan="2" class="px-1 py-1 text-left text-[10px] text-gray-700">                        
                            <span class="text-gray-500">Client:</span> {{ $transaction->table_name }}
                        </td>                        
                        <td class="px-1 py-1 text-left text-[10px] text-gray-700">
                            <span class="font-bold">TOTAL</span>
                        </td>
                        <td class="px-1 py-1 text-center text-[10px] text-gray-700 font-bold">
                            - ₱ {{ number_format($transTotalDiscount, 2) }}
                        </td>
                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold">
                            ₱ {{ number_format($transTotalSales, 2) }}
                        </td>
                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold">
                            ₱ {{ number_format($transTotalCost, 2) }}
                        </td>
                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold">
                            ₱ {{ number_format($transTotalProfit, 2) }}
                        </td>
                    </tr>
                </tbody>
            @endforeach
            
            {{-- Overall Grand Total --}}
            @if($transactions->count() > 0)
                <tfoot>
                    <tr style="background: #1e293b; color: white; font-weight: 400; border-top: 2px solid #000;">
                        <td colspan="4" class="px-1 py-2 text-right text-[11px] text-white">
                            GRAND TOTAL:
                        </td>
                        <td class="px-1 py-2 text-center text-[11px] text-white"></td>
                        <td class="px-1 py-2 text-center text-[11px] text-white">- ₱ {{ number_format($totalDiscount, 2) }}</td>
                        <td class="px-1 py-2 text-left text-[11px] text-white">₱ {{ number_format($totalSales, 2) }}</td>
                        <td class="px-1 py-2 text-left text-[11px] text-white">₱ {{ number_format($totalCost, 2) }}</td>
                        <td class="px-1 py-2 text-left text-[11px] text-white">₱ {{ number_format($totalProfit, 2) }}</td>
                        <td colspan="3" class="px-1 py-2 text-left text-[11px] text-white">
                            ₱ {{ number_format($grandTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </tbody>
    </table>
@else
    <div class="flex justify-center items-center h-64 text-gray-400">
        <div class="text-center">
            <i class="uil uil-file-times text-4xl block"></i>
            <span class="text-sm mt-2 block">No transactions found for this report</span>
        </div>
    </div>
@endif

<script>
    function printReceipt(transactionId) {
        if (transactionId) {
            const printUrl = "{{ url('/transactions/print') }}/" + transactionId;
            const newTab = window.open(printUrl, '_blank');
            if (newTab) {
                newTab.focus();
            }
        }
    }
</script>