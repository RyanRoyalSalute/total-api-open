<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFeedbacksTable extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('course_id')->constrained('courses'); // 隸屬課程Id
            $table->foreignId('user_id')->constrained('users'); // 隸屬學員Id
            $table->date('latest_feedback_date'); // 最新反饋日期
            $table->text('feedback_text')->nullable(); // 反饋文字
            $table->json('feedback_images')->nullable(); // 上傳花絮 (1~5 複數) URLs in the format: storage/GOLDF/feedback/fb[feedback pkey]_[1-5].png
            $table->integer('sort')->default(0); // 排序
            $table->integer('status')->default(1); // -1:隱藏, 1:置頂, 0正常
            $table->timestamps(); // created_at and updated_at
        });

        DB::statement('ALTER TABLE feedbacks AUTO_INCREMENT = 10000000;');

        // Insert sample feedback records with image URLs using the "fb" prefix
        DB::table('feedbacks')->insert([
            [
                'created_by' => 0,
                'updated_by' => 0,
                'course_id' => 1,
                'user_id' => 1,
                'latest_feedback_date' => '2025-01-01',
                'feedback_text' => '一直都是看著小朋友作流體熊，今天終於輪到自己做了。感謝有這個機會用這麼優質的顏料，大家都應該來看看這個顏料公司真的很厲害，通過許多環境友善的高階認證，連世界知名運動品牌都採用。提供體驗用的顏料有好幾十種，真的很容易找到自己喜歡的搭配，最重要的是竟然還有螢光和夜光（而且還是環保的）。期待收到作品的那一天。小朋友都等不及了，一直吵著說還要再來。（除了流體熊還能作畫和其他的，各種可愛的玩偶，甚至還有能做成某瑞典國寶汽車公司的鹿）',
                'feedback_images' => json_encode([
                    'storage/GOLDF/feedback/fb1_1.png',
                    'storage/GOLDF/feedback/fb1_2.png',
                    'storage/GOLDF/feedback/fb1_3.png',
                    'storage/GOLDF/feedback/fb1_4.png',
                    'storage/GOLDF/feedback/fb1_5.png'
                ]),
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'course_id' => 1,
                'user_id' => 2,
                'latest_feedback_date' => '2025-01-10',
                'feedback_text' => '好療癒～大人小孩都喜歡的好地方，老闆講解清晰，如果可以一定要來試試看最特別螢光色系～～',
                'feedback_images' => json_encode([
                    'storage/GOLDF/feedback/fb2_1.png',
                    'storage/GOLDF/feedback/fb2_2.png'
                ]),
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'course_id' => 1,
                'user_id' => 3,
                'latest_feedback_date' => '2025-01-20',
                'feedback_text' => '謝謝老闆細心、耐心的教學，剛滿四歲的小孩玩得不亦樂乎！下次再帶弟弟來挑戰：）',
                'feedback_images' => json_encode([
                    'storage/GOLDF/feedback/fb3_1.png'
                ]),
                'sort' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
}
