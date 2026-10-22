<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Report - {{ ucfirst($category) }} - {{ ucfirst($report) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f4f4f4;
            border-radius: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            table {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings->business_name ?? 'Business Name' }}</h1>
        <h2>{{ ucfirst($category) }} Report - {{ ucfirst($report) }}</h2>
        <p>Date Range: {{ date('M d, Y h:i A', strtotime($startDate)) }} to {{ date('M d, Y h:i A', strtotime($endDate)) }}</p>
        <p>Generated on: {{ date('M d, Y h:i A') }}</p>
    </div>

    <div class="summary">
        <p><strong>Total Records:</strong> {{ $resultsCount ?? count($results) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @php
                    $headers = [];
                    if(!empty($results) && count($results) > 0) {
                        $firstItem = $results[0];
                        if(is_object($firstItem)) {
                            $headers = array_keys(get_object_vars($firstItem));
                        } elseif(is_array($firstItem)) {
                            $headers = array_keys($firstItem);
                        }
                        
                        $unwanted = ['id', 'created_at', 'updated_at', 'is_deleted', 'deleted_at'];
                        $headers = array_diff($headers, $unwanted);
                    }
                @endphp
                
                @foreach($headers as $header)
                    @if(!in_array(strtolower($header), ['id', 'created_at', 'updated_at']))
                        <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($results as $item)
                <tr>
                    @foreach($headers as $header)
                        @if(!in_array(strtolower($header), ['id', 'created_at', 'updated_at']))
                            @php
                                $value = '';
                                if(is_object($item) && property_exists($item, $header)) {
                                    $value = $item->$header;
                                } elseif(is_array($item) && array_key_exists($header, $item)) {
                                    $value = $item[$header];
                                }
                                
                                if(is_numeric($value) && !in_array($header, ['number', 'id', 'menu_id', 'inv_id'])) {
                                    $value = '₱ ' . number_format($value, 2);
                                } elseif(strtotime($value) && str_contains($header, 'date')) {
                                    $value = date('M d, Y h:i A', strtotime($value));
                                }
                            @endphp
                            <td>{{ $value }}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Page 1 of 1</p>
        <p>Printed by: {{ Auth::user()->name ?? 'System' }}</p>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer;">
            Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #f44336; color: white; border: none; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
</body>
</html>