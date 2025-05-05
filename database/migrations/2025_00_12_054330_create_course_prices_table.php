<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCoursePricesTable extends Migration
{
    public function up(): void
    {
        Schema::create('course_prices', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('shop_brand_id')->constrained('shop_brands'); // 隸屬店頭(品牌)Id
            $table->decimal('original_price', 8, 2); // 原價
            $table->decimal('discount_price', 8, 2)->nullable(); // 預購折扣價
            $table->decimal('early_bird_price', 8, 2)->nullable(); // 早鳥折扣價
            $table->enum('price_group', ['none', 'Prime']); // 價格組
            $table->foreignId('recharge_id')->nullable()->constrained('recharges'); // 免單充值Id
            $table->boolean('active')->default(true); // 營業中
            $table->integer('sort')->default(0); // 排序
            $table->boolean('pinned')->default(false); // 置頂
            $table->timestamps(); // created_at and updated_at
        });

        // Insert provided data into course_prices table
        DB::table('course_prices')->insert([
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'original_price' => 600.00,
                'discount_price' => 499.00,
                'early_bird_price' => 349.00,
                'price_group' => 'none', // Price group as 'none'
                'recharge_id' => 1, // Corresponding to Recharge-1
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'original_price' => 700.00,
                'discount_price' => 599.00,
                'early_bird_price' => 449.00,
                'price_group' => 'none', // Price group as 'none'
                'recharge_id' => 1, // Corresponding to Recharge-1
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'original_price' => 800.00,
                'discount_price' => 699.00,
                'early_bird_price' => 549.00,
                'price_group' => 'none', // Price group as 'none'
                'recharge_id' => 1, // Corresponding to Recharge-1
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'original_price' => 900.00,
                'discount_price' => 799.00,
                'early_bird_price' => 649.00,
                'price_group' => 'none', // Price group as 'none'
                'recharge_id' => 1, // Corresponding to Recharge-1
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'original_price' => 1450.00,
                'discount_price' => 1249.00,
                'early_bird_price' => 1049.00,
                'price_group' => 'Prime', // Price group as 'Prime'
                'recharge_id' => 2, // Corresponding to Recharge-2
                'active' => true,
                'sort' => 0,
                'pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'original_price' => 1650.00,
                'discount_price' => 1449.00,
                'early_bird_price' => 1249.00,
                'price_group' => 'Prime', // Price group as 'Prime'
                'recharge_id' => 2, // Corresponding to Recharge-2
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
        Schema::dropIfExists('course_prices');
    }
}
