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
        Schema::table('doctors', function (Blueprint $table) {
            $table->unsignedBigInteger('title_fk_id')->nullable()->change();
            $table->unsignedBigInteger('gender_fk_id')->nullable()->change();
            $table->string('email_id')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('street')->nullable()->change();
            $table->string('pincode')->nullable()->change();
            $table->string('city')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            // Note: Reverting nullable columns can be tricky if data exists that is null.
            // We assume safe revert here but in production this might need data cleanup.
            $table->unsignedBigInteger('title_fk_id')->nullable(false)->change();
            $table->unsignedBigInteger('gender_fk_id')->nullable(false)->change();
            $table->string('email_id')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->string('street')->nullable(false)->change();
            $table->string('pincode')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
        });
    }
};
