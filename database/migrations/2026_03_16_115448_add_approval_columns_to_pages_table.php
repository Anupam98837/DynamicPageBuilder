<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'request_for_approval')) {
                $table->tinyInteger('request_for_approval')->default(0)->after('status');
            }
            if (!Schema::hasColumn('pages', 'is_approved')) {
                $table->tinyInteger('is_approved')->default(0)->after('request_for_approval');
            }
            if (!Schema::hasColumn('pages', 'is_rejected')) {
                $table->tinyInteger('is_rejected')->default(0)->after('is_approved');
            }
            if (!Schema::hasColumn('pages', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('is_rejected');
            }
            if (!Schema::hasColumn('pages', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('rejected_reason');
            }
            if (!Schema::hasColumn('pages', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $cols = ['request_for_approval', 'is_approved', 'is_rejected', 'rejected_reason', 'approved_at', 'approved_by'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
