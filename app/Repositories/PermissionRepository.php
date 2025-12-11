<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(
        protected Permission $model
    ) {}

    public function getAllPermissions(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function getAllPermissionsWithRoles(): Collection
    {
        return $this->model->newQuery()
            ->with('roles')
            ->get();
    }

    public function getPermissionById(int $permissionId): ?Permission
    {
        return $this->model->newQuery()->find($permissionId);
    }

    public function getPermissionByIdWithRoles(int $permissionId): ?Permission
    {
        return $this->model->newQuery()
            ->with('roles')
            ->find($permissionId);
    }

    public function createPermission(array $data): Permission
    {
        return $this->model->newQuery()->create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);
    }

    public function updatePermission(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission->fresh();
    }

    public function deletePermission(Permission $permission): bool
    {
        return (bool) $permission->delete();
    }

    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('roles');
    }
}
