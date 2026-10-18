<x-app-layout>

    @section('page_title', 'Clocking')
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

    {{-- Enter secure MODAL --}}
                
        <!-- Main modal -->
        <div id="secureCodeModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="font-semibold text-gray-900 flex items-center">
                            <i class="uil uil-exclamation-circle mr-2 text-xl md:text-2xl lg:text-3xl text-blue-700"></i>
                            <span class="text-blue-700 text-base md:text-lg lg:text-xl">Additional Information</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="secureCodeModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                     @csrf
                    <div class="px-6 py-3 md:py-6 space-y-6">
                        <p class="text-xs md:text-base leading-relaxed text-gray-500">
                            Enter your secure code.
                        </p>
                        <input type="text" id="sec_id" name="sec_id" value="{{ old('sec_id') }}" class="block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off" required>
                    </div>
                    <!-- Modal footer -->
                    <div class="flex justify-between px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="button"  class="submitSecureCode w-1/2 text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Yes</button>
                        <button data-modal-hide="secureCodeModal" type="button" id="closesecuremodal" class=" w-1/2 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    {{-- Enter secure MODAL END --}}

    {{-- picture MODAL --}}
                
        <!-- Main modal -->
         <button id="showpicturemodalBtn" type="button" data-modal-target="pictureModal" data-modal-toggle="pictureModal" class="hidden"></button>
        <div id="pictureModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] md:h-full">
            <div class="relative w-full h-full max-w-2xl md:h-auto">
                <!-- Modal content -->
                <div class="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 w-full max-w-md bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-4 py-2 border-b rounded-t">
                        <h3 class="font-semibold text-gray-900 flex items-center">
                            <i class="uil uil-exclamation-circle mr-2 text-xl md:text-2xl lg:text-3xl text-blue-700"></i>
                            <span class="text-blue-700 text-base md:text-lg lg:text-xl">Additional Information</span>
                        </h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-modal-hide="pictureModal">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                        </button>
                    </div>
                    <!-- Modal body -->
                    <form action="{{ route('staff.StoreClocking') }}" method="POST" enctype="multipart/form-data" class="grid">
                     @csrf
                    <div class="px-6 py-3 md:py-6 space-y-6 items-center">
                        <p class="text-xs md:text-base leading-relaxed text-gray-500">
                            Name : <span id="emp_name"> </span>
                        </p>
                        <img src=""  alt="imagebox" width="300px" height="300px" id="picdiv">
                        {{--<button type="button"  class=" w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" onclick="$('#imageUploadbtn').click();">Reupload</button>--}}
                        <input type="file" id="imageUploadbtn" name="imageUploadbtn" accept="image/*" capture="user" class="form-control" hidden/>
                        <input type="hidden" name="staff_id" id="staff_id">
                    </div>
                    
                    <!-- Modal footer -->
                    <div class="flex justify-between px-6 py-3 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit"  class="submitClockingBtn w-1/2 text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" data-id="">Submit</button>
                        <button data-modal-hide="pictureModal" type="button" class="w-1/2 text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    {{-- picture MODAL END --}}

    <div class="p-3 {{ auth()->user()->role == 1 ? 'lg:ml-64 lg:pt-3' : 'h-[calc(100vh-48px)] w-screen' }}">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-3">
                <!-- <form action="{{-- route('staff.store') --}}" method="POST" enctype="multipart/form-data" class="grid"> -->
                    
                    <div class="w-100%">
                        <button type="button" data-modal-target="secureCodeModal" data-modal-toggle="secureCodeModal" class="text-white w-full bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 focus:outline-none tracking-wider font-semibold" >Time In/Out</button>
                         
                        <!-- onclick="document.getElementById('imageUploadbtn').click()" -->
                    </div>

                <!-- </form> -->
            </div>
        </div>
     </div>

     
</x-app-layout>
<script>
        $(document).ready(function() {

            $('#sec_id').on('keydown', function(event) {
                if (event.keyCode === 13) {
                    $('.submitSecureCode').click();
                    event.preventDefault();
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
            $('.notifButton').click(function(){
                $('.notif').addClass('hidden');
            });
            jQuery(document).on("click", ".submitSecureCode", function(){
                //var slug = $(this).data('slug');
                var _token = $('input[name="_token"]').val();
                var sec = $('#sec_id').val();
                //alert(sec);
                $.ajax({
                    url:"{{ route('staff.check') }} ",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        sec: sec,
                        _token: _token
                    },
                    success:function(result){
                        //$('#ordersCount').html(result.ordersCount);
                        if( result.message == 'error' ){
                            alert('Security code is incorrect, Please try again!')
                        } else {
                            
                            $('#closesecuremodal').click();
                            $('#emp_name').html(result.fname + ' ' + result.lname)
                            $('#staff_id').val(result.id)
                            
                            $('#imageUploadbtn').click();
                        }
                    }
                })
                        
                });
                
                {{--jQuery(document).on("click", ".submitClockingBtn", function(){
                var id = $(this).data('id');
                var _token = $('input[name="_token"]').val();
                //var sec = $('#sec_id').val();
                //alert(id);
                $.ajax({
                    url:"{{ route('staff.StoreClocking') }} ",
                    method:"POST",
                    dataType: 'json',
                    data:{
                        id: id,
                        _token: _token
                    },
                    success:function(result){
                        //$('#ordersCount').html(result.ordersCount);
                        
                    }
                })
                        
                });--}}
                /* jQuery(document).on("change", "#imageUploadbtn", function(){
                    //$('#picdiv').attr('src',$('#imageUploadbtn').files[0]);
                    var reader = new FileReader();
                    reader.readAsDataURL(files[0]);
                    reader.onloadend = function(){
				    $("#picdiv").attr("src", this.result);
			        }
                }); */
                $("#imageUploadbtn").change(function(){
                    var files = !!this.files ? this.files : [];
                    if(!files.length || !window.FileReader){
                        $("#picdiv").remove();
                        //$lbl.appendTo("#preview");
                    }
                    if(/^image/.test(files[0].type)){
                        var reader = new FileReader();
                        reader.readAsDataURL(files[0]);
                        reader.onloadend = function(){
                            //$pic.appendTo("#preview");
                            $("#picdiv").attr("src", this.result);
                            
                            
                            
                        }
                        var img = $("#picdiv").attr("src", this.result);
                        var newSize = scaleSize(250, 320, img[0].width, img[0].height);
                        img.width(newSize[0]).height(newSize[1]);
                    }
                    $('#showpicturemodalBtn').click();
                    
                });
                
                function scaleSize(maxW, maxH, currW, currH){
                var ratio = currH / currW;
                if(currW >= maxW && ratio <= 1){
                    currW = maxW;
                    currH = currW * ratio;
                } else if(currH >= maxH){
                    currH = maxH;
                    currW = currH / ratio;
                }
                return [currW, currH];
                }
        });      
     </script>