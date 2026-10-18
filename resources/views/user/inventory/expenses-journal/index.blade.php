<x-app-layout>
    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }

        .expenses-page {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            box-sizing: border-box;
        }

        .expenses-page > .bg-white.shadow-md.rounded-lg {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        .expenses-page .bg-white.rounded-lg.shadow-md.p-4.mb-4 {
            flex: 0 0 auto;
        }
        .expenses-page .grid {
            flex: 0 0 auto;
        }

        /* =========================================================
        TABLE SHELL — wraps scroll box + grand total (sibling)
        ========================================================= */
        .table-shell {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }

        #journalScrollBox {
            flex: 1 1 auto;
            min-height: 0;
            max-height: none !important;
            overflow: auto;
            position: relative;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            scroll-behavior: smooth;
        }

        .expenses-page > .bg-white.shadow-md.rounded-lg > .p-4:last-child {
            flex: 0 0 auto;
        }
        .expenses-page {
            overflow-x: clip;
        }

        .expenses-table {
            width: 100%;
            min-width: 1100px;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        .expenses-table col.c-ref,
        .journal-total-table col.c-ref       { width: 180px; }
        .expenses-table col.c-date,
        .journal-total-table col.c-date      { width: 160px; }
        .expenses-table col.c-cos,
        .journal-total-table col.c-cos       { width: 130px; }
        .expenses-table col.c-opex,
        .journal-total-table col.c-opex      { width: 130px; }
        .expenses-table col.c-vat,
        .journal-total-table col.c-vat       { width: 110px; }
        .expenses-table col.c-cash,
        .journal-total-table col.c-cash      { width: 130px; }
        .expenses-table col.c-ap,
        .journal-total-table col.c-ap        { width: 110px; }
        .expenses-table col.c-wtax,
        .journal-total-table col.c-wtax      { width: 140px; }
        .expenses-table col.c-by,
        .journal-total-table col.c-by        { width: 180px; }

        #journalScrollBox > .expenses-table > thead > tr > th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #f3f4f6;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 0 #e5e7eb;
            white-space: nowrap;
        }
        #journalScrollBox > .expenses-table > thead > tr > th:first-child {
            left: 0;
            z-index: 30;
            background-color: #eef0f3;
            border-right: 1px solid #e5e7eb;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.12);
            text-align: center;
        }

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

        .expenses-table > tbody.transaction-group:nth-of-type(odd) > tr > td,
        .expenses-table > tbody.transaction-group:nth-of-type(odd) > tr > th {
            background-color: #ffffff;
        }
        .expenses-table > tbody.transaction-group:nth-of-type(even) > tr > td,
        .expenses-table > tbody.transaction-group:nth-of-type(even) > tr > th {
            background-color: #f5f6f8;
        }
        .expenses-table > tbody.transaction-group:nth-of-type(odd) > tr > td.txn-col {
            background-color: #eef0f3;
        }
        .expenses-table > tbody.transaction-group:nth-of-type(even) > tr > td.txn-col {
            background-color: #e6e9ed;
        }

        .expenses-table > thead > tr > th:nth-child(even) {
            background-color: #eef0f3;
        }
        .expenses-table > thead > tr > th:nth-child(odd) {
            background-color: #f8f9fb;
        }

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

        .expenses-table > tbody.transaction-group:hover > tr > td:not(.txn-col),
        .expenses-table > tbody.transaction-group:hover > tr > th {
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

        tbody.transaction-group > tr > td:first-child,
        tbody.transaction-group > tr > th:first-child { border-left: 1px solid #eef0f3; }
        tbody.transaction-group > tr > td:last-child,
        tbody.transaction-group > tr > th:last-child  { border-right: 1px solid #eef0f3; }

        tbody.transaction-group > tr + tr > td { border-top: none; }

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
        GRAND TOTAL — pinned below the scroll box (sibling)
        ========================================================= */
        .grand-total-wrap {
            flex: 0 0 auto;
            background: #f3f4f6;
            box-shadow: 0 -2px 6px -1px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
            overflow-y: hidden;
        }

        .journal-total-table {
            border-spacing: 0;
            margin-top: 0;
            background: #f3f4f6;
            width: 100%;
            min-width: 1100px;
            table-layout: fixed;
            border-collapse: separate;
        }
        .journal-total-table td {
            background: #f3f4f6;
            border-top: 1px solid #e5e7eb;
            padding: 12px 16px;
            vertical-align: top;
        }
        .journal-total-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 26;
            width: 180px;
            min-width: 180px;
            max-width: 180px;
            background: #eef0f3;
            border-right: 1px solid #e5e7eb;
            font-weight: 700;
        }
        .journal-total-table .amount {
            text-align: right;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .journal-total-table .grand-total-label {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #9ca3af;
            margin-bottom: 2px;
        }
        .journal-total-table > tbody > tr > td:nth-child(even) { background-color: #e9ecef; }
        .journal-total-table > tbody > tr > td:nth-child(odd)  { background-color: #f3f4f6; }
        .journal-total-table > tbody > tr > td:first-child     { background-color: #eef0f3; }

        .expenses-table > thead > tr > th:nth-child(5) {
            border-right: 2px solid #9ca3af;
        }
        .journal-total-table > tbody > tr > td:nth-child(5) {
            border-right: 2px solid #9ca3af;
        }

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

        #filterDropdown { max-height: min(420px, calc(100vh - 100px)); flex-direction: column; }
        #filterDropdown:not(.hidden) { display: flex; }
    </style>

    @include('partials.expenses-responsive')

    @section('page_title', "EXPENSES JOURNAL")

    <div class="lg:ml-64 expenses-page-shell">
    <div class="p-3 lg:pt-3 max-w-full expenses-page">

        {{-- FILTER BAR --}}
        <div class="bg-white rounded-lg shadow-md p-4 mb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <h1 class="text-2xl font-semibold text-gray-800">Expenses Journal</h1>
                <form method="GET" action="{{ route('expenses-journal.index') }}" id="filterForm" class="flex flex-wrap items-center gap-2">
                    <label class="text-sm text-gray-500">From</label>
                    <input type="date" name="from" value="{{ $from }}" max="{{ now()->format('Y-m-d') }}" class="border border-gray-300 rounded-lg text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    <label class="text-sm text-gray-500">To</label>
                    <input type="date" name="to" value="{{ $to }}" max="{{ now()->format('Y-m-d') }}" class="border border-gray-300 rounded-lg text-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-search"></i> Generate
                    </button>
                    <a href="{{ route('expenses-journal.index', ['from' => now()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]) }}"
                       id="resetFiltersBtn"
                       class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
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
                </form>
            </div>

            @php
                $activeJournalFilters = collect([
                    'f_classification' => 'Classification',
                    'f_category'       => 'Category',
                    'f_supplier'       => 'Supplier',
                ])->filter(fn ($label, $key) => filled(request($key)));
            @endphp
            @if ($activeJournalFilters->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="text-xs text-gray-500 font-semibold uppercase">Filters:</span>
                    @foreach ($activeJournalFilters as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery([$key => null, 'page' => 1]) }}"
                           class="filter-chip-remove inline-flex items-center gap-1 text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-200 px-2.5 py-1 rounded-full hover:bg-blue-100">
                            {{ $label }}: {{ request($key) }}
                            <i class="uil uil-times"></i>
                        </a>
                    @endforeach
                    <a href="{{ url()->current() }}?from={{ $from }}&to={{ $to }}" id="resetFiltersChipsLink" class="text-xs text-gray-400 hover:text-gray-600 underline ml-1">Reset filters</a>
                </div>
            @endif
        </div>

        <div class="bg-white shadow-md rounded-lg p-4">

            {{-- TABLE SHELL — scroll box + grand total --}}
            <div class="table-shell">

                {{-- SCROLL BOX — rows only --}}
                <div id="journalScrollBox" class="table-scroll w-full {{ $journal->isEmpty() ? 'is-empty' : '' }}">
                    <table class="expenses-table text-sm text-left text-gray-500">
                        <colgroup>
                            <col style="width:180px">   {{-- Reference --}}
                            <col style="width:160px">   {{-- Date --}}
                            <col style="width:130px">   {{-- Cost of Sales Dr --}}
                            <col style="width:130px">   {{-- OpEx Dr --}}
                            <col style="width:110px">   {{-- VAT Dr --}}
                            <col style="width:130px">   {{-- Cash Cr --}}
                            <col style="width:110px">   {{-- A/P Cr --}}
                            <col style="width:140px">   {{-- W/Tax Cr --}}
                            <col style="width:180px">   {{-- Approved By --}}
                        </colgroup>
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 whitespace-nowrap">Reference</th>
                                <th class="px-4 py-3 whitespace-nowrap">Date Approved</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Cost of Sales (Dr)</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Operating Exp (Dr)</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">VAT (Dr)</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Cash (Cr)</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">A/P (Cr)</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">W/Tax Payable (Cr)</th>
                                <th class="px-4 py-3 whitespace-nowrap">Approved By</th>
                            </tr>
                        </thead>

                        @forelse ($journal as $entry)
                            @php
                                $transDatePart = substr($entry->e_transid, 0, 8);
                                $transDate = preg_match('/^\d{8}$/', $transDatePart)
                                    ? \Carbon\Carbon::createFromFormat('Ymd', $transDatePart)->format('M d, Y')
                                    : null;

                                $entryItems = $lineItemsByEId->get($entry->e_id, collect());
                                $mopList = $entryItems->pluck('is_paid')->filter()->unique()->values();
                            @endphp
                            <tbody class="transaction-group">
                                <tr>
                                    <td rowspan="2" class="txn-col whitespace-nowrap">
                                        <span class="font-semibold text-gray-900 tracking-wide">{{ $entry->e_transid }}</span>
                                        @if ($transDate)
                                            <div class="text-xs text-gray-400 font-normal mt-1">Trans. date: {{ $transDate }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex flex-col leading-tight">
                                            <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($entry->approved_at)->format('M d, Y') }}</span>
                                            <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($entry->approved_at)->format('h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">{{ number_format($entry->cos_debit, 2) }}</td>
                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">{{ number_format($entry->opex_debit, 2) }}</td>
                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">{{ number_format($entry->e_vat, 2) }}</td>
                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">{{ number_format($entry->cash_credit, 2) }}</td>
                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">{{ number_format($entry->ap_credit, 2) }}</td>
                                    <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums text-gray-700">{{ number_format($entry->e_wtax, 2) }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold">
                                                {{ strtoupper(substr($entry->firstname ?? 'A', 0, 1) . substr($entry->lastname ?? 'X', 0, 1)) }}
                                            </div>
                                            <span class="text-gray-700">{{ $entry->firstname }} {{ $entry->lastname }}</span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="8" class="px-4 pt-0 pb-3">
                                        @php $items = $lineItemsByEId->get($entry->e_id, collect()); @endphp
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
                                                                <td class="px-3 py-2 text-right tabular-nums text-gray-700">₱{{ number_format($item->unit_price, 2) }}</td>
                                                                <td class="px-3 py-2 text-right tabular-nums font-semibold text-gray-900">₱{{ number_format($item->amount, 2) }}</td>
                                                                <td class="px-3 py-2 text-center text-gray-600">
                                                                    {{ $item->is_paid ? ucfirst($item->is_paid) : '—' }}
                                                                </td>
                                                                <td class="px-3 py-2 text-center">
                                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full
                                                                        {{ $item->payment_class == 1 ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-red-50 text-red-700 ring-1 ring-red-200' }}">
                                                                        {{ $item->payment_class == 1 ? 'Paid' : 'Unpaid' }}
                                                                    </span>
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
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3 text-gray-400">
                                            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                                                <i class="uil uil-file-search-alt text-2xl"></i>
                                            </div>
                                            <p class="text-sm font-medium text-gray-500">No approved expenses in this date range</p>
                                            <p class="text-xs">Try widening the date filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>

                {{-- GRAND TOTAL — pinned below the scroll box --}}
                @if ($journal->count() > 0)
                    <div class="grand-total-wrap">
                        <table class="journal-total-table">
                            <colgroup>
                                <col class="c-ref">
                                <col class="c-date">
                                <col class="c-cos">
                                <col class="c-opex">
                                <col class="c-vat">
                                <col class="c-cash">
                                <col class="c-ap">
                                <col class="c-wtax">
                                <col class="c-by">
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td>GRAND TOTAL</td>
                                    <td></td>
                                    <td class="amount"><div class="grand-total-label">Cost of Sales (Dr)</div>₱{{ number_format($grand['cos'], 2) }}</td>
                                    <td class="amount"><div class="grand-total-label">Operating Exp (Dr)</div>₱{{ number_format($grand['opex'], 2) }}</td>
                                    <td class="amount"><div class="grand-total-label">VAT (Dr)</div>₱{{ number_format($grand['vat'], 2) }}</td>
                                    <td class="amount"><div class="grand-total-label">Cash (Cr)</div>₱{{ number_format($grand['cash'], 2) }}</td>
                                    <td class="amount"><div class="grand-total-label">A/P (Cr)</div>₱{{ number_format($grand['ap'], 2) }}</td>
                                    <td class="amount"><div class="grand-total-label">W/Tax Payable (Cr)</div>₱{{ number_format($grand['wtax'], 2) }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
            {{-- /TABLE SHELL --}}

            <div class="p-4">
                {{ $journal->appends(['from' => $from, 'to' => $to])->links() }}
            </div>
        </div>
    </div>
    </div>

    {{-- FILTER DROPDOWN --}}
    <div id="filterDropdown" class="hidden fixed z-50 bg-white border border-gray-200 rounded-lg shadow-xl w-64" style="top:0; left:0;">
        <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
            <span id="filterDropdownTitle" class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Filter</span>
            <button type="button" id="filterDropdownClose" class="text-gray-400 hover:text-gray-600"><i class="uil uil-times"></i></button>
        </div>
        <div class="px-3 pt-2">
            <input type="text" id="filterDropdownSearch"
                   class="w-full border border-gray-200 rounded-md text-xs p-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Search values...">
        </div>
        <div id="filterDropdownList" class="max-h-60 overflow-y-auto py-1 text-sm"></div>
        <div class="px-3 py-2 border-t border-gray-100 flex items-center justify-between">
            <button type="button" id="filterDropdownClear" class="text-xs px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold">Clear</button>
            <button type="button" id="filterDropdownApply" class="text-xs px-3 py-1.5 rounded-md bg-blue-700 hover:bg-blue-800 text-white font-semibold">Apply</button>
        </div>
    </div>

    <script>
        (function () {
            var FILTER_KEY = 'expJournalFilterState';
            var RESET_FLAG = 'expJournalJustReset';

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
            }
        })();
    </script>

    <script>
        $(document).ready(function () {
            /* =========================================================
            HORIZONTAL SCROLL SYNC — main table <-> grand total
            ========================================================= */
            (function syncHorizontalScroll() {
                var $main = $('#journalScrollBox');
                var $totalWrap = $('.grand-total-wrap');
                if (!$main.length || !$totalWrap.length) return;

                var syncing = false;
                $main.on('scroll', function () {
                    if (syncing) return;
                    syncing = true;
                    $totalWrap.scrollLeft($main.scrollLeft());
                    syncing = false;
                });
                $totalWrap.on('scroll', function () {
                    if (syncing) return;
                    syncing = true;
                    $main.scrollLeft($totalWrap.scrollLeft());
                    syncing = false;
                });
            })();

            /* =========================================================
            COLUMN FILTER DROPDOWN
            ========================================================= */
            var $dd = $('#filterDropdown'),
                $ddList = $('#filterDropdownList'),
                $ddTtl = $('#filterDropdownTitle'),
                $ddSearch = $('#filterDropdownSearch'),
                currentCol = null;

            var FILTER_KEY = 'expJournalFilterState';
            var RESET_FLAG = 'expJournalJustReset';

            function markFiltersJustReset() {
                try {
                    sessionStorage.setItem(RESET_FLAG, '1');
                    sessionStorage.removeItem(FILTER_KEY);
                } catch (e) { /* ignore */ }
            }

            $('#resetFiltersBtn').on('click', function () {
                var destUrl = new URL(this.href, window.location.origin);
                if (!destUrl.search || destUrl.search.length <= 1) markFiltersJustReset();
            });
            $(document).on('click', '#resetFiltersChipsLink, .filter-chip-remove', function () {
                try {
                    var destUrl = new URL(this.href, window.location.origin);
                    if (!destUrl.search || destUrl.search.length <= 1) markFiltersJustReset();
                } catch (e) { /* ignore */ }
            });

            $(document).on('click', '.journal-filter-btn', function (e) {
                e.stopPropagation();
                var $btn = $(this);
                currentCol = $btn.data('column');
                var rect = this.getBoundingClientRect();
                var left = Math.min(rect.left + window.scrollX, window.innerWidth - 270);
                $dd.css({ top: (rect.bottom + window.scrollY + 6) + 'px', left: left + 'px' }).removeClass('hidden');
                $ddTtl.text($btn.data('title'));

                var params = new URLSearchParams(window.location.search);
                var selected = params.get('f_' + currentCol) || '';
                $ddSearch.val('');
                renderList(FILTER_OPTIONS[currentCol] || [], selected, '');
                setTimeout(function () { $ddSearch.focus(); }, 50);
            });

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
                    if (term && opt.label.toLowerCase().indexOf(term) === -1) return;
                    var checked = (selected === opt.value) ? 'checked' : '';
                    var safeVal = $('<div>').text(opt.value).html();
                    var safeLabel = $('<div>').text(opt.label).html();
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

            $('#filterDropdownClose').on('click', function () { $dd.addClass('hidden'); });

            $(document).on('mousedown', function (e) {
                if (!$dd.hasClass('hidden') &&
                    !$(e.target).closest('#filterDropdown').length &&
                    !$(e.target).closest('.journal-filter-btn').length) {
                    $dd.addClass('hidden');
                }
            });

            $ddSearch.on('input', function () {
                var params = new URLSearchParams(window.location.search);
                var selected = params.get('f_' + currentCol) || '';
                renderList(FILTER_OPTIONS[currentCol] || [], selected, $(this).val());
            });

            $('#filterDropdownApply').on('click', function () {
                var val = $ddList.find('input[name="dd-filter"]:checked').val() || '';
                applyColumnFilter(currentCol, val);
            });

            $('#filterDropdownClear').on('click', function () { applyColumnFilter(currentCol, ''); });

            function applyColumnFilter(column, value) {
                var url = new URL(window.location.href);
                if (value === '') url.searchParams.delete('f_' + column);
                else url.searchParams.set('f_' + column, value);
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
</x-app-layout>