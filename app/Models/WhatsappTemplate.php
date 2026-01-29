<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $fillable = [
        'event_name',
        'template_content',
        'whatsapp_content',
        'parameters',
        'allow_to_send',
        'status',
        'sender_id',
    ];
}