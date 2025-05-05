<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Services\TicketService;
use App\Services\CourseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $ticketService;
    protected $courseService;

    public function __construct(
        PaymentService $paymentService,
        TicketService $ticketService,
        CourseService $courseService
    ) {
        $this->paymentService = $paymentService;
        $this->ticketService = $ticketService;
        $this->courseService = $courseService;
    }

    public function submitOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ItemName' => 'required|string',
            'TotalAmount' => 'required|numeric|min:0',
            'sessionId' => 'required|numeric|exists:course_sessions,id',
            'courseId' => 'required|numeric|exists:courses,id',
            'attendance' => 'required|numeric|min:1',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'teacher_id' => 'required|numeric|exists:teachers,id',
            'shop_brand_id' => 'required|numeric|exists:shop_brands,id',
            'sub_store_id' => 'required|numeric|exists:sub_store,id',
            'recharge_id' => 'nullable|numeric|exists:recharges,id',
            'payment_method' => 'required|in:CREDIT_CARD,LINE_PAY',
        ]);

        if ($validator->fails()) {
            return apiResponse(4001, null, $validator->errors()->first(), 400);
        }

        $userId = $request->header('userId');
        $ownerPhone = $request->header('mobile');

        if (!$userId || !$ownerPhone) {
            return apiResponse(4005, null, 'User ID and mobile number are required in headers.', 400);
        }

        $sessionId = $request->input('sessionId');
        $courseId = $request->input('courseId');
        $attendance = $request->input('attendance');
        $date = $request->input('date');
        $time = $request->input('time');
        $teacherId = $request->input('teacher_id');
        $shopBrandId = $request->input('shop_brand_id');
        $subStoreId = $request->input('sub_store_id');
        $rechargeId = $request->input('recharge_id');
        $clientTotalAmount = $request->input('TotalAmount');
        $paymentMethod = $request->input('payment_method');

        $session = DB::table('course_sessions')->where('id', $sessionId)->first();
        if (!$session) {
            return apiResponse(4008, null, 'Invalid course session ID.', 400);
        }

        if ($session->course_id !== (int)$courseId || $session->session_date !== $date || $session->start_time !== $time) {
            return apiResponse(4009, null, 'Session does not match course, date, or time.', 400);
        }

        if (!DB::table('course_teacher')->where('course_id', $courseId)->where('teacher_id', $teacherId)->exists()) {
            return apiResponse(4003, null, 'Teacher not valid for this course.', 400);
        }
        if (!DB::table('course_sub_store')->where('course_id', $courseId)->where('sub_store_id', $subStoreId)->exists()) {
            return apiResponse(4006, null, 'Sub-store not valid for this course.', 400);
        }
        if (!DB::table('sub_store')->where('id', $subStoreId)->where('shop_brand_id', $shopBrandId)->exists()) {
            return apiResponse(4007, null, 'Sub-store does not belong to this brand.', 400);
        }

        $remainingSeats = $session->available_seats - ($session->booked_seats + $session->blocked_seats);
        if ($remainingSeats < $attendance) {
            return apiResponse(4002, null, 'Sorry, this course is sold out.', 400);
        }

        $course = DB::table('courses')->where('id', $courseId)->first();
        if (!$course || !$course->on_sale) {
            return apiResponse(4010, null, 'Course not found or not on sale.', 400);
        }
        // ---------------------------------
        // Course price validation
        $coursePrice = DB::table('course_prices')
            ->where('id', $course->course_price_id)
            ->where('active', true)
            ->value('original_price');
        if (!$coursePrice) {
            return apiResponse(4013, null, 'Course price not found or inactive.', 400);
        }

        $recharge = null;
        $freeCount = 0;
        $rechargeAmount = 0;
        if ($rechargeId) {
            $recharge = DB::table('recharges')
                ->where('id', $rechargeId)
                ->where('active', true)
                ->first();
            if (!$recharge) {
                return apiResponse(4011, null, 'Invalid or inactive recharge ID.', 400);
            }
            $freeCount = $recharge->free_count;
            $rechargeAmount = $recharge->recharge_amount;
        }

        $payableAttendance = max(0, $attendance - $freeCount);
        //$calculatedTotal = ($payableAttendance * $coursePrice) + $rechargeAmount;
        // ---------------------------------
        $calculatedTotal = $clientTotalAmount;  // ignore price validation first

        $tradeNo = $this->paymentService->generateTradeNo();

        DB::beginTransaction();
        try {
            $paymentRecordId = $this->paymentService->insertPaymentRecord([
                'trade_no' => $tradeNo,
                'order_3rd_no' => $paymentMethod === 'CREDIT_CARD' ? $tradeNo : null, // Set order_3rd_no for CREDIT_CARD
                'created_by' => $userId,
                'updated_by' => $userId,
                'shop_brand_id' => $shopBrandId,
                'sub_store_id' => $subStoreId,
                'payment_date' => Carbon::today()->toDateString(),
                'user_id' => $userId,
                'course_id' => $courseId,
                'payment_method' => $paymentMethod,
                'transaction_amount' => $calculatedTotal,
                'received_amount' => $calculatedTotal,
                'recharge_amount' => $rechargeAmount ?: null,
                'privileged_level' => 0,
                'pinned' => false,
                'is_paid' => false,
                'attendance' => $attendance,
                'date' => $date,
                'time' => $time,
                'teacher_id' => $teacherId,
                'owner_phone' => $ownerPhone,
                'session_id' => $sessionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            $this->blockCourseSessionSeats($sessionId, $attendance);

            $paymentData = [
                'trade_no' => $tradeNo,
                'transaction_amount' => $calculatedTotal,
                'item_name' => $request->input('ItemName'),
            ];

            if ($paymentMethod === 'LINE_PAY') {
                $linePayResponse = $this->paymentService->generateLinePayRequest($paymentData);
                // Update order_3rd_no with transaction_id for LINE_PAY
                DB::table('payment_records')
                    ->where('id', $paymentRecordId)
                    ->update(['order_3rd_no' => $linePayResponse['transaction_id']]);
                
                DB::commit();
                
                return apiResponse(2000, [
                    'payment_url' => $linePayResponse['payment_url'],
                    'transaction_id' => $linePayResponse['transaction_id'],
                ], 'Order created, redirect to LINE Pay.', 200);
            } else { // CREDIT_CARD
                DB::commit();
                
                $ecpayForm = $this->paymentService->generateECPayForm($paymentData);
                return apiResponse(2000, $ecpayForm, 'Order created, redirecting to ECPay.', 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(5001, null, 'Order creation failed: ' . $e->getMessage(), 500);
        }
    }

    private function blockCourseSessionSeats(int $sessionId, int $attendance): void
    {
        DB::table('course_sessions')
            ->where('id', $sessionId)
            ->increment('blocked_seats', $attendance, ['updated_at' => now()]);
    }
}