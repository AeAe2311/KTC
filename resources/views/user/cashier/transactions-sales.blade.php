<x-app-layout>
    <style>
        /* Each transaction group gets a clean border - using box-shadow instead of border */
        .transaction-group {
            display: table-row-group !important;
            box-shadow: 0 0 0 2px #830505, 0 2px 4px rgba(0,0,0,0.1) !important;
            border-radius: 6px !important;
            margin-bottom: 12px !important;
            background: white !important;
        }
        
        /* Remove internal borders between rows */
        .transaction-group .resultRow td {
            border-bottom: 1px solid #ffffff !important;
        }
        
        .transaction-group .resultRow:last-child td {
            border-bottom: none !important;
        }
        
        .transaction-group .total-row td {
            border-top: 2px dashed #8f0707 !important;
        }
        
        /* Alternating row colors */
        .row-colored td {
            background-color: #e8f4f8 !important;
        }
        
        .row-white td {
            background-color: #ffffff !important;
        }
        
        /* Hover effect */
        .resultRow:hover td {
            background-color: #fef3c7 !important;
        }
        
        .total-row:hover td {
            background-color: #dbeafe !important;
        }
        
        /* Total row styling */
        .total-row td {
            background-color: #f1f5f9 !important;
            font-weight: bold !important;
        }
        
        /* Keep table layout intact */
        #inventoryTable table {
            border-collapse: collapse !important;
            width: 100%;
        }
        
        /* Make transaction rows clickable with cursor pointer */
        .clickable-row {
            cursor: pointer;
        }
        
        /* Fix for border radius on transaction groups */
        .transaction-group:first-child tr:first-child td:first-child {
            border-top-left-radius: 6px;
        }
        
        .transaction-group:first-child tr:first-child td:last-child {
            border-top-right-radius: 6px;
        }
        
        .transaction-group:last-child tr:last-child td:first-child {
            border-bottom-left-radius: 6px;
        }
        
        .transaction-group:last-child tr:last-child td:last-child {
            border-bottom-right-radius: 6px;
        }

        /* FIX: Content wrapper - proper height management */
        #contentDiv {
            height: 100%;
            overflow: hidden;
        }

        .content-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 90px);
            max-height: calc(100vh - 90px);
            overflow: hidden;
        }

        /* FIX: Table container - flex layout with proper sizing */
        .table-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        /* FIX: Table wrapper - takes remaining space */
        .table-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        /* FIX: Scrollable body - proper overflow */
        .table-scroll-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: auto;
            min-height: 0;
            position: relative;
        }

        /*
         * FIX: Fixed layout driven by <colgroup>. The 12 column percentages below add up to 100%,
         * so the table fills its container exactly (no leftover space after Profit) and the
         * min-width keeps every column readable, falling back to horizontal scroll on small screens.
         * Fixed layout works with rowspan/colspan because widths come from the colgroup, not from cell content.
         */
        .table-scroll-body table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            min-width: 1400px;
        }

        .table-scroll-body col.col-date     { width: 7%; }
        .table-scroll-body col.col-receipt  { width: 7%; }
        .table-scroll-body col.col-code     { width: 6%; }
        .table-scroll-body col.col-desc     { width: 16%; }
        .table-scroll-body col.col-qty      { width: 5%; }
        .table-scroll-body col.col-disc     { width: 7%; }
        .table-scroll-body col.col-sales    { width: 7%; }
        .table-scroll-body col.col-cost     { width: 7%; }
        .table-scroll-body col.col-profit   { width: 7%; }
        .table-scroll-body col.col-payment  { width: 17%; }
        .table-scroll-body col.col-cashier  { width: 7%; }
        .table-scroll-body col.col-status   { width: 7%; }

        /* Make thead sticky within the scrollable container */
        .table-scroll-body thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-scroll-body thead th {
            background-color: #8aecb0 !important;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
        }

        /* Controls section sticky */
        .controls-sticky {
            flex-shrink: 0;
            position: sticky;
            top: 0;
            z-index: 30;
            background: white;
            padding-bottom: 0px;
        }

        /* Pagination sticky at bottom */
        .pagination-sticky {
            flex-shrink: 0;
            position: sticky;
            bottom: 0;
            z-index: 20;
            background: white;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        /* Totals section - keep visible */
        .totals-section {
            flex-shrink: 0;
        }

        /* FIX: Make totals smaller and more compact */
        .totals-section .grid {
            grid-template-columns: repeat(5, 1fr);
            gap: 0.5rem;
        }
        
        .totals-section .min-w-0 {
            padding: 0 !important;
        }
        
        .totals-section .text-lg {
            font-size: 1rem !important;
        }
        
        .totals-section .text-[10px] {
            font-size: 0.6rem !important;
        }

        /* FIX: Table cell padding reduced + vertical centering for rowspan cells */
        .resultRow td, .total-row td {
            padding: 4px 6px !important;
            font-size: 0.75rem !important;
            vertical-align: middle;
            overflow-wrap: break-word;
        }

        /* ===== PAYMENT COLUMN ===== */
        .resultRow td.payment-cell {
            text-align: left;
            vertical-align: middle;
            padding: 4px 6px 4px 12px !important;
        }

        .payment-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            gap: 3px;
            width: 100%;
        }

        .payment-line {
            white-space: nowrap;
            text-align: left;
            font-size: 13px;
            line-height: 1.3;
        }

        .payment-label {
            color: #374151;
            text-transform: uppercase;
            font-size: 12px;
        }

        .payment-amount {
            color: #111827;
            font-weight: 600;
            font-size: 13px;
        }

        .payment-empty {
            color: #9ca3af;
            font-style: italic;
            font-size: 13px;
        }

        /* FIX: Header controls compact */
        .controls-sticky .my-4 {
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
        }
        
        .controls-sticky h1.text-3xl {
            font-size: 1.5rem !important;
        }

        /* FIX: Searchable dropdown compact */
        .searchable-dropdown-wrapper {
            min-width: 150px;
            max-width: 200px;
        }
        
        .searchable-dropdown-wrapper input[type="text"] {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.75rem !important;
        }

        /* FIX: Date picker compact */
        [date-rangepicker] .relative {
            max-width: 130px;
        }
        
        [date-rangepicker] input {
            padding: 0.25rem 0.5rem 0.25rem 2rem !important;
            font-size: 0.75rem !important;
        }
        
        [date-rangepicker] .mx-4 {
            margin-left: 0.25rem !important;
            margin-right: 0.25rem !important;
        }

        /* FIX: Buttons compact */
        .controls-sticky button.bg-blue-600,
        .controls-sticky button.bg-green-600,
        .controls-sticky button.bg-emerald-600 {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.75rem !important;
            white-space: nowrap;
        }

        /* FIX: Inner container padding */
        .bg-white.overflow-hidden {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .controls-sticky .flex-wrap {
                flex-direction: column;
                align-items: stretch;
            }
            
            .controls-sticky .flex-wrap > * {
                margin-bottom: 0.25rem;
            }
            
            .controls-sticky .flex-wrap .flex.items-center {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .searchable-dropdown-wrapper {
                min-width: 100%;
                max-width: 100%;
            }
            
            [date-rangepicker] .relative {
                max-width: 100%;
                flex: 1;
            }
            
            .totals-section .grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .content-wrapper {
                height: calc(100vh - 160px);
                max-height: calc(100vh - 160px);
            }
        }

        @media (max-width: 768px) {
            .content-wrapper {
                height: calc(100vh - 180px);
                max-height: calc(100vh - 180px);
            }
            
            .totals-section .grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .pagination-sticky .grid {
                grid-template-columns: 1fr !important;
                gap: 0.5rem;
            }
            
            .pagination-sticky .justify-self-center {
                justify-self: center !important;
            }
        }

        @media (max-width: 480px) {
            .totals-section .grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .resultRow td, .total-row td {
                font-size: 0.65rem !important;
                padding: 2px 4px !important;
            }

            /* Keep payment readable on small screens (table keeps its min-width and scrolls horizontally) */
            .payment-line,
            .payment-amount,
            .payment-empty {
                font-size: 12px;
            }

            .payment-label {
                font-size: 11px;
            }
        }

        /* ===== SEARCHABLE DROPDOWN STYLES ===== */
        .searchable-dropdown-wrapper {
            position: relative;
            min-width: 200px;
        }

        .searchable-dropdown-wrapper input[type="text"] {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background: #f9fafb;
            transition: all 0.2s;
        }

        .searchable-dropdown-wrapper input[type="text"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        .searchable-dropdown-wrapper .dropdown-list {
            position: absolute;
            z-index: 50;
            width: 100%;
            margin-top: 4px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            display: none;
            animation: dropdownFadeIn 0.2s ease-in-out;
        }

        .searchable-dropdown-wrapper .dropdown-list.visible {
            display: block;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item.active {
            background-color: #dbeafe;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item .item-icon {
            flex-shrink: 0;
            margin-right: 0.5rem;
            color: #9ca3af;
        }

        .searchable-dropdown-wrapper .dropdown-list .dropdown-item .item-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.875rem;
        }

        .searchable-dropdown-wrapper .dropdown-list .all-items-header {
            position: sticky;
            top: 0;
            background: white;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #d1d5db;
            z-index: 10;
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .searchable-dropdown-wrapper .dropdown-list .all-items-header:hover {
            background-color: #f3f4f6;
        }

        .searchable-dropdown-wrapper .dropdown-list .no-results {
            padding: 1rem;
            text-align: center;
            color: #6b7280;
        }

        .searchable-dropdown-wrapper .dropdown-list .no-results svg {
            width: 2rem;
            height: 2rem;
            margin: 0 auto 0.5rem;
            color: #d1d5db;
        }

        .searchable-dropdown-wrapper .dropdown-list .no-results .sub-text {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        /* Dropdown scrollbar styling */
        .searchable-dropdown-wrapper .dropdown-list::-webkit-scrollbar {
            width: 6px;
        }

        .searchable-dropdown-wrapper .dropdown-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .searchable-dropdown-wrapper .dropdown-list::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }

        .searchable-dropdown-wrapper .dropdown-list::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Print styles - hide everything except printable area */
        @media print {
            body * {
                visibility: hidden;
            }
            #printableArea, #printableArea * {
                visibility: visible;
            }
            #printableArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white;
                padding: 20px;
                margin: 0;
            }
            #printableArea .no-print {
                display: none !important;
            }
            #printableArea .transaction-group {
                box-shadow: 0 0 0 1px #830505 !important;
                page-break-inside: avoid !important;
                margin-bottom: 10px !important;
            }
            #printableArea .table-scroll-body {
                overflow: visible !important;
                height: auto !important;
            }
            #printableArea .table-wrapper {
                height: auto !important;
                overflow: visible !important;
            }
            /* Fit the fixed-layout table to the page width when printing */
            #printableArea .table-scroll-body table {
                min-width: 0 !important;
                width: 100% !important;
            }
            #printableArea .table-scroll-body col.col-desc    { width: 15%; }
            #printableArea .table-scroll-body col.col-payment { width: 18%; }
            #printableArea thead th {
                background-color: #e3eee7 !important;
                position: static !important;
                color: #000000 !important;
                font-weight: 800 !important;
                padding: 6px 3px !important;
                border: 1px solid #000 !important;
                font-size: 10px !important;
                white-space: nowrap;
                overflow: hidden;
                box-shadow: inset 0 -1px 0 #000 !important;
            }
            #printableArea thead th i {
                display: none;
            }
            #printableArea .resultRow td,
            #printableArea .total-row td {
                font-size: 10px !important;
                padding: 3px 4px !important;
            }
            #printableArea .payment-line,
            #printableArea .payment-label,
            #printableArea .payment-amount,
            #printableArea .payment-empty {
                font-size: 9px !important;
            }
            #printableArea .controls-sticky,
            #printableArea .pagination-sticky,
            #printableArea .totals-section,
            #printableArea .no-print {
                display: none !important;
            }
            #printableArea .print-header {
                display: block !important;
                border-bottom: 2px solid #000000;
            }
            #printableArea .transaction-group {
                box-shadow: 0 0 0 2px #830505 !important;
            }
            .print-header {
                display: none;
            }
        }
        .print-header {
            display: none;
        }

        /* FIX: Prevent content from spilling out */
        .p-3.lg\:ml-64 {
            height: 100vh;
            overflow: hidden;
        }
        
        .bg-white.overflow-hidden.shadow-md.rounded-lg.p-4 {
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .p-4 {
            padding: 0.75rem !important;
        }
    </style>

    @section('page_title', '')

    {{-- NOTIFICATION --}}
    <div id="topNotification" class="fixed left-1/2 top-4 z-[60] hidden w-[min(92vw,24rem)] -translate-x-1/2 rounded-lg border px-4 py-3 shadow-lg">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <i id="topNotificationIcon" class="uil text-xl"></i>
                <h1 id="topNotificationMessage" class="font-semibold"></h1>
            </div>
            <button type="button" class="topNotificationClose self-center" aria-label="Close notification">
                <i class="uil uil-times text-xl"></i>
            </button>
        </div>
    </div>
    @if (session('message'))
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                window.showTopNotification && window.showTopNotification(@json(session('message')), 'success');
            });
        </script>
    @endif
    {{-- NOTIFICATION END --}}

    <div class="lg:ml-64">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                <div class="content-wrapper">
                    {{-- CONTROLS SECTION - STICKY --}}
                    <div class="controls-sticky">
                        <div class="my-2 flex justify-between flex-wrap gap-1">
                            <h1 class="text-2xl font-bold">Transactions</h1>
                            {{-- TRANSACTION NUMBER SEARCH (dedicated) --}}
                            <form method="POST" action="{{ route('transactions.searchByNumber') }}" 
                                class="flex items-center gap-1 mt-1" id="tranNumberSearchForm">
                                @csrf
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                        <i class="uil uil-search text-gray-500 text-xs"></i>
                                    </div>
                                    <input type="text" name="transaction_number" id="transaction_number"
                                        value="{{ $searchedNumber ?? '' }}"
                                        placeholder="Search transaction #..."
                                        autocomplete="off"
                                        class="bg-yellow-50 border border-yellow-300 text-gray-900 text-xs rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block w-full pl-7 p-1.5"
                                        style="min-width:200px;">
                                </div>
                                <button type="submit" 
                                    class="bg-yellow-600 px-3 py-1.5 rounded-lg text-white font-bold hover:bg-yellow-700 transition text-xs flex items-center gap-1">
                                    <i class="uil uil-search"></i> Find
                                </button>
                                @if(!empty($searchedNumber))
                                    <a href="{{ route('transactions.index') }}" 
                                    class="bg-gray-500 px-3 py-1.5 rounded-lg text-white font-bold hover:bg-gray-600 transition text-xs">
                                        Clear
                                    </a>
                                @endif
                            </form>                            
                            {{-- Updated form section --}}
                            <form method="POST" action="{{ route('transactions.generate') }}" class="flex flex-wrap gap-1 items-center" id="filterForm">
                                @csrf

                                {{-- BRANCH FILTER --}}
                                <div class="flex items-center">
                                    <select id="branch" name="branch"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-1.5">
                                        <option value="all" {{ $selectedBranch == 'all' ? 'selected' : '' }}>All Branches</option>
                                        @foreach ($branches as $branch)
                                            @if ($branch->id != 1)
                                                <option value="{{ $branch->branch_code }}" {{ $selectedBranch == $branch->branch_code ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                {{-- STATUS FILTER (NEW) --}}
                                <div class="flex items-center">
                                    <select id="status" name="status"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-1.5">
                                        <option value="all" {{ ($selectedStatus ?? 'all') == 'all' ? 'selected' : '' }}>All Status</option>
                                        <option value="PAID" {{ ($selectedStatus ?? 'all') == 'PAID' ? 'selected' : '' }}>Paid</option>
                                        <option value="UNPAID" {{ ($selectedStatus ?? 'all') == 'UNPAID' ? 'selected' : '' }}>Unpaid</option>
                                        <option value="CANCELLED" {{ ($selectedStatus ?? 'all') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>                                
                                {{-- CLIENT FILTER - SEARCHABLE DROPDOWN --}}
                                <div class="searchable-dropdown-wrapper">
                                    <input 
                                        type="text" 
                                        id="client_search" 
                                        class="w-full px-2 py-1.5 border rounded-lg focus:ring-blue-500 focus:border-blue-500 text-xs" 
                                        placeholder="Search client..."
                                        autocomplete="off"
                                        oninput="filterClients()"
                                        onfocus="showClientDropdown()"
                                        onkeydown="handleClientSearchKeydown(event)"
                                        value="{{ $selectedClient != 'all' ? $clients->firstWhere('id', (int)$selectedClient)->name ?? '' : '' }}"
                                    >
                                    <input type="hidden" name="client" id="selected_client_id" value="{{ $selectedClient }}">
                                    <div id="clientDropdown" class="dropdown-list">
                                        <div class="all-items-header" onclick="selectClient('all', 'All Clients')">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                            </svg>
                                            <span>All Clients</span>
                                        </div>
                                        <div class="dropdown-items-container">
                                            @foreach ($clients as $client)
                                                <div class="dropdown-item client-option" 
                                                    data-value="{{ $client->id }}" 
                                                    data-text="{{ htmlspecialchars($client->name, ENT_QUOTES, 'UTF-8') }}"
                                                    onclick="selectClient('{{ $client->id }}', '{{ htmlspecialchars($client->name, ENT_QUOTES, 'UTF-8') }}')">
                                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    <span class="item-text">{{ $client->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div id="clientNoResults" class="no-results" style="display: none;">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p>No clients found</p>
                                            <p class="sub-text">Try different keywords</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div date-rangepicker class="flex items-center gap-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                            <svg aria-hidden="true" class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <input name="startDate" id="startDate" value="{{ date('m/d/Y', strtotime($startDate)) }}" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 p-1.5" placeholder="Start" style="max-width:110px;">
                                    </div>
                                    <span class="text-gray-500 text-xs">to</span>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                            <svg aria-hidden="true" class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                        </div>
                                        <input name="endDate" id="endDate" value="{{ date('m/d/Y', strtotime($endDate)) }}" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 p-1.5" placeholder="End" style="max-width:110px;">
                                    </div>
                                </div>
                                <button class="bg-blue-600 px-3 py-1.5 rounded-lg text-white font-bold text-xs hover:bg-blue-700 transition">Generate</button>
                                
                                {{-- PRINT BUTTON --}}
                                <button type="button" onclick="printTransactions()" class="bg-green-600 px-3 py-1.5 rounded-lg text-white font-bold hover:bg-green-700 transition duration-200 flex items-center gap-1 text-xs">
                                    <i class="uil uil-print"></i>
                                    Print
                                </button>
                                {{-- EXCEL EXPORT BUTTON --}}
                                <button type="submit" formaction="{{ route('transactions.export') }}" formmethod="POST" class="bg-emerald-600 px-3 py-1.5 rounded-lg text-white font-bold hover:bg-emerald-700 transition duration-200 flex items-center gap-1 text-xs">
                                    <i class="uil uil-file-export"></i>
                                    Excel
                                </button>                                
                            </form>                           
                        </div>
                    </div>
                    {{-- CONTROLS END --}}

                    {{-- TABLE CONTAINER --}}
                    <div class="table-container">
                        {{-- TABLE WITH SCROLLABLE BODY --}}
                        <div id="printableArea" class="flex-1 flex flex-col min-h-0">
                            {{-- PRINT HEADER --}}
                            <div class="print-header">
                                <h2 style="font-size: 22px; font-weight: bold; text-align: center;">Sales Detail Report</h2>
                                <table style="width: 100%; font-size: 12px; margin-top: 5px; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 2px 5px; font-weight: bold; width: 120px;">Branch Name:</td>
                                        <td style="padding: 2px 5px;">
                                            @if($selectedBranch != 'all')
                                                {{ $branches->where('branch_code', $selectedBranch)->first()->name ?? 'N/A' }}
                                            @else
                                                All Branches
                                            @endif
                                        </td>
                                        <td style="padding: 2px 5px; font-weight: bold; width: 120px;">Date Printed:</td>
                                        <td style="padding: 2px 5px;">{{ date('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 5px; font-weight: bold;">Client:</td>
                                        <td style="padding: 2px 5px;">
                                            @if($selectedClient != 'all')
                                                {{ $clients->firstWhere('id', $selectedClient)->name ?? 'N/A' }}
                                            @else
                                                All Clients
                                            @endif
                                        </td>
                                        <td style="padding: 2px 5px; font-weight: bold;">Date Range:</td>
                                        <td style="padding: 2px 5px;">{{ date('Y-m-d', strtotime($startDate)) }} to {{ date('Y-m-d', strtotime($endDate)) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 5px; font-weight: bold;">Who Printed:</td>
                                        <td style="padding: 2px 5px;" colspan="3">{{ auth()->user()->firstname ?? '' }} {{ auth()->user()->lastname ?? '' }}</td>
                                    </tr>
                                </table>                            
                            </div>

                            <div class="table-wrapper">
                                <div class="table-scroll-body" id="tableScrollBody">
                                    {{-- NOTE: #transactionDiv now lives on the <table>; the invalid outer <tbody> wrapper around the
                                         per-transaction <tbody class="transaction-group"> blocks was removed. --}}
                                    <table id="transactionDiv" class="w-full text-xs text-left text-gray-500">
                                        {{-- 12 columns - widths add up to 100% (see CSS) --}}
                                        <colgroup>
                                            <col class="col-date">
                                            <col class="col-receipt">
                                            <col class="col-code">
                                            <col class="col-desc">
                                            <col class="col-qty">
                                            <col class="col-disc">
                                            <col class="col-sales">
                                            <col class="col-cost">
                                            <col class="col-profit">
                                            <col class="col-payment">
                                            <col class="col-cashier">
                                            <col class="col-status">
                                        </colgroup>
                                        <thead>
                                            <tr class="text-slate-700 font-semibold bg-slate-100">
                                                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-calendar-alt mr-0.5"></i>Date
                                                </th>

                                                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-receipt mr-0.5"></i>Receipt
                                                </th>

                                                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-barcode mr-0.5"></i>Code
                                                </th>

                                                <th class="px-1 py-2 text-left sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-package mr-0.5"></i>Description
                                                </th>

                                                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-sort-amount-up mr-0.5"></i>Qty
                                                </th>

                                                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-percentage mr-0.5"></i>Disc
                                                </th>

                                                <th class="px-1 py-2 text-left sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-money-withdraw mr-0.5"></i>Sales
                                                </th>

                                                <th class="px-1 py-2 text-left sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-tag-alt mr-0.5"></i>Cost
                                                </th>

                                                <th class="px-1 py-2 text-left sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-chart-line mr-0.5"></i>Profit
                                                </th>

                                                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-credit-card mr-0.5"></i>Payment
                                                </th>

                                                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-user mr-0.5"></i>Cashier
                                                </th>

                                                <th class="px-1 py-2 text-center sticky top-0 bg-slate-100 text-[10px]">
                                                    <i class="uil uil-check-circle mr-0.5"></i>Status
                                                </th>
                                            </tr>                                        
                                        </thead>
                                        @foreach($results as $index => $result)
                                            @php
                                                $items = $result->ordered_items;
                                                $itemCount = $items->count();
                                                $hasClient = !empty($result->table_name);
                                                $hasItems = $itemCount > 0;
                                                
                                                // Generate encrypted transaction ID for security
                                                $encryptedId = Crypt::encryptString($result->id . '|' . ($result->branch_code ?? 'default'));
                                                
                                                // Group items by name for display
                                                $groupedItems = $items->groupBy('menu_name');
                                                $displayItems = [];
                                                
                                                // Get transaction-level discounts
                                                $serviceCharge = isset($result->service_charge) ? floatval($result->service_charge) : 0;
                                                $ldValue = isset($result->ld_value) ? floatval($result->ld_value) : 0;
                                                $pwd = isset($result->pwd) ? floatval($result->pwd) : 0;
                                                
                                                // If these fields aren't in the result, fetch from database
                                                if (!isset($result->service_charge) || !isset($result->ld_value) || !isset($result->pwd)) {
                                                    $transactionData = DB::table('transactions')
                                                        ->where('id', $result->id)
                                                        ->select('service_charge', 'ld_value', 'pwd')
                                                        ->first();
                                                    if ($transactionData) {
                                                        $serviceCharge = floatval($transactionData->service_charge ?? 0);
                                                        $ldValue = floatval($transactionData->ld_value ?? 0);
                                                        $pwd = floatval($transactionData->pwd ?? 0);
                                                    }
                                                }
                                                
                                                $totalTransactionDiscount = $serviceCharge + $ldValue + $pwd;
                                                
                                                // Calculate item amounts for proportional allocation
                                                $itemAmounts = [];
                                                foreach ($groupedItems as $name => $group) {
                                                    $itemAmount = $group->sum('ordered_amount');
                                                    $itemAmounts[] = [
                                                        'name' => $name,
                                                        'group' => $group,
                                                        'amount' => $itemAmount
                                                    ];
                                                }
                                                
                                                // Sort by amount descending for consistent rounding application
                                                usort($itemAmounts, function($a, $b) {
                                                    return $b['amount'] <=> $a['amount'];
                                                });
                                                
                                                // Calculate discount for each item
                                                $totalItemAmount = array_sum(array_column($itemAmounts, 'amount'));
                                                
                                                // If we have items and total amount > 0, allocate discounts
                                                if ($hasItems && $totalItemAmount > 0 && $totalTransactionDiscount > 0) {
                                                    $allocatedDiscounts = [];
                                                    $runningTotal = 0;
                                                    
                                                    foreach ($itemAmounts as $idx => $itemData) {
                                                        $itemDiscount = ($itemData['amount'] / $totalItemAmount) * $totalTransactionDiscount;
                                                        $roundedDiscount = round($itemDiscount, 2);
                                                        $allocatedDiscounts[] = [
                                                            'name' => $itemData['name'],
                                                            'group' => $itemData['group'],
                                                            'discount' => $roundedDiscount,
                                                            'original_discount' => $itemDiscount
                                                        ];
                                                        $runningTotal += $roundedDiscount;
                                                    }
                                                    
                                                    // Apply rounding difference to the last item
                                                    $roundingDiff = $totalTransactionDiscount - $runningTotal;
                                                    if (abs($roundingDiff) > 0.001) {
                                                        $lastIndex = count($allocatedDiscounts) - 1;
                                                        $allocatedDiscounts[$lastIndex]['discount'] += $roundingDiff;
                                                        $allocatedDiscounts[$lastIndex]['discount'] = round($allocatedDiscounts[$lastIndex]['discount'], 2);
                                                    }
                                                    
                                                    // Build display items with allocated discounts
                                                    foreach ($allocatedDiscounts as $allocated) {
                                                        $group = $allocated['group'];
                                                        $salesAmount = $group->sum('ordered_amount');
                                                        $foodCost = $group->sum('ordered_food_cost');
                                                        $profit = $salesAmount - $foodCost;
                                                        $brandName = $group->first()->brand_name ?? '';
                                                        $brandCode = $group->first()->brand_code ?? '';
                                                        
                                                        $displayItems[] = [
                                                            'name' => $allocated['name'],
                                                            'brand' => $brandName,
                                                            'brand_code' => $brandCode,
                                                            'count' => $group->count(),
                                                            'total_quantity' => $group->sum('ordered_quantity'),
                                                            'item_code' => $group->first()->item_code ?? 'N/A',
                                                            'discount_amount' => $allocated['discount'],
                                                            'sales_amount' => $salesAmount,
                                                            'food_cost' => $foodCost,
                                                            'profit' => $profit,
                                                            'quantity_breakdown' => $group->pluck('ordered_quantity')->map(function($qty) {
                                                                return number_format($qty, 3);
                                                            })->implode('/')
                                                        ];
                                                    }
                                                } elseif ($hasItems && $totalItemAmount > 0) {
                                                    // No discount, just show items
                                                    foreach ($groupedItems as $name => $group) {
                                                        $salesAmount = $group->sum('ordered_amount');
                                                        $foodCost = $group->sum('ordered_food_cost');
                                                        $profit = $salesAmount - $foodCost;
                                                        $brandName = $group->first()->brand_name ?? '';
                                                        $brandCode = $group->first()->brand_code ?? '';
                                                        
                                                        $displayItems[] = [
                                                            'name' => $name,
                                                            'brand' => $brandName,
                                                            'brand_code' => $brandCode,
                                                            'count' => $group->count(),
                                                            'total_quantity' => $group->sum('ordered_quantity'),
                                                            'item_code' => $group->first()->item_code ?? 'N/A',
                                                            'discount_amount' => 0,
                                                            'sales_amount' => $salesAmount,
                                                            'food_cost' => $foodCost,
                                                            'profit' => $profit,
                                                            'quantity_breakdown' => $group->pluck('ordered_quantity')->map(function($qty) {
                                                                return number_format($qty, 3);
                                                            })->implode('/')
                                                        ];
                                                    }
                                                }
                                                
                                                $totalDisplayRows = count($displayItems);
                                                // Date, Receipt, Payment, Cashier and Status each span ALL item rows of the transaction.
                                                // The total row is a separate row and spans all 12 columns by itself.
                                                $rowspan = max(1, $totalDisplayRows);
                                                $first = true;
                                                
                                                // Track row color alternation
                                                $rowColorIndex = 0;
                                            @endphp

                                            <tbody class="transaction-group" data-transaction="{{ $encryptedId }}">
                                                @if($hasItems && count($displayItems) > 0)
                                                    {{-- Display grouped items with allocated discounts --}}
                                                    @foreach($displayItems as $displayIndex => $displayItem)
                                                        @php
                                                            $rowClass = ($rowColorIndex % 2 == 0) ? 'row-colored' : 'row-white';
                                                            $rowColorIndex++;
                                                        @endphp
                                                        <tr class="resultRow clickable-row {{ $rowClass }}" data-transaction="{{ $encryptedId }}">
                                                            {{-- Transaction-level columns (only on first row) - cols 1-2 --}}
                                                            @if($first)
                                                                <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $rowspan }}">
                                                                    {{ date('Y-m-d', strtotime($result->date ?? now())) }}
                                                                </td>
                                                                <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $rowspan }}">
                                                                    {{ $result->nn }}
                                                                </td>
                                                                @php $first = false; @endphp
                                                            @endif

                                                            {{-- Item-level columns - cols 3-9 --}}
                                                            <td class="px-1 py-0.5 text-center text-[10px]">{{ $displayItem['item_code'] }}</td>
                                                            <td class="px-1 py-0.5 text-left text-[10px]">
                                                                <div class="whitespace-normal"> 
                                                                    <span class="font-bold text-gray-800">{{ $displayItem['name'] }}</span> -
                                                                    <span class="font-semibold text-gray-500">{{ $displayItem['brand'] }}</span>
                                                                    <span class="text-gray-500">({{ $displayItem['brand_code'] }})</span>
                                                                </div>
                                                                @if($displayItem['count'] > 1)
                                                                    <div class="text-[10px] text-gray-500 font-normal">
                                                                        {{ $displayItem['quantity_breakdown'] }}
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="px-1 py-0.5 text-center text-[10px]">{{ number_format($displayItem['total_quantity'], 3) }}</td>
                                                            <td class="px-1 py-0.5 text-center text-[10px]">- ₱ {{ number_format($displayItem['discount_amount'], 2) }}</td>
                                                            <td class="px-1 py-0.5 text-left text-[10px]">₱ {{ number_format($displayItem['sales_amount'], 2) }}</td>
                                                            <td class="px-1 py-0.5 text-left text-[10px]">₱ {{ number_format($displayItem['food_cost'], 2) }}</td>
                                                            <td class="px-1 py-0.5 text-left text-[10px]">₱ {{ number_format($displayItem['profit'], 2) }}</td>

                                                            {{-- Transaction-level columns (only on first row of transaction) - cols 10-12 --}}
                                                            @if($displayIndex === 0)
                                                                <td class="payment-cell px-2 py-1 text-left" rowspan="{{ $rowspan }}">
                                                                    @if(!empty($result->payment_summary))
                                                                        <div class="payment-stack">
                                                                            @foreach($result->payment_summary as $payment)
                                                                                <div class="payment-line">
                                                                                    <span class="payment-label">{{ $payment['label'] }}:</span>
                                                                                    <span class="payment-amount font-semibold">₱ {{ number_format($payment['amount'], 2) }}</span>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @else
                                                                        <span class="payment-empty text-gray-400 italic">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $rowspan }}">
                                                                    {{ $result->cashier_name  ?? 'N/A' }}
                                                                </td>
                                                                @php
                                                                    $displayStatus = $result->tstatus ?? 'N/A';
                                                                    $statusColor = 'text-red-600';

                                                                    // CANCELLED always takes priority
                                                                    if ($result->ostatus == 'CANCELLED') {
                                                                        $displayStatus = 'CANCELLED';
                                                                        $statusColor = 'text-gray-600';

                                                                    // PAID + COMPLETE
                                                                    } elseif ($result->tstatus == 'PAID' && $result->ostatus == 'COMPLETE') {
                                                                        $displayStatus = 'PAID';
                                                                        $statusColor = 'text-green-600';

                                                                    // PAID regardless of other non-cancelled status
                                                                    } elseif ($result->tstatus == 'PAID') {
                                                                        $displayStatus = 'PAID';
                                                                        $statusColor = 'text-green-600';

                                                                    // UNPAID + PREPARING
                                                                    } elseif (
                                                                        ($result->tstatus == 'UNPAID' || $result->tstatus == 'unpaid') &&
                                                                        $result->ostatus == 'PREPARING'
                                                                    ) {
                                                                        $displayStatus = 'UNPAID';
                                                                        $statusColor = 'text-gray-600';

                                                                    // Other UNPAID
                                                                    } elseif ($result->tstatus == 'UNPAID' || $result->tstatus == 'unpaid') {
                                                                        $displayStatus = 'UNPAID';
                                                                        $statusColor = 'text-red-600';
                                                                    }
                                                                @endphp
                                                                <td class="px-1 py-0.5 text-center text-[10px] {{ $statusColor }}" rowspan="{{ $rowspan }}">
                                                                    {{ $displayStatus }}
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    {{-- Transaction has NO items: 2 + colspan 7 + 3 = 12 columns --}}
                                                    @php
                                                        $rowClass = ($rowColorIndex % 2 == 0) ? 'row-colored' : 'row-white';
                                                    @endphp
                                                    <tr class="resultRow clickable-row {{ $rowClass }}" data-transaction="{{ $encryptedId }}">
                                                        <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $rowspan }}">
                                                            {{ date('Y-m-d', strtotime($result->date ?? now())) }}
                                                        </td>
                                                        <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $rowspan }}">
                                                            {{ $result->nn }}
                                                        </td>
                                                        <td class="px-1 py-0.5 text-center text-[10px]" colspan="7">
                                                            <span class="text-gray-400 italic">No items found</span>
                                                        </td>
                                                        <td class="payment-cell px-2 py-1 text-left" rowspan="{{ $rowspan }}">
                                                            @if(!empty($result->payment_summary))
                                                                <div class="payment-stack">
                                                                    @foreach($result->payment_summary as $payment)
                                                                        <div class="payment-line">
                                                                            <span class="payment-label">{{ $payment['label'] }}:</span>
                                                                            <span class="payment-amount font-semibold">₱ {{ number_format($payment['amount'], 2) }}</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <span class="payment-empty text-gray-400 italic">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-1 py-0.5 text-center text-[10px]" rowspan="{{ $rowspan }}">
                                                            {{ $result->cashier_name  ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-1 py-0.5 text-center text-[10px] {{ $result->tstatus == 'PAID' ? 'text-green-600' : 'text-red-600' }}" rowspan="{{ $rowspan }}">
                                                            {{ $result->tstatus ?? 'N/A' }}
                                                        </td>
                                                    </tr>
                                                @endif

                                                {{-- Client/Grand Total row --}}
                                                @if($hasClient || $hasItems)
                                                    @php
                                                        // Calculate totals from display items
                                                        $totalDiscountDisplay = 0;
                                                        $totalSalesDisplay = 0;
                                                        $totalFoodCostDisplay = 0;
                                                        $totalProfitDisplay = 0;
                                                        foreach ($displayItems as $item) {
                                                            $totalDiscountDisplay += $item['discount_amount'];
                                                            $totalSalesDisplay += $item['sales_amount'];
                                                            $totalFoodCostDisplay += $item['food_cost'];
                                                            $totalProfitDisplay += $item['profit'];
                                                        }
                                                    @endphp
                                                    {{-- Total row column map (3+2+1+1+1+1+3 = 12):
                                                         Client  -> Date, Receipt, Code        (colspan 3)
                                                         TOTAL   -> Description, Qty          (colspan 2)
                                                         Disc / Sales / Cost / Profit          (1 each)
                                                         Button  -> Payment, Cashier, Status   (colspan 3) --}}
                                                    <tr class="total-row">
                                                        <td class="px-1 py-1 text-right text-[10px] text-gray-700 font-bold">
                                                            <span class="text-gray-500">Client:</span>
                                                        </td>
                                                        <td colspan="2" class="px-1 py-1 ml-4 text-left text-[10px] text-gray-700">
                                                            @if($hasClient)
                                                                {{ $result->table_name }}
                                                            @endif
                                                        </td>
                                                        <td colspan="2" class="px-1 py-1 text-right text-[10px] text-gray-700 font-bold">
                                                            TOTAL:
                                                        </td>
                                                        <td class="px-1 py-1 text-center text-[10px] text-gray-700 font-bold">
                                                            - ₱ {{ number_format($totalDiscountDisplay, 2) }}
                                                        </td>
                                                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold">
                                                            ₱ {{ number_format($totalSalesDisplay, 2) }}
                                                        </td>
                                                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold">
                                                            ₱ {{ number_format($totalFoodCostDisplay, 2) }}
                                                        </td>
                                                        <td class="px-1 py-1 text-left text-[10px] text-gray-700 font-bold">
                                                            ₱ {{ number_format($totalProfitDisplay, 2) }}
                                                        </td>
                                                        <td colspan="3" class="px-1 py-1 text-center text-[10px] text-gray-700 font-bold">
                                                            <button onclick="printReceipt('{{ $result->id }}')" 
                                                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-0.5 px-2 rounded text-[9px] transition duration-200">
                                                                <i class="uil uil-print mr-0.5"></i> Print Receipt
                                                            </button>
                                                        </td>                                                         
                                                    </tr>
                                                @endif
                                            </tbody>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- TABLE CONTAINER END --}}               
                </div>
                {{-- PAGINATION - STICKY --}}
                <div class="pagination-sticky">
                    <div class="flex items-center justify-between px-2 mt-2 gap-2 overflow-x-auto">
                        @php
                            $prev = $page - 1;
                            $next = $page + 1;
                            $from = (($page - 1) * $perPage) + 1;
                            $to = $page * $perPage;
                            if($to > $menuCount){
                                $to = $menuCount;
                            }
                            if($menuCount == 0){
                                $from = 0;
                            }
                            
                            // Build URL with all filters
                            $baseUrl = route('transactions.index', [
                                'startDate' => date('m/d/Y', strtotime($startDate)),
                                'endDate' => date('m/d/Y', strtotime($endDate)),
                                'branch' => $selectedBranch,
                                'status' => $selectedStatus ?? 'all',
                                'client' => $selectedClient,
                                'searchedNumber' => $searchedNumber ?? '',
                            ]);
                        @endphp
                        
                        {{-- Showing items info --}}
                        <div class="flex-shrink-0">
                            <span class="text-xs text-gray-700 whitespace-nowrap">
                                Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $menuCount }}</span> Items
                            </span>
                        </div>
                        
                        {{-- TOTALS - Compact horizontal layout --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <!-- Transactions -->
                            <div class="flex items-center px-2 py-1 bg-blue-50 border border-blue-200 rounded-md whitespace-nowrap">
                                <i class="uil uil-wallet text-xs text-blue-500 mr-1 flex-shrink-0"></i>
                                <span class="text-xs text-gray-500">Trans:</span>
                                <span class="text-xs font-bold text-blue-700 ml-0.5">₱{{ $checkingTotal }}</span>
                            </div>
                            <!-- Paid -->
                            <div class="flex items-center px-2 py-1 bg-green-50 border border-green-200 rounded-md whitespace-nowrap">
                                <i class="uil uil-check-circle text-xs text-green-500 mr-1 flex-shrink-0"></i>
                                <span class="text-xs text-gray-500">Paid:</span>
                                <span class="text-xs font-bold text-green-700 ml-0.5">₱{{ $audited }}</span>
                            </div>
                            <!-- Unpaid -->
                            <div class="flex items-center px-2 py-1 bg-red-50 border border-red-200 rounded-md whitespace-nowrap">
                                <i class="uil uil-times-circle text-xs text-red-500 mr-1 flex-shrink-0"></i>
                                <span class="text-xs text-gray-500">Unpaid:</span>
                                <span class="text-xs font-bold text-red-700 ml-0.5">₱{{ $nonAudited }}</span>
                            </div>
                            <!-- Cancelled -->
                            <div class="flex items-center px-2 py-1 bg-gray-50 border border-gray-300 rounded-md whitespace-nowrap">
                                <i class="uil uil-ban text-xs text-gray-500 mr-1 flex-shrink-0"></i>
                                <span class="text-xs text-gray-500">Void:</span>
                                <span class="text-xs font-bold text-gray-700 ml-0.5">₱{{ $cancelled }}</span>
                            </div>
                        </div>
                        
                        {{-- Pagination --}}
                        <div class="flex-shrink-0">
                            <nav aria-label="Page navigation example" class="shadow-xl">
                                <ul class="inline-flex items-center -space-x-px">
                                    <li>
                                        <a href="{{ $baseUrl . '&page=' . $prev }}" 
                                        class="{{ ($page == 1) ? 'pointer-events-none opacity-50' : '' }} block w-7 h-7 leading-7 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 text-xs">
                                            <i class="uil uil-angle-left-b"></i>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li>
                                        <p class="z-10 block font-semibold leading-7 text-center text-gray-500 bg-white border border-gray-300 w-7 h-7 text-xs">{{ $page }}</p>
                                    </li>
                                    <li>
                                        <a href="{{ $baseUrl . '&page=' . $next }}" 
                                        class="{{ ($to == $menuCount) ? 'pointer-events-none opacity-50' : '' }} block w-7 h-7 leading-7 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 text-xs">
                                            <i class="uil uil-angle-right-b"></i>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                {{-- PAGINATION END --}}       
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
            
            // ===== SEARCHABLE CLIENT DROPDOWN FUNCTIONS =====
            window.filterClients = function() {
                const input = document.getElementById('client_search');
                const filter = input.value.toLowerCase().trim();
                const dropdown = document.getElementById('clientDropdown');
                const items = dropdown.querySelectorAll('.client-option');
                const noResults = document.getElementById('clientNoResults');
                
                let hasVisible = false;
                
                items.forEach(item => {
                    const text = item.getAttribute('data-text').toLowerCase();
                    const value = item.getAttribute('data-value').toLowerCase();
                    
                    if (text.includes(filter) || value.includes(filter)) {
                        item.style.display = 'flex';
                        hasVisible = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                if (noResults) {
                    if (!hasVisible && filter !== '') {
                        noResults.style.display = 'block';
                    } else {
                        noResults.style.display = 'none';
                    }
                }
                
                if (filter !== '' || hasVisible) {
                    dropdown.classList.add('visible');
                }
            };
            
            window.showClientDropdown = function() {
                const dropdown = document.getElementById('clientDropdown');
                dropdown.classList.add('visible');
                filterClients();
            };
            
            window.selectClient = function(value, text) {
                document.getElementById('client_search').value = text;
                document.getElementById('selected_client_id').value = value;
                document.getElementById('clientDropdown').classList.remove('visible');
                
                // Update the form to reflect the selection
                const form = document.getElementById('filterForm');
                // The hidden input already has the value, the form will submit it
            };
            
            window.handleClientSearchKeydown = function(event) {
                const dropdown = document.getElementById('clientDropdown');
                const visibleItems = Array.from(dropdown.querySelectorAll('.client-option:not([style*="display: none"])'));
                const allItemsHeader = dropdown.querySelector('.all-items-header');
                
                // Include "All Clients" in navigation if it's visible
                let allItems = [];
                if (allItemsHeader && allItemsHeader.style.display !== 'none') {
                    allItems = [allItemsHeader];
                }
                const allVisible = [...allItems, ...visibleItems];
                
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    
                    // Find currently highlighted item
                    const currentFocus = dropdown.querySelector('.active');
                    let index = allVisible.indexOf(currentFocus);
                    
                    if (event.key === 'ArrowDown') {
                        index = (index + 1) % allVisible.length;
                    } else {
                        index = (index - 1 + allVisible.length) % allVisible.length;
                    }
                    
                    dropdown.querySelectorAll('.active').forEach(item => {
                        item.classList.remove('active');
                    });
                    
                    if (allVisible[index]) {
                        allVisible[index].classList.add('active');
                        allVisible[index].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    
                    const highlighted = dropdown.querySelector('.active');
                    if (highlighted) {
                        highlighted.click();
                    } else if (allVisible.length === 1) {
                        allVisible[0].click();
                    }
                } else if (event.key === 'Escape') {
                    dropdown.classList.remove('visible');
                }
            };
            
            // Click outside to close dropdown
            document.addEventListener('click', function(event) {
                const wrapper = document.querySelector('.searchable-dropdown-wrapper');
                const dropdown = document.getElementById('clientDropdown');
                
                if (wrapper && !wrapper.contains(event.target)) {
                    dropdown.classList.remove('visible');
                }
            });
            
            // Initialize selected client display
            document.addEventListener('DOMContentLoaded', function() {
                const selectedValue = document.getElementById('selected_client_id').value;
                if (selectedValue && selectedValue !== 'all') {
                    const dropdown = document.getElementById('clientDropdown');
                    const items = dropdown.querySelectorAll('.client-option');
                    
                    for (let i = 0; i < items.length; i++) {
                        if (items[i].getAttribute('data-value') === selectedValue) {
                            document.getElementById('client_search').value = items[i].getAttribute('data-text');
                            break;
                        }
                    }
                }
            });

            // ===== END SEARCHABLE CLIENT DROPDOWN =====
            
            $('.returnButton').click(function(e){
                e.stopPropagation();
                var slug = $(this).data('slug');
                $('#returnAccept').data('slug', slug);
                $('#openreturnModal').click();
            });
            
            $('#returnAccept').click(function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();

                $.ajax({
                    url: "{{ route('transactions.returnTable') }}",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        slug: slug,
                        _token: _token
                    },
                    success: function(response) {
                        if (response.message) {
                            alert(response.message);
                            location.reload();
                        } else if (response.error) {
                            alert(response.error);
                        }
                    }
                });
            });
        });
        // ===== PRINT RECEIPT FUNCTION =====
        function printReceipt(transactionId) {
            const printUrl = "{{ url('/transactions/print') }}/" + transactionId;

            const newTab = window.open(printUrl, '_blank');
            if (newTab) {
                newTab.focus();
            }
        }
        
        function showTopNotification(message, type = 'success') {
            const notification = document.getElementById('topNotification');
            const icon = document.getElementById('topNotificationIcon');
            const messageText = document.getElementById('topNotificationMessage');

            if (!notification || !icon || !messageText) return;

            const isError = type === 'error';
            notification.className = `fixed left-1/2 top-4 z-[60] w-[min(92vw,24rem)] -translate-x-1/2 rounded-lg border px-4 py-3 shadow-lg ${isError ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'}`;
            icon.className = `uil ${isError ? 'uil-times-circle' : 'uil-cloud-check'} text-xl`;
            messageText.textContent = message || (isError ? 'Something went wrong.' : 'Success');
            notification.classList.remove('hidden');

            clearTimeout(window.topNotificationTimer);
            window.topNotificationTimer = setTimeout(() => {
                notification.classList.add('hidden');
            }, 4000);
        }

        window.showTopNotification = showTopNotification;

        // Fix for date picker - reinitialize if needed
        if (typeof datepicker !== 'undefined') {
            // Reinitialize datepicker if needed
        }

        // ===== PRINT FUNCTION - Direct Print Without New Tab =====
        function printTransactions() {
            // Show the print dialog directly
            window.print();
        }
    </script>
</x-app-layout>