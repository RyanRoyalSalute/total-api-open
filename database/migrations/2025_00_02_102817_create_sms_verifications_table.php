<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsVerificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('sms_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_phone', 15);
            $table->string('verification_code', 6);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->index('mobile_phone');
            $table->unsignedBigInteger('user_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_verifications');
    }
}
