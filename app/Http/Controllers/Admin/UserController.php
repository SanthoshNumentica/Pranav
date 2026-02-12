<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get all roles for dropdowns.
     */
    public function roles()
    {
        $roles = Role::all(['id', 'name']);
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        // Check if user is super admin - utilizing the loaded role relationship or checking directly
        $isSuperAdmin = $currentUser->role && strtolower($currentUser->role->name) === 'super-admin';

        $users = User::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })
            ->when(!$isSuperAdmin, function ($query) use ($currentUser) {
                $query->where('id', $currentUser->id);
            })
            ->when($request->role_id, function ($query, $role_id) {
                $query->where('role_id', $role_id);
            })
            ->with('role')
            ->latest()
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load('role')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role_id = $data['role_id'];
        $user->status = $data['status'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
