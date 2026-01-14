<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sticky_buttons', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // id: BIGINT UNSIGNED PK AUTO_INCREMENT
            $table->id();

            // uuid: CHAR(36) UNIQUE NOT NULL
            $table->char('uuid', 36)
                ->unique();

            // buttons_json: JSON NULL (JSON array storing selected contact_info)
            $table->json('buttons_json')
                ->nullable();

            // status: VARCHAR(20) DEFAULT active + INDEX NOT NULL
            $table->string('status', 20)
                ->default('active')
                ->index();

            // created_by: BIGINT UNSIGNED NULL + INDEX + FK users.id
            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->index();

            // created_at, updated_at: TIMESTAMP NULL
            $table->timestamps();

            // created_at_ip, updated_at_ip: VARCHAR(45) NULL
            $table->string('created_at_ip', 45)
                ->nullable();

            $table->string('updated_at_ip', 45)
                ->nullable();

            // deleted_at: TIMESTAMP NULL + INDEX
            $table->softDeletes();
            $table->index('deleted_at');

            // metadata: JSON NULL
            $table->json('metadata')
                ->nullable();

            // FK: created_by -> users.id (SET NULL on delete, CASCADE on update)
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sticky_buttons');
    }
};
