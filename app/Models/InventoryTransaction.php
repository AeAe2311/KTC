<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inv_id',
        'type',
        'quantity_before',
        'quantity',
        'quantity_after',
        'amount',
        'category_id',
        'unit_price',
        'exp_type',
        'unit',
        'remarks',
        'user_id',
        'supplier_id',
        'is_paid',
        'is_uuid',
        'payment_class',
        'store_code',
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
