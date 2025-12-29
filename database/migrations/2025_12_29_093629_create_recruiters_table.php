<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruiters', function (Blueprint $table) {
            // PK
            $table->bigIncrements('id');

            // External identifiers
            $table->char('uuid', 36)->unique();          // UNIQUE, NOT NULL
            $table->string('slug', 160)->unique();       // UNIQUE, NOT NULL

            // Relations
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();                        // FK -> departments.id (nullable)

            // Content
            $table->string('title', 255);                // NOT NULL
            $table->longText('description')->nullable(); // LONGTEXT (HTML allowed)
            $table->string('logo_url', 255)->nullable(); // URL/path
            $table->json('job_roles_json')->nullable();  // List of job roles + CTC
            $table->json('metadata')->nullable();        // Extra metadata

            // Flags / ordering
            $table->boolean('is_featured_home')->default(false); // TINYINT(1) DEFAULT 0
            $table->unsignedInteger('sort_order')->default(0);   // DEFAULT 0
            $table->index('sort_order');

            // Status
            $table->string('status', 20)->default('active');     // DEFAULT 'active'
            $table->index('status');

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();                         // FK -> users.id (nullable)

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete with INDEX as per schema
            $table->softDeletes(); // deleted_at (nullable)
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiters');
    }
};
