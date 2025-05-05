<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CourseRecordController extends Controller
{
    public function getCourseDetailsByUserId(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = $request->input('user_id');
        $currentDateTime = Carbon::now();

        try {
            DB::beginTransaction();

            // 0, 未使用, 1, 已使用, 2, 作廢, 3, 未報到
            DB::table('course_tickets')
                ->where('owner_user_id', $userId)
                ->where('ticket_status', 0) // 未使用
                ->whereRaw("CONCAT(date, ' ', time) < ?", [$currentDateTime->toDateTimeString()])
                ->update(['ticket_status' => 3]); // Set to 未報到

            $courseDetails = DB::table('course_tickets')
                ->join('courses', 'course_tickets.course_id', '=', 'courses.id')
                ->join('teachers', 'course_tickets.teacher_id', '=', 'teachers.id')
                ->where('course_tickets.owner_user_id', $userId)
                ->select(
                    'courses.id as course_id',
                    'courses.course_name',
                    'courses.course_description',
                    'courses.course_dates',
                    'courses.course_times',
                    'courses.course_images',
                    'teachers.teacher_name',
                    'teachers.teacher_avatar',
                    'course_tickets.date as ticket_date',
                    'course_tickets.time as ticket_time',
                    'course_tickets.ticket_status as ticket_status',
                    'course_tickets.ticket_id'
                )
                ->get();

            DB::commit();

            if ($courseDetails->isEmpty()) {
                return apiResponse(404, null, 'No courses found for this user.', 404);
            }

            return apiResponse(200, $courseDetails, 'Course details retrieved successfully.', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while retrieving course details: ' . $e->getMessage(), 500);
        }
    }
}