<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeOfPayment extends Model
{
    use HasFactory;

    protected $table = 'mode_of_payment';

    protected $fillable = [
        'name',
        'store_code',
        'branch_code',
        'slug',
        'is_deleted',
    ];
}
