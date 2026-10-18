<x-app-layout>

    @section('page_title', 'USER - EDIT')

    <div class="p-3 lg:ml-64 max-h-screen lg:pt-3 overflow-y-auto">
        
        {{-- <header class="bg-white shadow-md rounded-lg">
            <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    INVENTORY - ADD
                </h2>
            </div>
        </header> --}}
    
        <!-- <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-3">
                <label class="block text-lg font-large text-gray-900 lg:text-base text-center">ACCOUNT UPDATE </label> -->
                <form action="{{ route('user.detail_update') }}" method="POST" enctype="multipart/form-data" class="grid">
                    @csrf
                    <input type="hidden" name="slug" value="{{ $user->slug }}">
                    <!-- <input type="hidden" name="slug" value="{{ $user->slug }}">
                    <div class="mb-2">
                        <label for="name" class="block text-sm font-medium text-gray-900 lg:text-base">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ $user->name }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="username" class="block text-sm font-medium text-gray-900 lg:text-base">Username <span class="text-red-500">*</span></label>
                        <input type="text" id="username" name="username" value="{{ $user->username }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>

                    <div class="mb-2">
                        <label for="role" class="block text-sm font-medium text-gray-900 lg:text-base">Role<span class="text-red-500">*</span></label>
                            <select id="role" name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base">
                                <option {{ ($user->role == '1') ? 'selected' : '' }} value="1">Administrator</option>
                                <option {{ ($user->role == '5') ? 'selected' : '' }} value="5">Manager</option>
                                <option {{ ($user->role == '6') ? 'selected' : '' }} value="6">Supervisor</option>
                                <option {{ ($user->role == '8') ? 'selected' : '' }} value="8">Admin</option>
                                <option {{ ($user->role == '2') ? 'selected' : '' }} value="2">Cashier</option>
                                <option {{ ($user->role == '3') ? 'selected' : '' }} value="3">Kitchen Officer</option>
                                <option {{ ($user->role == '9') ? 'selected' : '' }} value="9">Officer</option>
                                <option {{ ($user->role == '4') ? 'selected' : '' }} value="4">Reciever</option>
                                <option {{ ($user->role == '7') ? 'selected' : '' }} value="7">Server</option>
                            </select>
                    </div>

                    <div class="mb-2 flex justify-between">
                        <div class="w-1/2  mr-2">
                            <label for="role" class="block text-sm font-medium text-gray-900 lg:text-base">Table<span class="text-red-500">*</span></label>
                            <select id="table" name="table" value="{{ old('table') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base" >
                                @foreach ($tables as $table)
                                    @php
                                    $Tablebranches = DB::table('settings')->orderBy('name', 'asc')->where('id', $table->branch)->first();
                                    @endphp
                                    @if ($table->id == $user->table_id)
                                    <option value="{{ $table->id }}" selected>{{ $table->name . ' - ' . $Tablebranches->name }}</option>
                                    @else
                                    <option value="{{ $table->id }}">{{ $table->name . ' - ' . $Tablebranches->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="w-1/2">
                            <label for="role" class="block text-sm font-medium text-gray-900 lg:text-base">Branch<span class="text-red-500">*</span></label>
                            <select id="branch" name="branch" value="{{ old('branch') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base" >
                                @foreach ($branchs as $branch)
                                    @if ($branch->id !=1 )
                                        @if ($branch->id == $user->branch)
                                            <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>
                                        @else
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endif
                                        
                                    @endif
                                    
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-2" id="deptvid"  {{ ($user->role == '4' || $user->role == '9') ? '' : 'hidden' }}> 
                        <label for="department" class="block text-sm font-medium text-gray-900 lg:text-base">Department<span class="text-red-500">*</span></label>
                        <select id="department" name="department" value="{{ old('department') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 lg:text-base"> -->
                            <!-- <option {{ ($user->department == '1') ? 'selected' : '' }} value="1">All</option>
                            <option {{ ($user->department == '2') ? 'selected' : '' }} value="2">Bar</option>
                            <option {{ ($user->department == '3') ? 'selected' : '' }} value="3">Kitchen</option>
                            <option {{ ($user->department == '4') ? 'selected' : '' }} value="4">Stock Room</option>
                            <option {{ ($user->department == '5') ? 'selected' : '' }} value="5">Food Finds</option>
                            <option {{ ($user->department == '6') ? 'selected' : '' }} value="6">Dining</option>
                            <option {{ ($user->department == '7') ? 'selected' : '' }} value="7">Staff Meal</option> -->
                            <!-- @foreach ($depts as $dept)
                                @if ($dept->id <8)
                                    @if($dept->id == $user->department)
                                    <option value="{{ $dept->id }}" selected>{{ $dept->name }}</option>
                                    @else
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endif
                                    
                                @endif
                                    
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-900 lg:text-base">Password <span class="italic text-sm">*password must be atleast 8 characters</span></label>
                        <input type="password" id="password" name="password" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base">
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-900 lg:text-base">Password Confirmation</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base">
                    </div>
                    </div>
            </div> -->
            <!-- leo added 7/2/25 -->
            <div id="contentDiv" class="w-full p-2">
                <div class="p-3 overflow-hidden bg-white rounded-lg shadow-md">
                    <label class="block text-lg font-large text-gray-900 lg:text-base text-center">SUPPLIER INFORMATION </label>

                    <div class="mb-2">
                        <label for="company_name" class="block text-sm font-medium text-gray-900 lg:text-base">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" id="company_name" name="company_name" value="{{ $user->company_name }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="company_address" class="block text-sm font-medium text-gray-900 lg:text-base">Company Address <span class="text-red-500">*</span></label>
                        <input type="text" id="company_address" name="company_address" value="{{ $user->company_address }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="company_tin" class="block text-sm font-medium text-gray-900 lg:text-base">Company TIN Number <span class="text-red-500">*</span></label>
                        <input type="text" id="company_tin" name="company_tin" value="{{ $user->company_tin }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="company_email" class="block text-sm font-medium text-gray-900 lg:text-base">Company Email <span class="text-red-500">*</span></label>
                        <input type="email" id="company_email" name="company_email" value="{{ $user->company_email }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="company_no" class="block text-sm font-medium text-gray-900 lg:text-base">Company Contact Number <span class="text-red-500">*</span></label>
                        <input type="int" id="company_no" name="company_no" value="{{ $user->company_no }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="contact_person" class="block text-sm font-medium text-gray-900 lg:text-base">Contact Person <span class="text-red-500">*</span></label>
                        <input type="text" id="contact_person" name="contact_person" value="{{ $user->contact_person }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="contact_person_no" class="block text-sm font-medium text-gray-900 lg:text-base">Contact Person Number <span class="text-red-500">*</span></label>
                        <input type="text" id="contact_person_no" name="contact_person_no" value="{{ $user->contact_person_no }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="contact_person_email" class="block text-sm font-medium text-gray-900 lg:text-base">Contact Person Email<span class="text-red-500">*</span></label>
                        <input type="email" id="contact_person_email" name="contact_person_email" value="{{ $user->contact_person_email }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label for="terms" class="block text-sm font-medium text-gray-900 lg:text-base">Payment Terms <span class="text-red-500">*</span></label>
                        <select id="terms" name="terms" value="{{ $user->terms }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}" {{ $user->terms == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="mode_of_payment" class="block text-sm font-medium text-gray-900 lg:text-base">Mode of Payment <span class="text-red-500">*</span></label>
                        <select id="mode_of_payment" name="mode_of_payment" value="{{ $user->mode_of_payment }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                            @foreach($mops as $mop)
                                <option value="{{ $mop->id }}" {{ $user->mode_of_payment == $mop->id ? 'selected' : '' }}>
                                    {{ $mop->name }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    
                <!-- </div>
            </div>
            <div id="contentDiv" class="w-full p-2">
                <div class="p-3 overflow-hidden bg-white rounded-lg shadow-md">
                    <label class="block text-lg font-large text-gray-900 lg:text-base text-center">SUPPLIER REQUIREMENTS </label>

                    <div class="mb-2">
                        <label for="sales_invoice" class="block text-sm font-medium text-gray-900 lg:text-base">Sales Invoice <span class="text-red-500">*</span></label>
                        <input type="file" id="sales_invoice" name="sales_invoice" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs  focus:ring-blue-500 focus:border-blue-500 lg:text-base" accept="image/*" required>
                    </div> -->

                    <!-- <div class="mb-2">
                        <label for="business_permit" class="block text-sm font-medium text-gray-900 lg:text-base">Business Permit <span class="text-red-500">*</span></label>
                        <input type="file" id="business_permit" name="business_permit" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs  focus:ring-blue-500 focus:border-blue-500 lg:text-base" accept="image/*" required>
                    </div> -->
                    <!-- leo added 7/2/25 -->
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
            //leo start 3-12-2024
            $('#role').on('change', function() {
                var selectedRole = $(this).find(":selected").val();
                if (selectedRole == 4 || selectedRole == 9) {
                    $('#deptvid').attr("hidden", false);
                } else {
                    $('#deptvid').attr("hidden", true);
                }
            }); 
            //leo end 3-12-2024
        });      
     </script>
</x-app-layout>
