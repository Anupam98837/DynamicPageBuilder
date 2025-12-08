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
        Schema::create('privileges', function (Blueprint $table) {
            /* =========================
             * Primary / Identifiers
             * ========================= */
            $table->bigIncrements('id');                 // PK
            $table->char('uuid', 36)->unique();          // external UUID

            // Global privilege code (for backend checks)
            // e.g. 'fees.collection.collect', 'scholarship.assign'
            $table->string('key', 120)->unique();

            /* =========================
             *  Relationships
             * ========================= */
            $table->unsignedBigInteger('module_id');     // FK -> modules(id)

            /* =========================
             *  Core Privilege Fields
             * ========================= */
            // Short action name (used by your existing code)
            // e.g. 'Collect Fees', 'Assign Scholarship'
            $table->string('action', 80);

            // Optional description for UI / tooltips
            $table->text('description')->nullable();

            // Order inside the module privilege list
            $table->unsignedInteger('order_no')->nullable();

            // 'Active', 'Inactive', 'Archived', etc.
            $table->string('status', 20)->default('Active');

            /* =========================
             *  Assigned APIs (NEW)
             * ========================= */
            // JSON array of route names or URIs this privilege protects.
            // Example:
            // ["api.fees.collect.index","api.fees.collect.store"]
            $table->json('assigned_apis')->nullable();

            // Extra flexible JSON for future flags (category, level, etc.)
            // Example: {"category":"write","danger_level":"high"}
            $table->json('meta')->nullable();

            /* =========================
             *  Audit / Timestamps
             * ========================= */
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')
                  ->useCurrent()
                  ->useCurrentOnUpdate();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_at_ip', 45)->nullable();

            // Soft delete
            $table->timestamp('deleted_at')->nullable();

            /* =========================
             *  Indexes & Constraints
             * ========================= */
            $table->index('module_id');
            $table->index('action');
            $table->index('status');
            $table->index('created_by');

            // Prevent duplicate actions inside the same module
            $table->unique(['module_id', 'action'], 'privileges_module_action_unique');

            // FKs
            $table->foreign('module_id')
                  ->references('id')
                  ->on('modules')
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
        Schema::dropIfExists('privileges');
    }
};
