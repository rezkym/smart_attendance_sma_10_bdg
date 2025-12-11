<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        protected Role $model
    ) {}

    public function getAllRoles(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function getAllRolesWithPermissionsAndUserCount(): Collection
    {
        return $this->model->newQuery()
            ->with('permissions')
            ->withCount('users')
            ->get();
    }

    public function getRoleById(int $roleId): ?Role
    {
        return $this->model->newQuery()->find($roleId);
    }

    public function getRoleByIdWithPermissions(int $roleId): ?Role
    {
        return $this->model->newQuery()
            ->with('permissions')
            ->find($roleId);
    }

    public function createRole(array $data): Role
    {
        return $this->model->newQuery()->create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);
    }

    public function updateRole(Role $role, array $data): Role
    {
        $role->update($data);

        return $role->fresh();
    }

    public function deleteRole(Role $role): bool
    {
        return (bool) $role->delete();
    }

    public function syncPermissions(Role $role, array $permissionNames): Role
    {
        $role->syncPermissions($permissionNames);

        return $role->fresh()->load('permissions');
    }

    public function getRoleUsersCount(Role $role): int
    {
        return $role->users()->count();
    }

    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('permissions')
            ->withCount('users');
    }
}
