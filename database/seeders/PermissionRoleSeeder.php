<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = $this->loadJson(base_path('database/data/permission.json'))['permissions'] ?? [];
        $roles = $this->loadJson(base_path('database/data/role.json'))['roles'] ?? [];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'] ?? 'web',
                ]
            );
        }

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                [
                    'name' => $roleData['name'],
                    'guard_name' => $roleData['guard_name'] ?? 'web',
                ]
            );

            $role->syncPermissions($roleData['permissions'] ?? []);
        }
    }

    private function loadJson(string $path): array
    {
        if (! File::exists($path)) {
            throw new RuntimeException("JSON seed file not found: {$path}");
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in seed file: {$path}");
        }

        return $data ?? [];
    }
}
