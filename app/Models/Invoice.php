<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasAudit;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'invoice_no',
        'case_report_id',
        'patient_id',
        'branch_id',
        'discount_id',
        'sub_total',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'status', // pending, paid, cancelled
        'invoice_date',
        'notes',
        'added_by',
        'modified_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function caseReport(): BelongsTo
    {
        return $this->belongsTo(CaseReport::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
