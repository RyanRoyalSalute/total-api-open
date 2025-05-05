<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageClassroom;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = ManageClassroom::with('subStores', 'courses')
            ->orderBy('sort', 'asc')
            ->get();
        return apiResponse(200, $classrooms->toArray(), 'Classrooms retrieved successfully', 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'classroom_name' => 'required|string|max:255',
                'classroom_address' => 'required|string|max:255',
                'start_seats' => 'required|integer|min:1',
                'full_seats' => 'required|integer|min:1',
                'hour_costs' => 'required|numeric|min:0',
                'active' => 'boolean',
                'sort' => 'integer|min:0',
                'pinned' => 'boolean',
                'sub_store_ids' => 'nullable|array',
                'sub_store_ids.*' => 'exists:sub_store,id',
                'course_ids' => 'nullable|array',
                'course_ids.*' => 'exists:courses,id',
            ]);

            $subStoreIds = $validated['sub_store_ids'] ?? [];
            $courseIds = $validated['course_ids'] ?? [];
            unset($validated['sub_store_ids'], $validated['course_ids']);

            $classroom = ManageClassroom::create($validated);

            if (!empty($subStoreIds)) $classroom->subStores()->sync($subStoreIds);
            if (!empty($courseIds)) $classroom->courses()->sync($courseIds);

            return apiResponse(201, [$classroom->load('subStores', 'courses')], 'Classroom created successfully', 201);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the classroom: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $classroom = ManageClassroom::with('subStores', 'courses')->findOrFail($id);
        return apiResponse(200, [$classroom], 'Classroom retrieved successfully', 200);
    }

    public function update(Request $request, $id)
    {
        $classroom = ManageClassroom::findOrFail($id);

        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'classroom_name' => 'sometimes|string|max:255',
                'classroom_address' => 'sometimes|string|max:255',
                'start_seats' => 'sometimes|integer|min:1',
                'full_seats' => 'sometimes|integer|min:1',
                'hour_costs' => 'sometimes|numeric|min:0',
                'active' => 'sometimes|boolean',
                'sort' => 'sometimes|integer|min:0',
                'pinned' => 'sometimes|boolean',
                'sub_store_ids' => 'nullable|array',
                'sub_store_ids.*' => 'exists:sub_store,id',
                'course_ids' => 'nullable|array',
                'course_ids.*' => 'exists:courses,id',
            ]);

            $subStoreIds = $validated['sub_store_ids'] ?? [];
            $courseIds = $validated['course_ids'] ?? [];
            unset($validated['sub_store_ids'], $validated['course_ids']);

            $classroom->update($validated);

            $now = now();
            if (array_key_exists('sub_store_ids', $request->all())) {
                $syncData = array_fill_keys($subStoreIds, ['created_at' => $now, 'updated_at' => $now]);
                $classroom->subStores()->sync($syncData);
            }
            if (array_key_exists('course_ids', $request->all())) {
                $syncData = array_fill_keys($courseIds, ['created_at' => $now, 'updated_at' => $now]);
                $classroom->courses()->sync($syncData);
            }

            $updatedClassroom = ManageClassroom::with('subStores', 'courses')->findOrFail($id);
            return apiResponse(200, [$updatedClassroom], 'Classroom updated successfully', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the classroom: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $classroom = ManageClassroom::findOrFail($id);
            $classroom->subStores()->detach();
            $classroom->courses()->detach();
            
            // Delete associated images
            if ($classroom->classroom_images) {
                $images = is_array($classroom->classroom_images) 
                    ? $classroom->classroom_images 
                    : json_decode($classroom->classroom_images, true) ?? [];
                foreach ($images as $image) {
                    $filePath = str_starts_with($image, 'storage/') ? substr($image, 8) : $image;
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
            }
            
            $classroom->delete();

            DB::commit();

            return apiResponse(200, null, 'Classroom deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the classroom: ' . $e->getMessage(), 500);
        }
    }
}