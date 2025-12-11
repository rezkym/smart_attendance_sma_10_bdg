<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function __construct(
        protected PermissionRepositoryInterface $permissionRepository
    ) {}

    /**
     * Get all permissions with assigned roles
     *
     * @return Collection<int, Permission>
     */
    public function getAllPermissionsWithRoles(): Collection
    {
        return $this->permissionRepository->getAllPermissionsWithRoles();
    }

    /**
     * Get permission by ID with roles
     */
    public function getPermissionWithRoles(int $permissionId): ?Permission
    {
        return $this->permissionRepository->getPermissionByIdWithRoles($permissionId);
    }

    /**
     * Create a new permission
     *
     * @param  array{name: string, guard_name?: string}  $data
     */
    public function createPermission(array $data): Permission
    {
        return $this->permissionRepository->createPermission($data);
    }

    /**
     * Update a permission
     *
     * @param  array{name?: string, guard_name?: string}  $data
     *
     * @throws \InvalidArgumentException
     */
    public function updatePermission(int $permissionId, array $data): Permission
    {
        $permission = $this->permissionRepository->getPermissionById($permissionId);

        if ($permission === null) {
            throw new \InvalidArgumentException("Permission with ID {$permissionId} not found.");
        }

        return $this->permissionRepository->updatePermission($permission, $data);
    }

    /**
     * Delete a permission if it has no assigned roles
     *
     * @throws \InvalidArgumentException
     */
    public function deletePermission(int $permissionId): bool
    {
        $permission = $this->permissionRepository->getPermissionByIdWithRoles($permissionId);

        if ($permission === null) {
            throw new \InvalidArgumentException("Permission with ID {$permissionId} not found.");
        }

        // Check if permission is assigned to any roles
        if ($permission->roles->isNotEmpty()) {
            $roleNames = $permission->roles->pluck('name')->join(', ');
            throw new \InvalidArgumentException(
                "Cannot delete permission '{$permission->name}' because it is assigned to roles: {$roleNames}"
            );
        }

        return $this->permissionRepository->deletePermission($permission);
    }

    /**
     * Get DataTables query builder for permissions
     */
    public function getDataTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->permissionRepository->getDataTableQuery();
    }
}
