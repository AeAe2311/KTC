<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_movement';

    protected $fillable = [
        'type',
        'ordered_id',
        'void_id',
        'menu_id',
        'trans_id',
        'trans_number',
        'si_number',
        'reference',
        'detail',
        'unit', //ken added new column July 12, 2026
        'previous_qty',
        'qty_in',
        'qty_out',
        'remaining',
        'sold_price',
        'cost',
        'branch_code',
        'store_code',
        'is_uuid',
        'is_sync',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'ordered_id');
    }
}