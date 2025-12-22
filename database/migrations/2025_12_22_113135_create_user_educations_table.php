<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_educations', function (Blueprint $table) {

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
             * Education fields
             * ========================= */
            $table->string('education_level', 100); // School / UG / PG / PhD
            $table->string('degree_title', 255)->nullable();
            $table->string('field_of_study', 255)->nullable();

            $table->string('institution_name', 255);
            $table->string('university_name', 255)->nullable();

            $table->year('enrollment_year')->nullable();
            $table->year('passing_year')->nullable();

            $table->string('grade_type', 50)->nullable();   // CGPA / %
            $table->string('grade_value', 50)->nullable();  // 8.2 / 78%

            $table->string('location', 255)->nullable();
            $table->longText('description')->nullable();

            $table->string('certificate', 255)->nullable();
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
            $table->index(['user_id', 'education_level']);
            $table->index(['passing_year']);

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
        Schema::dropIfExists('user_educations');
    }
};
