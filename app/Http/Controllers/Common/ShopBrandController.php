<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Models\ShopBrand;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class ShopBrandController extends Controller
{
    /**
     * Fetch shop brand by Code.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBrandByCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $code = $request->input('code');
        if (empty($code)) {
            return apiResponse(4001, null, 'Code is required.', 400);
        }

        $brand = ShopBrand::where('brand_code', $code)
            ->leftJoin('sub_store', 'shop_brands.id', '=', 'sub_store.shop_brand_id')
            ->select(
                'shop_brands.brand_name',
                'shop_brands.brand_logo',
                'shop_brands.brand_background',
                DB::raw('MIN(sub_store.sub_store_name) as sub_store_name'),
                DB::raw('MIN(sub_store.sub_store_address) as sub_store_address')
            )
            ->groupBy('shop_brands.id', 'shop_brands.brand_name', 'shop_brands.brand_logo', 'shop_brands.brand_background')
            ->first();

        if (!$brand) {
            return apiResponse(4041, null, 'Brand not found.', 404);
        }

        return apiResponse(2000, [
            'brand_name' => $brand->brand_name,
            'brand_logo' => $brand->brand_logo,
            'brand_background' => $brand->brand_background,
            'sub_store_name' => $brand->sub_store_name ?? '',
            'sub_store_address' => $brand->sub_store_address ?? ''
        ], 'Brand details fetched successfully.', 200);
    }

    /**
     * Get Shop Details by Brand Code.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopCourses(Request $request): \Illuminate\Http\JsonResponse
    {
        $brandCode = $request->input('code');
        if (empty($brandCode)) {
            return apiResponse(4001, null, 'Code is required.', 400);
        }

        // Fetch shop brand with details using pivot tables
        $courses = DB::table('shop_brands')
            ->where('shop_brands.brand_code', $brandCode)
            ->leftJoin('sub_store', 'shop_brands.id', '=', 'sub_store.shop_brand_id')
            ->leftJoin('course_sub_store', 'sub_store.id', '=', 'course_sub_store.sub_store_id')
            ->leftJoin('courses', 'course_sub_store.course_id', '=', 'courses.id')
            ->leftJoin('course_prices', 'courses.course_price_id', '=', 'course_prices.id')
            ->leftJoin('recharges', 'course_prices.recharge_id', '=', 'recharges.id')
            ->where('courses.on_sale', 1)
            ->select(
                'shop_brands.brand_name as brand_name',
                'shop_brands.brand_logo as brand_logo',
                'shop_brands.brand_background as brand_background',
                'sub_store.id as sub_id',
                'sub_store.sub_store_name as sub_store_name',
                'sub_store.sub_store_address as sub_store_address',
                'courses.id as course_id',
                'courses.course_name as course_name',
                'courses.course_colors as course_colors',
                'courses.course_images as course_images',
                'courses.course_tab as course_tab',
                'courses.course_description as course_description',
                'courses.material_id as material_id',
                'courses.course_dates as course_dates',
                'courses.course_times as course_times',
                'course_prices.original_price as original_price',
                'course_prices.discount_price as discount_price',
                'course_prices.early_bird_price as early_bird_price',
                'recharges.id as recharges_id',
                'recharges.recharge_amount as recharge_amount',
                'recharges.free_count as free_count',
                'recharges.active as recharge_active',
            )
            ->distinct()
            ->get();

        if ($courses->isEmpty()) {
            return apiResponse(4041, null, 'No details found for the provided brand code.', 404);
        }

        // Fetch teachers separately and group them by course_id
        $teachers = DB::table('course_teacher')
            ->join('teachers', 'course_teacher.teacher_id', '=', 'teachers.id')
            ->whereIn('course_teacher.course_id', $courses->pluck('course_id'))
            ->select(
                'course_teacher.course_id',
                'teachers.id as teacher_id',
                'teachers.teacher_name as teacher_name',
                'teachers.teacher_avatar as teacher_avatar',
                'teachers.teacher_description as teacher_description',
                'teachers.teacher_portfolio as teacher_portfolio'
            )
            ->get()
            ->groupBy('course_id');

        $today = Carbon::today(); // Get today's date

        // Process course dates, times, colors, and material_id
        foreach ($courses as $course) {
            $courseDates = json_decode($course->course_dates, true);
            $courseTimes = json_decode($course->course_times, true) ?? [];
            $courseColors = json_decode($course->course_colors, true) ?? [];
            $materialIds = json_decode($course->material_id, true) ?? [];
            $courseTabs = json_decode($course-> course_tab, true) ?? [];

            $course->material_id = array_map('intval', $materialIds);

            if (!empty($courseTimes) && is_array($courseTimes)) {
                sort($courseTimes);
            }
            $course->course_times = $courseTimes;
            $course->course_colors = $courseColors;
            $course->course_tab = $courseTabs;

            // Add teachers as an array with specified fields only
            $course->teachers = $teachers->get($course->course_id, collect())->map(function ($teacher) {
                return [
                    'teacher_id' => $teacher->teacher_id,
                    'teacher_name' => $teacher->teacher_name,
                    'teacher_avatar' => $teacher->teacher_avatar,
                    'teacher_description' => $teacher->teacher_description,
                    'teacher_portfolio' => $teacher->teacher_portfolio,
                ];
            })->values()->all();

            if (!empty($courseDates) && is_array($courseDates)) {
                if (isset($courseDates[0]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $courseDates[0])) {
                    // Specific dates: Filter out past dates
                    $course->course_dates = array_filter($courseDates, function ($date) use ($today) {
                        return Carbon::parse($date)->gte($today);
                    });
                    $course->course_dates = array_values($course->course_dates); // Reindex array
                } else {
                    // Recurring weekdays: Generate future dates only
                    $weekdays = is_array($courseDates) ? $courseDates : [];
                    if (!empty($weekdays)) {
                        $course->course_dates = $this->generateDatesForWeekdays($weekdays, 8, $today);
                    } else {
                        $course->course_dates = [];
                    }
                }

                // Calculate is_early for each date
                $course->is_early = array_map(function ($date) use ($today) {
                    $dateTimestamp = strtotime($date);
                    $todayTimestamp = strtotime('today');
                    $daysDiff = ($dateTimestamp - $todayTimestamp) / (60 * 60 * 24);
                    return ($daysDiff > 30) ? 1 : 0;
                }, $course->course_dates);
            } else {
                $course->course_dates = [];
                $course->is_early = [];
            }
        }

        // Sort courses by the first date
        $coursesArray = $courses->toArray();
        usort($coursesArray, function ($a, $b) {
            $aDate = !empty($a->course_dates) ? strtotime($a->course_dates[0]) : PHP_INT_MAX;
            $bDate = !empty($b->course_dates) ? strtotime($b->course_dates[0]) : PHP_INT_MAX;
            return $aDate - $bDate;
        });

        return apiResponse(2000, $coursesArray, 'Shop details retrieved successfully.', 200);
    }

    /**
     * Generate the next N dates based on the given weekdays, starting from a specific date.
     *
     * @param array $weekdays
     * @param int $numOfDates
     * @param ?Carbon $startDate
     * @return array
     */
    private function generateDatesForWeekdays(array $weekdays, int $numOfDates, ?Carbon $startDate = null): array
    {
        $dates = [];
        $today = $startDate ?? Carbon::now();

        while (count($dates) < $numOfDates) {
            foreach ($weekdays as $weekday) {
                if (count($dates) >= $numOfDates) break;

                $nextDate = $this->getNextWeekdayDate($weekday, $today);
                if (Carbon::parse($nextDate)->gte(Carbon::today())) {
                    $dates[] = $nextDate;
                }
                $today = Carbon::parse($nextDate);
            }
        }

        return $dates;
    }

    /**
     * Get the next date for a given weekday (1 = Monday, 2 = Tuesday, etc.)
     *
     * @param int $weekday
     * @param Carbon $startDate
     * @return string
     */
    private function getNextWeekdayDate(int $weekday, Carbon $startDate): string
    {
        $weekday = $weekday == 7 ? 0 : $weekday; // Convert Sunday (7) to 0
        $daysToAdd = ($weekday - $startDate->dayOfWeek + 7) % 7;
        if ($daysToAdd == 0) {
            $daysToAdd = 7; // Move to next week if today is the target day
        }

        $date = $startDate->copy()->addDays($daysToAdd);
        return $date->format('Y-m-d');
    }
}
