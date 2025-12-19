<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * Display roles list page
     */
    public function index(): View
    {
        $roles = $this->roleService->getAllRolesWithDetails();
        $permissions = $this->roleService->getAllPermissions();

        return view('content.pages.admin.access-roles', compact('roles', 'permissions'));
    }

    /**
     * DataTables server-side data
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->roleService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('permissions_list', function ($role) {
                return $role->permissions->pluck('name')->toArray();
            })
            ->addColumn('actions', function ($role) {
                return $role->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get users with roles for DataTable
     */
    public function users(Request $request): JsonResponse
    {
        $users = \App\Models\User::with('roles')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ];
            });

        return response()->json([
            'data' => $users,
        ]);
    }

    /**
     * Store a new role
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->createRoleWithPermissions(
                ['name' => $request->validated('name')],
                $request->validated('permissions', [])
            );

            return response()->json([
                'success' => true,
                'message' => "Role '{$role->name}' created successfully.",
                'data' => $role->load('permissions'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get role data for editing
     */
    public function show(int $access_role): JsonResponse
    {
        $roleData = $this->roleService->getRoleWithPermissions($access_role);

        if ($roleData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $roleData->id,
                'name' => $roleData->name,
                'permissions' => $roleData->permissions->pluck('name')->toArray(),
            ],
        ]);
    }

    /**
     * Update an existing role
     */
    public function update(UpdateRoleRequest $request, int $access_role): JsonResponse
    {
        try {
            $updatedRole = $this->roleService->updateRoleWithPermissions(
                $access_role,
                ['name' => $request->validated('name')],
                $request->validated('permissions', [])
            );

            return response()->json([
                'success' => true,
                'message' => "Role '{$updatedRole->name}' updated successfully.",
                'data' => $updatedRole,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a role
     */
    public function destroy(int $access_role): JsonResponse
    {
        try {
            $this->roleService->deleteRole($access_role);

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
