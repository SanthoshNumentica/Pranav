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
            $table->string('mrn_id')->nullable()->after('patient_id');
            $table->string('place')->nullable()->after('city');
            $table->dropColumn(['address', 'street', 'pincode', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('street')->nullable();
            $table->string('pincode')->nullable();
            $table->string('city')->nullable();
            $table->dropColumn(['mrn_id', 'place']);
        });
    }
};
