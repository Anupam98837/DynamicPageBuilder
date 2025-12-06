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
        Schema::create('departments', function (Blueprint $table) {
            // Primary key
            $table->bigIncrements('id');

            // UUID
            $table->uuid('uuid')->unique(); // CHAR(36) with index

            // Main fields
            $table->string('title', 150);          // e.g. "Computer Science and Engineering"
            $table->string('slug', 160)->unique(); // e.g. "cse", "ece"
            $table->unsignedTinyInteger('total_semesters')
                  ->default(8)
                  ->comment('Number of semesters for this department');

            // Active / Inactive toggle
            // true  = active (visible/usable)
            // false = inactive (can be treated as archived/disabled)
            $table->boolean('active')->default(true);

            // Extra meta (for future extension: logos, NAAC code, etc.)
            $table->json('metadata')->nullable();

            // Audit: who created
            $table->unsignedBigInteger('created_by')->nullable();
            $table->ipAddress('created_at_ip')->nullable();

            // Timestamps
            // (created_at, updated_at) with useCurrent + useCurrentOnUpdate
            $table->timestamps();

            // Soft delete: for archive/trash/restore/permanent delete flows
            $table->softDeletes();        // adds deleted_at
            $table->index('deleted_at');  // for trash listing

            // Foreign key(s)
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
        Schema::dropIfExists('departments');
    }
};
