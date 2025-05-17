<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCourseSubStoreTable extends Migration
{
    public function up(): void
    {
        Schema::create('course_sub_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('sub_store_id')->constrained('sub_store')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['course_id', 'sub_store_id']);
        });

        DB::table('course_sub_store')->insert([
            ['course_id' => 1, 'sub_store_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sub_store');
    }
}