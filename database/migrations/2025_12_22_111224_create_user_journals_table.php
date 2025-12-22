<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_journals', function (Blueprint $table) {

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
             * Journal fields
             * ========================= */
            $table->string('title', 255);
            $table->string('publication_organization', 255)->nullable();
            $table->year('publication_year')->nullable();
            $table->longText('description')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('image', 255)->nullable();
            $table->integer('sort_order')->default(0);
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
            $table->index(['user_id', 'publication_year']);
            $table->index(['user_id', 'sort_order']);

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
        Schema::dropIfExists('user_journals');
    }
};
