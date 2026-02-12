<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\HasAudit;

class Gender extends Model
{
    use HasFactory, SoftDeletes, HasAudit;
    protected $fillable = ['gender_name', 'status', 'added_by', 'modified_by'];

    public function patients()
    {
        return $this->hasMany(Patient::class, 'gender_fk_id');
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'gender_fk_id');
    }
}
