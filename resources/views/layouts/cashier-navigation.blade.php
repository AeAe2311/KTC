<div class="flex justify-between h-12 bg-white shadow">
    <div class="flex items-center">
        <button class="flex items-center justify-center w-12 h-12 text-gray-800 hover:bg-gray-100" type="button" data-drawer-target="drawer-navigation" data-drawer-show="drawer-navigation" aria-controls="drawer-navigation">
            <i class="text-3xl uil uil-bars"></i>
        </button>
        <h2 class="text-base font-semibold leading-tight text-gray-600">
            @yield('page_title')
        </h2>
    </div>
    
      
   <div class="text-1xl h-12">
       @if (auth()->user()->role == 12)
         <span >Serve : <span class="text-red-600" id="serveAmount"></span> <span> Not Served (SS) : <span class="text-red-600" id="notServeAmount"></span></span></span>
      @endif
   </div>
   
       <form method="POST" action="{{ route('logout') }}" class="flex w-12 h-12 bg-red-600 hover:bg-red-700">
          @csrf
          
          <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center justify-center w-full h-full">
                <i class="text-3xl text-white uis uil-sign-out-alt"></i>
          </a>
          
       </form>
       
      
</div>


<!-- drawer component -->
<div id="drawer-navigation" class="fixed top-0 left-0 z-40 w-64 h-screen p-4 overflow-x-hidden overflow-y-auto transition-transform -translate-x-full bg-white" tabindex="-1" aria-labelledby="drawer-navigation-label">
    <h5 id="drawer-navigation-label" class="text-base font-semibold text-gray-500 uppercase">Menu</h5>
    <button type="button" data-drawer-hide="drawer-navigation" aria-controls="drawer-navigation" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center" >
        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        <span class="sr-only">Close menu</span>
    </button>
  <div class="pt-4 overflow-y-auto overflow-x-hidden flex flex-col justify-between h-[calc(100vh-72px)]">
      <ul class="space-y-2 font-medium">
         <li>
            <a href="{{ route('pos.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="text-2xl text-gray-500 uil uil-monitor"></i>
               <span class="ml-3">Orders</span>
            </a>
         </li>
         <li>
            <a href="{{ route('orders.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="text-2xl text-gray-500 uil uil-utensils-alt"></i>
               <span class="ml-3">Tables</span>
            </a>
         </li>
         
         @if (auth()->user()->role != 7)
         
         {{--
         <li>
            <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-ar" data-collapse-toggle="dropdown-ar">
                  <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" data-name="Layer 1" viewBox="0 0 24 24" id="layers"><path fill="#1e1e26" d="M2.5,10.56l9,5.2a1,1,0,0,0,1,0l9-5.2a1,1,0,0,0,0-1.73l-9-5.2a1,1,0,0,0-1,0l-9,5.2a1,1,0,0,0,0,1.73ZM12,5.65l7,4-7,4.05L5,9.69Zm8.5,7.79L12,18.35,3.5,13.44a1,1,0,0,0-1.37.36,1,1,0,0,0,.37,1.37l9,5.2a1,1,0,0,0,1,0l9-5.2a1,1,0,0,0,.37-1.37A1,1,0,0,0,20.5,13.44Z"></path></svg>
                  

                  <span class="flex-1 ml-3 text-left whitespace-nowrap">Account Receivables</span>
                  <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                     <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                  </svg>
            </button>
            <ul id="dropdown-ar" class="hidden py-2 space-y-2">
                  <li>
                     <a href="{{ route('ar.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Unpaid</a>
                  </li>
                  <li>
                     <a href="{{ route('ar.indexpaid') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Paid</a>
                  </li>
                  <li>
                     <a href="{{ route('arlogs.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Payment Logs</a>
                  </li>
            </ul>
         </li>
         --}}
         
         
         <li>
            <a href="{{ route('transactions.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                <svg aria-hidden="true" class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
               <span class="ml-3">Transactions</span>
            </a>
         </li>
         {{--<li>
             <a href="{{ route('areport.index') }}" class="{{ (Str::contains(url()->current(), url('/reports'))) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200';}}">
                 <svg aria-hidden="true" class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900" fill="currentColor" viewBox="0 -960 960 960" xmlns="http://www.w3.org/2000/svg"><path xmlns="http://www.w3.org/2000/svg" d="M180-120q-24 0-42-18t-18-42v-600q0-24 18-42t42-18h462l198 198v462q0 24-18 42t-42 18H180Zm0-60h600v-428.571H609V-780H180v600Zm99-111h402v-60H279v60Zm0-318h201v-60H279v60Zm0 159h402v-60H279v60Zm-99-330v171.429V-780v600-600Z"/></svg>
                 <span class="flex-1 ml-3 whitespace-nowrap">Reports</span>
             </a>
         </li>--}}
         {{--<li>
            <a href="{{ route('kitchen.display') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="text-2xl text-gray-500 uil uil-monitor"></i>
               <span class="ml-3">Kitchen Display</span>
            </a>
         </li>--}}
         {{--<li>
            <a href="{{ route('menu.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="text-2xl text-gray-500 uil uil-utensils-alt"></i>
               <span class="ml-3">Menu Preparation</span>
            </a>
         </li>
         <li>
            <a href="{{ route('inventory.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                  <path d="M120-142v-492q-14-2-27-20t-13-39v-127q0-23 18-41.5t42-18.5h680q23 0 41.5 18.5T880-820v127q0 21-13 39t-27 20v492q0 23-18.5 42.5T780-80H180q-24 0-42-19.5T120-142Zm60-491v493h600v-493H180Zm640-60v-127H140v127h680ZM360-423h240v-60H360v60ZM180-140v-493 493Z"/>
               </svg>
               <span class="ml-3">Raw Materials</span>
            </a>
         </li>
         <li>
            <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-waste" data-collapse-toggle="dropdown-waste">
                  <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                     <path xmlns="http://www.w3.org/2000/svg" d="M261-120q-24.75 0-42.375-17.625T201-180v-570h-41v-60h188v-30h264v30h188v60h-41v570q0 24-18 42t-42 18H261Zm438-630H261v570h438v-570ZM367-266h60v-399h-60v399Zm166 0h60v-399h-60v399ZM261-750v570-570Z"/>
                  </svg>
                  <span class="flex-1 ml-3 text-left whitespace-nowrap">Waste</span>
                  <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                     <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                  </svg>
            </button>
            <ul id="dropdown-waste" class="hidden py-2 space-y-2">
                  <li>
                     <a href="{{ route('waste.inventory.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Raw Materials</a>
                  </li>
                  <li>
                     <a href="{{ route('waste.menu.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Menu</a>
                  </li>
            </ul>
         </li>
         <li>
            <a href="{{ route('expenses.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                <!-- <i class="text-2xl text-gray-500 uil uil-monitor"></i>  -->
                  <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                     <path xmlns="http://www.w3.org/2000/svg" d="M540-420q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM220-280q-24.75 0-42.375-17.625T160-340v-400q0-24.75 17.625-42.375T220-800h640q24.75 0 42.375 17.625T920-740v400q0 24.75-17.625 42.375T860-280H220Zm100-60h440q0-42 29-71t71-29v-200q-42 0-71-29t-29-71H320q0 42-29 71t-71 29v200q42 0 71 29t29 71Zm480 180H100q-24.75 0-42.375-17.625T40-220v-460h60v460h700v60ZM220-340v-400 400Z"/>
                  </svg>
               <span class="ml-3">Expenses</span>
            </a>
         </li>--}}
         <!-- Expenses and Revolving Section-->
                 
                <li>
                   <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 " aria-controls="dropdown-expenses" data-collapse-toggle="dropdown-expenses">
                        <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                            <path xmlns="http://www.w3.org/2000/svg" d="M540-420q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM220-280q-24.75 0-42.375-17.625T160-340v-400q0-24.75 17.625-42.375T220-800h640q24.75 0 42.375 17.625T920-740v400q0 24.75-17.625 42.375T860-280H220Zm100-60h440q0-42 29-71t71-29v-200q-42 0-71-29t-29-71H320q0 42-29 71t-71 29v200q42 0 71 29t29 71Zm480 180H100q-24.75 0-42.375-17.625T40-220v-460h60v460h700v60ZM220-340v-400 400Z"/>
                        </svg>
                        <span class="flex-1 ml-3 text-left whitespace-nowrap">Expenses Management</span>
                        <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                   </button>
                   <ul id="dropdown-expenses" class="hidden py-2 space-y-2">
                         <li>
                            <a href="{{ route('expenses.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">General Expenses</a>
                         </li>
                         
                         
                         <li>
                            <a href="{{ route('revolving.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Revolving Budget</a>
                         </li>
                         {{--<li>
                            <a href="{{ route('e_request.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Expenses Approval</a>
                         </li>
                         <li>
                            <a href="{{ route('staff.clocking') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Time In/Out</a>
                         </li>
                         <li>
                            <a href="{{ route('clock.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Time logs</a>
                         </li>--}}
                   </ul>
                </li>
                
                <!-- End of Expenses and Revolving Section -->
                {{--<li>
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
                </li>--}}
                
         @endif
         {{--<li>
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
                  <li>
                     <a href="{{ route('report.menu.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">Menu</a>
                  </li>
            </ul>
         </li>
         <li>
            <a href="{{ route('pricingReport') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                <!-- <i class="text-2xl text-gray-500 uil uil-monitor"></i>  -->
                  <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                     <path xmlns="http://www.w3.org/2000/svg" d="M560-574v-48q33-14 67.5-21t72.5-7q26 0 51 4t49 10v44q-24-9-48.5-13.5T700-610q-38 0-73 9.5T560-574Zm0 220v-49q33-13.5 67.5-20.25T700-430q26 0 51 4t49 10v44q-24-9-48.5-13.5T700-390q-38 0-73 9t-67 27Zm0-110v-48q33-14 67.5-21t72.5-7q26 0 51 4t49 10v44q-24-9-48.5-13.5T700-500q-38 0-73 9.5T560-464ZM248-300q53.566 0 104.283 12.5T452-250v-427q-45-30-97.619-46.5Q301.763-740 248-740q-38 0-74.5 9.5T100-707v434q31-14 70.5-20.5T248-300Zm264 50q50-25 98-37.5T712-300q38 0 78.5 6t69.5 16v-429q-34-17-71.822-25-37.823-8-76.178-8-54 0-104.5 16.5T512-677v427Zm-30 90q-51-38-111-58.5T248-239q-36.537 0-71.768 9Q141-221 106-208q-23.1 11-44.55-3Q40-225 40-251v-463q0-15 7-27.5T68-761q42-20 87.395-29.5Q200.789-800 248-800q63 0 122.5 17T482-731q51-35 109.5-52T712-800q46.868 0 91.934 9.5Q849-781 891-761q14 7 21.5 19.5T920-714v463q0 27.894-22.5 42.447Q875-194 853-208q-34-14-69.232-22.5Q748.537-239 712-239q-63 0-121 21t-109 58ZM276-489Z"/>
                  </svg>
               <span class="ml-3">Pricing Report</span>
            </a>
         </li>
         <li>
            <a href="{{ route('financialReport') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                <!-- <i class="text-2xl text-gray-500 uil uil-monitor"></i>  -->
                  <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 -960 960 960">
                     <path xmlns="http://www.w3.org/2000/svg" d="M540-420q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM220-280q-24.75 0-42.375-17.625T160-340v-400q0-24.75 17.625-42.375T220-800h640q24.75 0 42.375 17.625T920-740v400q0 24.75-17.625 42.375T860-280H220Zm100-60h440q0-42 29-71t71-29v-200q-42 0-71-29t-29-71H320q0 42-29 71t-71 29v200q42 0 71 29t29 71Zm480 180H100q-24.75 0-42.375-17.625T40-220v-460h60v460h700v60ZM220-340v-400 400Z"/>
                  </svg>
               <span class="ml-3">Financial Report</span>
            </a>
         </li>--}}
         {{-- <li>
            <a href="{{ route('report.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="text-2xl text-gray-500 uil uil-newspaper"></i>
               <span class="ml-3">Low Stock Report</span>
            </a>
         </li> 
          <!--   4-6 harold -->
            <li>
                <button type="button"
                    class="{{ Str::contains(url()->current(), url('/procurementrequest')) || Str::contains(url()->current(), url('/procurementApproved')) || Str::contains(url()->current(), url('/procurementtransfer')) ? 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg hover:bg-white hover:shadow-md hover:border-gray-200 bg-gray-200 border border-gray-300 shadow-inner' : 'flex items-center p-2 text-base font-normal text-gray-900 rounded-lg border border-white hover:bg-white hover:shadow-md hover:border-gray-200' }}"
                    aria-controls="procurementTransferDropdown" data-collapse-toggle="procurementTransferDropdown">
                    <svg class="text-2xl text-gray-500 transition duration-75 uil uil-folder-transfer group-hover:text-gray-900"
                        xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M7 11h.625c1.756 0 2.633 0 3.264-.421a2.5 2.5 0 0 0 .69-.69C12 9.259 12 8.38 12 6.625c0-1.053 0-1.58-.253-1.958a1.5 1.5 0 0 0-.414-.414C10.955 4 10.428 4 9.375 4H7.714c-.144 0-.217 0-.283-.01a.9.9 0 0 1-.613-.377a2 2 0 0 1-.135-.246c-.262-.525-.502-1.053-1.083-1.271C5.345 2 5.054 2 4.472 2c-.908 0-1.362 0-1.703.19a1.5 1.5 0 0 0-.579.579C2 3.109 2 3.564 2 4.472V6c0 2.357 0 3.536.732 4.268S4.643 11 7 11m10 11h-.625c-1.756 0-2.633 0-3.264-.421a2.5 2.5 0 0 1-.69-.69C12 20.259 12 19.38 12 17.625c0-1.053 0-1.58.253-1.958a1.5 1.5 0 0 1 .414-.414c.378-.253.905-.253 1.958-.253h1.661c.144 0 .217 0 .283-.01a.9.9 0 0 0 .613-.377a2 2 0 0 0 .135-.246c.262-.525.502-1.053 1.083-1.272c.255-.095.546-.095 1.128-.095c.908 0 1.362 0 1.703.19c.243.136.443.336.579.579c.19.34.19.795.19 1.703V17c0 2.357 0 3.535-.732 4.268C20.535 22 19.357 22 17 22m2.336-13H22c-.5-4-3-6.5-6-7M4.664 15H2c.5 4 3 6.5 6 7"
                            color="currentColor" />
                    </svg>
                    <!-- <i class="text-2xl text-gray-500 transition duration-75 uil uil-folder-transfer group-hover:text-gray-900"></i> -->
                    <span class="flex-1 ml-3 text-left whitespace-nowrap w-36 mr-2.5"
                        sidebar-toggle-item>Procurement</span>
                    <i class="text-2xl text-gray-500 uil uil-angle-down"></i>
                </button>
                <ul id="procurementTransferDropdown" class="hidden py-2 space-y-2">
                    <li>
                        <a href="{{ route('procurementrequest.index') }}"
                            class="{{ Str::contains(url()->current(), url('/procurementrequest')) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200' }}">
                            Request
                        </a>
                    </li>
                    <!--    <li>
                      <a href="{{ route('procurementtransfer.index') }}" class="{{ Str::contains(url()->current(), url('/procurementtransfer')) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200' }}">
                      For Approval
                     </a>
                 </li>
                    </li>-->
                    <li>
                        <a href="{{ route('procurementtransfer.approvedIndex') }}"
                            class="{{ Str::contains(url()->current(), url('/procurementApproved')) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200' }}">
                            Approved
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('procurementreceived.receivedIndex') }}"
                            class="{{ Str::contains(url()->current(), url('/procurementreceived')) ? 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border hover:bg-white hover:shadow-md hover:border-gray-200 shadow-inner border-gray-300 bg-gray-200' : 'flex items-center w-46 p-2 text-base font-normal text-gray-900 transition duration-75 rounded-lg ml-5 pl-10 group border border-white hover:bg-white hover:shadow-md hover:border-gray-200' }}">
                            Received
                        </a>
                    </li>

                </ul>
            </li>
            --}}
        

            <!--   4-6 harold end -->
      </ul>
      {{--<a href="{{ route('pos.send') }}" id="sendSSButton" class="text-sm text-blue-600 underline">Send Sales Summary</a>--}}
   </div>
</div>