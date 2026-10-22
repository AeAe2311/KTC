<x-app-layout>

    <style>
        #inventoryMobile {
            max-height: calc(100vh - 245px);
        }


        @media (min-width: 1024px) {
            #inventoryTable {
                max-height: calc(100vh - 220px);
            }
        }

        #menuTabs::-webkit-scrollbar {
            height: 0px;
            background: transparent;
        }

        #categoryAllContents::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }

        /* Number */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }


        /* Dropdown */
        @media (max-width: 768px) {
            select {

                width: 200px;
                height: 30px;
                font-size: 12px;
                padding: 4px 8px;
                line-height: 1.25;
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
            }


            select option {
                font-size: 7px;
                padding: 1px 1px;
                line-height: 0.25;
            }
        }
    </style>

    @section('page_title', '')

    {{-- LOADING --}}
    <div wire:loading id="loadingScreen"
        class="hidden fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-[60] overflow-hidden bg-gray-900 opacity-75 opa flex flex-col items-center justify-center">
        <div role="status">
            <svg aria-hidden="true" class="inline w-10 h-10 mr-2 text-gray-200 animate-spin fill-blue-600"
                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                    fill="currentColor" />
                <path
                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                    fill="currentFill" />
            </svg>
            <span class="sr-only">Loading...</span>
        </div>
        <h2 class="text-center text-white text-xl font-semibold">Processing...</h2>
        <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
    </div>
    {{-- LOADING END --}}

    {{-- NOTIF --}}
    <div id="notifDiv" class="absolute top-16 left-1/2 -translate-x-1/2 z-50 w-[400px]">
    </div>
    {{-- NOTIF END --}}


    {{-- Request CONFIRM MODAL --}}
    <!-- Modal toggle -->


    <!-- Main modal -->
    <div id="reqConfirmModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow">
                <!-- Modal header -->
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-2xl font-semibold text-gray-900">
                        <i class="uil uil-exclamation-circle text-blue-500 text-3xl mr-3"></i>Request Confirmation
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                        data-modal-hide="reqConfirmModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-6 space-y-4">
                    <p class="text-base leading-relaxed text-gray-500">
                        Are you sure you want to submit this request?
                    </p>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                    <button id="submitButton" data-modal-hide="reqConfirmModal" data-slug="" type="button"
                        class="text-white bg-green-600 hover:bg-green-500 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Accept</button>
                    <button data-modal-hide="reqConfirmModal" type="button"
                        class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Request CONFIRM MODAL END --}}


    {{-- View Item --}}
    <div id="viewitem" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-screen">
        <div class="relative w-full max-w-sm">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-lg h-[300px] flex flex-col">
                <!-- Modal header -->
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-2xl font-semibold text-gray-900">
                        <i class="uil uil-shopping-cart-alt  text-blue-500 text-3xl mr-3"></i>Item List
                    </h3>

                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                        data-modal-hide="viewitem">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <!-- Modal body -->
 
                <div class="p-4 space-y-1 overflow-y-auto" style="max-height: 300px;" id="ordersModalContent">
           


                </div>
                <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                    <button data-modal-hide="viewitem" type="button"
                        class="text-gray-500 bg-white w-full hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>





    {{-- View Item END --}}

    <div id="simpleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-96 px-8 py-6 text-center animate-fadeIn">
            <div class="flex justify-center mb-3">
                <div class="bg-red-100 p-2 rounded-full">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-base font-semibold text-gray-800 mb-1">Notice</h3>
            <p id="modalMessage" class="text-gray-600 text-sm mb-4 leading-snug">
            </p>

            <button id="closeModalBtn"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md text-sm font-medium transition">
                Got it
            </button>
        </div>
    </div>

    <div id="simpleModal2" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-96 px-8 py-6 text-center animate-fadeIn">
            <div class="flex justify-center mb-3">
                <div class="bg-blue-100 p-2 rounded-full">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-base font-semibold text-gray-800 mb-1">Notice</h3>
            <p id="modalMessage2" class="text-gray-600 text-sm mb-4 leading-snug">
            </p>

            <button id="closeModalBtn2"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md text-sm font-medium transition">
                Got it
            </button>
        </div>
    </div>





    <div class="hidden md:block">
        <div id= "inventoryTable" class="min-h-screen p-4  gap-4 {{ Auth::user()->role == 1 ? 'lg:ml-64' : '' }}">

            {{-- LEFT CONTENT --}}

            <div
                class="h-[-10vh]  w-full xl:w-auto xl:col-span-2 bg-white shadow-lg rounded-lg border border-gray-200 overflow-hidden ">
                <div>
                    <div class="p-2 flex items-center justify-between">
                        @csrf
                        <div class="relative w-full sm:w-full md:w-full lg:w-1/2">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor"
                                    viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <input type="text" id="searchMenu"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-full focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                placeholder="Search" required autocomplete="off">
                        </div>

                    </div>
                    <div class="p-2 border-b border-neutral-300">
                        <div class="flex justify-between w-full">
                            <div class="p-px">
                                <button class="w-9 aspect-square border border-neutral-300 rounded-lg scroll-left">
                                    <i class="uil uil-arrow-left text-2xl"></i>
                                </button>
                            </div>
                            <div id="categoryTabsC" class="py-0.5 px-4 overflow-x-hidden w-[calc(100vw-920)]">
                                <div id="menuTabs" data-tabs-toggle="#categoryTabContent" role="tablist"
                                    class="overflow-x-auto flex gap-x-2 ">
                                    <button id="all-menu-tab" data-tabs-target="#all-menu" type="button"
                                        role="tab" aria-controls="all-menu" aria-selected="false"
                                        class="leading-8 px-4 font-semibold border rounded-lg whitespace-nowrap">
                                        All
                                    </button>
                                    @foreach ($categories as $category)
                                        <button id="{{ substr($category->name, 0, 3) . '-' . $category->id }}-tab"
                                            data-tabs-target="#{{ substr($category->name, 0, 3) . '-' . $category->id }}-menu"
                                            type="button" role="tab"
                                            aria-controls="{{ substr($category->name, 0, 3) . '-' . $category->id }}-menu"
                                            aria-selected="false"
                                            class="leading-8 px-4 font-semibold border rounded-lg whitespace-nowrap">
                                            {{ $category->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="p-px ">
                                <button class="w-9 aspect-square border border-neutral-300 rounded-lg scroll-right">
                                    <i class="uil uil-arrow-right text-2xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>


                    <div class="lg:flex items-center border-r-2 p-2 space-x-2">


                        <!-- <label for="supplier"
                            class="font-bold text-gray-700 text-sm whitespace-nowrap">SUPPLIER:</label>
                        <select id="supplier" id="supplier" name="supplier" placeholder="Enter supplier name"
                            class="border border-gray-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none w-1/2 lg:w-64"
                            required aria-describedby="supplierError" />
                        <option value="">-- Select Supplier --</option>
                        @foreach ($supplierlist as $supplierlistoption)
                            {
                            <option value ="{{ $supplierlistoption->id }}">{{ $supplierlistoption->name }}</option>
                            }
                        @endforeach
                        </select>
                        <span id="supplierError" class="text-red-500 text-xs mt-1 hidden">Supplier name is
                            required.</span> -->

                        <div class="flex items-center gap-2">
                            <label for="dateTimeInput" class="text-lg font-medium text-gray-900 whitespace-nowrap">Delivery Schedule</label>
                             <input type="datetime-local" id="dateTimeInput" name="dateTimeInput" class=" w-full border border-gray-300 rounded-lg px-5 py-2.5 text-sm focus:ring-2 focus:ring-green-400 focus:outline-none">
                            <button id="viewItemBtn" type="button" data-modal-target="viewitem"
                                data-modal-toggle="viewitem"
                                class="disabled:opacity-50 disabled:pointer-events-none w-full text-gray-50 bg-gradient-to-r from-green-600 to-teal-500 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center transition">
                                View Item
                            </button>
                        </div>

                    </div>
                    <div id="categoryAllContents" class="h-[calc(100vh-194px)] overflow-y-auto"
                        style="padding-bottom: 80px;">


                        <div id="categoryTabContent" class="h-auto min-h-full">
                            <div class="p-1" id="all-menu" role="tabpanel" aria-labelledby="all-menu-tab">
                                <div class="container mx-auto p-4 ">
                                    <div
                                        class="border-2 grid grid-cols-2 sm:grid-cols-2  md:grid-cols-3 lg:grid-cols-4 gap-4 font-bold text-xl   ">
                                        <div class="border-r-2 pl-2 hidden lg:block p-2">Item Name</div>
                                        <div class="border-r-2 text-center hidden lg:block p-2">Current Stock</div>
                                        <div class="border-r-2 text-center hidden lg:block p-2">Reorder Point</div>
                                        <div class="border-r-2 text-center hidden lg:block p-2">Request Stock</div>
                                    </div>
                                    @foreach ($menus as $menu)
                                        @php
                                            $requestvalue = 0;
                                            foreach ($orders as $ordered) {
                                                if ($menu->id == $ordered->inv_id) {
                                                    $requestvalue = $ordered->quantity;
                                                }
                                            }

                                        @endphp

                                        <span
                                            class=" border grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4  items-center text-center {{ $menu->quantity > $menu->reorder_point ? '' : 'bg-red-100 shadow-red-300' }}">
                                            <label
                                                class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Item
                                                :</label>

                                            <a data-slug="{{ $menu->slug }}"
                                                class="border-r-2 cols-span-1 pl-2 text-center  sm:text-center md:text-center lg:text-left xl:text-left">
                                                <div>{{ $menu->name }} </div>

                                            </a>

                                            <label
                                                class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Cur
                                                Qty :</label>

                                            <div class="cols-span-1 text-center border-r-2">
                                                {{ number_format($menu->quantity) . ' ' . $menu->unit }}</div>

                                            <label
                                                class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Reorder
                                                Qty :</label>

                                            <div class="cols-span-1 text-center border-r-2">
                                                {{ number_format($menu->reorder_point) . ' ' . $menu->unit }} </div>

                                            <label
                                                class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Req
                                                Qty :</label>

                                            <div class="cols-span-1 flex justify-center border-r-2">

                                                <div class="flex items-center space-x-2 w-3/4">
                                                    <!-- Minus Button -->
                                                    <button type="button"
                                                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold p-2 rounded"
                                                        onclick="adjustValue(this, -1)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M20 12H4" />
                                                        </svg>
                                                    </button>

                                                    <!-- Number Input -->
                                                    <input data-id="{{ $menu->id }}"
                                                        data-slug="{{ $menu->slug }}"
                                                        value="{{ $requestvalue ?? 0 }}" type="number"
                                                        min="1" autocomplete="off" required
                                                        class="reqStockTxt text-center w-full {{ $menu->quantity > $menu->reorder_point ? 'bg-white border border-white' : 'bg-red-100 shadow-red-300 border border-red-100' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                                                    <!-- Plus Button -->
                                                    <button type="button"
                                                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold p-2 rounded"
                                                        onclick="adjustValue(this, 1)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </button>
                                                </div>


                                                <button class="delete-btn text-red-600 text-xs px-1 ml-2 "
                                                    data-id="{{ $menu->id }}"> ✕</button>

                                            </div>
                                        </span>
                                    @endforeach

                                </div>



                            </div>
                            @foreach ($categories as $category)
                                <div class="p-1"
                                    id="{{ substr($category->name, 0, 3) . '-' . $category->id }}-menu"
                                    role="tabpanel"
                                    aria-labelledby="{{ substr($category->name, 0, 3) . '-' . $category->id }}-menu-tab">
                                    <div class=" container mx-auto p-4">
                                        <div
                                            class="border-2 grid grid-cols-2 sm:grid-cols-2  md:grid-cols-3 lg:grid-cols-4 gap-4 font-bold text-xl   ">
                                            <div class="border-r-2 pl-2 hidden lg:block p-2">Item Name</div>
                                            <div class="border-r-2 text-center hidden lg:block p-2">Current Stock</div>
                                            <div class="border-r-2 text-center hidden lg:block p-2">Reorder Point</div>
                                            <div class="border-r-2 text-center hidden lg:block p-2">Request Stock</div>
                                        </div>



                                        @foreach ($menus as $menu)
                                            @php
                                                $requestvalue = 0;
                                                foreach ($orders as $ordered) {
                                                    if ($menu->id == $ordered->inv_id) {
                                                        $requestvalue = $ordered->quantity;
                                                    }
                                                }

                                            @endphp
                                            @if ($menu->category_id == $category->id)
                                                <span
                                                    class="border grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4  items-center text-center {{ $menu->quantity > $menu->reorder_point ? '' : 'bg-red-100 shadow-red-300' }}">
                                                    <label
                                                        class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Item
                                                        :</label>

                                                    <a data-slug="{{ $menu->slug }}"
                                                        class="border-r-2 cols-span-1 pl-2 text-center  sm:text-center md:text-center lg:text-left xl:text-left">
                                                        <div>{{ $menu->name }}</div>

                                                    </a>

                                                    <label
                                                        class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Curent
                                                        Stock :</label>

                                                    <div class="cols-span-1 text-center border-r-2">
                                                        {{ number_format($menu->quantity) . ' ' . $menu->unit }}</div>

                                                    <label
                                                        class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Reorder
                                                        Point :</label>

                                                    <div class="cols-span-1 text-center border-r-2">
                                                        {{ number_format($menu->reorder_point) . ' ' . $menu->unit }}
                                                    </div>

                                                    <label
                                                        class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Request
                                                        Stock :</label>
                                                    <div class="cols-span-1 flex justify-center border-r-2">
                                                        <div class="flex items-center space-x-2 w-3/4">
                                                            <!-- Minus Button -->
                                                            <button type="button"
                                                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold p-2 rounded"
                                                                onclick="adjustValue(this, -1)">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-4 w-4" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M20 12H4" />
                                                                </svg>
                                                            </button>

                                                            <!-- Number Input -->
                                                            <input data-slug="{{ $menu->slug }}"
                                                                value="{{ $requestvalue }}" type="number"
                                                                min="1" autocomplete="off" required
                                                                class="reqStockTxt text-center w-full {{ $menu->quantity > $menu->reorder_point ? 'bg-white border border-white' : 'bg-red-100 shadow-red-300 border border-red-100' }} text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                                                            <!-- Plus Button -->
                                                            <button type="button"
                                                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold p-2 rounded"
                                                                onclick="adjustValue(this, 1)">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-4 w-4" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M12 4v16m8-8H4" />
                                                                </svg>
                                                            </button>
                                                        </div>

                                                        <button class="delete-btn text-red-600 text-xs px-1 ml-2"
                                                            data-id="{{ $menu->id }}"> ✕</button>

                                                    </div>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            {{-- LEFT CONTENT --}}

            <div class="flex justify-between items-center m-4 left-0 right-0 fixed bottom-0 pr-4 px-4 py-2 bg-white border-t border-gray-200 z-10"
                style ="{{ Auth::user()->role == 1 ? 'padding-left: 300px;' : '' }}">

                <button type="button" id="resetAllBtn"
                    class="w-1/2 text-red-600 bg-red-100 hover:bg-red-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition">
                    Reset
                </button>

                <button id="" type="button" data-modal-target="reqConfirmModal"
                    data-modal-toggle="reqConfirmModal"
                    class="disabled:opacity-50 disabled:pointer-events-none w-1/2 text-gray-50 bg-gradient-to-r from-green-600 to-teal-500 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center transition">
                    Submit
                </button>
            </div>

        </div>

    </div>







    {{-- INVENTORY LIST SMALL DEVICE --}}
    <div class="md:hidden">
        <div id="inventoryTableMobile" class="h-screen flex flex-col">
            {{-- Search and Category Tabs --}}
            <div class="bg-white rounded-lg shadow mb-2 p-2">
                <div class="relative w-full mb-2">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchMenuMobile"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-full focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                        placeholder="Search" required autocomplete="off">
                </div>

                <div class="flex items-center border-b border-neutral-300 pb-2 mb-2">


                    <div id="categoryTabsMobile" class="px-2 overflow-x-auto flex-1">
                        <div id="menuTabsMobile" class="flex gap-x-2 whitespace-nowrap">
                            <button id="all-menu-tab-mobile" data-tabs-target="#all-menu-mobile" type="button"
                                role="tab" aria-controls="all-menu-mobile" aria-selected="true"
                                class="text-sm leading-6 px-3 font-semibold border rounded-lg active-tab">
                                All
                            </button>
                            @foreach ($categories as $category)
                                <button id="{{ substr($category->name, 0, 3) . '-' . $category->id }}-tab-mobile"
                                    data-tabs-target="#{{ substr($category->name, 0, 3) . '-' . $category->id }}-menu-mobile"
                                    type="button" role="tab"
                                    aria-controls="{{ substr($category->name, 0, 3) . '-' . $category->id }}-menu-mobile"
                                    aria-selected="false"
                                    class="text-sm leading-6 px-3 font-semibold border rounded-lg">
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>


                </div>

                <div class="flex items-center p-1 space-x-2">
                    <label for="supplierMobile"
                        class="font-bold text-gray-700 text-xs whitespace-nowrap">SUPPLIER:</label>
                    <select id="supplierMobile" name="supplierMobile">
                        <option value="">-- Select --</option>
                        @foreach ($supplierlist as $supplierlistoption)
                            <option value="{{ $supplierlistoption->id }}">{{ $supplierlistoption->name }}</option>
                        @endforeach
                    </select>
                    <span id="supplierErrorMobile" class="text-red-500 text-xs mt-1 hidden">Required</span>

                    <div>
                        <button id="viewItemBtnmobile" type="button" data-modal-target="viewitem"
                            data-modal-toggle="viewitem"
                            class="disabled:opacity-50 disabled:pointer-events-none w-full text-gray-50 bg-gradient-to-r from-green-600 to-teal-500 hover:bg-gradient-to-bl font-medium rounded-md text-xs px-3 py-1.5 text-center transition">
                            View Item
                        </button>
                    </div>
                </div>

                {{-- Scrollable Inventory Items List --}}
                <div id="categoryAllContentsMobile" class="flex-1 overflow-y-auto bg-white rounded-lg shadow mb-2">
                    <div id="categoryTabContentMobile">
                        {{-- All Items Tab --}}
                        <div id="all-menu-mobile" role="tabpanel" aria-labelledby="all-menu-tab-mobile"
                            class="p-2 space-y-2">
                            @foreach ($menus as $menu)
                                @php
                                    $requestvalue = 0;
                                    foreach ($orders as $ordered) {
                                        if ($menu->id == $ordered->inv_id) {
                                            $requestvalue = $ordered->quantity;
                                        }
                                    }
                                @endphp

                                <div class="border rounded-lg p-2 {{ $menu->quantity > $menu->reorder_point ? '' : 'bg-red-100 shadow-red-300' }}"
                                    data-name="{{ strtolower($menu->name) }}">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-medium text-sm">{{ $menu->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $menu->unit }}</span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                        <div class="text-center">
                                            <div class="font-semibold">Current</div>
                                            <div>{{ number_format($menu->quantity) }}</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold">Reorder</div>
                                            <div>{{ number_format($menu->reorder_point) }}</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-semibold">Request</div>
                                            <div class="flex justify-center items-center">
                                                <input data-slug="{{ $menu->slug }}" autocomplete="off"
                                                    class="reqStockTxt text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-3/4 text-center"
                                                    value="{{ $requestvalue }}" required type="number"
                                                    min="1" name="" id="">


                                                <button class="delete-btn text-red-600 text-xs px-1"
                                                    data-id="{{ $menu->id }}">✕</button>
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Category-specific Tabs --}}
                        @foreach ($categories as $category)
                            <div id="{{ substr($category->name, 0, 3) . '-' . $category->id }}-menu-mobile"
                                role="tabpanel"
                                aria-labelledby="{{ substr($category->name, 0, 3) . '-' . $category->id }}-tab-mobile"
                                class="hidden p-2 space-y-2">
                                @foreach ($menus as $menu)
                                    @php
                                        $requestvalue = 0;
                                        foreach ($orders as $ordered) {
                                            if ($menu->id == $ordered->inv_id) {
                                                $requestvalue = $ordered->quantity;
                                            }
                                        }
                                    @endphp
                                    @if ($menu->category_id == $category->id)
                                        <div class="border rounded-lg p-2 {{ $menu->quantity > $menu->reorder_point ? '' : 'bg-red-100 shadow-red-300' }}"
                                            data-name="{{ strtolower($menu->name) }}">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="font-medium text-sm">{{ $menu->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $menu->unit }}</span>
                                            </div>

                                            <div class="grid grid-cols-3 gap-2 text-xs">
                                                <div class="text-center">
                                                    <div class="font-semibold">Current</div>
                                                    <div>{{ number_format($menu->quantity) }}</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="font-semibold">Reorder</div>
                                                    <div>{{ number_format($menu->reorder_point) }}</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="font-semibold">Request</div>
                                                    <div class="flex justify-center items-center">
                                                        <input data-slug="{{ $menu->slug }}" autocomplete="off"
                                                            class="reqStockTxt text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-3/4 text-center"
                                                            value="{{ $requestvalue }}" required type="number"
                                                            min="1" name="" id="">

                                                        <button class="delete-btn text-red-600 text-xs px-1"
                                                            data-id="{{ $menu->id }}">✕</button>

                                                        </button>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Fixed Action Buttons at Bottom --}}
                <div class="sticky bottom-0 bg-white p-2 border-t border-gray-200">
                    <div class="flex justify-between items-center gap-2">
                        <button type="button" id="resetAllBtn"
                            class="flex-1 text-red-600 bg-red-100 hover:bg-red-200 font-medium rounded-lg text-sm px-3 py-2.5 text-center transition">
                            Reset
                        </button>
                        <button id="" type="button" data-modal-target="reqConfirmModal"
                            data-modal-toggle="reqConfirmModal"
                            class="disabled:opacity-50 disabled:pointer-events-none w-1/2 text-gray-50 bg-gradient-to-r from-green-600 to-teal-500 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center transition">
                            Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>






        <script>
            $('#closeModalBtn').on('click', function() {
                $('#simpleModal').addClass('hidden');
            });
            $('#closeModalBtn2').on('click', function() {
                $('#simpleModal2').addClass('hidden');
                location.reload();
            });



            $(document).ready(function() {
                // Bind click handler for both buttons
                $('#viewItemBtn, #viewItemBtnmobile').on('click', modalHandler);
            });

            function modalHandler() {
                $.ajax({
                    url: "{{ route('procurementmodal') }}",
                    method: 'GET',

                    success: function(data) {
                        $('#ordersModalContent').empty();
                        if (data.length === 0) {
                            $('#ordersModalContent').append(
                                `<p class="text-center text-gray-400">No items in the list</p>`
                            );
                        } else {

                            let deliverySchedule = data[0].delivery_datetime || 'No schedule set';
                                $('#ordersModalContent').append(
                                    `<p class="text-center text-blue-600 mb-2 font-semibold">
                                        Delivery Schedule: ${deliverySchedule}
                                    </p>`
                                );
                            
                            data.forEach(function(item) {

                                $('#ordersModalContent').append(
                                    
                                    `<div class="flex justify-between items-center">
                                    <span class="text-base text-gray-700">${item.name}</span>
                                    <span class="text-base text-gray-500 flex items-center gap-2">
                                        ${item.quantity}
                                        <button class="delete-btn text-red-600 text-xs px-1" data-id="${item.inv_id}">✕</button>
                                    </span>
                                </div>`
                                );
                            });
                        }
                    },


                    error: function() {
                        alert('Failed to load data.');
                    }
                });
            }

            $(document).ready(function() {

                $('#menuTabsMobile button[role="tab"]').on('click', function() {
                    // Remove active class from all tabs
                    $('#menuTabsMobile button[role="tab"]').removeClass(
                        'active-tab bg-blue-100 border-blue-500 text-blue-700');

                    // Add active class to clicked tab
                    $(this).addClass('active-tab bg-blue-100 border-blue-500 text-blue-700');

                    // Hide all tab panels
                    $('#categoryTabContentMobile > div').addClass('hidden');

                    // Show the selected panel
                    const targetId = $(this).data('tabs-target');
                    $(targetId).removeClass('hidden');
                });


                // Category Tabs Scrolling for Mobile
                $('.scroll-left-mobile').on('click', function() {
                    $('#menuTabsMobile').scrollBy({
                        left: -100,
                        behavior: 'smooth'
                    });
                });

                $('.scroll-right-mobile').on('click', function() {
                    $('#menuTabsMobile').scrollBy({
                        left: 100,
                        behavior: 'smooth'
                    });
                });




            });
        </script>


        <script>
            function adjustValue(button, delta) {
                const input = button.parentElement.querySelector('input[type="number"]');
                const current = parseInt(input.value, 10) || 0;
                const min = parseInt(input.min, 10) || 1;
                const newValue = current + delta;

                if (newValue >= min) {
                    input.value = newValue;
                    $(input).trigger('change'); // This triggers your forecastHandler
                }
            }
            $(document).ready(function() {

                setupTabScrolling();


                $('#searchMenu, #searchMenuMobile').keyup(searchHandler);
                jQuery(document).on("change", ".reqStockTxt, .procurementreqStockTxt", forecastHandler);
                $('#submitButton, #submitBtnMobile').click(submitHandler);
                $(document).on('click', '.delete-btn, .delete-btn-mobile', deleteHandler);
                $('#resetAllBtn, #resetAllBtnMobile').click(resetHandler);
            });


            function setupTabScrolling() {
                const isMobile = window.innerWidth < 768;

                $('.scroll-left, .scroll-left-mobile').click(function() {
                    const container = isMobile ? $('#menuTabsMobile') : $('#menuTabs');
                    container.animate({
                        scrollLeft: `-=${isMobile ? 100 : container.width() * 0.66}px`
                    }, 'slow');
                });

                $('.scroll-right, .scroll-right-mobile').click(function() {
                    const container = isMobile ? $('#menuTabsMobile') : $('#menuTabs');
                    container.animate({
                        scrollLeft: `+=${isMobile ? 100 : container.width() * 0.66}px`
                    }, 'slow');
                });
            }

            function searchHandler() {
                const isMobile = $(this).is('#searchMenuMobile');
                const searchValue = $(this).val().toLowerCase();

                if (isMobile) {
                    $('#all-menu-tab-mobile').click();

                    $('#all-menu-mobile [data-name]').each(function() {
                        const itemName = $(this).data('name') || '';
                        const visibleText = $(this).text().toLowerCase();
                        const matches = itemName.toLowerCase().includes(searchValue) || visibleText.includes(
                            searchValue);
                        $(this).toggle(matches);
                    });

                } else {
                    $('#all-menu-tab').click();
                    $('#all-menu span').each(function() {
                        const visibleText = $(this).text().toLowerCase();
                        const matches = visibleText.includes(searchValue);
                        $(this).toggle(matches);
                    });
                }
            }

            function forecastHandler() {
                const slug = $(this).data('slug');
                const id = $(this).data('id');
                const qty = parseFloat($(this).val()) || 0;
                const dateTime = $('#dateTimeInput').val();
                $('#loadingScreen').removeClass('hidden');

                if (qty === 0) {
                    $.ajax({
                        url: "{{ route('procurementrequest.delete') }}",
                        type: "POST",
                        data: {
                            id,
                            _token: $('input[name="_token"]').val()
                        },
                        success: () => location.reload(),
                        error: () => alert('Error')
                    });
                } else {
                    $.ajax({
                        url: "{{ route('procurementrequest.forecast') }}",
                        method: "POST",
                        data: {
                            slug,
                            qty,
                            delivery_datetime: dateTime,
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(result) {
                            $('#loadingScreen').addClass('hidden');
                        },
                        error: () => {
                            $('#loadingScreen').addClass('hidden');
                            alert('Error updating forecast');
                        }
                    });
                }
            }

            function submitHandler() {
                const isMobile = $(this).is('#submitBtnMobile');
                const supplierInput = isMobile ? $('#supplierMobile') : $('#supplier');
                const errorElement = isMobile ? $('#supplierErrorMobile') : $('#supplierError');

                // if (!supplierInput.val().trim()) {
                //     errorElement.removeClass('hidden');
                //     supplierInput.addClass('border-red-500');
                //     return;
                // }

                $.ajax({
                    url: "{{ route('procurementmodal') }}",
                    method: 'GET',

                    success: function(data) {
                        if (data.length === 0) {
                            $('#simpleModal').removeClass('hidden');
                            $('#modalMessage').text('No Request Found');

                            return;
                        }
                        $.ajax({
                            url: "{{ route('procurementrequest.submit') }}",
                            method: "POST",
                            data: {
                                // supplier: supplierInput.val(),
                                _token: $('input[name="_token"]').val()
                            },
                            success: () => {
                                $('#modalMessage2').text('Request Submitted');
                                $('#simpleModal2').removeClass('hidden');
                                return;
                            }
                        });
                    }
                });
            }



            function deleteHandler() {
                const id = $(this).data('id');
                if (!id || !confirm("Remove this item?")) return;


                $.ajax({
                    url: "{{ route('procurementrequest.delete') }}",
                    type: "POST",
                    data: {
                        id,
                        _token: $('input[name="_token"]').val()
                    },
                    success: () => location.reload(),
                    error: () => alert('Error')
                });
            }

            function resetHandler() {
                if (!confirm("Reset all quantities to 0?")) return;

                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url: "{{ route('procurementrequest.resetAll') }}",
                    type: "POST",
                    data: {
                        _token: $('input[name="_token"]').val()
                    },
                    success: () => location.reload(),
                    error: () => alert('Error')
                });
            }

           
        </script>
</x-app-layout>
