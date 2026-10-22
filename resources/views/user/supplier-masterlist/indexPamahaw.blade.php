<x-app-layout>
    <style>
        #inventoryMobile{
            max-height: calc(100vh - 245px);
        }

        @media (min-width: 768px) {
            #inventoryTable{
                max-height: calc(100vh - 225px);
            }
        }

        @media (min-width: 1024px) {
            #inventoryTable{
                max-height: calc(100vh - 180px);
            }
        }
    </style>


    @section('page_title', 'USERS')

    {{-- DELETE MODAL --}}
        <!-- Modal toggle -->
        {{-- <button data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" type="button">
            Toggle modal
        </button> --}}
        
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
         <!-- LEO ADDED START 7-4-25 -->    
    {{-- picture MODAL --}}
        <!-- Modal toggle -->
        {{-- <button data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" class="hidden text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" type="button">
            Toggle modal
        </button> --}}
        
        <!-- Main modal -->
        <div id="imgModal"  tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <img src="" alt="" id="imgmodalelement">
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="font-semibold text-gray-900 flex items-center">
                            <i class="uil uil-exclamation-circle mr-2 text-xl md:text-2xl lg:text-3xl text-blue-700"></i>
                            <span class="text-blue-700 text-base md:text-lg lg:text-xl">Supplier Requirement</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="pictureModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    
                    <div class="px-6 py-3 md:py-6 space-y-6 items-center">
                        
                        <img src=""  alt="imagebox" width="300px" height="300px" id="picdiv">
                        {{--<button type="button"  class=" w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" onclick="$('#imageUploadbtn').click();">Reupload</button>--}}
                        <input type="file" id="imageUploadbtn" name="imageUploadbtn" accept="image/*" capture="user" class="form-control" hidden/>
                        <input type="hidden" name="user_id" id="user_id">
                    </div>

                    <!-- Modal footer -->
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <span></span>
                        <button data-modal-hide="imgModal" type="button" class="w-24 text-white bg-red-600 hover:bg-red-900 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 block">close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- picture MODAL END --}}
            <!-- LEO ADDED END 7-4-25 -->
    {{-- NOTIFICATION --}}
        @if (session('message'))
            <div class="notif absolute left-1/2 top-14 -translate-x-1/2 w-72 h-12 lg:ml-32 bg-emerald-200 rounded-lg z-50 shadow-md shadow-emerald-800 flex flex-row justify-between px-2">
                <div class="flex">
                    <i class="self-center uil uil-cloud-check text-emerald-800 text-2xl"></i>
                    <h1 class="self-center text-emerald-800 font-semibold ml-1">{{ session('message') }}</h1>
                </div>
                <button class="notifButton self-center">
                    <i class="uil uil-times text-2xl text-emerald-800"></i>
                </button>
            </div>
        @endif
    {{-- NOTIFICATION END --}}

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                {{-- CONTROLS --}}
                    <div class="mb-3">
                        <div class="md:grid md:grid-cols-2">
                            <div class=" w-24">
                                <!-- <a href="{{ route('user.add') }}" class="block mb-3 md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px"><i class="uil uil-plus mr-1"></i>ADD</a> -->
                            </div>
                            <div class="justify-self-end w-full xl:w-4/5">
                                <form method="GET" action="" id="searchForm" class="w-full">
                                    <label for="searchInput" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input type="search" id="searchInput" class="block z-10 w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="SEARCH" value="{{ $search }}" autocomplete="off">
                                        <button id="clearButton" type="button" class=" absolute right-20 bottom-1">
                                            <i class="uil uil-times text-2xl"></i>
                                        </button>
                                        <button id="searchButton" type="button" style="bottom: 5px; right: 5px;" type="submit" class="text-white absolute bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-2.5 py-1.5">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                {{-- CONTROLS END --}}

                <div>
                    {{-- TABLE --}}
                        <div class="hidden md:block">
                            <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Name
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Username
                                            </th>
                                            <!-- <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Role
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Department
                                            </th> -->

                                            <!-- leo added 7/2/25 -->
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Company Name
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Company Address
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Company TIN Number
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Company Email
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Company Contact Number
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Contact Person
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Contact Person Email
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Contact Person Number
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Payment Terms
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Mode of Payment
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap hidden">
                                                Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Sales Invoice
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Business Permit
                                            </th>
                                            <!-- leo added 7/2/25 -->
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr class="bg-white border-b">
                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    {{ $user->name }}
                                                </th>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->username }}
                                                </td>
                                                <!-- <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    @php
                                                        if($user->role == 1){
                                                            echo 'Administrator';
                                                        } else if($user->role == 2){
                                                            echo 'Cashier';
                                                        } else if($user->role == 3){
                                                            echo 'Kitchen Officer';
                                                        } else if($user->role == 4){
                                                            echo 'Receiver';
                                                        } else if($user->role == 5){
                                                            echo 'Manager';
                                                        } else if($user->role == 6){
                                                            echo 'Supervisor';
                                                        } else if($user->role == 7){
                                                            echo 'Server';
                                                        } else if($user->role == 8){
                                                            echo 'Admin';
                                                        } else if($user->role == 9){
                                                            echo 'Officer';
                                                        }
                                                    @endphp
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    @php
                                                        $deptName = 'NONE';
                                                        foreach($depts as $dept){
                                                            if ($user->department == $dept->id){
                                                                $deptName = $dept->name;
                                                            } 
                                                        }
                                                        echo $deptName;
                                                    @endphp
                                                </td> -->
                                                <!-- leo added 7/2/25 -->
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->company_name }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->company_address }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->company_tin }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->company_email }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->company_no }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->contact_person }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->contact_person_email }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    {{ $user->contact_person_no }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    @php
                                                        if($user->terms == 1){      
                                                            echo '7 days';
                                                        } else if ($user->terms == 2){
                                                            echo '15 days';
                                                        } else if ($user->terms == 3){
                                                            echo '30 days';
                                                        }
                                                        
                                                    @endphp
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    @php
                                                        if($user->mode_of_payment == 'cash'){      
                                                            echo 'Cash';
                                                        } else if ($user->mode_of_payment == 'cheque'){
                                                            echo 'Cheque';
                                                        } else if ($user->mode_of_payment == 'gcash'){
                                                            echo 'GCash';
                                                        }
                                                    @endphp
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap hidden">
                                                    {{ $user->status }}
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    @if ($user->sales_invoice)
                                                    <button type="button" data-modal-target="imgModal" data-modal-toggle="imgModal"
                                                        data-src="{{ asset('storage/' . $user->sales_invoice) }}"
                                                        onclick="get_imgsrc(this.querySelector('img').src)">
                                                        <img src="{{ asset('storage/' . $user->sales_invoice) }}" alt="Sales Invoice" class="rounded-xl h-16 w-16 mx-auto">
                                                    </button>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    @if ($user->business_permit)
                                                        <button type="button" data-modal-target="imgModal" data-modal-toggle="imgModal" id="{{ asset('storage/'.$user->business_permit) }}" onclick="get_imgsrc(this.querySelector('img').src)">
                                                    <img src="{{ asset('storage/' . $user->business_permit) }}" alt="Business Permit" class="rounded-xl h-16 w-16 mx-auto">
                                                    </button>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                
                                                <!-- leo added 7/2/25 -->
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    <!-- LEO ADDED START 7-6-25 -->
                                                    <form action="{{ route('supplier.pamahaw_approval' , $user->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" name="pamahaw_approval" value="APPROVED" class="text-green-600 hover:underline font-semibold text-sm">Approve</button> |
                                                        <button type="submit" name="pamahaw_approval" value="DECLINED" class="text-red-600 hover:underline font-semibold text-sm">Decline</button>
                                                    </form>

                                                    
                                                    <!-- LEO ADDED START 7-6-25 -->
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {{-- TABLE END --}}

                    
                </div>

                {{-- PAGINATION --}}
                    <div class="grid md:grid-cols-2 mt-3 px-3">
                        @php
                            $page = (int)$page;
                            $prev = $page - 1;
                            $next = $page + 1;
                            $from = ($prev * 100) + 1;
                            $to = $page * 100;
                            if($to > $supplier_accregCount){
                                $to = $supplier_accregCount;
                            }if($supplier_accregCount == 0){
                                $from = 0;
                            }
                        @endphp
                        <div class="justify-self-center md:justify-self-start self-center">
                            <span class="text-sm text-gray-700">
                                Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $supplier_accregCount }}</span> Items
                            </span>
                        </div>

                        <div class="justify-self-center md:justify-self-end">
                            <nav aria-label="Page navigation example" class="h-8 mb-0.5 shadow-xl">
                                <ul class="inline-flex items-center -space-x-px">
                                    <li>
                                        <a href="{{ ($search == '') ? url('/supplier-accreditation'.$prev) : url('/supplier-accreditation'.$prev.'/'.$search);  }}"  class="{{ ($page == 1) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                            <i class="uil uil-angle-left-b"></i>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li>
                                        <p class="block w-9 h-9 leading-9 text-center z-10 text-gray-500 border border-gray-300 bg-white font-semibold">{{ $page }}</p>
                                    </li>
                                    <li>
                                        <a href="{{ ($search == '') ? url('/supplier-accreditation'.$next) : url('/supplier-accreditation'.$next.'/'.$search); }}" class="{{ ($to == $supplier_accregCount) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
                                            <i class="uil uil-angle-right-b"></i>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                {{-- PAGINATION END --}}


            </div>
        </div>
     </div>

     <script>
        $(document).ready(function() {
            $('#searchButton').click(function(){
                var search = $('#searchInput').val();
                if(search != ""){
                    $('#searchForm').prop('action', `{{ url('/supplier-accreditation/1/${search}') }}`);
                }else{
                    $('#searchForm').prop('action', `{{ url('/supplier-accreditation/1') }}`);
                }
                $('#searchForm').submit();
            });

            $('#searchInput').on('keydown', function(event) {
                if (event.keyCode === 13) {
                    $('#searchButton').click();
                    event.preventDefault();
                }
            });

            $('#clearButton').click(function(){
                $('#searchInput').val('');
            });

            $('.notifButton').click(function(){
                $('.notif').addClass('hidden');
            });

            $('.deleteButton').click(function(){
                var slug = $(this).data('slug');
                $('.deleteConfirm').prop('href', `{{ url('/supplier-accreditation/delete/${slug}') }}`);
            });

            $('.contentDiv').click(function(){
                $('.notif').addClass('hidden');
            });
            $('.navDiv').click(function(){
                $('.notif').addClass('hidden');
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
            // LEO ADDED START 7-4-25 
            $(document).ready(function() {
                window.get_imgsrc = function(x) {
                    $('#picdiv').attr("src", x);
                };
            });
            // LEO ADDED END 7-4-25 

            
        });
     </script>
</x-app-layout>
