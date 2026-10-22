<x-app-layout>

    @section('page_title', 'TABLE - EDIT')

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
    
    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-3">
                <form action="{{ route('staff.update') }}" method="POST" enctype="multipart/form-data" class="grid">
                    @csrf
                    <input type="hidden" name="slug" value="{{ $staff->slug }}">
                    
                    <div class="mb-4 flex gap-x-2">
                        <div class="w-1/3 ">
                            <label for="firstname" class="block text-sm font-medium text-gray-900 lg:text-base">Firstname <span class="text-red-500">*</span></label>
                            <input type="text" id="firstname" name="firstname" value="{{ $staff->fname }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                        </div>
                        <div class="w-1/3">
                            <label for="middlename" class="block text-sm font-medium text-gray-900 lg:text-base">Middlename</label>
                            <input type="text" id="middlename" name="middlename" value="{{ $staff->mname }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                        </div>
                        <div class="w-1/3">
                            <label for="lastname" class="block text-sm font-medium text-gray-900 lg:text-base">Lastname <span class="text-red-500">*</span></label>
                            <input type="text" id="lastname" name="lastname" value="{{ $staff->lname }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="mb-4 flex gap-x-2">
                        
                        <div class="w-1/3">
                            <label for="sec_id" class="block text-sm font-medium text-gray-900 lg:text-base">Position<span class="text-red-500">*</span></label>
                            <select name="position" id="position" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" required autocomplete="off" >
                                @foreach ($positions as $pos)
                                    @if ($staff->position_id == $pos->id)
                                        <option value="{{ $pos->id }}" selected>{{ $pos->name }}</option>
                                    @else
                                        <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                    @endif
                                   
                                @endforeach
                                
                            </select>
                        </div>
                        <div class="w-1/3">
                            <label for="sec_id" class="block text-sm font-medium text-gray-900 lg:text-base">Security Code<span class="text-red-500">*</span></label>
                            
                            <div class="relative">
                                <input type="password" id="sec_id" name="sec_id" value="{{ $staff->sec_id }}" class="block w-full px-4 py-2.5  text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" autocomplete="off" required>
                                    <button id="showPassword" type="button" class=" absolute right-2 bottom-1 ">
                                        <i class="uil uil-eye text-3xl"></i>
                                    </button>
                            </div>
                        </div>
                        <div class="w-1/3">
                            <!-- <label for="sec_id" class="block text-sm font-medium text-gray-900 lg:text-base">Salary Rate</label> -->
                            <input type="hidden" id="salary" name="salary" value="{{ $staff->salary }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" >
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <a href="{{ route('staff.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
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
            $('.notifButton').click(function(){
                $('.notif').addClass('hidden');
            });

            $('#showPassword').click(function(){
                if ($("#sec_id").prop("type") == 'text'){
                    $("#sec_id").prop("type", "password");
                } else {
                    $("#sec_id").prop("type", "text");
                }
                
            });
        });      
     </script>
</x-app-layout>
