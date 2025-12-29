<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // PK
            $table->bigIncrements('id'); // BIGINT UNSIGNED, PK, AUTO_INCREMENT

            // Unique identifiers
            $table->char('uuid', 36)->unique();      // CHAR(36) UNIQUE
            $table->string('slug', 160)->unique();   // VARCHAR(160) UNIQUE

            // Relations
            $table->unsignedBigInteger('department_id')->nullable(); // FK -> departments.id (nullable)
            $table->unsignedBigInteger('created_by')->nullable();    // FK -> users.id (nullable)

            // Content
            $table->string('title', 255);            // VARCHAR(255) NOT NULL
            $table->longText('description')->nullable(); // LONGTEXT NULL
            $table->string('cover_image_url', 255)->nullable(); // VARCHAR(255) NULL
            $table->json('gallery_images_json')->nullable();     // JSON NULL
            $table->string('location', 255)->nullable();         // VARCHAR(255) NULL

            // Event dates/times
            $table->date('event_start_date')->nullable(); // DATE NULL
            $table->date('event_end_date')->nullable();   // DATE NULL
            $table->time('event_start_time')->nullable(); // TIME NULL
            $table->time('event_end_time')->nullable();   // TIME NULL

            // Flags & ordering
            $table->tinyInteger('is_featured_home')->default(0); // TINYINT(1) DEFAULT 0
            $table->unsignedInteger('sort_order')->default(0);   // INT UNSIGNED DEFAULT 0

            // Publish status
            $table->string('status', 20)->default('draft'); // VARCHAR(20) DEFAULT 'draft'
            $table->timestamp('publish_at')->nullable();     // TIMESTAMP NULL
            $table->timestamp('expire_at')->nullable();      // TIMESTAMP NULL

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0); // BIGINT UNSIGNED DEFAULT 0

            // Audit/meta
            $table->timestamps(); // created_at, updated_at (TIMESTAMP NULL)
            $table->string('created_at_ip', 45)->nullable(); // VARCHAR(45) NULL
            $table->string('updated_at_ip', 45)->nullable(); // VARCHAR(45) NULL
            $table->timestamp('deleted_at')->nullable();     // TIMESTAMP NULL (soft delete)
            $table->json('metadata')->nullable();            // JSON NULL

            // Indexes (as per schema)
            $table->index('event_start_date');
            $table->index('event_end_date');
            $table->index('sort_order');
            $table->index('status');
            $table->index('publish_at');
            $table->index('expire_at');
            $table->index('deleted_at');

            // Foreign Keys
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
        Schema::table('events', function (Blueprint $table) {
            // Drop FKs first (safe rollback)
            $table->dropForeign(['department_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('events');
    }
};
