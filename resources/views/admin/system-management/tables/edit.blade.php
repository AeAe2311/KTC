<x-app-layout>
    <style>
        .no-number-step::-webkit-outer-spin-button,
        .no-number-step::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .no-number-step {
            -moz-appearance: textfield;
            appearance: textfield;
        }
    </style>

    @section('page_title', 'TABLE - EDIT')

    {{-- <div class="p-3 lg:ml-64 max-h-screen pt-14 lg:pt-3 overflow-y-auto"> --}}
        
        {{-- <header class="bg-white shadow-md rounded-lg">
            <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    INVENTORY - ADD
                </h2>
            </div>
        </header> --}}
    
    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-3">
                <form action="{{ route('table.update') }}" method="POST" enctype="multipart/form-data" class="grid">
                    @csrf
                    <input type="hidden" name="slug" value="{{ $table->slug }}">
                    <div class="mb-2 flex justify-between">
                        <div class="w-1/2 mr-2">
                            <label for="name" class="block text-sm font-medium text-gray-900 lg:text-base">Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ $table->name }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                        </div>
                        <div class="w-1/2">
                            <label for="credit_limit" class="block text-sm font-medium text-gray-900 lg:text-base">Credit Limit</label>
                            <input type="number" id="credit_limit" name="credit_limit" value="{{ number_format((float) ($table->credit_limit ?? 0), 2, '.', '') }}" step="0.01" min="0" class="no-number-step block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                        </div>
                        {{--
                        <div class="w-1/2 mr-2">
                            <label for="role" class="block text-sm font-medium text-gray-900 lg:text-base">Branch<span class="text-red-500">*</span></label>
                            <select id="branch" name="branch" value="{{ old('branch') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full lg:text-base" >
                                @foreach ($branches as $branch)
                                    @if ($branch->id !=1 )
                                        @if ($branch->id == $table->branch)
                                            <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>
                                        @else
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endif
                                        
                                    @endif
                                    
                                @endforeach
                            </select>
                        </div>--}}
                        <!-- leo added start 4/12/25-->
                        {{--<div class="w-1/2">
                            <label for="tblcats" class="block text-sm font-medium text-gray-900 lg:text-base">Category<span class="text-red-500">*</span></label>
                            <select id="tblcats" name="tblcats" value="{{ $table->tblcats }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" >
                            @foreach ($tblcats as $tblcat)
                                <option value="{{ $tblcat->id }}" 
                                    {{ old('tblcats', $table->tblcats) == $tblcat->id ? 'selected' : '' }}>
                                    {{ $tblcat->name }}
                                </option>
                            @endforeach
                            </select>
                        </div>--}}
                        <!-- leo added end 4/12/25-->
                        
                    </div>
                    {{--<div class="mb-4">
                        <label for="">Settings</label>
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="sc" name="sc" class="sr-only peer" {{ ($table->is_sc == 0) ? 'checked' : '' }} >
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
                    $('#contentDiv').addClass('pt-14');
                });

            $(document).mouseup(function(e) {
                var container = $(".navDiv");

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $('#topNav').removeClass('absolute');
                    $('#topNav').addClass('sticky');
                $('#contentDiv').removeClass('pt-14');
                }
            });
        });      
     </script>
</x-app-layout>