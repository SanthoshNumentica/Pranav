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
        Schema::table('referers', function (Blueprint $table) {
            $table->string('hospital_name')->nullable()->after('place');
            $table->string('hospital_id')->nullable()->after('hospital_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referers', function (Blueprint $table) {
            $table->dropColumn(['hospital_name', 'hospital_id']);
        });
    }
};
