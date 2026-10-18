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

    {{-- MOP MODAL --}}
        <!-- Modal toggle -->
        <button data-modal-target="MOPModal" data-modal-toggle="MOPModal" id="openMOPModal" class="hidden" type="button"></button>
    
        <!-- Main modal -->
        <div id="MOPModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-auto overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full " >
            <div class="relative w-full max-w-5xl max-h-full ">
                <!-- Modal content -->
                <div class="relative  rounded-lg shadow bg-white">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Mode of Payment
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="MOPModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <input type="hidden" name="mop" id="mop">
                        <div class="grid grid-cols-3 gap-2 h-96 overflow-y-auto overflow-x-hidden">

                            <!-- CASH -->
                            <div>
                                <button type="button" data-mop="CASH" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-emerald-500 hover:bg-emerald-600 font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-money-bill text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">CASH</span>
                                </button>
                            </div>

                            <!-- MASTERCARD -->
                            <div>
                                <button type="button" data-mop="CREDIT CARD - MASTERCARD" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#d66625] hover:bg-[#d66625]/90 focus:ring-4 focus:ring-[#d66625]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-master-card text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">CREDIT CARD - MASTERCARD</span>
                                </button>
                            </div>

                            <!-- VISA -->
                            <div>
                                <button type="button" data-mop="VISA" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2557D6] hover:bg-[#2557D6]/90 focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-card-atm text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">VISA</span>
                                </button>
                            </div>

                            <!-- GCASH -->
                            <div>
                                <button type="button" data-mop="GCASH" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#5686ff] hover:bg-[#5686ff]/90 focus:ring-4 focus:ring-[#5686ff]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-money-bill text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">GCASH</span>
                                </button>
                            </div>

                            <!-- GCASH CREDIT GIVES -->
                            <div>
                                <button type="button" data-mop="GCASH CREDIT GIVES" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#5686ff] hover:bg-[#5686ff]/90 focus:ring-4 focus:ring-[#5686ff]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-money-bill text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">GCASH CREDIT GIVES</span>
                                </button>
                            </div>

                            <!-- AMEX -->
                            <div>
                                <button type="button" data-mop="AMEX" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2557D6] hover:bg-[#2557D6]/90 focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-credit-card text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">AMEX</span>
                                </button>
                            </div>

                            

                            <!-- BANK TRANSFER -->
                            <div>
                                <button type="button" data-mop="BANK TRANSFER" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#239c3b] hover:bg-[#239c3b]/90 focus:ring-4 focus:ring-[#239c3b]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-exchange text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">BANK TRANSFER</span>
                                </button>
                            </div>

                            <!-- DEBIT -->
                            <div>
                                <button type="button" data-mop="DEBIT" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2557D6] hover:bg-[#2557D6]/90 focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-card-atm text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">DEBIT</span>
                                </button>
                            </div>

                            <!-- DINERS -->
                            <div>
                                <button type="button" data-mop="DINERS" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2557D6] hover:bg-[#2557D6]/90 focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-transaction text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">DINERS</span>
                                </button>
                            </div>

                            <!-- DOWNPAYMENT -->
                            <div>
                                <button type="button" data-mop="DOWN PAYMENT" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2557D6] hover:bg-[#2557D6]/90 focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-transaction text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">DOWN PAYMENT</span>
                                </button>
                            </div>

                            

                            

                            <!-- GIFT CERT -->
                            <div>
                                <button type="button" data-mop="GIFT CERT" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2557D6] hover:bg-[#2557D6]/90 focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-gift text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">GIFT CERT</span>
                                </button>
                            </div>

                            <!-- JCB -->
                            <div>
                                <button type="button" data-mop="JCB" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2557D6] hover:bg-[#2557D6]/90 focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-bolt text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">JCB</span>
                                </button>
                            </div>


                            

                            <!-- PAYMAYA -->
                            <div>
                                <button type="button" data-mop="PAYMAYA" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2cbb3d] hover:bg-[#2cbb3d]/90 focus:ring-4 focus:ring-[#2cbb3d]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-receipt text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">PAYMAYA</span>
                                </button>
                            </div>

                            <!-- PAYMAYA QR -->
                            <div>
                                <button type="button" data-mop="PAYMAYA QR" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#2cbb3d] hover:bg-[#2cbb3d]/90 focus:ring-4 focus:ring-[#2cbb3d]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-qrcode-scan text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">PAYMAYA QR</span>
                                </button>
                            </div>

                            <!-- UNION BANK -->
                            <div>
                                <button type="button" data-mop="UNION BANK" data-modal-hide="MOPModal" data-modal-target="amountReceivedModal" data-modal-toggle="amountReceivedModal" class="mopButton text-white bg-[#da8632] hover:bg-[#da8632]/90 focus:ring-4 focus:ring-[#da8632]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-5 text-center inline-flex items-center  mr-2 mb-2 w-full h-36">
                                    <i class="uil uil-university text-6xl mr-3"></i>
                                    <span class="text-3xl font-bold">UNION BANK</span>
                                </button>
                            </div>

                            

                            
                            
                        </div>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button data-modal-hide="MOPModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-full">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- MOP MODAL END --}}

    {{-- AMOUNT RECEIVED MODAL --}}
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
                            <span class="text-base font-medium ml-1"> ₱ </span><span id="actualAmount" class="text-base font-bold"></span>
                        </div>
                        <div class="mb-2">
                            <label for="amount" class="block mb-2 text-base font-medium text-gray-900">Amount Received</label>
                            <input type="text" id="amount" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                            <p id="amountError" class="hidden text-sm text-red-500 italic">Invalid amount. Please enter an amount more than or equal to <span class="text-base font-medium ml-1"> ₱ </span><span id="actualAmount" class="text-base font-bold"></span></p>
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
                        <button id="payNowButton" data-modal-hide="amountReceivedModal" data-slug="" data-amount="" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Mark as Paid</button>
                        <button id="payNowCancelButton" data-modal-hide="amountReceivedModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- AMOUNT RECEIVED MODAL END --}}

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
                        ₱<p id="changeP" class="ml-2"></p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-300 rounded-b">
                        <button data-modal-hide="changeModal" id="closeChangeButton" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-300 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-full">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- CHANGE MODAL END --}}

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
                            {{--<div class="w-32">
                                <a href="{{ (Auth::user()->role == 1) ? route('dashboard')  : route('pos.index') }}" class=" md:block text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px text-center"><i class="uil uil-arrow-left mr-1"></i>BACK</a>
                            </div>--}}
                            {{-- <div class="w-32">
                                <a href="{{ url('/report/print/'.$startDate.'/'.$endDate.'/'.$category.'/'.$report) }}" target="_blank" class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px text-center"><i class="uil uil-print mr-1"></i>PRINT</a>
                            </div> --}}
                        </div>
                    </div>
                {{-- CONTROLS END --}}

                <div>
                    <div class="my-4 flex justify-between">
                        <h1 class="text-3xl">Account Receivables</h1>
                        {{--<form method="POST" action="{{ route('ar.generate') }}" class="flex">
                            @csrf
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
                        </form>--}}
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
                                                Number
                                            </th>
                                            <th class="px-6 text-center">
                                                Mode of Payment
                                            </th>
                                            <th class="px-6 text-center">
                                                Last Payment
                                            </th>
                                            <th class="px-6 text-center">
                                                Remaining Balance
                                            </th>
                                            
                                            <th class="px-6 text-center">
                                                Cashier
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="transactionDiv">
                                        @foreach ($results as $result)
                                            <tr  class="resultRow border-b hover:bg-gray-200 even:bg-gray-100">
                                                <th  class="px-6 py-1 text-center font-medium text-gray-900 whitespace-nowrap">
                                                    <span data-id="{{ $result->id }}">{{ date('Y-m-d h:i:s A', strtotime($result->created_at)) }}</span>
                                                </th>
                                                <td  class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->number }}
                                                </td>
                                                <td  class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->mode_of_payment }}
                                                </td>
                                                <td  class="px-6 py-1 text-center whitespace-nowrap">
                                                    ₱ {{ number_format($result->amount, 2, '.', ',') }}
                                                </td>
                                                <td  class="px-6 py-1 text-center whitespace-nowrap">
                                                    ₱ {{ number_format($result->balance, 2, '.', ',') }}
                                                </td>
                                                
                                                <td class="px-6 py-1 text-center whitespace-nowrap">
                                                    {{ $result->name }}
                                                </td>
                                                {{--<td class="px-6 py-4 text-center whitespace-nowrap">
                                                    <a data-slug="{{ $result->slug }}" data-amount="{{ $result->balance }}"
                                                    class="paymentBtn text-blue-600 hover:underline font-semibold text-sm">Add Payment</a>
                                                </td>--}}
                                                {{-- url('/expenses/edit/'.$inventory->id)  {{ $inventory->id }} --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {{-- TABLE END --}}

                    
                </div>

                


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
            

            $('.numpad-button').on('click', function() {
                const $amount = $('#amount');
                const buttonValue = $(this).data('value');

                if (buttonValue == 'bs') {
                    $amount.val($amount.val().slice(0, -1));
                }else if(buttonValue == 'clr'){
                    $amount.val('');
                }else if(buttonValue !== undefined){
                    $amount.val($amount.val() + buttonValue);

                    var inputValue = $('#amount').val();

                    var dotIndex = inputValue.indexOf('.');
                    if (dotIndex !== -1 && inputValue.indexOf('.', dotIndex + 1) !== -1) {
                        $('#amount').val(inputValue.substr(0, dotIndex + 1) + inputValue.substr(dotIndex + 1).replace(/\./g, ''));
                    }
                }
            });
            

            $('.paymentBtn').click(function(){
                var amount = $(this).data('amount');
                var slug = $(this).data('slug');
                //alert(amount)
                $('#payNowButton').data('slug', slug);
                $('#payNowButton').data('amount', amount);
                $('#actualAmount').html(amount);
                $('#openMOPModal').click();
            });


            $('.mopButton').click(function(){
                var mop = $(this).data('mop');
                $('#mop').val(mop);
                $('#paymentMethod').val('1');
            });

            $('#closeChangeButton').click(function(){
                location.reload();
            });
            

            $('#payNowButton').click(function(){
                var amount = $(this).data('amount');
                var slug = $(this).data('slug');
                var amountInput = $('#amount').val();
                var mop = $('#mop').val();
                var _token = $('input[name="_token"]').val();

             

                
                
                //alert(slug)
                var change = ((amountInput - amount) < 0) ? 0 : amountInput - amount ;
                
                $('#openChangeModal').click();
                $('#changeP').html(change.toFixed(2));

                $.ajax({
                        url:"{{ route('ar.paid') }}",
                        method:"POST",
                        dataType: 'json',
                        data:{
                            amount: amount,
                            amountInput: amountInput,
                            mop: mop,
                            slug: slug,
                            _token: _token
                        },
                        success:function(result){
                            
                        }
                    })

                

            });

            

        });
    </script>
</x-app-layout>
