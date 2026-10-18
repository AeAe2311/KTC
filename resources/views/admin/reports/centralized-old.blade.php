<x-app-layout>
<style>
.chart-section {
    width: 100%;
    height: 100%;
}

.hidden {
    display: none;
}

#reportChart, #paymentChart, #deductionsChart {
    width: 100% !important;
    height: 400px !important;
}
</style>    
    @section('page_title', 'REPORTS')
    
    {{-- PAY MODAL --}}
    <div id="payModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="flex items-center font-semibold text-gray-900">
                        <span class="text-base text-green-500 md:text-lg lg:text-xl">Mark as Paid</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="payModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-6">
                    @csrf
                    <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                        <h1 id="payName" class="pb-5 text-xl font-bold"></h1>
                        <div class="mb-6">
                            <label for="quantity" class="block text-sm font-medium text-gray-900">Quantity</label>
                            <h1 id="payQuantity" class="pb-2 text-lg font-bold"></h1>
                        </div>
                        <div class="mb-6">
                            <label for="price" class="block text-sm font-medium text-gray-900">Amount</label>
                            <h1 id="payPrice" class="pb-2 text-lg font-bold"></h1>
                        </div>
                        <div class="mb-6">
                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                            <div class="relative max-w-sm">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input datepicker type="text" id="payDate" value="" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                            </div>
                        </div>
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="button" data-modal-hide="payModal" class="paySubmit w-32 text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Mark as Paid</button>
                    <button data-modal-hide="payModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- PAY MODAL END --}}

    {{-- EDIT MODAL --}}
    <div id="editModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="flex items-center font-semibold text-gray-900">
                        <span class="text-base text-blue-700 md:text-lg lg:text-xl">Edit</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="editModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-6">
                    @csrf
                    <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                        <h1 class="pb-5 text-xl font-bold editName"></h1>
                        <div class="mb-6 nameDiv">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Item Name</label>
                            <div class="flex items-center">
                                <input type="text" id="name" class="editName bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                            </div>
                        </div>
                        @if(isset($category) && $category != 'sales')
                            <div class="mb-6">
                                <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900">Quantity</label>
                                <div class="flex items-center">
                                    <input type="text" id="editQuantity" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                                    <span id="addUnit" class="px-3 text-base font-bold text-gray-600"></span>
                                </div>
                            </div>
                        @endif
                        <div class="mb-6">
                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">{{ (isset($category) && $category == 'sales' ? 'Amount' : 'Price') }}</label>
                            <div class="flex items-center">
                                <span class="px-3 text-base font-bold text-gray-600">₱</span>
                                <input type="text" id="editPrice" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                            </div>
                        </div>
                        @if(isset($category) && $category == 'sales')
                            <div class="mb-6">
                                <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900">Mode of Payment</label>
                                <div class="flex items-center">
                                    <select name="mop_val" id="mop_val" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                                        <option value="CASH">CASH</option>
                                        <option value="AMEX">AMEX</option>
                                        <option value="BANK TRANSFER">BANK TRANSFER</option>
                                        <option value="DEBIT">DEBIT</option>
                                        <option value="DINERS">DINERS</option>
                                        <option value="DOWN PAYMENT">DOWN PAYMENT</option>
                                        <option value="GCASH CREDIT GIVES">GCASH CREDIT GIVES</option>
                                        <option value="GCASH">GCASH</option>
                                        <option value="GIFT CERT">GIFT CERT</option>
                                        <option value="JCB">JCB</option>
                                        <option value="MASTERCARD">MASTERCARD</option>
                                        <option value="PAYMAYA">PAYMAYA</option>
                                        <option value="BDO PAY">BDO PAY</option>
                                        <option value="UNION PAY">UNION PAY</option>
                                        <option value="VISA">VISA</option>
                                        <option value="MULTIPLE">MULTIPLE</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="mb-6">
                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                            <input type="datetime-local" id="editDate" value="{{ date('Y-m-d\TH:i') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="button" data-modal-hide="editModal" class="editSubmit w-24 text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Update</button>
                    <button data-modal-hide="editModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- EDIT MODAL END --}}

    {{-- PAY EXPENSES MODAL --}}
    <div id="payModal1" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="flex items-center font-semibold text-gray-900">
                        <span class="text-base text-blue-700 md:text-lg lg:text-xl">Pay Accounts Payable</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="payModal1">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-6">
                    @csrf
                    <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                        @if(isset($category) && $category == 'expenses')
                            <div class="mb-6">
                                <label for="quantity" class="block mb-2 text-sm font-medium text-gray-900">Mode of Payment</label>
                                <div class="flex items-center">
                                    <select name="mop_val" id="mop_val" class="inputNumber bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required autocomplete="off">
                                        <option value="CASH">CASH</option>
                                        <option value="AMEX">AMEX</option>
                                        <option value="BANK TRANSFER">BANK TRANSFER</option>
                                        <option value="DEBIT">DEBIT</option>
                                        <option value="DINERS">DINERS</option>
                                        <option value="DOWN PAYMENT">DOWN PAYMENT</option>
                                        <option value="GCASH CREDIT GIVES">GCASH CREDIT GIVES</option>
                                        <option value="GCASH">GCASH</option>
                                        <option value="GIFT CERT">GIFT CERT</option>
                                        <option value="JCB">JCB</option>
                                        <option value="MASTERCARD">MASTERCARD</option>
                                        <option value="PAYMAYA">PAYMAYA</option>
                                        <option value="PAYMAYA QR">PAYMAYA QR</option>
                                        <option value="UNION BANK">UNION BANK</option>
                                        <option value="VISA">VISA</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="mb-6">
                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                            <input type="datetime-local" id="paymentDate" value="{{ date('Y-m-d\TH:i') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="button" data-modal-hide="payModal1" class="payModal1Submit w-30 text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Mark As Paid</button>
                    <button data-modal-hide="payModal1" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- PAY EXPENSES MODAL END --}}

    {{-- DELETE MODAL --}}
    <div id="deleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute w-full max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow top-1/2 left-1/2">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="flex items-center font-semibold text-gray-900">
                        <i class="mr-2 text-xl text-red-700 uil uil-exclamation-triangle md:text-2xl lg:text-3xl"></i>
                        <span class="text-base text-red-700 md:text-lg lg:text-xl">Delete Item</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="deleteModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-3 space-y-6 md:py-6">
                    <p class="text-xs leading-relaxed text-gray-500 md:text-base">
                        Are you sure you want to delete this?
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="button" data-modal-hide="deleteModal" class="deleteSubmit w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</button>
                    <button data-modal-hide="deleteModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- DELETE MODAL END --}}
    
    <div class="p-3 lg:pt-3 {{ (Auth::user()->role == 2) ? '' : ' lg:ml-64' }}">
        <div class="py-3">


            
            @if(isset($results))
                {{-- CONTROLS --}}
                <!-- div class="mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                @php
                                    $ncat = '';
                                    if(isset($category)) {
                                        if($category == 'sales'){
                                            if ($report == 'served_list') {
                                                $ncat = 'Served Sales Transaction Logs';
                                            }elseif ($report == 'summary'){
                                                $ncat = 'Sales Summary';
                                            }elseif ($report == 'notServed_list') {
                                                $ncat = 'Not Served Sales Transaction Logs';
                                            } elseif ($report == 'hoursale') {
                                                $ncat = 'Hourly Sales Report';
                                            } elseif ($report == 'compli') {
                                                $ncat = 'Complimentary Report';
                                            } elseif ($report == 'compli_break') {
                                                $ncat = 'Complimentary Breakdown';
                                            } elseif ($report == 'beverages') {
                                                $ncat = 'Beverages Report';
                                            } elseif ($report == 'food') {
                                                $ncat = 'Food Report';
                                            } elseif ($report == 'server') {
                                                $ncat = 'Server Report';
                                            } elseif ($report == 'zreading') {
                                                $ncat = 'Z Reading Report'; // This line
                                            } elseif ($report == 'pax') {
                                                $ncat = '# of Pax Report';
                                            } elseif ($report == 'discount') {
                                                $ncat = 'Discount Report';
                                            } elseif ($report == 'srpwd') {
                                                $ncat = 'SR & PWD Report';
                                            } else {
                                                $ncat = 'Sales Transaction Logs';
                                            }
                                        }else if($category == 'expenses'){
                                            if ($report == 'unpaid'){
                                                $ncat = 'Accounts Payable';
                                            } elseif ($report == 'cos'){
                                                $ncat = 'Cost of Sales Logs';
                                            } elseif ($report == 'ooe') {
                                                $ncat = 'Other Operating Expenses'; 
                                            } else {
                                                $ncat = 'Expenses Transaction Logs';
                                            }
                                        }else if($category == 'inventory'){
                                            if ($report == 'logs') {
                                                $ncat = 'Raw Materials Outgoing Logs';
                                            }else{
                                                $ncat = 'Raw Materials Stocks Report';
                                            }
                                        }else if($category == 'menu'){
                                            if ($report == 'rank') {
                                                $ncat = 'Menu Rankings';
                                            }else{
                                                $ncat = 'Menu Current Stock';
                                            }
                                        }else if($category == 'invmenu'){
                                            if ($report == 'summary') {
                                                $ncat = 'Inventory Summary';
                                            }
                                        }else if($category == 'accounting'){
                                            if ($report == 'balsheet') {
                                                $ncat = 'Balance Sheet';
                                            } elseif ($report == 'incomeState') {
                                                $ncat = 'Income Statement';
                                            } else {
                                                $ncat = 'Accounting Report';
                                            }
                                        }else if($category == 'waste'){
                                            if ($report == 'raw') {
                                                $ncat = 'Raw Inventory Waste';
                                            }else{
                                                $ncat = 'Menu Waste';
                                            }
                                        }else if($category == 'remit'){
                                            $ncat = 'Remittance Report';
                                        }
                                    }
                                @endphp
                                {{ $ncat }}
                            </h2>
                            <p class="text-gray-600">
                                Date Range: {{ date('M d, Y h:i A', strtotime($startDate)) }} to {{ date('M d, Y h:i A', strtotime($endDate)) }}
                            </p>
                            @if(isset($selectedBranch) && Auth::user()->role == 1)
                                <p class="text-gray-600">Branch: {{ $selectedBranchName ?? 'Selected Branch' }}</p>
                            @endif
                            <p class="text-gray-600">Total Records: {{ $resultsCount ?? count($results) }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('areport.index') }}" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none text-center">
                                <i class="mr-1 uil uil-arrow-left"></i>BACK
                            </a>

                        </div>
                    </div>
                </div -->
                <div class="bg-gray-200 overflow-hidden shadow-md rounded-lg p-3 mb-6">
                    <form action="{{ route('areport.generate') }}" method="POST">
                        @csrf
                        <div class="flex items-end gap-4">
                            
                            <!-- Date Range -->
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-900 mb-1">Date</label>
                                <div class="flex items-center gap-x-2">
                                    <input type="datetime-local" name="start" id="dateStart"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="{{ date('Y-m-d 00:00:00') }}">
                                    <span class="text-gray-700 font-medium">to</span>
                                    <input type="datetime-local" name="end" id="dateEnd"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                        value="{{ date('Y-m-d 23:59:00') }}">
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-900 mb-1">Category</label>
                                <select id="category" name="category"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="remit">Remittance</option>
                                    <option value="accounting">Accounting Reports</option> <!-- ken added New OPTION FOR accounting reports -->
                                    <option value="dailySR">Daily Sales Reports</option>
                                    <option value="sales">Sales</option>
                                    <option value="expenses">Expenses</option>
                                    <option value="both">Sales & Expenses</option>
                                    <option value="inventory">Raw Materials</option>
                                    <option value="menu">Menu</option>
                                    <option value="invmenu">Raw Materials & Menu</option>
                                    <option value="waste">Waste</option>
                                </select>
                            </div>

                            <!-- Branch Selection (Only for admin users) -->
                            @auth
                                @if(auth()->user()->role == 1)
                                    <!-- Branch -->
                                    <div class="flex-1">
                                        <label for="branch" class="block text-sm font-medium text-gray-900 mb-1">
                                            Branch <span class="text-red-500">*</span>
                                        </label>
                                        <select id="branch" name="branch"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                            <option value="all">All Branches</option>
                                            @foreach ($branchs as $branch)
                                                @if ($branch->id != 1) {{-- Assuming id=1 is the main branch or admin branch --}}
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endauth

                            <!-- Report -->
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-900 mb-1">Report</label>
                                <select id="report" name="report"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="report">Report</option>
                                </select>
                            </div>

                            <!-- Generate Button -->
                            <div class="flex-1">
                                <button
                                    class="bg-blue-600 font-bold text-white w-full py-2.5 rounded-lg hover:scale-105 transition-transform">
                                    Generate
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Dynamic Graph Section -->
                {{-- Add Chart Type Selector --}}
                @if(isset($chartData) && !empty($chartData))
                <div class="bg-white shadow-md rounded-lg p-4 mb-6">
                    @if ($category == 'sale' && $report == 'summary' && isset($grandTotals))
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                                            <!-- Grand Total -->
                                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">Grand Total</span>
                                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                </div>
                                                <div class="mt-2 text-xl font-semibold text-gray-900">
                                                    ₱ {{ number_format($grandTotals['total'], 2) }}
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">All sales combined</div>
                                            </div>

                                            <!-- Net Total -->
                                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">Net Total</span>
                                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                </div>
                                                <div class="mt-2 text-xl font-semibold text-blue-600">
                                                    ₱ {{ number_format($grandTotals['net'], 2) }}
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">After VAT & charges</div>
                                            </div>

                                            <!-- VAT Total -->
                                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">VAT Total</span>
                                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                                </div>
                                                <div class="mt-2 text-xl font-semibold text-purple-600">
                                                    ₱ {{ number_format($grandTotals['vat'], 2) }}
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">Total VAT collected</div>
                                            </div>

                                            <!-- Service Charge -->
                                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">Service Charge</span>
                                                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                                </div>
                                                <div class="mt-2 text-xl font-semibold text-orange-600">
                                                    ₱ {{ number_format($grandTotals['sc'], 2) }}
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">Additional charges</div>
                                            </div>

                                            <!-- Served Total -->
                                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">Served</span>
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                </div>
                                                <div class="mt-2 text-xl font-semibold text-emerald-600">
                                                    ₱ {{ number_format($grandTotals['served_total'], 2) }}
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">Completed orders</div>
                                            </div>

                                            <!-- Not Served Total -->
                                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs uppercase tracking-wide text-gray-500">Not Served</span>
                                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                </div>
                                                <div class="mt-2 text-xl font-semibold text-red-600">
                                                    ₱ {{ number_format($grandTotals['not_served_total'], 2) }}
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">Unfulfilled orders</div>
                                            </div>

                                        </div>
                    @endif
                 
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                        <div>
                            <h3 class="text-xl font-semibold">Chart Visualization</h3>
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <div class="flex items-center">
                                <label class="mr-2 text-sm font-medium text-gray-700">Chart Type:</label>
                                <select id="chartTypeSelect" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2">
                                    <option value="bar">Bar Chart</option>
                                    <option value="stackedBar">Stacked Bar</option>
                                    <option value="line">Line Chart</option>
                                    <option value="pie">Pie Chart</option>
                                    <option value="doughnut">Doughnut Chart</option>
                                    <option value="area">Area Chart</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div id="reportChartContainer">
                        @if(isset($category) && $category == 'sales' && $report == 'zreading')
                            
                            <!-- Z-Reading Graph Type Selector -->
                            <div class="flex items-center mb-4">
                                <label class="mr-2 text-sm font-medium text-gray-700">Graph Type:</label>
                                <select id="zreadingGraphTypeSelect" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2">
                                    <option value="summary" selected>Summary Graph</option>
                                    <option value="payment">Payment Methods Graph</option>
                                    <option value="deductions">Deductions Graph</option>
                                </select>
                            </div>
                            
                            <div id="zreadingSummaryChart" class="chart-section">
                                @if(isset($summaryChartData) && !empty($summaryChartData))
                                    <canvas id="reportChart" width="400" height="400"></canvas>
                                @else
                                    <div class="p-4 text-center text-gray-500">No summary chart data available</div>
                                @endif
                            </div>
                            <div id="zreadingPaymentChart" class="chart-section hidden">
                                @if(isset($paymentChartData) && !empty($paymentChartData))
                                    <canvas id="paymentChart" width="400" height="400"></canvas>
                                @else
                                    <div class="p-4 text-center text-gray-500">No payment chart data available</div>
                                @endif
                            </div>
                            <div id="zreadingDeductionsChart" class="chart-section hidden">
                                @if(isset($deductionsChartData) && !empty($deductionsChartData))
                                    <canvas id="deductionsChart" width="400" height="400"></canvas>
                                @else
                                    <div class="p-4 text-center text-gray-500">No deductions chart data available</div>
                                @endif
                            </div>
                        @else
                            <!-- Regular Chart for other reports -->
                            @if(isset($chartData) && !empty($chartData))
                                <canvas id="reportChart"></canvas>
                            @else
                                <div class="p-4 text-center text-gray-500">No chart data available for this report</div>
                            @endif
                        @endif
                    </div>
                
                @endif
              
                <!-- Menu Category Filter - Only show for menu rankings -->
                @if(isset($category) && $category == 'menu' && $report == 'rank')
                <div class="bg-white shadow-md rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Filter by Menu Category</h3>
                            <p class="text-sm text-gray-600">Select a category to filter the menu rankings</p>
                        </div>
                        <div class="w-64">
                            <form id="categoryFilterForm" method="POST" action="{{ route('areport.generate') }}">
                                @csrf
                                <input type="hidden" name="start" value="{{ $startDate }}">
                                <input type="hidden" name="end" value="{{ $endDate }}">
                                <input type="hidden" name="category" value="menu">
                                <input type="hidden" name="report" value="rank">
                                @if(isset($selectedBranch))
                                    <input type="hidden" name="branch" value="{{ $selectedBranch }}">
                                @endif
                                <select id="menuCategoryFilter" name="category_id" class="block w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" autocomplete="off">
                                    <option value="">All Categories</option>
                                    @foreach ($menucats as $cats)
                                        <option value="{{ $cats->id }}" @if(isset($categoryId) && $cats->id == $categoryId) selected @endif>
                                            {{ $cats->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                </div>
                <!-- Export Button Section -->
                <div class="mb-4 flex items-center justify-between gap-4">
                    <button id="exportReportBtn" type="button" title="Print Report" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg shadow-md transition duration-300 ease-in-out flex items-center justify-center w-11 h-11">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 9V2h12v7" />
                            <path d="M6 19H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                            <path d="M6 14h12v7H6z" />
                        </svg>
                        <span class="sr-only">Print Report</span>
                    </button>

                    <div class="flex-1 flex justify-center">
                        @if(isset($category) && $category == 'dailySR')
                            <div class="flex flex-wrap gap-2 items-center justify-center">
                                <button id="dailySRTabSales" type="button" class="dailySR-tab px-4 py-2 rounded-lg font-semibold bg-blue-600 text-white">Sales</button>
                                <button id="dailySRTabAR" type="button" class="dailySR-tab px-4 py-2 rounded-lg font-semibold bg-gray-200 text-gray-700">AR Collection</button>
                                <button id="dailySRTabOnline" type="button" class="dailySR-tab px-4 py-2 rounded-lg font-semibold bg-gray-200 text-gray-700">Online Payments</button>
                                <button id="dailySRTabCheck" type="button" class="dailySR-tab px-4 py-2 rounded-lg font-semibold bg-gray-200 text-gray-700">Check Payment</button>
@if(isset($category) && $category == 'dailySR')
    <div style="background: yellow; padding: 10px; margin: 10px; font-size: 12px;">
        <strong>DEBUG:</strong>
        onlinePaymentLogs count: {{ ($onlinePaymentLogs ?? collect())->count() }} |
        checkPaymentLogs count: {{ ($checkPaymentLogs ?? collect())->count() }} |
        allPaymentLogs count: {{ ($allPaymentLogs ?? collect())->count() }}
    </div>
@endif                                
                            </div>
                        @endif
                    </div>

                    <div class="relative">
                        <button id="columnReducerBtn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                            <i class="fas fa-table mr-2"></i>Columns
                        </button>

                        <!-- Column Selector Dropdown -->
                        <div id="columnSelector" class="absolute top-full right-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg z-50 hidden">
                            <div class="p-3">
                                <h4 class="font-semibold text-gray-800 mb-2">Show/Hide Columns</h4>
                                <div id="columnCheckboxes" class="space-y-2 max-h-64 overflow-y-auto">
                                    <!-- Checkboxes will be populated dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ken 252306001 added balance sheet table for accounting reports 4-23-3036 -->
                @if(isset($category) && $category == 'accounting' && $report == 'balsheet')
                    <div id="balanceSheetContainer" class="bg-white shadow-md rounded-lg p-6 mb-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-semibold">Balance Sheet</h2>
                            <p class="text-gray-600">Date Range: {{ date('M d, Y h:i A', strtotime($startDate)) }} to {{ date('M d, Y h:i A', strtotime($endDate)) }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <h2 class="font-semibold">ASSETS</h2>                                        
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Category</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Account Name</th>
                                        <!-- <th class="px-4 py-3 text-left font-semibold text-gray-700">Source / Computation</th> -->
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Amount (₱)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr class="bg-gray-100">
                                        <td class="px-4 py-4 font-semibold">Current Assets</td>
                                        <td class="px-4 py-4">Cash</td>
                                        <!-- <td class="px-4 py-4 font-mono text-xs text-gray-700">SUM(total) FROM transactions WHERE status = 'PAID' AND LOWER(mode_of_payment) = 'cash'</td> -->
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($cash ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-4"></td>
                                        <td class="px-4 py-4">Trade and Other Receivables</td>
                                        <!-- <td class="px-4 py-4 font-mono text-xs text-gray-700">SUM(balance) FROM account_receivables WHERE balance > 0</td> -->
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($receivables ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-4"></td>
                                        <td class="px-4 py-4">Inventories</td>
                                        <!-- <td class="px-4 py-4 font-mono text-xs text-gray-700">SUM(quantity * price) FROM inventories</td> -->
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($inventories ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-4"></td>
                                        <td class="px-4 py-4">Prepayments and Other Current Assets</td>
                                        <!-- <td class="px-4 py-4 font-mono text-xs text-gray-700">SUM(amount) FROM prepayments (or manual if none)</td> -->
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($prepayments ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="px-4 py-4">&nbsp;</td>
                                        <td class="px-4 py-4">Total Current Assets</td>
                                        <!-- <td class="px-4 py-4">Cash + Receivables + Inventories + Prepayments</td> -->
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($totalCurrentAssets ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td class="px-4 py-4">Property and Equipment</td>
                                        <td class="px-4 py-4">Property and Equipment, Net</td>
                                        <!-- <td class="px-4 py-4 font-mono text-xs text-gray-700">SUM(acquisition_cost - accumulated_depreciation) FROM assets</td> -->
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($propertyAndEquipment ?? 0, 2) }}</td>
                                    </tr>
                                    <tr  class="bg-gray-100 font-semibold">
                                        <td class="px-4 py-4">&nbsp;</td>
                                        <td class="px-4 py-4 text-xl text-right font-semibold">TOTAL ASSETS</td>                                         
                                        <td class="px-4 py-4 text-xl text-right">₱ {{ number_format($totalAssets ?? 0, 2) }}</td> 
                                    </tr>  
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-8 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <h2 class="font-semibold">LIABILITIES AND OWNER’S EQUITY</h2>                                          
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Category</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Account Name</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Amount (₱)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="px-4 py-4">Current Liabilities</td>
                                        <td class="px-4 py-4">Trade and Other Payables</td>
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($trade_payables ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td class="px-4 py-4">Long-Term Debt</td>
                                        <td class="px-4 py-4">Loans Payable</td>
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($loans_payable ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="px-4 py-4">&nbsp;</td>
                                        <td class="px-4 py-4">Total Liabilities</td>
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($totalLiabilities ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td class="px-4 py-4">Owner’s Equity</td>
                                        <td class="px-4 py-4">Murang Karne, Capital</td>
                                        <td class="px-4 py-4 text-right">₱ {{ number_format($capital ?? 0, 2) }}</td>
                                    </tr>
                                    <tr  class="bg-gray-100 font-semibold">
                                        <td class="px-4 py-4">&nbsp;</td>
                                        <td class="px-4 py-4 text-xl text-right font-semibold">TOTAL LIABILITIES AND OWNER’S EQUITY </td>                                         
                                        <td class="px-4 py-4 text-xl text-right">₱ {{ number_format($totalLiabilitiesEquity ?? 0, 2) }}</td> 
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif                  
                @if(isset($category) && $category == 'accounting' && $report == 'incomeState')
                    <div id="incomeStatementContainer" class="bg-white shadow-md rounded-lg p-6 mb-6">
                        <div class="mb-5">
                            <h2 class="text-xl font-semibold">Income Statement</h2>
                            <p class="text-gray-600">Date Range: {{ date('M d, Y h:i A', strtotime($startDate)) }} to {{ date('M d, Y h:i A', strtotime($endDate)) }}</p>
                        </div>
                        <div class="overflow-x-auto scroll-smooth">
                            <table class="table-fixed min-w-max text-sm whitespace-nowrap border-collapse border border-black">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="sticky left-0 z-40 px-4 py-3 w-[220px] text-left font-semibold bg-gray-200 border-r border-b border-black border-l border-black" rowspan="2">Category</th>
                                        @foreach($monthLabels as $label)
                                            <th class="min-w-[120px] text-center font-semibold bg-gray-200 border-r border-b border-black" colspan="2">{{ $label }}</th>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-gray-200">
                                        @foreach($monthLabels as $label)
                                            <th class="min-w-[120px] text-center font-semibold bg-gray-200 border-r border-b border-black">Amount</th>
                                            <th class="min-w-[120px] text-center font-semibold bg-gray-200 border-r border-b border-black">%</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black">
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="sticky left-0 z-20 px-4 py-4 w-[220px] bg-gray-100 border-r border-black border-l border-black">Net Sales</td>
                                        @foreach($months as $monthKey => $label)
                                            <td class="px-4 py-4 text-right border-r border-black">₱ {{ number_format($salesByMonth[$monthKey] ?? 0, 2) }}</td>
                                            <td class="px-4 py-4 text-right border-r border-black">{{ number_format($salesPctByMonth[$monthKey] ?? 0, 2) }}%</td>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td class="sticky left-0 z-20 px-4 w-[220px] bg-gray-50 border-r border-black border-l border-black">Cost of Goods Sold</td>
                                        @foreach($months as $monthKey => $label)
                                            <td class="px-4 py-4 text-right border-r border-black">₱ {{ number_format($cogsByMonth[$monthKey] ?? 0, 2) }}</td>
                                            <td class="px-4 py-4 text-right border-r border-black">{{ number_format($cogsPctByMonth[$monthKey] ?? 0, 2) }}%</td>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="sticky left-0 z-20 px-4 py-4 w-[220px] bg-gray-100 border-r border-black border-l border-black">Gross Profit</td>
                                        @foreach($months as $monthKey => $label)
                                            <td class="px-4 py-4 text-right border-r border-b border-black">₱ {{ number_format($grossProfitByMonth[$monthKey] ?? 0, 2) }}</td>
                                            <td class="px-4 py-4 text-right border-r border-b border-black">{{ number_format($grossProfitPctByMonth[$monthKey] ?? 0, 2) }}%</td>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-gray-400 font-semibold">
                                        <td class="sticky left-0 z-20 px-4 w-[220px] bg-gray-400 border-r border-black border-l border-black">Operating Expenses</td>
                                    </tr>
                                    @if(!empty($expenseCategoryData))
                                        @foreach($expenseCategoryData as $expenseCategory)
                                            <tr class="bg-gray-100 font-semibold">
                                                <td class="sticky left-0 z-20 px-4 py-4 w-[220px] bg-gray-100 border-r border-black border-l border-black">{{ $expenseCategory['category'] }}</td>
                                                @foreach($months as $monthKey => $label)
                                                    <td class="px-4 py-4 text-right border-r border-black">₱ {{ number_format($expenseCategory['total_by_month'][$monthKey] ?? 0, 2) }}</td>
                                                    <td class="px-4 py-4 text-right border-r border-black">{{ number_format($totalOperatingExpenses > 0 ? (($expenseCategory['total_by_month'][$monthKey] ?? 0) / $totalOperatingExpenses) * 100 : 0, 2) }}%</td>
                                                @endforeach
                                            </tr>
                                            @foreach($expenseCategory['items'] as $item)
                                                <tr class="bg-white">
                                                    <td class="sticky left-0 z-20 px-4 py-4 w-[220px] bg-white border-r border-black border-l border-black text-left">&nbsp;&nbsp;&nbsp;{{ $item['item'] }}</td>
                                                    @foreach($months as $monthKey => $label)
                                                        <td class="px-4 py-4 text-right border-r border-black">₱ {{ number_format($item['amount_by_month'][$monthKey] ?? 0, 2) }}</td>
                                                        <td class="px-4 py-4 text-right border-r border-black">{{ number_format($totalOperatingExpenses > 0 ? (($item['amount_by_month'][$monthKey] ?? 0) / $totalOperatingExpenses) * 100 : 0, 2) }}%</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @else
                                        <tr class="bg-white">
                                            <td class="px-4 py-4 text-center" colspan="{{ count($months) * 2 + 1 }}">No operating expense data found for the selected period.</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-gray-50 font-semibold">
                                        <td class="sticky left-0 z-20 px-4 py-4 w-[220px] bg-gray-50 border-r border-black border-l border-black">Total Operating Expenses</td>
                                        @foreach($months as $monthKey => $label)
                                            <td class="px-4 py-4 text-right border-r border-black">₱ {{ number_format($operatingByMonth[$monthKey] ?? 0, 2) }}</td>
                                            <td class="px-4 py-4 text-right border-r border-black">{{ number_format($operatingPctByMonth[$monthKey] ?? 0, 2) }}%</td>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="sticky left-0 z-20 px-4 py-4 w-[220px] bg-gray-100 border-r border-black border-l border-black">Net Income</td>
                                        @foreach($months as $monthKey => $label)
                                            <td class="px-4 py-4 text-right border-r border-black">₱ {{ number_format($netIncomeByMonth[$monthKey] ?? 0, 2) }}</td>
                                            <td class="px-4 py-4 text-right border-r border-black">{{ number_format($netIncomePctByMonth[$monthKey] ?? 0, 2) }}%</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif                  
                <!-- Dynamic Table Section -->
                {{-- Update Table Structure --}}
                <div id="dailySRTableWrapper" class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table id="reportTable" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @php
                                        // Determine table headers based on category and report
                                        if(isset($category)) {
                                            if ($category == 'sales') {
                                                if ($report == 'summary') {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Amount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Discount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total PWD/Senior</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Less Vat</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Vat Sales</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Vat</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Vat Exempt Sales</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Service Charge</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Net</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Action</th>
                                                    ';
                                                }
                                                if ($report == 'hoursale') {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Transactions</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Discount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Number of Trasactions</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Discount</th>
                                                    ';
                                                }
                                                if($report == 'list' || $report == 'served_list' || $report == 'notServed_list' || $report == 'server') {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Transaction Number</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Amount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Mode of Payment</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Transaction Type</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Order Status</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Cashier</th>
                                                    ';
                                                    if (Auth::user()->role == 1) {
                                                        echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Action</th>';
                                                    }
                                                }
                                                if($report == 'zreading'){
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Gross Sales</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Discount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total PWD/Senior</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Less Vat</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Vat Sales</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Vat Amount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Vat Exempt Sales</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Cash</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Gcash</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Credit Card</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Bancnet Card</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Visa</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">JCB</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Diners</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">American Express</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Paymaya</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Food Panda</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Grab Food</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Union Pay</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Grab Pay</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">BDO Pay</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">We Chat Pay</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">QR Ph</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Downpayment</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Gift Cert</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Service Charge</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Net</th>
                                                    ';
                                                }
                                                if ($report == 'discount') {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Discount %</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Transactions</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Amount</th>
                                                    ';
                                                }
                                                if ($report == 'srpwd') {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center"> </th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Time Range</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Transactions</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Sales</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Discount Amount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Export</th>
                                                    ';
                                                }
                                            } elseif ($category == 'expenses') {
                                                if ($report == 'summary') {
                                                    // SIMPLIFIED HEADERS FOR EXPENSES SUMMARY
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Amount</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Items</th>
                                                    ';
                                                } else {
                                                    // For other expense reports (list, unpaid, cos, ooe) - keep existing detailed headers
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Classification</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Item Name</th>
                                                    ';
                                                    
                                                    if ($report == 'cash') {
                                                        echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Unit Price</th>';
                                                    }
                                                    
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Quantity</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Amount</th>
                                                    ';
                                                    
                                                    if ($report == 'cash') {
                                                        echo '
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Category</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Supplier</th>
                                                        ';
                                                    }
                                                    
                                                    
                                                    
                                                    if ($report == 'unpaid') {
                                                        echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Due</th>';
                                                    }
                                                    
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Status</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Cashier Name</th>
                                                    ';
                                                    
                                                    if ($report == 'unpaid' || ($report != 'unpaid' && Auth::user()->role == 1)) {
                                                        echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Action</th>';
                                                    }
                                                }

                                            } elseif ($category == 'waste') {
                                                echo '
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Name</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Quantity</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Cost</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Remarks</th>
                                                ';
                                            } elseif ($category == 'inventory') {
                                                if ($report == 'logs') {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Item Name</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Quantity Before</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Quantity</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Quantity After</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Remarks</th>
                                                    ';
                                                } else {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Item Name</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Category</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Quantity</th>
                                                    ';
                                                }
                                            } elseif ($category == 'menu') {
                                                if ($report == 'rank') {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">#</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Menu</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Total Served</th>
                                                    ';
                                                } else {
                                                    echo '
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Menu</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Category</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Current Stock</th>
                                                    ';
                                                }
                                            } elseif ($category == 'invmenu' && $report == 'summary') {
                                                echo '
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Menu</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Category</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Current Stock</th>
                                                ';

                                             
                                            } elseif ($category == 'dailySR') {
                                                if (!isset($dailySRPaymentColumns) || !is_array($dailySRPaymentColumns) || empty($dailySRPaymentColumns)) {
                                                    $dailySRPaymentColumns = ['CASH'];
                                                }

                                                echo '
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">#</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">REF</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">CUSTOMER</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">AMOUNT</th>
                                                ';

                                                foreach ($dailySRPaymentColumns as $paymentColumn) {
                                                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">' . e($paymentColumn) . '</th>';
                                                }

                                                echo '
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">AR</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">FORM 2307</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">REMARKS</th>
                                                ';
                                            


                                            } elseif ($category == 'remit') {
                                                echo '
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Payment Method</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Amount</th>
                                                ';
                                            }
                                        }
                                    @endphp
                                </tr>
                            </thead>
                            <tbody id="reportTableBody" class="bg-white divide-y divide-gray-200">
                                @php
                                    $x = 1;
                                    $total = 0;
                                    
                                    $grandTotalTransactions = 0;
                                    $grandTotalSales = 0;
                                    $grandTotalDiscount = 0;
                                        
                                    // Prepare arrays for chart data
                                    $timeLabels = [];
                                    $salesData = [];
                                    $transactionsData = [];
                                    $discountData = [];
                                    $dateTimeLabels = [];
                                    $uniqueDates = [];
                                @endphp
                                
                                {{-- SPECIAL HANDLING FOR SALES SUMMARY REPORT --}}
                                @if(isset($category) && $category == 'sales' && ($report == 'summary' || $report == 'zreading'))
                                    @php
                                        // Group results by date
                                        $groupedResults = [];
                                        $grandTotals = [
                                            'total' => 0, 'discount' => 0, 'pwd' => 0, 'lessVat' => 0,
                                            'vatSales' => 0, 'vat' => 0, 'vatExempt' => 0,
                                            // Payment method totals for zreading
                                            'tcash' => 0,
                                            'tgcash' => 0,
                                            'tgcard' => 0,
                                            'tbancnet' => 0,
                                            'tvisa' => 0,
                                            'tjcb' => 0,
                                            'tdiners' => 0,
                                            'tamex' => 0,   
                                            'tpaymaya' => 0,
                                            'tfoodpanda' => 0,
                                            'tgrabfood' => 0,
                                            'tunionpay' => 0,
                                            'tgrabpay' => 0,
                                            'tbdo' => 0,
                                            'twechat' => 0,
                                            'tqrph' => 0,
                                            'downpayment' => 0,
                                            'giftcert' => 0,                                                                                                    
                                            'sc' => 0,
                                            'net' => 0,
                                            'served_total' => 0, 'served_discount' => 0, 'served_pwd' => 0, 'served_lessVat' => 0,
                                            'served_vatSales' => 0, 'served_vat' => 0, 'served_vatExempt' => 0, 'served_sc' => 0, 'served_net' => 0,
                                            'not_served_total' => 0, 'not_served_discount' => 0, 'not_served_pwd' => 0, 'not_served_lessVat' => 0,
                                            'not_served_vatSales' => 0, 'not_served_vat' => 0, 'not_served_vatExempt' => 0, 'not_served_sc' => 0, 'not_served_net' => 0
                                        ];
                                        
                                        foreach ($results as $result) {
                                            $date = date('Y-m-d', strtotime($result->date ?? $result->created_at));
                                            if (!isset($groupedResults[$date])) {
                                                $groupedResults[$date] = [
                                                    'total' => 0,
                                                    'discount' => 0,
                                                    'pwd' => 0,
                                                    'lessVat' => 0,
                                                    'vatSales' => 0,
                                                    'vat' => 0,
                                                    'vatExempt' => 0,
                                                    // Payment method totals for zreading
                                                    'tcash' => 0,
                                                    'tgcash' => 0,
                                                    'tgcard' => 0,
                                                    'tbancnet' => 0,
                                                    'tvisa' => 0,
                                                    'tjcb' => 0,
                                                    'tdiners' => 0,
                                                    'tamex' => 0,   
                                                    'tpaymaya' => 0,
                                                    'tfoodpanda' => 0,
                                                    'tgrabfood' => 0,
                                                    'tunionpay' => 0,
                                                    'tgrabpay' => 0,
                                                    'tbdo' => 0,
                                                    'twechat' => 0,
                                                    'tqrph' => 0,
                                                    'downpayment' => 0,
                                                    'giftcert' => 0,                                                                                                    
                                                    'sc' => 0,
                                                    'net' => 0,
                                                    'served_total' => 0,
                                                    'served_discount' => 0,
                                                    'served_pwd' => 0,
                                                    'served_lessVat' => 0,
                                                    'served_vatSales' => 0,
                                                    'served_vat' => 0,
                                                    'served_vatExempt' => 0,
                                                    'served_sc' => 0,
                                                    'served_net' => 0,
                                                    'not_served_total' => 0,
                                                    'not_served_discount' => 0,
                                                    'not_served_pwd' => 0,
                                                    'not_served_lessVat' => 0,
                                                    'not_served_vatSales' => 0,
                                                    'not_served_vat' => 0,
                                                    'not_served_vatExempt' => 0,
                                                    'not_served_sc' => 0,
                                                    'not_served_net' => 0
                                                ];
                                            }
                                            
                                            // Add to totals
                                            $groupedResults[$date]['total'] += $result->total ?? 0;
                                            $groupedResults[$date]['discount'] += $result->lime_discount ?? 0;
                                            $groupedResults[$date]['pwd'] += $result->pwd ?? 0;
                                            $groupedResults[$date]['vat'] += $result->vat ?? 0;
                                            $groupedResults[$date]['vatSales'] += $result->vatSales ?? 0;
                                            $groupedResults[$date]['vatExempt'] += $result->vatExemptSales ?? 0;
                                            $groupedResults[$date]['lessVat'] += $result->lessVatDeduction ?? 0;
                                            $groupedResults[$date]['sc'] += $result->service_charge ?? 0;
                                            
                                            // Add payment method totals for zreading
                                            if ($report == 'zreading') {
                                                $groupedResults[$date]['tcash'] += ($result->tcash ?? 0) + ($result->Mtcashtotal ?? 0);
                                                $groupedResults[$date]['tgcash'] += ($result->tgcash ?? 0) + ($result->Mtgcash ?? 0);
                                                $groupedResults[$date]['tgcard'] += ($result->tccard ?? 0) + ($result->Mtccard ?? 0);
                                                $groupedResults[$date]['tbancnet'] += ($result->tbcard ?? 0) + ($result->Mtbcard ?? 0);
                                                $groupedResults[$date]['tvisa'] += ($result->tvisa ?? 0) + ($result->Mtvisa ?? 0);
                                                $groupedResults[$date]['tjcb'] += ($result->tjcb ?? 0) + ($result->Mtjcb ?? 0);
                                                $groupedResults[$date]['tdiners'] += ($result->tdiners ?? 0) + ($result->Mtdiners ?? 0);
                                                $groupedResults[$date]['tamex'] += ($result->tamex ?? 0) + ($result->Mtamex ?? 0);
                                                $groupedResults[$date]['tpaymaya'] += ($result->tpaymaya ?? 0) + ($result->Mtpaymaya ?? 0);
                                                $groupedResults[$date]['tfoodpanda'] += ($result->tpanda ?? 0) + ($result->Mtpanda ?? 0);
                                                $groupedResults[$date]['tgrabfood'] += ($result->tgrab ?? 0) + ($result->Mtgrab ?? 0);
                                                $groupedResults[$date]['tunionpay'] += ($result->tunionpay ?? 0) + ($result->Mtunionpay ?? 0);
                                                $groupedResults[$date]['tgrabpay'] += ($result->tgrabpay ?? 0) + ($result->Mtgrabpay ?? 0);
                                                $groupedResults[$date]['tbdo'] += ($result->tbdopay ?? 0) + ($result->Mtbdopay ?? 0);
                                                $groupedResults[$date]['twechat'] += ($result->twcp ?? 0) + ($result->Mtwcp ?? 0);
                                                $groupedResults[$date]['tqrph'] += ($result->tqrph ?? 0) + ($result->Mtqrph ?? 0);
                                                $groupedResults[$date]['downpayment'] += ($result->tdownpayment ?? 0) + ($result->Mtdownpayment ?? 0);
                                                $groupedResults[$date]['giftcert'] += ($result->tgiftcert ?? 0) + ($result->Mtgiftcert ?? 0);
                                            }
                                            
                                            $groupedResults[$date]['net'] = $groupedResults[$date]['total'] - ($groupedResults[$date]['sc'] + $groupedResults[$date]['vat']);
                                            
                                            // Check if served/audited
                                            $isServed = isset($result->is_audit) ? $result->is_audit : 
                                                    (isset($result->order_status) && $result->order_status == 'SERVED');
                                            
                                            if ($isServed) {
                                                $groupedResults[$date]['served_total'] += $result->total ?? 0;
                                                $groupedResults[$date]['served_discount'] += $result->lime_discount ?? 0;
                                                $groupedResults[$date]['served_pwd'] += $result->pwd ?? 0;
                                                $groupedResults[$date]['served_vat'] += $result->vat ?? 0;
                                                $groupedResults[$date]['served_vatSales'] += $result->vatSales ?? 0;
                                                $groupedResults[$date]['served_vatExempt'] += $result->vatExemptSales ?? 0;
                                                $groupedResults[$date]['served_lessVat'] += $result->lessVatDeduction ?? 0;
                                                $groupedResults[$date]['served_sc'] += $result->service_charge ?? 0;
                                                $groupedResults[$date]['served_net'] = $groupedResults[$date]['served_total'] - ($groupedResults[$date]['served_sc'] + $groupedResults[$date]['served_vat']);
                                            } else {
                                                $groupedResults[$date]['not_served_total'] += $result->total ?? 0;
                                                $groupedResults[$date]['not_served_discount'] += $result->lime_discount ?? 0;
                                                $groupedResults[$date]['not_served_pwd'] += $result->pwd ?? 0;
                                                $groupedResults[$date]['not_served_vat'] += $result->vat ?? 0;
                                                $groupedResults[$date]['not_served_vatSales'] += $result->vatSales ?? 0;
                                                $groupedResults[$date]['not_served_vatExempt'] += $result->vatExemptSales ?? 0;
                                                $groupedResults[$date]['not_served_lessVat'] += $result->lessVatDeduction ?? 0;
                                                $groupedResults[$date]['not_served_sc'] += $result->service_charge ?? 0;
                                                $groupedResults[$date]['not_served_net'] = $groupedResults[$date]['not_served_total'] - ($groupedResults[$date]['not_served_sc'] + $groupedResults[$date]['not_served_vat']);
                                            }
                                        }
                                        
                                        // Sort dates
                                        ksort($groupedResults);
                                    @endphp
                                                    
                                    {{-- Display grouped results --}}
                                    @foreach($groupedResults as $date => $totals)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ date('M d, Y', strtotime($date)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['total'], 2, '.', ',') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['discount'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['pwd'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['lessVat'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['vatSales'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['vat'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['vatExempt'], 2) }}
                                            </td>
                                            
                                            @if($report == 'zreading')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tcash'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tgcash'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tgcard'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tbancnet'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tvisa'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tjcb'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tdiners'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tamex'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tpaymaya'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tfoodpanda'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tgrabfood'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tunionpay'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tgrabpay'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tbdo'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['twechat'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['tqrph'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['downpayment'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($totals['giftcert'], 2) }}
                                                </td>
                                            @endif
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['sc'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ₱ {{ number_format($totals['net'], 2) }}
                                            </td>
                                            @if($category == 'sales' && $report == 'summary')
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <a href="{{ url('/report/print/'.$date.'/'.$date.'/'.$category.'/'.$report) }}" target="_blank" class="text-blue-500 hover:underline">Print</a>
                                            </td>
                                            @endif
                                        </tr>
                                        
                                        {{-- Accumulate grand totals --}}
                                        @php
                                            foreach ($totals as $key => $value) {
                                                if (isset($grandTotals[$key])) {
                                                    $grandTotals[$key] += $value;
                                                }
                                            }
                                        @endphp
                                    @endforeach
                                    
                                {{-- HOURLY SALES REPORT --}}
                                @elseif(isset($category) && $category == 'sales' && $report == 'hoursale')
                                    @if(isset($hourlySummary) && $hourlySummary->count() > 0)
                                        @foreach ($hourlySummary as $summary)
                                            @php
                                                $date = \Carbon\Carbon::parse($summary->date)->format('M j, Y');
                                                $dateKey = \Carbon\Carbon::parse($summary->date)->format('Y-m-d');
                                                
                                                // Convert to military time (24-hour format)
                                                $startHour = str_pad($summary->hour, 2, '0', STR_PAD_LEFT);
                                                $endHour = str_pad($summary->hour + 1, 2, '0', STR_PAD_LEFT);
                                                
                                                $timeRange = "{$startHour}:00 - {$endHour}:00";
                                                
                                                // Create combined label for chart
                                                $combinedLabel = $date . ' ' . $timeRange;
                                                
                                                // Store data for chart
                                                $timeLabels[] = $timeRange;
                                                $dateTimeLabels[] = $combinedLabel;
                                                $salesData[] = $summary->hourly_sales;
                                                $transactionsData[] = $summary->hourly_transactions;
                                                $discountData[] = $summary->hourly_discount;
                                                
                                                // Collect unique dates
                                                if (!in_array($date, $uniqueDates)) {
                                                    $uniqueDates[] = $date;
                                                }
                                                
                                                $grandTotalTransactions += $summary->hourly_transactions;
                                                $grandTotalSales += $summary->hourly_sales;
                                                $grandTotalDiscount += $summary->hourly_discount;
                                            @endphp
                                            
                                            <tr class="border-b hover:bg-gray-50 cursor-pointer view-hourly-details" 
                                                data-date="{{ $summary->date }}"
                                                data-hour="{{ $summary->hour }}"
                                                data-transactions="{{ $summary->hourly_transactions }}"
                                                data-sales="{{ $summary->hourly_sales }}"
                                                data-discount="{{ $summary->hourly_discount }}">
                                                <td class="px-6 py-3 text-center text-gray-900 whitespace-nowrap font-semibold" 
                                                    data-value="{{ $combinedLabel }}">
                                                    {{ $date }}
                                                </td>
                                                <td class="px-6 py-3 text-center text-gray-900 whitespace-nowrap font-medium" 
                                                    data-value="{{ $timeRange }}">
                                                    {{ $timeRange }}
                                                </td>
                                                <td class="px-6 py-3 text-center text-gray-900 whitespace-nowrap" 
                                                    data-value="{{ $summary->hourly_transactions }}">
                                                    {{ $summary->hourly_transactions }}
                                                </td>
                                                <td class="px-6 py-3 text-center text-gray-900 whitespace-nowrap" 
                                                    data-value="{{ $summary->hourly_sales }}">
                                                    ₱ {{ number_format($summary->hourly_sales, 2) }}
                                                </td>
                                                <td class="px-6 py-3 text-center text-gray-900 whitespace-nowrap" 
                                                    data-value="{{ $summary->hourly_discount }}">
                                                    ₱ {{ number_format($summary->hourly_discount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                                No hourly sales data found for the selected period.
                                            </td>
                                        </tr>
                                    @endif
                                    
                                {{-- ALL OTHER REPORTS --}}
                                @elseif(isset($category) && $category == 'sales' && $report == 'discount')
                                            @foreach ($results as $result)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ date('M d, Y', strtotime($result->date)) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $result->lime_discount }}%
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center" 
                                                        data-value="{{ $result->transaction_count }}">
                                                        {{ $result->transaction_count }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center" 
                                                        data-value="{{ $result->ld_value }}">
                                                        ₱ {{ number_format($result->ld_value, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach

                               <!--  ken start added here  -->            
                                @elseif(isset($category) && $category == 'dailySR')
                                    @php
                                        if (!isset($dailySRPaymentColumns) || !is_array($dailySRPaymentColumns) || empty($dailySRPaymentColumns)) {
                                            $dailySRPaymentColumns = ['CASH'];
                                        }
                                    @endphp

                                    @foreach ($dailySRRows as $row)
                                        @continue($row->status === 'UNPAID' && $row->order_status === 'CANCELLED')
                                        <tr class="border-b {{ $row->highlight_ar_in_sales ? 'bg-red-200 hover:bg-red-100' : 'hover:bg-gray-50' }}" data-report-type="{{ $row->report_type }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $x++ }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $row->number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $row->customer_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">₱ {{ number_format($row->row_amount, 2, '.', ',') }}</td>
                                            @foreach($dailySRPaymentColumns as $paymentColumn)
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($row->payments[$paymentColumn] ?? 0, 2, '.', ',') }}
                                                </td>
                                            @endforeach
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">₱ {{ number_format($row->ar_display_amount, 2, '.', ',') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $row->form2307 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $row->remarks }}</td>
                                        </tr>
                                    @endforeach
                                    <!-- ken end added here -->

                                @else
                                    @foreach ($results as $result)
                                        @php
                                            if(isset($category) && $category == 'waste'){
                                                $total = $total +  (isset($result->cost) ? $result->cost : 0);
                                            }
                                        @endphp
                                        <tr class="border-b hover:bg-gray-50">
                                            
                                            {{-- SALES TRANSACTION LISTS --}}
                                            @if($category == 'sales' && in_array($report, ['server', 'served_list', 'notServed_list', 'list']))
                                                @php
                                                    $username = $result->thisCashier->name ?? '';
                                                @endphp
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ date('M d, Y h:i A', strtotime($result->created_at)) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $result->number }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ number_format($result->total, 2, '.', ',') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $result->mode_of_payment }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $result->type }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ ($result->order_status ?? '') == 'CANCELLED' ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $result->order_status ?? '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $username }}  cashier
                                                </td>
                                                
                                                @if(Auth::user()->role == 1)
                                                    <td class="px-6 py-1 font-bold text-center whitespace-nowrap">
                                                        <button data-id="{{ $result->id }}" data-name="{{ $result->number }}" data-amount="{{ $result->total }}" 
                                                                data-date="{{ $result->created_at }}" data-category="{{ $category }}" 
                                                                data-mop="{{ $result->mode_of_payment }}" data-modal-show="editModal" 
                                                                data-modal-target="editModal" class="text-blue-500 actionButton hover:underline">
                                                            Edit
                                                        </button>
                                                        <span class="px-2">|</span>
                                                        <button data-id="{{ $result->id }}" data-category="{{ $category }}" 
                                                                data-modal-show="deleteModal" data-modal-target="deleteModal" 
                                                                class="text-red-500 actionButton hover:underline">
                                                            Delete
                                                        </button>
                                                    </td>
                                                @endif
                                            {{-- EXPENSES --}}
                                            @elseif($category == 'expenses')
                                                @if($report == 'summary')
                                                    {{-- SIMPLIFIED SUMMARY VIEW - Already grouped by date from controller --}}
                                                    @php
                                                        $dateToUse = $result->date ?? '';
                                                        // Convert date string to proper format
                                                        if ($dateToUse && strtotime($dateToUse)) {
                                                            $formattedDate = date('M d, Y', strtotime($dateToUse));
                                                        } else {
                                                            $formattedDate = 'Invalid Date';
                                                        }
                                                        $totalAmount = isset($result->total_amount) && is_numeric($result->total_amount) ? floatval($result->total_amount) : 0;
                                                        $totalItems = isset($result->total_items) && is_numeric($result->total_items) ? floatval($result->total_items) : 0;
                                                    @endphp
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $formattedDate }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        ₱ {{ number_format($totalAmount, 2, '.', ',') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ number_format($totalItems, 0) }}
                                                    </td>
                                                @else
                                                    {{-- DETAILED VIEW FOR OTHER EXPENSE REPORTS --}}
                                                    @php
                                                        $dateToUse = $result->date ?? $result->created_at ?? '';
                                                        $formattedDate = $dateToUse ? date('M d, Y h:i A', strtotime($dateToUse)) : '';
                                                        $iname = '';
                                                        
                                                        if(isset($result->inv_id) && $result->inv_id != 0 && isset($result->inv->name)){
                                                            $iname = $result->inv->name;
                                                        }elseif(isset($result->remarks)){
                                                            $iname = $result->remarks;
                                                        }
                                                        
                                                        $expType = isset($result->exp_type) && $result->exp_type == 1 ? 'Cost of Sales' : 'Other Operating Expenses';
                                                        $ip = isset($result->payment_class) && $result->payment_class == 1 ? 'PAID' : 'UNPAID';
                                                    @endphp
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $formattedDate }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $expType }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $iname }}
                                                    </td>
                                                    
                                                    @if($report == 'cash')
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                            {{ isset($result->unit_price) && is_numeric($result->unit_price) ? number_format(floatval($result->unit_price), 2) : '' }}
                                                        </td>
                                                    @endif
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ isset($result->quantity) && is_numeric($result->quantity) ? number_format(floatval($result->quantity), 2) : '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        ₱ {{ isset($result->amount) && is_numeric($result->amount) ? number_format(floatval($result->amount), 2, '.', ',') : '' }}
                                                    </td>
                                                    
                                                    @if($report == 'cash')
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                            {{ $result->ExpensesCategory->name ?? '' }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                            {{ $result->supplier->name ?? '' }}
                                                        </td>
                                                    @endif
                                                    
                                                    {{--<td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ ($result->is_paid ?? '') == 'cash' ? 'text-green-500' : 'text-red-500' }}">
                                                        {{ $result->is_paid ?? '' }}
                                                    </td>--}}
                                                    
                                                    @if($report == 'unpaid')
                                                        @php
                                                            $dueDate = '';
                                                            $isOverdue = false;
                                                            $createdDate = $result->date ?? $result->created_at ?? '';
                                                            
                                                            if($createdDate && isset($result->is_paid)){
                                                                switch($result->is_paid){
                                                                    case '7days':
                                                                        $dueDate = date('M d, Y h:i A', strtotime($createdDate . ' +7 days'));
                                                                        $isOverdue = strtotime($createdDate . ' +7 days') < time();
                                                                        break;
                                                                    case '15days':
                                                                        $dueDate = date('M d, Y h:i A', strtotime($createdDate . ' +15 days'));
                                                                        $isOverdue = strtotime($createdDate . ' +15 days') < time();
                                                                        break;
                                                                    case '30days':
                                                                        $dueDate = date('M d, Y h:i A', strtotime($createdDate . ' +30 days'));
                                                                        $isOverdue = strtotime($createdDate . ' +30 days') < time();
                                                                        break;
                                                                }
                                                            }
                                                        @endphp
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center {{ $isOverdue ? 'text-red-500' : 'text-black' }}">
                                                            {{ $dueDate }}
                                                        </td>
                                                    @endif
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $ip }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $report == 'summary' ? ($result->cashier ?? '') : ($result->user->name ?? '') }}
                                                    </td>
                                                    
                                                    @if(Auth::user()->role == 1)
                                                        @if($report != 'unpaid')
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center">
                                                                <button data-id="{{ $result->id }}" data-category="{{ $category }}" 
                                                                        data-modal-show="deleteModal" data-modal-target="deleteModal" 
                                                                        class="text-red-500 actionButton hover:underline">
                                                                    Delete
                                                                </button>
                                                            </td>
                                                        @else
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center">
                                                                <button data-id="{{ $result->id }}" data-category="{{ $category }}" 
                                                                        data-modal-show="payModal1" data-modal-target="payModal1" 
                                                                        class="text-green-500 actionButton hover:underline">
                                                                    Pay
                                                                </button>
                                                            </td>
                                                        @endif
                                                    @endif
                                                @endif
                                            {{-- INVENTORY --}}
                                            @elseif($category == 'inventory')
                                                @if($report == 'logs')
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ date('M d, Y h:i A', strtotime($result->created_at)) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $result->inv->name ?? '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ isset($result->quantity_before) ? round($result->quantity_before, 2) : '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ isset($result->quantity) ? round($result->quantity, 2) : '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ isset($result->quantity_after) ? round($result->quantity_after, 2) : '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $result->remarks ?? '' }}
                                                    </td>
                                                @else
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $result->name ?? '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $result->category->name ?? '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ isset($result->quantity) ? round($result->quantity, 2) : '' }}
                                                    </td>
                                                @endif

                                            {{-- MENU --}}
                                            @elseif($category == 'menu')
                                                @if($report == 'rank')
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                                                        {{ $x++ }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $result->name ?? '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ isset($result->quantity) ? round($result->quantity, 0) : '' }}
                                                    </td>
                                                @else
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $result->name ?? '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ $result->category->name ?? '' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                        {{ isset($result->quantity) ? round($result->quantity, 0) : '' }}
                                                    </td>
                                                @endif

                                            {{-- INVENTORY MENU SUMMARY --}}
                                            @elseif($category == 'invmenu' && $report == 'summary')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $result->name ?? '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $result->category ?? '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ isset($result->quantity) ? round($result->quantity, 0) : '' }}
                                                </td>

                                            {{-- WASTE --}}
                                            @elseif($category == 'waste')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center">
                                                    {{ date('M d, Y h:i A', strtotime($result->created_at)) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $result->name ?? '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $result->quantity ?? '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    ₱ {{ isset($result->cost) ? number_format($result->cost, 2, '.', ',') : '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                    {{ $result->waste_remarks ?? '' }}
                                                </td>

                                            {{-- REMITTANCE --}}
                                            @elseif($category == 'remit')
                                                @php
                                                    $isTotal = isset($result->name) && $result->name == 'Total';
                                                    $cellClass = $isTotal ? 'text-lg font-bold text-gray-600' : '';
                                                @endphp
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $cellClass }}">
                                                    {{ isset($result->name) ? strtoupper($result->name) : '' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $cellClass }}">
                                                    ₱ {{ isset($result->amount) ? number_format($result->amount, 2, '.', ',') : '' }}
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>

                            @if(isset($category) && $category == 'dailySR')
                                <tfoot>
                                    <tr id="dailySRSalesFooter" class="bg-gray-100 font-semibold">
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center" colspan="3">TOTAL</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center">₱ {{ number_format($dailyTotals['amount'], 2, '.', ',') }}</td>  <!--  dito mo lang ppalitan -->
                                        @foreach($dailySRPaymentColumns as $paymentColumn)
                                            <td class="px-6 py-4 text-sm text-gray-900 text-center">₱ {{ number_format($dailyTotals['payments'][$paymentColumn] ?? 0, 2, '.', ',') }}</td>
                                        @endforeach
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center">₱ {{ number_format($dailyTotals['ar'], 2, '.', ',') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center" colspan="2"></td>
                                    </tr>
                                    <tr id="dailySRARFooter" class="bg-gray-100 font-semibold hidden">
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center" colspan="3">AR TOTAL</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center">₱ {{ number_format($arTotals['amount'], 2, '.', ',') }}</td>
                                        @foreach($dailySRPaymentColumns as $paymentColumn)
                                            <td class="px-6 py-4 text-sm text-gray-900 text-center">₱ {{ number_format($arTotals['payments'][$paymentColumn] ?? 0, 2, '.', ',') }}</td>
                                        @endforeach
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center">₱ {{ number_format($arTotals['ar'], 2, '.', ',') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center" colspan="2"></td>
                                    </tr>
                                </tfoot>
                            @endif
                            
                            {{-- FOOTER FOR WASTE REPORT --}}
                            @if(isset($category) && $category == 'waste')
                                <tfoot>
                                    <tr class="bg-gray-50">
                                        <td colspan="3" class="px-6 py-4 text-sm font-medium text-right text-gray-900">Total Cost</td>
                                        <td class="px-6 py-4 text-sm font-bold text-center text-gray-900">₱ {{ number_format($total, 2, '.', ',') }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                            
                            {{-- FOOTER FOR SALES SUMMARY GRAND TOTALS --}}
                            @if(isset($grandTotals) && $category == 'sales' && $report == 'summary')
                                <thead class="bg-gray-50 font-bold">
                                    <tr>
                                        <td colspan="10" class="px-6 py-4 text-right text-sm">
                                            <div class="grid grid-cols-6 gap-4">
                                                <div class="text-center">
                                                    <div class="text-xs text-gray-500">Grand Total</div>
                                                    <div class="text-green-600">₱ {{ number_format($grandTotals['total'], 2) }}</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-xs text-gray-500">Net Total</div>
                                                    <div class="text-blue-600">₱ {{ number_format($grandTotals['net'], 2) }}</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-xs text-gray-500">VAT Total</div>
                                                    <div class="text-purple-600">₱ {{ number_format($grandTotals['vat'], 2) }}</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-xs text-gray-500">Service Charge</div>
                                                    <div class="text-orange-600">₱ {{ number_format($grandTotals['sc'], 2) }}</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-xs text-gray-500">Served Total</div>
                                                    <div class="text-green-600">₱ {{ number_format($grandTotals['served_total'], 2) }}</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-xs text-gray-500">Not Served Total</div>
                                                    <div class="text-green-600">₱ {{ number_format($grandTotals['not_served_total'], 2) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </thead>
                            @endif

                            {{-- Add this after your table --}}
                            @if($category == 'expenses' && $report == 'summary')
                                <tfoot>
                                    <tr class="bg-gray-50 font-bold">

                                        @if(isset($result->transaction_count))
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <strong>GRAND TOTAL</strong>
                                            </td>
                                        @endif                                        

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                            <strong>₱ {{ number_format($grandTotals['total_amount'] ?? 0, 2, '.', ',') }}</strong>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                            <strong>{{ number_format($grandTotals['total_items'] ?? 0, 0) }}</strong>
                                        </td>

                                    </tr>
                                </tfoot>
                            @endif                            
                        </table>
                    </div>
                </div>
                <!-- KEN STARTED ADDING HERE YOUR DAILY SR PAYMENT DETAILS 4-22-2026 -->
                @if(isset($category) && $category == 'dailySR')
                    {{-- ONLINE PAYMENTS TABLE - COMBINED FROM SALES + AR + PAYMENT_LOGS --}}
                    <div id="onlinePaymentsTableContainer" class="bg-white shadow-md rounded-lg overflow-hidden mb-6 hidden">
                        <div class="overflow-x-auto">
                            <table id="onlinePaymentsTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">#</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Ref No</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Payment Method</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Client Name</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php $onlineIndex = 1; $onlineTotalAmount = 0; @endphp
                                    @foreach($onlinePaymentLogs ?? [] as $log)
                                        @php $onlineTotalAmount += (float) $log->row_amount; @endphp
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $onlineIndex++ }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $log->display_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $log->payment_method }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $log->customer_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ $log->payment_date ? date('M d, Y h:i A', strtotime($log->payment_date)) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">₱ {{ number_format($log->row_amount, 2, '.', ',') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center" colspan="5">TOTAL</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">₱ {{ number_format($onlineTotalAmount ?? 0, 2, '.', ',') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- CHECK PAYMENTS TABLE - COMBINED FROM SALES + AR + PAYMENT_LOGS --}}
                    <div id="checkPaymentsTableContainer" class="bg-white shadow-md rounded-lg overflow-hidden mb-6 hidden">
                        <div class="overflow-x-auto">
                            <table id="checkPaymentsTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">#</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Cheque No</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Bank Name</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Client Name</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Date</th>
                                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php $checkIndex = 1; $checkTotalAmount = 0; @endphp
                                    @foreach($checkPaymentLogs ?? [] as $log)
                                        @php $checkTotalAmount += (float) $log->row_amount; @endphp
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $checkIndex++ }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $log->display_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $log->payment_method }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $log->customer_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                {{ $log->payment_date ? date('M d, Y h:i A', strtotime($log->payment_date)) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">₱ {{ number_format($log->row_amount, 2, '.', ',') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center" colspan="5">TOTAL</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">₱ {{ number_format($checkTotalAmount ?? 0, 2, '.', ',') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif
                <!-- end of adding here -->
            @endif
        
        <div id="paginationControls" class="bg-white px-6 py-4 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-2 md:mb-0">
                    <span id="pageInfo" class="text-sm text-gray-700">
                        @if(isset($resultsCount) && $resultsCount > 0)
                            Showing 1 to {{ min(10, $resultsCount) }} of {{ $resultsCount }} entries
                        @elseif(isset($results) && count($results) > 0)
                            Showing 1 to {{ min(10, count($results)) }} of {{ count($results) }} entries
                        @endif
                    </span>
                </div>
                 {{-- Add Table Controls --}}
                <div class="bg-white shadow-md rounded-lg p-4 mb-4">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <label class="mr-2 text-sm font-medium text-gray-700">Show:</label>
                                <select id="rowsPerPage" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2">
                                    <option value="10">10 rows</option>
                                    <option value="20">20 rows</option>
                                    <option value="30">30 rows</option>
                                    <option value="40">40 rows</option>
                                    <option value="50">50 rows</option>
                                    <option value="100">100 rows</option>
                                    <option value="0">All rows</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>   
                <div class="flex items-center space-x-2">
                    <button id="firstPage" class="px-3 py-1 text-sm border rounded disabled:opacity-50" disabled>First</button>
                    <button id="prevPage" class="px-3 py-1 text-sm border rounded disabled:opacity-50" disabled>Previous</button>
                    <div class="flex items-center space-x-2">
                        <input id="pageNumberInput" type="number" min="1" value="1" class="w-16 px-3 py-1 text-sm border rounded text-center" />
                        <span id="pageNumberTotal" class="text-sm text-gray-700">/ 1</span>
                    </div>
                    <button id="nextPage" class="px-3 py-1 text-sm border rounded disabled:opacity-50" disabled>Next</button>
                    <button id="lastPage" class="px-3 py-1 text-sm border rounded disabled:opacity-50" disabled>Last</button>
                </div>
            </div>
        </div>     
        </div>   
    </div>
    {{-- HOURLY DETAILS MODAL --}}
    <div id="hourlyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-900" id="modalDateTitle">Hourly Details</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="uil uil-times text-2xl"></i>
                    </button>
                </div>
                
                <div class="mb-4 p-3 bg-blue-50 rounded">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm text-gray-600">Total Transactions</p>
                            <p class="text-lg font-bold" id="modalTotalTransactions">0</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Sales</p>
                            <p class="text-lg font-bold" id="modalTotalSales">₱ 0.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Discount</p>
                            <p class="text-lg font-bold" id="modalTotalDiscount">₱ 0.00</p>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border border-gray-200 rounded-lg">
                    <div class="overflow-auto" style="max-height: 400px;">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-center">Time</th>
                                    <th class="px-6 py-3 text-center">Transaction ID</th>
                                    <th class="px-6 py-3 text-center">Total Sales</th>
                                    <th class="px-6 py-3 text-center">Discount Amount</th>
                                </tr>
                            </thead>
                            <tbody id="hourlyDetailsBody">
                                <!-- Hourly details will be populated here -->
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No transactions found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-2 text-sm text-gray-500 text-center" id="transactionCount">
                    <!-- Transaction count will be displayed here -->
                </div>
                
                <div class="flex justify-end mt-4">
                    <button onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        var currentDailySRTab = 'sales';
        var allData = [];
        var filteredData = [];
        var onlineData = [];
        var checkData = [];
        $(document).ready(function() {
            // ==============================
            // INITIALIZATION & STATE MANAGEMENT
            // ==============================
            
            // Initialize variables
            let currentPage = 1;
            let rowsPerPage = 10;
            allData = [];
            filteredData = [];
            onlineData = [];
            checkData = [];
            let totalPages = 0;
            let currentChart = null;
            let chartType = 'bar';
            
            @if(isset($category) && $category == 'sales' && $report == 'zreading')
                // Z-Reading Charts Management
                let summaryChart = null;
                let paymentChart = null;
                let deductionsChart = null;
                let currentZReadingGraphType = 'summary'; // Track current graph type
            @endif
            
            // Get current values from server
            var currentCategory = @json($category ?? '');
            var currentReport = @json($report ?? '');
            var currentStartDate = @json(isset($startDate) ? date('Y-m-d\TH:i', strtotime($startDate)) : '');
            var currentEndDate = @json(isset($endDate) ? date('Y-m-d\TH:i', strtotime($endDate)) : '');
            var currentBranch = @json($selectedBranch ?? '');
            
            // ==============================
            // NAVIGATION TOGGLE
            // ==============================
            
            $('#navButton').click(function(){
                $('#topNav').addClass('absolute');
                $('#topNav').removeClass('sticky');
                $('#contentDiv').addClass('pt-14');
            });

            $(document).mouseup(function(e) {
                var container = $(".navDiv");

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $('#topNav').removeClass('absolute');
                    $('#topNav').addClass('sticky');
                    $('#contentDiv').removeClass('pt-14');
                }
            });

            // ==============================
            // FORM INITIALIZATION
            // ==============================
            
            // Function to populate report options based on category
            function populateReportOptions(category, selectedReport) {
                var reportsHtml = '';
                
                if(category != 'both'){
                    if(category == 'expenses'){
                        reportsHtml = `
                            <option value="list" ${selectedReport == 'list' ? 'selected' : ''}>Transaction Logs</option>
                            <option value="summary" ${selectedReport == 'summary' ? 'selected' : ''}>Summary</option>
                            <option value="unpaid" ${selectedReport == 'unpaid' ? 'selected' : ''}>Accounts Payable</option>
                            <option value="cos" ${selectedReport == 'cos' ? 'selected' : ''}>Cost of Sales</option>
                            <option value="ooe" ${selectedReport == 'ooe' ? 'selected' : ''}>Operating Expenses</option>
                        `;
                    }else if(category == 'sales'){
                        reportsHtml = `
                            <option value="list" ${selectedReport == 'list' ? 'selected' : ''}>Transaction Logs</option>
                            <option value="summary" ${selectedReport == 'summary' ? 'selected' : ''}>Summary</option>
                            <option value="served_list" ${selectedReport == 'served_list' ? 'selected' : ''}>Served Sales</option>
                            <option value="notServed_list" ${selectedReport == 'notServed_list' ? 'selected' : ''}>Not Served Sales</option>
                            <option value="hoursale" ${selectedReport == 'hoursale' ? 'selected' : ''}>Hourly Sales</option>
                            <option value="compli" ${selectedReport == 'compli' ? 'selected' : ''}>Complimentary</option>
                            <option value="compli_break" ${selectedReport == 'compli_break' ? 'selected' : ''}>Complimentary Breakdown</option>
                            <option value="beverages" ${selectedReport == 'beverages' ? 'selected' : ''}>Beverages</option>
                            <option value="food" ${selectedReport == 'food' ? 'selected' : ''}>Food</option>
                            <option value="server" ${selectedReport == 'server' ? 'selected' : ''}>Server</option>
                            <option value="zreading" ${selectedReport == 'zreading' ? 'selected' : ''}>Z Reading</option>
                            <option value="pax" ${selectedReport == 'pax' ? 'selected' : ''}># of Pax</option>
                            <option value="discount" ${selectedReport == 'discount' ? 'selected' : ''}>Discount Report</option>
                            <option value="srpwd" ${selectedReport == 'srpwd' ? 'selected' : ''}>SR & PWD Report</option>
                        `;
                    }else if(category == 'dailySR'){
                        reportsHtml = `
                            <option value="dailySR" ${selectedReport == 'dailySR' ? 'selected' : ''}>Daily Sales Report</option>
                        `;
                    }else if(category == 'accounting'){
                        reportsHtml = `
                            <option value="balsheet" ${selectedReport == 'balsheet' ? 'selected' : ''}>Balance Sheet</option>
                            <option value="incomeState" ${selectedReport == 'incomeState' ? 'selected' : ''}>Income Statement</option>
                        `;
                                            
                    }else if(category == 'remit'){
                        reportsHtml = `
                            <option value="report" ${selectedReport == 'report' ? 'selected' : ''}>Report</option>
                        `;
                    }else if(category == 'inventory'){
                        reportsHtml = `
                            <option value="logs" ${selectedReport == 'logs' ? 'selected' : ''}>Logs</option>
                            <option value="stock" ${selectedReport == 'stock' ? 'selected' : ''}>Current Stocks</option>
                        `;
                    }else if(category == 'menu'){
                        reportsHtml = `
                            <option value="rank" ${selectedReport == 'rank' ? 'selected' : ''}>Rankings</option>
                            <option value="stock" ${selectedReport == 'stock' ? 'selected' : ''}>Current Stock</option>
                        `;
                    }else if(category == 'invmenu'){
                        reportsHtml = `
                            <option value="summary" ${selectedReport == 'summary' ? 'selected' : ''}>Summary Inventory</option>
                        `;
                    }else if(category == 'waste'){
                        reportsHtml = `
                            <option value="raw" ${selectedReport == 'raw' ? 'selected' : ''}>Raw Inventory</option>
                            <option value="menu" ${selectedReport == 'menu' ? 'selected' : ''}>Menu</option>
                        `;
                    }else{
                        reportsHtml = `
                            <option value="list" ${selectedReport == 'list' ? 'selected' : ''}>Transaction Logs</option>
                            <option value="summary" ${selectedReport == 'summary' ? 'selected' : ''}>Summary</option>
                        `;
                    }
                }else{
                    reportsHtml = `
                        <option value="summary" ${selectedReport == 'summary' ? 'selected' : ''}>Summary</option>
                    `;
                }
                
                return reportsHtml;
            }
            
            // Initialize form with saved values
            function initializeForm() {
                // Try to load from localStorage
                var savedStateRaw = localStorage.getItem('reportFormState');
                var savedState = null;
                
                // Check if we have server values (from a recent submission)
                var hasServerState = (currentCategory || currentReport || currentStartDate || currentEndDate || currentBranch);

                if (!hasServerState && savedStateRaw) {
                    try {
                        savedState = JSON.parse(savedStateRaw);
                    } catch (e) {
                        console.warn('Invalid reportFormState in localStorage; clearing it.', e);
                        localStorage.removeItem('reportFormState');
                        savedState = null;
                    }
                }

                var categoryToUse = currentCategory || ((savedState && savedState.category) ? savedState.category : 'sales');
                var reportToUse = currentReport || ((savedState && savedState.report) ? savedState.report : '');
                var startDateToUse = currentStartDate || ((savedState && savedState.start) ? savedState.start : '');
                var endDateToUse = currentEndDate || ((savedState && savedState.end) ? savedState.end : '');
                var branchToUse = currentBranch || ((savedState && savedState.branch) ? savedState.branch : 'all');
                
                // Set category and populate reports
                $('#category').val(categoryToUse);
                
                // Populate report options
                var reportsHtml = populateReportOptions(categoryToUse, reportToUse);
                $('#report').html(reportsHtml);
                
                // Set report value if it exists in the options
                if(reportToUse && $('#report option[value="' + reportToUse + '"]').length > 0) {
                    $('#report').val(reportToUse);
                }
                
                // Set other values
                if(startDateToUse) {
                    $('#dateStart').val(startDateToUse);
                } else {
                    // Set default start date (today 00:00)
                    var today = new Date();
                    var startOfDay = today.toISOString().slice(0, 10) + 'T00:00';
                    $('#dateStart').val(startOfDay);
                }
                
                if(endDateToUse) {
                    $('#dateEnd').val(endDateToUse);
                } else {
                    // Set default end date (today 23:59)
                    var today = new Date();
                    var endOfDay = today.toISOString().slice(0, 10) + 'T23:59';
                    $('#dateEnd').val(endOfDay);
                }
                
                if(branchToUse && $('#branch').length) {
                    $('#branch').val(branchToUse);
                }
                
                // Initialize branch visibility
                initializeBranchSelection();
                
                // Save initial state
                saveFormState();
            }
            
            // Initialize form on page load
            initializeForm();
            
            // ==============================
            // FORM STATE PERSISTENCE
            // ==============================
            
            // Save form state to localStorage
            function saveFormState() {
                var formState = {
                    category: $('#category').val(),
                    report: $('#report').val(),
                    start: $('#dateStart').val(),
                    end: $('#dateEnd').val(),
                    branch: $('#branch').val() || 'all'
                };
                localStorage.setItem('reportFormState', JSON.stringify(formState));
            }
            
            // Save form state when any form element changes
            $('#category, #report, #dateStart, #dateEnd, #branch').on('change', function() {
                saveFormState();
            });
            
            // Also save when generate button is clicked (before form submission)
            $('form button[type="submit"]').click(function() {
                saveFormState();
            });
            
            // ==============================
            // CATEGORY CHANGE HANDLER
            // ==============================
            
            $('#category').on('change', function(){
                var category = $(this).val();
                var currentSelectedReport = $('#report').val();
                
                // Populate report options based on selected category
                var reportsHtml = populateReportOptions(category, currentSelectedReport);
                $('#report').html(reportsHtml);
                
                // Save state after populating reports
                saveFormState();
            });

            // ==============================
            // BRANCH SELECTION MANAGEMENT
            // ==============================
            
            function initializeBranchSelection() {
                @if(Auth::user()->role == 1)
                    // Show branch selection for admin
                    $('#branch').show();
                    updateBranchSelection();
                @else
                    // Hide branch selection for non-admin
                    $('#branch').hide();
                @endif
            }
            
            function updateBranchSelection() {
                var category = $('#category').val();
                var branchSelect = $('#branch');
                
                @if(Auth::user()->role == 1)
                    // For admin, show branch selection for all categories
                    branchSelect.show();
                @endif
            }
            
            // Update branch selection when category changes
            $('#category').on('change', function() {
                updateBranchSelection();
            });
            
            // ==============================
            // CHART MANAGEMENT - Z-READING SPECIFIC
            // ==============================
            
            @if(isset($category) && $category == 'sales' && $report == 'zreading')
                // Initialize Z-Reading charts on page load
                console.log('Initializing Z-Reading charts...');
                
                // Set initial visibility - show summary chart by default
                showZReadingChart('summary', $('#chartTypeSelect').val());
                
                // Add event listener for graph type selector (summary/payment/deductions)
                $('#zreadingGraphTypeSelect').change(function() {
                    currentZReadingGraphType = $(this).val();
                    showZReadingChart(currentZReadingGraphType, $('#chartTypeSelect').val());
                });
                
                // Add event listener for chart type selector (bar/line/pie/etc)
                $('#chartTypeSelect').change(function() {
                    showZReadingChart(currentZReadingGraphType, $(this).val());
                });
            @else
                // Initialize regular chart if data exists
                @if(isset($chartData) && !empty($chartData))
                    initializeChart(@json($chartData), chartType);
                @endif

                // Chart type change handler for non-zreading reports
                $('#chartTypeSelect').change(function() {
                    chartType = $(this).val();
                    @if(isset($chartData) && !empty($chartData))
                        // Destroy existing chart
                        if (currentChart) {
                            currentChart.destroy();
                            currentChart = null;
                        }
                        // Reinitialize chart with new type
                        initializeChart(@json($chartData), chartType);
                    @endif
                });
            @endif

            // SINGLE initializeChart function for regular charts
            function initializeChart(chartData, type) {
                const canvas = document.getElementById('reportChart');
                if (!canvas) {
                    console.error('Canvas element not found!');
                    return;
                }
                
                const ctx = canvas.getContext('2d');
                
                // Destroy existing chart
                if (currentChart) {
                    currentChart.destroy();
                    currentChart = null;
                }
                
                // Check if we have valid data
                if (!chartData || !chartData.labels || chartData.labels.length === 0) {
                    canvas.parentElement.innerHTML = '<div class="p-4 text-center text-gray-500">No chart data available for this report</div>';
                    return;
                }
                
                // Determine chart type
                let finalType = type;
                if (type === 'stackedBar') {
                    finalType = 'bar';
                } else if (type === 'area') {
                    finalType = 'line';
                }
                
                // Prepare datasets
                const datasets = chartData.datasets.map(dataset => {
                    return {
                        ...dataset,
                        borderWidth: dataset.borderWidth || 2,
                        backgroundColor: dataset.backgroundColor || 
                            (type === 'bar' ? 'rgba(59, 130, 246, 0.7)' : 
                            type === 'line' ? 'rgba(59, 130, 246, 0.1)' : 
                            'rgba(59, 130, 246, 0.5)'),
                        borderColor: dataset.borderColor || 'rgb(59, 130, 246)',
                        fill: dataset.fill || (type === 'area'),
                        tension: dataset.tension || 0.4
                    };
                });
                
                // Chart options
                const options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: chartData.title || 'Report Chart',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            padding: 20
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += '';
                                    }
                                    if (context.parsed.y !== null) {
                                        if (chartData.title.toLowerCase().includes('sales') || 
                                            chartData.title.toLowerCase().includes('amount') ||
                                            chartData.title.toLowerCase().includes('revenue') ||
                                            chartData.title.toLowerCase().includes('cost')) {
                                            label += '' + context.parsed.y.toLocaleString('en-PH', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        } else {
                                            label += context.parsed.y.toLocaleString();
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (chartData.title.toLowerCase().includes('sales') || 
                                        chartData.title.toLowerCase().includes('amount') ||
                                        chartData.title.toLowerCase().includes('revenue') ||
                                        chartData.title.toLowerCase().includes('cost')) {
                                        return '₱ ' + value.toLocaleString('en-PH', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        });
                                    }
                                    return value.toLocaleString();
                                }
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                callback: function(value) {
                                    // No peso sign for transaction counts
                                    return value.toLocaleString();
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                };
                
                // Stacked bar chart options
                if (type === 'stackedBar') {
                    options.scales.x.stacked = true;
                    options.scales.y.stacked = true;
                }
                
                // Pie/Doughnut chart options
                if (type === 'pie' || type === 'doughnut') {
                    options.plugins.legend.position = 'right';
                    options.cutout = type === 'doughnut' ? '50%' : 0;
                }
                
                try {
                    // Create chart
                    currentChart = new Chart(ctx, {
                        type: finalType,
                        data: {
                            labels: chartData.labels,
                            datasets: datasets
                        },
                        options: options
                    });
                } catch (error) {
                    console.error('Error creating chart:', error);
                    canvas.parentElement.innerHTML = '<div class="p-4 text-center text-red-500">Error creating chart: ' + error.message + '</div>';
                }
            }

            // Z-READING SPECIFIC FUNCTIONS
            @if(isset($category) && $category == 'sales' && $report == 'zreading')
                // Function to show Z-Reading chart
                function showZReadingChart(graphType, chartType = 'bar') {
                    console.log('Switching to graph type:', graphType, 'with chart type:', chartType);
                    
                    // Destroy ALL existing charts before showing new one
                    destroyAllZReadingCharts();
                    
                    // Hide all chart containers
                    $('.chart-section').addClass('hidden');
                    
                    // Show selected chart container
                    const chartContainer = $(`#zreading${capitalizeFirstLetter(graphType)}Chart`);
                    chartContainer.removeClass('hidden');
                    
                    // Get the appropriate data based on graph type
                    let chartData;
                    switch(graphType) {
                        case 'summary':
                            chartData = @json($summaryChartData ?? null);
                            break;
                        case 'payment':
                            chartData = @json($paymentChartData ?? null);
                            break;
                        case 'deductions':
                            chartData = @json($deductionsChartData ?? null);
                            break;
                    }
                    
                    // Initialize the chart with selected type
                    if (chartData && !$.isEmptyObject(chartData)) {
                        switch(graphType) {
                            case 'summary':
                                summaryChart = initializeZReadingChart('reportChart', chartData, chartType);
                                break;
                            case 'payment':
                                paymentChart = initializeZReadingChart('paymentChart', chartData, chartType);
                                break;
                            case 'deductions':
                                deductionsChart = initializeZReadingChart('deductionsChart', chartData, chartType);
                                break;
                        }
                    } else {
                        console.warn('No chart data available for:', graphType);
                        chartContainer.html('<div class="p-4 text-center text-gray-500">No chart data available</div>');
                    }
                }
                
                // Function to destroy all Z-Reading charts
                function destroyAllZReadingCharts() {
                    console.log('Destroying all Z-Reading charts');
                    
                    // Destroy summary chart if it exists
                    if (summaryChart) {
                        console.log('Destroying summary chart');
                        summaryChart.destroy();
                        summaryChart = null;
                    }
                    
                    // Destroy payment chart if it exists
                    if (paymentChart) {
                        console.log('Destroying payment chart');
                        paymentChart.destroy();
                        paymentChart = null;
                    }
                    
                    // Destroy deductions chart if it exists
                    if (deductionsChart) {
                        console.log('Destroying deductions chart');
                        deductionsChart.destroy();
                        deductionsChart = null;
                    }
                    
                    // Also destroy any Chart.js charts that might exist on the canvases
                    ['reportChart', 'paymentChart', 'deductionsChart'].forEach(canvasId => {
                        const existingChart = Chart.getChart(canvasId);
                        if (existingChart) {
                            console.log(`Destroying chart on ${canvasId}`);
                            existingChart.destroy();
                        }
                    });
                }
                
                // Function to initialize Z-Reading chart with different types
                function initializeZReadingChart(canvasId, chartData, type) {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas) {
                        console.error(`Canvas element '${canvasId}' not found!`);
                        return null;
                    }
                    
                    // Clear any existing chart on this canvas FIRST
                    const existingChart = Chart.getChart(canvasId);
                    if (existingChart) {
                        console.log(`Destroying existing chart on ${canvasId}`);
                        existingChart.destroy();
                    }
                    
                    // Clear canvas context
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    
                    // Check if we have valid data
                    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
                        console.warn(`No valid chart data for ${canvasId}`, chartData);
                        // Clear the container and show message
                        const container = canvas.parentElement;
                        if (container) {
                            container.innerHTML = '<div class="p-4 text-center text-gray-500">No chart data available</div>';
                        }
                        return null;
                    }
                    
                    // Reset canvas if it was cleared by error message
                    if (canvas.parentElement && canvas.parentElement.querySelector('div')) {
                        canvas.parentElement.innerHTML = `<canvas id="${canvasId}" width="400" height="400"></canvas>`;
                        // Get new canvas reference
                        const newCanvas = document.getElementById(canvasId);
                        if (newCanvas) {
                            canvas = newCanvas;
                        }
                    }
                    
                    // Determine actual chart type
                    let actualType = type;
                    if (type === 'stackedBar') {
                        actualType = 'bar';
                    } else if (type === 'area') {
                        actualType = 'line';
                    }
                    
                    // Prepare datasets
                    const datasets = chartData.datasets.map((dataset, index) => {
                        const colors = [
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(201, 203, 207, 0.7)',
                            'rgba(0, 128, 0, 0.7)',
                            'rgba(128, 0, 128, 0.7)',
                            'rgba(0, 0, 128, 0.7)',
                            'rgba(128, 128, 0, 0.7)',
                            'rgba(128, 0, 0, 0.7)',
                            'rgba(0, 128, 128, 0.7)',
                        ];
                        
                        return {
                            ...dataset,
                            borderWidth: dataset.borderWidth || 2,
                            backgroundColor: dataset.backgroundColor || colors[index % colors.length],
                            borderColor: dataset.borderColor || colors[index % colors.length].replace('0.7', '1'),
                            fill: dataset.fill || (type === 'area'),
                            tension: dataset.tension || 0.1
                        };
                    });
                    
                    // Chart options - customize based on chart type
                    const options = {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: (type === 'pie' || type === 'doughnut') ? 'right' : 'top',
                                labels: {
                                    font: {
                                        size: 11
                                    },
                                    padding: 10,
                                    usePointStyle: true
                                }
                            },
                            title: {
                                display: true,
                                text: chartData.title || 'Z-Reading Chart',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                },
                                padding: 20
                            },
                            tooltip: {
                                mode: type === 'pie' || type === 'doughnut' ? 'point' : 'index',
                                intersect: type === 'pie' || type === 'doughnut',
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null || context.parsed !== undefined) {
                                            const value = context.parsed.y || context.parsed;
                                            label += '₱ ' + value.toLocaleString('en-PH', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    };
                    
                    // Add scales for non-pie/doughnut charts
                    if (type !== 'pie' && type !== 'doughnut') {
                        options.scales = {
                            x: {
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 0,
                                    font: {
                                        size: 10
                                    }
                                },
                                stacked: type === 'stackedBar'
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                stacked: type === 'stackedBar',
                                ticks: {
                                    callback: function(value) {
                                        return '₱ ' + value.toLocaleString('en-PH', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        });
                                    }
                                }
                            }
                        };
                    }
                    
                    // For pie/doughnut charts
                    if (type === 'pie' || type === 'doughnut') {
                        options.cutout = type === 'doughnut' ? '50%' : 0;
                    }
                    
                    // For stacked bar charts
                    if (type === 'stackedBar') {
                        options.scales.x.stacked = true;
                        options.scales.y.stacked = true;
                    }
                    
                    // For area charts
                    if (type === 'area') {
                        options.plugins.legend.position = 'top';
                    }
                    
                    try {
                        console.log(`Creating ${actualType} chart (type: ${type}) on ${canvasId}`);
                        const chart = new Chart(ctx, {
                            type: actualType,
                            data: {
                                labels: chartData.labels,
                                datasets: datasets
                            },
                            options: options
                        });
                        
                        // Update the canvas after chart creation
                        chart.update();
                        return chart;
                    } catch (error) {
                        console.error(`Error creating chart on ${canvasId}:`, error);
                        const container = canvas.parentElement;
                        if (container) {
                            container.innerHTML = `<div class="p-4 text-center text-red-500">Error creating chart: ${error.message}</div>`;
                        }
                        return null;
                    }
                }
                
                function capitalizeFirstLetter(string) {
                    return string.charAt(0).toUpperCase() + string.slice(1);
                }
            @endif

            // ==============================
            // TABLE PAGINATION
            // ==============================
            
            // Initialize table if data exists
            @if(isset($results) && !empty($results))
                // Only initialize once, after DOM is fully ready
                setTimeout(function() {
                    initializeTable();
                }, 50);
            @endif

            // Fallback for page info when Daily Sales Report renders without explicit page info
            if ($('#pageInfo').length && $('#pageInfo').text().trim() === '' && $('#reportTableBody tr').length > 0) {
                const totalRows = $('#reportTableBody tr').length;
                $('#pageInfo').text(`Showing 1 to ${Math.min(rowsPerPage, totalRows)} of ${totalRows} entries`);
            }

            // Rows per page change handler
            $('#rowsPerPage').change(function() {
                rowsPerPage = parseInt($(this).val());
                currentPage = 1;
                updateTable();
                updatePagination();
            });

                $('#dailySRTabSales').click(function() {
                    selectDailySRTab('sales');
                });

                $('#dailySRTabAR').click(function() {
                    selectDailySRTab('ar');
                });

                $('#dailySRTabOnline').click(function() {
                    selectDailySRTab('online');
                });

                $('#dailySRTabCheck').click(function() {
                    selectDailySRTab('check');
                });

                $('#prevPage').click(function() {
                if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                    updatePagination();
                }
            });

            $('#nextPage').click(function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                    updatePagination();
                }
            });

            $('#lastPage').click(function() {
                currentPage = totalPages;
                updateTable();
                updatePagination();
            });

            $('#pageNumberInput').on('change', function() {
                let requestedPage = parseInt($(this).val(), 10);
                if (isNaN(requestedPage) || requestedPage < 1) {
                    requestedPage = 1;
                }
                if (requestedPage > totalPages) {
                    requestedPage = totalPages || 1;
                }
                currentPage = requestedPage;
                updateTable();
                updatePagination();
            });

            // Table functions
            function initializeTable() {
                // Capture rows from the MAIN report table
                allData = $('#reportTable tbody tr').toArray();
                
                // Capture online/check rows into memory (clone so originals can be safely emptied)
                onlineData = $('#onlinePaymentsTable tbody tr').clone().toArray();
                checkData = $('#checkPaymentsTable tbody tr').clone().toArray();
                
                // Empty all three tbody elements — data now lives in JS arrays
                $('#reportTable tbody').empty();
                $('#onlinePaymentsTable tbody').empty();
                $('#checkPaymentsTable tbody').empty();
                
                if ($('#dailySRTabSales').length) {
                    selectDailySRTab('sales'); // Ensure default tab is applied
                } else {
                    filteredData = allData.slice();
                    totalPages = rowsPerPage > 0 ? Math.ceil(filteredData.length / rowsPerPage) : 1;
                    updateTable();
                    updatePagination();
                }
                
                setTimeout(initializeColumnReducer, 100);
            }

            function filterTableByDailySRTab() {
                if (currentDailySRTab === 'ar') {
                    filteredData = allData.filter(row => $(row).data('report-type') === 'ar');
                } else if (currentDailySRTab === 'online') {
                    filteredData = onlineData;
                } else if (currentDailySRTab === 'check') {
                    filteredData = checkData;
                } else {
                    filteredData = allData.filter(row => $(row).data('report-type') === 'sales');
                }
                
                totalPages = rowsPerPage > 0 ? Math.ceil(filteredData.length / rowsPerPage) : 1;
                currentPage = Math.min(currentPage, totalPages) || 1;
                updateDailySRFooter();
            }

            function selectDailySRTab(tab) {
                currentDailySRTab = tab;
                
                $('#dailySRTabSales')
                    .toggleClass('bg-blue-600 text-white', tab === 'sales')
                    .toggleClass('bg-gray-200 text-gray-700', tab !== 'sales');
                $('#dailySRTabAR')
                    .toggleClass('bg-blue-600 text-white', tab === 'ar')
                    .toggleClass('bg-gray-200 text-gray-700', tab !== 'ar');
                $('#dailySRTabOnline')
                    .toggleClass('bg-blue-600 text-white', tab === 'online')
                    .toggleClass('bg-gray-200 text-gray-700', tab !== 'online');
                $('#dailySRTabCheck')
                    .toggleClass('bg-blue-600 text-white', tab === 'check')
                    .toggleClass('bg-gray-200 text-gray-700', tab !== 'check');
                
                currentPage = 1;
                filterTableByDailySRTab();
                
                const isOnline = tab === 'online';
                const isCheck = tab === 'check';
                
                // Toggle containers first
                $('#dailySRTableWrapper').toggleClass('hidden', isOnline || isCheck);
                $('#onlinePaymentsTableContainer').toggleClass('hidden', !isOnline);
                $('#checkPaymentsTableContainer').toggleClass('hidden', !isCheck);
                $('#reportTable tfoot').toggle(!(isOnline || isCheck));
                
                // Render appropriate table
                if (isOnline) {
                    updateOnlineTable();
                } else if (isCheck) {
                    updateCheckTable();
                } else {
                    updateTable();
                }
                
                updatePagination();
                setTimeout(initializeColumnReducer, 100);
            }

            function updateOnlineTable() {
                const tbody = $('#onlinePaymentsTable tbody');
                tbody.empty();

                if (!onlineData || onlineData.length === 0) {
                    tbody.append('<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No online payments found for the selected period.</td></tr>');
                    $('#pageInfo').text('Showing 0 entries');
                    totalPages = 1;
                    return;
                }

                if (rowsPerPage === 0) {
                    onlineData.forEach(row => tbody.append($(row).clone()));
                } else {
                    const startIndex = (currentPage - 1) * rowsPerPage;
                    const endIndex = Math.min(startIndex + rowsPerPage, onlineData.length);
                    for (let i = startIndex; i < endIndex; i++) {
                        tbody.append($(onlineData[i]).clone());
                    }
                }

                const startIndex = onlineData.length === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
                const endIndex = rowsPerPage === 0 ? onlineData.length : Math.min(currentPage * rowsPerPage, onlineData.length);
                $('#pageInfo').text(`Showing ${startIndex} to ${endIndex} of ${onlineData.length} entries`);
                totalPages = rowsPerPage > 0 ? Math.ceil(onlineData.length / rowsPerPage) : 1;
            }

            function updateCheckTable() {
                const tbody = $('#checkPaymentsTable tbody');
                tbody.empty();

                if (!checkData || checkData.length === 0) {
                    tbody.append('<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No check payments found for the selected period.</td></tr>');
                    $('#pageInfo').text('Showing 0 entries');
                    totalPages = 1;
                    return;
                }

                if (rowsPerPage === 0) {
                    checkData.forEach(row => tbody.append($(row).clone()));
                } else {
                    const startIndex = (currentPage - 1) * rowsPerPage;
                    const endIndex = Math.min(startIndex + rowsPerPage, checkData.length);
                    for (let i = startIndex; i < endIndex; i++) {
                        tbody.append($(checkData[i]).clone());
                    }
                }

                const startIndex = checkData.length === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
                const endIndex = rowsPerPage === 0 ? checkData.length : Math.min(currentPage * rowsPerPage, checkData.length);
                $('#pageInfo').text(`Showing ${startIndex} to ${endIndex} of ${checkData.length} entries`);
                totalPages = rowsPerPage > 0 ? Math.ceil(checkData.length / rowsPerPage) : 1;
            }

            function updateDailySRFooter() {
                $('#dailySRSalesFooter').toggle(currentDailySRTab === 'sales');
                $('#dailySRARFooter').toggle(currentDailySRTab === 'ar');
            }

            function updateTable() {
                if (currentDailySRTab === 'online') {
                    updateOnlineTable();
                    return;
                }
                if (currentDailySRTab === 'check') {
                    updateCheckTable();
                    return;
                }
                const tbody = $('#reportTableBody');
                tbody.empty();
                
                if (rowsPerPage === 0) {
                    // Show all rows
                    filteredData.forEach(row => {
                        tbody.append(row);
                    });
                } else {
                    // Show paginated rows
                    const startIndex = (currentPage - 1) * rowsPerPage;
                    const endIndex = Math.min(startIndex + rowsPerPage, filteredData.length);
                    
                    for (let i = startIndex; i < endIndex; i++) {
                        tbody.append(filteredData[i]);
                    }
                }
                
                // Update page info
                const startIndex = (currentPage - 1) * rowsPerPage;
                const endIndex = Math.min(startIndex + rowsPerPage, filteredData.length);
                $('#pageInfo').text(`Showing ${startIndex + 1} to ${endIndex} of ${filteredData.length} entries`);
                
                // Re-initialize column reducer after table update
                setTimeout(initializeColumnReducer, 100);
            }

            function updatePagination() {
                if (rowsPerPage === 0) {
                    $('#paginationControls').hide();
                    return;
                }
                
                $('#paginationControls').show();
                
                // Update button states
                $('#firstPage').prop('disabled', currentPage === 1);
                $('#prevPage').prop('disabled', currentPage === 1);
                $('#nextPage').prop('disabled', currentPage === totalPages);
                $('#lastPage').prop('disabled', currentPage === totalPages);
                
                // Update page input value and total display
                const effectiveTotalPages = Math.max(1, totalPages);
                $('#pageNumberInput').val(currentPage);
                $('#pageNumberTotal').text(`/ ${effectiveTotalPages}`);
                
                // Only update pageInfo for sales/AR tabs; online/check handle their own
                if (currentDailySRTab !== 'online' && currentDailySRTab !== 'check') {
                    const startIndex = (currentPage - 1) * rowsPerPage;
                    const endIndex = Math.min(startIndex + rowsPerPage, filteredData.length);
                    $('#pageInfo').text(`Showing ${startIndex + 1} to ${endIndex} of ${filteredData.length} entries`);
                }
            }
          
            // ==============================
            // MODAL ACTIONS
            // ==============================
            
            var id;
            var category;
            var inv_id;

            $('.actionButton').click(function(){
                id = $(this).data('id');
                inv_id = $(this).data('inv_id');
                category = $(this).data('category');
                var name = $(this).data('name');
                var amount = $(this).data('amount');
                var date = $(this).data('date');
                var mop = $(this).data('mop');

                $('.nameDiv').addClass('hidden');

                if(category != 'sales'){
                    var quantity = $(this).data('quantity');
                    $('#editQuantity').val(quantity);

                    $('#payName').html(name);
                    $('#payPrice').html(amount);
                    $('#payQuantity').html(quantity);
                    $('#payDate').val(date);

                    if(inv_id == 0){
                        $('.nameDiv').removeClass('hidden');
                    }
                }

                $('.editName').html(name);
                $('#mop_val').val(mop);
                $('.editName').val(name);
                $('#editPrice').val(amount);
                $('#editDate').val(date);
            });

            $('.paySubmit').click(function(){
                var date = $('#payDate').val();
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url: @json(route('payExpenses')),
                    method:"POST",
                    data:{
                        id: id,
                        date: date,
                        _token: _token
                    },
                    success:function(result){
                        alert(result);
                        location.reload();
                    }
                })
            });

            $('.editSubmit').click(function(){
                var name = $('#name').val();
                var amount = $('#editPrice').val();
                var date = $('#editDate').val();
                var mop = $('#mop_val').val();
                var _token = $('input[name="_token"]').val();

                if(category != 'sales'){
                    var quantity = $('#editQuantity').val();
                    var route = @json(route('updateExpenses'));
                }else{
                    var route = @json(route('updateSales'));
                }

                $.ajax({
                    url: route,
                    method:"POST",
                    data:{
                        id: id,
                        name: name,
                        amount: amount,
                        quantity: quantity,
                        date: date,
                        mop : mop,
                        _token: _token
                    },
                    success:function(result){
                        alert(result);
                        location.reload();
                    }
                })
            });

            $('.payModal1Submit').click(function(){
                var date = $('#paymentDate').val();
                var mop = $('#mop_val').val();
                var _token = $('input[name="_token"]').val();

                if(category != 'sales'){
                    var route = @json(route('paymentExpenses'));
                }else{
                    var route = @json(route('updateSales'));
                }

                $.ajax({
                    url: route,
                    method:"POST",
                    data:{
                        id: id,
                        date: date,
                        mop : mop,
                        _token: _token
                    },
                    success:function(result){
                        alert(result);
                        location.reload();
                    }
                })
            });

            $('.deleteSubmit').click(function(){
                var _token = $('input[name="_token"]').val();

                if(category != 'sales'){
                    var route = @json(route('deleteExpenses'));
                }else{
                    var route = @json(route('deleteSales'));
                }

                $.ajax({
                    url: route,
                    method:"POST",
                    data:{
                        id: id,
                        _token: _token
                    },
                    success:function(result){
                        alert(result);
                        location.reload();
                    }
                })
            });

            // ==============================
            // FORM VALIDATION
            // ==============================
            
            $('form').submit(function(e) {
                @if(Auth::user()->role == 1)
                    // Validate branch selection for admin
                    var branch = $('#branch').val();
                    if (!branch || branch === '') {
                        alert('Please select a branch');
                        e.preventDefault();
                        return false;
                    }
                @endif
                
                // Validate date range
                var startDate = new Date($('#dateStart').val());
                var endDate = new Date($('#dateEnd').val());
                
                if (startDate > endDate) {
                    alert('Start date cannot be after end date');
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });

            // ==============================
            // COLUMN REDUCER FUNCTIONALITY
            // ==============================
            
            // Initialize column reducer
            initializeColumnReducer();
            
            function getCurrentVisibleReportTable() {
                if (currentDailySRTab === 'online') {
                    return document.getElementById('onlinePaymentsTable');
                }
                if (currentDailySRTab === 'check') {
                    return document.getElementById('checkPaymentsTable');
                }
                return document.getElementById('reportTable');
            }

            function initializeColumnReducer() {
                const table = getCurrentVisibleReportTable();
                if (!table) return;
                
                const headers = table.querySelectorAll('thead th');
                const checkboxesContainer = document.getElementById('columnCheckboxes');
                
                // Clear existing checkboxes
                checkboxesContainer.innerHTML = '';
                
                // Create checkboxes for each header
                headers.forEach((header, index) => {
                    const headerText = header.textContent.trim();
                    if (headerText) {
                        const checkboxDiv = document.createElement('div');
                        checkboxDiv.className = 'flex items-center';
                        
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.id = `col-${index}`;
                        checkbox.checked = true; // All columns visible by default
                        checkbox.className = 'mr-2';
                        checkbox.dataset.columnIndex = index;
                        
                        const label = document.createElement('label');
                        label.htmlFor = `col-${index}`;
                        label.className = 'text-sm text-gray-700 cursor-pointer';
                        label.textContent = headerText;
                        
                        checkboxDiv.appendChild(checkbox);
                        checkboxDiv.appendChild(label);
                        checkboxesContainer.appendChild(checkboxDiv);
                        
                        // Add event listener
                        checkbox.addEventListener('change', function() {
                            toggleColumnVisibility(index, this.checked);
                        });
                    }
                });
            }
            
            function toggleColumnVisibility(columnIndex, isVisible) {
                const table = getCurrentVisibleReportTable();
                if (!table) return;
                
                // Get all rows (header and body)
                const rows = table.querySelectorAll('tr');
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('th, td');
                    if (cells[columnIndex]) {
                        cells[columnIndex].style.display = isVisible ? '' : 'none';
                    }
                });
            }
            
            // Column reducer button click handler
            $('#columnReducerBtn').click(function(e) {
                e.stopPropagation();
                $('#columnSelector').toggleClass('hidden');
            });
            
            // Close dropdown when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#columnReducerBtn, #columnSelector').length) {
                    $('#columnSelector').addClass('hidden');
                }
            });
            
            // Clear form state (optional)
            function clearFormState() {
                localStorage.removeItem('reportFormState');
                // Reset form to defaults
                $('#category').val('sales');
                $('#dateStart').val("{{ date('Y-m-d\T00:00') }}");
                $('#dateEnd').val("{{ date('Y-m-d\T23:59') }}");
                if($('#branch').length) $('#branch').val('all');
                
                // Populate report options
                var reportsHtml = populateReportOptions('sales', '');
                $('#report').html(reportsHtml);
                
                // Save new state
                saveFormState();
            }
            // ==============================
            // Z-READING CHART MANAGEMENT
            // ==============================

            // Menu category filter change handler
            $('#menuCategoryFilter').change(function() {
                $('#categoryFilterForm').submit();
            });
            // Menu category filter change handler
                // 240506001 - leo - 11/13/25 - start 
                
                $('#category').change(function() {
                    var selected = $(this).val();

                    $('#categoryFilter input[name="category_id"]').val(selected);

                    $('#categoryFilter').submit();
                });

                // 240506001 - leo - 11/13/25 - end        
        });
    </script>
    <script>
        const baseUrl = @json(url('/'));
        function closeModal() {
            document.getElementById('hourlyModal').classList.add('hidden');
        }

        function openModal(date, hour, transactions, sales, discount) {
            console.log('Opening modal for:', date, 'Hour:', hour);
            
            // Convert to military time (24-hour format)
            const startHour = hour.toString().padStart(2, '0');
            const endHour = (hour + 1).toString().padStart(2, '0');
            
            // Set modal title and summary with military time
            document.getElementById('modalDateTitle').textContent = `Hourly Details - ${new Date(date).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            })} ${startHour}:00 - ${endHour}:00`;
            
            document.getElementById('modalTotalTransactions').textContent = transactions;
            document.getElementById('modalTotalSales').textContent = '₱ ' + parseFloat(sales).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('modalTotalDiscount').textContent = '₱ ' + parseFloat(discount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            // Filter transactions for the selected date and hour
            const selectedDate = date;
            const selectedHour = parseInt(hour);            
            let allTransactions = [];
            @isset($allTransactions)
                @if($allTransactions && $allTransactions->count() > 0)
                    allTransactions = @json($allTransactions);
                @endif
            @endisset            
            console.log('Total transactions available:', allTransactions.length);
            console.log('Looking for date:', selectedDate, 'hour:', selectedHour);
            
            const hourlyTransactions = allTransactions.filter(transaction => {
                // Parse the transaction date string directly without timezone conversion
                const transactionDate = new Date(transaction.date.replace(' ', 'T'));
                
                // Get date components in local timezone
                const transactionYear = transactionDate.getFullYear();
                const transactionMonth = transactionDate.getMonth() + 1; // Months are 0-indexed
                const transactionDay = transactionDate.getDate();
                const transactionHour = transactionDate.getHours();
                
                // Format the date as YYYY-MM-DD for comparison
                const transactionDateStr = `${transactionYear}-${transactionMonth.toString().padStart(2, '0')}-${transactionDay.toString().padStart(2, '0')}`;
                
                // Parse the selected date
                const selectedDateObj = new Date(selectedDate);
                const selectedYear = selectedDateObj.getFullYear();
                const selectedMonth = selectedDateObj.getMonth() + 1;
                const selectedDay = selectedDateObj.getDate();
                const selectedDateStr = `${selectedYear}-${selectedMonth.toString().padStart(2, '0')}-${selectedDay.toString().padStart(2, '0')}`;
                
                // Debug logging for each transaction
                console.log('Transaction:', transaction.number, 
                        'DB Date:', transaction.date, 
                        'Parsed Date:', transactionDateStr, 
                        'Hour:', transactionHour,
                        'Selected Date:', selectedDateStr,
                        'Selected Hour:', selectedHour,
                        'Match Date:', transactionDateStr === selectedDateStr,
                        'Match Hour:', transactionHour === selectedHour);
                
                return transactionDateStr === selectedDateStr && transactionHour === selectedHour;
            });

            console.log('Filtered transactions:', hourlyTransactions);

            // Build the table rows
            let tableBody = '';
            const totalTransactions = hourlyTransactions.length;
            
            if (totalTransactions > 0) {
                hourlyTransactions.forEach((transaction, index) => {
                    // Convert transaction time to military time
                    const transactionDate = new Date(transaction.date.replace(' ', 'T'));
                    const hours = transactionDate.getHours().toString().padStart(2, '0');
                    const minutes = transactionDate.getMinutes().toString().padStart(2, '0');
                    const seconds = transactionDate.getSeconds().toString().padStart(2, '0');
                    const time = `${hours}:${minutes}:${seconds}`;
                    
                    // Use 0 if pwd_total is null or undefined
                    const pwdAmount = transaction.pwd_total || 0;
                    
                    tableBody += `
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-2 text-center text-gray-900 whitespace-nowrap">
                                ${time}
                            </td>
                            <td class="px-6 py-2 text-center text-gray-900 whitespace-nowrap font-semibold">
                                ${transaction.number}
                            </td>
                            <td class="px-6 py-2 text-center text-gray-900 whitespace-nowrap">
                                ${parseFloat(transaction.total_sales).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </td>
                            <td class="px-6 py-2 text-center text-gray-900 whitespace-nowrap">
                                ₱ ${parseFloat(pwdAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </td>
                        </tr>
                    `;
                });
            } else {
                tableBody = `
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No transactions found for this hour.
                        </td>
                    </tr>
                `;
            }

            document.getElementById('hourlyDetailsBody').innerHTML = tableBody;
            
            // Update transaction count display
            const countElement = document.getElementById('transactionCount');
            if (totalTransactions > 13) {
                countElement.textContent = `Showing ${totalTransactions} transactions (scroll to see more)`;
                countElement.classList.remove('hidden');
            } else {
                countElement.textContent = `Total: ${totalTransactions} transactions`;
                countElement.classList.remove('hidden');
            }
            
            document.getElementById('hourlyModal').classList.remove('hidden');
        }

        // Update the click handlers
        $(document).ready(function() {
            $('.view-hourly-details').click(function() {
                const date = $(this).data('date');
                const hour = $(this).data('hour');
                const transactions = $(this).data('transactions');
                const sales = $(this).data('sales');
                const discount = $(this).data('discount');
                
                openModal(date, hour, transactions, sales, discount);
            });
        });

        const currentReportCategory = @json($category ?? null);
        const currentReportType = @json($report ?? null);

        // Build a printable table with full row data
        function buildPrintableTable(table, rowsToPrint) {
            const printableTable = table.cloneNode(true);
            const tbody = printableTable.querySelector('tbody');
            if (!tbody) {
                return printableTable;
            }

            tbody.innerHTML = '';
            rowsToPrint.forEach(row => {
                tbody.appendChild(row.cloneNode(true));
            });

            return printableTable;
        }

        // Print report table function
        function printVisibleTable() {
            if (currentReportCategory === 'accounting' && currentReportType === 'balsheet') {
                const sheetContainer = document.getElementById('balanceSheetContainer');
                if (!sheetContainer) {
                    alert('No balance sheet content available to print.');
                    return;
                }

                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    alert('Unable to open print window. Please allow popups.');
                    return;
                }

                const styles = `
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
                        thead { background: #f9fafb; }
                        tfoot { background: #f3f4f6; font-weight: bold; }
                        .print-title { margin-bottom: 20px; font-size: 18px; font-weight: 700; }
                    </style>
                `;

                const title = `<div class="print-title">Murang Karne Seafood at Iba Pa</div>`;
                const printableContent = sheetContainer.cloneNode(true);

                printWindow.document.write(`<!DOCTYPE html><html><head><title>Print Balance Sheet</title>${styles}</head><body>${title}${printableContent.outerHTML}</body></html>`);
                printWindow.document.close();
                printWindow.focus();

                printWindow.onload = function() {
                    printWindow.print();
                };
                return;
            }

            if (currentReportCategory === 'accounting' && currentReportType === 'incomeState') {
                const sheetContainer = document.getElementById('incomeStatementContainer');
                if (!sheetContainer) {
                    alert('No income statement content available to print.');
                    return;
                }

                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    alert('Unable to open print window. Please allow popups.');
                    return;
                }

                const styles = `
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
                        thead { background: #f9fafb; }
                        tfoot { background: #f3f4f6; font-weight: bold; }
                        .print-title { margin-bottom: 20px; font-size: 18px; font-weight: 700; }
                    </style>
                `;

                const title = `<div class="print-title">Income Statement</div>`;
                const printableContent = sheetContainer.cloneNode(true);

                printWindow.document.write(`<!DOCTYPE html><html><head><title>Print Income Statement</title>${styles}</head><body>${title}${printableContent.outerHTML}</body></html>`);
                printWindow.document.close();
                printWindow.focus();

                printWindow.onload = function() {
                    printWindow.print();
                };
                return;
            }

            const table = currentDailySRTab === 'online'
                ? document.getElementById('onlinePaymentsTable')
                : currentDailySRTab === 'check'
                    ? document.getElementById('checkPaymentsTable')
                    : document.getElementById('reportTable');
            if (!table) {
                alert('No table available to print.');
                return;
            }

            const rowsToPrint = currentReportCategory === 'dailySR'
                ? (currentDailySRTab === 'online'
                    ? onlineData
                    : currentDailySRTab === 'check'
                        ? checkData
                        : allData.filter(row => $(row).data('report-type') === (currentDailySRTab === 'ar' ? 'ar' : 'sales')))
                : Array.from(table.querySelectorAll('tbody tr'));

            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                alert('Unable to open print window. Please allow popups.');
                return;
            }

            const styles = `
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
                    thead { background: #f9fafb; }
                    tfoot { background: #f3f4f6; font-weight: bold; }
                    .print-title { margin-bottom: 20px; font-size: 18px; font-weight: 700; }
                </style>
            `;

            const title = `<div class="print-title">${currentReportCategory === 'dailySR' ? (currentDailySRTab === 'ar' ? 'AR Collection' : (currentDailySRTab === 'online' ? 'Online Payments' : (currentDailySRTab === 'check' ? 'Check Payment' : 'Sales'))) : 'Report'}</div>`;
            const printableTable = buildPrintableTable(table, rowsToPrint);

            printWindow.document.write(`<!DOCTYPE html><html><head><title>Print Report</title>${styles}</head><body>${title}${printableTable.outerHTML}</body></html>`);
            printWindow.document.close();
            printWindow.focus();

            printWindow.onload = function() {
                printWindow.print();
            };
        }

        // Handle print button click
        $('#exportReportBtn').on('click', function() {
            printVisibleTable();
        });
    </script>
</x-app-layout>