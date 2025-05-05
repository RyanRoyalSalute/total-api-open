<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateUserDiscountsTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recharge_id')->nullable()->constrained('recharges');
            $table->decimal('discount_rate', 3, 2);
            $table->date('start_date');
            $table->date('expiry_date');
            $table->unsignedBigInteger('payment_record_id')->nullable();
            $table->timestamps();
        });

        $usersWithDiscounts = DB::table('users')
            ->where('discount_rate', '<', 1.0)
            ->orWhereNotNull('discount_expiry_date')
            ->get();

        foreach ($usersWithDiscounts as $user) {
            DB::table('user_discounts')->insert([
                'user_id' => $user->id,
                'recharge_id' => null,
                'discount_rate' => $user->discount_rate ?? 1.0,
                'start_date' => $user->created_at ?? Carbon::today(),
                'expiry_date' => $user->discount_expiry_date ?? Carbon::today()->addDays(30),
                'payment_record_id' => $user->latest_payment_record_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_discounts');
    }
}