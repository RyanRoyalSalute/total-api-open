<?php

namespace App\Models\UploadTypes;

class BrandBackground extends BaseUploadType
{
    public function __construct()
    {
        parent::__construct(
            baseFolder: 'resources',
            table: 'shop_brands',
            column: 'brand_background',
            identifierField: 'id',
            defaultValue: null,
            maxSizeMb: 15
        );
    }
}