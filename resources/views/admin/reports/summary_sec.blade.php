<x-app-layout>
    @section('page_title', 'REPORTS')
    
    <div class="p-3 lg:pt-3 {{ (Auth::user()->role == 2) ? '' : ' lg:ml-64' }}">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                {{-- CONTROLS --}}
                    <div class="mb-3">
                        <div class="flex justify-between">
                            <div class="w-32">
                                <a href="{{ route('areport.index') }}" class="hidden md:block text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px text-center"><i class="uil uil-arrow-left mr-1"></i>BACK</a>
                            </div>
                            <div class="w-32">
                                <a href="{{ url('/report/print/'.$startDate.'/'.$endDate.'/'.$category.'/'.$report) }}" target="_blank" class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px text-center"><i class="uil uil-print mr-1"></i>PRINT</a>
                            </div>
                        </div>
                    </div>
                {{-- CONTROLS END --}}

                <div>
                    <div class="mt-4 flex justify-between">
                        <h1 class="text-3xl">S&E Summary Report</h1>
                        <h1 class="flex items-center">{{ date('M j, Y', strtotime($startDate)).' - '.date('M j, Y', strtotime('-1 day', strtotime($endDate))) }}</h1>
                    </div>
                    {{-- TABLE --}}
                        <div class="block">
                            <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="border-b">
                                            <th class="px-6 text-center">
                                                Date
                                            </th>
                                            <th class="px-6 text-center">
                                                Total Sales
                                            </th>
                                            <th class="px-6 text-center">
                                                Total Expenses
                                            </th>
                                            <th class="px-6 text-center">
                                                Total Service Charge
                                            </th>
                                            <th class="px-6 text-center">
                                                Total PWD
                                            </th>
                                            <th class="px-6 text-center">
                                                Total Vat
                                            </th>
                                            <th class="px-6 text-center">
                                                Total Profit
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $feCDate = strtotime($startDate);
                                            $feEDate = strtotime($endDate);
                                            $sctotal = $stotalv = $etotalv = $sumtotal = $sumvattotal = $sumpwdtotal = 0 ;
                                        @endphp
                                        @while ($feCDate < $feEDate)
                                            @php
                                                $sTotal = 0;
                                                $eTotal = 0;
                                                $pTotal = 0;
                                                $scTotal = 0;
                                                $vattotal = 0;
                                                $pwdtotal = 0;
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
                                                            ₱ {{ number_format($result['sctotal'], 2, '.', ',') }}
                                                        </td>
                                                        <td class="px-6 py-1 text-center whitespace-nowrap">
                                                            ₱ {{ number_format($result['pwdtotal'], 2, '.', ',') }}
                                                        </td>
                                                        <td class="px-6 py-1 text-center whitespace-nowrap">
                                                            ₱ {{ number_format($result['vattotal'], 2, '.', ',') }}
                                                        </td>
                                                        @php    
                                                        $incentives = $result['sctotal'];
                                                        $vat = $result['vattotal'];
                                                        $pwd = $result['pwdtotal'];
                                                        $totalvatpwd = $vat + $pwd;

                                                        $totalvpi = $totalvatpwd + $incentives;
                                                        $totalexpenses = $result['etotal'] + $totalvpi;
                                                        $totalresult = $result['stotal'] - $totalexpenses;
                                                        
                                                        $sctotal += $incentives ;
                                                        $stotalv += $result['stotal'];
                                                        $etotalv += $result['etotal'];
                                                        $sumvattotal += $vat;
                                                        $sumpwdtotal += $pwd;

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
                                                        ₱ {{ number_format($sumpwdtotal, 2, '.', ',') }}
                                                    </td>
                                                    <td class="px-6 py-1 text-center whitespace-nowrap font-semibold">
                                                        ₱ {{ number_format($sumvattotal, 2, '.', ',') }}
                                                    </td>
                                                    <td class="px-6 py-1 text-center whitespace-nowrap font-semibold">
                                                        ₱ {{ number_format($sumtotal, 2, '.', ',') }}
                                                    </td>
                                                    
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {{-- TABLE END --}}
                </div>
            </div>
        </div>
     </div>

    <script>
        $(document).ready(function() {
            $('#navButton').click(function(){
                    $('#topNav').addClass('absolute');
                    $('#topNav').removeClass('sticky');
                    $('#contentDiv').addClass('pt-14');
                });

            $(document).mouseup(function(e) {
                var container = $(".navDiv");

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $('#topNav').removeClass('absolute');
                    $('#topNav').addClass('sticky');
                $('#contentDiv').removeClass('pt-14');
                }
            });
        });
    </script>
</x-app-layout>
