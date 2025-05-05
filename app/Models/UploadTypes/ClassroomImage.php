<?php

namespace App\Models\UploadTypes;

use Carbon\Carbon;

class ClassroomImage extends BaseUploadType
{
    public function __construct()
    {
        parent::__construct(
            baseFolder: 'classroom',
            table: 'classrooms',
            column: 'classroom_images',
            identifierField: 'id',
            defaultValue: null,
            maxSizeMb: 5,
            fileNameFormat: function (string $type, string $identifier, string $extension) {
                return "classroom_{$identifier}_" . Carbon::now()->timestamp . ".{$extension}";
            },
            appendToArray: true,
            maxArraySize: 5,
            allowUploadWithoutRecord: true
        );
    }
}