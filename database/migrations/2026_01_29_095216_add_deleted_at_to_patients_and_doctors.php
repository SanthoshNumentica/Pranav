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
            if (!Schema::hasColumn('patients', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        Schema::table('doctors', function (Blueprint $table) {
            if (!Schema::hasColumn('doctors', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
