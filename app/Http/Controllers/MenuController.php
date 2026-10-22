<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Menu;
use App\Models\Waste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Branch; //added by ken - 250623001 - 1/22/2026
use League\Csv\Reader; //ADDED BY KEN
use League\Csv\Writer; //ADDED BY KEN
use Symfony\Component\HttpFoundation\StreamedResponse; //ADDED BY KEN
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class MenuController extends Controller {

    /**
     * Check for required data (branches and categories) and prepare toast notifications
     */
    private function checkRequiredData()
    {
        $user = Auth::user();
        $storeCode = $user->store_code;

        $branchesCount = DB::table('settings')
            ->where('store_code', $storeCode)
            ->where('is_deleted', 0)
            ->count();

        $categoriesCount = DB::table('menu_categories')
            ->where('store_code', $storeCode)
            ->where('is_deleted', 0)
            ->count();

        $messages = [];

        if ($branchesCount == 0) {
            $messages[] = [
                'type' => 'warning',
                'message' => 'No branches found! Please add at least one branch before managing menu items.',
                'options' => ['duration' => 8000]
            ];
        }

        if ($categoriesCount == 0) {
            $messages[] = [
                'type' => 'warning',
                'message' => 'No categories found! Please add at least one menu category before managing menu items.',
                'options' => ['duration' => 8000]
            ];
        }

        if ($branchesCount == 0 && $categoriesCount == 0) {
            $messages[] = [
                'type' => 'info',
                'message' => 'Setup Required: Go to Settings → Branches and Settings → Categories to add the necessary data for menu management.',
                'options' => ['duration' => 10000]
            ];
        }

        return $messages;
    }

    // ken start new update index,  paginate, search, ,searchcategory, add,  - 250623001 - 8/6/2026
    public function index() {
        $user = Auth::user();
        $branchFilter = request('branchFilter');
        $brandFilter = request('brandFilter');
        $search = request('search');
        $categoryFilter = request('searchSelect', 'All');
        $stockFilter = request('stockFilter', 'all');
        $perPage = 100; // Items per page
        
        // Combo menu calculations - using your original approach
        $comboMenus = Menu::where('is_combo', 1)
            ->where('store_code', $user->store_code)
            ->where('is_deleted', 0)
            ->get();
        
        $catVal = '';
        
        foreach ($comboMenus as $comboMenu) {
            $ingredients = Ingredient::where('menu_id', $comboMenu->id)
                ->where('is_deleted', 0)
                ->get();

            if ($ingredients->count() == 0) {
                $minQuantity = 0;
            } else {
                $minQuantity = PHP_INT_MAX;
            }

            foreach ($ingredients as $ingredient) {
                $inventoryQuantity = $ingredient->menu->quantity;
                $requiredQuantity = $ingredient->quantity;
                $availableCombos = floor($inventoryQuantity / $requiredQuantity);
                $minQuantity = min($minQuantity, $availableCombos);
            }

            $comboMenu->update([
                'current_quantity' => $minQuantity,
            ]);
        }
        
        // Get categories filtered by user's store/branch
        $categories = DB::table('menu_categories')
            ->where('is_hidden', 0)
            ->where('is_deleted', 0)
            /* ->where(function($query) use ($user) {
                if ($user->store_code) {
                    $query->where('store_code', $user->store_code);
                }
                if ($user->branch) {
                    $query->orWhere('branch', $user->branch);
                }
                $query->orWhereNull('store_code');
                $query->orWhereNull('branch');
            }) */
            ->orderBy('name', 'asc')
            ->get();
        
        // Build menu query with proper pagination
        $menuQuery = Menu::with(['category', 'branch', 'brand'])
            ->where('is_deleted', 0)
            ->where('store_code', $user->store_code);
        
        // Apply search filter
        if (!empty($search)) {
            $menuQuery = $menuQuery->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('item_code', 'like', '%' . $search . '%')
                    ->orWhere('brand_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }
        
        // Apply category filter
        if (!empty($categoryFilter) && $categoryFilter != 'All') {
            $menuQuery = $menuQuery->where('category_id', $categoryFilter);
            $catVal = $categoryFilter;
        }
        
        // Apply role-based filtering
        if ($user->role != 4 && $user->role != 2) {
            $menuQuery = $menuQuery->orderBy('name', 'asc');
            $items = DB::table('inventories')
                ->where('is_deleted', 0)
                ->get();
        } else {
            $menuQuery = $menuQuery->whereIn('department', [1, 2, 3, 4, 5])
                ->orderBy('name', 'asc');
            $items = DB::table('inventories')
                ->whereIn('department', [1, 2, 3, 4, 5])
                ->where('is_deleted', 0)
                ->get();
        }
        
        // Apply branch filter
        if (!empty($branchFilter)) {
            $menuQuery = $menuQuery->where('branch_id', $branchFilter);
        }
        
        // Apply brand filter
        if (!empty($brandFilter)) {
            $menuQuery = $menuQuery->where('brand_id', $brandFilter);
        }
        
        // ✅ REAL PAGINATION - Only loads current page
        $paginatedMenus = $menuQuery->paginate($perPage);
        
        // Group only the current page's items
        $groupedMenus = [];
        foreach ($paginatedMenus as $menu) {
            $groupedMenus[$menu->name][] = $menu;
        }
        
        $menuCount = $paginatedMenus->total();
        $page = $paginatedMenus->currentPage();
        $category = $categoryFilter;
        
        // Get additional data
        $depts = DB::table('departments')
            ->orderBy('id', 'asc')
            ->where('is_deleted', 0)
            ->get();
        
        $branches = DB::table('settings')
            ->where('store_code', $user->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();
        
        $brands = DB::table('brands')
            //->where('store_code', $user->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();
        
        // Check for required data and prepare toast notifications
        $toastMessages = $this->checkRequiredData();
        // Check if any brand codes exist for the current store
        $hasBrandCodes = \App\Models\Menu::where('store_code', auth()->user()->store_code)
                                        ->whereNotNull('brand_code')
                                        ->where('brand_code', '!=', '')
                                        ->exists();        
        return view('user.cook.menu-preparation', compact(
            'groupedMenus',
            'hasBrandCodes',
            'menuCount',
            'page',
            'search',
            'items',
            'categories',
            'category',
            'depts',
            'catVal',
            'branches',
            'brands',
            'branchFilter',
            'brandFilter',
            'stockFilter',
            'toastMessages',
            'paginatedMenus' // For pagination links in view
        ));
    }

    public function paginate($page) {
        $user = Auth::user();
        $branchFilter = request('branchFilter');
        $search = request('search');
        $categoryFilter = request('searchSelect', 'All');
        $perPage = 100; // Items per page
        
        // Combo menu calculations - using your original approach
        $comboMenus = Menu::where('is_combo', 1)
            ->where('store_code', $user->store_code)
            ->where('is_deleted', 0)
            ->get();
        
        $catVal = '';
        
        foreach ($comboMenus as $comboMenu) {
            $ingredients = Ingredient::where('menu_id', $comboMenu->id)
                ->where('is_deleted', 0)
                ->get();

            if ($ingredients->count() == 0) {
                $minQuantity = 0;
            } else {
                $minQuantity = PHP_INT_MAX;
            }

            foreach ($ingredients as $ingredient) {
                $inventoryQuantity = $ingredient->menu->quantity;
                $requiredQuantity = $ingredient->quantity;
                $availableCombos = floor($inventoryQuantity / $requiredQuantity);
                $minQuantity = min($minQuantity, $availableCombos);
            }

            $comboMenu->update([
                'current_quantity' => $minQuantity,
            ]);
        }
        
        // Get categories filtered by user's store/branch
        $categories = DB::table('menu_categories')
            ->where('is_hidden', 0)
            ->where('is_deleted', 0)
            /* ->where(function($query) use ($user) {
                if ($user->store_code) {
                    $query->where('store_code', $user->store_code);
                }
                if ($user->branch) {
                    $query->orWhere('branch', $user->branch);
                }
                $query->orWhereNull('store_code');
                $query->orWhereNull('branch');
            }) */
            ->orderBy('name', 'asc')
            ->get();
        
        // Build menu query with proper pagination
        $menuQuery = Menu::with(['category', 'branch', 'brand'])
            ->where('is_deleted', 0)
            ->where('store_code', $user->store_code);
        
        // Apply search filter
        if (!empty($search)) {
            $menuQuery = $menuQuery->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('item_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }
        
        // Apply category filter
        if (!empty($categoryFilter) && $categoryFilter != 'All') {
            $menuQuery = $menuQuery->where('category_id', $categoryFilter);
            $catVal = $categoryFilter;
        }
        
        // Apply role-based filtering
        if ($user->role != 4 && $user->role != 2) {
            $menuQuery = $menuQuery->orderBy('name', 'asc');
            $items = DB::table('inventories')
                ->where('is_deleted', 0)
                ->get();
        } else {
            $menuQuery = $menuQuery->whereIn('department', [1, 2, 3, 4, 5])
                ->orderBy('name', 'asc');
            $items = DB::table('inventories')
                ->whereIn('department', [1, 2, 3, 4, 5])
                ->where('is_deleted', 0)
                ->get();
        }
        
        // Apply branch filter
        if (!empty($branchFilter)) {
            $menuQuery = $menuQuery->where('branch_id', $branchFilter);
        }
        
        // ✅ REAL PAGINATION - Uses the $page parameter
        $paginatedMenus = $menuQuery->paginate($perPage, ['*'], 'page', $page);
        
        // Group only the current page's items
        $groupedMenus = [];
        foreach ($paginatedMenus as $menu) {
            $groupedMenus[$menu->name][] = $menu;
        }
        
        $menuCount = $paginatedMenus->total();
        $category = $categoryFilter;
        
        // Get additional data
        $depts = DB::table('departments')
            ->orderBy('id', 'asc')
            ->where('is_deleted', 0)
            ->get();
        
        $branches = DB::table('settings')
            ->where('store_code', $user->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();
        
        $brands = DB::table('brands')
            //->where('store_code', $user->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();
        $hasBrandCodes = \App\Models\Menu::where('store_code', auth()->user()->store_code)
                                        ->whereNotNull('brand_code')
                                        ->where('brand_code', '!=', '')
                                        ->exists();         
        return view('user.cook.menu-preparation', compact(
            'groupedMenus',
            'menuCount',
            'hasBrandCodes',
            'page',
            'search',
            'items',
            'categories',
            'category',
            'depts',
            'catVal',
            'branches',
            'brands',
            'branchFilter',
            'paginatedMenus' // For pagination links in view
        ));
    }

    public function search($page, $search) {
        $user = Auth::user();
        $branchFilter = request('branchFilter');
        $categoryFilter = request('searchSelect', 'All');
        
        $comboMenus = Menu::where('is_combo', 1)->where('is_deleted', 0)->get();
        $catVal = '';
        
        foreach ($comboMenus as $comboMenu) {
            $ingredients = Ingredient::where('menu_id', $comboMenu->id)->where('is_deleted', 0)->get();

            if ($ingredients->count() == 0) {
                $minQuantity = 0;
            } else {
                $minQuantity = PHP_INT_MAX;
            }

            foreach ($ingredients as $ingredient) {
                $inventoryQuantity = $ingredient->menu->quantity;
                $requiredQuantity = $ingredient->quantity;
                $availableCombos = floor($inventoryQuantity / $requiredQuantity);
                $minQuantity = min($minQuantity, $availableCombos);
            }

            $comboMenu->update([
                'quantity' => $minQuantity,
                'current_quantity' => $minQuantity,
            ]);
        }
        
        // Get categories filtered by user's store/branch
        $categories = DB::table('menu_categories')
            ->where('is_hidden', 0)
            ->where('is_deleted', 0)
            /* ->where(function($query) use ($user) {
                // Show categories for user's store
                if ($user->store_code) {
                    $query->where('store_code', $user->store_code);
                }
                // Also show categories for user's branch
                if ($user->branch) {
                    $query->orWhere('branch', $user->branch);
                }
                // Show global categories (no store/branch specified)
                $query->orWhereNull('store_code');
                $query->orWhereNull('branch');
            }) */
            ->orderBy('name', 'asc')
            ->get();
        
        // Get menus with user-specific filtering and search
        // 250623001 ken - 1/22/2026 - start here
        $menuQuery = Menu::with(['category', 'branch'])->where('is_deleted', 0);
        
        // Always filter by user's store code first
        if ($user->store_code) {
            $menuQuery = $menuQuery->where('store_code', $user->store_code);
        }
        
        // Apply search filter - UPDATED: Search by name, SKU, and barcode
        if (!empty($search)) {
            $menuQuery = $menuQuery->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('item_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }
        
        // Apply category filter
        if (!empty($categoryFilter) && $categoryFilter != 'All') {
            $menuQuery = $menuQuery->where('category_id', $categoryFilter);
            $catVal = $categoryFilter;
        }
        
        if ($user->role != 4 && $user->role != 2) {
            // Admin/superuser sees all items in their store
            $menuQuery = $menuQuery->orderBy('name', 'asc');
            $items = DB::table('inventories')
            ->where('is_deleted', 0)->get();
        } else {
            // Regular users see only their department/branch
            $menuQuery = $menuQuery->whereIn('department', [1, 2, 3, 4, 5])
                                ->orderBy('name', 'asc');
            $items = DB::table('inventories')
                ->whereIn('department', [1, 2, 3, 4, 5])
                ->where('is_deleted', 0)
                ->get();
        }
        
        // Apply branch filter from dropdown (after store_code filter)
        if (!empty($branchFilter)) {
            $menuQuery = $menuQuery->where('branch_id', $branchFilter);
        }
        // 250623001 ken - 1/22/2026 - end here
        
        // Get all menus (no pagination for grouping)
        $allMenus = $menuQuery->get();
        
        // Group menus by name for the dynamic table
        $groupedMenus = [];
        foreach ($allMenus as $menu) {
            $groupedMenus[$menu->name][] = $menu;
        }
        
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        
        // Get branches for the import modal and filter dropdown
        $branches = DB::table('settings')
            ->where('store_code', $user->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();

        $menuCount = count($groupedMenus);
        $category = $categoryFilter;
        $hasBrandCodes = \App\Models\Menu::where('store_code', auth()->user()->store_code)
                                        ->whereNotNull('brand_code')
                                        ->where('brand_code', '!=', '')
                                        ->exists();         
        // FIX: Add 'groupedMenus', 'search', and 'branchFilter' to compact()
        return view('user.cook.menu-preparation', compact(
            'groupedMenus',  // Add this line
            'menuCount', 
            'page', 
            'search', 
            'hasBrandCodes',
            'items',
            'categories',
            'category',
            'depts',
            'catVal',
            'branches',
            'branchFilter'  // Add this line
        ));
    }

    public function searchcategory($page, $category) {
        $user = Auth::user();
        $branchFilter = request('branchFilter');
        $search = request('search');
        
        $comboMenus = Menu::where('is_combo', 1)->where('is_deleted', 0)->get();

        foreach ($comboMenus as $comboMenu) {
            $ingredients = Ingredient::where('menu_id', $comboMenu->id)->where('is_deleted', 0)->get();

            if ($ingredients->count() == 0) {
                $minQuantity = 0;
            } else {
                $minQuantity = PHP_INT_MAX;
            }

            foreach ($ingredients as $ingredient) {
                $inventoryQuantity = $ingredient->menu->quantity;
                $requiredQuantity = $ingredient->quantity;
                $availableCombos = floor($inventoryQuantity / $requiredQuantity);
                $minQuantity = min($minQuantity, $availableCombos);
            }

            $comboMenu->update([
                'quantity' => $minQuantity,
                'current_quantity' => $minQuantity,
            ]);
        }
        
        $catVal = '';
        
        // Get categories filtered by user's store/branch
        $categories = DB::table('menu_categories')
            ->where('is_hidden', 0)
            ->where('is_deleted', 0)
            /* ->where(function($query) use ($user) {
                // Show categories for user's store
                if ($user->store_code) {
                    $query->where('store_code', $user->store_code);
                }
                // Also show categories for user's branch
                if ($user->branch) {
                    $query->orWhere('branch', $user->branch);
                }
                // Show global categories (no store/branch specified)
                $query->orWhereNull('store_code');
                $query->orWhereNull('branch');
            }) */
            ->orderBy('name', 'asc')
            ->get();
        
        // Get menus with user-specific filtering
        // 250623001 ken - 1/22/2026 - start here
        $menuQuery = Menu::with(['category', 'branch'])->where('is_deleted', 0);
        
        // Always filter by user's store code first
        if ($user->store_code) {
            $menuQuery = $menuQuery->where('store_code', $user->store_code);
        }
        
        // Apply search filter - UPDATED: Search by name, SKU, and barcode
        if (!empty($search)) {
            $menuQuery = $menuQuery->where(function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }
        
        if ($category != 'All') {
            $catVal = $category;
            $menuQuery = $menuQuery->where('category_id', $category);
        }
        
        if ($user->role != 4 && $user->role != 2) {
            // Admin/superuser sees all items in their store
            $menuQuery = $menuQuery->orderBy('name', 'asc');
            $items = DB::table('inventories')
            ->where('is_deleted', 0)->get();
        } else {
            // Regular users see only their department/branch
            $menuQuery = $menuQuery->whereIn('department', [1, 2, 3, 4, 5])
                                ->orderBy('name', 'asc');
            $items = DB::table('inventories')
                ->whereIn('department', [1, 2, 3, 4, 5])
                ->where('is_deleted', 0)
                ->get();
        }
        
        // Apply branch filter from dropdown (after store_code filter)
        if (!empty($branchFilter)) {
            $menuQuery = $menuQuery->where('branch_id', $branchFilter);
        }
        // 250623001 ken - 1/22/2026 - end here
        
        // Get all menus (no pagination for grouping)
        $allMenus = $menuQuery->get();
        
        // Group menus by name for the dynamic table
        $groupedMenus = [];
        foreach ($allMenus as $menu) {
            $groupedMenus[$menu->name][] = $menu;
        }
        
        $depts = DB::table('departments')->orderBy('id', 'asc')->where('is_deleted', 0)->get();
        
        // Get branches for the import modal and filter dropdown
        $branches = DB::table('settings')
            ->where('store_code', $user->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();

        $menuCount = count($groupedMenus);
        
        // FIX: Add 'groupedMenus', 'search', and 'branchFilter' to compact()
        return view('user.cook.menu-preparation', compact(
            'groupedMenus',  // Add this line
            'menuCount', 
            'page', 
            'category', 
            'items',
            'categories',
            'search',
            'depts',
            'catVal',
            'branches',
            'branchFilter'  // Add this line
        ));
    }

    public function add() {
        
        $categories = DB::table('menu_categories')
        //->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)->orderBy('name', 'asc')->get();
        
        $items = DB::table('inventories')
        ->select('id', 'name', 'unit', DB::raw('0 AS is_menu'))->where('is_deleted', 0)->get();
        
        $menus = DB::table('menus')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)->get();

        $depts = DB::table('departments')
        ->where('store_code', Auth::user()->store_code)
        ->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        $branches = DB::table('settings')
        ->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)
        ->orderBy('name', 'asc')
        ->get();

        $allBrands = DB::table('brands')
        //->where('store_code', Auth::user()->store_code)
        ->where('is_deleted', 0)
        ->orderBy('name', 'asc')
        ->get();

        return view('user.cook.menu-preparation-add', compact('categories', 'items', 'menus', 'depts','branches','allBrands'));
    }
    // ken end of new update index,  paginate, search, ,searchcategory, add,  - 250623001 - 8/6/2026

    public function changeIng(Request $request) {
        if ($request->combo == 'true') {
            $items = DB::table('menus')->select('id', 'name', 'unit','branch_id as branch', DB::raw('1 AS is_menu'))->where('is_deleted', 0)->where('is_combo', 0)->get();
        } else {
            $items = DB::table('inventories')->select('id', 'name', 'unit', 'branch', DB::raw('0 AS is_menu'))->where('is_deleted', 0)->get();
        }
        $li = '';


        foreach ($items as $item) {
            $branch = DB::table('settings')->where('id', $item->branch)->first();

            $s = $branch->name;
            if(preg_match_all('/\b(\w)/',strtoupper($s),$m)) {
                    $v = implode('',$m[1]); // $v is now SOQTU
            }
            $itemName = $item->name;
            $li .= '<li data-id="' . $item->id . '" data-name="' . $item->name . '" data-unit="' . $item->unit . '" data-is_menu="' . $item->is_menu . '" data-idnum="1" class="flex items-center pl-3 leading-9 rounded-md cursor-pointer h-9 hover:bg-gray-300">' . $itemName . ' -- '. $v. '</li>';
        }

        $result = '
            <div id="ing1" class="flex flex-row mb-5 gap-x-3">
                <div class="w-2/5">
                    <div class="relative w-full wrapper">
                        <div class="flex items-center justify-between p-2 bg-gray-100 border border-gray-300 rounded-md cursor-pointer select-btn h-9">
                            <span></span>
                            <i class="text-2xl transition-transform duration-300 uil uil-angle-down"></i>
                        </div>
                        <div class="absolute z-50 hidden w-full p-3 mt-1 bg-gray-100 rounded-md content">
                            <div class="relative search">
                                <i class="absolute leading-9 text-gray-500 uil uil-search left-3"></i> 
                                <input type="text" class="w-full leading-9 text-gray-700 rounded-md outline-none selectSearch pl-9 h-9" placeholder="Search">
                            </div>
                            <ul class="mt-2 overflow-y-auto listOption options max-h-52">
                                <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="1" class="flex items-center pl-3 leading-9 rounded-md cursor-pointer h-9 hover:bg-gray-300">None</li>
                                ' . $li . '
                            </ul>
                        </div>
                        <input type="hidden" name="item1" value="">
                    </div>
                </div>
                <div class="w-2/5">
                    <input type="text" id="quantity1" name="quantity1" value="" class="block w-full px-2 text-center text-gray-900 border border-gray-300 rounded-lg inputNumber h-9 bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                </div>
                <div class="flex w-1/5">
                    <div id="unit1" class="w-1/2 text-lg leading-9"></div>
                    <button type="button" data-thisid="ing1" class="w-1/2 text-center removeButton"><i class="text-3xl text-red-500 uil uil-minus-circle"></i></button>
                </div>
            </div>
        ';

        echo $result;
    }
    // 250623001 ken - 2/2/26 - update start
    public function addIng(Request $request) {
        $counter = $request->counter;

        if ($request->combo == 'true') {
            $items = DB::table('menus')->select('id', 'name', 'unit', DB::raw('1 AS is_menu'))->where('is_deleted', 0)->where('is_combo', 0)->get();
        } else {
            $items = DB::table('inventories')->select('id', 'name', 'unit', DB::raw('0 AS is_menu'))->where('is_deleted', 0)->get();
        }
        $li = '';

        foreach ($items as $item) {
            $li .= '<li data-id="' . $item->id . '" data-name="' . $item->name . '" data-unit="' . $item->unit . '" data-is_menu="' . $item->is_menu . '" data-idnum="' . $counter . '" class="flex items-center pl-3 leading-9 rounded-md cursor-pointer h-9 hover:bg-gray-300">' . $item->name . '</li>';
        }

        $result = '
            <div id="ing' . $counter . '" class="flex flex-row mb-5 gap-x-3">
                <div class="w-2/5">
                    <div class="relative w-full wrapper">
                        <div class="flex items-center justify-between p-2 bg-gray-100 border border-gray-300 rounded-md cursor-pointer select-btn h-9">
                            <span></span>
                            <i class="text-2xl transition-transform duration-300 uil uil-angle-down"></i>
                        </div>
                        <div class="absolute z-50 hidden w-full p-3 mt-1 bg-gray-100 rounded-md content">
                            <div class="relative search">
                                <i class="absolute leading-9 text-gray-500 uil uil-search left-3"></i>
                                <input type="text" class="w-full leading-9 text-gray-700 rounded-md outline-none selectSearch pl-9 h-9" placeholder="Search">
                            </div>
                            <ul class="mt-2 overflow-y-auto listOption options max-h-52">
                                <li data-id="" data-name="" data-unit="" data-is_menu="" data-idnum="1" class="flex items-center pl-3 leading-9 rounded-md cursor-pointer h-9 hover:bg-gray-300">None</li>
                                ' . $li . '
                            </ul>
                        </div>
                        <input type="hidden" name="item' . $counter . '" value="">
                    </div>
                </div>
                <div class="w-2/5">
                    <input type="text" id="quantity' . $counter . '" name="quantity' . $counter . '" value="" class="block w-full px-2 text-center text-gray-900 border border-gray-300 rounded-lg inputNumber h-9 bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                </div>
                <div class="flex w-1/5">
                    <div id="unit' . $counter . '" class="w-1/2 text-lg leading-9"></div>
                    <button type="button" data-thisid="ing' . $counter . '" class="w-1/2 text-center removeButton"><i class="text-3xl text-red-500 uil uil-minus-circle"></i></button>
                </div>
            </div>
        ';

        echo $result;
    }

    // ken start of update the store,edit, update method to include auto create ng brand code sa Same item same brand sa ibang Branch 8/06/2026
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required',
                'department' => 'required',
            ]);

            // Prevent duplicate active item names within the same store
            $name = ucwords(strtolower($request->name));
            $storeCode = Auth::user()->store_code;
            
            // IMAGE
            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/items'), $filename);
                $imagePath = 'uploads/items/'.$filename;
            }

            // Get all branches from settings table
            $branches = DB::table('settings')
                ->where('is_deleted', 0)
                ->select('store_code', 'name')
                ->get();

            // Check if user has selected any brands from the form
            $selectedBrandIds = [];
            if ($request->filled('branches')) {
                foreach ($request->branches as $brandId) {
                    if ($brandId != 1) {
                        $selectedBrandIds[] = $brandId;
                    }
                }
            }

            // Also check update_branch checkboxes
            foreach ($request->all() as $key => $value) {
                if (preg_match('/^update_branch_(\d+)$/', $key, $matches) && intval($value) === 1) {
                    $brandId = intval($matches[1]);
                    if ($brandId != 1) {
                        $selectedBrandIds[] = $brandId;
                    }
                }
            }

            // If no brands selected, use all brands
            if (empty($selectedBrandIds)) {
                $allBrands = DB::table('brands')
                    ->where('is_deleted', 0)
                    ->pluck('id')
                    ->toArray();
                $selectedBrandIds = $allBrands;
            }

            // Get category name for brand_code generation
            $categoryName = DB::table('menu_categories')->where('id', $request->category_id)->value('name');

            // Create item for each branch (store)
            foreach ($branches as $branch) {
                $branchStoreCode = $branch->store_code;
                
                // For each brand selected
                foreach ($selectedBrandIds as $brandId) {
                    // Check if menu already exists for this brand and branch
                    $exists = DB::table('menus')
                        ->where('name', $request->name)
                        ->where('brand_id', $brandId)
                        ->where('store_code', $branchStoreCode) // Use branch store_code
                        ->where('is_deleted', 0)
                        ->exists();

                    if (!$exists) {
                        $slug = Str::slug($request->name, '-');
                        $original = $slug;
                        $counter = 1;

                        while (DB::table('menus')->where('slug', $slug)->exists()) {
                            $slug = $original . '-' . $counter;
                            $counter++;
                        }

                        // Generate brand_code
                        $brandCode = null;
                        $matchingBrandCode = DB::table('menus')
                            ->join('menu_categories', 'menus.category_id', '=', 'menu_categories.id')
                            ->join('brands', 'menus.brand_id', '=', 'brands.id')
                            ->where('menus.name', $request->name)
                            ->where('menu_categories.name', $categoryName)
                            ->where('brands.name', DB::table('brands')->where('id', $brandId)->value('name'))
                            ->whereNotNull('menus.brand_code')
                            ->where('menus.brand_code', '!=', '')
                            ->select('menus.brand_code')
                            ->first();

                        if ($matchingBrandCode) {
                            $brandCode = $matchingBrandCode->brand_code;
                        } else {
                            do {
                                $brandCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                                $codeExists = DB::table('menus')
                                    ->where('brand_code', $brandCode)
                                    ->exists();
                            } while ($codeExists);
                        }

                        $branchCode = $request->name . '-' . $branchStoreCode;

                        // Get prices for this specific brand if provided, else use default values
                        $price = $request->input('prices.' . $brandId, $request->price);
                        $wholesale = $request->input('wholesaleprices.' . $brandId, $request->wholesale_price);
                        $slab = $request->input('slabprices.' . $brandId, $request->slab_price);
                        $retail = $request->input('retailprices.' . $brandId, $request->retail_price);
                        $stock = $request->input('stock.' . $brandId, $request->quantity);

                        $stock = preg_replace('/[^0-9\.]/', '', $stock);
                        $stock = $stock === '' ? 0 : floatval($stock);

                        DB::table('menus')->insert([
                            'name' => $request->name,
                            'category_id' => $request->category_id,
                            'department' => $request->department,
                            'brand_id' => $brandId,
                            'item_code' => $request->item_code,
                            'barcode' => $request->barcode,
                            'price' => $price,
                            'wholesale_price' => $wholesale,
                            'slab_price' => $slab,
                            'retail_price' => $retail,
                            'grab_price' => $request->grab ?? 0,
                            'panda_price' => $request->panda ?? 0,
                            'food_cost' => $request->foodCost ?? 0,
                            'quantity' => $stock,
                            'current_quantity' => $stock,
                            'reorder_point' => $request->reorder_point,
                            'is_uuid' => Str::uuid(),
                            'is_combo' => $request->combo ?? 0,
                            'is_discount' => $request->isDiscount ?? 0,
                            'is_hidden' => $request->hidden ?? 0,
                            'slug' => $slug,
                            'image' => $imagePath,
                            'store_code' => $branchStoreCode, // Use branch store_code
                            'branch_code' => $branchCode,
                            'brand_code' => $brandCode,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('menu.index')->with('message', 'Item created successfully across all branches');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::warning('Brand Insertion', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to create item: ' . $e->getMessage());
        }
    }

    public function edit($slug) {
        
        // Get the main menu item
        $item = DB::table('menus')->where('slug', $slug)->first();
    
        // If item doesn't exist, redirect back
        if (!$item) {
            return redirect()->route('menu.index')->with('error', 'Menu item not found.');
        }
        
        $categories = DB::table('menu_categories')
            //->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)->orderBy('name', 'asc')->get();

        $depts = DB::table('departments')
            //->where('store_code', Auth::user()->store_code)
            ->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        // Get all branches for the user's store
        $allBranches = DB::table('settings')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();

        $allBrands = DB::table('brands')
            //->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();



        $categoryName = DB::table('menu_categories')
            ->where('id', $item->category_id)
            ->value('name');

        // Get ALL menu items across brands that share the same name and category
        // (do not restrict by branch_code so we can surface brand-specific codes from other branches)
        $menuItemsByBrand = DB::table('menus')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->where('name', $item->name)
            ->where('category_id', $item->category_id)
            ->get()
            ->keyBy('brand_id');


        $brandCodeLookup = DB::table('menus')
            ->join('menu_categories', 'menus.category_id', '=', 'menu_categories.id')
            ->join('brands', 'menus.brand_id', '=', 'brands.id')
            //->where('menus.store_code', Auth::user()->store_code)
            ->where('menus.is_deleted', 0)
            ->where('menus.name', $item->name)
            ->where('menu_categories.name', $categoryName)
            ->whereNotNull('menus.brand_code')
            ->where('menus.brand_code', '!=', '')
            ->select('menus.brand_id', 'menus.brand_code')
            ->get()
            ->groupBy('brand_id')
            ->map(function ($rows) {
                return $rows->first()->brand_code ?? null;
            });

        if ($item->is_combo == 1) {
            $items = DB::table('menus')
                ->select('id', 'name', 'unit', 'branch_id as branch', DB::raw('1 AS is_menu'))
                ->where('is_deleted', 0)
                ->where('is_combo', 0)
                ->get();
            $ingredients = Ingredient::with('menu')->where('menu_id', $item->id)->where('is_deleted', 0)->get();
        } else {
            $items = DB::table('inventories')
                ->select('id', 'name', 'unit', 'branch', DB::raw('0 AS is_menu'))
                ->where('is_deleted', 0)
                ->get();
            $ingredients = Ingredient::with('inv')->where('menu_id', $item->id)->where('is_deleted', 0)->get();
        }

        
        return view('user.cook.menu-preparation-edit', compact(
            'item', 
            'ingredients', 
            'categories', 
            'items', 
            'slug',
            'depts',
            'allBranches',
            'allBrands',
            'menuItemsByBrand',
            'brandCodeLookup'
        ));
    }

    public function update(Request $request)
    {

        
        // Check if slug exists
        if (!$request->has('slug')) {
            return redirect()->back()->with('error', 'Slug is required for updating.');
        }

        $name = ucwords(strtolower($request->name));
        $storeCode = Auth::user()->store_code;

        $originalItem = DB::table('menus')->where('slug', $request->slug)->first();
        if (!$originalItem) {
            return redirect()->back()->withInput()->with('error', 'Original item not found.');
        }

        $originalName = ucwords(strtolower($originalItem->name));
        if ($name !== $originalName) {
            $duplicateName = DB::table('menus')
                ->where('store_code', $storeCode)
                ->where('brand_id', $originalItem->brand_id)
                ->where('name', $name)
                ->where('is_deleted', 0)
                ->where('slug', '!=', $request->slug)
                ->exists();

            if ($duplicateName) {
                return redirect()->back()->withInput()->with('error', 'Item name already exists for this brand. Please choose a different name.');
            }
        }

        // Ensure at least one brand checkbox was selected in the request
        // Log incoming update_branch_* inputs for debugging
        $updateBranchInputs = collect($request->all())->filter(function($value, $key) {
            return preg_match('/^update_branch_\d+$/', $key);
        })->toArray();
        \Log::info('Menu update - update_branch inputs', $updateBranchInputs);

        $selectedBrandInputs = collect($updateBranchInputs)->filter(function($value, $key) {
            return intval($value) === 1;
        });

        if ($selectedBrandInputs->isEmpty()) {
            \Log::warning('Menu update - no update_branch_* keys set to 1', $updateBranchInputs);
            return redirect()->back()->withInput()->with('error', 'Please select at least one brand to update.');
        }
        

        
        try {
            DB::transaction(function () use ($request) {

                $oldSlug = $request->slug;
                $name = ucwords(strtolower($request->name));
                $category_id = $request->category_id;
                $servings = $request->servings;
                $foodCost = $request->foodCost;
                $department = $request->department;
                $info = $request->info;
                $barcode = $request->barcode;
                $itemcode = $request->item_code;


                $combo = $request->combo ? 1 : 0;
                $hidden = $request->hidden ? 1 : 0;
                $isDiscount = $request->isDiscount ? 1 : 0;


                // ORIGINAL ITEM
                $originalItem = DB::table('menus')->where('slug', $oldSlug)->first();
                
                
                // Check if original item exists
                if (!$originalItem) {
                    throw new \Exception('Original menu item not found.');
                }
                
                $menuID = $originalItem->id;
                $originalBrandId = $originalItem->brand_id;

                // Get the category name for matching
                $categoryName = DB::table('menu_categories')->where('id', $category_id)->value('name');
        

                // ALL BRANDS - load all non-deleted brands (match edit view behavior)
                $allBrands = DB::table('brands')
                    ->where('is_deleted', 0)
                    ->orderBy('name', 'asc')
                    ->get();


                // Track if any brand was updated
                $brandsUpdated = 0;
                $brandsDeleted = 0;
                $brandsSkipped = 0;

                foreach ($allBrands as $brand) {

                    $updateKey = "update_branch_{$brand->id}";
                    $inputVal = $request->input($updateKey, null);
                    \Log::info('Menu update - processing brand', ['brand_id' => $brand->id, 'updateKey' => $updateKey, 'inputVal' => $inputVal]);
                    $shouldUpdate = $request->has($updateKey) && $inputVal == 1;
                    
                    // COMMON DATA
                    $stock = floatval(preg_replace('/[^0-9\.]/', '', $request->input("stock_{$brand->id}", 0)));
                    $price = is_numeric($request->price) ? floatval($request->price) : 0;
                    $reorder = is_numeric($request->input("reorder_{$brand->id}")) ? floatval($request->input("reorder_{$brand->id}")) : 0;

                    $wholesale = $request->input("wholesaleprices.{$brand->id}", 0);
                    $slab = $request->input("slabprices.{$brand->id}", 0);
                    $retail = $request->input("retailprices.{$brand->id}", 0);

                    $brandBarcode = $request->input("barcode_{$brand->id}", $barcode);
                    $brandItemCode = $request->input("item_code_{$brand->id}", $itemcode);


                    // FIND EXISTING (INCLUDING DELETED)
                    $existingMenuItem = DB::table('menus')
                        ->where('brand_id', $brand->id)
                        ->where('store_code', Auth::user()->store_code)
                        ->where('name', $originalItem->name)
                        ->first();

                    // BRAND CODE LOGIC
                    $brandCode = null;
                    
                    if ($shouldUpdate) {
                        $brandsUpdated++;
                        
                        // Get the brand name for THIS specific brand
                        $brandName = DB::table('brands')->where('id', $brand->id)->value('name');
    
                        // Look for existing brand_code
                        $matchingBrandCode = DB::table('menus')
                            ->join('menu_categories', 'menus.category_id', '=', 'menu_categories.id')
                            ->join('brands', 'menus.brand_id', '=', 'brands.id')
                            ->where('menus.name', $name)
                            ->where('menu_categories.name', $categoryName)
                            ->where('brands.name', $brandName)
                            ->whereNotNull('menus.brand_code')
                            ->where('menus.brand_code', '!=', '')
                            ->select('menus.brand_code')
                            ->first();

                        if ($matchingBrandCode) {
                            $brandCode = $matchingBrandCode->brand_code;
                        } else {
                            // Generate unique 6-digit brand_code
                            do {
                                $brandCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                                $codeExists = DB::table('menus')
                                    ->where('brand_code', $brandCode)
                                    ->exists();
                            } while ($codeExists);
                        }

                        if ($existingMenuItem) {
                            
                            // UPDATE + RESTORE
                            $updateData = [
                                'is_deleted' => 0,
                                'name' => $name,
                                'category_id' => $category_id,
                                'price' => $price,
                                'wholesale_price' => $wholesale,
                                'slab_price' => $slab,
                                'retail_price' => $retail,
                                'grab_price' => $request->grab ?? 0,
                                'panda_price' => $request->panda ?? 0,
                                'quantity' => $stock,
                                'current_quantity' => $stock,
                                'reorder_point' => $reorder,
                                'servings' => $servings,
                                'is_combo' => $combo,
                                'info' => $info,
                                'is_hidden' => $hidden,
                                'is_discount' => $isDiscount,
                                'food_cost' => $foodCost,
                                'department' => $department,
                                'barcode' => $brandBarcode,
                                'item_code' => $brandItemCode,
                                'brand_code' => $brandCode,
                                'slug' => Str::random(60),
                                'updated_at' => now(),
                            ];
                            
                            DB::table('menus')->where('id', $existingMenuItem->id)->update($updateData);
                
                            // If this is the original brand, update the menuID reference
                            if ($brand->id == $originalBrandId) {
                                $menuID = $existingMenuItem->id;
                            }

                        } else {
                            
                            // INSERT NEW
                            $newMenuId = DB::table('menus')->insertGetId([
                                'name' => $name,
                                'category_id' => $category_id,
                                'price' => $price,
                                'wholesale_price' => $wholesale,
                                'slab_price' => $slab,
                                'retail_price' => $retail,
                                'grab_price' => $request->grab ?? 0,
                                'panda_price' => $request->panda ?? 0,
                                'quantity' => $stock,
                                'current_quantity' => $stock,
                                'reorder_point' => $reorder,
                                'servings' => $servings,
                                'is_combo' => $combo,
                                'info' => $info,
                                'is_hidden' => $hidden,
                                'is_discount' => $isDiscount,
                                'food_cost' => $foodCost,
                                'department' => $department,
                                'store_code' => Auth::user()->store_code,
                                'brand_id' => $brand->id,
                                'branch_id' => $originalItem->branch_id,
                                'barcode' => $brandBarcode,
                                'item_code' => $brandItemCode,
                                'brand_code' => $brandCode,
                                'branch_code' => $originalItem->branch_code,
                                'slug' => Str::random(60),
                                'is_uuid' => Str::uuid(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            

                            if ($brand->id == $originalBrandId) {
                                $menuID = $newMenuId;
                            }
                        }
                    } else {
                        // UNCHECKED → SOFT DELETE
                        if ($existingMenuItem) {
                            $brandsDeleted++;
                            DB::table('menus')
                                ->where('id', $existingMenuItem->id)
                                ->update([
                                    'is_deleted' => 1,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            $brandsSkipped++;
                        }
                    }
                }


                // If no brands were updated, throw an error
                if ($brandsUpdated == 0 && $brandsDeleted == 0) {
                    throw new \Exception('No brand was selected for update.');
                }

                // Update the main item if it exists in the menu
                $mainItem = DB::table('menus')->where('id', $menuID)->first();
                
                if ($mainItem) {
                    // Update the main item with common fields
                    $mainUpdateData = [
                        'name' => $name,
                        'category_id' => $category_id,
                        'servings' => $servings,
                        'food_cost' => $foodCost,
                        'department' => $department,
                        'info' => $info,
                        'is_combo' => $combo,
                        'is_hidden' => $hidden,
                        'is_discount' => $isDiscount,
                        'barcode' => $barcode,
                        'item_code' => $itemcode,
                        'updated_at' => now(),
                    ];
                    DB::table('menus')->where('id', $menuID)->update($mainUpdateData);
                } 

                // INGREDIENTS - Only update if menuID is valid
                if ($menuID) {
                    DB::table('ingredients')->where('menu_id', $menuID)->delete();
                    for ($i = 1; $i <= $request->counter; $i++) {

                        $iname = 'item' . $i;
                        $qname = 'quantity' . $i;

                        if ($request->$iname != null) {
                            $itemsArray = explode(",", $request->$iname);
                            $is_menu = $itemsArray[0];
                            $item_id = $itemsArray[1];

                            $unit = $combo == 1
                                ? 'pc/s'
                                : DB::table('inventories')->where('id', $item_id)->value('unit');

                            DB::table('ingredients')->insert([
                                'menu_id' => $menuID,
                                'inventory_id' => $item_id,
                                'quantity' => $request->$qname,
                                'computed_quantity' => $request->$qname / $servings,
                                'unit' => $unit,
                                'is_menu' => $is_menu,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    
                }

            });
            return redirect()->route('menu.index')
                ->with('message', 'Menu item updated successfully!');

        } catch (\Exception $e) {

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update menu item: ' . $e->getMessage());
        }
    }
    // ken end of update the store,edit, update method to include auto create ng brand code sa Same item same brand sa ibang Branch 8/06/2026
    
   // public function update(Request $request) {
        //     $oldSlug = $request->slug;
        //     $name = strtolower($request->name);
        //     $name = ucwords($name);
        //     $category_id = $request->category_id;
        //     $servings = $request->servings;
        //     $foodCost = $request->foodCost;
        //     $department = $request->department;
        //     $info = $request->info;
        //     $barcode = $request->barcode;
        //     $itemcode = $request->item_code;
            
        //     if ($request->combo != null) {
        //         $combo = 1;
        //     } else {
        //         $combo = 0;
        //     }
            
        //     if ($request->hidden != null) {
        //         $hidden = 1;
        //     } else {
        //         $hidden = 0;
        //     }

        //     if ($request->isDiscount != null) {
        //         $isDiscount = 1;
        //     } else {
        //         $isDiscount = 0;
        //     }
            
        //     // Get the original menu item being edited
        //     $originalItem = DB::table('menus')->where('slug', $oldSlug)->first();
        //     $menuID = $originalItem->id;
        //     $originalBranchId = $originalItem->branch_id;
        //     $originalBrandId = $originalItem->brand_id;
        //     $originalName = $originalItem->name;

        //     // Process brand-specific data - ONLY for checked brands
        //     $allBrands = DB::table('brands')
        //         ->where('store_code', Auth::user()->store_code)
        //         ->where('is_deleted', 0)
        //         ->orderBy('name', 'asc')
        //         ->get();

        //     foreach ($allBrands as $brand) {
        //         // Check if this brand is explicitly marked for update
        //         $updateKey = "update_branch_{$brand->id}";
        //         $shouldUpdate = false;

        //         if ($request->has($updateKey) && $request->$updateKey == 1) {
        //             $shouldUpdate = true;
        //         }

        //         if ($shouldUpdate) {
        //             // Only process if checkbox is checked
        //             $stockKey = "stock_{$brand->id}";
        //             $reorderKey = "reorder_{$brand->id}";
                    
        //             $stock = $request->input($stockKey, 0);
        //             $stock = trim((string) $stock);
        //             $stock = preg_replace('/[^0-9\.]/', '', $stock);
        //             $stock = $stock === '' ? 0 : floatval($stock);

        //             // Use the common price field (same as the create form) unless branch-specific price overrides are introduced
        //             $price = $request->input('price');
        //             $price = is_numeric($price) ? floatval($price) : 0;

        //             $reorder = $request->input($reorderKey);
        //             $reorder = is_numeric($reorder) ? floatval($reorder) : 0;

        //             // Brand-specific pricing
        //             $wholesale = $request->wholesaleprices[$brand->id] ?? $request->wholesale_price ?? 0;
        //             $slab = $request->slabprices[$brand->id] ?? $request->slab_price ?? 0;
        //             $retail = $request->retailprices[$brand->id] ?? $request->retail_price ?? 0;
                    
        //             // Get brand-specific barcode and item code if they exist
        //             $brandBarcodeKey = "barcode_{$brand->id}";
        //             $brandItemCodeKey = "item_code_{$brand->id}";
                    
        //             $brandBarcode = $request->has($brandBarcodeKey) ? $request->$brandBarcodeKey : $barcode;
        //             $brandItemCode = $request->has($brandItemCodeKey) ? $request->$brandItemCodeKey : $itemcode;
                    
        //             // Check if a menu item exists for this brand (match by brand + store, and by name/original id)
        //             $existingMenuItem = DB::table('menus')
        //                 ->where('brand_id', $brand->id)
        //                 ->where('store_code', Auth::user()->store_code)
        //                 ->where('is_deleted', 0)
        //                 ->where(function($q) use ($originalName, $name, $menuID) {
        //                     $q->where('name', $originalName)
        //                       ->orWhere('name', $name)
        //                       ->orWhere('id', $menuID);
        //                 })
        //                 ->first();
                    
        //             if ($existingMenuItem) {
        //                 // Update existing menu item for this brand
        //                 DB::table('menus')->where('id', $existingMenuItem->id)->update([
        //                     'name' => $name, // Update to new name
        //                     'category_id' => $category_id,
        //                     'price' => $price,
        //                     'wholesale_price' => $wholesale,
        //                     'slab_price' => $slab,
        //                     'retail_price' => $retail,
        //                     'grab_price' => $request->grab ?? 0,
        //                     'panda_price' => $request->panda ?? 0,
        //                     'quantity' => $stock,
        //                     'current_quantity' => $stock,
        //                     'reorder_point' => $reorder,
        //                     'servings' => $servings,
        //                     'is_combo' => $combo,
        //                     'info' => $info,
        //                     'is_hidden' => $hidden,
        //                     'is_discount' => $isDiscount,
        //                     'food_cost' => $foodCost,
        //                     'department' => $department,
        //                     'barcode' => $brandBarcode,
        //                     'item_code' => $brandItemCode,
        //                     'slug' => Str::slug($name . '-' . $brand->id, '-'),
        //                     'updated_at' => now(),
        //                 ]);

        //                 // Ensure ingredients update is done on the original item (not brand-specific)
        //                 if ($existingMenuItem->brand_id == $originalBrandId) {
        //                     $menuID = $existingMenuItem->id;
        //                 }
        //             } else {
        //                 // Create new brand menu item only if it doesn't exist
        //                 $slug = Str::slug($name . '-' . $brand->id, '-');
                        
        //                 $newMenuId = DB::table('menus')->insertGetId([
        //                         'name' => $name,
        //                         'category_id' => $category_id,
        //                         'price' => $price,
        //                         'wholesale_price' => $wholesale,
        //                         'slab_price' => $slab,
        //                         'retail_price' => $retail,
        //                         'grab_price' => $request->grab,
        //                         'panda_price' => $request->panda,
        //                         'quantity' => $stock,
        //                         'current_quantity' => $stock,
        //                         'reorder_point' => $reorder,
        //                         'servings' => $servings,
        //                         'is_combo' => $combo,
        //                         'info' => $info,
        //                         'is_hidden' => $hidden,
        //                         'is_discount' => $isDiscount,
        //                         'food_cost' => $foodCost,
        //                         'department' => $department,
        //                         'store_code' => Auth::user()->store_code,
        //                         'brand_id' => $brand->id,
        //                         'branch_id' => $originalBranchId,
        //                         'barcode' => $brandBarcode,
        //                         'item_code' => $brandItemCode,
        //                         'branch_code' => $originalItem->branch_code,
        //                         'slug' => $slug,
        //                         'is_uuid' => Str::uuid(),
        //                         'created_at' => now(),
        //                         'updated_at' => now(),
        //                     ]);
                        
        //                 // If this is the brand we're editing (based on original item's brand_id)
        //                 if ($brand->id == $originalBrandId) {
        //                     $menuID = $newMenuId;
        //                 }
        //             }
        //         }
        //     }

        //     // Update ingredients for the menu item
        //     $Totalcounter = $request->counter;
        //     DB::table('ingredients')->where('menu_id', $menuID)->delete();

        //     for ($counter = 1; $counter <= $Totalcounter; $counter++) {
        //         $iname = 'item' . $counter;
        //         $qname = 'quantity' . $counter;

        //         if ($request->$iname != null) {
        //             $itemsArray = explode(",", $request->$iname);
        //             $is_menu = $itemsArray[0];
        //             $item_id = $itemsArray[1];

        //             if ($combo == 1) {
        //                 $invUnit = 'pc/s';
        //             } else {
        //                 $invUnit = (DB::table('inventories')->where('id', $item_id)->first())->unit;
        //             }

        //             $ingr = new Ingredient();
        //             $ingr->menu_id = $menuID;
        //             $ingr->inventory_id = $item_id;
        //             $ingr->quantity = $request->$qname;
        //             $ingr->computed_quantity = $request->$qname / $servings;
        //             $ingr->unit = $invUnit;
        //             $ingr->is_menu = $is_menu;
        //             $ingr->save();
        //         }
        //     }

        //     return redirect()->route('menu.index')->withInput()->with('message', 'Successfully Updated');
    // }
    
    /* public function update(Request $request)
    {
        DB::transaction(function () use ($request) {

            $oldSlug = $request->slug;
            $name = ucwords(strtolower($request->name));
            $category_id = $request->category_id;
            $servings = $request->servings;
            $foodCost = $request->foodCost;
            $department = $request->department;
            $info = $request->info;
            $barcode = $request->barcode;
            $itemcode = $request->item_code;

            $combo = $request->combo ? 1 : 0;
            $hidden = $request->hidden ? 1 : 0;
            $isDiscount = $request->isDiscount ? 1 : 0;

            // ORIGINAL ITEM
            $originalItem = DB::table('menus')->where('slug', $oldSlug)->first();
            $menuID = $originalItem->id;
            $originalBrandId = $originalItem->brand_id;

            // ALL BRANDS
            $allBrands = DB::table('brands')
                ->where('store_code', Auth::user()->store_code)
                ->where('is_deleted', 0)
                ->get();

            foreach ($allBrands as $brand) {

                $updateKey = "update_branch_{$brand->id}";
                $shouldUpdate = $request->has($updateKey);

                // COMMON DATA
                $stock = floatval(preg_replace('/[^0-9\.]/', '', $request->input("stock_{$brand->id}", 0)));
                $price = is_numeric($request->price) ? floatval($request->price) : 0;
                $reorder = is_numeric($request->input("reorder_{$brand->id}")) ? floatval($request->input("reorder_{$brand->id}")) : 0;

                $wholesale = $request->wholesaleprices[$brand->id] ?? 0;
                $slab = $request->slabprices[$brand->id] ?? 0;
                $retail = $request->retailprices[$brand->id] ?? 0;

                $brandBarcode = $request->input("barcode_{$brand->id}", $barcode);
                $brandItemCode = $request->input("item_code_{$brand->id}", $itemcode);

                // 🔥 FIND EXISTING (INCLUDING DELETED)
                $existingMenuItem = DB::table('menus')
                    ->where('brand_id', $brand->id)
                    ->where('store_code', Auth::user()->store_code)
                    ->where('name', $originalItem->name)
                    ->first();

                if ($shouldUpdate) {

                    if ($existingMenuItem) {

                        // ✅ UPDATE + RESTORE
                        DB::table('menus')->where('id', $existingMenuItem->id)->update([
                            'is_deleted' => 0, // 🔥 RESTORE
                            'name' => $name,
                            'category_id' => $category_id,
                            'price' => $price,
                            'wholesale_price' => $wholesale,
                            'slab_price' => $slab,
                            'retail_price' => $retail,
                            'grab_price' => $request->grab ?? 0,
                            'panda_price' => $request->panda ?? 0,
                            'quantity' => $stock,
                            'current_quantity' => $stock,
                            'reorder_point' => $reorder,
                            'servings' => $servings,
                            'is_combo' => $combo,
                            'info' => $info,
                            'is_hidden' => $hidden,
                            'is_discount' => $isDiscount,
                            'food_cost' => $foodCost,
                            'department' => $department,
                            'barcode' => $brandBarcode,
                            'item_code' => $brandItemCode,
                            'slug' => Str::slug($name . '-' . $brand->id, '-'),
                            'updated_at' => now(),
                        ]);

                        if ($brand->id == $originalBrandId) {
                            $menuID = $existingMenuItem->id;
                        }

                    } else {

                        // ✅ INSERT NEW
                        $newMenuId = DB::table('menus')->insertGetId([
                            'name' => $name,
                            'category_id' => $category_id,
                            'price' => $price,
                            'wholesale_price' => $wholesale,
                            'slab_price' => $slab,
                            'retail_price' => $retail,
                            'grab_price' => $request->grab ?? 0,
                            'panda_price' => $request->panda ?? 0,
                            'quantity' => $stock,
                            'current_quantity' => $stock,
                            'reorder_point' => $reorder,
                            'servings' => $servings,
                            'is_combo' => $combo,
                            'info' => $info,
                            'is_hidden' => $hidden,
                            'is_discount' => $isDiscount,
                            'food_cost' => $foodCost,
                            'department' => $department,
                            'store_code' => Auth::user()->store_code,
                            'brand_id' => $brand->id,
                            'branch_id' => $originalItem->branch_id,
                            'barcode' => $brandBarcode,
                            'item_code' => $brandItemCode,
                            'branch_code' => $originalItem->branch_code,
                            'slug' => Str::slug($name . '-' . $brand->id, '-'),
                            'is_uuid' => Str::uuid(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        if ($brand->id == $originalBrandId) {
                            $menuID = $newMenuId;
                        }
                    }

                } else {

                    // ❌ UNCHECKED → SOFT DELETE
                    if ($existingMenuItem) {
                        DB::table('menus')
                            ->where('id', $existingMenuItem->id)
                            ->update([
                                'is_deleted' => 1,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            // ✅ INGREDIENTS
            DB::table('ingredients')->where('menu_id', $menuID)->delete();

            for ($i = 1; $i <= $request->counter; $i++) {

                $iname = 'item' . $i;
                $qname = 'quantity' . $i;

                if ($request->$iname != null) {

                    $itemsArray = explode(",", $request->$iname);
                    $is_menu = $itemsArray[0];
                    $item_id = $itemsArray[1];

                    $unit = $combo == 1
                        ? 'pc/s'
                        : DB::table('inventories')->where('id', $item_id)->value('unit');

                    DB::table('ingredients')->insert([
                        'menu_id' => $menuID,
                        'inventory_id' => $item_id,
                        'quantity' => $request->$qname,
                        'computed_quantity' => $request->$qname / $servings,
                        'unit' => $unit,
                        'is_menu' => $is_menu,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

        });

        return redirect()->route('menu.index')
            ->withInput()
            ->with('message', 'Successfully Updated');
    } */

    public function updateCategory(Request $request) {
        $slug = $request->slug;
        $categoryId = $request->category_id;

        // Find the menu item by slug
        $menu = DB::table('menus')->where('slug', $slug)->first();
        if (!$menu) {
            return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
        }

        // Update category for all menu items with the same name in the same store (across branches)
        DB::table('menus')
            ->where('name', $menu->name)
            ->where('store_code', Auth::user()->store_code)
            ->update([
                'category_id' => $categoryId,
                'updated_at' => now(),
            ]);

        $categoryName = DB::table('menu_categories')->where('id', $categoryId)->value('name');

        return response()->json(['success' => true, 'category_name' => $categoryName]);
    }

    // 250623001 ken - 2/2/26 - update end

    public function move(Request $request) {
        $slug = $request->moveSlug;
        $qty = $request->moveServings;

        $inv = DB::table('inventories')->where('slug', $slug)->first();
        $menu = DB::table('menus')->where('slug', $slug)->first();

        $nname = $menu->name;

        if (!$inv) {
            $cat = DB::table('categories')->where('slug', 'menu')->first();

            if (!$cat) {
                $ncat = new Category();
                $ncat->name = 'Menu';
                $ncat->slug = 'menu';
                $ncat->save();

                $catID = $ncat->id;
            } else {
                $catID = $cat->id;
            }

            $ninv = new Inventory();
            $ninv->name = $nname;
            $ninv->category_id = $catID;
            $ninv->quantity = $qty;
            $ninv->reorder_point = '0';
            $ninv->unit = 'pc';
            $ninv->slug = $slug;
            $ninv->save();
        } else {
            DB::table('inventories')->where('slug', $slug)->update([
                'quantity' => $inv->quantity + $qty
            ]);
        }

        DB::table('menus')->where('slug', $slug)->update([
            'current_quantity' => $menu->current_quantity - $qty,
            'quantity' => $menu->quantity - $qty
        ]);

        // if(auth()->user()->role == 1){
        // return redirect()->route('menu.index')->withInput()->with('message', 'Successfully Updated');
        // }elseif(auth()->user()->role == 3){
        return redirect()->route('menu.index')->withInput()->with('message', 'Successfully Moved');
        // }
    }

    public function view(Request $request) {
        $slug = $request->slug;
        $is_combo = $request->is_combo;
        $menu = DB::table('menus')->where('slug', $slug)->first();
        $name = $menu->name;
        $id = $menu->id;
        $servings = $menu->servings;

            // leo start 4/20/25
        if ( auth()->user()->role == 4){

        if ($is_combo == 0){
            $ings = DB::table('ingredients')
            ->select('ingredients.menu_id', 'ingredients.inventory_id', 'inventories.name as name', 'ingredients.quantity', 'ingredients.computed_quantity', 'ingredients.unit')
            ->join('inventories', 'ingredients.inventory_id', '=', 'inventories.id')
            ->where('menu_id', $id)->where('ingredients.is_deleted', 0)->orderBy('ingredients.id', 'asc')->get();
        } else {
            $ings = DB::table('ingredients')
            ->select('ingredients.menu_id', 'ingredients.inventory_id', 'menus.name as name', 'ingredients.quantity', 'ingredients.computed_quantity', 'ingredients.unit')
            ->join('menus', 'ingredients.inventory_id', '=', 'menus.id')
            ->where('menu_id', $id)->where('ingredients.is_deleted', 0)->orderBy('ingredients.id', 'asc')->get();
        }

        $ingredients = '';
        $x = 1;

        foreach ($ings as $ing) {
            if ($is_combo == 0){
                $availq = DB::table('inventories')->where('id', $ing->inventory_id)->first();
            } else {
                $availq = DB::table('menus')->where('id', $ing->inventory_id)->first();
            }

            $is_lowstock = round($ing->computed_quantity * $servings, 2) > round($availq->quantity, 2); 
            if(!$is_lowstock){
                continue;
            }
            $lowstock = 'color: red;';

            $nq = 'ingq' . $x;
            $ingredients .= '
            <div class="flex items-center px-4 text-xl font-semibold tracking-wide">
                <span style="width: 100px; ' . $lowstock . '">PORTION</span>
            </div>
                <div class="grid sm:flex sm:flex-wrap items-center  px-4 text-xl font-semibold tracking-wide mb-2">
                    <div class="mb-1">
                        <span style="margin-left: 22px; white-space: nowrap; ' . $lowstock . '" >' . $ing->name . '</span>
                    </div>
                            
                    <div class="flex">
                        <input type="text" name="' . $nq . '" value="' . round($ing->computed_quantity * $servings, 2) . '" class="text-center text-gray-900 quantity w-16 h-9 bg-transparent text-l border-0" autocomplete="off" disabled>
                        <span style="margin-left: 8px; white-space: nowrap; ' . $lowstock . '">' . $ing->unit . '</span> 
                        <span style="margin-left: 10px; white-space: nowrap; ' . $lowstock . '">/</span>

                        <span style="margin-left: 10px; width: auto; nowrap; ' . $lowstock . '">' . round($availq->quantity, 2) . '</span>
                        <span style="margin-left: 10px; white-space: nowrap; ' . $lowstock . '">' . $availq->unit . '</span>
                    </div>
                </div>
                
            ';
            $x++;

            if ($is_combo == 1) {
                $sub_ingredients = DB::table('ingredients')
                    ->select('ingredients.inventory_id', 'inventories.name as name', 'ingredients.quantity', 'ingredients.computed_quantity', 'ingredients.unit')
                    ->join('inventories', 'ingredients.inventory_id', '=', 'inventories.id')
                    ->where('menu_id', $ing->inventory_id)
                    ->where('ingredients.is_deleted', 0)
                    ->orderBy('ingredients.id', 'asc')
                    ->get();

                    $raw_displayed = 0; //false
            
                foreach ($sub_ingredients as $sub_ing) {
                    $sub_availq = DB::table('inventories')->where('id', $sub_ing->inventory_id)->first();

                    $is_sublowstock = round($ing->computed_quantity * $servings, 2) > round($availq->quantity, 2); 
                    if(!$is_sublowstock){
                        continue;
                    }
                    $sub_lowstock = 'color: red;';

                    if (!$raw_displayed) {
                        $raw_label = 'RAW';
                        $raw_displayed = 1; //true
                    } else {
                        $raw_label = '';
                    }
            
                    $ingredients .= '
                  
                    <div class="flex items-center px-4 text-xl font-semibold tracking-wide">
                        <span style="width: 100px; ' . $sub_lowstock . '">' . $raw_label . '</span>
                    </div>
                        <div class="grid sm:flex sm:flex-wrap items-center  px-4 text-xl font-semibold tracking-wide mb-2">
                    <div class="mb-1">
                        <span style="margin-left: 22px; white-space: nowrap; ' . $sub_lowstock . '" >' . $sub_ing->name . '</span>
                    </div>
                            
                    <div class="flex">
                        <input type="text" name="' . $nq . '" value="' . round($ing->computed_quantity * $servings, 2) . '" class="text-center text-gray-900 quantity w-16 h-9 bg-transparent text-l border-0" autocomplete="off" disabled>
                        <span style="margin-left: 8px; white-space: nowrap; ' . $sub_lowstock . '">' . $sub_ing->unit . '</span> 
                        <span style="margin-left: 10px; white-space: nowrap; ' . $sub_lowstock . '">/</span>

                        <span style="margin-left: 10px; width: auto; nowrap; ' . $sub_lowstock . '">' . round($sub_availq->quantity, 2) . '</span>
                        <span style="margin-left: 10px; white-space: nowrap; ' . $sub_lowstock . '">' . $sub_availq->unit . '</span>
                    </div>
                </div>
                
                    ';
                  

                    
                }
            }
        }
    } else {

        if ($is_combo == 0){
            $ings = DB::table('ingredients')
            ->select('ingredients.menu_id', 'ingredients.inventory_id', 'inventories.name as name', 'ingredients.quantity', 'ingredients.computed_quantity', 'ingredients.unit')
            ->join('inventories', 'ingredients.inventory_id', '=', 'inventories.id')
            ->where('menu_id', $id)->where('ingredients.is_deleted', 0)->orderBy('ingredients.id', 'asc')->get();
        } else {
            $ings = DB::table('ingredients')
            ->select('ingredients.menu_id', 'ingredients.inventory_id', 'menus.name as name', 'ingredients.quantity', 'ingredients.computed_quantity', 'ingredients.unit')
            ->join('menus', 'ingredients.inventory_id', '=', 'menus.id')
            ->where('menu_id', $id)->where('ingredients.is_deleted', 0)->orderBy('ingredients.id', 'asc')->get();
        }

        $ingredients = '';
        $x = 1;

        foreach ($ings as $ing) {
            if ($is_combo == 0){
                $availq = DB::table('inventories')->where('id', $ing->inventory_id)->first();
            } else {
                $availq = DB::table('menus')->where('id', $ing->inventory_id)->first();
            }

            $nq = 'ingq' . $x;
            $lowstock = round($ing->computed_quantity * $servings, 2) > round($availq->quantity, 2) ? 'color: red;' : ''; 
            $ingredients .= '
            <div class="flex items-center px-4 text-xl font-semibold tracking-wide">
                <span style="width: 100px; ' . $lowstock . '">PORTION</span>
            </div>
                <div class="grid sm:flex sm:flex-wrap items-center  px-4 text-xl font-semibold tracking-wide mb-2">
                    <div class="mb-1">
                        <span style="margin-left: 22px; white-space: nowrap; ' . $lowstock . '" >' . $ing->name . '</span>
                    </div>
                            
                    <div class="flex">
                        <input type="text" name="' . $nq . '" value="' . round($ing->computed_quantity * $servings, 2) . '" class="block px-2 text-center text-gray-900border border-gray-300 rounded-lg quantity ml-5 w-24 h-9 bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base"autocomplete="off">
                        <span style="margin-left: 8px; white-space: nowrap; ' . $lowstock . '">' . $ing->unit . '</span> 
                        <span style="margin-left: 10px; white-space: nowrap; ' . $lowstock . '">/</span>

                        <span style="margin-left: 10px; width: auto; nowrap; ' . $lowstock . '">' . round($availq->quantity, 2) . '</span>
                        <span style="margin-left: 10px; white-space: nowrap; ' . $lowstock . '">' . $availq->unit . '</span>
                    </div>
                </div>
            ';
            $x++;

            if ($is_combo == 1) {
                $sub_ingredients = DB::table('ingredients')
                    ->select('ingredients.inventory_id', 'inventories.name as name', 'ingredients.quantity', 'ingredients.computed_quantity', 'ingredients.unit')
                    ->join('inventories', 'ingredients.inventory_id', '=', 'inventories.id')
                    ->where('menu_id', $ing->inventory_id)
                    ->where('ingredients.is_deleted', 0)
                    ->orderBy('ingredients.id', 'asc')
                    ->get();

                    $raw_displayed = false;
            
                foreach ($sub_ingredients as $sub_ing) {
                    $sub_availq = DB::table('inventories')->where('id', $sub_ing->inventory_id)->first();
                    $sub_lowstock = round($sub_ing->computed_quantity * $servings, 2) > round($sub_availq->quantity, 2) ? 'color: red;' : '';

                    $raw_label = !$raw_displayed ? 'RAW' : '';
                    if (!$raw_displayed) {
                        $raw_displayed = true;
                    }
            
                    $ingredients .= '
               
                    <div class="flex items-center px-4 text-xl font-semibold tracking-wide">
                        <span style="width: 100px; ' . $sub_lowstock . '">' . $raw_label . '</span>
                    </div>
                        <div class="grid sm:flex sm:flex-wrap items-center  px-4 text-l font-semibold tracking-wide mb-2">
                            <div class="mb-1">
                                <span style="margin-left: 22px; white-space: nowrap; ' . $sub_lowstock . '" >' . $sub_ing->name . '</span>
                            </div>
                            
                            <div class="flex">
                                <input type="text" name="' . $nq . '" value="' . round($sub_ing->computed_quantity * $servings, 2) . '" class="block px-2 text-center text-gray-900 border border-gray-300 rounded-lg quantity ml-5 w-24 h-9 bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                                <span style="margin-left: 8px; white-space: nowrap; ' . $sub_lowstock . '">' . $sub_ing->unit . '</span> 

                                <span style="margin-left: 10px; white-space: nowrap; ' . $sub_lowstock . '">/</span>

                                <span style="margin-left: 10px; width: auto; nowrap; ' . $sub_lowstock . '">' . round($sub_availq->quantity, 2) . '</span>
                                <span style="margin-left: 10px; white-space: nowrap; ' . $sub_lowstock . '">' . $sub_availq->unit . '</span>
                            </div>
                        </div>
                
                    ';
                     

                    
                }
            }
        }
    } // leo end 4/20/25

        // Include all brand variants for this menu item (for per-brand stock inputs)
        $brands = DB::table('menus')
            ->select('menus.brand_id', 'brands.name as brand_name', 'menus.quantity', 'menus.current_quantity', 'menus.slug')
            ->join('brands', 'menus.brand_id', '=', 'brands.id')
            ->where('menus.name', $name)
            ->where('menus.store_code', Auth::user()->store_code)
            ->where('menus.is_deleted', 0)
            ->get();

        $result = array(
            'name' => $name,
            'ingredients' => $ingredients,
            'servings' => $servings,
            'brands' => $brands,
        );

        echo json_encode($result);
    }

    public function computeqty(Request $request) {
        $slug = $request->slug;
        $qty = $request->qty;

        $menu = DB::table('menus')->where('slug', $slug)->first();
        $name = $menu->name;
        $id = $menu->id;

        // Retrieve all brand variants for this menu item (by name) so we can adjust quantities per brand
        $brands = DB::table('menus')
            ->select('menus.brand_id', 'brands.name as brand_name', 'menus.quantity', 'menus.current_quantity', 'menus.slug')
            ->join('brands', 'menus.brand_id', '=', 'brands.id')
            ->where('menus.name', $name)
            ->where('menus.store_code', Auth::user()->store_code)
            ->where('menus.is_deleted', 0)
            ->get();

        $ings = DB::table('ingredients')
            ->select('ingredients.menu_id', 'ingredients.inventory_id', 'inventories.name as name', 'ingredients.quantity', 'ingredients.computed_quantity', 'ingredients.unit')
            ->join('inventories', 'ingredients.inventory_id', '=', 'inventories.id')
            ->where('menu_id', $id)->where('ingredients.is_deleted', 0)->orderBy('ingredients.id', 'asc')->get();

        $ingredients = '';
        $x = 1;

        foreach ($ings as $ing) {
            $availq = DB::table('inventories')->where('id', $ing->inventory_id)->first();
            $nq = 'ingq' . $x;
            $nqty = round($ing->computed_quantity * $qty, 2);

            $ingredients .= '
                <div class="flex items-center px-4 text-xl font-semibold tracking-wide">
                    <input type="text" name="' . $nq . '" value="' . $nqty . '" class="block px-2 text-center text-gray-900 border border-gray-300 rounded-lg quantity w-52 h-9 bg-gray-50 sm:text-xs focus:ring-blue-500 focus:border-blue-500 lg:text-base" autocomplete="off">
                    <span style="margin-left: 10px;">' . $ing->unit . '</span>

                    <span style="margin-left: 30px;">/</span>

                    <span style="margin-left: 10px; width: 100px; text-align: right;">' . round($availq->quantity, 2) . '</span>
                    <span style="margin-left: 10px;">' . $availq->unit . '</span>
                    <span style="margin-left: 32px;">' . $ing->name . '</span>
                </div>
            ';
            $x++;
        }

        $result = array(
            'name' => $name,
            'ingredients' => $ingredients,
            'brands' => $brands,
        );

        echo json_encode($result);
    }

    public function changeqty(Request $request) {
        $slug = $request->slug;
        $brandId = $request->brand_id ?? null;
        $quantity = $request->servings;
        $Totalcounter = $request->counter;

        $menuQuery = DB::table('menus')->where('slug', $slug)->where('store_code', Auth::user()->store_code);
        if ($brandId) {
            $menuQuery->where('brand_id', $brandId);
        }

        $menu = $menuQuery->first();
        if (!$menu) {
            return redirect()->route('menu.index')->withInput()->with('error', 'Menu item not found for the selected brand.');
        }

        $cur_quantity = $menu->quantity;
        $uqty = $cur_quantity + $quantity;

        $menuID = $menu->id;
        $menuName = $menu->name;

        //ken update start here ken250623001 adding quantity per brand.
        // If brand-specific stock values are provided, update each brand row individually
        $brandStockInput = $request->input('brand_stock', []);
        if (!is_array($brandStockInput)) {
            $brandStockInput = [];
        }

        $updatedCount = 0;

        foreach ($brandStockInput as $brandIdKey => $brandStockValue) {
            $brandId = intval($brandIdKey);
            if ($brandId <= 0) {
                continue;
            }

            $brandStock = trim((string) $brandStockValue);
            $brandStock = preg_replace('/[^0-9\.]/', '', $brandStock);
            $brandStock = $brandStock === '' ? 0 : floatval($brandStock);
            $brandStock = round($brandStock, 2);

            // Skip if user didn't input anything (or entered 0)
            if ($brandStock <= 0) {
                continue;
            }

            // Update the specific menu variant for this brand (if it exists)
            // Use the item name (not slug) because slug may vary per brand variant.
            $brandMenu = DB::table('menus')
                ->where('name', $menuName)
                ->where('store_code', Auth::user()->store_code)
                ->where('brand_id', $brandId)
                ->where('is_deleted', 0)
                ->first();

            if ($brandMenu) {
                $newQty = round($brandMenu->quantity + $brandStock, 2);

                DB::table('menus')->where('id', $brandMenu->id)->update([
                    'quantity' => $newQty,
                    'current_quantity' => $newQty,
                    'updated_at' => now(),
                ]);

                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            return redirect()->route('menu.index')->withInput()->with('message', 'Brand stock updated successfully');
        }
        //ken update end here ken250623001 adding quantity per brand.

        // If a single brand stock value is provided, update the selected menu item
        if (!is_null($brandStockInput) && trim($brandStockInput) !== '') {
            $brandStock = trim((string) $brandStockInput);
            $brandStock = preg_replace('/[^0-9\.]/', '', $brandStock);
            $brandStock = $brandStock === '' ? 0 : floatval($brandStock);

            DB::table('menus')->where('id', $menuID)->update([
                'quantity' => $brandStock,
                'current_quantity' => $brandStock,
                'updated_at' => now(),
            ]);

            return redirect()->route('menu.index')->withInput()->with('message', 'Brand stock updated successfully');
        }
        $ings = DB::table('ingredients')->where('menu_id', $menuID)->where('is_deleted', 0)->orderBy('id', 'asc')->get();

        if ($ings->count() > 0) {
            $x = 1;
            foreach ($ings as $ing) {
                $nq = 'ingq' . $x;
                $old_quantity = (DB::table('inventories')->where('id', $ing->inventory_id)->first())->quantity;
                $tqty = $request->$nq;
                $new_quantity = $old_quantity - $tqty;

                if ($new_quantity < 0) {
                    return redirect()->route('menu.index')->withInput()->with('error', 'Insufficient Ingredients');
                }

                $x++;
            }
            $x = 1;
            foreach ($ings as $ing) {
                $nq = 'ingq' . $x;
                $inventoryItem = DB::table('inventories')->where('id', $ing->inventory_id)->first();
                $old_quantity = $inventoryItem->quantity;
                $tqty = $request->$nq;
                $new_quantity = $old_quantity - $tqty;

                DB::table('inventories')->where('id', $ing->inventory_id)->update([
                    'quantity' => $new_quantity
                ]);

                $it = new InventoryTransaction();
                $it->inv_id = $ing->inventory_id;
                $it->department_id = $inventoryItem->department;
                $it->type = 'OUTGOING';
                $it->quantity_before = round($old_quantity, 2);
                $it->quantity = $tqty;
                $it->quantity_after = $new_quantity;
                $it->remarks = $menuName;
                $it->user_id = Auth::id();
                $it->save();

                $x++;
            }
        }

        for ($counter = 1; $counter <= $Totalcounter; $counter++) {
            $iname = 'item' . $counter;
            $qname = 'quantity' . $counter;
            $inv = DB::table('inventories')->where('id', $request->$iname)->first();

            if ($request->$iname != null) {
                $it = new InventoryTransaction();
                $it->inv_id = $request->$iname;
                $it->type = 'OUTGOING';
                $it->quantity_before = $inv->quantity;
                $it->quantity = $request->$qname;
                $it->quantity_after = $inv->quantity - $request->$qname;
                $it->remarks = $menuName;
                $it->user_id = Auth::id();
                $it->save();

                DB::table('inventories')->where('id', $request->$iname)->update([
                    'quantity' => $inv->quantity - $request->$qname
                ]);
            }
        }

        DB::table('menus')->where('slug', $slug)
            ->update([
                'current_quantity' => $uqty,
                'quantity' => $uqty,
                'updated_at' => now(),
                
            ]);

        return redirect()->route('menu.index')->withInput()->with('message', 'Quantity Successfully Changed');
    }

    public function delete($slug) {
        $menuID = (DB::table('menus')->where('slug', $slug)->first())->id;

        DB::table('ingredients')->where('menu_id', $menuID)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
            ]);

        DB::table('ingredients')->where('is_menu', 1)->where('inventory_id', $menuID)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
            ]);

        DB::table('menus')->where('slug', $slug)
            ->update([
                'is_deleted' => 1,
                'updated_at' => now(),
            ]);

        return redirect()->route('menu.index')->withInput()->with('message', 'Successfully Deleted');
    }

    public function dispose(Request $request) {
        $menu = DB::table('menus')->where('slug', $request->disposeSlug)->first();
        $quantity = $request->quantity;
        $date = date('Y-m-d H:i:s', strtotime($request->disposeDate . ' ' . date('H:i:s')));
        $waste_remarks = $request->waste_remarks;

        if ($menu->current_quantity < $quantity) {
            return redirect()->route('menu.index')->withInput()->with('error', 'Please Enter a valid Quantity.');
        }

        $menuPrice = DB::table('menus')
            ->select('price')
            ->where('id', $menu->id)
            ->first();

        $cost = $menuPrice->price * $quantity;

        $waste = new Waste();
        $waste->on = 'MENU';
        $waste->iid = $menu->id;
        $waste->quantity = $quantity;
        $waste->cost = $cost;
        $waste->created_at = $date;
        $waste->waste_remarks = $waste_remarks;
        $waste->store_code = Auth::user()->store_code;
        $waste->save();

        DB::table('menus')->where('slug', $request->disposeSlug)->decrement('quantity', $quantity);
        DB::table('menus')->where('slug', $request->disposeSlug)->decrement('current_quantity', $quantity);

        return redirect()->route('menu.index')->withInput()->with('message', 'Menu Successfully Disposed');
    }
    
    // 4-27 harold

    public function deletedmenu() {
         
        $deletedmenu = DB::table('menus')->where('is_deleted', 1)->orderBy('name', 'asc')->get();
        $search = "";
        $page = 1;
        $menuCount = DB::table('menus')->where('is_deleted','1')->get()->count();

        return view('user.inventory.deletedmenu.index', compact('deletedmenu','search','page','menuCount'));

    }


        public function menurestore(Request $request)
    {
        $id = $request->id;

        DB::table('menus')
            ->where('id', $id)
            ->update(['is_deleted' => 0]);

        return response()->json(['message' => 'Menu restored successfully']);
    }


    public function searchdeletedmenu($page, $search){
        $deletedmenu = DB::table('menus')
            ->select('*')
            ->where('is_deleted', 1)
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->orderBy('name', 'asc')
            ->paginate(100,'*','page',$page);

        $menuCount = $deletedmenu->total();
        return view('user.inventory.deletedmenu.index', compact('deletedmenu', 'menuCount', 'page', 'search'));
    }









    public function deletedraw() {
         
        $deletedmenu = DB::table('inventories')->where('is_deleted', 1)->orderBy('name', 'asc')->get();
        $search = "";
        $page = 1;
        $menuCount = DB::table('inventories')->where('is_deleted','1')->get()->count();

        return view('user.inventory.deletedraw.index', compact('deletedmenu','search','page','menuCount'));

    }


    
        public function rawrestore(Request $request)
    {
        $id = $request->id;

        DB::table('inventories')
            ->where('id', $id)
            ->update(['is_deleted' => 0]);

        return response()->json(['message' => 'Menu restored successfully']);
    }


    public function searchdeletedraw($page, $search){
        $deletedmenu = DB::table('inventories')
            ->select('*')
            ->where('is_deleted', 1)
            ->whereRaw("CONCAT_WS(' ', name) LIKE '%{$search}%'")
            ->orderBy('name', 'asc')
            ->paginate(100,'*','page',$page);

        $menuCount = $deletedmenu->total();
        return view('user.inventory.deletedraw.index', compact('deletedmenu', 'menuCount', 'page', 'search'));
    }



    // 4-27 harold  end
    // added start 20250623001 ken 
    // start of ken added Export, Import and downloadtemplate csv menus to CSV function  12-13-2025

    public function exportMenusCSV(Request $request, $branch = null): StreamedResponse
    {
        $branchId = $branch ?: null;

        //$fileName = 'menu_import_template_' . date('Y-m-d') . '_branch_' . $branchId . '.csv';
        $fileName = 'menu_export_download' . date('Y-m-d');

        if ($branchId) {
            $fileName .= '_branch_' . $branchId;
        } else {
            $fileName .= '_all_branches';
        }

        $fileName .= '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
            'X-Frame-Options' => 'SAMEORIGIN',
        ];

        $callback = function () use ($branchId) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // CSV headers - MATCHING YOUR SCREENSHOT ORDER
            fputcsv($handle, [
                //'id',               // 6 - as in your screenshot
                'name',             // 7 - as in your screenshot
                'sku',      // 8 - as in your screenshot
                'current_quantity', // 9
                'quantity',         // 10
                'price',            // 11
                'grab_price',       // 12
                'panda_price',      // 13
               // 'image',            // 14
                //'slug',           // 15
                //'info',           // 16
                'food_cost',        // 17
                //'department',       // 18
                //'branch_id',      // 19
                //'created_at',     // 20
                //'updated_at',     // 21
                'servings',         // 22
                'is_combo',         // 23
                'unit',             // 24
                //'is_discount',    // 25
                //'is_hidden',      // 26
                'reorder_point',    // 27
                //'is_deleted',     // 28
                'bar_code',   
                'is_uuid',          // 1 - as in your screenshot
                //'client_token',   // 2 - as in your screenshot (THIS WAS WRONG)
                //'is_sync',        // 3 - as in your screenshot
                //'branch_code',    // 4 - as in your screenshot
                //'local_id',       // 5 - as in your screenshot     
            ]);
            
            // Get a few real examples from the database
            // $exampleMenus = DB::table('menus')
            //     ->limit(5)
            //     ->orderBy('id', 'asc')
            //     ->get();

            $exampleMenus = Menu::with('category')
            ->where('store_code', Auth::user()->store_code)
            ->when($branchId !== null, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where('is_deleted', 0)->orderBy('name', 'asc')->get();
            
            // Add example data
            if ($exampleMenus->count() > 0) {
                foreach ($exampleMenus as $menu) {
                    fputcsv($handle, [
                        //$menu->id ?? '',              // 6
                        $menu->name ?? '',            // 7
                        $menu->item_code ?? '',             // 8
                        $menu->current_quantity ?? 0, // 9
                        $menu->quantity ?? 0,         // 10
                        $menu->price ?? 0,            // 11
                        $menu->grab_price ?? 0,       // 12
                        $menu->panda_price ?? 0,      // 13
                       // $menu->image ?? '',           // 14
                        //$menu->slug ?? '',           // 15
                        //$menu->info ?? '',           // 16
                        $menu->food_cost ?? 0,        // 17
                        //$menu->department ?? 0,       // 18
                        //$menu->branch_id ?? 0,       // 19
                        //$menu->created_at ?? '',     // 20
                        //$menu->updated_at ?? '',     // 21
                        $menu->servings ?? 1,         // 22
                        $menu->is_combo ?? 0,         // 23
                        $menu->unit ?? '',            // 24
                        //$menu->is_discount ?? 0,     // 25
                        //$menu->is_hidden ?? 0,       // 26
                        $menu->reorder_point ?? 0,    // 27
                        //$menu->is_deleted ?? 0,      // 28
                        $menu->barcode ?? '', 
                        $menu->is_uuid ?? '',         // 1
                        //$menu->client_token ?? '',   // 2
                        //$menu->is_sync ?? 0,         // 3
                        //$menu->branch_code ?? '',    // 4
                        //$menu->local_id ?? '',       // 5    
                    ]);
                }
            } else {
                // Fallback examples if no menus exist
                fputcsv($handle, [
                    '',                   // id - leave empty for new record
                    'Chicken Adobo',      // name
                    1,                    // category_id
                    25,                   // current_quantity
                    25,                   // quantity
                    120.00,               // price
                    150.00,               // grab_price
                    140.00,               // panda_price
                    'images/menu/chicken-adobo.jpg', // image
                    //'chicken-adobo',    // slug
                    //'Traditional Filipino chicken adobo', // info
                    45.50,                // food_cost
                    1,                    // department
                    //1,                  // branch_id
                    //date('Y-m-d H:i:s'), // created_at
                    //date('Y-m-d H:i:s'), // updated_at
                    1,                    // servings
                    0,                    // is_combo
                    'plate',              // unit
                    //0,                  // is_discount
                    //0,                  // is_hidden
                    10,                   // reorder_point
                    //0,                  // is_deleted
                    '',                   // bar_code
                    '',                   // is_uuid - leave empty for auto-generation
                    //'',                 // client_token
                    //0,                  // is_sync
                    //'DEFAULT',          // branch_code
                    //'',                 // local_id                    
                ]);
            }
            
            // ---------- DEFAULT CATEGORY FOR TEMPLATE ----------
            $defaultCategoryId = DB::table('menu_categories')
                ->where('is_deleted', 0)
                ->value('id') ?? 1;
            
            // Add instructions
           /// fputcsv($handle, ['']); // Empty row
           // fputcsv($handle, ['INSTRUCTIONS FOR COMPLETE DATA IMPORT:']);
           // fputcsv($handle, ['1. Required fields: name, category_id, price']);
           // fputcsv($handle, ['2. Optional fields: all others - leave blank for defaults']);
           // fputcsv($handle, ['3. If id is provided and exists in database, that record will be UPDATED']);
           // fputcsv($handle, ['4. If no id is provided, a new record will be CREATED']);
           // fputcsv($handle, ['5. is_uuid: Auto-generated if empty. If you want to add quantity, remove the is_uuid']);
           // fputcsv($handle, ['6. client_token: Auto-generated if empty']);
          //  fputcsv($handle, ['7. slug: Auto-generated from name if empty']);
           // fputcsv($handle, ['8. Default category_id: ' . ($defaultCategoryId ?? 1)]);
           // fputcsv($handle, ['9. Export first to see complete data structure with values']);
           // fputcsv($handle, ['10. Column order must match exactly']);
            
           // fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function validateMenusCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $selectedBranchId = $request->input('branch_id');

        $warnings = [];
        $errors = [];

        try {
            $handle = fopen($file->getPathname(), 'r');
            if (!$handle) throw new \Exception('Unable to open CSV file.');

            $row = 0;
            $headers = [];
            $user = auth()->user();

            // Check if "All Branches" is selected
            $importToAllBranches = ($selectedBranchId == 0 || $selectedBranchId == 'all' || $selectedBranchId == '');

            if (!$importToAllBranches) {
                // Get branch info for specific branch
                $branchInfo = DB::table('settings')
                    ->where('id', $selectedBranchId)
                    ->first();

                if (!$branchInfo) {
                    throw new \Exception('Selected branch not found in settings table.');
                }

                $branchList = [$branchInfo];
            } else {
                // Get all branches for the user's store
                $branchList = DB::table('settings')
                    ->where('store_code', $user->store_code)
                    ->where('is_deleted', 0)
                    ->get();

                if ($branchList->isEmpty()) {
                    throw new \Exception('No branches found for your store.');
                }
            }

            $storeCode = Auth::user()->store_code ?? '';

            // Helper functions
            $int = fn($v, $d = 0) => ($v === '' || $v === null) ? $d : (int)$v;
            $float = fn($v, $d = 0) => ($v === '' || $v === null) ? $d : (float)$v;
            $str = fn($v, $d = '') => ($v === '' || $v === null) ? $d : trim((string)$v);

            // UUID validator
            $isValidUuid = fn($uuid) => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);

            $csvUuids = [];
            $processedItems = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row++;

                try {
                    if (empty(array_filter($data))) continue;

                    // Headers
                    if ($row === 1) {
                        $headers = array_map(fn($h) => str_replace('﻿', '', trim($h)), $data);
                        continue;
                    }

                    $rowData = array_combine($headers, $data);

                    // Check if name exists in CSV row
                    if (empty($rowData['name'])) {
                        $errors[] = "Row {$row}: This item doesn't have a name. We'll skip it for now - please add a name if you want to include it.";
                        continue;
                    }

                    // Check if SKU exists in CSV row (warning only)
                    if (empty($rowData['sku'])) {
                        $warnings[] = "Row {$row}: No SKU provided. We'll create this item without a SKU - you can add one later if needed.";
                    }

                    // Clean and format the name
                    $itemName = trim($rowData['name']);
                    $csvUuid = $str($rowData['is_uuid']);
                    $hasUuidInCsv = $csvUuid && $isValidUuid($csvUuid);

                    // Check for duplicate UUID in CSV
                    if ($hasUuidInCsv && in_array($csvUuid, $csvUuids)) {
                        $warnings[] = "Row {$row}: We found another item with the same ID in your file. We'll use the first one we found.";
                        continue;
                    }
                    if ($hasUuidInCsv) $csvUuids[] = $csvUuid;

                    // Process for each branch
                    foreach ($branchList as $branchInfo) {
                        $userBranch = (int)$branchInfo->id;
                        $branchCode = $branchInfo->branch_code ?? '';
                        $branchId = (int)$branchInfo->id;

                        // Check for duplicates within CSV (name + branch combination)
                        $itemKey = $itemName . '_' . $userBranch;
                        if (isset($processedItems[$itemKey])) {
                            $warnings[] = "Row {$row}: You have '{$itemName}' listed more than once. We'll use the first version.";
                            continue 2; // Skip to next row
                        }
                        $processedItems[$itemKey] = true;

                        // Check for existing items that would be updated
                        $existingMenu = null;

                        // Priority 1: If CSV has valid UUID, find by UUID first
                        if ($hasUuidInCsv) {
                            $existingMenu = Menu::where('branch', $userBranch)
                                ->where('store_code', $storeCode)
                                ->where('is_uuid', $csvUuid)
                                ->first();

                            if ($existingMenu) {
                                $warnings[] = "Row {$row}: '{$itemName}' already exists in our system. We'll update it with your new information.";
                            }
                        }

// Priority 2: If no UUID match, try multiple matching criteria
                        if (!$existingMenu) {
                            // Method 1: Match by SKU if provided
                            if (!empty($csvSku)) {
                                $existingMenu = Menu::where('branch', $userBranch)
                                    ->where('store_code', $storeCode)
                                    ->where('item_code', $csvSku)
                                    ->first();
                            }
                            
                            // Method 2: Match by barcode if provided and no SKU match
                            if (!$existingMenu && !empty($csvBarcode)) {
                                $existingMenu = Menu::where('branch', $userBranch)
                                    ->where('store_code', $storeCode)
                                    ->where('barcode', $csvBarcode)
                                    ->first();
                            }
                            
                            // Method 3: Match by name if no SKU/barcode match
                            if (!$existingMenu) {
                                // Exact name match
                                $existingMenu = Menu::where('branch', $userBranch)
                                    ->where('store_code', $storeCode)
                                    ->where('name', $itemName)
                                    ->first();
                                
                                // Case-insensitive name match
                                if (!$existingMenu) {
                                    $existingMenu = Menu::where('branch', $userBranch)
                                        ->where('store_code', $storeCode)
                                        ->whereRaw('LOWER(name) = ?', [strtolower($itemName)])
                                        ->first();
                                }
                                
                                // Try with branch_id
                                if (!$existingMenu) {
                                    $existingMenu = Menu::where('branch_id', $branchId)
                                        ->where('store_code', $storeCode)
                                        ->where('name', $itemName)
                                        ->first();
                                }
                            }
                        }

                        if ($existingMenu) {
                            $matchType = $hasUuidInCsv ? 'UUID' : (!empty($csvSku) && $existingMenu->item_code == $csvSku ? 'SKU' : (!empty($csvBarcode) && $existingMenu->barcode == $csvBarcode ? 'barcode' : 'name'));
                            $warnings[] = "Row {$row}: '{$itemName}' is already in our menu (matched by {$matchType}). We'll refresh it with your latest details.";
                        }

                        // Check for UUID conflicts
                        if ($hasUuidInCsv) {
                            // Check if another item in this branch already has this UUID
                            $uuidConflict = Menu::where('branch', $userBranch)
                                ->where('store_code', $storeCode)
                                ->where('is_uuid', $csvUuid)
                                ->where('id', '!=', $existingMenu->id ?? 0)
                                ->exists();
                            if ($uuidConflict) {
                                $errors[] = "Row {$row}: This item's ID is already being used by another menu item. Please use a different ID or update the existing item instead.";
                                continue 2; // Skip to next row
                            }
                        }
                    }

                } catch (\Exception $e) {
                    // For validation, we'll be more lenient - most data format issues can be handled during import
                    $warnings[] = "Row {$row}: We'll handle any data format issues automatically during import.";
                }
            }

            fclose($handle);

            return response()->json([
                'valid' => empty($errors),
                'warnings' => $warnings,
                'errors' => $errors,
                'requires_confirmation' => !empty($warnings)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'warnings' => [],
                'errors' => ['We had trouble reading your file. Please make sure it\'s a valid CSV file and try again.'],
                'requires_confirmation' => false
            ], 400);
        }
    }

    /**
     * ken 250623001 updated: CSV Import Performance Optimization - 02-27-2026
     * 
     * Optimizations implemented:
     * - Pre-load all existing menus with multi-index cache (UUID, SKU, barcode, name)
     * - Batch inserts/updates instead of row-by-row operations
     * - Eliminated N+1 database queries (21K+ queries -> ~100)
     * - Expected performance: 10-15x faster for 5K+ item CSVs (8-15 min -> 30-60 sec)
     */
    /* start ken 250623001 updated: CSV Import Performance Optimization - 02-27-2026 */
    public function importMenusCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $selectedBranchId = $request->input('branch_id');

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $duplicateUuidSkippedCount = 0;
        $duplicateNameSkippedCount = 0;
        $errors = [];

        try {
            // Start database transaction to ensure all-or-nothing processing
            DB::beginTransaction();
            
            $handle = fopen($file->getPathname(), 'r');
            if (!$handle) throw new \Exception('Unable to open CSV file.');

            $row = 0;
            $headers = [];
            $user = auth()->user();
            
            // Check if "All Branches" is selected
            $importToAllBranches = ($selectedBranchId == 0 || $selectedBranchId == 'all' || $selectedBranchId == '');
            
            if (!$importToAllBranches) {
                // Get branch info for specific branch
                $branchInfo = DB::table('settings')
                    ->where('id', $selectedBranchId)
                    ->first();
                    
                if (!$branchInfo) {
                    throw new \Exception('Selected branch not found in settings table.');
                }
                
                $branchList = [$branchInfo];
            } else {
                // Get all branches for the user's store
                $branchList = DB::table('settings')
                    ->where('store_code', $user->store_code)
                    ->where('is_deleted', 0)
                    ->get();
                    
                if ($branchList->isEmpty()) {
                    throw new \Exception('No branches found for your store.');
                }
                
                \Log::info('Importing to all branches', [
                    'branch_count' => $branchList->count(),
                    'store_code' => $user->store_code
                ]);
            }
            
            $storeCode = Auth::user()->store_code ?? '';

            if (empty($storeCode)) {
                throw new \Exception('User does not have a store_code assigned. Please contact your administrator.');
            }

            \Log::info('Menu CSV Import - Starting', [
                'user_id' => $user->id,
                'selected_branch_id' => $selectedBranchId,
                'import_to_all_branches' => $importToAllBranches,
                'store_code' => $storeCode,
                'file' => $file->getClientOriginalName()
            ]);

            // Helper functions
            $int = fn($v, $d = 0) => ($v === '' || $v === null) ? $d : (int)$v;
            $float = fn($v, $d = 0) => ($v === '' || $v === null) ? $d : (float)$v;
            $str = fn($v, $d = '') => ($v === '' || $v === null) ? $d : trim((string)$v);

            // Default category - try user's store first, then fallback to any category
            $defaultCategoryId = DB::table('menu_categories')
                ->where('store_code', $user->store_code)
                ->where('is_deleted', 0)
                ->orderBy('id')
                ->value('id');
                
            // If no category for user's store, use any active category as fallback
            if (!$defaultCategoryId) {
                $defaultCategoryId = DB::table('menu_categories')
                    ->where('is_deleted', 0)
                    ->orderBy('id')
                    ->value('id');
                    
                if (!$defaultCategoryId) {
                    throw new \Exception('No menu categories found in the system. Please create at least one category before importing items.');
                }
                
                \Log::warning('Using fallback category for import', [
                    'user_store_code' => $user->store_code,
                    'fallback_category_id' => $defaultCategoryId
                ]);
            }
                
            // Default department - try user's store first, then fallback
            $defaultDepartmentId = DB::table('departments')
                ->where('store_code', $user->store_code)
                ->where('is_deleted', 0)
                ->orderBy('id')
                ->value('id');
                
            // If no department for user's store, use any active department as fallback
            if (!$defaultDepartmentId) {
                $defaultDepartmentId = DB::table('departments')
                    ->where('is_deleted', 0)
                    ->orderBy('id')
                    ->value('id');
                    
                \Log::warning('Using fallback department for import', [
                    'user_store_code' => $user->store_code,
                    'fallback_department_id' => $defaultDepartmentId
                ]);
            }

            // UUID validator
            $isValidUuid = fn($uuid) => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);

            // Unique slug generator
            $generateUniqueSlug = function ($baseSlug) {
                $slug = $baseSlug;
                $i = 1;
                while (Menu::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $i;
                    $i++;
                }
                return $slug;
            };

            // UUID generator
            $generateUuid = function () {
                return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
            };

            $csvUuids = [];
            $processedItems = [];

            // ===== PERFORMANCE OPTIMIZATION: PRE-LOAD ALL EXISTING MENUS =====
            $existingMenus = [];
            $branchIds = collect($branchList)->pluck('id')->toArray();
            
            // Load all existing menus for these branches indexed by branch+identifier
            $allExistingMenus = DB::table('menus')
                ->whereIn('branch', $branchIds)
                ->where('store_code', $storeCode)
                ->get();
            
            foreach ($allExistingMenus as $menu) {
                $branchKey = $menu->branch;
                if (!isset($existingMenus[$branchKey])) {
                    $existingMenus[$branchKey] = [
                        'by_uuid' => [],
                        'by_sku' => [],
                        'by_barcode' => [],
                        'by_name' => [],
                        'all' => []
                    ];
                }
                
                // Index by multiple keys for fast lookup
                if (!empty($menu->is_uuid)) {
                    $existingMenus[$branchKey]['by_uuid'][$menu->is_uuid] = $menu;
                }
                if (!empty($menu->item_code)) {
                    $existingMenus[$branchKey]['by_sku'][$menu->item_code] = $menu;
                }
                if (!empty($menu->barcode)) {
                    $existingMenus[$branchKey]['by_barcode'][$menu->barcode] = $menu;
                }
                $nameKey = strtolower($menu->name);
                $existingMenus[$branchKey]['by_name'][$nameKey] = $menu;
                $existingMenus[$branchKey]['all'][$menu->id] = $menu;
            }
            
            // Batch processing variables
            $batchSize = 100;
            $insertBatch = [];
            $updateBatch = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row++;

                try {
                    if (empty(array_filter($data))) continue;

                    // Headers
                    if ($row === 1) {
                        $headers = array_map(fn($h) => str_replace('﻿', '', trim($h)), $data);
                        continue;
                    }

                    $rowData = array_combine($headers, $data);
                    
                    // Gracefully handle missing columns by adding null values
                    $expectedColumns = ['name', 'sku', 'current_quantity', 'quantity', 'price', 'grab_price', 'panda_price', 
                                       'food_cost', 'servings', 'is_combo', 'reorder_point', 'unit', 'bar_code', 'is_uuid', 'category_id', 'created_at'];
                    foreach ($expectedColumns as $col) {
                        if (!isset($rowData[$col])) {
                            $rowData[$col] = null;
                        }
                    }

                    // Check if name exists in CSV row
                    if (empty($rowData['name'])) {
                        $skippedCount++;
                        continue;
                    }

                    // Clean and format the name
                    $itemName = trim($rowData['name']);
                    $csvUuid = $str($rowData['is_uuid'] ?? '');
                    $hasUuidInCsv = $csvUuid && $isValidUuid($csvUuid);

                    // Get values from CSV (with null coalescing for safety)
                    $csvSku = $str($rowData['sku'] ?? '');
                    $csvCurrentQuantity = $float($rowData['current_quantity'] ?? 0);
                    $csvQuantity = $float($rowData['quantity'] ?? 0);
                    $csvPrice = ($rowData['price'] ?? '') !== '' && ($rowData['price'] ?? null) !== null ? (float)$rowData['price'] : null;
                    $csvGrabPrice = ($rowData['grab_price'] ?? '') !== '' && ($rowData['grab_price'] ?? null) !== null ? (float)$rowData['grab_price'] : null;
                    $csvPandaPrice = ($rowData['panda_price'] ?? '') !== '' && ($rowData['panda_price'] ?? null) !== null ? (float)$rowData['panda_price'] : null;
                    $csvFoodCost = ($rowData['food_cost'] ?? '') !== '' && ($rowData['food_cost'] ?? null) !== null ? (float)$rowData['food_cost'] : null;
                    $csvServings = ($rowData['servings'] ?? '') !== '' && ($rowData['servings'] ?? null) !== null ? (int)$rowData['servings'] : null;
                    $csvIsCombo = ($rowData['is_combo'] ?? '') !== '' && ($rowData['is_combo'] ?? null) !== null ? (int)$rowData['is_combo'] : null;
                    $csvReorderPoint = ($rowData['reorder_point'] ?? '') !== '' && ($rowData['reorder_point'] ?? null) !== null ? (int)$rowData['reorder_point'] : null;
                    $csvUnit = $str($rowData['unit'] ?? '');
                    $csvBarcode = $str($rowData['bar_code'] ?? '');
                    $csvCategoryId = isset($rowData['category_id']) ? $int($rowData['category_id'] ?? 0) : 0;

                    // Check for duplicate UUID in CSV
                    if ($hasUuidInCsv && in_array($csvUuid, $csvUuids)) {
                        $duplicateUuidSkippedCount++;
                        continue;
                    }
                    if ($hasUuidInCsv) $csvUuids[] = $csvUuid;

                    // Process for each branch - OPTIMIZED with pre-loaded data
                    foreach ($branchList as $branchInfo) {
                        $userBranch = (int)$branchInfo->id;
                        $branchCode = $branchInfo->branch_code ?? '';
                        $branchId = (int)$branchInfo->id;
                        
                        // Check for duplicates within CSV (name + branch combination)
                        $itemKey = $itemName . '_' . $userBranch;
                        if (isset($processedItems[$itemKey])) {
                            $duplicateNameSkippedCount++;
                            continue 2; // Skip to next row
                        }
                        $processedItems[$itemKey] = true;

                        // ===== FAST LOOKUP using pre-loaded indexes =====
                        $existingMenu = null;
                        $branchMenus = $existingMenus[$userBranch] ?? ['by_uuid' => [], 'by_sku' => [], 'by_barcode' => [], 'by_name' => []];

                        // Priority 1: UUID lookup (fastest)
                        if ($hasUuidInCsv && isset($branchMenus['by_uuid'][$csvUuid])) {
                            $existingMenu = $branchMenus['by_uuid'][$csvUuid];
                        }
                        
                        // Priority 2: SKU lookup
                        if (!$existingMenu && !empty($csvSku) && isset($branchMenus['by_sku'][$csvSku])) {
                            $existingMenu = $branchMenus['by_sku'][$csvSku];
                        }
                        
                        // Priority 3: Barcode lookup
                        if (!$existingMenu && !empty($csvBarcode) && isset($branchMenus['by_barcode'][$csvBarcode])) {
                            $existingMenu = $branchMenus['by_barcode'][$csvBarcode];
                        }
                        
                        // Priority 4: Name lookup (case-insensitive)
                        if (!$existingMenu) {
                            $nameKey = strtolower($itemName);
                            if (isset($branchMenus['by_name'][$nameKey])) {
                                $existingMenu = $branchMenus['by_name'][$nameKey];
                            }
                        }
                        
                        if ($existingMenu) {
                            // ===== UPDATE EXISTING ITEM =====
                            $updateData = [];
                            
                            if (!empty($csvSku) && $existingMenu->item_code != $csvSku) {
                                $updateData['item_code'] = $csvSku;
                            }
                            if ($csvCurrentQuantity > 0) {
                                $updateData['current_quantity'] = DB::raw('current_quantity + ' . $csvCurrentQuantity);
                            }
                            if ($csvQuantity > 0) {
                                $updateData['quantity'] = DB::raw('quantity + ' . $csvQuantity);
                            }
                            if ($csvPrice > 0 && $existingMenu->price != $csvPrice) {
                                $updateData['price'] = $csvPrice;
                            }
                            if ($csvGrabPrice > 0 && $existingMenu->grab_price != $csvGrabPrice) {
                                $updateData['grab_price'] = $csvGrabPrice;
                            }
                            if ($csvPandaPrice > 0 && $existingMenu->panda_price != $csvPandaPrice) {
                                $updateData['panda_price'] = $csvPandaPrice;
                            }
                            if ($csvFoodCost > 0 && $existingMenu->food_cost != $csvFoodCost) {
                                $updateData['food_cost'] = $csvFoodCost;
                            }
                            if ($csvServings > 0 && $existingMenu->servings != $csvServings) {
                                $updateData['servings'] = $csvServings;
                            }
                            if ($csvIsCombo > 0 && $existingMenu->is_combo != $csvIsCombo) {
                                $updateData['is_combo'] = $csvIsCombo;
                            }
                            if (!empty($csvUnit) && $existingMenu->unit != $csvUnit) {
                                $updateData['unit'] = $csvUnit;
                            }
                            if ($csvReorderPoint > 0 && $existingMenu->reorder_point != $csvReorderPoint) {
                                $updateData['reorder_point'] = $csvReorderPoint;
                            }
                            if (!empty($csvBarcode) && $existingMenu->barcode != $csvBarcode) {
                                $updateData['barcode'] = $csvBarcode;
                            }
                            
                            if (!empty($updateData)) {
                                // Batch updates
                                $updateBatch[] = [
                                    'id' => $existingMenu->id,
                                    'data' => $updateData
                                ];
                                $updatedCount++;
                                
                                // Flush batch if it gets large
                                if (count($updateBatch) >= $batchSize) {
                                    foreach ($updateBatch as $update) {
                                        DB::table('menus')->where('id', $update['id'])->update($update['data']);
                                    }
                                    $updateBatch = [];
                                }
                            } else {
                                $skippedCount++;
                            }
                        } else {
                            // ===== CREATE NEW ITEM =====
                            $baseSlug = Str::slug($itemName, '-');
                            $slug = $generateUniqueSlug($baseSlug);
                            
                            $newUuid = $csvUuid && $isValidUuid($csvUuid) ? $csvUuid : $generateUuid();
                            
                            // Batch inserts
                            $insertBatch[] = [
                                'name' => $itemName,
                                'category_id' => ($csvCategoryId > 0) ? $csvCategoryId : $defaultCategoryId,
                                'item_code' => $csvSku ?: null,
                                'current_quantity' => $csvCurrentQuantity ?: 0,
                                'quantity' => $csvQuantity ?: 0,
                                'price' => $csvPrice,
                                'grab_price' => $csvGrabPrice,
                                'panda_price' => $csvPandaPrice,
                                'food_cost' => $csvFoodCost,
                                'department' => $defaultDepartmentId,
                                'servings' => $csvServings ?: 1,
                                'is_combo' => $csvIsCombo ?: 0,
                                'reorder_point' => $csvReorderPoint ?: 0,
                                'unit' => $csvUnit ?: 'pc/s',
                                'store_code' => $storeCode,
                                'branch_code' => $branchCode,
                                'branch' => $userBranch,
                                'branch_id' => $branchId,
                                'barcode' => $csvBarcode ?: null,
                                'slug' => $slug,
                                'is_hidden' => 0,
                                'is_discount' => 1,
                                'is_deleted' => 0,
                                'is_sync' => 0,
                                'is_uuid' => $newUuid,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            $createdCount++;
                            
                            // Flush batch if it gets large
                            if (count($insertBatch) >= $batchSize) {
                                try {
                                    DB::table('menus')->insert($insertBatch);
                                } catch (\Exception $e) {
                                    \Log::warning("Batch insert error", ['error' => $e->getMessage()]);
                                }
                                $insertBatch = [];
                            }
                        }
                    }

                } catch (\Exception $e) {
                    $skippedCount++;
                    \Log::error("Menu CSV error at row {$row}", ['error' => $e->getMessage()]);
                }
            }
            
            // ===== FLUSH REMAINING BATCHES =====
            if (!empty($insertBatch)) {
                try {
                    DB::table('menus')->insert($insertBatch);
                } catch (\Exception $e) {
                    \Log::warning("Final batch insert error", ['error' => $e->getMessage()]);
                }
            }
            
            if (!empty($updateBatch)) {
                foreach ($updateBatch as $update) {
                    DB::table('menus')->where('id', $update['id'])->update($update['data']);
                }
            }

            fclose($handle);

            // Commit transaction before returning success
            DB::commit();

            // Summary
            $summary = [
                'created' => $createdCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'duplicate_uuid_skipped' => $duplicateUuidSkippedCount,
                'duplicate_name_skipped' => $duplicateNameSkippedCount,
                'error_count' => count($errors),
            ];
            
            \Log::info('Menu CSV Import Complete', $summary);

            // Prepare message
            $messageParts = [];
            if ($createdCount > 0) $messageParts[] = "{$createdCount} created";
            if ($updatedCount > 0) $messageParts[] = "{$updatedCount} updated";
            if ($skippedCount > 0) $messageParts[] = "{$skippedCount} skipped";
            if ($duplicateUuidSkippedCount > 0) $messageParts[] = "{$duplicateUuidSkippedCount} UUID conflicts";
            if ($duplicateNameSkippedCount > 0) $messageParts[] = "{$duplicateNameSkippedCount} duplicate names";
            
            $message = !empty($messageParts) ? "Import completed: " . implode(', ', $messageParts) : "No items were processed.";
            
            if ($importToAllBranches) {
                $message .= " (Imported to all branches)";
            }

            $sessionData = ['message' => $message];
            if (!empty($errors)) {
                $errorMessage = count($errors) > 3 
                    ? implode(' | ', array_slice($errors, 0, 3)) . '... and ' . (count($errors) - 3) . ' more suggestions'
                    : implode(' | ', $errors);
                $sessionData['warning'] = $errorMessage;
            }

            // Return JSON for AJAX requests, redirect for regular requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'warning' => $sessionData['warning'] ?? null,
                    'summary' => $summary
                ]);
            }
            
            return redirect()->route('menu.index')->with($sessionData);

        } catch (\Exception $e) {
            // Rollback transaction if any error occurs
            DB::rollBack();
            
            \Log::error('Menu CSV Import Failed', ['error' => $e->getMessage()]);
            
            $errorMsg = 'Import encountered an issue. Please check your CSV file format and try again. If the problem persists, contact support.';
            
            // Return JSON for AJAX requests, redirect for regular requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'error' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->route('menu.index')->with('warning', $errorMsg);
        }
    }
    /* end ken 250623001 updated: CSV Import Performance Optimization - 02-27-2026 */
    // 250623001 - ken - 2/2/26 - Download menu import template CSV
    public function downloadMenuTemplate(Request $request, $branch = null): StreamedResponse
    {
        $branchId = $branch ?: null;

        //$fileName = 'menu_import_template_' . date('Y-m-d') . '_branch_' . $branchId . '.csv';
        $fileName = 'menu_import_template_' . date('Y-m-d');

        if ($branchId) {
            $fileName .= '_branch_' . $branchId;
        } else {
            $fileName .= '_all_branches';
        }

        $fileName .= '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
            'X-Frame-Options' => 'SAMEORIGIN',
        ];

        $callback = function () use ($branchId) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // CSV headers - MATCHING YOUR SCREENSHOT ORDER
            fputcsv($handle, [
                //'id',               // 6 - as in your screenshot
                'name',             // 7 - as in your screenshot
                'sku',      // 8 - as in your screenshot
                'current_quantity', // 9
                'quantity',         // 10
                'price',            // 11
                'grab_price',       // 12
                'panda_price',      // 13
               // 'image',            // 14
                //'slug',           // 15
                //'info',           // 16
                'food_cost',        // 17
                //'department',       // 18
                //'branch_id',      // 19
                //'created_at',     // 20
                //'updated_at',     // 21
                'servings',         // 22
                'is_combo',         // 23
                'unit',             // 24
                //'is_discount',    // 25
                //'is_hidden',      // 26
                'reorder_point',    // 27
                //'is_deleted',     // 28
                'bar_code',   
                'is_uuid',          // 1 - as in your screenshot
                //'client_token',   // 2 - as in your screenshot (THIS WAS WRONG)
                //'is_sync',        // 3 - as in your screenshot
                //'branch_code',    // 4 - as in your screenshot
                //'local_id',       // 5 - as in your screenshot     
            ]);
            
            // Get a few real examples from the database
            // $exampleMenus = DB::table('menus')
            //     ->limit(5)
            //     ->orderBy('id', 'asc')
            //     ->get();

            $exampleMenus = Menu::with('category')
            ->where('store_code', Auth::user()->store_code)
            ->when($branchId !== null, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where('is_deleted', 0)->orderBy('name', 'asc')->get();
            
            // Add example data
            if ($exampleMenus->count() > 0) {
                foreach ($exampleMenus as $menu) {
                    fputcsv($handle, [
                        //$menu->id ?? '',              // 6
                        $menu->name ?? '',            // 7
                        $menu->item_code ?? '',             // 8
                        $menu->current_quantity ?? 0, // 9
                        $menu->quantity ?? 0,         // 10
                        $menu->price ?? 0,            // 11
                        $menu->grab_price ?? 0,       // 12
                        $menu->panda_price ?? 0,      // 13
                       // $menu->image ?? '',           // 14
                        //$menu->slug ?? '',           // 15
                        //$menu->info ?? '',           // 16
                        $menu->food_cost ?? 0,        // 17
                        //$menu->department ?? 0,       // 18
                        //$menu->branch_id ?? 0,       // 19
                        //$menu->created_at ?? '',     // 20
                        //$menu->updated_at ?? '',     // 21
                        $menu->servings ?? 1,         // 22
                        $menu->is_combo ?? 0,         // 23
                        $menu->unit ?? '',            // 24
                        //$menu->is_discount ?? 0,     // 25
                        //$menu->is_hidden ?? 0,       // 26
                        $menu->reorder_point ?? 0,    // 27
                        //$menu->is_deleted ?? 0,      // 28
                        $menu->barcode ?? '', 
                        $menu->is_uuid ?? '',         // 1
                        //$menu->client_token ?? '',   // 2
                        //$menu->is_sync ?? 0,         // 3
                        //$menu->branch_code ?? '',    // 4
                        //$menu->local_id ?? '',       // 5    
                    ]);
                }
            } else {
                // Fallback examples if no menus exist
                fputcsv($handle, [
                    '',                   // id - leave empty for new record
                    'Chicken Adobo',      // name
                    1,                    // category_id
                    25,                   // current_quantity
                    25,                   // quantity
                    120.00,               // price
                    150.00,               // grab_price
                    140.00,               // panda_price
                    'images/menu/chicken-adobo.jpg', // image
                    //'chicken-adobo',    // slug
                    //'Traditional Filipino chicken adobo', // info
                    45.50,                // food_cost
                    1,                    // department
                    //1,                  // branch_id
                    //date('Y-m-d H:i:s'), // created_at
                    //date('Y-m-d H:i:s'), // updated_at
                    1,                    // servings
                    0,                    // is_combo
                    'plate',              // unit
                    //0,                  // is_discount
                    //0,                  // is_hidden
                    10,                   // reorder_point
                    //0,                  // is_deleted
                    '',                   // bar_code
                    '',                   // is_uuid - leave empty for auto-generation
                    //'',                 // client_token
                    //0,                  // is_sync
                    //'DEFAULT',          // branch_code
                    //'',                 // local_id                    
                ]);
            }
            
            // ---------- DEFAULT CATEGORY FOR TEMPLATE ----------
            $defaultCategoryId = DB::table('menu_categories')
                ->where('is_deleted', 0)
                ->value('id') ?? 1;
            
            // Add instructions
           /// fputcsv($handle, ['']); // Empty row
           // fputcsv($handle, ['INSTRUCTIONS FOR COMPLETE DATA IMPORT:']);
           // fputcsv($handle, ['1. Required fields: name, category_id, price']);
           // fputcsv($handle, ['2. Optional fields: all others - leave blank for defaults']);
           // fputcsv($handle, ['3. If id is provided and exists in database, that record will be UPDATED']);
           // fputcsv($handle, ['4. If no id is provided, a new record will be CREATED']);
           // fputcsv($handle, ['5. is_uuid: Auto-generated if empty. If you want to add quantity, remove the is_uuid']);
           // fputcsv($handle, ['6. client_token: Auto-generated if empty']);
          //  fputcsv($handle, ['7. slug: Auto-generated from name if empty']);
           // fputcsv($handle, ['8. Default category_id: ' . ($defaultCategoryId ?? 1)]);
           // fputcsv($handle, ['9. Export first to see complete data structure with values']);
           // fputcsv($handle, ['10. Column order must match exactly']);
            
           // fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
    // End of ken added Export, Import and downloadtemplate csv menus to CSV function  12-13-2025


    // ken added new method para sa Generate ng brand_code 7-29-30
    public function getBrands(request $request)
    {
        $categoryId = $request->input('categoryId');

        $brands = DB::table('brands')
            ->where('store_code', Auth::user()->store_code)
            //->where('category_id', $categoryId)
            ->get();

        return response()->json($brands);
    }
    public function genBrandCode()
    {
        $counter = 100001;

        $groups = Menu::join('menu_categories', 'menus.category_id', '=', 'menu_categories.id')
            ->join('brands', 'menus.brand_id', '=', 'brands.id')
            ->select(
                DB::raw('LOWER(TRIM(menus.name)) as menu_name'),
                DB::raw('LOWER(TRIM(menu_categories.name)) as category_name'),
                DB::raw('LOWER(TRIM(brands.name)) as brand_name')
            )
            ->groupBy(
                DB::raw('LOWER(TRIM(menus.name))'),
                DB::raw('LOWER(TRIM(menu_categories.name))'),
                DB::raw('LOWER(TRIM(brands.name))')
            )
            ->orderBy('menu_name')
            ->get();

        foreach ($groups as $group) {

            Menu::join('menu_categories', 'menus.category_id', '=', 'menu_categories.id')
                ->join('brands', 'menus.brand_id', '=', 'brands.id')
                ->whereRaw('LOWER(TRIM(menus.name)) = ?', [$group->menu_name])
                ->whereRaw('LOWER(TRIM(menu_categories.name)) = ?', [$group->category_name])
                ->whereRaw('LOWER(TRIM(brands.name)) = ?', [$group->brand_name])
                ->update([
                    'menus.brand_code' => (string) $counter
                ]);

            $counter++;
        }

        return back()->with('success', 'Brand codes generated successfully.');
    }
    // end of ken added new method para sa Generate ng brand_code 7-29-30
}