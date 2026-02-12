<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasAudit;

class Title extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'titles';

    protected $fillable = ['title_name', 'status', 'added_by', 'modified_by'];
}
