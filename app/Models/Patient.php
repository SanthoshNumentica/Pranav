<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\HasAudit;

class Patient extends Model
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
        'blood_group_fk_id',
        'gender_fk_id',
        'place',
        'remarks',
        'status',

        'referer_id',
        'added_by',
        'modified_by'
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_fk_id');
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class, 'blood_group_fk_id');
    }

    public function caseReports()
    {
        return $this->hasMany(CaseReport::class, 'patient_fk_id');
    }



    public function referer(): BelongsTo
    {
        return $this->belongsTo(Referer::class, 'referer_id');
    }

}
