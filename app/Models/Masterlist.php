<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Masterlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'slug'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}
