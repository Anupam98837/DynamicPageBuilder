<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('content_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // Model class name (e.g. App\Models\Page)
            $table->unsignedBigInteger('model_id'); // Record ID
            $table->unsignedBigInteger('user_id'); // User who performed action
            $table->string('action'); // draft, submitted, checked, approved, rejected
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_approval_logs');
    }
};
