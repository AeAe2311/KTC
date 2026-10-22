<x-app-layout>

    @section('page_title', 'TABLE - ADD')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-3">
                <form action="{{ route('table.store') }}" method="POST" enctype="multipart/form-data" class="grid">
                    @csrf
                    <div class="mb-4 flex justify-between">
                        <div class="w-1/2 mr-2">
                        <label for="name" class="block text-sm font-medium text-gray-900 lg:text-base">Client Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                        </div>
                        {{--<div class="w-1/2 mr-2">
                            <label for="role" class="block text-sm font-medium text-gray-900 lg:text-base">Branch<span class="text-red-500">*</span></label>
                            <select id="branch" name="branch" value="{{ old('branch') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" >
                                @foreach ($branches as $branch)
                                    @if ($branch->id !=1 )
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endif
                                    
                                @endforeach
                            </select>
                        </div>--}}
                        <!-- leo added start 4/22/25-->
                        {{--<div class="w-1/2">
                            <label for="role" class="block text-sm font-medium text-gray-900 lg:text-base">Category<span class="text-red-500">*</span></label>
                            <select id="tblcats" name="tblcats" value="{{ old('tblcats') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" >
                                @foreach ($tblcats as $tblcat)    
                                        <option value="{{ $tblcat->id }}">{{ $tblcat->name }}</option>
                                @endforeach
                            </select>
                        </div>--}}
                        <!-- leo added end 4/22/25-->

                    </div>
                    {{--<div class="mb-4">
                        <label for="">Settings</label>
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="sc" name="sc" class="sr-only peer" checked>
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Service Charge</span>
                        </label>
                    </div>--}}
                    <div class="flex justify-between">
                        <a href="{{ route('table.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                        <button type="submit" class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold">SUBMIT</button>
                    </div>
                </form>
            </div>
        </div>
     </div>

     <script>
        $(document).ready(function() {
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
     </script>
</x-app-layout>
