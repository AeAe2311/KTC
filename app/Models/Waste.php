<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    protected $fillable = [
        'on',
        'iid',
        'quantity',
        'cost',
        'waste_remarks',
        'store_code',
        'created_at'
    ];

    use HasFactory;

    public function raw()
    {
        return $this->belongsTo(Inventory::class, 'iid');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'iid');
    }
}
