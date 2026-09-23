<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\PermissionSyncService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crée les permissions et synchronise les rôles par défaut
        // (source unique : PermissionSyncService).
        app(PermissionSyncService::class)->sync();

        // ============================================================
        // Migration des anciens rôles vers les nouveaux profils
        // (pour ne pas casser les comptes existants)
        // ============================================================
        $legacyMapping = [
            'directeur' => 'administrateur',
            'receptionniste' => 'secretaire',
            'agent_labo' => 'secretaire',
            'partenaire' => 'secretaire',
        ];

        foreach ($legacyMapping as $oldName => $newName) {
            $oldRole = Role::where('name', $oldName)->first();
            if (! $oldRole) {
                continue;
            }

            foreach ($oldRole->users as $user) {
                $user->syncRoles([$newName]);
            }

            $oldRole->permissions()->detach();
            $oldRole->delete();
        }

        // Compte administrateur par défaut
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        )->syncRoles(['administrateur']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}