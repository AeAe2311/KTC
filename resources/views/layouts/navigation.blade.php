<nav x-data="{ open: false }" id="topNav" class="sticky top-0 z-50 w-full bg-white shadow-md">
    <div class="lg:hidden flex flex-row {{ (url()->current() == url('/inventory')) ? 'justify-between md:justify-start' : ''; }} ">
        <button id="navButton" data-drawer-target="sidebar-multi-level-sidebar" data-drawer-toggle="sidebar-multi-level-sidebar" aria-controls="sidebar-multi-level-sidebar" type="button" class="inline-flex items-center h-12 p-2 ml-3 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
            <span class="sr-only">Open sidebar</span>
            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
            </svg>
        </button>
    
        <div class="self-center">
            <h2 class="ml-3 text-base font-semibold leading-tight text-gray-600">
                @yield('page_title')
            </h2>
        </div>

        {{-- INVENTORY ADD --}}
            @if (url()->current() != url('/inventory/add') && url()->current() != url('/inventory/edit') && Str::contains(url()->current(), url('/inventory')))
                <div class="self-center float-right ml-auto md:hidden">
                    <button id="inventoryMenu" data-dropdown-toggle="dropdownInventoryMenu" class="inline-flex items-center h-8 text-base font-medium text-center text-white rounded-lg w-7 bg-white-700 focus:outline-none" type="button">
                        <i class="text-black uil uil-ellipsis-v"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="dropdownInventoryMenu" class="z-10 hidden px-3 divide-y divide-gray-100">
                        <div class="px-3 border rounded-lg shadow-md bg-gray-50 w-44">
                            <ul class="py-2 text-sm text-gray-700" aria-labelledby="inventoryMenu">
                                <li>
                                    <a href="{{ route('inventory.add') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100">ADD</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        {{-- INVENTORY ADD END --}}

        {{-- USER ADD --}}
            @if (url()->current() != url('/system-management/user/add') && !Str::contains(url()->current(), url('/system-management/user/edit/')) && Str::contains(url()->current(), url('/system-management/user')))
                <div class="self-center float-right ml-auto md:hidden">
                    <button id="inventoryMenu" data-dropdown-toggle="dropdownInventoryMenu" class="inline-flex items-center h-8 text-base font-medium text-center text-white rounded-lg w-7 bg-white-700 focus:outline-none" type="button">
                        <i class="text-black uil uil-ellipsis-v"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="dropdownInventoryMenu" class="z-50 hidden px-3 divide-y divide-gray-100">
                        <div class="px-3 border rounded-lg shadow-md bg-gray-50 w-44">
                            <ul class="py-2 text-sm text-gray-700" aria-labelledby="inventoryMenu">
                                <li>
                                    <a href="{{ route('user.add') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100">ADD</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        {{-- USER ADD END --}}

        {{-- TABLE ADD --}}
            @if (url()->current() != url('/system-management/table/add') && !Str::contains(url()->current(), url('/system-management/table/edit')) && Str::contains(url()->current(), url('/system-management/table')))
                <div class="self-center float-right ml-auto md:hidden">
                    <button id="inventoryMenu" data-dropdown-toggle="dropdownInventoryMenu" class="inline-flex items-center h-8 text-base font-medium text-center text-white rounded-lg w-7 bg-white-700 focus:outline-none" type="button">
                        <i class="text-black uil uil-ellipsis-v"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="dropdownInventoryMenu" class="z-10 hidden px-3 divide-y divide-gray-100">
                        <div class="px-3 border rounded-lg shadow-md bg-gray-50 w-44">
                            <ul class="py-2 text-sm text-gray-700" aria-labelledby="inventoryMenu">
                                <li>
                                    <a href="{{ route('table.add') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100">ADD</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        {{-- TABLE ADD END --}}

        {{-- CATEGORY ADD --}}
            @if (url()->current() != url('/system-management/category/add') && !Str::contains(url()->current(), url('/system-management/category/edit')) && Str::contains(url()->current(), url('/system-management/category')))
                <div class="self-center float-right ml-auto md:hidden">
                    <button id="inventoryMenu" data-dropdown-toggle="dropdownInventoryMenu" class="inline-flex items-center h-8 text-base font-medium text-center text-white rounded-lg w-7 bg-white-700 focus:outline-none" type="button">
                        <i class="text-black uil uil-ellipsis-v"></i>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="dropdownInventoryMenu" class="z-10 hidden px-3 divide-y divide-gray-100">
                        <div class="px-3 border rounded-lg shadow-md bg-gray-50 w-44">
                            <ul class="py-2 text-sm text-gray-700" aria-labelledby="inventoryMenu">
                                <li>
                                    <a href="{{ route('category.add') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-100">ADD</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        {{-- CATEGORY ADD END --}}



    </div>
    
<!-- new code for testing -->
<!-- <div class="flex justify-between h-12 bg-white shadow">
    <div class="flex items-center">
        <button class="flex items-center justify-center w-12 h-12 text-gray-800 hover:bg-gray-100" type="button" data-drawer-target="drawer-navigation" data-drawer-show="drawer-navigation" aria-controls="drawer-navigation">
            <i class="text-3xl uil uil-bars"></i>
        </button>
        <h2 class="text-base font-semibold leading-tight text-gray-600">
            @yield('page_title')
        </h2>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="flex w-12 h-12 bg-red-600 hover:bg-red-700">
        @csrf
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center justify-center w-full h-full">
            <i class="text-3xl text-white uis uil-sign-out-alt"></i>
        </a>
    </form>
</div> -->

<!-- end of the new code -->
<!-- 2nd new code start -->
<!-- <div id="drawer-navigation" class="fixed top-0 left-0 z-40 w-64 h-screen p-4 overflow-x-hidden overflow-y-auto transition-transform -translate-x-full bg-white" tabindex="-1" aria-labelledby="drawer-navigation-label">
    <h5 id="drawer-navigation-label" class="text-base font-semibold text-gray-500 uppercase">Menu</h5>
    <button type="button" data-drawer-hide="drawer-navigation" aria-controls="drawer-navigation" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center" >
        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        <span class="sr-only">Close menu</span>
    </button> -->
    <!-- end of 2nd new code -->

     <aside style="z-index: 200;" id="sidebar-multi-level-sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 py-4 overflow-y-auto bg-white border-r border-gray-200 navDiv">
        <!-- <div class="pt-4 overflow-y-auto overflow-x-hidden justify-between h-[calc(100vh-72px)]"> -->
            <div class="flex justify-center gap-2 mb-3">
                @php
                
                    $settings = \App\Models\Setting::where('store_code', auth()->user()->store_code)->first();
                @endphp
                @if($settings)
                    <span class="text-xs font-semibold text-gray-900">
                        {{ $settings->name }}
                    </span>
                @endif
            </div>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ (Str::contains(url()->current(), url('/dashboard')) || url()->current() == url('/')) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                        <i class="text-2xl text-gray-500 transition duration-75 uil uil-analysis group-hover:text-gray-900"></i>
                        {{-- <svg aria-hidden="true" class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path></svg> --}}
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('areport.index') }}" class="{{ (Str::contains(url()->current(), url('/reports'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                        <svg aria-hidden="true" class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="flex-1 ml-3 whitespace-nowrap">Reports</span>
                    </a>
                </li>
                {{-- ken 20250623001 added accounting start 7-20-2026 --}}
                <li>
                    <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-lr" data-collapse-toggle="dropdown-lr">
                        <i class="text-2xl text-gray-500 uil uil-newspaper"></i>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap">Accounting Reports</span>
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>

                    <ul id="dropdown-lr" class="hidden py-2 space-y-2">
                        <li>
                            <a href="{{ route('sales-journal.index') }}"
                            class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                <i class="uil uil-chart-line text-lg mr-3"></i>
                                <span>Sales Journal</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('expenses-journal.index') }}"
                            class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                <i class="uil uil-chart-line text-lg mr-3"></i>
                                <span>Expenses Journal</span>
                            </a>
                        </li>   
                        <li>
                            <a href="{{ route('reports.income-statement') }}"
                            class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                <i class="uil uil-file-alt text-lg mr-3"></i>
                                <span>Income Statement</span>
                            </a>
                        </li>  
                        {{-- LINE 181: start of chart of accounts nav button added by ken 7-29-2026 --}}  
                        <li>
                            <a href="{{ route('report.chart-of-accounts.index') }}"
                            class="{{ (Str::contains(url()->current(), url('/report/chart-of-accounts')))
                                    ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner'
                                    : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">

                                <i class="text-2xl text-gray-500 uil uil-book-alt"></i>

                                <span class="flex-1 ml-3 whitespace-nowrap">
                                    View Chart of Accounts
                                </span>
                            </a>
                        </li>     
                        {{-- LINE 203: Start of trial balance nav button added by ken 7-29-2026 --}}                    
                        <li>
                            <a href="{{ route('report.trial_balance') }}" class="{{ (Str::contains(url()->current(), url('/report/trial-balance'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                                <i class="text-2xl text-gray-500 uil uil-calculator"></i>
                                <span class="flex-1 ml-3 whitespace-nowrap">Trial Balance</span>
                            </a>
                        </li>  
                        {{-- LINE 209: end of trial balance nav button added by ken 7-29-2026 --}}                                             
                        {{-- <li>
                            <a href="{{ route('cashreceipt-journal.index') }}"
                            class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                <i class="uil uil-money-withdraw text-lg mr-3"></i>
                                <span>Cash Receipt Journal</span>
                            </a>
                        </li> --}}
                    </ul>
                </li>    
                {{-- ken 20250623001 added accounting end 7-20-2026 --}} 

                <!-- <li>
                         //<a href="{{ route('orders.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                        <i class="text-2xl text-gray-500 uil uil-utensils-alt"></i>
                        <span class="ml-3">Tables</span>
                        </a> //
                        <a href="{{ route('orders.index') }}" class="{{ (Str::contains(url()->current(), url('/orders'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                        <i class="w-6 text-2xl text-gray-500 transition duration-75 uil uil-utensils-alt group-hover:text-gray-900"></i>
                        <span class="flex-1 ml-3 whitespace-nowrap">Tables</span>
                    </a>
                </li> -->

                {{-- <li>
                    <a href="{{ route('inventory.index') }}" class="{{ (Str::contains(url()->current(), url('/inventory'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                        <i class="w-6 text-2xl text-gray-500 transition duration-75 uil uil-box group-hover:text-gray-900"></i>
                        <span class="flex-1 ml-3 whitespace-nowrap">Inventory</span>
                    </a>
                </li> --}}
                <!-- start ar section -->
               
                <li>
                    <a href="{{ route('pos.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <i class="text-2xl text-gray-500 uil uil-monitor"></i>
                    <span class="ml-3">Orders</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('orders.indexAdmin') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <i class="text-2xl text-gray-500 uil uil-utensils-alt"></i>
                    <span class="ml-3">Clients</span>
                    </a>
                </li>
                 <li>
                    <button type="button" class="{{ (Str::contains(url()->current(), url('/ar')) || Str::contains(url()->current(), url('/arlogs'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}" aria-controls="arDropdown" data-collapse-toggle="arDropdown">
                        <i class="text-2xl text-gray-500 transition duration-75 uil uil-receipt group-hover:text-gray-900"></i>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap w-36 mr-2.5" sidebar-toggle-item>Account Receivables</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                    </button>
                    <ul id="arDropdown" class="hidden py-2 space-y-2">
                        
                        {{-- LINE 263: start added by ken 20250623001 CB 8-23-2026 --}}
                        <li>
                            <a href="{{ route('ar.customer-balance') }}" class="{{ (Str::contains(url()->current(), url('/inventory'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                            Customer Balance
                            </a>
                        </li>          
                        {{-- LINE 269: end added by ken 20250623001 CB 8-23-2026 --}}
                        
                        <li>
                            <a href="{{ route('ar.index') }}" class="{{ (Str::contains(url()->current(), url('/inventory'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                            Unpaid
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ar.indexpaid') }}" class="{{ (Str::contains(url()->current(), url('/menu'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                            Paid
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('arlogs.index') }}" class="{{ (Str::contains(url()->current(), url('/menu'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                            Payment Logs
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- end of ar section -->
                
                <!-- li>
                    <a href="{{ route('orders.indexAdmin') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                    <i class="text-2xl text-gray-500 uil uil-utensils-alt"></i>
                    <span class="ml-3">Tables</span>
                    </a>
                </li -->
                <!--<li>-->
                <!--    <a href="{{ route('transactions.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">-->
                <!--    <svg class="text-2xl text-gray-500 transition duration-75 uil uil-folder-transfer group-hover:text-gray-900" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M20 2H10a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V5a3 3 0 0 0-3-3m1 10a1 1 0 0 1-1 1H10a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1Zm-3.5-4a1.49 1.49 0 0 0-1 .39a1.5 1.5 0 1 0 0 2.22a1.5 1.5 0 1 0 1-2.61M16 17a1 1 0 0 0-1 1v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-4h1a1 1 0 0 0 0-2H3v-1a1 1 0 0 1 1-1a1 1 0 0 0 0-2a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1a1 1 0 0 0-1-1M6 18h1a1 1 0 0 0 0-2H6a1 1 0 0 0 0 2"/></svg>-->
                <!--    <span class="ml-3">Transactions</span>-->
                <!--    </a>-->
                <!--</li>-->
                {{-- start of update for transactions and sales detailed report NAV bar 8-20-2026 --}}
                <li>
                    <button
                        type="button"
                        class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                        aria-controls="dropdown-transactions"
                        data-collapse-toggle="dropdown-transactions"
                    >
                        <i class="text-2xl text-gray-500 uil uil-books"></i>

                        <span class="flex-1 ml-3 text-left whitespace-nowrap">
                            Transactions
                        </span>

                        <svg
                            class="w-3 h-3"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 10 6"
                        >
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m1 1 4 4 4-4"
                            />
                        </svg>
                    </button>

                    <ul id="dropdown-transactions" class="hidden py-2 space-y-2">
                        {{-- Transactions --}}
                        <li>
                            <a
                                href="{{ route('transactions.index') }}"
                                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 group"
                            >
                                <svg
                                    class="text-2xl text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="1em"
                                    height="1em"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        fill="currentColor"
                                        d="M20 2H10a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V5a3 3 0 0 0-3-3m1 10a1 1 0 0 1-1 1H10a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1Zm-3.5-4a1.49 1.49 0 0 0-1 .39a1.5 1.5 0 1 0 0 2.22a1.5 1.5 0 1 0 1-2.61M16 17a1 1 0 0 0-1 1v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-4h1a1 1 0 0 0 0-2H3v-1a1 1 0 0 1 1-1a1 1 0 0 0 0-2a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1a1 1 0 0 0-1-1M6 18h1a1 1 0 0 0 0-2H6a1 1 0 0 0 0 2"
                                    />
                                </svg>

                                <span class="ml-3">
                                    Transactions
                                </span>
                            </a>
                        </li>
                       <!-- LINE 355: Start of adding submit sales report by ken 10-22-2026 -->
                        <li>
                            <a href="{{ route('submit-salesreport.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                            <i class="text-2xl text-gray-500 uil uil-file-check-alt"></i>
                            <span class="ml-3">Submit Sales Report</span>
                            </a>
                        </li>
                        <!-- LINE 362: End of adding submit sales report by ken 10-22-2026 -->
                        {{-- Sales Detailed Report --}}
                        <li>
                            <a
                                href="{{ route('sales-report.index') }}"
                                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 group"
                            >
                                <svg
                                    class="text-2xl text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="1em"
                                    height="1em"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        fill="currentColor"
                                        d="M20 2H10a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V5a3 3 0 0 0-3-3m1 10a1 1 0 0 1-1 1H10a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1Zm-3.5-4a1.49 1.49 0 0 0-1 .39a1.5 1.5 0 1 0 0 2.22a1.5 1.5 0 1 0 1-2.61M16 17a1 1 0 0 0-1 1v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-4h1a1 1 0 0 0 0-2H3v-1a1 1 0 0 1 1-1a1 1 0 0 0 0-2a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1a1 1 0 0 0-1-1M6 18h1a1 1 0 0 0 0-2H6a1 1 0 0 0 0 2"
                                    />
                                </svg>

                                <span class="ml-3">
                                    Sales Detailed Report
                                </span>
                            </a>
                        </li>
                        {{-- LINE 351: Start of adding cash approval by ken 9-30-2026 --}}
                        <li>
                            <a
                                href="{{ route('cash-turnover-approval.index') }}"
                                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 group">

                                <!-- Cash Turn Over icon -->
                                <svg
                                    class="text-2xl text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="1em"
                                    height="1em"
                                    viewBox="0 0 24 24">
                                    <path
                                        fill="currentColor"
                                        d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 15h-2v-1H9v-2h2v-1h2a1 1 0 0 0 0-2h-2a3 3 0 0 1 0-6V4h2v1h2v2h-2v1h-2a1 1 0 0 0 0 2h2a3 3 0 0 1 0 6Z" />
                                </svg>

                                <span class="ml-3">
                                    CASH APPROVAL
                                </span>
                            </a>
                        </li>     
                        {{-- LINE 351: End of adding cash approval by ken 9-30-2026 --}}                    
                    </ul>
                </li>                
                {{-- end of update for transactions and sales detailed report NAV bar 8-20-2026 --}}
                <!-- added start by 20250623001 ken 1-11-2026 -->
                {{--<li>
                    <a href="{{ route('onlinebank.index') }}" 
                    class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                        
                        <i class="uil uil-university text-2xl text-gray-500 transition duration-75 group-hover:text-gray-900"></i>
                        
                        <span class="ml-3">Online Banking list</span>
                    </a>
                </li>      
                <li>
                    <a href="{{ route('returnbottle.index') }}"
                    class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 7111 7111"
                            class="w-6 h-6 fill-gray-500 transition duration-75 group-hover:fill-gray-800">

                            <path d="M4434 7112l-1745 0c-190,0 -346,-156 -346,-352l0 -2931c0,-443 162,-916 334,-1411 191,-551 388,-1123 408,-1750l-45 0c-57,0 -103,-46 -103,-104l0 -230c0,-185 149,-334 334,-334l575 0c185,0 334,149 334,334l0 230c0,58 -46,104 -103,104l-33 0c20,627 216,1199 407,1750 173,495 334,968 334,1411l0 2931c-6,196 -161,352 -351,352z"/>
                            <path d="M4681 6040l-2240 0c-57,0 -103,-46 -103,-103 0,-58 46,-104 103,-104l2240 0c58,0 104,46 104,104 0,57 -46,103 -104,103z"/>
                            <path d="M4670 3783l-2223 0c-58,0 -104,-46 -104,-104 0,-57 46,-103 104,-103l2229 0c57,0 103,46 103,103 -5,58 -52,104 -109,104z"/>
                            <path d="M4244 4871l-1371 0c-57,0 -104,-46 -104,-103l0 -547c0,-58 47,-104 104,-104l1371 0c57,0 103,46 103,104l0 553c0,51 -46,97 -103,97z"/>
                            <path d="M3985 5505l-853 0c-57,0 -103,-46 -103,-104l0 -345c0,-58 46,-104 103,-104l858 0c58,0 104,46 104,104l0 345c-6,58 -52,104 -109,104z"/>
                            <path d="M5885 7112l-1278 0c-41,0 -81,-12 -127,-29 -52,-23 -69,-87 -46,-133 23,-52 86,-69 132,-46 17,12 29,12 35,12l1278 0c41,0 75,-35 75,-81l0 -2154c0,-299 -115,-627 -236,-979 -133,-391 -288,-835 -305,-1313l-352 0c-17,478 -172,916 -305,1308l-6 17c-17,52 -74,81 -126,63 -46,-17 -75,-75 -58,-126l6 -18c134,-397 273,-803 292,-1244 -57,-1 -102,-46 -102,-103l0 -173c0,-150 121,-271 271,-271l420 0c150,0 271,121 271,271l0 173c0,57 -40,102 -96,103 18,446 158,853 297,1250 127,369 248,714 248,1042l0 2148c-6,156 -133,283 -288,283z"/>
                            <path d="M6063 6328l-1382 0c-57,0 -103,-46 -103,-103 0,-58 46,-104 103,-104l1382 0c58,0 104,46 104,104 0,57 -46,103 -104,103z"/>
                            <path d="M6063 4676l-1382 0c-57,0 -103,-46 -103,-104 0,-58 46,-104 103,-104l1382 0c58,0 104,46 104,104 0,58 -52,104 -104,104z"/>
                            <path d="M2505 7112l-1285 0c-155,0 -282,-127 -282,-283l0 -2148c0,-328 121,-679 248,-1042 139,-397 279,-804 297,-1250 -59,0 -107,-42 -107,-103l0 -173c0,-150 121,-271 271,-271l420 0c150,0 271,121 271,271l0 173c5,57 -39,103 -96,103 19,459 168,885 303,1262 17,51 -12,109 -63,126 -52,18 -110,-11 -127,-63 -138,-392 -294,-835 -317,-1325l-351 0c-17,478 -173,922 -311,1313 -121,352 -236,680 -236,979l0 2148c0,46 40,81 80,81l1279 0c6,0 17,0 34,-12 52,-23 110,-5 133,47 23,51 6,109 -46,132 -35,23 -75,35 -115,35z"/>
                            <path d="M2430 6328l-1388 0c-58,0 -104,-46 -104,-103 0,-58 46,-104 104,-104l1388 0c57,0 103,46 103,104 0,57 -46,103 -103,103z"/>
                            <path d="M2430 4676l-1382 0c-58,0 -104,-46 -104,-104 0,-58 46,-104 104,-104l1382 0c57,0 103,46 103,104 0,58 -46,104 -103,104z"/>
                            <path d="M5372 5879c-264,0 -478,-213 -478,-478 0,-265 214,-478 478,-478 265,0 478,213 478,478 0,265 -213,478 -478,478z"/>
                            <path d="M1739 5879c-265,0 -478,-213 -478,-478 0,-265 213,-478 478,-478 265,0 478,213 478,478 0,265 -213,478 -478,478z"/>

                        </svg>

                        <span class="ml-3">Return Bottle</span>
                    </a>
                </li>    --}}                      
                <!-- added end by 20250623001 ken 1-11-2026 -->
                
                <!-- start ar section -->
                 {{-----------------
                 <li>
                    <button type="button" class="{{ (Str::contains(url()->current(), url('/ar')) || Str::contains(url()->current(), url('/arlogs'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}" aria-controls="arDropdown" data-collapse-toggle="arDropdown">
                        <i class="text-2xl text-gray-500 transition duration-75 uil uil-receipt group-hover:text-gray-900"></i>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap w-36 mr-2.5" sidebar-toggle-item>Account Receivables</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                    </button>
                    <ul id="arDropdown" class="hidden py-2 space-y-2">
                        
                        <li>
                            <a href="{{ route('ar.index') }}" class="{{ (Str::contains(url()->current(), url('/inventory'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                            Unpaid
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ar.indexpaid') }}" class="{{ (Str::contains(url()->current(), url('/menu'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                            Paid
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('arlogs.index') }}" class="{{ (Str::contains(url()->current(), url('/menu'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                            Payment Logs
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- end of ar section -->
                 ------------------------}}
                  {{-- ken updated july 18, 2026 ken 20250623001 --}}
                <li>
                    <button type="button" class="{{ (Str::contains(url()->current(), url('/inventory')) || Str::contains(url()->current(), url('/menu'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}" aria-controls="stockDropdown" data-collapse-toggle="stockDropdown">
                        <i class="text-2xl text-gray-500 transition duration-75 uil uil-box group-hover:text-gray-900"></i>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap w-36 mr-2.5" sidebar-toggle-item>Supply Management</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                    </button>
                    <ul id="stockDropdown" class="hidden py-2 space-y-2">                 
                        <li>
                            <a href="{{ route('menu.index') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-shopping-bag group-hover:text-gray-800"></i>
                                <span class="ml-3">Items</span>
                            </a>
                        </li> 
                        <li>
                            <a href="{{ route('transform.index') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-exchange group-hover:text-gray-800"></i>
                                <span class="ml-3">Transform</span>
                            </a>
                        </li>
                        {{-- LINE 445: start of added nav sawdust by ken 9-30-26 --}}
                        <li>
                            <a href="{{ route('sawdust.index') }}"
                                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 group">
                                <svg
                                    class="text-2xl text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="1em"
                                    height="1em"
                                    viewBox="0 0 24 24">
                                    <path
                                        fill="currentColor"
                                        d="M12 2c-.5 0-.9.3-1.1.7-.6-.2-1.3 0-1.7.5-.5-.1-1 .1-1.4.4-.4-.3-.9-.4-1.4-.3-.5.1-.9.4-1.1.8-.5-.1-1 .1-1.3.5-.3.3-.4.8-.3 1.2-.4.2-.7.6-.8 1.1-.1.5 0 1 .3 1.4-.3.4-.4.9-.3 1.4.1.5.4.9.8 1.1-.1.5.1 1 .5 1.3.3.3.8.4 1.2.3.2.4.6.7 1.1.8.5.1 1 0 1.4-.3.4.3.9.4 1.4.3.5-.1.9-.4 1.1-.8.5.1 1-.1 1.3-.5.3-.3.4-.8.3-1.2.4-.2.7-.6.8-1.1.1-.5 0-1-.3-1.4.3-.4.4-.9.3-1.4-.1-.5-.4-.9-.8-1.1.1-.5-.1-1-.5-1.3-.3-.3-.8-.4-1.2-.3-.2-.4-.6-.7-1.1-.8-.5-.1-1 0-1.4.3-.4-.3-.9-.4-1.4-.3-.5.1-.9.4-1.1.8zM8 7c0-.6.4-1 1-1s1 .4 1 1-.4 1-1 1-1-.4-1-1zm6 0c0-.6.4-1 1-1s1 .4 1 1-.4 1-1 1-1-.4-1-1zm-3 3c0-.6.4-1 1-1s1 .4 1 1-.4 1-1 1-1-.4-1-1zm-4 4c0-.6.4-1 1-1s1 .4 1 1-.4 1-1 1-1-.4-1-1zm8 0c0-.6.4-1 1-1s1 .4 1 1-.4 1-1 1-1-.4-1-1z"
                                    />
                                </svg>

                                <span class="ml-3">
                                    SAWDUST
                                </span>
                            </a>
                        </li>     
                        {{-- LINE 466: end of added nav sawdust by ken 9-30-26 --}}                  
                        <li>
                            <a href="{{ route('inventory.movement.index') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-expand-arrows-alt group-hover:text-gray-800"></i>
                                <span class="ml-3">Inventory Movement</span>
                            </a>
                        </li>         
                        
                        <!-- <li>
                            <a href="{{ route('stock.add') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-file-plus-alt group-hover:text-gray-800"></i>
                                <span class="ml-3">Purchase Order</span>
                            </a>
                        </li>   -->
                        
                        <!-- <li>
                            <a href="{{ route('pending.purchase.orders.index') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-file-check group-hover:text-gray-800"></i>
                                <span class="ml-3">Receiving Orders</span>
                            </a>
                        </li> -->
                    </ul>
                </li>
                 {{-- ken end of update july 18, 2026 ken 20250623001 --}}
                                   {{-- ken updated july 18, 2026 ken 20250623001 --}}
                <li>
                    <button type="button" class="{{ (Str::contains(url()->current(), url('/inventory')) || Str::contains(url()->current(), url('/menu'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}" aria-controls="poDropdown" data-collapse-toggle="poDropdown">
                        <i class="text-2xl text-gray-500 transition duration-75 uil uil-box group-hover:text-gray-900"></i>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap w-36 mr-2.5" sidebar-toggle-item>Procurement</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                    </button>
                    <ul id="poDropdown" class="hidden py-2 space-y-2">                 
                        <li>
                            <a href="{{ route('stock.add') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-file-plus-alt group-hover:text-gray-800"></i>
                                <span class="ml-3">Purchase Order</span>
                            </a>
                        </li>  
                        
                        <li>
                            <a href="{{ route('pending.purchase.orders.index') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-file-check group-hover:text-gray-800"></i>
                                <span class="ml-3">Receiving Orders</span>
                            </a>
                        </li>                        
                    </ul>
                </li>
                 {{-- ken end of update july 18, 2026 ken 20250623001 --}}
                {{-- start of location transfer section by jet  --}}
                <li>
                    <button type="button" class="{{ (Str::contains(url()->current(), url('/inventory/branch-transfer')) || Str::contains(url()->current(), url('/inventory/location-transfer'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}" aria-controls="locationTransferDropdown" data-collapse-toggle="locationTransferDropdown">
                        <i class="text-2xl text-gray-500 transition duration-75 uil uil-location-arrow group-hover:text-gray-900"></i>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap w-36 mr-2.5" sidebar-toggle-item>Location Transfer</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                    </button>
                    <ul id="locationTransferDropdown" class="hidden py-2 space-y-2">
                        <li>
                            <a href="{{ route('branch-transfer.add') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-file-plus-alt group-hover:text-gray-800"></i>
                                <span class="ml-3">Request</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('branch-transfer.pending') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-clock group-hover:text-gray-800"></i>
                                <span class="ml-3">Pending</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('branch-transfer.received') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <i class="text-2xl text-gray-500 transition duration-75 uil uil-inbox group-hover:text-gray-800"></i>
                                <span class="ml-3">Received</span>
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- end of location transfer section by jet  --}}                
                 
                
                <!-- 4-27 harold -->
                <li>
                    <button type="button"
                        class="{{ Str::contains(url()->current(), url('/deletedmenu')) || Str::contains(url()->current(), url('/deletedraw')) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200' }}"
                        aria-controls="restoreDropdown" data-collapse-toggle="restoreDropdown">
                        <i
                        class="text-2xl text-gray-500 transition duration-75 uil  uil-rotate-360 group-hover:text-gray-900"></i>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap w-36 mr-2.5"
                            sidebar-toggle-item>Restore</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                    </button>
                    <ul id="restoreDropdown" class="hidden py-2 space-y-2">
                        <li>                            
                                <a href="{{ route('deletedmenu.index') }}"
                                    class="{{ Str::contains(url()->current(), url('/deletedmenu')) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200' }}">
                                    Item
                                </a>

                        </li>
                        <li>           

                        </li>
                    </ul>
                </li>
                 
                <li>
                   <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 " aria-controls="dropdown-expenses" data-collapse-toggle="dropdown-expenses">
                        <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                            <path xmlns="http://www.w3.org/2000/svg" d="M540-420q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM220-280q-24.75 0-42.375-17.625T160-340v-400q0-24.75 17.625-42.375T220-800h640q24.75 0 42.375 17.625T920-740v400q0 24.75-17.625 42.375T860-280H220Zm100-60h440q0-42 29-71t71-29v-200q-42 0-71-29t-29-71H320q0 42-29 71t-71 29v200q42 0 71 29t29 71Zm480 180H100q-24.75 0-42.375-17.625T40-220v-460h60v460h700v60ZM220-340v-400 400Z"/>
                        </svg>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap">Expenses Management</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                   </button>
                   <ul id="dropdown-expenses" class="hidden py-2 space-y-2">
                         @if (Auth::user()->role == 1 || Auth::user()->role == 2)
                         <li>
                            <a href="{{ route('expenses.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">General Expenses</a>
                         </li>
                         @endif

                         @if (Auth::user()->role == 5)
                         <li>
                            <a href="{{ route('e_request.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Expenses Approval</a>
                         </li>
                         <li>
                            <a href="{{ route('deletion-approval.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Deletion Approval</a>
                         </li>
                         @endif
                         <li>
                            <a href="{{ route('revolving.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Revolving Budget</a>
                         </li>
                         {{--<li>
                            <a href="{{ route('staff.clocking') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Time In/Out</a>
                         </li>
                         <li>
                            <a href="{{ route('clock.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Time logs</a>
                         </li>--}}
                   </ul>
                </li>
                
                <!-- End of Expenses and Revolving Section -->
                <li>
                   <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-waste" data-collapse-toggle="dropdown-waste">
                         <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                            <path xmlns="http://www.w3.org/2000/svg" d="M261-120q-24.75 0-42.375-17.625T201-180v-570h-41v-60h188v-30h264v30h188v60h-41v570q0 24-18 42t-42 18H261Zm438-630H261v570h438v-570ZM367-266h60v-399h-60v399Zm166 0h60v-399h-60v399ZM261-750v570-570Z"/>
                         </svg>
                         <span class="flex-1 ml-3 text-left whitespace-nowrap">Waste</span>
                         <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                   </button>
                   <ul id="dropdown-waste" class="hidden py-2 space-y-2">
                         <li>
                            <a href="{{ route('waste.inventory.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Inventory</a>
                         </li>
                         <li>
                            <a href="{{ route('waste.menu.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Item</a>
                         </li>
                   </ul>
                </li>
                

                            <!-- leo end added -->
                <li>
                    <button type="button" class="{{ (Str::contains(url()->current(), url('/system-management'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}" aria-controls="smDropdown" data-collapse-toggle="smDropdown">
                        <i class="text-2xl text-gray-500 transition duration-75 uil uil-setting group-hover:text-gray-900"></i>
                        <span class="w-full ml-3 text-left whitespace-nowrap" sidebar-toggle-item>System Management</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                    </button>
                    <ul id="smDropdown" class="hidden py-2 space-y-2">
                        <li>
                            <a href="{{ route('settings.index') }}" class="flex items-center p-2 pl-10 ml-5 text-base font-normal text-gray-900 transition duration-75 border border-white rounded-lg w-46 group hover:bg-white hover:shadow-md hover:border-gray-200">
                                Branch 
                            </a>
                        </li>                        
                        <li>
                            <a href="{{ route('user.index') }}" class="{{ (Str::contains(url()->current(), url('/system-management/user'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                                Users
                            </a>
                        </li>
                        
                        <li>
                            <a href="{{ route('menu.category.index') }}" class="{{ (Str::contains(url()->current(), url('/system-management/menu-category'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                                Item Category
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('brand.index') }}" class="flex items-center p-2 pl-10 ml-5 text-base font-normal text-gray-900 transition duration-75 border border-white rounded-lg w-46 group hover:bg-white hover:shadow-md hover:border-gray-200">
                                Item Brand 
                            </a>
                        </li>   
                         <!-- ken added added new menu button for MOP 252306001 -- 3-29-2026 -->
                        <li>
                            <a href="{{ route('mode-of-payment.index') }}" class="{{ (Str::contains(url()->current(), url('/system-management/mode-of-payment'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                                Mode of Payment
                            </a>
                        </li>
                        <!-- end  here -->   
                        <li>
                            <a href="{{ route('exp.cat.index') }}" class="{{ (Str::contains(url()->current(), url('/system-management/exp-cat'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                                Expenses Category
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('department.index') }}" class="{{ (Str::contains(url()->current(), url('/system-management/department'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                                Department List
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('supp.index') }}" class="{{ (Str::contains(url()->current(), url('/system-management/supp'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                                Suppliers List
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('table.index') }}" class="{{ (Str::contains(url()->current(), url('/system-management/table'))) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                                Client List
                            </a>
                        </li>

                     
                    </ul>
                </li>
                <li>
                    <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-lr" data-collapse-toggle="dropdown-lr">
                          <i class="text-2xl text-gray-500 uil uil-newspaper"></i>
                          <span class="flex-1 ml-3 text-left whitespace-nowrap">Low Stock Report</span>
                          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                             <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                          </svg>
                    </button>
                    <ul id="dropdown-lr" class="hidden py-2 space-y-2">
                          <li>
                             <a href="{{ route('report.inventory.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Inventory</a>
                          </li>
                    </ul>
                </li>
                {{--<li>
                   <a href="{{ route('pricingReport') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                      
                         <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                            <path xmlns="http://www.w3.org/2000/svg" d="M560-574v-48q33-14 67.5-21t72.5-7q26 0 51 4t49 10v44q-24-9-48.5-13.5T700-610q-38 0-73 9.5T560-574Zm0 220v-49q33-13.5 67.5-20.25T700-430q26 0 51 4t49 10v44q-24-9-48.5-13.5T700-390q-38 0-73 9t-67 27Zm0-110v-48q33-14 67.5-21t72.5-7q26 0 51 4t49 10v44q-24-9-48.5-13.5T700-500q-38 0-73 9.5T560-464ZM248-300q53.566 0 104.283 12.5T452-250v-427q-45-30-97.619-46.5Q301.763-740 248-740q-38 0-74.5 9.5T100-707v434q31-14 70.5-20.5T248-300Zm264 50q50-25 98-37.5T712-300q38 0 78.5 6t69.5 16v-429q-34-17-71.822-25-37.823-8-76.178-8-54 0-104.5 16.5T512-677v427Zm-30 90q-51-38-111-58.5T248-239q-36.537 0-71.768 9Q141-221 106-208q-23.1 11-44.55-3Q40-225 40-251v-463q0-15 7-27.5T68-761q42-20 87.395-29.5Q200.789-800 248-800q63 0 122.5 17T482-731q51-35 109.5-52T712-800q46.868 0 91.934 9.5Q849-781 891-761q14 7 21.5 19.5T920-714v463q0 27.894-22.5 42.447Q875-194 853-208q-34-14-69.232-22.5Q748.537-239 712-239q-63 0-121 21t-109 58ZM276-489Z"/>
                         </svg>
                      <span class="ml-3">Pricing Report</span>
                   </a>
                </li>
                <li>
                   <a href="{{ route('financialReport') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                      
                         <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                            <path xmlns="http://www.w3.org/2000/svg" d="M540-420q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM220-280q-24.75 0-42.375-17.625T160-340v-400q0-24.75 17.625-42.375T220-800h640q24.75 0 42.375 17.625T920-740v400q0 24.75-17.625 42.375T860-280H220Zm100-60h440q0-42 29-71t71-29v-200q-42 0-71-29t-29-71H320q0 42-29 71t-71 29v200q42 0 71 29t29 71Zm480 180H100q-24.75 0-42.375-17.625T40-220v-460h60v460h700v60ZM220-340v-400 400Z"/>
                         </svg>
                      <span class="ml-3">Financial Report</span>
                   </a>
                </li>--}}
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center p-2 text-base font-normal text-gray-900 border border-white rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200">
                            <i class="text-2xl text-gray-500 uis uis-signout"></i>
                            <span class="flex-1 ml-3 whitespace-nowrap">Sign Out</span>
                        </a>
                    </form>
                </li>
            </ul>
        </div>
     </aside>
<!-- </div> -->
     













    <!-- Primary Navigation Menu -->
    {{-- <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block w-auto text-gray-800 fill-current h-9" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="flex items-center -mr-2 sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div> --}}
</nav>
