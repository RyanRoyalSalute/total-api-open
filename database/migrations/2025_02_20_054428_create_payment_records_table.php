<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePaymentRecordsTable extends Migration
{
    public function up(): void
    {
        Schema::create('payment_records', function (Blueprint $table) {
            $table->id(); // 編碼Id
            $table->string('trade_no')->unique(); // 金流單號
            $table->unsignedBigInteger('created_by')->nullable(); // 創建者
            $table->unsignedBigInteger('updated_by')->nullable(); // 修改者
            $table->foreignId('shop_brand_id')->constrained('shop_brands'); // 隸屬店頭(品牌)Id
            $table->foreignId('sub_store_id')->nullable()->constrained('sub_store'); // 分店Id
            $table->date('payment_date'); // 消費日期
            $table->foreignId('user_id')->constrained('users'); // 消費學員Id
            $table->foreignId('course_id')->nullable()->constrained('courses'); // 購買課程Id
            $table->enum('payment_method', ['CREDIT_CARD', 'LINE_PAY']); // 支付方式
            $table->decimal('transaction_amount', 8, 2); // 交易金額
            $table->decimal('received_amount', 8, 2); // 實收金額
            $table->decimal('recharge_amount', 8, 2)->nullable(); // 充值金額
            $table->integer('privileged_level')->default(0); // 優待等級
            $table->boolean('pinned')->default(false); // 置頂
            $table->tinyInteger('is_paid')->default(0); // -1:逾時, 0:未支付, 1:已付款
            $table->integer('attendance')->nullable(); // 參與人數
            $table->date('date')->nullable(); // 課程日期
            $table->string('time', 5)->nullable(); // 課程時間 (HH:MM)
            $table->unsignedBigInteger('teacher_id')->nullable(); // 老師id
            $table->unsignedBigInteger('session_id')->nullable(); // Added session_id column
            $table->string('owner_phone')->nullable(); // 購買者電話
            $table->text('payment')->nullable(); // Payment column for HTML form data
            $table->string('order_3rd_no')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
            $table->foreign('session_id')->references('id')->on('course_sessions')->onDelete('set null');
            $table->index('trade_no');
            $table->index(['course_id', 'date', 'time', 'is_paid', 'created_at'], 'payment_records_seat_release_index');
        });

        $samplePaymentForm = '<!DOCTYPE html><html><head><meta charset=\"utf-8\"></head><body><form id=\"ecpay-form\" method=\"POST\" target=\"_self\" action=\"https:\/\/payment.ecpay.com.tw\/Cashier\/AioCheckOut\/V5\"><input type=\"hidden\" name=\"CheckMacValue\" value=\"8360E5B29089957BA9BBD31D461D672288CA93BD654A87A8B89C18CC1BBBB02F\"><input type=\"hidden\" name=\"ChoosePayment\" value=\"Credit\"><input type=\"hidden\" name=\"ClientBackURL\" value=\"https%3A%2F%2Fbooking.serp.tw%2Fmobile%2Findex.html\"><input type=\"hidden\" name=\"EncryptType\" value=\"1\"><input type=\"hidden\" name=\"ItemName\" value=\"\u967d\u5149\u89aa\u5b50\u6d41\u9ad4\u718a\"><input type=\"hidden\" name=\"MerchantID\" value=\"3384576\"><input type=\"hidden\" name=\"MerchantTradeDate\" value=\"2025\/03\/14 03:17:12\"><input type=\"hidden\" name=\"MerchantTradeNo\" value=\"25031403171201\"><input type=\"hidden\" name=\"PaymentType\" value=\"aio\"><input type=\"hidden\" name=\"ReturnURL\" value=\"https:\/\/booking.serp.tw\/api\/order\/ecpay\/callback\"><input type=\"hidden\" name=\"TotalAmount\" value=\"10\"><input type=\"hidden\" name=\"TradeDesc\" value=\"%e4%ba%a4%e6%98%93%e6%8f%8f%e8%bf%b0%e7%af%84%e4%be%8b\"></form><script type=\"text\/javascript\">document.getElementById(\"ecpay-form\").submit();<\/script></body></html>';

        DB::table('payment_records')->insert([
            [
                'trade_no' => '20250120101001',
                'order_3rd_no' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'shop_brand_id' => 1,
                'sub_store_id' => 1,
                'payment_date' => '2025-01-20',
                'user_id' => 1,
                'course_id' => 1,
                'payment_method' => 'CREDIT_CARD',
                'transaction_amount' => 1450,
                'received_amount' => 1377.50,
                'recharge_amount' => 0,
                'privileged_level' => 0,
                'pinned' => false,
                'is_paid' => 1,
                'attendance' => 1,
                'date' => '2025-02-01',
                'time' => '10:00',
                'teacher_id' => 2501,
                'session_id' => null,
                'owner_phone' => '0912398712',
                'payment' => $samplePaymentForm,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trade_no' => '20250120101002',
                'order_3rd_no' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'shop_brand_id' => 1,
                'sub_store_id' => 1,
                'payment_date' => '2025-01-20',
                'user_id' => 2,
                'course_id' => 2,
                'payment_method' => 'CREDIT_CARD',
                'transaction_amount' => 499,
                'received_amount' => 474.05,
                'recharge_amount' => 0,
                'privileged_level' => 0,
                'pinned' => false,
                'is_paid' => 1,
                'attendance' => 1,
                'date' => '2025-03-01',
                'time' => '14:00',
                'teacher_id' => 2502,
                'session_id' => null,
                'owner_phone' => '0912398712',
                'payment' => $samplePaymentForm,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'trade_no' => '20250120101003',
                'order_3rd_no' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'shop_brand_id' => 1,
                'sub_store_id' => 1,
                'payment_date' => '2025-01-20',
                'user_id' => 2,
                'course_id' => 3,
                'payment_method' => 'CREDIT_CARD',
                'transaction_amount' => 4999,
                'received_amount' => 4749.05,
                'recharge_amount' => 4999,
                'privileged_level' => 1,
                'pinned' => false,
                'is_paid' => 1,
                'attendance' => 1,
                'date' => '2025-01-30',
                'time' => '16:00',
                'teacher_id' => 2503,
                'session_id' => null,
                'owner_phone' => '0912398712',
                'payment' => $samplePaymentForm,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
}