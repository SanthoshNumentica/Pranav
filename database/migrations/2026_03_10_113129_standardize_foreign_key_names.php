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
        // 1. case_report_items
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE case_report_items CHANGE case_report_id case_report_fk_id BIGINT UNSIGNED NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE case_report_items CHANGE scan_type_id scan_types_fk_id BIGINT UNSIGNED NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE case_report_items CHANGE item_reference scan_type_id VARCHAR(191) DEFAULT NULL");

        // 2. invoices
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE invoices CHANGE case_report_id case_report_fk_id BIGINT UNSIGNED NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE invoices CHANGE patient_id patient_fk_id BIGINT UNSIGNED NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE invoices CHANGE branch_id branch_fk_id BIGINT UNSIGNED NOT NULL");
        if (Schema::hasColumn('invoices', 'discount_id')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE invoices CHANGE discount_id discount_fk_id BIGINT UNSIGNED DEFAULT NULL");
        }
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE invoices CHANGE invoice_no invoice_id VARCHAR(191) NOT NULL");

        // 3. invoice_items
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE invoice_items CHANGE invoice_id invoice_fk_id BIGINT UNSIGNED NOT NULL");
        if (Schema::hasColumn('invoice_items', 'case_report_item_id')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE invoice_items CHANGE case_report_item_id case_report_item_fk_id BIGINT UNSIGNED DEFAULT NULL");
        }
        if (Schema::hasColumn('invoice_items', 'scan_id')) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE invoice_items CHANGE scan_id scan_fk_id BIGINT UNSIGNED DEFAULT NULL");
        }

        // 4. payments
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE payments CHANGE invoice_id invoice_fk_id BIGINT UNSIGNED NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE payments CHANGE payment_method_id payment_method_fk_id BIGINT UNSIGNED NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration
    }
};
