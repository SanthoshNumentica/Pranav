<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Temporarily change to string to allow any value
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'unpaid'");

        // 2. Map existing data
        DB::table('invoices')->where('status', 'pending')->update(['status' => 'unpaid']);
        DB::table('invoices')->where('status', 'paid')->update(['status' => 'fully_paid']);
        // If there are any nulls or other values, ensure they are 'unpaid'
        DB::table('invoices')->whereNotIn('status', ['unpaid', 'fully_paid', 'cancelled'])->update(['status' => 'unpaid']);

        // 3. Change back to the new enum
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'due', 'fully_paid', 'cancelled') NOT NULL DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Map back
        DB::table('invoices')->where('status', 'unpaid')->update(['status' => 'pending']);
        DB::table('invoices')->where('status', 'fully_paid')->update(['status' => 'paid']);
        DB::table('invoices')->where('status', 'due')->update(['status' => 'pending']);

        // 2. Revert column
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
