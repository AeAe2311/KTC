<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reciept</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/solid.css">
    <link rel="shortcut icon" href="{{ url('logo.ico') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{asset('assets/js/jquery.js')}}"></script>
    
    <style>
      /* Default styles */
      header, footer {
        display: block;
      }
  
      /* Styles for printing */
      @media print {
        header, footer {
          display: none;
        }
      }
    </style>
</head>
<body>
    <div >
    <!-- class="w-screen" px-10-->
        <div class="">
            <div class="">
                <img src="{{ asset('storage/'.$settings->logo) }}" class="h-10" alt="">
            </div>
            <div class="">
                <h1 class="text-2xl">Sales and Expenses Summary Report</h1>
                <h1 class="flex">{{ $startDate.' - '.$endDate }}</h1>
            </div>
            <div class="">
                <table class="mt-4">
                    <thead>
                        <tr class="border-b">
                            <th class="text-7xl  ">
                                Date1
                            </th>
                            <th class="text-7xl ">
                                Total Sales
                            </th>
                            <th class="text-7xl ">
                                Total Expenses
                            </th>
                            <th class="text-7xl ">
                                Total Incentives
                            </th>
                            <th class="text-7xl ">
                                Total Profit
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $feCDate = strtotime($startDate);
                            $feEDate = strtotime($endDate);
                            $sctotal = $stotalv = $etotalv = $sumtotal = 0;
                        @endphp
                        @while ($feCDate < $feEDate)
                            @php
                                $sTotal = 0;
                                $eTotal = 0;
                                $pTotal = 0;
                                $scTotal = 0;
                                $x = 0;
                                $fCurDate = date("Y-m-d", $feCDate);
                            @endphp
                            @foreach ($results as $result)
                                @php
                                    $fResDate = date("Y-m-d", strtotime($result['date']));
                                @endphp
                                @if ($fResDate == $fCurDate)
                                    <tr class="border-b">
                                    <th class="px-6 py-1 text-center font-medium text-gray-900 whitespace-nowrap">
                                        {{ date('M d, Y', $feCDate) }}
                                    </th>
                                    <td class="px-6 py-1 text-center whitespace-nowrap">
                                        ₱ {{ number_format($result['stotal'], 2, '.', ',') }}
                                    </td>
                                    <td class="px-6 py-1 text-center whitespace-nowrap">
                                        ₱ {{ number_format($result['etotal'], 2, '.', ',') }}
                                    </td>
                                    <td class="px-6 py-1 text-center whitespace-nowrap">
                                        ₱ {{ number_format($result['sctotal']/2, 2, '.', ',') }}
                                    </td>
                                    @php    
                                    $incentives = $result['sctotal']/2;
                                    $totalexpenses = $result['etotal'] + $incentives;
                                    $totalresult = $result['stotal'] - $totalexpenses;
                                    $sctotal += $incentives ;
                                    $stotalv += $result['stotal'];
                                    $etotalv += $result['etotal'];
                                    $sumtotal += $totalresult;
                                    @endphp
                                    <td class="px-6 py-1 text-center whitespace-nowrap font-semibold {{ ($result['stotal'] - $result['etotal']) < 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                        ₱ {{ number_format($totalresult, 2, '.', ',') }}
                                    </td>
                                    </tr>
                                    @php
                                        $x = 1;
                                    @endphp
                                @endif
                            @endforeach
                            @if ($x == 0)
                                <tr class="border-b">
                                    <th class="px-6 py-1 text-center font-medium text-gray-900 whitespace-nowrap">
                                        {{ date('M d, Y', $feCDate) }}
                                    </th>
                                    <td class="px-6 py-1 text-center whitespace-nowrap">
                                        ₱ 0.00
                                    </td>
                                    <td class="px-6 py-1 text-center whitespace-nowrap">
                                        ₱ 0.00
                                    </td>
                                    <td class="px-6 py-1 text-center whitespace-nowrap font-semibold">
                                        ₱ 0.00
                                    </td>
                                    <td class="px-6 py-1 text-center whitespace-nowrap font-semibold">
                                        ₱ 0.00
                                    </td>
                                </tr>
                            @endif
                            @php
                                $feCDate = strtotime('+1 day', $feCDate);
                            @endphp
                        @endwhile
                        <tr class="border-b">
                                <th class="px-6 py-1 text-center font-medium text-gray-900 whitespace-nowrap">
                                TOTAL
                                </th>
                                <td class="px-6 py-1 text-center whitespace-nowrap">
                                    ₱ {{ number_format($stotalv, 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-1 text-center whitespace-nowrap">
                                    ₱ {{ number_format($etotalv, 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-1 text-center whitespace-nowrap font-semibold">
                                    ₱ {{ number_format($sctotal, 2, '.', ',') }}
                                </td>
                                <td class="px-6 py-1 text-center whitespace-nowrap font-semibold">
                                    ₱ {{ number_format($sumtotal, 2, '.', ',') }}
                                </td>
                                
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    


    <script>
        $(document).ready(function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        });
    </script>
</body>
</html>