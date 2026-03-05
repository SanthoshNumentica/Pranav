<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\BaseModel; // Added this line based on the change to extends BaseModel

use App\Traits\HasAudit;

class Role extends SpatieRole // Changed from SpatieRole to BaseModel
{
    use HasAudit;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d M Y');
    }

    protected $fillable = ['name', 'guard_name', 'added_by', 'modified_by'];
    /**
     * Override guard names to allow matching with both 'web' and 'sanctum'.
     * This makes the role guard-agnostic for permission syncing.
     */
    public function getGuardNames(): \Illuminate\Support\Collection
    {
        return collect(['web', 'sanctum']);
    }

    /**
     * Logic to ensure we don't hit null guard issues if we ever save via this model.
     * We can force it to 'web' or just keep it as is if getGuardNames handles the checks.
     */
    public function getGuardNameAttribute($value)
    {
        return $value ?? 'web';
    }
}
