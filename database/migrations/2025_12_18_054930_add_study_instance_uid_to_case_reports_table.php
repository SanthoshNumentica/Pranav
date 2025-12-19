<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('case_reports', function (Blueprint $table) {
            $table->string('study_instance_uid')
                ->nullable()
                ->after('case_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('case_reports', function (Blueprint $table) {
            $table->dropColumn('study_instance_uid');
        });
    }
};
