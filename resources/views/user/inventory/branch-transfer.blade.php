<x-app-layout>
    @section('page_title', 'BRANCH TRANSFER')

    <style>
        .scrollbar-hidden {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-hidden::-webkit-scrollbar {
            display: none;
        }

        #modalPrice::-webkit-outer-spin-button,
        #modalPrice::-webkit-inner-spin-button,
        #modalQuantity::-webkit-outer-spin-button,
        #modalQuantity::-webkit-inner-spin-button,
        .breakdown-input::-webkit-outer-spin-button,
        .breakdown-input::-webkit-inner-spin-button,
        .qty-input::-webkit-outer-spin-button,
        .qty-input::-webkit-inner-spin-button,
        .price-input::-webkit-outer-spin-button,
        .price-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        #modalPrice,
        #modalQuantity,
        .breakdown-input,
        .qty-input,
        .price-input {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        #addItemDialog {
            position: relative;
            z-index: 30;
        }

        #quantityBreakdownPanel {
            position: fixed !important;
            left: var(--quantity-breakdown-right, 9999px) !important;
            right: auto !important;
            top: var(--quantity-breakdown-top, 0px) !important;
            bottom: auto !important;
            transform: translateX(32px);
            transition: transform 220ms ease, opacity 180ms ease;
            pointer-events: none;
            z-index: 60;
        }

        #quantityBreakdownPanel.breakdown-open {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        #insufficientStockModal {
            z-index: 99999;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        #rowQuantityBreakdownPanel {
            z-index: 70;
        }

        .breakdown-scroll {
            max-height: 260px;
        }
    </style>

    <div class="p-3 lg:ml-64 lg:pt-3">
        <div id="contentDiv" class="p-2 w-full">
            <div class="bg-white overflow-hidden shadow-md rounded-lg p-6">
                <div class="mb-6 border-b pb-4">
                    <div class="flex justify-between items-center mb-4"> 
                        <div class="flex gap-2">   
                            <h1 class="text-3xl font-bold text-gray-900">Location Transfer</h1>
                        </div>
                        <div class="flex">
                            <a href="{{ route('branch-transfer.pending') }}"
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
                                <span class="font-medium">Location Transfer Pending Approval</span>
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 text-sm font-bold text-white bg-red-500 rounded-full ring-2 ring-white">
                                    {{ $pendingOrders->where('order_status', 'PENDING')->count() }}
                                </span>
                            </a>
                        </div>                        
                    </div>    
                    <p class="text-gray-600 mt-1">Create a new location transfer request for stock items</p>          
                </div>

                <form action="{{ route('branch-transfer.store') }}" method="POST" id="addStockForm" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="branch_id" class="block text-sm font-semibold text-gray-700 mb-2">Branch <span class="text-red-500">*</span></label>
                            <div class="relative" id="branchSelectWrapper">
                                <select id="branch_id" name="branch_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="display: none;">
                                    <option value="">Select a branch...</option>
                                    @foreach($branches->sortBy('name') as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div id="branchSearchContainer" class="relative">
                                    <input type="text"
                                           id="branchSearch"
                                           placeholder="Search branches..."
                                           autocomplete="off"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <div id="branchSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto mt-1 hidden"></div>
                                </div>
                            </div>
                            @error('branch_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="order_date" class="block text-sm font-semibold text-gray-700 mb-2">Request Date <span class="text-red-500">*</span></label>
                            <input type="date" id="order_date" name="order_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="sales_invoice" class="block text-sm font-semibold text-gray-700 mb-2">Transaction # <span class="text-red-500">*</span></label>
                            <input type="text" id="sales_invoice" name="sales_invoice" value="{{ old('sales_invoice', $transactionNumber) }}" required readonly placeholder="Auto-generated from logged-in branch" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-700 cursor-not-allowed focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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

                        <div class="overflow-x-auto scrollbar-hidden shadow rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-left text-gray-700 uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3">Brand</th>
                                        <th class="px-4 py-3">Price Type</th>
                                        <th class="px-4 py-3">Item Description</th>
                                        <th class="px-4 py-3 w-32">Quantity</th>
                                        <th class="px-4 py-3 w-24">Unit</th>
                                        <th class="px-4 py-3 w-32 text-center">Unit Price</th>
                                        <th class="px-4 py-3 w-32 text-center">Buying Price</th>
                                        <th class="px-4 py-3 w-32">Added Price</th>
                                        <th class="px-4 py-3 w-32 text-center">Line Total</th>
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
                        <a href="{{ route('branch-transfer.add') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</a>
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
        <div id="addItemModalStage" class="relative w-full max-w-2xl">
        <div id="addItemDialog" class="bg-white rounded-xl shadow-2xl w-full overflow-visible">
            <div class="bg-blue-700 px-6 py-6">
                <h3 class="text-2xl font-bold text-white">Add Stock Item</h3>
                <p class="text-blue-100 text-sm mt-1">Complete all fields below</p>
            </div>

            <div class="px-6 py-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="modalItem" class="block text-base font-bold text-gray-900 mb-2">
                            Item <span class="text-red-600">*</span>
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
                                <button type="button" id="clearModalItemSearch" class="hidden absolute inset-y-0 right-0 items-center pr-3 text-gray-400 hover:text-gray-700" aria-label="Clear item search">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <div id="modalItemSearchIcon" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
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
                                <button type="button" id="clearModalBrandSearch" class="hidden absolute inset-y-0 right-0 items-center pr-3 text-gray-400 hover:text-gray-700 disabled:pointer-events-none" aria-label="Clear brand search">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <div id="modalBrandSearchIcon" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <div id="brandSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="block text-base font-bold text-gray-900 mb-2"> Select Selling Price Types<span class="text-red-600">*</span></p>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex items-center gap-2 px-3 py-2 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="modal_price_type" value="wholesale" data-label="Wholesale" class="text-blue-700 focus:ring-blue-500">
                            <span id="wholesalePriceOption" class="text-sm font-semibold text-gray-800">Wholesale</span>
                        </label>

                        <label class="flex items-center gap-2 px-3 py-2 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="modal_price_type" value="retail" data-label="Retail" class="text-blue-700 focus:ring-blue-500">
                            <span id="retailPriceOption" class="text-sm font-semibold text-gray-800">Retail</span>
                        </label>

                        <label class="flex items-center gap-2 px-3 py-2 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" name="modal_price_type" value="slab" data-label="Slab" class="text-blue-700 focus:ring-blue-500">
                            <span id="slabPriceOption" class="text-sm font-semibold text-gray-800">Slab</span>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="priceTypePrice" id="priceTypePriceLabel" class="block text-base font-bold text-gray-900 mb-2">
                            Selling Price
                        </label>
                        <div class="relative">
                            <input type="number" id="priceTypePrice" min="0" step="0.001" disabled class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-100 text-gray-600 font-semibold text-base cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label for="buyingPriceDisplay" class="block text-base font-bold text-gray-900 mb-2">
                            Buying Price
                        </label>
                        <div class="relative">
                            <input type="text" id="buyingPriceDisplay" readonly disabled class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-100 text-gray-600 font-semibold text-base cursor-not-allowed" placeholder="Select an item to load">
                        </div>
                    </div>

                    <div>
                        <label for="modalPrice" class="block text-base font-bold text-gray-900 mb-2">
                            Added Price 
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 font-semibold">₱</span>
                            <input type="text" id="modalPrice" inputmode="numeric" maxlength="7" pattern="[0-9]*" class="w-full pl-8 pr-4 py-3 border-2 border-gray-400 rounded-lg focus:ring-3 focus:ring-blue-500 focus:border-blue-500 font-semibold text-base text-gray-800" placeholder="0">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-end gap-3 overflow-visible">
                            <div class="relative shrink-0">
                                <button type="button" id="openQuantityBreakdownBtn" disabled class="w-20 h-20 shrink-0 border-2 border-gray-300 bg-gray-100 text-gray-400 rounded-none font-bold text-xs leading-tight cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Open quantity breakdown">
                                    Qty.<br>Breakdown
                                </button>

                                <div id="quantityBreakdownPanel" class="absolute right-full mr-3 top-0 w-40 h-72 bg-white shadow-2xl border border-gray-300 opacity-0 flex flex-col z-50" aria-hidden="true">
                                    <div class="px-4 py-3 border-b border-gray-300 bg-white shrink-0">
                                        <h4 class="text-base font-bold text-gray-900">Qty. Breakdown</h4>
                                    </div>

                                    <div id="quantityBreakdownRows" class="breakdown-scroll flex-1 overflow-y-auto px-4 py-3 space-y-2 bg-white">
                                        <div class="flex items-center gap-2 breakdown-row">
                                            <input type="number" min="0" step="0.001" class="breakdown-input flex-1 min-w-0 px-3 py-2 border-2 border-gray-300 rounded-none font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.000" data-had-value="false">
                                            <button type="button" class="add-breakdown-row w-8 h-8 border-2 border-blue-600 text-blue-700 rounded-full text-xl leading-none font-bold hover:bg-blue-50" aria-label="Add breakdown line">+</button>
                                        </div>
                                    </div>

                                    <div class="px-3 py-3 border-t-2 border-gray-900 bg-white shrink-0">
                                        <div class="grid grid-cols-[1fr_auto] gap-3">
                                            <div class="border-2 border-gray-900 px-3 py-2 text-sm font-bold text-blue-600 bg-white flex items-center justify-center">
                                                <span id="quantityBreakdownTotal">0.000</span>
                                            </div>
                                            <button type="button" id="applyQuantityBreakdownBtn" class="px-4 py-2 bg-blue-600 text-white font-bold text-sm hover:bg-blue-700">OK</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <label for="modalQuantity" class="block text-base font-bold text-gray-900 mb-2">
                                    Quantity <span class="text-red-600">*</span>
                                </label>
                                <input type="number" id="modalQuantity" min="0.001" step="0.001" readonly class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed font-semibold text-base text-gray-700" placeholder="0.000">
                            </div>
                        </div>
                    </div>

                    <div id="currentStockDisplay" class="hidden bg-blue-50 border-l-4 border-blue-700 rounded p-3">
                        <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-1">Current Stock</p>
                        <p class="text-2xl font-bold text-blue-700"><span id="currentStock">0.000</span> units</p>
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
    </div>

    <div id="insufficientStockModal" class="hidden fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm border border-red-200">
            <div class="px-6 py-5 text-center">
                <h3 class="text-xl font-bold text-red-700 mb-2">Insufficient Stock Quantity.</h3>
                <button type="button" id="closeInsufficientStockModal" class="mt-4 px-6 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700">OK</button>
            </div>
        </div>
    </div>

    <div id="rowQuantityBreakdownPanel" class="hidden fixed w-40 h-72 bg-white shadow-2xl border border-gray-300 flex flex-col">
        <div class="px-4 py-3 border-b border-gray-300 bg-white shrink-0">
            <h4 class="text-base font-bold text-gray-900">Qty. Breakdown</h4>
        </div>

        <div id="rowQuantityBreakdownRows" class="breakdown-scroll flex-1 overflow-y-auto px-4 py-3 space-y-2 bg-white"></div>

        <div class="px-3 py-3 border-t-2 border-gray-900 bg-white shrink-0">
            <div class="grid grid-cols-[1fr_auto] gap-3">
                <div class="border-2 border-gray-200 px-3 py-2 text-sm font-bold text-blue-600 bg-white flex items-center justify-center">
                    <span id="rowQuantityBreakdownTotal">0.000</span>
                </div>
                <button type="button" id="applyRowQuantityBreakdownBtn" class="px-4 py-2 bg-blue-600 text-white font-bold text-sm hover:bg-blue-700">OK</button>
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
                    'wholesale_price' => (float) ($menu->wholesale_price ?? 0),
                    'retail_price' => (float) ($menu->retail_price ?? 0),
                    'slab_price' => (float) ($menu->slab_price ?? 0),
                    'buying_price' => $menu->buying_price !== null ? (float) $menu->buying_price : null,
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
        const transactionNumber = @json(old('sales_invoice', $transactionNumber));
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

        const ADDED_PRICE_MAX_DIGITS = 7;

        function sanitizeAddedPriceValue(value) {
            return String(value ?? '').replace(/\D/g, '').slice(0, ADDED_PRICE_MAX_DIGITS);
        }

        function formatAddedPriceForInput(value) {
            const numeric = parseFloat(value);
            if (Number.isFinite(numeric)) {
                return sanitizeAddedPriceValue(Math.trunc(numeric));
            }

            return sanitizeAddedPriceValue(value);
        }

        function getAddedPriceNumber(value) {
            const sanitized = sanitizeAddedPriceValue(value);
            return sanitized === '' ? 0 : parseInt(sanitized, 10);
        }

        function bindAddedPriceInput(input, onSanitized) {
            if (!input) return;

            const sanitize = () => {
                const sanitized = sanitizeAddedPriceValue(input.value);
                if (input.value !== sanitized) {
                    input.value = sanitized;
                }

                if (typeof onSanitized === 'function') {
                    onSanitized(getAddedPriceNumber(sanitized));
                }
            };

            input.addEventListener('input', sanitize);
            input.addEventListener('paste', () => setTimeout(sanitize, 0));
        }

        function setupBranchSearch() {
            const select = document.getElementById('branch_id');
            const searchInput = document.getElementById('branchSearch');
            const searchResults = document.getElementById('branchSearchResults');
            const branchSelectWrapper = document.getElementById('branchSelectWrapper');
            
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                filterBranchOptions(this.value || '');
            });
            
            searchInput.addEventListener('focus', function(e) {
                e.stopPropagation();
                filterBranchOptions(this.value || '');
            });
            
            searchInput.addEventListener('input', function(e) {
                e.stopPropagation();
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value && this.value.trim() !== selectedOption.textContent.trim()) {
                    select.value = '';
                    updateTransactionNumber();
                }

                selectExactBranchMatch(this.value);
                filterBranchOptions(this.value);
            });

            searchInput.addEventListener('change', function() {
                selectExactBranchMatch(this.value);
            });

            searchInput.addEventListener('blur', function() {
                selectExactBranchMatch(this.value);
            });

            select.addEventListener('change', updateTransactionNumber);
            
            document.addEventListener('click', function(e) {
                if (!branchSelectWrapper.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }

        function selectBranchOption(option) {
            const select = document.getElementById('branch_id');
            const searchInput = document.getElementById('branchSearch');

            select.value = option.value;
            searchInput.value = option.textContent;
            select.dispatchEvent(new Event('change'));
        }

        function selectExactBranchMatch(searchTerm) {
            const select = document.getElementById('branch_id');
            const term = searchTerm.trim().toLowerCase();

            if (!term) {
                select.value = '';
                updateTransactionNumber();
                return false;
            }

            for (let i = 1; i < select.options.length; i++) {
                const option = select.options[i];
                if (option.textContent.trim().toLowerCase() === term) {
                    selectBranchOption(option);
                    return true;
                }
            }

            return false;
        }

        function updateTransactionNumber() {
            const transactionInput = document.getElementById('sales_invoice');
            transactionInput.value = transactionNumber || '';
        }

        function filterBranchOptions(searchTerm) {
            const select = document.getElementById('branch_id');
            const searchResults = document.getElementById('branchSearchResults');
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
                        selectBranchOption(option);
                        searchResults.classList.add('hidden');
                    });
                    searchResults.appendChild(div);
                    found = true;
                }
                
                if (!found) {
                    searchResults.innerHTML = '<div class="px-4 py-2 text-gray-500 text-sm">No branches available</div>';
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
                        selectBranchOption(option);
                        searchResults.classList.add('hidden');
                    });
                    searchResults.appendChild(div);
                    found = true;
                }
            }
            
            if (!found) {
                searchResults.innerHTML = '<div class="px-4 py-2 text-gray-500 text-sm">No branches found</div>';
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
                        wholesalePrice: item.wholesale_price || 0,
                        retailPrice: item.retail_price || 0,
                        slabPrice: item.slab_price || 0,
                        buyingPrice: item.buying_price,
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
                option.dataset.wholesalePrice = brand.wholesalePrice;
                option.dataset.retailPrice = brand.retailPrice;
                option.dataset.slabPrice = brand.slabPrice;
                option.dataset.buyingPrice = brand.buyingPrice;
                option.dataset.stock = brand.stock;
                option.dataset.unit = brand.unit;
                select.appendChild(option);
            });
            
            select.disabled = false;
            document.getElementById('modalBrandSearch').disabled = false;
        }

        function toggleModalSearchClearButton(inputId, buttonId, iconId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            const icon = document.getElementById(iconId);

            if (!input || !button) {
                return;
            }

            const showClearButton = !input.disabled && input.value.length > 0;
            button.classList.toggle('hidden', !showClearButton);
            button.classList.toggle('flex', showClearButton);

            if (icon) {
                icon.classList.toggle('hidden', showClearButton);
                icon.classList.toggle('flex', !showClearButton);
            }
        }

        function toggleItemSearchClearButton() {
            toggleModalSearchClearButton('modalItemSearch', 'clearModalItemSearch', 'modalItemSearchIcon');
        }

        function toggleBrandSearchClearButton() {
            toggleModalSearchClearButton('modalBrandSearch', 'clearModalBrandSearch', 'modalBrandSearchIcon');
        }

        function clearBrandModalState(disableBrandSearch = false) {
            const brandSelect = document.getElementById('modalBrand');
            const brandSearch = document.getElementById('modalBrandSearch');

            brandSelect.value = '';
            brandSearch.value = '';
            brandSearch.disabled = disableBrandSearch;
            brandSelect.disabled = disableBrandSearch;
            document.getElementById('brandSearchResults').classList.add('hidden');
            document.getElementById('currentStockDisplay').classList.add('hidden');
            document.getElementById('priceTypePrice').value = '';
            document.getElementById('buyingPriceDisplay').value = '';
            document.getElementById('modalPrice').value = '';

            delete brandSelect.dataset.selectedUnit;
            delete brandSelect.dataset.selectedPrice;
            delete brandSelect.dataset.originalMenuPrice;
            delete brandSelect.dataset.menuId;
            delete brandSelect.dataset.wholesalePrice;
            delete brandSelect.dataset.retailPrice;
            delete brandSelect.dataset.slabPrice;
            delete brandSelect.dataset.buyingPrice;
            delete brandSelect.dataset.currentStock;

            updateSellingPriceTypeOptionLabels();
            toggleBrandSearchClearButton();
            resetModalQuantityState();
            updateQuantityBreakdownButtonState();
        }

        function hasValidBreakdownTarget() {
            const brandSelect = document.getElementById('modalBrand');
            return Boolean(
                document.getElementById('modalItem').value &&
                brandSelect.value &&
                brandSelect.dataset.menuId
            );
        }

        function updateQuantityBreakdownButtonState() {
            const button = document.getElementById('openQuantityBreakdownBtn');
            const enabled = hasValidBreakdownTarget();
            const open = isQuantityBreakdownOpen();

            button.disabled = !enabled;
            button.className = [
                'w-20 h-20 shrink-0 border-2 rounded-none font-bold text-xs leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500',
                !enabled
                    ? 'border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed'
                    : open
                        ? 'border-blue-700 bg-blue-700 text-white shadow-md'
                        : 'border-blue-600 bg-white text-blue-700 hover:bg-blue-50'
            ].join(' ');

            if (!enabled && open) {
                closeQuantityBreakdown();
            }
        }

        function resetModalQuantityState() {
            document.getElementById('modalQuantity').value = formatQuantity(0);
            resetQuantityBreakdown();
            updateLineTotal();
        }

        function getSelectedPriceType() {
            const selectedPriceType = document.querySelector('input[name="modal_price_type"]:checked');

            return selectedPriceType ? selectedPriceType.value : '';
        }

        function getBrandPriceForType(priceType) {
            const brandSelect = document.getElementById('modalBrand');
            const prices = {
                wholesale: brandSelect.dataset.wholesalePrice,
                retail: brandSelect.dataset.retailPrice,
                slab: brandSelect.dataset.slabPrice,
            };

            return parseFloat(prices[priceType]) || 0;
        }

        function updateSellingPriceTypeOptionLabels() {
            const brandSelect = document.getElementById('modalBrand');
            const priceTypeOptionLabels = {
                wholesale: document.getElementById('wholesalePriceOption'),
                retail: document.getElementById('retailPriceOption'),
                slab: document.getElementById('slabPriceOption'),
            };

            Object.entries(priceTypeOptionLabels).forEach(([priceType, optionLabel]) => {
                const label = document.querySelector(`input[name="modal_price_type"][value="${priceType}"]`).dataset.label;
                optionLabel.textContent = brandSelect.value
                    ? `${label} ${formatPeso(getBrandPriceForType(priceType))}`
                    : label;
            });
        }

        function updatePriceTypePrice() {
            const selectedPriceType = document.querySelector('input[name="modal_price_type"]:checked');
            const priceTypePriceInput = document.getElementById('priceTypePrice');
            const buyingPriceDisplay = document.getElementById('buyingPriceDisplay');
            const brandSelect = document.getElementById('modalBrand');
            const hasSelectedBrand = Boolean(brandSelect.value);

            updateSellingPriceTypeOptionLabels();

            const buyingPrice = Number.parseFloat(brandSelect.dataset.buyingPrice);
            buyingPriceDisplay.value = Number.isFinite(buyingPrice) ? formatPeso(buyingPrice) : '';

            if (!selectedPriceType || !hasSelectedBrand) {
                priceTypePriceInput.value = '';
                brandSelect.dataset.selectedPrice = 0;
                updateLineTotal();
                return;
            }

            const price = getBrandPriceForType(selectedPriceType.value);
            priceTypePriceInput.value = price.toFixed(2);
            brandSelect.dataset.selectedPrice = price;
            updateLineTotal();
        }

        function clearItemModalState() {
            document.getElementById('modalItem').value = '';
            document.getElementById('modalItemSearch').value = '';
            document.getElementById('itemSearchResults').classList.add('hidden');
            selectedItemName = '';

            const brandSelect = document.getElementById('modalBrand');
            brandSelect.innerHTML = '<option value="">Select brand...</option>';
            clearBrandModalState(true);
            toggleItemSearchClearButton();
            updateQuantityBreakdownButtonState();
        }

        function setupModalSearchClearButtons() {
            const clearItemButton = document.getElementById('clearModalItemSearch');
            const clearBrandButton = document.getElementById('clearModalBrandSearch');

            clearItemButton.addEventListener('click', function(e) {
                e.stopPropagation();
                clearItemModalState();
                document.getElementById('modalItemSearch').focus();
            });

            clearBrandButton.addEventListener('click', function(e) {
                e.stopPropagation();
                clearBrandModalState(false);
                document.getElementById('modalBrandSearch').focus();
            });
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
                toggleItemSearchClearButton();

                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value && this.value.trim() !== selectedOption.textContent.trim()) {
                    select.value = '';
                    selectedItemName = '';
                    document.getElementById('modalBrand').innerHTML = '<option value="">Select brand...</option>';
                    clearBrandModalState(true);
                }

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
                        toggleItemSearchClearButton();
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
                        toggleItemSearchClearButton();
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
                const typedValue = this.value;
                toggleBrandSearchClearButton();

                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value && typedValue.trim() !== selectedOption.textContent.trim()) {
                    clearBrandModalState(false);
                    this.value = typedValue;
                    toggleBrandSearchClearButton();
                }

                filterBrandOptions(typedValue);
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
                    div.dataset.wholesalePrice = option.dataset.wholesalePrice;
                    div.dataset.retailPrice = option.dataset.retailPrice;
                    div.dataset.slabPrice = option.dataset.slabPrice;
                    div.dataset.buyingPrice = option.dataset.buyingPrice;
                    div.dataset.stock = option.dataset.stock;
                    div.dataset.unit = option.dataset.unit;
                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        select.value = this.dataset.value;
                        document.getElementById('modalBrandSearch').value = this.textContent;
                        toggleBrandSearchClearButton();
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
                    div.dataset.wholesalePrice = option.dataset.wholesalePrice;
                    div.dataset.retailPrice = option.dataset.retailPrice;
                    div.dataset.slabPrice = option.dataset.slabPrice;
                    div.dataset.buyingPrice = option.dataset.buyingPrice;
                    div.dataset.stock = option.dataset.stock;
                    div.dataset.unit = option.dataset.unit;
                    div.addEventListener('click', function(e) {
                        e.stopPropagation();
                        select.value = this.dataset.value;
                        document.getElementById('modalBrandSearch').value = this.textContent;
                        toggleBrandSearchClearButton();
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
            
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const itemName = selectedOption.dataset.itemName || '';
                selectedItemName = itemName;
                searchInput.value = selectedOption.textContent;
                populateBrandDropdown(itemName);
                clearBrandModalState(document.getElementById('modalBrand').disabled);
                toggleItemSearchClearButton();
                updateQuantityBreakdownButtonState();
            } else {
                document.getElementById('modalBrand').innerHTML = '<option value="">Select brand...</option>';
                searchInput.value = '';
                selectedItemName = '';
                clearBrandModalState(true);
                toggleItemSearchClearButton();
                updateQuantityBreakdownButtonState();
            }
        });

        document.getElementById('modalBrand').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const brandSearch = document.getElementById('modalBrandSearch');
            
            if (!selectedOption || !selectedOption.value) {
                clearBrandModalState(false);
                return;
            }

            const stock = selectedOption.dataset.stock || 0;
            const price = selectedOption.dataset.price || 0;
            const wholesalePrice = selectedOption.dataset.wholesalePrice || 0;
            const retailPrice = selectedOption.dataset.retailPrice || 0;
            const slabPrice = selectedOption.dataset.slabPrice || 0;
            const buyingPrice = selectedOption.dataset.buyingPrice;
            const unit = selectedOption.dataset.unit || '';
            const menuId = selectedOption.dataset.menuId || '';

            brandSearch.value = selectedOption.textContent;
            toggleBrandSearchClearButton();
            document.getElementById('currentStock').textContent = formatQuantity(stock);
            document.getElementById('currentStockDisplay').classList.remove('hidden');
            
            document.getElementById('modalBrand').dataset.selectedUnit = unit;
            document.getElementById('modalBrand').dataset.originalMenuPrice = price;
            document.getElementById('modalBrand').dataset.menuId = menuId;
            document.getElementById('modalBrand').dataset.wholesalePrice = wholesalePrice;
            document.getElementById('modalBrand').dataset.retailPrice = retailPrice;
            document.getElementById('modalBrand').dataset.slabPrice = slabPrice;
            document.getElementById('modalBrand').dataset.buyingPrice = buyingPrice;
            document.getElementById('modalBrand').dataset.currentStock = stock;
            
            updatePriceTypePrice();
            updateQuantityBreakdownButtonState();
        });

        document.getElementById('modalQuantity').addEventListener('input', updateLineTotal);
        bindAddedPriceInput(document.getElementById('modalPrice'), updateLineTotal);
        document.querySelectorAll('input[name="modal_price_type"]').forEach(input => {
            input.addEventListener('change', function() {
                updatePriceTypePrice();
            });
        });

        function calculateLineTotal(quantity, priceTypePrice, addedPrice) {
            return (quantity * priceTypePrice) + addedPrice;
        }

        function exceedsAvailableStock(quantity, currentStock) {
            return quantity > currentStock + 0.0001;
        }

        function updateLineTotal() {
            const quantity = parseFloat(document.getElementById('modalQuantity').value) || 0;
            const priceTypePrice = parseFloat(document.getElementById('priceTypePrice').value) || 0;
            const addedPrice = getAddedPriceNumber(document.getElementById('modalPrice').value);
            const lineTotal = calculateLineTotal(quantity, priceTypePrice, addedPrice);
            document.getElementById('lineTotalPreview').textContent = formatPeso(lineTotal);
        }

        function isQuantityBreakdownOpen() {
            return document.getElementById('quantityBreakdownPanel').classList.contains('breakdown-open');
        }

        function positionQuantityBreakdownPanel() {
            const panel = document.getElementById('quantityBreakdownPanel');
            const dialog = document.getElementById('addItemDialog');
            const trigger = document.getElementById('openQuantityBreakdownBtn');

            const dialogRect = dialog.getBoundingClientRect();
            const triggerRect = trigger.getBoundingClientRect();
            const panelWidth = panel.offsetWidth || 256;
            const panelHeight = panel.offsetHeight || 288;
            const gap = 12;
            const viewportPadding = 8;
            const verticalOffset = 290;
            const breakdownSide = 'left';

            let left = breakdownSide === 'right'
                ? dialogRect.right + gap
                : dialogRect.left - panelWidth - gap;

            let top = triggerRect.top - verticalOffset;
            const maxTop = window.innerHeight - panelHeight - viewportPadding;
            if (top > maxTop) {
                top = Math.max(viewportPadding, maxTop);
            }

            panel.style.setProperty('--quantity-breakdown-right', `${Math.max(viewportPadding, left)}px`);
            panel.style.setProperty('--quantity-breakdown-top', `${Math.max(viewportPadding, top)}px`);
        }

        function openQuantityBreakdown() {
            if (!hasValidBreakdownTarget()) {
                return;
            }

            const panel = document.getElementById('quantityBreakdownPanel');

            positionQuantityBreakdownPanel();
            panel.classList.add('breakdown-open');
            panel.setAttribute('aria-hidden', 'false');
            updateQuantityBreakdownButtonState();

            const firstInput = panel.querySelector('.breakdown-input');
            if (firstInput) {
                firstInput.focus();
            }
        }

        function closeQuantityBreakdown() {
            const panel = document.getElementById('quantityBreakdownPanel');

            panel.classList.remove('breakdown-open');
            panel.setAttribute('aria-hidden', 'true');
            updateQuantityBreakdownButtonState();
        }

        window.addEventListener('resize', function() {
            if (isQuantityBreakdownOpen()) {
                positionQuantityBreakdownPanel();
            }
        });

        function addQuantityBreakdownRow(value = '', focusNewRow = true) {
            const rowsContainer = document.getElementById('quantityBreakdownRows');
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 breakdown-row';
            row.innerHTML = `
                <input type="number" min="0" step="0.001" class="breakdown-input flex-1 min-w-0 px-3 py-2 border-2 border-gray-300 rounded-none font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.000" value="${value}" data-had-value="${value !== '' ? 'true' : 'false'}">
                <button type="button" class="add-breakdown-row w-8 h-8 border-2 border-blue-600 text-blue-700 rounded-full text-xl leading-none font-bold hover:bg-blue-50" aria-label="Add breakdown line">+</button>
            `;

            rowsContainer.appendChild(row);
            refreshQuantityBreakdownRowControls();

            if (focusNewRow) {
                row.querySelector('.breakdown-input').focus();
            }

            updateQuantityBreakdownTotal();
        }

        function resetQuantityBreakdown() {
            const rowsContainer = document.getElementById('quantityBreakdownRows');
            rowsContainer.innerHTML = `
                <div class="flex items-center gap-2 breakdown-row">
                    <input type="number" min="0" step="0.001" class="breakdown-input flex-1 min-w-0 px-3 py-2 border-2 border-gray-300 rounded-none font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.000" data-had-value="false">
                    <button type="button" class="add-breakdown-row w-8 h-8 border-2 border-blue-600 text-blue-700 rounded-full text-xl leading-none font-bold hover:bg-blue-50" aria-label="Add breakdown line">+</button>
                </div>
            `;
            refreshQuantityBreakdownRowControls();
            updateQuantityBreakdownTotal();
            closeQuantityBreakdown();
        }

        function refreshQuantityBreakdownRowControls() {
            const rows = Array.from(document.querySelectorAll('#quantityBreakdownRows .breakdown-row'));

            rows.forEach((row, index) => {
                const button = row.querySelector('button');
                const isLastRow = index === rows.length - 1;

                button.className = isLastRow
                    ? 'add-breakdown-row w-8 h-8 border-2 border-blue-600 text-blue-700 rounded-full text-xl leading-none font-bold hover:bg-blue-50'
                    : 'remove-breakdown-row w-8 h-8 border-2 border-red-500 text-red-600 rounded-full text-xl leading-none font-bold hover:bg-red-50';
                button.textContent = isLastRow ? '+' : 'x';
                button.setAttribute('aria-label', isLastRow ? 'Add breakdown line' : 'Remove breakdown line');
            });
        }

        function hasBreakdownValue(input) {
            return input.value.trim() !== '' && !Number.isNaN(parseFloat(input.value));
        }

        function handleBreakdownInputChange(input) {
            if (hasBreakdownValue(input)) {
                input.dataset.hadValue = 'true';
            }
        }

        function removeEmptyBreakdownRowAfterBlur(input) {
            if (input.dataset.hadValue !== 'true') {
                return;
            }

            if (input.value.trim() !== '') {
                return;
            }

            input.closest('.breakdown-row').remove();

            if (!document.querySelector('#quantityBreakdownRows .breakdown-row')) {
                addQuantityBreakdownRow('', false);
            }

            refreshQuantityBreakdownRowControls();
        }

        function showInsufficientStockModal() {
            document.getElementById('insufficientStockModal').classList.remove('hidden');
        }

        function updateQuantityBreakdownTotal() {
            const total = Array.from(document.querySelectorAll('#quantityBreakdownRows .breakdown-input')).reduce((sum, input) => {
                return sum + (parseFloat(input.value) || 0);
            }, 0);

            document.getElementById('quantityBreakdownTotal').textContent = formatQuantity(total);
            return total;
        }

        function getQuantityBreakdownValues(containerId = 'quantityBreakdownRows') {
            return Array.from(document.querySelectorAll(`#${containerId} .breakdown-input`))
                .map(input => parseFloat(input.value))
                .filter(value => !Number.isNaN(value) && value > 0);
        }

        function makeBreakdownRow(value = '') {
            return `
                <div class="flex items-center gap-2 breakdown-row">
                    <input type="number" min="0" step="0.001" class="breakdown-input flex-1 min-w-0 px-3 py-2 border-2 border-gray-300 rounded-none font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.000" value="${value}" data-had-value="${value !== '' ? 'true' : 'false'}">
                    <button type="button" class="add-breakdown-row w-8 h-8 border-2 border-blue-600 text-blue-700 rounded-full text-xl leading-none font-bold hover:bg-blue-50" aria-label="Add breakdown line">+</button>
                </div>
            `;
        }

        let activeRowBreakdownIndex = null;

        function refreshRowQuantityBreakdownControls() {
            const rows = Array.from(document.querySelectorAll('#rowQuantityBreakdownRows .breakdown-row'));

            rows.forEach((row, index) => {
                const button = row.querySelector('button');
                const isLastRow = index === rows.length - 1;

                button.className = isLastRow
                    ? 'add-row-breakdown-row w-8 h-8 border-2 border-blue-600 text-blue-700 rounded-full text-xl leading-none font-bold hover:bg-blue-50'
                    : 'remove-row-breakdown-row w-8 h-8 border-2 border-red-500 text-red-600 rounded-full text-xl leading-none font-bold hover:bg-red-50';
                button.textContent = isLastRow ? '+' : 'x';
                button.setAttribute('aria-label', isLastRow ? 'Add breakdown line' : 'Remove breakdown line');
            });
        }

        function updateRowQuantityBreakdownTotal() {
            const total = Array.from(document.querySelectorAll('#rowQuantityBreakdownRows .breakdown-input')).reduce((sum, input) => {
                return sum + (parseFloat(input.value) || 0);
            }, 0);

            document.getElementById('rowQuantityBreakdownTotal').textContent = formatQuantity(total);
            return total;
        }

        function addRowQuantityBreakdownLine(value = '', focusNewRow = true) {
            const rowsContainer = document.getElementById('rowQuantityBreakdownRows');
            rowsContainer.insertAdjacentHTML('beforeend', makeBreakdownRow(value));
            refreshRowQuantityBreakdownControls();
            updateRowQuantityBreakdownTotal();

            if (focusNewRow) {
                rowsContainer.lastElementChild.querySelector('.breakdown-input').focus();
            }
        }

        function removeEmptyRowBreakdownAfterBlur(input) {
            if (input.dataset.hadValue !== 'true' || input.value.trim() !== '') {
                return;
            }

            input.closest('.breakdown-row').remove();

            if (!document.querySelector('#rowQuantityBreakdownRows .breakdown-row')) {
                addRowQuantityBreakdownLine('', false);
            }

            refreshRowQuantityBreakdownControls();
        }

        function openRowQuantityBreakdown(index, anchor) {
            activeRowBreakdownIndex = index;
            const panel = document.getElementById('rowQuantityBreakdownPanel');
            const rowsContainer = document.getElementById('rowQuantityBreakdownRows');
            const values = itemsData[index]?.quantity_breakdown?.length
                ? itemsData[index].quantity_breakdown
                : [itemsData[index]?.quantity || ''];
            const anchorRect = anchor.getBoundingClientRect();

            rowsContainer.innerHTML = '';
            values.forEach(value => addRowQuantityBreakdownLine(value, false));

            const gap = 12;
            const viewportPadding = 8;
            const verticalOffset = 200;
            const panelWidth = 160;
            const panelHeight = 288;
            const maxTop = window.innerHeight - panelHeight - viewportPadding;
            const left = anchorRect.left - panelWidth - gap;
            const top = Math.min(anchorRect.top - verticalOffset, maxTop);

            panel.style.left = `${Math.max(viewportPadding, left)}px`;
            panel.style.top = `${Math.max(viewportPadding, top)}px`;
            panel.classList.remove('hidden');
            refreshRowQuantityBreakdownControls();
            updateRowQuantityBreakdownTotal();

            const firstInput = panel.querySelector('.breakdown-input');
            if (firstInput) {
                firstInput.focus();
            }
        }

        function closeRowQuantityBreakdown() {
            document.getElementById('rowQuantityBreakdownPanel').classList.add('hidden');
            activeRowBreakdownIndex = null;
        }

        document.getElementById('openQuantityBreakdownBtn').addEventListener('click', function() {
            if (isQuantityBreakdownOpen()) {
                closeQuantityBreakdown();
                return;
            }

            openQuantityBreakdown();
        });

        document.getElementById('quantityBreakdownRows').addEventListener('click', function(e) {
            if (e.target.closest('.add-breakdown-row')) {
                addQuantityBreakdownRow();
                return;
            }

            const removeButton = e.target.closest('.remove-breakdown-row');
            if (removeButton) {
                removeButton.closest('.breakdown-row').remove();
                refreshQuantityBreakdownRowControls();
                updateQuantityBreakdownTotal();
            }
        });

        document.getElementById('quantityBreakdownRows').addEventListener('input', function(e) {
            if (e.target.classList.contains('breakdown-input')) {
                handleBreakdownInputChange(e.target);
                updateQuantityBreakdownTotal();
            }
        });

        document.getElementById('quantityBreakdownRows').addEventListener('focusout', function(e) {
            if (e.target.classList.contains('breakdown-input')) {
                removeEmptyBreakdownRowAfterBlur(e.target);
                updateQuantityBreakdownTotal();
            }
        });

        document.getElementById('quantityBreakdownRows').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.classList.contains('breakdown-input')) {
                e.preventDefault();

                if (!e.target.closest('.breakdown-row')?.querySelector('.add-breakdown-row')) {
                    return;
                }

                if (!hasBreakdownValue(e.target)) {
                    return;
                }

                addQuantityBreakdownRow();
            }
        });

        document.getElementById('applyQuantityBreakdownBtn').addEventListener('click', function() {
            const total = updateQuantityBreakdownTotal();
            const currentStock = parseFloat(document.getElementById('modalBrand').dataset.currentStock) || 0;

            if (exceedsAvailableStock(total, currentStock)) {
                showInsufficientStockModal();
                return;
            }

            document.getElementById('modalQuantity').value = formatQuantity(total);
            updateLineTotal();
            closeQuantityBreakdown();
        });

        document.getElementById('rowQuantityBreakdownRows').addEventListener('click', function(e) {
            if (e.target.closest('.add-row-breakdown-row')) {
                const currentInput = e.target.closest('.breakdown-row')?.querySelector('.breakdown-input');
                if (currentInput && !hasBreakdownValue(currentInput)) {
                    return;
                }

                addRowQuantityBreakdownLine();
                return;
            }

            const removeButton = e.target.closest('.remove-row-breakdown-row');
            if (removeButton) {
                removeButton.closest('.breakdown-row').remove();
                refreshRowQuantityBreakdownControls();
                updateRowQuantityBreakdownTotal();
            }
        });

        document.getElementById('rowQuantityBreakdownRows').addEventListener('input', function(e) {
            if (e.target.classList.contains('breakdown-input')) {
                handleBreakdownInputChange(e.target);
                updateRowQuantityBreakdownTotal();
            }
        });

        document.getElementById('rowQuantityBreakdownRows').addEventListener('focusout', function(e) {
            if (e.target.classList.contains('breakdown-input')) {
                removeEmptyRowBreakdownAfterBlur(e.target);
                updateRowQuantityBreakdownTotal();
            }
        });

        document.getElementById('rowQuantityBreakdownRows').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.classList.contains('breakdown-input')) {
                e.preventDefault();

                if (!e.target.closest('.breakdown-row')?.querySelector('.add-row-breakdown-row')) {
                    return;
                }

                if (!hasBreakdownValue(e.target)) {
                    return;
                }

                addRowQuantityBreakdownLine();
            }
        });

        document.getElementById('applyRowQuantityBreakdownBtn').addEventListener('click', function() {
            if (activeRowBreakdownIndex === null || !itemsData[activeRowBreakdownIndex]) {
                closeRowQuantityBreakdown();
                return;
            }

            const total = updateRowQuantityBreakdownTotal();
            const currentStock = parseFloat(itemsData[activeRowBreakdownIndex].current_stock) || 0;

            if (exceedsAvailableStock(total, currentStock)) {
                showInsufficientStockModal();
                return;
            }

            itemsData[activeRowBreakdownIndex].quantity = total;
            itemsData[activeRowBreakdownIndex].quantity_breakdown = getQuantityBreakdownValues('rowQuantityBreakdownRows');
            itemsData[activeRowBreakdownIndex].lineTotal = calculateLineTotal(
                itemsData[activeRowBreakdownIndex].quantity,
                itemsData[activeRowBreakdownIndex].price_type_price || 0,
                itemsData[activeRowBreakdownIndex].unit_price
            );

            const row = document.querySelector(`#itemsTableBody tr:nth-child(${activeRowBreakdownIndex + 1})`);
            if (row) {
                row.querySelector('.qty-input').value = formatQuantity(total);
                row.querySelector('.line-total').textContent = formatPeso(itemsData[activeRowBreakdownIndex].lineTotal);
            }

            updateTotals();
            closeRowQuantityBreakdown();
        });

        document.getElementById('closeInsufficientStockModal').addEventListener('click', function() {
            document.getElementById('insufficientStockModal').classList.add('hidden');
        });

        document.getElementById('addItemBtn').addEventListener('click', function() {
            if (!document.getElementById('branch_id').value) {
                alert('Please select a branch before adding items.');
                document.getElementById('branchSearch').focus();
                return;
            }

            document.getElementById('addItemModal').classList.remove('hidden');
            populateItemDropdown();

            document.getElementById('modalItemSearch').value = '';
            document.getElementById('itemSearchResults').classList.add('hidden');
            
            document.getElementById('modalBrand').innerHTML = '<option value="">Select brand...</option>';
            document.getElementById('modalBrand').disabled = true;
            document.getElementById('modalBrandSearch').value = '';
            document.getElementById('modalBrandSearch').disabled = true;
            document.getElementById('brandSearchResults').classList.add('hidden');
            updateSellingPriceTypeOptionLabels();
            
            document.getElementById('modalQuantity').value = '';
            document.getElementById('modalPrice').value = '';
            document.getElementById('priceTypePrice').value = '';
            document.querySelectorAll('input[name="modal_price_type"]').forEach(input => input.checked = false);
            document.getElementById('currentStockDisplay').classList.add('hidden');
            document.getElementById('lineTotalPreview').textContent = '₱0.00';
            
            selectedItemName = '';
            toggleItemSearchClearButton();
            toggleBrandSearchClearButton();
            resetQuantityBreakdown();
            updateQuantityBreakdownButtonState();
        });

        document.getElementById('closeModalBtn').addEventListener('click', function() {
            closeQuantityBreakdown();
            document.getElementById('addItemModal').classList.add('hidden');
        });

        document.getElementById('addItemModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuantityBreakdown();
                document.getElementById('addItemModal').classList.add('hidden');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (isQuantityBreakdownOpen()) {
                    closeQuantityBreakdown();
                    return;
                }

                document.getElementById('addItemModal').classList.add('hidden');
            }
        });

        document.getElementById('confirmAddItemBtn').addEventListener('click', function() {
            const selectedPriceType = document.querySelector('input[name="modal_price_type"]:checked');
            const priceType = selectedPriceType ? selectedPriceType.value : '';
            const priceTypeLabel = selectedPriceType ? selectedPriceType.dataset.label : '';
            const brandSelect = document.getElementById('modalBrand');
            const selectedBrandOption = brandSelect.options[brandSelect.selectedIndex];
            const brandId = brandSelect.value;
            const menuId = document.getElementById('modalBrand').dataset.menuId || (selectedBrandOption ? selectedBrandOption.dataset.menuId : '');
            const quantityValue = document.getElementById('modalQuantity').value;
            const quantity = parseFloat(quantityValue);
            const price = getAddedPriceNumber(document.getElementById('modalPrice').value);
            const priceTypePrice = parseFloat(document.getElementById('priceTypePrice').value) || 0;
            const buyingPrice = parseFloat(brandSelect.dataset.buyingPrice);
            const currentStock = parseFloat(document.getElementById('modalBrand').dataset.currentStock) || 0;
            const unit = document.getElementById('modalBrand').dataset.selectedUnit || (selectedBrandOption ? selectedBrandOption.dataset.unit : '');
            const originalPrice = document.getElementById('modalBrand').dataset.originalMenuPrice || (selectedBrandOption ? selectedBrandOption.dataset.price : price);
            
            const itemSelect = document.getElementById('modalItem');
            const selectedItemOption = itemSelect.options[itemSelect.selectedIndex];
            const itemName = selectedItemOption ? selectedItemOption.textContent : '';
            const brandName = selectedBrandOption ? selectedBrandOption.textContent : '';

            if (!hasValidDecimalPlaces(quantityValue, 3)) {
                alert('Quantity can have at most 3 decimal places');
                return;
            }

            if (!priceType) {
                alert('Please select a price type');
                return;
            }

            if (!brandId || !menuId || !quantity || quantity <= 0 || Number.isNaN(price) || price < 0) {
                alert('Please fill all fields correctly');
                return;
            }

            if (!Number.isFinite(buyingPrice) || buyingPrice < 0) {
                alert('This item has no purchase-order buying price and cannot be transferred.');
                return;
            }

            if (exceedsAvailableStock(quantity, currentStock)) {
                alert(`Quantity cannot exceed current stock (${formatQuantity(currentStock)} ${unit || 'units'}).`);
                return;
            }

            const lineTotal = calculateLineTotal(quantity, priceTypePrice, price);
            const quantityBreakdown = getQuantityBreakdownValues();

            itemsData.push({
                menu_id: menuId,
                brand_name: brandName,
                price_type: priceType,
                price_type_label: priceTypeLabel,
                price_type_price: priceTypePrice,
                buying_price: buyingPrice,
                item_name: itemName,
                quantity: quantity,
                quantity_breakdown: quantityBreakdown,
                current_stock: currentStock,
                unit: unit,
                unit_price: price,
                original_price: parseFloat(originalPrice) || 0,
                lineTotal: lineTotal
            });

            addRowToTable(brandName, priceTypeLabel, itemName, quantity, unit, priceTypePrice, buyingPrice, price, lineTotal);
            updateTotals();

            closeQuantityBreakdown();
            resetQuantityBreakdown();
            document.getElementById('addItemModal').classList.add('hidden');
        });

        function addRowToTable(brand, priceType, item, quantity, unit, unitPrice, buyingPrice, addedPrice, lineTotal) {
            const tbody = document.getElementById('itemsTableBody');
            const tr = document.createElement('tr');
            const rowIndex = itemsData.length - 1;
            const unitPriceDisplay = Number.isFinite(parseFloat(unitPrice)) ? formatPeso(unitPrice) : formatPeso(0);
            const buyingPriceDisplay = Number.isFinite(parseFloat(buyingPrice)) ? formatPeso(buyingPrice) : formatPeso(0);
            
            tr.innerHTML = `
                <td class="px-4 py-3 text-gray-700">${brand}</td>
                <td class="px-4 py-3 text-gray-700">${priceType}</td>
                <td class="px-4 py-3 text-gray-700">${item}</td>
                <td class="px-4 py-3"><input type="number" step="0.001" value="${formatQuantity(quantity)}" readonly class="w-full px-2 py-1 border rounded qty-input bg-gray-100 cursor-pointer" data-index="${rowIndex}" title="Click to edit quantity breakdown"></td>
                <td class="px-4 py-3 text-gray-700">${unit}</td>
                <td class="px-4 py-3 unit-price text-center text-gray-700 whitespace-nowrap">${unitPriceDisplay}</td>
                <td class="px-4 py-3 buying-price text-center text-gray-700 whitespace-nowrap">${buyingPriceDisplay}</td>
                <td class="px-4 py-3"><input type="text" inputmode="numeric" maxlength="7" pattern="[0-9]*" value="${formatAddedPriceForInput(addedPrice)}" class="w-full px-2 py-1 border rounded price-input" data-index="${rowIndex}"></td>
                <td class="px-4 py-3 line-total text-center font-semibold whitespace-nowrap">${formatPeso(lineTotal)}</td>
                <td class="px-4 py-3 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 remove-btn" data-index="${rowIndex}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </td>
            `;
            
            tbody.appendChild(tr);

            tr.querySelector('.qty-input').addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                openRowQuantityBreakdown(index, this);
            });

            const rowAddedPriceInput = tr.querySelector('.price-input');
            bindAddedPriceInput(rowAddedPriceInput, function(newPrice) {
                const index = parseInt(rowAddedPriceInput.dataset.index);
                if (Number.isNaN(index) || !itemsData[index]) return;

                itemsData[index].unit_price = newPrice;
                itemsData[index].lineTotal = calculateLineTotal(
                    itemsData[index].quantity,
                    itemsData[index].price_type_price || 0,
                    itemsData[index].unit_price
                );
                tr.querySelector('.line-total').textContent = formatPeso(itemsData[index].lineTotal);
                updateTotals();
            });

            tr.querySelector('.remove-btn').addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                itemsData.splice(index, 1);
                tr.remove();
                closeRowQuantityBreakdown();
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

            const branchId = document.getElementById('branch_id').value;
            if (!branchId) {
                alert('Please select a branch');
                document.getElementById('branchSearch').focus();
                return;
            }

            if (itemsData.length === 0) {
                alert('Please add at least one item');
                return;
            }

            if (itemsData.some(item => !Number.isFinite(item.buying_price) || item.buying_price < 0)) {
                alert('Every transfer item must have a purchase-order buying price. Remove items without a buying price and add them again after verifying their purchase order.');
                return;
            }

            const salesInvoice = document.getElementById('sales_invoice').value.trim();
            if (!salesInvoice) {
                alert('Please select a branch to generate the transaction number');
                return;
            }

            itemsData.forEach((item, index) => {
                const menuIdInput = document.createElement('input');
                menuIdInput.type = 'hidden';
                menuIdInput.name = `items[${index}][menu_id]`;
                menuIdInput.value = item.menu_id;
                this.appendChild(menuIdInput);

                const priceTypeInput = document.createElement('input');
                priceTypeInput.type = 'hidden';
                priceTypeInput.name = `items[${index}][price_type]`;
                priceTypeInput.value = item.price_type;
                this.appendChild(priceTypeInput);

                const priceTypePriceInput = document.createElement('input');
                priceTypePriceInput.type = 'hidden';
                priceTypePriceInput.name = `items[${index}][price_type_price]`;
                priceTypePriceInput.value = item.price_type_price || 0;
                this.appendChild(priceTypePriceInput);

                const buyingPriceInput = document.createElement('input');
                buyingPriceInput.type = 'hidden';
                buyingPriceInput.name = `items[${index}][buying_price]`;
                buyingPriceInput.value = item.buying_price;
                this.appendChild(buyingPriceInput);

                const quantityInput = document.createElement('input');
                quantityInput.type = 'hidden';
                quantityInput.name = `items[${index}][quantity]`;
                quantityInput.value = item.quantity;
                this.appendChild(quantityInput);

                const breakdownValues = item.quantity_breakdown && item.quantity_breakdown.length
                    ? item.quantity_breakdown
                    : [item.quantity];

                breakdownValues.forEach((breakdownQuantity) => {
                    const breakdownInput = document.createElement('input');
                    breakdownInput.type = 'hidden';
                    breakdownInput.name = `items[${index}][quantity_breakdown][]`;
                    breakdownInput.value = breakdownQuantity;
                    this.appendChild(breakdownInput);
                });

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
            setupBranchSearch();
            populateItemDropdown();
            setupItemSearch();
            setupBrandSearch();
            setupModalSearchClearButtons();

            const oldBranchId = @json(old('branch_id', ''));
            if (oldBranchId) {
                const select = document.getElementById('branch_id');
                const searchInput = document.getElementById('branchSearch');
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value == oldBranchId) {
                        select.value = oldBranchId;
                        searchInput.value = select.options[i].textContent;
                        break;
                    }
                }
            }
            updateTransactionNumber();
            
            const oldItems = @json(old('items', []));
            if (oldItems && oldItems.length > 0) {
                loadSavedItems(oldItems);
            }
        });

        function formatPriceType(value) {
            const labels = {
                wholesale: 'Wholesale',
                retail: 'Retail',
                slab: 'Slab'
            };

            return labels[value] || '';
        }

        function loadSavedItems(items) {
            if (!items || items.length === 0) return;
            
            items.forEach(item => {
                const brandName = item.brand_name || '';
                const priceType = item.price_type || '';
                const priceTypeLabel = item.price_type_label || formatPriceType(priceType);
                const itemName = item.menu_name || item.item_name || '';
                const quantity = parseFloat(item.quantity) || 0;
                const currentStock = parseFloat(item.current_stock) || 0;
                const unit = item.unit || '';
                const price = getAddedPriceNumber(formatAddedPriceForInput(item.price));
                const priceTypePrice = parseFloat(item.price_type_price) || 0;
                const sourceMenu = allMenus.find(menu => String(menu.id) === String(item.menu_id));
                const buyingPrice = parseFloat(item.buying_price ?? sourceMenu?.buying_price);
                const lineTotal = calculateLineTotal(quantity, priceTypePrice, price);
                
                itemsData.push({
                    menu_id: item.menu_id,
                    brand_name: brandName,
                    price_type: priceType,
                    price_type_label: priceTypeLabel,
                    price_type_price: priceTypePrice,
                    buying_price: buyingPrice,
                    item_name: itemName,
                    quantity: quantity,
                    quantity_breakdown: item.quantity_breakdown && item.quantity_breakdown.length ? item.quantity_breakdown : [quantity],
                    current_stock: currentStock,
                    unit: unit,
                    unit_price: price,
                    original_price: price,
                    lineTotal: lineTotal
                });
                
                addRowToTable(brandName, priceTypeLabel, itemName, quantity, unit, priceTypePrice, buyingPrice, price, lineTotal);
            });
            
            updateTotals();
        }
    </script>
</x-app-layout>