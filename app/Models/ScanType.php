<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasAudit;

class ScanType extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'scan_types';

    protected $fillable = [
        'name',
        'status',
        'added_by',
        'modified_by'
    ];

    public function scans()
    {
        return $this->hasMany(Scan::class, 'scan_type_id');
    }
}
