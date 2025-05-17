<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateClassroomSubStoreTable extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_sub_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_store_id')->constrained('sub_store')->onDelete('cascade');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->timestamps();

            // Ensure uniqueness to prevent duplicate associations
            $table->unique(['sub_store_id', 'classroom_id']);
        });

        // Seed initial relationships based on original data
        DB::table('classroom_sub_store')->insert([
            ['sub_store_id' => 1, 'classroom_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_sub_store');
    }
}