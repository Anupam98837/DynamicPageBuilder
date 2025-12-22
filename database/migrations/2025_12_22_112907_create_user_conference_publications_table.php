<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_conference_publications', function (Blueprint $table) {

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
             * Conference publication fields
             * ========================= */
            $table->string('conference_name', 255);
            $table->string('publication_organization', 255)->nullable();
            $table->string('title', 255);

            $table->year('publication_year')->nullable();
            $table->string('publication_type', 100)->nullable(); // Paper/Poster/Talk/Workshop
            $table->string('domain', 255)->nullable();
            $table->string('location', 255)->nullable();

            $table->longText('description')->nullable();
            $table->string('url', 500)->nullable();
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
            $table->index(['user_id', 'publication_year']);
            $table->index(['publication_type']);
            $table->index(['domain']);

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
        Schema::dropIfExists('user_conference_publications');
    }
};
