<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'patients';

    protected $fillable = [
        'patient_id',
        'title_fk_id',
        'name',
        'father_name',
        'email_id',
        'dob',
        'mobile_no',
        'whatsapp_no',
        'blood_group_fk_id',
        'gender_fk_id',
        'address',
        'street',
        'pincode',
        'city',
        'remarks',
        'status'
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
}
