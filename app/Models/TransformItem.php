<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Menu;
use App\Models\Inventory;

class TransformItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transform_id',
        'inventory_id',
        'menu_id',
        'quantity',
        'unit',
    ];

    public function transform()
    {
        return $this->belongsTo(Transform::class, 'transform_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
