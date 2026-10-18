<x-app-layout>
    <style>
        #inventoryMobile {
            max-height: calc(100vh - 245px);
        }

        @media (min-width: 768px) {
            #inventoryTable {
                max-height: calc(100vh - 225px);
            }
        }

        @media (min-width: 1024px) {
            #inventoryTable {
                max-height: calc(100vh - 180px);
            }
        }
    </style>

    @section('page_title', 'Received')

    {{-- DELETE MODAL --}}
    <!-- Modal toggle -->
    {{-- <button data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" type="button">
            Toggle modal
        </button> --}}

    <!-- Main modal -->
    <div id="itemDeleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <!-- Modal content -->
            <div
                class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                <!-- Modal header -->
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-red-700"></i>
                        <span class="text-red-700 text-base md:text-lg lg:text-xl">Delete Item</span>
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                        data-modal-hide="itemDeleteModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500">
                        Are you sure you want to delete this item?
                    </p>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <a type="button" href=""
                        class="deleteConfirm w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>
                    <button data-modal-hide="itemDeleteModal" type="button"
                        class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- DELETE MODAL END --}}

    {{-- NOTIFICATION --}}
    @if (session('message'))
        <div
            class="notif absolute left-1/2 top-14 -translate-x-1/2 w-72 h-12 lg:ml-32 bg-emerald-200 rounded-lg z-50 shadow-md shadow-emerald-800 flex flex-row justify-between px-2">
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


    {{-- Approved MODAL --}}
    <!-- Modal toggle -->
    {{-- <button data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" type="button">
            Toggle modal
        </button> --}}

    <!-- Main modal -->
    <div id="approvedModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full">
        <div class="relative w-full h-full max-w-6xl md:h-auto">
            <!-- Modal content -->
            <div
                class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-h-[90vh] bg-white rounded-lg shadow overflow-y-auto">
                <!-- Modal header -->
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-exclamation-circle mr-2 text-xl md:text-2xl lg:text-3xl text-gray-700"></i>
                        <span class="text-gray-700 text-base md:text-lg lg:text-xl">Order Approval Confirmation</span>
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                        data-modal-hide="approvedModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500">
                        Are you sure you want to Approve this request?
                        @csrf
                        <input type="hidden" name="slug" id="slug" value="{{ $reqNumber->slug }}">
                    </p>
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 border">#</th>
                                <th class="px-3 py-2 border">Item</th>
                                <th class="px-3 py-2 border">Approved Quantity</th>
                                <th class="px-3 py-2 border">Supplier</th>
                                <th class="px-3 py-2 border">Brand</th>
                                <th class="px-3 py-2 border">Price</th>
                                <th class="px-3 py-2 border">Total Amount</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($approvedOrders as $key => $order)
                                <tr>
                                <td class="px-3 py-2 border">{{ $key+1 }}</td>
                                <td class="px-3 py-2 border">{{ str_replace('rw-', '', strtolower($order->name)) }}</td>
                                <td class="px-3 py-2 border">{{ number_format($order->qty_received, 4, '.', '') }}</td>
                                <td class="px-3 py-2 border">{{ $order->supplier_name }}</td>
                                <td class="px-3 py-2 border">{{ $order->brand_name }}</td>
                                <td class="px-3 py-2 border">{{ number_format ($order->supplier_price, 2, '.' , '') }}</td>  
                                <td class="px-3 py-2 border">{{ number_format($order->supplier_price * $order->qty_received, 2, '.', ',') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <label for="overall_remarks" class="block text-sm font-medium text-gray-700">Overall Remarks</label>
                        <input type="text" name="overall_remarks" id="overall_remarks" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            placeholder="Enter overall remarks here">
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="button" data-modal-hide="approvedModal"
                        class="approvedRequestBtn w-24 text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</button>
                    <button data-modal-hide="approvedModal" type="button"
                        class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Approved MODAL END --}}



    <div class="p-3 {{ Auth::user()->role == 1 ? 'lg:ml-64' : '' }} lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                {{-- CONTROLS --}}
                {{-- <div class="mb-3">
                        <div class="md:grid md:grid-cols-2">
                            <div class=" w-24">
                                <a href="{{ route('request.index') }}" class="block mb-3 md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px"><i class="uil uil-plus mr-1"></i>ADD</a>
                            </div>
                            <div class="justify-self-end w-full xl:w-4/5">
                                <form method="GET" action="" id="searchForm" class="w-full">
                                    <label for="searchInput" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input type="search" id="searchInput" class="block w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="SEARCH" value="{{ $search }}" autocomplete="off">
                                        <button id="clearButton" type="button" class=" absolute right-20 bottom-1">
                                            <i class="uil uil-times text-2xl"></i>
                                        </button>
                                        <button id="searchButton" type="button" style="bottom: 5px; right: 5px;" type="submit" class="text-white absolute bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-2.5 py-1.5">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div> --}}
                {{-- CONTROLS END --}}

                <div>
                    {{-- TABLE --}}
                    <div class=" md:block">
                        <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th colspan="11" class="text-xl px-6 py-3 whitespace-nowrap">
                                            <div class="flex justify-between w-full">
                                                <div class="text-left">
                                                    Request Number : 
                                                    <span class="text-red-500">{{ $reqNumber->number }} </span>
                                                    <input type="hidden" name="gettranid" value="{{ $reqNumber->id }}">
                                                    <input type="hidden" name="getinvid" value="{{ $reqNumber->id }}">
                                                </div>
                                                <div class="text-right">
                                                    Delivery Schedule : 
                                                    <span class="text-red-500">
                                                        {{ \Carbon\Carbon::parse($reqNumber->delivery_datetime)->format('M d, Y h:i A') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr class="text-lg border-2 ">
                                        <th scope="row"
                                            class="border-2 px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Name
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Stock
                                        </th>
                                        <!-- <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Reorder
                                        </th> -->
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Request
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Supplier
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Brand
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Price per Kilo
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Total Amount
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Override Quantity
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Override Total Amount
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap hidden">
                                            Order Status
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Action
                                        </th>
                                        <th scope="row"
                                            class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            Remarks
                                        </th>
                                        <!-- <th scope="row" class="border-2 text-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                Remarks
                                            </th> -->

                                    </tr>
                                </thead>
                                        <tfoot>
                                            <tr>
                                                <th colspan="11" class="px-6 py-3 text-right">
                                                    <div class="inline-flex items-center space-x-2">
                                                        <button id="approve-all" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition">Approve All</button>
                                                        <span>|</span>
                                                        <button id="decline-all" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow hover:bg-red-700 transition">Decline All</button>
                                                    </div>
                                                </th>
                                            </tr>
                                        </tfoot>
                                <tbody>
                                   @foreach ($requestTransaction as $itemName => $request)
                                        <tr class="bg-white border-2 text-left text-gray-500">
                                            <td scope="row"
                                                class=" font-medium text-gray-900 whitespace-nowrap text-left pl-2">
                                                {{ str_replace('rw-', '', strtolower($itemName)) }}
                                                
                                            </td>
                                            <td scope="row"
                                                class="border-2 text-center  font-medium text-gray-900 whitespace-nowrap">
                                                {{ number_format($request->first()->current_stock, 2) }}
                                            </td>
                                            
                                            <td scope="row"
                                                class="border-2 text-center  font-medium text-gray-900 whitespace-nowrap">
                                                {{ number_format($request->first()->quantity, 2) }}
                                            </td>
                                            <td class="border-2 text-center font-medium text-gray-900 whitespace-nowrap">
                                                <select name="supplier_id" class="border rounded px-2 py-1 supplier-dropdown" data-item="{{ $itemName }}">
                                                    @foreach($request->groupBy('supplier_id') as $supplierId => $supplierRequests)
                                                        <option value="{{ $supplierId }}">
                                                            {{ $supplierRequests->first()->supplier_name }}   
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>                                            
                                            <td class="border-2 text-center font-medium text-gray-900 whitespace-nowrap">
                                                @foreach($request->groupBy('supplier_id') as $supplierId => $supplierRequests)
                                                    @if($supplierRequests->count() > 1)
                                                        <select name="brand_id" class="border rounded px-2 py-1 brand-dropdown hidden supplier-{{ $supplierId }}">
                                                            @foreach($supplierRequests as $brand)
                                                                <option value="{{ $brand->brand_id }}"
                                                                        data-price="{{ $brand->supplier_price }}"
                                                                        data-total="{{ $brand->supplier_total }}"
                                                                        data-itemstatus="{{ $brand->order_approval }}">
                                                                        
                                                                    {{ $brand->brand_name }}  
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                       <select name="brand_id" class="border rounded px-2 py-1 brand-dropdown hidden supplier-{{ $supplierId }}">
                                                            @foreach($supplierRequests as $brand)
                                                                <option value="{{ $brand->brand_id }}"
                                                                        data-price="{{ $brand->supplier_price }}"
                                                                        data-total="{{ $brand->supplier_total }}"
                                                                        data-itemstatus="{{ $brand->order_approval }}">
                                                                        
                                                                    {{ $brand->brand_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td class="border-2 text-center font-medium text-gray-900 whitespace-nowrap content-center price-cell">
                                                <span class="price-display">0.00</span>
                                            </td>
                                            <td class="border-2 text-center font-medium text-gray-900 whitespace-nowrap content-center price-cell">
                                                <span class="total-display">0.00</span>
                                            </td>
                                            <td scope="row"
                                                class="border-2 text-center  font-medium text-gray-900 whitespace-nowrap content-center">
                                                <input type="number" data-id="{{ $request->first()->id }}" min = "0"
                                                    max="{{ $request->first()->qty_approved }}"
                                                    class="recStockTxt text-gray-900 text-sm rounded-lg border-gray-300 block w-full  h-full  text-center"
                                                    value="{{ number_format($request->first()->qty_received, 2) }}">

                                            </td>
                                            <td class="border-2 text-center font-medium text-gray-900 whitespace-nowrap content-center price-cell">
                                                <span class="total-override">0.00</span>
                                            </td>
                                            
                                            <td class="border-2 text-center font-medium text-gray-900 whitespace-nowrap content-center price-cell hidden">
                                                <span class="status-display" data-id="{{ $request->first()->id }}" data-inv-id="{{ $request->first()->inv_id }}">
                                                    {{ $request->first()->order_approval ?? 'Pending' }}</span>
                                            </td>
                                            <td scope="row"
                                                class="border-2 text-center font-medium text-gray-900 whitespace-nowrap">
                                                <select class="status-dropdown border rounded px-3 py-1 font-semibold"> 
                                                    <option value=""> Select Status</option>
                                                    <option value="Approved"  {{ $request->first()->order_approval == 'Approved' ? 'selected' : '' }}>Approve</option>
                                                    <option value="Declined" {{ $request->first()->order_approval == 'Declined' ? 'selected' : '' }}>Decline</option>
                                                </select>
                                            </td>
                                            <td scope="row" 
                                                class="font-medium text-gray-900 whitespace-nowrap text-left pl-2">
                                                <input type="text" name="order_remarks" value="" class="border rounded px-2 py-1 w-full" placeholder="Enter remarks here"/>
                                            </td>
                                            
                                            <!-- <td scope="row" class="border-2 text-center  font-medium text-gray-900 content-center">
                                                    <input type="text" name="" id="" class=" text-gray-900 text-sm rounded-lg border-gray-300 block w-full  h-full  text-center">
                                                </td> -->
                                            <!-- <td scope="row" class="text-center  font-medium text-gray-900 whitespace-nowrap">
                                                    <button class="aspect-square min-w-8 bg-red-700 rounded-lg"><i class="uil uil-multiply text-lg text-white"></i></button>
                                                </td> -->
                                            {{-- <td scope="row" class="px-6 py-4 text-center whitespace-nowrap">
                                                    <a href="{{ url('/bidding/edit/'.$request->slug) }}"  class="text-blue-600 hover:underline font-semibold text-sm">View</a> 
                                                </td> --}}
                                        </tr>
                                                

                                    @endforeach

                                    
                                        
                                                

                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-between mt-5">
                            <a href="{{ route('procurementbidding.approvedIndex') }}"
                                class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                            <button type="button" data-modal-target="approvedModal"
                                data-modal-toggle="approvedModal"
                                class="text-white w-1/2 max-w-xs bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold">SUBMIT</button>
                        </div>
                    </div>
                    {{-- TABLE END --}}


                </div>

                {{-- PAGINATION --}}
                {{-- <div class="grid md:grid-cols-2 mt-3 px-3">
                        @php
                            $prev = $page - 1;
                            $next = $page + 1;
                            $from = ($prev * 100) + 1;
                            $to = $page * 100;
                            if($to > $requestcount){
                                $to = $requestcount;
                            }if($requestcount == 0){
                                $from = 0;
                            }
                        @endphp
                        <div class="justify-self-center md:justify-self-start self-center">
                            <span class="text-sm text-gray-700">
                                Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $requestcount }}</span> Items
                            </span>
                        </div>

                        <div class="justify-self-center md:justify-self-end">
                            <nav aria-label="Page navigation example" class="h-8 mb-0.5 shadow-xl">
                                <ul class="inline-flex items-center -space-x-px">
                                    <li>
                                        <a href="{{ ($search == '') ? url('/system-management/table/'.$prev) : url('/system-management/table/'.$prev.'/'.$search)  }}"  class="{{ ($page == 1) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                            <i class="uil uil-angle-left-b"></i>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li>
                                        <p class="block w-9 h-9 leading-9 text-center z-10 text-gray-500 border border-gray-300 bg-white font-semibold">{{ $page }}</p>
                                    </li>
                                    <li>
                                        <a href="{{ ($search == '') ? url('/system-management/table/'.$next) : url('/system-management/table/'.$next.'/'.$search); }}" class="{{ ($to == $requestcount) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
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

    <script>
        document.querySelectorAll('.recStockTxt').forEach(input => {
            input.addEventListener('input', function() {
                const max = parseFloat(this.getAttribute('max'));
                let value = parseFloat(this.value);

                if (value > max) {
                    this.value = max.toFixed(2); // Limit to 2 decimal places
                }
            });
        });


            jQuery(document).on("keyup", ".recStockTxt", function() {
                var val = $(this).val().replace(/[^0-9.]/g, '');
                $(this).val(val);

            });

            jQuery(document).on("change", ".recStockTxt", function() {
                var row = $(this).closest("tr");
                var id = $(this).data('id');
                var qty = parseFloat($(this).val()) || 0;
                var price = parseFloat(row.find(".price-display").text().replace(/,/g, "")) || 0;

                var total = qty * price;

                // Update total display
                row.find(".total-override").text(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                var _token = $('input[name="_token"]').val();

                //alert(id + ' - ' + qty)
                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url: "{{ route('procurementreceive.appqtyreceived') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        id: id,
                        qty: qty,
                        _token: _token
                    },
                    success: function(result) {
                        $('#loadingScreen').addClass('hidden');
                    }
                })
            });



        $(document).ready(function() {
            $('#searchButton').click(function() {
                var search = $('#searchInput').val();
                if (search != "") {
                    $('#searchForm').prop('action',
                        `{{ url('/system-management/transfer/1/${search}') }}`);
                } else {
                    $('#searchForm').prop('action', `{{ url('/system-management/transfer/1') }}`);
                }
                $('#searchForm').submit();
            });

            $('#searchInput').on('keydown', function(event) {
                if (event.keyCode === 13) {
                    $('#searchButton').click();
                    event.preventDefault();
                }
            });

            $('#clearButton').click(function() {
                $('#searchInput').val('');
            });

            $('.notifButton').click(function() {
                $('.notif').addClass('hidden');
            });

            $('.deleteButton').click(function() {
                var slug = $(this).data('slug');
                $('.deleteConfirm').prop('href', `{{ url('/system-management/table/delete/${slug}') }}`);
            });

            $('.contentDiv').click(function() {
                $('.notif').addClass('hidden');
            });
            $('.navDiv').click(function() {
                $('.notif').addClass('hidden');
            });

            $('#navButton').click(function() {
                $('#topNav').addClass('absolute');
                $('#topNav').removeClass('sticky');
                $('#topNav').removeClass('z-50');
                $('#contentDiv').addClass('pt-14');
            });

            $(document).mouseup(function(e) {
                var container = $(".navDiv");

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $('#topNav').removeClass('absolute');
                    $('#topNav').addClass('sticky');
                    $('#topNav').addClass('z-50');
                    $('#contentDiv').removeClass('pt-14');
                }
            });

            jQuery(document).on("keyup", ".approvedStockTxt", function() {
                var val = $(this).val().replace(/[^0-9.]/g, '');
                $(this).val(val);

            });



            jQuery(document).on("click", ".approvedRequestBtn", function() {
                var slug = $('#slug').val();
                var _token = $('input[name="_token"]').val();
                var order_remarks = $('input[name="order_remarks"]').val();
                var overall_remarks = $('input[name="overall_remarks"]').val();


                //alert(slug)
                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url: "{{ route('procurementrequest.received') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        slug: slug,
                        _token: _token,
                        order_remarks: order_remarks,
                        overall_remarks: overall_remarks
                    },
                    success: function(result) {
                        $('#loadingScreen').addClass('hidden');
                        alert('Request successfully Approved')
                        location = "{{ route('procurementbidding.approvedIndex') }}";
                    }
                })
            });

                $(document).on("change", ".supplier-dropdown", function () {
                    let row = $(this).closest("tr");
                    let supplierId = $(this).val();

                    // hide all brand dropdowns
                    row.find(".brand-dropdown, [class^='supplier-']").addClass("hidden");

                    // show only this supplier's brand section
                    let brandDropdown = row.find(".supplier-" + supplierId);
                    brandDropdown.removeClass("hidden");

                    // if supplier has multiple brands, reset price/total to first option
                    if (brandDropdown.is("select")) {
                        brandDropdown.trigger("change"); // auto trigger brand change
                    } else {
                        // single brand (span)
                        let brandSingle = brandDropdown.find(".brand-single");
                        let price = parseFloat(brandSingle.data("price")) || 0;
                        let total = parseFloat(brandSingle.data("total")) || 0;
                        let status = selected.data("itemstatus") || "Pending";

                        let overridetotal = parseFloat(brandSingle.data("total")) || 0;
                        
                        row.find(".price-display")
                        .text(price > 0 ? price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "No Response / No Bidding")
                        .css("color", price > 0 ? "" : "red");

                        row.find(".total-display")
                        .text(total > 0 ? total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "No Response / No Bidding")
                        .css("color", total > 0 ? "" : "red");

                        row.find(".total-override")
                        .text(overridetotal > 0 ? overridetotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "No Response / No Bidding")
                        .css("color", overridetotal > 0 ? "" : "red");
                        
                        row.find(".status-display").text(status);
                        }
                });

                // when brand changes → update price & total
                $(document).on("change", ".brand-dropdown", function () {
                    let row = $(this).closest("tr");
                    let selected = $(this).find(":selected");

                    let price = parseFloat(selected.data("price")) || 0;
                    let total = parseFloat(selected.data("total")) || 0;
                    let status = selected.data("itemstatus") || "Pending";


                    let qty = parseFloat(row.find(".recStockTxt").val()) || 0;


                    let overridetotal = qty * price;

                    row.find(".price-display")
                    .text(price > 0 ? price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "No Response / No Bidding")
                    .css("color", price > 0 ? "" : "red");

                    row.find(".total-display")
                    .text(total > 0 ? total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "No Response / No Bidding")
                    .css("color", total > 0 ? "" : "red");

                    row.find(".total-override")
                    .text(overridetotal > 0 ? overridetotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "No Response / No Bidding")
                    .css("color", overridetotal > 0 ? "" : "red");

                    row.find(".status-display").text(status);

                });



                $(document).ready(function () {
                    $(".supplier-dropdown").each(function () {
                        $(this).trigger("change");
                    });
                });

                $(document).ready(function () {
                $('table').on('change', '.status-dropdown', function () {
                    const row = $(this).closest('tr');
                    const status = $(this).val(); 
                    const rowId = row.find('.status-display').data('id'); 
                    const invId = row.find('.status-display').data('inv-id');
                    const supplierId = row.find('select[name="supplier_id"]').val();
                    const tran_id = $('input[name="gettranid"]').val();


                    let brandId = null;
                    const brandDropdown = row.find('.brand-dropdown:not(.hidden)');
                    if (brandDropdown.length) {
                        brandId = brandDropdown.val();
                    } else {
                        const brandSingle = row.find('.brand-single');
                        brandId = brandSingle.length ? brandSingle.data('brand-id') : null;
                    }

                    $.ajax({
                        url: "{{ route('procurementbidding.updateStatus') }}",
                        type: "POST",
                        data: {
                            id: tran_id,
                            inv_id: invId,
                            supplier_id: supplierId,
                            brand_id: brandId,
                            order_approval: status,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                // reset all status displays in the same group (tran_id + inv_id)
                                const allRows = $('table tbody tr').filter(function() {
                                    return $(this).find('.status-display').data('inv-id') == invId;
                                });

                                allRows.find('.status-display').text("Change Supplier / Brand");
                                allRows.find('.status-dropdown').val("Change Supplier / Brand");

                                // then set only the current row to Approved/Declined
                                row.find('.status-display').text(response.status); 
                                row.find('.status-dropdown').val(response.status);  
                            }
                        },
                        error: function() {
                            alert("Something went wrong!");
                        }
                    });
                });

                $('table').on('change', '.supplier-dropdown', function () {
                    const row = $(this).closest('tr');
                    const supplierId = $(this).val();

                    // hide all brand dropdowns/singles in this row
                    row.find('.brand-dropdown').addClass('hidden');
                    row.find('.brand-single').addClass('hidden');

                    let brandId = null;

                    // show only the brand(s) for this supplier
                    const brandDropdown = row.find('.brand-dropdown.supplier-' + supplierId);
                    if (brandDropdown.length) {
                        brandDropdown.removeClass('hidden');
                        brandId = brandDropdown.val();
                    } else {
                        const brandSingle = row.find('.brand-single.supplier-' + supplierId);
                        if (brandSingle.length) {
                            brandSingle.removeClass('hidden');
                            brandId = brandSingle.data('brand-id');
                        }
                    }

                    
                    const statusDropdown = row.find('.status-dropdown');
                    if (supplierId && brandId) {
                        statusDropdown.val("Approved").trigger('change');
                    } else {
                        statusDropdown.val("").trigger('change');
                    }
                });

                $('table').on('change', '.brand-dropdown', function () {
                    const row = $(this).closest('tr');
                    const statusDropdown = row.find('.status-dropdown');

                    
                    statusDropdown.val("Approved").trigger('change');
                });


                $('#approve-all').on('click', function () {
                    $('table tbody tr').each(function () {
                        const row = $(this);
                        const dropdown = row.find('.status-dropdown');

                        dropdown.val('Approved').trigger('change'); 
                        row.find('.status-display').text('Approved'); 
                    });
                });

                
                $('#decline-all').on('click', function () {
                    $('table tbody tr').each(function () {
                        const row = $(this);
                        const dropdown = row.find('.status-dropdown');

                        dropdown.val('Declined').trigger('change'); 
                        row.find('.status-display').text('Declined');
                    });
                });
            });
                    

                    
                


        });
    </script>
</x-app-layout>
