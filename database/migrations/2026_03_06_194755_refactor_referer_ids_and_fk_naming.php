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
        // 1. Rename column in case_reports using raw SQL for MariaDB compatibility
        if (Schema::hasColumn('case_reports', 'referer_id')) {
            // Drop foreign key if it exists, assuming standard naming. It actually might not exist since it was just a column in previous migration.
            // But we can just change the column name.
            DB::statement('ALTER TABLE case_reports CHANGE referer_id referer_fk_id BIGINT UNSIGNED NULL');
        }

        // 2. Format existing referer IDs in referers table to use 3-digit padding (REF001)
        $referers = DB::table('referers')->orderBy('id')->get();
        $counter = 1;
        foreach ($referers as $referer) {
            $newId = 'REF' . str_pad($counter, 3, '0', STR_PAD_LEFT);
            DB::table('referers')->where('id', $referer->id)->update(['referer_id' => $newId]);
            $counter++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse column formatting for case_reports
        if (Schema::hasColumn('case_reports', 'referer_fk_id')) {
            DB::statement('ALTER TABLE case_reports CHANGE referer_fk_id referer_id BIGINT UNSIGNED NULL');
        }
    }
};
