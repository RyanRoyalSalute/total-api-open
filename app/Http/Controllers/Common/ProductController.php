<?php

namespace App\Http\Controllers\Common;

use App\Models\Product;
use App\Http\Controllers\Controller; 

class ProductController extends Controller
{
    /**
     * Get product details based on the material_id.
     *
     * @param  int  $material_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductDetail($material_id)
    {
        $product = Product::find($material_id);

        // Check if the product exists
        if ($product) {
            return apiResponse(2000, $product, 'Product found.');
        } else {
            return apiResponse(404, null, 'Product not found.', 404);
        }
    }
}
