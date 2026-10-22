<x-app-layout>
    @section('page_title', 'COMPANY')
    
    <div class="p-3 lg:ml-64 h-screen pt-14 lg:pt-3">
        <div class="py-3">
            <div class="bg-white overflow-x-hidden overflow-y-auto shadow-md rounded-lg p-5 h-[calc(100vh-48px)]">
                <form method="POST" action="{{ route('settings.store') }}" enctype="multipart/form-data" class="w-full h-full">
                    @csrf
                    <input class="hidden block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" name="logo" type="file" accept="image/*">
                    <div class="mb-3">
                        <label for="name" class="block text-sm font-medium text-gray-900">Restaurant Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="block text-sm font-medium text-gray-900">Address <span class="text-red-500">*</span></label>
                        <input type="text" name="address" value="{{ old('address') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off" required>
                    </div>
                    <div class="mb-3">
                        <label for="number" class="block text-sm font-medium text-gray-900">Contact Number</label>
                        <input type="text" name="number" value="{{ old('number') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="footer" class="block text-sm font-medium text-gray-900">Footer Message</label>
                        <input type="text" name="footer" value="{{ old('footer') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="block text-sm font-medium text-gray-900">Email</label>
                        <input type="text" name="email" value="{{ old('email') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="voidCode" class="block text-sm font-medium text-gray-900">Void,Reduce,Remove,Refund Code</label>
                        
                        <div class="relative mb-2">
                                <input type="password" name="voidCode" id="voidCode" class=" block w-full px-4 py-2.5  text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" required autocomplete="off" value="{{ old('voidCode') }}">
                                    <button id="showPassword" type="button" class=" absolute right-2 bottom-1">
                                        <i class="uil uil-eye text-3xl"></i>
                                    </button>
                                
                                    
                        </div>
                    </div>
                    <div class="mb-3 flex justify-between">
                        <a href="{{ route('settings.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-bold rounded-lg text-sm px-10 py-2.5 mr-2 mb-2 focus:outline-none w-1/2 max-w-xs">Save</button>
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

            $('#showPassword').click(function(){
                if ($("#voidCode").prop("type") == 'text'){
                    $("#voidCode").prop("type", "password");
                } else {
                    $("#voidCode").prop("type", "text");
                }
                
            });
        });
    </script>
</x-app-layout>
