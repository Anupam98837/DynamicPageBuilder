<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notice_marquee', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // Primary key
            $table->id(); // BIGINT UNSIGNED, PK, AUTO_INCREMENT

            // Identifiers
            $table->uuid('uuid')->unique(); // CHAR(36) UNIQUE
            $table->string('slug', 160)->unique();

            // Marquee items list (title + url)
            $table->json('notice_items_json');

            // Behavior
            $table->boolean('auto_scroll')->default(true); // TINYINT(1) DEFAULT 1
            $table->unsignedInteger('scroll_speed')->default(60); // INT UNSIGNED DEFAULT 60
            $table->unsignedInteger('scroll_latency_ms')->default(0); // INT UNSIGNED DEFAULT 0
            $table->boolean('loop')->default(true); // TINYINT(1) DEFAULT 1
            $table->boolean('pause_on_hover')->default(true); // TINYINT(1) DEFAULT 1
            $table->string('direction', 10)->default('left'); // VARCHAR(10) DEFAULT 'left'

            // Publishing
            $table->string('status', 20)->default('draft')->index(); // VARCHAR(20) DEFAULT 'draft', INDEX
            $table->timestamp('publish_at')->nullable()->index(); // TIMESTAMP NULL, INDEX
            $table->timestamp('expire_at')->nullable()->index(); // TIMESTAMP NULL, INDEX

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0); // BIGINT UNSIGNED DEFAULT 0

            // Audit
            $table->unsignedBigInteger('created_by')->nullable(); // FK -> users.id, NULL
            $table->string('created_at_ip', 45)->nullable(); // VARCHAR(45) NULL
            $table->string('updated_at_ip', 45)->nullable();   // VARCHAR(45) NULL

            // Timestamps + soft deletes
            $table->timestamps();     // created_at, updated_at (nullable)
            $table->softDeletes();    // deleted_at (nullable + index)

            // Extra metadata
            $table->json('metadata')->nullable(); // JSON NULL

            // Foreign key
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notice_marquee');
    }
};
