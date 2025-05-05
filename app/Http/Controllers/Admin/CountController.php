<?php

namespace App\Http\Controllers\Admin;

// Course
use App\Models\ManageShopBrand;
use App\Models\ManageSubStore;
use App\Models\ManageClassroom;
use App\Models\ManageTeacher;
use App\Models\ManageProduct;
use App\Models\ManageCoursePrice;
use App\Models\ManageRecharge;
use App\Models\ManageCourse;
// Order
use App\Models\ManageUsers;
use App\Models\ManageCourseTickets;
use App\Models\ManagePaymentRecords;
use App\Models\ManagePointRecords;
use App\Models\ManageFeedbacks;
// Other
use App\Http\Controllers\Controller;

class CountController extends Controller
{
    public function getRecordCounts()
    {
        $counts = [
            'shop_brands' => ManageShopBrand::count(),
            'sub_stores' => ManageSubStore::count(),
            'classrooms' => ManageClassroom::count(),
            'teachers' => ManageTeacher::count(),
            'products' => ManageProduct::count(),
            'course_prices' => ManageCoursePrice::count(),
            'recharges' => ManageRecharge::count(),
            'courses' => ManageCourse::count(),
        ];
        return apiResponse(200, $counts, 'Record counts retrieved successfully', 200);
    }

    public function getOrderCounts()
    {
        $counts = [
            'users' => ManageUsers::count(),
            'course_tickets' => ManageCourseTickets::count(),
            'payment_records' => ManagePaymentRecords::count(),
            'point_records' => ManagePointRecords::count(),
            'feedbacks' => ManageFeedbacks::count(),
            /// 缺金流設置, 金流紀錄, 系統客服
        ];
        return apiResponse(200, $counts, 'Record counts retrieved successfully', 200);
    }
}