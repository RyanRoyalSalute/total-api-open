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
                'course_name' => '育成品牌(終身)',
                'course_description' => '單次購買，終生訂閱，是所有新創品牌的優先選擇，極低建置門檻，現在就建立您的變現收費頁\n• 可營業品牌數:1品牌\n• 可代理人:2人\n• 交易手續費:每筆7.5%\n• 金流服務費:1~3%\n• 提供收費上限：每天$2,000, 每月$20,000',
                'course_images' => json_encode([
                    'storage/Saabisu/courses/course_1_1.png',
                ]),
                'course_tab' => json_encode(['快訂']),
                'course_colors' => json_encode(['#D9EAD3', '#8DAE7F']),
                'course_dates' => json_encode(['1', '2', '3', '4', '5', '6', '7']),
                'course_times' => json_encode(['00:00']),
                'period' => 60,
                'sort' => 0,
                'course_price_id' => 5,
                'material_id' => json_encode([]),
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
                'course_name' => '卓越品牌訂閱',
                'course_description' => '可包月/包年訂閱，是邁向穩健品牌的最佳選擇，最大化收益，現在就建立您的變現收費頁

• 可營業品牌數:2品牌
• 可代理人:10人
• 交易手續費:每筆2.5%
• 金流服務費:1~3%
• 提供收費上限：每天$2,000, 每月$20,000',
                'course_images' => json_encode([
                    'storage/Saabisu/courses/course_2_1.png',
                ]),
                'course_tab' => json_encode(['快訂']),
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
                'course_name' => '尊榮品牌訂閱',
                'course_description' => '可包月/包年訂閱，是創造副業品牌的關鍵選擇，建立品牌組織，擴大服務區域，現在就建立您的變現收費頁

• 可營業品牌數:5品牌
• 可代理人:50人
• 交易手續費:每筆1%
• 金流服務費:1~3%
• 提供收費上限：每天$2,000, 每月$20,000',
                'course_images' => json_encode([
                    'storage/Saabisu/courses/course_3_1.png',
                ]),
                'course_tab' => json_encode(['快訂']),
                'course_colors' => json_encode(['#FBE7C6', '#FBC740']),
                'course_dates' => json_encode(['3']),
                'course_times' => json_encode(['14:00', '16:00']),
                'period' => 60,
                'course_price_id' => 1,
                'material_id' => json_encode([]), 
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
                'course_name' => '契約企業品牌',
                'course_description' => '聯繫快訂業務，共創SAABISU品牌聯盟，是本地活躍品牌的不二選擇，現在就聯繫業務，建立您的變現收費頁

• 可營業品牌數:1品牌
• 可代理人:2人
• 交易手續費:每筆7.5%
• 金流服務費:1~3%
• 提供收費上限：每天$2,000, 每月$20,000',
                'course_images' => json_encode([
                    'storage/Saabisu/courses/course_4_1.png',
                    'storage/Saabisu/courses/course_4_2.png',
                    'storage/Saabisu/courses/course_4_3.png',
                    'storage/Saabisu/courses/course_4_4.png'
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
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
}