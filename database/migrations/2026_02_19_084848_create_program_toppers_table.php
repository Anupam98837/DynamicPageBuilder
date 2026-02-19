<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_toppers', function (Blueprint $table) {
            // Primary
            $table->bigIncrements('id'); // BIGINT UNSIGNED PK AUTO_INCREMENT
            $table->char('uuid', 36)->unique()->comment('External UUID');

            // Relations
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('Link if topper has a portal account');
            $table->unsignedBigInteger('department_id')->nullable()->index()->comment("Topper's department/branch");
            $table->unsignedBigInteger('created_by')->nullable()->comment('Creator user');

            // Academic
            $table->string('program', 120)->nullable()->index()->comment('BCA/BTech/MCA/MBA etc.');
            $table->string('specialization', 120)->nullable()->comment('Optional');
            $table->unsignedSmallInteger('admission_year')->nullable()->index()->comment('Start year');
            $table->unsignedSmallInteger('passing_year')->nullable()->index()->comment('Graduation year (most used filter)');
            $table->string('roll_no', 60)->nullable()->unique()->comment('Only if institute uses stable roll/registration numbers');

            // Professional (kept same as alumni as requested)
            $table->string('current_company', 160)->nullable()->index()->comment('Current employer');
            $table->string('current_role_title', 160)->nullable()->comment('Current designation');
            $table->string('industry', 120)->nullable()->index()->comment('IT/Finance/Core/Startup/Govt etc.');

            // Location
            $table->string('city', 120)->nullable()->index()->comment('Current location');
            $table->string('country', 120)->nullable()->index()->comment('Current location');

            // Content / flags
            $table->longText('note')->nullable()->comment('Topper intro/achievement note (public-friendly)');
            $table->tinyInteger('is_featured_home')->default(0)->index()->comment('Show on homepage / highlight');
            $table->string('status', 20)->default('active')->index()->comment('active/inactive');
            $table->timestamp('verified_at')->nullable()->index()->comment('When verified by admin');

            // Timestamps / IPs / Soft delete / Metadata
            $table->timestamps(); // created_at, updated_at
            $table->string('created_at_ip', 45)->nullable()->comment('Creation IP');
            $table->string('updated_at_ip', 45)->nullable()->comment('Update IP');
            $table->softDeletes()->index(); // deleted_at (Soft delete)
            $table->json('metadata')->nullable()->comment('Extra metadata');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_toppers');
    }
};
