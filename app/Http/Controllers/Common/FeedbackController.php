<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    /**
     * Get feedback by course ID.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeedbackByCourseId(Request $request)
    {
        // Validate the incoming request to ensure `course_id` is provided
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id', // Ensure course_id exists in the courses table
        ]);

        // If validation fails, Laravel automatically returns a response with status 422
        $courseId = $request->input('course_id');

        // Retrieve feedbacks based on course_id
        $feedbacks = DB::table('feedbacks')
            ->where('course_id', $courseId)
            ->select(
                'feedbacks.id as feedback_id',
                'feedbacks.feedback_text',
                'feedbacks.feedback_images',
                'feedbacks.latest_feedback_date',
                'users.name as user_name', // Optionally include user name, adjust according to your needs
                'feedbacks.created_at',  // Include created_at timestamp
                'feedbacks.updated_at',  // Include updated_at timestamp
                'feedbacks.user_id'      // Include user_id for reference
            )
            ->join('users', 'feedbacks.user_id', '=', 'users.id') // Join users table to get user name
            ->get();

        // If no feedback records are found, return a 404 response
        if ($feedbacks->isEmpty()) {
            return apiResponse(404, null, 'No feedback found for this course.');
        }

        // Return the feedbacks in the response, wrapping the response in an object
        return apiResponse(2000, (object) [
            'feedbacks' => $feedbacks, // Ensuring the response is an object
            'message' => 'Feedback retrieved successfully.'
        ]);
    }
}
