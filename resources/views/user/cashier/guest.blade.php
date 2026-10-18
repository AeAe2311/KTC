<x-app-layout>

    <style>
        #menuTabs::-webkit-scrollbar {
            height: 0px;
            background: transparent; 
        }

        #categoryAllContents::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }
    </style>

    @section('page_title', 'GUEST')
    @php 
        $roleUser = Auth::user()->role;
    @endphp
    <input type="hidden" id="cashierId" value="{{ (!empty($cashiers->cashier)) ? $cashiers->cashier : 0  }}">
    <input type="hidden" id="userRole" value="{{ $roleUser }}">

    {{-- LOADING --}}
        <div wire:loading id="loadingScreen" class="hidden fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-[60] overflow-hidden bg-gray-900 opacity-75 opa flex flex-col items-center justify-center">
            <div role="status">
                <svg aria-hidden="true" class="inline w-10 h-10 mr-2 text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
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
        <div id="notifDiv" class="absolute top-16 left-1/2 -translate-x-1/2 z-50 w-[400px]">
        </div>
    {{-- NOTIF END --}}

    {{-- REMOVE MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="removeModal" data-modal-toggle="removeModal" id="openRemoveModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="removeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
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
                        <p id="removeMenuName" class="text-base leading-relaxed text-gray-500 font-semibold"></p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptRemoveButton" data-modal-hide="removeModal" data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Remove</button>
                        <button data-modal-hide="removeModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- REMOVE MODAL END --}}


    {{-- Menu Message MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="menuMessageModal" data-modal-toggle="menuMessageModal" id="openMenuMessageModal" class="hidden" type="button"></button>
        <!-- Main modal -->
        <div id="menuMessageModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-circle text-blue-500 text-3xl mr-3"></i>Remarks
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="menuMessageModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-base leading-relaxed text-gray-500">
                            <input type="text" id="msgtxt" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </p>
                        <p id="removeMenuName" class="text-base leading-relaxed text-gray-500 font-semibold"></p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptMenuRemarks" data-modal-hide="menuMessageModal" data-slug="" type="button" class="text-white bg-emerald-700 hover:bg-emerald-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Save</button>
                        <button data-modal-hide="menuMessageModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- Menu Message MODAL END --}}


    {{-- REMOVE LIME DISCOUNT MODAL --}}
        <!-- Modal toggle -->
        <!-- <button data-modal-target="removeLimeDiscountModal" data-modal-toggle="removeModal" id="openRemoveModal" class="hidden" type="button"></button> -->
    
        <!-- Main modal -->
        <div id="removeLimeDiscountModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
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
                        <p id="removeMenuName" class="text-base leading-relaxed text-gray-500 font-semibold"></p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="acceptRemoveLimeDiscountButton" data-modal-hide="removeModal" data-slug="" type="button" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Remove</button>
                        <button data-modal-hide="removeLimeDiscountModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- REMOVE LIME DISCOUNT MODAL END --}}

    {{-- MOP MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="MOPModal" data-modal-toggle="MOPModal" id="openMOPModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="MOPModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4  overflow-x-auto overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full " >
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
                            <div>
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
                                </div>
                                <div class="mb-3 flex justify-between w-full">
                                    <span class="text-1xl">Amount : ₱ <span id="actualAmount">{{ number_format($total, 2, '.', ',') }}</span></span>
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
                        <p id="amountError" class="hidden text-2xl text-red-500 italic">Invalid amount. Please enter an amount more than or equal to <span class=" font-medium ml-1"> ₱ </span><span id="actualAmount2" class=" font-bold text-2xl">{{ number_format($total, 2, '.', ',') }}</span></p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-2 space-x-6 border-t border-gray-300 rounded-b">
                        <button  id="payNowButton" data-amount="{{ $total }}" type="button" class="text-white bg-emerald-600 hover:bg-emerald-500 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-2xl font-medium px-5 py-5 hover:text-white focus:z-10 w-full">Submit</button>
                        <button data-modal-hide="MOPModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-2xl font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-full">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- MOP MODAL END --}}

    {{-- DISCOUNT MODAL --}}
        <!-- Main modal -->
        <div id="discountModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-3xl max-h-full">
                <!-- Modal content -->
                <form method="POST" action="{{ route('pos.updateDiscount') }}" class="relative bg-white rounded-lg shadow">
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
                            <label for="customer_with_discount" class="block mb-2 text-sm font-medium text-gray-900">Number Customer With Discount</label>
                            <input type="number" id="customer_with_discount" name="customer_with_discount" value="{{ ($discountRow->customer_with_discount) == 0 ? '' : $discountRow->customer_with_discount }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div>
                        <div class="mb-6">
                            <input type="hidden" id="total_customer" name="total_customer" value="{{ ($discountRow->total_customer) == 0 ? '' : $discountRow->total_customer }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button id="submitDiscountButton" data-modal-hide="discountModal" type="submit" class="text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none rounded-lg border border-blue-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">SUBMIT</button>
                        <button data-modal-hide="discountModal" type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none rounded-lg border border-gray-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">CANCEL</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- DISCOUNT MODAL END --}}


    {{-- Service Charge MODAL --}}
        <!-- Main modal -->
        <div id="ServiceChargeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-3xl max-h-full">
                <!-- Modal content -->
                <form method="POST" action="{{ route('pos.updateServiceCharge') }}" class="relative bg-white rounded-lg shadow">
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
                        <!-- <div class="mb-6">
                            <label for="total_customer" class="block mb-2 text-sm font-medium text-gray-900">Total Number of Customers</label>
                            <input type="text" id="total_customer" name="total_customer" value="{{ ($discountRow->total_customer) == 0 ? '' : $discountRow->total_customer }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div> -->
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button id="submitDiscountButton" data-modal-hide="ServiceChargeModal" type="submit" class="text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none rounded-lg border border-blue-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">SUBMIT</button>
                        <button data-modal-hide="ServiceChargeModal" type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none rounded-lg border border-gray-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">CANCEL</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- Service Charge MODAL END --}}


    {{-- Lime Discount MODAL --}}
        <!-- Main modal -->
        <div id="LimeDiscountModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-3xl max-h-full">
                <!-- Modal content -->
                <form method="POST" action="{{ route('pos.updateLimeDiscount') }}" class="relative bg-white rounded-lg shadow">
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
                            <input type="text" id="lime_discount" name="lime_discount" value="{{ $limeDiscountRow->percent }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div>
                        <!-- <div class="mb-6">
                            <label for="total_customer" class="block mb-2 text-sm font-medium text-gray-900">Total Number of Customers</label>
                            <input type="text" id="total_customer" name="total_customer" value="{{ ($discountRow->total_customer) == 0 ? '' : $discountRow->total_customer }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                        </div> -->
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button id="submitDiscountButton" data-modal-hide="ServiceChargeModal" type="submit" class="text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none rounded-lg border border-blue-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">SUBMIT</button>
                        <button data-modal-hide="LimeDiscountModal" type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:outline-none rounded-lg border border-gray-300 text-lg font-bold px-5 py-5 focus:z-10 w-1/2">CANCEL</button>
                    </div>
                </form>
            </div>
        </div>
    {{-- Lime Discount MODAL END --}}

    {{-- NAME/NUMBER MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="detailsModal" data-modal-toggle="detailsModal" id="openDetailsModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="detailsModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-3xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Details
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="detailsModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        
                        <div class="mb-6">
                        
                            <label for="base-input" class="block mb-2 text-sm font-medium text-gray-900">Name</label>
                            <input type="text" id="payor_name_pl" name="payor_name_pl" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <input type="hidden" id="paymentMethod"  value="0">
                        </div>
                        <div class="mb-6">
                            <label for="base-input" class="block mb-2 text-sm font-medium text-gray-900">Account/Phone Number</label>
                            <input type="text" id="payor_number_pl" name="payor_number_pl" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <!-- <div class="mb-6">
                            <label for="base-input" class="block mb-2 text-sm font-medium text-gray-900">Order Remarks</label>
                            <input type="text" id="order_remarks_pl" name="payor_number_pl" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div> -->
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button id="detailsContButton" data-modal-hide="detailsModal" type="button" class="text-white bg-emerald-500 hover:bg-emerald-600 focus:ring-4 focus:outline-none rounded-lg border border-emerald-300 text-lg font-bold px-5 py-5 focus:z-10 w-full">CONTINUE</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- NAME/NUMBER MODAL END --}}

    {{-- AMOUNT RECEIVED MODAL 
        <!-- Modal toggle -->
        <button data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" id="openAmountReceivedModal" class="hidden" type="button"></button>


        <div id="amountReceivedModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
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
                            <span class="text-base font-medium ml-1"> ₱ </span><span id="actualAmount" class="text-base font-bold">{{ number_format($total, 2, '.', ',') }}</span>
                        </div>
                        <div class="mb-2">
                            <label for="amount" class="block mb-2 text-base font-medium text-gray-900">Amount Received</label>
                            <input type="text" id="amount" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                            <p id="amountError" class="hidden text-sm text-red-500 italic">Invalid amount. Please enter an amount more than or equal to <span class="text-base font-medium ml-1"> ₱ </span><span id="actualAmount" class="text-base font-bold">{{ number_format($total, 2, '.', ',') }}</span></p>
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
                        <button id="payNowButton" data-amount="{{ $total }}" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Mark as Paid</button>
                        <button id="payNowCancelButton" data-modal-hide="amountReceivedModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
     AMOUNT RECEIVED MODAL END --}}

    {{-- Server Password MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-hide="serverPasswordModal" id="closeServerPasswordModal" class="hidden" type="button"></button>

        <button data-modal-target="serverPasswordModal" data-modal-toggle="serverPasswordModal" id="openServerPasswordModal" class="hidden" type="button"></button>

        <div id="serverPasswordModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div  class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-center p-4 border-b rounded-t">
                        
                        <div class="justify-center">
                            <h3 class="text-2xl font-semibold text-gray-900">
                                Enter Secure Code 
                            </h3>
                        </div>
                        
                    </div>
                    <form method="POST" action="{{ route('pos.check') }}" class="relative bg-white rounded-lg shadow">
                        @csrf
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        
                        <div class="w-full h-auto rounded-lg flex flex-col justify-between px-32">
                            <div class="relative mb-2">
                                <input type="password" name="sec_code" id="sec_code" class="text-center block w-full px-4 py-2.5  text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" required autocomplete="off">
                                    <!--<button id="showPassword" type="button" class=" absolute right-2 bottom-1">-->
                                    <!--    <i class="uil uil-eye text-3xl"></i>-->
                                    <!--</button>-->
                                
                                    
                            </div>
                            
                            <p id="secCodeError" class=" text-sm text-red-500 italic">
                            @if (session('message')) 
                                @if(session('message') == 'error')
                                    Security code is incorrect, Please try again!
                                @else
                                    Server ID already Logged In to another Device!
                                @endif
                            @endif
                            </p>
                        </div>
                        <div class="w-full h-auto rounded-lg flex flex-col justify-between px-32">
                            <div class="grid grid-cols-5 gap-4">
                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="6">6</button>
                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="7">7</button>
                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="8">8</button>
                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="9">9</button>
                                <button type="button" class="sec-code-button row-span-2 border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="bs"><i class="uil uil-arrow-left"></i></button>

                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="2">2</button>
                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="3">3</button>
                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="4">4</button>
                                
                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="5">5</button>
                                

                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="1">1</button>
                                
                                
                                

                                <button type="button" class="sec-code-button aspect-square border shadow bg-gray-50 focus:scale-95 text-xl font-bold" data-value="0">0</button>
                                <button type="button" class="sec-code-button aspect-rectangle border shadow bg-gray-50 focus:scale-95 text-xl font-bold col-span-3" data-value="clr">CLEAR</button>
                            </div>
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b px-32">
                        <button id="" type="submit"  class=" text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm py-5 text-center w-full">Submit</button>
                        <!-- submitSecCode  data-modal-hide="serverPasswordModal"-->
                    </div>
                    </form>
                </div>
            </div>
        </div>
    {{-- Server Password MODAL END --}}

    {{-- CHANGE MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="changeModal" data-modal-toggle="changeModal" id="openChangeModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="changeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
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
                        <button data-modal-hide="changeModal" id="closeChangeButton" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-full">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- CHANGE MODAL END --}}

    {{-- PAYLATER CONFIRMATION MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="plConModal" data-modal-toggle="plConModal" id="openPLConModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="plConModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                        {{ (Auth::user()->role != 7) ? 'Pay Later Confirmation' : 'Commit Confirmation'}}
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="plConModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                    {{ (Auth::user()->role != 7) ? 'Confirm Pay Later Transaction' : 'Confirm Commit Transaction'}}
                        
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button data-modal-hide="plConModal" id="plConfirmButton" type="button" class="text-white bg-emerald-500 hover:bg-emerald-600 focus:ring-4 focus:outline-none rounded-lg border border-emerald-300 text-sm font-medium px-5 py-5 w-full">Confirm</button>
                        <button data-modal-hide="plConModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-full">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- PAYLATER CONFIRMATION MODAL END --}}

    {{-- SELECT TABLE MODAL --}}
    <div id="tableModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-3xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-3 border-b rounded-t">
                        <h3 class="text-xl font-semibold text-gray-900">
                            Select Table
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="tableModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6">
                        <div class="mb-4 border-b border-gray-200">
                            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="categoryTabs" data-tabs-toggle="#tableTab" role="tablist">
                                <li class="mr-2" role="presentation">
                                    <button class="inline-block p-4 rounded-t-lg" id="all-tab" data-tabs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="false">All</button>
                                </li>
                                <li class="mr-2" role="presentation">
                                    <button class="inline-block p-4 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="open-tab" data-tabs-target="#open" type="button" role="tab" aria-controls="open" aria-selected="false"><span class="flex items-center text-sm font-medium"><span class="flex w-3 h-3 bg-emerald-500 rounded-full mr-1.5 flex-shrink-0"></span>Open</span></button>
                                </li>
                                <li class="mr-2" role="presentation">
                                    <button class="inline-block p-4 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="occupied-tab" data-tabs-target="#occupied" type="button" role="tab" aria-controls="occupied" aria-selected="false"><span class="flex items-center text-sm font-medium"><span class="flex w-3 h-3 bg-red-500 rounded-full mr-1.5 flex-shrink-0"></span>Occupied</span></button>
                                </li>
                                {{-- <li role="presentation">
                                    <button class="inline-block p-4 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="reserved-tab" data-tabs-target="#reserved" type="button" role="tab" aria-controls="reserved" aria-selected="false"><span class="flex items-center text-sm font-medium"><span class="flex w-3 h-3 bg-orange-500 rounded-full mr-1.5 flex-shrink-0"></span>Reserved</span></button>
                                </li> --}}
                            </ul>
                        </div>
                        <div id="tableTab">
                            <div class="hidden p-4 rounded-lg bg-gray-50 max-h-[calc(100vh-320px)] overflow-y-auto" id="all" role="tabpanel" aria-labelledby="all-tab">
                                <div class="grid grid-cols-2 gap-2 text-center">
                                    @foreach ($tables as $table)
                                        
                                            <div class="{{ ($table->status == 0) ? 'bg-emerald-500' : 'bg-red-500'; }} aspect-square rounded-xl">
                                                <button type="button" data-table="{{ $table->id }}" data-tablename="{{ $table->name }}" class="w-full h-full relative tableButton rounded-xl">
                                                    <p class="absolute top-1/3 -translate-y-1/2 w-full text-center text-2xl font-bold">{{ $table->name }}</p>
                                                    <img src="{{ asset('storage/images/ico/table-noBG.png') }}" alt="" class="w-4/5 absolute bottom-0 left-1/2 -translate-x-1/2">
                                                </button>
                                            </div>
                                        
                                    @endforeach
                                </div>
                            </div>
                            <div class="hidden p-4 rounded-lg bg-gray-50 max-h-[calc(100vh-320px)] overflow-y-auto" id="open" role="tabpanel" aria-labelledby="open-tab">
                                <div class="grid grid-cols-4 gap-4 text-center">
                                    @foreach ($tables as $table)
                                        @if ($table->status == 0)
                                            @if ($table->id == 1)
                                                <div class="bg-emerald-500 aspect-square rounded-xl">
                                                    <button type="button" data-table="{{ $table->id }}" data-tablename="{{ $table->name }}" class="w-full h-full relative tableButton rounded-xl">
                                                        <p class="absolute top-1/3 -translate-y-1/2 w-full text-center text-2xl font-bold">{{ $table->name }}</p>
                                                        <i class="uil uil-shopping-bag text-6xl absolute bottom-8 left-1/2 -translate-x-1/2"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="{{ ($table->status == 0) ? 'bg-emerald-500' : 'bg-red-500'; }} aspect-square rounded-xl">
                                                    <button type="button" data-table="{{ $table->id }}" data-tablename="{{ $table->name }}" class="w-full h-full relative tableButton rounded-xl">
                                                        <p class="absolute top-1/3 -translate-y-1/2 w-full text-center text-2xl font-bold">{{ $table->name }}</p>
                                                        <img src="{{ asset('storage/images/ico/table-noBG.png') }}" alt="" class="w-4/5 absolute bottom-0 left-1/2 -translate-x-1/2">
                                                    </button>
                                                </div>
                                            @endif
                                            {{-- <div class="{{ ($table->status == 0) ? 'bg-emerald-500' : 'bg-red-500'; }} aspect-square rounded-xl">
                                                <button type="button" data-table="{{ $table->id }}" data-tablename="{{ $table->name }}" class="w-full h-full relative tableButton rounded-xl">
                                                    <p class="absolute top-1/3 -translate-y-1/2 w-full text-center text-2xl font-bold">{{ $table->name }}</p>
                                                    <img src="{{ asset('storage/images/ico/table-noBG.png') }}" alt="" class="w-4/5 absolute bottom-0 left-1/2 -translate-x-1/2">
                                                </button>
                                            </div> --}}
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="hidden p-4 rounded-lg bg-gray-50 max-h-[calc(100vh-320px)] overflow-y-auto" id="occupied" role="tabpanel" aria-labelledby="occupied-tab">
                                <div class="grid grid-cols-4 gap-4 text-center">
                                    @foreach ($tables as $table)
                                        @if ($table->status == 1)
                                            @if ($table->id == 1)
                                                <div class="bg-emerald-500 aspect-square rounded-xl">
                                                    <button type="button" data-table="{{ $table->id }}" data-tablename="{{ $table->name }}" class="w-full h-full relative tableButton rounded-xl">
                                                        <p class="absolute top-1/3 -translate-y-1/2 w-full text-center text-2xl font-bold">{{ $table->name }}</p>
                                                        <i class="uil uil-shopping-bag text-6xl absolute bottom-8 left-1/2 -translate-x-1/2"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="{{ ($table->status == 0) ? 'bg-emerald-500' : 'bg-red-500'; }} aspect-square rounded-xl">
                                                    <button type="button" data-table="{{ $table->id }}" data-tablename="{{ $table->name }}" class="w-full h-full relative tableButton rounded-xl">
                                                        <p class="absolute top-1/3 -translate-y-1/2 w-full text-center text-2xl font-bold">{{ $table->name }}</p>
                                                        <img src="{{ asset('storage/images/ico/table-noBG.png') }}" alt="" class="w-4/5 absolute bottom-0 left-1/2 -translate-x-1/2">
                                                    </button>
                                                </div>
                                            @endif
                                            {{-- <div class="{{ ($table->status == 0) ? 'bg-emerald-500' : 'bg-red-500'; }} aspect-square rounded-xl">
                                                <button type="button" data-table="{{ $table->id }}" data-tablename="{{ $table->name }}" class="w-full h-full relative tableButton rounded-xl">
                                                    <p class="absolute top-1/3 -translate-y-1/2 w-full text-center text-2xl font-bold">{{ $table->name }}</p>
                                                    <img src="{{ asset('storage/images/ico/table-noBG.png') }}" alt="" class="w-4/5 absolute bottom-0 left-1/2 -translate-x-1/2">
                                                </button>
                                            </div> --}}
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            {{-- <div class="hidden p-4 rounded-lg bg-gray-50" id="reserved" role="tabpanel" aria-labelledby="reserved-tab">
                                <p class="text-sm text-gray-500">This is some placeholder content the <strong class="font-medium text-gray-800">Contacts tab's associated content</strong>. Clicking another tab will toggle the visibility of this one for the next. The tab JavaScript swaps classes to control the content visibility and styling.</p>
                            </div> --}}
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                        {{-- <button data-modal-hide="tableModal" type="button" class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save</button> --}}
                        <button data-modal-hide="tableModal" type="button" class="closeTableModal text-gray-500 bg-white hover:bg-gray-10 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- SELECT TABLE MODAL END --}}

    <div style="height: calc(100vh - 48px)" class="p-4 w-screen gap-4">

        {{-- LEFT CONTENT --}}
        
            <div class="h-full w-full xl:w-auto xl:col-span-2 bg-white shadow-lg rounded-lg border border-gray-200 overflow-hidden">
                <div>
                    <div class="p-2 flex items-center justify-between">
                        @csrf   
                        <div class="relative w-3/4">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
                            </div>
                            <input type="text" id="searchMenu" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-full focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Search" required autocomplete="off">
                        </div>
                        {{--@if(Auth::user()->role != 2)
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>

                        <div class="flex items-center ">
                            <!-- SERVER NAME -->
                            <div class="flex flex-col ms-3 me-3 border-r-4 border-gray-700">
                                <div class="font-bold flex justify-end pe-3">
                                    SERVER NAME:
                                </div>
                                <div class="flex justify-end pe-3" id="serverNameDiv">
                                    @if (!empty($cashiers->fname))
                                        {{  $cashiers->fname . ' '. $cashiers->lname }}
                                    
                                    @else
                                        None
                                    @endif
                                </div>
                            </div>
                            <!-- SERVER NAME END -->
                        <button id="logoutServerBtn" class=" w-[6vh] h-[6vh] mr-2 content-right bg-red-400 rounded-lg"><i class="text-xl text-white uis uis-lock"></i></button>
                        @endif--}}
                        <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button" class="relative inline-flex items-center p-1.5 text-sm font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" height="30" viewBox="0 -960 960 960" width="30" fill="currentColor"><path d="M220-80q-24 0-42-18t-18-42v-520q0-24 18-42t42-18h110v-10q0-63 43.5-106.5T480-880q63 0 106.5 43.5T630-730v10h110q24 0 42 18t18 42v520q0 24-18 42t-42 18H220Zm0-60h520v-520H630v90q0 12.75-8.675 21.375-8.676 8.625-21.5 8.625-12.825 0-21.325-8.625T570-570v-90H390v90q0 12.75-8.675 21.375-8.676 8.625-21.5 8.625-12.825 0-21.325-8.625T330-570v-90H220v520Zm170-580h180v-10q0-38-26-64t-64-26q-38 0-64 26t-26 64v10ZM220-140v-520 520Z"/></svg>
                            <span class="sr-only">Notifications</span>
                            <div id="ordersCount" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-2 -right-2">{{ $orders->count() }}</div>
                        </button>
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
                                <div id="menuTabs" data-tabs-toggle="#categoryTabContent" role="tablist" class="overflow-x-auto flex gap-x-2">
                                    <button id="all-menu-tab" data-tabs-target="#all-menu" type="button" role="tab" aria-controls="all-menu" aria-selected="false" class="leading-8 px-4 font-semibold border rounded-lg whitespace-nowrap">
                                        All
                                    </button>
                                    @foreach ($categories as $category)
                                        <button id="{{$category->slug}}-tab" data-tabs-target="#{{$category->slug}}-menu" type="button" role="tab" aria-controls="{{$category->slug}}-menu" aria-selected="false" class="leading-8 px-4 font-semibold border rounded-lg whitespace-nowrap">
                                            {{$category->name}}
                                        </button>

                                        {{-- <button id="{{$category->name}}-tab" data-tabs-target="#{{$category->slug}}" type="button" role="tab" aria-controls="{{$category->slug}}" aria-selected="false" class="leading-8 px-4 font-semibold border rounded-lg whitespace-nowrap">
                                            {{$category->name}}
                                        </button> --}}
                                    @endforeach
                                </div>
                            </div>
                            <div class="p-px">
                                <button class="w-9 aspect-square border border-neutral-300 rounded-lg scroll-right">
                                    <i class="uil uil-arrow-right text-2xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="categoryAllContents" class="h-[calc(100vh-194px)] overflow-y-auto">
                        <div id="categoryTabContent" class="h-auto min-h-full bg-gray-100">
                            <div class="p-8" id="all-menu" role="tabpanel" aria-labelledby="all-menu-tab">
                                <div class="w-[656px] xl:w-[880px] 2xl:w-[1104px] mx-auto grid grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-8 justify-items-center content-center">
                                    @foreach ($menus as $menu)
                                        @if ($menu->is_hidden != 1)
                                            <a data-slug="{{$menu->slug}}" class="w-52 h-80 p-3 bg-white border {{ ($menu->current_quantity > $menu->reorder_point) ? 'border-neutral-200' : 'border-red-500 shadow-red-300' }}  rounded-xl shadow-lg cursor-pointer hover:scale-105 transition-all menu">
                                                <div class="w-full aspect-square overflow-hidden">
                                                    <img src="{{ asset('storage/'.$menu->image) }}" alt="" class="rounded-xl h-full w-auto mx-auto">
                                                </div>
                                                <div class="h-[calc(100%-184px)] text-center">
                                                    <div class="h-2/3 border-b border-neutral-400 flex items-center">
                                                        <p class="text-lg font-medium">
                                                            {{$menu->name}}
                                                        </p>
                                                    </div>
                                                    <div class="h-1/3 flex justify-center items-center">
                                                        <p class="text-xl font-bold">
                                                            ₱ {{number_format($menu->price, 2, '.', ',')}}
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @foreach ($categories as $category)
                                <div class="p-8" id="{{$category->slug}}-menu" role="tabpanel" aria-labelledby="{{$category->slug}}-menu-tab">
                                    <div class="w-[656px] xl:w-[880px] 2xl:w-[1104px] mx-auto grid grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-8 justify-items-center content-center">
                                        @foreach ($menus as $menu)
                                            @if ($menu->category_id == $category->id)
                                                <div data-slug="{{$menu->slug}}" class="w-52 h-80 p-3 bg-white border {{ ($menu->current_quantity > $menu->reorder_point) ? 'border-neutral-200' : 'border-red-500 shadow-red-300' }} rounded-xl shadow-lg cursor-pointer hover:scale-105 transition-all menu">
                                                    <div class="w-full aspect-square overflow-hidden">
                                                        <img src="{{ asset('storage/'.$menu->image) }}" alt="" class="rounded-xl h-full w-auto mx-auto">
                                                    </div>
                                                    <div class="h-[calc(100%-184px)] text-center">
                                                        <div class="h-2/3 border-b border-neutral-400 flex items-center">
                                                            <p class="text-lg font-medium">
                                                                {{$menu->name}}
                                                            </p>
                                                        </div>
                                                        <div class="h-1/3 flex justify-center items-center">
                                                            <p class="text-xl font-bold">
                                                                ₱ {{number_format($menu->price, 2, '.', ',')}}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
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

        {{-- RIGHT CONTENT --}}
            <aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-full h-screen transition-transform translate-x-full" aria-label="Sidebar">
                <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50 dark:bg-gray-800">
                    <div class="h-[89vh] w-full xl:w-auto bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="border border-neutral-300 rounded-lg mt-2 ml-2 mr-2">
                            <div class="flex justify-between p-2 items-center">
                                <input type="hidden" id="table" name="table">
                                <h1 id="tableName" class="text-2xl font-black tracking-wide pl-2">
                                    - - -
                                </h1>
                                <button type="button" id="openTableModal" data-modal-target="tableModal" data-modal-show="tableModal" class="p-1 rounded-lg shadow border border-gray-100">
                                    <img src="{{ asset('storage/images/ico/table2.png') }}" alt="" class="w-9 rounded-full">
                                </button>
                            </div>
                        </div>
                        <div class=" px-4">
                            <span class="col-span-8 text-xs flex">
                                @if(Auth::user()->role != 7)
                                <span class="text-xl mr-3"><i class="uil uil-gift"></i></span>
                                
                                <span class="text-xl"><i class="uil uil-percentage"></i></span>
                                @endif
                            </span>   
                        </div>
                        <div >
                            <div class="flex flex-col">
                                <div id="ordersDiv" class="h-[calc(96vh-420px)] overflow-y-auto ">
                                    @foreach ($orders as $order)
                                    
                                        <div class="grid grid-cols-12 content-center h-14 w-full text-center px-4">
                                            
                                                @if(Auth::user()->role != 7)
                                                    <div class="col-span-1 flex items-center "></div>
                                                        <input type="checkbox" data-slug="{{ $order->slug }}" class="complimentary mr-4" id="{{ $order->slug }}" {{  ($order->type == 1) ? 'checked' : '' }}>
                                                
                                                        <input type="checkbox" data-slug="{{ $order->slug }}" class="orderSelection mr-4" id="orderSelection{{ $order->slug }}" {{ ($order->is_pwd == 1) ? 'checked' : '' }}>
                                                    </div>
                                                @endif
                                            
                                            <div class="col-span-1 flex items-center ">
                                            <button  type="button" data-slug="{{ $order->slug }}" class="myMsg aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-comment-alt-dots text-xl"></i></button>
                                            </div>
                                            <div class="col-span-3 text-xs font-semibold text-left flex items-center mr-1 ml-1">
                                                {{ $order->name }}
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="{{ $order->slug }}" class="descQty aspect-square w-full max-w-[50px] bg-red-200 rounded-lg"><i class="uil uil-minus text-xl text-red-900"></i></button>
                                            </div>
                                            <div class="col-span-1 flex items-center justify-center px-1">
                                                {{-- <p class="w-full text-center text-sm font-semibold border-0 h-7 leading-7">{{ $order->quantity }}</p> --}}
                                                <span class="w-full text-center text-sm">{{ $order->quantity }}</span>
                                                
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="{{ $order->slug }}" class="incQty aspect-square w-full max-w-[50px] bg-emerald-200 rounded-lg"><i class="uil uil-plus text-xl text-emerald-900"></i></button>
                                            </div>
                                            <div class="col-span-3 flex items-center text-sm font-semibold justify-center">
                                                {{ number_format($order->total_price, 2, '.', ',') }}
                                            </div>
                                            <div class="flex items-center justify-center">
                                                <button data-slug="{{ $order->slug }}" data-name="{{ $order->name }}" class="removeButton aspect-square w-full max-w-[50px] bg-red-600 rounded-lg"><i class="uil uil-times text-xl text-red-200"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <div class="p-4">
                                        <div class="pt-2 grid w-full bg-white overflow-y-auto">
                                            <div class="row-span-2">
                                                <!-- <div class="grid grid-cols-2">
                                                    <div class="justify-self-start">
                                                        <strong class="text-slate-600 font-bold "># of Pax:</strong>
                                                    </div>
                                                    <div class="justify-self-end">
                                                        <input type="text" id="numPacks" class="max-w-[50px] bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"/>
                                                    </div>
                                                    
                                                     -->
                                                </div>
                                                @if(Auth::user()->role != 7)
                                                <div class="grid grid-cols-2">
                                                    <div class="justify-self-start ">
                                                        <strong class="text-slate-600 text-xl font-medium ">Subtotal</strong>
                                                    </div>
                                                    <div class="justify-self-end ">
                                                        <strong class="text-slate-600 text-xl font-medium "><span class="text-2xl">₱ </span><span id="subTotal">{{ number_format($subTotal, 2, '.', ',') }}</span></strong>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2">
                                                    <div class="justify-self-start flex gap-x-2 items-center">
                                                        <strong class="text-slate-600 text-base font-medium w-full">PWD/SrCitizen</strong>
                                                        <button data-modal-target="discountModal" data-modal-toggle="discountModal" type="button" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                                                        <a href="{{ route('pos.deleteDiscount') }}" id="deleteDiscountButton" class="text-red-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M261-120q-24.75 0-42.375-17.625T201-180v-570h-41v-60h188v-30h264v30h188v60h-41v570q0 24-18 42t-42 18H261Zm438-630H261v570h438v-570ZM367-266h60v-399h-60v399Zm166 0h60v-399h-60v399ZM261-750v570-570Z"/></svg></span></a>
                                                        
                                                    </div>
                                                    <div class="justify-self-end ">
                                                        <strong class="text-slate-600 text-base font-medium "><span class="text-xl">₱ </span><span id="discountTotal">{{ number_format($discount, 2, '.', ',') }}</span></strong>
                                                        <span id="discountTotalValue" class="hidden">{{ $discount }}</span> 
                                                    </div>
                                                </div>
                                                
                                                <div class="grid grid-cols-2">
                                                    <div class="justify-self-start flex gap-x-2 items-center">
                                                        <strong class="text-slate-600 text-base font-medium w-full">Discount</strong>
                                                        <button data-modal-target="LimeDiscountModal" data-modal-toggle="LimeDiscountModal" type="button" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                                                        <!-- <button data-modal-target="removeLimeDiscountModal" data-modal-toggle="removeLimeDiscountModal" type="button" id="deleteLimeDiscountButton" class="text-red-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M261-120q-24.75 0-42.375-17.625T201-180v-570h-41v-60h188v-30h264v30h188v60h-41v570q0 24-18 42t-42 18H261Zm438-630H261v570h438v-570ZM367-266h60v-399h-60v399Zm166 0h60v-399h-60v399ZM261-750v570-570Z"/></svg></span></button> -->
                                                        
                                                    </div>
                                                    <div class="justify-self-end ">
                                                        <strong class="text-slate-600 text-base font-medium ">
                                                            <span class="text-xl">% </span><span id="limepercent">{{ $limePercent }}</span>
                                                            <span class="text-xl">₱ </span><span id="limediscountTotal">{{ number_format($limeVal, 2, '.', ',') }}</span>
                                                            <span id="limediscountTotalValue" class="hidden">{{ $limeVal }}</span>
                                                        </strong>
                                                    </div>
                                                </div>
                                                
                                                <!-- service charge -->
                                                <div class="grid grid-cols-2">
                                                    <div class="justify-self-start flex gap-x-2 items-center">
                                                        <strong class="text-slate-600 text-base font-medium w-full">Service Charge</strong>
                                                        <button data-modal-target="ServiceChargeModal" data-modal-toggle="ServiceChargeModal" type="button" class="text-blue-600"><span><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M180-180h44l443-443-44-44-443 443v44Zm614-486L666-794l42-42q17-17 42-17t42 17l44 44q17 17 17 42t-17 42l-42 42Zm-42 42L248-120H120v-128l504-504 128 128Zm-107-21-22-22 44 44-22-22Z"/></svg></span></button>
                                                       
                                                        
                                                    </div>
                                                    <div class="justify-self-end ">
                                                         <strong class="text-slate-600 text-base font-medium ">
                                                            
                                                            <span class="text-xl">% </span><span id="scpercent">{{ $SCPercent }}</span>
                                                            <span class="text-xl">₱ </span><span id="sctotal">{{ number_format($SCVal, 2, '.', ',') }}</span>
                                                            <input type="hidden" name="sc_val" id="sc_val" value="{{ $SCVal }}">
                                                        </strong> 
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2">
                                                    <div class="justify-self-start">
                                                        <strong class="text-slate-600 text-2xl font-medium ">TOTAL</strong>
                                                    </div>
                                                    
                                                    <div class="justify-self-end">
                                                        <strong class="text-slate-600 text-2xl font-medium"><span class="text-3xl">₱ </span><span id="total">{{ number_format($total, 2, '.', ',') }}</span></strong>
                                                    </div>
                                                </div>
                                                @else
                                                <div class="grid grid-cols-2">
                                                    <div class="justify-self-start ">
                                                        <strong class="text-slate-600 text-xl font-medium ">Subtotal</strong>
                                                    </div>
                                                    <div class="justify-self-end ">
                                                        <strong class="text-slate-600 text-xl font-medium "><span class="text-2xl">₱ </span><span id="subTotal">{{ number_format($subTotal, 2, '.', ',') }}</span></strong>
                                                    </div>
                                                </div>
                                                <!-- service charge -->
                                                <div class="grid grid-cols-2">
                                                    <div class="justify-self-start flex gap-x-2 items-center">
                                                        <strong class="text-slate-600 text-base font-medium w-full">Service Charge</strong>
                                                        
                                                       
                                                        
                                                    </div>
                                                    <div class="justify-self-end ">
                                                         <strong class="text-slate-600 text-base font-medium ">
                                                            
                                                            <span class="text-xl">% </span><span id="scpercent">{{ $SCPercent }}</span>
                                                            <span class="text-xl">₱ </span><span id="sctotal">{{ number_format($SCVal, 2, '.', ',') }}</span>
                                                            <input type="hidden" name="sc_val" id="sc_val" value="{{ $SCVal }}">
                                                        </strong> 
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2">
                                                    <div class="justify-self-start">
                                                        <strong class="text-slate-600 text-2xl font-medium ">TOTAL</strong>
                                                    </div>
                                                    
                                                    <div class="justify-self-end">
                                                        <strong class="text-slate-600 text-2xl font-medium"><span class="text-3xl">₱ </span><span id="total">{{ number_format($total, 2, '.', ',') }}</span></strong>
                                                    </div>
                                                </div>

                                                @endif
                                                <!-- sP -->
                                                 
                                                
                                            </div>
                                            <span id="subTotal" class="hidden">0</span>
                                            <span id="discountTotal" class="hidden">0</span>
                                            <span id="discountTotalValue" class="hidden">0</span>
                                            <span id="limepercent" class="hidden">0</span>
                                            <span id="limediscountTotal" class="hidden">0</span>
                                            <span id="limediscountTotalValue" class="hidden">0</span>
                                            <span id="scpercent" class="hidden">{{ $SCPercent }}</span>
                                            <span id="sctotal" class="hidden">0</span>
                                            <input type="hidden" name="sc_val" id="sc_val" value="{{ $SCVal }}">
                                            <input type="hidden" id="LessVatDeduction" value="{{ $LessVatDeduction }}">
                                            <input type="hidden" id="VatExemptSales" value="{{ $VatExemptSales }}">
                                            <input type="hidden" id="vatsales" value="{{ $vatsales }}">
                                            <input type="hidden" id="vat" value="{{ $vat }}">
                                            
                                            
                                            
                                            <!--less vat deduction<span id="LessVatDeduction">{{ $LessVatDeduction }}</span>-->
                                            <!--vat exempt <span id="VatExemptSales">{{ $VatExemptSales }}</span>-->
                                            <!--vat sales <span id="vatsales">{{ $vatsales }}</span>-->
                                            <!--vat <span id="vat">{{ $vat }}</span>-->
                                            <div class="p-auto row-span-1 flex items-center mb-2">
                                                @if(Auth::user()->role != 7)
                                                    <button disabled id="payButton" type="button" class="disabled:opacity-50 disabled:pointer-events-none m-2 w-full text-gray-50 bg-gradient-to-r from-green-600 to-teal-500 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center">Pay</button>
                                                    <button disabled id="payLaterButton" type="button" class="disabled:opacity-50 disabled:pointer-events-none w-full m-auto text-gray-50 bg-gradient-to-r from-purple-600 to-blue-500 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center">Pay later</button>
                                                @else
                                                    <button disabled id="payLaterButton" type="button" class="disabled:opacity-50 disabled:pointer-events-none w-full m-auto text-gray-50 bg-gradient-to-r from-emerald-900 to-emerald-700 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center">Commit</button >
                                                   
                                                @endif
                                                
                                            </div>
                                            <div class="p-auto row-span-1 flex items-center ">
                                                @if(Auth::user()->role != 7)
                                                    
                                                @else
                                                    <button id="" type="button" class="disabled:opacity-50 disabled:pointer-events-none w-full m-auto text-gray-50 bg-gradient-to-r from-red-700 to-red-400 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center" data-drawer-hide="default-sidebar">Close</button>
                                                @endif
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  
            </aside>
        {{-- RIGHT CONTENT --}}

    </div>
    <input type="hidden" id="auditVal" value=" {{ $audited }} ">
    <input type="hidden" id="notAuditVal" value=" {{ $nonAudited }} ">
                   
    <script>
        

        $(document).ready(function() {
            $('#serveAmount').text($('#auditVal').val());
            $('#notServeAmount').text($('#notAuditVal').val());
            $('.tableButton').click(function(){
                var id = $(this).data('table');
                var name = $(this).data('tablename');
                $('#table').val(id);
                $('#tableName').html(name);
                $('.tableButton').removeClass('ring-4 ring-inset ring-blue-500');
                $(this).addClass('ring-4 ring-inset ring-blue-500');

                if(id == '1'){
                    $('#payLaterButton').prop('disabled', false);
                    $('#payButton').prop('disabled', false);
                }else{
                    $('#payButton').prop('disabled', false);
                    $('#payLaterButton').prop('disabled', false);
                }

                $('.closeTableModal').click();
            })

            jQuery(document).on("blur","#orderqty", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var qty = $('#orderqty').val();
                //alert(qty);
                $.ajax({
                    url:"{{ route('pos.addqty') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        qty: qty
                    },
                    success:function(result){
                         $('#ordersCount').html(result.ordersCount);
                        //$('#ordersCount').html(result.orders.length);
                        $('#ordersDiv').html(result.orders);
                        $('#subTotal').html(result.subTotal);
                        $('#discountTotal').html(result.discount);
                        $('#limediscountTotal').html(result.limediscount);
                        $('#total').html(result.total);
                        $('#actualAmount').html(result.amount);
                        $('#actualAmount2').html(result.amount);
                        $('#sc_val').val(result.ServiceCharge);
                        $('#sctotal').html(result.ServiceCharge);
                        $('#payNowButton').data('amount', result.amount);
                        $('#notifDiv').html(result.thisNotif); 

                        $('#LessVatDeduction').val(result.LessVatDeduction); 
                        $('#VatExemptSales').val(result.VatExemptSales); 
                        $('#vatsales').val(result.vatsales); 
                        $('#vat').val(result.vat);
                        
                         $('#limediscountTotalValue').html(result.limediscount);
                         $('#discountTotalValue').html(result.discount);
                         
                    } 
                })
            });

            $('.scroll-left').click(function() {
                var divW = (($('#menuTabs').width() * 2) / 3);
                $('#menuTabs').animate({scrollLeft: '-=' + divW + 'px'}, 'slow');
            });
            
            $('.scroll-right').click(function() {
                var divW = (($('#menuTabs').width() * 2) / 3);
                $('#menuTabs').animate({scrollLeft: '+=' + divW + 'px'}, 'slow');
            });

            jQuery(document).on("click", ".menu", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var id = $('#table').val();

                var cashierId = $('#cashierId').val();
                var userRole = $('#userRole').val();

                $('#numPacks').val('');
                $.ajax({
                        url:"{{ route('pos.add') }}",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            slug: slug,
                            cashierId: cashierId,
                            userRole: userRole,
                            _token: _token
                        },
                        success:function(result){
                            $('#ordersCount').html(result.ordersCount);
                            $('#ordersDiv').html(result.orders);
                            $('#subTotal').html(result.subTotal);
                            $('#discountTotal').html(result.discount);
                            $('#total').html(result.total);
                            $('#actualAmount').html(result.amount);
                            $('#actualAmount2').html(result.amount);
                            $('#sc_val').val(result.ServiceCharge);
                            $('#sctotal').html(result.ServiceCharge);
                            $('#payNowButton').data('amount', result.amount);
                            $('#notifDiv').html(result.thisNotif);
                            $('#scpercent').html(result.scPercent);
                            $('#limepercent').html(result.limeDiscountPercent);
                            $('#limediscountTotal').html(result.limeDiscountVal);

                            $('#LessVatDeduction').val(result.LessVatDeduction); 
                            $('#VatExemptSales').val(result.VatExemptSales); 
                            $('#vatsales').val(result.vatsales); 
                            $('#vat').val(result.vat); 
                            
                            $('#limediscountTotalValue').html(result.limediscount);
                            $('#discountTotalValue').html(result.discount);
                            $('#searchMenu').val('');
                        }
                            
                    })
                
                

            });

            jQuery(document).on("click", ".incQty", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('pos.inc') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token
                    },
                    success:function(result){
                        $('#ordersCount').html(result.ordersCount);
                        //$('#ordersCount').html(result.orders.length);
                        $('#ordersDiv').html(result.orders);
                        $('#subTotal').html(result.subTotal);
                        $('#discountTotal').html(result.discount);
                        $('#total').html(result.total);
                        $('#actualAmount').html(result.amount);
                        $('#actualAmount2').html(result.amount);
                        $('#sc_val').val(result.ServiceCharge);
                        $('#sctotal').html(result.ServiceCharge);
                        $('#payNowButton').data('amount', result.amount);
                        $('#notifDiv').html(result.thisNotif);
                        $('#limepercent').html(result.limeDiscountPercent);
                        $('#limediscountTotal').html(result.limeDiscountVal);

                        $('#LessVatDeduction').val(result.LessVatDeduction); 
                        $('#VatExemptSales').val(result.VatExemptSales); 
                        $('#vatsales').val(result.vatsales); 
                        $('#vat').val(result.vat); 
                        
                        $('#limediscountTotalValue').html(result.limediscount);
                         $('#discountTotalValue').html(result.discount);
                    }
                })
            });

            jQuery(document).on("click", ".descQty", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('pos.desc') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token
                    },
                    success:function(result){
                        $('#ordersCount').html(result.ordersCount);
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
                         $('#discountTotalValue').html(result.discount);
                    }
                })
            });

            jQuery(document).on("click", ".complimentary", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var checkbox = $('#'+slug).is(":checked")
                /* if ($('.complimentary').is(":checked")){
                    $('.complimentary').prop('checked', false);
                } else {
                    $('.complimentary').prop('checked', true);
                } */
                // alert(checkbox + ' sl: '+ slug);
                $.ajax({
                    url:"{{ route('pos.comp') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        checkbox: checkbox
                    },
                    success:function(result){
                        $('#ordersCount').html(result.ordersCount);
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
                         $('#discountTotalValue').html(result.discount);
                    }
                })
            });

            jQuery(document).on("click", "#notifDiv button", function(){
                $('#notifDiv').html('');
            });

            jQuery(document).on("click", ".removeButton", function(){
                var slug = $(this).data('slug');
                var name = $(this).data('name');
                $('#removeMenuName').html(name);
                $('#acceptRemoveButton').data('slug', slug);
                $('#openRemoveModal').click();
            });

            jQuery(document).on("click", "#acceptRemoveButton", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('pos.remove') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token
                    },
                    success:function(result){
                        $('#ordersCount').html(result.ordersCount);
                        //$('#ordersCount').html(result.orders.length);
                        $('#ordersDiv').html(result.orders);
                        $('#subTotal').html(result.subTotal);
                        $('#discountTotal').html(result.discount);
                        $('#total').html(result.total);
                        $('#actualAmount').html(result.amount);
                        $('#actualAmount2').html(result.amount);
                        $('#payNowButton').data('amount', result.amount);

                        $('#LessVatDeduction').val(result.LessVatDeduction); 
                        $('#VatExemptSales').val(result.VatExemptSales); 
                        $('#vatsales').val(result.vatsales); 
                        $('#vat').val(result.vat); 

                        $('#limediscountTotalValue').html(result.limediscount);
                         $('#discountTotalValue').html(result.discount);
                        location.reload();
                    }
                })
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
                    } else {
                        mop = 'cashMop';
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

            $('.sec-code-button').on('click', function() {
                const $sec = $('#sec_code');
                const buttonValue = $(this).data('value');

                if (buttonValue == 'bs') {
                    $sec.val($sec.val().slice(0, -1));
                }else if(buttonValue == 'clr'){
                    $sec.val('');
                }else if(buttonValue !== undefined){
                    $sec.val($sec.val() + buttonValue);

                    var inputValue = $('#sec_code').val();

                    
                }
            });

            $('#amount').on('keyup', function() {
                var inputValue = $(this).val();
                $(this).val(inputValue.replace(/[^0-9\.]/g, ''));
                
                var dotIndex = inputValue.indexOf('.');
                if (dotIndex !== -1 && inputValue.indexOf('.', dotIndex + 1) !== -1) {
                $(this).val(inputValue.substr(0, dotIndex + 1) + inputValue.substr(dotIndex + 1).replace(/\./g, ''));
                }
            });

            $('#payButton').click(function(){
                var table = $('#table').val();
                var numPacks = $('#numPacks').val();
                if (numPacks == ''){
                    alert('Please input Pax Number!')
                } else {
                    if(table != ''){
                        $('#amountError').addClass('hidden');
                        $('#amount').val('');

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
                    }else{
                        $('#openTableModal').click();
                    }
                }
            });

            $('#payLaterButton').click(function(){
                var table = $('#table').val();
                
                if(table != '' && table != '0'){
                    $('#paymentMethod').val('0');
                    $('#openDetailsModal').click();
                }else{
                    $('#openTableModal').click();
                }
                
                
            });

            $('#payNowButton').click(function(){
                var amount = $(this).data('amount');
                var amountInput = $('#totalPayment').text();
                var sc_val = $('#sc_val').val();
                var table = $('#table').val();
                var mop = $('#mop').val();
                var payor_name = $('#payor_name').val();
                var payor_number = $('#payor_number').val();
                var order_remarks = $('#order_remarks').val();
                var _token = $('input[name="_token"]').val();

                var limepercent = $('#limepercent').text();
                var limediscountTotal = $('#limediscountTotalValue').text();
                var scpercent = $('#scpercent').text();
                var sctotal = $('#sctotal').text();
                var discountTotal = $('#discountTotalValue').text();
                

                var LessVatDeduction = $('#LessVatDeduction').val();
                var VatExemptSales = $('#VatExemptSales').val();
                var vatsales = $('#vatsales').val();
                var vat = $('#vat').val();
                var numPacks = $('#numPacks').val();

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
                
                //alert(scpercent+' s '+ sctotal)
                if(amountInput < amount){
                    $('#amountError').removeClass('hidden');
                }else{
                    $('#loadingScreen').removeClass('hidden');
                    var change = amountInput - amount;
                    $('#payNowCancelButton').click();
                    $('#openChangeModal').click();
                    $('#changeP').html(change);

                    $.ajax({
                        url:"{{ route('pos.pay') }}",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            amount: amount,
                            table: table,
                            amountInput: amountInput,
                            sc_val:sc_val,
                            mop: mop,
                            payor_name: payor_name,
                            payor_number: payor_number,
                            order_remarks : order_remarks,
                            limepercent : limepercent,
                            limediscountTotal : limediscountTotal,
                            scpercent : scpercent,
                            sctotal : sctotal,
                            discountTotal : discountTotal,
                            LessVatDeduction : LessVatDeduction,
                            VatExemptSales : VatExemptSales,
                            vatsales : vatsales,
                            numPacks : numPacks,
                            vat : vat,
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
                        $('#ordersCount').html(result.ordersCount);
                        //$('#ordersCount').html(result.orders.length);
                            $('#ordersDiv').html(result.orders);
                            $('#subTotal').html(result.subTotal);
                        $('#discountTotal').html(result.discount);
                            $('#total').html(result.total);
                            $('#actualAmount').html(result.amount);
                        $('#actualAmount2').html(result.amount);
                            $('#payNowButton').data('amount', result.amount);
                            $('#loadingScreen').addClass('hidden');
                        }
                    })
                }

            });

            

            $('#detailsContButton').click(function(){
                var pm = $('#paymentMethod').val();

                if(pm == 1){
                    $('#openAmountReceivedModal').click();
                }else{
                    $('#openPLConModal').click();
                }
            });

            $('#plConfirmButton').click(function(){
                var amount = $('#payNowButton').data('amount');
                var table = $('#table').val();
                var sc_val = $('#sc_val').val();
                var payor_name = $('#payor_name_pl').val();
                var payor_number = $('#payor_number_pl').val();
                var order_remarks = ''; //$('#order_remarks_pl').val();
                var _token = $('input[name="_token"]').val();

                var limepercent = $('#limepercent').text();
                var limediscountTotal = $('#limediscountTotalValue').text();
                var scpercent = $('#scpercent').text();
                var sctotal = $('#sctotal').text();
                var discountTotal = $('#discountTotalValue').text();

                var LessVatDeduction = $('#LessVatDeduction').val();
                var VatExemptSales = $('#VatExemptSales').val();
                var vatsales = $('#vatsales').val();
                var vat = $('#vat').val();
                var numPacks = ''; //$('#numPacks').val();

                var cashierId = $('#cashierId').val();

                $('#loadingScreen').removeClass('hidden');

                $.ajax({
                    url:"{{ route('pos.commit') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        amount: amount,
                        table: table,
                        sc_val:sc_val,
                        payor_name: payor_name,
                        payor_number: payor_number,
                        order_remarks : order_remarks,
                        limepercent : limepercent,
                        limediscountTotal : limediscountTotal,
                        scpercent : scpercent,
                        sctotal : sctotal,
                        discountTotal : discountTotal,
                        LessVatDeduction : LessVatDeduction,
                        VatExemptSales : VatExemptSales,
                        vatsales : vatsales,
                        numPacks : numPacks,
                        vat : vat,
                        cashierId: cashierId,
                        _token: _token
                    },
                    success:function(result){
                        //alert(result);
                        $('#ordersCount').html(result.ordersCount);
                        //$('#ordersCount').html(result.orders.length);
                        $('#ordersDiv').html(result.orders);
                        $('#subTotal').html(result.subTotal);
                        $('#discountTotal').html(result.discount);
                        $('#total').html(result.total);
                        $('#actualAmount').html(result.amount);
                        $('#actualAmount2').html(result.amount);
                        $('#payNowButton').data('amount', result.amount);
                        $('#loadingScreen').addClass('hidden');
                        $('#cashierId').val('');
                        location.reload();
                    }
                })
            });

            $('#closeChangeButton').click(function(){
                var table = $('#table').val();
                var url = `{{ url('/pos/print/${table}') }}`;
                window.open(url, '_blank');
                window.location.reload()
            });

            $('#searchMenu').keyup(function(){
                $('#all-menu-tab').click();
                var value = $(this).val().toLowerCase();
                $("#all-menu a").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('#deleteDiscountButton').click(function(){
                $('#loadingScreen').removeClass('hidden');
            });
            $('#submitDiscountButton').click(function(){
                $('#loadingScreen').removeClass('hidden');
            });

            $('#sendSSButton').click(function(){
                $('#loadingScreen').removeClass('hidden');
            })

            jQuery(document).on("click", ".showMsgModalBtn", function(){
                //alert('yes');
                var slug = $(this).data('slug');
                $('#acceptMenuRemarks').data('slug', slug);
                $('#msgtxt').val('');
            });

            jQuery(document).on("click", ".myMsg", function(){
                //alert('yes');
                var slug = $(this).data('slug');
                $('#acceptMenuRemarks').data('slug', slug);
                $('#msgtxt').val('');
                $('#openMenuMessageModal').click();
            });
            jQuery(document).on("click", ".orderSelection", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var checkbox = $('#orderSelection'+slug).is(":checked")
                $.ajax({
                    url:"{{ route('pos.discountSelected') }}",
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

            jQuery(document).on("click", "#acceptMenuRemarks", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var msgtxt = $('#msgtxt').val();
                $.ajax({
                    url:"{{ route('pos.menuRemarks') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token,
                        msgtxt: msgtxt
                    },
                    success:function(result){
                    }
                })
            });
            
            $('#showPassword').click(function(){
                if ($("#sec_code").prop("type") == 'text'){
                    $("#sec_code").prop("type", "password");
                } else {
                    $("#sec_code").prop("type", "text");
                }
                
            });

            jQuery(document).on("click", ".submitSecCode", function(){
                //var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var sec = $('#sec_code').val();
                if (sec == ''){
                    $('#secCodeError').removeClass('hidden');
                    $('#secCodeError').html('Please input Security Code!')
                } else {
                    $.ajax({
                        url:"{{ route('pos.check') }} ",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            sec: sec,
                            _token: _token
                        },
                        success:function(result){
                            //$('#ordersCount').html(result.ordersCount);
                            if( result.message == 'error' ){
                                $('#secCodeError').removeClass('hidden');
                                $('#serverNameDiv').html('None');
                                $('#secCodeError').html('Security code is incorrect, Please try again!')
                            } else {
                                location.reload();
                                
                            }
                        }
                    })
                }
                //alert(sec);
                
                        
                });

                jQuery(document).on("click", "#logoutServerBtn", function(){
                //var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                
                    $.ajax({
                        url:"{{ route('staff.serverout') }} ",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            _token: _token
                        },
                        success:function(result){
                           $('#cashierId').val(0);
                           $('#sec_code').val('');
                           $('#ordersCount').html('0');
                           $('#serverNameDiv').html('None');
                           $('#openServerPasswordModal').click(); 
                        }
                    })
                
                
                        
                });

                $('.mopButton').click(function(){
                    var mop = $(this).data('mop');
                    //$('#mopselected').val(mop);
                    $('#selectedMOPtxt').text(mop);
                    //$('#paymentMethod').val('1');

                    {{--$.ajax({
                        url:"{{ route('staff.serverout') }} ",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            _token: _token
                        },
                        success:function(result){
                           $('#cashierId').val(0);
                           $('#sec_code').val('');
                           $('#ordersCount').html('0');
                           $('#serverNameDiv').html('None');
                           $('#openServerPasswordModal').click(); 
                        }
                    })--}}
                });


            
            
        });
    </script>
</x-app-layout>
