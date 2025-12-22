<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_teaching_engagements', function (Blueprint $table) {

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
             * Teaching Engagement fields
             * ========================= */
            $table->string('organization_name', 255);
            $table->string('domain', 255)->nullable();
            $table->longText('description')->nullable();
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
        Schema::dropIfExists('user_teaching_engagements');
    }
};
