<x-app-layout>

    @section('page_title', 'USER - REQUIREMENT')

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
                <form action="{{ route('user.requirement_update') }}" method="POST" enctype="multipart/form-data" class="grid">
                    @csrf
                    <input type="hidden" name="slug" value="{{ $user->slug }}">
                    <div class="mb-2">
                        <label for="name" class="block text-sm font-medium text-gray-900 lg:text-base">Name <span class="text-red-500"></span></label>
                        <input type="text" id="suppliername" name="suppliername" value="{{ $user->name }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" readonly>
                    </div>
                        <input type="hidden" id="supplierid" name="supplierid" value="{{ $user->id }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" readonly>
                    

                    <div class="mb-4 flex justify-start">
                        <button type="button" id="add-field" class="px-4 py-2 bg-blue-500 text-white rounded">
                            +
                        </button>
                    </div>
                    <div class="mb-2">
                        <div id="fields-container">
                            <div class="flex gap-4 row mb-2">
                                
                                 <div class="w-1/3">
                                    <select name="name[]" class="w-full h-[42px] px-3 py-2 border rounded text-sm">
                                        <option value="">Select Requirement</option>
                                        @foreach($suppreq as $suppreqs)
                                            <option value="{{ $suppreqs->id }}">{{ $suppreqs->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                               
                                <div class="w-1/3">
                                    <input type="file" name="file[]" class="w-full h-[42px] px-3 py-2 border rounded text-sm">
                                </div>

                                
                                <div class="w-1/3">
                                    <input type="text" name="remarks" placeholder="Remarks" class="w-full h-[42px] px-3 py-2 border rounded text-sm">
                                </div>
                                <button type="button" class="remove-field bg-red-500 text-white px-3 py-2 rounded">−</button>
                            </div>
                        </div>
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
            //leo start 3-12-2024
            $('#role').on('change', function() {
                var selectedRole = $(this).find(":selected").val();
                if (selectedRole == 4 || selectedRole == 9) {
                    $('#deptvid').attr("hidden", false);
                } else {
                    $('#deptvid').attr("hidden", true);
                }
            }); 
        });  
        
        

         $(document).ready(function() {
            $('#add-field').click(function() {
                const newRow = `
                    <div class="row flex gap-4 mb-2">
                        <div class="w-1/3">
                            <select name="name[]" class="w-full h-[42px] px-3 py-2 border rounded text-sm">
                                <option value="">Select Requirement</option>
                                @foreach($suppreq as $suppreqs)
                                    <option value="{{ $suppreqs->id }}">{{ $suppreqs->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-1/3">
                            <input type="file" name="file[]" class="w-full h-[42px] px-3 py-2 border rounded text-sm">
                        </div>

                        <div class="w-1/3">
                            <input type="text" name="remarks" placeholder="Remarks" class="w-full h-[42px] px-3 py-2 border rounded text-sm">
                        </div>

                        <button type="button" class="remove-field bg-red-500 text-white px-3 py-2 rounded">−</button>
                    </div>
                `;
                $('#fields-container').append(newRow);
            });
            $('#fields-container').on('click', '.remove-field', function () {
                $(this).closest('.row').remove();  
            });
        });

            
     </script>
</x-app-layout>
