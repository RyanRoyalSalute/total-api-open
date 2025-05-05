<?php

namespace App\Models\UploadTypes;

use Carbon\Carbon;

class CourseImage extends BaseUploadType
{
    public function __construct()
    {
        parent::__construct(
            baseFolder: 'courses',
            table: 'courses',
            column: 'course_images',
            identifierField: 'id',
            defaultValue: null,
            maxSizeMb: 10,
            fileNameFormat: function (string $type, string $identifier, string $extension) {
                return "course_{$identifier}_img_" . Carbon::now()->timestamp . ".{$extension}";
            },
            appendToArray: true,
            maxArraySize: 5
        );
    }
}