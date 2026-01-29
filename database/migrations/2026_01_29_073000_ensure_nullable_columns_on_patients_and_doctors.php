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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('whatsapp_no')->nullable()->change();
            $table->string('remarks')->nullable()->change();
            $table->date('dob')->nullable()->change();
            $table->unsignedBigInteger('blood_group_fk_id')->nullable()->change();
            $table->string('patient_id')->nullable()->change();
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->string('doctor_id')->nullable()->change();
            $table->date('dob')->nullable()->change();
            $table->unsignedBigInteger('blood_group_fk_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to rollback "nullable" without potentially breaking existing null data
        // but we can try to restore them to non-nullable if we're sure they were non-nullable.
        // For whatsapp_no and remarks, they were supposed to be nullable anyway.
    }
};
