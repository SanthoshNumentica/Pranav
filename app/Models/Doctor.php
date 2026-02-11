<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctors';

    protected $fillable = [
        'doctor_id',
        'title_fk_id',
        'name',
        'gender_fk_id',
        'blood_group_fk_id',
        'mobile_no',
        'email_id',
        'dob',
        'address',
        'street',
        'pincode',
        'city',
        'status',
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

    public function caseReports(): HasMany
    {
        return $this->hasMany(CaseReport::class, 'doc_ref_fk_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
