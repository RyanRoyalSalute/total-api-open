<?php

namespace App\Models\UploadTypes;

use Ramsey\Uuid\Uuid;

class BrandLogo extends BaseUploadType
{
    public function __construct()
    {
        parent::__construct(
            baseFolder: 'resources/',
            table: 'shop_brands',
            column: 'brand_logo',
            identifierField: 'id',
            defaultValue: null,
            maxSizeMb: 5,
            fileNameFormat: function (string $type, string $identifier, string $extension) {
                return "logo_{$identifier}_" . Uuid::uuid4()->toString() . ".{$extension}";
            }
        );
    }
}