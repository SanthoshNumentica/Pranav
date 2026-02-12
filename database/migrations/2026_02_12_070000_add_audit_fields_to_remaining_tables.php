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
        $tables = [
            'users',
            'roles',
            'scan_types',
            'blood_groups',
            'genders',
            'titles'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'added_by')) {
                    $table->unsignedBigInteger('added_by')->nullable()->after('id');
                    $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
                }
                if (!Schema::hasColumn($table->getTable(), 'modified_by')) {
                    $table->unsignedBigInteger('modified_by')->nullable()->after('added_by');
                    $table->foreign('modified_by')->references('id')->on('users')->onDelete('set null');
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
            'users',
            'roles',
            'scan_types',
            'blood_groups',
            'genders',
            'titles'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign([$table->getTable() . '_added_by_foreign']);
                $table->dropForeign([$table->getTable() . '_modified_by_foreign']);
                $table->dropColumn(['added_by', 'modified_by']);
            });
        }
    }
};
