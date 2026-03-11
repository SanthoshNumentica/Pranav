<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasAudit;

class Payment extends BaseModel
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'payment_id',
        'invoice_fk_id',
        'payment_method_fk_id',
        'amount',
        'payment_date',
        'payment_details',
        'added_by',
        'modified_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_fk_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_fk_id');
    }
}
