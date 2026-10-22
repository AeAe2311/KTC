<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SawdustController extends Controller
{
    public function index()
    {
        $menus = $this->sawdustMenus()
            ->select('id', 'name', 'current_quantity', 'unit')
            ->orderBy('name')
            ->get();

        // Get distinct units for the dropdown
        $units = $this->sawdustMenus()
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->distinct()
            ->pluck('unit')
            ->sort()
            ->values();

        $logs = DB::table('sawdust_logs')
            ->where('store_code', Auth::user()->store_code)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('user.inventory.sawdust.index', compact('menus', 'logs', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'quantity' => ['required', 'numeric', 'gt:0', 'regex:/^\d{1,3}(\.\d{1,3})?$/'],
            'unit' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated) {
            $menu = $this->sawdustMenus()
                ->whereKey($validated['menu_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $quantityBefore = (string) $menu->current_quantity;
            $quantityAdded = (string) $validated['quantity'];
            $quantityAfter = (string) BigDecimal::of($quantityBefore)
                ->plus($quantityAdded)
                ->toScale(20);

            $menu->update([
                'quantity' => $quantityAfter,
                'current_quantity' => $quantityAfter,
                'unit' => $validated['unit'],
            ]);

            DB::table('sawdust_logs')->insert([
                'menu_id' => $menu->id,
                'item_name' => $menu->name,
                'quantity_added' => $quantityAdded,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'unit' => $validated['unit'],
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'store_code' => Auth::user()->store_code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('sawdust.index')
            ->with('success', 'Sawdust quantity added successfully.');
    }

    private function sawdustMenus()
    {
        return Menu::query()
            ->where('is_deleted', 0)
            ->where('store_code', Auth::user()->store_code)
            ->whereRaw('LOWER(name) LIKE ?', ['%sawdust%']);
    }
}