<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Traits\HasAudit;

class CaseReport extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'case_reports';

    protected $fillable = [
        'case_id',
        'patient_fk_id',
        'referer_id',
        'description',
        'documents',
        'status',
        'expires_at',
        'sharing_token',
        'branch_id',
        'rct_date',
        'rct_hour',
        'is_stat',
        'patient_type',
        'added_by',
        'modified_by'
    ];

    protected $casts = [
        'documents' => 'array',
        'is_stat' => 'boolean',
        'rct_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_fk_id');
    }

    public function referer(): BelongsTo
    {
        return $this->belongsTo(Referer::class, 'referer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CaseReportItem::class, 'case_report_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

}
