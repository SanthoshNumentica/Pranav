<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;
    use \App\Traits\HasAudit;

    protected $fillable = [
        'name',
        'percentage',
        'status',
        'added_by',
        'modified_by'
    ];

    public function addedByUser()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
