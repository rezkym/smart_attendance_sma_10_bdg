<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    /**
     * @return Collection<int, Role>
     */
    public function getAllRoles(): Collection;

    /**
     * @return Collection<int, Role>
     */
    public function getAllRolesWithPermissionsAndUserCount(): Collection;

    public function getRoleById(int $roleId): ?Role;

    public function getRoleByIdWithPermissions(int $roleId): ?Role;

    /**
     * @param  array{name: string, guard_name?: string}  $data
     */
    public function createRole(array $data): Role;

    /**
     * @param  array{name?: string, guard_name?: string}  $data
     */
    public function updateRole(Role $role, array $data): Role;

    public function deleteRole(Role $role): bool;

    /**
     * @param  array<int, string>  $permissionNames
     */
    public function syncPermissions(Role $role, array $permissionNames): Role;

    public function getRoleUsersCount(Role $role): int;

    /**
     * Get query builder for DataTables
     */
    public function getDataTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
