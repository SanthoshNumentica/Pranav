<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\HasAudit;

class Patient extends BaseModel
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'patients';

    protected $fillable = [
        'patient_id',
        'mrn_id',
        'title_fk_id',
        'name',
        'father_name',
        'email_id',
        'dob',
        'mobile_no',
        'whatsapp_no',
        'gender_fk_id',
        'place',
        'remarks',
        'status',

        'added_by',
        'modified_by'
    ];

    protected $casts = [
        'dob' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['age'];

    public function getAgeAttribute()
    {
        if ($this->dob) {
            return \Carbon\Carbon::parse($this->dob)->age;
        }
        return null;
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_fk_id');
    }



    public function caseReports()
    {
        return $this->hasMany(CaseReport::class, 'patient_fk_id');
    }





}
