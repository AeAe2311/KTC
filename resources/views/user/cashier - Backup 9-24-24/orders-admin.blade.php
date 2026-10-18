<x-app-layout>
    <style>

    </style>

    @section('page_title', '')


    {{-- LOADING --}}
        <div wire:loading id="loadingScreen" class="hidden fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-[60] overflow-hidden bg-gray-900 opacity-75 opa flex flex-col items-center justify-center">
            <div role="status">
                <svg aria-hidden="true" class="inline w-10 h-10 mr-2 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
            <h2 class="text-center text-white text-xl font-semibold">Processing...</h2>
            <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
        </div>
    {{-- LOADING END --}}

    {{-- NOTIF --}}
        <div id="notifDiv" class="absolute top-16 left-1/2 -translate-x-1/2 z-50 w-[400px]"></div>
    {{-- NOTIF END --}}

    {{-- AR MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="ArModal" data-modal-toggle="ArModal" id="openArModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="ArModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-circle text-blue-500 text-3xl mr-3"></i>Confirmation
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="ArModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-center leading-relaxed text-gray-500">
                            Are you sure you want to transfer this transaction to Account Receivables?
                        </p>
                        <div class="w-full text-center">
                            <label for="customer_name_ar" class="text-base leading-relaxed text-gray-500 w-full text-center" >Customer Name</label>
                            <div class="relative mb-2">
                                <input type="text" name="customer_name_ar" id="customer_name_ar" class=" block w-full px-4 py-2.5  text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 text-center" required autocomplete="off">
                                    <!-- <button id="showPasswordAr" type="button" class=" absolute right-2 bottom-1">
                                        <i class="uil uil-eye text-3xl"></i>
                                    </button>  -->
                            </div>
                        </div>
                        <div class="w-full text-center">
                            <label for="" class="text-base leading-relaxed text-gray-500 w-full text-center" >Enter Code</label>
                            <div class="relative mb-2">
                                <input type="password" name="arCode" id="arCode" class=" block w-full px-4 py-2.5  text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 text-center" required autocomplete="off">
                                    <button id="showPasswordAr" type="button" class=" absolute right-2 bottom-1">
                                        <i class="uil uil-eye text-3xl"></i>
                                    </button> 
                            </div>
                        </div>
                        <p class="text-red-600 text-center" id="errorArCode"></p>

                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptArButton"  data-slug="" type="button" class="text-white bg-emerald-700 hover:bg-emerald-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Submit</button>
                        <button data-modal-hide="ArModal" type="button" class="removeModalHide text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- AR MODAL END --}}

     {{-- Multiple Void Table MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="multipleVoidModal" data-modal-toggle="multipleVoidModal" id="openMultipleVoidModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="multipleVoidModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-triangle text-red-500 text-3xl mr-3"></i>Multiple Void
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="removeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4" >
                        
                        <p class="text-center leading-relaxed text-gray-500">
                            Are you sure you want to delete the selected items?
                        </p>
                        <div class="w-full text-center">
                            <label for="" class="text-base leading-relaxed text-gray-500 w-full text-center" >Enter Code</label>
                            <div class="relative mb-2">
                                <input type="password" name="voidCodeMultiple" id="voidCodeMultiple" class=" block w-full px-4 py-2.5  text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 text-center" required autocomplete="off">
                                    <button id="showPasswordMultiple" type="button" class=" absolute right-2 bottom-1">
                                        <i class="uil uil-eye text-3xl"></i>
                                    </button> 
                            </div>
                        </div>
                        <p class="text-red-600 text-center" id="errorVoidCodeMultiple"></p>
                    </div>
                    
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptMultipleVoidButton" data-modal-hide="" data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Confirm</button>
                        <button data-modal-hide="multipleVoidModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- Multiple Void Table MODAL END --}}

    {{-- REMOVE MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="removeModal" data-modal-toggle="removeModal" id="openRemoveModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="removeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-triangle text-red-500 text-3xl mr-3"></i>Remove Order
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="removeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-base leading-relaxed text-gray-500">
                            Are you sure you want to remove this order?
                        </p>
                        <div class="w-full text-center">
                            <label for="" class="text-base leading-relaxed text-gray-500 w-full text-center" >Enter Code</label>
                            <div class="relative mb-2">
                                <input type="password" name="voidCode" id="voidCode" class=" block w-full px-4 py-2.5  text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 text-center" required autocomplete="off">
                                    <button id="showPassword" type="button" class=" absolute right-2 bottom-1">
                                        <i class="uil uil-eye text-3xl"></i>
                                    </button> 
                            </div>
                        </div>
                        <p class="text-red-600" id="errorVoidCode"></p>

                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptRemoveButton"  data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Remove</button>
                        <button data-modal-hide="removeModal" type="button" class="removeModalHide text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- REMOVE MODAL END --}}


    {{-- Change Table MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="TransferTableModal" data-modal-toggle="TransferTableModal" id="openTransferTableModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="TransferTableModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-circle text-gray-500 text-3xl mr-3"></i>Transfer Table
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="removeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4" id="transferTableDiv">
                        
                    </div>
                    <input type="hidden" class="tableIDforDiscount" name="tableIDforDiscount">
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptTransferTableButton" data-modal-hide="TransferTableModal" data-slug="" type="button" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Confirm</button>
                        <button data-modal-hide="TransferTableModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- Change Table MODAL END --}}


    {{-- Split Table MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="SplitTableModal" data-modal-toggle="SplitTableModal" id="openSplitTableModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="SplitTableModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-circle text-gray-500 text-3xl mr-3"></i>Split Table
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="removeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6" id="splitTableDiv">
                        
                    </div>
                    <div class="text-center mb-2 hidden" id="splitErrorDiv">
                        <span class="text-red-700 p-6">Please Select Item to Split!</span>
                    </div>
                    <input type="hidden" class="tableIDforDiscount" name="tableIDforDiscount">
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptSplitTableButton" data-slug="" type="button" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Confirm</button>
                        <button data-modal-hide="SplitTableModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- Split Table MODAL END --}}

    {{-- REMOVE PWD MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="removePWDModal" data-modal-toggle="removePWDModal" id="openremovePWDModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="removePWDModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-triangle text-red-500 text-3xl mr-3"></i>Remove PWD / Seniors
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="removePWDModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-base leading-relaxed text-gray-500">
                            Are you sure you want to remove this PWD / Seniors?
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptRemovePwdButton" data-modal-hide="removePWDModal" data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Remove</button>
                        <button data-modal-hide="removePWDModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- REMOVE PWD MODAL END --}}

    {{-- Add Senior MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="addPwdModal" data-modal-toggle="addPwdModal" id="openAddPwdModal" class="hidden"  type="button"></button>
    
        <!-- Main modal -->
        <div id="addPwdModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-circle text-blue-500 text-3xl mr-3"></i>Add Senior Details
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="addPwdModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="h-[40vh] space-y-4 p-6 grid">
                        <label class="pb-0" for="">Discount Category</label>
                        <select name="category" id="category" class="rounded-xl">
                            <option value="sr">Senior Citizen</option>
                            <option value="pwd">PWD</option>
                        </select>
                        <label class="pb-0" for="">Name</label>
                        <input class="rounded-xl" type="text" name="pwdName" id="pwdName">
                        <label class="pb-0" for="">ID number</label>
                        <input class="rounded-xl" type="text" name="pwdIdNumber" id="pwdIdNumber">
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptPwdButton"  data-slug="" type="button" class="text-white bg-emerald-700 hover:bg-emerald-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Add</button>
                        <button data-modal-hide="addPwdModal" type="button" class="closePwdBtn text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- Add Senior MODAL END --}}

    {{-- REMOVE DISCOUNT MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="removeDiscountModal" data-modal-toggle="removeDiscountModal" id="openRemoveDiscountModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="removeDiscountModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-triangle text-red-500 text-3xl mr-3"></i>Remove Discount
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="removeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-base leading-relaxed text-gray-500">
                            Are you sure you want to remove the discount?
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptRemoveDiscountButton" data-modal-hide="removeDiscountModal" data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Remove</button>
                        <button data-modal-hide="removeDiscountModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- REMOVE DISCOUNT MODAL END --}}

    {{-- REDUCE MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="reduceModal" data-modal-toggle="reduceModal" id="openReduceModal" class="hidden" type="button"></button>
    
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" class="hidden" id="hideReduceModalBtn" data-modal-hide="reduceModal">

        </button>
        <!-- Main modal -->
        <div id="reduceModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-triangle text-red-500 text-3xl mr-3"></i>Reduce Quantity
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="reduceModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-center leading-relaxed text-gray-500">
                            Are you sure you want to reduce the quantity of this order?
                        </p>
                        <div class="w-full text-center">
                            <label for="" class="text-base leading-relaxed text-gray-500 w-full text-center" >Enter Code</label>
                            <div class="relative mb-2">
                                <input type="password" name="voidCodeReduce" id="voidCodeReduce" class=" block w-full px-4 py-2.5  text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 text-center" required autocomplete="off">
                                    <button id="showPasswordReduce" type="button" class=" absolute right-2 bottom-1">
                                        <i class="uil uil-eye text-3xl"></i>
                                    </button> 
                            </div>
                        </div>
                        <p class="text-red-600" id="errorVoidCodeReduce"></p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptReduceButton" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Confirm</button>
                        <button data-modal-hide="reduceModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- REDUCE MODAL END --}}

    {{-- CANCEL MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="cancelModal" data-modal-toggle="cancelModal" id="openCancelModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="cancelModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-triangle text-red-500 text-3xl mr-3"></i>Cancel All Orders
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="cancelModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-base leading-relaxed text-gray-500">
                            Are you sure you want to cancel all orders in this table?
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptCancelButton" data-modal-hide="cancelModal" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Confirm</button>
                        <button data-modal-hide="cancelModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- CANCEL MODAL END --}}

    

    {{-- OPEN MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="openModal" data-modal-toggle="openModal" id="openOpenModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="openModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Open Table
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="openModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-base leading-relaxed text-gray-500">
                            Are you sure you want to mark this table as open?
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptOpenButton" data-modal-hide="openModal" type="button" class="text-white bg-emerald-700 hover:bg-emerald-800 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Confirm</button>
                        <button data-modal-hide="openModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- OPEN MODAL END --}}

    {{-- OCCUPY MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="occupyModal" data-modal-toggle="occupyModal" id="occupyOpenModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="occupyModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Occupy Table
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="occupyModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-base leading-relaxed text-gray-500">
                            Are you sure you want to mark this table as occupied?
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptOccupyButton" data-modal-hide="occupyModal" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Confirm</button>
                        <button data-modal-hide="occupyModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- OCCUPY MODAL END --}}

    {{-- DISCOUNT MODAL --}}
    <!-- Modal toggle -->
    <button data-modal-target="discountModal" data-modal-toggle="discountModal" id="openDiscountModal" class="hidden" type="button"></button>
        <!-- Main modal -->
        
        <!-- discount -->
        <div id="discountModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-5xl max-h-full bg-white rounded-lg shadow">
                <!-- Modal content -->
                <!-- <form method="POST" action="{{ route('orders.OrderupdateDiscount') }}" class="relative bg-white rounded-lg shadow"> -->
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Details
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="discountModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <!-- <input type="hidden" id="paymentMethod"> -->
                        <div class="mb-6">
                            <label for="customer_with_discount" class="block mb-2 text-sm font-medium text-gray-900" hidden>Number Customer With Discount</label>
                            <input type="number" id="customer_with_discount" name="customer_with_discount" value="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div>
                        <div class="mb-6">
                            
                            <input type="hidden" id="total_customer" name="total_customer" value="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div>
                    </div>
                    <input type="hidden" class="tableIDforDiscount" id="" name="tableIDforDiscount">
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button id="" data-modal-hide="discountModal" type="button" class="submitDiscountButton text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none rounded-lg border border-blue-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">SUBMIT</button>
                        <button data-modal-hide="discountModal" type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none rounded-lg border border-gray-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">CANCEL</button>
                    </div>
                <!-- </form> -->
            </div>
        </div>
    {{-- DISCOUNT MODAL END --}}


    {{-- Service Charge MODAL --}}

    <button data-modal-target="ServiceChargeModal" data-modal-toggle="ServiceChargeModal" id="openServiceChargeModal" class="hidden" type="button"></button>
        <!-- Main modal -->
        <div id="ServiceChargeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-3xl max-h-full bg-white rounded-lg shadow">
                <!-- Modal content -->
                <!-- <form method="POST" action="{{ route('orders.OrderupdateDiscount') }}" class="relative bg-white rounded-lg shadow"> -->
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Details
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="discountModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <!-- <input type="hidden" id="paymentMethod"> -->
                        <div class="mb-6">
                            <label for="service_charge" class="block mb-2 text-sm font-medium text-gray-900">Percent of Service Charge</label>
                            <input type="text" id="service_charge" name="service_charge" value="{{ $SCRow->percent }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div>
                        <input type="hidden" class="tableIDforDiscount" name="tableIDforDiscount">
                        <!-- <div class="mb-6">
                            <label for="total_customer" class="block mb-2 text-sm font-medium text-gray-900">Total Number of Customers</label>
                            <input type="text" id="total_customer" name="total_customer" value="{{ ($discountRow->total_customer) == 0 ? '' : $discountRow->total_customer }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div> -->
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button id="" data-modal-hide="ServiceChargeModal" type="button" class="scSubmitBtn text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none rounded-lg border border-blue-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">SUBMIT</button>
                        <button data-modal-hide="ServiceChargeModal" type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none rounded-lg border border-gray-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">CANCEL</button>
                    </div>
                <!-- </form> -->
            </div>
        </div>
    {{-- Service Charge MODAL END --}}

    {{-- Lime Discount MODAL --}}

        <button data-modal-target="LimeDiscountModal" data-modal-toggle="LimeDiscountModal" id="openLimeDiscountModal" class="hidden" type="button"></button>
        <!-- Main modal -->
         
        <div id="LimeDiscountModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-3xl max-h-full bg-white rounded-lg shadow">
                <!-- Modal content -->
                <!-- <form method="POST" action="{{ route('pos.updateLimeDiscount') }}" class="relative bg-white rounded-lg shadow"> -->
                    @csrf
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Details
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="discountModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <!-- <input type="hidden" id="paymentMethod"> -->
                        <div class="mb-6">
                            <label for="service_charge" class="block mb-2 text-sm font-medium text-gray-900">Percent of Discount</label>
                            <input type="text" id="lime_discount" name="lime_discount" value="{{ (empty($limeDiscountRow->percent)) ? $limeDiscountRow->percent : 0 }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div>
                        <!-- <div class="mb-6">
                            <label for="total_customer" class="block mb-2 text-sm font-medium text-gray-900">Total Number of Customers</label>
                            <input type="text" id="total_customer" name="total_customer" value="{{ ($discountRow->total_customer) == 0 ? '' : $discountRow->total_customer }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div> -->
                    </div>
                    <input type="hidden" class="tableIDforDiscount" name="tableIDforDiscount">
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button id="" data-modal-hide="LimeDiscountModal" type="button" class="submitLimeDiscountButton text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none rounded-lg border border-blue-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">SUBMIT</button>
                        <button data-modal-hide="LimeDiscountModal" type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none rounded-lg border border-gray-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">CANCEL</button>
                    </div>
                <!-- </form> -->
            </div>
        </div>
    {{-- Lime Discount MODAL END --}}

    {{-- MOP MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="MOPModal" data-modal-toggle="MOPModal" id="openMOPModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="MOPModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4  overflow-x-auto overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full " >
            <div class="relative w-full  max-h-full ">
                <!-- Modal content -->
                <div class="relative  rounded-lg shadow bg-white ">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Payment Section
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="MOPModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-2 space-y-1 overflow-y-auto overflow-x-hidden">
                        <input type="hidden" name="mop" id="mop">
                        <div class="grid grid-cols-3 gap-2 ">

                            <div class="col-span-2">
                                
                                <div class="grid grid-cols-3 gap-2 h-50 overflow-y-hidden overflow-x-hidden">

                                    <!-- CASH -->
                                    <div data-mop="CASH" class="mopButton text-white bg-emerald-500  font-medium rounded-lg text-sm px-5  items-center w-full">
                                        <button type="button" data-mop="CASH"  class="mopButton text-white bg-emerald-500  font-medium rounded-lg text-sm  text-center inline-flex items-center w-full">
                                            <i class="uil uil-money-bill text-6xl mr-3"></i>
                                            <span class="text-2xl font-bold text-center">CASH</span>
                                            
                                        </button>
                                        <input type="text" id="cashMop" name="cashMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- MASTERCARD -->
                                    <div data-mop="CREDIT CARD - MASTERCARD" class="mopButton text-white bg-[#d66625]  focus:ring-4 focus:ring-[#d66625]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center  items-center mr-2 w-full">
                                        <button type="button" data-mop="CREDIT CARD - MASTERCARD" class="mopButton text-white bg-[#d66625]  focus:ring-4 focus:ring-[#d66625]/50 focus:outline-none font-medium rounded-lg text-sm  text-center inline-flex items-center w-full">
                                            <i class="uil uil-master-card text-6xl mr-3"></i>
                                            <span class="text-1xl font-bold text-center">CREDIT CARD - MASTERCARD</span>
                                        </button>
                                        <input type="text" id="ccMop" name="ccMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- VISA -->
                                    <div data-mop="VISA" class="mopButton text-white bg-[#2557D6]  focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5  text-center items-center  ">
                                        <button type="button" data-mop="VISA"  class="mopButton text-white bg-[#2557D6]  focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm  text-center inline-flex items-center w-full">
                                            <i class="uil uil-card-atm text-6xl mr-3"></i>
                                            <span class="text-2xl font-bold">VISA</span>
                                        </button>
                                        <input type="text" id="visaMop" name="visaMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- GCASH -->
                                    <div data-mop="GCASH" class="mopButton text-white bg-[#5686ff]  focus:ring-4 focus:ring-[#5686ff]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center h-25">
                                        <button type="button" data-mop="GCASH"  class="mopButton text-white bg-[#5686ff]  focus:ring-4 focus:ring-[#5686ff]/50 focus:outline-none font-medium rounded-lg text-sm text-center inline-flex items-center h-12">
                                            <i class="uil uil-money-bill text-6xl mr-3"></i>
                                            <span class="text-2xl font-bold">GCASH</span>
                                        </button>
                                        <input type="text" id="gcashMop" name="gcashMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- GCASH CREDIT GIVES -->
                                    <div data-mop="GCASH CREDIT GIVES" class="mopButton text-white bg-[#5686ff]  focus:ring-4 focus:ring-[#5686ff]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center  items-center w-full h-25">
                                        <button type="button" data-mop="GCASH CREDIT GIVES"  class="mopButton text-white bg-[#5686ff] focus:ring-4 focus:ring-[#5686ff]/50 focus:outline-none font-medium rounded-lg text-sm  text-center inline-flex items-center  w-full h-12">
                                            <i class="uil uil-money-bill text-6xl mr-3"></i>
                                            <span class="text-1xl font-bold">GCASH CREDIT GIVES</span>
                                        </button>
                                        <input type="text" id="ggivesMop" name="ggivesMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- AMEX -->
                                    <div data-mop="AMEX" class="mopButton text-white bg-[#2557D6]  focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5  text-center items-center w-full h-25">
                                        <button type="button" data-mop="AMEX"  class="mopButton text-white bg-[#2557D6] focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm text-center inline-flex items-center w-full h-12 ">
                                            <i class="uil uil-credit-card text-6xl mr-3"></i>
                                            <span class="text-2xl font-bold">AMEX</span>
                                        </button>
                                        <input type="text" id="amexMop" name="amexMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    

                                    <!-- BANK TRANSFER -->
                                    <div data-mop="BANK TRANSFER" class="mopButton text-white bg-[#239c3b]  focus:ring-4 focus:ring-[#239c3b]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center  items-center w-full h-24">
                                        <button type="button" data-mop="BANK TRANSFER"  class="mopButton text-white bg-[#239c3b]  focus:ring-4 focus:ring-[#239c3b]/50 focus:outline-none font-medium rounded-lg text-sm  text-center inline-flex items-center  mr-2 w-full h-12">
                                            <i class="uil uil-exchange text-6xl mr-3"></i>
                                            <span class="text-1xl font-bold">BANK TRANSFER</span>
                                        </button>
                                        <input type="text" id="banktMop" name="banktMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- DEBIT -->
                                    <div data-mop="DEBIT" class="mopButton text-white bg-[#2557D6]  focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center items-center w-full h-24">
                                        <button type="button" data-mop="DEBIT"  class="mopButton text-white bg-[#2557D6]  focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm text-center inline-flex items-center w-full h-12">
                                            <i class="uil uil-card-atm text-6xl mr-3"></i>
                                            <span class="text-2xl font-bold">DEBIT</span>
                                        </button>
                                        <input type="text" id="debitMop" name="debitMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- DINERS -->
                                    <div data-mop="DINERS" class="mopButton text-white bg-[#2557D6]  focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center items-center w-full h-24">
                                        <button type="button" data-mop="DINERS"  class="mopButton text-white bg-[#2557D6]  focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm text-center inline-flex items-center w-full h-12">
                                            <i class="uil uil-transaction text-4xl mr-3"></i>
                                            <span class="text-2xl font-bold">DINERS</span>
                                        </button>
                                        <input type="text" id="dinersMop" name="dinersMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- DOWNPAYMENT -->
                                    <div data-mop="DOWN PAYMENT" class="mopButton text-white bg-[#2557D6] focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center items-center  w-full h-24">
                                        <button type="button" data-mop="DOWN PAYMENT"  class="mopButton text-white bg-[#2557D6]  focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm  text-center inline-flex items-center w-full h-12">
                                            <i class="uil uil-transaction text-4xl mr-3"></i>
                                            <span class="text-1xl font-bold">DOWN PAYMENT</span>
                                        </button>
                                        <input type="text" id="dpMop" name="dpMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    

                                    

                                    <!-- GIFT CERT -->
                                    <div data-mop="GIFT CERT" class="mopButton text-white bg-[#2557D6] focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center items-center  mr-2 w-full h-24">
                                        <button type="button" data-mop="GIFT CERT"  class="mopButton text-white bg-[#2557D6] focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm  text-center inline-flex items-center  w-full h-12">
                                            <i class="uil uil-gift text-4xl mr-3"></i>
                                            <span class="text-2xl font-bold">GIFT CERT</span>
                                        </button>
                                        <input type="text" id="giftMop" name="giftMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- JCB -->
                                    <div data-mop="JCB" class="mopButton text-white bg-[#2557D6] focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center items-center  mr-2 w-full h-24">
                                        <button type="button" data-mop="JCB"  class="mopButton text-white bg-[#2557D6] focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 w-full h-12">
                                            <i class="uil uil-bolt text-4xl mr-3"></i>
                                            <span class="text-2xl font-bold">JCB</span>
                                        </button>
                                        <input type="text" id="jcbMop" name="jcbMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>


                                    

                                    <!-- PAYMAYA -->
                                    <div data-mop="PAYMAYA" class="mopButton text-white bg-[#2cbb3d] focus:ring-4 focus:ring-[#2cbb3d]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center  items-center  mr-2 w-full h-24">
                                    
                                        <button type="button" data-mop="PAYMAYA"  class="mopButton text-white bg-[#2cbb3d] focus:ring-4 focus:ring-[#2cbb3d]/50 focus:outline-none font-medium rounded-lg text-sm text-center inline-flex items-center  w-full h-12">
                                            <i class="uil uil-receipt text-4xl mr-3"></i>
                                            <span class="text-2xl font-bold">PAYMAYA</span>
                                        </button>
                                        <input type="text" id="mayaMop" name="mayaMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- PAYMAYA QR data-modal-hide="MOPModal" data-modal-target="detailsModal" data-modal-toggle="detailsModal"-->
                                    <div data-mop="PAYMAYA QR" class="mopButton text-white bg-[#2cbb3d] focus:ring-4 focus:ring-[#2cbb3d]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center items-center  mr-2 w-full h-24">
                                        <button type="button" data-mop="PAYMAYA QR"  class="mopButton text-white bg-[#2cbb3d] focus:ring-4 focus:ring-[#2cbb3d]/50 focus:outline-none font-medium rounded-lg text-sm text-center inline-flex items-center  w-full h-12">
                                            <i class="uil uil-qrcode-scan text-4xl mr-3"></i>
                                            <span class="text-1xl font-bold">PAYMAYA QR</span>
                                        </button>
                                        <input type="text" id="mayaQrMop" name="mayaQrMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    <!-- UNION BANK data-modal-hide="MOPModal" data-modal-target="detailsModal" data-modal-toggle="detailsModal" -->
                                    <div data-mop="UNION BANK" class="mopButton text-white bg-[#da8632] focus:ring-4 focus:ring-[#da8632]/50 focus:outline-none font-medium rounded-lg text-sm px-5 text-center items-center  mr-2 w-full h-24">
                                        <button type="button" data-mop="UNION BANK"  class="mopButton text-white bg-[#da8632] focus:ring-4 focus:ring-[#da8632]/50 focus:outline-none font-medium rounded-lg text-sm text-center inline-flex items-center  mr-2 w-full h-12">
                                            <i class="uil uil-university text-4xl mr-3"></i>
                                            <span class="text-1xl font-bold">UNION BANK</span>
                                        </button>
                                        <input type="text" id="unionMop" name="unionMop" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 mb-2 text-center" disabled>
                                    </div>

                                    

                                    
                                    
                                </div>
                            </div>
                            <div>
                            <!-- right content -->
                            {{--<div>
                                    <div class="grid grid-cols-2 gap-2 overflow-y-auto overflow-x-hidden mb-2">
                                        <label for="base-input" class="block  text-sm font-medium text-gray-900">Customer Name
                                        <input type="text" id="payor_name" name="payor_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        </label>
                                        <label for="base-input" class="block  text-sm font-medium text-gray-900">Acc/Cp Number
                                        <input type="text" id="payor_number" name="payor_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        </label>
                                        
                                    
                                    </div>
                                    <div class="mb-2">
                                        <label for="base-input" class="block  text-sm font-medium text-gray-900">Order Remarks
                                        <input type="text" id="order_remarks" name="payor_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        </label>
                                    </div>
                                </div>--}}
                                <div class="mb-3 flex justify-between w-full">
                                    <span class="text-1xl">Amount : ₱ <span id="actualAmount"></span></span>
                                    <span>MOP : <span id="selectedMOPtxt"><span class="text-red-600">Select Payment Method First</span></span></span>
                                    
                                </div>
                                <div class=" overflow-y-auto overflow-x-hidden mb-3">
                                    <div class="grid grid-cols-4 gap-2 w-4/6">
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="7">7</button>
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="8">8</button>
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="9">9</button>
                                        <button type="button" class="numpad-button row-span-2 border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="bs"><i class="uil uil-arrow-left"></i></button>

                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="4">4</button>
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="5">5</button>
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="6">6</button>

                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="1">1</button>
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="2">2</button>
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="3">3</button>
                                        <button type="button" class="numpad-button row-span-2 border shadow bg-gray-50 focus:scale-80 font-bold" data-value="clr">CLEAR</button>

                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="0">0</button>
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value="00">00</button>
                                        <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-80 text-xl font-bold" data-value=".">.</button>
                                    </div>
                                </div>

                                <!-- total section -->
                                <div class="flex-inline text-center">
                                    <label for="" class="text-1xl">Total : </label>
                                    <span id="totalPayment" class="text-2xl">0</span>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="flex-inline text-center">
                        <!-- <input type="text" id="mopselected"> -->
                        <p id="amountError" class="hidden text-2xl text-red-500 italic">Invalid amount. Please enter an amount more than or equal to <span class=" font-medium ml-1"> ₱ </span><span id="actualAmount2" class=" font-bold text-2xl"></span></p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-2 space-x-6 border-t border-gray-300 rounded-b">
                        <button  id="payNowButton" data-amount="" type="button" class="text-white bg-emerald-600 hover:bg-emerald-500 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-2xl font-medium px-5 py-5 hover:text-white focus:z-10 w-full">Submit</button>
                        <button data-modal-hide="MOPModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-2xl font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-full">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- MOP MODAL END --}}

    {{-- AMOUNT RECEIVED MODAL 
        <!-- Modal toggle -->
        <button data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" id="openAmountReceivedModal" class="hidden" type="button"></button>


        <div id="amountReceivedModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div  class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Pay Now
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="amountReceivedModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <div class="mb-2">
                            <label for="amount" class="block mb-2 text-base font-medium text-gray-900">Amount Received</label>
                            <input type="text" id="amount" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                            <p id="amountError" class="text-sm text-gray-600 italic">Enter an amount more than or equal to <span class="text-base font-medium ml-1"> ₱ </span><span id="actualAmount" class="text-base font-bold"></span></p>
                        </div>
                        <div class="w-full h-auto rounded-lg flex flex-col justify-between px-32">
                            <div class="grid grid-cols-4 gap-4">
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="7">7</button>
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="8">8</button>
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="9">9</button>
                                <button type="button" class="numpad-button row-span-2 border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="bs"><i class="uil uil-arrow-left"></i></button>

                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="4">4</button>
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="5">5</button>
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="6">6</button>

                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="1">1</button>
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="2">2</button>
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="3">3</button>
                                <button type="button" class="numpad-button row-span-2 border shadow bg-gray-50 focus:scale-95 font-bold" data-value="clr">CLEAR</button>

                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="0">0</button>
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="00">00</button>
                                <button type="button" class="numpad-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value=".">.</button>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="payNowButton" data-amount="" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Mark as Paid</button>
                        <button id="payNowCancelButton" data-modal-hide="amountReceivedModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
     AMOUNT RECEIVED MODAL END --}}

    {{-- CHANGE MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="changeModal" data-modal-toggle="changeModal" id="openChangeModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="changeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-[99] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-800 bg-opacity-70">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Change
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="changeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4 flex text-4xl leading-relaxed text-gray-500 font-semibold justify-center items-center">
                        ₱<p id="changeP" class="ml-2"></p>.00
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button id="changeCloseModal" data-modal-hide="changeModal" type="button"  class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-full">Close</button>
                        <!-- onclick="window.location.reload()" -->
                    </div>
                </div>
            </div>
        </div>
    {{-- CHANGE MODAL END --}}


    {{-- SELECTED TABLE MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="selectedTableModal" data-modal-toggle="selectedTableModal" id="openSelectedTableModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="selectedTableModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-4xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 id="tableName" class="text-2xl font-semibold text-gray-900"></h3>
                        
                        <div class="flex items-center">
                            {{--<button  data-modal-target="selectedTableModalAddOrder" data-modal-toggle="selectedTableModalAddOrder" id="openSelectedTableModal" class="rounded-lg bg-blue-700 text-white pt-2 pb-2 ps-4 pe-4 font-bold me-2">
                                <!-- <i class="uil uil-plus text-xl text-white bg-blue-700 p-2 rounded-xl"></i> -->
                                Add Order
                            </button>--}}
                            <div id="paidDiv" class="text-white me-2">
                            </div>
                            <div id="printDiv" class="bg-gray-200 rounded-lg">
                            </div>
                            <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-5 inline-flex items-center" data-modal-hide="selectedTableModal">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  

                            </button>
                        </div>
                    </div>
                    <!-- Modal body -->
                    <div class="px-4 py-2 space-y-4 h-[500px] overflow-y-auto">
                        <input type="hidden" name="table" id="table">
                        <p class="text-xl leading-relaxed text-gray-500 font-semibold">
                            <div id="allOrders" class="relative h-auto">

                            </div>
                            <div id="OrderDiscount" class="relative h-auto">
                            
                            </div>
                            
                        </p>

                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <div id="completeOrderDiv" class="w-1/3"></div>
                        @if (Auth::user()->role == 6)
                        <div id="cancelOrderDiv" class="w-1/3"></div>
                        @endif
                        <button data-modal-hide="selectedTableModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-base font-bold px-5 py-5 hover:text-gray-900 focus:z-10 w-1/3">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- SELECTED TABLE END --}}


    {{-- SELECTED TABLE ADDITIONAL ORDER START --}}
        <!-- Modal toggle -->
        <button data-modal-target="selectedTableModalAddOrder" data-modal-toggle="selectedTableModalAddOrder" id="openSelectedTableModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="selectedTableModalAddOrder" tabindex="-1" aria-hidden="true" class="bg-gray-300 fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full">
            <div class=" w-full h-full ">
                <!-- Modal content -->
                <div class=" bg-white rounded-lg shadow h-full">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 id="tableName" class="text-2xl font-semibold text-gray-900"> </h3>
                        
                        <div class="flex items-center">
                            <div id="paidDiv" class="mr-7">
                                
                            </div>
                            <div id="printDiv">
                                
                            </div>
                            <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-5 inline-flex items-center" data-modal-hide="selectedTableModal">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                                
                            </button>
                        </div>
                    </div>
                    <!-- Modal body -->
                    <div class="flex px-4 py-2 h-[550px] overflow-y-auto bg-white">
                        <div class=" rounded-xl border-gray-200 border-[1px] w-[130vh] me-1 ">
                            <div id="allOrders" class="">
                                <div class="bg-white flex flex-col items-start rounded-t-xl">
                                    <div class="flex h-10 w-full items-center justify-start gap-3 mb-2 ms-2 me-2 pb-3 pt-3 mt-3">
                                        <span class="rounded-3xl border-[1px] w-1/2 pt-1 pb-1 ps-5">
                                            Search
                                        </span>
                                    </div>
                                    <div class="flex h-10 w-auto items-center justify-start gap-3 mt-2 mb-2 ms-2 me-2 pb-3 pt-3">
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">All</div>
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">All Day Breakfast</div>
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">Pork</div>
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">Chicken</div>
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">Beef</div>
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">Cold Drinks</div>
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">Hot Drinks</div>
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">Appetizers</div>
                                        <div class="border-[1px] pt-1 pb-1 ps-3 pe-3 rounded-xl">Combo</div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-5 gap-5 h-full items-center justify-center mt-5 mb-2 ms-2 me-2">
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                    <div class="flex flex-col text-center bg-blue-300 pt-2 pb-2 ps-3 pe-3 rounded-xl">
                                        Fresh Spring Rolls
                                        <span class="border-t-2 mt-2">
                                            ₱ 150.00
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div id="OrderDiscount" class="relative h-auto"></div>
                        </div>
                        <div class="bg-white w-[70vh] h-full border-2 border-gray-200 rounded-xl">
                            <div class="flex flex-col items-center p-5">
                                <!-- ORDER -->
                                <div class="grid grid-cols-12 pt-2 pb-2 items-center text-center">
                                    <div class="col-span-3 flex items-center">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-comment-alt-dots pe-3 ps-3"></i></button>
                                    </div>
                                    <div class="col-span-3 text-xs items-start">
                                        Breakfast DSC Kaprao Chicken
                                    </div>
                                    <div class="col-span-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-minus text-xl text-red-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-1 text-xs">
                                        <span>
                                            1
                                        </span>
                                    </div>
                                    <div class="col-span-">
                                        <button class="bg-emerald-200 rounded"><i class="uil uil-plus text-xl text-emerald-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-2 text-xs">
                                        <span>
                                            245.00
                                        </span>
                                    </div>
                                    <div class="col-span-1">
                                        <button class=" w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200 p-1"></i></button>
                                    </div>
                                </div>
                                <!-- END OF ORDER -->

                                <!-- ORDER -->
                                <div class="grid grid-cols-12 pt-2 pb-2 items-center text-center">
                                    <div class="col-span-3 flex items-center">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-comment-alt-dots pe-3 ps-3"></i></button>
                                    </div>
                                    <div class="col-span-3 text-xs items-start">
                                        Breakfast DSC Kaprao Chicken
                                    </div>
                                    <div class="col-span-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-minus text-xl text-red-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-1 text-xs">
                                        <span>
                                            1
                                        </span>
                                    </div>
                                    <div class="col-span-">
                                        <button class="bg-emerald-200 rounded"><i class="uil uil-plus text-xl text-emerald-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-2 text-xs">
                                        <span>
                                            245.00
                                        </span>
                                    </div>
                                    <div class="col-span-1">
                                        <button class=" w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200 p-1"></i></button>
                                    </div>
                                </div>
                                <!-- END OF ORDER -->
                                 <!-- ORDER -->
                                <div class="grid grid-cols-12 pt-2 pb-2 items-center text-center">
                                    <div class="col-span-3 flex items-center">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-comment-alt-dots pe-3 ps-3"></i></button>
                                    </div>
                                    <div class="col-span-3 text-xs items-start">
                                        Breakfast DSC Kaprao Chicken
                                    </div>
                                    <div class="col-span-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-minus text-xl text-red-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-1 text-xs">
                                        <span>
                                            1
                                        </span>
                                    </div>
                                    <div class="col-span-">
                                        <button class="bg-emerald-200 rounded"><i class="uil uil-plus text-xl text-emerald-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-2 text-xs">
                                        <span>
                                            245.00
                                        </span>
                                    </div>
                                    <div class="col-span-1">
                                        <button class=" w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200 p-1"></i></button>
                                    </div>
                                </div>
                                <!-- END OF ORDER -->
                                <!-- ORDER -->
                                <div class="grid grid-cols-12 pt-2 pb-2 items-center text-center">
                                    <div class="col-span-3 flex items-center">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-comment-alt-dots pe-3 ps-3"></i></button>
                                    </div>
                                    <div class="col-span-3 text-xs items-start">
                                        Breakfast DSC Kaprao Chicken
                                    </div>
                                    <div class="col-span-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-minus text-xl text-red-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-1 text-xs">
                                        <span>
                                            1
                                        </span>
                                    </div>
                                    <div class="col-span-">
                                        <button class="bg-emerald-200 rounded"><i class="uil uil-plus text-xl text-emerald-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-2 text-xs">
                                        <span>
                                            245.00
                                        </span>
                                    </div>
                                    <div class="col-span-1">
                                        <button class=" w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200 p-1"></i></button>
                                    </div>
                                </div>
                                <!-- END OF ORDER -->
                                <!-- ORDER -->
                                <div class="grid grid-cols-12 pt-2 pb-2 items-center text-center">
                                    <div class="col-span-3 flex items-center">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-comment-alt-dots pe-3 ps-3"></i></button>
                                    </div>
                                    <div class="col-span-3 text-xs items-start">
                                        Breakfast DSC Kaprao Chicken
                                    </div>
                                    <div class="col-span-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-minus text-xl text-red-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-1 text-xs">
                                        <span>
                                            1
                                        </span>
                                    </div>
                                    <div class="col-span-">
                                        <button class="bg-emerald-200 rounded"><i class="uil uil-plus text-xl text-emerald-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-2 text-xs">
                                        <span>
                                            245.00
                                        </span>
                                    </div>
                                    <div class="col-span-1">
                                        <button class=" w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200 p-1"></i></button>
                                    </div>
                                </div>
                                <!-- END OF ORDER -->
                                <!-- ORDER -->
                                <div class="grid grid-cols-12 pt-2 pb-2 items-center text-center">
                                    <div class="col-span-3 flex items-center">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <input type="checkbox" id="checkbox" class="form-checkbox h-5 w-5 text-blue-600 me-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-comment-alt-dots pe-3 ps-3"></i></button>
                                    </div>
                                    <div class="col-span-3 text-xs items-start">
                                        Breakfast DSC Kaprao Chicken
                                    </div>
                                    <div class="col-span-1">
                                        <button class="bg-red-200 rounded"><i class="uil uil-minus text-xl text-red-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-1 text-xs">
                                        <span>
                                            1
                                        </span>
                                    </div>
                                    <div class="col-span-">
                                        <button class="bg-emerald-200 rounded"><i class="uil uil-plus text-xl text-emerald-900 p-1"></i></button>
                                    </div>
                                    <div class="col-span-2 text-xs">
                                        <span>
                                            245.00
                                        </span>
                                    </div>
                                    <div class="col-span-1">
                                        <button class=" w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200 p-1"></i></button>
                                    </div>
                                </div>
                                <!-- END OF ORDER -->
                                
                                
                                <!-- SUBTOTAL -->
                                <div class=" w-full">
                                    <div class="text-sm flex justify-between items-center">
                                        <div>
                                            # of Pax:
                                        </div>
                                        <div>
                                            <input type="text" class="w-10 h-[32px] rounded-xl border-gray-200">
                                        </div>
                                    </div>
                                    <div class="font-bold flex justify-between items-center">
                                        <div>
                                            Subtotal
                                        </div>
                                        
                                        <div>
                                            ₱ 1000.00
                                        </div>
                                    </div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span class="text-xs">
                                                PWD/SrCitizen
                                            </span>
                                            <button class="flex">
                                                <i class="uil uil-edit ms-3 text-blue-700 text-md me-1 "></i>
                                                <i class="uil uil-trash-alt text-red-700 text-md"></i>
                                            </button>
                                        </div>
                                        <div class="text-xs">
                                            <span>
                                                %10
                                            </span>
                                            <span>
                                                ₱ 1000.00
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span class="text-xs">
                                                Discount
                                            </span>
                                            <button class="flex">
                                                <i class="uil uil-edit ms-3 text-blue-700 text-md me-1 "></i>
                                            </button>
                                        </div>
                                        <div class="text-xs">
                                            <span>
                                                %10
                                            </span>
                                            <span>
                                                ₱ 1000.00
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span class="text-xs">
                                                Service Charge
                                            </span>
                                            <button class="flex">
                                                <i class="uil uil-edit ms-3 text-blue-700 text-md me-1 "></i>
                                            </button>
                                        </div>
                                        <div class="text-xs">
                                            <span>
                                                %10
                                            </span>
                                            <span>
                                                ₱ 1000.00
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-lg font-bold flex justify-between">
                                        <div>
                                            TOTAL
                                        </div>
                                        <div>
                                            ₱ 1000.00
                                        </div>
                                    </div>
                                </div>
                                

                                <!-- END OF SUBTOTAL -->
                                <!-- COMMIT BUTTON -->
                                <div class="w-full">
                                    <button class="bg-blue-300 rounded-lg px-3 py-3 font-bold text-white text-xs w-full mt-2">
                                        Commit
                                    </button>
                                </div>
                                <!-- COMMIT END BUTTON -->
                            </div>
                        </div>
                        

                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center border-t border-gray-200 rounded-b pt-3">
                        <div id="completeOrderDiv" class="w-1/3"></div>
                        @if (Auth::user()->role == 6)
                        <div id="cancelOrderDiv" class="w-1/3"></div>
                        @endif
                        <button data-modal-hide="selectedTableModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-base font-bold px-5 py-5 hover:text-gray-900 focus:z-10 w-1/3">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- SELECTED TABLE ADDITIONAL ORDER END --}}



    <div class="p-6 h-[calc(100vh-48px)]  lg:ml-64">
        @csrf
        <div class="w-full grid grid-cols-10 gap-6">
        @foreach (range (1, 7) as $category)
                @php
                    $tableChunks = $tables->filter(fn($table) => $table->category == $category)->chunk(10);
                @endphp

                @foreach ($tableChunks as $chunk)
                    <div class="column">
                        @foreach ($chunk as $table)
                            <div class="mb-3 {{ ($table->status == 0) ? 'bg-emerald-500' : 'bg-red-500'; }} w-full rounded-xl">
                                <button type="button" data-table="{{ $table->id }}" data-tablename="{{ $table->name }}" data-status="{{ $table->status }}" class="w-full h-[60px] tableButton rounded-xl">
                                    <p class="w-full text-center text-1xl font-bold">{{ $table->name }}</p>
                                    <!-- <img src="{{ asset('storage/images/ico/table-noBG.png') }}" alt="" class="w-12 h-12"> -->
                                    
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endforeach
                
            @endforeach
        </div>
        <input type="hidden" id="mop" name="mop">
    </div>


    <input type="hidden" id="auditVal" value=" {{ $audited }} ">
    <input type="hidden" id="notAuditVal" value=" {{ $nonAudited }} ">
    <script>
        $(document).ready(function() {
            var slug;
            var table;
            var id;
            var _token = $('input[name="_token"]').val();
            $('#serveAmount').text($('#auditVal').val());
            $('#notServeAmount').text($('#notAuditVal').val());


            $('.tableButton').click(function(){
                id = $(this).data('table');
                var name = $(this).data('tablename');
                var status = $(this).data('status');
                
                //alert(id)
                if(status != 0){
                    $.ajax({
                        url:"{{ route('orders.getMenu') }}",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            id: id,
                            _token: _token
                        },
                        success:function(result){
                            $('#allOrders').html(result.allOrders);
                            //$('#OrderDiscount').html(result.disc);
                            if(result.pd == '1'){
                                $('#paidDiv').html('');
                            $('#printDiv').html(``);
                            }else{
                                $('#paidDiv').html('<button id="paidButton" class="flex items-center px-4 py-2 rounded-lg bg-emerald-400"><span class="font-semibold">Mark as PAID</span></button>');
                                $('#printDiv').html(`<a href="{{ url('/orders/print/${id}') }}" target="_blank" class="flex items-center border border-gray-400 px-4 py-1 rounded-lg"><i class="uil uil-print text-xl mr-1"></i><span class="font-semibold">Print Bill</span></a>`);
                            }

                            if(result.co == '1'){
                                $('#completeOrderDiv').html('<button id="completeOrderButton" data-modal-hide="selectedTableModal" data-slug="" type="button" class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-lg text-base py-5 text-center w-full">Open Table</button>');
                                $('#cancelOrderDiv').html('');
                            }else{
                                $('#completeOrderDiv').html('');
                                $('#cancelOrderDiv').html('<button id="cancelOrderButton" data-modal-hide="selectedTableModal" data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-bold rounded-lg text-base py-5 text-center w-full">Cancel Order</button>');
                            }
                            $('#table').val(id);
                            $('#lime_discount').val(result.limeDiscountPercent);
                            $('#customer_with_discount').val(result.customer_with_discount);
                            $('#openSelectedTableModal').click();
                        }
                    })
                }else{
                    $('#completeOrderDiv').html('<button id="occupyButton" data-modal-hide="selectedTableModal" type="button" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-bold rounded-lg text-base py-5 text-center w-full">Occupy Table</button>');
                    $('#cancelOrderDiv').html('');
                    $('#printDiv').html('');
                    $('#paidDiv').html('');
                    $('#allOrders').html('This table is not occupied. test');
                    $('#openSelectedTableModal').click();
                }
                $('#tableName').html(name);
            })


            jQuery(document).on("click", ".reduceButton", function(){
                slug = $(this).data('slug');
                table = $(this).data('table');
                $('#voidCodeReduce').val('');
                $('#openReduceModal').click();
            });


            jQuery(document).on("click", "#acceptReduceButton", function(){

                voidCode = $('#voidCodeReduce').val();

                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url:"{{ route('orders.reduce') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        table: table,
                        voidCode : voidCode,
                        _token: _token
                    },
                    success:function(result){
                        if (result.msg == 'success') {
                            $('#allOrders').html(result.allOrders);
                            console.log(result.co);
                            if(result.co == '1'){
                                $('#completeOrderDiv').html('<button id="completeOrderButton" data-modal-hide="selectedTableModal" data-slug="" type="button" class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-lg text-base py-5 text-center w-full">Open Table</button>');

                                $('#cancelOrderDiv').html('');
                            }else{
                                $('#completeOrderDiv').html('');

                                $('#cancelOrderDiv').html('<button id="cancelOrderButton" data-modal-hide="selectedTableModal" data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-bold rounded-lg text-base py-5 text-center w-full">Cancel Order</button>');
                            }
                            $('#loadingScreen').addClass('hidden');
                            $('#hideReduceModalBtn').click();
                            $('.removeModalHide').click();
                            $('#loadingScreen').addClass('hidden');
                            $('#orderedSubtotal').html(result.subTotal);
                            $('#orderedPwd').html(result.discount);
                            $('#orderedOwnerDisc').html(result.limeDiscountVal);
                            $('#orderedSc').html(result.ServiceCharge);
                            $('#orderedTotal').html(result.total);
                        } else{
                            $('#loadingScreen').addClass('hidden');
                            $('#errorVoidCodeReduce').html('Security Code is incorrect! Please ask the administrator.');
                        }
                    }
                })
            });


            jQuery(document).on("click", ".removeButton", function(){
                slug = $(this).data('slug');
                table = $(this).data('table');
                $('#voidCode').val('');
                $('#openRemoveModal').click();
            });


            jQuery(document).on("click", "#acceptRemoveButton", function(){
                
                voidCode = $('#voidCode').val();

                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url:"{{ route('orders.remove') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        table: table,
                        voidCode : voidCode,
                        _token: _token
                    },
                    success:function(result){
                        if (result.msg == 'success') {
                            if(result.allOrders != '1'){
                                $('#allOrders').html(result.allOrders);
                                console.log(result.co);
                                if(result.co == '1'){
                                    $('#completeOrderDiv').html('<button id="completeOrderButton" data-modal-hide="selectedTableModal" data-slug="" type="button" class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-bold rounded-lg text-base py-5 text-center w-full">Open Table</button>');

                                    $('#cancelOrderDiv').html('');
                                }else{
                                    $('#completeOrderDiv').html('');

                                    $('#cancelOrderDiv').html('<button id="cancelOrderButton" data-modal-hide="selectedTableModal" data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-bold rounded-lg text-base py-5 text-center w-full">Cancel Order</button>');
                                }
                            }else{
                                location.reload();
                            }
                        $('.removeModalHide').click();
                        $('#loadingScreen').addClass('hidden');
                        $('#orderedSubtotal').html(result.subTotal);
                        $('#orderedPwd').html(result.discount);
                        $('#orderedOwnerDisc').html(result.limeDiscountVal);
                        $('#orderedSc').html(result.ServiceCharge);
                        $('#orderedTotal').html(result.total);
                        } else {
                            $('#loadingScreen').addClass('hidden');
                            $('#errorVoidCode').html('Security Code is incorrect! Please ask the administrator.');

                        }
                        
                    }
                })
            });

            jQuery(document).on("click", "#cancelOrderButton", function(){
                $('#openCancelModal').click();
            });


            jQuery(document).on("click", "#acceptCancelButton", function(){
                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url:"{{ route('orders.cancel') }}",
                    method:"POST",
                    data:{
                        id: id,
                        _token: _token
                    },
                    success:function(result){
                        location.reload();
                        $('#loadingScreen').addClass('hidden');
                    }
                })
            });

            jQuery(document).on("click", "#completeOrderButton", function(){
                $('#openOpenModal').click();
            });


            jQuery(document).on("click", "#acceptOpenButton", function(){
                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url:"{{ route('orders.open') }}",
                    method:"POST",
                    data:{
                        id: id,
                        _token: _token
                    },
                    success:function(result){
                        location.reload();
                        $('#loadingScreen').addClass('hidden');
                    }
                })
            });


            jQuery(document).on("click", "#occupyButton", function(){
                $('#occupyOpenModal').click();
            });


            jQuery(document).on("click", "#acceptOccupyButton", function(){
                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url:"{{ route('orders.occupy') }}",
                    method:"POST",
                    data:{
                        id: id,
                        _token: _token
                    },
                    success:function(result){
                        location.reload();
                        $('#loadingScreen').addClass('hidden');
                    }
                })
            });



            jQuery(document).on("click", "#paidButton", function(){
                $.ajax({
                        url:"{{ route('orders.getAmount') }}",
                        method:"POST",
                        data:{
                            id: id,
                            _token: _token
                        },
                        success:function(result){
                            $('#actualAmount').html(result);
                            $('#actualAmount2').html(result);
                            $('#payNowButton').data('amount', result);
                            $('#loadingScreen').addClass('hidden');
                            $('#cashMop').val('');
                            $('#ccMop').val('');
                            $('#visaMop').val('');
                            $('#gcashMop').val('');
                            $('#ggivesMop').val('');
                            $('#amexMop').val('');
                            $('#banktMop').val('');
                            $('#debitMop').val('');
                            $('#dinersMop').val('');
                            $('#dpMop').val('');
                            $('#giftMop').val('');
                            $('#jcbMop').val('');
                            $('#mayaMop').val('');
                            $('#mayaQrMop').val('');
                            $('#unionMop').val('');
                            $('#totalPayment').text('0');
                            $('#openMOPModal').click();
                        }
                })
                
            });
            jQuery(document).on("click", "#deleteDiscountButton", function(){
                $('#openRemoveDiscountModal').click();
            });
            jQuery(document).on("click", "#DiscountButton", function(){
                
                $('#openDiscountModal').click();
                
                $('.tableIDforDiscount').val(id);
                //alert($('#tableIDforDiscount').val());
            });

            jQuery(document).on("click", "#scBtn", function(){
                
                $('#openServiceChargeModal').click();
                
                $('.tableIDforDiscount').val(id);
                //alert($('#tableIDforDiscount').val());
            });

            jQuery(document).on("click", "#LimeDiscountButton", function(){
                
                $('#openLimeDiscountModal').click();
                
                $('.tableIDforDiscount').val(id);
                //alert($('#tableIDforDiscount').val());
            });

            jQuery(document).on("click", ".scSubmitBtn , .submitDiscountButton, .submitLimeDiscountButton", function(){
                //var totalcus = $('#total_customer').val();
                var cust_with_discount = $('#customer_with_discount').val();
                var service_charge = $('#service_charge').val();
                var tableId = $('.tableIDforDiscount').val();
                var lime_discount = $('#lime_discount').val();
                

                $.ajax({
                    url:"{{ route('orders.OrderupdateDiscount') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        tableId: tableId,
                        cust_with_discount: cust_with_discount,
                        service_charge: service_charge,
                        lime_discount : lime_discount,
                        _token: _token
                    },
                    success:function(result){
                        $('#orderedSubtotal').html(result.subTotal);
                        $('#orderedPwd').html(result.discount);
                        $('#orderedOwnerDisc').html(result.limeDiscountVal);
                        $('#orderedSc').html(result.ServiceCharge);
                        $('#orderedTotal').html(result.total);
                        $('#limeDiscountSpan').html(result.limeDiscountPercent);
                        $('#scSpan').html(result.scPercent);
                        //alert(result)
                        // $('#actualAmount').html(result);
                        // $('#payNowButton').data('amount', result);
                        // $('#loadingScreen').addClass('hidden');
                        //window.location.reload()
                    }
                })
                //alert($('#tableIDforDiscount').val());
            });

            jQuery(document).on("click", "#acceptRemoveDiscountButton", function(){
                var totalcus = $('#total_customer').val();
                var cust_with_discount = $('#customer_with_discount').val();
                var service_charge = $('#service_charge').val();
                var tableId = $('.tableIDforDiscount').val();
                var lime_discount = $('#lime_discount').val();

                $.ajax({
                    url:"{{ route('orders.OrderdeleteDiscount') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        tableId: tableId,
                        totalcus: totalcus,
                        cust_with_discount: cust_with_discount,
                        service_charge: service_charge,
                        lime_discount : lime_discount,
                        _token: _token
                    },
                    success:function(result){
                        //alert(result)
                        // $('#actualAmount').html(result);
                        // $('#payNowButton').data('amount', result);
                        // $('#loadingScreen').addClass('hidden');
                        //window.location.reload()
                    }
                })
                //alert($('#tableIDforDiscount').val());
            });
            

            

            jQuery(document).on("click", "#payNowButton", function(){
                
                var amount = parseFloat($(this).data('amount'));
                var amountInput = $('#totalPayment').text();
                var namountInput = parseFloat(amountInput);
                var mop = $('#mop').val();
                //alert(namountInput + ' yest ' + amount);
                var cashMop = ( $('#cashMop').val() == '' ) ? 0 : parseFloat( $('#cashMop').val() );
                var ccMop = ( $('#ccMop').val() == '' ) ? 0 : parseFloat( $('#ccMop').val() );
                var visaMop = ( $('#visaMop').val() == '' ) ? 0 : parseFloat( $('#visaMop').val() );
                var gcashMop = ( $('#gcashMop').val() == '' ) ? 0 : parseFloat( $('#gcashMop').val() );
                var ggivesMop = ( $('#ggivesMop').val() == '' ) ? 0 : parseFloat( $('#ggivesMop').val() );
                var amexMop = ( $('#amexMop').val() == '' ) ? 0 : parseFloat( $('#amexMop').val() );
                var banktMop = ( $('#banktMop').val() == '' ) ? 0 : parseFloat( $('#banktMop').val() );
                var debitMop = ( $('#debitMop').val() == '' ) ? 0 : parseFloat( $('#debitMop').val() );
                var dinersMop = ( $('#dinersMop').val() == '' ) ? 0 : parseFloat( $('#dinersMop').val() );
                var dpMop = ( $('#dpMop').val() == '' ) ? 0 : parseFloat( $('#dpMop').val() );
                var giftMop = ( $('#giftMop').val() == '' ) ? 0 : parseFloat( $('#giftMop').val() );
                var jcbMop = ( $('#jcbMop').val() == '' ) ? 0 : parseFloat( $('#jcbMop').val() );
                var mayaMop = ( $('#mayaMop').val() == '' ) ? 0 : parseFloat( $('#mayaMop').val() );
                var mayaQrMop = ( $('#mayaQrMop').val() == '' ) ? 0 : parseFloat( $('#mayaQrMop').val() );
                var unionMop = ( $('#unionMop').val() == '' ) ? 0 : parseFloat( $('#unionMop').val() );


                if(namountInput >= amount){
                    $('#loadingScreen').removeClass('hidden');
                    var change = amountInput - amount;
                    $('#payNowCancelButton').click();
                    $('#changeP').html(change);

                    $.ajax({
                        url:"{{ route('orders.paid') }}",
                        method:"POST",
                        data:{
                            id: id,
                            amountInput: amountInput,
                            mop: mop,
                            cashMop : cashMop,
                            ccMop : ccMop ,
                            visaMop : visaMop ,
                            gcashMop : gcashMop ,
                            ggivesMop : ggivesMop ,
                            amexMop : amexMop ,
                            banktMop : banktMop ,
                            debitMop : debitMop ,
                            dinersMop : dinersMop ,
                            dpMop : dpMop ,
                            giftMop : giftMop ,
                            jcbMop : jcbMop ,
                            mayaMop : mayaMop ,
                            mayaQrMop : mayaQrMop ,
                            unionMop : unionMop ,
                            _token: _token
                        },
                        success:function(result){
                            $('#openChangeModal').click();
                            $('#loadingScreen').addClass('hidden');
                        }
                    })
                }else{
                    $('#amountError').removeClass('hidden');
                }
            });
            

            /* $('.numpad-button').on('click', function() {
                const $amount = $('#amount');
                const buttonValue = $(this).data('value');

                if (buttonValue == 'bs') {
                    $amount.val($amount.val().slice(0, -1));
                }else if(buttonValue == 'clr'){
                    $amount.val('');
                }else if(buttonValue !== undefined){
                    $amount.val($amount.val() + buttonValue);

                    var inputValue = $('#amount').val();

                    // var dotIndex = inputValue.indexOf('.');
                    // if (dotIndex !== -1 && inputValue.indexOf('.', dotIndex + 1) !== -1) {
                    //     $('#amount').val(inputValue.substr(0, dotIndex + 1) + inputValue.substr(dotIndex + 1).replace(/\./g, ''));
                    // }
                }
            }); */

            // $('#amount').on('keyup', function() {
            //     var inputValue = $(this).val();
            //     $(this).val(inputValue.replace(/[^0-9\.]/g, ''));
                
            //     var dotIndex = inputValue.indexOf('.');
            //     if (dotIndex !== -1 && inputValue.indexOf('.', dotIndex + 1) !== -1) {
            //     $(this).val(inputValue.substr(0, dotIndex + 1) + inputValue.substr(dotIndex + 1).replace(/\./g, ''));
            //     }
            // });

            $('#changeCloseModal').click(function(){
                location.reload();
            });

            $('#changeCloseModal').click(function(){
                //var table = $('#table').val();
                var url = `{{ url('/pos/print/${id}') }}`;
                window.open(url, '_blank');
                window.location.reload()
            });
            // jQuery(document).on("click", "#acceptPaidButton", function(){
            //     $('#loadingScreen').removeClass('hidden');
            //     $.ajax({
            //         url:"{{ route('orders.paid') }}",
            //         method:"POST",
            //         data:{
            //             id: id,
            //             _token: _token
            //         },
            //         success:function(result){
            //             location.reload();
            //             $('#loadingScreen').addClass('hidden');
            //         }
            //     })
            // });

            jQuery(document).on("click", ".complimentary", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var checkbox = $('#'+slug).is(":checked")
                
                $.ajax({
                    url:"{{ route('orders.comp') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        checkbox: checkbox
                    },
                    success:function(result){
                        $('#allOrders').html(result.allOrders);
                        // $('#orderedTotal').html(''); 
                        // $('#orderedSubtotal').html(''); 
                        // $('#orderedSubtotal').html(result.subtotal); 
                        // $('#orderedTotal').html(result.total); 
                        /* $('#ordersCount').html(result.ordersCount);
                        //$('#ordersCount').html(result.orders.length);
                        $('#ordersDiv').html(result.orders);
                        $('#subTotal').html(result.subTotal);
                        $('#discountTotal').html(result.discount);
                        $('#total').html(result.total);
                        $('#sc_val').val(result.ServiceCharge);
                        $('#sctotal').html(result.ServiceCharge);
                        $('#actualAmount').html(result.amount);
                        $('#actualAmount2').html(result.amount);
                        $('#payNowButton').data('amount', result.amount);
                        $('#limepercent').html(result.limeDiscountPercent);
                        $('#limediscountTotal').html(result.limeDiscountVal);

                        $('#LessVatDeduction').val(result.LessVatDeduction); 
                        $('#VatExemptSales').val(result.VatExemptSales); 
                        $('#vatsales').val(result.vatsales); 
                        $('#vat').val(result.vat); 
                        
                        $('#limediscountTotalValue').html(result.limediscount);
                         $('#discountTotalValue').html(result.discount); */
                    }
                })
            });

            jQuery(document).on("click", ".orderSelection", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var checkbox = $('#orderSelection'+slug).is(":checked")
                $.ajax({
                    url:"{{ route('orders.discountSelected') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        checkbox: checkbox
                    },
                    success:function(result){
                    }
                })
            });
            jQuery(document).on("click", ".addPwdButton", function(){
                $('#openAddPwdModal').click();
                var slug = $(this).data('slug');
                //alert(slug);
                $('#acceptPwdButton').data('slug',slug);
            });
            jQuery(document).on("click", ".closePwdBtn", function(){
                window.location.reload()
            });
            jQuery(document).on("click", "#acceptPwdButton", function(){
                var pwdName = $('#pwdName').val();
                var pwdIdNumber = $('#pwdIdNumber').val();
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var category = $('#category').val();

                $.ajax({
                    url:"{{ route('orders.addpwd') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        pwdName: pwdName,
                        category: category,
                        pwdIdNumber : pwdIdNumber
                    },
                    success:function(result){
                        $('#pwdName').val('');
                        $('#pwdIdNumber').val('');
                    }
                })
            });

            jQuery(document).on("click", ".deletePwdBtn", function(){
                $('#openremovePWDModal').click();
                var slug = $(this).data('slug');
                //alert(slug);
                $('#acceptRemovePwdButton').data('slug',slug);
            });


            jQuery(document).on("click", "#acceptRemovePwdButton", function(){
                
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('orders.delpwd') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token
                    },
                    success:function(result){
                        window.location.reload()
                    }
                })
            });

            jQuery(document).on("click", ".btnTransfer", function(){
                
                
                
                //$('.tableIDforDiscount').val(id);
                $.ajax({
                        url:"{{ route('orders.transferTable') }}",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            id: id,
                            _token: _token
                        },
                        success:function(result){
                            $('#transferTableDiv').html(result.tables);
                            $('#openTransferTableModal').click();
                            //$('#OrderDiscount').html(result.disc);
                            
                            
                        }
                    })
            });
            
            jQuery(document).on("click", "#acceptTransferTableButton", function(){
                
                var tranID = $('#tableOption').data('id');
                var NewTableId = $('#tableOption').val();
               //alert(NewTableId)
                //$('.tableIDforDiscount').val(id);
                $.ajax({
                        url:"{{ route('orders.updateTable') }}",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            tranID : tranID,
                            NewTableId : NewTableId,
                            _token: _token
                        },
                        success:function(result){
                            //$('#transferTableDiv').html(result.tables);
                            //$('#OrderDiscount').html(result.disc);
                            location.reload();
                            
                        }
                    })
            });

            $('#showPassword').click(function(){
                if ($("#voidCode").prop("type") == 'text'){
                    $("#voidCode").prop("type", "password");
                } else {
                    $("#voidCode").prop("type", "text");
                }
                
            });

            $('#showPasswordReduce').click(function(){
                if ($("#voidCodeReduce").prop("type") == 'text'){
                    $("#voidCodeReduce").prop("type", "password");
                } else {
                    $("#voidCodeReduce").prop("type", "text");
                }
                
            });

            jQuery(document).on("click", ".btnAr", function(){
                $('#openArModal').click();
                
            });

            $('#showPasswordAr').click(function(){
                if ($("#arCode").prop("type") == 'text'){
                    $("#arCode").prop("type", "password");
                } else {
                    $("#arCode").prop("type", "text");
                }
                
            });


            jQuery(document).on("click", "#acceptArButton", function(){

                var arCode = $('#arCode').val();
                var customer_name_ar = $('#customer_name_ar').val();

                $.ajax({
                    url:"{{ route('orders.moveToAr') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        id : id,
                        arCode : arCode,
                        customer_name_ar : customer_name_ar,
                        _token: _token
                    },
                    success:function(result){
                        //location.reload();
                        if (result.msg == 'success') {
                            alert("Transaction successfully tranferred to Account Receivables!")
                            location.reload();
                        } else {
                            $('#errorArCode').html('Security Code is incorrect! Please ask the administrator.');
                            //alert(result.msg)
                        }
                        
                    }
                })
                
            });


            $('#showPasswordMultiple').click(function(){
                if ($("#voidCodeMultiple").prop("type") == 'text'){
                    $("#voidCodeMultiple").prop("type", "password");
                } else {
                    $("#voidCodeMultiple").prop("type", "text");
                }
                
            });

            jQuery(document).on("click", ".btnMultipleVoid", function(){
                
                var slug = $(this).data('slug');
                
                $('#acceptMultipleVoidButton').data('slug', slug);
                $('#openMultipleVoidModal').click();
                //$('.tableIDforDiscount').val(id);
                
            });

            jQuery(document).on("click", "#acceptMultipleVoidButton", function(){
                var slug = $(this).data('slug');
                voidCode = $('#voidCodeMultiple').val();

                //$voidCode = $request->voidCode;
                var _token = $('input[name="_token"]').val();
                //$('#openMultipleVoidModal').click();
                //$('.tableIDforDiscount').val(id);
                $.ajax({
                    url:"{{ route('orders.multivoid') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        voidCode : voidCode,
                        _token: _token
                    },
                    success:function(result){
                        //
                        if (result.msg == 'success') {
                            location.reload();
                        } else {
                            $('#loadingScreen').addClass('hidden');
                            $('#errorVoidCodeMultiple').html('Security Code is incorrect! Please ask the administrator.');

                        }
                    }
                })
                
            });

            jQuery(document).on("click", ".delorderedBtn", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var checkbox = $('#multiVoid'+slug).is(":checked")
                //alert(checkbox);
                $.ajax({
                    url:"{{ route('orders.multivoidcheck') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        checkbox: checkbox
                    },
                    success:function(result){
                        //alert('Selected items successfully deleted!')
                    }
                })
            });
            
            jQuery(document).on("click", ".btnReprintBar", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                
                $.ajax({
                    url:"{{ route('orders.bar') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token
                    },
                    success:function(result){
                        alert('Bar Reprint Successful!');
                    }
                })
            });

            jQuery(document).on("click", ".btnReprintKitchen", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                
                $.ajax({
                    url:"{{ route('orders.kitchen') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token
                    },
                    success:function(result){
                        alert('Kitchen Reprint Successful!');
                    }
                })
            });

            $('.mopButton').click(function(){
                    var mop = $(this).data('mop');
                    //$('#mopselected').val(mop);
                    $('#selectedMOPtxt').text(mop);
                    //$('#paymentMethod').val('1');
                    
                    
                });

            $('.numpad-button').on('click', function() {
            var mopSelected = $('#selectedMOPtxt').text();
            
            if (mopSelected == 'Select Payment Method First'){
                alert('Please select Payment Method First!')
            } else {

                
                    var mop = '';

                    /* start of getting mode of payment value */
                    if (mopSelected == 'CASH'){
                        mop = 'cashMop';
                    } else if (mopSelected == 'CREDIT CARD - MASTERCARD'){
                        mop = 'ccMop';
                    } else if (mopSelected == 'VISA'){
                        mop = 'visaMop';
                    } else if (mopSelected == 'GCASH'){
                        mop = 'gcashMop';
                    } else if (mopSelected == 'GCASH CREDIT GIVES'){
                        mop = 'ggivesMop';
                    } else if (mopSelected == 'AMEX'){
                        mop = 'amexMop';
                    } else if (mopSelected == 'BANK TRANSFER'){
                        mop = 'banktMop';
                    } else if (mopSelected == 'DEBIT'){
                        mop = 'debitMop';
                    } else if (mopSelected == 'DINERS'){
                        mop = 'dinersMop';
                    } else if (mopSelected == 'DOWN PAYMENT'){
                        mop = 'dpMop';
                    } else if (mopSelected == 'GIFT CERT'){
                        mop = 'giftMop';
                    } else if (mopSelected == 'JCB'){
                        mop = 'jcbMop';
                    } else if (mopSelected == 'PAYMAYA'){
                        mop = 'mayaMop';
                    } else if (mopSelected == 'PAYMAYA QR'){
                        mop = 'mayaQrMop';
                    } else if (mopSelected == 'UNION BANK'){
                        mop = 'unionMop';
                    }

                    /* end of getting mode of payment value */

                    //alert(mop)

                    /* start of key click */
                    const $amount = $('#'+mop);
                    const buttonValue = $(this).data('value');

                    if (buttonValue == 'bs') {
                        $amount.val($amount.val().slice(0, -1));
                    }else if(buttonValue == 'clr'){
                        $amount.val('0');
                    }else if(buttonValue !== undefined){
                        $amount.val($amount.val() + buttonValue);

                        var inputValue = $('#'+mop).val();

                        var dotIndex = inputValue.indexOf('.');
                        if (dotIndex !== -1 && inputValue.indexOf('.', dotIndex + 1) !== -1) {
                            $('#'+mop).val(inputValue.substr(0, dotIndex + 1) + inputValue.substr(dotIndex + 1).replace(/\./g, ''));
                        }
                    }

                    /* end of key click */


                    /* Start of computation */

                    var cashMop = ( $('#cashMop').val() == '' ) ? 0 : parseFloat( $('#cashMop').val() );
                    var ccMop = ( $('#ccMop').val() == '' ) ? 0 : parseFloat( $('#ccMop').val() );
                    var visaMop = ( $('#visaMop').val() == '' ) ? 0 : parseFloat( $('#visaMop').val() );
                    var gcashMop = ( $('#gcashMop').val() == '' ) ? 0 : parseFloat( $('#gcashMop').val() );
                    var ggivesMop = ( $('#ggivesMop').val() == '' ) ? 0 : parseFloat( $('#ggivesMop').val() );
                    var amexMop = ( $('#amexMop').val() == '' ) ? 0 : parseFloat( $('#amexMop').val() );
                    var banktMop = ( $('#banktMop').val() == '' ) ? 0 : parseFloat( $('#banktMop').val() );
                    var debitMop = ( $('#debitMop').val() == '' ) ? 0 : parseFloat( $('#debitMop').val() );
                    var dinersMop = ( $('#dinersMop').val() == '' ) ? 0 : parseFloat( $('#dinersMop').val() );
                    var dpMop = ( $('#dpMop').val() == '' ) ? 0 : parseFloat( $('#dpMop').val() );
                    var giftMop = ( $('#giftMop').val() == '' ) ? 0 : parseFloat( $('#giftMop').val() );
                    var jcbMop = ( $('#jcbMop').val() == '' ) ? 0 : parseFloat( $('#jcbMop').val() );
                    var mayaMop = ( $('#mayaMop').val() == '' ) ? 0 : parseFloat( $('#mayaMop').val() );
                    var mayaQrMop = ( $('#mayaQrMop').val() == '' ) ? 0 : parseFloat( $('#mayaQrMop').val() );
                    var unionMop = ( $('#unionMop').val() == '' ) ? 0 : parseFloat( $('#unionMop').val() );

                    var total = 0;
                    total = cashMop + ccMop + visaMop + gcashMop + ggivesMop + amexMop + banktMop + debitMop + dinersMop + dpMop + giftMop + jcbMop + mayaMop + mayaQrMop + unionMop ;
                    $('#totalPayment').text(total.toFixed(2));

                    /* end of computation */

                }
            });
            
            jQuery(document).on("click", ".spliSelect", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var checkbox = $('#split'+slug).is(":checked")
                //alert(checkbox);
                $.ajax({
                    url:"{{ route('orders.splitcheck') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        checkbox: checkbox
                    },
                    success:function(result){
                        //alert('Selected items successfully deleted!')
                    }
                })
            });

            jQuery(document).on("click", ".btnSplit", function(){
                
                //$('#splitTableDiv').html('');
                $('#splitErrorDiv').addClass('hidden');
                //$('.tableIDforDiscount').val(id);
                $.ajax({
                        url:"{{ route('orders.splitTable') }}",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            id: id,
                            _token: _token
                        },
                        success:function(result){
                            $('#splitTableDiv').html(result.tables);
                            $('#openSplitTableModal').click();
                            //$('#OrderDiscount').html(result.disc);
                            
                            
                        }
                    })
            });

            jQuery(document).on("click", "#acceptSplitTableButton", function(){
                
                var tranID = $('#tableOptionSplit').data('id');
                var NewTableId = $('#tableOptionSplit').val();
                //alert(NewTableId + ' 2 ' + tranID)
                //$('.tableIDforDiscount').val(id);
                $.ajax({
                    url:"{{ route('orders.split') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        tranID : tranID,
                        NewTableId : NewTableId,
                        _token: _token
                    },
                    success:function(result){
                        //$('#transferTableDiv').html(result.tables);
                        //$('#OrderDiscount').html(result.disc);
                        if (result.msg == 'success'){
                            location.reload();
                        } else {
                            $('#splitErrorDiv').removeClass('hidden');
                        }
                        
                        
                    }
                })
            });

        });
    </script>
</x-app-layout>
