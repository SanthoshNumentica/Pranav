<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'payment_methods';

    protected $fillable = [
        'name',
        'status',
        'added_by',
        'modified_by',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
