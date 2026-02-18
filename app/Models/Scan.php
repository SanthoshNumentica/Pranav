<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scans';

    protected $fillable = [
        'scan_type_id',
        'name',
        'status',
        'amount',
    ];

    public function scanType()
    {
        return $this->belongsTo(ScanType::class, 'scan_type_id');
    }
}
