<?php

namespace App\Models\UploadTypes;

use Carbon\Carbon;

class TeacherAvatar extends BaseUploadType
{
    public function __construct()
    {
        parent::__construct(
            baseFolder: 'teachers/avatars',
            table: 'teachers',
            column: 'teacher_avatar',
            identifierField: 'id',
            defaultValue: 'default',
            maxSizeMb: 5,
            fileNameFormat: function (string $type, string $identifier, string $extension) {
                return "teacher_{$identifier}_avatar_" . Carbon::now()->timestamp . ".{$extension}";
            },
            allowUploadWithoutRecord: true
        );
    }
}