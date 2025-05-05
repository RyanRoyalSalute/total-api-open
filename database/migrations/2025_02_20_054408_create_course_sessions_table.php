<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateCourseSessionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->date('session_date'); // Specific date of the session
            $table->string('start_time', 5); // Start time of the session (HH:MM)
            $table->string('end_time', 5);   // End time of the session (HH:MM)
            $table->integer('available_seats')->default(0); // Total seats still open for booking
            $table->integer('booked_seats')->default(0);    // Seats confirmed and paid
            $table->integer('blocked_seats')->default(0);   // Seats reserved but not yet paid
            $table->boolean('is_active')->default(true);    // Session status
            $table->timestamps();

            // Unique constraint to prevent duplicate sessions
            $table->unique(['course_id', 'session_date', 'start_time']);
        });

        // Seed demo data based on courses and course_classroom tables
        $this->seedDemoData();
    }

    private function seedDemoData(): void
    {
        $courses = DB::table('courses')->get();
        $courseClassrooms = DB::table('course_classroom')->get()->groupBy('course_id');
        $classrooms = DB::table('classrooms')->pluck('full_seats', 'id')->toArray();

        foreach ($courses as $course) {
            $courseDates = json_decode($course->course_dates, true);
            $courseTimes = json_decode($course->course_times, true) ?? [];
            if (empty($courseDates) || empty($courseTimes)) {
                continue;
            }

            // Get associated classroom(s) for this course
            $associatedClassrooms = $courseClassrooms[$course->id] ?? collect([]);
            if ($associatedClassrooms->isEmpty()) {
                continue; // Skip if no classroom is associated
            }

            // Use the first associated classroom for simplicity
            $classroomId = $associatedClassrooms->first()->classroom_id;
            $defaultSeats = $classrooms[$classroomId] ?? 10; // Use classroom's full_seats or default to 10

            $today = Carbon::now();
            $sessionsToInsert = [];

            // Check if dates are specific (YYYY-MM-DD) or recurring weekdays (1-7)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $courseDates[0])) {
                // Specific dates: use all provided dates that are today or future
                foreach ($courseDates as $date) {
                    if (Carbon::parse($date)->gte($today)) {
                        $sessionsToInsert[] = $date;
                    }
                }
            } else {
                // Recurring weekdays: generate next occurrence for each weekday
                foreach ($courseDates as $weekday) {
                    $nextDate = $this->getNextWeekdayDate($weekday, $today);
                    $sessionsToInsert[] = $nextDate;
                    $today = Carbon::parse($nextDate); // Move forward to avoid overlap
                }
            }

            foreach ($sessionsToInsert as $date) {
                foreach ($courseTimes as $startTime) {
                    // Calculate end time based on period
                    $start = Carbon::parse($startTime);
                    $end = $start->copy()->addMinutes($course->period);

                    DB::table('course_sessions')->insert([
                        'course_id' => $course->id,
                        'classroom_id' => $classroomId,
                        'session_date' => $date,
                        'start_time' => $start->format('H:i'), // HH:MM format
                        'end_time' => $end->format('H:i'),     // HH:MM format
                        'available_seats' => $defaultSeats,
                        'booked_seats' => 0,
                        'blocked_seats' => 0,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function getNextWeekdayDate(int $weekday, Carbon $startDate): string
    {
        $weekday = $weekday == 7 ? 0 : $weekday; // Convert Sunday (7) to 0
        $daysToAdd = ($weekday - $startDate->dayOfWeek + 7) % 7;
        if ($daysToAdd == 0) {
            $daysToAdd = 7; // If today is the day, move to next week
        }
        return $startDate->copy()->addDays($daysToAdd)->format('Y-m-d');
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sessions');
    }
}