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
        // 1) Rename table
        if (Schema::hasTable('department_enquiry_settings')) {
            Schema::rename('department_enquiry_settings', 'course_enquiry_settings');
        }

        // 2) Rename column in new table
        if (Schema::hasTable('course_enquiry_settings')) {
            Schema::table('course_enquiry_settings', function (Blueprint $table) {
                if (Schema::hasColumn('course_enquiry_settings', 'department_id')) {
                    $table->renameColumn('department_id', 'course_id');
                }
                $table->string('custom_name')->nullable()->after('id');
            });
        }

        // 3) Rename column in contact_us
        if (Schema::hasTable('contact_us')) {
            Schema::table('contact_us', function (Blueprint $table) {
                if (Schema::hasColumn('contact_us', 'department_ids')) {
                    $table->renameColumn('department_ids', 'course_ids');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('contact_us')) {
            Schema::table('contact_us', function (Blueprint $table) {
                if (Schema::hasColumn('contact_us', 'course_ids')) {
                    $table->renameColumn('course_ids', 'department_ids');
                }
            });
        }

        if (Schema::hasTable('course_enquiry_settings')) {
            Schema::table('course_enquiry_settings', function (Blueprint $table) {
                if (Schema::hasColumn('course_enquiry_settings', 'custom_name')) {
                    $table->dropColumn('custom_name');
                }
                if (Schema::hasColumn('course_enquiry_settings', 'course_id')) {
                    $table->renameColumn('course_id', 'department_id');
                }
            });
            Schema::rename('course_enquiry_settings', 'department_enquiry_settings');
        }
    }
};
