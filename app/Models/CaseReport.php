<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\HasAudit;

class CaseReport extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'case_reports';

    protected $fillable = [
        'case_id',
        'patient_fk_id',
        'doc_ref_fk_id',
        'description',
        'documents',
        'status',
        'expires_at',
        'sharing_token',
        'branch_id',
        'added_by',
        'modified_by'
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_fk_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doc_ref_fk_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CaseReportItem::class, 'case_report_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

}
