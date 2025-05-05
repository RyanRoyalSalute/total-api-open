<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRechargesTable extends Migration
{
    public function up(): void
    {
        Schema::create('recharges', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('shop_brand_id')->constrained('shop_brands'); // 隸屬店頭(品牌)Id
            $table->decimal('recharge_amount', 8, 2); // 充值金額
            $table->integer('free_count'); // 本次充值的免單數 (renamed column)
            $table->decimal('discount_rate', 3, 2); // 商品折扣
            $table->integer('privileged_days'); // 商品折扣日數
            $table->boolean('active')->default(true); // 營業中
            $table->integer('sort')->default(0); // 排序
            $table->boolean('pinned')->default(false); // 置頂
            $table->timestamps(); // created_at and updated_at
        });

        // Insert provided data into recharges table
        DB::table('recharges')->insert([
            [
                'created_by' => 0,
                'updated_by' => 0,
                'shop_brand_id' => 1,
                'recharge_amount' => 4999.00,
                'free_count' => 1,
                'discount_rate' => 0.9,
                'privileged_days' => 30,
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
                'recharge_amount' => 8888.00,
                'free_count' => 1, 
                'discount_rate' => 0.8,
                'privileged_days' => 60,
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
        Schema::dropIfExists('recharges');
    }
}
