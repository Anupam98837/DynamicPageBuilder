<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contact_us', function (Blueprint $table) {
            // 1. Add name column
            if (!Schema::hasColumn('contact_us', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            
            // 2. Make message nullable
            $table->text('message')->nullable()->change();
        });

        // 3. Copy data from first_name and last_name to name
        DB::table('contact_us')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $fullName = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                DB::table('contact_us')
                    ->where('id', $row->id)
                    ->update(['name' => $fullName]);
            }
        });

        Schema::table('contact_us', function (Blueprint $table) {
            // 4. Drop first_name and last_name
            if (Schema::hasColumn('contact_us', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('contact_us', 'last_name')) {
                $table->dropColumn('last_name');
            }
            
            // Make name required now that data is copied
            $table->string('name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_us', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_us', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('contact_us', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            
            $table->text('message')->nullable(false)->change();
        });

        // Copy data back (best effort: name into first_name)
        DB::table('contact_us')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('contact_us')
                    ->where('id', $row->id)
                    ->update(['first_name' => $row->name]);
            }
        });

        Schema::table('contact_us', function (Blueprint $table) {
            if (Schema::hasColumn('contact_us', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
