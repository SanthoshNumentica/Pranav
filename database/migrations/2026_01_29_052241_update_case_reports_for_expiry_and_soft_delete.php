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
            if (!Schema::hasColumn('case_reports', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('status');
            }
            if (Schema::hasColumn('case_reports', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });

        // 1. Temporarily change to string to allow any value
        \DB::statement("ALTER TABLE case_reports MODIFY COLUMN status VARCHAR(255) DEFAULT 'pending'");

        // 2. Map old 'closed' status to new 'available' status
        \DB::statement("UPDATE case_reports SET status = 'available' WHERE status = 'closed'");

        // 3. Finalize enum with new values
        \DB::statement("ALTER TABLE case_reports MODIFY COLUMN status ENUM('pending', 'available', 'expired', 'deleted') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_reports', function (Blueprint $table) {
            $table->string('remarks')->nullable()->after('description');
            $table->dropColumn('expires_at');
        });

        \DB::statement("ALTER TABLE case_reports MODIFY COLUMN status ENUM('pending', 'closed') DEFAULT 'pending'");
    }
};
