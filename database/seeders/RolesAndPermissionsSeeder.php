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
            'manage insurers',
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

        // Directeur : acces total (equivalent admin)
        $directeur = Role::firstOrCreate(['name' => 'directeur', 'guard_name' => $guard]);
        $directeur->syncPermissions($permissions);

        // Partenaire (actionnaire) : lecture seule globale, aucune modification
        $partenaire = Role::firstOrCreate(['name' => 'partenaire', 'guard_name' => $guard]);
        $partenaire->syncPermissions([
            'view dashboard',
            'view clients',
            'view invoices',
            'view visits',
            'view devis',
        ]);

        // Comptable : facturation, paiements, contrats d'assurance
        $comptable = Role::firstOrCreate(['name' => 'comptable', 'guard_name' => $guard]);
        $comptable->syncPermissions([
            'view dashboard',
            'view clients',
            'view invoices', 'create invoice', 'edit invoice',
            'manage payments', 'print invoice',
            'manage insurers',
            'view visits',
            'view devis',
        ]);

        // Agent labo (technicien + medecin) : visites, resultats, stock de reactifs
        $agentLabo = Role::firstOrCreate(['name' => 'agent_labo', 'guard_name' => $guard]);
        $agentLabo->syncPermissions([
            'view dashboard',
            'view clients',
            'view visits', 'create visit', 'edit visit', 'delete visit',
            'manage reagents',
        ]);

        // Receptionniste (accueil + commercial) : clients, prise de rdv, contrats, devis
        $receptionniste = Role::firstOrCreate(['name' => 'receptionniste', 'guard_name' => $guard]);
        $receptionniste->syncPermissions([
            'view dashboard',
            'view clients', 'create client', 'edit client',
            'manage insurers',
            'view invoices', 'create invoice',
            'view visits', 'create visit', 'edit visit',
            'view devis', 'create devis', 'edit devis', 'print devis', 'convert devis',
        ]);

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        )->syncRoles(['directeur']);
    }
}
