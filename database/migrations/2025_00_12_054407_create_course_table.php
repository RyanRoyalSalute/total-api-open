<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCourseTable extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // 編碼Id - Unique identifier for the course
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('shop_brand_id')->constrained('shop_brands'); // 隸屬店頭(品牌)Id
            $table->string('course_name'); // 課程名稱
            $table->text('course_description')->nullable(); // 課程介紹
            $table->json('course_images')->nullable(); // 課程主圖
            $table->json('course_tab')->nullable(); // 分類標籤
            $table->json('course_colors')->nullable(); // 課程卡主色
            $table->json('course_dates')->nullable(); // 開課日期
            $table->json('course_times')->nullable(); // 開課時間
            $table->integer('period')->default(60); // 課程時長(分)
            $table->foreignId('course_price_id')->nullable()->constrained('course_prices'); // 售價Id
            $table->json('material_id')->nullable(); // 教材商品Id (changed to JSON to support multiple values)
            $table->boolean('on_sale')->default(true); // 販售開關
            $table->integer('sort')->default(0); // 排序
            $table->boolean('pinned')->default(false); // 置頂
            
            $table->timestamps();
        });

        DB::table('courses')->insert([
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'course_name' => '尊榮流體熊體驗班',
                'course_description' => '',
                'course_images' => json_encode([
                    'storage/GOLDF/courses/course_1_1.png',
                    'storage/GOLDF/courses/course_1_2.png',
                    'storage/GOLDF/courses/course_1_3.png',
                    'storage/GOLDF/courses/course_1_4.png'
                ]),
                'course_tab' => json_encode(['桃園富國']),
                'course_colors' => json_encode(['#FBE7C6', '#FBC740']),
                'course_dates' => json_encode(['1', '2', '4']),
                'course_times' => json_encode(['16:00']),
                'period' => 60,
                'sort' => 0,
                'course_price_id' => 5,
                'material_id' => json_encode([]), // Empty array for no materials
                'on_sale' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'course_name' => '下午茶遇到流體藝術',
                'course_description' => '',
                'course_images' => json_encode([
                    'storage/GOLDF/courses/course_2_1.png',
                    'storage/GOLDF/courses/course_2_2.png',
                    'storage/GOLDF/courses/course_2_3.png',
                    'storage/GOLDF/courses/course_2_4.png'
                ]),
                'course_tab' => json_encode(['桃園富國']),
                'course_colors' => json_encode(['#FBE7C6', '#FBC740']),
                'course_dates' => json_encode(['5']),
                'course_times' => json_encode(['15:00']),
                'period' => 60,
                'course_price_id' => 6,
                'material_id' => json_encode([]), // Empty array for no materials
                'on_sale' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'course_name' => '流體藝術師資班',
                'course_description' => '',
                'course_images' => json_encode([
                    'storage/GOLDF/courses/course_3_1.png',
                    'storage/GOLDF/courses/course_3_2.png',
                    'storage/GOLDF/courses/course_3_3.png',
                    'storage/GOLDF/courses/course_3_4.png'
                ]),
                'course_tab' => json_encode(['桃園富國']),
                'course_colors' => json_encode(['#FBE7C6', '#FBC740']),
                'course_dates' => json_encode(['3']),
                'course_times' => json_encode(['14:00', '16:00']),
                'period' => 60,
                'course_price_id' => 1,
                'material_id' => json_encode([]), // Empty array for no materials
                'on_sale' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'course_name' => '周末流體熊',
                'course_description' => '',
                'course_images' => json_encode([
                    'storage/GOLDF/courses/course_4_1.png',
                    'storage/GOLDF/courses/course_4_2.png',
                    'storage/GOLDF/courses/course_4_3.png',
                    'storage/GOLDF/courses/course_4_4.png'
                ]),
                'course_tab' => json_encode(['桃園富國']),
                'course_colors' => json_encode(['#FBE7C6', '#FBC740']),
                'course_dates' => json_encode(['6']),
                'course_times' => json_encode(['13:00']),
                'period' => 60,
                'course_price_id' => 1,
                'material_id' => json_encode([]), // Empty array for no materials
                'on_sale' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'course_name' => '陽光親子流體熊',
                'course_description' => '',
                'course_images' => json_encode([
                    'storage/GOLDF/courses/course_5_1.png',
                    'storage/GOLDF/courses/course_5_2.png',
                    'storage/GOLDF/courses/course_5_3.png',
                    'storage/GOLDF/courses/course_5_4.png'
                ]),
                'course_tab' => json_encode(['社區中心']),
                'course_colors' => json_encode(['#B4F8C8', '#638C80']),
                'course_dates' => json_encode(['7']),
                'course_times' => json_encode(['10:00']),
                'period' => 60,
                'course_price_id' => 1,
                'material_id' => json_encode([]), // Empty array for no materials
                'on_sale' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'course_name' => '陽光親子流體版畫',
                'course_description' => '',
                'course_images' => json_encode([
                    'storage/GOLDF/courses/course_6_1.png',
                    'storage/GOLDF/courses/course_6_2.png',
                    'storage/GOLDF/courses/course_6_3.png',
                    'storage/GOLDF/courses/course_6_4.png'
                ]),
                'course_tab' => json_encode(['社區中心']),
                'course_colors' => json_encode(['#A0E7E5', '#FFC2C7']),
                'course_dates' => json_encode(['7']),
                'course_times' => json_encode(['14:00']),
                'period' => 60,
                'course_price_id' => 1,
                'material_id' => json_encode([]), // Empty array for no materials
                'on_sale' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'course_name' => '蛇年來聚市-雙人流體熊快閃',
                'course_description' => '2025年與無邊境市集再次相聚，在中原文創園區喜迎農曆新年，打造喜慶吉祥的年節氛圍，小文青年貨市集，體驗流體熊不一樣的年貨小物，蛇年來聚市，喜迎蛇年！',
                'course_images' => json_encode([
                    'storage/GOLDF/courses/course_7_1.png',
                    'storage/GOLDF/courses/course_7_2.png',
                    'storage/GOLDF/courses/course_7_3.png',
                    'storage/GOLDF/courses/course_7_4.png'
                ]),
                'course_tab' => json_encode(['快閃']),
                'course_colors' => json_encode(['#FFAEBC', '#EF7C8E']),
                'course_dates' => json_encode(['2025-02-25', '2025-03-26', '2025-12-25']),
                'course_times' => json_encode(["11:00", "12:00"]),
                'period' => 60,
                'course_price_id' => 1,
                'material_id' => json_encode([4]), // Single material ID in array
                'on_sale' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
}