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
        <!-- Modal toggle -->
        {{-- <button data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" type="button">
            Toggle modal
        </button> --}}

        <button data-modal-target="itemReturnModal" data-modal-toggle="itemReturnModal" id="openreturnModal" class="hidden" type="button"></button>
        
        <!-- Main modal -->
        <div id="itemReturnModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="font-semibold text-gray-900 flex items-center">
                            <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-orange-600"></i>
                            <span class="text-orange-600 text-base md:text-lg lg:text-xl">Return Item</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemReturnModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-3 md:py-6 space-y-6">
                        <p class="text-xs md:text-base leading-relaxed text-gray-500 text-center">
                            Are you sure you want to return this item to table?
                        </p>
                        <p class="text-xs md:text-base leading-relaxed text-red-500 font-bold text-center border-t-2 py-1 border-dark">
                            NOTE: This action will return the transaction to the table.
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <a href="javascript:void(0)" id="returnAccept"  data-slug="" data-modal-hide="itemReturnModal" class=" w-24 text-white bg-gradient-to-br from-blue-800 to-blue-500 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>

                        <button data-modal-hide="itemReturnModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- RETURN MODAL END --}}
    
    <!-- View modal -->
    <div id="viewModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow">
                <!-- Modal header -->
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 id="viewTranNumber" class="text-xl font-semibold text-gray-900">
                        Transaction Number
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="viewModal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-6 space-y-6">
                    <div>
                        <div id="viewOrders" class="font-bold text-xl">
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                {{-- href="{{ url('/orders/print/${id}') }}" --}}
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

    <div class="p-3 {{ (Auth::user()->role == 1) ? 'lg:ml-64' : '' }}" >
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                {{-- CONTROLS --}}
                    <div class="mb-3">
                        <div class="flex justify-between">
                            <div class="w-32">
                                <!-- a href="{{ (Auth::user()->role == 1) ? route('dashboard')  : route('pos.index') }}" class=" md:block text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px text-center"><i class="uil uil-arrow-left mr-1"></i>BACK</a -->
                            </div>
                            {{-- <div class="w-32">
                                <a href="{{ url('/report/print/'.$startDate.'/'.$endDate.'/'.$category.'/'.$report) }}" target="_blank" class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px text-center"><i class="uil uil-print mr-1"></i>PRINT</a>
                            </div> --}}
                        </div>
                    </div>
                {{-- CONTROLS END --}}

                <div>
                    <div class="my-4 flex justify-between">
                        <h1 class="text-3xl">Transactions</h1>
                        {{-- Updated form section --}}
                        <form method="POST" action="{{ route('transactions.generate') }}" class="flex">
                            @csrf

                            {{-- BRANCH FILTER --}}
                            <div class="flex items-center mr-2">
                                <!-- label for="branch" class="block text-sm font-medium text-gray-900 mb-1">
                                    Branch <span class="text-red-500">*</span>
                                </label -->
                                <select id="branch" name="branch"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="all" {{ $selectedBranch == 'all' ? 'selected' : '' }}>All Branches</option>
                                    @foreach ($branches as $branch)
                                        @if ($branch->id != 1) {{-- Assuming id=1 is the main branch or admin branch --}}
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
                    <div class="my-4 flex justify-center">
                        <span class="p-2 text-green-600">Paid : {{ $audited }}</span> 
                        <span class="p-2 text-red-600">Unpaid : {{ $nonAudited }}</span>
                    </div>
                    {{-- TABLE --}}
                        <div class=" md:block">
                            <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="border-b border-neutral-500">
                                            <th class="px-6 text-center">
                                                Date
                                            </th>
                                            <th class="px-6 text-center">
                                                Transaction Number
                                            </th>
                                            <th class="px-6 text-center">
                                                Amount
                                            </th>
                                            <th class="px-6 text-center">
                                                Table
                                            </th>
                                            <th class="px-6 text-center">
                                                Mode of Payment
                                            </th>
                                            <th class="px-6 text-center">
                                                Status
                                            </th>
                                            <th class="px-6 text-center">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="transactionDiv">
                                        @foreach ($results as $result)
                                            @if (Auth::user()->role == 2)
                                                @if ($result->ostatus == 'PREPARING' || $result->ostatus == 'COMPLETED' ) 
                                            @php
                                                // Debug: Show all properties of $result
                                                // Remove this after debugging
                                                // dd($result); // Uncomment to see all properties
                                            @endphp                                        
                                            <tr  class="resultRow border-b cursor-pointer hover:bg-gray-200 even:bg-gray-100">
                                                <th data-modal-target="viewModal" data-modal-toggle="viewModal" 
                                                    class="px-6 py-1 text-center font-medium text-gray-900 whitespace-nowrap">
                                                    <span data-id="{{ $result->local_id }}" 
                                                        data-branch="{{ isset($result->branch_code) ? $result->branch_code : 'default' }}"> <!-- Add null check -->
                                                        {{ date('Y-m-d h:i:s A', strtotime($result->date)) }}
                                                    </span>
                                                </th>
                                                <td data-modal-target="viewModal" data-modal-toggle="viewModal" class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->nn }}
                                                </td>
                                                <td data-modal-target="viewModal" data-modal-toggle="viewModal" class="px-6 py-1 text-center whitespace-nowrap">
                                                    ₱ {{ number_format($result->amount, 2, '.', ',') }}
                                                </td>
                                                <td data-modal-target="viewModal" data-modal-toggle="viewModal" class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->table }}
                                                </td>
                                                <td data-modal-target="viewModal" data-modal-toggle="viewModal" class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->mode_of_payment }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap {{ ($result->tstatus == 'PAID') ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $result->tstatus }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    <a data-slug="{{ $result->slug }}"
                                                    class="auditBtn {{ ($result->sales_type == 0) ? 'text-blue-600' : 'text-red-600' }} hover:underline font-semibold text-sm">{{ ($result->sales_type == 0) ? 'Mark as Serve' : 'Mark as Not Served(SS)' }}</a>
                                                    @if (auth()->user()->role == '5' || auth()->user()->role == '1')
                                                    | 
                                                    <a data-slug="{{ $result->slug }}" class="returnButton font-bold border-2 border-transparent rounded-lg px-3 py-2 text-white bg-gradient-to-br from-red-800 to-red-500">
                                                        Return to table
                                                    </a>
                                                    @endif
                                                </td>
                                                {{-- url('/expenses/edit/'.$inventory->id)  {{ $inventory->id }} --}}
                                            </tr>
                                                @endif
                                            @else
                                            <tr  class="resultRow border-b cursor-pointer hover:bg-gray-200 even:bg-gray-100">
                                                <th data-modal-target="viewModal" data-modal-toggle="viewModal" 
                                                    class="px-6 py-1 text-center font-medium text-gray-900 whitespace-nowrap">
                                                    <span data-id="{{ $result->local_id }}" 
                                                        data-branch="{{ isset($result->branch_code) ? $result->branch_code : 'default' }}"> <!-- Add null check -->
                                                        {{ date('Y-m-d h:i:s A', strtotime($result->date)) }}
                                                    </span>
                                                </th>
                                                <td data-modal-target="viewModal" data-modal-toggle="viewModal" class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->nn }}
                                                </td>
                                                <td data-modal-target="viewModal" data-modal-toggle="viewModal" class="px-6 py-1 text-center whitespace-nowrap">
                                                    ₱ {{ number_format($result->amount, 2, '.', ',') }}
                                                </td>
                                                <td data-modal-target="viewModal" data-modal-toggle="viewModal" class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->table }}
                                                </td>
                                                <td data-modal-target="viewModal" data-modal-toggle="viewModal" class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->mode_of_payment }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap {{ ($result->tstatus == 'PAID') ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $result->tstatus . ' - ('. $result->ostatus . ')' }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    <a data-slug="{{ $result->slug }}"
                                                    class="auditBtn {{ ($result->sales_type == 0) ? 'text-blue-600' : 'text-red-600' }} hover:underline font-semibold text-sm">{{ ($result->sales_type == 0) ? 'Mark as Serve' : 'Mark as Not Served(SS)' }}</a>
                                                    @if (auth()->user()->role == '5' || auth()->user()->role == '1')
                                                    | 
                                                    <a data-slug="{{ $result->slug }}" class="returnButton font-bold border-2 border-transparent rounded-lg px-3 py-2 text-white bg-gradient-to-br from-red-800 to-red-500">
                                                        Return to table
                                                    </a>
                                                    @endif
                                                </td>
                                                {{-- url('/expenses/edit/'.$inventory->id)  {{ $inventory->id }} --}}
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {{-- TABLE END --}}

                    {{-- INVENTORY LIST SMALL DEVICE --}}
                        {{--<div id="inventoryMobile" class="overflow-auto md:hidden">
                            <div id="accordion-collapse" data-accordion="collapse">
                                @php
                                    $x = 1;
                                    foreach ($results as $result) {
                                            $mqt = 'Mode of Payment';
                                            $mq = $result->mode_of_payment;
                                        if($x == 1){
                                            echo '
                                                <h2 id="accordion-collapse-heading-'.$x.'">
                                                    <button type="button" class="flex items-center justify-between w-full px-3 py-1.5 text-sm font-semibold text-left text-gray-500 border border-b-0 border-gray-200 rounded-t-xl hover:bg-gray-100" data-accordion-target="#accordion-collapse-body-'.$x.'" aria-expanded="false" aria-controls="accordion-collapse-body-'.$x.'">
                                                        <span>'.$result->nn.'</span>
                                                        <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                    </button>
                                                </h2>
                                                <div id="accordion-collapse-body-'.$x.'" class="hidden" aria-labelledby="accordion-collapse-heading-'.$x.'">
                                                    <div class="px-3 py-1.5 font-light border border-b-0 border-gray-200">
                                                        <div class="grid grid-cols-3">
                                                            <div class="text-xs leading-5">Date</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                '.date('Y-m-d', strtotime($result->date)).'
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-3">
                                                            <div class="text-xs leading-5">Amount</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                ₱ '.$result->amount.'.00
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-3">
                                                            <div class="text-xs leading-5">'.$mqt.'</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                '.$mq.'
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ';
                                        }else if($x == $results->count()){
                                            echo '
                                                <h2 id="accordion-collapse-heading-'.$x.'">
                                                    <button type="button" class="flex items-center justify-between w-full px-3 py-1.5 text-sm font-medium text-left text-gray-500 border border-gray-200 hover:bg-gray-100" data-accordion-target="#accordion-collapse-body-'.$x.'" aria-expanded="false" aria-controls="accordion-collapse-body-'.$x.'">
                                                        <span>'.$result->nn.'</span>
                                                        <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                    </button>
                                                </h2>
                                                <div id="accordion-collapse-body-'.$x.'" class="hidden" aria-labelledby="accordion-collapse-heading-'.$x.'">
                                                    <div class="px-3 py-1.5 font-light border border-t-0 border-gray-200 rounded-b-xl">
                                                        <div class="grid grid-cols-3 content-center">
                                                            <div class="text-xs leading-5">Date</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                '.date('Y-m-d', strtotime($result->date)).'
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-3">
                                                            <div class="text-xs leading-5">Amount</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                ₱ '.$result->amount.'.00
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-3">
                                                            <div class="text-xs leading-5">'.$mqt.'</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                '.$mq.'
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ';
                                        }else{
                                            echo '
                                                <h2 id="accordion-collapse-heading-'.$x.'">
                                                    <button type="button" class="flex items-center justify-between w-full px-3 py-1.5 text-sm font-medium text-left text-gray-500 border border-b-0 border-gray-200 hover:bg-gray-100" data-accordion-target="#accordion-collapse-body-'.$x.'" aria-expanded="false" aria-controls="accordion-collapse-body-'.$x.'">
                                                        <span>'.$result->nn.'</span>
                                                        <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                    </button>
                                                </h2>
                                                <div id="accordion-collapse-body-'.$x.'" class="hidden" aria-labelledby="accordion-collapse-heading-'.$x.'">
                                                    <div class="px-3 py-1.5 font-light border border-b-0 border-gray-200">
                                                        <div class="grid grid-cols-3 content-center">
                                                            <div class="text-xs leading-5">Date</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                '.date('Y-m-d', strtotime($result->date)).'
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-3">
                                                            <div class="text-xs leading-5">Amount</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                ₱ '.$result->amount.'.00
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-3">
                                                            <div class="text-xs leading-5">'.$mqt.'</div>
                                                            <div class="col-span-2 font-semibold text-sm">
                                                                '.$mq.'
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ';
                                        }
                                        $x++;
                                    }
                                @endphp
                            </div>
                        </div>--}}
                    {{-- INVENTORY LIST SMALL DEVICE END --}}
                </div>

                {{-- PAGINATION --}}
                    {{-- <div class="grid md:grid-cols-2 mt-3 px-3">
                        @php
                            $prev = $page - 1;
                            $next = $page + 1;
                            $from = ($prev * 100) + 1;
                            $to = $page * 100;
                            if($to > $resultsCount){
                                $to = $resultsCount;
                            }if($resultsCount == 0){
                                $from = 0;
                            }
                        @endphp
                        <div class="justify-self-center md:justify-self-start self-center">
                            <span class="text-sm text-gray-700">
                                Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $resultsCount }}</span> Items
                            </span>
                        </div>

                        <div class="justify-self-center md:justify-self-end">
                            <nav aria-label="Page navigation example" class="h-8 mb-0.5 shadow-xl">
                                <ul class="inline-flex items-center -space-x-px">
                                    <li>
                                        <a href="{{ ($search == '') ? url('/system-management/user/'.$prev) : url('/system-management/user/'.$prev.'/'.$search);  }}"  class="{{ ($page == 1) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                            <i class="uil uil-angle-left-b"></i>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li>
                                        <p class="block w-9 h-9 leading-9 text-center z-10 text-gray-500 border border-gray-300 bg-white font-semibold">{{ $page }}</p>
                                    </li>
                                    <li>
                                        <a href="{{ ($search == '') ? url('/system-management/user/'.$next) : url('/system-management/user/'.$next.'/'.$search); }}" class="{{ ($to == $resultsCount) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
                                            <i class="uil uil-angle-right-b"></i>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div> --}}
                {{-- PAGINATION END --}}


            </div>
        </div>
        </div>
        <input type="hidden" id="auditVal" value=" {{ $audited }} ">
        <input type="hidden" id="notAuditVal" value=" {{ $nonAudited }} ">
    <script>

        
        $(document).ready(function() {
            var _token = $('input[name="_token"]').val();
            $('#serveAmount').text($('#auditVal').val());
            $('#notServeAmount').text($('#notAuditVal').val());
        $('.resultRow').click(function(){
            var id = $(this).find('span').data('id');
            
            $.ajax({
                url:"{{ route('transactions.view') }}",
                method:"POST",
                dataType: 'json',
                data:{
                    id: id,  // Pass ONLY the ID
                    _token: _token
                },
                success:function(result){
                    $('#viewTranNumber').html('Transaction #' + result.number);
                    $('#viewOrders').html(result.ordered);
                    $('#printUrl').attr('href', result.url);
                    $('#printOnlyUrl').attr('href', result.printOnly);
                }
            });
        });
            // Update the auditBtn click handler
            jQuery(document).on("click", ".auditBtn", function(){
                var slug = $(this).data('slug');
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                var branch = $('#branch').val(); // Get selected branch
                
                $.ajax({
                    url:"{{ route('transactions.updatestatus') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        startDate: startDate,
                        endDate: endDate,
                        branch: branch // Add branch to the data
                    },
                    success:function(result){
                        location.reload();
                    }
                })
            });
            
            $('.returnButton').click(function(){
                var slug = $(this).data('slug');
                //alert(amount)
                $('#returnAccept').data('slug', slug);
                $('#openreturnModal').click();
            });
            
            $('#returnAccept').click(function(){
                
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();

                // alert(slug)
                $.ajax({
                    url: "{{ route('transactions.returnTable') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        slug: slug,
                        _token: _token
                    },
                    success: function(response) {
                        if (response.message) {
                            // Display success message
                            
                            alert(response.message);
                            location.reload();
                        } else if (response.error) {
                            // Display error message
                            alert(response.error);
                        }
                        // Optionally, reload the page after the message is shown
                        
                    }
                });
            });
        });
    </script>
</x-app-layout>
