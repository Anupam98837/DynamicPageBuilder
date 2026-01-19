<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {

            // ✅ Approval Flags (0/1)
            $table->tinyInteger('request_for_approval')->default(0)->after('status');
            $table->tinyInteger('is_approved')->default(0)->after('request_for_approval');

        });
    }

    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {

            $table->dropColumn('request_for_approval');
            $table->dropColumn('is_approved');

        });
    }
};
