<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Movement Print All</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; padding: 15px 20px; color: #1a1a1a; background: #fff; font-size: 11px; line-height: 1.5; }
        .print-container { max-width: 1200px; margin: 0 auto; }
        .print-header { text-align: center; line-height: 1.3; margin-bottom: 14px; }
        .print-header h1 { font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px; }
        .print-header .sub-header { font-size: 10px; color: #555; margin-bottom: 6px; }
        .print-header .filter-info { font-size: 9px; color: #444; background: #f3f4f6; padding: 6px 12px; border: 1px solid #ccc; border-radius: 4px; display: inline-block; line-height: 1.4; }
        .report-table-wrapper { overflow-x: auto; border: 1px solid #999; border-radius: 0; margin-bottom: 10px; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid #999; }
        .report-table thead { display: table-header-group; }
        .report-table tbody { display: table-row-group; }
        .report-table thead th { background: #e5e7eb; padding: 6px 8px; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 9px; border: 1px solid #999; letter-spacing: 0.5px; }
        .report-table tbody td { padding: 5px 8px; border: 1px solid #ccc; font-size: 9.5px; word-break: break-word; overflow-wrap: anywhere; white-space: normal; }
        .report-table tbody tr:nth-child(even) { background-color: #f9fafb; }
        .report-table tbody tr { page-break-inside: avoid; }
        .report-footer { text-align: center; margin-top: 8px; font-size: 9px; color: #666; }
        .text-right { text-align: right; }
        .report-table-wrapper .rounded-full { -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 8px; padding: 2px 8px; font-weight: 600; border-radius: 9999px; display: inline-block; }
        .bg-green-100 { background-color: #dcfce7; color: #166534; }
        .bg-blue-100 { background-color: #dbeafe; color: #1e40af; }
        .bg-red-100 { background-color: #fee2e2; color: #991b1b; }
        .bg-purple-100 { background-color: #f3e8ff; color: #6b21a8; }
        .bg-orange-100 { background-color: #ffedd5; color: #9a3412; }
        .bg-gray-100 { background-color: #f3f4f6; color: #1f2937; }
        .text-green-600 { color: #15803d; }
        .text-red-600 { color: #b91c1c; }
        .text-gray-600 { color: #374151; }
        @page { margin: 22mm 10mm 15mm 10mm; size: landscape; }
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
        .report-table td div { margin-top: 2px; }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <h1>Inventory Movement Report</h1>
            <div class="sub-header">Filtered results for all pages</div>
            @php
                $filters = [];
                if(request('item_code')) { $filters[] = 'Item: ' . request('item_code'); }
                if(request('search')) { $filters[] = 'Search: ' . request('search'); }
                if(request('movement_type')) { $filters[] = 'Type: ' . request('movement_type'); }
                if(request('date_from')) { $filters[] = 'From: ' . request('date_from'); }
                if(request('date_to')) { $filters[] = 'To: ' . request('date_to'); }
            @endphp
            @if(count($filters) > 0)
                <div class="filter-info">{{ implode(' | ', $filters) }}</div>
            @endif
        </div>

        <div class="report-table-wrapper">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Item Code</th>
                        <th>Item Description</th>
                        <th>Detail</th>
                        <th>Before</th>
                        <th>Qty In</th>
                        <th>Qty Out</th>
                        <th>Qty On Hand</th>
                        <th>Sold Price</th>
                        <th>Standard Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->type }}</td>
                            <td>{{ $transaction->reference }}</td>
                            <td>{{ $transaction->created_at ? 
                                Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d H:i') : 'N/A' }}</td>
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
                            <td>{{ $transaction->previous_qty }}</td>
                            <td>{{ $transaction->qty_in }}</td>
                            <td>{{ $transaction->qty_out }}</td>
                            <td>{{ $transaction->remaining }}</td>
                            <td>{{ $transaction->sold_price }}</td>
                            <td>{{ $transaction->cost }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">No records found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-footer">
            Generated on {{ now()->format('Y-m-d H:i:s') }} | Total rows: {{ $transactions->count() }}
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
