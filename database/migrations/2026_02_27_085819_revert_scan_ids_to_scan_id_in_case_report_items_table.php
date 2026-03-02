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
        // Use raw SQL for better compatibility with older MariaDB versions in XAMPP
        \DB::statement("ALTER TABLE case_report_items CHANGE scan_ids scan_id BIGINT UNSIGNED NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE case_report_items CHANGE scan_id scan_ids LONGTEXT NULL");
    }
};
