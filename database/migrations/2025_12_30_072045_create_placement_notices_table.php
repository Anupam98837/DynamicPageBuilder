<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placement_notices', function (Blueprint $table) {
            // PK
            $table->id(); // BIGINT UNSIGNED, PK, AUTO_INCREMENT

            // Identifiers
            $table->char('uuid', 36)->unique();          // UNIQUE, not null
            $table->string('slug', 160)->unique();       // UNIQUE, not null

            // ✅ Departments (JSON array of IDs selected from frontend)
            // Example stored value: [1, 3, 7]
            $table->json('department_ids')->nullable();

            // Relations
            $table->foreignId('recruiter_id')
                ->nullable()
                ->constrained('recruiters')
                ->nullOnDelete();                        // FK -> recruiters.id (indexed by default)

            // Core fields
            $table->string('title', 255);                // not null
            $table->longText('description')->nullable(); // nullable, HTML allowed
            $table->string('banner_image_url', 255)->nullable();
            $table->string('role_title', 255)->nullable();

            // CTC
            $table->decimal('ctc', 6, 2)->nullable();     // DECIMAL(6,2)

            // Extra details
            $table->longText('eligibility')->nullable();  // HTML allowed
            $table->string('apply_url', 255)->nullable();
            $table->date('last_date_to_apply')->nullable()->index();

            // Flags / ordering / status
            $table->boolean('is_featured_home')->default(false); // TINYINT(1) DEFAULT 0, not null
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('draft')->index(); // draft/published/archived

            // Publish / expiry
            $table->timestamp('publish_at')->nullable()->index();
            $table->timestamp('expire_at')->nullable()->index();

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();                        // FK -> users.id

            $table->timestamps();                        // created_at, updated_at (nullable)
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete + index (schema requires INDEX)
            $table->softDeletes();
            $table->index('deleted_at');

            // Metadata
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_notices');
    }
};
