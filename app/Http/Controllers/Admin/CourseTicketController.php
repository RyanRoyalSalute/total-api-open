<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageCourseTickets;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CourseTicketController extends Controller
{
    /**
     * Display a listing of all course tickets.
     */
    public function index()
    {
        $tickets = ManageCourseTickets::with('course', 'owner', 'teacher', 'paymentRecord')->get();
        return apiResponse(200, $tickets->toArray(), 'Course tickets retrieved successfully', 200);
    }

    /**
     * Store a newly created course ticket in storage.
     */
    public function store(Request $request)
    {
        try {
            /* 票自動產生, 先不提供自建
            $validated = $request->validate([
                'ticket_id' => 'required|string|unique:course_tickets,ticket_id|max:255',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'course_id' => 'required|exists:courses,id',
                'ticket_status' => 'required|integer',
                'owner_phone' => 'nullable|string|max:255',
                'owner_user_id' => 'nullable|exists:users,id',
                'payment_record_id' => 'nullable|exists:payment_records,trade_no',
                'teacher_id' => 'nullable|exists:teachers,id',
                'date' => 'nullable|date',
                'time' => 'nullable|date_format:H:i',
            ]);

            $ticket = ManageCourseTickets::create($validated);

            return apiResponse(201, [$ticket->load('course', 'owner', 'teacher', 'paymentRecord')], 'Course ticket created successfully', 201);*/
            return apiResponse(201, ['暫未開放'], '暫未開放', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the course ticket: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified course ticket.
     */
    public function show($id)
    {
        $ticket = ManageCourseTickets::with('course', 'owner', 'teacher', 'paymentRecord')->findOrFail($id);
        return apiResponse(200, [$ticket], 'Course ticket retrieved successfully', 200);
    }

    /**
     * Update the specified course ticket in storage.
     */
    public function update(Request $request, $id)
    {
        $ticket = ManageCourseTickets::findOrFail($id);

        try {
            $validated = $request->validate([
                'ticket_id' => 'sometimes|string|unique:course_tickets,ticket_id,' . $id . '|max:255',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'course_id' => 'sometimes|exists:courses,id',
                'ticket_status' => 'sometimes|integer',
                'owner_phone' => 'nullable|string|max:255',
                'owner_user_id' => 'nullable|exists:users,id',
                'payment_record_id' => 'nullable|exists:payment_records,trade_no',
                'teacher_id' => 'nullable|exists:teachers,id',
                'date' => 'nullable|date',
                'time' => 'nullable|date_format:H:i',
            ]);

            $ticket->update($validated);

            $updatedTicket = ManageCourseTickets::with('course', 'owner', 'teacher', 'paymentRecord')->findOrFail($id);
            return apiResponse(200, [$updatedTicket], 'Course ticket updated successfully', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the course ticket: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified course ticket from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $ticket = ManageCourseTickets::findOrFail($id);
            $ticket->delete();

            DB::commit();

            return apiResponse(200, null, 'Course ticket deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the course ticket: ' . $e->getMessage(), 500);
        }
    }
}