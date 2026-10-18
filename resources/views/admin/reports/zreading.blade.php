<div id="zreadingContainer" class="mb-6 border border-gray-300 bg-white p-5 font-mono text-[14px] uppercase tracking-wide text-gray-800 shadow-sm">
    <!-- HEADER / REPORT PERIOD (full width) -->
    <div class="border-b border-gray-300 pb-3 mb-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xl font-black tracking-[0.2em]">Z-READING</div>
                <div class="mt-2 text-[12px] text-gray-500">REPORT PERIOD</div>
                <div class="mt-1 text-[12px] font-semibold text-gray-700">
                    Branch: {{ $selectedBranchName ?? $selectedBranch ?? 'All Branches' }}
                </div>
            </div>
            <div class="text-right text-[13px] text-gray-700">
            <div class="no-print mt-4 flex justify-end">
                <a href="{{ route('areport.print', ['start' => $startDate, 'end' => $endDate, 'category' => 'sales', 'report' => 'zreading', 'branch' => $selectedBranch]) }}" 
                target="_blank" 
                rel="noopener" 
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-200 hover:bg-blue-700 hover:shadow-xl hover:scale-105 active:scale-95 border border-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                        class="h-5 w-5 flex-shrink-0" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        stroke="currentColor" 
                        stroke-width="2.5" 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        aria-hidden="true">
                        <path d="M6 9V2h12v7" />
                        <path d="M6 19H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5h-2" />
                        <path d="M6 14h12v7H6z" />
                    </svg>
                    <span>Print Z-Reading</span>
                </a>
            </div>
            </div>
        </div>
        <div class="mt-2 text-[13px] text-gray-600">{{ date('M d, Y h:i A', strtotime($startDate)) }} - {{ date('M d, Y h:i A', strtotime($endDate)) }}</div>
      
    </div>

        <!-- GRID: 2 columns on md+, 1 column on small screens -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- SALES SUMMARY -->
                <div class="border border-gray-300 bg-gray-50">
                    <div class="border-b border-gray-300 bg-gray-100 px-3 py-2 text-[13px] font-bold tracking-[0.15em] text-gray-700">
                        SALES SUMMARY
                    </div>
                    <div class="p-4">
                        @php
                            $zread = $zreadingSummary ?? [
                                'gross_sales' => 0,
                                'less_returns' => 0,
                                'net_sales_after_sc' => 0,
                                'sales_charge' => 0,
                                'vat_12' => 0,
                                'vatable_sales' => 0,
                                'vat_exempt_sales' => 0,
                                'service_charge' => 0,
                                'discounts' => 0,
                                'less_vat_senior_pwd' => 0,
                                'discount_senior_pwd' => 0,
                                'grand_total' => 0,
                            ];
                        @endphp

                        <div class="space-y-1">
                            <div class="flex justify-between gap-4 py-1"><span>GROSS SALES</span><span class="text-right">₱ {{ number_format($zread['gross_sales'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>LESS RETURNS</span><span class="text-right">₱ {{ number_format($zread['less_returns'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>NET SALES AFTER SC</span><span class="text-right">₱ {{ number_format($zread['net_sales_after_sc'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>SALES CHARGE</span><span class="text-right">₱ {{ number_format($zread['sales_charge'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>VAT 12%</span><span class="text-right">₱ {{ number_format($zread['vat_12'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>VATABLE SALES</span><span class="text-right">₱ {{ number_format($zread['vatable_sales'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>VAT EXEMPT SALES</span><span class="text-right">₱ {{ number_format($zread['vat_exempt_sales'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>SERVICE CHARGE</span><span class="text-right">₱ {{ number_format($zread['serviceCharge'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>DISCOUNTS</span><span class="text-right">₱ {{ number_format($zread['discounts'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>LESS VAT SENIOR/PWD</span><span class="text-right">₱ {{ number_format($zread['less_vat_senior_pwd'], 2, '.', ',') }}</span></div>
                            <div class="flex justify-between gap-4 py-1"><span>DISCOUNT SENIOR/PWD</span><span class="text-right">₱ {{ number_format($zread['discount_senior_pwd'], 2, '.', ',') }}</span></div>
                        </div>
                        <div class="my-3 border-t border-gray-400"></div>
                        <div class="flex justify-between gap-4 py-1 text-[15px] font-black"><span>GRAND TOTAL</span><span class="text-right">₱ {{ number_format($zread['grand_total'], 2, '.', ',') }}</span></div>

                        <br>
                        <div class="flex justify-between gap-4 py-1 text-[15px] font-black"><span>RUNNING TOTAL</span><span class="text-right">₱ {{ number_format($zread['runningTotal'], 2, '.', ',') }}</span></div>                        
                        <div class="flex justify-between gap-4 py-1 text-[15px] font-black"><span>BEGINNING TOTAL</span><span class="text-right">₱ {{ number_format($zread['beginningTotal'], 2, '.', ',') }}</span></div>                        
                        <div class="flex justify-between gap-4 py-1 text-[15px] font-black"><span>ENDING TOTAL</span><span class="text-right">₱ {{ number_format($zread['endingTotal'], 2, '.', ',') }}</span></div>
                    </div>
                </div>

                <!-- CASH BREAKDOWN -->
                <div class="border border-gray-300 bg-white">
                    <div class="border-b border-gray-300 bg-gray-100 px-3 py-2 text-[13px] font-bold tracking-[0.15em] text-gray-700">
                        CASH BREAKDOWN
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full border-collapse text-[13px]">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 text-left">DENOMINATION</th>
                                    <th class="border border-gray-300 px-3 py-2 text-center">QTY</th>
                                    <th class="border border-gray-300 px-3 py-2 text-right">AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $denominationLabels = [
                                        'count_1000' => '₱ 1,000.00',
                                        'count_500' => '₱ 500.00',
                                        'count_200' => '₱ 200.00',
                                        'count_100' => '₱ 100.00',
                                        'count_50' => '₱ 50.00',
                                        'count_20' => '₱ 20.00',
                                        'count_10' => '₱ 10.00',
                                        'count_5' => '₱ 5.00',
                                        'count_1' => '₱ 1.00',
                                        'count_50cents' => '₱ 0.50',
                                        'count_10cents' => '₱ 0.10',
                                        'count_5cents' => '₱ 0.05',
                                    ];
                                @endphp

                                @forelse($denominationTotals as $key => $data)
                                    <tr>
                                        <td class="border border-gray-300 px-3 py-2 text-left">
                                            {{ $denominationLabels[$key] ?? $key }}
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            {{ number_format($data['count']) }}
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-right">
                                            ₱ {{ number_format($data['total'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="border border-gray-300 px-3 py-4 text-center text-gray-500">
                                            No cash breakdown data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-100 font-bold">
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2 text-right">TOTAL CASH</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-right" style="color: #059669;">
                                        ₱ {{ number_format($totalCash, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2 text-right">CASH SALES</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-right" style="color: #059669;">
                                        ₱ {{ number_format($cashSales->amount ?? 0, 2) }}
                                    </td>
                                </tr>
                                <tr class="{{ $variance > 0 ? 'bg-green-50' : ($variance < 0 ? 'bg-red-50' : '') }}">
                                    <td class="border border-gray-300 px-3 py-2 text-right">VARIANCE</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center"></td>
                                    <td class="border border-gray-300 px-3 py-2 text-right" 
                                        style="font-weight: bold; color: {{ $variance > 0 ? '#059669' : ($variance < 0 ? '#dc2626' : '#6b7280') }};">
                                        {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                                        @if($variance != 0)
                                            <span style="font-size: 10px; font-weight: normal;">
                                                ({{ $variance > 0 ? 'OVER' : 'SHORT' }})
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
        </div> <!-- end grid -->

        <!-- HOURLY SALES -->  <!-- CATEGORY SALES SUMMARY -->
        {{-- <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
            
            <div class="border border-gray-300 bg-white">
                <div class="border-b border-gray-300 bg-gray-100 px-3 py-2 text-[13px] font-bold tracking-[0.15em] text-gray-700">
                    HOURLY SALES
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full border-collapse text-[13px]">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-left">DATE</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">HOUR</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">TRANS.</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">SALES</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">DISCOUNT</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">GUESTS</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">QTY</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hourlySummary as $h)
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2 text-left">{{ date('M d, Y', strtotime($h->date)) }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-left">{{ date('h:00A', strtotime($h->date . ' ' . $h->hour . ':00:00')) }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $h->hourly_transactions }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-right">₱ {{ number_format($h->hourly_sales,2) }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-right">₱ {{ number_format($h->hourly_discount,2) }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $h->hourly_customer }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $h->sold_quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-bold">
                            <tr>
                                <td colspan="2" class="border border-gray-300 px-3 py-2 text-right">TOTAL</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $hourlyGrandTotal['transactions'] }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right">₱ {{ number_format($hourlyGrandTotal['sales'],2) }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right">₱ {{ number_format($hourlyGrandTotal['discount'],2) }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $hourlyGrandTotal['customer'] }}</td>
                                <td class="border border-gray-300 px-3 py-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>            
           
            <div class="border border-gray-300 bg-white">
                <div class="border-b border-gray-300 bg-gray-100 px-3 py-2 text-[13px] font-bold tracking-[0.15em] text-gray-700">
                    CATEGORY SALES SUMMARY
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full border-collapse text-[13px]">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-left">CATEGORY / ITEM</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">QTY SOLD</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categorySummary as $cat)
                                <tr class="bg-gray-50 font-bold">
                                    <td class="border border-gray-300 px-3 py-2 text-left">{{ $cat->category_name }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $cat->total_quantity }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-right">₱ {{ number_format($cat->total_amount,2) }}</td>
                                </tr>
                                @foreach($cat->menus as $m)
                                    <tr>
                                        <td class="border border-gray-300 px-3 py-2 pl-6 text-left">{{ $m->menu_name }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $m->quantity_sold }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-right">₱ {{ number_format($m->menu_amount,2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-bold">
                            <tr>
                                <td class="border border-gray-300 px-3 py-2 text-right">GRAND TOTAL</td>
                                <td class="border border-gray-300 px-3 py-2"></td>
                                <td class="border border-gray-300 px-3 py-2 text-right">₱ {{ number_format($categoryGrandTotal,2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div> --}}    

        <!-- TOP SELLING & TOP FREQUENT – placed in a single row across both columns (full width) -->
        {{-- <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
            <!-- TOP SELLING ITEMS -->
            <div class="border border-gray-300 bg-white">
                <div class="border-b border-gray-300 bg-gray-100 px-3 py-2 text-[13px] font-bold tracking-[0.15em] text-gray-700">
                    8 TOP SELLING ITEMS
                </div>
                <div class="p-3 bg-gray-50">
                    @if(isset($topSellingItems) && $topSellingItems->count())
                        @foreach($topSellingItems as $item)
                            <div class="flex items-center justify-between gap-3 py-1 {{ !$loop->last ? 'border-b border-dashed border-gray-300' : '' }}">
                                <span class="font-bold uppercase text-gray-800">{{ strtoupper($item->menu_name) }} - {{ strtoupper($item->brand_name ?? 'N/A') }}</span>
                                <span class="whitespace-nowrap text-gray-700">₱ {{ number_format((float) $item->total_amount, 2, '.', ',') }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 py-2">NO SOLD ITEMS FOUND FOR THIS REPORT PERIOD.</p>
                    @endif
                </div>
            </div>

            <!-- TOP FREQUENTLY SOLD ITEMS -->
            <div class="border border-gray-300 bg-white">
                <div class="border-b border-gray-300 bg-gray-100 px-3 py-2 text-[13px] font-bold tracking-[0.15em] text-gray-700">
                    8 TOP FREQUENTLY SOLD ITEMS
                </div>
                <div class="p-3 bg-gray-50">
                    @if(isset($topItems) && $topItems->count())
                        @foreach($topItems as $item)
                            <div class="flex items-center justify-between gap-3 py-1 {{ !$loop->last ? 'border-b border-dashed border-gray-300' : '' }}">
                                <span class="font-bold uppercase text-gray-800">{{ strtoupper($item->menu_name) }} - {{ strtoupper($item->brand_name ?? 'N/A') }}</span>
                                <span class="whitespace-nowrap text-gray-700">{{ $item->transaction_count }}/{{ number_format((float) $item->total_quantity, 3, '.', ',') }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 py-2">NO SOLD ITEMS FOUND FOR THIS REPORT PERIOD.</p>
                    @endif
                </div>
            </div>
        </div> --}}
       {{--  MODE OF PAYMENT and CASH BREAKDOWN PER Cashier --}}
       {{--  <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
            <!-- MODE OF PAYMENT SUMMARY -->
            <div class="border border-gray-300 bg-white">
                <div class="border-b border-gray-300 bg-gray-100 px-3 py-2 text-[13px] font-bold tracking-[0.15em] text-gray-700">
                    TERMINAL CASH POSITION
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full border-collapse text-[13px]">
                        <tbody>
                            @foreach($paymentSummary as $p)
                                <tr class="{{ isset($p->is_special) && $p->is_special ? 'bg-red-50' : '' }}">
                                    <td class="border border-gray-300 px-3 py-2 text-left">
                                        {{ $p->mop }}
                                        @if(isset($p->is_special) && $p->is_special)
                                            <span class="ml-2 text-xs text-red-600">*</span>
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        {{ $p->total_count }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-right">
                                        ₱ {{ number_format($p->total_amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <!-- PAID Transactions Grand Total -->
                        <tfoot class="bg-green-100 font-bold">
                            <tr>
                                <td class="border border-gray-300 px-3 py-2 text-right text-green-800">PAID GRAND TOTAL</td>
                                <td class="border border-gray-300 px-3 py-2 text-center text-green-800">{{ $paidTotalCount }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right text-green-800">₱ {{ number_format($paidGrandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                        <!-- ALL Transactions Grand Total (including VOID and UNPAID) -->
                        <tfoot class="bg-gray-100 font-bold">
                            <tr>
                                <td class="border border-gray-300 px-3 py-2 text-right">ALL TRANSACTIONS GRAND TOTAL</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $allTotalCount }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right">₱ {{ number_format($allGrandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="px-3 py-1 text-xs text-gray-500">
                    <span class="text-red-600">*</span> VOID/CANCELLED and UNPAID transactions
                </div>
            </div>
            <!-- CASHIER PAYMENT BREAKDOWN -->
            <div class="border border-gray-300 bg-white">
                <div class="border-b border-gray-300 bg-gray-100 px-3 py-2 text-[13px] font-bold tracking-[0.15em] text-gray-700">
                    CASHIER CASH POSITION
                </div>
                <div class="p-4">
                    @if(isset($cashierData) && count($cashierData) > 0)
                        <div class="font-mono text-[13px]">
                            @foreach($cashierData as $cashierName => $data)
                                <div class="mb-3 pb-3 {{ !$loop->last ? 'border-b border-gray-300' : '' }}">
                                    <div class="font-bold mb-1.5">
                                        === {{ strtoupper($cashierName) }} ===
                                    </div>
                                    @foreach($data['payments'] as $payment)
                                        <div class="flex justify-between py-0.5">
                                            <span>({{ $payment['count'] }}) {{ $payment['mop'] }}</span>
                                            <span class="text-right">₱ {{ number_format($payment['amount'], 2, '.', ',') }}</span>
                                        </div>
                                    @endforeach
                                    <div class="flex justify-between font-bold pt-1 mt-1 border-t border-gray-300">
                                        <span>TOTAL</span>
                                        <span class="text-right">₱ {{ number_format($data['total'], 2, '.', ',') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 py-4 text-center">NO PAYMENT DATA FOUND FOR THIS REPORT PERIOD.</p>
                    @endif
                </div>
            </div>                    
        </div> --}}

</div> <!-- end zreadingContainer -->



