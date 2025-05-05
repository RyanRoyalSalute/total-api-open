<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCourseTeacherTable extends Migration
{
    public function up(): void
    {
        Schema::create('course_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['course_id', 'teacher_id']);
        });

        DB::table('course_teacher')->insert([
            ['course_id' => 1, 'teacher_id' => 2501, 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => 2, 'teacher_id' => 2502, 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => 3, 'teacher_id' => 2504, 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => 4, 'teacher_id' => 2501, 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => 5, 'teacher_id' => 2503, 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => 6, 'teacher_id' => 2503, 'created_at' => now(), 'updated_at' => now()],
            ['course_id' => 7, 'teacher_id' => 2503, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('course_teacher');
    }
}