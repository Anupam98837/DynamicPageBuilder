<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED PK AUTO_INCREMENT

            $table->char('uuid', 36)->unique(); // CHAR(36) UNIQUE

            // Optional: department-specific activity
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->string('title', 255);        // VARCHAR(255)
            $table->string('slug', 160)->unique(); // VARCHAR(160) UNIQUE
            $table->longText('body');            // LONGTEXT (HTML allowed)

            $table->string('cover_image', 255)->nullable(); // VARCHAR(255) NULL
            $table->json('attachments_json')->nullable();   // JSON NULL

            $table->boolean('is_featured_home')->default(false); // TINYINT(1) DEFAULT 0
            $table->string('status', 20)->default('draft');      // VARCHAR(20) DEFAULT 'draft'

            $table->timestamp('publish_at')->nullable(); // TIMESTAMP NULL
            $table->timestamp('expire_at')->nullable();  // TIMESTAMP NULL

            $table->unsignedBigInteger('views_count')->default(0); // BIGINT UNSIGNED DEFAULT 0

            // Creator user ID
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps(); // created_at, updated_at (nullable timestamps)

            $table->string('created_at_ip', 45)->nullable(); // VARCHAR(45) NULL
            $table->string('updated_at_ip', 45)->nullable(); // VARCHAR(45) NULL

            $table->softDeletes();         // deleted_at TIMESTAMP NULL
            $table->index('deleted_at');   // INDEX on deleted_at (as per schema)

            $table->json('metadata')->nullable(); // JSON NULL
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activities');
    }
};
