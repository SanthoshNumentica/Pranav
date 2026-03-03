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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('scan_id')->nullable()->after('case_report_item_id');
            $table->index(['case_report_item_id', 'scan_id']);
            
            // Note: Not adding a foreign key constraint to 'scans' table yet 
            // as it might be in a different module or have soft deletes that 
            // might cause issues depending on existing data.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex(['case_report_item_id', 'scan_id']);
            $table->dropColumn('scan_id');
        });
    }
};
