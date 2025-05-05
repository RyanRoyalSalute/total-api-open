<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->string('mobile_phone', 15)->unique(); // 手機號碼
            $table->string('token')->nullable(); // Token
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('shop_brand_id')->nullable()->constrained('shop_brands'); // 隸屬店頭(品牌)Id
            $table->string('line_auth_code')->nullable(); // LINE授權ID (Renamed to line_auth_code)
            $table->string('country_calling_code', 5)->nullable(); // 手機國碼 (Renamed to country_calling_code)
            $table->timestamp('last_visited_at')->nullable(); // 最近造訪時間
            $table->string('avatar')->default('storage/users/default')->nullable(); // 學員頭像
            $table->string('name')->nullable(); // 姓名
            $table->string('gender')->nullable(); // 性別
            $table->date('birth_date')->nullable(); // 出生年月日 (Renamed to birth_date)
            $table->integer('age')->nullable(); // 年齡
            $table->string('address')->nullable(); // 地址
            $table->integer('current_points')->default(0); // 當前積分
            $table->decimal('discount_rate', 3, 2)->default(1.0); // 商品折扣
            $table->date('discount_expiry_date')->nullable(); // 商品折扣優待期
            $table->integer('status')->default(0); // 狀態: -1:封鎖, 0:正常, >=1: VIP
            $table->unsignedBigInteger('latest_payment_record_id')->nullable(); // 最新消費紀錄Id
            $table->unsignedBigInteger('latest_point_record_id')->nullable(); // 最新積分紀錄Id
            $table->integer('permission')->default(0); // 0: 用戶, >=1: 管理員
            $table->timestamps(); // created_at and updated_at
        });

        DB::table('users')->insert([
            [
                'mobile_phone' => '0912398712',
                'token' => 'sample_token_1',
                'created_by' => 1,
                'updated_by' => 1,
                'shop_brand_id' => 1,
                'line_auth_code' => 'line_user_001',
                'country_calling_code' => '+886',
                'last_visited_at' => now(),
                'avatar' => 'storage/GOLDF/users/default',
                'name' => 'Yuheng#咪咪 Kuan',
                'gender' => '女',
                'birth_date' => '2000-04-01',
                'age' => 24,
                'address' => '桃園市蘆竹區南山路一段55號3F',
                'current_points' => 4999,
                'discount_rate' => 0.9,
                'discount_expiry_date' => now()->addDays(30),
                'status' => 0,
                'latest_payment_record_id' => null,
                'latest_point_record_id' => null,
                'permission' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mobile_phone' => '0912398713',
                'token' => 'sample_token_2',
                'created_by' => 1,
                'updated_by' => 1,
                'shop_brand_id' => 1,
                'line_auth_code' => 'line_user_002',
                'country_calling_code' => '+886',
                'last_visited_at' => now(),
                'avatar' => 'storage/GOLDF/users/default',
                'name' => '施淳瑜',
                'gender' => '女',
                'birth_date' => '2000-04-01',
                'age' => 24,
                'address' => '桃園市蘆竹區南山路一段55號3F',
                'current_points' => 4999,
                'discount_rate' => 0.9,
                'discount_expiry_date' => now()->addDays(30),
                'status' => 0,
                'latest_payment_record_id' => null,
                'latest_point_record_id' => null,
                'permission' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mobile_phone' => '0912398714',
                'token' => 'sample_token_3',
                'created_by' => 1,
                'updated_by' => 1,
                'shop_brand_id' => 1,
                'line_auth_code' => 'line_user_003',
                'country_calling_code' => '+886',
                'last_visited_at' => now(),
                'avatar' => 'storage/GOLDF/users/0912398714_avatar.png',
                'name' => 'Wwney',
                'gender' => '女',
                'birth_date' => '2000-04-01',
                'age' => 24,
                'address' => '桃園市蘆竹區南山路一段55號3F',
                'current_points' => 4999,
                'discount_rate' => 0.9,
                'discount_expiry_date' => now()->addDays(30),
                'status' => 0,
                'latest_payment_record_id' => null,
                'latest_point_record_id' => null,
                'permission' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mobile_phone' => '0938189829',
                'token' => 'FvTdrlBpB9qlD2X1NT9ePVaP2GZcIULtV1d67at3zlidOU08vp7YQW6bkYrt',
                'created_by' => 2204,
                'updated_by' => 2204,
                'shop_brand_id' => 1,
                'line_auth_code' => 'undefined',
                'country_calling_code' => '+886',
                'last_visited_at' => '2025-03-10 06:03:08',
                'avatar' => 'storage/GOLDF/users/0938189829_avatar_1741586697.png',
                'name' => '曾子芸',
                'gender' => 'female',
                'birth_date' => null,
                'age' => null,
                'address' => null,
                'current_points' => 0,
                'discount_rate' => 1.00,
                'discount_expiry_date' => null,
                'status' => 0,
                'latest_payment_record_id' => null,
                'latest_point_record_id' => null,
                'permission' => 1,
                'created_at' => '2025-03-10 06:03:08',
                'updated_at' => '2025-03-10 06:04:57',
            ],
            [
                'mobile_phone' => '0938502858',
                'token' => 'jmT9mih80iTG6KTFOvdFcJ0W6iIqhbTQHrkb8rTaZAF8tBc4ifZNHVAuMwGH',
                'created_by' => 2204,
                'updated_by' => 2204,
                'shop_brand_id' => 1,
                'line_auth_code' => 'undefined',
                'country_calling_code' => '+886',
                'last_visited_at' => '2025-03-10 06:05:09',
                'avatar' => 'storage/users/default',
                'name' => '遲芋橙',
                'gender' => 'female',
                'birth_date' => null,
                'age' => null,
                'address' => null,
                'current_points' => 0,
                'discount_rate' => 1.00,
                'discount_expiry_date' => null,
                'status' => 0,
                'latest_payment_record_id' => null,
                'latest_point_record_id' => null,
                'permission' => 1,
                'created_at' => '2025-03-10 06:05:09',
                'updated_at' => '2025-03-10 06:05:41',
            ],
            [
                'mobile_phone' => '0960654097',
                'token' => '14zxb2qYB9LEEqqIiIidnTQl58M5CGVucalHOIq4FdavuVNe5rs8k4LFysjn',
                'created_by' => 2204,
                'updated_by' => 2204,
                'shop_brand_id' => 1,
                'line_auth_code' => 'undefined',
                'country_calling_code' => '+886',
                'last_visited_at' => '2025-03-10 06:02:55',
                'avatar' => 'storage/users/default',
                'name' => '簡政乾',
                'gender' => 'male',
                'birth_date' => null,
                'age' => null,
                'address' => null,
                'current_points' => 0,
                'discount_rate' => 1.00,
                'discount_expiry_date' => null,
                'status' => 0,
                'latest_payment_record_id' => null,
                'latest_point_record_id' => null,
                'permission' => 1,
                'created_at' => '2025-03-10 06:02:55',
                'updated_at' => '2025-03-10 06:03:27',
            ],
            [
                'mobile_phone' => '0983505703',
                'token' => 'freUD8zOfOYzATR3rcd7IrPzCq2FFTsHQ8k7rdXslHqHwTunwAl8zrBoBv8u',
                'created_by' => 2204,
                'updated_by' => 2204,
                'shop_brand_id' => 1,
                'line_auth_code' => 'undefined',
                'country_calling_code' => '+886',
                'last_visited_at' => '2025-03-10 06:02:57',
                'avatar' => 'storage/users/default',
                'name' => '胡美玲',
                'gender' => 'female',
                'birth_date' => null,
                'age' => null,
                'address' => null,
                'current_points' => 0,
                'discount_rate' => 1.00,
                'discount_expiry_date' => null,
                'status' => 0,
                'latest_payment_record_id' => null,
                'latest_point_record_id' => null,
                'permission' => 1,
                'created_at' => '2025-03-10 06:02:57',
                'updated_at' => '2025-03-10 06:20:04',
            ],
            [
                'mobile_phone' => '0928236231',
                'token' => 'freUD8zOfOYzATR3rcd7IrPzCq2FFTsHQ8k7rdXslHqHwTunwAl8zrBoBv8u',
                'created_by' => 2204,
                'updated_by' => 2204,
                'shop_brand_id' => 1,
                'line_auth_code' => 'undefined',
                'country_calling_code' => '+886',
                'last_visited_at' => '2025-03-10 06:02:57',
                'avatar' => 'storage/users/default',
                'name' => 'JZ',
                'gender' => 'male',
                'birth_date' => null,
                'age' => null,
                'address' => null,
                'current_points' => 0,
                'discount_rate' => 1.00,
                'discount_expiry_date' => null,
                'status' => 0,
                'latest_payment_record_id' => null,
                'latest_point_record_id' => null,
                'permission' => 1,
                'created_at' => '2025-03-10 06:02:57',
                'updated_at' => '2025-03-10 06:20:04',
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}