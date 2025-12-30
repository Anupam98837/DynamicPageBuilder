<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('placed_students', function (Blueprint $table) {
            // Primary
            $table->id();

            // External UUID (CHAR(36)) + UNIQUE
            $table->char('uuid', 36)->unique();

            // FKs
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('placement_notice_id')
                ->nullable()
                ->constrained('placement_notices')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Fields
            $table->string('role_title', 255)->nullable();
            $table->decimal('ctc', 6, 2)->nullable();

            $table->date('offer_date')->nullable()->index();
            $table->date('joining_date')->nullable()->index();

            $table->string('offer_letter_url', 255)->nullable();
            $table->longText('note')->nullable();

            $table->boolean('is_featured_home')->default(false);

            $table->unsignedInteger('sort_order')->default(0)->index();

            $table->string('status', 20)->default('active')->index();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Timestamps (nullable as per schema)
            $table->timestamps();

            // IP audit
            $table->string('created_at_ip', 45)->nullable();
            $table->string('updated_at_ip', 45)->nullable();

            // Soft delete timestamp + INDEX
            $table->timestamp('deleted_at')->nullable()->index();

            // Extra metadata
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placed_students');
    }
};
