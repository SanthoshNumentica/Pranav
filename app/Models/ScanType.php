<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanType extends Model
{
    use HasFactory;

    protected $table = 'scan_types';

    protected $fillable = [
        'name',
    ];

    public function scans()
    {
        return $this->hasMany(Scan::class, 'scan_type_id');
    }
}
