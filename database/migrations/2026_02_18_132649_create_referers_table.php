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
        Schema::create('referers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referer_type_id')->constrained('referer_types')->onDelete('cascade');
            $table->string('name');
            $table->string('mobile_no');
            $table->string('email_id')->nullable();
            $table->string('place')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referers');
    }
};
