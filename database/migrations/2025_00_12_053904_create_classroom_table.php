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
                'classroom_name' => '富國3F團體教室',
                'classroom_address' => '台北市中山區富國路123號3樓',
                'classroom_images' => json_encode(['storage/GOLDF/classroom/classroom_1_1.png', 'storage/GOLDF/classroom/classroom_1_2.png']),
                'start_seats' => 10,
                'full_seats' => 90,
                'hour_costs' => 4000.00,
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'classroom_name' => '富國2F教室',
                'classroom_address' => '台北市中山區富國路123號2樓',
                'classroom_images' => json_encode(['storage/GOLDF/classroom/classroom_2_1.png']),
                'start_seats' => 2,
                'full_seats' => 20,
                'hour_costs' => 2000.00,
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'classroom_name' => '市集教室',
                'classroom_address' => '台北市中正區市場街456號',
                'classroom_images' => json_encode(['storage/GOLDF/classroom/classroom_3_1.png']),
                'start_seats' => 2,
                'full_seats' => 5,
                'hour_costs' => 1000.00,
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'classroom_name' => '桃園戶政事務所-市民活動教室',
                'classroom_address' => '桃園市桃園區市政路789號',
                'classroom_images' => json_encode(['storage/GOLDF/classroom/classroom_4_1.png']),
                'start_seats' => 10,
                'full_seats' => 40,
                'hour_costs' => 2000.00,
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