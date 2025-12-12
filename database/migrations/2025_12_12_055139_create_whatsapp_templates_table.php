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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_name'); // Template identifier
            $table->text('template_content'); // Message content with placeholders
            $table->string('parameters')->nullable(); // Placeholder names comma-separated
            $table->boolean('allow_to_send')->default(true); // Allow sending
            $table->boolean('status')->default(true); // Active/Inactive
            $table->string('sender_id')->nullable(); // Optional sender name/id
            $table->text('whatsapp_content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
