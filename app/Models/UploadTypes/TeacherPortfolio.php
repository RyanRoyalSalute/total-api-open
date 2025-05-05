<?php

namespace App\Models\UploadTypes;

use Carbon\Carbon;

class TeacherPortfolio extends BaseUploadType
{
    public function __construct()
    {
        parent::__construct(
            baseFolder: 'teachers/portfolio',
            table: 'teachers',
            column: 'teacher_portfolio',
            identifierField: 'id',
            defaultValue: null,
            maxSizeMb: 5,
            fileNameFormat: function (string $type, string $identifier, string $extension) {
                return "teacher_{$identifier}_portfolio_" . Carbon::now()->timestamp . ".{$extension}";
            },
            appendToArray: true,
            maxArraySize: 5,
            allowUploadWithoutRecord: true
        );
    }
}