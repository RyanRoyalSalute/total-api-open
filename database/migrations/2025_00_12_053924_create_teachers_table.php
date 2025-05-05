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
                'teacher_name' => 'Janet老師',
                'teacher_avatar' => 'storage/GOLDF/teachers/avatar_1.png',
                'teacher_description' => '台藝大雕塑系畢業。主攻雕塑與壓克力繪畫，擅長自由藝術創作。',
                'teacher_portfolio' => json_encode([
                    'storage/GOLDF/teachers/portfolio/protfolio_1_1.png',
                    'storage/GOLDF/teachers/portfolio/protfolio_1_2.png',
                ]), 
                'hourly_rate' => 2000.00,
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
                'teacher_name' => 'Sophia老師',
                'teacher_avatar' => 'storage/GOLDF/teachers/avatar_2.png',
                'teacher_description' => '百萬人氣部落客，擅長將繪畫過程結合手作茶點 並讓學員有賓至如歸的舒壓感。',
                'teacher_portfolio' => json_encode([
                    'storage/GOLDF/teachers/portfolio/protfolio_2_1.png',
                ]), 
                'hourly_rate' => 3000.00,
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
                'teacher_name' => '朵朵老師',
                'teacher_avatar' => 'storage/GOLDF/teachers/avatar_3.png',
                'teacher_description' => '以色彩及數字能量，結合流體繪畫，幫助學員們從流動美學中自我療癒及提升磁場，並利用數字密碼和易經讓空間營造色彩能量美學。',
                'teacher_portfolio' => json_encode([
                    'storage/GOLDF/teachers/portfolio/protfolio_3_1.png',
                ]), 
                'hourly_rate' => 2000.00,
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
                'teacher_name' => '彩勤老師',
                'teacher_avatar' => 'storage/GOLDF/teachers/avatar_4.png',
                'teacher_description' => '邱彩勤老師畢業於東京設計師學院，經營個人美術創意工作室15年，彩勤老師入圍2021台灣粉彩藝術公開賽展出/台北市藝文中心，及台北粉彩畫協會傑出會員聯展 / 桃園文化局，目前為金羽毛流體畫師資班主授課老師；目前致力推廣流體畫課程及粉彩畫作提倡。',
                'teacher_portfolio' => json_encode([
                    'storage/GOLDF/teachers/portfolio/protfolio_4_1.png',
                ]), 
                'hourly_rate' => 4000.00,
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
