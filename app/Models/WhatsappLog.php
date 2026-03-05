<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends BaseModel
{
    use HasFactory;
    protected $table = 'whatsapp_log';
    protected $fillable = [
        'message',
        'message_type',
        'status',
        'resend',
        'created_by',
        'response',
        'sender_mobile_no',
        'recipient_mobile_no',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}