<x-app-layout>
    {{-- ken 250623001 updated: CSV Import Performance Optimization - Batch inserts/updates with pre-loaded menu lookups for 10-15x faster imports on 5K+ items - 02-27-2026 --}}
    @section('page_title', 'MENU SETUP')
    <style>
        /* Ensure dropdown stays visible on hover */
        .group:hover .group-hover\:block {
            display: block !important;
        }

        /* Improve dropdown styling */
        .group .group-hover\:block {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
        }

        .group:hover .group-hover\:block {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        /* ============================================================
           SEARCHABLE DROPDOWN (Combobox) STYLES
           ============================================================ */
        .searchable-dropdown {
            position: relative;
            width: 100%;
        }

        .searchable-dropdown .dropdown-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background-color: #f9fafb;
            outline: none;
            transition: border-color 0.2s;
        }

        .searchable-dropdown .dropdown-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .searchable-dropdown .dropdown-input::placeholder {
            color: #9ca3af;
        }

        .searchable-dropdown .dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 50;
            display: none;
            margin-top: 2px;
        }

        .searchable-dropdown .dropdown-list.open {
            display: block;
        }

        .searchable-dropdown .dropdown-list .dropdown-item {
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
            color: #1f2937;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s;
        }

        .searchable-dropdown .dropdown-list .dropdown-item:last-child {
            border-bottom: none;
        }

        .searchable-dropdown .dropdown-list .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .searchable-dropdown .dropdown-list .dropdown-item.active {
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        .searchable-dropdown .dropdown-list .dropdown-item.disabled {
            color: #9ca3af;
            cursor: not-allowed;
            background-color: #f9fafb;
        }

        .searchable-dropdown .dropdown-list .dropdown-item.disabled:hover {
            background-color: #f9fafb;
        }

        .searchable-dropdown .dropdown-list .dropdown-item .highlight {
            background-color: #fef08a;
            padding: 0 2px;
            border-radius: 2px;
        }

        .searchable-dropdown .dropdown-list .no-results {
            padding: 12px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        /* Dropdown arrow indicator */
        .searchable-dropdown .dropdown-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            transition: transform 0.2s;
        }

        .searchable-dropdown .dropdown-arrow.open {
            transform: translateY(-50%) rotate(180deg);
        }

        .searchable-dropdown .dropdown-clear {
            position: absolute;
            right: 32px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            display: none;
            font-size: 16px;
        }

        .searchable-dropdown .dropdown-clear:hover {
            color: #374151;
        }

        .searchable-dropdown .dropdown-clear.visible {
            display: block;
        }

        /* Focus ring for the input */
        .searchable-dropdown.focused .dropdown-input {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Highlight matching text */
        .search-highlight {
            background-color: #fef08a;
            padding: 0 2px;
            border-radius: 2px;
        }
    </style>
    {{-- DELETE MODAL --}}
        <div id="itemDeleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <div class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <i class="mr-2 text-xl text-red-700 uil uil-exclamation-triangle md:text-2xl lg:text-3xl"></i>
                            <span class="text-base text-red-700 md:text-lg lg:text-xl">Delete Item</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemDeleteModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <div class="px-6 py-3 space-y-6 md:py-6">
                        <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                            Select the brand you want to delete.
                        </p>
                        <div>
                            <label for="deleteBrandSelect" class="block mb-2 text-sm font-medium text-gray-900">Brand</label>
                            <!-- SEARCHABLE DROPDOWN -->
                            <div class="searchable-dropdown" id="deleteDropdown">
                                <input type="text" 
                                       id="deleteBrandInput" 
                                       class="dropdown-input" 
                                       placeholder="Search or select brand..."
                                       autocomplete="off"
                                       data-dropdown-target="deleteDropdown">
                                <span class="dropdown-clear" id="deleteClearBtn">&times;</span>
                                <span class="dropdown-arrow" id="deleteArrow">▾</span>
                                <div class="dropdown-list" id="deleteDropdownList">
                                    <!-- Options will be populated dynamically -->
                                </div>
                            </div>
                            <input type="hidden" id="deleteBrandSelect" value="">
                        </div>
                    </div>
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <a type="button" href="" class="deleteConfirm w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>
                        <button data-modal-hide="itemDeleteModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- DELETE MODAL END --}}

    {{-- TRANSFORM SOURCE MODAL --}}
        <div id="transformSourceModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <div class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <span class="text-base text-emerald-600 md:text-lg lg:text-xl">Select Transform Source Brand</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="transformSourceModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                    <div class="px-6 py-3 space-y-4 md:py-6">
                        <p id="transformSourceItemName" class="text-sm font-semibold text-gray-900"></p>
                        <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                            Choose the brand to use as source for transform. Only brands with quantity above 0 can be selected.
                        </p>
                        <div>
                            <label for="transformBrandSelect" class="block mb-2 text-sm font-medium text-gray-900">Brand (with quantity)</label>
                            <!-- SEARCHABLE DROPDOWN -->
                            <div class="searchable-dropdown" id="transformDropdown">
                                <input type="text" 
                                       id="transformBrandInput" 
                                       class="dropdown-input" 
                                       placeholder="Search or select brand..."
                                       autocomplete="off"
                                       data-dropdown-target="transformDropdown">
                                <span class="dropdown-clear" id="transformClearBtn">&times;</span>
                                <span class="dropdown-arrow" id="transformArrow">▾</span>
                                <div class="dropdown-list" id="transformDropdownList">
                                    <!-- Options will be populated dynamically -->
                                </div>
                            </div>
                            <input type="hidden" id="transformBrandSelect" value="">
                        </div>
                    </div>
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <a id="transformProceedLink" href="{{ route('transform.create') }}" class="w-36 text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center pointer-events-none opacity-50">Proceed</a>
                        <button data-modal-hide="transformSourceModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- TRANSFORM SOURCE MODAL END --}}
    
    {{-- MOVE MODAL --}}
        <div id="moveMenuModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <form method="POST" action="{{ route('menu.move') }}" class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    @csrf
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <span class="text-base text-emerald-600 md:text-lg lg:text-xl">Move Menu to Inventory</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="moveMenuModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <div class="px-6 py-3 space-y-6 md:py-6">
                        <input type="hidden" id="moveSlug" name="moveSlug">
                        <div class="mb-6">
                            <label for="moveServings" class="block mb-2 text-sm font-medium text-gray-900">Servings</label>
                            <input type="number" id="moveServings" name="moveServings" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 appearance-none" autocomplete="off" min="1" max="" required>
                        </div>
                    </div>
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="moveConfirm w-24 text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Submit</button>
                        <button data-modal-hide="moveMenuModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- MOVE MODAL END --}}

    {{-- DISPOSE MODAL --}}
        <div id="disposeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <form method="POST" action="{{ route('menu.dispose') }}" class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    @csrf
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <span class="text-base text-blue-500 md:text-lg lg:text-xl">Waste</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="disposeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <div class="px-6 py-6">
                        <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                            <h1 id="disposeName" class="pb-5 text-xl font-bold"></h1>
                            <input type="hidden" id="disposeSlug" name="disposeSlug">
                            <div class="mb-6">
                                <label for="disposeBrandSelect" class="block mb-2 text-sm font-medium text-gray-900">Brand</label>
                                <!-- SEARCHABLE DROPDOWN -->
                                <div class="searchable-dropdown" id="disposeDropdown">
                                    <input type="text" 
                                           id="disposeBrandInput" 
                                           class="dropdown-input" 
                                           placeholder="Search or select brand..."
                                           autocomplete="off"
                                           data-dropdown-target="disposeDropdown">
                                    <span class="dropdown-clear" id="disposeClearBtn">&times;</span>
                                    <span class="dropdown-arrow" id="disposeArrow">▾</span>
                                    <div class="dropdown-list" id="disposeDropdownList">
                                        <!-- Options will be populated dynamically -->
                                    </div>
                                </div>
                                <input type="hidden" id="disposeBrandSelect" value="">
                            </div>
                            <div class="mb-6">
                                <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900">Quantity</label>
                                <div class="flex items-center">
                                    <input type="text" id="quantity" name="quantity" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                                    <span id="disposeUnit" class="px-3 text-base font-bold text-gray-600"></span>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="waste_remarks" class="block mb-2 text-sm font-medium text-gray-900">Remarks</label>
                                <div class="flex items-center">
                                    <input type="text" id="waste_remarks" name="waste_remarks" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                                    <span id="disposeUnit" class="px-3 text-base font-bold text-gray-600"></span>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                                <div class="relative max-w-sm">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                      <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <input datepicker type="text" name="disposeDate" value="{{ date('m/d/Y') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                                  </div>
                            </div>
                        </p>
                    </div>
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="submitQtyButton w-24 text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Submit</button>
                        <button data-modal-hide="disposeModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- DISPOSE MODAL END --}}


    <!-- ken 250623001 - ken update start, update quantity adding per brand. -->
    {{-- QUANTITY CHANGING SIDEBAR --}}
        <button data-drawer-target="addServingModal" data-drawer-toggle="addServingModal" aria-controls="addServingModal" type="button" id="addServingButton" class="hidden">
        </button>
            
        <aside id="addServingModal" data-drawer-backdrop="static" tabindex="-1" class="fixed top-0 z-40 w-full left-0 h-screen transition-transform translate-x-full lg:left-1/4 lg:w-3/4" aria-label="Sidebar"><!-- leo changes 4/22/25 -->
            <form method="POST" action="{{ route('menu.changeqty') }}" class="relative h-full px-4 py-4 overflow-y-auto bg-gray-50">
                @csrf
                <input type="hidden" id="counter" name="counter" value="1">
                <h1 id="menuName" class="text-3xl font-bold tracking-wider">ITEM NAME</h1>
                <div class="grid w-full grid-cols-2 mt-5 mb-1">
                    <input type="hidden" id="slug" name="slug">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand stock (per brand)</label>
                        <div id="brandStocks" class="space-y-2"></div>
                        <div id="brandStocksPagination" class="flex items-center justify-between mt-2 gap-2 text-sm">
                            <button id="brandStockPrevBtn" type="button" class="px-4 py-2 font-semibold bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>Prev</button>
                            <span id="brandStockPageInfo" class="text-gray-700 font-medium">Page 0 of 0</span>
                            <button id="brandStockNextBtn" type="button" class="px-4 py-2 font-semibold bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>Next</button>
                        </div>
                    </div>
                </div>
                <div class="">
                    <h2 class="text-2xl font-bold tracking-wide">PACKAGE BUNDLE</h2>
                    <div id="ingredients" class="">
                    </div>

                    <div class="mt-5">

                        <div class="flex items-center mb-2 mt-7">
                            <h2 class="mr-5 text-xl italic font-bold tracking-wide">INCLUSIONS</h2>
                            <button id="addButton" type="button" class="text-3xl text-blue-500"><i class="uil uil-plus-circle"></i></button>
                        </div>
    
                        <div class="flex flex-row h-6 mb-2 gap-x-3">
                            <div class="w-2/5 text-center">
                                <label class="block font-medium text-gray-900">Name</label>
                            </div>
                            <div class="w-2/5 text-center">
                                <label class="block font-medium text-gray-900">Quantity</label>
                            </div>
                            <div class="flex w-1/5">
                                <div class="w-1/2"></div>
                                <div class="w-1/2 text-center">Remove</div>
                            </div>
                        </div>

                        <div id="addDiv">

                        </div>

                    </div>
                </div>
                <div class="float-right mt-5">
                <!-- leo start 4/22/25 -->
                    <button type="submit" data-drawer-hide="addServingModal" class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 focus:outline-none">Proceed</button>
                <!-- leo end 4/22/25 -->
                    <button type="button" data-drawer-hide="addServingModal" class="text-white bg-gray-500 hover:bg-gray-600 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none">Cancel</button>
                </div>
            </form>
        </aside>
    {{-- QUANTITY CHANGING SIDEBAR END --}}
    <!-- ken 250623001 - ken update end, update quantity adding per brand. -->
     
    <!-- 250623001 - ken - 2/2/26 - Import CSV Modal start here -->
    <div id="importModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <!-- Modal content -->
            <form method="POST" action="{{ route('menu.import') }}" enctype="multipart/form-data" class="w-full max-w-2xl bg-white rounded-lg shadow-lg
                max-h-[90vh] flex flex-col">
                @csrf
                <!-- Modal header -->
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="flex items-center font-semibold text-gray-900">
                        <i class="mr-2 text-xl text-purple-600 uil uil-import"></i>
                        <span class="text-base text-purple-500 md:text-lg lg:text-xl">Import Menu from CSV</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="importModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <!-- Modal body -->
                <div class="px-6 py-6 overflow-y-auto flex-1">
                    <div class="mb-6">
                        <!-- Download Template Button -->
                        <div class="p-4 mb-4 text-center bg-purple-50 rounded-lg">
                            <h4 class="mb-2 text-lg font-semibold text-purple-800">Step 1: Download Template</h4>
                            <p class="mb-4 text-sm text-purple-600">Download the CSV template to ensure proper formatting</p>
                            <div class="mb-4">
                                <label for="branch_id_template" class="block mb-2 text-sm font-medium text-gray-900">Branch *</label>
                                <select id="branch_id_template" name="branch_id_template" 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" 
                                    >
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <a id="downloadTemplate" href="{{ route('menu.template') }}" class="inline-flex items-center px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300">
                                <i class="mr-2 uil uil-file-download-alt"></i>
                                Download CSV Template
                            </a>
                        </div>

                        <!-- File Upload -->
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h4 class="mb-2 text-lg font-semibold text-gray-800">Step 2: Upload Your CSV</h4>
                            
                            {{-- BRANCH SELECTION FOR CSV IMPORT --}}
                            <div class="mb-4">
                                <label for="branch_id" class="block mb-2 text-sm font-medium text-gray-900">Import To *</label>
                                <select id="branch_id" name="branch_id" 
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" 
                                    required>
                                    <option value="">-- Select Import Destination --</option>
                                    <option value="all">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Select "All Branches" to import the same data to all branches</p>
                            </div>
                            
                            <div class="mb-4">
                                <label for="csv_file" class="block mb-2 text-sm font-medium text-gray-900">CSV File *</label>
                                <div class="flex items-center">
                                    <input type="file" id="csv_file" name="csv_file" accept=".csv" 
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" 
                                        required>
                                </div>
                            </div>

                            <!-- Options -->
                            <div class="mb-4">
                                <label class="flex items-center p-3 bg-white border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="update_existing" value="1" 
                                        class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Update existing items</span>
                                        <p class="text-xs text-gray-500">Items with matching name in selected branch(es) will be updated instead of creating duplicates</p>
                                    </div>
                                </label>
                            </div>

                            <!-- CSV Format Info -->
                            <div class="p-3 text-sm text-gray-700 bg-white border rounded-lg">
                                <h5 class="mb-2 font-semibold">CSV Format Requirements:</h5>
                                <ul class="pl-5 space-y-1 list-disc">
                                    <li>Required columns: <code>name</code></li>
                                    <li>Optional columns: <code>sku, current_quantity, quantity, price, grab_price, panda_price, food_cost, servings, is_combo, unit, reorder_point, bar_code, is_uuid</code></li>
                                    <li>Select "All Branches" to import to all branches at once</li>
                                    <li>Use comma (,) as delimiter</li>
                                    <li>Maximum file size: 10MB</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="submit" 
                            class="w-full text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center flex items-center justify-center">
                        <i class="mr-2 uil uil-upload"></i>
                        Import CSV File
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- 250623001 - ken - 2/2/26 - Import CSV Modal end here -->
    
    <!-- CSV Import Confirmation Modal -->
    <div id="importConfirmationModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <!-- Modal content -->
            <div class="w-full max-w-2xl bg-white rounded-lg shadow-lg max-h-[90vh] flex flex-col">
                <!-- Modal header -->
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="flex items-center font-semibold text-gray-900">
                        <i class="mr-2 text-xl text-yellow-500 uil uil-exclamation-triangle"></i>
                        <span class="text-base text-yellow-600 md:text-lg lg:text-xl">Ready to Import?</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="importConfirmationModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <!-- Modal body -->
                <div class="px-6 py-6 overflow-y-auto flex-1">
                    <div class="mb-6">
                        <div class="p-4 mb-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="mb-2 text-lg font-semibold text-yellow-800">Import Preview</h4>
                            <p class="text-sm text-yellow-700">We found a few things to let you know about. Take a look below and let us know if you'd like to proceed.</p>
                        </div>

                        <!-- Warnings Section -->
                        <div id="warningsSection" class="mb-4 hidden">
                            <h5 class="mb-2 font-semibold text-orange-700 flex items-center">
                                <i class="mr-2 uil uil-info-circle"></i>
                                What we'll update:
                            </h5>
                            <div id="warningsList" class="max-h-40 overflow-y-auto p-3 bg-orange-50 border border-orange-200 rounded-lg text-sm text-orange-800"></div>
                        </div>

                        <!-- Errors Section -->
                        <div id="errorsSection" class="mb-4 hidden">
                            <h5 class="mb-2 font-semibold text-red-700 flex items-center">
                                <i class="mr-2 uil uil-times-circle"></i>
                                Items we can't import:
                            </h5>
                            <div id="errorsList" class="max-h-40 overflow-y-auto p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800"></div>
                        </div>

                        <!-- Success Preview -->
                        <div id="successPreview" class="p-3 bg-green-50 border border-green-200 rounded-lg hidden">
                            <h5 class="font-semibold text-green-700 mb-2">All set!</h5>
                            <p id="successMessage" class="text-sm text-green-800"></p>
                        </div>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="button" id="proceedImportBtn" 
                            class="flex-1 text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center flex items-center justify-center">
                        <i class="mr-2 uil uil-check"></i>
                        Let's do it!
                    </button>
                    <button data-modal-hide="importConfirmationModal" type="button" 
                            class="flex-1 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- CSV Import Confirmation Modal End -->

    <!-- CSV Import Loading Modal -->
    <div id="importLoadingModal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <!-- Loading Spinner -->
                <div class="mb-6 flex justify-center">
                    <div class="relative w-16 h-16">
                        <div class="absolute inset-0 rounded-full border-4 border-purple-100"></div>
                        <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-purple-600 border-r-purple-600 animate-spin"></div>
                    </div>
                </div>

                <!-- Loading Text -->
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Importing Items</h3>
                <p class="text-gray-600 mb-4">Please wait while we process your CSV file...</p>

                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full animate-pulse" style="width: 100%"></div>
                </div>

                <!-- Status Message -->
                <p id="importStatusMessage" class="text-sm text-gray-500 mt-4">Processing rows...</p>
            </div>
        </div>
    </div>
    <!-- CSV Import Loading Modal End -->
     

    <div class="">
        <div style="height: calc(100vh - 48px);" class="flex {{ auth()->user()->role == 1 ? 'lg:ml-64' : '' }}">
            <div id="contentDiv" class="w-full p-2">
                <div class="w-full p-3 overflow-hidden bg-white rounded-lg shadow-md max-w-none">
                {{-- CONTROLS - Single Horizontal Line --}}
                <div class="mb-2 w-full">
                    <div class="flex items-center gap-1.5 w-full whitespace-nowrap">
                        @if (auth()->user()->store_code == 'store-012026003-000005')
                            
                            @if (!$hasBrandCodes)
                                {{-- GBC Button - Active --}}
                                <form action="{{ route('menus.generateBrandCode') }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <button type="submit"
                                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-2 focus:ring-blue-300
                                            font-semibold rounded-lg text-xs px-3 py-1.5 whitespace-nowrap"
                                        onclick="return confirm('Generate brand codes?')">
                                        GBC
                                    </button>
                                </form>
                            @else
                                {{-- GBC Button - Disabled (hidden) --}}
                                {{-- Commented out to hide completely --}}
                                {{-- 
                                <button type="button"
                                    class="text-white bg-green-600 cursor-default
                                        font-semibold rounded-lg text-xs px-3 py-1.5 whitespace-nowrap"
                                    disabled>
                                    ✓ GBC Complete
                                </button>
                                --}}
                                
                            @endif
                        @endif

                        {{-- Refresh Button --}}
                        {{-- Main Filter/Search Form --}}
                        <form method="GET"
                            action="{{ route('menu.index') }}"
                            id="filterForm"
                            class="flex items-center gap-1.5 flex-1 min-w-0">
                        @if (auth()->user()->store_code == 'store-012026003-000005')
                                {{-- ADD Button --}}
                                <a href="{{ route('menu.add') }}"
                                    class="flex-shrink-0 text-white bg-blue-700 hover:bg-blue-800
                                        focus:ring-2 focus:ring-blue-300 font-semibold rounded-lg
                                        text-xs px-3 py-1.5 whitespace-nowrap">
                                    ADD
                                </a>

                                {{-- Export Button --}}
                                <a id="exportBtn"
                                    href="{{ route('menu.export', ['branch' => request('branchFilter')]) }}"
                                    class="flex-shrink-0 text-white bg-green-600 hover:bg-green-700
                                        focus:ring-2 focus:ring-green-300 font-semibold rounded-lg
                                        text-xs px-3 py-1.5 whitespace-nowrap">
                                    Export
                                </a>

                                {{-- Import Button --}}
                                <button type="button"
                                    data-modal-target="importModal"
                                    data-modal-toggle="importModal"
                                    class="flex-shrink-0 text-white bg-red-600 hover:bg-red-700
                                        focus:ring-2 focus:ring-red-300 font-semibold rounded-lg
                                        text-xs px-3 py-1.5 whitespace-nowrap">
                                    Import
                                </button>
                            @endif
                            {{-- Category Filter --}}
                            <select name="searchSelect"
                                id="searchSelect"
                                class="flex-shrink-0 px-2 py-1.5 text-xs border border-gray-300
                                    rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                <option value="All">All Categories</option>

                                @foreach ($categories as $categorys)
                                    <option value="{{ $categorys->id }}"
                                        {{ $catVal == $categorys->id ? 'selected' : '' }}>
                                        {{ $categorys->name }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Stock Filter --}}
                            <select name="stockFilter"
                                id="stockFilter"
                                class="flex-shrink-0 px-2 py-1.5 text-xs border border-gray-300
                                    rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All Stock</option>
                                <option value="low"
                                    {{ (request('stockFilter') ?? 'all') == 'low' ? 'selected' : '' }}>
                                    Low Stock
                                </option>
                                <option value="out"
                                    {{ (request('stockFilter') ?? 'all') == 'out' ? 'selected' : '' }}>
                                    Out of Stock
                                </option>
                            </select>

                            {{-- Search Field --}}
                        <div class="relative flex-1 min-w-0">
                            {{-- Search Icon --}}
                            <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                <svg class="w-3.5 h-3.5 text-gray-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <input type="search"
                                id="searchInput"
                                name="search"
                                class="block w-full px-2 py-1.5 pl-7 pr-16 text-xs
                                    border border-gray-300 rounded-lg bg-gray-50
                                    focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Search item, barcode, SKU"
                                value="{{ $search }}"
                                autocomplete="off">

                            {{-- Search Controls --}}
                            <div class="absolute inset-y-0 right-0 flex items-center pr-1">
                                {{-- Clear - hidden by default, shows when input has value --}}
                                <button id="clearButton"
                                    type="button"
                                    class="p-1 text-gray-500 hover:text-gray-700 {{ $search ? '' : 'hidden' }}">
                                   {{--  <i class="uil uil-times text-xs"></i> --}}
                                </button>

                                {{-- Search --}}
                                <button type="submit"
                                    class="ml-0.5 text-white bg-blue-700 hover:bg-blue-800
                                        focus:ring-2 focus:ring-blue-300 rounded-lg
                                        text-xs px-2 py-1">
                                    Search
                                </button>
                            </div>
                        </div>

                        </form>

                    </div>
                </div>
                {{-- CONTROLS END --}}

     
                    <div>
                        {{-- TABLE --}}
                    <div class="w-full overflow-x-auto">
                        <div id="menuTable" class="w-full shadow-md sm:rounded-lg">
                            <table class="w-full table-fixed text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-200 sticky top-0 z-10">
                                    <tr>
                                        <th scope="col" class="w-[44%] px-4 py-3 whitespace-nowrap">
                                            ITEM Name
                                        </th>
                                        <th scope="col" class="w-[18%] px-3 py-3 text-center whitespace-nowrap">
                                            Category
                                        </th>
                                        <th scope="col" class="w-[18%] px-3 py-3 text-center whitespace-nowrap">
                                            Department
                                        </th>
                                        <th scope="col" class="w-[20%] px-3 py-3 text-center whitespace-nowrap">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groupedMenus as $itemName => $menuItems)
                                        @php
                                            // Get first menu item for category and department info
                                            $firstItem = $menuItems[0];
                                            
                                            // Organize menu items by branch for easy access
                                            $itemsByBranch = [];
                                            $itemsByBrand = [];
                                            $stockTotal = 0;
                                            $lowestReorderPoint = null;
                                            foreach ($menuItems as $item) {
                                                $itemsByBranch[$item->branch_id] = $item;
                                                $itemsByBrand[$item->brand_id] = $item;
                                                $stockTotal += (float) ($item->quantity ?? 0);
                                                $currentReorderPoint = (float) ($item->reorder_point ?? 0);
                                                if ($lowestReorderPoint === null || $currentReorderPoint < $lowestReorderPoint) {
                                                    $lowestReorderPoint = $currentReorderPoint;
                                                }
                                            }
                                            $brandCount = count($itemsByBrand);
                                        @endphp
                                        
                                        <tr data-is_combo="{{ $firstItem->is_combo }}" data-item-row="{{ $firstItem->slug }}" data-stock-total="{{ $stockTotal }}" data-reorder-point="{{ $lowestReorderPoint ?? 0 }}" aria-expanded="false" title="Click to view brand details" class="group bg-white border-b hover:bg-slate-50 cursor-pointer item-row-toggle align-middle">
                                            {{-- Item Name --}}
                                            <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap align-middle">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="min-w-0">
                                                        <span class="block truncate text-sm font-semibold tracking-wide text-gray-900 sm:text-base">{{ mb_strtoupper($itemName) }}</span>
                                                        <span class="mt-1 block text-xs text-gray-500">{{ $brandCount }} brand{{ $brandCount === 1 ? '' : 's' }} available</span>
                                                    </div>
                                                    <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                                        <span>View Brands</span>
                                                        <span class="text-gray-400 transition-transform duration-200 item-row-chevron">▾</span>
                                                    </span>
                                                </div>
                                                <span class="hidden" data-slug="{{ $firstItem->slug }}"></span>
                                            </td>
                                            
                                            {{-- ken update start here ken250623001 remove dropdown of  Category --}}
                                            {{-- Category --}}
                                            <td class="px-3 py-1.5 text-center whitespace-nowrap align-middle">
                                                @php
                                                    foreach($categories as $category) {
                                                        if (($firstItem->category_id ?? null) == $category->id) {
                                                            echo $category->name;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            {{-- ken update end here ken250623001 --}}
                                            
                                            {{-- Department --}}
                                            <td class="px-4 py-1.5 text-center whitespace-nowrap align-middle">
                                                @php
                                                    foreach($depts as $dept){
                                                        if ($firstItem->department == $dept->id){
                                                            echo $dept->name;
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            
                                            {{-- Actions --}}
                                                            <td class="px-4 py-1.5 text-center whitespace-nowrap align-middle">
                                                                {{-- @if ($menu->is_combo != 1)
                                                                    <a type="button" data-modal-target="moveMenuModal" data-modal-toggle="moveMenuModal" data-slug="{{ $menu->slug }}" data-quantity="{{ $menu->quantity }}" class="text-sm font-semibold text-teal-600 moveButton hover:underline">Move</a> | 
                                                                @endif --}}
                                                                                <div class="flex items-center justify-center gap-1.5 whitespace-nowrap text-xs font-semibold">
                                                                                    <button type="button" data-modal-target="transformSourceModal" data-modal-toggle="transformSourceModal" data-item-slug="{{ $firstItem->slug }}" data-item-name="{{ $firstItem->name }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-2 py-1 text-white shadow-sm transition hover:bg-emerald-700 transformButton">
                                                                        Transform
                                                                    </button>
                                                                                    <a href="{{ url('/menu/edit/'.$firstItem->slug) }}" class="text-blue-600 editButton hover:underline">Edit</a>
                                                                
                                                                    @if(Auth::user()->role == 1 || Auth::user()->role == 3)
                                                                        <span class="text-gray-300">|</span>

                                                                        @if ($firstItem->is_combo != 1)
                                                                            <a type="button" data-modal-target="disposeModal" data-modal-toggle="disposeModal" data-slug="{{ $firstItem->slug }}"data-name="{{ $firstItem->name }}"  class="text-orange-500 cursor-pointer disposeButton hover:underline">Waste</a>
                                                                            <span class="text-gray-300">|</span>
                                                                        @endif
                                                                        
                                                                        <a type="button" data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" data-slug="{{ $firstItem->slug }}" class="text-red-600 cursor-pointer deleteButton hover:underline">Delete</a>
                                                                    @endif
                                                                </div>
                                                            </td>
                                        </tr>

                                        <tr class="hidden bg-gray-50 border-b item-detail-row" data-item-detail="{{ $firstItem->slug }}">
                                            <td colspan="4" class="px-4 py-2">
                                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                                                    <div class="flex flex-col gap-2 border-b border-gray-200 bg-gray-50 p-2.5 sm:flex-row sm:items-center sm:justify-between">
                                                        <div>
                                                            <p class="text-sm font-semibold text-gray-800">Brand details</p>
                                                            <p class="text-xs text-gray-500">Search by brand code, brand name, quantity, or price.</p>
                                                        </div>
                                                        <div class="w-full sm:max-w-md">
                                                            <input type="search" class="brand-search w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none" data-brand-search="{{ $firstItem->slug }}" placeholder="Search brand details..." autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="max-h-72 overflow-auto">
                                                        <table class="min-w-full divide-y divide-gray-200 text-left text-xs sm:text-sm">
                                                            <thead class="bg-gray-50 text-gray-600 sticky top-0 z-10">
                                                                <tr>
                                                                    <th class="px-3 py-2 font-semibold">Brand Code</th>
                                                                    <th class="px-3 py-2 font-semibold">Brand</th>
                                                                    <th class="px-3 py-2 font-semibold text-right">Qty</th>
                                                                    <th class="px-3 py-2 font-semibold text-right">Reorder</th>
                                                                    <th class="px-3 py-2 font-semibold text-right">Retail</th>
                                                                    <th class="px-3 py-2 font-semibold text-right">Wholesale</th>
                                                                    <th class="px-3 py-2 font-semibold text-right">Slab</th>
                                                                    <th class="px-3 py-2 font-semibold text-right">Acq. Cost</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-100 bg-white text-gray-700" data-brand-tbody="{{ $firstItem->slug }}">
                                                                @forelse($brands as $brand)
                                                                    @php
                                                                        $brandItem = $itemsByBrand[$brand->id] ?? null;
                                                                    @endphp
                                                                    @if($brandItem)
                                                                        <tr class="hover:bg-gray-50 brand-data-row" data-brand-row="{{ $firstItem->slug }}" data-brand-slug="{{ $brandItem->slug }}" data-brand-menu-id="{{ $brandItem->id }}" data-brand-name="{{ $brand->name }}" data-brand-code="{{ $brandItem->brand_code ?? '' }}" data-brand-quantity="{{ $brandItem->quantity }}" data-brand-unit="{{ $brandItem->unit ?? '' }}" data-brand-text="{{ strtolower(($brandItem->brand_code ?? 'n/a') . ' ' . $brand->name . ' ' . number_format($brandItem->quantity, 3, '.', ',') . ' ' . number_format($brandItem->reorder_point ?? 0, 0, '.', ',') . ' ' . number_format($brandItem->price, 2, '.', ',') . ' ' . number_format($brandItem->wholesale_price ?? 0, 2, '.', ',') . ' ' . number_format($brandItem->slab_price ?? 0, 2, '.', ',') . ' ' . number_format($brandItem->food_cost, 2, '.', ',')) }}">
                                                                            <td class="px-3 py-2 font-medium text-gray-900">{{ $brandItem->brand_code ?? 'N/A' }}</td>
                                                                            <td class="px-3 py-2">{{ $brand->name }}</td>
                                                                            <td class="px-3 py-2 text-right">{{ number_format($brandItem->quantity, 3, '.', ',') }}</td>
                                                                            <td class="px-3 py-2 text-right">{{ number_format($brandItem->reorder_point ?? 0, 0, '.', ',') }}</td>
                                                                            <td class="px-3 py-2 text-right">₱{{ number_format($brandItem->price, 2, '.', ',') }}</td>
                                                                            <td class="px-3 py-2 text-right">₱{{ number_format($brandItem->wholesale_price ?? 0, 2, '.', ',') }}</td>
                                                                            <td class="px-3 py-2 text-right">₱{{ number_format($brandItem->slab_price ?? 0, 2, '.', ',') }}</td>
                                                                            <td class="px-3 py-2 text-right">₱{{ number_format($brandItem->food_cost, 2, '.', ',') }}</td>
                                                                        </tr>
                                                                    @endif
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="8" class="px-3 py-4 text-center text-gray-500">No brand data available</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                                No items found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- TABLE END --}}
    
                        

                        {{-- INVENTORY LIST SMALL DEVICE --}}
                            {{-- <div id="menuMobile" class="overflow-auto md:hidden">
                                <div id="accordion-collapse" data-accordion="collapse">
                                    @php
                                        $x = 1;
                                        foreach ($menus as $menu) {
                                            if($x == 1){
                                                echo '
                                                    <h2 id="accordion-collapse-heading-'.$x.'">
                                                        <button type="button" class="flex items-center justify-between w-full px-3 py-1.5 text-sm font-semibold text-left text-gray-500 border border-b-0 border-gray-200 rounded-t-xl hover:bg-gray-100" data-accordion-target="#accordion-collapse-body-'.$x.'" aria-expanded="false" aria-controls="accordion-collapse-body-'.$x.'">
                                                            <span>'.$menu->name.'</span>
                                                            <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                        </button>
                                                    </h2>
                                                    <div id="accordion-collapse-body-'.$x.'" class="hidden" aria-labelledby="accordion-collapse-heading-'.$x.'">
                                                        <div class="px-3 py-1.5 font-light border border-b-0 border-gray-200">
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Category</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    '.$menu->category.'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Quantity</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    '.number_format($menu->quantity, 2, '.', ',').'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Price</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    ₱ '.number_format($menu->price, 2, '.', ',').'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Action</div>
                                                                <div class="col-span-2">
                                                                    <a href="'.url('/menu/edit/'.$menu->slug).'" class="text-sm font-semibold text-blue-600 hover:underline">Setup</a> | 
                                                                    <a type="button" data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" data-slug="'.$menu->slug.'" class="text-sm font-semibold text-red-600 deleteButton hover:underline">Delete</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ';
                                            }else if($x == $menus->count()){
                                                echo '
                                                    <h2 id="accordion-collapse-heading-'.$x.'">
                                                        <button type="button" class="flex items-center justify-between w-full px-3 py-1.5 text-sm font-medium text-left text-gray-500 border border-gray-200 hover:bg-gray-100" data-accordion-target="#accordion-collapse-body-'.$x.'" aria-expanded="false" aria-controls="accordion-collapse-body-'.$x.'">
                                                            <span>'.$menu->name.'</span>
                                                            <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                        </button>
                                                    </h2>
                                                    <div id="accordion-collapse-body-'.$x.'" class="hidden" aria-labelledby="accordion-collapse-heading-'.$x.'">
                                                        <div class="px-3 py-1.5 font-light border border-t-0 border-gray-200 rounded-b-xl">
                                                            <div class="grid content-center grid-cols-3">
                                                                <div class="text-xs leading-5">Category</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    '.$menu->category.'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Quantity</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    '.$menu->quantity.'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Price</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    ₱ '.number_format($menu->price, 2, '.', ',').'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div>Action</div>
                                                                <div class="col-span-2">
                                                                    <a href="'.url('/menu/edit/'.$menu->slug).'" class="text-sm font-semibold text-blue-600 hover:underline">Setup</a> | 
                                                                    <a type="button" data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" data-slug="'.$menu->slug.'" class="text-sm font-semibold text-red-600 deleteButton hover:underline">Delete</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ';
                                            }else{
                                                echo '
                                                    <h2 id="accordion-collapse-heading-'.$x.'">
                                                        <button type="button" class="flex items-center justify-between w-full px-3 py-1.5 text-sm font-medium text-left text-gray-500 border border-b-0 border-gray-200 hover:bg-gray-100" data-accordion-target="#accordion-collapse-body-'.$x.'" aria-expanded="false" aria-controls="accordion-collapse-body-'.$x.'">
                                                            <span>'.$menu->name.'</span>
                                                            <svg data-accordion-icon class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                        </button>
                                                    </h2>
                                                    <div id="accordion-collapse-body-'.$x.'" class="hidden" aria-labelledby="accordion-collapse-heading-'.$x.'">
                                                        <div class="px-3 py-1.5 font-light border border-b-0 border-gray-200">
                                                            <div class="grid content-center grid-cols-3">
                                                                <div class="text-xs leading-5">Category</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    '.$menu->category.'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Quantity</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    '.$menu->quantity.'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Price</div>
                                                                <div class="col-span-2 text-sm font-semibold">
                                                                    ₱ '.number_format($menu->price, 2, '.', ',').'
                                                                </div>
                                                            </div>
                                                            <div class="grid grid-cols-3">
                                                                <div class="text-xs leading-5">Action</div>
                                                                <div class="col-span-2">
                                                                    <a href="'.url('/menu/edit/'.$menu->slug).'" class="text-sm font-semibold text-blue-600 hover:underline">Setup</a> | 
                                                                    <a type="button" data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" data-slug="'.$menu->slug.'" class="text-sm font-semibold text-red-600 deleteButton hover:underline">Delete</a>
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
                            </div> --}}
                        {{-- INVENTORY LIST SMALL DEVICE END --}}
                    </div>
    
                    {{-- PAGINATION --}}
                        <div class="grid px-3 mt-3 md:grid-cols-2">
                            @php
                                $prev = $page - 1;
                                $next = $page + 1;
                                $from = ($prev * 100) + 1;
                                $to = $page * 100;
                                if($to > $menuCount){
                                    $to = $menuCount;
                                }if($menuCount == 0){
                                    $from = 0;
                                }
                            @endphp
                            <div class="self-center justify-self-center md:justify-self-start">
                                <span class="text-sm text-gray-700">
                                    Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $menuCount }}</span> Items
                                </span>
                            </div>
    
                            <div class="justify-self-center md:justify-self-end">
                                <nav aria-label="Page navigation example" class="h-8 mb-0.5 shadow-xl">
                                    <ul class="inline-flex items-center -space-x-px">
                                        <li>
                                            <a href="{{ ($search == '') ? url('/menu/'.$prev) : url('/menu/'.$prev.'/'.$search);  }}"  class="{{ ($page == 1) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                                <i class="uil uil-angle-left-b"></i>
                                                <span class="sr-only">Previous</span>
                                            </a>
                                        </li>
                                        <li>
                                            <p class="z-10 block font-semibold leading-9 text-center text-gray-500 bg-white border border-gray-300 w-9 h-9">{{ $page }}</p>
                                        </li>
                                        <li>
                                            <a href="{{ ($search == '') ? url('/menu/'.$next) : url('/menu/'.$next.'/'.$search); }}" class="{{ ($to == $menuCount) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
                                                <i class="uil uil-angle-right-b"></i>
                                                <span class="sr-only">Next</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    {{-- PAGINATION END --}}
    
    
                </div>
            </div>
        </div>
     </div>
    <script>
        $(document).ready(function() {
            var counter = 2;
            // Add debounce variable
            var searchTimeout;

            const branchSelect = document.getElementById('branch_id_template');
            const downloadTemplateLink = document.getElementById('downloadTemplate');
            const branchSelectIndex = document.getElementById('branchFilter');
            const exportlink = document.getElementById('exportBtn');
            
            // Keep track of whether we're manually submitting
            var isManualSubmit = false;
            
            // Update export link when filters change
            $('#branchFilter, #searchSelect').on('change', function() {
                updateExportLink();
            });
            
            function updateDownloadLink() {
                const branchId = branchSelect.value;

                console.log('Selected branch:', branchId);

                if (branchId !== '') {
                    downloadTemplateLink.href = `{{ route('menu.template', ['branch' => 'BRANCH_ID']) }}`
                        .replace('BRANCH_ID', branchId);
                } else {
                    downloadTemplateLink.href = `{{ route('menu.template') }}`;
                }
            }

            if (branchSelect && downloadTemplateLink) {
                branchSelect.addEventListener('change', updateDownloadLink);
                updateDownloadLink();
            }

            // ============================================================
            // SEARCHABLE DROPDOWN (Combobox) FUNCTIONALITY
            // ============================================================
            // Initialize searchable dropdowns
            function initSearchableDropdown(dropdownId, optionData, hiddenFieldId) {
                var $container = $('#' + dropdownId);
                var $input = $container.find('.dropdown-input');
                var $list = $container.find('.dropdown-list');
                var $arrow = $container.find('.dropdown-arrow');
                var $clearBtn = $container.find('.dropdown-clear');
                var $hidden = $('#' + hiddenFieldId);
                
                var selectedValue = '';
                var selectedLabel = '';
                var allOptions = [];
                
                // Store original options data
                allOptions = optionData;
                
                // Function to render filtered options
                function renderOptions(filterText) {
                    var html = '';
                    var searchTerm = filterText.toLowerCase().trim();
                    var hasResults = false;
                    
                    allOptions.forEach(function(opt) {
                        var label = opt.label || opt;
                        var value = opt.value || opt;
                        var disabled = opt.disabled || false;
                        
                        // Check if it matches the search
                        var labelLower = label.toLowerCase();
                        var matches = searchTerm === '' || labelLower.indexOf(searchTerm) > -1;
                        
                        if (matches) {
                            hasResults = true;
                            var displayLabel = label;
                            
                            // Highlight matching text if search term exists
                            if (searchTerm !== '') {
                                var regex = new RegExp('(' + searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                                displayLabel = label.replace(regex, '<span class="highlight">$1</span>');
                            }
                            
                            var disabledClass = disabled ? 'disabled' : '';
                            var selectedClass = (value === selectedValue) ? 'active' : '';
                            
                            html += '<div class="dropdown-item ' + disabledClass + ' ' + selectedClass + '" data-value="' + value + '" data-label="' + label.replace(/"/g, '&quot;') + '">' + displayLabel + '</div>';
                        }
                    });
                    
                    if (!hasResults) {
                        html = '<div class="no-results">No results found</div>';
                    }
                    
                    $list.html(html);
                    
                    // Show/hide clear button
                    if ($input.val().trim() !== '') {
                        $clearBtn.addClass('visible');
                    } else {
                        $clearBtn.removeClass('visible');
                    }
                }
                
                // Open dropdown
                function openDropdown() {
                    $list.addClass('open');
                    $arrow.addClass('open');
                    $container.addClass('focused');
                    renderOptions($input.val());
                }
                
                // Close dropdown
                function closeDropdown() {
                    $list.removeClass('open');
                    $arrow.removeClass('open');
                    $container.removeClass('focused');
                }
                
                // Select an option
                function selectOption(value, label) {
                    selectedValue = value;
                    selectedLabel = label;
                    $input.val(label);
                    $hidden.val(value);
                    closeDropdown();
                    
                    // Trigger change event on hidden field
                    $hidden.trigger('change');
                    
                    // Clear button visibility
                    if (label !== '') {
                        $clearBtn.addClass('visible');
                    } else {
                        $clearBtn.removeClass('visible');
                    }
                }
                
                // Populate options from data
                function populateOptions(optionsData) {
                    allOptions = optionsData;
                    if ($list.hasClass('open')) {
                        renderOptions($input.val());
                    }
                }
                
                // Expose methods
                $container.data('populateOptions', populateOptions);
                $container.data('selectOption', selectOption);
                $container.data('closeDropdown', closeDropdown);
                $container.data('openDropdown', openDropdown);
                $container.data('selectedValue', function() { return selectedValue; });
                
                // Event: Input focus
                $input.on('focus', function() {
                    // Always show all options when focusing, with current filter
                    openDropdown();
                });
                
                // Event: Input keyup (filter)
                $input.on('keyup', function(e) {
                    var val = $(this).val();
                    
                    if (e.key === 'Escape') {
                        closeDropdown();
                        return;
                    }
                    
                    if (e.key === 'Enter') {
                        // Select first visible item
                        var $firstItem = $list.find('.dropdown-item:not(.disabled)').first();
                        if ($firstItem.length) {
                            selectOption($firstItem.data('value'), $firstItem.data('label'));
                        }
                        return;
                    }
                    
                    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        var $items = $list.find('.dropdown-item:not(.disabled)');
                        if ($items.length === 0) return;
                        
                        var currentIndex = -1;
                        $items.each(function(i) {
                            if ($(this).hasClass('active')) {
                                currentIndex = i;
                            }
                        });
                        
                        if (e.key === 'ArrowDown') {
                            var nextIndex = (currentIndex + 1) % $items.length;
                            $items.removeClass('active');
                            $items.eq(nextIndex).addClass('active');
                            // Scroll to item
                            $items.eq(nextIndex)[0].scrollIntoView({ block: 'nearest' });
                        } else {
                            var prevIndex = currentIndex <= 0 ? $items.length - 1 : currentIndex - 1;
                            $items.removeClass('active');
                            $items.eq(prevIndex).addClass('active');
                            $items.eq(prevIndex)[0].scrollIntoView({ block: 'nearest' });
                        }
                        return;
                    }
                    
                    // Filter options
                    if ($list.hasClass('open') || val.length > 0) {
                        if (!openDropdown()) {
                            renderOptions(val);
                        } else {
                            renderOptions(val);
                        }
                    }
                });
                
                // Event: Click outside to close
                $(document).on('click', function(e) {
                    if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                        // Only close if no item was selected (handled in item click)
                        // But we need to check if click was on an item
                        if (!$(e.target).closest('.dropdown-item').length) {
                            closeDropdown();
                        }
                    }
                });
                
                // Event: Click on dropdown item (delegated)
                $list.on('click', '.dropdown-item:not(.disabled)', function() {
                    var value = $(this).data('value');
                    var label = $(this).data('label');
                    selectOption(value, label);
                });
                
                // Event: Clear button
                $clearBtn.on('click', function(e) {
                    e.stopPropagation();
                    selectOption('', '');
                    $input.val('');
                    $clearBtn.removeClass('visible');
                    $input.focus();
                    renderOptions('');
                    openDropdown();
                });
                
                // Event: Arrow click to toggle
                $arrow.on('click', function(e) {
                    e.stopPropagation();
                    if ($list.hasClass('open')) {
                        closeDropdown();
                    } else {
                        renderOptions($input.val());
                        openDropdown();
                    }
                });
                
                // Initial state
                renderOptions('');
                
                // Return the instance
                return {
                    populateOptions: populateOptions,
                    selectOption: selectOption,
                    closeDropdown: closeDropdown,
                    openDropdown: openDropdown,
                    getSelectedValue: function() { return selectedValue; },
                    getSelectedLabel: function() { return selectedLabel; }
                };
            }

            // ============================================================
            // DELETE MODAL - Searchable Dropdown
            // ============================================================
            var deleteDropdownInstance = null;
            
            $(document).on('click', '.deleteButton', function(){
                var slug = $(this).data('slug');
                $('.deleteConfirm').prop('href', `{{ url('/menu/delete/${slug}') }}`);
                
                // Get brand data
                var options = [];
                var selectedBrandRows = $('.brand-data-row').filter(function() {
                    return String($(this).data('brand-row')) === String(slug);
                });
                
                selectedBrandRows.each(function() {
                    var brandSlug = $(this).data('brand-slug') || '';
                    var brandName = $(this).data('brand-name') || '';
                    var brandCode = $(this).data('brand-code') || '';
                    var label = brandName + (brandCode ? ' (' + brandCode + ')' : '');
                    options.push({ value: brandSlug, label: label, disabled: false });
                });
                
                if (options.length === 0) {
                    options.push({ value: '', label: 'No brand data found', disabled: true });
                }
                
                // Initialize or update dropdown
                if (deleteDropdownInstance) {
                    deleteDropdownInstance.populateOptions(options);
                    deleteDropdownInstance.selectOption('', '');
                } else {
                    deleteDropdownInstance = initSearchableDropdown('deleteDropdown', options, 'deleteBrandSelect');
                }
                
                // When selection changes, update delete link
                $('#deleteBrandSelect').off('change').on('change', function() {
                    var selectedSlug = $(this).val();
                    if (selectedSlug) {
                        $('.deleteConfirm').prop('href', `{{ url('/menu/delete/${selectedSlug}') }}`);
                    } else {
                        $('.deleteConfirm').prop('href', `{{ url('/menu/delete/${slug}') }}`);
                    }
                });
            });

            // ============================================================
            // TRANSFORM MODAL - Searchable Dropdown
            // ============================================================
            var transformDropdownInstance = null;
            
            $(document).on('click', '.transformButton', function() {
                var itemSlug = $(this).data('item-slug');
                var itemName = $(this).data('item-name') || '';
                var proceedLink = $('#transformProceedLink');
                
                $('#transformSourceItemName').text(itemName);
                
                // Get brand data
                var options = [];
                var selectedBrandRows = $('.brand-data-row').filter(function() {
                    return String($(this).data('brand-row')) === String(itemSlug);
                });
                
                selectedBrandRows.each(function() {
                    var brandSlug = $(this).data('brand-slug') || '';
                    var brandName = $(this).data('brand-name') || '';
                    var brandCode = $(this).data('brand-code') || '';
                    var brandQty = parseFloat($(this).data('brand-quantity')) || 0;
                    var brandUnit = $(this).data('brand-unit') || '';
                    
                    var canSelect = brandQty > 0;
                    var qtyText = ' - Qty: ' + brandQty.toFixed(3) + (brandUnit ? ' ' + brandUnit : '');
                    var codeText = brandCode ? ' (' + brandCode + ')' : '';
                    var stockText = canSelect ? '' : ' - Out of stock';
                    var label = brandName + codeText + qtyText + stockText;
                    
                    options.push({ value: brandSlug, label: label, disabled: !canSelect });
                });
                
                if (options.length === 0) {
                    options.push({ value: '', label: 'No brand data found', disabled: true });
                }
                
                // Initialize or update dropdown
                if (transformDropdownInstance) {
                    transformDropdownInstance.populateOptions(options);
                    transformDropdownInstance.selectOption('', '');
                } else {
                    transformDropdownInstance = initSearchableDropdown('transformDropdown', options, 'transformBrandSelect');
                }
                
                proceedLink.attr('href', '{{ route("transform.create") }}');
                proceedLink.addClass('pointer-events-none opacity-50');
                
                // When selection changes, update proceed link
                $('#transformBrandSelect').off('change').on('change', function() {
                    var selectedBrandSlug = $(this).val();
                    if (selectedBrandSlug) {
                        var targetUrl = '{{ route("transform.create") }}' + '?source_slug=' + encodeURIComponent(selectedBrandSlug);
                        proceedLink.attr('href', targetUrl);
                        proceedLink.removeClass('pointer-events-none opacity-50');
                    } else {
                        proceedLink.attr('href', '{{ route("transform.create") }}');
                        proceedLink.addClass('pointer-events-none opacity-50');
                    }
                });
            });

            // ============================================================
            // DISPOSE MODAL - Searchable Dropdown
            // ============================================================
            var disposeDropdownInstance = null;
            
            $('.disposeButton').click(function(){
                var slug = $(this).data('slug');
                var name = $(this).data('name');
                $('#disposeSlug').val(slug);
                $('#disposeName').html(name);
                
                // Get brand data
                var options = [];
                var selectedBrandRows = $('[data-brand-row="' + slug + '"]');
                selectedBrandRows.each(function() {
                    var brandSlug = $(this).data('brand-slug') || '';
                    var brandName = $(this).data('brand-name') || '';
                    var brandCode = $(this).data('brand-code') || '';
                    var label = brandName + (brandCode ? ' (' + brandCode + ')' : '');
                    options.push({ value: brandSlug, label: label, disabled: false });
                });
                
                if (options.length === 0) {
                    options.push({ value: '', label: 'No brand data found', disabled: true });
                }
                
                // Initialize or update dropdown
                if (disposeDropdownInstance) {
                    disposeDropdownInstance.populateOptions(options);
                    disposeDropdownInstance.selectOption('', '');
                } else {
                    disposeDropdownInstance = initSearchableDropdown('disposeDropdown', options, 'disposeBrandSelect');
                }
                
                // When selection changes, update dispose slug
                $('#disposeBrandSelect').off('change').on('change', function() {
                    var selectedSlug = $(this).val();
                    if (selectedSlug) {
                        $('#disposeSlug').val(selectedSlug);
                    } else {
                        $('#disposeSlug').val(slug);
                    }
                });
            });

            // ============================================================
            // Clear searchable dropdowns when modals close
            // ============================================================
            function clearDropdownInstance(instance) {
                if (instance) {
                    instance.selectOption('', '');
                    instance.closeDropdown();
                }
            }
            
            $('#itemDeleteModal').on('hidden.bs.modal', function() {
                clearDropdownInstance(deleteDropdownInstance);
                // Reset the hidden field
                $('#deleteBrandSelect').val('');
            });
            
            $('#transformSourceModal').on('hidden.bs.modal', function() {
                clearDropdownInstance(transformDropdownInstance);
                $('#transformBrandSelect').val('');
            });
            
            $('#disposeModal').on('hidden.bs.modal', function() {
                clearDropdownInstance(disposeDropdownInstance);
                $('#disposeBrandSelect').val('');
            });
            
            // Handle modal close via data-modal-hide
            $(document).on('click', '[data-modal-hide]', function() {
                var modalId = $(this).closest('[id$="Modal"]').attr('id');
                if (modalId === 'itemDeleteModal') {
                    clearDropdownInstance(deleteDropdownInstance);
                    $('#deleteBrandSelect').val('');
                } else if (modalId === 'transformSourceModal') {
                    clearDropdownInstance(transformDropdownInstance);
                    $('#transformBrandSelect').val('');
                } else if (modalId === 'disposeModal') {
                    clearDropdownInstance(disposeDropdownInstance);
                    $('#disposeBrandSelect').val('');
                }
            });
            // ============================================================
            // END SEARCHABLE DROPDOWN FUNCTIONALITY
            // ============================================================

            $('#searchInput').on('keyup', function(event) {
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Set new timeout
                searchTimeout = setTimeout(function() {
                    document.getElementById('filterForm').submit();
                }, 500); // 500ms delay after typing stops
            });
            
            jQuery(document).on( "click", ".select-btn", function(e){
                $('.content').not($(this).closest('.wrapper').find('.content')).addClass('hidden');
                $(this).closest('.wrapper').find('.content').toggleClass('hidden');
                $(this).closest('.wrapper').find('.uil-angle-down').toggleClass('-rotate-180');
                e.stopPropagation();
            });

            function searchFilter(searchInput){
                $(".listOption li").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchInput) > -1)
                });
            }

            jQuery(document).on( "click", ".content", function(e){
                e.stopPropagation();
            });

            jQuery(document).on( "input", ".selectSearch", function(){
                var value = $(this).val().toLowerCase();
                searchFilter(value);
            });

            jQuery(document).on( "click", ".listOption li", function(){
                var id = $(this).data('id');
                var name = $(this).data('name');
                var unitid = 'unit' + $(this).data('idnum');
                var qtyid = 'quantity' + $(this).data('idnum');

                if(id == ""){
                    $(this).closest('.wrapper').find('input').val(id);
                    $(this).closest('.wrapper').find('.select-btn span').html(name);
                    $('.content').addClass('hidden');
                    $('.selectSearch').val('');
                    var value = $(".selectSearch").val().toLowerCase();
                    searchFilter(value);

                    $('#' + unitid).html('');
                    $('#' + qtyid).val('');
                }else{
                    var unit = $(this).data('unit');
                    $(this).closest('.wrapper').find('input').val(id);
                    $(this).closest('.wrapper').find('.select-btn span').html(name);
                    $('.content').addClass('hidden');
                    $('.selectSearch').val('');
                    var value = $(".selectSearch").val().toLowerCase();
                    searchFilter(value);

                    $('#' + unitid).html(unit);
                    $('#' + qtyid).val('');
                }

                $('.uil-angle-down').removeClass('-rotate-180');
            });

            // Updated search function with auto-submit
            function performSearch() {
                var search = $('#searchInput').val();
                const categoryId = $('#searchSelect').val();
                const branchFilter = $('#branchFilter').val();
                const stockFilter = $('#stockFilter').val();
                
                let url = '{{ url("/menu") }}';
                const params = new URLSearchParams();
                
                if (search) {
                    params.set('search', search);
                }
                if (categoryId && categoryId !== 'All') {
                    params.set('searchSelect', categoryId);
                }
                if (branchFilter) {
                    params.set('branchFilter', branchFilter);
                }
                if (stockFilter && stockFilter !== 'all') {
                    params.set('stockFilter', stockFilter);
                }
                
                const queryString = params.toString();
                if (queryString) {
                    url += '?' + queryString;
                }
                
                window.location.href = url;
            }

            // Initialize stock filter on page load
            const initialStockFilter = '{{ $stockFilter ?? 'all' }}';
            $('#stockFilter').val(initialStockFilter);
            
            // Apply initial stock filter if not 'all'
            if (initialStockFilter !== 'all') {
                filterTableByStock(initialStockFilter);
            }

            // Stock filter event handler (client-side only)
            $('#stockFilter').on('change', function() {
                const stockFilter = $(this).val();
                console.log('Stock filter changed to:', stockFilter);
                filterTableByStock(stockFilter);
                
                // Update URL without triggering page reload
                const url = new URL(window.location);
                if (stockFilter === 'all') {
                    url.searchParams.delete('stockFilter');
                } else {
                    url.searchParams.set('stockFilter', stockFilter);
                }
                window.history.pushState({}, '', url);
            });

            function filterTableByStock(filterType) {
                console.log('Filtering table by stock type:', filterType);
                const rows = $('#menuTable tbody tr.item-row-toggle');
                console.log('Found', rows.length, 'rows to filter');

                if (filterType === 'all') {
                    // Show all rows and their detail rows
                    rows.each(function() {
                        const row = $(this);
                        const slug = row.data('item-row');
                        const detailRow = $('[data-item-detail="' + slug + '"]');
                        
                        row.show();
                        // Only show detail row if it was previously open (has data attribute or check)
                        // We'll use the aria-expanded attribute to determine state
                        if (row.attr('aria-expanded') === 'true') {
                            detailRow.removeClass('hidden');
                        } else {
                            detailRow.addClass('hidden');
                        }
                    });
                    return;
                }

                // For 'low' and 'out' filters
                rows.each(function(index) {
                    const row = $(this);
                    const slug = row.data('item-row');
                    const detailRow = $('[data-item-detail="' + slug + '"]');
                    let shouldShow = false;

                    // Get the stock quantity from the row
                    const stockCell = row.find('td:nth-child(4)');
                    console.log('Row', index, 'stock cell text:', stockCell.text().trim());
                    let quantity = 0;
                    let reorderPoint = 0;

                    // Check if there's a select dropdown (multiple branches) or direct text
                    const selectElement = stockCell.find('select');
                    if (selectElement.length > 0) {
                        console.log('Row', index, 'has select dropdown');
                        // Multiple branches - get reorder point first
                        const reorderCell = row.find('td:nth-child(5)');
                        const reorderSelect = reorderCell.find('select');

                        if (reorderSelect.length > 0) {
                            const firstOption = reorderSelect.find('option').first();
                            if (firstOption.length > 0) {
                                const optionText = firstOption.text();
                                console.log('Row', index, 'reorder option text:', optionText);
                                const reorderMatch = optionText.match(/: (\d+(?:,\d+)*)/);
                                if (reorderMatch) {
                                    reorderPoint = parseInt(reorderMatch[1].replace(/,/g, ''));
                                    console.log('Row', index, 'parsed reorderPoint:', reorderPoint);
                                }
                            }
                        }

                        // Check if any branch has low/out of stock
                        selectElement.find('option').each(function() {
                            const optionText = $(this).text();
                            console.log('Row', index, 'stock option text:', optionText);
                            const quantityMatch = optionText.match(/: (\d+(?:,\d+)*)/);
                            if (quantityMatch) {
                                const branchQuantity = parseInt(quantityMatch[1].replace(/,/g, ''));
                                console.log('Row', index, 'branch quantity:', branchQuantity);

                                // Get the branch name from the option text
                                const branchNameMatch = optionText.match(/^([^:]+):/);
                                if (branchNameMatch) {
                                    const branchName = branchNameMatch[1].trim();
                                    console.log('Row', index, 'branch name:', branchName);

                                    // Find the corresponding reorder point for this branch
                                    const reorderCell = row.find('td:nth-child(5)');
                                    const reorderSelect = reorderCell.find('select');
                                    let branchReorderPoint = 0;

                                    if (reorderSelect.length > 0) {
                                        reorderSelect.find('option').each(function() {
                                            const reorderOptionText = $(this).text();
                                            if (reorderOptionText.startsWith(branchName + ':')) {
                                                const reorderMatch = reorderOptionText.match(/: (\d+(?:,\d+)*)/);
                                                if (reorderMatch) {
                                                    branchReorderPoint = parseInt(reorderMatch[1].replace(/,/g, ''));
                                                    console.log('Row', index, 'found reorder point for', branchName, ':', branchReorderPoint);
                                                }
                                            }
                                        });
                                    }

                                    // Check stock level against this branch's reorder point
                                    if (filterType === 'low' && branchQuantity <= branchReorderPoint && branchQuantity > 0) {
                                        shouldShow = true;
                                        console.log('Row', index, 'matches low stock criteria for', branchName);
                                    } else if (filterType === 'out' && branchQuantity === 0) {
                                        shouldShow = true;
                                        console.log('Row', index, 'matches out of stock criteria for', branchName);
                                    }
                                }
                            }
                        });
                        console.log('Row', index, 'multi-branch shouldShow:', shouldShow);
                    } else {
                        console.log('Row', index, 'single branch view');
                        // Single branch - get the text content
                        const quantityText = stockCell.text().trim();
                        quantity = parseInt(quantityText.replace(/,/g, '')) || 0;
                        console.log('Row', index, 'parsed quantity:', quantity);

                        // Get reorder point
                        const reorderCell = row.find('td:nth-child(5)');
                        const reorderSelect = reorderCell.find('select');
                        if (reorderSelect.length > 0) {
                            // Multiple branches - use the first reorder point as reference
                            const firstOption = reorderSelect.find('option').first();
                            if (firstOption.length > 0) {
                                const optionText = firstOption.text();
                                console.log('Row', index, 'single branch reorder option text:', optionText);
                                const reorderMatch = optionText.match(/: (\d+(?:,\d+)*)/);
                                if (reorderMatch) {
                                    reorderPoint = parseInt(reorderMatch[1].replace(/,/g, ''));
                                    console.log('Row', index, 'single branch parsed reorderPoint:', reorderPoint);
                                }
                            }
                        } else {
                            // Single branch - get the text content
                            const reorderText = reorderCell.text().trim();
                            reorderPoint = parseInt(reorderText.replace(/,/g, '')) || 0;
                            console.log('Row', index, 'single branch reorder text:', reorderText, 'parsed reorderPoint:', reorderPoint);
                        }

                        if (filterType === 'low') {
                            // Show items where quantity <= reorder point (low stock)
                            shouldShow = quantity <= reorderPoint && quantity > 0;
                            console.log('Row', index, 'low stock check:', quantity, '<=', reorderPoint, '&&', quantity, '> 0 =', shouldShow);
                        } else if (filterType === 'out') {
                            // Show items where quantity == 0 (out of stock)
                            shouldShow = quantity === 0;
                            console.log('Row', index, 'out of stock check:', quantity, '=== 0 =', shouldShow);
                        }
                    }

                    console.log('Row', index, 'final shouldShow:', shouldShow);
                    if (shouldShow) {
                        row.show();
                        // Don't automatically show detail rows when filtering
                        detailRow.addClass('hidden');
                        row.attr('aria-expanded', 'false');
                    } else {
                        row.hide();
                        detailRow.addClass('hidden');
                    }
                });
            }
            
            // Auto-search on input with debounce (500ms delay)
            $('#searchInput').on('keyup', function(event) {
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Set new timeout
                searchTimeout = setTimeout(function() {
                    performSearch();
                }, 500); // 500ms delay after typing stops
            });

            // Also trigger search on Enter key
            $('#searchInput').on('keydown', function(event) {
                if (event.keyCode === 13) {
                    clearTimeout(searchTimeout); // Clear any pending timeout
                    performSearch();
                    event.preventDefault();
                }
            });

            // Clear button functionality
            $('#clearButton').click(function(){
                $('#searchInput').val('');
                // Trigger search immediately when clearing
                performSearch();
            });

            $('.notifButton').click(function(){
                $('.notif').addClass('hidden');
            });

            // Updated delete button handler for dynamic table
            // (The handler above now uses the searchable dropdown)
            // Keeping this for compatibility but it's now handled above

            $('.contentDiv').click(function(){
                $('.notif').addClass('hidden');
            });

            $('#incQty').click(function(){
                var qty = $('#servings').val();
                qty++;
                $('#servings').val(qty);

                var slug = $('#slug').val();
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('menu.computeqty') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        qty: qty,
                        _token: _token
                    },
                    success:function(result){
                        $('#ingredients').html(result.ingredients);
                    }
                })
            });

            $('#decQty').click(function(){
                var qty = $('#servings').val();
                qty--;
                if(qty > 0){
                    $('#servings').val(qty);
                }else{
                    qty++;
                }

                var slug = $('#slug').val();
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('menu.computeqty') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        qty: qty,
                        _token: _token
                    },
                    success:function(result){
                        $('#ingredients').html(result.ingredients);
                    }
                })
            });

            $('#servings').on('keyup', function() {
                var qty = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(qty);
                if(qty == ''){
                    $(this).val(1);
                }

                if(qty < 1){
                    $('#servings').val(1);
                    qty = 1;
                }

                var slug = $('#slug').val();
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('menu.computeqty') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        qty: qty,
                        _token: _token
                    },
                    success:function(result){
                        $('#ingredients').html(result.ingredients);
                    }
                })
            });

            // Limit brand stock fields to 2 decimal places
            $(document).on('input', '.brand-stock-input', function() {
                var val = $(this).val();
                // Allow digits and dot only
                val = val.replace(/[^0-9\.]/g, '');
                if (val.indexOf('.') !== -1) {
                    var parts = val.split('.');
                    parts[1] = parts[1].slice(0, 2);
                    val = parts[0] + '.' + parts[1];
                }
                $(this).val(val);
            });

            $(document).on('blur', '.brand-stock-input', function() {
                var val = $(this).val();
                if (val === '') return;
                if (val.indexOf('.') === -1) {
                    $(this).val(val + '.00');
                    return;
                }
                var parts = val.split('.');
                parts[1] = (parts[1] + '00').slice(0, 2);
                $(this).val(parts[0] + '.' + parts[1]);
            });

            // Brand stocks pagination state
            var brandStockRowsPerPage = 5;
            var brandStockCurrentPage = 1;

            function updateBrandStockPagination() {
                var rows = $('#brandStocks .brand-stock-row');
                var totalRows = rows.length;
                var totalPages = Math.max(1, Math.ceil(totalRows / brandStockRowsPerPage));

                if (brandStockCurrentPage > totalPages) {
                    brandStockCurrentPage = totalPages;
                }
                if (brandStockCurrentPage < 1) {
                    brandStockCurrentPage = 1;
                }

                var start = (brandStockCurrentPage - 1) * brandStockRowsPerPage;
                var end = start + brandStockRowsPerPage;

                rows.each(function(index) {
                    if (index >= start && index < end) {
                        $(this).removeClass('hidden');
                    } else {
                        $(this).addClass('hidden');
                    }
                });

                $('#brandStockPrevBtn').prop('disabled', brandStockCurrentPage <= 1);
                $('#brandStockNextBtn').prop('disabled', brandStockCurrentPage >= totalPages);
                $('#brandStockPageInfo').text('Page ' + brandStockCurrentPage + ' of ' + totalPages);
            }

            $('#brandStockPrevBtn').click(function() {
                if (brandStockCurrentPage > 1) {
                    brandStockCurrentPage--;
                    updateBrandStockPagination();
                }
            });

            $('#brandStockNextBtn').click(function() {
                var totalRows = $('#brandStocks .brand-stock-row').length;
                var totalPages = Math.max(1, Math.ceil(totalRows / brandStockRowsPerPage));
                if (brandStockCurrentPage < totalPages) {
                    brandStockCurrentPage++;
                    updateBrandStockPagination();
                }
            });

            $(document).on('input', '.brand-search', function() {
                var slug = $(this).data('brand-search');
                var query = ($(this).val() || '').toLowerCase().trim();

                $('[data-brand-row="' + slug + '"]').each(function() {
                    var rowText = $(this).data('brand-text') || '';
                    $(this).toggleClass('hidden', rowText.indexOf(query) === -1);
                });
            });

            // Increase/decrease buttons for brand stock
            $(document).on('click', '.brand-qty-increase, .brand-qty-decrease', function() {
                var $input = $(this).closest('div').find('.brand-stock-input');
                var val = parseFloat($input.val());
                if (isNaN(val)) val = 0;
                if ($(this).hasClass('brand-qty-increase')) {
                    val += 1;
                } else {
                    val -= 1;
                }
                if (val < 0) val = 0;
                $input.val(val.toFixed(2));
            });

            $('#addButton').click(function(){
                $('#counter').val(counter);
                $('#addDiv').append(`
                    <div id="ing${counter}" class="flex flex-row mb-5 gap-x-3">
                        <div class="w-2/5">
                            <div class="relative w-full wrapper">
                                <div class="flex items-center justify-between p-2 bg-gray-100 border border-gray-300 rounded-md cursor-pointer select-btn h-9">
                                    <span></span>
                                    <i class="text-2xl transition-transform duration-300 uil uil-angle-down"></i>
                                </div>
                                <div class="absolute z-50 hidden w-full p-3 mt-1 bg-gray-100 rounded-md content">
                                    <div class="relative search">
                                        <i class="absolute leading-9 text-gray-500 uil uil-search left-3"></i>
                                        <input type="text" class="w-full leading-9 text-gray-700 rounded-md outline-none selectSearch pl-9 h-9" placeholder="Search">
                                    </div>
                                    <ul class="mt-2 overflow-y-auto listOption options max-h-52">
                                        <li data-id="" data-code="None" class="flex items-center pl-3 leading-9 rounded-md cursor-pointer h-9 hover:bg-gray-300">None</li>
                                        @foreach ($items as $item)
                                            <li data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-unit="{{ $item->unit }}" data-idnum="${counter}" class="flex items-center pl-3 leading-9 rounded-md cursor-pointer h-9 hover:bg-gray-300">{{ $item->name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <input type="hidden" name="item${counter}" value="">
                            </div>
                        </div>
                        <div class="w-2/5">
                            <input type="text" id="quantity${counter}" name="quantity${counter}" value="{{ old('quantity') }}" class="block w-full px-2 text-center text-gray-900 border border-gray-300 rounded-lg quantity h-9 bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base">
                        </div>
                        <div class="flex w-1/5">
                            <div id="unit${counter}" class="w-1/2 text-lg leading-9"></div>
                            <button type="button" data-thisid="ing${counter}" class="w-1/2 text-center removeButton"><i class="text-3xl text-red-500 uil uil-minus-circle"></i></button>
                        </div>
                    </div>
                `);
                counter++;
            });

            jQuery(document).on( "click", ".removeButton", function(){
                var thisid = $(this).data('thisid');
                $('#'+thisid).remove();
            });

            jQuery(document).on( "keyup", "#quantity", function(){
                var val = $(this).val().replace(/[^0-9.]/g, '');
                $(this).val(val);
                if(val == ''){
                    $('#addQtyButton').prop('disabled', true);
                }else{
                    $('#addQtyButton').prop('disabled', false);
                }
            });

            $('.moveButton').click(function(){
                var slug = $(this).data('slug');
                var quantity = $(this).data('quantity');

                $('#moveSlug').val(slug);
                $('#moveServings').val(1);
                $('#moveServings').attr('max', quantity);
            });

            // dispose button handler now uses searchable dropdown (above)

            //ken 250623001 ken updated here (category dropdown removed; no longer updating categories via JS)
            // Function to update export link
            function updateExportLink() {
                const branchId = $('#branchFilter').val();
                const categoryId = $('#searchSelect').val();
                
                let url = '{{ route("menu.export") }}';
                
                if (branchId) {
                    url += (url.includes('?') ? '&' : '?') + 'branch=' + branchId;
                }
                
                if (categoryId && categoryId !== 'All') {
                    url += (url.includes('?') ? '&' : '?') + 'searchSelect=' + categoryId;
                }
                
                exportlink.href = url;
            }

            // CSV Import with validation and confirmation
            $('#importModal form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                var $form = $(this);
                var $submitBtn = $form.find('button[type="submit"]');
                var originalText = $submitBtn.html();
                
                // Hide import modal and show loading modal during validation
                $('#importModal').addClass('hidden');
                $('#importLoadingModal').removeClass('hidden');
                $('#importStatusMessage').text('Validating your CSV file...');
                
                // First, validate the CSV
                $.ajax({
                    url: "{{ route('menu.validate-csv') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.valid && !response.requires_confirmation) {
                            // No issues found, proceed directly with import
                            $('#importStatusMessage').text('Processing your CSV file...');
                            proceedWithImport(formData, $submitBtn, originalText);
                        } else {
                            // Hide loading modal and show confirmation modal with warnings/errors
                            $('#importLoadingModal').addClass('hidden');
                            showImportConfirmation(response, formData, $submitBtn, originalText);
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Validation failed. Please check your CSV file.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        }
                        
                        // Hide loading modal and show error
                        $('#importLoadingModal').addClass('hidden');
                        
                        // Small delay to ensure modal is closed before showing toast
                        setTimeout(function() {
                            if (window.showToast) {
                                showToast('error', errorMsg);
                            } else {
                                alert(errorMsg);
                            }
                        }, 300);
                        
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            function showImportConfirmation(validationResult, formData, $submitBtn, originalText) {
                var $modal = $('#importConfirmationModal');
                var $warningsSection = $('#warningsSection');
                var $errorsSection = $('#errorsSection');
                var $warningsList = $('#warningsList');
                var $errorsList = $('#errorsList');
                var $successPreview = $('#successPreview');
                var $successMessage = $('#successMessage');
                
                // Reset modal content
                $warningsSection.addClass('hidden');
                $errorsSection.addClass('hidden');
                $successPreview.addClass('hidden');
                
                // Show warnings if any
                if (validationResult.warnings && validationResult.warnings.length > 0) {
                    $warningsList.html('<ul class="list-disc pl-5 space-y-1">' + 
                        validationResult.warnings.map(function(warning) {
                            return '<li>' + warning + '</li>';
                        }).join('') + '</ul>');
                    $warningsSection.removeClass('hidden');
                }
                
                // Show errors if any
                if (validationResult.errors && validationResult.errors.length > 0) {
                    $errorsList.html('<ul class="list-disc pl-5 space-y-1">' + 
                        validationResult.errors.map(function(error) {
                            return '<li>' + error + '</li>';
                        }).join('') + '</ul>');
                    $errorsSection.removeClass('hidden');
                }
                
                // Show success preview if valid
                if (validationResult.valid) {
                    var branchText = formData.get('branch_id') === 'all' ? 'all branches' : 'selected branch';
                    $successMessage.text('Perfect! Your file looks good. We\'ll add new items to your item list and update any existing ones with the latest information.');
                    $successPreview.removeClass('hidden');
                }
                
                // Store form data for later use
                $modal.data('formData', formData);
                $modal.data('submitBtn', $submitBtn);
                $modal.data('originalText', originalText);
                
                // Show modal
                $modal.removeClass('hidden');
                $submitBtn.prop('disabled', false).html(originalText);
            }

            function proceedWithImport(formData, $submitBtn, originalText) {
                // The loading modal is already visible from validation step
                // Just update the message to show we're importing
                $('#importStatusMessage').text('Processing your CSV file...');
                
                $.ajax({
                    url: "{{ route('menu.import') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update loading message
                            $('#importStatusMessage').text('Import completed successfully! Refreshing page...');
                            
                            // Show success toast
                            if (window.showToast) {
                                showToast('success', 'Import completed successfully! Refreshing page...');
                            }
                            
                            // Auto refresh after a short delay
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        } else {
                            var errorMsg = response.message || 'Import failed. Please try again.';
                            
                            // Hide loading modal and show error
                            $('#importLoadingModal').addClass('hidden');
                            
                            // Small delay to ensure modal is closed before showing toast
                            setTimeout(function() {
                                if (window.showToast) {
                                    showToast('error', errorMsg);
                                } else {
                                    alert(errorMsg);
                                }
                            }, 300);
                            
                            $submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Import failed. Please try again.';
                        try {
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                        } catch(e) {}
                        
                        // Hide loading modal and show error
                        $('#importLoadingModal').addClass('hidden');
                        
                        console.error('Import error:', xhr);
                        
                        // Small delay to ensure modal is closed before showing toast
                        setTimeout(function() {
                            if (window.showToast) {
                                showToast('error', errorMsg);
                            } else {
                                alert(errorMsg);
                            }
                        }, 300);
                        
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            }

            // Handle proceed button in confirmation modal
            $('#proceedImportBtn').on('click', function() {
                var $modal = $('#importConfirmationModal');
                var formData = $modal.data('formData');
                var $submitBtn = $modal.data('submitBtn');
                var originalText = $modal.data('originalText');
                
                if (formData) {
                    // Hide confirmation modal and show loading modal
                    $('#importConfirmationModal').addClass('hidden');
                    $('#importLoadingModal').removeClass('hidden');
                    proceedWithImport(formData, $submitBtn, originalText);
                }
            });

            // Handle cancel button in confirmation modal
            $('#importConfirmationModal [data-modal-hide="importConfirmationModal"]').on('click', function() {
                // Close confirmation modal
                $('#importConfirmationModal').addClass('hidden');
                
                // Small delay to ensure modal is closed before showing toast
                setTimeout(function() {
                    if (window.showToast) {
                        showToast('info', 'Import cancelled. Your menu items remain unchanged.');
                    }
                }, 300);
            });

            // Handle close button in import modal
            $('#importModal [data-modal-hide="importModal"]').on('click', function() {
                // Small delay to ensure modal is closed before showing toast
                setTimeout(function() {
                    if (window.showToast) {
                        showToast('info', 'Import modal closed. No changes were made.');
                    }
                }, 300);
            });

            // ============================================================
            // FIX: Accordion behavior - only one row open at a time
            // ============================================================
            // Use event delegation for dynamic rows
            $(document).on('click', '.item-row-toggle', function(e) {
                // Ignore clicks on interactive elements
                if (e.target.closest('a, button, input, select, textarea, label')) {
                    return;
                }
                
                var $clickedRow = $(this);
                var $clickedDetail = $clickedRow.next('.item-detail-row');
                var isHidden = $clickedDetail.hasClass('hidden');
                
                // Close ALL open detail rows
                $('.item-detail-row').each(function() {
                    var $detail = $(this);
                    if (!$detail.hasClass('hidden')) {
                        $detail.addClass('hidden');
                        // Update the corresponding row's aria-expanded and chevron
                        var $row = $detail.prev('.item-row-toggle');
                        if ($row.length) {
                            $row.attr('aria-expanded', 'false');
                            var $chevron = $row.find('.item-row-chevron');
                            if ($chevron.length) {
                                $chevron.removeClass('rotate-180');
                            }
                        }
                    }
                });
                
                // If the clicked row was hidden, open it
                if (isHidden) {
                    $clickedDetail.removeClass('hidden');
                    $clickedRow.attr('aria-expanded', 'true');
                    var $chevron = $clickedRow.find('.item-row-chevron');
                    if ($chevron.length) {
                        $chevron.addClass('rotate-180');
                    }
                }
                // If it was already open, it stays closed (we already closed it above)
            });

        });
    </script>
</x-app-layout>