<x-app-layout>
    <style>
        /* =========================================================
        PAGE SHELL — full viewport, only the table scrolls
        ========================================================= */
        html, body { height: 100%; overflow: hidden; }

        .revolving-page {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            overflow-x: clip;
            box-sizing: border-box;
        }
        .revolving-page > .bg-white.shadow-md.rounded-lg {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }
        .revolving-page .bg-white.rounded-lg.shadow-md.p-4.mb-4,
        .revolving-page .grid { flex: 0 0 auto; }

        /* Slightly smaller overall fonts for Revolving Budget */
        .revolving-page { font-size: 13px; }
        .revolving-table { font-size: 13px; }
        .revolving-table > thead > tr > th { font-size: 10px; }
        .journal-filter-btn { font-size: 12px; padding: 6px 12px; }
        .search-shell input { font-size: 12px; }

        /* =========================================================
        SCROLL BOX (only scrolling region)
        ========================================================= */
        .journal-scroll {
            flex: 1 1 auto;
            min-height: 0;
            max-height: none !important;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow: auto;
            scroll-behavior: smooth;
            position: relative;
        }

        /* =========================================================
        TABLE SHELL — fixed layout, widths from <colgroup>
        ========================================================= */
        .revolving-table {
            width: 100%;
            min-width: 1200px;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        /* Sticky header */
        .journal-scroll > .revolving-table > thead > tr > th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #f3f4f6;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 0 #e5e7eb;
            white-space: nowrap;
            color: #374151;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 10px 14px;
        }
        .journal-scroll > .revolving-table > thead > tr > th:first-child {
            left: 0;
            z-index: 30;
            background-color: #eef0f3;
            border-right: 1px solid #e5e7eb;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.12);
            text-align: center;
        }

        /* Sticky first column (Category) */
        .revolving-table > tbody > tr > td.sticky-col {
            position: sticky;
            left: 0;
            z-index: 10;
            border-right: 1px solid #e5e7eb;
            vertical-align: middle;
            text-align: center;
            font-weight: 600;
            color: #111827;
            padding: 14px 12px;
            white-space: normal;
            word-break: break-word;
            font-variant-numeric: tabular-nums;
        }
        .revolving-table > tbody > tr > td.sticky-col::before {
            content: '';
            position: absolute;
            left: 0; top: 8px; bottom: 8px;
            width: 3px;
            border-radius: 3px;
            background-color: transparent;
            transition: background-color 0.18s ease;
            pointer-events: none;
        }

        /* Row cells */
        .revolving-table > tbody > tr > td {
            border-top: 1px solid #eef0f3;
            padding: 14px 14px;
            vertical-align: middle;
            transition: background-color 0.18s ease;
            font-variant-numeric: tabular-nums;
        }

        /* Zebra — vertical (columns) */
        .revolving-table > thead > tr > th:nth-child(even) { background-color: #eef0f3; }
        .revolving-table > thead > tr > th:nth-child(odd)  { background-color: #f8f9fb; }
        .revolving-table > thead > tr > th:first-child     { background-color: #eef0f3; }

        .revolving-table > tbody > tr > td:nth-child(even):not(.sticky-col) { background-color: #f4f6f9; }
        .revolving-table > tbody > tr > td:nth-child(odd):not(.sticky-col)  { background-color: #ffffff; }
        .revolving-table > tbody > tr > td.sticky-col                        { background-color: #e8ebef; }
        .revolving-table > tbody > tr:nth-child(even) > td.sticky-col        { background-color: #dfe3e8; }

        /* Hover */
        .revolving-table > tbody > tr:hover > td:not(.sticky-col) {
            background-color: #dbe4ff !important;
        }
        .revolving-table > tbody > tr:hover > td.sticky-col { background-color: #dde3f5 !important; }
        .revolving-table > tbody > tr:hover > td.sticky-col::before { background-color: #3b82f6; }

        /* Separator between the money columns and the remarks/action columns (col 8 = Total Amount) */
        .revolving-table > thead > tr > th:nth-child(8),
        .revolving-table > tbody > tr > td:nth-child(8) {
            border-right: 2px solid #9ca3af;
        }

        /* Truncate long text */
        .revolving-table td.particulars-col,
        .revolving-table td.description-col {
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: help;
        }

        /* Status dots */
        .status-dot {
            display: inline-block;
            width: 6px; height: 6px;
            border-radius: 9999px;
            margin-right: 6px;
            vertical-align: middle;
        }
        .status-dot.dot-pending  { background: #eab308; box-shadow: 0 0 0 3px #fef9c3; }
        .status-dot.dot-approved { background: #11d85a; box-shadow: 0 0 0 3px #dcfce7; }
        .status-dot.dot-draft    { background: #9ca3af; box-shadow: 0 0 0 3px #f3f4f6; }
        .status-dot.dot-blue     { background: #3b82f6; box-shadow: 0 0 0 3px #dbeafe; }
        .status-dot.dot-purple   { background: #8b5cf6; box-shadow: 0 0 0 3px #ede9fe; }
        .status-dot.dot-pink     { background: #ec4899; box-shadow: 0 0 0 3px #fce7f3; }

        /* Filter buttons (same as journal) */
        .journal-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 9999px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .journal-filter-btn:hover  { border-color: #93c5fd; color: #1d4ed8; }
        .journal-filter-btn.active { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }

        /* Search — fixed 38px slot; the shell expands leftward over it
           so opening the search never reflows the filter bar */
        .search-slot {
            position: relative;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            align-self: center;
            z-index: 40;
        }
        .search-shell {
            position: absolute;
            right: 0;
            top: 0;
            display: inline-flex;
            align-items: center;
            height: 38px;
            width: 38px;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: width 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s ease;
        }
        .search-shell:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
        .search-shell.open {
            width: 320px;
            max-width: calc(100vw - 48px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        .search-shell input {
            flex: 1; height: 100%; min-width: 0;
            border: 0; outline: 0; background: transparent;
            padding: 0 40px 0 16px;
            font-size: 14px; color: #111827;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease 0.1s;
        }
        .search-shell.open input { opacity: 1; pointer-events: auto; padding-right: 78px; }
        .search-shell input::placeholder { color: #9ca3af; }
        .search-shell .search-btn {
            position: absolute; right: 0; top: 0;
            width: 38px; height: 38px; border-radius: 9999px;
            background: transparent; color: #1e3a8a;
            display: inline-flex; align-items: center; justify-content: center;
            border: 0; cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
            z-index: 3;
        }
        .search-shell .search-btn i { font-size: 17px; }
        .search-shell.open .search-btn { background: #1d4ed8; color: #ffffff; }
        .search-shell.open .search-btn:hover { background: #1e40af; }
        .search-shell .search-close {
            position: absolute; right: 42px; top: 50%; transform: translateY(-50%);
            width: 26px; height: 26px; border-radius: 9999px;
            background: transparent; color: #9ca3af;
            display: inline-flex; align-items: center; justify-content: center;
            border: 0; cursor: pointer;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease 0.15s;
            z-index: 3;
        }
        .search-shell.open .search-close { opacity: 1; pointer-events: auto; }
        .search-shell .search-close:hover { color: #374151; background: #f3f4f6; }

        #filterDropdown { max-height: min(420px, calc(100vh - 100px)); flex-direction: column; }
        #filterDropdown:not(.hidden) { display: flex; }
    </style>

    @include('partials.expenses-responsive')

    @section('page_title', "REVOLVING BUDGET")

    {{-- LOADING --}}
    <div wire:loading id="loadingScreen" class="hidden fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-[60] overflow-hidden bg-gray-900 opacity-75 flex flex-col items-center justify-center">
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

    {{-- DELETE MODAL --}}
    <div id="itemDeleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
        <div class="relative w-full h-full max-w-2xl md:h-auto">
            <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                    <h3 class="font-semibold text-gray-900 flex items-center">
                        <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-red-700"></i>
                        <span class="text-red-700 text-base md:text-lg lg:text-xl">Delete Item</span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemDeleteModal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <div class="px-6 py-3 md:py-6 space-y-6">
                    <p class="text-xs md:text-base leading-relaxed text-gray-500">Are you sure you want to delete this item?</p>
                </div>
                <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                    <a type="button" href="" class="deleteConfirm w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>
                    <button data-modal-hide="itemDeleteModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    {{-- DELETE MODAL END --}}

    {{-- NOTIFICATION --}}
    @if (session('message'))
        <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-96 h-12 lg:ml-32 bg-emerald-200 rounded-lg z-50 shadow-md shadow-emerald-800 flex flex-row justify-between px-2">
            <div class="flex">
                <i class="self-center uil uil-cloud-check text-emerald-800 text-2xl"></i>
                <h1 class="self-center text-emerald-800 font-semibold ml-1">{{ session('message') }}</h1>
            </div>
            <button class="notifButton self-center">
                <i class="uil uil-times text-2xl text-emerald-800"></i>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-96 h-12 bg-red-200 rounded-lg z-50 shadow-md shadow-red-800 flex flex-row justify-between px-2">
            <div class="flex">
                <i class="self-center uil uil-cloud-check text-red-800 text-2xl"></i>
                <h1 class="self-right text-red-800 font-semibold ml-1">{{ session('error') }}</h1>
            </div>
            <button class="notifButton self-center">
                <i class="uil uil-times text-2xl text-red-800"></i>
            </button>
        </div>
    @endif
    {{-- NOTIFICATION END --}}

    <div class="lg:ml-64 revolving-page-shell">
    <div class="p-3 lg:pt-3 max-w-full revolving-page">

        {{-- HEADER / ACTIONS BAR --}}
        <div class="bg-white rounded-lg shadow-md p-4 mb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('revolving.add') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-plus mr-1"></i>ADD - REVOLVING EXPENSES
                    </a>
                    <a href="{{ route('revolving.budget_index') }}"
                       class="inline-flex items-center px-4 py-2 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-wallet mr-1"></i>CASH FUNDS
                    </a>
                </div>

                <form method="GET" action="{{ url('/revolving/1') }}" id="filterForm" class="flex flex-wrap items-center gap-2">
                    @foreach (collect(request()->query())->filter(fn ($v, $k) => str_starts_with($k, 'f_') && filled($v)) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach

                    <label class="text-sm text-gray-500">From</label>
                    <input type="date" name="from" id="fromInput" value="{{ $from ?? '' }}"
                           max="{{ now()->format('Y-m-d') }}"
                           class="border border-gray-300 rounded-lg text-sm p-2 transition-colors focus:ring-blue-500 focus:border-blue-500">
                    <label class="text-sm text-gray-500">To</label>
                    <input type="date" name="to" id="toInput" value="{{ $to ?? '' }}"
                           max="{{ now()->format('Y-m-d') }}"
                           class="border border-gray-300 rounded-lg text-sm p-2 transition-colors focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-search"></i> Generate
                    </button>
                    <a href="{{ url('/revolving/1') }}" id="resetFiltersBtn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                        Reset
                    </a>

                    <span class="w-px h-6 bg-gray-200 mx-1 hidden lg:inline-block"></span>

                    <button type="button" class="journal-filter-btn" data-column="category" data-title="Category">
                        Category <i class="uil uil-filter"></i>
                    </button>
                    <button type="button" class="journal-filter-btn" data-column="budget" data-title="Budget">
                        Budget <i class="uil uil-filter"></i>
                    </button>

                    <div class="search-slot">
                        <div id="searchShell" class="search-shell {{ !empty($search) ? 'open' : '' }}">
                            <input type="search" id="searchInput" name="search"
                                   placeholder="Search Here ...."
                                   value="{{ $search ?? '' }}" autocomplete="off">
                            <button id="searchClose" type="button" class="search-close" aria-label="Clear">
                                <i class="uil uil-times"></i>
                            </button>
                            <button id="searchSubmit" type="button" class="search-btn" aria-label="Search">
                                <i class="uil uil-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @php
                $activeColFilters = collect(request()->query())
                    ->filter(fn ($v, $k) => str_starts_with($k, 'f_') && $v !== '' && $v !== null)
                    ->all();
                $filterLabels = [
                    'f_category' => 'Category',
                    'f_budget'   => 'Budget',
                ];
                $chipUrl = function ($removeKey = null) {
                    $q = collect(request()->query())
                        ->except(array_filter(['page', $removeKey]))
                        ->filter(fn ($v) => filled($v))
                        ->all();
                    return url('/revolving/1') . ($q ? '?' . http_build_query($q) : '');
                };
                $resetQ = collect(request()->query())->only(['from', 'to', 'search'])->filter(fn ($v) => filled($v))->all();
            @endphp

            @if (count($activeColFilters))
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="text-xs text-gray-500 font-semibold uppercase">Filters:</span>
                    @foreach ($activeColFilters as $key => $val)
                        <a href="{{ $chipUrl($key) }}"
                           class="filter-chip-remove inline-flex items-center gap-1 text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-200 px-2.5 py-1 rounded-full hover:bg-blue-100">
                            {{ $filterLabels[$key] ?? $key }}: {{ $val }}
                            <i class="uil uil-times"></i>
                        </a>
                    @endforeach
                    <a href="{{ url('/revolving/1') . ($resetQ ? '?' . http_build_query($resetQ) : '') }}"
                       id="resetFiltersChipsLink"
                       class="text-xs text-gray-400 hover:text-gray-600 underline ml-1">Reset filters</a>
                </div>
            @endif
        </div>

        {{-- TABLE --}}
        <div class="bg-white shadow-md rounded-lg p-4 w-full max-w-full">

            <div id="inventoryTable" class="journal-scroll w-full">
                <table id="revolvingTable" class="revolving-table text-sm text-left text-gray-500">
                    <colgroup>
                        <col style="width:170px">   {{-- Category (sticky) --}}
                        <col style="width:160px">   {{-- Budget --}}
                        <col style="width:200px">   {{-- Particulars --}}
                        <col style="width:220px">   {{-- Description --}}
                        <col style="width:140px">   {{-- Date --}}
                        <col style="width:90px">    {{-- Quantity --}}
                        <col style="width:130px">   {{-- Unit Amount --}}
                        <col style="width:140px">   {{-- Total Amount --}}
                        <col style="width:180px">   {{-- Remarks --}}
                        <col style="width:100px">   {{-- Action --}}
                    </colgroup>
                    <thead>
                        <tr>
                            @php
                                $headers = [
                                    'cat_name'      => 'Category',
                                    'budget_name'   => 'Budget',
                                    'particulars'   => 'Particulars',
                                    'description'   => 'Description',
                                    'created_at'    => 'Date',
                                    'quantity'      => 'Quantity',
                                    'unit_amount'   => 'Unit Amount',
                                    'amount'        => 'Total Amount',
                                    'other_remarks' => 'Remarks',
                                    'action'        => 'Action',
                                ];
                            @endphp
                            @foreach ($headers as $col => $label)
                                <th scope="col" data-col="{{ $col }}" class="{{ $col === 'cat_name' ? 'text-center' : '' }}">
                                    {{ $label }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                    @forelse ($inventories as $inventory)
                        <tr>
                            <td data-col="cat_name" class="sticky-col text-center font-semibold text-gray-900">
                                {{ $inventory->cat_name ?? 'N/A' }}
                            </td>
                            <td data-col="budget_name" class="whitespace-nowrap">{{ $inventory->budget_name ?? 'N/A' }}</td>
                            <td data-col="particulars" class="particulars-col" title="{{ $inventory->particulars }}">{{ $inventory->particulars }}</td>
                            <td data-col="description" class="description-col" title="{{ $inventory->description }}">{{ $inventory->description }}</td>
                            <td data-col="created_at" class="whitespace-nowrap">
                                <div class="flex flex-col leading-tight">
                                    <span class="font-semibold text-gray-800">{{ $inventory->created_at->format('M d, Y') }}</span>
                                    <span class="text-xs text-gray-400">{{ $inventory->created_at->format('h:i A') }}</span>
                                </div>
                            </td>
                            <td data-col="quantity" class="text-center tabular-nums">{{ $inventory->quantity }}</td>
                            <td data-col="unit_amount" class="text-right tabular-nums text-gray-700">₱{{ number_format((float) $inventory->unit_amount, 2) }}</td>
                            <td data-col="amount" class="text-right tabular-nums font-bold text-gray-900">₱{{ number_format((float) $inventory->amount, 2) }}</td>
                            <td data-col="other_remarks" class="text-gray-600">{{ $inventory->other_remarks ?: '—' }}</td>
                            <td class="action-col text-center">
                                <div class="flex justify-center gap-1">
                                    <a href="{{ url('/revolving/edit/'.$inventory->id) }}"
                                       class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 p-2 rounded-lg transition"
                                       title="Edit">
                                        <i class="uil uil-edit-alt text-lg"></i>
                                    </a>
                                    <button
                                        type="button"
                                        class="deleteButton text-red-600 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition"
                                        data-id="{{ $inventory->id }}"
                                        data-modal-target="itemDeleteModal"
                                        data-modal-toggle="itemDeleteModal"
                                        title="Delete">
                                        <i class="uil uil-trash-alt text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                                        <i class="uil uil-file-search-alt text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500">No revolving expense entries found</p>
                                    <p class="text-xs">Try adjusting your search or add a new entry.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div id="toastNotification" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white text-sm font-bold px-4 py-3 rounded-lg shadow-lg z-50">
                Request for deletion sent successfully!
            </div>

            {{-- PAGINATION --}}
            <div class="grid md:grid-cols-2 mt-3 px-3">
                @php
                    $prev = $page - 1;
                    $next = $page + 1;
                    $rowFrom = ($prev * 100) + 1;
                    $rowTo   = $page * 100;
                    if ($rowTo > $invCount) { $rowTo = $invCount; }
                    if ($invCount == 0)     { $rowFrom = 0; }
                    $queryParams = collect(request()->query())
                        ->only(['from', 'to', 'search', 'f_category', 'f_budget'])
                        ->filter(fn ($v) => $v !== null && $v !== '')
                        ->toArray();
                    $buildPageUrl = function ($p) use ($queryParams) {
                        $url = url('/revolving/' . $p);
                        return $queryParams ? $url . '?' . http_build_query($queryParams) : $url;
                    };
                @endphp

                <div class="justify-self-center md:justify-self-start self-center">
                    <span class="text-sm text-gray-700">
                        Showing <span class="font-semibold text-gray-900">{{ $rowFrom }}</span>
                        to <span class="font-semibold text-gray-900">{{ $rowTo }}</span>
                        of <span class="font-semibold text-gray-900">{{ $invCount }}</span> Items
                    </span>
                </div>
                <div class="justify-self-center md:justify-self-end">
                    <nav aria-label="Page navigation" class="h-8 mb-0.5 shadow-xl">
                        <ul class="inline-flex items-center -space-x-px">
                            <li>
                                <a href="{{ $buildPageUrl($prev) }}"
                                class="{{ ($page == 1) ? 'pointer-events-none opacity-50' : '' }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                    <i class="uil uil-angle-left-b"></i>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            <li>
                                <p class="block w-9 h-9 leading-9 text-center z-10 text-gray-500 border border-gray-300 bg-white font-semibold">{{ $page }}</p>
                            </li>
                            <li>
                                <a href="{{ $buildPageUrl($next) }}"
                                class="{{ ($rowTo == $invCount) ? 'pointer-events-none opacity-50' : '' }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
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

    {{-- COLUMN FILTER DROPDOWN --}}
    <div id="filterDropdown"
         class="hidden fixed z-50 bg-white border border-gray-200 rounded-lg shadow-xl w-64"
         style="top:0; left:0;">
        <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
            <span id="filterDropdownTitle" class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Filter</span>
            <button type="button" id="filterDropdownClose" class="text-gray-400 hover:text-gray-600">
                <i class="uil uil-times"></i>
            </button>
        </div>

        <div class="px-3 pt-2">
            <input type="text" id="filterDropdownSearch"
                   class="w-full border border-gray-200 rounded-md text-xs p-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Search values...">
        </div>

        <div id="filterDropdownList" class="max-h-60 overflow-y-auto py-1 text-sm"></div>

        <div class="px-3 py-2 border-t border-gray-100 flex items-center justify-between">
            <button type="button" id="filterDropdownClear"
                    class="text-xs px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold">
                Clear
            </button>
            <button type="button" id="filterDropdownApply"
                    class="text-xs px-3 py-1.5 rounded-md bg-blue-700 hover:bg-blue-800 text-white font-semibold">
                Apply
            </button>
        </div>
    </div>

    <script>
        /* =========================================================
        FILTER PERSISTENCE ACROSS MODULES
        ========================================================= */
        (function () {
            var FILTER_KEY = 'revolvingFilterState';
            var RESET_FLAG = 'revolvingJustReset';

            try {
                var curSearch = window.location.search;
                var curFull   = window.location.pathname + curSearch;

                if (curSearch && curSearch.length > 1) {
                    sessionStorage.setItem(FILTER_KEY, curFull);
                    sessionStorage.removeItem(RESET_FLAG);
                } else if (sessionStorage.getItem(RESET_FLAG)) {
                    sessionStorage.removeItem(RESET_FLAG);
                    sessionStorage.removeItem(FILTER_KEY);
                } else {
                    var saved = sessionStorage.getItem(FILTER_KEY);
                    if (saved && saved !== curFull) {
                        window.location.replace(saved);
                    }
                }
            } catch (e) { /* ignore */ }
        })();
    </script>

    <script>
        $(document).ready(function () {
            var FILTER_KEY = 'revolvingFilterState';
            var RESET_FLAG = 'revolvingJustReset';

            function markFiltersJustReset() {
                try {
                    sessionStorage.setItem(RESET_FLAG, '1');
                    sessionStorage.removeItem(FILTER_KEY);
                } catch (e) { /* ignore */ }
            }

            $('#resetFiltersBtn').on('click', function () {
                markFiltersJustReset();
            });

            $(document).on('click', '#resetFiltersChipsLink, .filter-chip-remove', function () {
                try {
                    var destUrl = new URL(this.href, window.location.origin);
                    if (!destUrl.search || destUrl.search.length <= 1) {
                        markFiltersJustReset();
                    }
                } catch (e) { /* ignore */ }
            });

            var $shell = $('#searchShell');
            var $input = $('#searchInput');

            @if (!empty($search))
                $shell.addClass('open');
            @endif

            $('#searchSubmit').click(function () {
                if ($shell.hasClass('open')) {
                    $('#filterForm').submit();
                } else {
                    $shell.addClass('open');
                    setTimeout(function () { $input.focus(); }, 200);
                }
            });

            $('#searchClose').click(function (e) {
                e.stopPropagation();
                $input.val('');
                $shell.removeClass('open');
                $('#filterForm').submit();
            });

            $input.on('keydown', function (e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    $('#filterForm').submit();
                }
            });

            $(document).on('mousedown', function (e) {
                if ($shell.hasClass('open') && !$(e.target).closest('#searchShell').length) {
                    if ($input.val().trim() === '') {
                        $shell.removeClass('open');
                    }
                }
            });

            $('.notifButton').click(function () {
                $('.notif').addClass('hidden');
            });

            $('.deleteButton').click(function () {
                var id = $(this).data('id');
                $('.deleteConfirm').prop('href', `{{ url('/revolving/delete') }}/${id}`);
            });

            /* ---------- Column filter dropdown ---------- */
            var $dd       = $('#filterDropdown');
            var $ddList   = $('#filterDropdownList');
            var $ddTtl    = $('#filterDropdownTitle');
            var $ddSearch = $('#filterDropdownSearch');
            var currentCol = null;

            var FILTER_OPTIONS = {
                category: @json($expcats->pluck('name')->map(fn ($n) => ['value' => $n, 'label' => $n])->values()),
                budget: @json($budgetList->pluck('classification')->map(fn ($n) => ['value' => $n, 'label' => $n])->values()),
            };

            function renderList(options, selected, searchTerm) {
                $ddList.empty();
                var term = (searchTerm || '').toLowerCase();

                if (!term || 'all'.indexOf(term) !== -1) {
                    $ddList.append(
                        '<label class="flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50 cursor-pointer">' +
                            '<input type="radio" name="dd-filter" value="" ' + (selected === '' ? 'checked' : '') + ' class="form-radio text-blue-600">' +
                            '<span class="text-gray-700 font-semibold">All</span>' +
                        '</label>'
                    );
                }

                options.forEach(function (opt) {
                    var label = opt.label, value = opt.value;
                    if (term && label.toLowerCase().indexOf(term) === -1) return;
                    var checked   = (selected === value) ? 'checked' : '';
                    var safeVal   = $('<div>').text(value).html();
                    var safeLabel = $('<div>').text(label).html();
                    $ddList.append(
                        '<label class="flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50 cursor-pointer">' +
                            '<input type="radio" name="dd-filter" value="' + safeVal + '" ' + checked + ' class="form-radio text-blue-600">' +
                            '<span class="text-gray-700 truncate">' + safeLabel + '</span>' +
                        '</label>'
                    );
                });

                if (!$ddList.children().length) {
                    $ddList.append('<div class="px-3 py-2 text-xs text-gray-400 italic">No matches</div>');
                }
            }

            $(document).on('click', '.journal-filter-btn', function (e) {
                e.stopPropagation();
                var $btn = $(this);
                currentCol = $btn.data('column');

                var rect = this.getBoundingClientRect();
                var left = Math.min(rect.left, window.innerWidth - 270);
                $dd.css({
                    top:  (rect.bottom + 6) + 'px',
                    left: left + 'px'
                }).removeClass('hidden');

                $ddTtl.text($btn.data('title'));

                var params   = new URLSearchParams(window.location.search);
                var selected = params.get('f_' + currentCol) || '';

                $ddSearch.val('');
                renderList(FILTER_OPTIONS[currentCol] || [], selected, '');

                setTimeout(function () { $ddSearch.focus(); }, 50);
            });

            $('#filterDropdownClose').on('click', function () {
                $dd.addClass('hidden');
            });

            $(document).on('mousedown', function (e) {
                if (!$dd.hasClass('hidden') &&
                    !$(e.target).closest('#filterDropdown').length &&
                    !$(e.target).closest('.journal-filter-btn').length) {
                    $dd.addClass('hidden');
                }
            });

            $ddSearch.on('input', function () {
                var params   = new URLSearchParams(window.location.search);
                var selected = params.get('f_' + currentCol) || '';
                renderList(FILTER_OPTIONS[currentCol] || [], selected, $(this).val());
            });

            $('#filterDropdownApply').on('click', function () {
                var val = $ddList.find('input[name="dd-filter"]:checked').val() || '';
                applyColumnFilter(currentCol, val);
            });

            $('#filterDropdownClear').on('click', function () {
                applyColumnFilter(currentCol, '');
            });

            function applyColumnFilter(column, value) {
                var url = new URL(window.location.href);
                if (value === '') {
                    url.searchParams.delete('f_' + column);
                } else {
                    url.searchParams.set('f_' + column, value);
                }
                url.pathname = url.pathname.replace(/\/revolving\/\d+/, '/revolving/1');

                if (!url.search || url.search.length <= 1) {
                    markFiltersJustReset();
                }

                window.location.href = url.toString();
            }

            (function markActiveFilters() {
                var params = new URLSearchParams(window.location.search);
                document.querySelectorAll('.journal-filter-btn').forEach(function (el) {
                    if (params.get('f_' + el.dataset.column)) el.classList.add('active');
                });
            })();
        });
    </script>
</x-app-layout>