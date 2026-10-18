<x-app-layout>

    @section('page_title', 'EXPENSES - EDIT')

    <div class="p-3 lg:pt-3 lg:ml-64">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-3">
                <div class="">
                    <form action="{{ route('expenses.update') }}" method="POST" enctype="multipart/form-data" class="grid" class="">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        {{-- <div class="mb-2">
                            <label for="item_code" class="block text-sm font-medium text-gray-900 lg:text-base">Item Code</label>
                            <input type="text" id="item_code" name="item_code" value="{{ $item->item_code }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base">
                        </div> --}}
                        <div class="mb-2">
                            <label for="name" class="block text-sm font-medium text-gray-900 lg:text-base">Item Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ $item->remarks }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" required>
                        </div>
                        {{-- <div class="mb-2">
                            <label for="category_id" class="block text-sm font-medium text-gray-900 lg:text-base">Category <span class="text-red-500">*</span></label>
                            <select id="category_id" name="category_id" value="{{ $item->category_id }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                                @foreach ($categories as $category)
                                    <option {{ ($item->category_id == $category->id) ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}

                        <div class="mb-2">
                            <label for="quantity" class="block text-sm font-medium text-gray-900 lg:text-base">Quantity <span class="text-red-500">*</span></label>
                            <input type="text" id="quantity" name="quantity" value="{{ $item->quantity }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" required>
                        </div>

                        {{-- <div class="mb-2">
                            <label for="reorder_point" class="block text-sm font-medium text-gray-900 lg:text-base">Reorder Point<span class="text-red-500">*</span></label>
                            <input type="text" id="reorder_point" name="reorder_point" value="{{ $item->reorder_point }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" required>
                        </div> --}}
    
                        {{-- <div class="mb-5">
                            <label for="unit" class="block text-sm font-medium text-gray-900 lg:text-base">Unit <span class="text-red-500">*</span></label>
                            <select id="unit" name="unit" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                                <option {{ ($item->unit == 'kg') ? 'selected' : '' }} value="kg">kilogram (kg)</option>
                                <option {{ ($item->unit == 'ea') ? 'selected' : '' }} value="ea">each (ea)</option>
                            </select>
                        </div> --}}

                        <div class="mb-2">
                            <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Price</label>
                            <input type="text" id="price" name="price" value="{{ $item->amount }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base">
                        </div>

                        <div class="mb-2">
                        <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Category <span class="text-red-500">*</span></label>
                        @php
                                    echo
                                        '<div id="categorydiv" class="mb-5 flex flex-row gap-x-3">
                                                <div class="wrapper w-full relative">
                                                    <div class="select-btn flex items-center justify-between rounded-md bg-gray-100 border border-gray-300 p-2 h-9 cursor-pointer">
                                                        <span>'.$item->ExpensesCategory->name.'</span>
                                                        <i class="uil uil-angle-down text-2xl transition-transform duration-300"></i>
                                                    </div>
                                                    <div class="content bg-gray-100 mt-1 rounded-md p-3 hidden absolute w-full z-50">
                                                        <div class="search relative">
                                                            <i class="uil uil-search absolute left-3 leading-9 text-gray-500"></i>
                                                            <input type="text" class="selectSearch w-full leading-9 text-gray-700 rounded-md pl-9 outline-none h-9" placeholder="Search">
                                                        </div>
                                                        <ul class="listOption options mt-2 max-h-52 overflow-y-auto">
                                                            <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="'.$item->id.'" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">None</li>
                                                            ';
                                                            foreach ($expcats as $expcat){
                                                                echo '<li data-id="'.$expcat->id.'" data-name="MENU-'.$expcat->name.'" data-unit="'.$expcat->name.'" data-is_menu="'.$expcat->name.'" data-idnum="'.$expcat->id.'" class=" cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">'.$expcat->name.'</li>';
                                                            }
                                                        echo '</ul>
                                                    </div>
                                                    <input type="hidden" name="category" value="'.$item->ExpensesCategory->name.','.$item->category_id.'">
                                                </div>
                                        </div>';

                        @endphp
                    </div>
                    <div class="mb-2">
                        <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Supplier <span class="text-red-500">*</span></label>
                        @php
                                    echo
                                        '<div id="Supplierdiv" class="mb-5 flex flex-row gap-x-3">
                                                <div class="wrapper w-full relative">
                                                    <div class="select-btn flex items-center justify-between rounded-md bg-gray-100 border border-gray-300 p-2 h-9 cursor-pointer">
                                                        <span>'.$item->Supplier->name.'</span>
                                                        <i class="uil uil-angle-down text-2xl transition-transform duration-300"></i>
                                                    </div>
                                                    <div class="content bg-gray-100 mt-1 rounded-md p-3 hidden absolute w-full z-50">
                                                        <div class="search relative">
                                                            <i class="uil uil-search absolute left-3 leading-9 text-gray-500"></i>
                                                            <input type="text" class="selectSearch w-full leading-9 text-gray-700 rounded-md pl-9 outline-none h-9" placeholder="Search">
                                                        </div>
                                                        <ul class="listOption options mt-2 max-h-52 overflow-y-auto">
                                                            <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="'.$item->id.'" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">None</li>
                                                            ';
                                                            foreach ($supps as $supp){
                                                                echo '<li data-id="'.$supp->id.'" data-name="MENU-'.$supp->name.'" data-unit="'.$supp->name.'" data-is_menu="'.$supp->name.'" data-idnum="'.$supp->id.'" class=" cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">'.$supp->name.'</li>';
                                                            }
                                                        echo '</ul>
                                                    </div>
                                                    <input type="hidden" name="supplier" value="'.$item->Supplier->name.','.$item->supplier_id.'">
                                                </div>
                                        </div>';

                        @endphp
                    </div>
                    
                    

                        <div class="mb-6">
                            <label for="" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                            <div class="flex items-center gap-x-10">
                                <div class="flex items-center">
                                    <input {{ ($item->is_paid == 1) ? 'checked' : '' }} id="paid" type="radio" value="1" name="status" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="paid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Paid</label>
                                </div>
                                <div class="flex items-center">
                                    <input {{ ($item->is_paid == 1) ? '' : 'checked' }} id="unpaid" type="radio" value="0" name="status" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="unpaid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Unpaid</label>
                                </div>
                            </div>
                        </div>

                        @if (Auth::user()->role == 1)
                            <div class="mb-2">
                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                                <div class="relative max-w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <input datepicker type="text" name="dateAdd" value="{{ date('m/d/Y') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                                </div>
                            </div>
                        @endif
                        
                        {{-- <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-900 lg:text-base" for="image">Image</label>
                            <input class="px-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none lg:text-base" id="image" name="image" type="file" accept="image/*">
                        </div> --}}
                        
                        <div class="flex justify-between">
                            <a href="{{ Auth::user()->role == 5 ? route('e_request.index') : route('expenses.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                            <button type="submit" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold">UPDATE</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
     </div>

     <script>
        // $(document).ready(function() {
        //     $('.inputNumber').on('keypress keyup', function(event){
        //         var regex = /^[0-9.]+$/;
        //         var value = $(this).val() + String.fromCharCode(event.keyCode);
        //         if (!regex.test(value)) {
        //         event.preventDefault();
        //         return false;
        //         }
        //         if ((event.keyCode == 46) && ($(this).val().indexOf('.') >= 0)) {
        //         event.preventDefault();
        //         return false;
        //         }
        //     });
            // harold allow negative 5-25
         $('.inputNumber').on('keypress', function (event) {
                    const char = String.fromCharCode(event.which);
                    let value = $(this).val();
        
                    // allow numbers, dot, minus
                    if (!/[0-9.\-]/.test(char)) {
                        event.preventDefault();
                        return false;
                    }
        
                    // only one dot
                    if (char === '.' && value.includes('.')) {
                        event.preventDefault();
                        return false;
                    }
        
                    // minus only at start and only one
                    if (char === '-') {
                        if (value.length > 0 || value.includes('-')) {
                            event.preventDefault();
                            return false;
                        }
                    }
                });
                // harold allow negative 5-25 end
        
        
            $("#type").change(function(){
                if ($('#type').val() == 'Others'){
                    $("#type_txt").attr('type','text');
                    $('#type_txt').prop('required',true);
                } else {
                    $("#type_txt").attr('type','hidden');
                    
                    $('#type_txt').removeAttr('required');
                }
            }); 
            
            $('#navButton').click(function(){
                    $('#topNav').addClass('absolute');
                    $('#topNav').removeClass('sticky');
                    $('#topNav').removeClass('z-50');
                    $('#contentDiv').addClass('pt-14');
                });

            $(document).mouseup(function(e) {
                var container = $(".navDiv");

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $('#topNav').removeClass('absolute');
                    $('#topNav').addClass('sticky');
                    $('#topNav').addClass('z-50');
                $('#contentDiv').removeClass('pt-14');
                }
            });


            //List option Search
            jQuery(document).on( "click", ".select-btn", function(e){
                $('.content').not($(this).closest('.wrapper').find('.content')).addClass('hidden');
                $(this).closest('.wrapper').find('.content').toggleClass('hidden');
                $(this).closest('.wrapper').find('.uil-angle-down').toggleClass('-rotate-180');
                e.stopPropagation();
            });

            function searchFilter(searchInput){
                $(".listOption li").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchInput) > -1)
                });
            }

            jQuery(document).on( "click", ".content", function(e){
                e.stopPropagation();
            });
            
            jQuery(document).on( "input", ".selectSearch", function(){
                var value = $(this).val().toLowerCase();
                searchFilter(value);
            });

            jQuery(document).on( "click", ".listOption li", function(){
                var id = $(this).data('id');
                var name = $(this).data('name');
                var unit = $(this).data('unit');
                var is_menu = $(this).data('is_menu');
                var unitid = 'unit' + $(this).data('idnum');
                var qtyid = 'quantity' + $(this).data('idnum');

                if(id != ""){
                    $(this).closest('.wrapper').find('input').val(is_menu+','+id);
                    $(this).closest('.wrapper').find('.select-btn span').html(name);
                }else{
                    $(this).closest('.wrapper').find('input').val('');
                    $(this).closest('.wrapper').find('.select-btn span').html('');
                }
                $('.content').addClass('hidden');
                $('.selectSearch').val('');
                var value = $(".selectSearch").val().toLowerCase();
                searchFilter(value);

                $('#' + unitid).html(unit);
                $('#' + qtyid).val('');
                $('.uil-angle-down').removeClass('-rotate-180');

            });

            $(document).click(function() {
                $('.content').addClass('hidden');
                $('.uil-angle-down').removeClass('-rotate-180');
            });
        });      
     </script>
</x-app-layout>
