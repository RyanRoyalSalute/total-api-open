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
            ['sub_store_id' => 1, 'classroom_id' => 1, 'created_at' => now(), 'updated_at' => now()], // 桃園富國總店 -> 富國3F團體教室
            ['sub_store_id' => 1, 'classroom_id' => 2, 'created_at' => now(), 'updated_at' => now()], // 桃園富國總店 -> 富國2F教室
            ['sub_store_id' => 2, 'classroom_id' => 3, 'created_at' => now(), 'updated_at' => now()], // 桃園快閃店 -> 市集教室
            ['sub_store_id' => 3, 'classroom_id' => 4, 'created_at' => now(), 'updated_at' => now()], // 桃園市民活動店 -> 桃園戶政事務所-市民活動教室
            // Example of shared classroom (optional, adjust as needed)
            ['sub_store_id' => 3, 'classroom_id' => 1, 'created_at' => now(), 'updated_at' => now()], // 桃園市民活動店 -> 富國3F團體教室 (shared)
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_sub_store');
    }
}