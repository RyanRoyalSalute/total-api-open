<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTeachersTable extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('shop_brand_id')->constrained('shop_brands'); // 隸屬店頭(品牌)Id
            $table->string('teacher_name'); // 老師名稱
            $table->string('teacher_avatar')->nullable(); // 老師頭像
            $table->text('teacher_description')->nullable(); // 老師介紹內文
            $table->json('teacher_portfolio')->nullable(); // 老師作品 (1~5 複數)
            $table->decimal('hourly_rate', 8, 2); // 老師鐘點費
            $table->integer('active')->default(1); // -1: 申請中, 0: 停業中, 1:營業中
            $table->integer('sort')->default(0); // 排序
            $table->boolean('pinned')->default(false); // 置頂
            $table->timestamps(); // created_at and updated_at
        });


        DB::statement('ALTER TABLE teachers AUTO_INCREMENT = 2501;');

        // Insert provided data with proper field mapping
        DB::table('teachers')->insert([
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'teacher_name' => '貝斯特 育成顧問',
                'teacher_avatar' => 'storage/Saabisu/teachers/avatar_1.png',
                'teacher_description' => '幫助品牌從策略定位到市場落地，確保品牌不只是好看，而是能吸引顧客、提升銷售、創造市場影響力！',
                'teacher_portfolio' => json_encode([]), 
                'hourly_rate' => 4000.00,
                'active' => 1,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'teacher_name' => '尚玄 創投顧問',
                'teacher_avatar' => 'storage/Saabisu/teachers/avatar_2.png',
                'teacher_description' => '光服務與產品還不夠，品牌形象才是市場記憶的關鍵。我們將核心與視覺形象相輔相成，提升品牌辨識度，讓你的品牌在市場中脫穎而出。',
                'teacher_portfolio' => json_encode([]), 
                'hourly_rate' => 5000.00,
                'active' => 1,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'teacher_name' => '富達 投資顧問',
                'teacher_avatar' => 'storage/Saabisu/teachers/avatar_3.png',
                'teacher_description' => '市場最大挑戰是讓消費者接受你。透過市場研究、品牌溝通與行銷推廣，確保你的品牌能夠被目標客群看見、認可，並成功轉換。',
                'teacher_portfolio' => json_encode([]), 
                'hourly_rate' => 6000.00,
                'active' => 1,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
}
