<x-app-layout>
    <style>
        /* =========================================================
        PAGE SHELL — full viewport, only the table scrolls
        ========================================================= */
        html, body { height: 100%; overflow: hidden; }

        .expenses-page {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            overflow-x: clip;
            box-sizing: border-box;
        }
        .expenses-page > .bg-white.shadow-md.rounded-lg {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }
        .expenses-page .bg-white.rounded-lg.shadow-md.p-4.mb-4,
        .expenses-page .grid { flex: 0 0 auto;
        }
        /* Slightly smaller overall fonts for General Expenses */
        .expenses-page { font-size: 13px; }
        .expenses-table { font-size: 13px; }
        .expenses-table > thead > tr > th { font-size: 10px; }
        .line-items-wrap table { font-size: 11px; }
        .line-items-wrap table thead th { font-size: 9px; }
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
        TABLE SHELL — fixed layout, widths come from <colgroup>
        ========================================================= */
        .expenses-table {
            width: 100%;
            min-width: 990px;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        /* Sticky header */
        .journal-scroll > .expenses-table > thead > tr > th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #f3f4f6;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 0 #e5e7eb;
            white-space: nowrap;
        }
        .journal-scroll > .expenses-table > thead > tr > th:first-child {
            left: 0;
            z-index: 30;
            background-color: #eef0f3;
            border-right: 1px solid #e5e7eb;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.12);
            text-align: center;
        }

        /* Sticky reference column */
        tbody.transaction-group > tr > td.txn-col {
            position: sticky;
            left: 0;
            z-index: 10;
            width: 180px;
            min-width: 180px;
            max-width: 180px;
            border-right: 1px solid #e5e7eb;
            vertical-align: middle;
            text-align: center;
            font-variant-numeric: tabular-nums;
            padding: 16px 12px;
            white-space: normal;
            word-break: break-word;
        }

        /* Transaction group card */
        tbody.transaction-group {
            position: relative;
            outline: 1px solid #e5e7eb;
            outline-offset: -1px;
            border-radius: 12px;
        }
        tbody.transaction-group > tr > td,
        tbody.transaction-group > tr > th {
            border-top: 1px solid #eef0f3;
            transition: background-color 0.18s ease;
        }

        /* Zebra — vertical (columns) */
        .expenses-table > thead > tr > th:nth-child(even) { background-color: #eef0f3; }
        .expenses-table > thead > tr > th:nth-child(odd)  { background-color: #f8f9fb; }
        .expenses-table > thead > tr > th:first-child     { background-color: #eef0f3; }

        .expenses-table > tbody.transaction-group > tr > td:nth-child(even):not(.txn-col) { background-color: #f4f6f9; }
        .expenses-table > tbody.transaction-group > tr > td:nth-child(odd):not(.txn-col)  { background-color: #ffffff; }
        .expenses-table > tbody.transaction-group > tr > td.txn-col { background-color: #e8ebef; }
        .expenses-table > tbody.transaction-group:nth-of-type(even) > tr > td.txn-col { background-color: #dfe3e8; }

        /* Hover */
        .expenses-table > tbody.transaction-group:hover > tr > td:not(.txn-col),
        .expenses-table > tbody.transaction-group:hover > tr > th {
            background-color: #dbe4ff !important;
        }
        .expenses-table > tbody.transaction-group:hover > tr > td.txn-col { background-color: #dde3f5; }
        tbody.transaction-group::before {
            content: '';
            position: absolute;
            left: 0; top: 8px; bottom: 8px;
            width: 3px;
            border-radius: 3px;
            background-color: transparent;
            transition: background-color 0.18s ease;
            pointer-events: none;
        }
        tbody.transaction-group:hover::before { background-color: #3b82f6; }

        /* Row borders */
        tbody.transaction-group > tr > td:first-child,
        tbody.transaction-group > tr > th:first-child { border-left: 1px solid #eef0f3; }
        tbody.transaction-group > tr > td:last-child,
        tbody.transaction-group > tr > th:last-child  { border-right: 1px solid #eef0f3; }
        tbody.transaction-group > tr + tr > td { border-top: none; }

        /* Separator between the money columns and the payment/status columns (col 6 = W/Tax) */
        .expenses-table > thead > tr > th:nth-child(6),
        .expenses-table > tbody.transaction-group > tr:first-child > td:nth-child(6) {
            border-right: 2px solid #9ca3af;
        }

        /* Line-items sub-table */
        .line-items-wrap {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            background: #ffffff;
            width: 100%;
        }
        .line-items-wrap table { width: 100%; min-width: 900px; }
        .line-items-wrap table thead th {
            position: static !important;
            z-index: auto !important;
            background: #f9fafb;
            color: #6b7280;
            font-weight: 600;
            font-size: 10px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            box-shadow: none !important;
        }
        .line-items-wrap table tbody tr:hover { background: #f9fafb; }

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
        .status-dot.dot-unpaid   { background: #ef4444; box-shadow: 0 0 0 3px #fee2e2; }

        /* Approved transaction — light green tint */
        .expenses-table > tbody.transaction-group.status-approved > tr > td,
        .expenses-table > tbody.transaction-group.status-approved > tr > th {
            background-color: #e8f7ec !important;
        }
        .expenses-table > tbody.transaction-group.status-approved > tr > td.txn-col {
            background-color: #d6efdc !important;
        }
        .expenses-table > tbody.transaction-group.status-approved:hover > tr > td,
        .expenses-table > tbody.transaction-group.status-approved:hover > tr > th {
            background-color: #cfead6 !important;
        }
        .expenses-table > tbody.transaction-group.status-approved:hover > tr > td.txn-col {
            background-color: #c2e3cb !important;
        }

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

        /* =========================================================
        EDIT TRANSACTION MODAL — liitan + responsive
        ========================================================= */
        .edit-transaction-card {
            max-width: min(1000px, 92vw);
            width: 100%;
            height: 85vh;
            max-height: 85vh;
        }
        @media (max-width: 640px) {
            .edit-transaction-card {
                max-width: 96vw;
                height: 92vh;
                max-height: 92vh;
            }
        }

    </style>

    @include('partials.expenses-responsive')

    @section('page_title', "GENERAL EXPENSES")



    {{-- DELETE REQUEST MODAL --}}
    <div id="deleteModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-sm">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h2 class="text-base font-bold">Request Deletion — <span id="deleteTransId"></span></h2>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-700 text-xl leading-none transition-colors">&times;</button>
            </div>
            <div class="px-4 py-3 space-y-3">
                <div>
                    <label class="text-xs font-semibold text-gray-600 uppercase">Reason<span class="text-red-500">*</span></label>
                    <textarea id="deleteReason" rows="3" class="w-full border border-gray-300 rounded-lg text-sm p-2 mt-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Why should this be deleted?"></textarea>
                </div>
                <p id="deleteError" class="hidden text-xs text-red-600"></p>
            </div>
            <div class="flex justify-end gap-2 px-4 py-3 border-t">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors">Cancel</button>
                <button id="confirmDeleteBtn" onclick="submitDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">Submit Request</button>
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
    {{-- NOTIFICATION END --}}

    {{-- ERROR NOTIFICATION --}}
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
    {{-- ERROR NOTIFICATION END --}}

    <div class="lg:ml-64 expenses-page-shell">
    <div class="p-3 lg:pt-3 max-w-full expenses-page">

        {{-- HEADER / ACTIONS BAR --}}
        <div class="bg-white rounded-lg shadow-md p-4 mb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('expenses.add', ['form' => 'cost']) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-plus mr-1"></i>ADD COST OF SALES
                    </a>
                    <a href="{{ route('expenses.add', ['form' => 'operating']) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-plus mr-1"></i>ADD OPERATING EXP
                    </a>
                </div>

                <form method="GET" action="{{ url('/expenses/1') }}" id="filterForm" class="flex flex-wrap items-center gap-2">
                    @foreach (collect(request()->query())->filter(fn ($v, $k) => str_starts_with($k, 'f_') && filled($v)) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach

                    <label class="text-sm text-gray-500">From</label>
                    <input type="date" name="from" id="fromInput" value="{{ $from ?? '' }}"
                           class="border border-gray-300 rounded-lg text-sm p-2 transition-colors focus:ring-blue-500 focus:border-blue-500">
                    <label class="text-sm text-gray-500">To</label>
                    <input type="date" name="to" id="toInput" value="{{ $to ?? '' }}"
                           class="border border-gray-300 rounded-lg text-sm p-2 transition-colors focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-search"></i> Generate
                    </button>
                    <a href="{{ url('/expenses/1') }}" id="resetFiltersBtn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                        Reset
                    </a>

                    <span class="w-px h-6 bg-gray-200 mx-1 hidden lg:inline-block"></span>

                    <button type="button" class="journal-filter-btn" data-column="classification" data-title="Classification">
                        Classification <i class="uil uil-filter"></i>
                    </button>
                    <button type="button" class="journal-filter-btn" data-column="category" data-title="Category">
                        Category <i class="uil uil-filter"></i>
                    </button>
                    <button type="button" class="journal-filter-btn" data-column="supplier" data-title="Supplier">
                        Supplier <i class="uil uil-filter"></i>
                    </button>
                    <button type="button" class="journal-filter-btn" data-column="e_approval" data-title="Status">
                        Status <i class="uil uil-filter"></i>
                    </button>

                    <div class="search-slot">
                        <div id="searchShell" class="search-shell">
                            <input type="text" id="searchInput" name="search"
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
                    'f_e_transid'      => 'Transaction ID',
                    'f_created_at'     => 'Date',
                    'f_classification' => 'Classification',
                    'f_category'       => 'Category',
                    'f_supplier'       => 'Supplier',
                    'f_item_name'      => 'Item Name',
                    'f_unit'           => 'Unit',
                    'f_qty'            => 'QTY',
                    'f_unit_price'     => 'Unit Price',
                    'f_amount'         => 'Amount',
                    'f_total'          => 'TOTAL',
                    'f_e_approval'     => 'Status',
                ];

                $chipUrl = function ($removeKey = null) {
                    $q = collect(request()->query())
                        ->except(array_filter(['page', $removeKey]))
                        ->filter(fn ($v) => filled($v))
                        ->all();
                    return url('/expenses/1') . ($q ? '?' . http_build_query($q) : '');
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
                    <a href="{{ url('/expenses/1') . ($resetQ ? '?' . http_build_query($resetQ) : '') }}"
                       id="resetFiltersChipsLink"
                       class="text-xs text-gray-400 hover:text-gray-600 underline ml-1">Reset filters</a>
                </div>
            @endif
        </div>

        {{-- TABLE --}}
        <div class="bg-white shadow-md rounded-lg p-4 w-full max-w-full">

            <div id="inventoryTable" class="journal-scroll w-full {{ $e_inventories->isEmpty() ? 'is-empty' : '' }}">
                <table id="expensesTable" class="expenses-table text-sm text-left text-gray-500">
                    <colgroup>
                        <col style="width:180px">   {{-- Transaction ID --}}
                        <col style="width:140px">   {{-- Date --}}
                        <col style="width:90px">    {{-- Items --}}
                        <col style="width:150px">   {{-- Total --}}
                        <col style="width:120px">   {{-- VAT --}}
                        <col style="width:120px">   {{-- W/Tax --}}
                        <col style="width:150px">   {{-- Server/Cashier --}}
                        <col style="width:80px">    {{-- Actions --}}
                    </colgroup>
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">Transaction ID</th>
                            <th class="px-4 py-3 whitespace-nowrap">Date</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Items</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">Total</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">VAT</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">W/Tax</th>
                            <th class="px-4 py-3 whitespace-nowrap">Server/Cashier</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>

                    @forelse ($e_inventories as $i_e)
                        @php
                            $items = $lineItemsByEId->get($i_e->e_id, collect());
                        @endphp
                        <tbody class="transaction-group {{ $i_e->e_approval == '2' ? 'status-approved' : '' }}">
                            <tr>
                                <td rowspan="2" class="txn-col whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 tracking-wide">{{ $i_e->e_transid }}</span>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex flex-col leading-tight">
                                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($i_e->created_at)->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($i_e->created_at)->format('h:i A') }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-center whitespace-nowrap text-xs text-gray-400 italic">
                                    {{ $items->count() }} item(s)
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-bold text-gray-900">
                                    ₱{{ number_format((float) ($i_e->e_total ?? 0), 2) }}
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">
                                    ₱{{ number_format((float) ($i_e->e_vat ?? 0), 2) }}
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">
                                    ₱{{ number_format((float) ($i_e->e_wtax ?? 0), 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($i_e->firstname ?? 'A', 0, 1) . substr($i_e->lastname ?? 'X', 0, 1)) }}
                                        </div>
                                        <span class="text-gray-700">{{ $i_e->firstname }} {{ $i_e->lastname }}</span>
                                    </div>
                                </td>


                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex justify-center items-center gap-1">
                                        @if ($i_e->e_approval != '2')
                                            <button type="button"
                                                onclick="openEditModal({{ $i_e->e_id }})"
                                                class="editButton text-blue-600 hover:text-blue-700 hover:bg-blue-50 p-2 rounded-lg transition"
                                                title="Edit Transaction">
                                                <i class="uil uil-edit-alt text-lg"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="delete-btn text-red-600 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition"
                                                data-id="{{ $i_e->e_id }}"
                                                data-transid="{{ $i_e->e_transid }}"
                                                title="Request Deletion">
                                                <i class="uil uil-trash-alt text-lg"></i>
                                            </button>
                                        @else
                                            <span class="text-gray-300 p-2" title="Approved transactions can't be edited or deleted">
                                                <i class="uil uil-lock text-lg"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="7" class="px-4 pt-0 pb-3">
                                    @if ($items->isEmpty())
                                        <div class="line-items-wrap px-4 py-3 text-xs text-gray-400 italic">No line items.</div>
                                    @else
                                        <div class="line-items-wrap">
                                            <table class="w-full text-xs text-left text-gray-600">
                                                <thead>
                                                    <tr>
                                                        <th class="px-3 py-2">Classification</th>
                                                        <th class="px-3 py-2 text-center">Category</th>
                                                        <th class="px-3 py-2 text-center">Supplier</th>
                                                        <th class="px-3 py-2">Item Name</th>
                                                        <th class="px-3 py-2">Unit</th>
                                                        <th class="px-3 py-2 text-center">Qty</th>
                                                        <th class="px-3 py-2 text-right">Unit Price</th>
                                                        <th class="px-3 py-2 text-right">Amount</th>
                                                        <th class="px-3 py-2 text-center">Mode of Payment</th>
                                                        <th class="px-3 py-2 text-center">Payment</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($items as $item)
                                                        <tr class="bg-white border-t border-gray-100">
                                                            <td class="px-3 py-2 whitespace-nowrap">
                                                                <span class="inline-flex items-center gap-1.5 font-semibold {{ $item->exp_type == 1 ? 'text-emerald-600' : 'text-blue-600' }}">
                                                                    <i class="uil {{ $item->exp_type == 1 ? 'uil-receipt' : 'uil-shopping-cart' }}"></i>
                                                                    {{ $item->exp_type == 1 ? 'Operating' : 'Cost of Sales' }}
                                                                </span>
                                                            </td>
                                                            <td class="px-3 py-2 text-center text-gray-600">{{ $item->category_name ?? 'N/A' }}</td>
                                                            <td class="px-3 py-2 text-center text-gray-600">{{ $item->supplier_name ?? 'N/A' }}</td>
                                                            <td class="px-3 py-2 text-gray-700">{{ $item->remarks }}</td>
                                                            <td class="px-3 py-2 text-gray-600">{{ $item->unit }}</td>
                                                            <td class="px-3 py-2 text-center tabular-nums text-gray-700">{{ $item->quantity }}</td>
                                                            <td class="px-3 py-2 text-right tabular-nums text-gray-700">₱{{ number_format((float) ($item->unit_price ?? 0), 2) }}</td>
                                                            <td class="px-3 py-2 text-right tabular-nums font-semibold text-gray-900">₱{{ number_format((float) ($item->amount ?? 0), 2) }}</td>
                                                            <td class="px-3 py-2 text-center text-gray-600">{{ $item->is_paid ? ucfirst($item->is_paid) : '—' }}</td>
                                                            <td class="px-3 py-2 text-center">
                                                                @if ((int) ($item->payment_class ?? 0) === 0)
                                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200">
                                                                        <span class="status-dot dot-unpaid"></span>Unpaid
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-800 ring-1 ring-green-200">
                                                                        <span class="status-dot dot-approved"></span>Paid
                                                                    </span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr class="empty-state-row">
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                                            <i class="uil uil-file-search-alt text-2xl"></i>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500">No expense transactions found</p>
                                        <p class="text-xs">Try adjusting your search or add a new expense.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
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
                        ->only([
                            'from', 'to', 'search',
                            'f_e_transid', 'f_created_at', 'f_classification', 'f_category',
                            'f_supplier', 'f_item_name', 'f_unit', 'f_qty', 'f_unit_price',
                            'f_amount', 'f_total', 'f_e_approval',
                        ])
                        ->filter(fn ($v) => $v !== null && $v !== '')
                        ->toArray();

                    $buildPageUrl = function ($p) use ($queryParams) {
                        $url = url('/expenses/' . $p);
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
            var FILTER_KEY = 'genExpFilterState';
            var RESET_FLAG = 'genExpJustReset';

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
            var FILTER_KEY = 'genExpFilterState';
            var RESET_FLAG = 'genExpJustReset';

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

            /* ---------- Column filter dropdown ---------- */
            var $dd       = $('#filterDropdown');
            var $ddList   = $('#filterDropdownList');
            var $ddTtl    = $('#filterDropdownTitle');
            var $ddSearch = $('#filterDropdownSearch');
            var currentCol = null;

            var FILTER_OPTIONS = {
                classification: [
                    { value: 'Operating', label: 'Operating' },
                    { value: 'Cost of Sales', label: 'Cost of Sales' },
                ],
                category: @json($expcats->pluck('name')->map(fn ($n) => ['value' => $n, 'label' => $n])->values()),
                supplier: @json($supps->pluck('name')->map(fn ($n) => ['value' => $n, 'label' => $n])->values()),
                e_approval: [
                    { value: 'Waiting', label: 'Waiting' },
                    { value: 'Approved', label: 'Approved' },
                ],
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
                url.pathname = url.pathname.replace(/\/expenses\/\d+/, '/expenses/1');

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

            /* ---------- Delete-request modal ---------- */
            window.deleteTargetId = null;

            $(document).on('click', '.delete-btn', function () {
                window.deleteTargetId = $(this).data('id');
                $('#deleteTransId').text($(this).data('transid'));
                $('#deleteReason').val('');
                $('#deleteError').addClass('hidden');
                $('#confirmDeleteBtn').prop('disabled', false).text('Submit Request');
                $('#deleteModal').removeClass('hidden');
            });
        });

        window.closeDeleteModal = function () {
            document.getElementById('deleteModal').classList.add('hidden');
        };

        window.submitDelete = function () {
            const reason = document.getElementById('deleteReason').value.trim();
            const err = document.getElementById('deleteError');
            const showErr = m => { err.innerText = m; err.classList.remove('hidden'); };

            if (reason.length < 5) return showErr('Please enter a reason (at least 5 characters).');

            const fd = new FormData();
            fd.append('slug', window.deleteTargetId);
            fd.append('reason', reason);

            const btn = document.getElementById('confirmDeleteBtn');
            btn.disabled = true;
            btn.innerText = 'Submitting...';

            console.log('POST tran_remove', { slug: window.deleteTargetId, reason: reason });

            fetch('{{ route("expenses.tran_remove") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: fd
            })
            .then(async res => {
                console.log('Response status:', res.status);
                const data = await res.json();
                console.log('Response body:', data);
                return { ok: res.ok, status: res.status, data };
            })
            .then(({ ok, status, data }) => {
                if (ok && data.success) {
                    closeDeleteModal();
                    showSuccessToast(data.message || 'Deletion request submitted for approval.');
                    setTimeout(() => location.reload(), 1400);
                    return;
                }
                const msg = data.message || Object.values(data.errors || {}).flat()[0] || `Request failed (${status}).`;
                showErr(msg);
                btn.disabled = false;
                btn.innerText = 'Submit Request';
            })
            .catch((e) => {
                console.error('Delete error:', e);
                showErr('Something went wrong: ' + e.message);
                btn.disabled = false;
                btn.innerText = 'Submit Request';
            });
        };
    </script>

    {{-- SUCCESS TOAST --}}
    <div id="successToast" class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div id="successToastCard" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 flex flex-col items-center gap-4 scale-95 opacity-0 transition-all duration-200">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="uil uil-check-circle text-4xl text-emerald-600"></i>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-bold text-gray-900">Success</h3>
                <p id="successToastMessage" class="text-sm text-gray-500 mt-1">Data transferred successfully.</p>
            </div>
            <div class="w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                <div id="successToastBar" class="h-full bg-emerald-500 w-full origin-left"></div>
            </div>
        </div>
    </div>

    <script>
        function showSuccessToast(message) {
            const wrap = document.getElementById('successToast');
            const card = document.getElementById('successToastCard');
            const msg  = document.getElementById('successToastMessage');
            const bar  = document.getElementById('successToastBar');
            if (!wrap || !card) return;

            msg.textContent = message || 'Data transferred successfully.';
            wrap.classList.remove('hidden');

            requestAnimationFrame(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            });

            bar.style.transition = 'none';
            bar.style.transform  = 'scaleX(1)';
            requestAnimationFrame(() => {
                bar.style.transition = 'transform 1300ms linear';
                bar.style.transform  = 'scaleX(0)';
            });

            setTimeout(() => {
                card.classList.add('scale-95', 'opacity-0');
                card.classList.remove('scale-100', 'opacity-100');
                setTimeout(() => wrap.classList.add('hidden'), 200);
            }, 1400);
        }
    </script>

    {{-- EDIT TRANSACTION MODAL — loads edit2 page inside an iframe --}}
        <div id="editTransactionModal"
            class="hidden" 
            style="position: fixed; inset: 0; z-index: 2147483647;
                    background-color: rgba(0, 0, 0, 0.6);
                    display: none; align-items: center; justify-content: center;
                    padding: 1rem;">
            <div class="bg-white rounded-lg shadow-2xl w-full flex flex-col edit-transaction-card">

                <div class="flex items-center justify-between px-4 py-3 border-b flex-shrink-0">
                    <h2 class="text-base font-bold text-gray-800">Edit Transaction</h2>
                    <button type="button" onclick="closeEditModal()"
                            class="text-gray-400 hover:text-gray-700 text-2xl leading-none transition-colors"
                            aria-label="Close">&times;</button>
                </div>

                <div class="flex-1 min-h-0 bg-gray-100">
                    <iframe id="editTransactionFrame"
                            src="about:blank"
                            class="w-full h-full border-0 block"
                            title="Edit Transaction"></iframe>
                </div>
            </div>
        </div>

        <script>
            function openEditModal(eId) {
                var url = "{{ url('/expenses/edit2') }}/" + eId;
                var modal = document.getElementById('editTransactionModal');
                var frame = document.getElementById('editTransactionFrame');

                frame.src = url;
                modal.classList.remove('hidden');
                modal.style.display = 'flex';              // force the layout
                modal.style.position = 'fixed';
                modal.style.inset = '0';
                modal.style.zIndex = '2147483647';
                document.body.style.overflow = 'hidden';
            }

            function closeEditModal() {
                var modal = document.getElementById('editTransactionModal');
                var frame = document.getElementById('editTransactionFrame');

                modal.classList.add('hidden');
                modal.style.display = 'none';              // clear
                frame.src = 'about:blank';
                document.body.style.overflow = '';

                location.reload();
            }

            // Listen for "done" signal from the iframe
            window.addEventListener('message', function (e) {
                if (e.data && e.data.type === 'editTransactionDone') {
                    closeEditModal();
                }
            });
            document.addEventListener('DOMContentLoaded', function () {
                var modal = document.getElementById('editTransactionModal');
                if (modal && modal.parentNode !== document.body) {
                    document.body.appendChild(modal);
                }
            });
        </script>
</x-app-layout>