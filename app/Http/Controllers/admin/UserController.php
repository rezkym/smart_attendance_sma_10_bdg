<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected RoleService $roleService
    ) {}

    /**
     * Display users list page
     */
    public function index(): View
    {
        $roles = $this->roleService->getAllRolesWithDetails();
        $stats = $this->userService->getUserStats();

        return view('content.pages.admin.users', compact('roles', 'stats'));
    }

    /**
     * DataTables server-side data
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->userService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('roles_list', function ($user) {
                return $user->roles->pluck('name')->toArray();
            })
            ->addColumn('actions', function ($user) {
                return $user->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get user statistics for dashboard cards
     */
    public function stats(): JsonResponse
    {
        $stats = $this->userService->getUserStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Store a new user
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser(
                [
                    'name' => $request->validated('name'),
                    'email' => $request->validated('email'),
                    'password' => $request->validated('password'),
                ],
                $request->validated('roles', [])
            );

            return response()->json([
                'success' => true,
                'message' => "User '{$user->name}' created successfully.",
                'data' => $user->load('roles'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get user data for editing
     */
    public function show(int $user): JsonResponse
    {
        $userData = $this->userService->getUserById($user);

        if ($userData === null) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $userData->id,
                'name' => $userData->name,
                'email' => $userData->email,
                'roles' => $userData->roles->pluck('name')->toArray(),
            ],
        ]);
    }

    /**
     * Update an existing user
     */
    public function update(UpdateUserRequest $request, int $user): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateUser(
                $user,
                [
                    'name' => $request->validated('name'),
                    'email' => $request->validated('email'),
                    'password' => $request->validated('password'),
                ],
                $request->validated('roles', [])
            );

            return response()->json([
                'success' => true,
                'message' => "User '{$updatedUser->name}' updated successfully.",
                'data' => $updatedUser,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a user
     */
    public function destroy(int $user): JsonResponse
    {
        try {
            $this->userService->deleteUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
