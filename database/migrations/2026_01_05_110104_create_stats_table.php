<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED PK AI

            $table->uuid('uuid')->unique();                 // CHAR(36) UNIQUE
            $table->string('slug', 160)->unique();          // VARCHAR(160) UNIQUE
            $table->string('background_image_url', 160);    // VARCHAR(160) NOT NULL

            $table->json('stats_items_json');               // JSON NOT NULL (list of stats)

            $table->boolean('auto_scroll')->default(true);  // TINYINT(1) DEFAULT 1
            $table->unsignedInteger('scroll_latency_ms')->default(3000); // INT UNSIGNED DEFAULT 3000
            $table->boolean('loop')->default(true);         // TINYINT(1) DEFAULT 1
            $table->boolean('show_arrows')->default(true);  // TINYINT(1) DEFAULT 1
            $table->boolean('show_dots')->default(false);   // TINYINT(1) DEFAULT 0

            $table->string('status', 20)->default('draft')->index(); // VARCHAR(20) DEFAULT 'draft', INDEX

            $table->timestamp('publish_at')->nullable()->index(); // TIMESTAMP NULL, INDEX
            $table->timestamp('expire_at')->nullable()->index();  // TIMESTAMP NULL, INDEX

            $table->unsignedBigInteger('views_count')->default(0); // BIGINT UNSIGNED DEFAULT 0

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate(); // FK -> users.id (nullable)

            $table->timestamps(); // created_at, updated_at (nullable by default)

            $table->string('created_at_ip', 45)->nullable(); // VARCHAR(45) NULL
            $table->string('updated_at_ip', 45)->nullable(); // VARCHAR(45) NULL

            $table->timestamp('deleted_at')->nullable()->index(); // soft delete timestamp + INDEX
            $table->json('metadata')->nullable(); // JSON NULL
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats');
    }
};
