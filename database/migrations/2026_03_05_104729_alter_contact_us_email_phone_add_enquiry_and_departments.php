<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterContactUsEmailPhoneAddEnquiryAndDepartments extends Migration
{
    public function up()
    {
        // 1) Backfill existing NULL phones so we can safely make phone NOT NULL
        if (Schema::hasTable('contact_us') && Schema::hasColumn('contact_us', 'phone')) {
            DB::table('contact_us')->whereNull('phone')->update(['phone' => '']);
        }

        // 2) Alter existing columns + add new columns
        Schema::table('contact_us', function (Blueprint $table) {

            // ✅ email nullable
            // ✅ phone required (NOT NULL)
            // NOTE: ->change() may require doctrine/dbal in some setups.
            // We'll do change() in a try-catch fallback below.
            
            // ✅ New columns
            if (!Schema::hasColumn('contact_us', 'is_admission_enquiry')) {
                $table->boolean('is_admission_enquiry')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('contact_us', 'department_ids')) {
                // stores array like [1,2,3]
                $table->json('department_ids')->nullable()->after('is_admission_enquiry');
            }
        });

        // 3) Modify email/phone types with fallback (works even without doctrine/dbal)
        try {
            Schema::table('contact_us', function (Blueprint $table) {
                $table->string('email')->nullable()->change();
                $table->string('phone', 20)->nullable(false)->change();
            });
        } catch (\Throwable $e) {
            // Fallback for MySQL/MariaDB
            DB::statement("ALTER TABLE `contact_us` MODIFY `email` VARCHAR(255) NULL");
            DB::statement("ALTER TABLE `contact_us` MODIFY `phone` VARCHAR(20) NOT NULL");
        }
    }

    public function down()
    {
        // 1) Drop added columns
        Schema::table('contact_us', function (Blueprint $table) {
            if (Schema::hasColumn('contact_us', 'department_ids')) {
                $table->dropColumn('department_ids');
            }
            if (Schema::hasColumn('contact_us', 'is_admission_enquiry')) {
                $table->dropColumn('is_admission_enquiry');
            }
        });

        // 2) Backfill NULL emails (because we are going back to NOT NULL)
        if (Schema::hasTable('contact_us') && Schema::hasColumn('contact_us', 'email')) {
            DB::table('contact_us')->whereNull('email')->update(['email' => '']);
        }

        // 3) Revert email to NOT NULL, phone to nullable
        try {
            Schema::table('contact_us', function (Blueprint $table) {
                $table->string('email')->nullable(false)->change();
                $table->string('phone', 20)->nullable()->change();
            });
        } catch (\Throwable $e) {
            DB::statement("ALTER TABLE `contact_us` MODIFY `email` VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE `contact_us` MODIFY `phone` VARCHAR(20) NULL");
        }
    }
}