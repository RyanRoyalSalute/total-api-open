<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Models\ShopBrand;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    /**
     * Check seat availability for a course session and release expired blocked seats.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSeatAvailability(Request $request): \Illuminate\Http\JsonResponse
    {
        $courseId = $request->input('course_id');
        $sessionDate = $request->input('session_date'); // Expected format: YYYY-MM-DD
        $startTime = $request->input('start_time');     // Expected format: HH:MM

        // Validate inputs
        if (empty($courseId) || empty($sessionDate) || empty($startTime)) {
            return apiResponse(4001, null, 'Course ID, session date, and start time are required.', 400);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sessionDate) || !preg_match('/^\d{2}:\d{2}$/', $startTime)) {
            return apiResponse(4002, null, 'Invalid date (YYYY-MM-DD) or time (HH:MM) format.', 400);
        }

        // Step 1: Release expired blocked seats and mark payments as expired
        $this->releaseExpiredBlockedSeats($courseId, $sessionDate, $startTime);

        // Step 2: Check if the session already exists
        $session = DB::table('course_sessions')
            ->where('course_id', $courseId)
            ->where('session_date', $sessionDate)
            ->where('start_time', $startTime)
            ->first();

        if ($session) {
            $remaining = $session->available_seats - $session->booked_seats - $session->blocked_seats;
            return apiResponse(2000, [
                'session_id' => $session->id,
                'course_id' => $session->course_id,
                'session_date' => $session->session_date,
                'start_time' => $session->start_time,
                'end_time' => $session->end_time,
                'available_seats' => $session->available_seats,
                'booked_seats' => $session->booked_seats,
                'blocked_seats' => $session->blocked_seats,
                'remaining' => $remaining,
                'is_active' => $session->is_active,
            ], 'Seat availability retrieved successfully.', 200);
        }

        // If session doesn't exist, validate and create it
        $course = DB::table('courses')->where('id', $courseId)->first();
        if (!$course) {
            return apiResponse(4041, null, 'Course not found.', 404);
        }

        $courseDates = json_decode($course->course_dates, true) ?? [];
        $courseTimes = json_decode($course->course_times, true) ?? [];
        if (empty($courseDates) || empty($courseTimes)) {
            return apiResponse(4003, null, 'Course has no scheduled dates or times.', 400);
        }

        $isSpecificDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $courseDates[0]);
        $dateCarbon = Carbon::parse($sessionDate);
        $isValidDate = false;

        if ($isSpecificDate) {
            $isValidDate = in_array($sessionDate, $courseDates);
        } else {
            $weekday = $dateCarbon->dayOfWeek == 0 ? 7 : $dateCarbon->dayOfWeek;
            $isValidDate = in_array((string)$weekday, array_map('strval', $courseDates));
        }

        if (!$isValidDate) {
            return apiResponse(4004, null, 'Requested date does not match course schedule.', 400);
        }

        if (!in_array($startTime, $courseTimes)) {
            return apiResponse(4005, null, 'Requested start time does not match course schedule.', 400);
        }

        $classroom = DB::table('course_classroom')
            ->where('course_id', $courseId)
            ->first();

        if (!$classroom) {
            return apiResponse(4006, null, 'No classroom assigned to this course.', 400);
        }

        $fullSeats = DB::table('classrooms')
            ->where('id', $classroom->classroom_id)
            ->value('full_seats') ?? 10;

        DB::table('course_sessions')->insert([
            'course_id' => $courseId,
            'classroom_id' => $classroom->classroom_id,
            'session_date' => $sessionDate,
            'start_time' => $startTime,
            'end_time' => Carbon::parse($startTime)->addMinutes($course->period)->format('H:i'),
            'available_seats' => $fullSeats,
            'booked_seats' => 0,
            'blocked_seats' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $newSession = DB::table('course_sessions')
            ->where('course_id', $courseId)
            ->where('session_date', $sessionDate)
            ->where('start_time', $startTime)
            ->first();

        $remaining = $newSession->available_seats - $newSession->booked_seats - $newSession->blocked_seats;
        return apiResponse(2000, [
            'session_id' => $newSession->id,
            'course_id' => $newSession->course_id,
            'session_date' => $newSession->session_date,
            'start_time' => $newSession->start_time,
            'end_time' => $newSession->end_time,
            'available_seats' => $newSession->available_seats,
            'booked_seats' => $newSession->booked_seats,
            'blocked_seats' => $newSession->blocked_seats,
            'remaining' => $remaining,
            'is_active' => $newSession->is_active,
        ], 'Session created and seat availability retrieved successfully.', 200);
    }

    /**
     * Release blocked seats from unpaid payment records older than 1 hour and mark them as expired.
     *
     * @param int $courseId
     * @param string $sessionDate
     * @param string $startTime
     * @return void
     */
    private function releaseExpiredBlockedSeats(int $courseId, string $sessionDate, string $startTime): void
    {
        $oneHourAgo = Carbon::now()->subHour();

        // Find unpaid payment records older than 1 hour for this session
        $expiredPayments = DB::table('payment_records')
            ->where('course_id', $courseId)
            ->where('date', $sessionDate)
            ->where('time', $startTime)
            ->where('is_paid', 0)
            ->where('created_at', '<=', $oneHourAgo)
            ->get();

        if ($expiredPayments->isEmpty()) {
            return;
        }

        // Process each expired payment
        foreach ($expiredPayments as $payment) {
            // Mark the payment as expired (is_paid = -1)
            DB::table('payment_records')
                ->where('trade_no', $payment->trade_no)
                ->update(['is_paid' => -1, 'updated_at' => now()]);

            // Release the blocked seats
            DB::table('course_sessions')
                ->where('course_id', $payment->course_id)
                ->where('session_date', $payment->date)
                ->where('start_time', $payment->time)
                ->decrement('blocked_seats', $payment->attendance, ['updated_at' => now()]);
        }
    }
}