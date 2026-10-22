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
    </style>
    {{-- DELETE MODAL --}}
        <!-- Modal toggle -->
        {{-- <button data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" type="button">
            Toggle modal
        </button> --}}
        
        <!-- Main modal -->
        <div id="itemDeleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <div class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <i class="mr-2 text-xl text-red-700 uil uil-exclamation-triangle md:text-2xl lg:text-3xl"></i>
                            <span class="text-base text-red-700 md:text-lg lg:text-xl">Delete Item</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemDeleteModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-3 space-y-6 md:py-6">
                        <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                            Are you sure you want to delete this item?
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <a type="button" href="" class="deleteConfirm w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>
                        <button data-modal-hide="itemDeleteModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- DELETE MODAL END --}}
    
    {{-- MOVE MODAL --}}
        <!-- Modal toggle -->
        {{-- <button data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" type="button">
            Toggle modal
        </button> --}}
        
        <!-- Main modal -->
        <div id="moveMenuModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <form method="POST" action="{{ route('menu.move') }}" class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            {{-- <i class="mr-2 text-xl text-yellow-400 uil uil-exclamation-triangle md:text-2xl lg:text-3xl"></i> --}}
                            <span class="text-base text-emerald-600 md:text-lg lg:text-xl">Move Menu to Inventory</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="moveMenuModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-3 space-y-6 md:py-6">
                        <input type="hidden" id="moveSlug" name="moveSlug">
                        <div class="mb-6">
                            <label for="moveServings" class="block mb-2 text-sm font-medium text-gray-900">Servings</label>
                            <input type="number" id="moveServings" name="moveServings" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 appearance-none" autocomplete="off" min="1" max="" required>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="moveConfirm w-24 text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Submit</button>
                        <button data-modal-hide="moveMenuModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- MOVE MODAL END --}}

    {{-- DISPOSE MODAL --}}
        <!-- Main modal -->
        <div id="disposeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <form method="POST" action="{{ route('menu.dispose') }}" class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <span class="text-base text-blue-500 md:text-lg lg:text-xl">Waste</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="disposeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-6">
                        <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                            <h1 id="disposeName" class="pb-5 text-xl font-bold"></h1>
                            <input type="hidden" id="disposeSlug" name="disposeSlug">
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
                    <!-- Modal footer -->
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
                    </div>
                    <!-- p class="text-2xl font-bold tracking-wide">Pack</p -->
                <!-- leo start 4/22/25 -->
                    <!-- div class="flex flex-row-reverse items-center text-center rounded-lg">
                        <button id="incQty" type="button">
                            <i class="text-3xl text-blue-500 uil uil-plus-circle"></i>
                        </button>
                        <div class="mx-2 text-2xl">
                            <input type="text" id="servings" name="servings" value="" class="p-0 text-center w-14 h-9 bg-gray-50" autocomplete="off">
                        </div>
                        <button id="decQty" type="button">
                            <i class="text-3xl text-red-500 uil uil-minus-circle"></i>
                        </button>
                    </div -->
                <!-- leo end 4/22/25 -->
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
     

    <div class=" lg:pt-3">
        <div style="height: calc(100vh - 48px);" class="flex {{ auth()->user()->role == 1 ? 'lg:ml-64 lg:pt-3' : '' }}">
            <div id="contentDiv" class="w-full p-2">
                <div class="p-4 overflow-hidden bg-white rounded-lg shadow-md">
                {{-- CONTROLS update by ken 12-14-2025 start --}}
                <div class="mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    {{-- SINGLE FILTER FORM --}}
                    <form method="GET" action="{{ route('menu.index') }}" id="filterForm" class="flex flex-wrap lg:flex-nowrap items-center gap-3 w-full">
                        {{-- ADD BUTTON (outside form) --}}
                        <a href="{{ route('menu.add') }}"
                            class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5">
                            <i class="mr-2 uil uil-plus"></i>ADD
                        </a>

                        {{-- EXPORT / IMPORT (outside form) --}}
                        <a id="exportBtn" href="{{ route('menu.export', ['branch' => request('branchFilter')]) }}"
                            class="inline-flex items-center text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-semibold rounded-lg text-sm px-5 py-2.5">
                            <i class="mr-2 uil uil-export"></i>Export
                        </a>

                        <button type="button"
                                data-modal-target="importModal"
                                data-modal-toggle="importModal"
                                class="inline-flex items-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-semibold rounded-lg text-sm px-5 py-2.5">
                            <i class="mr-2 uil uil-import"></i>Import
                        </button>

                        {{-- CATEGORY FILTER --}}
                        <div class="w-64 flex-shrink-0">
                            <select name="searchSelect"
                                    id="searchSelect"
                                    class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                <option value="All">All Categories</option>
                                @foreach ($categories as $categorys)
                                    <option value="{{ $categorys->id }}"
                                            {{ $catVal == $categorys->id ? 'selected' : '' }}>
                                        {{ $categorys->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- BRANCH FILTER --}}
                        <div class="w-56 flex-shrink-0">
                            <select name="branchFilter"
                                    id="branchFilter"
                                    class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                            {{ request('branchFilter') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- STOCK FILTER --}}
                        <div class="w-48 flex-shrink-0">
                            <select name="stockFilter"
                                    id="stockFilter"
                                    class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All Stock Levels</option>
                                <option value="low" {{ (request('stockFilter') ?? 'all') == 'low' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out" {{ (request('stockFilter') ?? 'all') == 'out' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                        </div>

                        {{-- SEARCH FIELD --}}
                        <div class="flex-1 min-w-[300px]">
                            <div class="relative w-full">
                                {{-- SEARCH ICON --}}
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>

                                {{-- INPUT --}}
                                <input type="search"
                                    id="searchInput"
                                    name="search"
                                    class="block w-full px-4 py-2.5 pl-10 pr-36 text-sm border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Search item, barcode, SKU"
                                    value="{{ $search }}"
                                    autocomplete="off">

                                {{-- BUTTONS INSIDE INPUT --}}
                                <div class="absolute inset-y-0 right-0 flex items-center pr-1">
                                    <button id="clearButton" type="button"
                                            class="p-2 text-gray-500 hover:text-gray-700">
                                        <i class="uil uil-times text-lg"></i>
                                    </button>

                                    <button type="submit"
                                            class="ml-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-4 py-2">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
                {{-- CONTROLS END --}}

     
                    <div>
                        {{-- TABLE --}}
                    <div class="overflow-x-auto hidden sm:block">
                        <div id="menuTable" class="w-full shadow-md sm:rounded-lg">
                            <table class="min-w-[900px] w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            ITEM Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                            Category
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                            Department
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                            Stock
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                            Reordering Point
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                            Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                            Acquisition Cost
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
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
                                            foreach ($menuItems as $item) {
                                                $itemsByBranch[$item->branch_id] = $item;
                                                $itemsByBrand[$item->brand_id] = $item;
                                            }
                                        @endphp
                                        
                                        <tr data-is_combo="{{ $firstItem->is_combo }}" class="bg-white border-b cursor-pointer addServing hover:bg-slate-50">
                                            {{-- Item Name --}}
                                            <td class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap">
                                                {{ $itemName }}
                                                <span class="hidden" data-slug="{{ $firstItem->slug }}"></span>
                                            </td>
                                            
                                            {{-- ken update start here ken250623001 remove dropdown of  Category --}}
                                            {{-- Category --}}
                                            <td class="px-3 sm:px-6 py-2 sm:py-3 text-center whitespace-nowrap">
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
                                            <td class="px-6 py-3 text-center whitespace-nowrap">
                                                @php
                                                    foreach($depts as $dept){
                                                        if ($firstItem->department == $dept->id){
                                                            echo $dept->name;
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            
                                            {{-- Stock --}}
                                            <td class="px-3 sm:px-6 py-2 sm:py-3 text-center whitespace-nowrap text-sm sm:text-base">
                                                @if($brandFilter)
                                                    @php
                                                        $selectedBrandItem = $itemsByBrand[$brandFilter] ?? null;
                                                    @endphp
                                                    @if($selectedBrandItem)
                                                        {{ number_format($selectedBrandItem->quantity, 3, '.', ',') }}
                                                    @else
                                                        0
                                                    @endif
                                                @else
                                                    <select class="bg-gray-50 border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full py-1 px-2 sm:p-2.5" title="Stock breakdown per branch">
                                                        @foreach($brands as $brand)
                                                            @php
                                                                $brandItem = $itemsByBrand[$brand->id] ?? null;
                                                            @endphp
                                                            @if($brandItem)
                                                                <option value="{{ $brand->id }}">{{ $brand->name }}: {{ number_format($brandItem->quantity, 3, '.', ',') }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                            
                                            {{-- Reordering Point --}}
                                            <td class="px-3 sm:px-6 py-2 sm:py-3 text-center whitespace-nowrap text-sm sm:text-base">
                                                @if($brandFilter)
                                                    @php
                                                        $selectedBrandItem = $itemsByBrand[$brandFilter] ?? null;
                                                    @endphp
                                                    @if($selectedBrandItem)
                                                        {{ number_format($selectedBrandItem->reorder_point, 0, '.', ',') }}
                                                    @else
                                                        0
                                                    @endif
                                                @else
                                                    <select class="bg-gray-50 border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full py-1 px-2 sm:p-2.5" title="Reordering point per brand">
                                                        @foreach($brands as $brand)
                                                            @php
                                                                $brandItem = $itemsByBrand[$brand->id] ?? null;
                                                            @endphp
                                                            @if($brandItem)
                                                                <option value="{{ $brand->id }}">{{ $brand->name }}: {{ number_format($brandItem->reorder_point, 0, '.', ',') }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                            
                                            {{-- Price --}}
                                            <td class="px-3 sm:px-6 py-2 sm:py-3 text-center whitespace-nowrap text-sm sm:text-base">
                                                @if($brandFilter)
                                                    @php
                                                        $selectedBrandItem = $itemsByBrand[$brandFilter] ?? null;
                                                    @endphp
                                                    @if($selectedBrandItem)
                                                        ₱ {{ number_format($selectedBrandItem->price, 2, '.', ',') }}
                                                    @else
                                                        ₱ 0.00
                                                    @endif
                                                @else
                                                    <select class="bg-gray-50 border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full py-1 px-2 sm:p-2.5" title="Price per branch">
                                                        @foreach($brands as $brand)
                                                            @php
                                                                $brandItem = $itemsByBrand[$brand->id] ?? null;
                                                            @endphp
                                                            @if($brandItem)
                                                                <option value="{{ $brand->id }}">{{ $brand->name }}: ₱{{ number_format($brandItem->price, 2, '.', ',') }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                            
                                            {{-- Acquisition Cost --}}
                                            <td class="px-3 sm:px-6 py-2 sm:py-3 text-center whitespace-nowrap text-sm sm:text-base">
                                                @if($brandFilter)
                                                    @php
                                                        $selectedBrandItem = $itemsByBrand[$brandFilter] ?? null;
                                                    @endphp
                                                    @if($selectedBrandItem)
                                                        ₱ {{ number_format($selectedBrandItem->food_cost, 2, '.', ',') }}
                                                    @else
                                                        ₱ 0.00
                                                    @endif
                                                @else
                                                    <select class="bg-gray-50 border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full py-1 px-2 sm:p-2.5" title="Acquisition cost per brand">
                                                        @foreach($brands as $brand)
                                                            @php
                                                                $brandItem = $itemsByBrand[$brand->id] ?? null;
                                                            @endphp
                                                            @if($brandItem)
                                                                <option value="{{ $brand->id }}">{{ $brand->name }}: ₱{{ number_format($brandItem->food_cost, 2, '.', ',') }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                            
                                            {{-- Actions --}}
                                                            <td class="px-6 py-3 text-center whitespace-nowrap">
                                                                {{-- @if ($menu->is_combo != 1)
                                                                    <a type="button" data-modal-target="moveMenuModal" data-modal-toggle="moveMenuModal" data-slug="{{ $menu->slug }}" data-quantity="{{ $menu->quantity }}" class="text-sm font-semibold text-teal-600 moveButton hover:underline">Move</a> | 
                                                                @endif --}}
                            
                                                                <a href="{{ url('/menu/edit/'.$firstItem->slug) }}" class="text-sm font-semibold text-blue-600 editButton hover:underline">Edit</a>
                                                                
                                                                @if(Auth::user()->role == 1 || Auth::user()->role == 3)
                                                                    | 

                                                                    @if ($firstItem->is_combo != 1)
                                                                        <a type="button" data-modal-target="disposeModal" data-modal-toggle="disposeModal" data-slug="{{ $firstItem->slug }}"data-name="{{ $firstItem->name }}"  class="text-sm font-semibold text-orange-500 cursor-pointer disposeButton hover:underline">Waste</a> | 
                                                                    @endif
                                                                    
                                                                    <a type="button" data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" data-slug="{{ $firstItem->slug }}" class="text-sm font-semibold text-red-600 cursor-pointer deleteButton hover:underline">Delete</a>
                                                                @endif
                                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
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
        
        function updateExportLinkFromFilter() {
            const branchId = branchSelectIndex.value;

            console.log('Selected branch:', branchId);

            if (branchId !== '') {
                exportlink.href = `{{ route('menu.export', ['branch' => 'BRANCH_ID']) }}`
                    .replace('BRANCH_ID', branchId);
            } else {
                exportlink.href = `{{ route('menu.export') }}`;
            }
        }

        if (branchSelect) {
            branchSelect.addEventListener('change', updateDownloadLink);
        }
        
        if (branchSelectIndex) {
            branchSelectIndex.addEventListener('change', function() {
                updateExportLinkFromFilter();
                // Mark as manual submit and submit the form
                isManualSubmit = true;
                document.getElementById('branchFilterForm').submit();
            });
        }
        
        // Initialize on load - DON'T submit form on initial load
        updateDownloadLink();
        updateExportLinkFromFilter();
        
        // Category filter auto-submit
        $('#searchSelect').on('change', function() {
            updateExportLink();
            document.getElementById('filterForm').submit();
        });

        // Branch filter auto-submit
        $('#branchFilter').on('change', function() {
            updateExportLink();
            document.getElementById('filterForm').submit();
        });

        // Clear button functionality
        $('#clearButton').click(function(){
            $('#searchInput').val('');
            // Trigger search immediately when clearing
            document.getElementById('filterForm').submit();
        });

        // Search button is now part of the form, so no special handler needed
        // Auto-search on Enter key
        $('#searchInput').on('keydown', function(event) {
            if (event.keyCode === 13) {
                clearTimeout(searchTimeout); // Clear any pending timeout
                document.getElementById('filterForm').submit();
                event.preventDefault();
            }
        });

        // Auto-search on input with debounce (500ms delay)
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
            const rows = $('#menuTable tbody tr');
            console.log('Found', rows.length, 'rows to filter');

            rows.each(function(index) {
                const row = $(this);
                let shouldShow = true;

                if (filterType === 'all') {
                    shouldShow = true;
                } else {
                    // Get the stock quantity from the row
                    const stockCell = row.find('td:nth-child(4)'); // Stock column
                    console.log('Row', index, 'stock cell text:', stockCell.text().trim());
                    let quantity = 0;
                    let reorderPoint = 0;

                    // Check if there's a select dropdown (multiple branches) or direct text
                    const selectElement = stockCell.find('select');
                    if (selectElement.length > 0) {
                        console.log('Row', index, 'has select dropdown');
                        // Multiple branches - get reorder point first
                        const reorderCell = row.find('td:nth-child(5)'); // Reorder point column
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
                        let hasMatchingStock = false;
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
                                        hasMatchingStock = true;
                                        console.log('Row', index, 'matches low stock criteria for', branchName);
                                    } else if (filterType === 'out' && branchQuantity === 0) {
                                        hasMatchingStock = true;
                                        console.log('Row', index, 'matches out of stock criteria for', branchName);
                                    }
                                }
                            }
                        });
                        shouldShow = hasMatchingStock;
                        console.log('Row', index, 'multi-branch shouldShow:', shouldShow);
                    } else {
                        console.log('Row', index, 'single branch view');
                        // Single branch - get the text content
                        const quantityText = stockCell.text().trim();
                        quantity = parseInt(quantityText.replace(/,/g, '')) || 0;
                        console.log('Row', index, 'parsed quantity:', quantity);

                        // Get reorder point
                        const reorderCell = row.find('td:nth-child(5)'); // Reorder point column
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
                }

                console.log('Row', index, 'final shouldShow:', shouldShow);
                if (shouldShow) {
                    row.show();
                } else {
                    row.hide();
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
        $(document).on('click', '.deleteButton', function(){
            var slug = $(this).data('slug');
            $('.deleteConfirm').prop('href', `{{ url('/menu/delete/${slug}') }}`);
        });

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

        $('.addServing').click(function(event){
            var $target = $(event.target);

            // If clicking any interactive element inside the row/card, do NOT open the sidebar
            if ($target.is('select, option, input, textarea, button, a') || $target.closest('select, option, input, textarea, button, a, .select-btn, .content, .listOption, .category-select, .branch-input').length > 0) {
                return; // allow normal control behavior
            }

            // Also ignore clicks on specific action links/buttons
            if ($target.is('.deleteButton, .editButton, .disposeButton, .moveButton') || $target.closest('.deleteButton, .editButton, .disposeButton, .moveButton').length) {
                return;
            }

            // Find the slug from the branch data columns (right side)
            var slug = $(this).find('td:has(span[data-slug])').first().find('span').data('slug');
            var is_combo = $(this).data('is_combo');

            // If no slug found in branch columns, try to find it elsewhere
            if (!slug) {
                // You might need to adjust this based on your actual data structure
                slug = $(this).closest('tr').find('span[data-slug]').first().data('slug');
            }

            if (slug) {
                var _token = $('input[name="_token"]').val();
                $.ajax({
                    url:"{{ route('menu.view') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        is_combo : is_combo,
                        _token: _token
                    },
                    success:function(result){
                        $('#menuName').html(result.name);
                        $('#ingredients').html(result.ingredients);
                        $('#servings').val(result.servings);
                        $('#slug').val(slug);

                        // Render per-brand stock inputs (allows setting quantity per brand)
                        //ken 250623001 ken updated here - make reduce button red
                        $('#brandStocks').empty();
                        if (result.brands && result.brands.length) {
                            result.brands.forEach(function(brand) {
                                var stock = parseFloat(brand.quantity);
                                if (isNaN(stock)) stock = 0;
                                var formattedStock = stock.toFixed(2);

                                $('#brandStocks').append(`
                                    <div class="grid gap-2 p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm font-semibold text-gray-700">${brand.brand_name}</div>
                                            <div class="text-xs text-gray-500">Current: <span class="font-medium">${formattedStock}</span></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="brand-qty-btn brand-qty-decrease inline-flex items-center justify-center w-9 h-9 text-white bg-red-500 rounded-lg hover:bg-red-600 focus:outline-none" title="Decrease">
                                                <i class="uil uil-minus"></i>
                                            </button>
                                            <input type="text" name="brand_stock[${brand.brand_id}]" value="" placeholder="0.00" class="brand-stock-input flex-1 px-2 py-2 text-center text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500" autocomplete="off">
                                            <button type="button" class="brand-qty-btn brand-qty-increase inline-flex items-center justify-center w-9 h-9 text-white bg-blue-500 rounded-lg hover:bg-blue-600 focus:outline-none" title="Increase">
                                                <i class="uil uil-plus"></i>
                                            </button>
                                            <span class="text-xs text-gray-500">pcs</span>
                                        </div>
                                    </div>
                                `);
                            });
                        }

                        $('#addServingButton').click();
                        $('#addDiv').html(`
                        <div id="ing1" class="flex flex-row mb-5 gap-x-3">
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
                                            <li data-id="" data-name="" data-idnum="1" class="flex items-center pl-3 leading-9 rounded-md cursor-pointer h-9 hover:bg-gray-300">None</li>
                                            @foreach ($items as $item)
                                                <li data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-unit="{{ $item->unit }}" data-idnum="1" class="flex items-center pl-3 leading-9 rounded-md cursor-pointer h-9 hover:bg-gray-300">{{ $item->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <input type="hidden" name="item1" value="">
                                </div>
                            </div>
                            <div class="w-2/5">
                                <input type="text" id="quantity1" name="quantity1" value="{{ old('quantity') }}" class="block w-full px-2 text-center text-gray-900 border border-gray-300 rounded-lg quantity h-9 bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                            </div>
                            <div class="flex w-1/5">
                                <div id="unit1" class="w-1/2 text-lg leading-9"></div>
                                <button type="button" data-thisid="ing1" class="w-1/2 text-center removeButton"><i class="text-3xl text-red-500 uil uil-minus-circle"></i></button>
                            </div>
                        </div>`);
                    }
                })
            }
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

        $('.disposeButton').click(function(){
            var slug = $(this).data('slug');
            var name = $(this).data('name');
            $('#disposeSlug').val(slug);
            $('#disposeName').html(name);
        });

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
    });
</script>
</x-app-layout>
