<?php

namespace App\Models\UploadTypes;

class UserAvatar extends BaseUploadType
{
    public function __construct()
    {
        parent::__construct(
            baseFolder: 'users/avatars',
            table: 'users',
            column: 'avatar',
            identifierField: 'mobile_phone',
            defaultValue: 'default',
            maxSizeMb: 5
        );
    }
}