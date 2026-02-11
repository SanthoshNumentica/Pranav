<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->listUsers($request->all(), $request->get('limit', 15));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $user = $this->userService->createUser($request->all());

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully',
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $user = $this->userService->updateUser($id, $request->all());

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User updated successfully',
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Update user status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $user = $this->userService->updateStatus($id, $request->status);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User status updated successfully',
        ]);
    }
}
