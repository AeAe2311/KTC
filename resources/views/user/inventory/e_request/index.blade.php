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
        .expenses-page .grid { flex: 0 0 auto; }

        /* =========================================================
        SCROLL BOX — the ONLY scrolling region
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
        TABLE SHELL — responsive min-width
        ========================================================= */
        .expenses-table {
            /* Narrower baseline so 100% zoom on a laptop shows the whole table.
               Wider screens get more breathing room; the sticky first column
               keeps the reference pinned while the rest scrolls. */
            min-width: 900px;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        /* =========================================================
        STICKY HEADER
        ========================================================= */
        .journal-scroll > .expenses-table > thead > tr > th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
            font-size: 11px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            box-shadow: 0 1px 0 #e5e7eb;
            white-space: nowrap;
            padding: 10px 16px;
        }
        .journal-scroll > .expenses-table > thead > tr > th:first-child {
            left: 0;
            z-index: 30;
            background-color: #eef0f3;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.12);
            text-align: center;
        }
        .journal-scroll > .expenses-table > thead > tr > th:last-child {
            border-right: none;
        }

        /* =========================================================
        STICKY REFERENCE COLUMN
        ========================================================= */
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

        /* =========================================================
        TRANSACTION CARD
        ========================================================= */
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

        /* =========================================================
        ZEBRA — VERTICAL (per column)
        ========================================================= */
        .expenses-table > tbody.transaction-group > tr > td:nth-child(even):not(.txn-col) {
            background-color: #f4f6f9;
        }
        .expenses-table > tbody.transaction-group > tr > td:nth-child(odd):not(.txn-col) {
            background-color: #ffffff;
        }
        .expenses-table > tbody.transaction-group > tr > td.txn-col {
            background-color: #e8ebef;
        }
        .expenses-table > tbody.transaction-group:nth-of-type(even) > tr > td.txn-col {
            background-color: #dfe3e8;
        }
        .expenses-table > thead > tr > th:nth-child(even) { background-color: #eef0f3; }
        .expenses-table > thead > tr > th:nth-child(odd)  { background-color: #f8f9fb; }
        .expenses-table > thead > tr > th:first-child     { background-color: #eef0f3; }

        /* =========================================================
        HOVER — whole transaction block
        ========================================================= */
        .expenses-table > tbody.transaction-group:not(.status-approved):hover > tr > td:not(.txn-col),
        .expenses-table > tbody.transaction-group:not(.status-approved):hover > tr > th {
            background-color: #dbe4ff !important;
        }

        .expenses-table > tbody.transaction-group:hover > tr > td.txn-col {
            background-color: #dde3f5;
        }
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

        /* =========================================================
        ROW BORDERS
        ========================================================= */
        tbody.transaction-group > tr > td:first-child,
        tbody.transaction-group > tr > th:first-child { border-left: 1px solid #eef0f3; }
        tbody.transaction-group > tr > td:last-child,
        tbody.transaction-group > tr > th:last-child  { border-right: 1px solid #eef0f3; }
        tbody.transaction-group > tr + tr > td { border-top: none; }

        /* =========================================================
        LINE-ITEMS SUB-TABLE
        ========================================================= */
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

        /* =========================================================
        APPROVAL-SPECIFIC
        ========================================================= */

        /* Approved rows — resting state */
        tbody.transaction-group.status-approved > tr > td { background-color: #ffc7c7 !important; }
        tbody.transaction-group.status-approved > tr > td.txn-col { background-color: #f5b8b8 !important; }

        /* Approved rows — hover: darken EVERYTHING including Transaction# */
        tbody.transaction-group.status-approved:hover > tr > td { background-color: #e8a4a4 !important; }
        tbody.transaction-group.status-approved:hover > tr > td.txn-col { background-color: #e8a4a4 !important; }
        /* Tooltip on approved rows */
        td.txn-col { position: relative; }
        .approved-tooltip {
            position: absolute;
            left: 100%; top: 8px; margin-left: 8px;
            z-index: 999;
            width: 256px; max-width: 256px;
            white-space: normal;
            opacity: 0; pointer-events: none;
            transition: opacity 0.15s ease;
        }
        tbody.transaction-group:hover .approved-tooltip { opacity: 1; }

        .row-check { opacity: 1; cursor: pointer; }
        .row-check:disabled { cursor: not-allowed; }

        /* Right-edge border on the Action column */
        tbody.transaction-group > tr > td.action-col { border-right: 2px solid #9ca3af; }

        /* =========================================================
        COLUMN ALIGNMENT (widths now come from the <colgroup>)
        ========================================================= */
        th[data-col="total"],      td[data-col="total"]      { text-align: right; }
        th[data-col="e_approval"], td[data-col="e_approval"] { text-align: center; }
        th[data-col="action"],     td[data-col="action"]     { text-align: center; }

        /* =========================================================
        STATUS DOTS
        ========================================================= */
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

        /* =========================================================
        FILTER PILL BUTTONS (matches Expenses Journal)
        ========================================================= */
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

        /* =========================================================
        SEARCH — fixed 38px slot; the shell expands leftward over
        it so opening the search never reflows the filter bar
        ========================================================= */
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

        /* Hide WebKit native clear button on search inputs */
        .search-shell input[type="search"]::-webkit-search-cancel-button,
        .search-shell input[type="search"]::-webkit-search-decoration,
        .search-shell input[type="search"]::-webkit-search-results-button,
        .search-shell input[type="search"]::-webkit-search-results-decoration {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }

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
        SUMMARY CARDS — smaller footprint
        ========================================================= */
        .summary-card {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
        }
        .summary-card .label {
            font-size: 9px;
            line-height: 1;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .summary-card .value {
            font-size: 0.9375rem; /* 15px */
            line-height: 1.2;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .summary-approve-btn {
            padding: 0.5rem 0.75rem;
            font-size: 1; /* 16px */
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            font-weight: 600;
        }

        /* =========================================================
        ACTION ICONS — larger click targets
        ========================================================= */
        .action-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            transition: background-color 0.15s ease, color 0.15s ease;
            font-size: 20px;
        }
        .action-icon-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        .action-icon-btn.action-edit { color: #2563eb; }
        .action-icon-btn.action-edit:hover { color: #1d4ed8; background-color: #eff6ff; }
        .action-icon-btn.action-delete { color: #dc2626; }
        .action-icon-btn.action-delete:hover { color: #b91c1c; background-color: #fef2f2; }

        /* =========================================================
        APPROVE MODAL — pure CSS, not dependent on Tailwind JIT
        ========================================================= */
        #approveModal .modal-card {
            width: 750px;
            max-width: 90vw;
            max-height: 75vh;
            background: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #approveModal .modal-body {
            overflow-y: auto;
            max-height: 45vh;
            padding: 0 1rem;
        }
        #approveModal .modal-body table {
            width: 100%;
            font-size: 0.875rem;
            text-align: left;
            color: #6b7280;
            border-collapse: collapse;
        }
        #approveModal .modal-body table thead th {
            position: sticky;
            top: 0;
            background: #f3f4f6;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #374151;
            padding: 0.5rem 0.75rem;
            z-index: 1;
            font-weight: 600;
        }
        #approveModal .modal-body table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #f3f4f6;
        }

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

    @section('page_title', "EXPENSES APPROVAL")

    <div class="lg:ml-64 expenses-page-shell">
    <div class="p-3 lg:pt-3 max-w-full expenses-page">

        {{-- FILTER BAR --}}
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
                <form method="GET" action="{{ route('e_request.index') }}" id="filterForm" class="flex flex-wrap items-center gap-2">
                    {{-- keep active column filters when Generate / search is used --}}
                    @foreach (collect(request()->query())->filter(fn ($v, $k) => str_starts_with($k, 'f_') && filled($v)) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach

                    <label class="text-sm text-gray-500">From</label>
                    <input type="date" name="from" value="{{ $from }}" max="{{ now()->format('Y-m-d') }}" class="border border-gray-300 rounded-lg text-sm p-2 transition-colors focus:ring-blue-500 focus:border-blue-500">
                    <label class="text-sm text-gray-500">To</label>
                    <input type="date" name="to" value="{{ $to }}" max="{{ now()->format('Y-m-d') }}" class="border border-gray-300 rounded-lg text-sm p-2 transition-colors focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-search"></i> Generate
                    </button>
                    <a href="{{ route('e_request.index', ['from' => now()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}" id="resetFiltersBtn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
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
                $activeApprovalFilters = collect([
                    'f_classification' => 'Classification',
                    'f_category'       => 'Category',
                    'f_supplier'       => 'Supplier',
                    'search'           => 'Search',
                ])->filter(fn ($label, $key) => filled(request($key)));
            @endphp
            @if ($activeApprovalFilters->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="text-xs text-gray-500 font-semibold uppercase">Filters:</span>
                    @foreach ($activeApprovalFilters as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery([$key => null, 'page' => 1]) }}"
                        class="filter-chip-remove inline-flex items-center gap-1 text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-200 px-2.5 py-1 rounded-full hover:bg-blue-100">
                            {{ $label }}: {{ request($key) }}
                            <i class="uil uil-times"></i>
                        </a>
                    @endforeach
                    <a href="{{ route('e_request.index', ['from' => $from, 'to' => $to]) }}" id="resetFiltersChipsLink" class="text-xs text-gray-400 hover:text-gray-600 underline ml-1">Reset filters</a>
                </div>
            @endif
        </div>

        <div class="bg-white shadow-md rounded-lg p-4">
            {{-- SUMMARY CARDS + BULK APPROVE — single even row --}}
        <div class="flex flex-wrap items-stretch gap-2 mb-3">
            <div class="bg-blue-100 summary-card flex-1 min-w-[140px]">
                <p class="label text-blue-700/80">Selected</p>
                <p class="value text-blue-700">₱ <span id="selectedTotal">0.00</span></p>
            </div>
            <div class="bg-gray-200 summary-card flex-1 min-w-[140px]">
                <p class="label text-gray-600">Total ({{ $summary->total_count }})</p>
                <p class="value text-gray-700">₱ {{ number_format($summary->total_amount, 2) }}</p>
            </div>
            <div class="bg-yellow-100 summary-card flex-1 min-w-[140px]">
                <p class="label text-yellow-700/80">Pending ({{ $summary->pending_count }})</p>
                <p class="value text-yellow-700">₱ {{ number_format($summary->pending_total, 2) }}</p>
            </div>
            <div class="bg-green-100 summary-card flex-1 min-w-[140px]">
                <p class="label text-green-700/80">Approved ({{ $summary->approved_count }})</p>
                <p class="value text-green-700">₱ {{ number_format($summary->approved_total, 2) }}</p>
            </div>

            <button id="openApproveModalBtn" onclick="openApproveModal()" disabled
    class="summary-approve-btn flex-1 min-w-[140px] text-white bg-gray-300 cursor-not-allowed transition-colors">
    <i class="uil uil-check-circle"></i> Approve
    <span id="selectedCount" class="bg-white/30 rounded-full px-2 py-0.5">0</span>
</button>
        </div>

            {{-- TABLE --}}
            <div id="inventoryTable" class="journal-scroll w-full {{ $e_inventories->isEmpty() ? 'is-empty' : '' }}">
                <table id="approvalTable" class="expenses-table w-full text-sm text-left text-gray-500">
                    <colgroup>
                        <col style="width:180px">   {{-- Transaction ID --}}
                        <col style="width:130px">   {{-- Date --}}
                        <col style="width:80px">    {{-- Items --}}
                        <col style="width:130px">   {{-- Total --}}
                        <col style="width:90px">    {{-- VAT --}}
                        <col style="width:90px">    {{-- W/Tax --}}
                        <col style="width:150px">   {{-- Server/Cashier --}}
                        <col style="width:140px">    {{-- Actions (wider for bigger icons) --}}
                    </colgroup>
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <input type="checkbox" id="selectAllCheckbox" class="rounded" onclick="toggleSelectAll(this)">
                                    <span>Transaction ID</span>
                                </div>
                            </th>
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
                            $isApproved = $i_e->e_approval == 2;
                            $items = $lineItemsByEId->get($i_e->e_id, collect());
                            $statusClass = $isApproved ? 'status-approved' : ($i_e->e_approval == 1 ? 'status-pending' : 'status-draft');
                        @endphp
                        <tbody class="transaction-group {{ $statusClass }}">
                            {{-- ============ MAIN ROW ============ --}}
                            <tr>
                                <td rowspan="2" data-col="e_transid" class="txn-col">
                                    @if ($isApproved)
                                        <div class="approved-tooltip">
                                            <div class="bg-gray-800 text-white text-xs rounded-lg shadow-xl px-3 py-2 flex items-start gap-1.5">
                                                <i class="uil uil-exclamation-triangle text-yellow-400 mt-0.5"></i>
                                                <span>This transaction has already been approved and can no longer be edited or selected.</span>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-center gap-2">
                                        @if ($isApproved)
                                            {{-- No checkbox for approved rows --}}
                                        @elseif ($i_e->e_approval == null || $i_e->e_approval == 1)
                                            <input type="checkbox"
                                                class="row-check rowCheckbox rounded"
                                                data-id="{{ $i_e->e_id }}"
                                                data-transid="{{ $i_e->e_transid }}"
                                                data-date="{{ \Carbon\Carbon::parse($i_e->created_at)->format('M d, Y h:i A') }}"
                                                data-amount="{{ $i_e->e_total }}"
                                                data-mop="{{ $i_e->mop }}"
                                                onclick="updateSelection()">
                                        @else
                                            <input type="checkbox" disabled title="Not yet submitted for approval." class="row-check rounded">
                                        @endif
                                        <span class="font-semibold text-gray-900 tracking-wide">{{ $i_e->e_transid }}</span>
                                    </div>
                                </td>

                                <td data-col="created_at" class="date-col px-4 py-4 whitespace-nowrap">
                                    <div class="flex flex-col leading-tight">
                                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($i_e->created_at)->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($i_e->created_at)->format('h:i A') }}</span>
                                    </div>
                                </td>

                                <td data-col="items" class="text-xs text-gray-400 italic text-center">
                                    {{ $items->count() }} item(s)
                                </td>

                                <td data-col="total" class="total-col tabular-nums font-bold text-gray-900 text-base text-right">
                                    ₱{{ number_format($i_e->e_total, 2) }}
                                </td>

                                <td data-col="vat" class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">
                                    ₱{{ number_format((float) ($i_e->e_vat ?? 0), 2) }}
                                </td>

                                <td data-col="wtax" class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">
                                    ₱{{ number_format((float) ($i_e->e_wtax ?? 0), 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($i_e->firstname ?? 'A', 0, 1) . substr($i_e->lastname ?? 'X', 0, 1)) }}
                                        </div>
                                        <span class="text-gray-700 truncate" title="{{ $i_e->firstname }} {{ $i_e->lastname }}">
                                            {{ $i_e->firstname }} {{ $i_e->lastname }}
                                        </span>
                                    </div>
                                </td>


                                <td data-col="action" class="action-col">
                                    @if ($isApproved)
                                        <span class="text-gray-300">—</span>
                                    @else
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                onclick="openEditModal({{ $i_e->e_id }})"
                                                title="Edit" class="action-icon-btn action-edit">
                                                <i class="uil uil-edit"></i>
                                            </button>
                                            @if ($i_e->e_approval == 1)
                                                <button type="button" title="Delete"
                                                        class="action-icon-btn action-delete delete-btn"
                                                        data-id="{{ $i_e->e_id }}" data-transid="{{ $i_e->e_transid }}">
                                                    <i class="uil uil-trash-alt"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            {{-- ============ DETAIL ROW — SUB-TABLE ============ --}}
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
                                                        @php
                                                            $isBankTransfer = strtolower(preg_replace('/[^a-z]/i', '', (string) ($item->is_paid ?? ''))) === 'banktransfer';
                                                            $isPaid = $isBankTransfer || (int) ($item->payment_class ?? 0) !== 0;
                                                        @endphp
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
                                                                @if (! $isPaid)
                                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200">Unpaid</span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-800 ring-1 ring-green-200">Paid</span>
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
                                        <p class="text-sm font-medium text-gray-500">No transactions in this date range</p>
                                        <p class="text-xs">Try widening the date filter.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            <div class="p-4">
                {{ $e_inventories->withQueryString()->links() }}
            </div>
        </div>
    </div>
    </div>

    {{-- APPROVE CONFIRMATION MODAL --}}
    <div id="approveModal" class="hidden fixed inset-0 flex items-center justify-center p-4" style="z-index: 9999; background-color: rgba(0, 0, 0, 0.55);">
        <div class="modal-card">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h2 class="text-base font-bold">Approve Selected Expenses</h2>
                <div class="flex items-center gap-3">
                    <button onclick="removeAllFromModal()" class="px-2.5 py-1 rounded-full bg-red-50 text-red-500 text-xs font-semibold hover:bg-red-100 transition-colors">Remove All</button>
                    <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-700 text-xl leading-none transition-colors">&times;</button>
                </div>
            </div>
            <div class="px-4 py-3 grid grid-cols-2 gap-2">
                <div class="bg-amber-50 rounded-lg p-2 ring-1 ring-amber-200">
                    <p class="text-xs text-amber-600 uppercase">Count of selected transaction</p>
                    <p class="text-base font-bold text-amber-700" id="modalCount">0</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-2 ring-1 ring-blue-200">
                    <p class="text-xs text-blue-600 uppercase">Total Amount</p>
                    <p class="text-base font-bold text-blue-700">₱ <span id="modalTotal">0.00</span></p>
                </div>
            </div>
            <div class="modal-body">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction #</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:center;">Mode of Payment</th>
                            <th style="text-align:center;">Status</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="modalRowsBody"></tbody>
                </table>
            </div>
            <div class="flex items-center justify-end gap-2 px-4 py-3 border-t">
                <button onclick="closeApproveModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors">Cancel</button>
                <button onclick="confirmBulkApprove()" id="confirmApproveBtn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">Approve</button>
            </div>
        </div>
    </div>

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
        <div id="filterDropdownList" class="max-h-60 overflow-y-auto py-1 text-sm">
            {{-- populated by JS --}}
        </div>
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
            var FILTER_KEY = 'expApprovalFilterState';
            var RESET_FLAG = 'expApprovalJustReset';

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
            } catch (e) {
                // sessionStorage unavailable
            }
        })();
    </script>

    <script>
        function updateSelection() {
            const checked = document.querySelectorAll('.rowCheckbox:checked');
            let total = 0;
            checked.forEach(cb => total += parseFloat(cb.dataset.amount));

            document.getElementById('selectedTotal').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('selectedCount').innerText = checked.length;

            const btn = document.getElementById('openApproveModalBtn');
            if (checked.length > 0) {
                btn.disabled = false;
                btn.classList.remove('bg-gray-300', 'cursor-not-allowed');
                btn.classList.add('bg-green-600', 'hover:bg-green-700');
            } else {
                btn.disabled = true;
                btn.classList.add('bg-gray-300', 'cursor-not-allowed');
                btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            }

            syncSelectAll();
        }

        function syncSelectAll() {
            const selectAll = document.getElementById('selectAllCheckbox');
            if (!selectAll) return;

            const allBoxes = document.querySelectorAll('.rowCheckbox:not(:disabled)');
            const checkedBoxes = document.querySelectorAll('.rowCheckbox:not(:disabled):checked');

            // Only check the header checkbox when ALL rows are selected.
            // Any partial selection → empty (no dash, no indeterminate).
            selectAll.checked = allBoxes.length > 0 && checkedBoxes.length === allBoxes.length;
            selectAll.indeterminate = false;
        }

        function toggleSelectAll(source) {
            document.querySelectorAll('.rowCheckbox:not(:disabled)').forEach(cb => {
                cb.checked = source.checked;
            });
            // Force indeterminate off since user explicitly clicked
            source.indeterminate = false;
            updateSelection();
        }

        async function openApproveModal() {
            const checked = document.querySelectorAll('.rowCheckbox:checked');
            if (checked.length === 0) return;

            const body = document.getElementById('modalRowsBody');
            body.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Loading...</td></tr>';
            document.getElementById('approveModal').classList.remove('hidden');

            let total = 0;
            let html = '';
            for (const cb of checked) {
                total += parseFloat(cb.dataset.amount);
                html += `
                    <tr class="bg-white border-b" data-modal-row-id="${cb.dataset.id}">
                        <td class="px-3 py-2 whitespace-nowrap">${cb.dataset.date}</td>
                        <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap">${cb.dataset.transid}</td>
                        <td class="px-3 py-2 text-right tabular-nums font-bold text-gray-900">₱${parseFloat(cb.dataset.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td class="px-3 py-2 text-center">${cb.dataset.mop || '—'}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200">Pending</span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button class="text-red-500 hover:underline text-xs font-semibold" onclick="removeFromModal(${cb.dataset.id})">Remove</button>
                        </td>
                    </tr>`;
            }
            body.innerHTML = html;
            document.getElementById('modalCount').innerText = checked.length;
            document.getElementById('modalTotal').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function closeApproveModal() { document.getElementById('approveModal').classList.add('hidden'); }

        function recalcModalTotals() {
            const rows = document.querySelectorAll('#modalRowsBody tr[data-modal-row-id]');
            document.getElementById('modalCount').innerText = rows.length;
            let total = 0;
            rows.forEach(row => {
                const cb = document.querySelector(`.rowCheckbox[data-id="${row.dataset.modalRowId}"]`);
                if (cb) total += parseFloat(cb.dataset.amount);
            });
            document.getElementById('modalTotal').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            if (rows.length === 0) closeApproveModal();
        }

        function removeFromModal(id) {
            document.querySelector(`#modalRowsBody tr[data-modal-row-id="${id}"]`)?.remove();
            const cb = document.querySelector(`.rowCheckbox[data-id="${id}"]`);
            if (cb) cb.checked = false;
            updateSelection();
            recalcModalTotals();
        }

        function removeAllFromModal() {
            document.querySelectorAll('#modalRowsBody tr[data-modal-row-id]').forEach(row => {
                const cb = document.querySelector(`.rowCheckbox[data-id="${row.dataset.modalRowId}"]`);
                if (cb) cb.checked = false;
            });
            updateSelection();
            closeApproveModal();
        }

        function confirmBulkApprove() {
            const ids = Array.from(document.querySelectorAll('#modalRowsBody tr[data-modal-row-id]')).map(row => row.dataset.modalRowId);
            if (ids.length === 0) return;

            const btn = document.getElementById('confirmApproveBtn');
            btn.disabled = true;
            btn.innerText = 'Approving...';

            fetch('{{ route("update-expenses-approve") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeApproveModal();
                    const toast = document.getElementById('toastNotification');
                    if (toast) { toast.innerText = data.message; toast.classList.remove('hidden'); }
                    setTimeout(() => location.reload(), 1200);
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerText = 'Approve';
                }
            })
            .catch(() => {
                alert('Something went wrong while approving.');
                btn.disabled = false;
                btn.innerText = 'Approve';
            });
        }

        let deleteTargetId = null;

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.delete-btn');
            if (!btn) return;
            deleteTargetId = btn.dataset.id;
            document.getElementById('deleteTransId').innerText = btn.dataset.transid;
            document.getElementById('deleteReason').value = '';
            document.getElementById('deleteError').classList.add('hidden');
            document.getElementById('confirmDeleteBtn').disabled = false;
            document.getElementById('confirmDeleteBtn').innerText = 'Submit Request';
            document.getElementById('deleteModal').classList.remove('hidden');
        });

        function closeDeleteModal() { document.getElementById('deleteModal').classList.add('hidden'); }

        function submitDelete() {
            const reason = document.getElementById('deleteReason').value.trim();
            const err = document.getElementById('deleteError');
            const showErr = m => { err.innerText = m; err.classList.remove('hidden'); };

            if (reason.length < 5) return showErr('Please enter a reason (at least 5 characters).');

            const fd = new FormData();
            fd.append('slug', deleteTargetId);
            fd.append('reason', reason);

            const btn = document.getElementById('confirmDeleteBtn');
            btn.disabled = true;
            btn.innerText = 'Submitting...';

            fetch('{{ route("expenses.tran_remove") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: fd
            })
            .then(async res => ({ ok: res.ok, data: await res.json() }))
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    closeDeleteModal();
                    showSuccessToast(data.message || 'Deletion request submitted for approval.');

                    setTimeout(() => {
                        location.reload();
                    }, 1400);

                    return;
                }

                const msg = data.message || Object.values(data.errors || {}).flat()[0] || 'Request failed.';
                showErr(msg);
                btn.disabled = false;
                btn.innerText = 'Submit Request';
            })
            .catch(() => {
                showErr('Something went wrong.');
                btn.disabled = false;
                btn.innerText = 'Submit Request';
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const shell = document.getElementById('searchShell');
            const input = document.getElementById('searchInput');

            var FILTER_KEY = 'expApprovalFilterState';
            var RESET_FLAG = 'expApprovalJustReset';

            // Initialize select-all state on page load
            syncSelectAll();

            function markFiltersJustReset() {
                try {
                    sessionStorage.setItem(RESET_FLAG, '1');
                    sessionStorage.removeItem(FILTER_KEY);
                } catch (e) { /* ignore */ }
            }

            document.getElementById('resetFiltersBtn')?.addEventListener('click', function () {
                var destUrl = new URL(this.href, window.location.origin);
                if (!destUrl.search || destUrl.search.length <= 1) markFiltersJustReset();
            });
            document.addEventListener('click', function (e) {
                const link = e.target.closest('#resetFiltersChipsLink, .filter-chip-remove');
                if (!link) return;
                try {
                    var destUrl = new URL(link.href, window.location.origin);
                    if (!destUrl.search || destUrl.search.length <= 1) markFiltersJustReset();
                } catch (err) { /* ignore */ }
            });

            if (!shell || !input) return;

            document.getElementById('searchSubmit')?.addEventListener('click', function () {
                if (shell.classList.contains('open')) {
                    document.getElementById('filterForm').submit();
                } else {
                    shell.classList.add('open');
                    setTimeout(() => input.focus(), 200);
                }
            });

            document.getElementById('searchClose')?.addEventListener('click', function (e) {
                e.stopPropagation();
                input.value = '';
                shell.classList.remove('open');
                document.getElementById('filterForm').submit();
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); document.getElementById('filterForm').submit(); }
            });

            document.addEventListener('mousedown', function (e) {
                if (shell.classList.contains('open') && !e.target.closest('#searchShell')) {
                    if (input.value.trim() === '') shell.classList.remove('open');
                }
            });

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
                category: @json(($expcats ?? collect())->pluck('name')->map(fn ($n) => ['value' => $n, 'label' => $n])->values()),
                supplier: @json(($supps ?? collect())->pluck('name')->map(fn ($n) => ['value' => $n, 'label' => $n])->values()),
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

                var options = FILTER_OPTIONS[currentCol] || [];
                $ddSearch.val('');
                renderList(options, selected, '');

                setTimeout(function () { $ddSearch.focus(); }, 50);
            });

            $('#filterDropdownClose').on('click', function () { $dd.addClass('hidden'); });

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
                url.searchParams.set('page', '1');

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
        {{-- SUCCESS TOAST --}}
    <div id="successToast" class="hidden fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div id="successToastCard" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 flex flex-col items-center gap-4 scale-95 opacity-0 transition-all duration-200">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="uil uil-check-circle text-4xl text-emerald-600"></i>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-bold text-gray-900">Success</h3>
                <p id="successToastMessage" class="text-sm text-gray-500 mt-1">Success.</p>
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
            msg.textContent = message || 'Success.';
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
        <div id="editTransactionModal" class="hidden">
            <div class="bg-white rounded-lg shadow-2xl flex flex-col"
                style="position: fixed; inset: 0; z-index: 99999;
                        background: rgba(0,0,0,0.55);
                        display: flex; align-items: center; justify-content: center;
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
        </div>

        <script>
            function openEditModal(eId) {
                var url = "{{ url('/expenses/edit2') }}/" + eId;
                var modal = document.getElementById('editTransactionModal');
                var frame = document.getElementById('editTransactionFrame');

                frame.src = url;
                modal.classList.remove('hidden');
                // Force display block — the outer .hidden removal alone isn't enough
                // when the inner div has its own inline display:flex
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }

            function closeEditModal() {
                var modal = document.getElementById('editTransactionModal');
                var frame = document.getElementById('editTransactionFrame');

                modal.classList.add('hidden');
                modal.style.display = '';
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