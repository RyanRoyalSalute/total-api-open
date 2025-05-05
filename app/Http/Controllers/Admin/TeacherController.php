<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageTeacher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of all teachers, sorted by 'sort' column.
     */
    public function index()
    {
        $teachers = ManageTeacher::with('shopBrand', 'courses')
            ->orderBy('sort', 'asc')
            ->get();
        return apiResponse(200, $teachers->toArray(), 'Teachers retrieved successfully', 200);
    }

    /**
     * Store a newly created teacher in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'required|exists:shop_brands,id',
                'teacher_name' => 'required|string|max:255',
                'teacher_avatar' => 'nullable|string|max:255',
                'teacher_description' => 'nullable|string',
                'teacher_portfolio' => 'nullable|array|max:5',
                'teacher_portfolio.*' => 'string|max:255',
                'hourly_rate' => 'required|numeric|min:0',
                'active' => 'integer|in:-1,0,1', // New field: -1: 申請中, 0: 停業中, 1: 營業中
                'sort' => 'integer|min:0', // New field: 排序
                'pinned' => 'boolean', // New field: 置頂
                'course_ids' => 'nullable|array',
                'course_ids.*' => 'exists:courses,id',
            ]);

            $courseIds = $validated['course_ids'] ?? [];
            unset($validated['course_ids']);

            $teacher = ManageTeacher::create($validated);

            if (!empty($courseIds)) {
                $teacher->courses()->sync($courseIds);
            }

            return apiResponse(201, [$teacher->load('shopBrand', 'courses')], 'Teacher created successfully', 201);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the teacher: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show($id)
    {
        $teacher = ManageTeacher::with('shopBrand', 'courses')->findOrFail($id);
        return apiResponse(200, [$teacher], 'Teacher retrieved successfully', 200);
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(Request $request, $id)
    {
        $teacher = ManageTeacher::findOrFail($id);

        try {
            $validated = $request->validate([
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'shop_brand_id' => 'sometimes|exists:shop_brands,id',
                'teacher_name' => 'sometimes|string|max:255',
                'teacher_avatar' => 'nullable|string|max:255',
                'teacher_description' => 'nullable|string',
                'teacher_portfolio' => 'nullable|array|max:5',
                'teacher_portfolio.*' => 'string|max:255',
                'hourly_rate' => 'sometimes|numeric|min:0',
                'active' => 'sometimes|integer|in:-1,0,1', // New field: -1: 申請中, 0: 停業中, 1: 營業中
                'sort' => 'sometimes|integer|min:0', // New field: 排序
                'pinned' => 'sometimes|boolean', // New field: 置頂
                'course_ids' => 'nullable|array',
                'course_ids.*' => 'exists:courses,id',
            ]);


            $courseIds = $validated['course_ids'] ?? [];
            unset($validated['course_ids']);

            $teacher->update($validated);

            if (array_key_exists('course_ids', $request->all())) {
                $syncData = [];
                $now = now();
                foreach ($courseIds as $courseId) {
                    $syncData[$courseId] = ['created_at' => $now, 'updated_at' => $now];
                }
                $teacher->courses()->sync($syncData);
            }

            $updatedTeacher = ManageTeacher::with('shopBrand', 'courses')->findOrFail($id);
            return apiResponse(200, [$updatedTeacher], 'Teacher updated successfully', 200);
        } catch (ValidationException $e) {
            return apiResponse(422, null, 'The given data was invalid: ' . json_encode($e->errors()), 422);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the teacher: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified teacher from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $teacher = ManageTeacher::findOrFail($id);
            $teacher->courses()->detach();
            $teacher->delete();

            DB::commit();

            return apiResponse(200, null, 'Teacher deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the teacher: ' . $e->getMessage(), 500);
        }
    }
}