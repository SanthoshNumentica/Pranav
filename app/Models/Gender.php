<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gender extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['gender_name', 'status'];

    public function patients()
    {
        return $this->hasMany(Patient::class, 'gender_fk_id');
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'gender_fk_id');
    }
}
