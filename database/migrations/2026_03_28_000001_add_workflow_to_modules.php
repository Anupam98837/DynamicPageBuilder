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
        $tables = [
            'pages',
            'announcements',
            'achievements',
            'scholarships',
            'hero_carousel',
            'notices',
            'career_notices',
            'student_activities',
            'why_us',
            'placement_notices',
            'gallery',
            'events'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBP) use ($table) {
                if (!Schema::hasColumn($table, 'workflow_status')) {
                    $tableBP->enum('workflow_status', ['draft', 'pending_check', 'checked', 'approved', 'rejected'])
                            ->default('draft')
                            ->after('id');
                }
                if (!Schema::hasColumn($table, 'draft_data')) {
                    $tableBP->json('draft_data')->nullable()->after('workflow_status');
                }

                // Audit Columns for Workflow
                if (!Schema::hasColumn($table, 'request_for_approval')) {
                    $tableBP->tinyInteger('request_for_approval')->default(0)->after('draft_data');
                }
                if (!Schema::hasColumn($table, 'is_approved')) {
                    $tableBP->tinyInteger('is_approved')->default(0)->after('request_for_approval');
                }
                if (!Schema::hasColumn($table, 'is_rejected')) {
                    $tableBP->tinyInteger('is_rejected')->default(0)->after('is_approved');
                }
                if (!Schema::hasColumn($table, 'rejected_reason')) {
                    $tableBP->text('rejected_reason')->nullable()->after('is_rejected');
                }
                if (!Schema::hasColumn($table, 'approved_at')) {
                    $tableBP->timestamp('approved_at')->nullable()->after('rejected_reason');
                }
                if (!Schema::hasColumn($table, 'approved_by')) {
                    $tableBP->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'pages',
            'announcements',
            'achievements',
            'scholarships',
            'hero_carousel',
            'notices',
            'career_notices',
            'student_activities',
            'why_us',
            'placement_notices',
            'gallery',
            'events'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBP) use ($table) {
                $cols = [
                    'workflow_status', 'draft_data', 
                    'request_for_approval', 'is_approved', 'is_rejected', 
                    'rejected_reason', 'approved_at', 'approved_by'
                ];
                foreach ($cols as $c) {
                    if (Schema::hasColumn($table, $c)) {
                        $tableBP->dropColumn($c);
                    }
                }
            });
        }
    }
};
