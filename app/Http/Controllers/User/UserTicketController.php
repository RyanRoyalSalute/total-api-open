<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserTicketController extends Controller
{
    /**
     * Get all tickets belonging to a user and their course information.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserTickets(Request $request)
    {
        // Validate the input
        $request->validate([
            'userId' => 'required|integer|exists:users,id',
        ]);

        $userId = $request->input('userId');

        // Update expired tickets with status = 0
        DB::table('course_tickets')
            ->where('user_id', $userId)
            ->where('ticket_status', 0)
            ->whereRaw('JSON_EXTRACT(course_dates, "$.end_date") < ?', [now()->toDateString()])
            ->update(['ticket_status' => 3]);

        // Fetch tickets and corresponding course information
        $tickets = DB::table('course_tickets')
            ->join('course', 'course_tickets.course_id', '=', 'course.id')
            ->leftJoin('teachers', DB::raw('JSON_CONTAINS(course.teacher_ids, CAST(teachers.id AS JSON))'), '=', DB::raw('1'))
            ->select(
                'course_tickets.id as ticket_id',
                'course_tickets.ticket_status',
                'course.course_name',
                'course.course_colors',
                'course.course_dates',
                'course.course_times',
                'course.course_images',
                'teachers.teacher_name',
                'teachers.teacher_avatar'
            )
            ->where('course_tickets.user_id', $userId)
            ->get();

        if ($tickets->isEmpty()) {
            return apiResponse(404, null, 'No tickets found for the specified user.', 404);
        }

        // Format the data
        $formattedTickets = $tickets->map(function ($ticket) {
            return [
                'ticket_id' => $ticket->ticket_id,
                'ticket_status' => $ticket->ticket_status,
                'course_name' => $ticket->course_name,
                'course_colors' => json_decode($ticket->course_colors, true),
                'course_dates' => json_decode($ticket->course_dates, true),
                'course_times' => json_decode($ticket->course_times, true),
                'course_images' => json_decode($ticket->course_images, true),
                'teacher_name' => $ticket->teacher_name,
                'teacher_avatar' => $ticket->teacher_avatar,
            ];
        });

        return apiResponse(200, $formattedTickets, 'User tickets retrieved successfully.', 200);
    }
}