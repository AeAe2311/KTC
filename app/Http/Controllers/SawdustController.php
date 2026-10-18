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

        $logs = DB::table('sawdust_logs')
            ->where('store_code', Auth::user()->store_code)
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return view('user.inventory.sawdust.index', compact('menus', 'logs'));
    }

    public function add()
    {
        $menus = $this->sawdustMenus()
            ->select('id', 'name', 'current_quantity', 'unit')
            ->orderBy('name')
            ->get();

        return view('user.inventory.sawdust.add', compact('menus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'quantity' => ['required', 'numeric', 'gt:0', 'regex:/^\d{1,3}(\.\d{1,3})?$/'],
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
            ]);

            DB::table('sawdust_logs')->insert([
                'menu_id' => $menu->id,
                'item_name' => $menu->name,
                'quantity_added' => $quantityAdded,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
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