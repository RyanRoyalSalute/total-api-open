<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSubStoreTable extends Migration
{
    public function up(): void
    {
        Schema::create('sub_store', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('shop_brand_id')->constrained('shop_brands'); // 隸屬店頭(品牌)Id
            $table->string('sub_store_name'); // 分店名稱
            $table->string('sub_store_address'); // 分店地址
            $table->string('line_chat_id')->nullable(); // [LINE] 群對應Id - 新增欄位
            $table->boolean('active')->default(true); // 營業中
            $table->integer('sort')->default(0); // 排序
            $table->boolean('pinned')->default(false); // 置頂
            $table->timestamps(); // created_at and updated_at
        });

        DB::table('sub_store')->insert([
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'shop_brand_id' => 1,
                'sub_store_name' => 'LINE快訂',
                'sub_store_address' => '快速打造您的本地品牌服務',
                'line_chat_id' => '@Saabisu',
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
        Schema::dropIfExists('sub_store');
    }
}