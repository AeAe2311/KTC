<div class="flex justify-between bg-white h-12 shadow">
    <div class="flex items-center">
        <button class="text-gray-800 hover:bg-gray-100 flex items-center justify-center w-12 h-12" type="button" data-drawer-target="drawer-navigation" data-drawer-show="drawer-navigation" aria-controls="drawer-navigation">
            <i class="uil uil-bars text-3xl"></i>
        </button>
        <h2 class="font-semibold text-base text-gray-600 leading-tight">
            @yield('page_title')
        </h2>
    </div>
    <form method="POST" action="{{ route('logout') }}" class="h-12 w-12 flex bg-red-600 hover:bg-red-700">
        @csrf
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="h-full w-full flex items-center justify-center">
            <i class="uis uil-sign-out-alt text-3xl text-white"></i>
        </a>
    </form>
</div>


<!-- drawer component -->
<div id="drawer-navigation" class="fixed top-0 left-0 z-40 w-64 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white" tabindex="-1" aria-labelledby="drawer-navigation-label">
    <h5 id="drawer-navigation-label" class="text-base font-semibold text-gray-500 uppercase">Menu</h5>
    <button type="button" data-drawer-hide="drawer-navigation" aria-controls="drawer-navigation" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center" >
        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        <span class="sr-only">Close menu</span>
    </button>
  <div class="py-4 overflow-y-auto">
      <ul class="space-y-2 font-medium">
         
         <li>
            <a href="{{ route('inventory.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="uil uil-utensils-alt text-2xl text-gray-500"></i>
               <span class="ml-3">Raw Inventory</span>
            </a>
         </li>
         <li>
            <a href="{{ route('menu.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="uil uil-folder text-2xl text-gray-500"></i>
               <span class="ml-3">Menu Preparation</span>
            </a>
         </li>
         <li>
            <a href="{{ route('kitchen.display') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="uil uil-monitor text-2xl text-gray-500"></i>
               <span class="ml-3">Kitchen Display</span>
            </a>
         </li>


         <li>
            <a href="{{ route('request.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="uil uil-file-question text-2xl text-gray-500"></i>
               <span class="ml-3">Request</span>
            </a>
         </li>
        {{-- @if (Auth::user()->role != 3)--}}
         <li>
            <a href="{{ route('transfer.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="uil uil-file-check text-2xl text-gray-500"></i>
               <span class="ml-3">For Approval</span>
            </a>
         </li>
         
         <li>
            <a href="{{ route('transfer.approvedIndex') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
               <i class="uil uil-folder-check text-2xl text-gray-500"></i>
               <span class="ml-3">Approved</span>
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


            <!--   4-6 harold end -->
      </ul>
   </div>
</div>