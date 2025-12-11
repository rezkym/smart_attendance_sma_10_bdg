<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Display permissions list page
     */
    public function index(): View
    {
        return view('content.pages.admin.access-permission');
    }

    /**
     * DataTables server-side data
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->permissionService->getDataTableQuery();

        return DataTables::eloquent($query)
            ->addColumn('assigned_to', function ($permission) {
                return $permission->roles->pluck('name')->toArray();
            })
            ->addColumn('created_at_formatted', function ($permission) {
                return $permission->created_at?->format('d M Y') ?? '-';
            })
            ->addColumn('actions', function ($permission) {
                return $permission->id;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Store a new permission
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        try {
            $permission = $this->permissionService->createPermission([
                'name' => $request->validated('name'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Permission '{$permission->name}' created successfully.",
                'data' => $permission,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get permission data for editing
     */
    public function show(int $access_permission): JsonResponse
    {
        $permissionData = $this->permissionService->getPermissionWithRoles($access_permission);

        if ($permissionData === null) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $permissionData->id,
                'name' => $permissionData->name,
                'roles' => $permissionData->roles->pluck('name')->toArray(),
            ],
        ]);
    }



    /**
     * Delete a permission
     */
    public function destroy(int $access_permission): JsonResponse
    {
        try {
            $this->permissionService->deletePermission($access_permission);

            return response()->json([
                'success' => true,
                'message' => 'Permission deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
