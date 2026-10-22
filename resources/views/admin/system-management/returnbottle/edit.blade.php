<x-app-layout>
    <style>
        .product-preview-container {
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
        
        .product-preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .product-placeholder {
            text-align: center;
            color: #9ca3af;
        }
        
        .product-placeholder i {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        
        .product-placeholder p {
            font-size: 0.875rem;
        }
        
        .amount-input {
            position: relative;
        }
        
        .amount-input .prefix {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-weight: 500;
        }
        
        .current-product-container {
            width: 100px;
            height: 100px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9fafb;
            overflow: hidden;
            margin: 0 auto 1rem;
        }
        
        .current-product-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .bottle-icon {
            color: #3b82f6;
        }
        
        .date-input {
            cursor: pointer;
        }
    </style>

    @section('page_title', 'EDIT RETURN BOTTLE')
    
    <div class="p-3 lg:ml-64 h-screen pt-14 lg:pt-3">
        <div class="py-3">
            <div class="bg-white overflow-x-hidden overflow-y-auto shadow-md rounded-lg p-5 h-[calc(100vh-48px)]">
                <form method="POST" action="{{ route('returnbottles.update') }}" enctype="multipart/form-data" class="w-full h-full">
                    @csrf
                    <input type="hidden" name="id" value="{{ $returnBottle->id }}">
                    
                    <h2 class="text-lg font-bold text-gray-900 mb-6 pb-2 border-b">Edit Return Bottle Record</h2>

                    {{-- Return Date --}}
                    <div class="mb-4">
                        <label for="return_date" class="block text-sm font-medium text-gray-900 mb-1">
                            Return Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="return_date" 
                               id="return_date" 
                               value="{{ old('return_date', \Carbon\Carbon::parse($returnBottle->return_date)->format('Y-m-d')) }}" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 date-input" 
                               autocomplete="off" 
                               required>
                        @error('return_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Customer Name --}}
                    <div class="mb-4">
                        <label for="customer_name" class="block text-sm font-medium text-gray-900 mb-1">
                            Customer Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="customer_name" 
                               id="customer_name" 
                               value="{{ old('customer_name', $returnBottle->customer_name) }}" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                               placeholder="Enter customer name"
                               autocomplete="off" 
                               required>
                        @error('customer_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Product Information --}}
                    <div class="mb-4">
                        <label for="product_name" class="block text-sm font-medium text-gray-900 mb-1">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="product_name" 
                               id="product_name" 
                               value="{{ old('product_name', $returnBottle->product_name) }}" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                               placeholder="e.g., Coca-Cola 1.5L, San Miguel Beer"
                               autocomplete="off" 
                               required>
                        @error('product_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Bottle Quantity --}}
                    <div class="mb-4">
                        <label for="bottle_quantity" class="block text-sm font-medium text-gray-900 mb-1">
                            Bottle Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="bottle_quantity" 
                               id="bottle_quantity" 
                               value="{{ old('bottle_quantity', $returnBottle->bottle_quantity) }}" 
                               min="1"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                               placeholder="Enter number of bottles"
                               autocomplete="off" 
                               required>
                        <p class="text-xs text-gray-500 mt-1">
                            Number of bottles being returned
                        </p>
                        @error('bottle_quantity')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Refund Amount --}}
                    <div class="mb-6">
                        <label for="refund_amount" class="block text-sm font-medium text-gray-900 mb-1">
                            Refund Amount <span class="text-red-500">*</span>
                        </label>
                        <div class="amount-input">
                            <span class="prefix">₱</span>
                            <input type="number" 
                                   name="refund_amount" 
                                   id="refund_amount" 
                                   value="{{ old('refund_amount', number_format($returnBottle->refund_amount, 2)) }}" 
                                   step="0.01"
                                   min="0"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pl-10" 
                                   placeholder="0.00"
                                   autocomplete="off" 
                                   required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Total refund amount for the returned bottles
                        </p>
                        @error('refund_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Bottle Condition (Optional) --}}
                    <div class="mb-6">
                        <label for="condition" class="block text-sm font-medium text-gray-900 mb-1">
                            Bottle Condition (Optional)
                        </label>
                        <select name="condition" 
                                id="condition" 
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Select condition</option>
                            <option value="good" {{ old('condition', $returnBottle->condition) == 'good' ? 'selected' : '' }}>Good</option>
                            <option value="fair" {{ old('condition', $returnBottle->condition) == 'fair' ? 'selected' : '' }}>Fair</option>
                            <option value="damaged" {{ old('condition', $returnBottle->condition) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Optional: Condition of returned bottles
                        </p>
                    </div>

                    {{-- Notes (Optional) --}}
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-900 mb-1">
                            Notes (Optional)
                        </label>
                        <textarea name="notes" 
                                  id="notes" 
                                  rows="3"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                                  placeholder="Any additional notes about this return">{{ old('notes', $returnBottle->notes) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Optional: Additional information about this return
                        </p>
                    </div>

                    {{-- Status Information --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Status:</span>
                                <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $returnBottle->is_deleted ? 'bg-red-100 text-red-800' : ($returnBottle->status == 'returned' ? 'bg-emerald-100 text-emerald-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $returnBottle->is_deleted ? 'Deleted' : ($returnBottle->status == 'returned' ? 'Returned' : 'Pending') }}
                                </span>
                            </div>
                            @if($returnBottle->is_deleted)
                                <span class="text-xs text-red-600 font-medium">
                                    <i class="uil uil-exclamation-triangle mr-1"></i>This record is deleted
                                </span>
                            @endif
                        </div>
                        
                        @if(!$returnBottle->is_deleted && $returnBottle->status != 'returned')
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="uil uil-info-circle text-blue-600 mr-2"></i>
                                <p class="text-sm text-blue-700">
                                    This return is still pending. To mark as returned, use the "Mark as Returned" button on the list page.
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Additional Information --}}
                    <div class="mb-6 text-xs text-gray-500">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p>Created: {{ \Carbon\Carbon::parse($returnBottle->created_at)->format('M d, Y h:i A') }}</p>
                                <p>Last Updated: {{ \Carbon\Carbon::parse($returnBottle->updated_at)->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-8 flex justify-between space-x-4">
                        <a href="{{ route('returnbottles.index') }}" 
                           class="text-white w-1/2 max-w-xs bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 rounded-lg text-sm px-5 py-2.5 focus:outline-none tracking-wider font-semibold text-center">
                            <i class="uil uil-arrow-left mr-2"></i>CANCEL
                        </a>
                        <button type="submit" 
                                class="text-white w-1/2 max-w-xs bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-bold rounded-lg text-sm px-5 py-2.5 focus:outline-none">
                            <i class="uil uil-save mr-2"></i>UPDATE RECORD
                        </button>
                    </div>
                    
                    @if(!$returnBottle->is_deleted && $returnBottle->status != 'returned')
                    <div class="mt-4">
                        <a href="{{ route('returnbottles.complete', $returnBottle->id) }}" 
                           onclick="return confirm('Are you sure you want to mark this return as completed?')"
                           class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-300 font-bold rounded-lg text-sm px-5 py-2.5 focus:outline-none text-center block">
                            <i class="uil uil-check-circle mr-2"></i>MARK AS RETURNED
                        </a>
                    </div>
                    @endif
                    
                    @if(!$returnBottle->is_deleted)
                    <div class="mt-3">
                        <a href="{{ route('returnbottles.delete', $returnBottle->id) }}" 
                           onclick="return confirm('Are you sure you want to delete this return bottle record?')"
                           class="w-full text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-bold rounded-lg text-sm px-5 py-2.5 focus:outline-none text-center block">
                            <i class="uil uil-trash-alt mr-2"></i>DELETE RECORD
                        </a>
                    </div>
                    @endif
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

            // Format refund amount input
            $('#refund_amount').on('blur', function() {
                let value = parseFloat($(this).val());
                if (!isNaN(value)) {
                    $(this).val(value.toFixed(2));
                } else {
                    $(this).val('0.00');
                }
            });

            // Set initial refund amount if empty
            if (!$('#refund_amount').val()) {
                $('#refund_amount').val('0.00');
            }

            // Set max date to today for return date
            const today = new Date().toISOString().split('T')[0];
            $('#return_date').attr('max', today);

            // Calculate refund amount based on quantity (optional feature)
            // You can add this if you have a standard bottle refund rate
            /*
            $('#bottle_quantity').on('change', function() {
                const bottleRate = 5.00; // Set your bottle refund rate here
                const quantity = parseInt($(this).val()) || 0;
                const refundAmount = (bottleRate * quantity).toFixed(2);
                $('#refund_amount').val(refundAmount);
            });
            */
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const customerName = document.getElementById('customer_name').value.trim();
            const productName = document.getElementById('product_name').value.trim();
            const bottleQuantity = document.getElementById('bottle_quantity').value;
            const refundAmount = document.getElementById('refund_amount').value;
            const returnDate = document.getElementById('return_date').value;
            
            let errors = [];
            
            if (!customerName) {
                errors.push('Please enter customer name');
                document.getElementById('customer_name').focus();
            }
            
            if (!productName) {
                errors.push('Please enter product name');
                if (!errors[0]) document.getElementById('product_name').focus();
            }
            
            if (!bottleQuantity || parseInt(bottleQuantity) < 1) {
                errors.push('Please enter a valid bottle quantity (minimum 1)');
                if (!errors[0]) document.getElementById('bottle_quantity').focus();
            }
            
            if (!refundAmount || parseFloat(refundAmount) < 0) {
                errors.push('Please enter a valid refund amount');
                if (!errors[0]) document.getElementById('refund_amount').focus();
            }
            
            if (!returnDate) {
                errors.push('Please select a return date');
                if (!errors[0]) document.getElementById('return_date').focus();
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                alert(errors.join('\n'));
                return;
            }
            
            // Optional: Show loading indicator
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="uil uil-spinner animate-spin mr-2"></i>UPDATING...';
                submitBtn.disabled = true;
            }
        });
        
        // Confirm delete action
        function confirmDelete() {
            return confirm('Are you sure you want to delete this return bottle record? This action cannot be undone.');
        }
        
        // Confirm mark as returned action
        function confirmComplete() {
            return confirm('Are you sure you want to mark this return as completed? This will update the status to "Returned".');
        }
    </script>
</x-app-layout>