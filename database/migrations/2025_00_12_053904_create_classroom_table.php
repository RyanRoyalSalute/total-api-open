<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateClassroomTable extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id(); // 編碼Id 
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->string('classroom_name'); // 教室名稱
            $table->string('classroom_address'); // 教室地址
            $table->json('classroom_images')->nullable(); // 教室主圖 (1~5 複數)
            $table->integer('start_seats')->default(1); // 開課位數
            $table->integer('full_seats')->default(10); // 滿課位數
            $table->decimal('hour_costs', 8, 2); // 教室鐘點費
            $table->boolean('active')->default(true); // 營業中
            $table->integer('sort')->default(0); // 排序
            $table->boolean('pinned')->default(false); // 置頂
            $table->timestamps(); // created_at and updated_at
        });

        DB::table('classrooms')->insert([
            [
                'created_by' => 0,
                'updated_by' => 0,
                'classroom_name' => '據點',
                'classroom_address' => '全台',
                'classroom_images' => json_encode(['storage\/Saabisu\/classroom\/classroom_1_1747609333.png']),
                'start_seats' => 10,
                'full_seats' => 90,
                'hour_costs' => 4000.00,
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
}