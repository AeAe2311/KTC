<x-app-layout>
    @section('page_title', 'ITEM SETUP')
    {{-- ken added 250623001 - start 02-11-2026 --}}
    <style>
        .branch-row {
            display: none;
        }
        
        .branch-row:first-child,
        .branch-row:nth-child(2),
        .branch-row:nth-child(3),
        .branch-row:nth-child(4),
        .branch-row:nth-child(5),
        .branch-row:nth-child(6),
        .branch-row:nth-child(7),
        .branch-row:nth-child(8),
        .branch-row:nth-child(9),
        .branch-row:nth-child(10) {
            display: table-row;
        }
        
        .branch-row.visible {
            display: table-row;
        }
    </style>
    {{-- ken added 250623001 - end 02-11-2026 --}}
    <div class="">
        <div style="height: calc(100vh - 48px);" class="flex {{ auth()->user()->role == 1 ? 'lg:ml-64 lg:pt-3' : '' }}">
            <div id="contentDiv" class="p-2 w-full h-full">
                <div class="bg-white overflow-x-hidden overflow-y-auto shadow-md rounded-lg py-3 px-5 h-full pb-8 lg:pb-24">
                    <form action="{{ route('menu.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if ($errors->any())
                            <div class="mb-4 text-red-600">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <input type="hidden" id="counter" name="counter" value="{{ old('counter', ($ingredients->count() == 0) ? 1 : $ingredients->count()) }}">
                        <input type="hidden" name="slug" value="{{$slug}}">
                        
                        <!-- Main Card Container -->
                        <div class="space-y-8">
                            
                            <!-- Toggle Switches Card -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Item Settings</h3>
                                <div class="w-full lg:w-2/5 flex justify-between mb-3 pr-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input @if(old('combo', $item->is_combo) == 1) checked @endif type="checkbox" name="combo" id="combo" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-900">Package</span>
                                    </label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input @if(old('isDiscount', $item->is_discount) == 1) checked @endif type="checkbox" name="isDiscount" id="isDiscount" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-900">Discount</span>
                                    </label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input @if(old('hidden', $item->is_hidden) == 1) checked @endif type="checkbox" name="hidden" id="hidden" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-900">Deposit</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Modernized Basic Information Card -->
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                                <!-- Modernized form with minimalist design -->
                                <div class="space-y-8">
                                    
                                    <!-- First row: Menu Name and Category -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                        <!-- Menu Name Field -->
                                        <div class="relative">
                                            <label for="name" class="block text-sm font-medium text-gray-700 tracking-wide uppercase text-xs">
                                                ITEM NAME <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}"
                                                    class="w-full px-0 py-4 text-gray-900 bg-transparent border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-all duration-300 placeholder-gray-400 text-lg font-medium"
                                                    required autocomplete="off"
                                                    placeholder="Enter menu name">
                                                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-gradient-to-r from-blue-500/0 via-blue-500/0 to-blue-500/0 group-focus-within:from-blue-500/100 group-focus-within:via-blue-500/100 group-focus-within:to-blue-500/100 transition-all duration-300"></div>
                                            </div>
                                        </div>

                                        <!-- Category Field -->
                                        <div class="relative">
                                            <label for="category_id" class="block text-sm font-medium text-gray-700 tracking-wide uppercase text-xs">
                                                CATEGORY <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <select id="category_id" name="category_id"
                                                    class="w-full pl-0 pr-10 py-4 text-gray-900 bg-transparent border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-all duration-300 appearance-none text-lg font-medium cursor-pointer">
                                                    <option value="" disabled class="text-gray-400">Select category</option>

                                                    @php
                                                        $selectedCategoryId = old('category_id', $item->category_id);
                                                        $selectedCategory = $categories->firstWhere('id', $selectedCategoryId);
                                                        if (!$selectedCategory && $selectedCategoryId) {
                                                            $selectedCategory = DB::table('menu_categories')->where('id', $selectedCategoryId)->first();
                                                        }
                                                    @endphp

                                                    @if($selectedCategory && !$categories->firstWhere('id', $selectedCategory->id))
                                                        <option value="{{ $selectedCategory->id }}" selected>
                                                            {{ $selectedCategory->name }} (archived)
                                                        </option>
                                                    @endif

                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}" class="text-gray-900 py-2" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <!-- Dropdown arrow icon - moved to the right with proper padding -->
                                                <div class="absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none pr-3">
                                                </div>
                                                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-gradient-to-r from-blue-500/0 via-blue-500/0 to-blue-500/0 group-focus-within:from-blue-500/100 group-focus-within:via-blue-500/100 group-focus-within:to-blue-500/100 transition-all duration-300"></div>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Second row: SKU and Department -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                        <!-- SKU Field -->
                                        <div class="relative">
                                            <label for="item_code" class="block text-sm font-medium text-gray-700 tracking-wide uppercase text-xs">
                                                SKU
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="item_code" name="item_code" value="{{ old('item_code', $item->item_code) }}"
                                                    class="w-full px-0 py-4 text-gray-900 bg-transparent border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-all duration-300 placeholder-gray-400 text-lg"
                                                    placeholder="Enter SKU code">
                                                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-gradient-to-r from-blue-500/0 via-blue-500/0 to-blue-500/0 group-focus-within:from-blue-500/100 group-focus-within:via-blue-500/100 group-focus-within:to-blue-500/100 transition-all duration-300"></div>
                                            </div>
                                            <div class="mt-1 mb-3">
                                                <p class="text-xs text-gray-500 italic">
                                                    Unique identifier for the item
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Department Field -->
                                        <div class="relative">
                                            <label for="department" class="block text-sm font-medium text-gray-700 tracking-wide uppercase text-xs">
                                                DEPARTMENT <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <select id="department" name="department"
                                                    class="w-full pl-0 pr-10 py-4 text-gray-900 bg-transparent border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-all duration-300 appearance-none text-lg font-medium cursor-pointer"
                                                    required>
                                                    <option value="" disabled class="text-gray-400">Select department</option>
                                                    @foreach ($depts as $dept)
                                                        @if ($dept->id < 8 || $dept->id > 10)
                                                            <option value="{{ $dept->id }}"
                                                                {{ old('department', $item->department) == $dept->id ? 'selected' : '' }} class="text-gray-900 py-2">
                                                                {{ $dept->name }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <!-- Dropdown arrow icon - moved to the right with proper padding -->
                                                <div class="absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none pr-3">
                                                </div>
                                                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-gradient-to-r from-blue-500/0 via-blue-500/0 to-blue-500/0 group-focus-within:from-blue-500/100 group-focus-within:via-blue-500/100 group-focus-within:to-blue-500/100 transition-all duration-300"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Third row: Barcode and Product Image -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                        <!-- Barcode Field -->
                                        <div class="relative">
                                            <label for="barcode" class="block text-sm font-medium text-gray-700 tracking-wide uppercase text-xs">
                                                BARCODE
                                            </label>
                                            <div class="relative">
                                                <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $item->barcode) }}"
                                                    class="w-full px-0 py-4 text-gray-900 bg-transparent border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:ring-0 transition-all duration-300 placeholder-gray-400 text-lg"
                                                    autocomplete="off"
                                                    placeholder="Enter barcode">
                                                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-gradient-to-r from-blue-500/0 via-blue-500/0 to-blue-500/0 group-focus-within:from-blue-500/100 group-focus-within:via-blue-500/100 group-focus-within:to-blue-500/100 transition-all duration-300"></div>
                                            </div>
                                        </div>

                                        <!-- Product Image Field - Fixed -->
                                        <div class="relative">
                                            <label for="image" class="block text-sm font-medium text-gray-700 tracking-wide uppercase text-xs">
                                                PRODUCT IMAGE
                                            </label>
                                            <div class="relative">
                                                <!-- Custom file upload button -->
                                                <div class="relative border-0 border-b-2 border-gray-200 focus-within:border-blue-500 transition-all duration-300">
                                                    <!-- Hidden actual file input -->
                                                    <input class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                                        id="image" name="image" type="file" accept="image/*">
                                                    
                                                    <!-- Custom UI -->
                                                    <div class="flex items-center justify-between py-4 min-h-[56px]">
                                                        <!-- Left side: Text display -->
                                                        <div class="flex-1 min-w-0">
                                                            <span class="text-gray-400 text-lg truncate block">
                                                                Choose an image...
                                                            </span>
                                                        </div>
                                                        
                                                        <!-- Right side: Upload button -->
                                                        <div class="flex items-center space-x-2 pl-4">
                                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                            </svg>
                                                            <span class="text-sm text-gray-500 font-medium">Upload</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-gradient-to-r from-blue-500/0 via-blue-500/0 to-blue-500/0 group-focus-within:from-blue-500/100 group-focus-within:via-blue-500/100 group-focus-within:to-blue-500/100 transition-all duration-300"></div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Inventory Card -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-6">Inventory Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                                    <!-- Reorder Point -->
                                    <div class="relative">
                                        <label for="reorder_point" class="block text-sm font-medium text-gray-700 mb-2 tracking-wide uppercase text-xs">
                                            REORDER POINT <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative border-b-2 border-gray-200 focus-within:border-blue-500 transition-all duration-300 pb-1">
                                            <div class="flex items-center min-h-[48px]">
                                                <input type="text" id="reorder_point" name="reorder_point" value="{{ old('reorder_point', $item->reorder_point) }}"
                                                    class="inputNumber w-full bg-transparent border-0 p-0 text-lg font-medium text-gray-900 focus:outline-none focus:ring-0 placeholder:text-gray-400"
                                                    autocomplete="off" required
                                                    placeholder="Enter quantity">
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 italic">
                                                Low stock notification trigger level
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Servings (Hidden but styled) -->
                                    <div class="relative hidden">
                                        <label for="servings" class="block text-sm font-medium text-gray-700 mb-2 tracking-wide uppercase text-xs">
                                            SERVINGS
                                        </label>
                                        <div class="relative border-b-2 border-gray-200 focus-within:border-blue-500 transition-all duration-300 pb-1">
                                            <div class="flex items-center min-h-[48px]">
                                                <input type="text" id="servings" name="servings" value="{{ old('servings', $item->servings ?? '') }}"
                                                    class="inputNumber w-full bg-transparent border-0 p-0 text-lg font-medium text-gray-900 focus:outline-none focus:ring-0 placeholder:text-gray-400"
                                                    autocomplete="off"
                                                    placeholder="Enter servings">
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 italic">
                                                *please base on ingredients
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Card -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-6">Pricing Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <!-- Regular Price -->
                                    <div class="relative">
                                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2 tracking-wide uppercase text-xs">
                                            REGULAR PRICE <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative border-b-2 border-gray-200 focus-within:border-blue-500 transition-all duration-300 pb-1">
                                            <div class="flex items-center min-h-[48px]">
                                                <span class="text-gray-900 text-lg font-medium pr-3">₱</span>
                                                <input type="text" id="price" name="price" value="{{ old('price', $item->price) }}"
                                                    class="inputNumber w-full bg-transparent border-0 p-0 text-lg font-medium text-gray-900 focus:outline-none focus:ring-0 placeholder:text-gray-400"
                                                    required autocomplete="off"
                                                    placeholder="0.00">
                                            </div>
                                            <!-- Helper text below -->
                                            <div class="mt-1 mb-3">
                                                <p class="text-xs text-gray-500 italic">
                                                    To indicate the price upon sale
                                                </p>
                                            </div>                                            
                                        </div>
                                    </div>

                                    <!-- Grab Price -->
                                    <div class="relative">
                                        <label for="grab" class="block text-sm font-medium text-gray-700 mb-2 tracking-wide uppercase text-xs">
                                            GRAB PRICE
                                        </label>
                                        <div class="relative border-b-2 border-gray-200 focus-within:border-blue-500 transition-all duration-300 pb-1">
                                            <div class="flex items-center min-h-[48px]">
                                                <span class="text-gray-900 text-lg font-medium pr-3">₱</span>
                                                <input type="text" id="grab" name="grab" value="{{ old('grab', $item->grab_price) }}"
                                                    class="inputNumber w-full bg-transparent border-0 p-0 text-lg font-medium text-gray-900 focus:outline-none focus:ring-0 placeholder:text-gray-400"
                                                    autocomplete="off"
                                                    placeholder="0.00">
                                            </div>
                                            <!-- Helper text below -->
                                            <div class="mt-1 mb-3">
                                                <p class="text-xs text-gray-500 italic">
                                                    To indicate the price for Grab
                                                </p>
                                            </div>                                              
                                        </div>
                                    </div>

                                    <!-- Food Panda Price -->
                                    <div class="relative">
                                        <label for="panda" class="block text-sm font-medium text-gray-700 mb-2 tracking-wide uppercase text-xs">
                                            FOOD PANDA PRICE
                                        </label>
                                        <div class="relative border-b-2 border-gray-200 focus-within:border-blue-500 transition-all duration-300 pb-1">
                                            <div class="flex items-center min-h-[48px]">
                                                <span class="text-gray-900 text-lg font-medium pr-3">₱</span>
                                                <input type="text" id="panda" name="panda" value="{{ old('panda', $item->panda_price) }}"
                                                    class="inputNumber w-full bg-transparent border-0 p-0 text-lg font-medium text-gray-900 focus:outline-none focus:ring-0 placeholder:text-gray-400"
                                                    autocomplete="off"
                                                    placeholder="0.00">
                                            </div>

                                            <!-- Helper text below -->
                                            <div class="mt-1 mb-3">
                                                <p class="text-xs text-gray-500 italic">
                                                    To indicate the price for  FoodPanda
                                                </p>
                                            </div>                                              
                                        </div>
                                    </div>

                                    <!-- Acquisition Cost -->
                                    <div class="relative">
                                        <label for="foodCost" class="block text-sm font-medium text-gray-700 mb-2 tracking-wide uppercase text-xs">
                                            ACQUISITION COST
                                        </label>
                                        <div class="relative border-b-2 border-gray-200 focus-within:border-blue-500 transition-all duration-300 pb-1">
                                            <div class="flex items-center min-h-[48px]">
                                                <span class="text-gray-900 text-lg font-medium pr-3">₱</span>
                                                <input type="text" id="foodCost" name="foodCost" value="{{ old('foodCost', $item->food_cost) }}"
                                                    class="inputNumber w-full bg-transparent border-0 p-0 text-lg font-medium text-gray-900 focus:outline-none focus:ring-0 placeholder:text-gray-400"
                                                    autocomplete="off"
                                                    placeholder="0.00">
                                            </div>
                                            <!-- Helper text below -->
                                            <div class="mt-1 mb-3">
                                                <p class="text-xs text-gray-500 italic">
                                                    Acquisition Cost for this item
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Brand Selection Card -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">BRAND SELECTION</h3>
                                        <p class="text-sm text-gray-600 mt-1">Select which brands this item will be available in</p>
                                    </div>

                                    @php
                                        $brandCounter = 0;
                                        $visibleRows = 10;
                                        $anyBrandExists = false;
                                        $brandData = [];

                                        foreach($allBrands as $brand) {
                                            if($brand->id != 1) {
                                                $brandCounter++;
                                                $menuItem = isset($menuItemsByBrand[$brand->id]) ? $menuItemsByBrand[$brand->id] : null;
                                                $brandItemExists = $menuItem ? true : false;

                                                if ($brandItemExists) {
                                                    $anyBrandExists = true;
                                                }

                                                $brandData[] = [
                                                    'id' => $brand->id,
                                                    'name' => $brand->name,
                                                    'menuItem' => $menuItem,
                                                    'brandItemExists' => $brandItemExists,
                                                    'stockValue' => $menuItem ? $menuItem->quantity : 0,
                                                    'wholesaleValue' => $menuItem ? $menuItem->wholesale_price : 0,
                                                    'slabValue' => $menuItem ? $menuItem->slab_price : 0,
                                                    'retailValue' => $menuItem ? $menuItem->retail_price : 0,
                                                    'reorderValue' => $menuItem ? $menuItem->reorder_point : 0,
                                                    'barcodeValue' => $menuItem ? $menuItem->barcode : ($item->barcode ?? ''),
                                                    'itemCodeValue' => $menuItem ? $menuItem->item_code : ($item->item_code ?? ''),
                                                ];
                                            }
                                        }

                                        // Sort brands: 1) brands with positive stock (desc), 2) brands with menu items (desc), 3) brands without menu items
                                        usort($brandData, function($a, $b) {
                                            // prioritize brands with positive stock
                                            $aHasStock = (float)$a['stockValue'] > 0;
                                            $bHasStock = (float)$b['stockValue'] > 0;
                                            if ($aHasStock && !$bHasStock) return -1;
                                            if (!$aHasStock && $bHasStock) return 1;

                                            // then prioritize brands that have menu items
                                            if ($a['brandItemExists'] && !$b['brandItemExists']) return -1;
                                            if (!$a['brandItemExists'] && $b['brandItemExists']) return 1;

                                            // then sort by stock descending
                                            $av = (float)$a['stockValue'];
                                            $bv = (float)$b['stockValue'];
                                            if ($bv != $av) return $bv <=> $av;

                                            // finally fallback to alphabetical
                                            return strcmp($a['name'], $b['name']);
                                        });
                                    @endphp

                                    <div class="mt-4 lg:mt-0 flex flex-wrap gap-2">
                                        <button type="button" id="selectAllBranchesBtn" class="px-4 py-2 text-sm bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors duration-200 flex items-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span>Select All</span>
                                        </button>
                                        <button type="button" id="deselectAllBranchesBtn" class="px-4 py-2 text-sm bg-red-50 text-red-700 hover:bg-red-100 rounded-lg border border-red-200 transition-colors duration-200 flex items-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            <span>Deselect All</span>
                                        </button>
                                    </div>

                                    <div class="text-sm text-gray-600 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                                        <span id="selectedCount" class="font-medium text-gray-900">0</span>
                                        <span> of </span>
                                        <span class="font-medium text-gray-900">{{ $brandCounter }}</span>
                                        <span> brands selected</span>
                                    </div>
                                </div>

                                <div class="overflow-hidden border border-gray-200 rounded-lg">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left text-gray-700 min-w-[400px]" id="branchTable">
                                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-4 text-center w-12">
                                                        <input type="checkbox" 
                                                            id="selectAllBranches" 
                                                            class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                                                    </th>
                                                    <th class="px-6 py-4 font-semibold text-gray-700">Brand Name</th>
                                                    <th class="px-6 py-4 font-semibold text-gray-700 text-center">Stock</th>
                                                    <th class="px-6 py-4 font-semibold text-gray-700 text-center">WholeSale Price</th>
                                                    <th class="px-6 py-4 font-semibold text-gray-700 text-center">Slab Price</th>
                                                    <th class="px-6 py-4 font-semibold text-gray-700 text-center">Retail Price</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200" id="branchTableBody">
                                                @foreach($brandData as $index => $brand)
                                                    <tr class="bg-white hover:bg-gray-50 transition-colors duration-150 {{ $index >= $visibleRows ? 'hidden-row' : '' }}">
                                                        @php
                                                            $checked = old("update_branch_{$brand['id']}", $brand['brandItemExists'] ? '1' : '') ? true : false;
                                                            $wholesale = old("wholesaleprices.{$brand['id']}", $brand['wholesaleValue']);
                                                            $slab = old("slabprices.{$brand['id']}", $brand['slabValue']);
                                                            $retail = old("retailprices.{$brand['id']}", $brand['retailValue']);
                                                        @endphp
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="checkbox" 
                                                                name="update_branch_{{ $brand['id'] }}" 
                                                                value="1" 
                                                                class="branch-checkbox w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                                                {{ $checked ? 'checked' : '' }}>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <div class="font-medium text-gray-900">{{ $brand['name'] }}</div>
                                                        </td>
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="text" name="stock_{{ $brand['id'] }}" value="{{ old('stock_'.$brand['id'], $brand['stockValue']) }}" class="branch-input w-full px-2 py-1 border border-gray-300 rounded-md">
                                                        </td>
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="text" name="wholesaleprices[{{ $brand['id'] }}]" value="{{ $wholesale }}" class="branch-input w-full px-2 py-1 border border-gray-300 rounded-md">
                                                        </td>
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="text" name="slabprices[{{ $brand['id'] }}]" value="{{ $slab }}" class="branch-input w-full px-2 py-1 border border-gray-300 rounded-md">
                                                        </td>
                                                        <td class="px-6 py-4 text-center">
                                                            <input type="text" name="retailprices[{{ $brand['id'] }}]" value="{{ $retail }}" class="branch-input w-full px-2 py-1 border border-gray-300 rounded-md">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                @if($brandCounter > $visibleRows)
                                    <div class="mt-4 text-center">
                                        <button type="button" id="showMoreBtn" class="px-5 py-2 text-sm font-medium text-blue-700 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors duration-200 inline-flex items-center space-x-2">
                                            <i class="uil uil-angle-down text-base"></i>
                                            <span>Show More ({{ $brandCounter - $visibleRows }} more)</span>
                                        </button>
                                        <button type="button" id="showLessBtn" class="px-5 py-2 text-sm font-medium text-blue-700 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors duration-200 hidden inline-flex items-center space-x-2">
                                            <i class="uil uil-angle-up text-base"></i>
                                            <span>Show Less</span>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <!-- Inclusions Card -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">Inclusions</h3>
                                        <p class="text-sm text-gray-600 mt-1">Add items to be included in this package</p>
                                    </div>
                                    <button id="addButton" type="button" class="mt-4 lg:mt-0 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 flex items-center space-x-2">
                                        <i class="uil uil-plus text-base"></i>
                                        <span>Add Inclusion</span>
                                    </button>
                                </div>

                                <div id="ingredientsDiv" class="space-y-4">
                                    @php
                                        $counter = $ingredients->count() + 1;
                                        $x = 1;
                                        
                                        if ($ingredients->count() > 0) {
                                            foreach ($ingredients as $ingredient){
                                                if ($ingredient->is_menu == 1) {
                                                    $ingName = $ingredient->menu->name;
                                                }else{
                                                    $ingName = $ingredient->inv->name;
                                                }
                                                echo
                                                '<div id="ing'.$x.'" class="flex flex-col lg:flex-row items-start lg:items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200 hidden">
                                                    <div class="w-full lg:w-5/12">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                                                        <div class="wrapper w-full relative">
                                                            <div class="select-btn flex items-center justify-between rounded-md bg-white border border-gray-300 px-4 py-3 cursor-pointer hover:border-gray-400 transition-colors duration-200">
                                                                <span class="text-gray-900 truncate">'.$ingName.'</span>
                                                                <i class="uil uil-angle-down text-xl transition-transform duration-300 text-gray-500"></i>
                                                            </div>
                                                            <div class="content bg-white mt-1 rounded-lg border border-gray-300 shadow-lg p-4 hidden absolute w-full z-50">
                                                                <div class="search relative mb-3">
                                                                    <i class="uil uil-search absolute left-3 top-3 text-gray-500"></i>
                                                                    <input type="text" class="selectSearch w-full pl-10 pr-4 py-2.5 text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Search items...">
                                                                </div>
                                                                <ul class="listOption options max-h-64 overflow-y-auto">
                                                                    <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="'.$x.'" class="px-3 py-2.5 cursor-pointer hover:bg-gray-100 rounded-md text-gray-700">None</li>
                                                                    ';foreach ($items as $item){
                                                                        echo '<li data-id="'.$item->id.'" data-name="MENU-'.$item->name.'" data-unit="'.$item->unit.'" data-is_menu="'.$item->is_menu.'" data-idnum="'.$x.'" class="px-3 py-2.5 cursor-pointer hover:bg-gray-100 rounded-md text-gray-700">'.$item->name.'</li>';
                                                                    }
                                                                echo '</ul>
                                                            </div>
                                                            <input type="hidden" name="item'.$x.'" value="'.$ingredient->is_menu.','.$ingredient->inventory_id.'">
                                                        </div>
                                                    </div>
                                                    <div class="w-full lg:w-4/12">
                                                        <label for="quantity'.$x.'" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                                        <input type="text" id="quantity'.$x.'" name="quantity'.$x.'" value="'.number_format($ingredient->quantity, 2, '.', '').'" class="quantity w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-center" autocomplete="off">
                                                    </div>
                                                    <div class="w-full lg:w-2/12">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                                                        <div id="unit'.$x.'" class="px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-700">'.$ingredient->unit.'</div>
                                                    </div>
                                                    <div class="w-full lg:w-1/12 pt-6 lg:pt-0">
                                                        <button type="button" data-thisid="ing'.$x++.'" class="removeButton w-full py-3 text-center text-red-600 hover:text-red-700">
                                                            <i class="uil uil-trash-alt text-xl"></i>
                                                        </button>
                                                    </div>
                                                </div>';
                                            }
                                        }else{
                                            echo '
                                                <div id="ing'.$x.'" class="flex flex-col lg:flex-row items-start lg:items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                    <div class="w-full lg:w-5/12">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                                                        <div class="wrapper w-full relative">
                                                            <div class="select-btn flex items-center justify-between rounded-md bg-white border border-gray-300 px-4 py-3 cursor-pointer hover:border-gray-400 transition-colors duration-200">
                                                                <span class="text-gray-500">Select an item...</span>
                                                                <i class="uil uil-angle-down text-xl transition-transform duration-300 text-gray-500"></i>
                                                            </div>
                                                            <div class="content bg-white mt-1 rounded-lg border border-gray-300 shadow-lg p-4 hidden absolute w-full z-50">
                                                                <div class="search relative mb-3">
                                                                    <i class="uil uil-search absolute left-3 top-3 text-gray-500"></i>
                                                                    <input type="text" class="selectSearch w-full pl-10 pr-4 py-2.5 text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Search items...">
                                                                </div>
                                                                <ul class="listOption options max-h-64 overflow-y-auto">
                                                                    <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="'.$x.'" class="px-3 py-2.5 cursor-pointer hover:bg-gray-100 rounded-md text-gray-700">None</li>
                                                                    ';foreach ($items as $item){
                                                                        echo '<li data-id="'.$item->id.'" data-name="'.$item->name.'" data-unit="'.$item->unit.'" data-is_menu="'.$item->is_menu.'" data-idnum="'.$x.'" class="px-3 py-2.5 cursor-pointer hover:bg-gray-100 rounded-md text-gray-700">'.$item->name.'</li>';
                                                                    }
                                                                echo '</ul>
                                                            </div>
                                                            <input type="hidden" name="item'.$x.'" value="">
                                                        </div>
                                                    </div>
                                                    <div class="w-full lg:w-4/12">
                                                        <label for="quantity'.$x.'" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                                        <input type="text" id="quantity'.$x.'" name="quantity'.$x.'" value="" class="quantity w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-center" autocomplete="off">
                                                    </div>
                                                    <div class="w-full lg:w-2/12">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                                                        <div id="unit'.$x.'" class="px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-500">-</div>
                                                    </div>
                                                    <div class="w-full lg:w-1/12 pt-6 lg:pt-0">
                                                        <button type="button" data-thisid="ing'.$x++.'" class="removeButton w-full py-3 text-center text-red-600 hover:text-red-700">
                                                            <i class="uil uil-trash-alt text-xl"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            ';
                                        }
                                    @endphp
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="flex flex-col sm:flex-row justify-between gap-4">
                                    <a href="{{ route('menu.index') }}" class="px-6 py-3 text-sm font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg border border-gray-300 transition-colors duration-200 flex items-center justify-center space-x-2">
                                        <i class="uil uil-arrow-left text-base"></i>
                                        <span>Back to Menu List</span>
                                    </a>
                                    <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg border border-blue-700 transition-colors duration-200 flex items-center justify-center space-x-2">
                                        <i class="uil uil-check-circle text-base"></i>
                                        <span>Update Menu Item</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
     </div>
     {{-- ken added 250623001 - end 02-11-2026 --}}
    
    
    {{-- ken added 250623001 - start 02-11-2026 --}}
    <script>
        $(document).ready(function() {
            // Track visible rows
            let allVisible = false;
            const totalRows = {{ $brandCounter }};
            const initialVisible = {{ min($visibleRows, $brandCounter) }};
            
            // Store original values when page loads to restore them later
            $('.branch-input').each(function() {
                $(this).data('original-value', $(this).val());
            });
            
            // Show More/Less functionality
            $('#showMoreBtn').click(function() {
                $('.branch-row').addClass('visible');
                $(this).addClass('hidden');
                $('#showLessBtn').removeClass('hidden');
                allVisible = true;
            });
            
            $('#showLessBtn').click(function() {
                $('.branch-row').each(function(index) {
                    if (index >= initialVisible) {
                        $(this).removeClass('visible');
                    }
                });
                $(this).addClass('hidden');
                $('#showMoreBtn').removeClass('hidden');
                allVisible = false;
            });
            
// Branch selection helpers
            function initBranchTable() {
                const branchCheckbox = $('.branch-checkbox');
                const selectAllCheckbox = $('#selectAllBranches');
                const selectedCount = $('#selectedCount');

                function updateSelectedCount() {
                    const selected = $('.branch-checkbox:checked').length;
                    selectedCount.text(selected);
                }

                function toggleBranches(state) {
                    branchCheckbox.prop('checked', state).trigger('change');
                }

                function populateBranchPrices(branchId) {
                    const regularPrice = $('#price').val().trim();
                    if (!regularPrice || isNaN(regularPrice)) return;

                    const row = $(`.branch-checkbox[value='${branchId}']`).closest('tr');
                    row.find('input[name^="wholesaleprices"]').each(function() {
                        if (!$(this).val().trim()) {
                            $(this).val(regularPrice);
                        }
                    });
                    row.find('input[name^="slabprices"]').each(function() {
                        if (!$(this).val().trim()) {
                            $(this).val(regularPrice);
                        }
                    });
                    row.find('input[name^="retailprices"]').each(function() {
                        if (!$(this).val().trim()) {
                            $(this).val(regularPrice);
                        }
                    });
                }

                // Unbind before binding (helps re-init after HTML replacement)
                branchCheckbox.off('change');
                selectAllCheckbox.off('change');
                $('#selectAllBranchesBtn').off('click');
                $('#deselectAllBranchesBtn').off('click');

                // Handle individual branch checkbox changes
                branchCheckbox.change(function() {
                    const isChecked = $(this).is(':checked');
                    const row = $(this).closest('tr');

                    // Enable/disable inputs based on checkbox. Keep stock input always enabled so it can be edited even when unchecked.
                    row.find('.branch-input').each(function() {
                        const name = $(this).attr('name') || '';
                        const isStockField = name.startsWith('stock_');

                        if (isStockField) {
                            // Keep stock field enabled so users can type the value and trigger save.
                            $(this).prop('disabled', false);
                            $(this).prop('required', false);
                            const originalValue = $(this).data('original-value');
                            if (originalValue !== undefined && $(this).val() === '') {
                                $(this).val(originalValue);
                            }
                            return;
                        }

                        if (isChecked) {
                            $(this).prop('disabled', false);
                            $(this).prop('required', true);
                            const originalValue = $(this).data('original-value');
                            if (originalValue !== undefined && $(this).val() === '') {
                                $(this).val(originalValue);
                            }
                        } else {
                            $(this).prop('disabled', true);
                            $(this).prop('required', false);
                        }
                    });

                    if (isChecked) {
                        populateBranchPrices($(this).val());
                    }

                    updateSelectedCount();
                });

                // Auto-check branch if stock is edited
                $(document).on('input', 'input[name^="stock_"]', function() {
                    const row = $(this).closest('tr');
                    const checkbox = row.find('.branch-checkbox');
                    if ($(this).val().trim() !== '') {
                        checkbox.prop('checked', true).trigger('change');
                    }
                });

                // Select all checkbox control
                selectAllCheckbox.change(function() {
                    const isChecked = $(this).is(':checked');
                    toggleBranches(isChecked);
                });

                // Quick action buttons
                $('#selectAllBranchesBtn').click(function() {
                    selectAllCheckbox.prop('checked', true);
                    toggleBranches(true);
                });

                $('#deselectAllBranchesBtn').click(function() {
                    selectAllCheckbox.prop('checked', false);
                    toggleBranches(false);
                });

                // Ensure selected count is correct on load
                updateSelectedCount();

                // Initialize selection state on page load
                $('#selectAllBranches').prop('checked', false);
                branchCheckbox.trigger('change');
            }

            // Initialize branch table behavior now
            initBranchTable();

            // When main price changes, update checked branches
            $('#price').on('input blur', function() {
                const regularPrice = $(this).val().trim();
                if (!regularPrice || isNaN(regularPrice)) return;

                $('.branch-checkbox:checked').each(function() {
                    populateBranchPrices($(this).val());
                });
            });

            // When main regular price changes, propagate to checked branch price inputs
            $('#price').on('input blur', function() {
                var regularPrice = $(this).val().trim();
                if (regularPrice && !isNaN(regularPrice)) {
                    $('.branch-checkbox:checked').each(function() {
                        var id = $(this).closest('tr').data('branch-id');
                        var priceInputs = $(`input[name="wholesaleprices[${id}]"], input[name="slabprices[${id}]"], input[name="retailprices[${id}]" ]`);
                        priceInputs.each(function() {
                            if (!$(this).val().trim()) {
                                $(this).val(regularPrice);
                            }
                        });
                    });
                }
            });

            // Category change: refresh brand list
            $('#category_id').change(function() {
                const categoryId = $(this).val();
                if (!categoryId) {
                    return;
                }

                $.ajax({
                    url: "{{ route('menu.getBrands') }}",
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        categoryId: categoryId,
                    },
                    success: function(brands) {
                        let html = '';
                        brands.forEach(function(branch) {
                            html += `
                                <tr class="bg-white hover:bg-gray-50 transition-colors duration-150" data-branch-id="${branch.id}">
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox"
                                            name="update_branch_${branch.id}"
                                            value="1"
                                            class="branch-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded"
                                            checked>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">${branch.name}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="text"
                                            name="stock_${branch.id}"
                                            class="branch-input w-full px-2 py-1 border border-gray-300 rounded-md">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="text"
                                            name="wholesaleprices[${branch.id}]"
                                            class="branch-input w-full px-2 py-1 border border-gray-300 rounded-md">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="text"
                                            name="slabprices[${branch.id}]"
                                            class="branch-input w-full px-2 py-1 border border-gray-300 rounded-md">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <input type="text"
                                            name="retailprices[${branch.id}]"
                                            class="branch-input w-full px-2 py-1 border border-gray-300 rounded-md">
                                    </td>
                                </tr>
                            `;
                        });

                        $('#branchTableBody').html(html);
                        initBranchTable();
                    }
                });
            });

            // Form validation before submission
            $('form').on('submit', function(e) {
                let atLeastOneChecked = false;

                // Check if at least one branch is selected for update
                $('.branch-checkbox').each(function() {
                    if ($(this).is(':checked')) {
                        atLeastOneChecked = true;
                        return false; // Break out of loop
                    }
                });

                if (!atLeastOneChecked) {
                    e.preventDefault();
                    alert('Please select at least one branch to update.');
                    return false;
                }

                // Validate required fields only for checked branches
                let isValid = true;
                $('.branch-checkbox:checked').each(function() {
                    const row = $(this).closest('tr');
                    row.find('.branch-input[required]').each(function() {
                        if (!$(this).val().trim()) {
                            isValid = false;
                            $(this).addClass('border-red-500');
                        } else {
                            $(this).removeClass('border-red-500');
                        }
                    });
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields for selected branches.');
                    return false;
                }
            });
        });
    </script>
    {{-- ken added 250623001 - end 02-11-2026 --}}

    <script>
        $(document).ready(function() {

            var counter = "<?php echo $counter; ?>";
            counter++;

            jQuery(document).on( "click", ".select-btn", function(e){
                $('.content').not($(this).closest('.wrapper').find('.content')).addClass('hidden');
                $(this).closest('.wrapper').find('.content').toggleClass('hidden');
                $(this).closest('.wrapper').find('.uil-angle-down').toggleClass('-rotate-180');
                e.stopPropagation();
            });

            function searchFilter(searchInput){
                $(".listOption li").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchInput) > -1)
                });
            }

            jQuery(document).on( "click", ".content", function(e){
                e.stopPropagation();
            });
            
            jQuery(document).on( "input", ".selectSearch", function(){
                var value = $(this).val().toLowerCase();
                searchFilter(value);
            });

            jQuery(document).on( "click", ".listOption li", function(){
                var id = $(this).data('id');
                var name = $(this).data('name');
                var unit = $(this).data('unit');
                var is_menu = $(this).data('is_menu');
                var unitid = 'unit' + $(this).data('idnum');
                var qtyid = 'quantity' + $(this).data('idnum');

                if(id != ""){
                    $(this).closest('.wrapper').find('input').val(is_menu+','+id);
                    $(this).closest('.wrapper').find('.select-btn span').html(name);
                }else{
                    $(this).closest('.wrapper').find('input').val('');
                    $(this).closest('.wrapper').find('.select-btn span').html('');
                }

                $('.content').addClass('hidden');
                $('.selectSearch').val('');
                var value = $(".selectSearch").val().toLowerCase();
                searchFilter(value);

                $('#' + unitid).html(unit);
                $('#' + qtyid).val('');
                $('.uil-angle-down').removeClass('-rotate-180');

            });

            $(document).click(function() {
                $('.content').addClass('hidden');
                $('.uil-angle-down').removeClass('-rotate-180');
            });

            jQuery(document).on( "keyup", ".quantity", function(){
                var val = $(this).val().replace(/[^0-9.]/g, '');
                $(this).val(val);
            });

            $('#addButton').click(function(){
                if($('#combo').is(":checked")){
                    var combo = true;
                }else{
                    var combo = false;
                }
                _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('menu.addIng') }}",
                    method:"POST",
                    data:{
                        combo: combo,
                        counter : counter,
                        _token: _token
                    },
                    success:function(result){
                        console.log(result);
                        $('#ingredientsDiv').append(result);
                        $('#counter').val(counter);
                    }
                })
                counter++;
            });

            jQuery(document).on( "click", ".removeButton", function(){
                var thisid = $(this).data('thisid');
                $('#'+thisid).remove();
            });

            jQuery(document).on( "click", "#combo", function(){
                if($(this).is(":checked")){
                    var combo = true;
                }else{
                    var combo = false;
                }
                _token = $('input[name="_token"]').val();

                $.ajax({
                    url:"{{ route('menu.changeIng') }}",
                    method:"POST",
                    data:{
                        combo: combo,
                        _token: _token
                    },
                    success:function(result){
                        $('#ingredientsDiv').html(result);
                    }
                })
            });
        });
        // EASY FIX: Different formatting for different field types
        $(document).ready(function() {
            // Function to remove decimal points (for stock and reorder)
            function formatWholeNumber(value) {
                if (value && value !== '') {
                    // Remove any decimal part
                    return Math.round(parseFloat(value));
                }
                return value;
            }
            
            // Function to format with two decimals (for price and quantity)
            function formatTwoDecimals(value) {
                if (value && value !== '') {
                    let num = parseFloat(value);
                    if (!isNaN(num)) {
                        return num.toFixed(2);
                    }
                }
                return value;
            }
            
            // Format existing values on page load
            $('.branch-input').each(function() {
                let $this = $(this);
                let name = $this.attr('name') || '';
                let value = $this.val();
                
                if (value && value !== '') {
                    if (name.includes('stock_') || name.includes('reorder_')) {
                        // Stock and reorder fields - whole numbers only
                        $this.val(formatWholeNumber(value));
                    } else if (name.includes('price_') || name.includes('wholesaleprices') || name.includes('slabprices') || name.includes('retailprices')) {
                        // Price fields - two decimals
                        $this.val(formatTwoDecimals(value));
                    }
                }
            });
            
            // Format quantity fields in inclusions section
            $('.quantity').each(function() {
                let value = $(this).val();
                if (value && value !== '') {
                    $(this).val(formatTwoDecimals(value));
                }
            });
            
            // Format the main item cost and price inputs
            $('#foodCost, #price').each(function() {
                let value = $(this).val();
                if (value && value !== '') {
                    $(this).val(formatTwoDecimals(value));
                }
            });
            
            // Auto-format on blur (when user leaves the field)
            $(document).on('blur', '.branch-input', function() {
                let $this = $(this);
                let name = $this.attr('name') || '';
                let value = $this.val();
                
                if (value && value !== '') {
                    if (name.includes('stock_') || name.includes('reorder_')) {
                        // Remove decimals for stock and reorder
                        $this.val(formatWholeNumber(value));
                    } else if (name.includes('price_') || name.includes('wholesaleprices') || name.includes('slabprices') || name.includes('retailprices')) {
                        // Two decimals for price-related fields
                        $this.val(formatTwoDecimals(value));
                    }
                }
            });
            
            // Format quantity fields on blur
            $(document).on('blur', '.quantity', function() {
                let value = $(this).val();
                if (value && value !== '') {
                    $(this).val(formatTwoDecimals(value));
                }
            });
            
            // Prevent decimal input for stock and reorder fields
            $(document).on('input', 'input[name*="stock_"], input[name*="reorder_"]', function(e) {
                let value = $(this).val();
                // Remove any decimal points and non-numeric characters
                value = value.replace(/[^0-9]/g, '');
                $(this).val(value);
            });
        });     
    </script>
</x-app-layout>
