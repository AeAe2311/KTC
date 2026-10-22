<x-app-layout>
    @section('page_title', 'REPORTS')
    
    <div class="p-3 lg:pt-3 {{ (Auth::user()->role == 2) ? '' : ' lg:ml-64' }}">
        <div class="py-3">
            <div class="bg-gray-200 overflow-hidden shadow-md rounded-lg p-3">
                <form action="{{ route('areport.generate') }}" method="POST">
                    @csrf
                    <div class="flex items-end gap-4">
                        
                        <!-- Date Range -->
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-900 mb-1">Date</label>
                            <div class="flex items-center gap-x-2">
                                <input type="datetime-local" name="start" id="dateStart"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    value="{{ date('Y-m-d 00:00:00') }}">
                                <span class="text-gray-700 font-medium">to</span>
                                <input type="datetime-local" name="end" id="dateEnd"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    value="{{ date('Y-m-d 23:59:00') }}">
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-900 mb-1">Category</label>
                            <select id="category" name="category"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="" selected="true">-- Select Here --</option>
                                <!--<option value="remit">Remittance</option>-->
                                <option value="accounting">Accounting Reports</option> <!--ken added New OPTION FOR accounting reports -->
                                <option value="dailySR">Daily Sales Reports</option> <!--ken added NE OPTION FOR DSR -->
                                <option value="sales">Sales</option>
                                <option value="expenses">Expenses</option>
                                <!--<option value="both">Sales & Expenses</option>-->
                                <!--<option value="inventory">Raw Materials</option>-->
                                <!--<option value="menu">Menu</option>-->
                                <!--<option value="invmenu">Raw Materials & Menu</option>-->
                                <!--<option value="waste">Waste</option>-->
                            </select>
                        </div>

                        @auth
                            @if(auth()->user()->role == 1)
                                <!-- Branch -->
                                <div class="flex-1">
                                    <label for="branch" class="block text-sm font-medium text-gray-900 mb-1">
                                        Branch <span class="text-red-500">*</span>
                                    </label>
                                    <select id="branch" name="branch"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                            @foreach ($branchs as $branch)
                                                @if ($branch->id != 1)
                                                    <option value="{{ $branch->store_code }}">{{ $branch->name }}</option>
                                                @endif
                                            @endforeach
                                    </select>
                                </div>
                            @endif
                        @endauth


                        <!-- Report -->
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-900 mb-1">Report</label>
                            <select id="report" name="report"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="report">Report</option>
                            </select>
                        </div>

                        <!-- Generate Button -->
                        <div class="flex-1">
                            <button
                                class="bg-blue-600 font-bold text-white w-full py-2.5 rounded-lg hover:scale-105 transition-transform">
                                Generate
                            </button>
                        </div>

                    </div>
                </form>
            </div>
            
            <!-- The report will be displayed here after generation -->
            @if(session('report_data'))
                <div class="mt-6">
                    <!-- Report will be loaded here via the centralized view -->
                </div>
            @endif
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

            $('#category').on('change', function(){
                var category = $(this).val();

                $('#report').empty();

                if(category != 'both'){
                    if(category == 'expenses'){
                        $('#report').html(`
                            <option value="list">Transaction Logs</option>
                            <option value="summary">Summary</option>
                            <option value="unpaid">Accounts Payable</option>
                        `);
                    }else if(category == 'sales'){
                        $('#report').html(`
                            <option value="list">Transaction Logs</option>
                            <option value="summary">Summary</option>
                            
                            <option value="xreading">X Reading</option>
                            <option value="zreading">Z Reading</option>
                            
                                                       
                        `);
                        
                        /*
                        <option value="zreading">Z Reading</option>
                        <option value="hoursale">Hourly Sales</option>
                            <option value="compli">Complimentary</option>
                            <option value="compli_break">Complimentary Breakdown</option>
                            <option value="beverages">Beverages</option>
                            <option value="food">Food</option>
                            <option value="server">Server</option>
                        <option value="pax"># of Pax</option>
                            <option value="discount">Discount Report</option>
                            <option value="srpwd">SR & PWD Report</option> 
                        */
                        
                    /* KEN ADDED START HERE FOR DSR VIEWING KEN 252306001 - 4-19-2026   */ 
                    }else if(category == 'dailySR'){
                        $('#report').html(`
                            <option value="dailySR">Daily Sales Report</option>
                        `);
                    /* KEN ADDED END HERE FOR DSR VIEWING KEN 252306001 - 4-19-2026   */ 
                    
                    /* KEN ADDED START HERE FOR DSR VIEWING KEN 252306001 - 4-22-2026   */ 
                    }else if(category == 'accounting'){
                        $('#report').html(`
                            <option value="balsheet">Balance Sheet</option>
                            <option value="incomeState">Income Statement</option>
                        `);
                    /* KEN ADDED END HERE FOR DSR VIEWING KEN 252306001 - 4-22-2026   */ 
                    
                    }else if(category == 'remit'){
                        $('#report').html(`
                            <option value="report">Report</option>
                        `);
                    }else if(category == 'inventory'){
                        $('#report').html(`
                            <option value="logs">Logs</option>
                            <option value="stock">Current Stocks</option>
                        `);
                    }else if(category == 'menu'){
                        $('#report').html(`
                            <option value="rank">Rankings</option>
                            <option value="stock">Current Stock</option>
                        `);
                    }else if(category == 'invmenu'){
                        $('#report').html(`
                            <option value="summary">Summary Inventory</option>
                        `);
                    }else if(category == 'waste'){
                        $('#report').html(`
                            <option value="raw">Raw Inventory</option>
                            <option value="menu">Menu</option>
                        `);
                    }else{
                        $('#report').html(`
                            <option value="list">Transaction Logs</option>
                            <option value="summary">Summary</option>
                        `);
                    }
                    
                }else{
                    $('#report').html(`
                        <option value="summary">Summary</option>
                    `);
                }
            });
        });
    </script>
</x-app-layout>
