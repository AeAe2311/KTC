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
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }        
        .status-deleted {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .status-returned {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-pending {
            background-color: #f3e8ff;
            color: #5b21b6;
        }
        
        .product-image-container {
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
        
        .product-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>

    @section('page_title', 'RETURN BOTTLES')

    {{-- DELETE MODAL --}}
    <div id="itemDeleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-red-700"></i>
                        <span class="text-red-700 text-base md:text-lg lg:text-xl">Delete Return Bottle</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemDeleteModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500">
                        Are you sure you want to delete this return bottle record? This action will remove the record permanently.
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
    <div id="restoreReturnModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-history mr-2 text-xl md:text-2xl lg:text-3xl text-emerald-700"></i>
                        <span class="text-emerald-700 text-base md:text-lg lg:text-xl">Restore Return Bottle</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="restoreReturnModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500">
                        Are you sure you want to restore this return bottle record?
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <a type="button" href="" id="restoreConfirm" class="w-24 text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Restore</a>
                    <button data-modal-hide="restoreReturnModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- RESTORE MODAL END --}}

    {{-- RETURN COMPLETE MODAL --}}
    <div id="completeReturnModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-check-circle mr-2 text-xl md:text-2xl lg:text-3xl text-emerald-700"></i>
                        <span class="text-emerald-700 text-base md:text-lg lg:text-xl">Mark as Returned</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="completeReturnModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500">
                        Are you sure you want to mark this return bottle as completed? This will update the status to returned.
                    </p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <a type="button" href="" id="completeConfirm" class="w-24 text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Complete</a>
                    <button data-modal-hide="completeReturnModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- RETURN COMPLETE MODAL END --}}

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
                        <div class="mb-4 flex flex-wrap gap-2">
                            <!-- a href="{{ route('returnbottle.add') }}" class="block mb-3 md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px">
                                <i class="uil uil-plus mr-1"></i>ADD
                            </a -->
                    <span class="text-sm text-gray-700 font-medium self-center">Filter by:</span>
                    <a href="{{ route('returnbottle.index') }}" class="px-3 py-1 text-xs font-medium rounded-full {{ !request('status') && !request('deleted') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        All
                    </a>
                    <a href="{{ route('returnbottle.index') }}?status=pending" class="px-3 py-1 text-xs font-medium rounded-full {{ request('status') == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                        Pending
                    </a>
                    <a href="{{ route('returnbottle.index') }}?status=returned" class="px-3 py-1 text-xs font-medium rounded-full {{ request('status') == 'returned' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }}">
                        Returned
                    </a>
                    <!-- a href="{{ route('returnbottle.index') }}?deleted=1" class="px-3 py-1 text-xs font-medium rounded-full {{ request('deleted') == '1' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                        Deleted
                    </a -->                            
                        </div>
                        <div class="justify-self-end w-full xl:w-4/5">
                                <form method="GET" action="" id="searchForm" class="w-full">
                                    <label for="searchInput" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input type="search" id="searchInput" name="search" class="block w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="SEARCH" value="{{ $search }}" autocomplete="off">
                                        <button id="clearButton" type="button" class=" absolute right-20 bottom-1">
                                            <i class="uil uil-times text-2xl"></i>
                                        </button>
                                        <button id="searchButton" type="submit" style="bottom: 5px; right: 5px;" class="text-white absolute bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-2.5 py-1.5">Search</button>
                                    </div>
                                </form>
                        </div>
                    </div>
                </div>
                {{-- CONTROLS END --}}

                {{-- FILTERS --}}
                <!-- div class="mb-4 flex flex-wrap gap-2">
                    <span class="text-sm text-gray-700 font-medium self-center">Filter by:</span>
                    <a href="{{ route('returnbottle.index') }}" class="px-3 py-1 text-xs font-medium rounded-full {{ !request('status') && !request('deleted') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        All
                    </a>
                    <a href="{{ route('returnbottle.index') }}?status=pending" class="px-3 py-1 text-xs font-medium rounded-full {{ request('status') == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                        Pending
                    </a>
                    <a href="{{ route('returnbottle.index') }}?status=returned" class="px-3 py-1 text-xs font-medium rounded-full {{ request('status') == 'returned' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }}">
                        Returned
                    </a>
                    <a href="{{ route('returnbottle.index') }}?deleted=1" class="px-3 py-1 text-xs font-medium rounded-full {{ request('deleted') == '1' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                        Deleted
                    </a>
                </divv -->
                {{-- FILTERS END --}}

                {{-- SUMMARY STATS --}}
                <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Total Returns</div>
                        <div class="text-2xl font-bold text-gray-800">{{ $totalCount }}</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Pending Returns</div>
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ $pendingCount }}
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Completed Returns</div>
                        <div class="text-2xl font-bold text-emerald-600">
                            {{ $returnedCount }}
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="text-sm text-gray-500">Total Refund Amount</div>
                        <div class="text-2xl font-bold text-blue-600">
                            ₱{{ number_format($totalAmount, 2) }}
                        </div>
                    </div>
                </div>
                {{-- SUMMARY STATS END --}}

                <div>
                    {{-- TABLE --}}
                    <div class="hidden md:block">
                        <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Transaction #
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Customer Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Bottle Qty
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Refund Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                            Status
                                        </th>
                                        <!-- th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                            Actions
                                        </th -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($returnbottle as $return)
                                        @php
                                            // Get user details
                                            $user = DB::table('users')->where('id', $return->user_id)->first();
                                            $customerName = $user ? $user->firstname . ' ' . $user->lastname : 'N/A';
                                        @endphp
                                        <tr class="bg-white border-b hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($return->created_at)->format('M d, Y h:i A') }}
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                {{ $return->trans_number ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $customerName }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $return->quantity_return ?? '0' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                ₱{{ number_format($return->amount_paid ?? 0, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($return->status == 'Paid')
                                                    <span class="status-badge status-paid">Paid</span>
                                                @elseif($return->status == 'returned')
                                                    <span class="status-badge status-returned">Returned</span>
                                                @else
                                                    <span class="status-badge status-pending">Pending</span>
                                                @endif
                                            </td>
                                            <!-- td class="px-6 py-4 text-center whitespace-nowrap">
                                                <div class="flex justify-center space-x-2">
                                                    @if(!$return->is_deleted)
                                                        <a href="{{ url('/returnbottle/edit/'.$return->id) }}"
                                                           class="text-blue-600 hover:text-blue-900 font-semibold text-sm px-2 py-1 rounded hover:bg-blue-50"
                                                           title="Edit">
                                                            <i class="uil uil-edit text-lg"></i>
                                                        </a>
                                                        
                                                        @if($return->status != 'returned')
                                                            <button type="button" 
                                                                    data-modal-target="completeReturnModal" 
                                                                    data-modal-toggle="completeReturnModal" 
                                                                    data-id="{{ $return->id }}"
                                                                    class="completeButton text-emerald-600 hover:text-emerald-900 font-semibold text-sm px-2 py-1 rounded hover:bg-emerald-50"
                                                                    title="Mark as Returned">
                                                                <i class="uil uil-check-circle text-lg"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        <button type="button" 
                                                                data-modal-target="itemDeleteModal" 
                                                                data-modal-toggle="itemDeleteModal" 
                                                                data-id="{{ $return->id }}"
                                                                class="deleteButton text-red-600 hover:text-red-900 font-semibold text-sm px-2 py-1 rounded hover:bg-red-50"
                                                                title="Delete">
                                                            <i class="uil uil-trash-alt text-lg"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                                data-modal-target="restoreReturnModal" 
                                                                data-modal-toggle="restoreReturnModal" 
                                                                data-id="{{ $return->id }}"
                                                                class="restoreButton text-emerald-600 hover:text-emerald-900 font-semibold text-sm px-2 py-1 rounded hover:bg-emerald-50"
                                                                title="Restore">
                                                            <i class="uil uil-history text-lg"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td -->
                                        </tr>
                                    @endforeach
                                    
                                    @if($returnbottle->isEmpty())
                                        <tr>
                                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                                <div class="flex flex-col items-center justify-center">
                                                    <i class="uil uil-bottle text-4xl text-gray-300 mb-2"></i>
                                                    <p class="text-gray-600">No return bottle records found</p>
                                                    @if($search)
                                                        <p class="text-sm text-gray-500 mt-1">Try adjusting your search terms</p>
                                                    @else
                                                        <!-- a href="{{ route('returnbottle.add') }}" class="mt-3 text-blue-600 hover:text-blue-800 font-medium">
                                                            Add your first return bottle record →
                                                        </a -->
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- TABLE END --}}

                    {{-- MOBILE VIEW --}}
                    <div class="md:hidden">
                        <div id="inventoryMobile" class="overflow-auto space-y-4">
                            @foreach ($returnbottle as $return)
                                @php
                                    $user = DB::table('users')->where('id', $return->user_id)->first();
                                    $customerName = $user ? $user->firstname . ' ' . $user->lastname : 'N/A';
                                @endphp
                                <div class="bg-white border rounded-lg p-4 shadow-sm">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $customerName }}</h3>
                                            <p class="text-sm text-gray-500">Trans #: {{ $return->trans_number ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ \Carbon\Carbon::parse($return->created_at)->format('M d, Y h:i A') }}
                                            </p>
                                        </div>
                                        <div class="flex space-x-2">
                                            @if(!$return->is_deleted)
                                                <a href="{{ url('/returnbottle/edit/'.$return->id) }}"
                                                   class="text-blue-600 hover:text-blue-900">
                                                    <i class="uil uil-edit text-lg"></i>
                                                </a>
                                                @if($return->status != 'returned')
                                                    <button type="button" 
                                                            data-modal-target="completeReturnModal" 
                                                            data-modal-toggle="completeReturnModal" 
                                                            data-id="{{ $return->id }}"
                                                            class="completeButton text-emerald-600 hover:text-emerald-900">
                                                        <i class="uil uil-check-circle text-lg"></i>
                                                    </button>
                                                @endif
                                                <button type="button" 
                                                        data-modal-target="itemDeleteModal" 
                                                        data-modal-toggle="itemDeleteModal" 
                                                        data-id="{{ $return->id }}"
                                                        class="deleteButton text-red-600 hover:text-red-900">
                                                    <i class="uil uil-trash-alt text-lg"></i>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        data-modal-target="restoreReturnModal" 
                                                        data-modal-toggle="restoreReturnModal" 
                                                        data-id="{{ $return->id }}"
                                                        class="restoreButton text-emerald-600 hover:text-emerald-900">
                                                    <i class="uil uil-history text-lg"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 mb-3">
                                        <div>
                                            <p class="text-xs text-gray-500">Bottle Quantity</p>
                                            <p class="text-sm font-medium">{{ $return->quantity_return ?? '0' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Refund Amount</p>
                                            <p class="text-sm font-medium">₱{{ number_format($return->amount_paid ?? 0, 2) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-center">
                                        <div>
                                            @if($return->is_deleted)
                                                <span class="status-badge status-deleted text-xs">Deleted</span>
                                            @elseif($return->status == 'returned')
                                                <span class="status-badge status-returned text-xs">Returned</span>
                                            @else
                                                <span class="status-badge status-pending text-xs">Pending</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($returnbottle->isEmpty())
                                <div class="bg-white border rounded-lg p-8 text-center">
                                    <i class="uil uil-bottle text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-600 mb-2">No return bottle records found</p>
                                    @if($search)
                                        <p class="text-sm text-gray-500">Try adjusting your search terms</p>
                                    @else
                                        <!-- a href="{{ route('returnbottle.add') }}" class="text-blue-600 hover:text-blue-800 font-medium inline-block mt-2">
                                            Add your first return bottle record
                                        </a -->
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- MOBILE VIEW END --}}
                </div>

                {{-- PAGINATION --}}
                <div class="grid md:grid-cols-2 mt-3 px-3">
                    @php
                        // Cast $page to integer to avoid string - int error
                        $page = (int)$page;
                        $prev = $page - 1;
                        $next = $page + 1;
                        $from = ($prev * 100) + 1;
                        $to = $page * 100;
                        if($to > $returnbottleCount){
                            $to = $returnbottleCount;
                        }
                        if($returnbottleCount == 0){
                            $from = 0;
                        }
                    @endphp
                    <div class="justify-self-center md:justify-self-start self-center">
                        <span class="text-sm text-gray-700">
                            Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $returnbottleCount }}</span> Items
                        </span>
                    </div>

                    <div class="justify-self-center md:justify-self-end">
                        <nav aria-label="Page navigation example" class="h-8 mb-0.5 shadow-xl">
                            <ul class="inline-flex items-center -space-x-px">
                                <li>
                                    <a href="{{ $prev <= 0 ? '#' : url()->current() . '?' . http_build_query(array_merge(request()->except('page'), ['page' => $prev])) }}"  
                                       class="{{ $prev <= 0 ? 'pointer-events-none opacity-50' : '' }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                        <i class="uil uil-angle-left-b"></i>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                </li>
                                <li>
                                    <p class="block w-9 h-9 leading-9 text-center z-10 text-gray-500 border border-gray-300 bg-white font-semibold">{{ $page }}</p>
                                </li>
                                <li>
                                    <a href="{{ $next > $returnbottle->lastPage() ? '#' : url()->current() . '?' . http_build_query(array_merge(request()->except('page'), ['page' => $next])) }}" 
                                       class="{{ $next > $returnbottle->lastPage() ? 'pointer-events-none opacity-50' : '' }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
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
            // Search functionality
            $('#searchButton').click(function(e){
                e.preventDefault();
                var search = $('#searchInput').val();
                var form = $('#searchForm');
                var url = new URL(window.location.href);
                
                if (search) {
                    url.searchParams.set('search', search);
                } else {
                    url.searchParams.delete('search');
                }
                
                window.location.href = url.toString();
            });

            $('#searchInput').on('keydown', function(event) {
                if (event.keyCode === 13) {
                    $('#searchButton').click();
                    event.preventDefault();
                }
            });

            $('#clearButton').click(function(){
                $('#searchInput').val('');
                var url = new URL(window.location.href);
                url.searchParams.delete('search');
                window.location.href = url.toString();
            });

            // Notification handling
            $('.notifButton').click(function(){
                $(this).closest('.notif').addClass('hidden');
            });

            // Delete modal handling
            $('.deleteButton').click(function(){
                var id = $(this).data('id');
                $('.deleteConfirm').prop('href', `{{ url('returnbottle/delete/') }}/${id}`);
            });

            // Complete return modal handling
            $('.completeButton').click(function(){
                var id = $(this).data('id');
                $('#completeConfirm').prop('href', `{{ url('returnbottle/complete/') }}/${id}`);
            });

            // Restore modal handling
            $('.restoreButton').click(function(){
                var id = $(this).data('id');
                $('#restoreConfirm').prop('href', `{{ url('returnbottle/restore/') }}/${id}`);
            });

            // Auto-hide notifications after 5 seconds
            setTimeout(function() {
                $('.notif').addClass('hidden');
            }, 5000);

            // Close notifications when clicking elsewhere
            $('#contentDiv').click(function(){
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