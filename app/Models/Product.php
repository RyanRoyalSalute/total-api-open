<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Define the table name if it's not the plural form of the model
    protected $table = 'products';

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'created_by',
        'updated_by',
        'store_id',
        'product_name',
        'product_image',
        'product_spec',
        'product_costs',
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
    ];
}
