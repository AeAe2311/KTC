<x-app-layout>

    @section('page_title', 'EXPENSES - EDIT')

    <div class="p-3 lg:pt-3 {{ (Auth::user()->role == 1) ? 'lg:ml-64' : 'lg:ml-64' }}">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-3">
                <div class="">
                    <form action="{{ route('revolving.update_budget') }}" method="POST" enctype="multipart/form-data" class="grid" class="">
                        @csrf
                        <input type="hidden" name="id" value="{{ $budget->id }}">
                        {{-- <div class="mb-2">
                            <label for="item_code" class="block text-sm font-medium text-gray-900 lg:text-base">Item Code</label>
                            <input type="text" id="item_code" name="item_code" value="{{ $item->item_code }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base">
                        </div> --}}

                        <div class="mb-2">
                            <label for="current_budget" class="block text-sm font-medium text-gray-900 lg:text-base">Edit Budget - {{ $budget->classification }} <span class="text-red-500">*</span></label>
                            <input type="number" id="current_budget" name="current_budget" value="{{ $budget->current_budget }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" step="any" required>
                        </div>
                        

                        <!-- <div class="mb-2">
                            <label for="resto" class="block text-sm font-medium text-gray-900 lg:text-base">Resto <span class="text-red-500">*</span></label>
                            <input type="number" id="resto" name="resto" value="{{ $budget->resto }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" required>
                        </div>

                        <div class="mb-2">
                            <label for="studio_morii" class="block text-sm font-medium text-gray-900 lg:text-base">Studio Morii <span class="text-red-500">*</span></label>
                            <input type="number" id="studio_morii" name="studio_morii" value="{{ $budget->studio_morii }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" required>
                        </div> -->

                        

                        @if (Auth::user()->role == 1)
                            
                        @endif
                        
                        {{-- <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-900 lg:text-base" for="image">Image</label>
                            <input class="px-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none lg:text-base" id="image" name="image" type="file" accept="image/*">
                        </div> --}}
                        
                        <div class="flex justify-between mt-5">
                            <a href="{{ route('revolving.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                            <button type="submit" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mb-2 focus:outline-none tracking-wider font-semibold">UPDATE</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
     </div>

     <script>
        $(document).ready(function() {
            $('.inputNumber').on('keypress keyup', function(event){
                var regex = /^[0-9.]+$/;
                var value = $(this).val() + String.fromCharCode(event.keyCode);
                if (!regex.test(value)) {
                event.preventDefault();
                return false;
                }
                if ((event.keyCode == 46) && ($(this).val().indexOf('.') >= 0)) {
                event.preventDefault();
                return false;
                }
            });

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
