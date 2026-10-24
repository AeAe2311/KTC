<x-app-layout>
    <style>
        .double-underline {
            text-decoration-line: underline;
            text-decoration-style: double;
            text-underline-offset: 4px;
        }
        .income-statement-table {
            table-layout: fixed;
            min-width: max-content;
            white-space: nowrap;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        .income-statement-table th,
        .income-statement-table td {
            border: 1px solid #000;
        }
        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 20;
        }
        .sticky-header {
            position: sticky;
            left: 0;
            z-index: 40;
        }

        /* ---- Modal scroll container ---- */
        .expense-modal-scroll {
            max-height: 55vh;
            overflow-y: auto;
            overflow-x: auto;
        }
        .expense-modal-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #e5e7eb;
        }
        .expense-modal-scroll tbody td.sticky-left-cell,
        .expense-modal-scroll thead th.sticky-left-cell {
            position: sticky;
            left: 0;
            z-index: 6;
        }
        .expense-modal-scroll thead th.sticky-left-cell {
            z-index: 7;
        }
        /* 20260528001 JET START LINES 50 - 54 */
        .expense-modal-scroll tbody.breakdown-group:hover > tr {
            background-color: #eff6ff;
        } /* 20260528001 JET START LINES 50 - 54 */
    </style>

    @section('page_title', 'INCOME STATEMENT')

    <div class="p-3 lg:pt-3 {{ (Auth::user()->role == 2) ? '' : ' lg:ml-64' }}">
        <div class="py-3">

            {{-- ============================================================ --}}
            {{-- FILTER FORM --}}
            {{-- ============================================================ --}}
            <div class="bg-gray-200 overflow-hidden shadow-md rounded-lg p-3 mb-6">
                <form action="{{ route('reports.income-statement.generate') }}" method="POST">
                    @csrf
                    <div class="flex items-end gap-4">

                        {{-- Date Range --}}
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-900 mb-1">Date</label>
                            <div class="flex items-center gap-x-2">
                                <input type="datetime-local" name="start" id="dateStart"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    value="{{ isset($startDate) ? date('Y-m-d\TH:i', strtotime($startDate)) : date('Y-m-d\T00:00') }}">
                                <span class="text-gray-700 font-medium">to</span>
                                <input type="datetime-local" name="end" id="dateEnd"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    value="{{ isset($endDate) ? date('Y-m-d\TH:i', strtotime($endDate)) : date('Y-m-d\T23:59') }}">
                            </div>
                        </div>

                        {{-- Branch (Admin only; every role when the data comes from the selected company's API) --}}
                        @auth
                            @if(auth()->user()->role == 1 || ($storePickerForAllRoles ?? false))
                                <div class="flex-1">
                                    <label for="branch" class="block text-sm font-medium text-gray-900 mb-1">
                                        {{ ($storePickerForAllRoles ?? false) ? 'Store' : 'Branch' }} <span class="text-red-500">*</span>
                                    </label>
                                    <select id="branch" name="branch"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        <option value="" disabled {{ empty($selectedBranch) ? 'selected' : '' }}>Select a branch</option>
                                        @forelse ($branchs as $branch)
                                            <option value="{{ $branch->store_code }}"
                                                @selected(($selectedBranch ?? null) === $branch->store_code)>
                                                {{ $branch->name }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No branches available</option>
                                        @endforelse
                                    </select>
                                </div>
                            @endif
                        @endauth

                        {{-- Generate --}}
                        <div class="flex-1">
                            <button class="bg-blue-600 font-bold text-white w-full py-2.5 rounded-lg hover:scale-105 transition-transform">
                                Generate
                            </button>
                        </div>
                        <div class="flex-1">
                            <button id="exportIncomeStatementBtn"
                                    type="button"
                                    title="Print Income Statement"
                                    class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg shadow-md transition duration-300 ease-in-out flex items-center justify-center w-11 h-11">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="M6 9V2h12v7" />
                                    <path d="M6 19H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                    <path d="M6 14h12v7H6z" />
                                </svg>
                                <span class="sr-only">Print Income Statement</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ============================================================ --}}
            {{-- INCOME STATEMENT TABLE --}}
            {{-- ============================================================ --}}
            @if(isset($months) && !empty($months))
            <div id="incomeStatementContainer" class="bg-white shadow-md rounded-lg p-6 mb-6">
                <div class="mb-5">
                    <h2 class="text-xl font-semibold">Income Statement</h2>
                    <p class="text-gray-600">
                        Date Range: {{ date('M d, Y h:i A', strtotime($startDate)) }} to {{ date('M d, Y h:i A', strtotime($endDate)) }}
                    </p>
                </div>

                <div class="overflow-x-auto scroll-smooth">
                    <table class="income-statement-table text-sm">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="sticky-header px-4 py-3 w-[400px] text-left font-semibold bg-gray-200 border-r border-b border-black border-l border-black" rowspan="2"></th>
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

                            {{-- ==================== SALES, NET ==================== --}}
                            <tr class="bg-gray-100 font-semibold cursor-pointer hover:bg-gray-200 transition-colors breakdown-row"
                                data-breakdown-type="sales"
                                title="Click to view sales breakdown">
                                <td class="sticky-col px-4 py-4 w-[400px] bg-gray-100 border-r border-black border-l border-black">
                                    <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="12" y1="16" x2="12" y2="12"/>
                                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                                        </svg>
                                        Sales, Net
                                    </span>
                                </td>
                                @foreach($months as $monthKey => $label)
                                    <td class="px-4 py-4 text-right border-r border-black">
                                        ₱ {{ number_format(($salesByMonth[$monthKey] ?? 0) + ($serviceChargeByMonth[$monthKey] ?? 0), 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right border-r border-black">
                                        {{ number_format($salesPctByMonth[$monthKey] ?? 0, 2) }}%
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ==================== COST OF GOODS SOLD ==================== --}}
                            <tr class="bg-gray-50 font-semibold cursor-pointer hover:bg-gray-200 transition-colors breakdown-row"
                                data-breakdown-type="cogs"
                                title="Click to view cost of goods sold breakdown">
                                <td class="sticky-col px-4 py-4 w-[400px] bg-gray-50 border-r border-black border-l border-black">
                                    <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="12" y1="16" x2="12" y2="12"/>
                                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                                        </svg>
                                        Cost of Goods Sold
                                    </span>
                                </td>
                                @foreach($months as $monthKey => $label)
                                    <td class="px-4 py-4 text-right border-r border-b border-black">
                                        ₱ {{ number_format($cogsValueByMonth[$monthKey] ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right border-r border-b border-black">
                                        {{ number_format($cogsPctByMonth[$monthKey] ?? 0, 2) }}%
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ==================== GROSS PROFIT ==================== --}}
                            <tr class="bg-gray-100 font-semibold">
                                <td class="sticky-col px-4 py-4 w-[400px] bg-gray-100 border-r border-black border-l border-t border-black">
                                    Gross Profit
                                </td>
                                @foreach($months as $monthKey => $label)
                                    <td class="px-4 py-4 text-right border-r border-b border-black">
                                        ₱ {{ number_format(
                                            ($salesByMonth[$monthKey] ?? 0) +
                                            ($serviceChargeByMonth[$monthKey] ?? 0) -
                                            ($cogsValueByMonth[$monthKey] ?? 0) +
                                            ($cashOverageByMonth[$monthKey] ?? 0),
                                            2
                                        ) }}
                                    </td>
                                    <td class="px-4 py-4 text-right border-r border-b border-black border-t">
                                        {{ number_format(
                                            ($salesPctByMonth[$monthKey] ?? 0) -
                                            ($cogsPctByMonth[$monthKey] ?? 0) +
                                            (
                                                ($salesByMonth[$monthKey] ?? 0) > 0
                                                    ? (($cashOverageByMonth[$monthKey] ?? 0) / $salesByMonth[$monthKey]) * 100
                                                    : 0
                                            ),
                                            2
                                        ) }}%
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ==================== OPERATING EXPENSES HEADER ==================== --}}
                            <tr class="bg-gray-400 font-semibold">
                                <td class="sticky-col px-4 py-4 w-[400px] bg-gray-400 border-r border-black border-l border-black">
                                    Operating Expenses
                                </td>
                                @foreach($months as $monthKey => $label)
                                    <td class="px-4 py-4 border-r border-black bg-gray-400"></td>
                                    <td class="px-4 py-4 border-r border-black bg-gray-400"></td>
                                @endforeach
                            </tr>

                            {{-- ==================== OPERATING EXPENSE CATEGORIES ==================== --}}
                            @if(!empty($expenseCategoryData))
                                @foreach($expenseCategoryData as $expenseCategory)
                                    @php
                                        $hasValue = false;
                                        foreach ($months as $mKey => $mLabel) {
                                            if (($expenseCategory['total_by_month'][$mKey] ?? 0) > 0) {
                                                $hasValue = true;
                                                break;
                                            }
                                        }
                                    @endphp
                                    @if(strtolower(trim($expenseCategory['category'])) !== 'cash overage' && $hasValue)
                                        <tr class="bg-gray-100 font-semibold cursor-pointer hover:bg-gray-200 transition-colors expense-category-row"
                                            data-category="{{ $expenseCategory['category'] }}"
                                            data-category-id="{{ $loop->index }}"
                                            title="Click to view item breakdown">
                                            <td class="sticky-col w-[400px] pl-8 text-left px-16 bg-gray-100 border-r border-black border-l border-black">
                                                <span class="flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10"/>
                                                        <line x1="12" y1="16" x2="12" y2="12"/>
                                                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                                                    </svg>
                                                    {{ ucwords(strtolower($expenseCategory['category'])) }}
                                                </span>
                                            </td>
                                            @foreach($months as $monthKey => $label)
                                                <td class="px-4 py-4 text-right border-r border-black">
                                                    ₱ {{ number_format($expenseCategory['total_by_month'][$monthKey] ?? 0, 2) }}
                                                </td>
                                                <td class="px-4 py-4 text-right border-r border-black">
                                                    {{ number_format(($salesByMonth[$monthKey] ?? 0) > 0 ? (($expenseCategory['total_by_month'][$monthKey] ?? 0) / $salesByMonth[$monthKey]) * 100 : 0, 2) }}%
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr class="bg-white">
                                    <td class="px-4 py-4 text-center" colspan="{{ count($months) * 2 + 1 }}">
                                        No operating expense data found for the selected period.
                                    </td>
                                </tr>
                            @endif

                            {{-- ==================== TOTAL OPERATING EXPENSES ==================== --}}
                            <tr class="bg-gray-200 font-bold">
                                <td class="sticky-col px-4 py-4 w-[400px] border-r border-black border-l border-black border-t">
                                    Total Operating Expenses
                                </td>
                                @foreach($months as $monthKey => $label)
                                    <td class="px-4 py-4 text-right border-r border-t border-black">
                                        ₱ {{ number_format($operatingByMonth[$monthKey] ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right border-r border-t border-black">
                                        {{ number_format($operatingPctByMonth[$monthKey] ?? 0, 2) }}%
                                    </td>
                                @endforeach
                            </tr>

                            {{-- ==================== NET INCOME ==================== --}}
                            <tr class="bg-gray-100 font-semibold">
                                <td class="sticky-col px-4 py-4 w-[400px] bg-gray-100 border-r border-black border-l border-black border-t">
                                    NET INCOME
                                </td>
                                @foreach($months as $monthKey => $label)
                                    <td class="px-4 py-4 text-right border-r border-t border-black double-underline">
                                        ₱ {{ number_format($netIncomeByMonth[$monthKey] ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right border-r border-t border-black double-underline">
                                        {{ number_format($netIncomePctByMonth[$monthKey] ?? 0, 2) }}%
                                    </td>
                                @endforeach
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            @else
                <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                    <p class="text-center text-gray-500">No data available. Please generate the report.</p>
                </div>
            @endif

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- BREAKDOWN MODAL (shared by expenses / sales / COGS) --}}
    {{-- ============================================================ --}}
    <div id="expenseModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="expenseModalLabel" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" id="expenseModalBackdrop"></div>

        {{-- Modal Panel --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-6xl transform overflow-hidden rounded-lg bg-white shadow-xl transition-all flex flex-col"
                 style="height: 80vh; max-height: 80vh;">

                {{-- Header (fixed) --}}
                <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-4 flex-shrink-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900" id="expenseModalLabel">
                            Breakdown
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Click outside or press ESC to close</p>
                    </div>
                    <button type="button" id="closeExpenseModal"
                        class="rounded-md bg-white p-1 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body (fills remaining space; only table scrolls) --}}
                <div class="px-6 py-4 flex-1 flex flex-col min-h-0 overflow-hidden">
                    <div id="expenseModalContent" class="flex-1 flex flex-col min-h-0 overflow-hidden">
                        {{-- Dynamic content will be injected here --}}
                    </div>
                </div>

                {{-- Footer (fixed) --}}
                <div class="flex justify-end border-t border-gray-200 bg-gray-50 px-6 py-3 flex-shrink-0">
                    <button type="button" id="closeExpenseModalFooter"
                        class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SCRIPT: FORM STATE + PRINT + MODAL --}}
    {{-- ============================================================ --}}
    <script>
        $(document).ready(function () {
            var currentStartDate = "{{ isset($startDate) ? date('Y-m-d\TH:i', strtotime($startDate)) : '' }}";
            var currentEndDate   = "{{ isset($endDate) ? date('Y-m-d\TH:i', strtotime($endDate)) : '' }}";
            var currentBranch    = "{{ $selectedBranch ?? '' }}";

            if (currentStartDate) $('#dateStart').val(currentStartDate);
            if (currentEndDate)   $('#dateEnd').val(currentEndDate);
            if (currentBranch && $('#branch').length) $('#branch').val(currentBranch);
        });

        // ---------- PRINT INCOME STATEMENT ----------
        $('#exportIncomeStatementBtn').on('click', function () {
            var container = document.getElementById('incomeStatementContainer');
            if (!container) {
                alert('No income statement content available to print.');
                return;
            }

            var printWindow = window.open('', '_blank');
            if (!printWindow) {
                alert('Unable to open print window. Please allow popups.');
                return;
            }

            var styles = `
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; color: #111827; background: #fff; }
                    h2 { text-align: left; font-size: 20px; font-weight: 700; margin: 0 0 4px 0; }
                    p  { font-size: 13px; margin: 0 0 16px 0; color: #4b5563; }
                    table { border-collapse: collapse; width: 100%; font-size: 12px; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: right; }
                    th:first-child, td:first-child { text-align: left; width: 320px; }
                    thead th { background: #e5e7eb; text-align: center; }
                    .double-underline { text-decoration-line: underline; text-decoration-style: double; text-underline-offset: 4px; }
                    .bg-gray-100 td { background: #f3f4f6; }
                    .bg-gray-200 td { background: #e5e7eb; }
                    .bg-gray-400 td { background: #9ca3af; }
                    @media print {
                        body { margin: 10mm; }
                        table, th, td { page-break-inside: avoid; }
                    }
                </style>
            `;

            var printTitle = `<div style="margin-bottom:20px;font-size:18px;font-weight:700;text-align:center;">MURANG KARNE SEAFOOD AT IBA PA</div>`;
            var printableContent = container.cloneNode(true);

            printableContent.querySelectorAll('.expense-category-row svg, .breakdown-row svg').forEach(function (svg) {
                svg.remove();
            });

            var expenseCategoryData = @json($expenseCategoryData ?? []);
            var monthsData = @json($months ?? []);

            if (expenseCategoryData && expenseCategoryData.length > 0) {
                var monthKeys = Object.keys(monthsData);
                var breakdownHtml = '<div style="margin-top:30px;page-break-before:always;">';
                breakdownHtml += '<h3 style="font-size:16px;font-weight:700;margin-bottom:12px;">Expense Breakdown by Category</h3>';

                expenseCategoryData.forEach(function (category) {
                    if (category.category.toLowerCase().trim() === 'cash overage') return;

                    if (category.items && category.items.length > 0) {
                        var hasValue = false;
                        monthKeys.forEach(function (mKey) {
                            if (parseFloat(category.total_by_month[mKey] || 0) > 0) {
                                hasValue = true;
                            }
                        });
                        if (!hasValue) return;

                        breakdownHtml += '<h4 style="font-size:14px;font-weight:600;margin:16px 0 8px 0;">' + category.category + '</h4>';
                        breakdownHtml += '<table style="width:100%;border-collapse:collapse;font-size:11px;margin-bottom:16px;">';
                        breakdownHtml += '<thead><tr style="background:#e5e7eb;">';
                        breakdownHtml += '<th style="border:1px solid #000;padding:6px;text-align:left;">Item</th>';
                        monthKeys.forEach(function (mKey) {
                            breakdownHtml += '<th style="border:1px solid #000;padding:6px;text-align:right;">' + monthsData[mKey] + '</th>';
                        });
                        breakdownHtml += '<th style="border:1px solid #000;padding:6px;text-align:right;background:#d1d5db;">Total</th>';
                        breakdownHtml += '</tr></thead><tbody>';

                        var monthlyTotals = {};
                        monthKeys.forEach(function (mKey) { monthlyTotals[mKey] = 0; });
                        var grandTotal = 0;

                        category.items.forEach(function (item) {
                            var itemTotal = 0;
                            breakdownHtml += '<tr>';
                            breakdownHtml += '<td style="border:1px solid #000;padding:6px;">' + (item.item || '-') + '</td>';
                            monthKeys.forEach(function (mKey) {
                                var amount = (item.amount_by_month && item.amount_by_month[mKey]) ? parseFloat(item.amount_by_month[mKey]) : 0;
                                itemTotal += amount;
                                monthlyTotals[mKey] += amount;
                                breakdownHtml += '<td style="border:1px solid #000;padding:6px;text-align:right;">₱ ' + amount.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>';
                            });
                            grandTotal += itemTotal;
                            breakdownHtml += '<td style="border:1px solid #000;padding:6px;text-align:right;font-weight:600;background:#f3f4f6;">₱ ' + itemTotal.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>';
                            breakdownHtml += '</tr>';
                        });

                        breakdownHtml += '<tr style="font-weight:700;background:#f3f4f6;">';
                        breakdownHtml += '<td style="border:1px solid #000;padding:6px;">Total</td>';
                        monthKeys.forEach(function (mKey) {
                            breakdownHtml += '<td style="border:1px solid #000;padding:6px;text-align:right;">₱ ' + monthlyTotals[mKey].toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>';
                        });
                        breakdownHtml += '<td style="border:1px solid #000;padding:6px;text-align:right;background:#e5e7eb;">₱ ' + grandTotal.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>';
                        breakdownHtml += '</tr></tbody></table>';
                    }
                });

                breakdownHtml += '</div>';
                printableContent.insertAdjacentHTML('beforeend', breakdownHtml);
            }

            printWindow.document.write(
                `<!DOCTYPE html><html><head><title>Print Income Statement</title>${styles}</head><body>${printTitle}${printableContent.outerHTML}</body></html>`
            );
            printWindow.document.close();
            printWindow.focus();

            printWindow.onload = function () {
                printWindow.print();
            };
        });

        // ---------- BREAKDOWN MODAL (shared) ----------
        (function () {
            var expenseCategoryData    = @json($expenseCategoryData ?? []);
            var months                 = @json($months ?? []);
            var salesBreakdown         = @json($salesBreakdown ?? []);
            var serviceChargeBreakdown = @json($serviceChargeBreakdown ?? []);
            var cogsBreakdown          = @json($cogsBreakdown ?? []);

            var modal = document.getElementById('expenseModal');
            var modalContent = document.getElementById('expenseModalContent');
            var modalLabel = document.getElementById('expenseModalLabel');

            if (!modal) return;

            function openModal() {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }

            function formatNumber(num) {
                return Number(num || 0).toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function escapeHtml(str) {
                return String(str || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            // =====================================================
            // Expense category modal (existing behavior)
            // =====================================================
            function buildModalContent(category) {
                var items = category.items || [];
                var monthKeys = Object.keys(months);

                var sortedItems = items.slice().sort(function (a, b) {
                    var totalA = 0, totalB = 0;
                    monthKeys.forEach(function (mKey) {
                        totalA += parseFloat((a.amount_by_month && a.amount_by_month[mKey]) || 0);
                        totalB += parseFloat((b.amount_by_month && b.amount_by_month[mKey]) || 0);
                    });
                    return totalB - totalA;
                });

                var headerHtml = '<tr class="bg-gray-200">';
                headerHtml += '<th class="sticky-left-cell px-4 py-3 text-left text-xs font-semibold text-gray-700 border border-black bg-gray-200" style="min-width: 320px; max-width: 480px;">Item / Description</th>';
                monthKeys.forEach(function (mKey) {
                    headerHtml += '<th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 border border-black bg-gray-200" style="min-width: 140px;">' + months[mKey] + '</th>';
                });
                headerHtml += '<th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 border border-black bg-gray-300" style="min-width: 140px;">Total</th>';
                headerHtml += '</tr>';

                var bodyHtml = '';
                var grandTotal = 0;
                var monthlyTotals = {};
                monthKeys.forEach(function (mKey) { monthlyTotals[mKey] = 0; });

                if (sortedItems.length === 0) {
                    bodyHtml = '<tr><td colspan="' + (monthKeys.length + 2) + '" class="px-4 py-6 text-center text-gray-500 border border-black">No items found for this category.</td></tr>';
                } else {
                    sortedItems.forEach(function (item, idx) {
                        var itemTotal = 0;
                        var rowClass = idx % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                        bodyHtml += '<tr class="' + rowClass + ' hover:bg-blue-50 transition-colors">';

                        var itemText = item.item || '-';
                        var rawText  = item.raw_item || itemText;
                        bodyHtml += '<td class="sticky-left-cell px-4 py-3 text-sm text-gray-800 border border-black ' + rowClass + '" style="min-width: 320px; max-width: 480px; word-break: break-word; white-space: normal; line-height: 1.5;" title="' + escapeHtml(rawText) + '">';
                        bodyHtml += escapeHtml(itemText);
                        bodyHtml += '</td>';

                        monthKeys.forEach(function (mKey) {
                            var amount = (item.amount_by_month && item.amount_by_month[mKey]) ? parseFloat(item.amount_by_month[mKey]) : 0;
                            itemTotal += amount;
                            monthlyTotals[mKey] += amount;
                            var amountClass = amount > 0 ? 'text-gray-900 font-medium' : 'text-gray-400';
                            bodyHtml += '<td class="px-4 py-3 text-sm text-right border border-black whitespace-nowrap ' + amountClass + '">₱ ' + formatNumber(amount) + '</td>';
                        });

                        grandTotal += itemTotal;
                        bodyHtml += '<td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 border border-black bg-yellow-50 whitespace-nowrap">₱ ' + formatNumber(itemTotal) + '</td>';
                        bodyHtml += '</tr>';
                    });

                    bodyHtml += '<tr class="bg-gray-800 text-white font-bold">';
                    bodyHtml += '<td class="sticky-left-cell px-4 py-3 text-sm border border-black bg-gray-800">TOTAL</td>';
                    monthKeys.forEach(function (mKey) {
                        bodyHtml += '<td class="px-4 py-3 text-sm text-right border border-black whitespace-nowrap bg-gray-800">₱ ' + formatNumber(monthlyTotals[mKey]) + '</td>';
                    });
                    bodyHtml += '<td class="px-4 py-3 text-sm text-right border border-black bg-yellow-400 text-gray-900 whitespace-nowrap">₱ ' + formatNumber(grandTotal) + '</td>';
                    bodyHtml += '</tr>';
                }

                var tableHtml = '<div class="expense-modal-scroll rounded border border-black">' +
                    '<table class="min-w-full border-collapse text-sm">' +
                    '<thead>' + headerHtml + '</thead>' +
                    '<tbody>' + bodyHtml + '</tbody>' +
                    '</table></div>';

                return tableHtml;
            }

            // =====================================================
            // Generic breakdown modal (Sales, Net / COGS)
            // =====================================================
            // keepOrder: list rows in the order the server sent them (COGS: Beginning → Purchases → Ending)
            // 20260528001 JET START LINES 639 - 660
            function buildGenericBreakdownHtml(breakdownByMonth, keepOrder) {
                var monthKeys = Object.keys(months);

                // Rows may carry `lines` (a P.O.'s items). They get a Details + Amount cell each,
                // while the P.O. cell and its month/Total cells span all of them.
                var linesMap = {};
                monthKeys.forEach(function (mKey) {
                    (breakdownByMonth[mKey] || []).forEach(function (row) {
                        if (row.lines && row.lines.length) { linesMap[row.item || 'Unnamed Item'] = row.lines; }
                    });
                });
                var showDetails = Object.keys(linesMap).length > 0;
                var detailCellClass = 'px-4 py-3 text-sm text-gray-800 border border-black';
                var lineAmountClass = 'px-4 py-3 text-sm text-right text-gray-900 border border-black whitespace-nowrap';

                var headerHtml = '<tr class="bg-gray-200">';
                headerHtml += '<th class="sticky-left-cell px-4 py-3 text-left text-xs font-semibold text-gray-700 border border-black bg-gray-200" style="min-width: 320px; max-width: 480px;">Item / Description</th>';
                if (showDetails) {
                    headerHtml += '<th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border border-black bg-gray-200" style="min-width: 220px;">Details</th>';
                    headerHtml += '<th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 border border-black bg-gray-200" style="min-width: 140px;">Amount</th>';
                } // 20260528001 JET END
                monthKeys.forEach(function (mKey) {
                    headerHtml += '<th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 border border-black bg-gray-200" style="min-width: 140px;">' + months[mKey] + '</th>';
                });
                headerHtml += '<th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 border border-black bg-gray-300" style="min-width: 140px;">Total</th>';
                headerHtml += '</tr>';

                // Merge all items across months: itemName -> { mKey: amount }
                var itemMap = {};
                monthKeys.forEach(function (mKey) {
                    (breakdownByMonth[mKey] || []).forEach(function (row) {
                        var name = row.item || 'Unnamed Item';
                        if (!itemMap[name]) {
                            itemMap[name] = {};
                            monthKeys.forEach(function (k) { itemMap[name][k] = 0; });
                        }
                        itemMap[name][mKey] += parseFloat(row.amount || 0);
                    });
                });

                // 20260528001 JET START LINES 680 - 685
                var itemNames = keepOrder ? Object.keys(itemMap) : Object.keys(itemMap).sort(function (a, b) {
                    var totalA = 0, totalB = 0;
                    monthKeys.forEach(function (k) { totalA += itemMap[a][k]; totalB += itemMap[b][k]; });
                    return totalB - totalA;
                }); // 20260528001 JET END

                var bodyHtml = '';
                var grandTotal = 0;
                var monthlyTotals = {};
                monthKeys.forEach(function (mKey) { monthlyTotals[mKey] = 0; });

                // 20260528001 JET START LINES 692 - 750
                if (itemNames.length === 0) {
                    bodyHtml = '<tbody><tr><td colspan="' + (monthKeys.length + 2 + (showDetails ? 2 : 0)) + '" class="px-4 py-6 text-center text-gray-500 border border-black">No breakdown data available.</td></tr></tbody>';
                } else {
                    itemNames.forEach(function (name, idx) {
                        var rowClass = idx % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                        var lines = linesMap[name] || [];
                        var span = Math.max(lines.length, 1);
                        var rowspan = span > 1 ? ' rowspan="' + span + '"' : '';

                        // One <tbody> per item, so hovering highlights all of a P.O.'s rows together
                        bodyHtml += '<tbody class="breakdown-group">';
                        bodyHtml += '<tr class="' + rowClass + ' hover:bg-blue-50 transition-colors">';
                        bodyHtml += '<td' + rowspan + ' class="sticky-left-cell px-4 py-3 text-sm text-gray-800 border border-black ' + rowClass + '" style="min-width: 320px; max-width: 480px; word-break: break-word; white-space: normal; line-height: 1.5;">' + escapeHtml(name) + '</td>';
                        if (showDetails) {
                            bodyHtml += '<td class="' + detailCellClass + '">' + (lines[0] ? escapeHtml(lines[0].label) : '') + '</td>';
                            bodyHtml += '<td class="' + lineAmountClass + '">' + (lines[0] ? '₱ ' + formatNumber(parseFloat(lines[0].amount || 0)) : '') + '</td>';
                        }

                        var itemTotal = 0;
                        monthKeys.forEach(function (mKey) {
                            var amount = itemMap[name][mKey] || 0;
                            itemTotal += amount;
                            monthlyTotals[mKey] += amount;
                            var amountClass = amount > 0 ? 'text-gray-900 font-medium' : 'text-gray-400';
                            bodyHtml += '<td' + rowspan + ' class="px-4 py-3 text-sm text-right border border-black whitespace-nowrap ' + amountClass + '">₱ ' + formatNumber(amount) + '</td>';
                        });

                        grandTotal += itemTotal;
                        bodyHtml += '<td' + rowspan + ' class="px-4 py-3 text-sm text-right font-semibold text-gray-900 border border-black bg-yellow-50 whitespace-nowrap">₱ ' + formatNumber(itemTotal) + '</td>';
                        bodyHtml += '</tr>';

                        // The P.O.'s other items: only their Details + Amount cells (the rest is spanned).
                        for (var i = 1; i < lines.length; i++) {
                            bodyHtml += '<tr class="' + rowClass + ' hover:bg-blue-50 transition-colors">';
                            bodyHtml += '<td class="' + detailCellClass + '">' + escapeHtml(lines[i].label) + '</td>';
                            bodyHtml += '<td class="' + lineAmountClass + '">₱ ' + formatNumber(parseFloat(lines[i].amount || 0)) + '</td>';
                            bodyHtml += '</tr>';
                        }
                        bodyHtml += '</tbody>';
                    });

                    bodyHtml += '<tbody><tr class="bg-gray-800 text-white font-bold">';
                    bodyHtml += '<td class="sticky-left-cell px-4 py-3 text-sm border border-black bg-gray-800">TOTAL</td>';
                    if (showDetails) {
                        bodyHtml += '<td colspan="2" class="px-4 py-3 text-sm border border-black bg-gray-800"></td>';
                    }
                    monthKeys.forEach(function (mKey) {
                        bodyHtml += '<td class="px-4 py-3 text-sm text-right border border-black whitespace-nowrap bg-gray-800">₱ ' + formatNumber(monthlyTotals[mKey]) + '</td>';
                    });
                    bodyHtml += '<td class="px-4 py-3 text-sm text-right border border-black bg-yellow-400 text-gray-900 whitespace-nowrap">₱ ' + formatNumber(grandTotal) + '</td>';
                    bodyHtml += '</tr></tbody>';
                } 

                return '<div class="expense-modal-scroll rounded border border-black">' +
                    '<table class="min-w-full border-collapse text-sm">' +
                    '<thead>' + headerHtml + '</thead>' +
                    bodyHtml +
                    '</table></div>'; // 20260528001 JET END
            }

            // =====================================================
            // Wire up: expense category rows
            // =====================================================
            document.querySelectorAll('.expense-category-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    var categoryName = this.getAttribute('data-category');
                    var category = expenseCategoryData.find(function (c) {
                        return c.category === categoryName;
                    });

                    if (!category) {
                        alert('Category data not found.');
                        return;
                    }

                    modalLabel.textContent = 'Expense Breakdown: ' + categoryName;

                    var items = category.items || [];
                    var itemCount = items.length;
                    var grandTotal = 0;
                    Object.values(category.total_by_month || {}).forEach(function (v) {
                        grandTotal += parseFloat(v || 0);
                    });

                    var summaryHtml = '<div class="mb-4 flex flex-wrap items-center gap-4 text-sm flex-shrink-0">' +
                        '<div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-md">' +
                            '<span class="text-blue-600 font-medium">Items:</span>' +
                            '<span class="font-semibold text-blue-900">' + itemCount + '</span>' +
                        '</div>' +
                        '<div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-md">' +
                            '<span class="text-green-600 font-medium">Grand Total:</span>' +
                            '<span class="font-semibold text-green-900">₱ ' + formatNumber(grandTotal) + '</span>' +
                        '</div>' +
                        '</div>';

                    modalContent.innerHTML =
                        '<div class="flex flex-col min-h-0 flex-1">' +
                            summaryHtml +
                            '<div class="flex-1 min-h-0">' + buildModalContent(category) + '</div>' +
                        '</div>';

                    openModal();
                });
            });

            // =====================================================
            // Wire up: Sales, Net + COGS breakdown rows
            // =====================================================
            document.querySelectorAll('.breakdown-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    var type = this.getAttribute('data-breakdown-type');
                    var breakdownByMonth = {};
                    var title = '';

                    if (type === 'sales') {
                        // Merge sales + service charge into one breakdown map
                        Object.keys(salesBreakdown).forEach(function (mKey) {
                            breakdownByMonth[mKey] = (breakdownByMonth[mKey] || []).concat(salesBreakdown[mKey]);
                        });
                        Object.keys(serviceChargeBreakdown).forEach(function (mKey) {
                            breakdownByMonth[mKey] = (breakdownByMonth[mKey] || []).concat(serviceChargeBreakdown[mKey]);
                        });
                        title = 'Sales, Net — Breakdown';
                    } else if (type === 'cogs') {
                        breakdownByMonth = cogsBreakdown || {};
                        title = 'Cost of Goods Sold — Breakdown';
                    } else {
                        return;
                    }

                    modalLabel.textContent = title;

                    var monthKeys = Object.keys(months);
                    var grandTotal = 0;
                    var itemCount = 0;
                    var seenItems = {};
                    monthKeys.forEach(function (mKey) {
                        (breakdownByMonth[mKey] || []).forEach(function (r) {
                            grandTotal += parseFloat(r.amount || 0);
                            if (!seenItems[r.item]) { seenItems[r.item] = true; itemCount++; }
                        });
                    });

                    var summaryHtml = '<div class="mb-4 flex flex-wrap items-center gap-4 text-sm flex-shrink-0">' +
                        '<div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-md">' +
                            '<span class="text-blue-600 font-medium">Items:</span>' +
                            '<span class="font-semibold text-blue-900">' + itemCount + '</span>' +
                        '</div>' +
                        '<div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-md">' +
                            '<span class="text-green-600 font-medium">Grand Total:</span>' +
                            '<span class="font-semibold text-green-900">₱ ' + formatNumber(grandTotal) + '</span>' +
                        '</div>' +
                        '</div>';

                    modalContent.innerHTML =
                        '<div class="flex flex-col min-h-0 flex-1">' +
                            summaryHtml +
                            '<div class="flex-1 min-h-0">' + buildGenericBreakdownHtml(breakdownByMonth, type === 'cogs') + '</div>' + //20260528001 JET ADD LINE 850 FROM '<div class="flex-1 min-h-0">' + buildGenericBreakdownHtml(breakdownByMonth) + '</div>' +
                        '</div>';

                    openModal();
                });
            });

            // =====================================================
            // Close handlers (shared)
            // =====================================================
            var closeBtn = document.getElementById('closeExpenseModal');
            var closeFooterBtn = document.getElementById('closeExpenseModalFooter');
            var backdrop = document.getElementById('expenseModalBackdrop');

            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (closeFooterBtn) closeFooterBtn.addEventListener('click', closeModal);
            if (backdrop) backdrop.addEventListener('click', closeModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>
</x-app-layout>