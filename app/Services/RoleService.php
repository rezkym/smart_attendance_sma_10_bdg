<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
        protected PermissionRepositoryInterface $permissionRepository
    ) {}

    /**
     * Get all roles with permissions and user count
     *
     * @return Collection<int, Role>
     */
    public function getAllRolesWithDetails(): Collection
    {
        return $this->roleRepository->getAllRolesWithPermissionsAndUserCount();
    }

    /**
     * Get all available permissions for role assignment
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissionRepository->getAllPermissions();
    }

    /**
     * Get role by ID with permissions
     */
    public function getRoleWithPermissions(int $roleId): ?Role
    {
        return $this->roleRepository->getRoleByIdWithPermissions($roleId);
    }

    /**
     * Create a new role with optional permissions
     *
     * @param  array{name: string, guard_name?: string}  $roleData
     * @param  array<int, string>  $permissionNames
     */
    public function createRoleWithPermissions(array $roleData, array $permissionNames = []): Role
    {
        return DB::transaction(function () use ($roleData, $permissionNames) {
            $role = $this->roleRepository->createRole($roleData);

            if (filled($permissionNames)) {
                $this->roleRepository->syncPermissions($role, $permissionNames);
            }

            return $role->fresh()->load('permissions');
        });
    }

    /**
     * Update role and sync permissions
     *
     * @param  array{name?: string, guard_name?: string}  $roleData
     * @param  array<int, string>  $permissionNames
     *
     * @throws \InvalidArgumentException
     */
    public function updateRoleWithPermissions(int $roleId, array $roleData, array $permissionNames = []): Role
    {
        $role = $this->roleRepository->getRoleById($roleId);

        if ($role === null) {
            throw new \InvalidArgumentException("Role with ID {$roleId} not found.");
        }

        return DB::transaction(function () use ($role, $roleData, $permissionNames) {
            if (filled($roleData)) {
                $this->roleRepository->updateRole($role, $roleData);
            }

            $this->roleRepository->syncPermissions($role, $permissionNames);

            return $role->fresh()->load('permissions');
        });
    }

    /**
     * Delete a role if it has no assigned users
     *
     * @throws \InvalidArgumentException
     */
    public function deleteRole(int $roleId): bool
    {
        $role = $this->roleRepository->getRoleById($roleId);

        if ($role === null) {
            throw new \InvalidArgumentException("Role with ID {$roleId} not found.");
        }

        // Prevent deletion of admin role
        if (strtolower($role->name) === 'admin') {
            throw new \InvalidArgumentException('Cannot delete the admin role.');
        }

        // Check if role has users assigned
        $usersCount = $this->roleRepository->getRoleUsersCount($role);
        if ($usersCount > 0) {
            throw new \InvalidArgumentException(
                "Cannot delete role '{$role->name}' because it has {$usersCount} user(s) assigned."
            );
        }

        return $this->roleRepository->deleteRole($role);
    }

    /**
     * Get DataTables query builder for roles
     */
    public function getDataTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->roleRepository->getDataTableQuery();
    }
}
