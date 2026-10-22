<x-app-layout>
    <style>

    </style>

    @section('page_title', '')


    {{-- NOTIFICATION --}}
    @if (session('message'))
            <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-72 h-12 lg:ml-32 bg-emerald-200 rounded-lg z-50 shadow-md shadow-emerald-800 flex flex-row justify-between px-2">
                <div class="flex">
                    <i class="self-center uil uil-cloud-check text-emerald-800 text-2xl"></i>
                    <h1 class="self-center text-emerald-800 font-semibold ml-1">{{ session('message') }}</h1>
                </div>
                <button class="notifButton self-center">
                    <i class="uil uil-times text-2xl text-emerald-800"></i>
                </button>
            </div>
        @endif
    {{-- NOTIFICATION END --}}

    {{-- RETURN MODAL --}}
        <button data-modal-target="itemReturnModal" data-modal-toggle="itemReturnModal" id="openreturnModal" class="hidden" type="button"></button>
        
        <div id="itemReturnModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="font-semibold text-gray-900 flex items-center">
                            <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-orange-600"></i>
                            <span class="text-orange-600 text-base md:text-lg lg:text-xl">Return Item</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemReturnModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <div class="px-6 py-3 md:py-6 space-y-6">
                        <p class="text-xs md:text-base leading-relaxed text-gray-500 text-center">
                            Are you sure you want to return this item to table?
                        </p>
                        <p class="text-xs md:text-base leading-relaxed text-red-500 font-bold text-center border-t-2 py-1 border-dark">
                            NOTE: This action will return the transaction to the table.
                        </p>
                    </div>
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <a href="javascript:void(0)" id="returnAccept"  data-slug="" data-modal-hide="itemReturnModal" class=" w-24 text-white bg-gradient-to-br from-blue-800 to-blue-500 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>

                        <button data-modal-hide="itemReturnModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- RETURN MODAL END --}}
    
    <div id="viewModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 id="viewTranNumber" class="text-xl font-semibold text-gray-900">
                        Transaction Number
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="viewModal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <div id="viewOrders" class="font-bold text-xl">
                        </div>
                    </div>
                </div>
                <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b"> 
                    <a id="printUrl" target="_blank" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Print Receipt</a>
                    <button data-modal-hide="viewModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Close</button>
                    @if (auth()->user()->role == '1')
                    <a id="printOnlyUrl" target="_blank" type="button" class="text-white bg-red-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Print Only</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 lg:ml-64">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                {{-- CONTROLS --}}
                    <div class="mb-3">
                        <div class="flex justify-between">
                            <div class="w-32">
                            </div>
                        </div>
                    </div>
                {{-- CONTROLS END --}}

                <div>
                    <div class="my-4 flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <h1 class="text-3xl">Sale Journal</h1>
                            {{-- BULK ACTION BUTTON --}}
                            <button id="approveSelectedBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow transition duration-150 hidden">
                                Approve Selected (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                        
                        <form method="POST" action="{{ route('sales-journal.generate') }}" class="flex">
                            @csrf

                            {{-- BRANCH FILTER --}}
                            <div class="flex items-center mr-2">
                                <select id="branch" name="branch"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="all" {{ $selectedBranch == 'all' ? 'selected' : '' }}>All Branches</option>
                                    @foreach ($branches as $branch)
                                        @if ($branch->id != 1)
                                            <option value="{{ $branch->branch_code }}" {{ $selectedBranch == $branch->branch_code ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div date-rangepicker class="flex items-center">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <input name="startDate" id="startDate" value="{{ date('m/d/Y', strtotime($startDate)) }}" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date start">
                                </div>
                                <span class="mx-4 text-gray-500">to</span>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <input name="endDate" id="endDate" value="{{ date('m/d/Y', strtotime($endDate)) }}" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date end">
                                </div>
                            </div>
                            <button class="bg-blue-600 px-5 rounded-lg ml-3 text-white font-bold">Generate</button>
                        </form>
                    </div>

                    {{-- TABLE --}}
                    <div class="md:block">
                        <div class="flex justify-end mb-2 gap-2">
                            <button id="resetTableFilters" type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">
                                Reset filters
                            </button>
                        </div>
                        <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                            @php
                                $accountOptions = collect($results)->pluck('table')->filter()->unique()->sort()->values();
                                $salesOptions = collect($results)->pluck('total')->filter()->unique()->sort()->values();
                                $cashOptions = collect($results)->pluck('amount')->filter()->unique()->sort()->values();
                                $bdoOptions = collect($results)->map(function ($result) {
                                    $mode = strtolower($result->mode_of_payment ?? '');
                                    if (strpos($mode, 'bdo') !== false || strpos($mode, 'cib') !== false) {
                                        return number_format($result->amount, 2, '.', ',');
                                    }
                                    return null;
                                })->filter()->unique()->sort()->values();
                                $serviceChargeOptions = collect($results)->pluck('service_charge')->filter()->unique()->sort()->values();
                                $tradeOptions = collect($results)->map(function ($result) {
                                    return number_format(($result->total - $result->amount) ?? 0, 2, '.', ',');
                                })->filter()->unique()->sort()->values();
                                $referenceOptions = collect($results)->map(function ($result) {
                                    return $result->nn ?? $result->number ?? '';
                                })->filter()->unique()->sort()->values();
                                // Date options for filter
                                $dateOptions = collect($results)->map(function ($result) {
                                    return date('m/d/Y', strtotime($result->date ?? $result->created_at ?? now()));
                                })->filter()->unique()->sort()->values();

                                // Calculate Grand Totals (initial load only)
                                $grandTotalSales = $results->sum('total');
                                $grandTotalCash = $results->sum('amount');
                                $grandTotalBDO = $results->filter(function($item) {
                                    $mode = strtolower($item->mode_of_payment ?? '');
                                    return strpos($mode, 'bdo') !== false || strpos($mode, 'cib') !== false;
                                })->sum('amount');
                                $grandTotalServiceCharge = $results->sum('service_charge');
                                $grandTotalTrade = $results->sum(function($item) {
                                    return $item->total - $item->amount;
                                });
                            @endphp

                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr class="border-b border-neutral-500">
                                        <th class="px-6 py-3 text-left align-top">
                                            <div class="flex items-center justify-between">
                                                <span>Date</span>
                                                <button type="button"
                                                        class="filter-toggle text-blue-600 text-sm hover:text-blue-800"
                                                        data-column="0"
                                                        title="Filter">
                                                    <i class="uil uil-filter"></i>
                                                </button>                                                
                                            </div>
                                            <div class="mt-2 hidden filter-input-wrapper">
                                                <input list="dateList" type="text" data-column="0" class="filter-input w-full text-sm text-gray-900 border border-gray-300 rounded-lg p-2" placeholder="Select date">
                                                <datalist id="dateList">
                                                    @foreach ($dateOptions as $value)
                                                        <option value="{{ $value }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </th>                                        
                                        <th class="px-6 py-3 text-left align-top">
                                            <div class="flex items-center justify-between">
                                                <span>Customer's Account Debited</span>
                                                <button type="button"
                                                        class="filter-toggle text-blue-600 text-sm hover:text-blue-800"
                                                        data-column="1"
                                                        title="Filter">
                                                    <i class="uil uil-filter"></i>
                                                </button>                                                
                                            </div>
                                            <div class="mt-2 hidden filter-input-wrapper">
                                                <input list="accountList" type="text" data-column="1" class="filter-input w-full text-sm text-gray-900 border border-gray-300 rounded-lg p-2" placeholder="Select account">
                                                <datalist id="accountList">
                                                    @foreach ($accountOptions as $value)
                                                        <option value="{{ $value }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-right align-top">
                                            <div class="flex items-center justify-between">
                                                <span>Sales (credit)</span>
                                                <button type="button" class="filter-toggle text-blue-600 text-sm hover:text-blue-800" data-column="2" title="Filter">
                                                    <i class="uil uil-filter"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2 hidden filter-input-wrapper">
                                                <input list="salesList" type="text" data-column="2" class="filter-input w-full text-sm text-gray-900 border border-gray-300 rounded-lg p-2" placeholder="Select sales">
                                                <datalist id="salesList">
                                                    @foreach ($salesOptions as $value)
                                                        <option value="₱ {{ number_format($value, 2, '.', ',') }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-right align-top">
                                            <div class="flex items-center justify-between">
                                                <span>Cash (debit)</span>
                                                <button type="button" class="filter-toggle text-blue-600 text-sm hover:text-blue-800" data-column="3" title="Filter">
                                                    <i class="uil uil-filter"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2 hidden filter-input-wrapper">
                                                <input list="cashList" type="text" data-column="3" class="filter-input w-full text-sm text-gray-900 border border-gray-300 rounded-lg p-2" placeholder="Select cash">
                                                <datalist id="cashList">
                                                    @foreach ($cashOptions as $value)
                                                        <option value="₱ {{ number_format($value, 2, '.', ',') }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-right align-top">
                                            <div class="flex items-center justify-between">
                                                <span>CIB BDO (debit)</span>
                                                <button type="button" class="filter-toggle text-blue-600 text-sm hover:text-blue-800" data-column="4" title="Filter">
                                                    <i class="uil uil-filter"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2 hidden filter-input-wrapper">
                                                <input list="bdoList" type="text" data-column="4" class="filter-input w-full text-sm text-gray-900 border border-gray-300 rounded-lg p-2" placeholder="Select BDO">
                                                <datalist id="bdoList">
                                                    @foreach ($bdoOptions as $value)
                                                        <option value="₱ {{ $value }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-right align-top">
                                            <div class="flex items-center justify-between">
                                                <span>2307</span>
                                                <button type="button" class="filter-toggle text-blue-600 text-sm hover:text-blue-800" data-column="5" title="Filter">
                                                    <i class="uil uil-filter"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2 hidden filter-input-wrapper">
                                                <input list="serviceChargeList" type="text" data-column="5" class="filter-input w-full text-sm text-gray-900 border border-gray-300 rounded-lg p-2" placeholder="Select 2307">
                                                <datalist id="serviceChargeList">
                                                    @foreach ($serviceChargeOptions as $value)
                                                        <option value="₱ {{ number_format($value, 2, '.', ',') }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </th>                                        
                                        <th class="px-6 py-3 text-right align-top">
                                            <div class="flex items-center justify-between">
                                                <span>Trade Receivables (debit)</span>
                                                <button type="button" class="filter-toggle text-blue-600 text-sm hover:text-blue-800" data-column="6" title="Filter">
                                                    <i class="uil uil-filter"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2 hidden filter-input-wrapper">
                                                <input list="tradeList" type="text" data-column="6" class="filter-input w-full text-sm text-gray-900 border border-gray-300 rounded-lg p-2" placeholder="Select trade">
                                                <datalist id="tradeList">
                                                    @foreach ($tradeOptions as $value)
                                                        <option value="₱ {{ $value }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-center align-top">
                                            <div class="flex items-center justify-between">
                                                <span>Reference</span>
                                                <button type="button" class="filter-toggle text-blue-600 text-sm hover:text-blue-800" data-column="7" title="Filter">
                                                    <i class="uil uil-filter"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2 hidden filter-input-wrapper">
                                                <input list="refList" type="text" data-column="7" class="filter-input w-full text-sm text-gray-900 border border-gray-300 rounded-lg p-2" placeholder="Select ref">
                                                <datalist id="refList">
                                                    @foreach ($referenceOptions as $value)
                                                        <option value="{{ $value }}"></option>
                                                    @endforeach
                                                </datalist>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="transactionDiv">
                                    @foreach ($results as $result)
                                        @php
                                            $customer = $result->table;
                                            $credit = $result->total ?? 0;
                                            $debit = $result->amount;
                                            $servicecharge = $result->service_charge;
                                            $mode = strtolower($result->mode_of_payment ?? '');
                                            $cash = (strpos($mode, 'cash') !== false) ? $debit : '';
                                            $bdo = (strpos($mode, 'bdo') !== false || strpos($mode, 'cib') !== false) ? $debit : '';
                                            $trade = $result->total - $result->amount ?? 0;
                                            $reference = $result->nn ?? $result->number ?? '';
                                            $transactionDate = $result->date ?? $result->created_at ?? now();
                                        @endphp
                                        <tr class="border-b hover:bg-gray-50 even:bg-white">
                                            <td class="px-6 py-2 text-left font-medium">
                                                {{ date('Y-m-d h:i:s A', strtotime($transactionDate)) }}
                                            </td>
                                            <td class="px-6 py-2 text-left font-medium">{{ $customer }}</td>
                                            <td class="px-6 py-2 text-right sales-cell">₱ {{ number_format($credit, 2, '.', ',') }}</td>
                                            <td class="px-6 py-2 text-right cash-cell">₱ {{ number_format($debit, 2, '.', ',') }}</td>
                                            <td class="px-6 py-2 text-right bdo-cell">@if($bdo) ₱ {{ number_format($bdo, 2, '.', ',') }} @endif</td>
                                            <td class="px-6 py-2 text-right service-charge-cell">₱ {{ number_format($servicecharge, 2, '.', ',') }}</td>
                                            <td class="px-6 py-2 text-right trade-cell">₱ {{ number_format($trade, 2, '.', ',') }}</td>
                                            <td class="px-6 py-2 text-center">{{ $reference }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <!-- GRAND TOTAL FOOTER -->
                                <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-400">
                                    <tr>
                                        <td colspan="2" class="px-6 py-3 text-right text-gray-700">GRAND TOTAL</td>
                                        <td id="grandTotalSales" class="px-6 py-3 text-right text-gray-700">₱ {{ number_format($grandTotalSales, 2, '.', ',') }}</td>
                                        <td id="grandTotalCash" class="px-6 py-3 text-right text-gray-700">₱ {{ number_format($grandTotalCash, 2, '.', ',') }}</td>
                                        <td id="grandTotalBDO" class="px-6 py-3 text-right text-gray-700">₱ {{ number_format($grandTotalBDO, 2, '.', ',') }}</td>
                                        <td id="grandTotalServiceCharge" class="px-6 py-3 text-right text-gray-700">₱ {{ number_format($grandTotalServiceCharge, 2, '.', ',') }}</td>
                                        <td id="grandTotalTrade" class="px-6 py-3 text-right text-gray-700">₱ {{ number_format($grandTotalTrade, 2, '.', ',') }}</td>
                                        <td class="px-6 py-3 text-center text-gray-700"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                {{-- TABLE END --}}
            </div>
        </div>
    </div>
</x-app-layout>

{{-- JAVASCRIPT FOR SELECT ALL & BULK APPROVE --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('selectAll');
        const itemCheckboxes = document.querySelectorAll('.selectTran');
        const approveBtn = document.getElementById('approveSelectedBtn');
        const countSpan = document.getElementById('selectedCount');

        // Toggles the visibility and counter on the Approve Button
        function toggleApproveButtonVisibility() {
            const checkedCount = document.querySelectorAll('.selectTran:checked').length;
            if (checkedCount > 0) {
                approveBtn.classList.remove('hidden');
                countSpan.textContent = checkedCount;
            } else {
                approveBtn.classList.add('hidden');
            }
        }

        // Handle "Select All" global toggle
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                toggleApproveButtonVisibility();
            });
        }

        // Handle Individual Checkbox changes
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const totalChecked = document.querySelectorAll('.selectTran:checked').length;
                
                // Sync Master Checkbox status
                selectAllCheckbox.checked = (totalChecked === itemCheckboxes.length);
                toggleApproveButtonVisibility();
            });
        });

        // Handle Bulk Action Approval Submission
        if (approveBtn) {
            approveBtn.addEventListener('click', function () {
                const selectedSlugs = Array.from(document.querySelectorAll('.selectTran:checked')).map(cb => cb.value);
                
                if (selectedSlugs.length === 0) return;

                if (confirm(`Are you sure you want to approve the ${selectedSlugs.length} selected transaction(s)?`)) {
                    fetch('{{ route('sales-journal.bulkApprove') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ slugs: selectedSlugs })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload(); // Refresh the table state on completion
                        } else {
                            alert(data.message || 'Something went wrong during approval.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred.');
                    });
                }
            });
        }

        const singleApproveButtons = document.querySelectorAll('.approveSingleBtn');
        singleApproveButtons.forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const slug = this.dataset.slug;
                if (!slug) return;

                if (confirm('Are you sure you want to approve this transaction?')) {
                    fetch('{{ route('sales-journal.approve') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ slug: slug })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Something went wrong during approval.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred.');
                    });
                }
            });
        });

        // Function to update grand totals based on visible rows
        function updateGrandTotals() {
            const visibleRows = document.querySelectorAll('#transactionDiv tr:not([style*="display: none"])');
            
            let totalSales = 0;
            let totalCash = 0;
            let totalBDO = 0;
            let totalServiceCharge = 0;
            let totalTrade = 0;

            visibleRows.forEach(row => {
                const salesCell = row.querySelector('.sales-cell');
                const cashCell = row.querySelector('.cash-cell');
                const bdoCell = row.querySelector('.bdo-cell');
                const serviceChargeCell = row.querySelector('.service-charge-cell');
                const tradeCell = row.querySelector('.trade-cell');

                if (salesCell) {
                    const salesValue = parseFloat(salesCell.textContent.replace(/[₱,]/g, '').trim()) || 0;
                    totalSales += salesValue;
                }

                if (cashCell) {
                    const cashValue = parseFloat(cashCell.textContent.replace(/[₱,]/g, '').trim()) || 0;
                    totalCash += cashValue;
                }

                if (bdoCell && bdoCell.textContent.trim()) {
                    const bdoValue = parseFloat(bdoCell.textContent.replace(/[₱,]/g, '').trim()) || 0;
                    totalBDO += bdoValue;
                }

                if (serviceChargeCell) {
                    const serviceChargeValue = parseFloat(serviceChargeCell.textContent.replace(/[₱,]/g, '').trim()) || 0;
                    totalServiceCharge += serviceChargeValue;
                }

                if (tradeCell) {
                    const tradeValue = parseFloat(tradeCell.textContent.replace(/[₱,]/g, '').trim()) || 0;
                    totalTrade += tradeValue;
                }
            });

            // Update the footer cells
            document.getElementById('grandTotalSales').textContent = '₱ ' + totalSales.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            document.getElementById('grandTotalCash').textContent = '₱ ' + totalCash.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            document.getElementById('grandTotalBDO').textContent = '₱ ' + totalBDO.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            document.getElementById('grandTotalServiceCharge').textContent = '₱ ' + totalServiceCharge.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            document.getElementById('grandTotalTrade').textContent = '₱ ' + totalTrade.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Excel-style per-column filtering
        const filterInputs = document.querySelectorAll('.filter-input');
        const tableRows = document.querySelectorAll('#transactionDiv tr');

        function applyTableFilters() {
            const filters = Array.from(filterInputs).map(input => input.value.trim().toLowerCase());

            tableRows.forEach(row => {
                const cells = Array.from(row.querySelectorAll('td'));
                const visible = filters.every((filter, index) => {
                    if (!filter) return true;
                    const cellText = (cells[index]?.textContent || '').trim().toLowerCase();
                    return cellText.includes(filter);
                });
                row.style.display = visible ? '' : 'none';
            });

            // Update grand totals after filtering
            updateGrandTotals();
        }

        filterInputs.forEach(input => {
            input.addEventListener('input', applyTableFilters);
        });

        const resetFiltersBtn = document.getElementById('resetTableFilters');
        const filterToggles = document.querySelectorAll('.filter-toggle');

        filterToggles.forEach(toggle => {
            toggle.addEventListener('click', function () {
                const wrapper = this.closest('th').querySelector('.filter-input-wrapper');
                if (wrapper) {
                    const isHidden = wrapper.classList.toggle('hidden');
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.className = isHidden ? 'uil uil-filter' : 'uil uil-times';
                    }
                }
            });
        });

        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', function () {
                filterInputs.forEach(input => input.value = '');
                applyTableFilters();
            });
        }

        // Initial calculation of grand totals
        updateGrandTotals();
    });
</script>