<x-app-layout>

    @section('page_title', 'RAW MATERIALS - ADD')

    {{-- ERROR NOTIFICATION --}}
        @if (session('error'))
            <div class="absolute z-50 flex flex-row justify-between h-12 px-2 -translate-x-1/2 bg-red-200 rounded-lg shadow-md notif left-1/2 top-14 w-96 shadow-red-800">
                <div class="flex">
                    <i class="self-center text-2xl text-red-800 uil uil-cloud-check"></i>
                    <h1 class="self-center ml-1 font-semibold text-red-800">{{ session('error') }}</h1>
                </div>
                <button class="self-center notifButton">
                    <i class="text-2xl text-red-800 uil uil-times"></i>
                </button>
            </div>
        @endif
    {{-- ERROR NOTIFICATION END --}}

    <div class="p-3 lg:pt-3 {{ (Auth::user()->role == 1) ? 'lg:ml-64' : '' }}">
        <div id="contentDiv" class="w-full p-2">
            <div class="p-3 overflow-hidden bg-white rounded-lg shadow-md">
                <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" class="grid">
                    @csrf
                    {{--<div class="mb-2">
                        <label for="item_code" class="block text-sm font-medium text-gray-900 lg:text-base">Item Code</label>
                        <input type="text" id="item_code" name="item_code" value="{{ old('item_code') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                    </div>

                    <div class="mb-2">
                        <label for="name" class="block text-sm font-medium text-gray-900 lg:text-base">Item Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>

                    <div class="mb-2">
                        <label for="category_id" class="block text-sm font-medium text-gray-900 lg:text-base">Category <span class="text-red-500">*</span></label>
                        <select id="category_id" name="category_id" value="{{ old('category_id') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                            @foreach ($categories as $category)
                                <option {{ old('category_id') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>--}}
                    <div class="mb-2 {{  ( auth()->user()->role == 3 || auth()->user()->role == 9) ? 'hidden' : '' }} ">
                        <label for="category_id" class="block text-sm font-medium text-gray-900 lg:text-base">Branch<span class="text-red-500"> *</span></label>
                        <select id="branch" name="branch" value="{{ old('branch') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base" >
                            @foreach ($branches as $branch)
                                @if ($branch->id !=1 )
                                    <option value="{{ $branch->id }}" {{ (auth()->user()->branch == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endif
                                
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                    <div class="w-full">
                        <label for="" class="block text-sm font-medium text-gray-900 lg:text-base">Item Name<span class="text-red-500">*</span></label>
                                    <div class="wrapper w-full relative">
                                        <div class="select-btn flex items-center justify-between rounded-md bg-gray-100 border border-gray-300 p-2 h-9 cursor-pointer">
                                            <span></span>
                                            <i class="uil uil-angle-down text-2xl transition-transform duration-300"></i>
                                        </div>
                                        <div class="content bg-gray-100 mt-1 rounded-md p-3 hidden absolute w-full z-50">
                                            <div class="search relative">
                                                <i class="uil uil-search absolute left-3 leading-9 text-gray-500"></i>
                                                <input type="text" class="selectSearch w-full leading-9 text-gray-700 rounded-md pl-9 outline-none h-9" placeholder="Search">
                                            </div>
                                            <ul class="listOption options mt-2 max-h-52 overflow-y-auto">
                                                
                                                @foreach ($masterlists as $item)
                                                    <li data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-unit="{{ $item->unit }}"  data-idnum="1" class="h-9 cursor-pointer hover:bg-gray-300 rounded-md flex items-center pl-3 leading-9">{{ $item->name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <input type="hidden" name="itemID" id="itemID" value="">
                                        <!-- <input type="text" name="itemName" id="itemName" value=""> -->
                                    </div>
                                </div>
                    </div>
                    
                    
                    {{-- <div class="mb-2">
                        <label for="quantity" class="block text-sm font-medium text-gray-900 lg:text-base">Quantity <span class="text-red-500">*</span></label>
                        <input type="text" id="quantity" name="quantity" value="{{ old('quantity') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-5">
                        <label for="unit" class="block text-sm font-medium text-gray-900 lg:text-base">Unit<span class="text-red-500">*</span></label>
                        <select id="unit" name="unit" value="{{ old('unit') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                            <option {{ old('unit') == 'tsp' ? 'selected' : '' }} value="tsp">teaspoon (tsp)</option>
                            <option {{ old('unit') == 'tbsp' ? 'selected' : '' }} value="tbsp">tablespoon (tbsp)</option>
                            <option {{ old('unit') == 'gal' ? 'selected' : '' }} value="gal">gallon (gal)</option>
                            <option {{ old('unit') == 'L' ? 'selected' : '' }} value="L">liter (L)</option>
                            <option {{ old('unit') == 'mL' ? 'selected' : '' }} value="mL">milliliter (mL)</option>
                            <option {{ old('unit') == 'c' ? 'selected' : '' }} value="c">cup (c)</option>
                            <option {{ old('unit') == 'kg' ? 'selected' : '' }} value="kg">kilogram (kg)</option>
                            <option {{ old('unit') == 'g' ? 'selected' : '' }} value="g">gram (g)</option>
                            <option {{ old('unit') == 'mg' ? 'selected' : '' }} value="mg">milligram (mg)</option>
                            <option {{ old('unit') == 'oz' ? 'selected' : '' }} value="oz">ounce (oz)</option>
                            <option {{ old('unit') == 'lb' ? 'selected' : '' }} value="lb">pound (lb)</option>
                            <option {{ old('unit') == 'ea' ? 'selected' : '' }} value="ea">each (ea)</option>
                            <option {{ old('unit') == 'doz' ? 'selected' : '' }} value="doz">dozen (doz)</option>
                        </select>
                    </div>
                     --}}

                    <div class="mb-2">
                        <label for="reorder_point" class="block text-sm font-medium text-gray-900 lg:text-base">Reorder Point<span class="text-red-500">*</span></label>
                        <input type="text" id="reorder_point" name="reorder_point" value="{{ old('reorder_point') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>

                    
                    <div class="mb-2">
                        <label for="department" class="block text-sm font-medium text-gray-900 lg:text-base">Department<span class="text-red-500">*</span></label>
                        
                        <select id="department" name="department" value="{{ old('department') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                            @foreach ($depts as $dept)
                                @if ($dept->id < 8)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endif
                                    
                            @endforeach
                        </select>
                    </div>    
                    {{-- <div class="mb-2">
                        <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Price</label>
                        <input type="text" id="price" name="price" value="{{ old('price') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                    </div> --}}

                    {{-- <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-900 lg:text-base" for="image">Image</label>
                        <input class="block w-full px-1 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none lg:text-base" id="image" name="image" type="file" accept="image/*">
                    </div> --}}

                    <div class="flex justify-between">
                        <a href="{{ route('inventory.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                        <button type="submit" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold">SUBMIT</button>
                    </div>
                </form>
            </div>
        </div>
     </div>

     <script>
        $(document).ready(function() {
            $('#price').on('input', function() {
                var val = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(val);
            });
            $('#quantity').on('input', function() {
                var val = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(val);
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

            $('.notifButton').click(function(){
                $('.notif').addClass('hidden');
            });


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
                //var is_menu = $(this).data('is_menu');
                var unitid = 'unit' + $(this).data('idnum');
                var qtyid = 'quantity' + $(this).data('idnum');

                if(id != ""){
                    //$(this).closest('.wrapper').find('input').val(id);
                    $('#itemID').val(id);
                    //$('#itemName').val(name);
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

        });      
     </script>
</x-app-layout>
