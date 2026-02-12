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
        $configs = [
            'patients' => 'status',
            'doctors' => 'status',
            'case_reports' => 'expires_at',
            'users' => 'remember_token',
            'roles' => 'guard_name',
            'scan_types' => 'status',
            'blood_groups' => 'status',
            'genders' => 'status',
            'titles' => 'status'
        ];

        foreach ($configs as $table => $afterColumn) {
            Schema::table($table, function (Blueprint $table) use ($afterColumn) {
                $table->unsignedBigInteger('added_by')->nullable()->change()->after($afterColumn);
                $table->unsignedBigInteger('modified_by')->nullable()->change()->after('added_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reordering columns back is usually not necessary but for completeness:
        // We don't have a record of where they were exactly for every table without more research.
        // Usually, the 'added_by' was after 'id' in my previous migrations.
    }
};
