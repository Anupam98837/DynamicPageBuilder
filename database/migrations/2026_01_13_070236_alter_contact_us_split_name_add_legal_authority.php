<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterContactUsSplitNameAddLegalAuthority extends Migration
{
    public function up()
    {
        // 1) Add new columns
        Schema::table('contact_us', function (Blueprint $table) {
            // Keep nullable to avoid breaking existing rows before backfill
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');

            // Store legal authority / consent text shown at the time of submission
            $table->json('legal_authority_json')->nullable()->after('message');
        });

        // 2) Backfill first_name/last_name from old "name"
        if (Schema::hasColumn('contact_us', 'name')) {
            DB::table('contact_us')
                ->select('id', 'name')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $full = trim((string) $row->name);

                        if ($full === '') {
                            DB::table('contact_us')->where('id', $row->id)->update([
                                'first_name' => null,
                                'last_name'  => null,
                            ]);
                            continue;
                        }

                        // Split by spaces (multiple spaces safe)
                        $parts = preg_split('/\s+/', $full) ?: [];
                        $first = $parts[0] ?? null;
                        $last  = null;

                        if (count($parts) > 1) {
                            $last = implode(' ', array_slice($parts, 1));
                        }

                        DB::table('contact_us')->where('id', $row->id)->update([
                            'first_name' => $first,
                            'last_name'  => $last,
                        ]);
                    }
                });

            // 3) Drop old name column
            Schema::table('contact_us', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    public function down()
    {
        // 1) Add back "name"
        Schema::table('contact_us', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        // 2) Backfill name from first_name + last_name
        DB::table('contact_us')
            ->select('id', 'first_name', 'last_name')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $first = trim((string) ($row->first_name ?? ''));
                    $last  = trim((string) ($row->last_name ?? ''));

                    $full = trim($first . ' ' . $last);
                    $full = $full === '' ? null : $full;

                    DB::table('contact_us')->where('id', $row->id)->update([
                        'name' => $full,
                    ]);
                }
            });

        // 3) Drop new columns
        Schema::table('contact_us', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'legal_authority_json']);
        });
    }
}
