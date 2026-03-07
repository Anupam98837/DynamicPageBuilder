<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentEnquirySettingsTable extends Migration
{
    public function up()
    {
        Schema::create('department_enquiry_settings', function (Blueprint $table) {

            $table->id();

            // ✅ UUID
            $table->uuid('uuid')->unique();

            // ✅ Department reference (NO FK to departments)
            $table->unsignedBigInteger('department_id');

            // ✅ Order + Featured
            $table->integer('sort_order')->default(0);
            $table->boolean('featured')->default(false);

            // ✅ Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->ipAddress('created_at_ip')->nullable();

            // ✅ Timestamps with useCurrent + useCurrentOnUpdate
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // ✅ Uniques / Indexes
            $table->unique('department_id');
            $table->index(['featured', 'sort_order']);

            // ✅ Foreign key: created_by → users.id
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_enquiry_settings');
    }
}