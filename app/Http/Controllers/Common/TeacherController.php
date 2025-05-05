<?php
namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Get teacher details by teacher_id (single or multiple).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeacherInfo(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required',
        ]);

        $teacherIdInput = $request->input('teacher_id');
        $query = DB::table('teachers')
            ->select(
                'id',
                'teacher_name',
                'teacher_avatar',
                'teacher_description',
                'teacher_portfolio',
                'hourly_rate',
                'shop_brand_id'
            );

        if (is_array($teacherIdInput)) {
            $teacherIds = array_filter($teacherIdInput, 'is_numeric');
            if (empty($teacherIds)) {
                return apiResponse(400, null, 'Invalid teacher IDs provided.');
            }
            $teacherInfo = $query->whereIn('id', $teacherIds)->get();
        } elseif (is_string($teacherIdInput)) {
            $cleanedInput = str_replace(['[', ']'], '', $teacherIdInput);
            $teacherIds = array_filter(explode(',', $cleanedInput), 'is_numeric');

            if (empty($teacherIds)) {
                if (is_numeric($teacherIdInput)) {
                    $teacherInfo = $query->where('id', $teacherIdInput)->get();
                } else {
                    return apiResponse(400, null, 'Invalid teacher ID format.');
                }
            } else {
                $teacherInfo = $query->whereIn('id', $teacherIds)->get();
            }
        } else {
            return apiResponse(400, null, 'Invalid teacher_id format.');
        }

        if ($teacherInfo->isEmpty()) {
            return apiResponse(404, null, 'No teachers found for the provided IDs.');
        }

        $teacherInfo = $teacherInfo->map(function ($teacher) {
            $teacher->teacher_portfolio = json_decode($teacher->teacher_portfolio, true);
            return $teacher;
        });

        return apiResponse(2000, $teacherInfo->toArray(), '');
    }
}