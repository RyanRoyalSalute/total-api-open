<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Common\ShopBrandController;
use App\Http\Controllers\Common\ProductController;
use App\Http\Controllers\Common\TeacherController;
use App\Http\Controllers\Common\FeedbackController;
use App\Http\Controllers\Common\CourseController;

use App\Http\Controllers\Common\ImageUploadController;

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\SmsAuthController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\CourseRecordController;
use App\Http\Controllers\User\PaymentRecordController;

use App\Http\Controllers\Order\PaymentController;
use App\Http\Controllers\Order\ECPayCallbackController;
use App\Http\Controllers\Order\LinePayCallbackController;

use App\Http\Middleware\TokenValidator;

use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\CountController;
use App\Http\Controllers\Admin\ShopBrandController as AdminShopBrandController;
use App\Http\Controllers\Admin\SubStoreController as AdminSubStoreController;
use App\Http\Controllers\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\CoursePriceController;
use App\Http\Controllers\Admin\RechargeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CourseTicketController;
use App\Http\Controllers\Admin\PaymentRecordController as AdminPaymentRecordController;
use App\Http\Controllers\Admin\PointRecordController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;


/** common **/
Route::prefix('common')->group(function () {
    Route::post('shopbrand', [ShopBrandController::class, 'getBrandByCode']);
    Route::get('courses', [ShopBrandController::class, 'getShopCourses'])->middleware(TokenValidator::class);
    Route::get('products/{material_id}', [ProductController::class, 'getProductDetail']);
    Route::get('teacher', [TeacherController::class, 'getTeacherInfo']);
    Route::get('feedback', [FeedbackController::class, 'getFeedbackByCourseId']);
    Route::post('seat-availability', [CourseController::class, 'checkSeatAvailability']);

    /** Images **/
    Route::post('/upload-image', [ImageUploadController::class, 'uploadImage']);
    Route::post('/delete-image', [ImageUploadController::class, 'deleteImage']);
});


/** User **/
Route::post('user/signIn', [AuthController::class, 'signIn']);
Route::post('user/verify-sms', [SmsAuthController::class, 'verify']);

Route::middleware([TokenValidator::class])->group(function () {
    Route::post('user/profile/upload-avatar', [UserProfileController::class, 'uploadAvatar']);
    Route::put('user/profile', [UserProfileController::class, 'updateProfile']);
    Route::get('user/profile', [UserProfileController::class, 'getProfile']);
    Route::get('user/course-record', [CourseRecordController::class, 'getCourseDetailsByUserId']);
    Route::get('user/payment-record', [PaymentRecordController::class, 'getPaymentDetailsByUserId']);
});


/** Order **/
Route::post('order/submit', [PaymentController::class, 'submitOrder'])->middleware(TokenValidator::class);
Route::post('order/submitExt', [PaymentController::class, 'submitOrderExternal'])->middleware(TokenValidator::class);
Route::get('order/confirm', [LinePayCallbackController::class, 'handleConfirm']);
Route::get('order/cancel', [LinePayCallbackController::class, 'handleCancel']);
Route::get('order/form', [PaymentController::class, 'getPaymentForm']);
Route::post('order/ecpay/callback', [ECPayCallbackController::class, 'handleCallback']);

/** Admin **/
Route::post('/users/{userId}/permissions', [PermissionsController::class, 'assignPermissionsToUser']);
Route::post('/users/{userId}/check-permission', [PermissionsController::class, 'checkPermission']);


Route::prefix('admin')->group(function () {
    Route::get('/course_counts', [CountController::class, 'getRecordCounts']);
    Route::get('/order_counts', [CountController::class, 'getOrderCounts']);
    Route::resource('shop-brands', AdminShopBrandController::class);
    Route::resource('sub-stores', AdminSubStoreController::class);
    Route::resource('classrooms', ClassroomController::class);
    Route::resource('teachers', AdminTeacherController::class);

    Route::resource('products', AdminProductController::class);
    Route::resource('course-prices', CoursePriceController::class);
    Route::resource('recharges', RechargeController::class);
    Route::resource('courses', AdminCourseController::class);

    Route::resource('users', AdminUserController::class);
    Route::resource('course-tickets', CourseTicketController::class);
    Route::resource('payment-records', AdminPaymentRecordController::class);
    Route::resource('point-records', PointRecordController::class);
    Route::resource('feedbacks', AdminFeedbackController::class);
});

/** Test **/
Route::post('test-sms', function (\Illuminate\Http\Request $request) {
    // Retrieve input from request
    $mobile = $request->input('mobile');
    $message = $request->input('message');

    // Validate input
    if (empty($mobile) || empty($message)) {
        return apiResponse(400, null, 'Mobile number and message are required.', 400);
    }

    // Call the sendSms function
    return sendSms($mobile, $message);
});
