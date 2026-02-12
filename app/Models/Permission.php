<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = ['module_id', 'action_id'];

    protected $appends = ['name'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    // Override the getNameAttribute to dynamically generate the name if not set
    public function getNameAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->relationLoaded('module') && $this->relationLoaded('action')) {
            return strtolower($this->module->name) . '-' . strtolower($this->action->name);
        }

        return 'permission-' . $this->id;
    }

    public function getGuardNameAttribute()
    {
        return 'web';
    }

    public static function findByName(string $name, $guardName = null): \Spatie\Permission\Contracts\Permission
    {
        $parts = explode('-', $name);
        if (count($parts) < 2) {
            throw \Spatie\Permission\Exceptions\PermissionDoesNotExist::create($name, $guardName ?? 'web');
        }

        $actionName = array_pop($parts);
        $moduleName = implode('-', $parts);

        $module = Module::where('name', $moduleName)->first();
        $action = Action::where('name', $actionName)->first();

        if (!$module || !$action) {
            throw \Spatie\Permission\Exceptions\PermissionDoesNotExist::create($name, $guardName ?? 'web');
        }

        $permission = static::where('module_id', $module->id)
            ->where('action_id', $action->id)
            ->first();

        if (!$permission) {
            throw \Spatie\Permission\Exceptions\PermissionDoesNotExist::create($name, $guardName ?? 'web');
        }

        return $permission;
    }

    public static function findById($id, $guardName = null): \Spatie\Permission\Contracts\Permission
    {
        $permission = static::find($id);

        if (!$permission) {
            throw \Spatie\Permission\Exceptions\PermissionDoesNotExist::withId($id, $guardName ?? 'web');
        }

        return $permission;
    }
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            unset($model->attributes['guard_name']);
            unset($model->guard_name);
        });

        static::saving(function ($model) {
            unset($model->attributes['guard_name']);
            unset($model->guard_name);
        });
    }
}
