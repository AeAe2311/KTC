<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCartTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inv_id',
        'type',
        'quantity_before',
        'quantity',
        'quantity_after',
        'amount',
        'remarks',
        'user_id',
        'is_paid',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inv()
    {
        return $this->belongsTo(Inventory::class, 'inv_id');
    }

    public function ExpensesCategory()
    {
        return $this->belongsTo(ExpensesCategory::class, 'category_id');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
