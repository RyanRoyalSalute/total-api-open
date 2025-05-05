<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageFeedbacks;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    /**
     * Display a listing of all feedbacks, sorted by 'sort'.
     */
    public function index()
    {
        $feedbacks = ManageFeedbacks::with('course', 'user')
            ->orderBy('sort', 'asc')
            ->get();
        return apiResponse(200, $feedbacks->toArray(), 'Feedbacks retrieved successfully', 200);
    }

    /**
     * Store a newly created feedback in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'course_id' => 'required|exists:courses,id',
                'user_id' => 'required|exists:users,id',
                'latest_feedback_date' => 'required|date',
                'feedback_text' => 'nullable|string',
                'feedback_images' => 'nullable|array|max:5',
                'feedback_images.*' => 'string|max:255',
                'sort' => 'integer|min:0',
                'status' => 'integer|in:-1,0,1', // -1:隱藏, 1:置頂, 0正常
            ]);


            $feedback = ManageFeedbacks::create($validated);

            return apiResponse(201, [$feedback->load('course', 'user')], 'Feedback created successfully', 201);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the feedback: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified feedback.
     */
    public function show($id)
    {
        $feedback = ManageFeedbacks::with('course', 'user')->findOrFail($id);
        return apiResponse(200, [$feedback], 'Feedback retrieved successfully', 200);
    }

    /**
     * Update the specified feedback in storage.
     */
    public function update(Request $request, $id)
    {
        $feedback = ManageFeedbacks::findOrFail($id);

        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'course_id' => 'sometimes|exists:courses,id',
                'user_id' => 'sometimes|exists:users,id',
                'latest_feedback_date' => 'sometimes|date',
                'feedback_text' => 'nullable|string',
                'feedback_images' => 'nullable|array|max:5',
                'feedback_images.*' => 'string|max:255',
                'sort' => 'sometimes|integer|min:0',
                'status' => 'sometimes|integer|in:-1,0,1', // -1:隱藏, 1:置頂, 0正常
            ]);


            $feedback->update($validated);

            $updatedFeedback = ManageFeedbacks::with('course', 'user')->findOrFail($id);
            return apiResponse(200, [$updatedFeedback], 'Feedback updated successfully', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the feedback: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified feedback from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $feedback = ManageFeedbacks::findOrFail($id);
            $feedback->delete();

            DB::commit();

            return apiResponse(200, null, 'Feedback deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the feedback: ' . $e->getMessage(), 500);
        }
    }
}