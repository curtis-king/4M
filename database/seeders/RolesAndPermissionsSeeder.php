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
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm, 'guard_name' => $guard]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::create(['name' => 'admin', 'guard_name' => $guard]);
        $admin->givePermissionTo($permissions);

        $editor = Role::create(['name' => 'editor', 'guard_name' => $guard]);
        $editor->givePermissionTo([
            'view dashboard',
            'view clients', 'create client', 'edit client', 'delete client',
            'view invoices', 'create invoice', 'edit invoice', 'delete invoice',
            'manage payments', 'print invoice',
            'view visits', 'create visit', 'edit visit', 'delete visit',
            'manage services', 'manage reagents',
        ]);

        $user = Role::create(['name' => 'user', 'guard_name' => $guard]);
        $user->givePermissionTo(['view dashboard']);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ])->assignRole('admin');
    }
}
