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
        Schema::table('scan_types', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('name');
        });
        Schema::table('scans', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('name');
        });
        Schema::table('genders', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('gender_name');
        });
        Schema::table('blood_groups', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scan_types', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('genders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('blood_groups', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
