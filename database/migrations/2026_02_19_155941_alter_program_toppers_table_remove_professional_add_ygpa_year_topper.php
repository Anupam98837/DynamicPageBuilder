<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_toppers', function (Blueprint $table) {

            // ✅ Remove fields
            // (Dropping multiple columns together is supported in MySQL)
            $table->dropColumn([
                'current_company',
                'current_role_title',
                'industry',
            ]);

            // ✅ Add new fields
            // YGPA example: 7.1 => stored as 7.10
            // decimal(4,2) allows up to 10.00 comfortably
            $table->unsignedTinyInteger('year_topper')
                ->nullable()
                ->index()
                ->after('passing_year')
                ->comment('Year topper: 1=1st year, 2=2nd year, 3=3rd year, etc.');

            $table->decimal('ygpa', 4, 2)
                ->nullable()
                ->index()
                ->after('year_topper')
                ->comment('Year GPA (e.g., 7.10)');
        });
    }

    public function down(): void
    {
        Schema::table('program_toppers', function (Blueprint $table) {

            // ✅ Remove newly added fields
            $table->dropColumn(['ygpa', 'year_topper']);

            // ✅ Restore removed fields (same as original migration)
            $table->string('current_company', 160)
                ->nullable()
                ->index()
                ->after('roll_no')
                ->comment('Current employer');

            $table->string('current_role_title', 160)
                ->nullable()
                ->after('current_company')
                ->comment('Current designation');

            $table->string('industry', 120)
                ->nullable()
                ->index()
                ->after('current_role_title')
                ->comment('IT/Finance/Core/Startup/Govt etc.');
        });
    }
};
