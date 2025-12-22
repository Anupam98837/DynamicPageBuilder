<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_honors', function (Blueprint $table) {

            /* =========================
             * Primary / Identifiers
             * ========================= */
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();

            /* =========================
             * Relations
             * ========================= */
            $table->unsignedBigInteger('user_id');

            /* =========================
             * Honor fields
             * ========================= */
            $table->string('title', 255);
            $table->string('honor_type', 100)->nullable();
            $table->string('honouring_organization', 255)->nullable();
            $table->year('honor_year')->nullable();
            $table->longText('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->json('metadata')->nullable();

            /* =========================
             * Audit
             * ========================= */
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->ipAddress('created_at_ip')->nullable();

            /* =========================
             * Soft delete
             * ========================= */
            $table->softDeletes();
            $table->index('deleted_at');

            /* =========================
             * Indexes
             * ========================= */
            $table->index('user_id');
            $table->index(['user_id', 'honor_year']);

            /* =========================
             * Foreign keys
             * ========================= */
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_honors');
    }
};
