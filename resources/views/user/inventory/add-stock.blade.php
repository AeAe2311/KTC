<x-app-layout>
    @section('page_title', 'ADD STOCKS')

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
                <div class="mb-6 border-b pb-4">
                    <div class="flex justify-between items-center mb-4"> 
                        <div class="flex gap-2">   
                            <h1 class="text-3xl font-bold text-gray-900">Purchase Order</h1>
                        </div>
                        <div class="flex">
                            <a href="{{ route('pending.purchase.orders.index') }}"
                                class="inline-flex items-center gap-3 px-5 py-2.5 border border-blue-500 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 hover:shadow-md transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 flex-shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.25 12.75V7.5A2.25 2.25 0 0018 5.25H6A2.25 2.25 0 003.75 7.5v10.5A2.25 2.25 0 006 20.25h12A2.25 2.25 0 0020.25 18V12.75zm-16.5 0h4.19a1.5 1.5 0 011.06.44l1.06 1.06a1.5 1.5 0 001.06.44h1.76a1.5 1.5 0 001.06-.44l1.06-1.06a1.5 1.5 0 011.06-.44h4.19" />
                                </svg>
                                <span class="font-medium">Receiving Pending Approval</span>
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 text-sm font-bold text-white bg-red-500 rounded-full ring-2 ring-white">
                                    {{ $pendingOrders->where('status', 'pending')->count() }}
                                </span>
                            </a>
                        </div>                        
                    </div>    
                    <p class="text-gray-600 mt-1">Create a new purchase order for stock items</p>          
                </div>

                <form action="{{ route('stock.store') }}" method="POST" id="addStockForm" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="supplier_id" class="block text-sm font-semibold text-gray-700 mb-2">Supplier <span class="text-red-500">*</span></label>
                            <div class="relative" id="supplierSelectWrapper">
                                <select id="supplier_id" name="supplier_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="display: none;">
                                    <option value="">Select a supplier...</option>
                                    @foreach($suppliers->sortBy('name') as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <div id="supplierSearchContainer" class="relative">
                                    <input type="text" 
                                           id="supplierSearch" 
                                           placeholder="Search suppliers..." 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <div id="supplierSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto mt-1 hidden"></div>
                                </div>
                            </div>
                            @error('supplier_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="order_date" class="block text-sm font-semibold text-gray-700 mb-2">Order Date <span class="text-red-500">*</span></label>
                            <input type="date" id="order_date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="sales_invoice" class="block text-sm font-semibold text-gray-700 mb-2">Sales Invoice # <span class="text-red-500">*</span></label>
                            <input type="text" id="sales_invoice" name="sales_invoice" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>                        
                    </div>

                    <div class="mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">Stock Items</h2>
                            <button type="button" id="addItemBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <div class="overflow-x-auto shadow rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-left text-gray-700 uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3">Brand</th>
                                        <th class="px-4 py-3">Item Description</th>
                                        <th class="px-4 py-3 w-32">Quantity</th>
                                        <th class="px-4 py-3 w-24">Unit</th>
                                        <th class="px-4 py-3 w-32">Unit Price</th>
                                        <th class="px-4 py-3 w-32">Line Total</th>
                                        <th class="px-4 py-3 w-16">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <div class="w-full md:w-80 space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700 font-medium">Sub-total:</span>
                                    <span id="subtotal" class="text-gray-900 font-semibold">₱0.00</span>
                                </div>
                                <div class="flex justify-between text-lg border-t pt-2">
                                    <span class="text-gray-900 font-bold">Total Amount:</span>
                                    <span id="totalAmount" class="text-blue-600 font-bold">₱0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 justify-end mt-8 border-t pt-6">
                        <a href="{{ route('pending.purchase.orders.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</a>
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save & Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="addItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden">
            <div class="bg-blue-700 px-6 py-6">
                <h3 class="text-2xl font-bold text-white">Add Stock Item</h3>
                <p class="text-blue-100 text-sm mt-1">Complete all fields below</p>
            </div>

            <div class="px-6 py-6 space-y-5">
                <div>
                    <label for="modalItem" class="block text-base font-bold text-gray-900 mb-2">
                        Item (Please select an item before choosing a brand.)<span class="text-red-600">*</span>
                    </label>
                    <div class="relative" id="itemSelectWrapper">
                        <select id="modalItem" class="w-full px-4 py-3 pr-10 border-2 border-gray-400 rounded-lg focus:ring-3 focus:ring-blue-500 focus:border-blue-500 font-semibold text-base text-gray-800 bg-white appearance-none cursor-pointer searchable-select" style="display: none;">
                            <option value="">Select item...</option>
                        </select>
                        <div id="itemSearchContainer" class="relative">
                            <input type="text" 
                                   id="modalItemSearch" 
                                   placeholder="Search items..." 
                                   class="w-full px-4 py-3 pr-10 border-2 border-gray-400 rounded-lg focus:ring-3 focus:ring-blue-500 focus:border-blue-500 font-semibold text-base text-gray-800 bg-white">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <div id="itemSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto mt-1 hidden"></div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="modalBrand" class="block text-base font-bold text-gray-900 mb-2">
                        Brand <span class="text-red-600">*</span>
                    </label>
                    <div class="relative" id="brandSelectWrapper">
                        <select id="modalBrand" class="w-full px-4 py-3 pr-10 border-2 border-gray-400 rounded-lg focus:ring-3 focus:ring-blue-500 focus:border-blue-500 font-semibold text-base text-gray-800 bg-white appearance-none cursor-pointer searchable-select" disabled style="display: none;">
                            <option value="">Select brand...</option>
                        </select>
                        <div id="brandSearchContainer" class="relative">
                            <input type="text" 
                                   id="modalBrandSearch" 
                                   placeholder="Search brands..." 
                                   class="w-full px-4 py-3 pr-10 border-2 border-gray-400 rounded-lg focus:ring-3 focus:ring-blue-500 focus:border-blue-500 font-semibold text-base text-gray-800 bg-white" disabled>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <div id="brandSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto mt-1 hidden"></div>
                        </div>
                    </div>
                </div>
                
                <div id="currentStockDisplay" class="hidden bg-blue-50 border-l-4 border-blue-700 rounded p-3">
                    <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-1">Current Stock</p>
                    <p class="text-2xl font-bold text-blue-700"><span id="currentStock">0.000</span> units</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="modalQuantity" class="block text-base font-bold text-gray-900 mb-2">
                            Quantity <span class="text-red-600">*</span>
                        </label>
                        <input type="number" id="modalQuantity" min="0.001" step="0.001" class="w-full px-4 py-3 border-2 border-gray-400 rounded-lg focus:ring-3 focus:ring-blue-500 focus:border-blue-500 font-semibold text-base text-gray-800" placeholder="0.000">
                    </div>

                    <div>
                        <label for="modalPrice" class="block text-base font-bold text-gray-900 mb-2">
                            Unit Price <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 font-semibold">₱</span>
                            <input type="number" id="modalPrice" min="0" step="0.001" class="w-full pl-8 pr-4 py-3 border-2 border-gray-400 rounded-lg focus:ring-3 focus:ring-blue-500 focus:border-blue-500 font-semibold text-base text-gray-800" placeholder="0.000">
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-200">
                    <p class="text-sm font-bold text-gray-700 mb-1">Line Total</p>
                    <p class="text-3xl font-bold text-blue-700"><span id="lineTotalPreview">₱0.00</span></p>
                </div>
            </div>

            <div class="bg-gray-100 px-6 py-4 flex gap-3 justify-end border-t-2 border-gray-300">
                <button type="button" id="closeModalBtn" class="px-6 py-2.5 border-2 border-gray-600 text-gray-800 font-bold text-base rounded-lg hover:bg-gray-200 transition-all">Cancel</button>
                <button type="button" id="confirmAddItemBtn" class="px-6 py-2.5 bg-blue-700 text-white font-bold text-base rounded-lg hover:bg-blue-800 transition-all">Add Item</button>
            </div>
        </div>
    </div>

    @php
        $allMenus = $menus->map(function ($menu) {
            if ($menu->brand && $menu->brand->is_deleted == 0) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'brand_id' => $menu->brand_id,
                    'brand_name' => $menu->brand->name,
                    'price' => (float) $menu->price,
                    'current_quantity' => (float) ($menu->current_quantity ?? 0),
                    'unit' => $menu->unit ?? '',
                ];
            }
            return null;
        })->filter()->sortBy('name')->values();
    @endphp
    
    <script>
        let itemsData = [];
        const allMenus = @json($allMenus);
        let selectedItemName = '';
        let selectedBrandId = '';

        function formatPeso(amount) {
            return '₱' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function formatQuantity(value) {
            return parseFloat(value).toFixed(3);
        }

        function hasValidDecimalPlaces(value, maxDecimals = 3) {
            const str = String(value);
            if (!str.includes('.')) return true;
            const decimalPlaces = str.split('.')[1].length;
            return decimalPlaces <= maxDecimals;
        }

        function setupSupplierSearch() {
            const select = document.getElementById('supplier_id');
            const searchInput = document.getElementById('supplierSearch');
            const searchResults = document.getElementById('supplierSearchResults');
            const supplierSelectWrapper = document.getElementById('supplierSelectWrapper');
            
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                filterSupplierOptions(this.value || '');
            });
            
            searchInput.addEventListener('focus', function(e) {
                e.stopPropagation();
                filterSupplierOptions(this.value || '');
            });
            
            searchInput.addEventListener('input', function(e) {
                e.stopPropagation();
                filterSupplierOptions(this.value);
            });
            
            document.addEventListener('click', function(e) {
                if (!supplierSelectWrapper.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }

        function filterSupplierOptions(searchTerm) {
            const select = document.getElementById('supplier_id');
            const searchResults = document.getElementById('supplierSearchResults');
            const options = select.options;
            
            searchResults.innerHTML = '';
            
            if (searchTerm.trim() === '') {
                let found = false;
                for (let i = 1; i < options.length; i++) {
                    const option = options[i];
                    const div = document.createElement('div');
                    div.className = 'px-4 py-2 hover:bg-blue-50 cursor-pointer text-gray-700 border-b border-gray-100';
                    div.textContent = option.textContent;
                    div.dataset.value = option.value;
                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        select.value = this.dataset.value;
                        document.getElementById('supplierSearch').value = this.textContent;
                        select.dispatchEvent(new Event('change'));
                        searchResults.classList.add('hidden');
                    });
                    searchResults.appendChild(div);
                    found = true;
                }
                
                if (!found) {
                    searchResults.innerHTML = '<div class="px-4 py-2 text-gray-500 text-sm">No suppliers available</div>';
                }
                searchResults.classList.remove('hidden');
                return;
            }
            
            const term = searchTerm.toLowerCase().trim();
            let found = false;
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                if (option.textContent.toLowerCase().includes(term)) {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-2 hover:bg-blue-50 cursor-pointer text-gray-700 border-b border-gray-100';
                    div.textContent = option.textContent;
                    div.dataset.value = option.value;
                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        select.value = this.dataset.value;
                        document.getElementById('supplierSearch').value = this.textContent;
                        select.dispatchEvent(new Event('change'));
                        searchResults.classList.add('hidden');
                    });
                    searchResults.appendChild(div);
                    found = true;
                }
            }
            
            if (!found) {
                searchResults.innerHTML = '<div class="px-4 py-2 text-gray-500 text-sm">No suppliers found</div>';
            }
            
            searchResults.classList.remove('hidden');
        }

        function getUniqueItems() {
            const seen = new Set();
            const uniqueItems = [];
            const validMenus = allMenus.filter(item => {
                return item.brand_id && item.brand_name && item.brand_name !== '';
            });
            
            validMenus.forEach(item => {
                if (!seen.has(item.name)) {
                    seen.add(item.name);
                    uniqueItems.push(item);
                }
            });
            
            return uniqueItems.sort((a, b) => a.name.localeCompare(b.name));
        }

        function populateItemDropdown() {
            const select = document.getElementById('modalItem');
            const uniqueItems = getUniqueItems();
            
            select.innerHTML = '<option value="">Select item...</option>';
            uniqueItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                option.dataset.itemName = item.name;
                select.appendChild(option);
            });
        }

        function populateBrandDropdown(itemName) {
            const select = document.getElementById('modalBrand');
            select.innerHTML = '<option value="">Select brand...</option>';
            
            if (!itemName) {
                select.disabled = true;
                document.getElementById('modalBrandSearch').disabled = true;
                return;
            }

            const matchingItems = allMenus.filter(item => 
                item.name === itemName && 
                item.brand_id && 
                item.brand_name && 
                item.brand_name !== ''
            );
            
            const seenBrands = new Map();
            
            matchingItems.forEach(item => {
                if (!seenBrands.has(item.brand_id)) {
                    seenBrands.set(item.brand_id, {
                        id: item.brand_id,
                        name: item.brand_name,
                        menuId: item.id,
                        price: item.price || 0,
                        stock: item.current_quantity || 0,
                        unit: item.unit || ''
                    });
                }
            });
            
            const uniqueBrands = Array.from(seenBrands.values())
                .filter(brand => brand.id && brand.name)
                .sort((a, b) => a.name.localeCompare(b.name));
            
            if (uniqueBrands.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No valid brands available';
                select.appendChild(option);
                select.disabled = true;
                document.getElementById('modalBrandSearch').disabled = true;
                return;
            }
            
            uniqueBrands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand.id;
                option.textContent = brand.name;
                option.dataset.menuId = brand.menuId;
                option.dataset.price = brand.price;
                option.dataset.stock = brand.stock;
                option.dataset.unit = brand.unit;
                select.appendChild(option);
            });
            
            select.disabled = false;
            document.getElementById('modalBrandSearch').disabled = false;
        }

        function setupItemSearch() {
            const select = document.getElementById('modalItem');
            const searchInput = document.getElementById('modalItemSearch');
            const searchResults = document.getElementById('itemSearchResults');
            const itemSelectWrapper = document.getElementById('itemSelectWrapper');
            
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                filterItemOptions(this.value || '');
            });
            
            searchInput.addEventListener('focus', function(e) {
                e.stopPropagation();
                filterItemOptions(this.value || '');
            });
            
            searchInput.addEventListener('input', function(e) {
                e.stopPropagation();
                filterItemOptions(this.value);
            });
            
            document.addEventListener('click', function(e) {
                if (!itemSelectWrapper.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }
        
        function filterItemOptions(searchTerm) {
            const select = document.getElementById('modalItem');
            const searchResults = document.getElementById('itemSearchResults');
            const options = select.options;
            
            searchResults.innerHTML = '';
            
            if (searchTerm.trim() === '') {
                let found = false;
                for (let i = 1; i < options.length; i++) {
                    const option = options[i];
                    const div = document.createElement('div');
                    div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-gray-700 border-b border-gray-100';
                    div.textContent = option.textContent;
                    div.dataset.value = option.value;
                    div.dataset.itemName = option.dataset.itemName;
                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        select.value = this.dataset.value;
                        document.getElementById('modalItemSearch').value = this.textContent;
                        select.dispatchEvent(new Event('change'));
                        searchResults.classList.add('hidden');
                    });
                    searchResults.appendChild(div);
                    found = true;
                }
                
                if (!found) {
                    searchResults.innerHTML = '<div class="px-4 py-3 text-gray-500 text-sm">No items available</div>';
                }
                searchResults.classList.remove('hidden');
                return;
            }
            
            const term = searchTerm.toLowerCase().trim();
            let found = false;
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                if (option.textContent.toLowerCase().includes(term)) {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-gray-700 border-b border-gray-100';
                    div.textContent = option.textContent;
                    div.dataset.value = option.value;
                    div.dataset.itemName = option.dataset.itemName;
                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        select.value = this.dataset.value;
                        document.getElementById('modalItemSearch').value = this.textContent;
                        select.dispatchEvent(new Event('change'));
                        searchResults.classList.add('hidden');
                    });
                    searchResults.appendChild(div);
                    found = true;
                }
            }
            
            if (!found) {
                searchResults.innerHTML = '<div class="px-4 py-3 text-gray-500 text-sm">No items found</div>';
            }
            
            searchResults.classList.remove('hidden');
        }

        function setupBrandSearch() {
            const select = document.getElementById('modalBrand');
            const searchInput = document.getElementById('modalBrandSearch');
            const searchResults = document.getElementById('brandSearchResults');
            const brandSelectWrapper = document.getElementById('brandSelectWrapper');
            
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (select.disabled) {
                    alert('Please select an item first');
                    return;
                }
                filterBrandOptions(this.value || '');
            });
            
            searchInput.addEventListener('focus', function(e) {
                e.stopPropagation();
                if (select.disabled) {
                    alert('Please select an item first');
                    return;
                }
                filterBrandOptions(this.value || '');
            });
            
            searchInput.addEventListener('input', function(e) {
                e.stopPropagation();
                filterBrandOptions(this.value);
            });
            
            document.addEventListener('click', function(e) {
                if (!brandSelectWrapper.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }
        
        function filterBrandOptions(searchTerm) {
            const select = document.getElementById('modalBrand');
            const searchResults = document.getElementById('brandSearchResults');
            const options = select.options;
            
            searchResults.innerHTML = '';
            
            if (select.disabled) {
                searchResults.innerHTML = '<div class="px-4 py-3 text-gray-500 text-sm">Please select an item first</div>';
                searchResults.classList.remove('hidden');
                return;
            }
            
            if (searchTerm.trim() === '') {
                let found = false;
                for (let i = 1; i < options.length; i++) {
                    const option = options[i];
                    const div = document.createElement('div');
                    div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-gray-700 border-b border-gray-100';
                    div.textContent = option.textContent;
                    div.dataset.value = option.value;
                    div.dataset.menuId = option.dataset.menuId;
                    div.dataset.price = option.dataset.price;
                    div.dataset.stock = option.dataset.stock;
                    div.dataset.unit = option.dataset.unit;
                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        select.value = this.dataset.value;
                        document.getElementById('modalBrandSearch').value = this.textContent;
                        select.dispatchEvent(new Event('change'));
                        searchResults.classList.add('hidden');
                    });
                    searchResults.appendChild(div);
                    found = true;
                }
                
                if (!found) {
                    searchResults.innerHTML = '<div class="px-4 py-3 text-gray-500 text-sm">No brands available</div>';
                }
                searchResults.classList.remove('hidden');
                return;
            }
            
            const term = searchTerm.toLowerCase().trim();
            let found = false;
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                if (option.textContent.toLowerCase().includes(term)) {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer text-gray-700 border-b border-gray-100';
                    div.textContent = option.textContent;
                    div.dataset.value = option.value;
                    div.dataset.menuId = option.dataset.menuId;
                    div.dataset.price = option.dataset.price;
                    div.dataset.stock = option.dataset.stock;
                    div.dataset.unit = option.dataset.unit;
                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        select.value = this.dataset.value;
                        document.getElementById('modalBrandSearch').value = this.textContent;
                        select.dispatchEvent(new Event('change'));
                        searchResults.classList.add('hidden');
                    });
                    searchResults.appendChild(div);
                    found = true;
                }
            }
            
            if (!found) {
                searchResults.innerHTML = '<div class="px-4 py-3 text-gray-500 text-sm">No brands found</div>';
            }
            
            searchResults.classList.remove('hidden');
        }

        document.getElementById('modalItem').addEventListener('change', function() {
            const searchInput = document.getElementById('modalItemSearch');
            const brandSearch = document.getElementById('modalBrandSearch');
            const brandResults = document.getElementById('brandSearchResults');
            
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const itemName = selectedOption.dataset.itemName || '';
                selectedItemName = itemName;
                searchInput.value = selectedOption.textContent;
                populateBrandDropdown(itemName);
                document.getElementById('modalBrand').value = '';
                brandSearch.value = '';
                brandSearch.disabled = false;
                document.getElementById('modalPrice').value = '';
                document.getElementById('currentStockDisplay').classList.add('hidden');
                brandResults.classList.add('hidden');
            } else {
                document.getElementById('modalBrand').innerHTML = '<option value="">Select brand...</option>';
                document.getElementById('modalBrand').disabled = true;
                brandSearch.value = '';
                brandSearch.disabled = true;
                searchInput.value = '';
                selectedItemName = '';
            }
        });

        document.getElementById('modalBrand').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const brandSearch = document.getElementById('modalBrandSearch');
            
            if (!selectedOption || !selectedOption.value) {
                document.getElementById('currentStockDisplay').classList.add('hidden');
                brandSearch.value = '';
                return;
            }

            const stock = selectedOption.dataset.stock || 0;
            const price = selectedOption.dataset.price || 0;
            const unit = selectedOption.dataset.unit || '';
            const menuId = selectedOption.dataset.menuId || '';

            brandSearch.value = selectedOption.textContent;
            document.getElementById('currentStock').textContent = formatQuantity(stock);
            document.getElementById('currentStockDisplay').classList.remove('hidden');
            
            document.getElementById('modalPrice').value = parseFloat(price).toFixed(2);
            document.getElementById('modalPrice').dataset.originalPrice = price;
            
            document.getElementById('modalBrand').dataset.selectedUnit = unit;
            document.getElementById('modalBrand').dataset.selectedPrice = price;
            document.getElementById('modalBrand').dataset.menuId = menuId;
            
            updateLineTotal();
        });

        document.getElementById('modalQuantity').addEventListener('input', updateLineTotal);
        document.getElementById('modalPrice').addEventListener('input', updateLineTotal);

        function updateLineTotal() {
            const quantity = parseFloat(document.getElementById('modalQuantity').value) || 0;
            const price = parseFloat(document.getElementById('modalPrice').value) || 0;
            const lineTotal = quantity * price;
            document.getElementById('lineTotalPreview').textContent = formatPeso(lineTotal);
        }

        document.getElementById('addItemBtn').addEventListener('click', function() {
            document.getElementById('addItemModal').classList.remove('hidden');
            populateItemDropdown();
            
            document.getElementById('modalItemSearch').value = '';
            document.getElementById('itemSearchResults').classList.add('hidden');
            
            document.getElementById('modalBrand').innerHTML = '<option value="">Select brand...</option>';
            document.getElementById('modalBrand').disabled = true;
            document.getElementById('modalBrandSearch').value = '';
            document.getElementById('modalBrandSearch').disabled = true;
            document.getElementById('brandSearchResults').classList.add('hidden');
            
            document.getElementById('modalQuantity').value = '';
            document.getElementById('modalPrice').value = '';
            document.getElementById('currentStockDisplay').classList.add('hidden');
            document.getElementById('lineTotalPreview').textContent = '₱0.00';
            
            selectedItemName = '';
        });

        document.getElementById('closeModalBtn').addEventListener('click', function() {
            document.getElementById('addItemModal').classList.add('hidden');
        });

        document.getElementById('addItemModal').addEventListener('click', function(e) {
            if (e.target === this) {
                document.getElementById('addItemModal').classList.add('hidden');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('addItemModal').classList.add('hidden');
            }
        });

        document.getElementById('confirmAddItemBtn').addEventListener('click', function() {
            const brandSelect = document.getElementById('modalBrand');
            const selectedBrandOption = brandSelect.options[brandSelect.selectedIndex];
            const brandId = brandSelect.value;
            const menuId = document.getElementById('modalBrand').dataset.menuId || (selectedBrandOption ? selectedBrandOption.dataset.menuId : '');
            const quantityValue = document.getElementById('modalQuantity').value;
            const quantity = parseFloat(quantityValue);
            const price = parseFloat(document.getElementById('modalPrice').value);
            const unit = document.getElementById('modalBrand').dataset.selectedUnit || (selectedBrandOption ? selectedBrandOption.dataset.unit : '');
            const originalPrice = document.getElementById('modalBrand').dataset.selectedPrice || (selectedBrandOption ? selectedBrandOption.dataset.price : price);
            
            const itemSelect = document.getElementById('modalItem');
            const selectedItemOption = itemSelect.options[itemSelect.selectedIndex];
            const itemName = selectedItemOption ? selectedItemOption.textContent : '';
            const brandName = selectedBrandOption ? selectedBrandOption.textContent : '';

            if (!hasValidDecimalPlaces(quantityValue, 3)) {
                alert('Quantity can have at most 3 decimal places');
                return;
            }

            if (!brandId || !menuId || !quantity || quantity <= 0 || !price || price < 0) {
                alert('Please fill all fields correctly');
                return;
            }

            const lineTotal = quantity * price;

            itemsData.push({
                menu_id: menuId,
                brand_name: brandName,
                item_name: itemName,
                quantity: quantity,
                unit: unit,
                unit_price: price,
                original_price: parseFloat(originalPrice),
                lineTotal: lineTotal
            });

            addRowToTable(brandName, itemName, quantity, unit, price, lineTotal);
            updateTotals();

            document.getElementById('addItemModal').classList.add('hidden');
        });

        function addRowToTable(brand, item, quantity, unit, price, lineTotal) {
            const tbody = document.getElementById('itemsTableBody');
            const tr = document.createElement('tr');
            const rowIndex = itemsData.length - 1;
            
            tr.innerHTML = `
                <td class="px-4 py-3 text-gray-700">${brand}</td>
                <td class="px-4 py-3 text-gray-700">${item}</td>
                <td class="px-4 py-3"><input type="number" step="0.001" value="${formatQuantity(quantity)}" class="w-full px-2 py-1 border rounded qty-input" data-index="${rowIndex}"></td>
                <td class="px-4 py-3 text-gray-700">${unit}</td>
                <td class="px-4 py-3"><input type="number" step="0.01" value="${price.toFixed(2)}" class="w-full px-2 py-1 border rounded price-input" data-index="${rowIndex}"></td>
                <td class="px-4 py-3 line-total text-right font-semibold">${formatPeso(lineTotal)}</td>
                <td class="px-4 py-3 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 remove-btn" data-index="${rowIndex}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </td>
            `;
            
            tbody.appendChild(tr);

            tr.querySelector('.qty-input').addEventListener('change', function() {
                const index = parseInt(this.dataset.index);
                const newValue = this.value;
                
                if (!hasValidDecimalPlaces(newValue, 3)) {
                    alert('Quantity can have at most 3 decimal places');
                    this.value = formatQuantity(itemsData[index].quantity);
                    return;
                }
                
                const newQuantity = parseFloat(newValue) || 0;
                if (newQuantity <= 0) {
                    alert('Quantity must be greater than 0');
                    this.value = formatQuantity(itemsData[index].quantity);
                    return;
                }
                
                itemsData[index].quantity = newQuantity;
                itemsData[index].lineTotal = itemsData[index].quantity * itemsData[index].unit_price;
                tr.querySelector('.line-total').textContent = formatPeso(itemsData[index].lineTotal);
                updateTotals();
            });

            tr.querySelector('.qty-input').addEventListener('blur', function() {
                const index = parseInt(this.dataset.index);
                if (itemsData[index]) {
                    this.value = formatQuantity(itemsData[index].quantity);
                }
            });

            tr.querySelector('.price-input').addEventListener('change', function() {
                const index = parseInt(this.dataset.index);
                const newPrice = parseFloat(this.value) || 0;
                if (newPrice < 0) {
                    alert('Price cannot be negative');
                    this.value = itemsData[index].unit_price.toFixed(2);
                    return;
                }
                itemsData[index].unit_price = newPrice;
                itemsData[index].lineTotal = itemsData[index].quantity * itemsData[index].unit_price;
                tr.querySelector('.line-total').textContent = formatPeso(itemsData[index].lineTotal);
                updateTotals();
            });

            tr.querySelector('.remove-btn').addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                itemsData.splice(index, 1);
                tr.remove();
                updateTotals();
                updateRowIndices();
            });
        }

        function updateRowIndices() {
            const rows = document.querySelectorAll('#itemsTableBody tr');
            rows.forEach((row, index) => {
                const qtyInput = row.querySelector('.qty-input');
                const priceInput = row.querySelector('.price-input');
                const removeBtn = row.querySelector('.remove-btn');
                
                if (qtyInput) qtyInput.dataset.index = index;
                if (priceInput) priceInput.dataset.index = index;
                if (removeBtn) removeBtn.dataset.index = index;
            });
        }

        function updateTotals() {
            const subtotal = itemsData.reduce((sum, item) => sum + item.lineTotal, 0);
            document.getElementById('subtotal').textContent = formatPeso(subtotal);
            document.getElementById('totalAmount').textContent = formatPeso(subtotal);
        }

        document.getElementById('addStockForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (itemsData.length === 0) {
                alert('Please add at least one item');
                return;
            }

            const supplierId = document.getElementById('supplier_id').value;
            if (!supplierId) {
                alert('Please select a supplier');
                return;
            }

            const salesInvoice = document.getElementById('sales_invoice').value.trim();
            if (!salesInvoice) {
                alert('Please enter the sales invoice number');
                return;
            }

            itemsData.forEach((item, index) => {
                const menuIdInput = document.createElement('input');
                menuIdInput.type = 'hidden';
                menuIdInput.name = `items[${index}][menu_id]`;
                menuIdInput.value = item.menu_id;
                this.appendChild(menuIdInput);

                const quantityInput = document.createElement('input');
                quantityInput.type = 'hidden';
                quantityInput.name = `items[${index}][quantity]`;
                quantityInput.value = item.quantity;
                this.appendChild(quantityInput);

                const priceInput = document.createElement('input');
                priceInput.type = 'hidden';
                priceInput.name = `items[${index}][price]`;
                priceInput.value = item.unit_price;
                this.appendChild(priceInput);
                
                const unitInput = document.createElement('input');
                unitInput.type = 'hidden';
                unitInput.name = `items[${index}][unit]`;
                unitInput.value = item.unit || '';
                this.appendChild(unitInput);
                
                if (item.original_price) {
                    const originalPriceInput = document.createElement('input');
                    originalPriceInput.type = 'hidden';
                    originalPriceInput.name = `items[${index}][original_price]`;
                    originalPriceInput.value = item.original_price;
                    this.appendChild(originalPriceInput);
                }
            });

            this.submit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            setupSupplierSearch();
            populateItemDropdown();
            setupItemSearch();
            setupBrandSearch();
            
            const oldSupplierId = @json(old('supplier_id', ''));
            if (oldSupplierId) {
                const select = document.getElementById('supplier_id');
                const searchInput = document.getElementById('supplierSearch');
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value == oldSupplierId) {
                        select.value = oldSupplierId;
                        searchInput.value = select.options[i].textContent;
                        break;
                    }
                }
            }
            
            const oldItems = @json(old('items', []));
            if (oldItems && oldItems.length > 0) {
                loadSavedItems(oldItems);
            }
        });

        function loadSavedItems(items) {
            if (!items || items.length === 0) return;
            
            items.forEach(item => {
                const brandName = item.brand_name || '';
                const itemName = item.menu_name || item.item_name || '';
                const quantity = parseFloat(item.quantity) || 0;
                const unit = item.unit || '';
                const price = parseFloat(item.price) || 0;
                const lineTotal = quantity * price;
                
                itemsData.push({
                    menu_id: item.menu_id,
                    brand_name: brandName,
                    item_name: itemName,
                    quantity: quantity,
                    unit: unit,
                    unit_price: price,
                    original_price: price,
                    lineTotal: lineTotal
                });
                
                addRowToTable(brandName, itemName, quantity, unit, price, lineTotal);
            });
            
            updateTotals();
        }
    </script>
</x-app-layout>