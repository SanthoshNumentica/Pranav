<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add the new JSON column if not exists
        if (!Schema::hasColumn('case_report_items', 'scan_details')) {
            Schema::table('case_report_items', function (Blueprint $table) {
                $table->json('scan_details')->nullable()->after('scan_id');
            });
        }

        // 2. Migrate existing data
        DB::table('case_report_items')->chunkById(100, function ($items) {
            foreach ($items as $item) {
                // Only update if it hasn't been migrated already
                if (empty($item->scan_details)) {
                    DB::table('case_report_items')
                        ->where('id', $item->id)
                        ->update([
                            'scan_details' => json_encode([
                                'scan_id' => $item->scan_id,
                                'amount' => $item->amount,
                            ])
                        ]);
                }
            }
        });

        // 3. Drop the old columns and foreign key if they exist
        $dbName = DB::getDatabaseName();
        $fkExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'case_report_items' AND TABLE_SCHEMA = '{$dbName}' AND CONSTRAINT_NAME = 'case_report_items_scan_id_foreign'");

        Schema::table('case_report_items', function (Blueprint $table) use ($fkExists) {
            if (!empty($fkExists)) {
                $table->dropForeign(['scan_id']);
            }
            
            $columnsToDrop = [];
            foreach (['custom_id', 'group_token', 'scan_id', 'amount'] as $col) {
                if (Schema::hasColumn('case_report_items', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add back the columns
        Schema::table('case_report_items', function (Blueprint $table) {
            $table->string('custom_id')->nullable()->after('case_report_id');
            $table->string('group_token')->nullable()->after('custom_id');
            $table->foreignId('scan_id')->nullable()->after('group_token')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2)->nullable()->after('scan_id');
        });

        // 2. Restore data from scan_details
        DB::table('case_report_items')->chunkById(100, function ($items) {
            foreach ($items as $item) {
                if ($item->scan_details) {
                    $details = json_decode($item->scan_details, true);
                    DB::table('case_report_items')
                        ->where('id', $item->id)
                        ->update([
                            'scan_id' => $details['scan_id'] ?? null,
                            'amount' => $details['amount'] ?? null,
                        ]);
                }
            }
        });

        // 3. Drop the JSON column
        Schema::table('case_report_items', function (Blueprint $table) {
            $table->dropColumn('scan_details');
        });
    }
};
