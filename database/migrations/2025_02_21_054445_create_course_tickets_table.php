<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCourseTicketsTable extends Migration
{
    public function up(): void
    {
        Schema::create('course_tickets', function (Blueprint $table) {
            $table->id(); // 編碼Id (Primary Key)
            $table->string('ticket_id')->unique(); // 票卡編號, [4-digit courseId][6-digit DATE, yymmdd][4-digit TIME, hhmm][3-digit SEAT, 001]
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('course_id')->constrained('courses'); // 課程Id (Foreign Key)
            $table->unsignedTinyInteger('ticket_status')->default(0);  // 未使用0, 已使用1, 作廢2
            $table->string('owner_phone')->nullable(); // 持有人電話 (nullable for unassigned)
            $table->unsignedBigInteger('owner_user_id')->nullable(); // 持有人UserId
            $table->string('payment_record_id')->nullable(); // 消費紀錄Id (Foreign Key for payment record)
            $table->unsignedBigInteger('teacher_id')->nullable(); // Teacher ID (Foreign Key)
            $table->date('date')->nullable(); // 課程日期
            $table->string('time', 5)->nullable(); // 課程時間 (HH:MM)
            $table->unsignedBigInteger('session_id')->nullable(); // Added session_id column
            $table->timestamps(); // created_at and updated_at

            $table->foreign('payment_record_id')->references('trade_no')->on('payment_records')->onDelete('set null');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
            $table->foreign('session_id')->references('id')->on('course_sessions')->onDelete('set null');
        });

        DB::table('course_tickets')->insert([
            [
                'ticket_id' => '00012501151059001',
                'created_by' => 0,
                'updated_by' => 0,
                'course_id' => 1,
                'ticket_status' => 1,
                'owner_phone' => '0912398712',
                'owner_user_id' => 1,
                'payment_record_id' => '20250120101001',
                'teacher_id' => 2501,
                'date' => '2025-02-01',
                'time' => '10:00',
                'session_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ticket_id' => '00022501151000002',
                'created_by' => 0,
                'updated_by' => 0,
                'course_id' => 2,
                'ticket_status' => 0,
                'owner_phone' => '0912398712',
                'owner_user_id' => 1,
                'payment_record_id' => '20250120101002',
                'teacher_id' => 2502,
                'date' => '2025-03-01',
                'time' => '14:00',
                'session_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ticket_id' => '00032501151059003',
                'created_by' => 0,
                'updated_by' => 0,
                'course_id' => 3,
                'ticket_status' => 2,
                'owner_phone' => '0912398712',
                'owner_user_id' => 1,
                'payment_record_id' => '20250120101003',
                'teacher_id' => 2503,
                'date' => '2025-01-30',
                'time' => '16:00',
                'session_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('course_tickets');
    }
}