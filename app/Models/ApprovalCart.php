<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalCart extends Model
{
    use HasFactory;

    protected $table = 'approval_cart';

    protected $fillable = [
        'number',
        'total',
        'mode_of_payment',
        'amount',
        'fee',
        'service_charge',
        'sc_percent',
        'pwd',
        'customer_with_discount',
        'total_customer',
        'lime_discount',
        'ld_value',
        'payor_name',
        'payor_number',
        'lessVatDeduction',
        'vat',
        'vatSales',
        'vatExemptSales',
        'type',
        'is_vat',
        'table',
        'status',
        'order_status',
        'sales_type',
        'cashier',
        'staff_id',
        'slug',
        'print',
        'is_uuid',
        'is_sync',
        'is_app',
        'is_branch',
        'branch_code',
        'local_id',
        'store_code',
        'branch',
        'approved_by',
        'is_deleted',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'sc_percent' => 'decimal:2',
        'lessVatDeduction' => 'decimal:2',
        'vat' => 'decimal:2',
        'vatSales' => 'decimal:2',
        'vatExemptSales' => 'decimal:2',
        'is_vat' => 'boolean',
        'sales_type' => 'boolean',
        'print' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function cashierUser()
    {
        return $this->belongsTo(User::class, 'cashier');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
