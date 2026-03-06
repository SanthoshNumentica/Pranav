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
        // For MariaDB 10.4, use CHANGE COLUMN if columns still have old names
        if (Schema::hasColumn('case_reports', 'rct_date')) {
            DB::statement('ALTER TABLE case_reports CHANGE rct_date scanning_date DATE NULL');
        }
        if (Schema::hasColumn('case_reports', 'rct_hour')) {
            DB::statement('ALTER TABLE case_reports CHANGE rct_hour check_in VARCHAR(10) NULL');
        }

        Schema::table('case_reports', function (Blueprint $table) {
            // Drop old columns if they exist
            $columnsToDrop = [];
            foreach (['patient_type', 'in_patient', 'out_patient', 'is_stat'] as $col) {
                if (Schema::hasColumn('case_reports', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            // Add new column after renamed column if it doesn't exist
            if (!Schema::hasColumn('case_reports', 'is_stat_case')) {
                $table->boolean('is_stat_case')->default(false)->after('check_in');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_reports', function (Blueprint $table) {
            if (Schema::hasColumn('case_reports', 'is_stat_case')) {
                $table->dropColumn('is_stat_case');
            }
        });

        if (Schema::hasColumn('case_reports', 'scanning_date')) {
            DB::statement('ALTER TABLE case_reports CHANGE scanning_date rct_date DATE NULL');
        }
        if (Schema::hasColumn('case_reports', 'check_in')) {
            DB::statement('ALTER TABLE case_reports CHANGE check_in rct_hour VARCHAR(10) NULL');
        }

        Schema::table('case_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('case_reports', 'patient_type')) {
                $table->enum('patient_type', ['in_patient', 'out_patient'])->default('out_patient');
            }
            if (!Schema::hasColumn('case_reports', 'in_patient')) {
                $table->boolean('in_patient')->default(false);
            }
            if (!Schema::hasColumn('case_reports', 'out_patient')) {
                $table->boolean('out_patient')->default(true);
            }
            if (!Schema::hasColumn('case_reports', 'is_stat')) {
                $table->boolean('is_stat')->default(false);
            }
        });
    }
};
