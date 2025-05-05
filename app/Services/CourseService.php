<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CourseService
{
    /**
     * Get the total full seats for a given course ID using the course_classroom pivot table.
     *
     * @param int $courseId
     * @return int
     */
    public function getTotalFullSeats(int $courseId): int
    {
        $course = DB::table('courses')->where('id', $courseId)->first();

        if (!$course) {
            return 0;
        }

        $classroomIds = DB::table('course_classroom')
            ->where('course_id', $courseId)
            ->pluck('classroom_id')
            ->toArray();

        if (empty($classroomIds)) {
            return 0;
        }

        $totalFullSeats = DB::table('classrooms')
            ->whereIn('id', $classroomIds)
            ->sum('full_seats');

        return (int) $totalFullSeats;
    }

    /**
     * Get the remaining seats for a course on a specific date and time using course_sessions.
     *
     * @param int $courseId
     * @param string $date
     * @param string $time
     * @return int
     */
    public function getRemainingSeats(int $courseId, string $date, string $time): int
    {
        $session = DB::table('course_sessions')
            ->where('course_id', $courseId)
            ->where('session_date', $date)
            ->where('start_time', $time)
            ->first();

        if (!$session) {
            if ($this->isValidCourseSchedule($courseId, $date, $time)) {
                $classroom = DB::table('course_classroom')
                    ->where('course_id', $courseId)
                    ->first();

                if ($classroom) {
                    $fullSeats = DB::table('classrooms')
                        ->where('id', $classroom->classroom_id)
                        ->value('full_seats') ?? 10;

                    $course = DB::table('courses')->where('id', $courseId)->first();
                    $start = Carbon::parse($time);
                    $end = $start->copy()->addMinutes($course->period);

                    DB::table('course_sessions')->insert([
                        'course_id' => $courseId,
                        'classroom_id' => $classroom->classroom_id,
                        'session_date' => $date,
                        'start_time' => $time,
                        'end_time' => $end->format('H:i'),
                        'available_seats' => $fullSeats,
                        'booked_seats' => 0,
                        'blocked_seats' => 0,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return $fullSeats;
                }
            }
            return 0;
        }

        return max(0, $session->available_seats - $session->booked_seats - $session->blocked_seats);
    }

    /**
     * Validate if the given date and time are part of the course schedule.
     *
     * @param int $courseId
     * @param string $date
     * @param string $time
     * @return bool
     */
    public function isValidCourseSchedule(int $courseId, string $date, string $time): bool
    {
        $course = DB::table('courses')->where('id', $courseId)->first();

        if (!$course) {
            return false;
        }

        $courseDates = json_decode($course->course_dates, true) ?? [];
        $courseTimes = json_decode($course->course_times, true) ?? [];

        if (empty($courseDates) || empty($courseTimes)) {
            return false;
        }

        if (!in_array($time, $courseTimes)) {
            return false;
        }

        $firstDate = $courseDates[0] ?? '';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $firstDate)) {
            return in_array($date, $courseDates);
        } else {
            $requestWeekday = (int) Carbon::parse($date)->dayOfWeekIso;
            foreach ($courseDates as $weekday) {
                if (!is_numeric($weekday) || $weekday < 1 || $weekday > 7) {
                    continue;
                }
                if ((int) $weekday === $requestWeekday) {
                    return true;
                }
            }
            return false;
        }
    }
}