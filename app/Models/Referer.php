<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Referer extends Model
{
    use HasFactory, SoftDeletes;
    use \App\Traits\HasAudit;

    protected $fillable = [
        'referer_type_id',
        'name',
        'mobile_no',
        'email_id',
        'place',
        'status',
        'added_by',
        'modified_by'
    ];

    public function refererType()
    {
        return $this->belongsTo(RefererType::class);
    }

    public function addedByUser()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
