<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        $permissions = [
            'manage users',
            'view users',
            'create user',
            'edit user',
            'delete user',
            'manage roles',
            'view roles',
            'view dashboard',
            'view clients',
            'create client',
            'edit client',
            'delete client',
            'view invoices',
            'create invoice',
            'edit invoice',
            'delete invoice',
            'manage payments',
            'print invoice',
            'view visits',
            'create visit',
            'edit visit',
            'delete visit',
            'manage services',
            'manage reagents',
            'manage settings',
            'view devis',
            'create devis',
            'edit devis',
            'delete devis',
            'print devis',
            'convert devis',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $admin->syncPermissions($permissions);

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => $guard]);
        $editor->syncPermissions([
            'view dashboard',
            'view clients', 'create client', 'edit client', 'delete client',
            'view invoices', 'create invoice', 'edit invoice', 'delete invoice',
            'manage payments', 'print invoice',
            'view visits', 'create visit', 'edit visit', 'delete visit',
            'manage services', 'manage reagents',
            'view devis', 'create devis', 'edit devis', 'delete devis', 'print devis', 'convert devis',
        ]);

        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);
        $user->syncPermissions(['view dashboard']);

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        )->syncRoles('admin');
    }
}
