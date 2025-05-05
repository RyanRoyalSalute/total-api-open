<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // 編碼Id (Primary Key)
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('store_id')->constrained('sub_store'); // 隸屬分駐店(倉儲)Id, now referencing sub_store table
            $table->string('product_name'); // 商品名稱
            $table->string('product_image')->nullable(); // 商品主圖
            $table->text('product_spec')->nullable(); // 商品內容規格
            $table->decimal('product_costs', 8, 2); // 教材商品零售價
            $table->timestamps(); // created_at and updated_at
        });

        // Inserting provided data into the products table
        DB::table('products')->insert([
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'store_id' => 1, // Corresponding to sub_store ID
                'product_name' => '流體熊 23cm',
                'product_image' => 'storage/GOLDF/product/product_1_1.png',
                'product_spec' => '標準白胚公仔，現場多色顏料任用',
                'product_costs' => 650.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'store_id' => 1, // Corresponding to sub_store ID
                'product_name' => '流體熊 33cm',
                'product_image' => 'storage/GOLDF/product/product_2_1.png',
                'product_spec' => '大型白胚公仔，現場多色顏料任用',
                'product_costs' => 950.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'store_id' => 1, // Corresponding to sub_store ID
                'product_name' => '流體熊 53cm',
                'product_image' => 'storage/GOLDF/product/product_3_1.png',
                'product_spec' => '巨型白胚公仔，現場多色顏料任用',
                'product_costs' => 2000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'store_id' => 1, // Corresponding to sub_store ID
                'product_name' => '雙人流體熊快閃包',
                'product_image' => 'storage/GOLDF/product/product_4_1.png',
                'product_spec' => '標準白胚公仔x2，現場多色顏料任用',
                'product_costs' => 0.00, // Product with no retail price
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'store_id' => 1, // Corresponding to sub_store ID
                'product_name' => '畫布 30cm',
                'product_image' => 'storage/GOLDF/product/product_5_1.png',
                'product_spec' => '白麻畫布，現場多色顏料任用',
                'product_costs' => 500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'store_id' => 1, // Corresponding to sub_store ID
                'product_name' => '畫布 40cm',
                'product_image' => 'storage/GOLDF/product/product_6_1.png',
                'product_spec' => '白麻畫布，現場多色顏料任用',
                'product_costs' => 800.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'store_id' => 1, // Corresponding to sub_store ID
                'product_name' => '畫布 50cm',
                'product_image' => 'storage/GOLDF/product/product_7_1.png',
                'product_spec' => '白麻畫布，現場多色顏料任用',
                'product_costs' => 1200.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 24002,
                'updated_by' => 24002,
                'store_id' => 1, // Corresponding to sub_store ID
                'product_name' => '尊榮體驗包',
                'product_image' => 'storage/GOLDF/product/product_8_1.png',
                'product_spec' => '鑰匙熊, 畫布, 流體熊，現場多色顏料任用',
                'product_costs' => 1250.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}
