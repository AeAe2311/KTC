<x-app-layout>

    @section('page_title', 'USER - ADD')

    {{-- <div class="max-h-screen p-3 overflow-y-auto lg:ml-64 pt-14 lg:pt-3"> --}}
        
        {{-- <header class="bg-white rounded-lg shadow-md">
            <div class="px-4 py-3 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    INVENTORY - ADD
                </h2>
            </div>
        </header> --}}
    
    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="w-full p-2">
            <div class="p-3 overflow-hidden bg-white rounded-lg shadow-md">
                <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data" class="grid">
                    @csrf
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-2">
                        <label for="firstname" class="block text-sm font-medium text-gray-900 lg:text-base">Firstname <span class="text-red-500">*</span></label>
                        <input type="text" id="firstname" name="firstname" value="{{ old('firstname') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="middlename" class="block text-sm font-medium text-gray-900 lg:text-base">Middlename</label>
                        <input type="text" id="middlename" name="middlename" value="{{ old('middlename') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                    </div>
                    <div class="mb-2">
                        <label for="lastname" class="block text-sm font-medium text-gray-900 lg:text-base">Lastname <span class="text-red-500">*</span></label>
                        <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="suffix" class="block text-sm font-medium text-gray-900 lg:text-base">Suffix </label>
                        <select
                            id="suffix"
                            name="suffix"
                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                        >
                            <option value="">None</option>
                            <option value="Jr" {{ old('suffix') == 'Jr' ? 'selected' : '' }}>Jr.</option>
                            <option value="Sr" {{ old('suffix') == 'Sr' ? 'selected' : '' }}>Sr.</option>
                            <option value="II" {{ old('suffix') == 'II' ? 'selected' : '' }}>II</option>
                            <option value="III" {{ old('suffix') == 'III' ? 'selected' : '' }}>III</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="username" class="block text-sm font-medium text-gray-900 lg:text-base">Username <span class="text-red-500">*</span></label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="role" class="block text-sm font-medium text-gray-900 lg:text-base">Role<span class="text-red-500">*</span></label>
                        <select id="role" name="role" value="{{ old('role') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base" >
                            <option value="1">Administrator</option>
                            
                            <option value="2">Cashier</option>
                            <option value="3">Inventory Staff</option>
                            <option value="5">Accountant</option>
                        </select>
                    </div>

                    <div class="mb-2 flex justify-between">
                        
                        {{-- <div class="w-1/3 mr-2">
                            <label for="role" class="block text-sm font-medium text-gray-900 lg:text-base">Branch<span class="text-red-500">*</span></label>
                            <select id="branch" name="branch" value="{{ old('branch') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base" >
                                @foreach ($branchs as $branch)
                                    @if ($branch->id !=1 )
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endif
                                    
                                @endforeach
                            </select>
                        </div>
                        <div class="w-1/3">
                            <label for="is_account" class="block text-sm font-medium text-gray-900 lg:text-base">With Account<span class="text-red-500">*</span></label>
                            <select id="is_account" name="is_account"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base" >
                                <option value="">-- Select --</option>
                                <option value="1" {{ old('is_account', $user->is_account ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_account', $user->is_account ?? 0) == 0 ? 'selected' : '' }}>No</option>   
                            </select>
                        </div> --}}
                    </div>

                    <div class="mb-2" id="deptvid" hidden>
                        <label for="department" class="block text-sm font-medium text-gray-900 lg:text-base">Department<span class="text-red-500">*</span></label>
                        <select id="department" name="department" value="{{ old('department') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                            <!-- <option value="1">All</option>
                            <option value="2">Bar</option>
                            <option value="3">Kitchen</option>
                            <option value="4">Stock Room</option>
                            <option value="5">Food Finds</option>
                            <option value="6">Dining</option>
                            <option value="7">Staff Meal</option> -->
                            @foreach ($depts as $dept)
                                @if ($dept->id < 8)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-900 lg:text-base">Password <span class="text-red-500">*</span><span class="text-sm italic">password must be atleast 8 characters</span></label>
                        <input type="password" id="password" name="password" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" required>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-900 lg:text-base">Password Confirmation <span class="text-red-500">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" required>
                    </div>
                    <div class="flex justify-between">
                        <a href="{{ route('user.index') }}" class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold text-center">BACK</a>
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
            $('#role').on('change', function() {
                if ( $ (this).find(":selected").val() == 4){
                    $('#deptvid').attr("hidden", false);
                } else {
                    $('#deptvid').attr("hidden", true);
                }
            }); 
        });      
     </script>
</x-app-layout>
