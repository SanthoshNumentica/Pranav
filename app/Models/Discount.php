<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends BaseModel
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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
