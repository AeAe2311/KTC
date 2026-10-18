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
                max-height: calc(100vh - 180px);
            }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-deleted {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .status-synced {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-pending {
            background-color: #f3e8ff;
            color: #5b21b6;
        }
        
        .logo-container {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            overflow: hidden;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .bank-logo {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .transaction-type {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .type-deposit {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .type-withdrawal {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .type-transfer {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .amount-positive {
            color: #065f46;
            font-weight: 600;
        }
        
        .amount-negative {
            color: #991b1b;
            font-weight: 600;
        }
    </style>

    @section('page_title', 'ONLINE BANKS MANAGEMENT')

    {{-- DELETE MODAL --}}
    <div id="itemDeleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-red-700"></i>
                        <span class="text-red-700 text-base md:text-lg lg:text-xl">Delete Bank</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemDeleteModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500">
                        Are you sure you want to delete this bank? This action will soft delete the bank and can be restored later.
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <a type="button" href="" class="deleteConfirm w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Delete</a>
                    <button data-modal-hide="itemDeleteModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- DELETE MODAL END --}}

    {{-- RESTORE MODAL --}}
    <div id="restoreBankModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-history mr-2 text-xl md:text-2xl lg:text-3xl text-emerald-700"></i>
                        <span class="text-emerald-700 text-base md:text-lg lg:text-xl">Restore Bank</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="restoreBankModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500">
                        Are you sure you want to restore this bank? It will become active again.
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <a type="button" href="" id="restoreConfirm" class="w-24 text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Restore</a>
                    <button data-modal-hide="restoreBankModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- RESTORE MODAL END --}}

    {{-- NOTIFICATION --}}
    @if (session('success'))
        <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-72 h-12 lg:ml-32 bg-emerald-200 rounded-lg z-50 shadow-md shadow-emerald-800 flex flex-row justify-between px-2">
            <div class="flex">
                <i class="self-center uil uil-cloud-check text-emerald-800 text-2xl"></i>
                <h1 class="self-center text-emerald-800 font-semibold ml-1">{{ session('success') }}</h1>
            </div>
            <button class="notifButton self-center">
                <i class="uil uil-times text-2xl text-emerald-800"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-72 h-12 lg:ml-32 bg-red-200 rounded-lg z-50 shadow-md shadow-red-800 flex flex-row justify-between px-2">
            <div class="flex">
                <i class="self-center uil uil-exclamation-triangle text-red-800 text-2xl"></i>
                <h1 class="self-center text-red-800 font-semibold ml-1">{{ session('error') }}</h1>
            </div>
            <button class="notifButton self-center">
                <i class="uil uil-times text-2xl text-red-800"></i>
            </button>
        </div>
    @endif
    {{-- NOTIFICATION END --}}

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                {{-- CONTROLS --}}
                <div class="mb-3">
                    <div class="md:grid md:grid-cols-2">
                        <div class="w-24">
                            @if(!$showTransactions)
                                <a href="{{ route('onlinebank.add') }}" class="block mb-3 md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px">
                                    <i class="uil uil-plus mr-1"></i>ADD
                                </a>
                            @endif
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
                                    <button id="searchButton" type="button" style="bottom: 5px; right: 5px;" class="text-white absolute bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-2.5 py-1.5">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- CONTROLS END --}}

                {{-- FILTERS --}}
                <div class="mb-4 flex flex-wrap gap-2">
                    <span class="text-sm text-gray-700 font-medium self-center">Filter by:</span>
                    <a href="{{ route('onlinebank.index') }}" class="px-3 py-1 text-xs font-medium rounded-full {{ !$showTransactions && !request('deleted') && !request('sync') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        Bank List
                    </a>
                    <a href="{{ route('onlinebank.index') }}?transactions=1" class="px-3 py-1 text-xs font-medium rounded-full {{ $showTransactions ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        Transactions
                    </a>
                    <!-- @if(!$showTransactions)
                        <a href="{{ route('onlinebank.index') }}?deleted=1" class="px-3 py-1 text-xs font-medium rounded-full {{ request('deleted') == '1' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                            Deleted
                        </a>
                        <a href="{{ route('onlinebank.index') }}?sync=0" class="px-3 py-1 text-xs font-medium rounded-full {{ request('sync') == '0' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                            Pending Sync
                        </a>
                        <a href="{{ route('onlinebank.index') }}?sync=1" class="px-3 py-1 text-xs font-medium rounded-full {{ request('sync') == '1' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            Synced
                        </a>
                    @endif -->
                </div>
                {{-- FILTERS END --}}

                {{-- SUMMARY STATS --}}
                @php
                    // Get the current user's store_code
                    $storeCode = auth()->user()->store_code;
                @endphp

                @if(!$showTransactions)
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                        <span class="text-sm text-blue-800">
                            Total Banks
                        </span>

                        <h3 class="text-2xl font-bold text-blue-800">
                            {{ $onlinebankCount }}
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                        <span class="text-sm text-emerald-800">
                            Active Banks
                        </span>

                        <h3 class="text-2xl font-bold text-emerald-800">
                            {{ DB::table('online_banking_list')->where('store_code', $storeCode)->where('is_deleted', 0)->count() }}
                        </h3>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                        <span class="text-sm text-red-800">
                            Deleted Banks
                        </span>

                        <h3 class="text-2xl font-bold text-red-800">
                            {{ DB::table('online_banking_list')->where('store_code', $storeCode)->where('is_deleted', 1)->count() }}
                        </h3>
                    </div>  
                </div>
                @else
                {{-- TRANSACTION STATS - Responsive Layout --}}
                <div class="mb-6">
                    {{-- Mobile View: Single Column Stack --}}
                    <div class="md:hidden space-y-4">
                        <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                            <span class="text-sm text-gray-800">
                                Total Transactions
                            </span>
                            <h3 class="text-2xl font-bold text-red-800">
                                {{ $transactionsCount }}
                            </h3>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                            <span class="text-sm text-gray-800">
                                Total Amount
                            </span>
                            <h3 class="text-2xl font-bold text-emerald-600">
                                ₱{{ number_format(DB::table('online_banking_trans')->where('store_code', $storeCode)->where('is_deleted', 0)->sum('amount'), 2) }}
                            </h3>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                            <span class="text-sm text-gray-800">
                                Total Fees
                            </span>
                            <h3 class="text-2xl font-bold text-yellow-600">
                                ₱{{ number_format(DB::table('online_banking_trans')->where('store_code', $storeCode)->where('is_deleted', 0)->sum('fee'), 2) }}
                            </h3>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                            <span class="text-sm text-gray-800">
                                Today's Transactions
                            </span>
                            <h3 class="text-2xl font-bold text-blue-600">
                                {{ DB::table('online_banking_trans')->where('store_code', $storeCode)->whereDate('created_at', today())->count() }}
                            </h3>
                        </div>
                    </div>
                    
                    {{-- Desktop View: 4 Columns Grid --}}
                    <div class="hidden md:grid grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                            <span class="text-sm text-gray-800">
                                Total Transactions
                            </span>
                            <h3 class="text-2xl font-bold text-red-800">
                                {{ $transactionsCount }}
                            </h3>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                            <span class="text-sm text-gray-800">
                                Total Amount
                            </span>
                            <h3 class="text-2xl font-bold text-emerald-600">
                                ₱{{ number_format(DB::table('online_banking_trans')->where('store_code', $storeCode)->where('is_deleted', 0)->sum('amount'), 2) }}
                            </h3>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                            <span class="text-sm text-gray-800">
                                Total Fees
                            </span>
                            <h3 class="text-2xl font-bold text-yellow-600">
                                ₱{{ number_format(DB::table('online_banking_trans')->where('store_code', $storeCode)->where('is_deleted', 0)->sum('fee'), 2) }}
                            </h3>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border flex items-center justify-between">
                            <span class="text-sm text-gray-800">
                                Today's Transactions
                            </span>
                            <h3 class="text-2xl font-bold text-blue-600">
                                {{ DB::table('online_banking_trans')->where('store_code', $storeCode)->whereDate('created_at', today())->count() }}
                            </h3>
                        </div>
                    </div>
                </div>
                @endif
                {{-- SUMMARY STATS END --}}

                <div>
                    @if(!$showTransactions)
                    {{-- BANK LIST TABLE --}}
                    <div class="hidden md:block">
                        <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-200 sticky top-0">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Logo
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Bank Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Fee
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($onlinebank as $bank)
                                        <tr class="bg-white border-b hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                @if($bank->logo)
                                                    <div class="logo-container">
                                                        <img src="{{ asset('storage/' . $bank->logo) }}" alt="{{ $bank->bank_name }} logo" class="bank-logo" onerror="this.src='{{ asset('images/default-bank.png') }}'">
                                                    </div>
                                                @else
                                                    <div class="logo-container">
                                                        <i class="uil uil-university text-xl text-gray-400"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                {{ $bank->bank_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                %{{ number_format($bank->fee, 2) }}
                                            </td>
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                <div class="flex justify-center space-x-2">
                                                    @if(!$bank->is_deleted)
                                                        <a href="{{ url('/system-management/onlinebank/edit/'.$bank->id) }}"
                                                           class="text-blue-600 hover:text-blue-900 font-semibold text-sm px-2 py-1 rounded hover:bg-blue-50"
                                                           title="Edit">
                                                            <i class="uil uil-edit text-lg"></i>
                                                        </a>
                                                        
                                                        <button type="button" 
                                                                data-modal-target="itemDeleteModal" 
                                                                data-modal-toggle="itemDeleteModal" 
                                                                data-id="{{ $bank->id }}"
                                                                class="deleteButton text-red-600 hover:text-red-900 font-semibold text-sm px-2 py-1 rounded hover:bg-red-50"
                                                                title="Delete">
                                                            <i class="uil uil-trash-alt text-lg"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                data-modal-target="restoreBankModal" 
                                                                data-modal-toggle="restoreBankModal" 
                                                                data-id="{{ $bank->id }}"
                                                                class="restoreButton text-emerald-600 hover:text-emerald-900 font-semibold text-sm px-2 py-1 rounded hover:bg-emerald-50"
                                                                title="Restore">
                                                            <i class="uil uil-history text-lg"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    @if($onlinebank->isEmpty())
                                        <tr>
                                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                                <div class="flex flex-col items-center justify-center">
                                                    <i class="uil uil-university text-4xl text-gray-300 mb-2"></i>
                                                    <p class="text-gray-600">No banks found</p>
                                                    @if($search)
                                                        <p class="text-sm text-gray-500 mt-1">Try adjusting your search terms</p>
                                                    @else
                                                        <a href="{{ route('onlinebank.add') }}" class="mt-3 text-blue-600 hover:text-blue-800 font-medium">
                                                            Add your first bank →
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- BANK LIST TABLE END --}}

                    {{-- BANK LIST MOBILE VIEW --}}
                    <div class="md:hidden">
                        <div id="inventoryMobile" class="overflow-auto space-y-4">
                            @foreach ($onlinebank as $bank)
                                <div class="bg-white border rounded-lg p-4 shadow-sm">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center space-x-3">
                                            @if($bank->logo)
                                                <div class="logo-container" style="width: 40px; height: 40px;">
                                                    <img src="{{ asset('storage/' . $bank->logo) }}" alt="{{ $bank->bank_name }} logo" class="bank-logo" onerror="this.src='{{ asset('images/default-bank.png') }}'">
                                                </div>
                                            @else
                                                <div class="logo-container" style="width: 40px; height: 40px;">
                                                    <i class="uil uil-university text-lg text-gray-400"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h3 class="font-semibold text-gray-900">{{ $bank->bank_name }}</h3>
                                                <p class="text-sm text-gray-500">Fee: %{{ number_format($bank->fee, 2) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            @if(!$bank->is_deleted)
                                                <a href="{{ url('/system-management/onlinebank/edit/'.$bank->id) }}"
                                                   class="text-blue-600 hover:text-blue-900">
                                                    <i class="uil uil-edit text-lg"></i>
                                                </a>
                                                <button type="button" 
                                                        data-modal-target="itemDeleteModal" 
                                                        data-modal-toggle="itemDeleteModal" 
                                                        data-id="{{ $bank->id }}"
                                                        class="deleteButton text-red-600 hover:text-red-900">
                                                    <i class="uil uil-trash-alt text-lg"></i>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        data-modal-target="restoreBankModal" 
                                                        data-modal-toggle="restoreBankModal" 
                                                        data-id="{{ $bank->id }}"
                                                        class="restoreButton text-emerald-600 hover:text-emerald-900">
                                                    <i class="uil uil-history text-lg"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                <!-- <div class="flex justify-between items-center">
                                        <div class="flex gap-1">
                                            @if($bank->is_deleted)
                                                <span class="status-badge status-deleted text-xs">Deleted</span>
                                            @else
                                                <span class="status-badge status-active text-xs">Active</span>
                                            @endif
                                            
                                            @if($bank->is_sync)
                                                <span class="status-badge status-synced text-xs">Synced</span>
                                            @else
                                                <span class="status-badge status-pending text-xs">Pending</span>
                                            @endif
                                        </div>
                                    </div> -->
                                </div>
                            @endforeach
                            
                            @if($onlinebank->isEmpty())
                                <div class="bg-white border rounded-lg p-8 text-center">
                                    <i class="uil uil-university text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-600 mb-2">No banks found</p>
                                    @if($search)
                                        <p class="text-sm text-gray-500">Try adjusting your search terms</p>
                                    @else
                                        <a href="{{ route('onlinebank.add') }}" class="text-blue-600 hover:text-blue-800 font-medium inline-block mt-2">
                                            Add your first bank
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- BANK LIST MOBILE VIEW END --}}
                    
                    @else
                    {{-- TRANSACTIONS TABLE --}}
                    <div class="hidden md:block">
                        <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-200 sticky top-0">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Transaction ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Bank
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Fee Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Function
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Cashier
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr class="bg-white border-b hover:bg-gray-50">
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                {{ $transaction->trans }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $transaction->bank }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="{{ $transaction->amount >= 0 ? 'amount-positive' : 'amount-negative' }}">
                                                    ₱{{ number_format($transaction->amount, 2) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ number_format($transaction->fee, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="transaction-type type-{{ strtolower($transaction->function) }}">
                                                    {{ $transaction->function }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $transaction->cashier }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y h:i A') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    @if($transactions->isEmpty())
                                        <tr>
                                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                                <div class="flex flex-col items-center justify-center">
                                                    <i class="uil uil-receipt text-4xl text-gray-300 mb-2"></i>
                                                    <p class="text-gray-600">No transactions found</p>
                                                    @if($search)
                                                        <p class="text-sm text-gray-500 mt-1">Try adjusting your search terms</p>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- TRANSACTIONS TABLE END --}}

                    {{-- TRANSACTIONS MOBILE VIEW --}}
                    <div class="md:hidden">
                        <div id="inventoryMobile" class="overflow-auto space-y-4">
                            @foreach ($transactions as $transaction)
                                <div class="bg-white border rounded-lg p-4 shadow-sm">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="font-semibold text-gray-900">{{ $transaction->trans }}</h3>
                                            <p class="text-sm text-gray-500">{{ $transaction->bank }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="{{ $transaction->amount >= 0 ? 'amount-positive' : 'amount-negative' }} font-semibold">
                                                ₱{{ number_format($transaction->amount, 2) }}
                                            </span>
                                            <p class="text-sm text-gray-500">Fee: ₱{{ number_format($transaction->fee, 2) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2 mt-3">
                                        <div>
                                            <p class="text-xs text-gray-500">Function</p>
                                            <span class="transaction-type type-{{ strtolower($transaction->function) }} text-xs">
                                                {{ $transaction->function }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Cashier</p>
                                            <p class="text-sm">{{ $transaction->cashier }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-t">
                                        <p class="text-xs text-gray-500">Date</p>
                                        <p class="text-sm">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($transactions->isEmpty())
                                <div class="bg-white border rounded-lg p-8 text-center">
                                    <i class="uil uil-receipt text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-600 mb-2">No transactions found</p>
                                    @if($search)
                                        <p class="text-sm text-gray-500">Try adjusting your search terms</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- TRANSACTIONS MOBILE VIEW END --}}
                    @endif
                </div>

                {{-- PAGINATION --}}
                <div class="grid md:grid-cols-2 mt-3 px-3">
                    @php
                        // Cast $page to integer to avoid string - int error
                        $page = (int)$page;
                        $prev = $page - 1;
                        $next = $page + 1;
                        
                        if(!$showTransactions) {
                            $count = $onlinebankCount;
                            $items = $onlinebank;
                        } else {
                            $count = $transactionsCount;
                            $items = $transactions;
                        }
                        
                        $from = ($prev * 100) + 1;
                        $to = $page * 100;
                        if($to > $count){
                            $to = $count;
                        }
                        if($count == 0){
                            $from = 0;
                        }
                    @endphp
                    <div class="justify-self-center md:justify-self-start self-center">
                        <span class="text-sm text-gray-700">
                            Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $count }}</span> Items
                        </span>
                    </div>

                    <div class="justify-self-center md:justify-self-end">
                        <nav aria-label="Page navigation example" class="h-8 mb-0.5 shadow-xl">
                            <ul class="inline-flex items-center -space-x-px">
                                <li>
                                    <a href="{{ $showTransactions ? url('/system-management/onlinebank/'.$prev.'?transactions=1') : (($search == '') ? url('/system-management/onlinebank/'.$prev) : url('/system-management/onlinebank/'.$prev.'/'.$search)) }}"  
                                       class="{{ ($page == 1) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                        <i class="uil uil-angle-left-b"></i>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                </li>
                                <li>
                                    <p class="block w-9 h-9 leading-9 text-center z-10 text-gray-500 border border-gray-300 bg-white font-semibold">{{ $page }}</p>
                                </li>
                                <li>
                                    <a href="{{ $showTransactions ? url('/system-management/onlinebank/'.$next.'?transactions=1') : (($search == '') ? url('/system-management/onlinebank/'.$next) : url('/system-management/onlinebank/'.$next.'/'.$search)) }}" 
                                       class="{{ ($to == $count) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
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
            var showTransactions = {{ $showTransactions ? 'true' : 'false' }};
            
            $('#searchButton').click(function(){
                var search = $('#searchInput').val();
                var baseUrl = showTransactions ? 
                    '{{ url("/system-management/onlinebank/1") }}?transactions=1' : 
                    '{{ url("/system-management/onlinebank/1") }}';
                
                if(search != ""){
                    $('#searchForm').prop('action', baseUrl + (showTransactions ? '' : '/' + search));
                } else {
                    $('#searchForm').prop('action', baseUrl);
                }
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
            });

            // Notification handling
            $('.notifButton').click(function(){
                $(this).closest('.notif').addClass('hidden');
            });

            // Delete modal handling
            $('.deleteButton').click(function(){
                var id = $(this).data('id');
                $('.deleteConfirm').prop('href', `{{ url('system-management/onlinebank/delete/') }}/${id}`);
            });

            // Restore modal handling
            $('.restoreButton').click(function(){
                var id = $(this).data('id');
                $('#restoreConfirm').prop('href', `{{ url('system-management/onlinebank/restore/') }}/${id}`);
            });

            // Auto-hide notifications after 5 seconds
            setTimeout(function() {
                $('.notif').addClass('hidden');
            }, 5000);

            // Close notifications when clicking elsewhere
            $('.contentDiv').click(function(){
                $('.notif').addClass('hidden');
            });
            
            $('.navDiv').click(function(){
                $('.notif').addClass('hidden');
            });

            // Mobile navigation handling
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
        });
    </script>
</x-app-layout>