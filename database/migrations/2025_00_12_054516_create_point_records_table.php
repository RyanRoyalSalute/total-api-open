<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePointRecordsTable extends Migration
{
    public function up(): void
    {
        Schema::create('point_records', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('user_id')->constrained('users'); // 隸屬學員Id
            $table->string('change_reason'); // 變動事項
            $table->integer('points_changed'); // 積分變動值
            $table->timestamps(); // created_at and updated_at
        });

        // Insert sample point records
        DB::table('point_records')->insert([
            [
                'created_by' => 1,
                'updated_by' => 1,
                'user_id' => 1, // Assign to user with ID 1
                'change_reason' => '充值',
                'points_changed' => 4999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 1,
                'updated_by' => 1,
                'user_id' => 1, // Assign to user with ID 1
                'change_reason' => '註冊獎勵',
                'points_changed' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 1,
                'updated_by' => 1,
                'user_id' => 1, // Assign to user with ID 1
                'change_reason' => '地址獎勵',
                'points_changed' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 1,
                'updated_by' => 1,
                'user_id' => 1, // Assign to user with ID 1
                'change_reason' => '消費',
                'points_changed' => -200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 1,
                'updated_by' => 1,
                'user_id' => 1, // Assign to user with ID 1
                'change_reason' => '贈票',
                'points_changed' => -50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 1,
                'updated_by' => 1,
                'user_id' => 1, // Assign to user with ID 1
                'change_reason' => '換課',
                'points_changed' => -20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by' => 1,
                'updated_by' => 1,
                'user_id' => 1, // Assign to user with ID 1
                'change_reason' => '換日期',
                'points_changed' => -20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('point_records');
    }
}
