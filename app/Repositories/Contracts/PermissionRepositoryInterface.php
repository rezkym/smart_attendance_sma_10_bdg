<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

interface PermissionRepositoryInterface
{
    /**
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(): Collection;

    /**
     * @return Collection<int, Permission>
     */
    public function getAllPermissionsWithRoles(): Collection;

    public function getPermissionById(int $permissionId): ?Permission;

    public function getPermissionByIdWithRoles(int $permissionId): ?Permission;

    /**
     * @param  array{name: string, guard_name?: string}  $data
     */
    public function createPermission(array $data): Permission;

    /**
     * @param  array{name?: string, guard_name?: string}  $data
     */
    public function updatePermission(Permission $permission, array $data): Permission;

    public function deletePermission(Permission $permission): bool;

    /**
     * Get query builder for DataTables
     */
    public function getDataTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
