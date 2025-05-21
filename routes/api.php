<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- 核心用戶認證與既有服務 API ---
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\SmsAuthController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\CourseRecordController;
use App\Http\Controllers\User\PaymentRecordController as UserPaymentRecordController;
use App\Http\Controllers\User\UserTicketController;

use App\Http\Controllers\Common\ShopBrandController;
use App\Http\Controllers\Common\ProductController as CommonProductController;
use App\Http\Controllers\Common\TeacherController as CommonTeacherController;
use App\Http\Controllers\Common\FeedbackController as CommonFeedbackController;
use App\Http\Controllers\Common\CourseController as CommonCourseControllerForSaabisu;
use App\Http\Controllers\Common\ImageUploadController;

use App\Http\Controllers\Order\PaymentController;
use App\Http\Controllers\Order\ECPayCallbackController;
use App\Http\Controllers\Order\LinePayCallbackController;

use App\Http\Controllers\Admin\PermissionsController as AdminPermissionsController;
use App\Http\Controllers\Admin\CountController as AdminCountController;
use App\Http\Controllers\Admin\ShopBrandController as AdminShopBrandController;
use App\Http\Controllers\Admin\SubStoreController as AdminSubStoreController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\Admin\CoursePriceController as AdminCoursePriceController;
use App\Http\Controllers\Admin\RechargeController as AdminRechargeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CourseTicketController as AdminCourseTicketController;
use App\Http\Controllers\Admin\PaymentRecordController as AdminPaymentRecordController;
use App\Http\Controllers\Admin\PointRecordController as AdminPointRecordController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;

/*
|--------------------------------------------------------------------------
| 主控台 API  (Saabisu-API) - 里程碑一
|--------------------------------------------------------------------------
|
| 職責劃分:
| A. 平台核心功能: 處理用戶認證、SSO。
| B. SaaS 服務展示與購買
| C. 平台管理後台: /admin
|
*/

// --- A. 平台核心功能 ---

// Passport 的 OAuth2 端點，用於獲取 token
Route::group(['prefix' => 'oauth'], function () {
    Route::post('token', ['uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken']);
});

// 公開 API: 用戶註冊/登入流程
Route::post('user/signIn', [AuthController::class, 'signIn']);
Route::post('user/verify-sms', [SmsAuthController::class, 'verify']);

// 需授權的 API (平台通用)
Route::middleware(['auth:api'])->group(function () {
    // 個人資料
    Route::get('user/profile', [UserProfileController::class, 'getProfile']);
    Route::put('user/profile', [UserProfileController::class, 'updateProfile']);
    Route::post('user/profile/upload-avatar', [UserProfileController::class, 'uploadAvatar']);

    // Passport路徑，讓 Token 持有者可以獲取自己的用戶資訊
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});


// --- B. SaaS 服務展示與購買 ---
Route::prefix('common')->group(function () {
    Route::post('shopbrand', [ShopBrandController::class, 'getBrandByCode']);
    Route::get('courses', [ShopBrandController::class, 'getShopCourses'])->middleware('auth:api');
    Route::get('products/{material_id}', [CommonProductController::class, 'getProductDetail']);
    Route::get('teacher', [CommonTeacherController::class, 'getTeacherInfo']);
    Route::get('feedback', [CommonFeedbackController::class, 'getFeedbackByCourseId']);
    Route::post('seat-availability', [CommonCourseControllerForSaabisu::class, 'checkSeatAvailability']);
    
    Route::post('/upload-image', [ImageUploadController::class, 'uploadImage'])->middleware('auth:api');
    Route::post('/delete-image', [ImageUploadController::class, 'deleteImage'])->middleware('auth:api');
});

Route::post('order/submit', [PaymentController::class, 'submitOrder'])->middleware('auth:api');
Route::post('order/submitExt', [PaymentController::class, 'submitOrderExternal'])->middleware('auth:api');

Route::get('order/confirm', [LinePayCallbackController::class, 'handleConfirm']);
Route::get('order/cancel', [LinePayCallbackController::class, 'handleCancel']);
Route::get('order/form', [PaymentController::class, 'getPaymentForm']);
Route::post('order/ecpay/callback', [ECPayCallbackController::class, 'handleCallback']);

Route::get('user/course-record', [CourseRecordController::class, 'getCourseDetailsByUserId'])->middleware('auth:api');
Route::get('user/payment-record', [UserPaymentRecordController::class, 'getPaymentDetailsByUserId'])->middleware('auth:api');
Route::get('/user/tickets', [UserTicketController::class, 'index'])->middleware('auth:api');


// --- C. 平台管理後台 ---
Route::prefix('admin')->middleware(['auth:api'])->group(function () {
    Route::get('/course_counts', [AdminCountController::class, 'getRecordCounts']);
    Route::get('/order_counts', [AdminCountController::class, 'getOrderCounts']);
    
    Route::apiResource('shop-brands', AdminShopBrandController::class);
    Route::apiResource('sub-stores', AdminSubStoreController::class);
    Route::apiResource('teachers', AdminTeacherController::class);
    Route::apiResource('products', AdminProductController::class);
    Route::apiResource('courses', AdminCourseController::class);
    Route::apiResource('classrooms', AdminClassroomController::class);
    Route::apiResource('course-prices', AdminCoursePriceController::class);
    Route::apiResource('recharges', AdminRechargeController::class);
    Route::apiResource('users', AdminUserController::class); 
    Route::apiResource('course-tickets', AdminCourseTicketController::class);
    Route::apiResource('payment-records', AdminPaymentRecordController::class);
    Route::apiResource('point-records', AdminPointRecordController::class);
    Route::apiResource('feedbacks', AdminFeedbackController::class);
    
    Route::get('/permissions', [AdminPermissionsController::class, 'index']);
    Route::post('/users/{userId}/assign-permissions', [AdminPermissionsController::class, 'assignPermissionsToUser']);
});


// 測試
Route::post('test-sms', function(Request $request) {
    $mobile = $request->input('mobile');
    $message = $request->input('message');
    if(empty($mobile) || empty($message)) {
        return apiResponse(400, null, 'Mobile number and message are required.', 400);
    }
    return sendSms($mobile, $message);
});