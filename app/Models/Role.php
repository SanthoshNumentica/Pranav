<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
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
