<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'created_by',
        'updated_by',
        'shop_brand_id',
        'teacher_name',
        'teacher_avatar',
        'teacher_description',
        'teacher_portfolio',
        'hourly_rate',
        'active',
        'sort',
        'pinned',
    ];

    protected $casts = [
        'teacher_portfolio' => 'array', // Cast JSON to array
        'hourly_rate' => 'decimal:2',
        'active' => 'integer',
        'sort' => 'integer',
        'pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function shopBrand()
    {
        return $this->belongsTo(ShopBrand::class, 'shop_brand_id');
    }
}