<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    protected $fillable = ['gender_name'];

    public function patients()
    {
        return $this->hasMany(Patient::class, 'gender_fk_id');
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'gender_fk_id');
    }
}
