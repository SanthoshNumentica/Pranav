<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CaseReport extends Model
{
    use HasFactory, SoftDeletes;

    // Columns that can be mass assigned
    protected $fillable = [
        'case_id',
        'patient_fk_id',
        'doc_ref_fk_id',
        'description',
        'remarks',
        'status',
    ];

    /**
     * Relationships
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_fk_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doc_ref_fk_id');
    }

    public function items()
    {
        return $this->hasMany(CaseReportItem::class);
    }

    /**
     * Booted method to handle events
     */
    protected static function booted()
    {
        // Auto-generate case_id safely
        static::creating(function ($model) {
            // Use DB transaction to avoid race conditions
            $lastNumber = DB::table('case_reports')
                ->select(DB::raw("MAX(CAST(SUBSTRING(case_id, 4) AS UNSIGNED)) as max_id"))
                ->value('max_id');

            $lastNumber = $lastNumber ?? 0;
            $model->case_id = 'CAS' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

            // Default status
            $model->status = $model->status ?? 'pending';
        });

        // Soft delete related items when parent is deleted
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                // Permanently delete items
                $model->items()->forceDelete();
            } else {
                // Soft delete items
                $model->items()->delete();
            }
        });

        // Restore related items when parent is restored
        static::restoring(function ($model) {
            $model->items()->withTrashed()->restore();
        });
    }
}
