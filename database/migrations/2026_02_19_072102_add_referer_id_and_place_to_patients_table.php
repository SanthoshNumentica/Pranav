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
            if (!Schema::hasColumn('patients', 'referer_id')) {
                $table->unsignedBigInteger('referer_id')->nullable()->after('gender_fk_id');
                // $table->foreign('referer_id')->references('id')->on('referers')->nullOnDelete();
            }
            if (!Schema::hasColumn('patients', 'place')) {
                $table->string('place')->nullable()->after('address');
            }

            // Make address fields nullable as we use 'place' now
            if (Schema::hasColumn('patients', 'street')) {
                $table->string('street')->nullable()->change();
            }
            if (Schema::hasColumn('patients', 'city')) {
                $table->string('city')->nullable()->change();
            }
            if (Schema::hasColumn('patients', 'pincode')) {
                $table->string('pincode')->nullable()->change();
            }
            if (Schema::hasColumn('patients', 'address')) {
                $table->string('address')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['referer_id', 'place']);
            // Reverting nullability is tricky without knowing original state perfectly, skipping for now
        });
    }
};
