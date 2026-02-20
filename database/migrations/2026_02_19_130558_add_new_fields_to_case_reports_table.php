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
        Schema::table('case_reports', function (Blueprint $table) {
            $table->string('srf_no')->nullable()->after('case_id');
            $table->date('rct_date')->nullable()->after('srf_no');
            $table->string('rct_hour', 10)->nullable()->after('rct_date');
            $table->boolean('is_stat')->default(false)->after('rct_hour');
            $table->enum('patient_type', ['in_patient', 'out_patient'])->default('out_patient')->after('is_stat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_reports', function (Blueprint $table) {
            $table->dropColumn(['srf_no', 'rct_date', 'rct_hour', 'is_stat', 'patient_type']);
        });
    }
};
