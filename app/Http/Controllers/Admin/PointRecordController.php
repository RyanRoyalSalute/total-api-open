<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManagePointRecords;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PointRecordController extends Controller
{
    /**
     * Display a listing of all point records.
     */
    public function index()
    {
        $pointRecords = ManagePointRecords::with('user')->get();
        return apiResponse(200, $pointRecords->toArray(), 'Point records retrieved successfully', 200);
    }

    /**
     * Store a newly created point record in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'user_id' => 'required|exists:users,id',
                'change_reason' => 'required|string|max:255',
                'points_changed' => 'required|integer',
            ]);

            $pointRecord = ManagePointRecords::create($validated);

            return apiResponse(201, [$pointRecord->load('user')], 'Point record created successfully', 201);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the point record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified point record.
     */
    public function show($id)
    {
        $pointRecord = ManagePointRecords::with('user')->findOrFail($id);
        return apiResponse(200, [$pointRecord], 'Point record retrieved successfully', 200);
    }

    /**
     * Update the specified point record in storage.
     */
    public function update(Request $request, $id)
    {
        $pointRecord = ManagePointRecords::findOrFail($id);

        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'user_id' => 'sometimes|exists:users,id',
                'change_reason' => 'sometimes|string|max:255',
                'points_changed' => 'sometimes|integer',
            ]);

            $pointRecord->update($validated);

            $updatedPointRecord = ManagePointRecords::with('user')->findOrFail($id);
            return apiResponse(200, [$updatedPointRecord], 'Point record updated successfully', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the point record: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified point record from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $pointRecord = ManagePointRecords::findOrFail($id);
            $pointRecord->delete();

            DB::commit();

            return apiResponse(200, null, 'Point record deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the point record: ' . $e->getMessage(), 500);
        }
    }
}