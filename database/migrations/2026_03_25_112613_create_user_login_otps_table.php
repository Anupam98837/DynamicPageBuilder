<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_login_otps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('email')->index();
            $table->string('otp', 6);
            $table->unsignedInteger('attempt_count')->default(0);
            $table->boolean('is_used')->default(false);
            $table->string('system_ip', 45)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'email', 'is_used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_otps');
    }
};