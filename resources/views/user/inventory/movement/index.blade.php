<x-app-layout>
    @section('page_title', 'Inventory Movement')
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

    {{-- NO RESULTS NOTIFICATION --}}
    @if(isset($noResultsMessage))
        <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-auto max-w-2xl min-w-72 lg:ml-32 bg-red-100 rounded-lg z-50 shadow-md shadow-red-800 flex flex-row justify-between px-4 py-3">
            <div class="flex items-center">
                <i class="self-center uil uil-exclamation-triangle text-red-600 text-2xl"></i>
                <div class="ml-2">
                    <p class="text-sm font-semibold text-red-700">{{ $noResultsMessage }}</p>
                    <p class="text-xs text-red-600">Try removing some filters or using more general search terms</p>
                </div>
            </div>
            <div class="flex items-center gap-2 ml-4">
                <button onclick="resetFilters()" class="text-xs text-red-700 hover:text-red-900 underline font-medium whitespace-nowrap">
                    Reset Filters
                </button>
                <button class="notifButton self-center text-red-600 hover:text-red-800">
                    <i class="uil uil-times text-2xl"></i>
                </button>
            </div>
        </div>
    @endif
    {{-- NOTIFICATION END --}}
    <div class="lg:ml-64">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                <div class="flex gap-2 items-end relative mb-4">
                    <!-- Filter Form -->
                    <form id="filterForm" method="GET" action="{{ route('inventory.movement.index') }}" class="flex flex-wrap gap-3 flex-1">
                        <!-- Searchable Item Code Dropdown -->
                        <div class="flex-1 min-w-[200px]">
                            <label for="item_code_search" class="block mb-1 text-xs font-semibold text-gray-600">Item Code</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="item_code_search" 
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                    placeholder="Search item..."
                                    autocomplete="off"
                                    oninput="filterItems()"
                                    onfocus="showDropdown()"
                                    onkeydown="handleSearchKeydown(event)"
                                >
                                <input type="hidden" name="item_code" id="selected_item_code" value="{{ request('item_code') }}">
                                <div id="itemDropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg hidden" style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
                                    <div class="sticky top-0 bg-white p-2 hover:bg-gray-100 cursor-pointer border-b border-gray-200 z-10" onclick="selectItem('', 'All Items')">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                            </svg>
                                            <span class="font-semibold">All Items</span>
                                        </span>
                                    </div>
                                    <div class="overflow-y-auto" style="max-height: 250px;">
                                        @foreach ($allItems as $item)
                                            @php
                                                $displayText = '';
                                                if (!empty($item->item_code)) {
                                                    $displayText = $item->item_code;
                                                }
                                                if (!empty($item->brand_name)) {
                                                    $displayText .= (!empty($displayText) ? ' - ' : '') . $item->brand_name;
                                                }
                                                if (!empty($item->item_name)) {
                                                    $displayText .= (!empty($displayText) ? ' - ' : '') . $item->item_name;
                                                }
                                                if (empty($displayText)) {
                                                    $displayText = 'No Code - Unnamed Item (ID: ' . $item->menu_id . ')';
                                                }
                                                
                                                $optionValue = !empty($item->item_code) ? $item->item_code : 'NO_CODE_' . $item->menu_id;
                                                $escapedDisplayText = htmlspecialchars($displayText, ENT_QUOTES, 'UTF-8');
                                            @endphp
                                            <div class="item-option p-2 hover:bg-gray-100 cursor-pointer flex items-center border-b border-gray-100" 
                                                data-value="{{ $optionValue }}" 
                                                data-text="{{ $escapedDisplayText }}"
                                                onclick="selectItem('{{ $optionValue }}', '{{ $escapedDisplayText }}')">
                                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                                <span class="truncate text-sm">{{ $displayText }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div id="noResults" class="p-4 text-center text-gray-500 hidden">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{-- <p class="text-sm">No items found</p>
                                        <p class="text-xs text-gray-400 mt-1">Try different keywords</p> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search input -->
                        <div class="flex-1 min-w-[200px]">
                            <label for="search" class="block mb-1 text-xs font-semibold text-gray-600">Search</label>
                            <input 
                                id="search" 
                                name="search" 
                                type="text" 
                                value="{{ request('search') }}" 
                                class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Item, brand, reference, remarks..." 
                            />
                            {{-- @if(request()->has('search') && $transactions->total() == 0)
                            <p class="text-xs text-red-500 mt-1">No results found. Try different keywords.</p>
                            @endif --}}
                        </div>
                        
                        <div class="flex-1 min-w-[200px]">
                            <label for="movement_type" class="block mb-1 text-xs font-semibold text-gray-600">Movement Type</label>
                            <select id="movement_type" name="movement_type" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Types</option>
                                @foreach ($movementTypes as $type)
                                    <option value="{{ $type }}" {{ request('movement_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="flex-1 min-w-[200px]">
                            <label for="date_from" class="block mb-1 text-xs font-semibold text-gray-600">Date from</label>
                            <input 
                                id="date_from" 
                                name="date_from" 
                                type="datetime-local" 
                                value="{{ request('date_from', now()->startOfDay()->format('Y-m-d\TH:i')) }}" 
                                class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                            />
                        </div>
                        
                        <div class="flex-1 min-w-[200px]">
                            <label for="date_to" class="block mb-1 text-xs font-semibold text-gray-600">Date to</label>
                            <input 
                                id="date_to" 
                                name="date_to" 
                                type="datetime-local" 
                                value="{{ request('date_to', now()->endOfDay()->format('Y-m-d\TH:i')) }}" 
                                class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                            />
                        </div>
                        
                        <div class="flex gap-2 items-end">
                            <button type="submit" class="px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Filter
                            </button>
                        </div>
                        
                        <div class="flex gap-2 items-end">
                            <a href="{{ route('inventory.movement.index') }}" class="w-full px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 text-center flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>
                    
                    <!-- CLEAN DROPDOWN WITH HORIZONTAL FORMAT OPTIONS & TOOLTIPS -->
                    <!-- FIXED: Positioned outside the form with proper z-index -->
                    <div class="relative inline-block flex-shrink-0" id="dropdownContainer" style="z-index: 40;">
                        <button onclick="toggleDropdown()" 
                                title="Click to open actions menu" 
                                class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center gap-2 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            Actions
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Main Dropdown Menu - FIXED positioning -->
                        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50" style="min-width: 320px;">
                            <div class="py-1">
                                <!-- Summary Report ISSR -->
                                <div class="px-4 py-3 border-b border-gray-100 hover:bg-blue-50 transition-colors duration-150">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                            Summary Report (ISSR)
                                        </span>
                                    </div>
                                    <!-- Format Options - Horizontal -->
                                    <div class="flex gap-1 mt-2 ml-6">
                                        <button onclick="handleAction('print_summary', 'pdf')" 
                                                title="Print Summary Report as PDF"
                                                class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors duration-150">
                                            PDF
                                        </button>
                                        <button onclick="handleAction('export_summary', 'csv')" 
                                                title="Export Summary Report as CSV"
                                                class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors duration-150">
                                            CSV
                                        </button>
                                        <button onclick="handleAction('export_summary', 'xlsx')" 
                                                title="Export Summary Report as Excel"
                                                class="px-3 py-1 text-xs font-medium text-white bg-green-700 rounded hover:bg-green-800 transition-colors duration-150">
                                            Excel
                                        </button>
                                    </div>
                                </div>

                                <!-- Inventory Movement Report (All Filtered Data) -->
                                <div class="px-4 py-3 border-b border-gray-100 hover:bg-purple-50 transition-colors duration-150">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                                            </svg>
                                            Inventory Movement Report (IVM)
                                        </span>
                                    </div>
                                    <!-- Format Options - Horizontal -->
                                    <div class="flex gap-1 mt-2 ml-6">
                                        <button onclick="handleAction('print_all_filtered', 'pdf')" 
                                                title="Print All Filtered Inventory Movement Data as PDF"
                                                class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors duration-150">
                                            PDF
                                        </button>
                                        <button onclick="handleAction('export_data', 'csv')" 
                                                title="Export All Filtered Inventory Movement Data as CSV"
                                                class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors duration-150">
                                            CSV
                                        </button>
                                        <button onclick="handleAction('export_data', 'xlsx')" 
                                                title="Export All Filtered Inventory Movement Data as Excel"
                                                class="px-3 py-1 text-xs font-medium text-white bg-green-700 rounded hover:bg-green-800 transition-colors duration-150">
                                            Excel
                                        </button>
                                    </div>
                                </div>

                                <!-- IVM Current Page -->
                                <div class="px-4 py-3 hover:bg-orange-50 transition-colors duration-150">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h4l2-2h2l2 2h4a2 2 0 012 2v12a2 2 0 01-2 2zM8 13h8M8 17h6"></path>
                                            </svg>
                                            IVM Current Page
                                        </span>
                                    </div>
                                    <!-- Format Options - Horizontal -->
                                    <div class="flex gap-1 mt-2 ml-6">
                                        <button onclick="handleAction('print_view', 'pdf')" 
                                                title="Print Current Page View as PDF"
                                                class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors duration-150">
                                            PDF
                                        </button>
                                        <button onclick="handleAction('export_current_page', 'csv')" 
                                                title="Export Current Page Data as CSV"
                                                class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors duration-150">
                                            CSV
                                        </button>
                                        <button onclick="handleAction('export_current_page', 'xlsx')" 
                                                title="Export Current Page Data as Excel"
                                                class="px-3 py-1 text-xs font-medium text-white bg-green-700 rounded hover:bg-green-800 transition-colors duration-150">
                                            Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto shadow rounded-lg" id="tableContainer">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" id="inventoryTable">
                        <thead class="bg-gray-50 text-left text-gray-700 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Reference #</th>
                                <th class="px-4 py-3">Date</th>                                                                
                                <th class="px-4 py-3">Item Code</th>
                                <th class="px-4 py-3">Item Description</th>                                
                                <th class="px-4 py-3">Detail</th>
                                <th class="px-4 py-3">Before</th>                                                                
                                <th class="px-4 py-3">Qty In</th>
                                <th class="px-4 py-3">Qty Out</th>
                                <th class="px-4 py-3">Qty On Hand</th>
                                <th class="px-4 py-3">Sold Price</th>
                                <th class="px-4 py-3">Standard Cost</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                            @forelse ($transactions as $transaction)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            @if(strtoupper($transaction->type) === 'DELIVERY NOTE') bg-green-100 text-green-800
                                            @elseif(strtoupper($transaction->type) === 'STOCK IN' || strtoupper($transaction->type) === 'INCOMING') bg-blue-100 text-blue-800
                                            @elseif(strtoupper($transaction->type) === 'STOCK OUT') bg-red-100 text-red-800
                                            @elseif(strtoupper($transaction->type) === 'TRANSFORMATION') bg-purple-100 text-purple-800
                                            @elseif(strtoupper($transaction->type) === 'WASTE') bg-orange-100 text-orange-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $transaction->type ?? 'N/A' }}
                                        </span>
                                    </td>
                                   
                                    <td class="px-4 py-3 font-mono text-sm">
                                        @php
                                            $reference = $transaction->reference ?? 'N/A';
                                        @endphp
                                        <button 
                                            class="text-blue-600 hover:text-blue-800 hover:underline focus:outline-none"
                                            onclick="openPopup('{{ $transaction->id }}')"
                                            title="Click to view transaction details"
                                            type="button"
                                        >
                                            {{ $reference }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-600">
                                                {{ isset($transaction->created_at) ? \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d') : 'N/A' }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ isset($transaction->created_at) ? \Carbon\Carbon::parse($transaction->created_at)->format('H:i') : '' }}
                                            </span>
                                        </div>
                                    </td>                                     
                                    <td class="px-4 py-3" colspan="3">
                                        <div class="space-y-1">
                                            <span class="text-sm">
                                                @if($transaction->item_code)
                                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                                        {{ $transaction->item_code }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                                
                                                <span class="mx-2 text-gray-400">-</span>
                                                
                                                <span class="font-medium">
                                                    @if(!empty($transaction->brand_name))
                                                        {{ $transaction->brand_name }} 
                                                    @endif
                                                    {{ $transaction->item_name ?? 'N/A' }}
                                                    @if(!empty($transaction->unit))
                                                        <span class="text-xs text-gray-400">(per {{ $transaction->unit }})</span>
                                                    @endif
                                                </span>
                                                
                                                <span class="mx-2 text-gray-400">-</span>
                                                
                                                <span class="text-xs text-gray-600">{{ $transaction->detail ?? 'N/A' }}</span>
                                            </span>
                                        </div>
                                    </td>                                                                    
                                    
                                    <td class="px-4 py-3 text-right">{{ number_format((float) ($transaction->quantity_before ?? 0), 3) }}</td>
                                    
                                    <td class="px-4 py-3 text-right">
                                        @if(($transaction->quantity_in ?? 0) > 0)
                                            <span class="text-green-600 font-medium">+{{ number_format((float) ($transaction->quantity_in ?? 0), 3) }}</span>
                                        @else
                                            {{ number_format((float) ($transaction->quantity_in ?? 0), 3) }}
                                        @endif
                                    </td>
                                    
                                    <td class="px-4 py-3 text-right">
                                        @if(($transaction->quantity_out ?? 0) > 0)
                                            <span class="text-red-600 font-medium">-{{ number_format((float) ($transaction->quantity_out ?? 0), 3) }}</span>
                                        @else
                                            {{ number_format((float) ($transaction->quantity_out ?? 0), 3) }}
                                        @endif
                                    </td>                                   
                                    
                                    <td class="px-4 py-3 text-right font-medium">{{ number_format((float) ($transaction->remaining ?? 0), 3) }}</td>
                                    
                                    <td class="px-4 py-3 text-right whitespace-nowrap">{{ '₱ ' . number_format((float) ($transaction->amount ?? 0) * (float) ($transaction->quantity_out ?? 0), 2)}}</td>
                                    
                                    <td class="px-4 py-3 text-right">{{ (!empty($transaction->cost) ? '₱ ' . number_format((float) $transaction->cost, 2) : '₱ 0.00') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-4 py-6 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                            <p>No inventory movement records found.</p>
                                            @if(request()->has('search') || request()->has('item_code'))
                                            <p class="text-sm mt-2">Try adjusting your search criteria or reset filters.</p>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-gray-600">
                        Page {{ $transactions->currentPage() }} of {{ $transactions->lastPage() }}
                        @if ($transactions->total() > 0)
                            · {{ $transactions->total() }} record{{ $transactions->total() === 1 ? '' : 's' }}
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($transactions->previousPageUrl())
                            <a href="{{ $transactions->previousPageUrl() }}" class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Prev</a>
                        @else
                            <span class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">Prev</span>
                        @endif
                        <span class="px-3 py-2 text-sm font-semibold text-gray-900 bg-gray-100 border border-gray-200 rounded-lg">{{ $transactions->currentPage() }}</span>
                        @if ($transactions->nextPageUrl())
                            <a href="{{ $transactions->nextPageUrl() }}" class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100">Next</a>
                        @else
                            <span class="inline-flex items-center justify-center px-3 py-2 text-sm font-semibold text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">Next</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Modal -->
    <div id="popupModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50" aria-hidden="true" onclick="closePopup()"></div>
            
            <div class="relative inline-block w-full max-w-6xl overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl my-8">
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 text-left sm:mt-0 sm:text-left">
                            <div class="flex justify-between items-center mb-4 no-print">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="popupTitle">Transaction #</h3>
                                
                                <button onclick="closePopup()" 
                                        title="Close popup (ESC)" 
                                        class="text-gray-400 hover:text-gray-500 no-print">
                                    <span class="sr-only">Close</span>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div id="popupContent" style="max-height: 80vh; overflow: hidden;">
                                <div class="text-left py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
                                    <p class="mt-2 text-gray-500">Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        /* Dropdown Styles */
        #itemDropdown::-webkit-scrollbar { width: 8px; }
        #itemDropdown::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        #itemDropdown::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        #itemDropdown::-webkit-scrollbar-thumb:hover { background: #555; }
        #itemDropdown { scrollbar-width: thin; scrollbar-color: #888 #f1f1f1; }
        #itemDropdown { animation: fadeIn 0.2s ease-in-out; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .item-option:hover { background-color: #e5e7eb; }
        .item-option.bg-blue-100 { background-color: #dbeafe; }
        #item_code_search:focus + #itemDropdown { border-color: #3b82f6; }
        
        @media (max-width: 640px) {
            #itemDropdown { max-height: 250px !important; }
            #itemDropdown .overflow-y-auto { max-height: 200px !important; }
        }

        /* Popup Styles */
        #popupContent .overflow-y-auto::-webkit-scrollbar { width: 8px; height: 8px; }
        #popupContent .overflow-y-auto::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        #popupContent .overflow-y-auto::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        #popupContent .overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #555; }
        #popupContent .overflow-y-auto { scrollbar-width: thin; scrollbar-color: #888 #f1f1f1; }
        
        .popup-print-container {
            max-height: 75vh;
            overflow-y: auto;
            padding: 4px;
        }
        
        .popup-table-wrapper {
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin: 0.5rem 0;
        }
        
        .popup-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .popup-table th,
        .popup-table td {
            padding: 6px 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .popup-table thead th {
            background-color: #f9fafb;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            color: #4b5563;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .popup-table tfoot td {
            background-color: #f9fafb;
            font-weight: 700;
        }
        
        .popup-table .text-right { text-align: right; }
        .popup-table .text-left { text-align: left; }
        .popup-table .font-bold { font-weight: 700; }
        .popup-table .font-mono { font-family: monospace; }
        
        .print-signatures {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #d1d5db;
        }
        
        .print-signatures .border-t {
            border-top: 1px solid #d1d5db;
        }
        
        .print-signatures .mt-6 { margin-top: 1.5rem; }
        .print-signatures .pt-1 { padding-top: 0.25rem; }
        .print-border-bottom {
            border-bottom: 1px solid #d1d5db !important;
        }

        /* Dropdown animation */
        #dropdownMenu {
            animation: dropdownFadeIn 0.2s ease-in-out;
        }
        
        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Tooltip styles */
        [title] {
            position: relative;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            .no-print, 
            #dropdownContainer, 
            .filter-form, 
            #filterForm .flex-wrap,
            .mt-4.flex-col,
            .pagination {
                display: none !important;
            }
            
            body {
                background: white !important;
                padding: 15px 20px !important;
                font-size: 11px !important;
                line-height: 1.5 !important;
                color: #1a1a1a !important;
            }
            
            .p-3, .lg\:ml-64, .lg\:pt-3 {
                padding: 0 !important;
                margin: 0 !important;
            }
            
            #contentDiv {
                padding: 0 !important;
                max-width: 100% !important;
            }
            
            .bg-white {
                background: white !important;
                box-shadow: none !important;
                border: none !important;
                padding: 5px !important;
            }
            
            #tableContainer {
                overflow: visible !important;
                box-shadow: none !important;
                border: 1px solid #999 !important;
                border-radius: 0 !important;
                margin-top: 10px !important;
            }
            
            #inventoryTable {
                width: 100% !important;
                font-size: 10px !important;
                border-collapse: collapse !important;
                border: 1px solid #999 !important;
            }

            #inventoryTable thead {
                display: table-header-group !important;
            }
            #inventoryTable tbody {
                display: table-row-group !important;
            }
            
            #inventoryTable thead th {
                background-color: #e5e7eb !important;
                color: #1a1a1a !important;
                font-size: 9px !important;
                font-weight: 700 !important;
                padding: 6px 8px !important;
                border: 1px solid #999 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            #inventoryTable thead tr.report-letterhead-row th.report-letterhead {
                background: #fff !important;
                padding: 0 0 8px 0 !important;
                border: none !important;
                border-bottom: 2px solid #1a1a1a !important;
            }
            
            #inventoryTable tbody td {
                padding: 5px 8px !important;
                border: 1px solid #ccc !important;
                font-size: 9.5px !important;
                vertical-align: middle !important;
            }
            
            #inventoryTable tbody tr {
                page-break-inside: avoid !important;
            }
            
            #inventoryTable tbody tr:nth-child(even) {
                background-color: #f9fafb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            #inventoryTable .rounded-full {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                font-size: 8px !important;
                padding: 2px 8px !important;
                font-weight: 600 !important;
                border-radius: 9999px !important;
                display: inline-block !important;
            }
            
            .bg-green-100 { background-color: #dcfce7 !important; color: #166534 !important; }
            .bg-blue-100 { background-color: #dbeafe !important; color: #1e40af !important; }
            .bg-red-100 { background-color: #fee2e2 !important; color: #991b1b !important; }
            .bg-purple-100 { background-color: #f3e8ff !important; color: #6b21a8 !important; }
            .bg-orange-100 { background-color: #ffedd5 !important; color: #9a3412 !important; }
            .bg-gray-100 { background-color: #f3f4f6 !important; color: #1f2937 !important; }
            
            .text-green-600 { color: #15803d !important; }
            .text-red-600 { color: #b91c1c !important; }
            .text-gray-600 { color: #374151 !important; }
            
            .print-header {
                display: block !important;
                text-align: center !important;
                line-height: 1.3 !important;
            }
            
            .print-header h1 {
                font-size: 18px !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                letter-spacing: 1.5px !important;
                margin: 0 0 3px 0 !important;
                line-height: 1.3 !important;
                color: #1a1a1a !important;
            }
            
            .print-header .sub-header {
                font-size: 10px !important;
                color: #555 !important;
                margin: 0 0 4px 0 !important;
                line-height: 1.3 !important;
            }
            
            .print-header .filter-info {
                font-size: 9px !important;
                color: #444 !important;
                margin: 0 !important;
                background: #f3f4f6 !important;
                padding: 4px 12px !important;
                border: 1px solid #ccc !important;
                border-radius: 4px !important;
                display: inline-block !important;
                line-height: 1.3 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            @page {
                margin: 12mm 10mm 15mm 10mm !important;
                size: landscape !important;
                @bottom-center {
                    content: "Page " counter(page) " of " counter(pages);
                    font-size: 8px;
                    color: #888;
                }
            }
            
            .popup-print-container .popup-table {
                font-size: 10px !important;
            }
            
            .popup-print-container .popup-table th {
                font-size: 9px !important;
                padding: 6px 8px !important;
            }
            
            .popup-print-container .popup-table td {
                font-size: 9px !important;
                padding: 5px 8px !important;
            }
            
            .popup-print-container .grid {
                display: grid !important;
                gap: 0.75rem !important;
            }
            
            .popup-print-container .grid-cols-2 {
                grid-template-columns: 1fr 1fr !important;
            }
            
            .popup-print-container .grid-cols-3 {
                grid-template-columns: 1fr 1fr 1fr !important;
            }
        }
    </style>

    <script>
        // ============== DROPDOWN FUNCTIONS ==============
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.classList.toggle('hidden');
        }
        // Auto-dismiss notifications after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            // Existing notification dismiss
            document.querySelectorAll('.notifButton').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.notif').remove();
                });
            });

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.notif').forEach(notif => {
                    notif.style.transition = 'opacity 0.5s ease';
                    notif.style.opacity = '0';
                    setTimeout(() => notif.remove(), 500);
                });
            }, 5000);
        });

        function resetFilters() {
            window.location.href = '{{ route("inventory.movement.index") }}';
        }
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const container = document.getElementById('dropdownContainer');
            const menu = document.getElementById('dropdownMenu');
            
            if (container && !container.contains(event.target)) {
                if (menu) {
                    menu.classList.add('hidden');
                }
            }
        });

        // Close dropdown on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const menu = document.getElementById('dropdownMenu');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            }
        });

        // ============== CONSOLIDATED ACTION HANDLER ==============
        function handleAction(action, format = null) {
            // Close the main dropdown immediately
            const menu = document.getElementById('dropdownMenu');
            if (menu) {
                menu.classList.add('hidden');
            }
            
            // Get filter values
            const itemCode = document.getElementById('selected_item_code')?.value || '';
            const search = document.getElementById('search')?.value || '';
            const movementType = document.getElementById('movement_type')?.value || '';
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';

            // Build params
            const params = new URLSearchParams();
            if (itemCode) params.append('item_code', itemCode);
            if (search) params.append('search', search);
            if (movementType) params.append('movement_type', movementType);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);
            
            // Handle different actions
            switch(action) {
                case 'print_summary':
                    if (format === 'pdf') {
                        const printUrl = '{{ route("inventory.movement.print") }}?' + params.toString();
                        window.open(printUrl, '_blank');
                    }
                    break;
                    
                case 'export_summary':
                    if (format) params.append('format', format);
                    const exportSummaryUrl = '{{ route("inventory.export") }}?' + params.toString();
                    window.open(exportSummaryUrl, '_blank');
                    break;
                    
                case 'print_all_filtered':
                    if (format === 'pdf') {
                        const allFilteredUrl = '{{ route("inventory.movement.printAll") }}?' + params.toString();
                        window.open(allFilteredUrl, '_blank');
                    }
                    break;
                    
                case 'export_data':
                    if (format) params.append('format', format);
                    const exportDataUrl = '{{ route("inventory.movement.export") }}?' + params.toString();
                    window.open(exportDataUrl, '_blank');
                    break;
                    
                case 'print_view':
                    if (format === 'pdf') {
                        printCurrentView();
                    }
                    break;
                    
                case 'export_current_page':
                    if (format === 'csv') {
                        exportCSV();
                    } else if (format === 'xlsx') {
                        exportExcel();
                    }
                    break;
                    
                default:
                    console.warn('Unknown action:', action);
            }
        }

        // ============== Searchable Dropdown Functions ==============
        function handleSearchKeydown(event) {
            const dropdown = document.getElementById('itemDropdown');
            const visibleItems = Array.from(dropdown.querySelectorAll('.item-option:not([style*="display: none"])'));
            
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                
                const currentFocus = dropdown.querySelector('.bg-blue-100');
                let index = visibleItems.indexOf(currentFocus);
                
                if (event.key === 'ArrowDown') {
                    index = (index + 1) % visibleItems.length;
                } else {
                    index = (index - 1 + visibleItems.length) % visibleItems.length;
                }
                
                dropdown.querySelectorAll('.bg-blue-100').forEach(item => {
                    item.classList.remove('bg-blue-100');
                });
                
                if (visibleItems[index]) {
                    visibleItems[index].classList.add('bg-blue-100');
                    visibleItems[index].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            } else if (event.key === 'Enter') {
                event.preventDefault();
                
                const highlighted = dropdown.querySelector('.bg-blue-100');
                if (highlighted) {
                    highlighted.click();
                } else if (visibleItems.length === 1) {
                    visibleItems[0].click();
                }
            } else if (event.key === 'Escape') {
                dropdown.classList.add('hidden');
            }
        }
        
        function filterItems() {
            const input = document.getElementById('item_code_search');
            const filter = input.value.toLowerCase();
            const dropdown = document.getElementById('itemDropdown');
            const items = dropdown.getElementsByClassName('item-option');
            const noResults = document.getElementById('noResults');
            
            let hasVisible = false;
            
            for (let i = 0; i < items.length; i++) {
                const text = items[i].getAttribute('data-text').toLowerCase();
                const value = items[i].getAttribute('data-value').toLowerCase();
                
                if (text.includes(filter) || value.includes(filter)) {
                    items[i].style.display = '';
                    hasVisible = true;
                } else {
                    items[i].style.display = 'none';
                }
            }
            
            if (noResults) {
                if (!hasVisible && filter !== '') {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }
            
            if (filter !== '' || hasVisible) {
                dropdown.classList.remove('hidden');
            }
        }
        
        function showDropdown() {
            const dropdown = document.getElementById('itemDropdown');
            dropdown.classList.remove('hidden');
            filterItems();
        }
        
        function selectItem(value, text) {
            document.getElementById('item_code_search').value = text;
            document.getElementById('selected_item_code').value = value;
            document.getElementById('itemDropdown').classList.add('hidden');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const selectedValue = document.getElementById('selected_item_code').value;
            if (selectedValue) {
                const dropdown = document.getElementById('itemDropdown');
                const items = dropdown.getElementsByClassName('item-option');
                
                for (let i = 0; i < items.length; i++) {
                    if (items[i].getAttribute('data-value') === selectedValue) {
                        document.getElementById('item_code_search').value = items[i].getAttribute('data-text');
                        break;
                    }
                }
            }
        });
        
        document.addEventListener('click', function(event) {
            const searchInput = document.getElementById('item_code_search');
            const dropdown = document.getElementById('itemDropdown');
            
            if (event.target !== searchInput && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // ============== Print Current View ==============
        function printCurrentView() {
            const tableContainer = document.getElementById('tableContainer');
            if (!tableContainer) {
                alert('No data to print.');
                return;
            }

            const rows = tableContainer.querySelectorAll('tbody tr');
            if (rows.length === 0 || (rows.length === 1 && rows[0].querySelector('td[colspan]'))) {
                alert('No data to print. Please apply filters to get results.');
                return;
            }

            const filters = [];
            const itemCode = document.getElementById('selected_item_code')?.value || '';
            const search = document.getElementById('search')?.value || '';
            const movementType = document.getElementById('movement_type')?.value || '';
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';

            if (itemCode) filters.push('Item: ' + itemCode);
            if (search) filters.push('Search: ' + search);
            if (movementType) filters.push('Type: ' + movementType);
            if (dateFrom) filters.push('From: ' + dateFrom);
            if (dateTo) filters.push('To: ' + dateTo);

            const title = 'INVENTORY MOVEMENT REPORT';
            const now = new Date();
            const generatedOn = now.toLocaleString();
            const totalColumns = 12;

            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                alert('Please allow popups for this site to print the report.');
                return;
            }

            const tableClone = document.getElementById('inventoryTable').cloneNode(true);
            
            tableClone.querySelectorAll('button, a, .no-print').forEach(el => {
                const txt = el.textContent.trim();
                const txtNode = document.createTextNode(txt);
                el.parentNode.replaceChild(txtNode, el);
            });

            tableClone.querySelectorAll('tbody tr').forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 2) {
                    const refCell = cells[1];
                    if (refCell) {
                        refCell.textContent = refCell.textContent.trim();
                    }
                }
            });

            const filterHtml = filters.length ? 
                `<div class="filter-info">${filters.join(' | ')}</div>` : '';

            const thead = tableClone.querySelector('thead');
            const headerRow = document.createElement('tr');
            headerRow.className = 'report-letterhead-row';
            headerRow.innerHTML = `
                <th colspan="${totalColumns}" class="report-letterhead">
                    <div class="print-header">
                        <h1>${title}</h1>
                        <div class="sub-header">Generated on: ${generatedOn}</div>
                        ${filterHtml}
                    </div>
                </th>
            `;
            thead.insertBefore(headerRow, thead.firstChild);

            const contentHtml = `
                <div class="print-container">
                    <div class="report-table-wrapper">
                        ${tableClone.outerHTML}
                    </div>
                </div>
            `;

            printWindow.document.write(`
                <html>
                    <head>
                        <meta charset="utf-8" />
                        <title>${title}</title>
                        <style>
                            * { box-sizing: border-box; margin: 0; padding: 0; }
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                padding: 15px 20px; 
                                color: #1a1a1a; 
                                background: #fff;
                                font-size: 11px;
                                line-height: 1.5;
                            }
                            .print-container { max-width: 1200px; margin: 0 auto; }
                            .report-table-wrapper table thead tr.report-letterhead-row th.report-letterhead {
                                background: #fff !important;
                                padding: 0 0 8px 0 !important;
                                border: none !important;
                                border-bottom: 2px solid #1a1a1a !important;
                            }
                            .print-header { text-align: center; line-height: 1.3; }
                            .print-header h1 {
                                font-size: 18px;
                                font-weight: 700;
                                text-transform: uppercase;
                                letter-spacing: 1.5px;
                                margin: 0 0 3px 0;
                                line-height: 1.3;
                            }
                            .print-header .sub-header {
                                font-size: 10px;
                                color: #555;
                                margin: 0 0 4px 0;
                                line-height: 1.3;
                            }
                            .print-header .filter-info {
                                font-size: 9px;
                                color: #444;
                                margin: 0;
                                background: #f3f4f6;
                                padding: 4px 12px;
                                border: 1px solid #ccc;
                                border-radius: 4px;
                                display: inline-block;
                                line-height: 1.3;
                            }
                            .report-table-wrapper {
                                overflow-x: auto;
                                border: 1px solid #999;
                                border-radius: 0;
                                margin-bottom: 10px;
                            }
                            .report-table-wrapper table {
                                width: 100%;
                                border-collapse: collapse;
                                font-size: 10px;
                                border: 1px solid #999;
                            }
                            .report-table-wrapper table thead { display: table-header-group; }
                            .report-table-wrapper table tbody { display: table-row-group; }
                            .report-table-wrapper table thead th {
                                background: #e5e7eb;
                                padding: 6px 8px;
                                text-align: left;
                                font-weight: 700;
                                text-transform: uppercase;
                                font-size: 9px;
                                border: 1px solid #999;
                                letter-spacing: 0.5px;
                            }
                            .report-table-wrapper table tbody td {
                                padding: 5px 8px;
                                border: 1px solid #ccc;
                                font-size: 9.5px;
                            }
                            .report-table-wrapper table tbody tr { page-break-inside: avoid; }
                            .report-table-wrapper table tbody tr:nth-child(even) { background-color: #f9fafb; }
                            .report-table-wrapper .rounded-full {
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                                font-size: 8px;
                                padding: 2px 8px;
                                font-weight: 600;
                                border-radius: 9999px;
                                display: inline-block;
                            }
                            .bg-green-100 { background-color: #dcfce7; color: #166534; }
                            .bg-blue-100 { background-color: #dbeafe; color: #1e40af; }
                            .bg-red-100 { background-color: #fee2e2; color: #991b1b; }
                            .bg-purple-100 { background-color: #f3e8ff; color: #6b21a8; }
                            .bg-orange-100 { background-color: #ffedd5; color: #9a3412; }
                            .bg-gray-100 { background-color: #f3f4f6; color: #1f2937; }
                            .text-green-600 { color: #15803d; }
                            .text-red-600 { color: #b91c1c; }
                            .text-gray-600 { color: #374151; }
                            .text-right { text-align: right; }
                            .font-medium { font-weight: 500; }
                            @page {
                                margin: 22mm 10mm 15mm 10mm;
                                size: landscape;
                                @bottom-center {
                                    content: "Page " counter(page) " of " counter(pages);
                                    font-size: 8px;
                                    color: #888;
                                }
                            }
                            @media print { body { padding: 10px 15px; } }
                            .report-table-wrapper table th:nth-child(1) { width: 8%; }
                            .report-table-wrapper table th:nth-child(2) { width: 10%; }
                            .report-table-wrapper table th:nth-child(3) { width: 8%; }
                            .report-table-wrapper table th:nth-child(4) { width: 9%; }
                            .report-table-wrapper table th:nth-child(5) { width: 14%; }
                            .report-table-wrapper table th:nth-child(6) { width: 9%; }
                            .report-table-wrapper table th:nth-child(7) { width: 6%; }
                            .report-table-wrapper table th:nth-child(8) { width: 6%; }
                            .report-table-wrapper table th:nth-child(9) { width: 6%; }
                            .report-table-wrapper table th:nth-child(10) { width: 7%; }
                            .report-table-wrapper table th:nth-child(11) { width: 8%; }
                            .report-table-wrapper table th:nth-child(12) { width: 8%; }
                        </style>
                    </head>
                    <body>
                        ${contentHtml}
                    </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
            }, 400);
        }

        function isNoDataRow(row) {
            const emptyCell = row.querySelector('td[colspan="12"]');
            return emptyCell && emptyCell.textContent.trim().length > 0;
        }

        function getExportHeaders() {
            const headers = [];
            const headerCells = document.querySelectorAll('#inventoryTable thead th');
            headerCells.forEach(th => {
                const text = th.textContent.trim();
                if (text === 'Item Code') {
                    headers.push('Item / Description / Detail');
                } else if (text === 'Item Description' || text === 'Detail') {
                    return;
                } else {
                    headers.push(text);
                }
            });
            return headers;
        }

        function extractCellValues(td) {
            if (td.colSpan === 3) {
                return [td.textContent.trim().replace(/\s+/g, ' ')];
            }
            return [td.textContent.trim().replace(/\s+/g, ' ')];
        }

        // ============== Export CSV Function ==============
        function exportCSV() {
            const table = document.getElementById('inventoryTable');
            const rows = table.querySelectorAll('tbody tr');
            
            if (rows.length === 0 || (rows.length === 1 && isNoDataRow(rows[0]))) {
                alert('No data to export.');
                return;
            }
            
            const headers = getExportHeaders();
            
            const dataRows = [];
            rows.forEach(row => {
                if (!isNoDataRow(row)) {
                    const rowData = [];
                    const cells = row.querySelectorAll('td');
                    let combinedItemCell = null;
                    cells.forEach(td => {
                        if (td.colSpan === 3) {
                            combinedItemCell = td.textContent.trim().replace(/\s+/g, ' ');
                        } else {
                            extractCellValues(td).forEach(value => rowData.push(value));
                        }
                    });
                    if (combinedItemCell !== null) {
                        rowData.splice(3, 0, combinedItemCell);
                    }
                    dataRows.push(rowData);
                }
            });
            
            const csvData = [headers, ...dataRows];
            
            const csvContent = csvData.map(row => {
                return row.map(cell => {
                    if (typeof cell === 'string' && (cell.includes(',') || cell.includes('"') || cell.includes('\n'))) {
                        return `"${cell.replace(/"/g, '""')}"`;
                    }
                    return cell;
                }).join(',');
            }).join('\n');
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `inventory_movement_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }

        // ============== Export Excel Function ==============
        function exportExcel() {
            const table = document.getElementById('inventoryTable');
            const rows = table.querySelectorAll('tbody tr');
            
            if (rows.length === 0 || (rows.length === 1 && isNoDataRow(rows[0]))) {
                alert('No data to export.');
                return;
            }
            
            const headers = getExportHeaders();
            
            const dataRows = [];
            rows.forEach(row => {
                if (!isNoDataRow(row)) {
                    const rowData = [];
                    const cells = row.querySelectorAll('td');
                    let combinedItemCell = null;
                    cells.forEach(td => {
                        if (td.colSpan === 3) {
                            combinedItemCell = td.textContent.trim().replace(/\s+/g, ' ');
                        } else {
                            extractCellValues(td).forEach(value => rowData.push(value));
                        }
                    });
                    if (combinedItemCell !== null) {
                        rowData.splice(3, 0, combinedItemCell);
                    }
                    dataRows.push(rowData);
                }
            });
            
            const excelData = [headers, ...dataRows];
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(excelData);
            XLSX.utils.book_append_sheet(wb, ws, 'Inventory Movement');
            
            const fileName = `inventory_movement_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }

        // ============== Popup Functions ==============
        
        function formatMoney(amount) {
            return '₱ ' + Number(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        const popupRoutePattern = "{{ route('inventory.movement.popup', ['id' => '__ID__']) }}";

        function openPopup(transactionId) {
            const modal = document.getElementById('popupModal');
            const content = document.getElementById('popupContent');
            
            modal.classList.remove('hidden');
            content.innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div>
                    <p class="mt-2 text-gray-500">Loading...</p>
                </div>
            `;

            const url = popupRoutePattern.replace('__ID__', transactionId);
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        content.innerHTML = `<div class="text-red-500 text-center py-4">${data.error}</div>`;
                        return;
                    }
                    renderPopupContent(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = `<div class="text-red-500 text-center py-4">Failed to load data. Please try again.</div>`;
                });
        }

        function renderPopupContent(data) {
            const content = document.getElementById('popupContent');
            const isDeliveryNote = data.type === 'DELIVERY NOTE';
            const isStockIn = data.type === 'STOCK IN';
            const isTransformation = data.type === 'TRANSFORMATION';
            const isWaste = data.type === 'WASTE';
            
            let subTotal = 0;
            let shippingCost = Number(data.shipping_cost) || 0;
            let grandTotal = 0;
            
            if (data.items && data.items.length > 0 && !isTransformation && !isWaste) {
                subTotal = data.items.reduce((sum, item) => {
                    const quantity = Number(item.quantity) || 0;
                    const price = Number(item.price || item.sold_price) || 0;
                    return sum + (quantity * price);
                }, 0);
            }
            
            grandTotal = subTotal + shippingCost;
            
            let html = '';
            
            if (isDeliveryNote) {
                html = `
                    <div class="popup-print-container space-y-6">
                        <div class="grid grid-cols-3 gap-4 print-grid-cols-3">
                            <div class="space-y-2">
                                <p><span class="font-semibold">Charge To:</span> ${data.charge_to || 'N/A'}</p>
                                <p><span class="font-semibold">Charge Branch:</span> ${data.charge_branch || 'N/A'}</p>
                                <p>
                                    <span class="font-semibold">Delivered To:</span> 
                                    <input 
                                        type="text" 
                                        id="deliveredToInput" 
                                        class="delivered-to-input border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none px-2 py-1 w-48 print-border-bottom"
                                        placeholder="Enter recipient name"
                                        value="${data.delivered_to || ''}"
                                    >
                                </p>
                                <p><span class="font-semibold">Dispatch Date:</span> ${data.dispatch_date || 'N/A'}</p>                                
                            </div>
                            <div class="space-y-2">
                                <p><span class="font-semibold">Reference:</span> POS - ${data.reference || 'N/A'}</p>
                                <p><span class="font-semibold">Our Order No.:</span> ${data.reference || 'N/A'}</p>
                                <p><span class="font-semibold">Customer Order Ref:</span> </p>
                                <p><span class="font-semibold">Due Date:</span> </p>
                            </div>
                            <div class="space-y-2">
                                <p>
                                    <span class="font-semibold">Shipping Company:</span> 
                                    <input 
                                        type="text" 
                                        id="shippingCompanyInput" 
                                        class="shipping-company-input border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none px-2 py-1 w-48 print-border-bottom"
                                        placeholder="Enter shipping company"
                                        value="${data.shipping_company || ''}"
                                    >
                                </p>                                                                
                                <p><span class="font-semibold">Sales Type:</span> ${data.sales_type || 'N/A'}</p>
                            </div>                            
                        </div>

                        <div class="popup-table-wrapper">
                            <table class="popup-table min-w-full divide-y divide-gray-200 text-sm print-table">
                                <thead class="bg-gray-50 print-bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Description</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    ${data.items && data.items.length > 0 ? data.items.map(item => {
                                        const quantity = Number(item.quantity) || 0;
                                        const price = Number(item.price || item.sold_price) || 0;
                                        const total = quantity * price;
                                        
                                        return `
                                            <tr>
                                                <td class="px-4 py-2">${item.item_code || 'N/A'}</td>
                                                <td class="px-4 py-2">${item.item_description || 'N/A'}</td>
                                                <td class="px-4 py-2 text-right">${quantity.toFixed(3)}</td>
                                                <td class="px-4 py-2">${item.unit || 'pcs'}</td>
                                                <td class="px-4 py-2 text-right">${formatMoney(price)}</td>
                                                <td class="px-4 py-2 text-right">${formatMoney(total)}</td>
                                            </tr>                                          
                                        `;
                                        
                                    }).join('') : `
                                        <tr>
                                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">No items found</td>
                                        </tr>
                                    `}
                                </tbody>
                                <tfoot class="bg-gray-100 print-bg-gray-100">
                                    <tr class="font-bold">
                                        <td colspan="5" class="px-4 py-2 text-right">SUB TOTAL:</td>
                                        <td class="px-4 py-2 text-right">${formatMoney(subTotal)}</td>
                                    </tr>                                    
                                    <tr class="font-bold">
                                        <td colspan="5" class="px-4 py-2 text-right">SHIPPING:</td>
                                        <td class="px-4 py-2 text-right">${formatMoney(shippingCost)}</td>
                                    </tr>                                    
                                    <tr class="font-bold">
                                        <td colspan="5" class="px-4 py-2 text-right">TOTAL VALUE:</td>
                                        <td class="px-4 py-2 text-right">${formatMoney(grandTotal)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="print-signatures">
                            <div class="grid grid-cols-4 gap-4 pt-4 print-grid-cols-4">
                                <div>
                                    <p class="font-semibold text-center">Prepared By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.prepared_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Check By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.checked_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Received By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.received_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Approved By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.approved_by || ''}</p>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-200 no-print">
                                <button onclick="printPopup()" class="px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print / Save
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else if(isStockIn) {
                html = `
                    <div class="popup-print-container space-y-4">
                        <div class="grid grid-cols-2 gap-4 print-grid-cols-2">
                            <div>
                                <p><span class="font-semibold">Type:</span> ${data.type || 'N/A'}</p>
                                <p><span class="font-semibold">Reference:</span> ${data.reference || 'N/A'}</p>
                                <p><span class="font-semibold">Date:</span> ${data.dispatch_date || 'N/A'}</p>
                            </div>
                            <div>
                                <p><span class="font-semibold">Supplier:</span> ${data.detail || 'N/A'}</p>
                                <p><span class="font-semibold">User:</span> ${data.user_name || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="popup-table-wrapper">
                            <table class="popup-table min-w-full divide-y divide-gray-200 text-sm print-table">
                                <thead class="bg-gray-50 print-bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Item Code</th>
                                        <th class="px-4 py-2 text-left">Item Description</th>
                                        <th class="px-4 py-2 text-right">Qty In</th>
                                        <th class="px-4 py-2 text-left">Unit</th>
                                        <th class="px-4 py-2 text-right">Unit Price</th>
                                        <th class="px-4 py-2 text-right">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.items && data.items.length > 0 ? data.items.map(item => {
                                        const quantityIn = Number(item.quantity_in || item.qty_in || 0);
                                        const unitCost = Number(item.sold_price || 0);
                                        const totalCost = quantityIn * unitCost;
                                        
                                        return `
                                            <tr>
                                                <td class="px-4 py-2">${item.item_code || 'N/A'}</td>
                                                <td class="px-4 py-2">${item.item_description || 'N/A'}</td>
                                                <td class="px-4 py-2 text-right">${quantityIn.toFixed(3)}</td>
                                                <td class="px-4 py-2">${item.unit || 'pcs'}</td>
                                                <td class="px-4 py-2 text-right">${formatMoney(unitCost)}</td>
                                                <td class="px-4 py-2 text-right">${formatMoney(totalCost)}</td>
                                            </tr>
                                        `;
                                    }).join('') : `
                                        <tr>
                                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">No items found</td>
                                        </tr>
                                    `}
                                </tbody>
                                <tfoot class="bg-gray-100 print-bg-gray-100">
                                    ${data.items && data.items.length > 0 ? `
                                        <tr class="font-bold">
                                            <td colspan="5" class="px-4 py-2 text-right">TOTAL:</td>
                                            <td class="px-4 py-2 text-right">${formatMoney(
                                                data.items.reduce((sum, item) => {
                                                    const cost = Number(item.standard_cost || item.cost || item.unit_cost || 0);
                                                    return sum + cost;
                                                }, 0)
                                            )}</td>
                                        </tr>
                                    ` : ''}
                                </tfoot>
                            </table>
                        </div>
                        <div class="print-signatures">
                            <div class="grid grid-cols-4 gap-4 pt-4 print-grid-cols-4">
                                <div>
                                    <p class="font-semibold text-center">Prepared By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.prepared_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Check By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.checked_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Received By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.received_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Approved By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.approved_by || ''}</p>
                                </div>
                            </div>                        
                            <div class="flex justify-end pt-4 border-t border-gray-200 no-print">
                                <button onclick="printPopup()" class="px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print / Save
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }else if (isTransformation) {
                // Get status from detail
                const mainDetail = data.detail || '';
                const isForApproval = mainDetail.includes('For Approval');
                const isApproved = mainDetail.includes('Approved');
                const isReverted = mainDetail.includes('Reverted');
                
                // Filter items based on status
                let sourceItems = [];
                let outputItems = [];
                let wasteItems = [];
                
                if (isForApproval) {
                    // For Approval: Only show items that are "For Approval"
                    sourceItems = (data.items || []).filter(item => {
                        const qtyOut = parseFloat(item.qty_out) || 0;
                        const itemType = (item.type || '').toLowerCase();
                        return qtyOut > 0 && itemType !== 'waste';
                    });
                    
                    wasteItems = (data.items || []).filter(item => {
                        const itemType = (item.type || '').toLowerCase();
                        const qtyIn = parseFloat(item.qty_in) || 0;
                        // For scrap, only show if it has "For Approval" in description
                        const desc = (item.detail || '').toLowerCase();
                        return (itemType === 'waste' || (qtyIn > 0 && itemType === 'waste')) && 
                            desc.includes('for approval');
                    });
                    
                    outputItems = [];
                    
                } else if (isApproved) {
                    // Approved: Only show scrap that has "Approved" in description
                    sourceItems = (data.items || []).filter(item => {
                        const qtyOut = parseFloat(item.qty_out) || 0;
                        const itemType = (item.type || '').toLowerCase();
                        return qtyOut > 0 && itemType !== 'waste';
                    });
                    
                    outputItems = (data.items || []).filter(item => {
                        const qtyIn = parseFloat(item.qty_in) || 0;
                        const itemType = (item.type || '').toLowerCase();
                        return qtyIn > 0 && itemType !== 'waste';
                    });
                    
                    wasteItems = (data.items || []).filter(item => {
                        const itemType = (item.type || '').toLowerCase();
                        const qtyIn = parseFloat(item.qty_in) || 0;
                        const qtyOut = parseFloat(item.qty_out) || 0;
                        const desc = (item.detail || '').toLowerCase();
                        // Only show scrap that has "Approved" in description
                        return (itemType === 'waste' || (qtyIn > 0 && itemType === 'waste') || (qtyOut > 0 && itemType === 'waste')) && 
                            desc.includes('approved');
                    });
                    
                } else if (isReverted) {
                    // Reverted: Show only scrap that has "Reverted" in description
                    sourceItems = (data.items || []).filter(item => {
                        const qtyOut = parseFloat(item.qty_out) || 0;
                        const itemType = (item.type || '').toLowerCase();
                        return qtyOut > 0 && itemType !== 'waste';
                    });
                    
                    outputItems = (data.items || []).filter(item => {
                        const qtyIn = parseFloat(item.qty_in) || 0;
                        const itemType = (item.type || '').toLowerCase();
                        return qtyIn > 0 && itemType !== 'waste';
                    });
                    
                    wasteItems = (data.items || []).filter(item => {
                        const itemType = (item.type || '').toLowerCase();
                        const qtyIn = parseFloat(item.qty_in) || 0;
                        const qtyOut = parseFloat(item.qty_out) || 0;
                        const desc = (item.detail || '').toLowerCase();
                        // Only show scrap that has "Reverted" in description
                        return (itemType === 'waste' || (qtyIn > 0 && itemType === 'waste') || (qtyOut > 0 && itemType === 'waste')) && 
                            desc.includes('reverted');
                    });
                    
                } else {
                    // Default fallback - show all items
                    sourceItems = (data.items || []).filter(item => {
                        const qtyOut = parseFloat(item.qty_out) || 0;
                        const itemType = (item.type || '').toLowerCase();
                        return qtyOut > 0 && itemType !== 'waste';
                    });
                    
                    outputItems = (data.items || []).filter(item => {
                        const qtyIn = parseFloat(item.qty_in) || 0;
                        const itemType = (item.type || '').toLowerCase();
                        return qtyIn > 0 && itemType !== 'waste';
                    });
                    
                    wasteItems = (data.items || []).filter(item => {
                        const itemType = (item.type || '').toLowerCase();
                        const qtyIn = parseFloat(item.qty_in) || 0;
                        const qtyOut = parseFloat(item.qty_out) || 0;
                        const desc = (item.detail || '').toLowerCase();
                        // Only show scrap that has "Approved" in description for default
                        return (itemType === 'waste' || (qtyIn > 0 && itemType === 'waste') || (qtyOut > 0 && itemType === 'waste')) && 
                            desc.includes('approved');
                    });
                }
                
                // Determine source label based on status
                let sourceLabel = 'SOURCE (Deducted)';
                let sourceLabelColor = 'text-red-700';
                let sourceBgColor = 'bg-red-50';
                let sourceBorderColor = 'border-red-200';
                let sourceQtyColor = 'text-red-600';
                
                if (isReverted) {
                    sourceLabel = 'REVERTED SOURCE';
                    sourceLabelColor = 'text-orange-700';
                    sourceBgColor = 'bg-orange-50';
                    sourceBorderColor = 'border-orange-200';
                    sourceQtyColor = 'text-orange-600';
                } else if (isForApproval) {
                    sourceLabel = 'SOURCE (For Approval)';
                    sourceLabelColor = 'text-yellow-700';
                    sourceBgColor = 'bg-yellow-50';
                    sourceBorderColor = 'border-yellow-200';
                    sourceQtyColor = 'text-yellow-600';
                }
                
                // Determine output label based on status
                let outputLabel = 'OUTPUT (Added)';
                let outputLabelColor = 'text-green-700';
                let outputBgColor = 'bg-green-50';
                let outputBorderColor = 'border-green-200';
                let outputQtyColor = 'text-green-600';
                
                if (isReverted) {
                    outputLabel = 'REVERTED OUTPUT';
                    outputLabelColor = 'text-orange-700';
                    outputBgColor = 'bg-orange-50';
                    outputBorderColor = 'border-orange-200';
                    outputQtyColor = 'text-orange-600';
                } else if (isForApproval) {
                    outputLabel = 'OUTPUT (For Approval)';
                    outputLabelColor = 'text-yellow-700';
                    outputBgColor = 'bg-yellow-50';
                    outputBorderColor = 'border-yellow-200';
                    outputQtyColor = 'text-yellow-600';
                }
                
                let statusBadge = '';
                if (isForApproval) {
                    statusBadge = '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 ml-2">For Approval</span>';
                } else if (isApproved) {
                    statusBadge = '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 ml-2">Approved</span>';
                } else if (isReverted) {
                    statusBadge = '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 ml-2">Reverted</span>';
                }
                
                html = `
                    <div class="popup-print-container space-y-4">
                        <!-- Header Info -->
                        <div class="grid grid-cols-2 gap-4 print-grid-cols-2">
                            <div>
                                <p class="flex items-center"><span class="font-semibold">Type:</span> <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 ml-2">TRANSFORMATION</span>${statusBadge}</p>
                                <p><span class="font-semibold">Reference:</span> ${data.reference || 'N/A'}</p>
                                <p><span class="font-semibold">Date:</span> ${data.dispatch_date || 'N/A'}</p>
                            </div>
                            <div>
                                <p><span class="font-semibold">Detail:</span> ${mainDetail}</p>
                                <p><span class="font-semibold">User:</span> ${data.user_name || 'N/A'}</p>
                            </div>
                        </div>

                        <!-- Source Item Section -->
                        ${sourceItems.length > 0 ? `
                        <div>
                            <h3 class="text-sm font-bold ${sourceLabelColor} mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                ${sourceLabel} (${sourceItems.length} item${sourceItems.length > 1 ? 's' : ''})
                            </h3>
                            <div class="popup-table-wrapper overflow-hidden border ${sourceBorderColor} rounded-lg">
                                <table class="popup-table min-w-full divide-y divide-gray-200 text-sm print-table">
                                    <thead class="${sourceBgColor} print-bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium ${sourceLabelColor} uppercase">Item Code</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium ${sourceLabelColor} uppercase">Item Description</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium ${sourceLabelColor} uppercase">Qty Out</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium ${sourceLabelColor} uppercase">Unit</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium ${sourceLabelColor} uppercase">Before</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium ${sourceLabelColor} uppercase">After</th>
                                            ${isForApproval ? `<th class="px-4 py-2 text-left text-xs font-medium ${sourceLabelColor} uppercase">Status</th>` : ''}
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        ${sourceItems.map(item => {
                                            const qtyOut = parseFloat(item.qty_out) || 0;
                                            const before = parseFloat(item.quantity_before) || 0;
                                            const after = parseFloat(item.remaining) || 0;
                                            const brandText = item.brand_name && item.brand_name !== 'N/A' ? item.brand_name + ' - ' : '';
                                            const itemStatus = isForApproval ? 'For Approval' : (isApproved ? 'Approved' : (isReverted ? 'Reverted' : ''));
                                            
                                            return `
                                                <tr>
                                                    <td class="px-4 py-2 font-mono text-xs">${item.item_code || 'N/A'}</td>
                                                    <td class="px-4 py-2 font-medium">${brandText}${item.item_name || item.item_description || 'N/A'}</td>
                                                    <td class="px-4 py-2 text-right ${sourceQtyColor} font-bold">-${qtyOut.toFixed(3)}</td>
                                                    <td class="px-4 py-2">${item.unit || 'N/A'}</td>
                                                    <td class="px-4 py-2 text-right">${before.toFixed(3)}</td>
                                                    <td class="px-4 py-2 text-right">${after.toFixed(3)}</td>
                                                    ${isForApproval ? `<td class="px-4 py-2"><span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">For Approval</span></td>` : ''}
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        ` : ''}
                        
                        <!-- Waste Section -->
                        ${wasteItems.length > 0 ? `
                        <div>
                            <h3 class="text-sm font-bold text-orange-700 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                SCRAP (${wasteItems.length} item${wasteItems.length > 1 ? 's' : ''})
                                ${isForApproval ? '<span class="text-xs font-normal text-yellow-600 ml-2">(For Approval)</span>' : ''}
                                ${isApproved ? '<span class="text-xs font-normal text-green-600 ml-2">(Approved)</span>' : ''}
                                ${isReverted ? '<span class="text-xs font-normal text-red-600 ml-2">(Reverted)</span>' : ''}
                            </h3>
                            <div class="popup-table-wrapper overflow-hidden border border-orange-200 rounded-lg">
                                <table class="popup-table min-w-full divide-y divide-gray-200 text-sm print-table">
                                    <thead class="bg-orange-50 print-bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-orange-700 uppercase">Item Code</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-orange-700 uppercase">Item Description</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-orange-700 uppercase">Scrap Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-orange-700 uppercase">Unit</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-orange-700 uppercase">Description</th>
                                            ${isForApproval || isApproved || isReverted ? `<th class="px-4 py-2 text-left text-xs font-medium text-orange-700 uppercase">Status</th>` : ''}
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        ${wasteItems.map(item => {
                                            const wasteQty = parseFloat(item.qty_in) || parseFloat(item.remaining) || 0;
                                            const brandText = item.brand_name && item.brand_name !== 'N/A' ? item.brand_name + ' - ' : '';
                                            const wasteDesc = item.detail || item.remarks || 'N/A';
                                            
                                            let statusText = '';
                                            let statusColor = '';
                                            if (isForApproval) {
                                                statusText = 'For Approval';
                                                statusColor = 'bg-yellow-100 text-yellow-800';
                                            } else if (isApproved) {
                                                statusText = 'Approved';
                                                statusColor = 'bg-green-100 text-green-800';
                                            } else if (isReverted) {
                                                statusText = 'Reverted';
                                                statusColor = 'bg-red-100 text-red-800';
                                            }
                                            
                                            return `
                                                <tr>
                                                    <td class="px-4 py-2 font-mono text-xs">${item.item_code || 'N/A'}</td>
                                                    <td class="px-4 py-2 font-medium">${brandText}${item.item_name || item.item_description || 'N/A'}</td>
                                                    <td class="px-4 py-2 text-right text-orange-600 font-bold">${wasteQty.toFixed(3)}</td>
                                                    <td class="px-4 py-2">${item.unit || 'N/A'}</td>
                                                    <td class="px-4 py-2 text-xs text-gray-600">${wasteDesc}</td>
                                                    ${isForApproval || isApproved || isReverted ? `<td class="px-4 py-2"><span class="px-2 py-1 text-xs font-medium rounded-full ${statusColor}">${statusText}</span></td>` : ''}
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        ` : ''}
                        
                        <!-- Output Items Section -->
                        ${outputItems.length > 0 ? `
                        <div>
                            <h3 class="text-sm font-bold ${outputLabelColor} mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                                ${outputLabel} (${outputItems.length} item${outputItems.length > 1 ? 's' : ''})
                                ${isForApproval ? '<span class="text-xs font-normal text-yellow-600 ml-2">(For Approval)</span>' : ''}
                            </h3>
                            <div class="popup-table-wrapper overflow-hidden border ${outputBorderColor} rounded-lg">
                                <table class="popup-table min-w-full divide-y divide-gray-200 text-sm print-table">
                                    <thead class="${outputBgColor} print-bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium ${outputLabelColor} uppercase">Item Code</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium ${outputLabelColor} uppercase">Item Description</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium ${outputLabelColor} uppercase">Qty In</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium ${outputLabelColor} uppercase">Unit</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium ${outputLabelColor} uppercase">Before</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium ${outputLabelColor} uppercase">After</th>
                                            ${isForApproval ? `<th class="px-4 py-2 text-left text-xs font-medium ${outputLabelColor} uppercase">Status</th>` : ''}
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        ${outputItems.map(item => {
                                            const qtyIn = parseFloat(item.qty_in) || 0;
                                            const before = parseFloat(item.previous_qty || item.quantity_before) || 0;
                                            const after = parseFloat(item.remaining || item.quantity_after) || 0;
                                            const brandText = item.brand_name && item.brand_name !== 'N/A' ? item.brand_name + ' - ' : '';
                                            
                                            return `
                                                <tr>
                                                    <td class="px-4 py-2 font-mono text-xs">${item.item_code || 'N/A'}</td>
                                                    <td class="px-4 py-2 font-medium">${brandText}${item.item_name || item.item_description || 'N/A'}</td>
                                                    <td class="px-4 py-2 text-right ${outputQtyColor} font-bold">+${qtyIn.toFixed(3)}</td>
                                                    <td class="px-4 py-2">${item.unit || 'N/A'}</td>
                                                    <td class="px-4 py-2 text-right">${before.toFixed(3)}</td>
                                                    <td class="px-4 py-2 text-right">${after.toFixed(3)}</td>
                                                    ${isForApproval ? `<td class="px-4 py-2"><span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">For Approval</span></td>` : ''}
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        ` : ''}

                        <!-- If no items at all -->
                        ${sourceItems.length === 0 && outputItems.length === 0 && wasteItems.length === 0 ? `
                        <div class="text-center py-8 text-gray-500">
                            <p>No items found for this transformation.</p>
                        </div>
                        ` : ''}

                        <!-- Footer -->
                        <div class="print-signatures">
                            <div class="grid grid-cols-4 gap-4 pt-4 print-grid-cols-4">
                                <div>
                                    <p class="font-semibold text-center">Prepared By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.prepared_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Check By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.checked_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Received By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.received_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Approved By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.approved_by || ''}</p>
                                </div>
                            </div>                        
                            <div class="flex justify-end pt-4 border-t border-gray-200 no-print">
                                <button onclick="printPopup()" class="px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print / Save
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }else if (isWaste) {
                html = `
                    <div class="popup-print-container space-y-4">
                        <div class="grid grid-cols-2 gap-4 print-grid-cols-2">
                            <div>
                                <p><span class="font-semibold">Type:</span> <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">WASTE</span></p>
                                <p><span class="font-semibold">Reference:</span> ${data.reference || 'N/A'}</p>
                                <p><span class="font-semibold">Date:</span> ${data.dispatch_date || 'N/A'}</p>
                            </div>
                            <div>
                                <p><span class="font-semibold">Detail:</span> ${data.detail || 'N/A'}</p>
                                <p><span class="font-semibold">User:</span> ${data.user_name || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="popup-table-wrapper">
                            <table class="popup-table min-w-full divide-y divide-gray-200 text-sm print-table">
                                <thead class="bg-gray-50 print-bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Item Code</th>
                                        <th class="px-4 py-2 text-left">Item Description</th>
                                        <th class="px-4 py-2 text-right">Qty</th>
                                        <th class="px-4 py-2 text-left">Unit</th>
                                        <th class="px-4 py-2 text-left">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.items && data.items.length > 0 ? data.items.map(item => {
                                        const quantity = Number(item.quantity || item.qty_out || 0);
                                        const reason = item.detail || item.remarks || 'N/A';
                                        const brandText = item.brand_name && item.brand_name !== 'N/A' ? item.brand_name + ' - ' : '';
                                        
                                        return `
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-xs">${item.item_code || 'N/A'}</td>
                                                <td class="px-4 py-2">${brandText}${item.item_name || item.item_description || 'N/A'}</td>
                                                <td class="px-4 py-2 text-right text-orange-600 font-bold">${quantity.toFixed(3)}</td>
                                                <td class="px-4 py-2">${item.unit || 'N/A'}</td>
                                                <td class="px-4 py-2 text-xs text-gray-600">${reason}</td>
                                            </tr>
                                        `;
                                    }).join('') : `
                                        <tr>
                                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No items found</td>
                                        </tr>
                                    `}
                                </tbody>
                            </table>
                        </div>
                        <div class="print-signatures">
                            <div class="grid grid-cols-4 gap-4 pt-4 print-grid-cols-4">
                                <div>
                                    <p class="font-semibold text-center">Prepared By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.prepared_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Check By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.checked_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Received By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.received_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Approved By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.approved_by || ''}</p>
                                </div>
                            </div>                        
                            <div class="flex justify-end pt-4 border-t border-gray-200 no-print">
                                <button onclick="printPopup()" class="px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print / Save
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Default for other transaction types
                html = `
                    <div class="popup-print-container space-y-4">
                        <div class="grid grid-cols-2 gap-4 print-grid-cols-2">
                            <div>
                                <p><span class="font-semibold">Type:</span> ${data.type || 'N/A'}</p>
                                <p><span class="font-semibold">Reference:</span> ${data.reference || 'N/A'}</p>
                                <p><span class="font-semibold">Date:</span> ${data.dispatch_date || 'N/A'}</p>
                            </div>
                            <div>
                                <p><span class="font-semibold">Detail:</span> ${data.detail || 'N/A'}</p>
                                <p><span class="font-semibold">User:</span> ${data.user_name || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="popup-table-wrapper">
                            <table class="popup-table min-w-full divide-y divide-gray-200 text-sm print-table">
                                <thead class="bg-gray-50 print-bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Item Code</th>
                                        <th class="px-4 py-2 text-left">Item Description</th>
                                        <th class="px-4 py-2 text-right">Quantity</th>
                                        <th class="px-4 py-2 text-left">Unit</th>
                                        <th class="px-4 py-2 text-right">Unit Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.items && data.items.length > 0 ? data.items.map(item => {
                                        const quantity = Number(item.quantity) || 0;                                       
                                        return `
                                            <tr>
                                                <td class="px-4 py-2">${item.item_code || 'N/A'}</td>
                                                <td class="px-4 py-2">${item.item_description || 'N/A'}</td>
                                                <td class="px-4 py-2 text-right">${quantity.toFixed(3)}</td>
                                                <td class="px-4 py-2">${item.unit || 'N/A'}</td>
                                                <td class="px-4 py-2 text-right">${formatMoney(Number(item.cost || 0))}</td>
                                            </tr>
                                        `;
                                    }).join('') : `
                                        <tr>
                                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">No items found</td>
                                        </tr>
                                    `}
                                </tbody>
                            </table>
                        </div>
                        <div class="print-signatures">
                            <div class="grid grid-cols-4 gap-4 pt-4 print-grid-cols-4">
                                <div>
                                    <p class="font-semibold text-center">Prepared By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.prepared_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Check By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.checked_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Received By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.received_by || ''}</p>
                                </div>
                                <div>
                                    <p class="font-semibold text-center">Approved By:</p>
                                    <p class="mt-6 border-t border-gray-300 pt-1">${data.approved_by || ''}</p>
                                </div>
                            </div>                        
                            <div class="flex justify-end pt-4 border-t border-gray-200 no-print">
                                <button onclick="printPopup()" class="px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print / Save
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            content.innerHTML = html;
            
            document.getElementById('popupTitle').textContent = 
                isDeliveryNote ? `Dispatch Note #${data.reference || ''}` : 
                isTransformation ? `Transformation #${data.reference || ''}` :
                isWaste ? `Waste #${data.reference || ''}` :
                `${data.type || 'Transaction'} #${data.reference || ''}`;
        }
        
        function closePopup() {
            document.getElementById('popupModal').classList.add('hidden');
        }

        function printPopup() {
            const deliveredToInput = document.getElementById('deliveredToInput');
            const deliveredToValue = deliveredToInput ? deliveredToInput.value : '';
            const shippingCompanyInput = document.getElementById('shippingCompanyInput');
            const shippingCompanyValue = shippingCompanyInput ? shippingCompanyInput.value : '';
            
            const printContents = document.getElementById('popupContent').innerHTML;
            const originalTitle = document.getElementById('popupTitle').textContent;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>${originalTitle}</title>
                        <style>
                            /* Reset & Base */
                            * { margin: 0; padding: 0; box-sizing: border-box; }
                            body { 
                                font-family: Arial, Helvetica, sans-serif; 
                                padding: 20px; 
                                color: #333; 
                                background: #fff;
                                font-size: 12px;
                                line-height: 1.4;
                            }
                            
                            /* Container */
                            .popup-print-container {
                                max-width: 100%;
                                margin: 0 auto;
                            }
                            
                            /* Grid Layouts */
                            .grid { display: grid; gap: 1rem; }
                            .grid-cols-2 { grid-template-columns: 1fr 1fr; }
                            .grid-cols-3 { grid-template-columns: 1fr 1fr 1fr; }
                            .grid-cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
                            .gap-4 { gap: 1rem; }
                            .space-y-2 > * + * { margin-top: 0.5rem; }
                            .space-y-4 > * + * { margin-top: 1rem; }
                            .space-y-6 > * + * { margin-top: 1.5rem; }
                            
                            /* Tables */
                            .popup-table-wrapper {
                                overflow: visible !important;
                                border: 1px solid #e5e7eb;
                                border-radius: 0.5rem;
                                margin: 0.5rem 0;
                            }
                            
                            .popup-table {
                                width: 100%;
                                border-collapse: collapse;
                                font-size: 11px;
                                table-layout: auto;
                            }
                            
                            .popup-table th,
                            .popup-table td {
                                padding: 6px 10px;
                                text-align: left;
                                border-bottom: 1px solid #e5e7eb;
                            }
                            
                            .popup-table thead th {
                                background-color: #f3f4f6;
                                font-weight: 600;
                                text-transform: uppercase;
                                font-size: 10px;
                                color: #4b5563;
                                position: sticky;
                                top: 0;
                                z-index: 10;
                            }
                            
                            .popup-table tfoot td {
                                background-color: #f3f4f6;
                                font-weight: 700;
                            }
                            
                            .popup-table .text-right { text-align: right; }
                            .popup-table .text-left { text-align: left; }
                            .popup-table .font-bold { font-weight: 700; }
                            .popup-table .font-mono { font-family: monospace; }
                            
                            /* Print Signatures */
                            .print-signatures {
                                margin-top: 1rem;
                                padding-top: 1rem;
                                border-top: 1px solid #d1d5db;
                            }
                            
                            .print-signatures .border-t {
                                border-top: 1px solid #d1d5db;
                            }
                            
                            .print-signatures .mt-6 { margin-top: 1.5rem; }
                            .print-signatures .pt-1 { padding-top: 0.25rem; }
                            
                            /* Print border bottom for inputs */
                            .print-border-bottom {
                                border-bottom: 1px solid #d1d5db !important;
                            }
                            
                            /* No print */
                            .no-print { display: none !important; }
                            
                            /* Status badges */
                            .rounded-full { border-radius: 9999px; }
                            .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
                            .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
                            .text-xs { font-size: 0.75rem; }
                            .text-sm { font-size: 0.875rem; }
                            .font-medium { font-weight: 500; }
                            .font-semibold { font-weight: 600; }
                            .font-bold { font-weight: 700; }
                            
                            .bg-yellow-100 { background-color: #fef9c3; }
                            .text-yellow-800 { color: #854d0e; }
                            .bg-green-100 { background-color: #dcfce7; }
                            .text-green-800 { color: #166534; }
                            .bg-red-100 { background-color: #fee2e2; }
                            .text-red-800 { color: #991b1b; }
                            .bg-purple-100 { background-color: #f3e8ff; }
                            .text-purple-800 { color: #6b21a8; }
                            .bg-orange-100 { background-color: #ffedd5; }
                            .text-orange-800 { color: #9a3412; }
                            .bg-gray-100 { background-color: #f3f4f6; }
                            .text-gray-800 { color: #1f2937; }
                            
                            .text-red-700 { color: #b91c1c; }
                            .text-green-700 { color: #15803d; }
                            .text-orange-700 { color: #c2410c; }
                            .text-red-600 { color: #dc2626; }
                            .text-green-600 { color: #16a34a; }
                            .text-orange-600 { color: #ea580c; }
                            .text-gray-600 { color: #4b5563; }
                            
                            .bg-red-50 { background-color: #fef2f2; }
                            .bg-green-50 { background-color: #f0fdf4; }
                            .bg-orange-50 { background-color: #fff7ed; }
                            
                            .border-red-200 { border-color: #fecaca; }
                            .border-green-200 { border-color: #bbf7d0; }
                            .border-orange-200 { border-color: #fed7aa; }
                            
                            .border { border: 1px solid #e5e7eb; }
                            .border-t { border-top: 1px solid #d1d5db; }
                            .rounded-lg { border-radius: 0.5rem; }
                            
                            .ml-2 { margin-left: 0.5rem; }
                            .mb-2 { margin-bottom: 0.5rem; }
                            
                            .flex { display: flex; }
                            .items-center { align-items: center; }
                            .gap-2 { gap: 0.5rem; }
                            
                            .w-4 { width: 1rem; }
                            .h-4 { height: 1rem; }
                            
                            /* Print-specific overrides */
                            @media print {
                                body { 
                                    padding: 10px; 
                                    font-size: 10px;
                                    background: #fff !important;
                                }
                                
                                .popup-print-container {
                                    max-width: 100%;
                                }
                                
                                .popup-table-wrapper {
                                    overflow: visible !important;
                                    border: 1px solid #e5e7eb !important;
                                    page-break-inside: avoid;
                                }
                                
                                .popup-table {
                                    font-size: 9px;
                                    page-break-inside: avoid;
                                }
                                
                                .popup-table th,
                                .popup-table td {
                                    padding: 4px 6px;
                                    border-bottom: 1px solid #e5e7eb;
                                }
                                
                                .popup-table thead th {
                                    background-color: #e5e7eb !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                
                                .popup-table tfoot td {
                                    background-color: #e5e7eb !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                
                                .print-bg-gray-100 {
                                    background-color: #e5e7eb !important;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                
                                .print-grid-cols-2 { grid-template-columns: 1fr 1fr; }
                                .print-grid-cols-3 { grid-template-columns: 1fr 1fr 1fr; }
                                .print-grid-cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
                                
                                .print-border-bottom {
                                    border-bottom: 1px solid #d1d5db !important;
                                }
                                
                                .no-print { display: none !important; }
                                
                                /* Ensure signatures print */
                                .print-signatures .border-t {
                                    border-top: 1px solid #d1d5db !important;
                                }
                                
                                /* Page break control */
                                .popup-print-container {
                                    page-break-after: avoid;
                                }
                                
                                /* Color badges */
                                .bg-yellow-100, .bg-green-100, .bg-red-100, 
                                .bg-purple-100, .bg-orange-100, .bg-gray-100,
                                .bg-red-50, .bg-green-50, .bg-orange-50 {
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <h1 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; text-align: center;">${originalTitle}</h1>
                        ${printContents.replace(/<input[^>]*id="deliveredToInput"[^>]*>/g, `<span class="delivered-to-input" style="border-bottom: 1px solid #d1d5db; padding: 2px 8px; min-width: 150px; display: inline-block;">${deliveredToValue || '________________'}</span>`).replace(/<input[^>]*id="shippingCompanyInput"[^>]*>/g, `<span class="shipping-company-input" style="border-bottom: 1px solid #d1d5db; padding: 2px 8px; min-width: 150px; display: inline-block;">${shippingCompanyValue || '________________'}</span>`)}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePopup();
            }
        });

        document.addEventListener('click', function(event) {
            const modal = document.getElementById('popupModal');
            if (event.target === modal || event.target === modal.querySelector('.fixed.inset-0')) {
                closePopup();
            }
        });
    </script>
</x-app-layout>