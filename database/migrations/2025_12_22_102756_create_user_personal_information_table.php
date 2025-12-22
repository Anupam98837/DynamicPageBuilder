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
        Schema::create('user_personal_information', function (Blueprint $table) {

            /* =========================
             * Primary / Identifiers
             * ========================= */
            $table->bigIncrements('id');                 // PK
            $table->uuid('uuid')->unique();              // External UUID

            /* =========================
             * User relation (1:1)
             * ========================= */
            $table->unsignedBigInteger('user_id')->unique();

            /* =========================
             * Personal Information
             * ========================= */
            $table->json('qualification')->nullable();   // Array of qualifications
            $table->longText('affiliation')->nullable(); // Affiliation
            $table->longText('specification')->nullable(); // Specialization
            $table->longText('experience')->nullable();  // Experience
            $table->longText('interest')->nullable();    // Interests
            $table->longText('administration')->nullable(); // Administration
            $table->longText('research_project')->nullable(); // Research projects

            /* =========================
             * Audit
             * ========================= */
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();                        // created_at, updated_at
            $table->ipAddress('created_at_ip')->nullable();

            /* =========================
             * Soft delete
             * ========================= */
            $table->softDeletes();
            $table->index('deleted_at');

            /* =========================
             * Foreign Keys
             * ========================= */
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_personal_information');
    }
};
