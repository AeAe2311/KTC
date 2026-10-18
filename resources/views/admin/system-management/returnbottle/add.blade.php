<x-app-layout>
    <style>
        .logo-preview-container {
            width: 150px;
            height: 150px;
            border: 2px dashed #e5e7eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9fafb;
            overflow: hidden;
            margin: 0 auto;
        }
        
        .logo-preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .logo-placeholder {
            text-align: center;
            color: #9ca3af;
        }
        
        .logo-placeholder i {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        
        .logo-placeholder p {
            font-size: 0.875rem;
        }
        
        .fee-input {
            position: relative;
        }
        
        .fee-input .prefix {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-weight: 500;
        }
    </style>

    @section('page_title', 'ADD ONLINE BANK')
    
    <div class="p-3 lg:ml-64 h-screen pt-14 lg:pt-3">
        <div class="py-3">
            <div class="bg-white overflow-x-hidden overflow-y-auto shadow-md rounded-lg p-5 h-[calc(100vh-48px)]">
                <form method="POST" action="{{ route('onlinebank.store') }}" enctype="multipart/form-data" class="w-full h-full">
                    @csrf
                    
                    {{-- Logo Upload Section --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-900 mb-2">Bank Logo</label>
                        <div class="logo-preview-container mb-3">
                            <div id="logoPreview" class="logo-placeholder">
                                <i class="uil uil-university text-4xl text-gray-400"></i>
                                <p>No logo selected</p>
                            </div>
                            <img id="logoPreviewImage" class="logo-preview hidden" alt="Logo preview">
                        </div>
                        
                        <input type="file" 
                               name="logo" 
                               id="logoInput" 
                               accept="image/*" 
                               class="hidden"
                               onchange="previewLogo(event)">
                        
                        <div class="flex justify-center space-x-3">
                            <button type="button" 
                                    onclick="document.getElementById('logoInput').click()" 
                                    class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2.5">
                                <i class="uil uil-image-upload mr-2"></i>Upload Logo
                            </button>
                            <button type="button" 
                                    id="removeLogoBtn" 
                                    class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2.5 hidden">
                                <i class="uil uil-trash-alt mr-2"></i>Remove Logo
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 text-center mt-2">
                            Recommended: 500x500px, PNG or JPG format
                        </p>
                    </div>

                    {{-- Bank Name --}}
                    <div class="mb-4">
                        <label for="bank_name" class="block text-sm font-medium text-gray-900 mb-1">
                            Bank Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="bank_name" 
                               id="bank_name" 
                               value="{{ old('bank_name') }}" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                               placeholder="e.g., BDO, BPI, Metrobank"
                               autocomplete="off" 
                               required>
                        @error('bank_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Fee --}}
                    <div class="mb-6">
                        <label for="fee" class="block text-sm font-medium text-gray-900 mb-1">
                            Transaction Fee <span class="text-red-500">*</span>
                        </label>
                        <div class="fee-input">
                            <span class="prefix">₱</span>
                            <input type="number" 
                                   name="fee" 
                                   id="fee" 
                                   value="{{ old('fee', '0.00') }}" 
                                   step="0.01"
                                   min="0"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pl-10" 
                                   placeholder="0.00"
                                   autocomplete="off" 
                                   required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Enter the transaction fee charged by this bank
                        </p>
                        @error('fee')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Information Note --}}
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="uil uil-info-circle text-blue-600 text-xl mr-2 mt-0.5"></i>
                            <div>
                                <p class="text-sm text-blue-800 font-medium">Information</p>
                                <p class="text-xs text-blue-700 mt-1">
                                    Store code, branch code, and UUID will be automatically generated in the backend.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-8 flex justify-between space-x-4">
                        <a href="{{ route('onlinebank.index') }}" 
                           class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 focus:outline-none tracking-wider font-semibold text-center">
                            <i class="uil uil-arrow-left mr-2"></i>BACK
                        </a>
                        <button type="submit" 
                                class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-bold rounded-lg text-sm px-5 py-2.5 focus:outline-none">
                            <i class="uil uil-save mr-2"></i>SAVE BANK
                        </button>
                    </div>
                </form>
            </div>
        </div>
     </div>

    <script>
        $(document).ready(function() {
            // Mobile navigation handling
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

            // Format fee input
            $('#fee').on('blur', function() {
                let value = parseFloat($(this).val());
                if (!isNaN(value)) {
                    $(this).val(value.toFixed(2));
                } else {
                    $(this).val('0.00');
                }
            });

            // Set initial fee value if empty
            if (!$('#fee').val()) {
                $('#fee').val('0.00');
            }
        });

        // Logo preview functionality
        function previewLogo(event) {
            const input = event.target;
            const preview = document.getElementById('logoPreview');
            const previewImage = document.getElementById('logoPreviewImage');
            const removeBtn = document.getElementById('removeLogoBtn');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.classList.add('hidden');
                    previewImage.src = e.target.result;
                    previewImage.classList.remove('hidden');
                    removeBtn.classList.remove('hidden');
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Remove logo functionality
        document.getElementById('removeLogoBtn')?.addEventListener('click', function() {
            const logoInput = document.getElementById('logoInput');
            const preview = document.getElementById('logoPreview');
            const previewImage = document.getElementById('logoPreviewImage');
            const removeBtn = document.getElementById('removeLogoBtn');
            
            // Reset file input
            logoInput.value = '';
            
            // Show placeholder
            preview.classList.remove('hidden');
            previewImage.classList.add('hidden');
            previewImage.src = '';
            removeBtn.classList.add('hidden');
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const bankName = document.getElementById('bank_name').value.trim();
            const fee = document.getElementById('fee').value;
            
            if (!bankName) {
                e.preventDefault();
                alert('Please enter a bank name');
                document.getElementById('bank_name').focus();
                return;
            }
            
            if (!fee || parseFloat(fee) < 0) {
                e.preventDefault();
                alert('Please enter a valid fee amount');
                document.getElementById('fee').focus();
                return;
            }
            
            // Optional: Show loading indicator
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="uil uil-spinner animate-spin mr-2"></i>SAVING...';
            submitBtn.disabled = true;
        });
    </script>
</x-app-layout>