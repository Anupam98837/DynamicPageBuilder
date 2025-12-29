<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_carousel_settings', function (Blueprint $table) {
            $table->bigIncrements('id');                 // BIGINT UNSIGNED PK AUTO_INCREMENT
            $table->uuid('uuid')->unique();              // CHAR(36) UNIQUE (External UUID)

            $table->boolean('autoplay')->default(1);     // TINYINT(1) DEFAULT 1
            $table->unsignedInteger('autoplay_delay_ms')->default(4000); // INT UNSIGNED DEFAULT 4000
            $table->boolean('loop')->default(1);         // TINYINT(1) DEFAULT 1
            $table->boolean('pause_on_hover')->default(1);// TINYINT(1) DEFAULT 1
            $table->boolean('show_arrows')->default(1);  // TINYINT(1) DEFAULT 1
            $table->boolean('show_dots')->default(1);    // TINYINT(1) DEFAULT 1

            $table->string('transition', 20)->default('slide'); // VARCHAR(20) DEFAULT 'slide'
            $table->unsignedInteger('transition_ms')->default(450); // INT UNSIGNED DEFAULT 450

            // created_by BIGINT UNSIGNED FK -> users.id (nullable)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // created_at, updated_at TIMESTAMP NULL
            $table->timestamps();

            // IP audit fields
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // deleted_at TIMESTAMP NULL + INDEX
            $table->softDeletes();
            $table->index('deleted_at');

            // metadata JSON NULL
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_carousel_settings');
    }
};
