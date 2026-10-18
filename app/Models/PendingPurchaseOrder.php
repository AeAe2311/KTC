<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingPurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'pending_purchase_orders';

    protected $fillable = [
        'supplier_id',
        'sales_invoice',
        'order_date',
        'reference_number',
        'items_data',
        'user_id',
        'store_code',
        'total_amount',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'remarks'
    ];

    protected $casts = [
        'items_data' => 'array',
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}