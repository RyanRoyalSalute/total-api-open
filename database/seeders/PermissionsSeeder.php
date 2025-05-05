<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            ['name' => '訪問LINE後台網頁', 'description' => '允許訪問LINE後台網頁界面。', 'permission_code' => 'LINE_BACKEND_VIEW'],
            ['name' => '查看店頭管理', 'description' => '允許查看店頭管理功能。', 'permission_code' => 'STORE_MANAGE_VIEW'],
            ['name' => '管理店頭', 'description' => '允許管理店頭資訊。', 'permission_code' => 'STORE_MANAGE'],
            ['name' => '查看課程', 'description' => '允許查看可用的課程。', 'permission_code' => 'COURSE_VIEW'],
            ['name' => '管理課程', 'description' => '允許創建、編輯和刪除課程。', 'permission_code' => 'COURSE_MANAGE'],
            ['name' => '查看課程詳情', 'description' => '允許查看詳細的課程資訊。', 'permission_code' => 'COURSE_DETAIL_VIEW'],
            ['name' => '課程管理（購買）', 'description' => '允許管理課程購買。', 'permission_code' => 'COURSE_PURCHASE_MANAGE'],
            ['name' => '管理付款', 'description' => '允許管理付款資訊。', 'permission_code' => 'PAYMENT_MANAGE'],
            ['name' => '管理第三方付款', 'description' => '允許管理第三方付款整合。', 'permission_code' => 'THIRD_PARTY_PAYMENT_MANAGE'],
            ['name' => '管理用戶權限', 'description' => '允許編輯用戶權限。', 'permission_code' => 'USER_PERMISSION_MANAGE'],
            ['name' => '查看用戶權限', 'description' => '允許查看用戶的權限。', 'permission_code' => 'USER_PERMISSION_VIEW'],
            ['name' => '管理系統設置', 'description' => '允許管理系統設置。', 'permission_code' => 'SYSTEM_SETTING_MANAGE'],
            ['name' => '查看財務設置', 'description' => '允許查看財務系統設置。', 'permission_code' => 'FINANCE_SETTING_VIEW'],
            ['name' => '管理財務設置', 'description' => '允許管理財務系統設置。', 'permission_code' => 'FINANCE_SETTING_MANAGE'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
