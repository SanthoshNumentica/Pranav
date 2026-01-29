<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScanType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scan_types';

    protected $fillable = [
        'name',
        'status',
    ];

    public function scans()
    {
        return $this->hasMany(Scan::class, 'scan_type_id');
    }
}
