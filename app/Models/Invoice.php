<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasAudit;

class Invoice extends BaseModel
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'invoice_id',
        'case_report_fk_id',
        'patient_fk_id',
        'branch_fk_id',
        'discount_fk_id',
        'referer_fk_id',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function caseReport(): BelongsTo
    {
        return $this->belongsTo(CaseReport::class, 'case_report_fk_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_fk_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_fk_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_fk_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_fk_id');
    }
}
