<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Balance Report - {{ date('Y-m-d') }}</title>
    <style>
        /* Same exact styles as the single client print view above */
        /* Copy all the styles from the single client print view */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            margin: 0.8cm 0.5cm 1.2cm 0.5cm !important;
            size: auto !important;
        }
        
        html, body {
            background: white !important;
            height: auto !important;
            overflow: visible !important;
            font-family: 'Arial', 'Helvetica', sans-serif !important;
            font-size: 8pt !important;
            line-height: 1.3 !important;
            color: #000 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        body * {
            visibility: hidden !important;
        }
        
        #printContainer, 
        #printContainer * {
            visibility: visible !important;
        }
        
        #printContainer {
            position: relative !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
            overflow: visible !important;
        }

        .print-page-break {
            page-break-after: always !important;
            page-break-inside: avoid !important;
        }

        .client-card {
            border: none !important;
            border-radius: 0 !important;
            margin: 0 0 20px 0 !important;
            padding: 0 !important;
            background: white !important;
            width: 100% !important;
            page-break-after: auto !important;
            page-break-inside: auto !important;
            break-inside: auto !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        .client-card .overflow-x-auto {
            overflow: visible !important;
            width: 100% !important;
        }
        
        table {
            border-collapse: collapse !important;
            width: 100% !important;
            font-size: 7pt !important;
            border: none !important;
            margin: 0 !important;
            table-layout: auto !important;
        }
        
        th, td {
            border: none !important;
            padding: 2px 4px !important;
            text-align: left !important;
            vertical-align: middle !important;
            background: white !important;
            font-size: 7pt !important;
            line-height: 1.2 !important;
        }
        
        thead th {
            background-color: #f0f0f0 !important;
            color: #000 !important;
            font-weight: 700 !important;
            padding: 2px 4px !important;
            text-transform: uppercase !important;
            font-size: 6pt !important;
            letter-spacing: 0.3px !important;
            border-bottom: 2px solid #000 !important;
        }
        
        tfoot td {
            font-weight: 700 !important;
            padding: 2px 4px !important;
            border-top: 2px solid #000 !important;
        }
        
        .bg-yellow-50 td {
            background-color: #fff9e6 !important;
            font-weight: 600 !important;
            padding: 2px 4px !important;
            border-bottom: 1px solid #ccc !important;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9f9f9 !important;
        }
        
        tbody tr:nth-child(even) td {
            background-color: #f9f9f9 !important;
        }
        
        .bg-gray-100 td {
            background-color: #f0f0f0 !important;
            font-weight: 700 !important;
            padding: 2px 4px !important;
        }
        
        tfoot tr:last-child td {
            background-color: #e8f5e9 !important;
            font-weight: 700 !important;
            border-top: 2px solid #000 !important;
            padding: 2px 4px !important;
        }
        
        .text-red-600, .text-green-600, .text-blue-600, .text-gray-600, .text-gray-700, .text-gray-800 {
            color: #000 !important;
        }
        
        td.text-right, th.text-right {
            text-align: right !important;
        }
        
        td.text-center, th.text-center {
            text-align: center !important;
        }
        
        .text-lg {
            font-size: 9pt !important;
        }
        
        .text-sm {
            font-size: 7pt !important;
        }
        
        .text-xs {
            font-size: 6.5pt !important;
        }
        
        .truncate {
            overflow: visible !important;
            text-overflow: clip !important;
            white-space: normal !important;
            max-width: none !important;
        }
        
        .bg-yellow-50 .truncate {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }
        
        .client-card .mb-8 {
            margin-bottom: 0 !important;
        }
        
        .client-card .border {
            border: none !important;
        }
        
        .client-card .rounded-lg {
            border-radius: 0 !important;
        }
        
        .client-card .overflow-hidden {
            overflow: visible !important;
        }
        
        .overflow-x-auto {
            overflow: visible !important;
            width: 100% !important;
        }
        
        #printContainer > *:first-child {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        
        .report-header-row {
            display: table-row !important;
            page-break-inside: avoid !important;
        }

        .report-header-row th {
            padding: 4px 6px !important;
            border-bottom: 2px solid #333 !important;
            background: white !important;
            color: #000 !important;
            text-align: left !important;
            text-transform: none !important;
            font-size: 6pt !important;
            font-weight: normal !important;
        }

        .report-header-content {
            display: flex !important;
            justify-content: space-between !important;
            gap: 12px !important;
        }

        .report-header-right {
            text-align: right !important;
        }

        th:first-child, td:first-child {
            padding-left: 2px !important;
        }
        
        th:last-child, td:last-child {
            padding-right: 2px !important;
        }
        
        .client-card table {
            page-break-inside: auto !important;
        }
        
        .client-card tbody tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }
        
        .client-card thead {
            display: table-header-group !important;
        }
        
        .client-card tfoot {
            display: table-row-group !important;
        }

        .pl-6 {
            padding-left: 12px !important;
        }

        .font-bold {
            font-weight: 700 !important;
        }
        .font-semibold {
            font-weight: 600 !important;
        }
        .font-medium {
            font-weight: 500 !important;
        }
        .font-mono {
            font-family: monospace !important;
        }

        .italic {
            font-style: italic !important;
        }

        .page-number {
            font-size: 6pt !important;
            font-weight: normal !important;
        }
    </style>
</head>
<body>
    <div id="printContainer">
        @php
            $groupedResults = $results->groupBy(function($item) {
                return $item->table . '|' . $item->table_name;
            });
            
            $grandTotalCharges = 0;
            $grandTotalCredits = 0;
            $grandTotalAllocated = 0;
            $grandTotalOutstanding = 0;
            $clientTotals = [];
            $clientCounter = 0;
        @endphp

        @forelse ($groupedResults as $groupKey => $clientTransactions)
            @php
                $clientCounter++;
                list($tableId, $tableName) = explode('|', $groupKey);
                $firstTrans = $clientTransactions->first();
                $payorName = $firstTrans->payor_name ?? '';
                
                $openBalanceItems = $clientTransactions->filter(function($item) {
                    return $item->trans_type === 'Open Balance';
                });
                $salesItems = $clientTransactions->filter(function($item) {
                    return $item->trans_type === 'Sales Invoice';
                });
                $paymentItems = $clientTransactions->filter(function($item) {
                    return $item->trans_type === 'Customer Payment';
                });
                
                $salesByNumber = [];
                foreach ($salesItems as $item) {
                    $key = $item->number ?? $item->id;
                    $salesByNumber[$key] = $item;
                }
                
                $paymentsByNumber = [];
                foreach ($paymentItems as $item) {
                    $key = $item->number ?? $item->id;
                    if (!isset($paymentsByNumber[$key])) {
                        $paymentsByNumber[$key] = [];
                    }
                    $paymentsByNumber[$key][] = $item;
                }
                
                $transactionGroups = [];
                
                foreach ($salesByNumber as $number => $salesItem) {
                    $groupKey = $number . '|' . $tableId;
                    $transactionGroups[$groupKey] = [
                        'sales' => $salesItem,
                        'payments' => isset($paymentsByNumber[$number]) ? $paymentsByNumber[$number] : [],
                        'number' => $number,
                        'is_sales' => true,
                        'table_id' => $tableId
                    ];
                    unset($paymentsByNumber[$number]);
                }
                
                foreach ($paymentsByNumber as $number => $paymentList) {
                    $groupKey = 'payment_' . $number . '|' . $tableId;
                    $transactionGroups[$groupKey] = [
                        'sales' => null,
                        'payments' => $paymentList,
                        'number' => $number,
                        'is_sales' => false,
                        'table_id' => $tableId
                    ];
                }
                
                $sortedGroups = [];
                foreach ($transactionGroups as $key => $group) {
                    $sortDate = $group['sales'] 
                        ? $group['sales']->created_at 
                        : ($group['payments'][0]->created_at ?? now());
                    $sortedGroups[$sortDate . '_' . $key] = $group;
                }
                ksort($sortedGroups);
                $transactionGroups = array_values($sortedGroups);
                
                $openCharges = $openBalanceItems->sum('charges');
                $openCredits = $openBalanceItems->sum('credits');
                $openAllocated = $openBalanceItems->sum('allocated');
                $openOutstanding = $openCredits - $openAllocated;
                
                $clientCharges = 0;
                $clientCredits = 0;
                $clientAllocated = 0;
                $clientOutstanding = 0;

                $clientCharges += $openCharges;
                $clientCredits += $openCredits;
                $clientAllocated += $openAllocated;
                $clientOutstanding += $openOutstanding;
                
                foreach ($transactionGroups as $group) {
                    $salesItem = $group['sales'];
                    $payments = $group['payments'];
                    
                    if ($salesItem) {
                        $salesCharges = $salesItem->charges ?? 0;
                        $salesCredits = 0;
                        $salesAllocated = $salesItem->allocated ?? 0;
                        $salesOutstanding = $openOutstanding - $salesAllocated;
                        
                        $clientCharges += $salesCharges;
                        $clientCredits += $salesCredits;
                        $clientAllocated += $salesAllocated;
                        $clientOutstanding += $salesOutstanding;
                    }
                    
                    foreach ($payments as $payment) {
                        $paymentCharges = 0;
                        $paymentCredits = $payment->credits ?? 0;
                        $paymentAllocated = $payment->allocated ?? 0;
                        $paymentOutstanding = $paymentCredits - $paymentAllocated;
                        
                        $clientCharges += $paymentCharges;
                        $clientCredits += $paymentCredits;
                        $clientAllocated += $paymentAllocated;
                        $clientOutstanding += $paymentOutstanding;
                    }
                }
                
                $totalClientCharges = $clientCharges;
                $totalClientCredits = $clientCredits;
                $totalClientAllocated = $clientAllocated;
                $totalClientOutstanding = $clientOutstanding;
                
                $clientTotals[] = [
                    'charges' => $totalClientCharges,
                    'credits' => $totalClientCredits,
                    'allocated' => $totalClientAllocated,
                    'outstanding' => $totalClientOutstanding
                ];
                
                $grandTotalCharges += $totalClientCharges;
                $grandTotalCredits += $totalClientCredits;
                $grandTotalAllocated += $totalClientAllocated;
                $grandTotalOutstanding += $totalClientOutstanding;
                
                $printRows = collect();

                $printRows->push([
                    'type' => 'open_balance',
                    'charges' => $openCharges,
                    'credits' => $openCredits,
                    'allocated' => $openAllocated,
                    'outstanding' => max($openOutstanding, 0),
                ]);

                foreach ($transactionGroups as $group) {
                    $salesItem = $group['sales'];
                    $payments = $group['payments'];

                    if ($salesItem) {
                        $charges = $salesItem->charges ?? 0;
                        $salesAllocated = $salesItem->allocated ?? 0;
                        $outstanding = $charges - $salesAllocated;

                        $printRows->push([
                            'type' => 'sales',
                            'number' => $salesItem->number ?? 'N/A',
                            'date' => $salesItem->created_at ? date('Y-m-d', strtotime($salesItem->created_at)) : '0000-00-00',
                            'due_date' => $salesItem->updated_at ? date('Y-m-d', strtotime($salesItem->updated_at . ' +7 days')) : '0000-00-00',
                            'charges' => $charges,
                            'credits' => 0,
                            'allocated' => $salesAllocated,
                            'outstanding' => $outstanding,
                        ]);
                    }

                    foreach ($payments as $payment) {
                        $paymentCredits = $payment->credits ?? 0;
                        $paymentAllocated = $payment->allocated ?? 0;
                        $paymentOutstanding = $salesItem
                            ? ($paymentAllocated - $paymentCredits)
                            : ($paymentCredits - $paymentAllocated);

                        $printRows->push([
                            'type' => 'payment',
                            'number' => $payment->number ?? 'N/A',
                            'date' => $payment->created_at ? date('Y-m-d', strtotime($payment->created_at)) : '0000-00-00',
                            'due_date' => null,
                            'charges' => 0,
                            'credits' => $paymentCredits,
                            'allocated' => $paymentAllocated,
                            'outstanding' => $paymentOutstanding,
                        ]);
                    }
                }

                if ($printRows->count() === 1) {
                    $printRows->push(['type' => 'empty']);
                }
                
                $totalOutstanding = $printRows
                    ->whereIn('type', ['sales', 'payment'])
                    ->sum('outstanding');
                
                $rowsPerPage = 57;
                $printChunks = $printRows->chunk($rowsPerPage)->values();
                $totalPrintPages = $printChunks->count();
            @endphp

            <div class="client-card print-client-container">
                <div class="overflow-x-auto">
                    @foreach ($printChunks as $pageIndex => $chunkRows)
                        <table class="w-full text-sm text-left text-gray-700 border-collapse {{ !$loop->last ? 'print-page-break' : '' }}">
                            <thead class="text-xs text-gray-700 uppercase bg-gradient-to-r from-blue-50 to-blue-100">
                                <tr class="report-header-row">
                                    <th colspan="8">
                                        <div class="report-header-content">
                                            <div>
                                                <strong class="text-2xl font-bold">Customer Balances</strong><br>
                                                Print Out Date: {{ date('Y-m-d H:i') }}<br>
                                                Fiscal Year: {{ date('Y') }}-01-01 - {{ date('Y') }}-12-31 (Active)<br>
                                                Period: {{ date('Y-m-d', strtotime($startDate)) }} - {{ date('Y-m-d', strtotime($endDate)) }}<br>
                                                Customer: {{ $tableName }}<br>
                                                Suppress Zeros: No
                                            </div>
                                            <div class="report-header-right">
                                                <strong>{{ $settings->name ?? 'KUMPADRES / LIMEATLESS TRADING CORP' }}</strong><br>
                                                {{ $settings->email ?? 'tindahan9181.bitpos.pro' }}<br>
                                                {{ auth()->user()->lastname.', '.auth()->user()->firstname ?? 'N/A' }}<br>
                                                <br>
                                                <span class="page-number">Page {{ $pageIndex + 1 }} of {{ $totalPrintPages }}</span>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-2 text-left" style="min-width: 120px;">TRANS TYPE</th>
                                    <th class="px-4 py-2 text-center">#</th>
                                    <th class="px-4 py-2 text-center">DATE</th>
                                    <th class="px-4 py-2 text-center">DUE DATE</th>
                                    <th class="px-4 py-2 text-right">CHARGES</th>
                                    <th class="px-4 py-2 text-right">CREDITS</th>
                                    <th class="px-4 py-2 text-right">ALLOCATED</th>
                                    <th class="px-4 py-2 text-right">OUTSTANDING</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($chunkRows as $row)
                                    @if ($row['type'] === 'open_balance')
                                        <tr class="bg-yellow-50 font-semibold">
                                            <td colspan="3" class="px-4 py-2 text-left">
                                                <span class="text-lg font-bold text-gray-800 truncate block max-w-xs" title="{{ $tableName ?: 'Unknown Table' }}">
                                                    {{ $tableName ?: 'Unknown Table' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-left italic">Open Balance</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right
                                                @if($row['outstanding'] > 0) text-red-600
                                                @elseif($row['outstanding'] < 0) text-green-600
                                                @else text-gray-600 @endif">
                                                ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                            </td>
                                        </tr>

                                    @elseif ($row['type'] === 'sales')
                                        <tr class="border-b hover:bg-gray-50 even:bg-gray-50">
                                            <td class="px-4 py-2 text-left font-medium text-blue-700">Sales Invoice</td>
                                            <td class="px-4 py-2 text-center font-mono">{{ $row['number'] }}</td>
                                            <td class="px-4 py-2 text-center">{{ $row['date'] }}</td>
                                            <td class="px-4 py-2 text-center">{{ $row['due_date'] }}</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right font-bold
                                                @if($row['outstanding'] > 0) text-red-600
                                                @elseif($row['outstanding'] < 0) text-green-600
                                                @else text-gray-600 @endif">
                                                ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                            </td>
                                        </tr>

                                    @elseif ($row['type'] === 'payment')
                                        <tr class="border-b hover:bg-gray-50 even:bg-gray-50">
                                            <td class="px-4 py-2 text-left text-green-600 pl-6">Customer Payment</td>
                                            <td class="px-4 py-2 text-center font-mono">{{ $row['number'] }}</td>
                                            <td class="px-4 py-2 text-center">{{ $row['date'] }}</td>
                                            <td class="px-4 py-2 text-center">-</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['charges'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['credits'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right">₱ {{ number_format($row['allocated'], 2, '.', ',') }}</td>
                                            <td class="px-4 py-2 text-right font-bold
                                                @if($row['outstanding'] > 0) text-red-600
                                                @elseif($row['outstanding'] < 0) text-green-600
                                                @else text-gray-600 @endif">
                                                ₱ {{ number_format($row['outstanding'], 2, '.', ',') }}
                                            </td>
                                        </tr>

                                    @elseif ($row['type'] === 'empty')
                                        <tr class="border-b hover:bg-gray-50 even:bg-gray-50 text-gray-400">
                                            <td class="px-4 py-2 text-left" colspan="8">No transactions in this period</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>

                            @if ($loop->last)
                                <tfoot class="font-bold">
                                    <tr class="bg-gray-100 border-t-2 border-gray-300">
                                        <td colspan="4" class="px-4 py-2 text-right text-gray-700">TOTAL</td>
                                        <td class="px-4 py-2 text-right">₱ {{ number_format($clientCharges, 2, '.', ',') }}</td>
                                        <td class="px-4 py-2 text-right">₱ {{ number_format($clientCredits, 2, '.', ',') }}</td>
                                        <td class="px-4 py-2 text-right">₱ {{ number_format($clientAllocated, 2, '.', ',') }}</td>
                                        <td class="px-4 py-2 text-right text-gray-600">
                                            ₱ {{ number_format($openOutstanding + $totalOutstanding, 2, '.', ',') }}
                                        </td>
                                    </tr>

                                    <tr class="border-t border-gray-300 bg-blue-50">
                                        <td class="px-4 py-2 text-left"></td>
                                        <td colspan="3" class="px-4 py-2 text-right text-gray-700">GRAND TOTAL:</td>
                                        <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientCharges, 2, '.', ',') }}</td>
                                        <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientCredits, 2, '.', ',') }}</td>
                                        <td class="px-4 py-2 text-right font-bold">₱ {{ number_format($totalClientAllocated, 2, '.', ',') }}</td>
                                        <td class="px-4 py-2 text-right text-gray-600">
                                            ₱ {{ number_format($openOutstanding + $totalOutstanding, 2, '.', ',') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <h3 class="text-xl font-medium text-gray-600">No transactions found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your search filters or date range.</p>
            </div>
        @endforelse
    </div>

    <script>
        /**
         * Auto-print when page loads
         * Tab stays open after print (user closes manually)
         */
        window.onload = function() {
            // Trigger print dialog
            window.print();
        };

        // Optional: Add a close button after print
        window.onafterprint = function() {
            var closeButton = document.createElement('div');
            closeButton.style.cssText = 'position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 9999;';
            closeButton.innerHTML = '<button onclick="window.close()" style="padding: 12px 40px; background: #dc2626; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">Close Tab</button>';
            document.body.appendChild(closeButton);
        };
    </script>
</body>
</html>