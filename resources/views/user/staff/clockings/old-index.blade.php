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

    @section('page_title', 'Clocking Records')

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
                    <!-- Modal footer -->
                    <div class="flex items-center px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <span></span>
                        <button data-modal-hide="imgModal" type="button" class="w-24 text-white bg-red-600 hover:bg-red-900 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 block">close</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- picture MODAL END --}}



    <div class="p-3 {{ auth()->user()->role == 1 ? 'lg:ml-64 lg:pt-3' : 'h-[calc(100vh-48px)] w-screen' }}">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-4">
                {{-- CONTROLS --}}
                    <div class="mb-3">
                        <div class="md:grid md:grid-cols-2">
                            <div class=" w-24">
                                <!-- <a href="{{ route('staff.add') }}" class="hidden md:block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-2.5 focus:outline-none my-px"><i class="uil uil-plus mr-1"></i>ADD</a> -->
                            </div>
                            <div class="justify-self-end w-full xl:w-4/5">
                                <form method="GET" action="" id="searchForm" class="w-full">
                                    <label for="searchInput" class="mb-2 text-sm font-medium text-gray-900 sr-only">Search</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input type="search" id="searchInput" class="block w-full px-4 py-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="SEARCH" value="{{ $search }}" autocomplete="off">
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
                        <div class=" md:block">
                            <div id="inventoryTable" class="overflow-auto w-full shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Name
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Position
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Image
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                In
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Image
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Out
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Duration
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-center whitespace-nowrap">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($clocks as $clock)
                                            <tr class="bg-white border-b">
                                                <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    @if (auth()->user()->username == 'bizdeveloper')
                                                    <a href="{{ url('/clocking/approved/'.$clock->slug) }}"  class="approveButton text-red-600 hover:underline font-semibold text-sm cursor-pointer">{{ ($clock->approved == 1) ? 'Done' : 'Approved'}} </a>
                                                    @endif
                                                    {{ $clock->staff->fname. ' '. $clock->staff->mname .' '. $clock->staff->lname }}
                                                </td>
                                                <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    {{ $clock->staff->position->name}}
                                                </td>
                                                <!-- IN image -->
                                               <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"  >
                                               <button type="button" data-modal-target="imgModal" data-modal-toggle="imgModal" id="{{ asset('storage/'.$clock->in_image) }}" onclick="get_imgsrc(this.id)">
                                                    <img src="{{ asset('storage/'.$clock->in_image) }}" alt="no image" class="rounded-xl h-16 w-16 mx-auto">
                                               </button>
                                                </td>

                                                <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $clock->in_time)->format('h:i:s A F d, y') }}
                                                    
                                                </td>

                                                <!-- Out image -->
                                                <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    <button type="button" data-modal-target="imgModal" data-modal-toggle="imgModal" id="{{ asset('storage/'.$clock->out_image) }}" onclick="get_imgsrc(this.id)">
                                                        <img src="{{ asset('storage/'.$clock->out_image) }}" alt="" class="rounded-xl h-16 w-16 mx-auto">
                                                   </button>     
                                                </td>

                                                <td scope="row" class="px-6 py-4 font-medium {{ (!empty($clock->out_time)) ? 'text-gray-900 ' : 'text-red-600' }} whitespace-nowrap">
                                                    {{ (!empty($clock->out_time)) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $clock->out_time )->format('h:i:s A F d, y') : 'none' }}
                                                    
                                                </td>
                                                @php
                                                    
                                                    $start  = new \Carbon\Carbon($clock->in_time);
                                                    $end    = new \Carbon\Carbon($clock->out_time);

                                                    $options = [
                                                        'join' => true,
                                                        'parts' => 4,
                                                        'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                                        ];
                                                    $duration = $end->diffForHumans($start,$options);
                                                    //$duration = $end->diffInMinutes($start,$options);
                                                    //$duration = $end->diff($start)->format('%H:%i:%s');
                                                    //$duration = $end->diffInHours($start,$options);
                                                    
                                                @endphp
                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">

                                                    {{ $duration }}
                                                </th>
                                                <th scope="row" class="px-6 py-4 font-medium {{ ($clock->approved == 0) ? 'text-red-600 ' : 'text-green-600' }} whitespace-nowrap">

                                                    {{ ($clock->approved == 0) ? 'Pending' : 'Approved' }}
                                                </th>
                                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                                    <a href="{{ url('/clocking/edit/'.$clock->slug) }}" class="text-blue-600 hover:underline font-semibold text-sm">Edit</a> | <a type="button" data-modal-target="itemDeleteModal" data-modal-toggle="itemDeleteModal" data-slug="{{ $clock->slug }}" class="deleteButton text-red-600 hover:underline font-semibold text-sm cursor-pointer">Delete</a> 
                                                    
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
                            $prev = $page - 1;
                            $next = $page + 1;
                            $from = ($prev * 100) + 1;
                            $to = $page * 100;
                            if($to > $clocksCount){
                                $to = $clocksCount;
                            }if($clocksCount == 0){
                                $from = 0;
                            }
                        @endphp
                        <div class="justify-self-center md:justify-self-start self-center">
                            <span class="text-sm text-gray-700">
                                Showing <span class="font-semibold text-gray-900">{{ $from }}</span> to <span class="font-semibold text-gray-900">{{ $to }}</span> of <span class="font-semibold text-gray-900">{{ $clocksCount }}</span> Items
                            </span>
                        </div>

                        <div class="justify-self-center md:justify-self-end">
                            <nav aria-label="Page navigation example" class="h-8 mb-0.5 shadow-xl">
                                <ul class="inline-flex items-center -space-x-px">
                                    <li>
                                        <a href="{{ ($search == '') ? url('/clocking/'.$prev) : url('/clocking/'.$prev.'/'.$search);  }}"  class="{{ ($page == 1) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                                            <i class="uil uil-angle-left-b"></i>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li>
                                        <p class="block w-9 h-9 leading-9 text-center z-10 text-gray-500 border border-gray-300 bg-white font-semibold">{{ $page }}</p>
                                    </li>
                                    <li>
                                        <a href="{{ ($search == '') ? url('/clocking/'.$next) : url('/clocking/'.$next.'/'.$search); }}" class="{{ ($to == $clocksCount) ? 'pointer-events-none' : ''; }} block w-9 h-9 leading-9 text-center text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
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
                    $('#searchForm').prop('action', `{{ url('/clocking/1/${search}') }}`);
                }else{
                    $('#searchForm').prop('action', `{{ url('/clocking/1') }}`);
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
                $('.deleteConfirm').prop('href', `{{ url('/clocking/delete/${slug}') }}`);
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
        });
        
        function get_imgsrc(x){
                //$('#imgsrc').clear();
                $('#imgsrc').val(x);
                $("#imgmodalelement").attr("src",x);
                //show_edit_user()
            }
     </script>
</x-app-layout>
