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
        #deletionScrollBox {
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
        TABLE SHELL + COLUMN TOKENS
        ========================================================= */
        .expenses-table,
        .journal-total-table {
            width: 100%;
            min-width: 1100px;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        .expenses-table col.c-ref,
        .journal-total-table col.c-ref   { width: 180px; }
        .expenses-table col.c-date,
        .journal-total-table col.c-date  { width: 160px; }
        .expenses-table col.c-cos,
        .journal-total-table col.c-cos   { width: 130px; }
        .expenses-table col.c-opex,
        .journal-total-table col.c-opex  { width: 130px; }
        .expenses-table col.c-vat,
        .journal-total-table col.c-vat   { width: 110px; }
        .expenses-table col.c-cash,
        .journal-total-table col.c-cash  { width: 130px; }
        .expenses-table col.c-ap,
        .journal-total-table col.c-ap    { width: 110px; }
        .expenses-table col.c-wtax,
        .journal-total-table col.c-wtax  { width: 140px; }
        .expenses-table col.c-by,
        .journal-total-table col.c-by    { width: 180px; }

        /* =========================================================
        STICKY HEADER
        ========================================================= */
        #deletionScrollBox > .expenses-table > thead > tr > th {
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
        #deletionScrollBox > .expenses-table > thead > tr > th:first-child {
            left: 0;
            z-index: 30;
            background-color: #eef0f3;
            box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.12);
            text-align: center;
        }
        #deletionScrollBox > .expenses-table > thead > tr > th:last-child {
            border-right: none;
        }

        /* =========================================================
        STICKY REFERENCE COLUMN
        ========================================================= */
        tbody.transaction-group > tr > td.txn-col,
        tbody.txn-block > tr > td.txn-col {
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
        tbody.transaction-group,
        tbody.txn-block {
            position: relative;
            outline: 1px solid #e5e7eb;
            outline-offset: -1px;
            border-radius: 12px;
        }
        tbody.transaction-group > tr > td,
        tbody.transaction-group > tr > th,
        tbody.txn-block > tr > td,
        tbody.txn-block > tr > th {
            border-top: 1px solid #eef0f3;
            transition: background-color 0.18s ease;
        }

        /* =========================================================
        ZEBRA — HORIZONTAL (per transaction)  +  VERTICAL (per column)
        ========================================================= */
        .expenses-table > tbody.transaction-group:nth-of-type(odd) > tr > td,
        .expenses-table > tbody.transaction-group:nth-of-type(odd) > tr > th,
        .expenses-table > tbody.txn-block:nth-of-type(odd) > tr > td,
        .expenses-table > tbody.txn-block:nth-of-type(odd) > tr > th {
            background-color: #ffffff;
        }
        .expenses-table > tbody.transaction-group:nth-of-type(even) > tr > td,
        .expenses-table > tbody.transaction-group:nth-of-type(even) > tr > th,
        .expenses-table > tbody.txn-block:nth-of-type(even) > tr > td,
        .expenses-table > tbody.txn-block:nth-of-type(even) > tr > th {
            background-color: #f5f6f8;
        }

        .expenses-table > tbody.transaction-group > tr > td:nth-child(even):not(.txn-col),
        .expenses-table > tbody.txn-block > tr > td:nth-child(even):not(.txn-col) {
            background-color: #f4f6f9;
        }
        .expenses-table > tbody.transaction-group > tr > td:nth-child(odd):not(.txn-col),
        .expenses-table > tbody.txn-block > tr > td:nth-child(odd):not(.txn-col) {
            background-color: #ffffff;
        }

        .expenses-table > tbody.transaction-group > tr > td.txn-col,
        .expenses-table > tbody.txn-block > tr > td.txn-col {
            background-color: #e8ebef;
        }
        .expenses-table > tbody.transaction-group:nth-of-type(even) > tr > td.txn-col,
        .expenses-table > tbody.txn-block:nth-of-type(even) > tr > td.txn-col {
            background-color: #dfe3e8;
        }

        #deletionScrollBox > .expenses-table > thead > tr > th:nth-child(even) { background-color: #eef0f3; }
        #deletionScrollBox > .expenses-table > thead > tr > th:nth-child(odd)  { background-color: #f8f9fb; }
        #deletionScrollBox > .expenses-table > thead > tr > th:first-child     { background-color: #eef0f3; }

        /* =========================================================
        HOVER — whole transaction block
        ========================================================= */
        .expenses-table > tbody.transaction-group:hover > tr > td:not(.txn-col),
        .expenses-table > tbody.transaction-group:hover > tr > th,
        .expenses-table > tbody.txn-block:hover > tr > td:not(.txn-col),
        .expenses-table > tbody.txn-block:hover > tr > th {
            background-color: #dbe4ff !important;
        }
        .expenses-table > tbody.transaction-group:hover > tr > td.txn-col,
        .expenses-table > tbody.txn-block:hover > tr > td.txn-col {
            background-color: #dde3f5;
        }
        tbody.transaction-group::before,
        tbody.txn-block::before {
            content: '';
            position: absolute;
            left: 0; top: 8px; bottom: 8px;
            width: 3px;
            border-radius: 3px;
            background-color: transparent;
            transition: background-color 0.18s ease;
            pointer-events: none;
        }
        tbody.transaction-group:hover::before,
        tbody.txn-block:hover::before { background-color: #3b82f6; }

        /* =========================================================
        ROW BORDERS
        ========================================================= */
        tbody.transaction-group > tr > td:first-child,
        tbody.transaction-group > tr > th:first-child,
        tbody.txn-block > tr > td:first-child,
        tbody.txn-block > tr > th:first-child { border-left: 1px solid #eef0f3; }
        tbody.transaction-group > tr > td:last-child,
        tbody.transaction-group > tr > th:last-child,
        tbody.txn-block > tr > td:last-child,
        tbody.txn-block > tr > th:last-child  { border-right: 1px solid #eef0f3; }
        tbody.transaction-group > tr + tr > td,
        tbody.txn-block > tr + tr > td { border-top: none; }

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
        GRAND TOTAL — sticky bottom, mirrors column widths
        ========================================================= */
        .journal-total-table {
            border-spacing: 0;
            margin-top: 12px;
            position: sticky;
            bottom: 0;
            z-index: 25;
            background: #f3f4f6;
            box-shadow: 0 -2px 6px -1px rgba(0, 0, 0, 0.08);
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

        .expenses-table > thead > tr > th:nth-child(5),
        .journal-total-table > tbody > tr > td:nth-child(5) {
            border-right: 2px solid #9ca3af;
        }

        /* =========================================================
        FILTER UI
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

        #filterDropdown { max-height: min(420px, calc(100vh - 100px)); flex-direction: column; }
        #filterDropdown:not(.hidden) { display: flex; }

        /* =========================================================
        STATUS DOTS (Approval / Deletion)
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
        SEARCH SHELL (Approval / General Expenses)
        ========================================================= */
        .search-shell {
            position: relative;
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
            flex-shrink: 0;
            align-self: center;
        }
        .search-shell:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
        .search-shell.open  { width: 320px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08); }
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

        /* ===== DELETION-APPROVAL-SPECIFIC ===== */

        tbody.transaction-group.status-pending > tr > td {
            background-color: #fdf6fb !important;
        }

        .attachment-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 12px;
            color: #374151;
            text-decoration: none;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .attachment-chip:hover { background: #f3f4f6; border-color: #d1d5db; }
    </style>

    @include('partials.expenses-responsive')

    @section('page_title', "DELETION APPROVAL")

    <div class="lg:ml-64 expenses-page-shell">
    <div class="p-3 lg:pt-3 max-w-full expenses-page">

        {{-- FILTER BAR --}}
        <div class="bg-white rounded-lg shadow-md p-4 mb-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <h1 class="text-2xl font-semibold text-gray-800">Deletion Approval</h1>
                <form method="GET" action="{{ route('deletion-approval.index') }}" class="flex flex-wrap items-center gap-2">
                    <label class="text-sm text-gray-500">From</label>
                    <input type="date" name="from" value="{{ $from }}" max="{{ now()->format('Y-m-d') }}" class="border border-gray-300 rounded-lg text-sm p-2 transition-colors focus:ring-blue-500 focus:border-blue-500">
                    <label class="text-sm text-gray-500">To</label>
                    <input type="date" name="to" value="{{ $to }}" max="{{ now()->format('Y-m-d') }}" class="border border-gray-300 rounded-lg text-sm p-2 transition-colors focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="uil uil-search"></i> Generate
                    </button>
                    <a href="{{ route('deletion-approval.index') }}" id="resetFiltersBtn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                        Reset
                    </a>
                </form>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-4">

            {{-- SUMMARY --}}
            <div class="mb-4">
                <div class="bg-yellow-100 inline-block rounded-lg p-3">
                    <p class="text-xs text-gray-600 uppercase font-semibold">Pending Deletion Requests</p>
                    <p class="text-lg font-bold text-yellow-700">{{ $pendingCount }}</p>
                </div>
            </div>

            {{-- TABLE --}}
            <div id="deletionScrollBox" class="table-scroll w-full {{ $pending->isEmpty() ? 'is-empty' : '' }}">
                <table class="expenses-table w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">Transaction #</th>
                            <th class="px-4 py-3 whitespace-nowrap">Requested</th>
                            <th class="px-4 py-3 whitespace-nowrap">Requested By</th>
                            <th class="px-4 py-3 whitespace-nowrap">Reason</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">Total</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($pending as $p)
                        <tbody class="transaction-group">
                            <tr class="status-pending">
                                <td rowspan="2" class="txn-col whitespace-nowrap">
                                    <span class="font-semibold text-gray-900 tracking-wide">{{ $p->e_transid }}</span>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex flex-col leading-tight">
                                        <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($p->deletion_requested_at)->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($p->deletion_requested_at)->format('h:i A') }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($p->firstname ?? '?', 0, 1) . substr($p->lastname ?? '', 0, 1)) }}
                                        </div>
                                        <span class="text-gray-700">{{ $p->firstname }} {{ $p->lastname }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-4 max-w-xs">
                                    <p class="text-gray-700 text-sm line-clamp-2" title="{{ $p->deletion_reason }}">
                                        {{ $p->deletion_reason ?: '—' }}
                                    </p>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap tabular-nums font-bold text-gray-900 text-base">
                                    ₱{{ number_format($p->e_total, 2) }}
                                </td>

                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200">
                                        <span class="status-dot dot-pending"></span>Pending Deletion
                                    </span>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" onclick="openApproveConfirm({{ $p->id }}, '{{ $p->e_transid }}')"
                                            class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                            Approve
                                        </button>
                                        <button type="button" onclick="openRejectModal({{ $p->id }}, '{{ $p->e_transid }}')"
                                            class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition-colors">
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="6" class="px-4 pt-0 pb-3">
                                    @php $items = $lineItemsByEId->get($p->e_id, collect()); @endphp
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
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                                            <i class="uil uil-file-search-alt text-2xl"></i>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500">No pending deletion requests in this date range</p>
                                        <p class="text-xs">Try widening the date filter.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $pending->appends(['from' => $from, 'to' => $to])->links() }}
            </div>
        </div>
    </div>
    </div>

    {{-- APPROVE CONFIRM MODAL --}}
    <div id="approveConfirmModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white shadow-lg rounded-lg w-full max-w-sm p-6">
            <h2 class="text-lg font-bold text-center mb-2">Approve Deletion?</h2>
            <p class="text-center text-sm text-gray-600 mb-6">
                Transaction <span id="approveConfirmTransId" class="font-semibold"></span> will be permanently marked as deleted. This cannot be undone.
            </p>
            <div class="flex flex-row justify-center gap-2">
                <button onclick="closeApproveConfirm()" class="px-6 py-2 bg-gray-200 text-gray-800 text-sm font-bold rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button onclick="submitApprove()" id="approveConfirmBtn" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg transition-colors">
                    Yes, Approve
                </button>
            </div>
        </div>
    </div>

    {{-- REJECT CONFIRM MODAL --}}
    <div id="rejectModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white shadow-lg rounded-lg w-full max-w-sm p-6">
            <div class="flex flex-col items-center gap-3 mb-4">
                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="uil uil-exclamation-triangle text-3xl text-red-600"></i>
                </div>
                <h2 class="text-lg font-bold text-center">Reject Deletion Request?</h2>
                <p class="text-center text-sm text-gray-600">
                    Transaction <span id="rejectTransId" class="font-semibold text-gray-800"></span> will <span class="font-semibold text-red-600">not</span> be deleted and will remain active.
                </p>
            </div>
            <div class="flex flex-row justify-center gap-2">
                <button onclick="closeRejectModal()" class="px-6 py-2 bg-gray-200 text-gray-800 text-sm font-bold rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button onclick="submitReject()" id="rejectSubmitBtn" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition-colors">
                    Yes, Reject
                </button>
            </div>
        </div>
    </div>

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
        /* =========================================================
        FILTER PERSISTENCE — date filter survives navigation within
        the same session (until the user signs out or hits Reset).
        ========================================================= */
        (function () {
            var FILTER_KEY = 'delApprovalFilterState';
            var RESET_FLAG = 'delApprovalJustReset';

            try {
                var curSearch = window.location.search;
                var curFull   = window.location.pathname + curSearch;

                if (curSearch && curSearch.length > 1) {
                    // Has query params → save them
                    sessionStorage.setItem(FILTER_KEY, curFull);
                    sessionStorage.removeItem(RESET_FLAG);
                } else if (sessionStorage.getItem(RESET_FLAG)) {
                    // User explicitly hit Reset → drop saved state
                    sessionStorage.removeItem(RESET_FLAG);
                    sessionStorage.removeItem(FILTER_KEY);
                } else {
                    // No query params → restore saved state if any
                    var saved = sessionStorage.getItem(FILTER_KEY);
                    if (saved && saved !== curFull) {
                        window.location.replace(saved);
                    }
                }
            } catch (e) { /* sessionStorage unavailable — ignore */ }
        })();

        document.addEventListener('DOMContentLoaded', function () {
            var resetBtn = document.getElementById('resetFiltersBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    try {
                        sessionStorage.setItem('delApprovalJustReset', '1');
                        sessionStorage.removeItem('delApprovalFilterState');
                    } catch (e) { /* ignore */ }
                });
            }
        });
    </script>

    <script>
        var actionTargetId = null;

        function openApproveConfirm(id, transId) {
            actionTargetId = id;
            document.getElementById('approveConfirmTransId').innerText = transId;
            document.getElementById('approveConfirmModal').classList.remove('hidden');
        }
        function closeApproveConfirm() {
            document.getElementById('approveConfirmModal').classList.add('hidden');
            actionTargetId = null;
        }
        function submitApprove() {
            if (!actionTargetId) return;
            var btn = document.getElementById('approveConfirmBtn');
            btn.disabled = true;
            btn.innerText = 'Approving...';

            fetch('{{ route("deletion-approval.approve") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ id: actionTargetId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeApproveConfirm();
                    showSuccessToast(data.message);
                    setTimeout(() => location.reload(), 1400);
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerText = 'Yes, Approve';
                }
            })
            .catch(() => {
                alert('Something went wrong while approving.');
                btn.disabled = false;
                btn.innerText = 'Yes, Approve';
            });
        }

        function openRejectModal(id, transId) {
            actionTargetId = id;
            document.getElementById('rejectTransId').innerText = transId;
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            actionTargetId = null;
        }
        function submitReject() {
            if (!actionTargetId) return;

            var btn = document.getElementById('rejectSubmitBtn');
            btn.disabled = true;
            btn.innerText = 'Rejecting...';

            fetch('{{ route("deletion-approval.reject") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: actionTargetId })
            })
            .then(res => res.json().then(data => ({ status: res.status, data })))
            .then(({ status, data }) => {
                if (data.success) {
                    closeRejectModal();
                    showSuccessToast(data.message);
                    setTimeout(() => location.reload(), 1400);
                } else {
                    alert(data.message || 'Something went wrong.');
                    btn.disabled = false;
                    btn.innerText = 'Yes, Reject';
                }
            })
            .catch(() => {
                alert('Something went wrong while rejecting.');
                btn.disabled = false;
                btn.innerText = 'Yes, Reject';
            });
        }

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
</x-app-layout>