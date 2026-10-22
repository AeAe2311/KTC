<x-app-layout>

    @section('page_title', 'EXPENSES - ADD')

    {{-- DELETE MODAL --}}
        
        <!-- Main modal -->
        <div id="itemDeleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="font-semibold text-gray-900 flex items-center">
                            <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-red-700"></i>
                            <span class="text-red-700 text-base md:text-lg lg:text-xl">Delete Item</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemDeleteModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
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
                        <a type="button" href="" class="deleteConfirm w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>
                        <button data-modal-hide="itemDeleteModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- DELETE MODAL END --}}

    <div class="p-3 lg:pt-3 {{ (Auth::user()->role == 1) ? 'lg:ml-64' : '' }}">
        <div id="contentDiv" class="p-2 w-full bg-gray-100">
            <div class="flex gap-2 h-[96vh]">
                <!-- LEFT SIDE -->
                <div class="bg-white overflow-hidden shadow-md rounded-lg p-4 w-1/3">
                    {{-- CONTROLS --}}
                        <div class="mb-3">
                            
                        </div>
                    {{-- CONTROLS END --}}

                    <div class="">
                        {{-- TABLE --}}
                            <div class="hidden md:block ">
                                <div id="inventoryTable" class="overflow-auto w-full shadow-xl sm:rounded-lg  p-3 h-[90vh]">
                                    <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" id="costForm" class="grid">
                                        @csrf
                                        <!-- TITLE -->
                                        <div class="flex justify-center mb-2">
                                            <h1 class="text-2xl font-extrabold">
                                                Cost of Sales
                                            </h1>
                                        </div>
                                        <!-- END OF TITLE -->


                                        <!-- CATEGORY -->
                                        <div class="mb-2 w-full">
                                            <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Category<span class="text-red-500">*</span></label>
                                            <div class="wrapper w-full relative">
                                                <div class="select-btn flex items-center justify-between rounded-md bg-gray-100 border border-gray-300 p-2 h-9 cursor-pointer">
                                                    <span></span>
                                                    <i class="uil uil-angle-down text-2xl transition-transform duration-300"></i>
                                                </div>
                                                <div class="content bg-gray-100 mt-1 rounded-md p-3 hidden absolute w-full z-50">
                                                    <div class="search relative">
                                                        <i class="uil uil-search absolute left-3 leading-9 text-gray-500"></i>
                                                        <input type="text" class="selectSearch w-full leading-9 text-gray-700 rounded-md pl-9 outline-none h-9" placeholder="Search">
                                                    </div>
                                                    <ul class="listOption options mt-2 max-h-52 overflow-y-auto">
                                                        <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="1" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">None</li>
                                                        @foreach ($expcats as $expcat)
                                                            <li data-id="{{ $expcat->id }}" data-name="{{ $expcat->name }}" data-unit="{{ $expcat->id }}" data-is_menu="{{ $expcat->name }}" data-idnum="1" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">{{ $expcat->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <input type="hidden" name="category" value="">
                                                <div class="field-error-bubble">
                                                    <span class="bubble-icon">!</span>
                                                    <span>Please pick from the choices.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END OF CATEGORY -->

                                        <!-- SUPPLIER -->
                                        <div class="mb-2 w-full">
                                            <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Supplier <span class="text-red-500">*</span></label>
                                            
                                            <div class="wrapper w-full relative">
                                                <div class="select-btn flex items-center justify-between rounded-md bg-gray-100 border border-gray-300 p-2 h-9 cursor-pointer">
                                                    <span></span>
                                                    <i class="uil uil-angle-down text-2xl transition-transform duration-300"></i>
                                                </div>
                                                <div class="content bg-gray-100 mt-1 rounded-md p-3 hidden absolute w-full z-50">
                                                    <div class="search relative">
                                                        <i class="uil uil-search absolute left-3 leading-9 text-gray-500"></i>
                                                        <input type="text" class="selectSearch w-full leading-9 text-gray-700 rounded-md pl-9 outline-none h-9" placeholder="Search">
                                                    </div>
                                                    <ul class="listOption options mt-2 max-h-52 overflow-y-auto">
                                                        <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="1" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">None</li>
                                                        @foreach ($supps as $supp)
                                                            <li data-id="{{ $supp->id }}" data-name="{{ $supp->name }}" data-unit="{{ $supp->id }}" data-is_menu="{{ $supp->name }}" data-idnum="1" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">{{ $supp->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <input type="hidden" name="supplier" value="">
                                                <div class="field-error-bubble">
                                                    <span class="bubble-icon">!</span>
                                                    <span>Please pick from the choices.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END OF SUPPLIER -->

                                        <!-- DATE -->

                                        <div class="mb-6">
                                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                                            <div class="relative max-w-full">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                                </div>
                                                <input datepicker type="text" name="dateAdd" value="{{ date('m/d/Y') }}" max="{{ date('m/d/Y') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                                            </div>
                                        </div>
                                        <!-- END OF DATE -->

                                        <div class="grid grid-cols-2 gap-2">
                                            <!-- QUANTITY -->
                                            <div class="mb-2">
                                                <label for="quantity" class="block text-sm font-medium text-gray-900 lg:text-base mb-2">Quantity<span class="text-red-500">*</span></label>
                                                <input type="text" id="quantity" name="quantity" value="{{ old('quantity') }}" class="inputNumber block w-full py-2.5 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                            </div>
                                            <!-- END OF QUANTITY -->
                                            <!-- UNIT -->
                                            <div class="mb-2">
                                                <label for="unit" class="block text-sm font-medium text-gray-900 lg:text-base mb-2">Unit<span class="text-red-500">*</span></label>
                                                <select id="unit" name="unit" value="{{ old('unit') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                                                    <option value="tsp">teaspoon (tsp)</option>
                                                    <option value="tbsp">tablespoon (tbsp)</option>
                                                    <option value="kg">kilogram (kg)</option>
                                                    <option value="g">gram (g)</option>
                                                    <option value="mg">milligram (mg)</option>
                                                    <option value="oz">ounce (oz)</option>
                                                    <option value="lb">pound (lb)</option>
                                                    <option value="gal">gallon (gal)</option>
                                                    <option value="L">liter (L)</option>
                                                    <option value="mL">milliliter (mL)</option>
                                                    <option value="c">cup (c)</option>
                                                    <option value="ea">each (ea)</option>
                                                    <option value="doz">dozen (doz)</option>
                                                </select>
                                            </div>
                                            <!-- END OF UNIT -->
                                        </div>

                                        <!-- EXPENSES TYPE -->
                                        <div class="mb-2">
                                            <label for="exp_type" class="hidden text-sm font-medium text-gray-900 lg:text-base">Expenses Type<span class="text-red-500">*</span></label>
                                            <input type="text" id="exp_type" name="exp_type" value="{{ old('exp_type', 0) }}" class="hidden w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                                        </div>
                                        <!-- END OF EXPENSES TYPE -->


                                        <!-- ITEM NAME -->
                                        <div class="mb-2">
                                            <label for="name" class="block text-sm font-medium text-gray-900 lg:text-base">Item Name<span class="text-red-500">*</span></label>
                                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                        </div>
                                        <!-- END OF ITEM NAME -->

                                        <div class="grid grid-cols-2 gap-2">
                                            <!-- UNIT PRICE -->
                                            <div class="mb-2">
                                                <label for="unit_price" class="block text-sm font-medium text-gray-900 lg:text-base">Unit Price<span class="text-red-500">*</span></label>
                                                <input type="text" id="unit_price" name="unit_price" value="{{ old('unit_price') }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                            </div>
                                            <!-- END OF UNIT PRICE -->

                                            <!-- PRICE -->
                                            <div class="mb-2">
                                                <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Amount <span class="text-red-500">*</span></label>
                                                <input type="text" id="price" name="price" value="{{ old('price') }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                            </div>
                                            <!-- END OF PRICE -->
                                        </div>
                                        
                                        <!-- REMARKS -->
                                        <div class="mb-2">
                                            <label for="other_name" class="block text-sm font-medium text-gray-900 lg:text-base">Remarks <span class="text-red-500"></span></label>
                                            <input type="text" id="other_name" name="other_name" value="{{ old('other_name') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                                        </div>
                                        <!-- END OF REMARKS -->

                                        <!-- MODE OF PAYMENT -->
                                        <div class="mb-5">
                                            <label for="status" class="block text-sm font-medium text-gray-900 lg:text-base">Mode of Payment<span class="text-red-500">*</span></label>
                                            <select id="status" name="status" value="{{ old('status') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                                                <option value="cash">Cash</option>
                                                <option value="7days">7days</option>
                                                <option value="15days">15days</option>
                                                <option value="30days">30days</option>
                                            </select>
                                        </div>
                                        <!-- END OF MODE OF PAYMENT -->
                                        <!-- STATUS -->
                                        <div class="mb-6">
                                            <label for="" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                                            <div class="flex items-center gap-x-10">
                                                <div class="flex items-center">
                                                    <input checked id="paid" type="radio" value="1" name="payment_class" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                    <label for="paid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Paid</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="unpaid" type="radio" value="0" name="payment_class" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                    <label for="unpaid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Unpaid</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END OF STATUS -->

                                        <div class="flex justify-between">
                                            <a href="{{ Auth::user()->role == 5 ? route('e_request.index') : route('expenses.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                                            <button type="submit" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold">ADD</button>
                                        </div>
                                    </form>






                                    <!-- NEW FORM -->
                                    <form action="{{ route('expenses.storeOther') }}" method="POST" enctype="multipart/form-data" id="operatingForm" class="grid">
                                        @csrf
                                        <!-- TITLE -->
                                        <div class="flex justify-center mb-2">
                                            <h1 class="text-2xl font-extrabold">
                                                Other Operating Expenses
                                            </h1>
                                        </div>
                                        <!-- END OF TITLE -->


                                        <!-- CATEGORY -->
                                        <div class="mb-2 w-full">
                                            <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Category<span class="text-red-500">*</span></label>
                                            <div class="wrapper w-full relative">
                                                <div class="select-btn flex items-center justify-between rounded-md bg-gray-100 border border-gray-300 p-2 h-9 cursor-pointer">
                                                    <span></span>
                                                    <i class="uil uil-angle-down text-2xl transition-transform duration-300"></i>
                                                </div>
                                                <div class="content bg-gray-100 mt-1 rounded-md p-3 hidden absolute w-full z-50">
                                                    <div class="search relative">
                                                        <i class="uil uil-search absolute left-3 leading-9 text-gray-500"></i>
                                                        <input type="text" class="selectSearch w-full leading-9 text-gray-700 rounded-md pl-9 outline-none h-9" placeholder="Search">
                                                    </div>
                                                    <ul class="listOption options mt-2 max-h-52 overflow-y-auto">
                                                        <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="1" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">None</li>
                                                        @foreach ($expcats as $expcat)
                                                            <li data-id="{{ $expcat->id }}" data-name="{{ $expcat->name }}" data-unit="{{ $expcat->id }}" data-is_menu="{{ $expcat->name }}" data-idnum="1" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">{{ $expcat->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <input type="hidden" name="category1" value="">
                                                <div class="field-error-bubble">
                                                    <span class="bubble-icon">!</span>
                                                    <span>Please pick from the choices.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END OF CATEGORY -->

                                        <!-- SUPPLIER -->
                                        <div class="mb-2 w-full">
                                            <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Supplier <span class="text-red-500">*</span></label>
                                            
                                            <div class="wrapper w-full relative">
                                                <div class="select-btn flex items-center justify-between rounded-md bg-gray-100 border border-gray-300 p-2 h-9 cursor-pointer">
                                                    <span></span>
                                                    <i class="uil uil-angle-down text-2xl transition-transform duration-300"></i>
                                                </div>
                                                <div class="content bg-gray-100 mt-1 rounded-md p-3 hidden absolute w-full z-50">
                                                    <div class="search relative">
                                                        <i class="uil uil-search absolute left-3 leading-9 text-gray-500"></i>
                                                        <input type="text" class="selectSearch w-full leading-9 text-gray-700 rounded-md pl-9 outline-none h-9" placeholder="Search">
                                                    </div>
                                                    <ul class="listOption options mt-2 max-h-52 overflow-y-auto">
                                                        @foreach ($supps as $supp)
                                                            <li data-id="{{ $supp->id }}" data-name="{{ $supp->name }}" data-unit="{{ $supp->id }}" data-is_menu="{{ $supp->name }}" data-idnum="1" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">{{ $supp->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <input type="hidden" name="supplier1" value="">
                                                <div class="field-error-bubble">
                                                    <span class="bubble-icon">!</span>
                                                    <span>Please pick from the choices.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END OF SUPPLIER -->

                                        <!-- DATE -->

                                        <div class="mb-6">
                                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                                            <div class="relative max-w-full">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                                </div>
                                                <input datepicker type="text" name="dateAdd1" value="{{ date('m/d/Y') }}" max="{{ date('m/d/Y') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                                            </div>
                                        </div>
                                        <!-- END OF DATE -->

                                        <div class="grid grid-cols-2 gap-2">
                                            <!-- QUANTITY -->
                                            <div class="mb-2">
                                                <label for="quantity1" class="block text-sm font-medium text-gray-900 lg:text-base mb-2">Quantity<span class="text-red-500">*</span></label>
                                                <input type="text" id="quantity1" name="quantity1" value="{{ old('quantity1') }}" class="inputNumber block w-full py-2.5 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                            </div>
                                            <!-- END OF QUANTITY -->
                                            <!-- UNIT -->
                                            <div class="mb-2">
                                                <label for="unit" class="block text-sm font-medium text-gray-900 lg:text-base mb-2">Unit<span class="text-red-500">*</span></label>
                                                <select id="unit" name="unit1" value="{{ old('unit') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                                                    <option value="tsp">teaspoon (tsp)</option>
                                                    <option value="tbsp">tablespoon (tbsp)</option>
                                                    <option value="kg">kilogram (kg)</option>
                                                    <option value="g">gram (g)</option>
                                                    <option value="mg">milligram (mg)</option>
                                                    <option value="oz">ounce (oz)</option>
                                                    <option value="lb">pound (lb)</option>
                                                    <option value="gal">gallon (gal)</option>
                                                    <option value="L">liter (L)</option>
                                                    <option value="mL">milliliter (mL)</option>
                                                    <option value="c">cup (c)</option>
                                                    <option value="ea">each (ea)</option>
                                                    <option value="doz">dozen (doz)</option>
                                                </select>
                                            </div>
                                            <!-- END OF UNIT -->
                                        </div>

                                        <!-- EXPENSES TYPE -->
                                        <div class="mb-2">
                                            <label for="exp_type1" class="hidden text-sm font-medium text-gray-900 lg:text-base">Expenses Type<span class="text-red-500">*</span></label>
                                            <input type="text" id="exp_type1" name="exp_type1" value="{{ old('exp_type1', 1) }}" class="hidden w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                                        </div>
                                        <!-- END OF EXPENSES TYPE -->


                                        <!-- ITEM NAME -->
                                        <div class="mb-2">
                                            <label for="name1" class="block text-sm font-medium text-gray-900 lg:text-base">Item Name<span class="text-red-500">*</span></label>
                                            <input type="text" id="name1" name="name1" value="{{ old('name1') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                        </div>
                                        <!-- END OF ITEM NAME -->

                                        <div class="grid grid-cols-2 gap-2">
                                            <!-- UNIT PRICE -->
                                            <div class="mb-2">
                                                <label for="unit_price1" class="block text-sm font-medium text-gray-900 lg:text-base">Unit Price<span class="text-red-500">*</span></label>
                                                <input type="text" id="unit_price1" name="unit_price1" value="{{ old('unit_price1') }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                            </div>
                                            <!-- END OF UNIT PRICE -->

                                            <!-- PRICE -->
                                            <div class="mb-2">
                                                <label for="price1" class="block text-sm font-medium text-gray-900 lg:text-base">Amount <span class="text-red-500">*</span></label>
                                                <input type="text" id="price1" name="price1" value="{{ old('price1') }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                            </div>
                                            <!-- END OF PRICE -->
                                        </div>
                                        
                                        <!-- REMARKS -->
                                        <div class="mb-2">
                                            <label for="other_name1" class="block text-sm font-medium text-gray-900 lg:text-base">Remarks <span class="text-red-500"></span></label>
                                            <input type="text" id="other_name1" name="other_name1" value="{{ old('other_name1') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                                        </div>
                                        <!-- END OF REMARKS -->

                                        <!-- MODE OF PAYMENT -->
                                        <div class="mb-5">
                                            <label for="status" class="block text-sm font-medium text-gray-900 lg:text-base">Mode of Payment<span class="text-red-500">*</span></label>
                                            <select id="status" name="status1" value="{{ old('status1') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                                                <option value="cash">Cash</option>
                                                <option value="7days">7days</option>
                                                <option value="15days">15days</option>
                                                <option value="30days">30days</option>
                                            </select>
                                        </div>
                                        <!-- END OF MODE OF PAYMENT -->

                                        <!-- STATUS -->
                                        <div class="mb-6">
                                            <label for="" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                                            <div class="flex items-center gap-x-10">
                                                <div class="flex items-center">
                                                    <input checked id="paid" type="radio" value="1" name="payment_class1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                    <label for="paid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Paid</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="unpaid" type="radio" value="0" name="payment_class1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                    <label for="unpaid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Unpaid</label>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- END OF STATUS -->

                                        <div class="flex justify-between">
                                            <a href="{{ Auth::user()->role == 5 ? route('e_request.index') : route('expenses.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                                            <button type="submit" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold">ADD</button>
                                        </div>
                                    </form>
                                    <!-- END OF NEW FORM -->
                                </div>
                            </div>
                        {{-- TABLE END --}}

                       
                    </div>
                </div>
            <!-- END OF LEFT SIDE -->
             
            <!-- RIGHT SIDE -->
            <div class="bg-white overflow-x-hidden shadow-md rounded-lg p-4 w-2/3">
                {{-- CONTROLS --}}
                    <div class="mb-3">
                        <div class="md:grid md:grid-cols-2 flex flex-col">
                            
                        </div>
                    </div>
                {{-- CONTROLS END --}}

                <div class="">
                    {{-- TABLE --}}
                        <div class="hidden md:block ">
                            <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg ">

                                    <div class=" gap-3 flex">
  
                                    </div>

                                    <table class="w-full text-sm text-left text-gray-500 ">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                    Classification
                                                </th>
                                                <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                    Qty
                                                </th>
                                                <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    Unit
                                                </th >
                                                <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                    Name
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                    Unit Price
                                                </th>
                                                <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    Amount
                                                </th>
                                                <!-- <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    Remarks
                                                </th> -->
                                                <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    MOP
                                                </th>
                                                <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    Status
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="">
                                            @foreach ($inventories as $inventory)
                                                <tr class="bg-white border-b">
                                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                        {{ $inventory->exp_type == 1 ? 'Other Expenses' : 'Cost of Sales' }}
                                                    </th>
                                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                        {{ $inventory->quantity }}
                                                    </th>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        {{ $inventory->unit }}
                                                    </td>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        {{ $inventory->remarks}}
                                                    </td>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        {{ $inventory->unit_price }}
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 text-center whitespace-nowrap row-value">
                                                    ₱ {{ number_format($inventory->amount, 2, '.', ',') }}  
                                                    </td>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        {{ $inventory->is_paid }}
                                                    </td>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap
                                                        {{ $inventory->payment_class == 1 ? 'text-green-500' : 'text-red-500' }}">
                                                        {{ $inventory->payment_class == 1 ? 'Paid' : 'Unpaid'}}
                                                        
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        <a type="button" data-slug="{{ $inventory->id }}" class="cartDeleteButton text-red-600 hover:underline font-semibold text-sm cursor-pointer">Delete</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="w-full flex justify-between z-0">
                        
                        <div></div>

                        <div class="text-m p-4">
                            <div id="total-sum" class="px-6 py-4 text-center text-lg whitespace-nowrap">₱ 0.00</div>
                            <div class="{{($inventoriesCount <= 0 ) ? 'hidden':''}}">  
                            <div class="flex justify-between mb-2 ">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="vatBtn" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">VAT &nbsp; &nbsp; &nbsp;</span>
                                </label>

                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="taxBtn" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Withholding Tax: &nbsp; &nbsp;</span>
                                    <input type="number" id="wtaxPercent" name = "wtaxPercent" class="border-0 border-b px-1 py-1 text-sm" placeholder="" min="0" step="0.01" style="width: 30px; border-bottom: 1px solid #000;"> %</input>

                                </label>
                            </div>

                            <!-- Additional Fields for Total Sales, VAT, Withholding Tax, and Amount Due -->
                            <div class="flex justify-between mb-2">
                                <label class="mr-5">Total Sales ( VAT Inclusive ) : </label>
                                <div id="totalDiv" data-amount="0.00">₱ 0.00</div>
                                <input type="hidden" name="total_value" id="total_value">
                            </div>
                            <div class="flex justify-between mb-2">
                                <label for="">Less VAT : </label>
                                <div id="vatDiv" data-amount="0.00">₱ 0.00</div>
                                <input type="hidden" name="vat_value" id="vat_value">
                            </div>
                            <div class="flex justify-between mb-2">
                                <label for="">Less Withholding Tax : </label>
                                <div id="wtaxDiv" data-amount="0.00">₱ 0.00</div>
                                <input type="hidden" name="wtax_value" id="wtax_value">
                            </div>
                            <div class="flex justify-between mb-2">
                                <label for="">TOTAL AMOUNT DUE : </label>
                                <div id="amountDue" data-amount="0.00">₱ 0.00</div>
                                <input type="hidden" name="amount_due" id="amount_due">
                            </div>
                            
                                        </div>
                            
                        </div>

                    </div>
                                    <div id="expenseDiv" class="flex justify-center mt-5 {{($inventoriesCount <= 0 ) ? 'hidden':''}}">
                                            
                                    <a href="#" type="" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center submit-button">SUBMIT</a>
                                        </div>
                            </div>
                                
                        </div>
                    {{-- TABLE END --}}
                </div>
            </div>
            <!-- END OF RIGHT SIDE -->
            </div>
        </div>
     </div>

<style>
 
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Disable the number input arrows in Firefox */
input[type="number"] {
    -moz-appearance: textfield;
}

#total-sum {
    display: none;
}

/* =========================================================
CUSTOM VALIDATION BUBBLE FOR FAKE DROPDOWNS
========================================================= */
.field-error-bubble {
    display: none;
    position: absolute;
    left: 0;
    top: 100%;
    margin-top: 6px;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    color: #111827;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    z-index: 100;
    white-space: nowrap;
    align-items: center;
    gap: 8px;
}
.field-error-bubble::before {
    content: '';
    position: absolute;
    top: -6px;
    left: 14px;
    width: 10px;
    height: 10px;
    background: #fff;
    border-left: 1px solid #d1d5db;
    border-top: 1px solid #d1d5db;
    transform: rotate(45deg);
}
.field-error-bubble .bubble-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    background: #f97316;
    color: #fff;
    border-radius: 4px;
    font-weight: bold;
    font-size: 13px;
    flex-shrink: 0;
}
.field-error-bubble.show {
    display: inline-flex;
}
.wrapper.has-error .select-btn {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 1px #ef4444;
}

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // Function to calculate the total of row values
    function sumValues(selector) {
        let total = 0;
        document.querySelectorAll(selector).forEach(function (element) {
            const rawValue = element.textContent.replace('₱', '').replace(/,/g, '').trim();
            const numericValue = parseFloat(rawValue);
            if (!isNaN(numericValue)) {
                total += numericValue;
            }
        });
        return total;
    }

    // Get references to elements
    const totalDiv = document.getElementById('totalDiv');
    const vatDiv = document.getElementById('vatDiv');
    const wtaxDiv = document.getElementById('wtaxDiv');
    const amountDueDiv = document.getElementById('amountDue');

    const vatBtn = document.getElementById('vatBtn');
    const taxBtn = document.getElementById('taxBtn');
    const wtaxPercentInput = document.getElementById('wtaxPercent');

    const totalValueInput = document.getElementById('total_value');
    const vatValueInput = document.getElementById('vat_value');
    const wtaxValueInput = document.getElementById('wtax_value');
    const amountDueInput = document.getElementById('amount_due');

    // Function to update the values
    function updateValues() {
        const total = sumValues('.row-value');

        // Prepare data for server
        const vatChecked = vatBtn.checked;
        const taxChecked = taxBtn.checked;
        const wtaxPercent = parseFloat(wtaxPercentInput.value) || 0;

        const requestData = {
            total: total,
            vatChecked: vatChecked,
            taxChecked: taxChecked,
            wtaxPercent: wtaxPercent,
        };

        // Send AJAX request to process the calculation
        fetch('/update-tax-values', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(requestData),
        })
            .then((response) => response.json())
            .then((data) => {
                totalDiv.textContent = `₱ ${data.total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                vatDiv.textContent = `₱ ${data.vatAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                wtaxDiv.textContent = `₱ ${data.wtaxAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                amountDueDiv.textContent = `₱ ${data.totalWithTax.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                totalValueInput.value = data.total.toFixed(2);
                vatValueInput.value = data.vatAmount.toFixed(2);
                wtaxValueInput.value = data.wtaxAmount.toFixed(2);
                amountDueInput.value = data.totalWithTax.toFixed(2);
            })
            .catch((error) => console.error('Error:', error));
    }

    vatBtn.addEventListener('change', updateValues);
    taxBtn.addEventListener('change', updateValues);
    wtaxPercentInput.addEventListener('input', updateValues);

    updateValues();
});

</script>






 <script>

        $(document).ready(function() {
            // for hiding two forms
            $('#costForm').hide();
            $('#operatingForm').hide();

            // Get URL query parameters
            const urlParams = new URLSearchParams(window.location.search);
            const formType = urlParams.get('form');

            // Show the appropriate form based on the 'form' query parameter
            if (formType === 'cost') {
                $('#costForm').show();
            } else if (formType === 'operating') {
                $('#operatingForm').show();
            }
            // end of hiding two forms

            // =========================================================
            // CUSTOM DROPDOWN VALIDATION
            // =========================================================
            function validateCustomDropdown($form) {
                let valid = true;

                $form.find('.wrapper').each(function () {
                    const $wrapper = $(this);
                    const $hidden  = $wrapper.find('input[type="hidden"]');

                    if ($hidden.length === 0) return;

                    if ($hidden.val() === '') {
                        valid = false;
                        $wrapper.addClass('has-error');
                        $wrapper.find('.field-error-bubble').addClass('show');
                    } else {
                        $wrapper.removeClass('has-error');
                        $wrapper.find('.field-error-bubble').removeClass('show');
                    }
                });

                return valid;
            }

            // Clear error state when user picks from custom dropdown
            $(document).on('click', '.listOption li', function () {
                const $wrapper = $(this).closest('.wrapper');
                $wrapper.removeClass('has-error');
                $wrapper.find('.field-error-bubble').removeClass('show');
            });

            // Also clear when a real <select> is changed
            $(document).on('change', 'select', function () {
                $(this).closest('.wrapper').removeClass('has-error');
                $(this).closest('.wrapper').find('.field-error-bubble').removeClass('show');
            });
            
            // right side
            $('.submit-button').click(function(e) {
                e.preventDefault();

                const totalvalue = parseFloat($('#total_value').val());
                const eVat = parseFloat($('#vat_value').val());
                const ewtaxpercent = parseFloat($('#wtaxPercent').val());
                const eWtax = parseFloat($('#wtax_value').val());
                const eAmount = parseFloat($('#amount_due').val());

                console.log('total:', totalvalue);
                console.log('VAT:', eVat);
                console.log('Withholding Tax Percent:', ewtaxpercent);
                console.log('Withholding Tax:', eWtax);
                console.log('Amount Due:', eAmount);

                $.ajax({
                    url: '{{ route("expenses.cart_store") }}', 
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        e_total: totalvalue,
                        e_vat: eVat,
                        e_wtaxpercent: ewtaxpercent,
                        e_wtax: eWtax,
                        e_amount: eAmount
                    },
                    success: function(response) {
                        if (response.success) {
                            showSuccessToast(response.message || 'Data transferred successfully.');
                            setTimeout(function () {
                                window.location.href = "{{ Auth::user()->role == 5 ? route('e_request.index') : route('expenses.index') }}";
                            }, 1400);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });
            });
            // end of right side

            $(document).on('click', '.cartDeleteButton', function() {
                var slug = $(this).data('slug');
                var row = $(this).closest('tr');  // Get the row to remove
                $.ajax({
                    url: '{{ route("expenses.cart_delete") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        slug: slug
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove();  // Remove the deleted row from the DOM
                            location.reload();
                            alert(response.message);
                            // Check if the table is empty after deletion and hide the submit button
                            if ($('#inventoryTable tbody tr').length === 0) {
                                $('#expenseDiv').addClass('hidden');
                                
                            }
                        } else {
                            alert(response.message);
                            
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });
            });


            // Event listener for form submission
            $('#costForm').on('submit', function(event) {
                // Validate custom dropdowns first
                if (!validateCustomDropdown($(this))) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    return false;
                }

                event.preventDefault(); 
                
                var formData = {
                    _token: $('input[name="_token"]').val(), 
                    name: $('input[name="name"]').val(),
                    other_name: $('input[name="other_name"]').val(),
                    status: $('select[name="status"]').val(),
                    quantity: $('input[name="quantity"]').val(),
                    price: $('input[name="price"]').val(),
                    category: $('input[name="category"]').val(),
                    supplier: $('input[name="supplier"]').val(),
                    dateAdd: $('input[name="dateAdd"]').val(),
                    unit: $('select[name="unit"]').val(),
                    unit_price: $('input[name="unit_price"]').val(),
                    exp_type: $('input[name="exp_type"]').val(),
                    payment_class: $('input[name="payment_class"]:checked').val(),
                };

                $.ajax({
                    url: '{{ route("expenses.store") }}', 
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Clear form fields
                        $('input[name="quantity"]').val(''); 
                        $('input[name="unit"]').val(''); 
                        $('input[name="name"]').val(''); 
                        $('input[name="unit_price"]').val(''); 
                        $('input[name="price"]').val(''); 
                        $('input[name="status"]').val(''); 
                        $('input[name="other_name"]').val(''); 
                        
                        // $('input[name="status"]').prop('checked', false); 
                       
                        
                        // $('input[name="dateAdd"]').val(''); 
                        
                        // Update the inventory table (right side) dynamically
                        $('#inventoryTable tbody').empty(); // Clear the existing rows

                        // Loop through the response to append new rows
                        $.each(response.inventories, function(index, inventory) {
                            $('#inventoryTable tbody').append(
                                '<tr class="bg-white border-b">' +
                                    '<th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">' + 
                                        (inventory.exp_type == 1 ? 'Other Expenses' : 'Cost of Sales') + 
                                    '</th>' +
                                    '<th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">' + inventory.quantity + '</th>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.unit + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.remarks + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.unit_price + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">₱ ' + parseFloat(inventory.amount).toFixed(2) + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.is_paid + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap ' + 
                                        (inventory.payment_class == 1 ? 'text-green-500' : 'text-red-500') + '">' + 
                                        (inventory.payment_class == 1 ? 'Paid' : 'Unpaid') + 
                                    '</td>'

                                    +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' +
                                        
                                        '<a type="button"  data-slug="' + inventory.id + '" class="cartDeleteButton text-red-600 hover:underline font-semibold text-sm cursor-pointer">Delete</a>' +
                                    '</td>' +
                                '</tr>'
                            );
                        });
                        $('#expenseDiv').removeClass('hidden');
                        location.reload();
                    },
                    error: function(xhr) {
                        // Handle errors
                    }
                });
            });

            $('#operatingForm').on('submit', function(event) {
                // Validate custom dropdowns first
                if (!validateCustomDropdown($(this))) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    return false;
                }

                event.preventDefault(); 
                
                var formData1 = {
                    _token: $('input[name="_token"]').val(), 
                    name: $('input[name="name1"]').val(),
                    other_name: $('input[name="other_name1"]').val(),
                    status: $('select[name="status1"]').val(),
                    quantity: $('input[name="quantity1"]').val(),
                    price: $('input[name="price1"]').val(),
                    category: $('input[name="category1"]').val(),
                    supplier: $('input[name="supplier1"]').val(),
                    dateAdd: $('input[name="dateAdd1"]').val(),
                    unit: $('select[name="unit1"]').val(),
                    unit_price: $('input[name="unit_price1"]').val(),
                    exp_type: $('input[name="exp_type1"]').val(),
                    payment_class: $('input[name="payment_class1"]:checked').val(),
                };

                $.ajax({
                    url: '{{ route("expenses.store") }}', 
                    type: 'POST',
                    data: formData1,
                    success: function(response) {
                        // Clear form fields
                        $('input[name="quantity1"]').val(''); 
                        $('input[name="unit1"]').val(''); 
                        $('input[name="name1"]').val(''); 
                        $('input[name="unit_price1"]').val(''); 
                        $('input[name="price1"]').val(''); 
                        $('input[name="status1"]').val(''); 
                        $('input[name="other_name1"]').val(''); 
                        
                        // $('input[name="status"]').prop('checked', false); 
                       
                        
                        // $('input[name="dateAdd"]').val(''); 
                        
                        // Update the inventory table (right side) dynamically
                        $('#inventoryTable tbody').empty(); // Clear the existing rows

                        // Loop through the response to append new rows
                        $.each(response.inventories, function(index, inventory) {
                            $('#inventoryTable tbody').append(
                                '<tr class="bg-white border-b">' +
                                    '<th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">' + 
                                        (inventory.exp_type == 1 ? 'Other Expenses' : 'Cost of Sales') + 
                                    '</th>'
                                    +
                                    '<th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">' + inventory.quantity + '</th>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.unit + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.remarks + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.unit_price + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">₱ ' + parseFloat(inventory.amount).toFixed(2) + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.is_paid + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap ' + 
                                        (inventory.payment_class == 1 ? 'text-green-500' : 'text-red-500') + '">' + 
                                        (inventory.payment_class == 1 ? 'Paid' : 'Unpaid') + 
                                    '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' +
                                        
                                        '<a type="button"  data-slug="' + inventory.id + '" class="cartDeleteButton text-red-600 hover:underline font-semibold text-sm cursor-pointer">Delete</a>' +
                                    '</td>' +
                                '</tr>'
                            );
                        });
                        $('#expenseDiv').removeClass('hidden');
                        location.reload();
                    },
                    error: function(xhr) {
                        // Handle errors
                    }
                });
            });


            


            // new
            
            $('.inputNumber').on('keypress keyup', function(event){
                var regex = /^[0-9.]+$/;
                var value = $(this).val() + String.fromCharCode(event.keyCode);
                if (!regex.test(value)) {
                event.preventDefault();
                return false;
                }
                if ((event.keyCode == 46) && ($(this).val().indexOf('.') >= 0)) {
                event.preventDefault();
                return false;
                }
            });
            
            $("#type").change(function(){
                if ($('#type').val() == 'Others'){
                    $("#type_txt").attr('type','text');
                    $('#type_txt').prop('required',true);
                } else {
                    $("#type_txt").attr('type','hidden');
                    
                    $('#type_txt').removeAttr('required');
                }
            }); 

            $('#navButton').click(function(){
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

            //List option Search
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
                var unit = $(this).data('unit');
                var is_menu = $(this).data('is_menu');
                var unitid = 'unit' + $(this).data('idnum');
                var qtyid = 'quantity' + $(this).data('idnum');

                if(id != ""){
                    $(this).closest('.wrapper').find('input').val(is_menu+','+id);
                    $(this).closest('.wrapper').find('.select-btn span').html(name);
                }else{
                    $(this).closest('.wrapper').find('input').val('');
                    $(this).closest('.wrapper').find('.select-btn span').html('');
                }
                $('.content').addClass('hidden');
                $('.selectSearch').val('');
                var value = $(".selectSearch").val().toLowerCase();
                searchFilter(value);

                $('#' + unitid).html(unit);
                $('#' + qtyid).val('');
                $('.uil-angle-down').removeClass('-rotate-180');

            });

            $(document).click(function() {
                $('.content').addClass('hidden');
                $('.uil-angle-down').removeClass('-rotate-180');
            });

            // end

        });
     </script>
     {{-- SUCCESS TOAST --}}
        <div id="successToast"
            class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div id="successToastCard"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 flex flex-col items-center gap-4
                        scale-95 opacity-0 transition-all duration-200">
                <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                    <i class="uil uil-check-circle text-4xl text-emerald-600"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-bold text-gray-900">Success</h3>
                    <p id="successToastMessage" class="text-sm text-gray-500 mt-1">Data transferred successfully.</p>
                </div>
                <div class="w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                    <div id="successToastBar" class="h-full bg-emerald-500 w-full origin-left"></div>
                </div>
            </div>
        </div>

        <script>
        function showSuccessToast(message) {
            const wrap = document.getElementById('successToast');
            const card = document.getElementById('successToastCard');
            const msg  = document.getElementById('successToastMessage');
            const bar  = document.getElementById('successToastBar');
            if (!wrap || !card) return;

            msg.textContent = message || 'Data transferred successfully.';
            wrap.classList.remove('hidden');

            requestAnimationFrame(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            });

            bar.style.transition = 'none';
            bar.style.transform  = 'scaleX(1)';
            requestAnimationFrame(() => {
                bar.style.transition = 'transform 1300ms linear';
                bar.style.transform  = 'scaleX(0)';
            });

            setTimeout(() => {
                card.classList.add('scale-95', 'opacity-0');
                card.classList.remove('scale-100', 'opacity-100');
                setTimeout(() => wrap.classList.add('hidden'), 200);
            }, 1400);
        }
        </script>
</x-app-layout>