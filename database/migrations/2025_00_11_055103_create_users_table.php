<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // 確保 DB Facade 已引入
use Illuminate\Support\Facades\Hash; // 確保 Hash Facade 已引入 (如果需要在 insert 中 Hashed 密碼)

class CreateUsersTable extends Migration // 確保 class 名稱與檔案名匹配 (如果您的檔名是 CreateUsersTable)
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->string('mobile_phone', 20)->unique()->nullable()->comment('手機號碼'); // 允許 nullable 以便 email 登入，或 unique
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('shop_brand_id')->nullable()->constrained('shop_brands')->onDelete('set null'); // 隸屬店頭(品牌)Id, onDelete 可考慮 set null
            $table->string('line_auth_code')->nullable(); // LINE授權ID
            $table->string('country_calling_code', 10)->nullable(); // 手機國碼
            $table->timestamp('last_visited_at')->nullable(); // 最近造訪時間
            $table->string('avatar')->default('storage/users/default')->nullable(); // 學員頭像
            $table->string('name')->nullable(); // 姓名
            $table->string('gender')->nullable(); // 性別
            $table->date('birth_date')->nullable(); // 出生年月日
            $table->integer('age')->nullable(); // 年齡 (通常由 birth_date 計算，可考慮移除)
            $table->string('address')->nullable(); // 地址
            $table->integer('current_points')->default(0); // 當前積分
            $table->decimal('discount_rate', 3, 2)->default(1.0); // 商品折扣
            $table->date('discount_expiry_date')->nullable(); // 商品折扣優待期
            $table->integer('status')->default(0); // 狀態: -1:封鎖, 0:正常, >=1: VIP
            $table->unsignedBigInteger('latest_payment_record_id')->nullable(); // 最新消費紀錄Id
            $table->unsignedBigInteger('latest_point_record_id')->nullable(); // 最新積分紀錄Id
            $table->integer('permission')->default(0); // 0: 用戶, >=1: 管理員

            $table->string('email')->unique()->nullable(); // Email, nullable 以便手機註冊優先
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // 密碼, nullable

            $table->rememberToken();
            $table->timestamps(); // created_at and updated_at
        });

        // --- 保留您的預設用戶數據填充 ---
        // 注意：為 password 和 email 欄位提供預設值
        // 為 password 提供一個 Hashed 的隨機值或固定測試密碼
        // 為 email 提供一個基於手機號的唯一值
        DB::table('users')->insert([
            [
                'mobile_phone' => '0912398712',
                // 'token' => 'sample_token_1', // 移除 token 欄位
                'email' => '0912398712@example.com', // 新增 email
                'password' => Hash::make('password'), // 新增 password (建議 Hashed)
                'email_verified_at' => now(),
                'created_by' => 1, 'updated_by' => 1, 'shop_brand_id' => 1, 'line_auth_code' => 'line_user_001',
                'country_calling_code' => '+886', 'last_visited_at' => now(), 'avatar' => 'storage/Saabisu/users/default',
                'name' => 'Yuheng#咪咪 Kuan', 'gender' => '女', 'birth_date' => '2000-04-01', 'age' => 24,
                'address' => '桃園市蘆竹區南山路一段55號3F', 'current_points' => 4999, 'discount_rate' => 0.9,
                'discount_expiry_date' => now()->addDays(30), 'status' => 0, 'latest_payment_record_id' => null,
                'latest_point_record_id' => null, 'permission' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'mobile_phone' => '0912398713',
                'email' => '0912398713@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_by' => 1, 'updated_by' => 1, 'shop_brand_id' => 1, 'line_auth_code' => 'line_user_002',
                'country_calling_code' => '+886', 'last_visited_at' => now(), 'avatar' => 'storage/Saabisu/users/default',
                'name' => '施淳瑜', 'gender' => '女', 'birth_date' => '2000-04-01', 'age' => 24,
                'address' => '桃園市蘆竹區南山路一段55號3F', 'current_points' => 4999, 'discount_rate' => 0.9,
                'discount_expiry_date' => now()->addDays(30), 'status' => 0, 'latest_payment_record_id' => null,
                'latest_point_record_id' => null, 'permission' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'mobile_phone' => '0912398714',
                'email' => '0912398714@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_by' => 1, 'updated_by' => 1, 'shop_brand_id' => 1, 'line_auth_code' => 'line_user_003',
                'country_calling_code' => '+886', 'last_visited_at' => now(), 'avatar' => 'storage/Saabisu/users/0912398714_avatar.png',
                'name' => 'Wwney', 'gender' => '女', 'birth_date' => '2000-04-01', 'age' => 24,
                'address' => '桃園市蘆竹區南山路一段55號3F', 'current_points' => 4999, 'discount_rate' => 0.9,
                'discount_expiry_date' => now()->addDays(30), 'status' => 0, 'latest_payment_record_id' => null,
                'latest_point_record_id' => null, 'permission' => 0, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'mobile_phone' => '0938189829',
                'email' => '0938189829@example.com',
                'password' => Hash::make('password'), // 您原有的 'token' 欄位值看起來像是 Hashed 字串，但不適合直接當密碼
                'email_verified_at' => now(),
                'created_by' => 2204, 'updated_by' => 2204, 'shop_brand_id' => 1, 'line_auth_code' => 'undefined',
                'country_calling_code' => '+886', 'last_visited_at' => '2025-03-10 06:03:08', 'avatar' => 'storage/Saabisu/users/0938189829_avatar_1741586697.png',
                'name' => '曾子芸', 'gender' => 'female', 'birth_date' => null, 'age' => null,
                'address' => null, 'current_points' => 0, 'discount_rate' => 1.00,
                'discount_expiry_date' => null, 'status' => 0, 'latest_payment_record_id' => null,
                'latest_point_record_id' => null, 'permission' => 1, 'created_at' => '2025-03-10 06:03:08', 'updated_at' => '2025-03-10 06:04:57',
            ],
            [
                'mobile_phone' => '0938502858',
                'email' => '0938502858@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_by' => 2204, 'updated_by' => 2204, 'shop_brand_id' => 1, 'line_auth_code' => 'undefined',
                'country_calling_code' => '+886', 'last_visited_at' => '2025-03-10 06:05:09', 'avatar' => 'storage/users/default',
                'name' => '遲芋橙', 'gender' => 'female', 'birth_date' => null, 'age' => null,
                'address' => null, 'current_points' => 0, 'discount_rate' => 1.00,
                'discount_expiry_date' => null, 'status' => 0, 'latest_payment_record_id' => null,
                'latest_point_record_id' => null, 'permission' => 1, 'created_at' => '2025-03-10 06:05:09', 'updated_at' => '2025-03-10 06:05:41',
            ],
            [
                'mobile_phone' => '0960654097',
                'email' => '0960654097@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_by' => 2204, 'updated_by' => 2204, 'shop_brand_id' => 1, 'line_auth_code' => 'undefined',
                'country_calling_code' => '+886', 'last_visited_at' => '2025-03-10 06:02:55', 'avatar' => 'storage/users/default',
                'name' => '簡政乾', 'gender' => 'male', 'birth_date' => null, 'age' => null,
                'address' => null, 'current_points' => 0, 'discount_rate' => 1.00,
                'discount_expiry_date' => null, 'status' => 0, 'latest_payment_record_id' => null,
                'latest_point_record_id' => null, 'permission' => 1, 'created_at' => '2025-03-10 06:02:55', 'updated_at' => '2025-03-10 06:03:27',
            ],
            [
                'mobile_phone' => '0983505703',
                'email' => '0983505703@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_by' => 2204, 'updated_by' => 2204, 'shop_brand_id' => 1, 'line_auth_code' => 'undefined',
                'country_calling_code' => '+886', 'last_visited_at' => '2025-03-10 06:02:57', 'avatar' => 'storage/users/default',
                'name' => '胡美玲', 'gender' => 'female', 'birth_date' => null, 'age' => null,
                'address' => null, 'current_points' => 0, 'discount_rate' => 1.00,
                'discount_expiry_date' => null, 'status' => 0, 'latest_payment_record_id' => null,
                'latest_point_record_id' => null, 'permission' => 1, 'created_at' => '2025-03-10 06:02:57', 'updated_at' => '2025-03-10 06:20:04',
            ],
            [
                'mobile_phone' => '0928236231',
                'email' => '0928236231@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'created_by' => 2204, 'updated_by' => 2204, 'shop_brand_id' => 1, 'line_auth_code' => 'undefined',
                'country_calling_code' => '+886', 'last_visited_at' => '2025-03-10 06:02:57', 'avatar' => 'storage/users/default',
                'name' => 'JZ', 'gender' => 'male', 'birth_date' => null, 'age' => null,
                'address' => null, 'current_points' => 0, 'discount_rate' => 1.00,
                'discount_expiry_date' => null, 'status' => 0, 'latest_payment_record_id' => null,
                'latest_point_record_id' => null, 'permission' => 1, 'created_at' => '2025-03-10 06:02:57', 'updated_at' => '2025-03-10 06:20:04',
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}