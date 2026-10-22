<x-app-layout>
    @section('page_title', 'CREATE TRANSFORM')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-visible shadow-md rounded-lg p-4">
                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-700 bg-red-100 rounded-lg p-3">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 text-sm text-red-700 bg-red-100 rounded-lg p-3">
                        {{ session('error') }}
                    </div>
                @endif

                <form id="transformForm" action="{{ route('transform.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <input type="hidden" name="status" value="PENDING">
                            <input type="text" value="PENDING" readonly class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100">
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Source Item</label>
                            <div id="source_dropdown" class="relative mt-1">
                                <button type="button" id="source_dropdown_toggle" class="w-full text-left rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm flex items-center justify-between">
                                    <span id="source_dropdown_label">{{ optional($menus->firstWhere('id', old('source_inventory_id')))->name ? optional($menus->firstWhere('id', old('source_inventory_id')))->name . ' - ' . (optional($menus->firstWhere('id', old('source_inventory_id')))->brand->name ?? 'No Brand') . ' (' . optional($menus->firstWhere('id', old('source_inventory_id')))->unit . ')' : 'Select source item' }}</span>
                                    <span class="text-gray-400">▾</span>
                                </button>
                                <div id="source_dropdown_panel" class="hidden absolute z-50 mt-1 w-full rounded-lg border border-gray-300 bg-white shadow-lg">
                                    <div class="p-2">
                                        <input type="search" id="source_dropdown_search" placeholder="Search source items..." class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none" autocomplete="off">
                                    </div>
                                    <div id="source_dropdown_list" class="max-h-64 overflow-y-auto border-t border-gray-200">
                                        @foreach ($menus as $menu)
                                            <button type="button" class="source-option w-full text-left px-3 py-2 hover:bg-blue-50 focus:bg-blue-50 {{ $menu->quantity <= 0 ? 'text-gray-400 bg-gray-50 cursor-not-allowed' : '' }}" data-value="{{ $menu->id }}" data-unit="{{ $menu->unit }}" data-quantity="{{ $menu->quantity }}" data-label="{{ strtolower($menu->name . ' ' . optional($menu->brand)->name) }}" data-label-text="{{ $menu->name }} - {{ optional($menu->brand)->name ?? 'No Brand' }} ({{ $menu->unit }})" {{ $menu->quantity <= 0 ? 'disabled' : '' }}>
                                                {{ $menu->name }} - {{ optional($menu->brand)->name ?? 'No Brand' }} ({{ $menu->unit }})
                                                @if($menu->quantity <= 0)
                                                    - Out of stock
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="source_inventory_id" name="source_inventory_id" value="{{ old('source_inventory_id') }}" required>
                        </div>
                        <div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Source Quantity <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0" name="source_quantity" id="source_quantity_input" value="{{ old('source_quantity') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" required oninput="validateSourceQuantity()">
                                    <p id="source_available_quantity" class="mt-1 text-xs text-gray-500">Select a source item to see available quantity.</p>
                                    <p id="source_quantity_error" class="mt-1 text-xs text-red-600" style="display:none;"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Source Unit</label>
                                    <input type="text" id="source_unit" name="source_unit" value="{{ old('source_unit') }}" readonly class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100" placeholder="Select source item" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Remarks</label>
                        <textarea name="remarks" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">{{ old('remarks') }}</textarea>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h2 class="text-base font-semibold mb-3">Waste / Scrap</h2>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waste Quantity</label>
                                <input type="number" step="0.01" min="0" name="waste_quantity" value="{{ old('waste_quantity') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waste Unit</label>
                                <select name="waste_unit" class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm">
                                    <option value="">Select unit</option>
                                    <option value="kg" {{ old('waste_unit') === 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                    <option value="g" {{ old('waste_unit') === 'g' ? 'selected' : '' }}>Gram (g)</option>
                                    <option value="oz" {{ old('waste_unit') === 'oz' ? 'selected' : '' }}>Ounce (oz)</option>
                                    <option value="lb" {{ old('waste_unit') === 'lb' ? 'selected' : '' }}>Pound (lb)</option>
                                    <option value="L" {{ old('waste_unit') === 'L' ? 'selected' : '' }}>Liter (L)</option>
                                    <option value="ml" {{ old('waste_unit') === 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                                    <option value="pcs" {{ old('waste_unit') === 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                    <option value="box" {{ old('waste_unit') === 'box' ? 'selected' : '' }}>Box (box)</option>
                                    <option value="bag" {{ old('waste_unit') === 'bag' ? 'selected' : '' }}>Bag (bag)</option>
                                    <option value="pack" {{ old('waste_unit') === 'pack' ? 'selected' : '' }}>Pack (pack)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waste Description</label>
                                <input type="text" name="waste_description" value="{{ old('waste_description') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" placeholder="e.g. trimming loss">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-semibold">Transformed Outputs</h2>
                            </div>
                            <button type="button" id="addOutput" class="inline-flex items-center px-3 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add output
                            </button>
                        </div>

                        <div id="outputRows" class="space-y-3">
                            <div class="output-row space-y-3">
                                <div class="grid gap-4 md:grid-cols-4 items-end">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Output Item <span class="text-red-500">*</span></label>
                                        <div class="output_dropdown relative mt-1">
                                            <button type="button" class="output_dropdown_toggle w-full text-left rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm flex items-center justify-between">
                                                <span class="output_dropdown_label">Select item</span>
                                                <span class="text-gray-400">▾</span>
                                            </button>
                                            <div class="output_dropdown_panel hidden absolute z-50 mt-1 w-full rounded-lg border border-gray-300 bg-white shadow-lg">
                                                <div class="p-2">
                                                    <input type="search" class="output_dropdown_search w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none" placeholder="Search items..." autocomplete="off">
                                                </div>
                                                <div class="output_dropdown_list max-h-64 overflow-y-auto border-t border-gray-200">
                                                    @if(isset($menus) && $menus->count() > 0)
                                                        @foreach ($menus as $menu)
                                                            <button type="button" class="output-option w-full text-left px-3 py-2 hover:bg-blue-50 focus:bg-blue-50" data-value="{{ $menu->id }}" data-unit="{{ $menu->unit }}" data-quantity="{{ $menu->quantity }}" data-label="{{ strtolower($menu->name . ' ' . optional($menu->brand)->name) }}" data-label-text="{{ $menu->name }} - {{ optional($menu->brand)->name ?? 'No Brand' }} ({{ $menu->unit }})">
                                                                {{ $menu->name }} - {{ optional($menu->brand)->name ?? 'No Brand' }} ({{ $menu->unit }})
                                                            </button>
                                                        @endforeach
                                                    @else
                                                        <div class="px-3 py-2 text-gray-500 text-sm">No items available</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <select name="transformed_item_id[]" class="output_item_select hidden" required>
                                            <option value="">Select item</option>
                                            @if(isset($menus))
                                                @foreach ($menus as $menu)
                                                    <option value="{{ $menu->id }}">{{ $menu->name }} - {{ optional($menu->brand)->name ?? 'No Brand' }} ({{ $menu->unit }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantity <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" min="0.01" name="transformed_quantity[]" class="output_quantity_input mt-1 block w-full rounded-lg border-gray-300 shadow-sm" required>
                                        <p class="output_quantity_error mt-1 text-xs text-red-600" style="display:none;"></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Unit</label>
                                        <input type="text" name="transformed_unit[]" class="transformed-unit mt-1 block w-full rounded-lg border-gray-300 shadow-sm bg-gray-100" readonly placeholder="Select output item" required>
                                    </div>
                                    <div>
                                        <button type="button" class="remove-output inline-flex items-center justify-center w-full px-3 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700">Remove</button>
                                    </div>
                                </div>
                                <p class="output_available_quantity mt-1 text-xs text-gray-500">Select an item to see current stock.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 md:flex-row md:justify-between">
                        <a href="{{ route('transform.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-gray-900 bg-gray-200 rounded-lg hover:bg-gray-300">Back</a>
                        <button id="submit_transform" type="button" class="inline-flex items-center justify-center px-4 py-2 text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Submit Transform Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" style="background-color: rgba(107, 114, 128, 0.6);" aria-hidden="true"></div>

        <!-- Modal panel - centered -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirm Transform Submission
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Please review the following details before confirming:
                                </p>
                                <div class="mt-3 bg-gray-50 rounded-lg p-3">
                                    <dl class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <dt class="font-medium text-gray-700">Date:</dt>
                                            <dd id="confirm_date" class="text-gray-900"></dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="font-medium text-gray-700">Source Item:</dt>
                                            <dd id="confirm_source" class="text-gray-900"></dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="font-medium text-gray-700">Source Quantity:</dt>
                                            <dd id="confirm_source_qty" class="text-gray-900"></dd>
                                        </div>
                                        <div class="border-t pt-2 mt-2">
                                            <dt class="font-medium text-gray-700 mb-1">Output Items:</dt>
                                            <dd id="confirm_outputs" class="text-gray-900"></dd>
                                        </div>
                                        <div id="confirm_waste_row" class="flex justify-between hidden">
                                            <dt class="font-medium text-gray-700">Waste:</dt>
                                            <dd id="confirm_waste" class="text-gray-900"></dd>
                                        </div>
                                    </dl>
                                </div>
                                <!-- NOTE: Added confirmation message -->
                                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md">
                                    <p class="text-sm text-red-700 font-medium">
                                        ⚠️ Note: After approving this Transform you cannot revert it.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirmSubmit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm & Submit
                    </button>
                    <button type="button" id="cancelSubmit" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal handling
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmSubmitBtn = document.getElementById('confirmSubmit');
        const cancelSubmitBtn = document.getElementById('cancelSubmit');
        const transformForm = document.getElementById('transformForm');
        const submitTransformBtn = document.getElementById('submit_transform');

        function showConfirmationModal() {
            // Gather form data for display
            const dateInput = document.querySelector('input[name="date"]');
            const sourceLabel = document.getElementById('source_dropdown_label').textContent.trim();
            const sourceQty = document.getElementById('source_quantity_input').value;
            const sourceUnit = document.getElementById('source_unit').value;
            
            // Gather output items
            const outputRows = document.querySelectorAll('.output-row');
            let outputsHtml = '';
            outputRows.forEach((row, index) => {
                const itemLabel = row.querySelector('.output_dropdown_label').textContent.trim();
                const qty = row.querySelector('.output_quantity_input').value;
                const unit = row.querySelector('.transformed-unit').value;
                if (itemLabel !== 'Select item' && qty) {
                    outputsHtml += `<div class="flex justify-between text-xs">
                        <span>${itemLabel}</span>
                        <span>${qty} ${unit}</span>
                    </div>`;
                }
            });

            // Gather waste info
            const wasteQty = document.querySelector('input[name="waste_quantity"]').value;
            const wasteUnit = document.querySelector('select[name="waste_unit"]').value;
            const wasteDesc = document.querySelector('input[name="waste_description"]').value;

            // Populate modal
            document.getElementById('confirm_date').textContent = dateInput ? dateInput.value : 'N/A';
            document.getElementById('confirm_source').textContent = sourceLabel;
            document.getElementById('confirm_source_qty').textContent = `${sourceQty} ${sourceUnit}`;
            document.getElementById('confirm_outputs').innerHTML = outputsHtml || '<span class="text-gray-400">No outputs added</span>';
            
            const wasteRow = document.getElementById('confirm_waste_row');
            const wasteText = document.getElementById('confirm_waste');
            if (wasteQty && wasteUnit) {
                wasteRow.classList.remove('hidden');
                wasteText.textContent = `${wasteQty} ${wasteUnit}${wasteDesc ? ' - ' + wasteDesc : ''}`;
            } else {
                wasteRow.classList.add('hidden');
            }

            confirmationModal.classList.remove('hidden');
            // Prevent background scrolling when modal is open
            document.body.style.overflow = 'hidden';
        }

        function hideConfirmationModal() {
            confirmationModal.classList.add('hidden');
            // Re-enable background scrolling
            document.body.style.overflow = '';
        }

        submitTransformBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // First, validate source quantity
            const quantityInput = document.getElementById('source_quantity_input');
            const maxQuantity = parseFloat(quantityInput.max) || parseFloat(quantityInput.dataset.maxQuantity) || 0;
            const inputValue = parseFloat(quantityInput.value) || 0;

            if (maxQuantity > 0 && inputValue > maxQuantity) {
                validateSourceQuantity();
                alert(`Source quantity cannot exceed available stock of ${maxQuantity.toFixed(2)}`);
                quantityInput.focus();
                return;
            }

            // Check if form is valid
            if (!transformForm.checkValidity()) {
                transformForm.reportValidity();
                return;
            }

            // Show confirmation modal
            showConfirmationModal();
        });

        confirmSubmitBtn.addEventListener('click', function() {
            hideConfirmationModal();
            transformForm.submit();
        });

        cancelSubmitBtn.addEventListener('click', function() {
            hideConfirmationModal();
        });

        // Close modal when clicking the background overlay
        confirmationModal.addEventListener('click', function(e) {
            if (e.target === confirmationModal || e.target.classList.contains('fixed') && e.target.style.backgroundColor === 'rgba(107, 114, 128, 0.6)') {
                hideConfirmationModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !confirmationModal.classList.contains('hidden')) {
                hideConfirmationModal();
            }
        });

        function setSourceUnit() {
            const hiddenInput = document.getElementById('source_inventory_id');
            const unitInput = document.getElementById('source_unit');
            const selectedValue = hiddenInput.value;
            const selectedOption = document.querySelector(`#source_dropdown_list .source-option[data-value='${selectedValue}']`);

            if (selectedOption && selectedOption.dataset.unit) {
                unitInput.value = selectedOption.dataset.unit;
            } else {
                unitInput.value = '';
            }
        }

        function setSourceAvailableQuantity(selectedOption) {
            const availableElement = document.getElementById('source_available_quantity');
            const quantityInput = document.getElementById('source_quantity_input');
            const submitButton = document.getElementById('submit_transform');

            if (!selectedOption || !selectedOption.dataset.quantity) {
                availableElement.textContent = 'Select a source item to see available quantity.';
                quantityInput.removeAttribute('max');
                quantityInput.dataset.maxQuantity = '';
                if (submitButton) submitButton.disabled = false;
                return;
            }

            const quantity = parseFloat(selectedOption.dataset.quantity) || 0;
            const unit = selectedOption.dataset.unit || '';
            if (quantity <= 0) {
                availableElement.textContent = `Available quantity: 0 ${unit} — Out of stock`;
                quantityInput.value = '';
                quantityInput.disabled = true;
                quantityInput.removeAttribute('max');
                quantityInput.dataset.maxQuantity = '';
                if (submitButton) submitButton.disabled = true;
            } else {
                availableElement.textContent = `Available quantity: ${quantity.toFixed(2)} ${unit} (maximum allowed)`;
                quantityInput.disabled = false;
                quantityInput.max = quantity;
                quantityInput.dataset.maxQuantity = quantity;
                if (submitButton) submitButton.disabled = false;
            }
        }

        function setOutputAvailableQuantity(row, selectedOption) {
            const availableElement = row.querySelector('.output_available_quantity');
            const quantityInput = row.querySelector('.output_quantity_input');

            if (!selectedOption || !selectedOption.dataset.quantity) {
                availableElement.textContent = 'Select an item to see current stock.';
                quantityInput.removeAttribute('max');
                quantityInput.dataset.maxQuantity = '';
                return;
            }

            const quantity = parseFloat(selectedOption.dataset.quantity) || 0;
            const unit = selectedOption.dataset.unit || '';
            availableElement.textContent = `Current stock: ${quantity.toFixed(2)} ${unit} (output quantity is not limited by current stock)`;
            quantityInput.removeAttribute('max');
            quantityInput.dataset.maxQuantity = '';
        }

        function validateSourceQuantity() {
            const quantityInput = document.getElementById('source_quantity_input');
            const errorElement = document.getElementById('source_quantity_error');
            const maxQuantity = parseFloat(quantityInput.max) || parseFloat(quantityInput.dataset.maxQuantity) || 0;
            const inputValue = parseFloat(quantityInput.value) || 0;

            if (maxQuantity > 0 && inputValue > maxQuantity) {
                errorElement.textContent = `Quantity cannot exceed available stock of ${maxQuantity.toFixed(2)}`;
                errorElement.style.display = 'block';
                quantityInput.classList.add('border-red-500', 'bg-red-50');
                quantityInput.classList.remove('border-gray-300');
            } else {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                quantityInput.classList.remove('border-red-500', 'bg-red-50');
                quantityInput.classList.add('border-gray-300');
            }
        }

        function getSelectedOutputIds() {
            return Array.from(document.querySelectorAll('.output_item_select'))
                .map(select => select.value)
                .filter(value => value);
        }

        function filterOutputOptions() {
            const sourceId = document.getElementById('source_inventory_id').value;
            const selectedIds = getSelectedOutputIds();
            document.querySelectorAll('.output-row').forEach(row => {
                const currentValue = row.querySelector('.output_item_select')?.value;
                const optionButtons = Array.from(row.querySelectorAll('.output-option'));

                optionButtons.forEach(option => {
                    const isSource = sourceId && option.dataset.value === sourceId;
                    const isSelectedElsewhere = selectedIds.includes(option.dataset.value) && option.dataset.value !== currentValue;
                    option.classList.toggle('hidden', isSource || isSelectedElsewhere);
                });
            });
        }

        function clearInvalidOutputSelections(sourceId) {
            if (!sourceId) {
                return;
            }

            document.querySelectorAll('.output-row').forEach(row => {
                const hiddenInput = row.querySelector('.output_item_select');
                const labelSpan = row.querySelector('.output_dropdown_label');
                const unitInput = row.querySelector('.transformed-unit');
                const availableElement = row.querySelector('.output_available_quantity');
                const quantityInput = row.querySelector('.output_quantity_input');

                if (hiddenInput && hiddenInput.value === sourceId) {
                    hiddenInput.value = '';
                    if (labelSpan) {
                        labelSpan.textContent = 'Select item';
                    }
                    if (unitInput) {
                        unitInput.value = '';
                    }
                    if (availableElement) {
                        availableElement.textContent = 'Select an item to see current stock.';
                    }
                    if (quantityInput) {
                        quantityInput.removeAttribute('max');
                        quantityInput.dataset.maxQuantity = '';
                    }
                }
            });

            filterOutputOptions();
        }

        function attachOutputListeners(row) {
            const removeButton = row.querySelector('.remove-output');
            removeButton?.addEventListener('click', () => {
                if (document.querySelectorAll('.output-row').length > 1) {
                    row.remove();
                    filterOutputOptions();
                }
            });
        }

        function attachOutputDropdown(row) {
            const toggle = row.querySelector('.output_dropdown_toggle');
            const panel = row.querySelector('.output_dropdown_panel');
            const searchInput = row.querySelector('.output_dropdown_search');
            const optionButtons = Array.from(row.querySelectorAll('.output-option'));
            const hiddenInput = row.querySelector('.output_item_select');
            const labelSpan = row.querySelector('.output_dropdown_label');
            const unitInput = row.querySelector('.transformed-unit');

            function closePanel() {
                panel.classList.add('hidden');
            }

            function updateSelection(button) {
                const value = button.dataset.value;
                const label = button.dataset.labelText;
                const unit = button.dataset.unit;
                hiddenInput.value = value;
                labelSpan.textContent = label;
                if (unitInput && unit) {
                    unitInput.value = unit;
                }
                setOutputAvailableQuantity(row, button);
                closePanel();
                filterOutputOptions();
            }

            optionButtons.forEach(button => {
                button.addEventListener('click', () => {
                    updateSelection(button);
                });
            });

            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                filterOutputOptions();
                panel.classList.toggle('hidden');
                if (!panel.classList.contains('hidden')) {
                    searchInput.focus();
                }
            });

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();
                const sourceId = document.getElementById('source_inventory_id').value;
                optionButtons.forEach(option => {
                    const label = option.dataset.label;
                    const isSource = sourceId && option.dataset.value === sourceId;
                    const visible = !query || label.includes(query);
                    option.classList.toggle('hidden', isSource || !visible);
                });
            });

            document.addEventListener('click', (event) => {
                if (!row.contains(event.target)) {
                    closePanel();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            setSourceUnit();
            const outputRows = Array.from(document.querySelectorAll('.output-row'));
            outputRows.forEach(row => {
                attachOutputListeners(row);
                attachOutputDropdown(row);
            });

            const dropdown = document.getElementById('source_dropdown');
            const toggle = document.getElementById('source_dropdown_toggle');
            const panel = document.getElementById('source_dropdown_panel');
            const searchInput = document.getElementById('source_dropdown_search');
            const optionButtons = Array.from(document.querySelectorAll('#source_dropdown_list .source-option'));
            const hiddenInput = document.getElementById('source_inventory_id');
            const labelSpan = document.getElementById('source_dropdown_label');

            function closePanel() {
                panel.classList.add('hidden');
            }

            function updateSourceSelection(button) {
                const value = button.dataset.value;
                const label = button.dataset.labelText;
                const unit = button.dataset.unit;
                hiddenInput.value = value;
                labelSpan.textContent = label;
                document.getElementById('source_unit').value = unit;
                setSourceAvailableQuantity(button);
                filterOutputOptions();
                clearInvalidOutputSelections(value);
                closePanel();
            }

            optionButtons.forEach(button => {
                button.addEventListener('click', () => {
                    updateSourceSelection(button);
                });
            });

            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                panel.classList.toggle('hidden');
                if (!panel.classList.contains('hidden')) {
                    searchInput.focus();
                }
            });

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();
                optionButtons.forEach(option => {
                    const label = option.dataset.label;
                    const visible = !query || label.includes(query);
                    option.classList.toggle('hidden', !visible);
                });
            });

            document.addEventListener('click', (event) => {
                if (!dropdown.contains(event.target)) {
                    closePanel();
                }
            });

            if (hiddenInput.value) {
                const initial = optionButtons.find(option => option.dataset.value === hiddenInput.value);
                if (initial) {
                    labelSpan.textContent = initial.dataset.labelText;
                    document.getElementById('source_unit').value = initial.dataset.unit;
                    setSourceAvailableQuantity(initial);
                }
            }

            filterOutputOptions();

            document.getElementById('addOutput').addEventListener('click', () => {
                const container = document.getElementById('outputRows');
                const template = document.querySelector('.output-row');
                const clone = template.cloneNode(true);
                
                clone.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    if (input.classList.contains('output_quantity_input')) {
                        input.removeAttribute('max');
                        input.dataset.maxQuantity = '';
                        input.classList.remove('border-red-500', 'bg-red-50');
                        input.classList.add('border-gray-300');
                    }
                });
                
                clone.querySelectorAll('select').forEach(select => select.value = '');
                clone.querySelectorAll('.output_dropdown_label').forEach(label => label.textContent = 'Select item');
                clone.querySelectorAll('.output_dropdown_panel').forEach(panel => panel.classList.add('hidden'));
                clone.querySelectorAll('.output_available_quantity').forEach(el => el.textContent = 'Select an item to see current stock.');
                clone.querySelectorAll('.output_quantity_error').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
                
                container.appendChild(clone);
                attachOutputListeners(clone);
                attachOutputDropdown(clone);
                filterOutputOptions();
            });
        });
    </script>
</x-app-layout>