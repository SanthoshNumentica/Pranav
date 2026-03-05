<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseReportItem extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'case_report_items';

    protected $fillable = [
        'case_report_id',
        'scan_type_id',
        'item_reference',
        'scan_details',
        'documents',
        'remarks',
        'total_amount',
    ];

    protected $appends = ['scans_with_names'];

    protected $casts = [
        'scan_details' => 'array',
        'documents' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        // For backward compatibility and single items, return the first scan ID if it's an array
        $details = $this->scan_details;
        $scanId = is_array($details) 
            ? ($details[0]['scan_id'] ?? ($details['scan_id'] ?? null))
            : null;
            
        return $this->belongsTo(Scan::class, 'id', 'id')->where('id', $scanId);
    }

    public function getScansWithNamesAttribute(): array
    {
        $details = $this->scan_details ?? [];
        $scans = is_array($details) ? (isset($details[0]) ? $details : [$details]) : [];
        
        $enrichedScans = [];
        foreach ($scans as $s) {
            if (empty($s['scan_id'])) continue;
            
            $scanName = $s['scan_name'] ?? null;
            if (!$scanName) {
                $scanModel = \App\Models\Scan::find($s['scan_id']);
                $scanName = $scanModel ? $scanModel->name : 'Scan';
            }
            
            $enrichedScans[] = [
                'scan_id' => $s['scan_id'],
                'scan_name' => $scanName,
                'amount' => $s['amount'] ?? 0,
            ];
        }
        
        return $enrichedScans;
    }
}
