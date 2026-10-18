<x-app-layout>

    <style>
        #menuTabs::-webkit-scrollbar {
            height: 0px;
            background: transparent; 
        }

        #categoryAllContents::-webkit-scrollbar {
            width: 0px;
            background: transparent;
        }
    </style>

    @section('page_title', '')

    {{-- LOADING --}}
        <div wire:loading id="loadingScreen" class="hidden fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-[60] overflow-hidden bg-gray-900 opacity-75 opa flex flex-col items-center justify-center">
            <div role="status">
                <svg aria-hidden="true" class="inline w-10 h-10 mr-2 text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
            <h2 class="text-center text-white text-xl font-semibold">Processing...</h2>
            <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
        </div>
    {{-- LOADING END --}}

    {{-- NOTIF --}}
        <div id="notifDiv" class="absolute top-16 left-1/2 -translate-x-1/2 z-50 w-[400px]">
        </div>
    {{-- NOTIF END --}}


    {{-- Request CONFIRM MODAL --}}
        <!-- Modal toggle -->
        
    
        <!-- Main modal -->
        <div id="reqConfirmModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative w-full max-w-2xl max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->   
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            <i class="uil uil-exclamation-circle text-blue-500 text-3xl mr-3"></i>Request Confirmation
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="reqConfirmModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-4">
                        <p class="text-base leading-relaxed text-gray-500">
                            Are you sure you want to submit this request?
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center p-6 space-x-6 border-t border-gray-200 rounded-b">
                        <button id="submitButton"  data-modal-hide="reqConfirmModal" data-slug="" type="button" class="text-white bg-green-600 hover:bg-green-500 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm py-5 text-center w-1/2">Accept</button>
                        <button data-modal-hide="reqConfirmModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-5 hover:text-gray-900 focus:z-10 w-1/2">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- Request CONFIRM MODAL END --}}






    <div  class="h-screen p-4  gap-4 {{ (Auth::user()->role == 1) ? 'lg:ml-64' : '' }}">

        {{-- LEFT CONTENT --}}
        
        <div class="h-3/4 w-full xl:w-auto xl:col-span-2 bg-white shadow-lg rounded-lg border border-gray-200 overflow-hidden sm:pt-10">
                <div>
                    <div class="p-2 flex items-center justify-between">
                        @csrf   
                        <div class="relative w-full sm:w-full md:w-full lg:w-1/2">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
                            </div>
                            <input type="text" id="searchMenu" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-full focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Search" required autocomplete="off">
                        </div>
                        {{--<button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button" class="relative inline-flex items-center p-1.5 text-sm font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-800 hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" height="30" viewBox="0 -960 960 960" width="30" fill="currentColor"><path d="M220-80q-24 0-42-18t-18-42v-520q0-24 18-42t42-18h110v-10q0-63 43.5-106.5T480-880q63 0 106.5 43.5T630-730v10h110q24 0 42 18t18 42v520q0 24-18 42t-42 18H220Zm0-60h520v-520H630v90q0 12.75-8.675 21.375-8.676 8.625-21.5 8.625-12.825 0-21.325-8.625T570-570v-90H390v90q0 12.75-8.675 21.375-8.676 8.625-21.5 8.625-12.825 0-21.325-8.625T330-570v-90H220v520Zm170-580h180v-10q0-38-26-64t-64-26q-38 0-64 26t-26 64v10ZM220-140v-520 520Z"/></svg>
                            <span class="sr-only">Notifications</span>
                            <div id="ordersCount" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-2 -right-2">{{ $orders->count() }}</div>
                        </button>--}}
                    </div>
                    <div class="p-2 border-b border-neutral-300">
                        <div class="flex justify-between w-full">
                            <div class="p-px">
                                <button class="w-9 aspect-square border border-neutral-300 rounded-lg scroll-left">
                                    <i class="uil uil-arrow-left text-2xl"></i>
                                </button>
                            </div>
                            <div id="categoryTabsC" class="py-0.5 px-4 overflow-x-hidden w-[calc(100vw-920)]">
                                <div id="menuTabs" data-tabs-toggle="#categoryTabContent" role="tablist" class="overflow-x-auto flex gap-x-2 ">
                                    <button id="all-menu-tab" data-tabs-target="#all-menu" type="button" role="tab" aria-controls="all-menu" aria-selected="false" class="leading-8 px-4 font-semibold border rounded-lg whitespace-nowrap">
                                        All
                                    </button>
                                    @foreach ($categories as $category)
                                        <button id="{{ substr($category->name,0,3) .'-'. $category->id }}-tab" data-tabs-target="#{{ substr($category->name,0,3) .'-'. $category->id }}-menu" type="button" role="tab" aria-controls="{{ substr($category->name,0,3) .'-'. $category->id }}-menu" aria-selected="false" class="leading-8 px-4 font-semibold border rounded-lg whitespace-nowrap">
                                            {{$category->name}}
                                        </button>

                                        {{-- <button id="{{$category->name}}-tab" data-tabs-target="#{{$category->slug}}" type="button" role="tab" aria-controls="{{$category->slug}}" aria-selected="false" class="leading-8 px-4 font-semibold border rounded-lg whitespace-nowrap">
                                            {{$category->name}}
                                        </button> --}}
                                    @endforeach
                                </div>
                            </div>
                            <div class="p-px ">
                                <button class="w-9 aspect-square border border-neutral-300 rounded-lg scroll-right">
                                    <i class="uil uil-arrow-right text-2xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="categoryAllContents" class="h-[calc(100vh-194px)] overflow-y-auto">
                        <div id="categoryTabContent" class="h-auto min-h-full">
                            <div class="p-1" id="all-menu" role="tabpanel" aria-labelledby="all-menu-tab">
                                <div class="container mx-auto p-4 ">
                                    <div class="border-2 grid grid-cols-2 sm:grid-cols-2  md:grid-cols-3 lg:grid-cols-4 gap-4 font-bold text-xl   ">
                                        <div class="border-r-2 pl-2 hidden lg:block p-2">Item Name</div>
                                        <div class="border-r-2 text-center hidden lg:block p-2">Current Stock</div>
                                        <div class="border-r-2 text-center hidden lg:block p-2">Reorder Point</div>
                                        <div class="border-r-2 text-center hidden lg:block p-2">Request Stock</div>
                                    </div>
                                            @foreach ($menus as $menu)
                                                    <span class=" border grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4  items-center text-center {{ ($menu->quantity > $menu->reorder_point) ? '' : 'bg-red-100 shadow-red-300' }}">
                                                            <label class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Item :</label>

                                                            <a data-slug="{{$menu->slug}}" class="border-r-2 cols-span-1 pl-2 text-center  sm:text-center md:text-center lg:text-left xl:text-left">
                                                                <div>{{ $menu->name }}</div>
                                                                
                                                            </a>

                                                            <label class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Cur Qty :</label>

                                                            <div class="cols-span-1 text-center border-r-2">{{ number_format($menu->quantity) . ' ' . $menu->unit }}</div>

                                                            <label class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Reorder Qty :</label>

                                                            <div class="cols-span-1 text-center border-r-2">{{ number_format($menu->reorder_point) . ' ' . $menu->unit }} </div>

                                                            <label class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Req Qty :</label>
                                                            <div class="cols-span-1 flex justify-center border-r-2">
                                                            <input data-slug="{{$menu->slug}}" class="reqStockTxt {{ ($menu->quantity > $menu->reorder_point) ? 'bg-white border border-white' : 'bg-red-100 shadow-red-300 border border-red-100' }}  text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-3/4 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-3/4 pr-2 text-center"  placeholder="0" required type="number" name="" id="">
                                                            </div>
                                                    </span>       
                                            @endforeach
                                        
                                </div>
                            
                                {{--<div class="w-[300px] sm:w-[640px] xl:w-[880px] 2xl:w-[1200px] mx-auto grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-4 gap-5 justify-items-center content-center">
                                    @foreach ($menus as $menu)
                                        @if ($menu->is_hidden != 1)
                                            <a data-slug="{{$menu->slug}}" class="w-full h-40 p-3 bg-white border border-neutral-200 rounded-xl shadow-lg cursor-pointer hover:scale-105 transition-all menu bg-gray-200 flex">
                                                <div class=" h-full w-52 overflow-hidden  flex items-center rounded-xl mr-2 bg-blue-300">
                                                    <img src="{{ asset('storage/'.$menu->image) }}" alt="" class="rounded-lg h- mx-auto object-contain">
                                                </div>
                                                <div class="h-full w-full text-center ">
                                                    <div class="h-2/3 border-b border-neutral-400 flex items-center justify-center">
                                                        <p class="text-lg font-medium">
                                                            {{$menu->name}}
                                                        </p>
                                                    </div>
                                                    
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>--}}
                            </div>
                            @foreach ($categories as $category)
                                <div class="p-1" id="{{ substr($category->name,0,3) .'-'. $category->id }}-menu" role="tabpanel" aria-labelledby="{{ substr($category->name,0,3) .'-'. $category->id }}-menu-tab">
                                    <div class=" container mx-auto p-4">
                                        <div class="border-2 grid grid-cols-2 sm:grid-cols-2  md:grid-cols-3 lg:grid-cols-4 gap-4 font-bold text-xl   ">
                                            <div class="border-r-2 pl-2 hidden lg:block p-2">Item Name</div>
                                            <div class="border-r-2 text-center hidden lg:block p-2">Current Stock</div>
                                            <div class="border-r-2 text-center hidden lg:block p-2">Reorder Point</div>
                                            <div class="border-r-2 text-center hidden lg:block p-2">Request Stock</div>
                                        </div>
                                        @foreach ($menus as $menu)
                                            @if ($menu->category_id == $category->id)
                                            <span class="border grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4  items-center text-center {{ ($menu->quantity > $menu->reorder_point) ? '' : 'bg-red-100 shadow-red-300' }}">
                                                            <label class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Item :</label>

                                                            <a data-slug="{{$menu->slug}}" class="border-r-2 cols-span-1 pl-2 text-center  sm:text-center md:text-center lg:text-left xl:text-left">
                                                                <div>{{ $menu->name }}</div>
                                                                
                                                            </a>

                                                            <label class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Curent Stock :</label>

                                                            <div class="cols-span-1 text-center border-r-2">{{ number_format($menu->quantity) . ' ' . $menu->unit }}</div>

                                                            <label class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Reorder Point :</label>

                                                            <div class="cols-span-1 text-center border-r-2">{{ number_format($menu->reorder_point) . ' ' . $menu->unit }} </div>

                                                            <label class="font-bold cols-span-1 text-center md:block lg:hidden border-r-2">Request Stock :</label>
                                                            <div class="cols-span-1 flex justify-center border-r-2">
                                                            <input data-slug="{{$menu->slug}}" class="reqStockTxt inputNumber {{ ($menu->quantity > $menu->reorder_point) ? 'bg-white border border-white' : 'bg-red-100 shadow-red-300 border border-red-100' }}  text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-3/4 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-3/4 pr-2 text-center"  placeholder="0" required type="number" name="" id="">
                                                            </div>
                                                    </span>    
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        {{-- LEFT CONTENT --}}

        <button id="" type="button" data-modal-target="reqConfirmModal" data-modal-toggle="reqConfirmModal" class="disabled:opacity-50 disabled:pointer-events-none m-2 w-full text-gray-50 bg-gradient-to-r from-green-600 to-teal-500 hover:bg-gradient-to-bl font-medium rounded-lg text-sm px-5 py-2.5 text-center">Submit</button>

    </div>

    <script>
        $(document).ready(function() {
            //$('#table').val('');
            

            $('.scroll-left').click(function() {
                var divW = (($('#menuTabs').width() * 2) / 3);
                $('#menuTabs').animate({scrollLeft: '-=' + divW + 'px'}, 'slow');
            });
            
            $('.scroll-right').click(function() {
                var divW = (($('#menuTabs').width() * 2) / 3);
                $('#menuTabs').animate({scrollLeft: '+=' + divW + 'px'}, 'slow');
            });

            jQuery(document).on("click", ".menu", function(){
                var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var id = $('#table').val();

                $.ajax({
                    url:"{{ route('request.add') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        _token: _token
                    },
                    success:function(result){
                        $('#ordersCount').html(result.ordersCount);
                        $('#ordersDiv').html(result.orders);
                        $('#subTotal').html(result.subTotal);
                        $('#discountTotal').html(result.discount);
                        $('#total').html(result.qty);
                        $('#actualAmount').html(result.amount);
                        $('#actualAmount2').html(result.amount);
                        $('#sc_val').val(result.ServiceCharge);
                        $('#sctotal').html(result.ServiceCharge);
                        $('#payNowButton').data('amount', result.amount);
                        $('#notifDiv').html(result.thisNotif);
                        $('#scpercent').html(result.scPercent);
                        $('#limepercent').html(result.limeDiscountPercent);
                        $('#limediscountTotal').html(result.limeDiscountVal);
                    }
                })

            });



            $('#searchMenu').keyup(function(){
                $('#all-menu-tab').click();
                var value = $(this).val().toLowerCase();
                $("#all-menu span").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            $('#deleteDiscountButton').click(function(){
                $('#loadingScreen').removeClass('hidden');
            });
            $('#submitDiscountButton').click(function(){
                $('#loadingScreen').removeClass('hidden');
            });

            $('#sendSSButton').click(function(){
                $('#loadingScreen').removeClass('hidden');
            })

            jQuery(document).on( "keyup", ".reqStockTxt", function(){
                var val = $(this).val().replace(/[^0-9.]/g, '');
                $(this).val(val);
                
            });

            jQuery(document).on("change", ".reqStockTxt", function(){
                var slug = $(this).data('slug');
                var qty = $(this).val();
                var _token = $('input[name="_token"]').val();
                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url:"{{ route('request.forecast') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        slug: slug,
                        qty : qty,
                        _token: _token
                    },
                    success:function(result){
                        $('#loadingScreen').addClass('hidden');
                    }
                })
            });

            $('#submitButton').click(function(){
                
                var _token = $('input[name="_token"]').val();
                $('#loadingScreen').removeClass('hidden');
                $.ajax({
                    url:"{{ route('request.submit') }}",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        _token: _token
                    },
                    success:function(result){
                        var qty = $('.reqStockTxt').val('');
                        $('#loadingScreen').addClass('hidden');
                    }
                })
            });
        });
    </script>
</x-app-layout>
