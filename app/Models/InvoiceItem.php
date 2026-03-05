<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'case_report_item_id',
        'scan_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['scan_type_name'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getScanTypeNameAttribute(): ?string
    {
        return $this->caseReportItem?->scanType?->name;
    }

    public function caseReportItem(): BelongsTo
    {
        return $this->belongsTo(CaseReportItem::class)->withTrashed();
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class)->withTrashed();
    }
}
