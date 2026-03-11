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
        // 1. Remove column from patients table
        if (Schema::hasColumn('patients', 'blood_group_fk_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropColumn('blood_group_fk_id');
            });
        }

        // 2. Remove column from doctors table
        if (Schema::hasColumn('doctors', 'blood_group_fk_id')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('blood_group_fk_id');
            });
        }

        // 3. Drop blood_groups table
        Schema::dropIfExists('blood_groups');

        // 4. Remove permissions by deleting the module (cascades to permissions)
        if (Schema::hasTable('modules')) {
            \Illuminate\Support\Facades\DB::table('modules')
                ->where('name', 'like', '%blood%')
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We will not reverse the drop of the entire module since it is a permanent refactor.
    }
};
