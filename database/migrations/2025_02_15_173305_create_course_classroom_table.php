<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCourseClassroomTable extends Migration
{
    public function up(): void
    {
        Schema::create('course_classroom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['course_id', 'classroom_id']);
        });

        DB::table('course_classroom')->insert([
            ['course_id' => 1, 'classroom_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('course_classroom');
    }
}