<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasAudit
{
    /**
     * Boot the trait.
     */
    protected static function bootHasAudit(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                if (!$model->isDirty('added_by')) {
                    $model->added_by = Auth::id();
                }
                if (!$model->isDirty('modified_by')) {
                    $model->modified_by = Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && !$model->isDirty('modified_by')) {
                $model->modified_by = Auth::id();
            }
        });
    }

    /**
     * Get the user who added this record.
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get the user who last modified this record.
     */
    public function modifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
