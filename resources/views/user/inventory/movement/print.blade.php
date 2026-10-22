<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Sales Summary Report</title>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            padding: 10px 15px;
            color: #333;
            background: #fff;
            font-size: 11px;
            line-height: 1.3;
        }

        .print-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }

        /* Screen-only single header */
        .report-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .report-header h1 {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
        }

        .report-header .sub-header {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }

        .report-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            font-size: 10px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            padding: 6px 12px;
            border-radius: 3px;
            background: #f9fafb;
        }

        .report-info p {
            margin: 1px 0;
        }

        .report-info strong {
            font-weight: 600;
        }

        .report-info .page-counter {
            text-align: right;
            font-weight: 600;
            color: #4b5563;
        }

        /* Table Styles */
        .report-table-wrapper {
            overflow-x: auto;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-bottom: 8px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin: 0;
        }

        .report-table thead th {
            background-color: #e5e7eb;
            padding: 4px 6px;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
            border: 1px solid #ddd;
        }

        .report-table tbody td {
            padding: 3px 6px;
            border: 1px solid #ddd;
            font-size: 9px;
        }

        .report-table tfoot td {
            background-color: #e5e7eb;
            padding: 4px 6px;
            border: 1px solid #ddd;
            font-weight: 700;
            font-size: 9px;
        }

        .report-table .text-right { text-align: right; }
        .report-table .text-left { text-align: left; }
        .report-table .text-center { text-align: center; }
        .report-table .profit-positive { color: #000000; }
        .report-table .profit-negative { color: #000000; }

        .category-row td {
            background-color: #e5e7eb !important;
            font-weight: 700;
            font-size: 10px;
            padding: 4px 6px;
        }

        .category-row .category-name { font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .category-row .category-id { color: #4b5563; font-weight: 600; }

        .subtotal-row td {
            background-color: #f3f4f6 !important;
            font-weight: 700;
            border-top: 2px solid #6b7280;
            padding: 3px 6px;
        }

        .subtotal-row .subtotal-label {
            font-weight: 700;
            font-size: 9px;
            color: #4b5563;
        }

        .grand-total-row td {
            background-color: #d1d5db !important;
            font-weight: 700;
            font-size: 10px;
            border-top: 3px solid #333;
            padding: 4px 6px;
        }

        .grand-total-row .grand-total-label {
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
        }

        .report-footer {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-top: 8px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .item-row-even { background-color: #fafafa; }
        .item-row-odd { background-color: #ffffff; }

        /* ===== PER-PAGE PRINT HEADER ===== */
        .print-page-header {
            display: none;
        }

        .print-page {
            /* On screen these just stack normally with spacing. */
        }

        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            body {
                padding: 5px 10px !important;
                font-size: 10px !important;
            }

            .print-container {
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Hide the screen-only single header/info */
            .report-header.screen-only,
            .report-info.screen-only {
                display: none !important;
            }

            /* Show each page's own header, in normal flow above its table */
            .print-page-header {
                display: block !important;
            }

            .print-page-header .report-header {
                padding-bottom: 4px !important;
                margin-bottom: 4px !important;
                border-bottom: 2px solid #333 !important;
            }

            .print-page-header .report-header h1 {
                font-size: 14px !important;
            }

            .print-page-header .report-info {
                padding: 4px 10px !important;
                margin-bottom: 6px !important;
                font-size: 9px !important;
                border: 1px solid #ccc !important;
                background: #f9fafb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* One page of the report = one printed sheet.
               No page break after the last one. */
            .print-page {
                page-break-after: always;
            }

            .print-page:last-child {
                page-break-after: auto;
            }

            .report-table {
                font-size: 8px !important;
                margin-top: 0 !important;
            }

            .report-table thead th {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 2px 4px !important;
                font-size: 7px !important;
            }

            .report-table tbody td {
                padding: 2px 4px !important;
                font-size: 8px !important;
            }

            .report-table tfoot td {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 2px 4px !important;
                font-size: 8px !important;
            }

            .category-row td {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 2px 4px !important;
                font-size: 9px !important;
            }

            .subtotal-row td {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 2px 4px !important;
            }

            .grand-total-row td {
                background-color: #d1d5db !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 2px 4px !important;
            }

            .item-row-even {
                background-color: #fafafa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .item-row-odd {
                background-color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print { display: none !important; }

            .report-table-wrapper {
                border: 1px solid #ddd !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }

            .report-footer {
                margin-top: 4px !important;
                padding-top: 3px !important;
                font-size: 8px !important;
            }

            .report-table tbody tr { page-break-inside: avoid; }
            .category-row { page-break-after: avoid; }
        }

        @page {
            margin: 10mm 10mm;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1f2937;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 13px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 10000;
        }

        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { background: #16a34a; }
        .toast.info { background: #2563eb; }
        .toast.warning { background: #d97706; }
    </style>
</head>
<body>
    <div class="toast" id="toast"></div>

    <div class="no-print" style="margin-bottom: 12px; display: flex; gap: 10px;">
        <a href="{{ route('inventory.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700">
            Export CSV
        </a>
        <a href="{{ route('inventory.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700">
            Export Excel
        </a>
    </div>

    @php
        $formatNum = function ($num) {
            return number_format((float) $num, 3);
        };
        $formatCurrency = function ($num) {
            return number_format((float) $num, 2);
        };

        // --- 1. Flatten every row we need to print into one flat list,
        //        tagged with its type, in the exact order it must appear. ---
        $flatRows = [];

        foreach ($groupedTransactions as $categoryName => $categoryData) {
            $flatRows[] = [
                'type'  => 'category',
                'name'  => $categoryName,
                'id'    => $categoryData['category_id'],
                'count' => count($categoryData['items']),
            ];

            $itemRowCount = 0;
            foreach ($categoryData['items'] as $itemGroup) {
                $itemRowCount++;
                $flatRows[] = [
                    'type' => 'item',
                    'even' => $itemRowCount % 2 === 0,
                    'item_name' => $itemGroup['item_name'],
                    'brand_name' => $itemGroup['brand_name'],
                    'brand_code' => $itemGroup['brand_code'],
                    'total_qty' => $itemGroup['total_qty'],
                    'total_sales' => $itemGroup['total_sales'],
                    'total_cost' => $itemGroup['total_cost'],
                    'total_profit' => $itemGroup['total_profit'],
                    'transaction_count' => $itemGroup['transaction_count'],
                ];
            }

            $flatRows[] = [
                'type' => 'subtotal',
                'qty'  => $categoryData['total_qty'],
                'sales'  => $categoryData['total_sales'],
                'cost'   => $categoryData['total_cost'],
                'profit' => $categoryData['total_profit'],
            ];
        }

        if (!empty($groupedTransactions)) {
            $flatRows[] = ['type' => 'grandtotal'];
        }

        if (empty($flatRows)) {
            $flatRows[] = ['type' => 'empty'];
        }

        // --- 2. Chunk the flat list into pages ---
        $ROWS_PER_PAGE = 60;

        $pages = [];
        $currentPage = [];
        $unitsUsed = 0;

        foreach ($flatRows as $row) {
            $weight = in_array($row['type'], ['category', 'subtotal', 'grandtotal']) ? 1.6 : 1;

            if ($unitsUsed + $weight > $ROWS_PER_PAGE && !empty($currentPage)) {
                $pages[] = $currentPage;
                $currentPage = [];
                $unitsUsed = 0;
            }

            $currentPage[] = $row;
            $unitsUsed += $weight;
        }

        if (!empty($currentPage)) {
            $pages[] = $currentPage;
        }

        $totalPages = count($pages);
    @endphp

    <div class="print-container" id="printContainer">

        {{-- Screen-only header, shown once at the top of the page --}}
        <div class="report-header screen-only">
            <h1>INVENTORY SALES SUMMARY REPORT</h1>
            <div class="sub-header">Generated on: {{ now()->format('Y-m-d H:i:s') }}</div>
        </div>

        <div class="report-info screen-only">
            <div>
                <p><strong>Print Out Date:</strong> {{ now()->format('Y-m-d H:i') }}</p>
                <p><strong>Fiscal Year:</strong> 2026-01-01 - 2026-12-31 (Active)</p>
                <p><strong>Period:</strong> {{ $dateFrom ?? 'Start' }} - {{ $dateTo ?? 'End' }}</p>
                <p><strong>Printed By:</strong> {{ $printedBy ?? 'N/A' }}</p>
            </div>
            <div>
                <p>{{ $storeName ?? 'N/A' }}</p>
                <p><strong>Category:</strong> {{ $category ?? 'All' }}</p>
                <p><strong>Location:</strong>{{ $storeAddress ?? 'N/A' }}</p>
                <p><strong>Comments:</strong></p>
            </div>
        </div>

        @foreach ($pages as $pageIndex => $pageRows)
            <div class="print-page">

                {{-- This header only renders visibly when printing --}}
                <div class="print-page-header">
                    <div class="report-header">
                        <h1>INVENTORY SALES SUMMARY REPORT</h1>
                        <div class="sub-header">Generated on: {{ now()->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div class="report-info">
                        <div>
                            <p><strong>Print Out Date:</strong> {{ now()->format('Y-m-d H:i') }}</p>
                            <p><strong>Fiscal Year:</strong> 2026-01-01 - 2026-12-31 (Active)</p>
                            <p><strong>Period:</strong> {{ $dateFrom ?? 'Start' }} - {{ $dateTo ?? 'End' }}</p>
                            <p><strong>Printed By:</strong> {{ $printedBy ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p>{{ $storeName ?? 'N/A' }}</p>
                            <p><strong>Category:</strong> {{ $category ?? 'All' }}</p>
                            <p><strong>Location:</strong>{{ $storeAddress ?? 'N/A' }}</p>
                            <p><strong>Comments:</strong></p>
                            <p class="page-counter">Page: {{ $pageIndex + 1 }} of {{ $totalPages }}</p>
                        </div>
                    </div>
                </div>

                <div class="report-table-wrapper">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Category</th>
                                {{-- <th style="width: 12%;">Brand Code</th>
                                <th style="width: 18%;">Brand</th> --}}
                                <th style="width: 48%;">Description</th>
                                <th style="width: 12%; text-align: right;">Qty</th>
                                <th style="width: 12%; text-align: right;">Sales</th>
                                {{-- <th style="width: 12%; text-align: right;">Cost</th>
                                <th style="width: 12%; text-align: right;">Profit</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pageRows as $row)
                                @if ($row['type'] === 'category')
                                    <tr class="category-row">
                                        <td><span class="category-id">{{ $row['count'] }}</span></td>
                                        <td colspan="3"><span class="category-name">{{ strtoupper($row['name']) }}</span></td>
                                    </tr>

                                @elseif ($row['type'] === 'item')
                                    @php
                                        $itemDesc = $row['item_name'] ?? 'N/A';
                                        $brandName = $row['brand_name'] ?? 'N/A';
                                        $brandCode = $row['brand_code'] ?? 'N/A';
                                        $qtyOut = (float) ($row['total_qty'] ?? 0);
                                        $sales = (float) ($row['total_sales'] ?? 0);
                                        $cost = (float) ($row['total_cost'] ?? 0);
                                        $profit = (float) ($row['total_profit'] ?? 0);
                                        $profitClass = $profit >= 0 ? 'profit-positive' : 'profit-negative';
                                        $itemRowClass = $row['even'] ? 'item-row-even' : 'item-row-odd';
                                    @endphp
                                    <tr class="{{ $itemRowClass }}">
                                        <td class="text-right">{{ $brandCode }}</td>
                                        <td>{{ $itemDesc }} - {{ $brandName }}</td>
                                        <td class="text-right">{{ $formatNum($qtyOut) }}</td>
                                        <td class="text-right">{{ $formatCurrency($sales) }}</td>
                                        {{-- <td class="text-right">{{ $formatCurrency($cost) }}</td>
                                        <td class="text-right {{ $profitClass }}">{{ $formatCurrency($profit) }}</td> --}}
                                    </tr>

                                @elseif ($row['type'] === 'subtotal')
                                    <tr class="subtotal-row">
                                        <td colspan="2" class="subtotal-label text-right">TOTAL</td>
                                        <td class="text-right">{{ $formatNum($row['qty']) }}</td>
                                        <td class="text-right">{{ $formatCurrency($row['sales']) }}</td>
                                        {{-- <td class="text-right">{{ $formatCurrency($row['cost']) }}</td>
                                        <td class="text-right">{{ $formatCurrency($row['profit']) }}</td> --}}
                                    </tr>

                                @elseif ($row['type'] === 'grandtotal')
                                    <tr class="grand-total-row">
                                        <td class="text-left">{{ $grandTotalCount ?? 0 }}</td>
                                        <td colspan="1" class="grand-total-label text-right">GRAND TOTAL</td>
                                        <td class="text-right">{{ $formatNum($grandTotalQty ?? 0) }}</td>
                                        <td class="text-right">{{ $formatCurrency($grandTotalSales ?? 0) }}</td>
                                        {{-- <td class="text-right">{{ $formatCurrency($grandTotalCost ?? 0) }}</td>
                                        <td class="text-right">{{ $formatCurrency($grandTotalProfit ?? 0) }}</td> --}}
                                    </tr>

                                @else
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 20px; color: #999;">
                                            No inventory movement records found.
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($pageIndex === $totalPages - 1)
                    <div class="report-footer">
                        <p>*** End of Report ***</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <script>
        let printDialogShown = false;
        let isPrinting = false;

        function triggerPrint() {
            if (isPrinting) return;
            isPrinting = true;
            setTimeout(() => {
                window.print();
                isPrinting = false;
                printDialogShown = true;
            }, 300);
        }

        window.addEventListener('afterprint', function () {
            isPrinting = false;
        });

        window.onload = function () {
            if (!printDialogShown) {
                setTimeout(triggerPrint, 500);
            }
        };

        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                triggerPrint();
            }
            if (e.key === 'Escape') {
                if (confirm('Are you sure you want to close this window?')) {
                    window.close();
                }
            }
        });
    </script>
</body>
</html>