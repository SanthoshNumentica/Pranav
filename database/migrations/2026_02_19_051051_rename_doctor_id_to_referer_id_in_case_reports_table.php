<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if target column already exists
        if (Schema::hasColumn('case_reports', 'referer_id')) {
            // Already renamed, skip rename
        } elseif (Schema::hasColumn('case_reports', 'doc_ref_fk_id')) {
            // Drop foreign key if it exists
            try {
                Schema::table('case_reports', function (Blueprint $table) {
                    $table->dropForeign('case_reports_doc_ref_fk_id_foreign');
                });
            } catch (\Exception $e) {
                // Foreign key might not exist
            }

            // Rename column
            DB::statement('ALTER TABLE case_reports CHANGE doc_ref_fk_id referer_id BIGINT UNSIGNED NOT NULL');
        }

        // Migrate Doctors to Referers to ensure FK integrity
        if (Schema::hasTable('doctors')) {
            $doctors = DB::table('doctors')->get();

            // Get or Create 'Doctor' Referer Type
            $doctorTypeId = DB::table('referer_types')->where('name', 'Doctor')->value('id');
            if (!$doctorTypeId) {
                $doctorTypeId = DB::table('referer_types')->insertGetId([
                    'name' => 'Doctor',
                    'slug' => 'doctor',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            foreach ($doctors as $doctor) {
                if (!DB::table('referers')->where('id', $doctor->id)->exists()) {
                    DB::table('referers')->insert([
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'mobile_no' => $doctor->mobile_no,
                        'referer_type_id' => $doctorTypeId,
                        'created_at' => $doctor->created_at ?? now(),
                        'updated_at' => $doctor->updated_at ?? now(),
                        // Map other fields if necessary
                    ]);
                }
            }
        }

        Schema::table('case_reports', function (Blueprint $table) {
            $table->foreign('referer_id')->references('id')->on('referers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_reports', function (Blueprint $table) {
            $table->dropForeign(['referer_id']);
        });

        DB::statement('ALTER TABLE case_reports CHANGE referer_id doc_ref_fk_id BIGINT UNSIGNED NOT NULL');

        Schema::table('case_reports', function (Blueprint $table) {
            // We can't easily restore the old FK if doctors table was dropped.
            // But assuming this runs before drop_doctors_table in rollback:
            if (Schema::hasTable('doctors')) {
                $table->foreign('doc_ref_fk_id')->references('id')->on('doctors')->onDelete('restrict');
            }
        });
    }
};
