<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_log', function (Blueprint $table) {
            $table->id();

            // New structure
            $table->text('message')->nullable();
            $table->enum('message_type', ['whatsapp', 'sms']);
            $table->enum('status', ['not_sent', 'sent', 'failed'])->default('not_sent');
            $table->enum('resend', ['yes', 'no'])->default('no');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('sender_mobile_no', 50)->nullable();
            $table->string('recipient_mobile_no', 50)->nullable();
            $table->text('response')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_log');
    }
};
