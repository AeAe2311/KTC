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

    // 250623001 - ken - 2/2/26 - update start 
    public function index() {
        $user = Auth::user();
        $branchFilter = request('branchFilter');
        $search = request('search');
        $categoryFilter = request('searchSelect', 'All');
        $stockFilter = request('stockFilter', 'all');
        
        $comboMenus = Menu::where('is_combo', 1)->where('store_code', $user->store_code)->where('is_deleted', 0)->get();     // 250623001 - ken - 1/21/26 - update
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
            ->where(function($query) use ($user) {
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
            })
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
        $page = 1;
        $category = $categoryFilter;
        
        // Check for required data and prepare toast notifications
        $toastMessages = $this->checkRequiredData();
        
        // FIX: Add 'groupedMenus', 'search', and 'branchFilter' to compact()
        return view('user.cook.menu-preparation', compact(
            'groupedMenus',  // Add this line
            'menuCount', 
            'page', 
            'search', 
            'items',
            'categories',
            'category',
            'depts',
            'catVal',
            'branches',
            'branchFilter',  // Add this line
            'stockFilter',   // Add this line
            'toastMessages'  // Add toast messages
        ));
    }

    public function paginate($page) {
        $user = Auth::user();
        $branchFilter = request('branchFilter');
        $search = request('search');
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
            ->where(function($query) use ($user) {
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
            })
            ->orderBy('name', 'asc')
            ->get();
        
        // Get menus with user-specific filtering
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
        
        // 250623001 ken - 1/22/2026 - update - Apply branch filter from dropdown (after store_code filter)
        if (!empty($branchFilter)) {
            $menuQuery = $menuQuery->where('branch_id', $branchFilter);
        }
        
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
        
        // FIX: Add 'groupedMenus', 'search', and 'branchFilter' to compact()
        return view('user.cook.menu-preparation', compact(
            'groupedMenus',  // Add this line
            'menuCount', 
            'page', 
            'search', 
            'items',
            'categories',
            'category',
            'depts',
            'catVal',
            'branches',
            'branchFilter'  // Add this line
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
            ->where(function($query) use ($user) {
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
            })
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
        
        // FIX: Add 'groupedMenus', 'search', and 'branchFilter' to compact()
        return view('user.cook.menu-preparation', compact(
            'groupedMenus',  // Add this line
            'menuCount', 
            'page', 
            'search', 
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
            ->where(function($query) use ($user) {
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
            })
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
    // 250623001 - ken - 2/2/26 - update end
    public function add() {
        
        $categories = DB::table('menu_categories')
        ->where('store_code', Auth::user()->store_code)
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

        return view('user.cook.menu-preparation-add', compact('categories', 'items', 'menus', 'depts','branches'));
    }

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

    public function store(Request $request) {
        $name = strtolower($request->name);
        $name = ucwords($name);
        $category_id = $request->category_id;
        $price = $request->price;
        $grab = $request->grab;
        $panda = $request->panda;
        $image = $request->image;
        $reorder_point = $request->reorder_point;
        $servings = $request->servings;
        $Totalcounter = $request->counter;
        $foodCost = $request->foodCost;
        $department = $request->department;
        $branch_option = $request->branch_option;
        $info = $request->info;
        $barcode = $request->barcode;
        $itemcode = $request->item_code;
        
        $storeCode = Auth::user()->store_code ?? '';

        // Determine which branches to create menu items for
        $branchesToCreate = [];
        
        // Check if branches are selected via checkboxes
        $selectedBranches = $request->branches ?? [];
        if (!empty($selectedBranches)) {
            foreach ($selectedBranches as $branchId) {
                if ($branchId != 1) {
                    $branchesToCreate[] = $branchId;
                }
            }
        } else {
            // Fallback to branch_option logic if needed
            switch ($branch_option) {
                case 'single':
                    // Single branch selected
                    $singleBranch = $request->branch;
                    if ($singleBranch && $singleBranch != 1) {
                        $branchesToCreate[] = $singleBranch;
                    }
                    break;
                    
                case 'all':
                    // All branches (except branch id 1)
                    $allBranches = DB::table('settings')
                        ->where('store_code', $storeCode)
                        ->where('is_deleted', 0)
                        ->where('id', '!=', 1)
                        ->get();
                        
                    foreach ($allBranches as $branch) {
                        $branchesToCreate[] = $branch->id;
                    }
                    break;
                    
                case 'multiple':
                    // Multiple branches selected
                    $selectedBranches = $request->branches ?? [];
                    foreach ($selectedBranches as $branchId) {
                        if ($branchId != 1) {
                            $branchesToCreate[] = $branchId;
                        }
                    }
                    break;
            }
        }
        
        // Validate that at least one branch is selected
        if (empty($branchesToCreate)) {
            return redirect()->back()->with('error', 'Please select at least one branch.')->withInput();
        }

        if ($request->combo != null) {
            $combo = 1;
        } else {
            $combo = 0;
        }
        if ($request->hidden != null) {
            $hidden = 1;
        } else {
            $hidden = 0;
        }
        if ($request->isDiscount != null) {
            $isDiscount = 1;
        } else {
            $isDiscount = 0;
        }
        
        $slug = Str::slug($name, '-');
        $check_slug = DB::table('menus')->where('slug', $slug)->get();
        $x = 1;
        $nslug = $slug;
        while (count($check_slug) > 0) {
            $nslug = $slug . '-' . $x;
            $check_slug = DB::table('menus')->where('slug', $nslug)->get();
            $x++;
        }
        $slug = $nslug;
        
        $manager = new ImageManager(new Driver());
        $imagePath = null;
        
        if ($image != null) {
            $filename = $slug . '.' . $request->file('image')->getClientOriginalExtension();
            $image = $manager->read($request->image);
            $image->scale(width: 300);
            $path = "images/menu/";
            $imagePath = $path . $filename;
            $image->save(public_path('storage/' . $imagePath));
        }

        $request->validate([
            'name' => 'required',
            'price' => 'required',
        ]);

        // Arrays to track created items
        $createdMenuIds = [];
        $createdCount = 0;

        // Create menu item for each selected branch
        foreach ($branchesToCreate as $branchId) {
            // Get branch info
            $branchInfo = DB::table('settings')
                ->where('id', $branchId)
                ->first();
                
            if (!$branchInfo) {
                continue; // Skip if branch doesn't exist
            }
            
            $branchCode = $branchInfo->branch_code;
            
            // Check if item with same name already exists in this branch
            $existingItem = Menu::where('branch', $branchId)
                ->where('store_code', $storeCode)
                ->where('name', $name)
                ->first();
                
            if ($existingItem) {
                // Item already exists in this branch - skip or update based on your preference
                // For now, we'll skip and continue to next branch
                continue;
            }
            
            // Generate unique slug for this branch
            $branchSlug = $slug;
            $branchSlugCheck = DB::table('menus')->where('slug', $branchSlug)->get();
            $y = 1;
            while (count($branchSlugCheck) > 0) {
                $branchSlug = $slug . '-' . $y;
                $branchSlugCheck = DB::table('menus')->where('slug', $branchSlug)->get();
                $y++;
            }
            
            // Generate unique UUID for this item
            $uuid = $branchSlug;
            
            // Get branch-specific price or use default price
            $branchPrices = $request->prices ?? [];
            $itemPrice = isset($branchPrices[$branchId]) && !empty($branchPrices[$branchId]) 
                ? $branchPrices[$branchId] 
                : $price;
            
            $item = new Menu();
            $item->name = $name;
            $item->category_id = $category_id;
            $item->price = $itemPrice;
            $item->grab_price = $grab ?? 0;
            $item->panda_price = $panda ?? 0;
            $item->quantity = 0;
            $item->current_quantity = 0;
            $item->reorder_point = $reorder_point;
            $item->servings = $servings;
            $item->is_combo = $combo;
            $item->info = $info;
            $item->is_hidden = $hidden;
            $item->food_cost = $foodCost;
            $item->is_discount = $isDiscount;
            $item->department = $department;
            $item->store_code = $storeCode;
            $item->branch = $branchId;
            $item->branch_id = $branchId;
            $item->barcode = $barcode;
            $item->item_code = $itemcode;
            $item->branch_code = $branchCode;
            
            if ($image != null) {
                $item->image = $imagePath;
            }
            $item->slug = $branchSlug;
            $item->is_uuid = $uuid;
            $item->save();
            
            $createdMenuIds[] = $item->id;
            $createdCount++;
            
            // Create ingredients for this menu item
            for ($counter = 1; $counter <= $Totalcounter; $counter++) {
                $iname = 'item' . $counter;
                $qname = 'quantity' . $counter;

                if ($request->$iname != null) {
                    $itemsArray = explode(",", $request->$iname);
                    $is_menu = $itemsArray[0];
                    $item_id = $itemsArray[1];

                    if ($request->combo != null) {
                        $invUnit = 'pc/s';
                    } else {
                        $invUnit = (DB::table('inventories')->where('id', $item_id)->first())->unit;
                    }

                    $ingr = new Ingredient();
                    $ingr->menu_id = $item->id;
                    $ingr->inventory_id = $item_id;
                    $ingr->quantity = $request->$qname;
                    $ingr->computed_quantity = $servings > 0 ? ($request->$qname / $servings) : 0;
                    $ingr->unit = $invUnit;
                    $ingr->is_menu = $is_menu;
                    $ingr->save();
                }
            }
        }

        // Prepare success message
        $message = "Successfully Added";
        if ($createdCount > 1) {
            $message .= " to {$createdCount} branches";
        }
        
        if ($createdCount == 0) {
            return redirect()->back()->with('error', 'Menu item already exists in all selected branches.')->withInput();
        }

        return redirect()->route('menu.index')->withInput()->with('message', $message);
    }
    // 250623001 ken - 2/2/26 - update start
    public function edit($slug) {
        // Get the main menu item
        $item = DB::table('menus')->where('slug', $slug)->first();
        
        $categories = DB::table('menu_categories')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)->orderBy('name', 'asc')->get();

        $depts = DB::table('departments')
            ->where('store_code', Auth::user()->store_code)
            ->orderBy('id', 'asc')->where('is_deleted', 0)->get();

        // Get all branches for the user's store
        $allBranches = DB::table('settings')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->orderBy('name', 'asc')
            ->get();

        // Get ALL menu items with the same name across different branches
        $menuItemsByBranch = DB::table('menus')
            ->where('name', $item->name)
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->get()
            ->keyBy('branch_id'); // Index by branch_id for easy access

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
            'menuItemsByBranch' // Pass the collection of menu items by branch
        ));
    }
    public function update(Request $request) {
        $oldSlug = $request->slug;
        $name = strtolower($request->name);
        $name = ucwords($name);
        $category_id = $request->category_id;
        $servings = $request->servings;
        $foodCost = $request->foodCost;
        $department = $request->department;
        $info = $request->info;
        $barcode = $request->barcode;
        $itemcode = $request->item_code;
        
        if ($request->combo != null) {
            $combo = 1;
        } else {
            $combo = 0;
        }
        
        if ($request->hidden != null) {
            $hidden = 1;
        } else {
            $hidden = 0;
        }

        if ($request->isDiscount != null) {
            $isDiscount = 1;
        } else {
            $isDiscount = 0;
        }
        
        // Get the original menu item being edited
        $originalItem = DB::table('menus')->where('slug', $oldSlug)->first();
        $menuID = $originalItem->id;
        $originalBranchId = $originalItem->branch_id;
        $originalName = $originalItem->name;

        // Process branch-specific data - ONLY for checked branches
        $allBranches = DB::table('settings')
            ->where('store_code', Auth::user()->store_code)
            ->where('is_deleted', 0)
            ->get();

        foreach ($allBranches as $branch) {
            // Check if this branch is marked for update
            $updateKey = "update_branch_{$branch->id}";
            // Ensure the original branch item is always processed (so renames update the original record)
            $shouldUpdate = false;
            if ($branch->id == $originalBranchId) {
                $shouldUpdate = true;
            } elseif ($request->has($updateKey) && $request->$updateKey == 1) {
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                // Only process if checkbox is checked
                $stockKey = "stock_{$branch->id}";
                $priceKey = "price_{$branch->id}";
                $reorderKey = "reorder_{$branch->id}";
                
                $stock = $request->$stockKey ?? 0;
                $price = $request->$priceKey ?? 0;
                $reorder = $request->$reorderKey ?? 0;
                
                // Get branch-specific barcode and item code if they exist
                $branchBarcodeKey = "barcode_{$branch->id}";
                $branchItemCodeKey = "item_code_{$branch->id}";
                
                $branchBarcode = $request->has($branchBarcodeKey) ? $request->$branchBarcodeKey : $barcode;
                $branchItemCode = $request->has($branchItemCodeKey) ? $request->$branchItemCodeKey : $itemcode;
                
                // Check if a menu item exists for this branch with the ORIGINAL name (before edit)
                $existingMenuItem = DB::table('menus')
                    ->where('branch_id', $branch->id)
                    ->where('store_code', Auth::user()->store_code)
                    ->where('is_deleted', 0)
                    ->where(function($query) use ($originalName, $name, $menuID) {
                        // Check for item with original name OR if this is the exact item being edited
                        $query->where('name', $originalName)
                            ->orWhere('id', $menuID);
                    })
                    ->first();
                
                if ($existingMenuItem) {
                    // If this is the item from the original branch OR has the original name, update it
                    if ($existingMenuItem->id == $menuID || $existingMenuItem->branch_id == $branch->id) {
                        DB::table('menus')->where('id', $existingMenuItem->id)->update([
                            'name' => $name, // Update to new name
                            'category_id' => $category_id,
                            'price' => $price,
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
                            'barcode' => $branchBarcode,
                            'item_code' => $branchItemCode,
                            'slug' => Str::slug($name . '-' . $branch->id, '-'),
                            'updated_at' => now(),
                        ]);
                        
                        // Update menuID if this is the item for the original branch
                        if ($existingMenuItem->branch_id == $originalBranchId) {
                            $menuID = $existingMenuItem->id;
                        }
                    }
                } else {
                    // Create new branch menu item only if it doesn't exist
                    $slug = Str::slug($name . '-' . $branch->id, '-');
                    
                    $newMenuId = DB::table('menus')->insertGetId([
                            'name' => $name,
                            'category_id' => $category_id,
                            'price' => $price,
                            'grab_price' => $request->grab,
                            'panda_price' => $request->panda,
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
                            'branch_id' => $branch->id,
                            'barcode' => $branchBarcode,
                            'item_code' => $branchItemCode,
                            'branch_code' => $branch->branch_code,
                            'slug' => $slug,
                            'is_uuid' => Str::uuid(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    
                    // If this is the branch we're editing (based on original item's branch_id)
                    if ($branch->id == $originalBranchId) {
                        $menuID = $newMenuId;
                    }
                }
            }
        }

        // Update ingredients for the menu item
        $Totalcounter = $request->counter;
        DB::table('ingredients')->where('menu_id', $menuID)->delete();

        for ($counter = 1; $counter <= $Totalcounter; $counter++) {
            $iname = 'item' . $counter;
            $qname = 'quantity' . $counter;

            if ($request->$iname != null) {
                $itemsArray = explode(",", $request->$iname);
                $is_menu = $itemsArray[0];
                $item_id = $itemsArray[1];

                if ($combo == 1) {
                    $invUnit = 'pc/s';
                } else {
                    $invUnit = (DB::table('inventories')->where('id', $item_id)->first())->unit;
                }

                $ingr = new Ingredient();
                $ingr->menu_id = $menuID;
                $ingr->inventory_id = $item_id;
                $ingr->quantity = $request->$qname;
                $ingr->computed_quantity = $request->$qname / $servings;
                $ingr->unit = $invUnit;
                $ingr->is_menu = $is_menu;
                $ingr->save();
            }
        }

        return redirect()->route('menu.index')->withInput()->with('message', 'Successfully Updated');
    }

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

        $result = array(
            'name' => $name,
            'ingredients' => $ingredients,
            'servings' => $servings,
        );

        echo json_encode($result);
    }

    public function computeqty(Request $request) {
        $slug = $request->slug;
        $qty = $request->qty;

        $menu = DB::table('menus')->where('slug', $slug)->first();
        $name = $menu->name;
        $id = $menu->id;

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
        );

        echo json_encode($result);
    }

    public function changeqty(Request $request) {
        $slug = $request->slug;
        $quantity = $request->servings;
        $Totalcounter = $request->counter;
        $cur_quantity = (DB::table('menus')->where('slug', $slug)->first())->quantity;
        $uqty = $cur_quantity + $quantity;

        $menu = DB::table('menus')->where('slug', $slug)->first();
        $menuID = $menu->id;
        $menuName = $menu->name;
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
                        $skippedCount++;
                        $errors[] = "Suggestion: Row {$row} was skipped because the item name was missing. Please add a name to import this item.";
                        continue;
                    }

                    // Clean and format the name
                    $itemName = trim($rowData['name']);
                    $csvUuid = $str($rowData['is_uuid']);
                    $hasUuidInCsv = $csvUuid && $isValidUuid($csvUuid);

                    // Get values from CSV
                    $csvSku = $str($rowData['sku']);
                    $csvCurrentQuantity = $float($rowData['current_quantity']);
                    $csvQuantity = $float($rowData['quantity']);
                    $csvPrice = $rowData['price'] !== '' && $rowData['price'] !== null ? (float)$rowData['price'] : null;
                    $csvGrabPrice = $rowData['grab_price'] !== '' && $rowData['grab_price'] !== null ? (float)$rowData['grab_price'] : null;
                    $csvPandaPrice = $rowData['panda_price'] !== '' && $rowData['panda_price'] !== null ? (float)$rowData['panda_price'] : null;
                    $csvFoodCost = $rowData['food_cost'] !== '' && $rowData['food_cost'] !== null ? (float)$rowData['food_cost'] : null;
                    $csvServings = $rowData['servings'] !== '' && $rowData['servings'] !== null ? (int)$rowData['servings'] : null;
                    $csvIsCombo = $rowData['is_combo'] !== '' && $rowData['is_combo'] !== null ? (int)$rowData['is_combo'] : null;
                    $csvReorderPoint = $rowData['reorder_point'] !== '' && $rowData['reorder_point'] !== null ? (int)$rowData['reorder_point'] : null;
                    $csvUnit = $str($rowData['unit']);
                    $csvBarcode = $str($rowData['bar_code']);
                    $csvCategoryId = isset($rowData['category_id']) ? $int($rowData['category_id']) : 0;

                    // Check for duplicate UUID in CSV
                    if ($hasUuidInCsv && in_array($csvUuid, $csvUuids)) {
                        $duplicateUuidSkippedCount++;
                        $errors[] = "Info: Row {$row} was skipped due to duplicate UUID in your CSV file. Each item should have a unique identifier.";
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
                            $duplicateNameSkippedCount++;
                            $errors[] = "Info: Row {$row} was skipped because '{$itemName}' appears multiple times in your CSV file for the same branch. Consider using unique names or UUIDs.";
                            continue 2; // Skip to next row
                        }
                        $processedItems[$itemKey] = true;

                        // ========== FIND EXISTING MENU IN CURRENT BRANCH ==========
                        $existingMenu = null;

                        // Priority 1: If CSV has valid UUID, find by UUID first
                        if ($hasUuidInCsv) {
                            $existingMenu = Menu::where('branch', $userBranch)
                                ->where('store_code', $storeCode)
                                ->where('is_uuid', $csvUuid)
                                ->first();
                            
                            if ($existingMenu) {
                                \Log::info("Found existing item by UUID", [
                                    'row' => $row,
                                    'uuid' => $csvUuid,
                                    'menu_id' => $existingMenu->id,
                                    'name' => $existingMenu->name
                                ]);
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
                                
                                if ($existingMenu) {
                                    \Log::info("Found existing item by SKU", [
                                        'row' => $row,
                                        'sku' => $csvSku,
                                        'menu_id' => $existingMenu->id,
                                        'name' => $existingMenu->name
                                    ]);
                                }
                            }
                            
                            // Method 2: Match by barcode if provided and no SKU match
                            if (!$existingMenu && !empty($csvBarcode)) {
                                $existingMenu = Menu::where('branch', $userBranch)
                                    ->where('store_code', $storeCode)
                                    ->where('barcode', $csvBarcode)
                                    ->first();
                                
                                if ($existingMenu) {
                                    \Log::info("Found existing item by barcode", [
                                        'row' => $row,
                                        'barcode' => $csvBarcode,
                                        'menu_id' => $existingMenu->id,
                                        'name' => $existingMenu->name
                                    ]);
                                }
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
                                
                                if ($existingMenu) {
                                    \Log::info("Found existing item by name", [
                                        'row' => $row,
                                        'name' => $itemName,
                                        'menu_id' => $existingMenu->id
                                    ]);
                                }
                            }
                        }
                        
                        if ($existingMenu) {
                            // ========== UPDATE EXISTING ITEM ==========
                            $hasUpdates = false;
                            $updateData = [
                                'updated_at' => now(),
                            ];
                            
                            // sku: only update if CSV has non-empty value
                            if (!empty($csvSku) && $existingMenu->item_code != $csvSku) {
                                $updateData['item_code'] = $csvSku;
                                $hasUpdates = true;
                            }
                            
                            // current_quantity: only update if CSV > 0
                            if ($csvCurrentQuantity > 0) {
                                $updateData['current_quantity'] = DB::raw('current_quantity + ' . $csvCurrentQuantity);
                                $hasUpdates = true;
                            }
                            
                            // quantity: only update if CSV > 0
                            if ($csvQuantity > 0) {
                                $updateData['quantity'] = DB::raw('quantity + ' . $csvQuantity);
                                $hasUpdates = true;
                            }
                            
                            // price: only update if CSV > 0
                            if ($csvPrice > 0 && $existingMenu->price != $csvPrice) {
                                $updateData['price'] = $csvPrice;
                                $hasUpdates = true;
                            }
                            
                            // grab_price: only update if CSV > 0
                            if ($csvGrabPrice > 0 && $existingMenu->grab_price != $csvGrabPrice) {
                                $updateData['grab_price'] = $csvGrabPrice;
                                $hasUpdates = true;
                            }
                            
                            // panda_price: only update if CSV > 0
                            if ($csvPandaPrice > 0 && $existingMenu->panda_price != $csvPandaPrice) {
                                $updateData['panda_price'] = $csvPandaPrice;
                                $hasUpdates = true;
                            }
                            
                            // food_cost: only update if CSV > 0
                            if ($csvFoodCost > 0 && $existingMenu->food_cost != $csvFoodCost) {
                                $updateData['food_cost'] = $csvFoodCost;
                                $hasUpdates = true;
                            }
                            
                            // servings: only update if CSV > 0
                            if ($csvServings > 0 && $existingMenu->servings != $csvServings) {
                                $updateData['servings'] = $csvServings;
                                $hasUpdates = true;
                            }
                            
                            // is_combo: only update if CSV > 0
                            if ($csvIsCombo > 0 && $existingMenu->is_combo != $csvIsCombo) {
                                $updateData['is_combo'] = $csvIsCombo;
                                $hasUpdates = true;
                            }
                            
                            // unit: only update if CSV has non-empty value
                            if (!empty($csvUnit) && $existingMenu->unit != $csvUnit) {
                                $updateData['unit'] = $csvUnit;
                                $hasUpdates = true;
                            }
                            
                            // reorder_point: only update if CSV > 0
                            if ($csvReorderPoint > 0 && $existingMenu->reorder_point != $csvReorderPoint) {
                                $updateData['reorder_point'] = $csvReorderPoint;
                                $hasUpdates = true;
                            }
                            
                            // barcode: only update if CSV has non-empty value
                            if (!empty($csvBarcode) && $existingMenu->barcode != $csvBarcode) {
                                $updateData['barcode'] = $csvBarcode;
                                $hasUpdates = true;
                            }
                            
                            // category_id: only update if CSV has valid value
                            $csvCategoryIdForUpdate = isset($rowData['category_id']) ? $int($rowData['category_id']) : 0;
                            if ($csvCategoryIdForUpdate > 0 && DB::table('menu_categories')->where('id', $csvCategoryIdForUpdate)->where('store_code', $storeCode)->exists() && $existingMenu->category_id != $csvCategoryIdForUpdate) {
                                $updateData['category_id'] = $csvCategoryIdForUpdate;
                                $hasUpdates = true;
                            }
                            
                            // Handle UUID if CSV has one
                            if ($hasUuidInCsv) {
                                // If this is the same item (matched by UUID), no need to update
                                if ($existingMenu->is_uuid !== $csvUuid) {
                                    // Check if another item in this branch already has this UUID
                                    $uuidConflict = Menu::where('branch', $userBranch)
                                        ->where('store_code', $storeCode)
                                        ->where('is_uuid', $csvUuid)
                                        ->where('id', '!=', $existingMenu->id)
                                        ->exists();
                                    if ($uuidConflict) {
                                        $duplicateUuidSkippedCount++;
                                        $errors[] = "Warning: Row {$row} was skipped because the UUID '{$csvUuid}' is already used by another item in this branch. Use a different UUID or update the existing item directly.";
                                        continue 2; // Skip to next row
                                    }
                                    $updateData['is_uuid'] = $csvUuid;
                                    $hasUpdates = true;
                                }
                            }
                            
                            // Update the existing item if there are changes
                            if ($hasUpdates) {
                                try {
                                    $existingMenu->update($updateData);
                                    $updatedCount++;
                                    \Log::info("Updated existing item", [
                                        'row' => $row,
                                        'menu_id' => $existingMenu->id,
                                        'name' => $itemName,
                                        'branch' => $userBranch,
                                        'updates' => $updateData
                                    ]);
                                } catch (\Exception $updateError) {
                                    $skippedCount++;
                                    \Log::error("Failed to update menu item", [
                                        'row' => $row,
                                        'menu_id' => $existingMenu->id,
                                        'name' => $itemName,
                                        'error' => $updateError->getMessage(),
                                        'updates' => $updateData
                                    ]);
                                    continue;
                                }
                            } else {
                                $skippedCount++;
                                \Log::info("No updates needed for existing item", [
                                    'row' => $row,
                                    'menu_id' => $existingMenu->id,
                                    'name' => $itemName
                                ]);
                            }
                            continue; // Move to next branch
                        }
                        
                        // ========== CREATE NEW ITEM ==========
                        // Generate slug
                        $baseSlug = Str::slug($itemName, '-');
                        $slug = $generateUniqueSlug($baseSlug);
                        
                        // Prepare menu data for new item
                        $menuData = [
                            'name' => $itemName,
                            'category_id' => ($csvCategoryId > 0 && DB::table('menu_categories')->where('id', $csvCategoryId)->where('store_code', $storeCode)->exists()) ? $csvCategoryId : $defaultCategoryId,
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
                            'created_at' => $rowData['created_at'] ?? now(),
                            'updated_at' => now(),
                        ];
                        
                        // Handle UUID for new item
                        if ($hasUuidInCsv) {
                            // Check if this UUID already exists in this branch
                            $uuidExists = Menu::where('branch', $userBranch)
                                ->where('store_code', $storeCode)
                                ->where('is_uuid', $csvUuid)
                                ->exists();
                            if ($uuidExists) {
                                $duplicateUuidSkippedCount++;
                                $errors[] = "Warning: Row {$row} was skipped because the UUID '{$csvUuid}' is already used in this branch. Consider using a different UUID or check if this item already exists.";
                                continue 2; // Skip to next row
                            }
                            $menuData['is_uuid'] = $csvUuid;
                        } else {
                            // Generate new UUID and ensure it's unique in this branch
                            $newUuid = $generateUuid();
                            while (Menu::where('branch', $userBranch)
                                ->where('store_code', $storeCode)
                                ->where('is_uuid', $newUuid)
                                ->exists()) {
                                $newUuid = $generateUuid();
                            }
                            $menuData['is_uuid'] = $newUuid;
                        }
                        
                        // Create new menu item
                        try {
                            $newMenu = Menu::create($menuData);
                            $createdCount++;
                            \Log::info("Created new item", [
                                'row' => $row,
                                'menu_id' => $newMenu->id,
                                'name' => $itemName,
                                'branch' => $userBranch,
                                'data' => $menuData
                            ]);
                        } catch (\Exception $createError) {
                            $skippedCount++;
                            \Log::error("Failed to create menu item", [
                                'row' => $row,
                                'name' => $itemName,
                                'error' => $createError->getMessage(),
                                'data' => $menuData
                            ]);
                            continue;
                        }
                    }

                } catch (\Exception $e) {
                    // Handle data issues gracefully - most problems can be resolved with default values
                    $errorMessage = $e->getMessage();
                    if (strpos($errorMessage, 'Integrity constraint violation') !== false ||
                        strpos($errorMessage, 'cannot be null') !== false) {
                        // This shouldn't happen with our default values, but if it does, skip the row
                        $skippedCount++;
                        \Log::warning("Menu CSV import skipped row {$row} due to null constraint", ['error' => $errorMessage]);
                        continue;
                    }
                    
                    // For other unexpected errors, skip the row but don't show error to user
                    $skippedCount++;
                    \Log::error("Menu CSV unexpected error at row {$row}", ['error' => $errorMessage]);
                }
            }

            fclose($handle);

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

            return redirect()->route('menu.index')->with($sessionData);

        } catch (\Exception $e) {
            \Log::error('Menu CSV Import Failed', ['error' => $e->getMessage()]);
            return redirect()->route('menu.index')->with('warning', 'Import encountered an issue. Please check your CSV file format and try again. If the problem persists, contact support.');
        }
    }
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
}
