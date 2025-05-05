<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateShopBrandsTable extends Migration
{
    public function up(): void
    {
        Schema::create('shop_brands', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->string('brand_code')->unique(); // 品牌Code
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->string('brand_name'); // 店頭品牌名稱
            $table->string('brand_logo')->nullable(); // 店頭品牌圖像
            $table->string('brand_background')->nullable(); // 店頭品牌背景
            $table->boolean('teacher_permission')->default(false); // 老師申請許可
            $table->boolean('active')->default(true); // 營業中
            $table->integer('sort')->default(0); // 排序
            $table->boolean('pinned')->default(false); // 置頂
            $table->timestamps(); // created_at and updated_at
        });


        DB::table('shop_brands')->insert([
            'brand_code' => 'FLUIDART',
            'created_by' => 24002,
            'updated_by' => 24002,
            'brand_name' => '金羽毛流體藝術',
            'brand_logo' => 'storage/GOLDF/resources/shop_logo.png',
            'brand_background' => 'storage/GOLDF/resources/shop_banner.png',
            'teacher_permission' => false,
            'active' => true,
            'sort' => 0,
            'pinned' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_brands');
    }
}
