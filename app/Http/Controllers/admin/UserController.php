<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserRolesRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['store']);
        $this->middleware('permission:users.update')->only(['update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
        $this->middleware('permission:users.assign-roles')->only(['assignRoles']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->userService->datatableQuery($request->only('role'));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('roles', fn (User $user) => $user->roles->pluck('name')->join(', '))
                ->editColumn('email_verified_at', fn (User $user) => $user->email_verified_at?->format('Y-m-d H:i') ?? '-')
                ->addColumn('actions', fn (User $user) => view('content.pages.admin.users.partials.actions', compact('user'))->render())
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'like', "%{$search}%"));
                        });
                    }
                })
                ->rawColumns(['actions'])
                ->toJson();
        }

        $roles = Role::query()->orderBy('name')->pluck('name');

        return view('content.pages.admin.users.index', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('roles');

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->update($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => new UserResource($updatedUser),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ], 200);
    }

    public function assignRoles(UpdateUserRolesRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->syncRoles($user, $request->validated('roles'));

        return response()->json([
            'success' => true,
            'message' => 'Roles updated successfully.',
            'data' => new UserResource($updatedUser),
        ]);
    }
}
