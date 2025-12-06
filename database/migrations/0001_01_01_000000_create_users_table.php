<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            /* =========================
             *  Primary / Identifiers
             * ========================= */
            $table->bigIncrements('id'); // PK
            $table->uuid('uuid')->unique(); // CHAR(36) external id

            $table->string('name');
            $table->string('slug', 140)->unique(); // human-friendly unique id

            /* =========================
             *  Contact Info
             * ========================= */
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            // nullable for safe migration; app will enforce presence on create
            $table->string('phone_number', 32)->nullable()->unique();
            $table->string('alternative_email', 255)->nullable();
            $table->string('alternative_phone_number', 32)->nullable();
            $table->string('whatsapp_number', 32)->nullable();

            /* =========================
             *  Auth / Profile
             * ========================= */
            $table->string('password');

            // store path/URL (app writes to /Public/UserProfileImage/{unique}.{ext})
            $table->string('image', 255)->nullable();
            $table->text('address')->nullable();

            /*
             | MSIT roles:
             | director, principal, hod, faculty, technical_assistant, it_person, student(optional)
             */
            // Set sensible default – change to whatever you prefer (director/principal/faculty/etc.)
            $table->string('role', 50)->default('faculty');
            // DIR, PRI, HOD, FAC, TA, IT, STD
            $table->string('role_short_form', 10)->default('FAC');

            /* =========================
             *  Status / Login tracking
             * ========================= */
            $table->string('status', 20)->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();

            /* =========================
             *  Audit
             * ========================= */
            $table->unsignedBigInteger('created_by')->nullable(); // FK -> users(id)
            $table->timestamps(); // created_at, updated_at
            $table->ipAddress('created_at_ip')->nullable();

            /* =========================
             *  Soft delete + metadata
             * ========================= */
            $table->softDeletes();              // adds deleted_at
            $table->index('deleted_at');
            $table->json('metadata')->nullable(); // optional extras (timezone, device, etc.)

            // Self-referencing FK (creator)
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
