<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage users',
            'manage roles',
            'manage categories',
            'manage products',
            'manage reservations',
            'manage trainers',
            'manage sessions',
            'manage contacts',
            'view trainer dashboard',
            'respond assigned sessions',
            'view assigned participants',
            'reserve products',
            'register sessions',
            'manage own profile',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $trainer = Role::query()->firstOrCreate(['name' => 'trainer', 'guard_name' => 'web']);
        $client = Role::query()->firstOrCreate(['name' => 'client', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions);

        $trainer->syncPermissions([
            'view trainer dashboard',
            'respond assigned sessions',
            'view assigned participants',
            'manage own profile',
        ]);

        $client->syncPermissions([
            'reserve products',
            'register sessions',
            'manage own profile',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
