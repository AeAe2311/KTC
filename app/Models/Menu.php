<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'current_quantity',
        'quantity',
        'price',
        'image',
        'servings',
        'is_combo',
        'unit',
        'slug',
        'is_hidden',
        'is_discount',
        'is_deleted',
        'is_sync',
        'store_code',     
        'branch_code',    
        'branch',         // Added missing field
        'branch_id',         
        'barcode',        
        'reorder_point',  
        'food_cost',      
        'grab_price',     
        'panda_price',    
        'department',     
        'is_uuid', 
        'item_code',
    ];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

}
