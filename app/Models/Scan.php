<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    use HasFactory;

    protected $table = 'scans';

    protected $fillable = [
        'scan_type_id',
        'name',
    ];

    public function scanType()
    {
        return $this->belongsTo(ScanType::class, 'scan_type_id');
    }
}
