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
        Schema::table('case_report_items', function (Blueprint $table) {
            $table->string('group_token')->nullable()->after('custom_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_report_items', function (Blueprint $table) {
            $table->dropColumn('group_token');
        });
    }
};
