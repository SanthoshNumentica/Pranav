<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scan extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'scans';

    protected $fillable = [
        'scan_type_id',
        'name',
        'status',
        'amount',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scanType()
    {
        return $this->belongsTo(ScanType::class, 'scan_type_id');
    }
}
