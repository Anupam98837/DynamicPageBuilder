<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_assistant_preview_orders', function (Blueprint $table) {
            // PK
            $table->bigIncrements('id');

            // External UUID
            $table->char('uuid', 36)->unique();

            // One row per department (FK + INDEX + UNIQUE)
            $table->foreignId('department_id')
                ->constrained('departments'); // references departments.id (unsigned bigint)
            $table->unique('department_id', 'technical_assistant_preview_orders_department_unique');

            // Ordered array of users.id (technical assistant list)
            $table->json('technical_assistant_user_ids_json')->nullable();

            // active/inactive
            $table->string('status', 20)->default('active')->index();

            // Creator user id
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // timestamps (nullable as per your schema)
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // IPs
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // soft delete timestamp (nullable + index)
            $table->timestamp('deleted_at')->nullable()->index();

            // Extra metadata (optional)
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_assistant_preview_orders');
    }
};