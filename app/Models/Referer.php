<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Referer extends BaseModel
{
    use HasFactory, SoftDeletes;
    use \App\Traits\HasAudit;

    protected $fillable = [
        'referer_id',
        'referer_type_id',
        'title_id',
        'name',
        'mobile_no',
        'email_id',
        'place',
        'hospital_name',
        'hospital_id',
        'status',
        'added_by',
        'modified_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function refererType()
    {
        return $this->belongsTo(RefererType::class);
    }

    public function title()
    {
        return $this->belongsTo(Title::class);
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
