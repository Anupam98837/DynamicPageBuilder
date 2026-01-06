<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            // Primary
            $table->bigIncrements('id'); // BIGINT UNSIGNED, PK, AUTO_INCREMENT

            // External UUID
            $table->char('uuid', 36)->unique(); // CHAR(36) UNIQUE, NOT NULL

            // Optional department mapping
            $table->unsignedBigInteger('department_id')->nullable(); // FK -> departments.id

            // Content fields
            $table->string('title', 255);                 // NOT NULL
            $table->string('slug', 160)->unique();        // UNIQUE, NOT NULL
            $table->longText('body');                     // NOT NULL (HTML allowed)
            $table->string('cover_image', 255)->nullable();
            $table->json('attachments_json')->nullable(); // Optional attachments list

            // Flags / status
            $table->tinyInteger('is_featured_home')->default(0); // DEFAULT 0, NOT NULL
            $table->string('status', 20)->default('draft');      // DEFAULT 'draft', NOT NULL

            // Publish controls
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0); // DEFAULT 0, NOT NULL

            // Audit
            $table->unsignedBigInteger('created_by')->nullable(); // FK -> users.id
            $table->timestamps();                                 // created_at, updated_at (nullable timestamps)
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete + required index
            $table->softDeletes();          // deleted_at (nullable)
            $table->index('deleted_at');    // INDEX (as per schema)

            // Extra metadata
            $table->json('metadata')->nullable();

            // Foreign keys
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
