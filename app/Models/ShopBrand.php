<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopBrand extends Model
{
    use HasFactory;

    protected $table = 'shop_brands'; // Define the table name

    protected $fillable = [
        'created_by',
        'updated_by',
        'brand_name',
        'brand_logo',
        'brand_background',
        'teacher_permission',
        'brand_code',
    ];
    
}
