<x-app-layout>

    @section('page_title', 'EXPENSES - ADD')

    {{-- DELETE MODAL --}}
        
        <!-- Main modal -->
        <div id="itemDeleteModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="font-semibold text-gray-900 flex items-center">
                            <i class="uil uil-exclamation-triangle mr-2 text-xl md:text-2xl lg:text-3xl text-red-700"></i>
                            <span class="text-red-700 text-base md:text-lg lg:text-xl">Delete Item</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="itemDeleteModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="px-6 py-3 md:py-6 space-y-6">
                        <p class="text-xs md:text-base leading-relaxed text-gray-500">
                            Are you sure you want to delete this item?
                        </p>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <a type="button" href="" class="deleteConfirm w-24 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</a>
                        <button data-modal-hide="itemDeleteModal" type="button" class="w-24 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- DELETE MODAL END --}}

    <div class="p-3 lg:pt-3 lg:ml-64">
        <div id="contentDiv" class="p-2 w-full bg-gray-100">
            <div class="flex gap-2 h-[90vh]">
                <!-- LEFT SIDE -->
                <div class="bg-white overflow-y-auto shadow-md rounded-lg p-4 w-1/2">
                    {{-- CONTROLS --}}
                        <div class="mb-3">
                            
                        </div>
                    {{-- CONTROLS END --}}

                    <div class="">
                        {{-- TABLE --}}
                            <div class="hidden md:block ">
                                <div id="inventoryTable" class="overflow-auto w-full sm:rounded-lg ">
                                    <form action="{{ route('revolving.store') }}" method="POST" enctype="multipart/form-data" class="grid">
                                        @csrf

                                        <p class="text-center text-xs text-gray-400 mb-3">
                                            Fields marked with <span class="text-red-500">*</span> are required.
                                        </p>

                                        <!-- DATE -->

                                        <div class="mb-6">
                                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date<span class="text-red-500">*</span></label>
                                            <div class="relative max-w-full">
                                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                                </div>
                                                <input datepicker type="text" name="dateAdd" value="{{ date('m/d/Y') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                                            </div>
                                        </div>
                                        <!-- END OF DATE -->


                                        <!-- PARTICULARS -->
                                        <div class="mb-2">
                                            <label for="particulars" class="block text-sm font-medium text-gray-900 lg:text-base">Category <span class="text-red-500">*</span></label>
                                            <select name="category_id" id="category_id" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base">
                                                    <option value="" selected="true" disabled>List of categories</option>
                                                        @foreach ($expcats as $category)
                                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                        @endforeach
                                            </select>
                                        </div>
                                        <!-- END OF PARTICULARS -->


                                        <!-- PARTICULARS -->
                                        <div class="mb-2">
                                            <label for="particulars" class="block text-sm font-medium text-gray-900 lg:text-base">Particulars <span class="text-red-500">*</span></label>
                                            <input type="text" id="particulars" name="particulars" value="{{ old('particulars') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                        </div>
                                        <!-- END OF PARTICULARS -->

                                        <!-- DESCRIPTION -->
                                        <div class="mb-2">
                                            <label for="description" class="block text-sm font-medium text-gray-900 lg:text-base">Description <span class="text-red-500">*</span></label>
                                            <input type="text" id="description" name="description" value="{{ old('description') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                        </div>
                                        <!-- END OF DESCRIPTION -->

                                        <!-- QUANTITY -->
                                        <div class="mb-2">
                                            <label for="quantity" class="block text-sm font-medium text-gray-900 lg:text-base">Quantity <span class="text-red-500">*</span></label>
                                            <input type="text" id="quantity" name="quantity" value="{{ old('quantity') }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                        </div>
                                        <!-- END OF QUANTITY -->

                                        <!-- UNIT AMOUNT -->
                                        <div class="mb-2">
                                            <label for="unit_amount" class="block text-sm font-medium text-gray-900 lg:text-base">Unit Amount <span class="text-red-500">*</span></label>
                                            <input type="text" id="unit_amount" name="unit_amount" value="{{ old('unit_amount') }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                        </div>
                                        <!-- END OF UNIT AMOUNT -->

                                        <!-- TOTAL AMOUNT -->
                                        <div class="mb-2">
                                            <label for="price" class="block text-sm font-medium text-gray-900 lg:text-base">Total Amount <span class="text-red-500">*</span></label>
                                            <input type="text" id="price" name="price" value="{{ old('price') }}" class="inputNumber block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                                        </div>
                                        <!-- END OF TOTAL AMOUNT -->
                                        

                                        <!-- REMARKS -->
                                        <div class="mb-6">
                                            <label for="other_name" class="block text-sm font-medium text-gray-900 lg:text-base">Remarks </label>
                                            <input type="text" id="other_name" name="other_name" value="{{ old('other_name') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" >
                                        </div>
                                        <!-- END OF REMARKS -->

                                        <!-- CURRENT FUND -->
                                        
                                            
                                        <div class="grid gap-3 mb-5">
                                            <div>
                                                <label for="budget_display" class="mb-3 block text-sm font-medium text-gray-900 lg:text-base">
                                                    Current Budget
                                                </label>
                                                <ul id="budget_display" 
                                                    class="block w-full p-2 text-gray-900 border-0 shadow-inner rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base">
                                                    @foreach($budget as $item)
                                                        <li class="ms-3">
                                                            {{ $item->classification }} &nbsp;&nbsp;&nbsp;&nbsp; ₱ {{ $item->current_budget }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>

                                            
                          
                                        
                                        <!-- END OF CURRENT FUND -->

                                        <!-- OPTION FOR CURRENT FUND -->
                                        <div class="mb-5">
                                            <label for="list" class="block text-sm font-medium text-gray-900 lg:text-base">Choose Budget to less<span class="text-red-500">*</span></label>
                                            <select id="list" name="list" value="{{ old('list') }}" class="mt-3 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base" >
                                                <option value="" selected="true" disabled>List of budgets</option>
                                                    @foreach ($budget as $budgets)
                                                        <option value="{{ $budgets->id }}">{{ $budgets->classification }}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                        <!-- END OF OPTION FOR CURRENT FUND -->

                                        <!-- STATUS -->
                                        <!-- <div class="mb-6">
                                            <label for="" class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                                            <div class="flex items-center gap-x-10">
                                                <div class="flex items-center">
                                                    <input checked id="paid" type="radio" value="1" name="status" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                    <label for="paid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Paid</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="unpaid" type="radio" value="0" name="status" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                    <label for="unpaid" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Unpaid</label>
                                                </div>
                                            </div>
                                        </div> -->
                                        <!-- END OF STATUS -->

                                        {{--@if (Auth::user()->role == 1)
                                            <div class="mb-6">
                                                <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Date</label>
                                                <div class="relative max-w-full">
                                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                                                    </div>
                                                    <input datepicker type="text" name="dateAdd" value="{{ date('m/d/Y') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Select date">
                                                </div>
                                            </div>
                                        @endif--}}

                                        <div class="flex justify-between mt-6">
                                            <a href="{{ route('revolving.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                                            <button type="submit" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mb-2 focus:outline-none tracking-wider font-semibold">ADD</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        {{-- TABLE END --}}

                       
                    </div>
                </div>
            <!-- END OF LEFT SIDE -->
             
            <!-- RIGHT SIDE -->
            <div class="bg-white overflow-x-hidden shadow-md rounded-lg p-4 w-1/2">
                {{-- CONTROLS --}}
                    <div class="mb-3">
                        <div class="md:grid md:grid-cols-2 flex flex-col">
                            
                        </div>
                    </div>
                {{-- CONTROLS END --}}

                <div class="">
                    {{-- TABLE --}}
                        <div class="hidden md:block ">
                            <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg ">

                                    <div class=" gap-3 flex">
  
                                    </div>

                                    <table class="w-full text-sm text-left text-gray-500 ">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                    Action
                                                </th>
                                                <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                    Particulars
                                                </th>
                                                <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    Description
                                                </th >
                                                <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                    Quantity
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                    Unit Amount
                                                </th>
                                                
                                                <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    Total Amount
                                                </th>
                                                <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    Remarks
                                                </th>
                                                <th class="px-6 py-3 text-center whitespace-nowrap">
                                                    List
                                                </th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody class="">
                                            @foreach ($inventories as $inventory)
                                                <tr class="bg-white border-b">
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        <a type="button" data-slug="{{ $inventory->id }}" class="cartDeleteButton text-red-600 hover:underline font-semibold text-sm cursor-pointer">Delete</a>
                                                    </td>
                                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap text-center">
                                                        {{ $inventory->particulars }}
                                                    </th>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        {{ $inventory->description }}
                                                    </td>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        {{ $inventory->quantity }}
                                                    </td>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        ₱ {{ number_format($inventory->unit_amount, 2, '.', ',') }}
                                                    </td>
                                                    <td class="total-amount px-6 py-4 text-center whitespace-nowrap" data-amount="{{ $inventory->amount }}">
                                                        ₱ {{ number_format($inventory->amount, 2, '.', ',') }}
                                                    </td>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        {{ $inventory->other_remarks }}
                                                    </td>
                                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                                        @if($inventory->rev_id == 1)
                                                            <p>Personal</p>
                                                        @elseif($inventory->rev_id == 2)
                                                            <p>Resto</p>
                                                        @else
                                                            <p>Studio Morii</p>
                                                        @endif


                                                    </td>
                                                    
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <!-- auto display of total amount -->
                                    <div class="w-auto flex justify-center items-center gap-5 m-5 border rounded-xl shadow-xl p-10 ">
                                        <div>
                                            
                                            <a href="#" type="" class="mt-5 text-white w-1/2 max-w-xs bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-3 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center recalculate-button"><i class="uis uis-refresh mr-2"></i>CALCULATE</a>
                                        </div>
                                        <div>
                                            <label class="font-bold" for="">Total Amount : </label>
                                            <div class="text-xl" id="totalDiv" data-amount="0.00">₱ 0.00</div>
                                            <input type="hidden" name="total_value" id="total_value">  
                                        </div>
                                    </div>

                                    <!-- end of auto display of total amount -->
                                    
                                    <div id="expenseDiv" class="flex justify-center mt-5 {{($inventoriesCount <= 0 ) ? 'hidden':''}}">    
                                        <a href="#" type="" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center submit-button">SUBMIT</a>
                                    </div>
                            </div>
                                
                        </div>
                    {{-- TABLE END --}}
                </div>
            </div>
            <!-- END OF RIGHT SIDE -->
            </div>
        </div>
     </div>

     <script>
        $(document).ready(function() {
            
            // right side
            $('.submit-button').click(function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route("revolving.cart_store") }}', 
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });
            });
            // end of right side

            

            $(document).on('click', '.cartDeleteButton', function() {
                var slug = $(this).data('slug');
                var row = $(this).closest('tr');  // Get the row to remove
                $.ajax({
                    url: '{{ route("revolving.cart_delete") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        slug: slug
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove();  // Remove the deleted row from the DOM
                            alert(response.message);
                            // Check if the table is empty after deletion and hide the submit button
                            if ($('#inventoryTable tbody tr').length === 0) {
                                $('#expenseDiv').addClass('hidden');
                            }
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });
            });



            // Event listener for form submission
            $('form').on('submit', function(event) {
                event.preventDefault(); 
                
                var formData = {
                    _token: $('input[name="_token"]').val(), 
                    // name: $('input[name="name"]').val(),
                    // other_name: $('input[name="other_name"]').val(),
                    // status: $('input[name="status"]:checked').val(),
                    // quantity: $('input[name="quantity"]').val(),
                    // price: $('input[name="price"]').val(),
                    category_id: $('select[name="category_id"]').val(),
                    // supplier: $('input[name="supplier"]').val(),
                    dateAdd: $('input[name="dateAdd"]').val(),
                    particulars:$('input[name="particulars"]').val(),
                    description:$('input[name="description"]').val(),
                    quantity: $('input[name="quantity"]').val(),
                    unit_amount: $('input[name="unit_amount"]').val(),
                    price: $('input[name="price"]').val(),
                    other_name: $('input[name="other_name"]').val(),
                    list: $('select[name="list"]').val(),
                };

                $.ajax({
                    url: '{{ route("revolving.store") }}', 
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Clear form fields
                        // $('input[name="name"]').val(''); 
                        // $('input[name="other_name"]').val(''); 
                        // $('input[name="status"]').prop('checked', false); 
                        // $('input[name="quantity"]').val(''); 
                        // $('input[name="price"]').val(''); 
                        // $('input[name="dateAdd"]').val(''); 
                        // $('input[name="description"]').val(''); 
                        $('input[name="quantity"]').val(''); 
                        $('input[name="unit_amount"]').val(''); 
                        $('input[name="total_amount"]').val(''); 
                        $('input[name="remarks"]').val(''); 
                        $('input[name="list"]').val(''); 


                        // Update the inventory table (right side) dynamically
                        $('#inventoryTable tbody').empty(); // Clear the existing rows

                        // Loop through the response to append new rows
                        $.each(response.inventories, function(index, inventory) {
                            $('#inventoryTable tbody').append(
                                '<tr class="bg-white border-b">' +
                                    '<th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">' + inventory.particulars + '</th>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.description + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.quantity + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">₱ ' + parseFloat(inventory.unit_amount).toFixed(2) + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + parseFloat(inventory.amount).toFixed(2) + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + inventory.other_remarks + '</td>' +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' + 
                                    (inventory.rev_id == 1 ? 'Personal' : 
                                    (inventory.rev_id == 2 ? 'Resto' : 'Studio Morii')) + 
                                    '</td>'
                                    +
                                    '<td class="px-6 py-4 text-center whitespace-nowrap">' +
                                        
                                        '<a type="button"  data-slug="' + inventory.id + '" class="cartDeleteButton text-red-600 hover:underline font-semibold text-sm cursor-pointer">Delete</a>' +
                                    '</td>' +
                                '</tr>'
                            );
                        });
                        $('#expenseDiv').removeClass('hidden');

                        setTimeout(function() {
                            // location.reload();
                        }, 500); 
                    },
                    error: function(xhr) {
                        // Handle errors
                    }
                });

            });

            // new
            
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

            
            function calculatePrice() {
                var quantity = parseFloat($('#quantity').val()) || 0; 
                var unitAmount = parseFloat($('#unit_amount').val()) || 0; 
                var price = quantity * unitAmount;
                
                $('#price').val(price.toFixed(2));
            }

            $('#quantity, #unit_amount').on('input', function() {
                calculatePrice();
            });

            $(document).ready(function() {
                calculatePrice();
            });

            // calculation for total for right side

            function calculateTotal() {
                let totalAmount = 0;

                $('.total-amount').each(function () {
                    let amount = parseFloat($(this).data('amount')) || 0; 
                    totalAmount += amount;
                });

                $('#totalDiv').text(`₱ ${totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
                $('#total_value').val(totalAmount); 
            }
            
            calculateTotal();
            
            $(document).on('click', '.cartDeleteButton', function () {
                $(this).closest('tr').remove(); 
                calculateTotal(); 
            });

            $(document).on('click','.recalculate-button', function() {
                setTimeout(function() {
                            location.reload();
                        }, 500);
            });

            // $(document).on('DOMNodeInserted', 'tbody', function () {
            //     calculateTotal();
            // });

            // end calculation for total for right side

            // end

        });
     </script>
</x-app-layout>