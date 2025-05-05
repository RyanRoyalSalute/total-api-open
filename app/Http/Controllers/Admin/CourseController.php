<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManageCourse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Process course dates by removing duplicates and sorting
     * Handles either weekdays (1-7) or specific dates (Y-m-d), not both
     *
     * @param array|null $dates
     * @return array|null
     */
    private function processCourseDates($dates)
    {
        if (isset($dates) && !empty($dates)) {
            $isWeekdayType = is_numeric($dates[0]) && (int)$dates[0] >= 1 && (int)$dates[0] <= 7;

            $processed = collect($dates)->map(function ($date) {
                return is_int($date) ? (string)$date : $date;
            })->unique()->sort(function ($a, $b) use ($isWeekdayType) {
                if ($isWeekdayType) {
                    return (int)$a <=> (int)$b;
                }
                return strcmp($a, $b);
            })->values()->all();

            return $processed;
        }
        return $dates;
    }

    /**
     * Process material IDs by converting to integers and removing duplicates
     *
     * @param array|null $materialIds
     * @return array|null
     */
    private function processMaterialIds($materialIds)
    {
        if (isset($materialIds) && !empty($materialIds)) {
            $processed = collect($materialIds)
                ->map(function ($id) {
                    return (int)$id; // Ensure integer values
                })
                ->unique()
                ->values()
                ->all();

            return $processed;
        }
        return $materialIds;
    }

    /**
     * Display a listing of all courses, sorted by 'sort' column.
     */
    public function index()
    {
        $courses = ManageCourse::with('shopBrand', 'subStores', 'teachers', 'classrooms', 'coursePrice')
            ->orderBy('sort', 'asc')
            ->get();
        return apiResponse(200, $courses->toArray(), 'Courses retrieved successfully', 200);
    }

    /**
     * Store a newly created course in storage
     */
    public function store(CourseRequest $request)
    {
        try {
            $validated = $request->validated();

            // Process course dates
            $validated['course_dates'] = $this->processCourseDates($validated['course_dates'] ?? null);

            // Process material IDs
            $validated['material_id'] = $this->processMaterialIds($validated['material_id'] ?? null);

            // Extract relationship IDs
            $subStoreIds = $validated['sub_store_ids'] ?? [];
            $teacherIds = $validated['teacher_ids'] ?? [];
            $classroomIds = $validated['classroom_ids'] ?? [];
            unset($validated['sub_store_ids'], $validated['teacher_ids'], $validated['classroom_ids']);

            // Create the course
            $course = ManageCourse::create($validated);

            // Sync relationships
            if (!empty($subStoreIds)) $course->subStores()->sync($subStoreIds);
            if (!empty($teacherIds)) $course->teachers()->sync($teacherIds);
            if (!empty($classroomIds)) $course->classrooms()->sync($classroomIds);

            return apiResponse(201, [$course->load('shopBrand', 'subStores', 'teachers', 'classrooms', 'coursePrice')], 'Course created successfully', 201);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while creating the course: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified course.
     */
    public function show($id)
    {
        $course = ManageCourse::with('shopBrand', 'subStores', 'teachers', 'classrooms', 'coursePrice')->findOrFail($id);
        return apiResponse(200, [$course], 'Course retrieved successfully', 200);
    }

    /**
     * Update the specified course in storage
     */
    public function update(CourseRequest $request, $id)
    {
        $course = ManageCourse::findOrFail($id);

        try {
            $validated = $request->validated();

            // Process course dates
            $validated['course_dates'] = $this->processCourseDates($validated['course_dates'] ?? null);

            // Process material IDs
            $validated['material_id'] = $this->processMaterialIds($validated['material_id'] ?? null);

            // Extract relationship IDs
            $subStoreIds = $validated['sub_store_ids'] ?? [];
            $teacherIds = $validated['teacher_ids'] ?? [];
            $classroomIds = $validated['classroom_ids'] ?? [];
            unset($validated['sub_store_ids'], $validated['teacher_ids'], $validated['classroom_ids']);

            $course->update($validated);

            $now = now();
            if (array_key_exists('sub_store_ids', $request->all())) {
                $syncData = array_fill_keys($subStoreIds, ['created_at' => $now, 'updated_at' => $now]);
                $course->subStores()->sync($syncData);
            }
            if (array_key_exists('teacher_ids', $request->all())) {
                $syncData = array_fill_keys($teacherIds, ['created_at' => $now, 'updated_at' => $now]);
                $course->teachers()->sync($syncData);
            }
            if (array_key_exists('classroom_ids', $request->all())) {
                $syncData = array_fill_keys($classroomIds, ['created_at' => $now, 'updated_at' => $now]);
                $course->classrooms()->sync($syncData);
            }

            $updatedCourse = ManageCourse::with('shopBrand', 'subStores', 'teachers', 'classrooms', 'coursePrice')->findOrFail($id);
            return apiResponse(200, [$updatedCourse], 'Course updated successfully', 200);
        } catch (\Exception $e) {
            return apiResponse(500, null, 'An error occurred while updating the course: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $course = ManageCourse::findOrFail($id);
            $course->subStores()->detach();
            $course->teachers()->detach();
            $course->classrooms()->detach();
            $course->delete();

            DB::commit();

            return apiResponse(200, null, 'Course deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return apiResponse(500, null, 'An error occurred while deleting the course: ' . $e->getMessage(), 500);
        }
    }
}