<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_social_media', function (Blueprint $table) {

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
             * Social fields
             * ========================= */
            $table->string('platform', 100);        // LinkedIn, GitHub, X, etc.
            $table->string('icon', 100)->nullable(); // fa-linkedin / svg path etc.
            $table->string('link', 500);            // URL
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
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
            $table->index(['user_id', 'sort_order']);
            $table->index(['platform']);
            $table->index(['active']);

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
        Schema::dropIfExists('user_social_media');
    }
};
