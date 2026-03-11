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
        Schema::table('payments', function (Blueprint $table) {
            // Wait, looking at the schema, it's just an index KEY `payments_payment_method_id_foreign`. Let's try to drop the column, it might automatically drop the index.
            
            // Actually, we should execute raw statements just to be 100% sure it works without index errors on MariaDB.
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE payments DROP FOREIGN KEY payments_payment_method_fk_id_foreign");
            } catch (\Exception $e) { }

            try {
                 \Illuminate\Support\Facades\DB::statement("ALTER TABLE payments DROP INDEX payments_payment_method_id_foreign");
            } catch (\Exception $e) { }

            // Drop columns
            if (Schema::hasColumn('payments', 'payment_method_fk_id')) {
                $table->dropColumn('payment_method_fk_id');
            }

            if (Schema::hasColumn('payments', 'amount')) {
                $table->dropColumn('amount');
            }

            // Add new payment_details column
            if (!Schema::hasColumn('payments', 'payment_details')) {
                $table->text('payment_details')->nullable()->after('payment_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the new column
            if (Schema::hasColumn('payments', 'payment_details')) {
                $table->dropColumn('payment_details');
            }

            // Re-add the removed columns
            if (!Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 10, 2)->notNull()->after('payment_method_fk_id')->default(0);
            }

            if (!Schema::hasColumn('payments', 'payment_method_fk_id')) {
                $table->bigInteger('payment_method_fk_id')->unsigned()->notNull()->after('invoice_fk_id')->default(1);
                $table->foreign('payment_method_fk_id')->references('id')->on('payment_methods')->onDelete('cascade');
            }
        });
    }
};
