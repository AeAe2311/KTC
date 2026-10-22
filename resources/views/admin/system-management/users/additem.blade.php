<x-app-layout>
    <style>
        #inventoryMobile{
            max-height: calc(100vh - 245px);
        }

        @media (min-width: 768px) {
            #inventoryTable{
                max-height: calc(100vh - 225px);
            }
        }

        @media (min-width: 1024px) {
            #inventoryTable{
                max-height: calc(100vh - 220px);
            }
        }
    </style>

    @section('page_title', 'RAW MATERIALS')

    {{-- LOADING --}}
        <div wire:loading id="loadingScreen" class="hidden fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-[60] overflow-hidden bg-gray-900 opacity-75 opa flex flex-col items-center justify-center">
            <div role="status">
                <svg aria-hidden="true" class="inline w-10 h-10 mr-2 text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
            <h2 class="text-xl font-semibold text-center text-white">Processing...</h2>
            <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
        </div>
    {{-- LOADING END --}}

    {{-- PORTION MODAL --}}
        <!-- Main modal -->
        <div id="portionModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <form method="POST" action="{{ route('inventory.portion') }}" class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <span class="text-base text-blue-500 md:text-lg lg:text-xl">Portion Item</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="portionModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-6">
                        <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                            <h1 id="disposeName" class="pb-5 text-xl font-bold"></h1>

                            <input type="hidden" id="rawId" name="rawId">
                            <input type="hidden" id="porId" name="porId">

                            <div class="mb-6">
                                <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900">Raw Quantity</label>
                                <div class="flex items-center">
                                    <input type="text" id="quantity" name="quantity" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                                    <span id="disposeUnit" class="px-3 text-base font-bold text-gray-600"></span>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="waste_remarks" class="block mb-2 text-sm font-medium text-gray-900">Menu</label>
                                <div class="flex items-center">
                                    
                                    <span id="menuName"  class="px-3 text-base font-bold text-gray-600"></span>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900">Portion Quantity</label>
                                <div class="flex items-center">
                                    <input type="text" id="Porquantity" name="Porquantity" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
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
                        <button type="submit" class="submitPortionButton w-24 text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" data-id="" data-por="">Submit</button>
                        <button data-modal-hide="portionModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- PORTION MODAL END --}}

    {{-- DELETE MODAL --}}
        
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

    {{-- DISPOSE MODAL --}}
        <!-- Main modal -->
        <div id="disposeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <form method="POST" action="{{ route('inventory.dispose') }}" class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <span class="text-base text-blue-500 md:text-lg lg:text-xl">Waste Item</span>
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
                                    <input type="text" id="waste_remarks" name="waste_remarks" class=" bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
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

    {{-- ADD MODAL --}}
        <!-- Main modal -->
        <div id="addQtyModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <form method="POST" action="{{ route('inventory.addqty') }}" class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <span class="text-base text-blue-500 md:text-lg lg:text-xl">Increase Quantity</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="addQtyModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-6">
                        <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                            <h1 id="addName" class="pb-5 text-xl font-bold"></h1>
                            <input type="hidden" id="addSlug" name="addSlug">
                            <div class="mb-6">
                                <label for="dquantity" class="block mb-2 text-sm font-medium text-gray-900">Quantity</label>
                                <div class="flex items-center gap-x-2">
                                    <input type="text" id="dquantity" name="quantity" class="w-3/4 inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" required autocomplete="off">
                                    <select id="unitAdd" name="unit" value="{{ old('unit') }}" class="w-1/4 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 lg:text-base"></select>
                                    {{-- <span id="addUnit" class="px-3 text-base font-bold text-gray-600"></span> --}}
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Price</label>
                                <div class="flex items-center">
                                    <span class="px-3 text-base font-bold text-gray-600">₱</span>
                                    <input type="text" id="price" name="price" value="0" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"   >
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                                <div class="flex items-center gap-x-10">
                                    <div class="flex items-center">
                                        <input checked id="paid" type="radio" value="1" name="status" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="paid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Paid</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="unpaid" type="radio" value="0" name="status" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="unpaid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Unpaid</label>
                                    </div>
                                </div>
                            </div>
                            @if (Auth::user()->role == 1)
                                <div class="mb-6">
                                    <label for="" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                                    <div class="relative max-w-sm">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <input datepicker type="text" name="dateAdd" value="{{ date('m/d/Y') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                                    </div>
                                </div>
                            @endif
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="submitQtyButton w-24 text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Submit</button>
                        <button data-modal-hide="addQtyModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- ADD MODAL END --}}

    {{-- MINUS MODAL --}}
        <!-- Main modal -->
        <div id="minusQtyModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <form method="POST" action="{{ route('inventory.minusqty') }}" class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            <span class="text-base text-red-500 md:text-lg lg:text-xl">Decrease Quantity</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="minusQtyModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-6">
                        <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                            <input type="hidden" id="minSlug" name="minSlug">
                            <h1 id="minName" class="pb-5 text-xl font-bold"></h1>
                            <div class="mb-6">
                                <label for="minQuantity" class="block mb-2 text-sm font-medium text-gray-900">Quantity</label>
                                <div class="flex items-center gap-x-2">
                                    <input type="text" id="minQuantity" name="minQuantity" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                                    <select id="unitMinus" name="unit" value="{{ old('unit') }}" class="w-1/4 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 lg:text-base"></select>
                                    {{-- <span id="minUnit" class="px-3 text-base font-bold text-gray-600"></span> --}}
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="remarks" class="block mb-2 text-sm font-medium text-gray-900">Remarks</label>
                                <div class="flex items-center">
                                    {{-- <span class="px-3 text-base font-bold text-gray-600">₱</span> --}}
                                    <input type="text" id="remarks" name="remarks" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                                </div>
                            </div>
                            @if (Auth::user()->role == 1)
                                <div class="mb-6">
                                    <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                                    <div class="relative max-w-sm">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <input datepicker type="text" name="dateMinus" value="{{ date('m/d/Y') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                                    </div>
                                </div>
                            @endif
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="submitQtyButton w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Submit</button>
                        <button data-modal-hide="minusQtyModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- MINUS MODAL END --}}

    {{-- ITEM CART MODAL --}}
        
        <!-- Main modal -->
        <div id="itemCartModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <div class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="flex items-center font-semibold text-gray-900">
                            
                            <span class="text-base text-green-700 md:text-lg lg:text-xl">Selected Items</span>
                              <input type="text" name="user_id"  id = "userid"/>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemCartModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-3 space-y-6 md:py-6">

                    <input type="hidden" name="brand_id" id="brand_id"/>

                        <ul class="list-disc pl-5 text-sm text-gray-700" id="checkedItemsList">
                            <!-- Dynamically filled by JS -->
                        </ul>
                    </div>
                    <!-- Modal footer -->
                    
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">

                        <a type="button" href=""  class="itemCartConfirm  w-24 text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Submit</a>

                        <button data-modal-hide="itemCartModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                    
                </div>
            </div>
        </div>
    {{-- ITEM CART MODAL END --}}

    {{-- NOTIFICATION --}}
        @if (session('message'))
            <div class="absolute z-50 flex flex-row justify-between h-12 px-2 -translate-x-1/2 rounded-lg shadow-md notif left-1/2 top-14 w-96 lg:ml-32 bg-emerald-200 shadow-emerald-800">
                <div class="flex">
                    <i class="self-center text-2xl uil uil-cloud-check text-emerald-800"></i>
                    <h1 class="self-center ml-1 font-semibold text-emerald-800">{{ session('message') }}</h1>
                </div>
                <button class="self-center notifButton">
                    <i class="text-2xl uil uil-times text-emerald-800"></i>
                </button>
            </div>
        @endif
    {{-- NOTIFICATION END --}}

    {{-- ERROR NOTIFICATION --}}
        @if (session('error'))
            <div class="absolute z-50 flex flex-row justify-between h-12 px-2 -translate-x-1/2 bg-red-200 rounded-lg shadow-md notif left-1/2 top-14 w-96 shadow-red-800">
                <div class="flex">
                    <i class="self-center text-2xl text-red-800 uil uil-cloud-check"></i>
                    <h1 class="self-center ml-1 font-semibold text-red-800">{{ session('error') }}</h1>
                </div>
                <button class="self-center notifButton">
                    <i class="text-2xl text-red-800 uil uil-times"></i>
                </button>
            </div>
        @endif
    {{-- ERROR NOTIFICATION END --}}
    <div class="p-3 {{ (Auth::user()->role == 1) ? 'lg:ml-64' : '' }} lg:pt-3">
        <div id="contentDiv" class="w-full p-2">
            <div class="p-4 overflow-hidden bg-white rounded-lg shadow-md">
                    
                    <div class="mb-2">
                        <label for="name" class="block text-sm font-medium text-gray-900 lg:text-base">Name <span class="text-red-500"></span></label>
                        <input type="text" id="suppliername" name="suppliername" value="{{ $user->name  }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" readonly>
                    </div>
                </div>
            </div>
        </div>

    <div class="p-3 {{ (Auth::user()->role == 1) ? 'lg:ml-64' : '' }} lg:pt-3">
        <div id="contentDiv" class="w-full p-2">
            <div class="p-4 overflow-hidden bg-white rounded-lg shadow-md">
                    
                        <input type="hidden" id="supplierid" name="supplierid" value="{{ $user->id }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" readonly>
                {{-- CONTROLS --}}
                    <div class="mb-3">
                        <div class="md:grid md:grid-cols-2">
                            <div class="w-24 ">
                                <a href="{{ route('item.add') }}" class="block mb-3 md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px"><i class="mr-1 uil uil-plus"></i>ADD</a>
                            </div>
                            <div class="w-full justify-self-end xl:w-4/5">
                                <form method="GET" action="" id="searchForm" class="w-full">

                                <div class="w-full grid justify-self-end mb-3">
                                    
                                    <div class="w-full justify-self-end">
                                    <input type="hidden" id="slug"  value="{{ $user->slug  }}">
                                    <select  name="searchSelect" id="searchSelect" class="block w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" autocomplete="off" selected="{{ old('searchSelect') }}"> 
                                            
                                            <option value="All">All Categories</option>
                                            @foreach ($categories as $categorys)
                                                @if ($catVal == $categorys->id)
                                               <option value="{{ $categorys->id }}" selected>{{ $categorys->name }}</option>
                                               @else
                                               <option value="{{ $categorys->id }}" >{{ $categorys->name }}</option>
                                               @endif
                                               
                                            @endforeach
                                             
                                    </select>
                                    </div>
                                    <div class="w-full justify-self-end xl:w-4/5">
                                    
                                        <label for="searchInput" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>
                                        
                                    </div>
                                    

                                </div>
                                    <label for="searchInput" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input type="search" id="searchInput" class="block w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="SEARCH" value="{{ $search }}" autocomplete="off">
                                        <button id="clearButton" type="button" class="absolute right-20 bottom-1">
                                            <i class="text-2xl uil uil-times"></i>
                                        </button>
                                        <button id="searchButton" type="button" style="bottom: 5px; right: 5px;" type="submit" class="text-white absolute bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-2.5 py-1.5">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                {{-- CONTROLS END --}}
                
                

                <div>
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                Item Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                Category
                            </th>
                            <!-- <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                Quantity
                            </th> -->
                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                Department
                            </th>
                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                Brand
                            </th>
                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventories as $inventory)
                            
                            <tr class="bg-white border-b">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ str_replace('RW-', '', $inventory->name) }}
                                </th>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    {{ $inventory->cat_name }}
                                </td>
                                <!-- <td class="flex items-center justify-center px-6 py-4 text-center whitespace-nowrap">
                                    @if ( auth()->user()->role == 1 || auth()->user()->role == 9)
                                        @if($inventory->category_id != $menu_id)
                                            <button data-slug="{{ $inventory->slug }}" data-unit="{{ $inventory->unit }}" data-name="{{ $inventory->name }}" data-modal-target="minusQtyModal" data-modal-toggle="minusQtyModal" class="minusButton"><i class="mr-2 text-xl text-red-500 uil uil-minus-circle"></i></button>
                                        @endif
                                    @endif
                                    {{ round($inventory->quantity, 2) }}
                                    @if ( auth()->user()->role == 1 || auth()->user()->role == 9 || auth()->user()->role == 4 || auth()->user()->role == 3)
                                        @if($inventory->category_id != $menu_id)
                                            <button data-slug="{{ $inventory->slug }}" data-unit="{{ $inventory->unit }}" data-name="{{ $inventory->name }}" data-modal-target="addQtyModal" data-modal-toggle="addQtyModal" class="addButton"><i class="mx-2 text-xl text-blue-500 uil uil-plus-circle"></i></button>
                                        @endif
                                    @endif
                                    {{ $inventory->unit }}
                                </td> -->
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    {{ $inventory->dname }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div id="fields-container-{{ $inventory->id }}" class="flex flex-col gap-2 items-center justify-center">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" class="remove-field bg-red-500 hover:bg-red-600 text-white rounded px-2 py-1 text-sm">−</button>

                                            <select name="brand_id[{{ $inventory->id }}][]" class="border rounded px-2 py-1 text-sm">
                                                <option value="">Select Brand</option>
                                                @foreach ($brands as $brand)
                                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>

                                            <button type="button" class="add-field bg-blue-500 hover:bg-blue-600 text-white rounded px-2 py-1 text-sm">+</button>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <!-- @if(Auth::user()->role == 1 || Auth::user()->role == 5 || Auth::user()->role == 9 || Auth::user()->role == 3)
                                        <a type="button" data-modal-target="disposeModal" data-modal-toggle="disposeModal" data-slug="{{ $inventory->slug }}"data-name="{{ $inventory->name }}"  class="text-sm font-semibold text-orange-500 cursor-pointer disposeButton hover:underline">Waste</a> | 
                                    @endif

                                    <a href="{{ url('/inventory/edit/'.$inventory->slug) }}" class="text-sm font-semibold text-blue-600 hover:underline">Edit</a>
                                    | <a type="button" data-modal-target="portionModal" data-modal-toggle="portionModal" data-slug="{{ $inventory->slug }}" class="portionBtn text-sm font-semibold text-emerald-600 cursor-pointer deleteButton hover:underline">Portion</a>
                                    @if(Auth::user()->role == 1)
                                        | <a type="button" data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" data-slug="{{ $inventory->slug }}" class="text-sm font-semibold text-red-600 cursor-pointer deleteButton hover:underline">Delete</a>
                                    @endif -->
                                    <input type="hidden" value="{{ $user->id }}">
                                    
                                    <input type="checkbox" data-userid="{{$user->id}}" data-id="{{$inventory->id}}" data-slug="{{ $inventory->slug }}"  data-name="{{ $inventory->name }}" data-category="{{ $inventory->category_id }}" data-department="{{ $inventory->department }}" data-brand="{{ $inventory->brand_id }}" 
                                    class="item-adding-checkbox mr-1" 
                                      @foreach($getitemcheck as $getcheck)
                                        {{$inventory->slug ==  $getcheck->inv_slug ? 'checked' : '' }}
                                        @endforeach
                                    >
                                   
                                </td>
                                
                            </tr>
                            
                        @endforeach
                            
                    </tbody>
                    
                </table>

                    <div class="mt-4">
                        <a type="button" data-modal-target="itemCartModal" data-modal-toggle="itemCartModal" data-slug="{{ $user->slug }}" data-userid="{{ $user->id }}" class="itemCartButton w-full block text-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 transition duration-200">Submit</a>
                    </div>


                {{-- PAGINATION --}}
                    <div class="grid px-3 mt-3 md:grid-cols-2">
                        @php
                            $prev = $page - 1;
                            $next = $page + 1;
                            $from = ($prev * 100) + 1;
                            $to = $page * 100;
                            if($to > $invCount){
                                $to = $invCount;
                            }if($invCount == 0){
                                $from = 0;
                            }
                        @endphp
                        <div class="self-center justify-self-center md:justify-self-start">
                            <span class="text-sm text-gray-700">
                                Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $invCount }}</span> Items
                            </span>
                        </div>

                        <div class="justify-self-center md:justify-self-end">
                            <nav aria-label="Page navigation example" class="h-8 mb-0.5 shadow-xl">
                                <ul class="inline-flex items-center -space-x-px">
                                    <li>
                                        <a href="{{ ($search == '') ? url('/inventory/'.$prev) : url('/inventory/'.$prev.'/'.$search);  }}"  class="{{ ($page == 1) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                            <i class="uil uil-angle-left-b"></i>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li>
                                        <p class="z-10 block font-semibold leading-9 text-center text-gray-500 bg-white border border-gray-300 w-9 h-9">{{ $page }}</p>
                                    </li>
                                    <li>
                                        <a href="{{ ($search == '') ? url('/inventory/'.$next) : url('/inventory/'.$next.'/'.$search); }}" class="{{ ($to == $invCount) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
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

     <script>
        $(document).ready(function() {
            var g1 = ['tsp', 'tbsp', 'gal', 'L', 'mL', 'c'];
            var g2 = ['kg', 'g', 'mg', 'oz', 'lb'];
            var g3 = ['ea', 'doz'];

            $('#searchButton').click(function(){
                var search = $('#searchInput').val();
                var slug = $('#slug').val();
                if(search != ""){
                    $('#searchForm').prop('action', `{{ url('/system-management/user/additem/1/${search}/${slug}') }}`);
                }else{
                    $('#searchForm').prop('action', `{{ url('/system-management/user/additem/1/${slug}') }}`);
                }
                $('#searchForm').submit();
            });
            $('#searchSelect').on('change', function(event) {
                var categories = $('#searchSelect').find(":selected").val();
                var slug = $('#slug').val();
                $('#searchForm').prop('action', `{{ url('/system-management/user/additem/category/search/1/${categories}/${slug}') }}`);
                $('#searchForm').submit();
            });

            $('#searchInput').on('keydown', function(event) {
                if (event.keyCode === 13) {
                    $('#searchButton').click();
                    event.preventDefault();
                }
            });

            $('#clearButton').click(function(){
                $('#searchInput').val('');
                $('#searchButton').click();
            });

            $('.notifButton').click(function(){
                $('.notif').addClass('hidden');
            });

            $('.deleteButton').click(function(){
                var slug = $(this).data('slug');
                $('.deleteConfirm').prop('href', `{{ url('/system-management/user/delete/${slug}') }}`);
            });

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

            $('.addButton').click(function(){
                $('#quantity').val('');
                $('#price').val('');
                $('#quantity').focus();

                var slug = $(this).data('slug');
                var unit = $(this).data('unit');
                var name = $(this).data('name');

                var selectedArray;
                if (g1.includes(unit)) {
                    selectedArray = g1;
                } else if (g2.includes(unit)) {
                    selectedArray = g2;
                } else if (g3.includes(unit)) {
                    selectedArray = g3;
                } else {
                    selectedArray = [];
                }

                var select = $('#unitAdd');
                select.empty();
                $.each(selectedArray, function(key, value) {
                    select.append($('<option>', {
                        value: value,
                        text: value
                    }));
                });
                select.val(unit);
                

                $('#addName').html(name);
                $('#addSlug').val(slug);
                $('#addUnit').html(unit);
            });

            $('.minusButton').click(function(){
                $('#minQuantity').val('');
                $('#remarks').val('');

                var slug = $(this).data('slug');
                var unit = $(this).data('unit');
                var name = $(this).data('name');

                var selectedArray;
                if (g1.includes(unit)) {
                    selectedArray = g1;
                } else if (g2.includes(unit)) {
                    selectedArray = g2;
                } else if (g3.includes(unit)) {
                    selectedArray = g3;
                } else {
                    selectedArray = [];
                }

                var select = $('#unitMinus');
                select.empty();
                $.each(selectedArray, function(key, value) {
                    select.append($('<option>', {
                        value: value,
                        text: value
                    }));
                });
                select.val(unit);


                $('#minSlug').val(slug);
                $('#minUnit').html(unit);
                $('#minName').html(name);
            });

            $('.disposeButton').click(function(){
                var slug = $(this).data('slug');
                var name = $(this).data('name');
                $('#disposeSlug').val(slug);
                $('#disposeName').html(name);
            });

            $('.portionBtn').click(function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                $.ajax({
                    url:"{{ route('inventory.checking') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        _token: _token,
                        slug: slug
                    },
                    success:function(result){
                         $('#menuName').html(result.name)
                         $('#rawId').val(result.raw_id)
                         $('#porId').val(result.por_id)
                    }
                })
            });

            // $(document).ready(function () {
            //     $('.item-adding-checkbox').on('change', function () {
                    
            //             const isChecked = $(this).is(':checked'); 
            //             const itemName = $(this).data('name');
            //             const itemCategory = $(this).data('category');
            //             const itemDepartment = $(this).data('department');
            //             const inv_slug = $(this).data('slug');
            //             const inv_id = $(this).data('id');
            //             const userid = $(this).data('userid');
            //             const brand_id = $(`#fields-container-${inv_id} select`).val();
                        
            //             $.ajax({
            //                 url: "{{ route('user.itemAdding') }}",
            //                 type: "POST",
            //                 data: {
                               
            //                     item_name: itemName,
            //                     item_category: itemCategory,
            //                     item_department: itemDepartment,
            //                     inv_slug: inv_slug,
            //                     inv_id: inv_id,
            //                     is_checked: isChecked ? 1 : 0,
            //                     userid: userid,
            //                     brand_id : brand_id,
            //                     _token: $('meta[name="csrf-token"]').attr('content') 
            //                 },

            //                 success: function (response) {
            //                     if (response.success == 'add') {
                                    
            //                     }
            //                     else if(response.success == 'delete'){
                                     
            //                     }
                                
            //                 },
                            
            //             });
                    
            //     });
            // });

            $(document).on('change', '.item-adding-checkbox', function () {
                const isChecked = $(this).is(':checked');
                const itemName = $(this).data('name');
                const itemCategory = $(this).data('category');
                const itemDepartment = $(this).data('department');
                const invSlug = $(this).data('slug');
                const invId = $(this).data('id');
                const userId = $(this).data('userid');

                const brandSelects = $(`select[name='brand_id[${invId}][]']`);
                const brandIds = [];

                brandSelects.each(function () {
                    const val = $(this).val();
                    if (val) {
                        brandIds.push(val);
                    }
                });

                // If unchecked, still send one request to delete
                if (!isChecked) {
                    brandIds.forEach(function (brandId) {
                        $.ajax({
                            url: "{{ route('user.itemAdding') }}",
                            method: 'POST',
                            data: {
                                item_name: itemName,
                                item_category: itemCategory,
                                item_department: itemDepartment,
                                inv_slug: invSlug,
                                inv_id: invId,
                                is_checked: 0,
                                userid: userId,
                                brand_id: brandId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                console.log(response);
                            }
                        });
                    });
                } else {
                    // Send a request per selected brand
                    brandIds.forEach(function (brandId) {
                        $.ajax({
                            url: "{{ route('user.itemAdding') }}",
                            method: 'POST',
                            data: {
                                item_name: itemName,
                                item_category: itemCategory,
                                item_department: itemDepartment,
                                inv_slug: invSlug,
                                inv_id: invId,
                                is_checked: 1,
                                userid: userId,
                                brand_id: brandId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                console.log(response);
                            }
                        });
                    });
                }
            });

            function loadCheckedItems(userId) {
                $.post("{{ route('user.itemCheck') }}", {
                    userid: userId,
                    _token: '{{ csrf_token() }}'
                })
                .done(items => {
                    const list = $('#checkedItemsList').empty();

                    if (!Array.isArray(items) || !items.length) {
                        list.append('<li>No checked items.</li>');
                        return;
                    }

                    items.forEach(item => {
                        const name = item.item_name ?? 'Unnamed';
                        const brand = item.brand_name ?? 'No brand';

                        list.append(`<li>${name} - ${brand}</li>`);
                    });
                })
                .fail(() => {
                    $('#checkedItemsList').html('<li class="text-red-600">Failed to load items.</li>');
                });
            }

         $(document).on('click', '[data-modal-toggle="itemCartModal"]', function () {
                const userId = $(this).data('userid');

                if (userId) {
                    loadCheckedItems(userId); 

                    // Pass to modal input
                    $('#itemCartModal input[name="user_id"]').val(userId);

                    // Or display in a span
                    $('#itemCartModal span[data-user-id-display]').text(userId);
                }
            });
            
            $(document).ready(function () {
                $('.itemCartConfirm').on('click', function (e) {
                    e.preventDefault();
                    
                     const userId = $('#userid').val();

                    $.ajax({
                        url: "{{ route('item.storeTemp') }}", 
                        method: 'POST',
                        data: {
                            userId: userId,
                            // item_name: itemName,
                            // item_category: itemCategory,
                            // item_department: itemDepartment,
                            // inv_slug: inv_slug,
                            // status: 'PENDING',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            
                            alert('Item saved successfully!');
                            window.location.reload();

                            $('.item-adding-checkbox:checked').prop('checked', false);
                            
                        },
                        error: function (xhr) {
                            alert('Failed to save item.');
                            console.log(xhr.responseText);
                        }
                    });
                });
            });

            $(document).ready(function () {
                $(document).on('click', '.add-field', function () {
                    const container = $(this).closest('[id^="fields-container-"]');
                    const select = container.find('select').first().clone(); 
                    const newRow = $('<div class="flex items-center space-x-2 mt-2"></div>');

                    $('<button type="button" class="remove-field bg-red-500 hover:bg-red-600 text-white rounded px-2 py-1 text-sm">−</button>').appendTo(newRow);
                    select.appendTo(newRow);
                    $('<button type="button" class="add-field bg-blue-500 hover:bg-blue-600 text-white rounded px-2 py-1 text-sm">+</button>').appendTo(newRow);

                    container.append(newRow);
                });


                $(document).on('click', '.remove-field', function () {
                    const container = $(this).closest('[id^="fields-container-"]');

                    if (container.children().length > 1) {
                        $(this).closest('.flex').remove();
                    }
                });
            });
            
        });
     </script>
</x-app-layout>
