<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated users with filters.
     */
    public function listUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%");
                });
            })
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $data['status'] = $data['status'] ?? 'active';
        $data['email_verified_at'] = now();
        $data['remember_token'] = \Illuminate\Support\Str::random(10);
        $user = User::create($data);
        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }
        return $user;
    }

    /**
     * Update an existing user.
     */
    public function updateUser(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }
        return $user;
    }

    /**
     * Delete a user.
     */
    public function deleteUser(int $id): bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }

    /**
     * Update user status.
     */
    public function updateStatus(int $id, string $status): User
    {
        $user = User::findOrFail($id);
        $user->update(['status' => $status]);
        return $user;
    }
}
