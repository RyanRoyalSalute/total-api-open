<?php

namespace App\Models\UploadTypes;

class ProductImage extends BaseUploadType
{
    public function __construct()
    {
        parent::__construct(
            baseFolder: 'products/images',
            table: 'products',
            column: 'product_image',
            identifierField: 'id',
            defaultValue: null,
            maxSizeMb: 5,
            appendToArray: false
        );
    }
}