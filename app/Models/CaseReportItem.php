<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseReportItem extends Model
{
    use HasFactory;

    protected $table = 'case_report_items';

    protected $fillable = [
        'case_report_id',
        'scan_type_id',
        'scan_id',
        'documents',
        'remarks',
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    public function caseReport(): BelongsTo
    {
        return $this->belongsTo(CaseReport::class);
    }

    public function scanType(): BelongsTo
    {
        return $this->belongsTo(ScanType::class);
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}
