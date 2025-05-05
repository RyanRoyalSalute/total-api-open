<?php
namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PaymentRecordController extends Controller
{
    public function getPaymentDetailsByUserId(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = $request->input('user_id');

        DB::table('course_tickets')
            ->where('owner_user_id', $userId)
            ->where('ticket_status', 0)
            ->where('date', '<', now()->toDateString())
            ->update(['ticket_status' => 3]);

        $paymentDetails = DB::table('payment_records')
            ->join('course_tickets', 'payment_records.trade_no', '=', 'course_tickets.payment_record_id')
            ->join('courses', 'course_tickets.course_id', '=', 'courses.id')
            ->join('teachers', 'course_tickets.teacher_id', '=', 'teachers.id')
            ->join('shop_brands', 'payment_records.shop_brand_id', '=', 'shop_brands.id')
            ->leftJoin('course_sessions', 'payment_records.session_id', '=', 'course_sessions.id')
            ->leftJoin('classrooms', 'course_sessions.classroom_id', '=', 'classrooms.id')
            ->where('payment_records.user_id', '=', $userId)
            ->select(
                'payment_records.id as payment_id',
                'payment_records.trade_no',
                'payment_records.payment_date',
                'payment_records.payment_method',
                'payment_records.transaction_amount',
                'payment_records.received_amount',
                'payment_records.recharge_amount',
                'payment_records.is_paid',
                'payment_records.privileged_level',
                DB::raw('MAX(courses.id) as course_id'),
                DB::raw('MAX(courses.course_name) as course_name'),
                DB::raw('MAX(courses.course_description) as course_description'),
                DB::raw('MAX(courses.course_images) as course_images'),
                DB::raw('MAX(courses.course_colors) as course_colors'),
                DB::raw('MAX(teachers.teacher_name) as teacher_name'),
                DB::raw('MAX(teachers.teacher_avatar) as teacher_avatar'),
                'payment_records.created_at as payment_created_at',
                'payment_records.updated_at as payment_updated_at',
                DB::raw('MAX(classrooms.classroom_address) as classroom_address'),
                DB::raw('MAX(classrooms.classroom_name) as classroom_name'),
                DB::raw('MIN(course_tickets.date) as earliest_date')
            )
            ->groupBy('payment_records.id')
            ->orderBy('earliest_date', 'desc')
            ->get();

        if ($paymentDetails->isEmpty()) {
            return apiResponse(404, null, 'No payment records found for this user.');
        }

        foreach ($paymentDetails as $paymentDetail) {
            $ticketDetails = DB::table('course_tickets')
                ->where('payment_record_id', '=', $paymentDetail->trade_no)
                ->select('ticket_id', 'ticket_status', 'date', 'time')
                ->get();

            $attendance = $ticketDetails->count();

            $paymentDetail->tickets = $ticketDetails;
            $paymentDetail->attendance = $attendance;
            unset($paymentDetail->earliest_date);
        }

        return apiResponse(2000, $paymentDetails, 'Payment and ticket details retrieved successfully.');
    }
}