@php
    $totalSales = 0;
    $totalDiscount = 0;
    $totalCost = 0;
    $totalProfit = 0;
    $grandTotal = 0;
    $rowColorIndex = 0;
    $transactionCounter = 0;
@endphp

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
                    // Check if there are actual ordered items from the database
                    $orderedItems = $transaction->ordered_items ?? collect();
                    $hasOrderedItems = $orderedItems->count() > 0;
                    // Also check if we have items in displayItems
                    $hasDisplayItems = count($displayItems) > 0;
                    // Use OR logic - if either has items, we have items to display
                    $hasItems = $hasOrderedItems || $hasDisplayItems;
                    
                    // Check if transaction is cancelled
                    $isCancelled = $transaction->is_cancelled ?? false;
                    
                    // If we have ordered items but no displayItems, build them
                    if ($hasOrderedItems && !$hasDisplayItems) {
                        $displayItems = [];
                        $groupedItems = $orderedItems->groupBy('menu_name');
                        foreach ($groupedItems as $name => $group) {
                            $displayItems[] = [
                                'name' => $name,
                                'brand' => $group->first()->brand_name ?? '',
                                'brand_code' => $group->first()->brand_code ?? '',
                                'count' => $group->count(),
                                'total_quantity' => $group->sum('ordered_quantity'),
                                'item_code' => $group->first()->item_code ?? 'N/A',
                                'discount_amount' => $group->sum('ordered_discount_amount') ?? 0,
                                'sales_amount' => $group->sum('ordered_amount') ?? 0,
                                'food_cost' => $group->sum('ordered_food_cost') ?? 0,
                                'profit' => ($group->sum('ordered_amount') ?? 0) - ($group->sum('ordered_food_cost') ?? 0),
                                'quantity_breakdown' => $group->pluck('ordered_quantity')->map(function($qty) {
                                    return number_format($qty, 3);
                                })->implode('/'),
                                'is_cancelled' => $isCancelled
                            ];
                        }
                        // Recalculate hasItems and hasDisplayItems
                        $hasDisplayItems = count($displayItems) > 0;
                        $hasItems = $hasDisplayItems;
                    }
                    
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
                    // start of update for excluding cancelled transactions from totals 9-10-2026
                    if (!$isCancelled) {
                        $totalSales += $transTotalSales;
                        $totalDiscount += $transTotalDiscount;
                        $totalCost += $transTotalCost;
                        $totalProfit += $transTotalProfit;
                        $grandTotal += $transaction->amount ?? 0;
                    }
                    // end of update for excluding cancelled transactions from totals 9-10-2026
                    
                    $statusColor = 'text-red-600';
                    $displayStatus = strtoupper($transaction->status ?? 'UNPAID');
                    
                    if (strtoupper($transaction->status ?? '') == 'PAID') {
                        $statusColor = 'text-green-600';
                    } elseif ($isCancelled || strtoupper($transaction->order_status ?? '') == 'CANCELLED') {
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

                <tbody class="transaction-group {{ $isCancelled ? 'cancelled-transaction' : '' }}">
                    
                    @if($hasItems && count($displayItems) > 0)
                        @foreach($displayItems as $displayIndex => $displayItem)
                            @php
                                $rowClass = ($rowColorIndex % 2 == 0) ? 'row-colored' : 'row-white';
                                $rowColorIndex++;
                                $itemIsCancelled = $displayItem['is_cancelled'] ?? false;
                            @endphp
                            <tr class="resultRow {{ $rowClass }} {{ $isCancelled ? 'cancelled-row' : '' }}">
                                @if($first)
                                    <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}" rowspan="{{ $totalDisplayRows + 1 }}">
                                        {{ date('Y-m-d', strtotime($report->start_date ?? '')) }}  {{-- UPDATED BY KEN 09-21-2026 --}}
                                        {{-- @if($isCancelled)
                                            <span class="ml-1 text-red-500 text-[8px] font-bold">[CANCELLED]</span>
                                        @endif --}}
                                    </td>
                                    <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}" rowspan="{{ $totalDisplayRows + 1 }}">
                                        {{ $transaction->number ?? 'N/A' }}
                                    </td>
                                    @php $first = false; @endphp
                                @endif

                                <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">{{ $displayItem['brand_code'] ?? 'N/A' }}</td>
                                <td class="px-1 py-0.5 text-left text-[12px]">
                                    <div class="whitespace-normal"> 
                                        @if($isCancelled || $itemIsCancelled)
                                            <span class="font-bold text-gray-500 ">{{ strtoupper($displayItem['name'] ?? 'Unknown') }}</span>
                                            {{-- <span class="ml-1 text-red-500 text-[10px] font-bold">[CANCELLED]</span> --}}
                                        @else
                                            <span class="font-bold text-gray-800">{{ strtoupper($displayItem['name'] ?? 'Unknown') }}</span>
                                        @endif
                                        @if(!empty($displayItem['brand']))
                                            - <span class="font-semibold {{ $isCancelled ? 'text-gray-400' : 'text-gray-500' }}">{{ strtoupper($displayItem['brand']) }}</span>
                                        @endif
                                    </div>
                                    @if($displayItem['count'] > 1)
                                        <div class="text-[10px] text-gray-500 font-normal">
                                        {{ $displayItem['quantity_breakdown'] }}
                                        </div>
                                    @endif                                    
                                </td>
                                <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >{{ number_format($displayItem['total_quantity'] ?? 0, 3) }}</span>
                                    @else
                                        {{ number_format($displayItem['total_quantity'] ?? 0, 3) }}
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >- ₱ {{ number_format($displayItem['discount_amount'] ?? 0, 2) }}</span>
                                    @else
                                        - ₱ {{ number_format($displayItem['discount_amount'] ?? 0, 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-left text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >₱ {{ number_format($displayItem['sales_amount'] ?? 0, 2) }}</span>
                                    @else
                                        ₱ {{ number_format($displayItem['sales_amount'] ?? 0, 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-left text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >₱ {{ number_format($displayItem['food_cost'] ?? 0, 2) }}</span>
                                    @else
                                        ₱ {{ number_format($displayItem['food_cost'] ?? 0, 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-left text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >₱ {{ number_format($displayItem['profit'] ?? 0, 2) }}</span>
                                    @else
                                        ₱ {{ number_format($displayItem['profit'] ?? 0, 2) }}
                                    @endif
                                </td>
                               
                                @if($displayIndex === 0)
                                    <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                        @if($isCancelled)
                                            <span >₱ {{ number_format($displayItem['amount'] ?? 0, 2) }}</span> {{-- ken update this line to make void/cancelled payment to 0  9-10-2026 --}}
                                           {{--  <span class="ml-1 text-red-500 text-[8px] font-bold">CANCELLED</span> --}}
                                        @else
                                            ₱ {{ number_format($transaction->amount, 2) }}
                                        @endif
                                    </td>                                 
                                    <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $totalDisplayRows + 1 }}">
                                        {{ $transaction->cashier_name ?? 'N/A' }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @elseif($hasOrderedItems)
                        {{-- We have ordered items but couldn't build display items - show raw items --}}
                        @php
                            $rawItems = $orderedItems->groupBy('menu_name');
                            $first = true;
                            $totalRawRows = $rawItems->count();
                            $rowspan = $totalRawRows + 1;
                        @endphp
                        @foreach($rawItems as $name => $group)
                            <tr class="resultRow {{ ($rowColorIndex % 2 == 0) ? 'row-colored' : 'row-white' }} {{ $isCancelled ? 'cancelled-row' : '' }}">
                                @if($first)
                                    <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}" rowspan="{{ $rowspan }}">
                                        {{ date('Y-m-d', strtotime($report->start_date ?? '')) }}  {{-- UPDATED BY KEN 09-21-2026 --}}
                                        {{-- @if($isCancelled)
                                            <span class="ml-1 text-red-500 text-[8px] font-bold">[CANCELLED]</span>
                                        @endif --}}
                                    </td>
                                    <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}" rowspan="{{ $rowspan }}">
                                        {{ $transaction->number ?? 'N/A' }}
                                    </td>
                                    @php $first = false; @endphp
                                @endif
                                <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">{{ $group->first()->item_code ?? 'N/A' }}</td>
                                <td class="px-1 py-0.5 text-left text-[12px]">
                                    @if($isCancelled)
                                        <span class="font-bold text-gray-500 ">{{ strtoupper($name) }}</span>
                                        {{-- <span class="ml-1 text-red-500 text-[10px] font-bold">[CANCELLED]</span> --}}
                                    @else
                                        <span class="font-bold text-gray-800">{{ strtoupper($name) }}</span>
                                    @endif
                                    @if($group->count() > 1)
                                        <div class="text-[10px] text-gray-500 font-normal">
                                            {{ $group->pluck('ordered_quantity')->map(function($qty) { return number_format($qty, 3); })->implode('/') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >{{ number_format($group->sum('ordered_quantity'), 3) }}</span>
                                    @else
                                        {{ number_format($group->sum('ordered_quantity'), 3) }}
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >- ₱ 0.00</span>
                                    @else
                                        - ₱ 0.00
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-left text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >₱ {{ number_format($group->sum('ordered_amount'), 2) }}</span>
                                    @else
                                        ₱ {{ number_format($group->sum('ordered_amount'), 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-left text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >₱ {{ number_format($group->sum('ordered_food_cost'), 2) }}</span>
                                    @else
                                        ₱ {{ number_format($group->sum('ordered_food_cost'), 2) }}
                                    @endif
                                </td>
                                <td class="px-1 py-0.5 text-left text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                    @if($isCancelled)
                                        <span >₱ {{ number_format($group->sum('ordered_amount') - $group->sum('ordered_food_cost'), 2) }}</span>
                                    @else
                                        ₱ {{ number_format($group->sum('ordered_amount') - $group->sum('ordered_food_cost'), 2) }}
                                    @endif
                                </td>
                                @if($loop->first)
                                    <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}" rowspan="{{ $rowspan }}">
                                        @if($isCancelled)
                                            <span >₱ {{ number_format($displayItem['amount'] ?? 0, 2) }}</span> {{-- ken update this line to make void/cancelled payment to 0  9-10-2026 --}}
                                            {{-- <span class="ml-1 text-red-500 text-[8px] font-bold">CANCELLED</span> --}}
                                        @else
                                            ₱ {{ number_format($transaction->amount, 2) }}
                                        @endif
                                    </td>
                                    <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $rowspan }}">
                                        {{ $transaction->cashier_name ?? 'N/A' }}
                                    </td>
                                @endif
                            </tr>
                            @php $rowColorIndex++; @endphp
                        @endforeach
                    @else
                        {{-- No items at all --}}
                        <tr class="resultRow row-white {{ $isCancelled ? 'cancelled-row' : '' }}">
                            <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}" rowspan="2">
                                {{ date('Y-m-d', strtotime($report->start_date ?? '')) }}  {{-- UPDATED BY KEN 09-21-2026 --}}
                                @if($isCancelled)
                                    <span class="ml-1 text-red-500 text-[8px] font-bold">[CANCELLED]</span>
                                @endif
                            </td>
                            <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}" rowspan="2">
                                {{ $transaction->number ?? 'N/A' }}
                            </td>
                            <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}" colspan="7">
                                @if($isCancelled)
                                    <span class="text-red-400 italic font-bold">CANCELLED TRANSACTION</span>
                                @else
                                    <span class="text-gray-400 italic">No items found</span>
                                @endif
                            </td>
                            <td class="px-1 py-0.5 text-center text-[10px] {{ $isCancelled ? 'text-gray-400' : '' }}">
                                @if($isCancelled)
                                    <span >₱ {{ number_format($displayItem['amount'] ?? 0, 2) }}</span> {{-- ken update this line to make void/cancelled payment to 0  9-10-2026 --}}
                                    {{-- <span class="ml-1 text-red-500 text-[8px] font-bold">CANCELLED</span> --}}
                                @else
                                    ₱ {{ number_format($transaction->amount, 2) }}
                                @endif
                            </td>                             
                            <td class="px-1 py-0.5 text-center text-[10px]" rowspan="2">
                                {{ $transaction->cashier_name ?? 'N/A' }}
                            </td>
                        </tr>
                    @endif

                    {{-- Grand Total row for this transaction --}}
                    <tr class="total-row {{ $isCancelled ? 'cancelled-total' : '' }}">
                        <td colspan="2" class="px-1 py-1 text-left text-[10px] text-gray-700">                        
                            <span class="text-gray-500">Client:</span> {{ $transaction->table_name ?? 'N/A' }}
                            {{-- @if($isCancelled)
                                <span class="ml-2 text-red-500 text-[9px] font-bold">[CANCELLED]</span>
                            @endif --}}
                        </td>                        
                        <td class="px-1 py-1 text-left text-[10px] text-gray-700">
                            <span class="font-bold">TOTAL</span>
                        </td>
                        <td class="px-1 py-1 text-center text-[10px] text-gray-700 font-bold {{ $isCancelled ? 'text-gray-400' : '' }}">
                            @if($isCancelled)
                                <span >- ₱ {{ number_format($transTotalDiscount, 2) }}</span>
                            @else
                                - ₱ {{ number_format($transTotalDiscount, 2) }}
                            @endif
                        </td>
                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold {{ $isCancelled ? 'text-gray-400' : '' }}">
                            @if($isCancelled)
                                <span >₱ {{ number_format($transTotalSales, 2) }}</span>
                            @else
                                ₱ {{ number_format($transTotalSales, 2) }}
                            @endif
                        </td>
                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold {{ $isCancelled ? 'text-gray-400' : '' }}">
                            @if($isCancelled)
                                <span >₱ {{ number_format($transTotalCost, 2) }}</span>
                            @else
                                ₱ {{ number_format($transTotalCost, 2) }}
                            @endif
                        </td>
                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold {{ $isCancelled ? 'text-gray-400' : '' }}">
                            @if($isCancelled)
                                <span >₱ {{ number_format($transTotalProfit, 2) }}</span>
                            @else
                                ₱ {{ number_format($transTotalProfit, 2) }}
                            @endif
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

<style>
    /* Cancelled transaction styling */
    .cancelled-transaction {
        background-color: #fef2f2 !important;
    }
    
    .cancelled-row {
        background-color: #fee2e2 !important;
    }
    
    .cancelled-row td {
        background-color: #fee2e2 !important;
    }
    
    .cancelled-total {
        background-color: #fecaca !important;
    }
    
    .cancelled-total td {
        background-color: #fecaca !important;
    }
    
    /* Regular row colors */
    .row-colored {
        background-color: #f8fafc;
    }
    
    .row-white {
        background-color: #ffffff;
    }
    
    /* Override row colors for cancelled transactions */
    .cancelled-transaction .row-colored {
        background-color: #fee2e2 !important;
    }
    
    .cancelled-transaction .row-white {
        background-color: #fef2f2 !important;
    }
</style>

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