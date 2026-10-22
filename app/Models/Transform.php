<?php

namespace App\Models;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transform extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'transform_no',
        'approval_reference', //ken added new column July 12, 2026
        'slug',
        'date',
        'remarks',
        'created_by',
        'approved_by',
        'status',
        'source_inventory_id',
        'source_quantity',
        'source_unit',
        'waste_quantity',
        'waste_unit',
        'waste_description',
    ];

    public function source()
    {
        return $this->belongsTo(Menu::class, 'source_inventory_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(TransformItem::class, 'transform_id');
    }
}
