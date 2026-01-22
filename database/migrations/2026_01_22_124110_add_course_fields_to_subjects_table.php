<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {

            // ✅ Add course_id (optional)
            if (!Schema::hasColumn('subjects', 'course_id')) {
                $table->foreignId('course_id')
                    ->nullable()
                    ->after('department_id')
                    ->constrained('courses')
                    ->nullOnDelete();
            }

            // ✅ Add course_semester_id (optional)
            if (!Schema::hasColumn('subjects', 'course_semester_id')) {
                $table->foreignId('course_semester_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('course_semesters')
                    ->nullOnDelete();
            }

        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {

            // ✅ Drop FK + column (course_semester_id)
            if (Schema::hasColumn('subjects', 'course_semester_id')) {
                $table->dropForeign(['course_semester_id']);
                $table->dropColumn('course_semester_id');
            }

            // ✅ Drop FK + column (course_id)
            if (Schema::hasColumn('subjects', 'course_id')) {
                $table->dropForeign(['course_id']);
                $table->dropColumn('course_id');
            }

        });
    }
};
