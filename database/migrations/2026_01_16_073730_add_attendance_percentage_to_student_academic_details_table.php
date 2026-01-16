<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_academic_details', function (Blueprint $table) {

            // Attendance percentage (0.00 to 100.00)
            $table->decimal('attendance_percentage', 5, 2)
                ->unsigned()
                ->nullable()
                ->after('session')
                ->comment('Student attendance percentage (0-100)');
        });
    }

    public function down(): void
    {
        Schema::table('student_academic_details', function (Blueprint $table) {
            $table->dropColumn('attendance_percentage');
        });
    }
};
