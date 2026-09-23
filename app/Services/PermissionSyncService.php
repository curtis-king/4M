<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Source unique de vérité pour le catalogue de permissions et les rôles par défaut.
 * Permet de « réparer » les accès : recrée les permissions manquantes,
 * re-synchronise les rôles de base et vide le cache Spatie.
 */
class PermissionSyncService
{
    protected string $guard = 'web';

    public function allPermissions(): array
    {
        $permissions = array_keys(config('permissions', []));

        $permissions[] = 'view dashboard';

        return array_values(array_unique($permissions));
    }

    public function defaultRoles(): array
    {
        return [
            'administrateur' => [
                'label' => 'Administrateur / Directeur général',
                'permissions' => $this->allPermissions(),
            ],
            'comptable' => [
                'label' => 'Comptable',
                'permissions' => [
                    'view dashboard',
                    'view financial data',
                    'view clients',
                    'view invoices', 'create invoice', 'edit invoice',
                    'print invoice',
                    'manage payments',
                    'manage insurers',
                    'view devis',
                    'view visits',
                    'export financial data',
                    'import financial data',
                ],
            ],
            'secretaire' => [
                'label' => 'Secrétaire',
                'permissions' => [
                    'view dashboard',
                    'view clients', 'create client', 'edit client',
                    'view invoices',
                    'view devis', 'create devis', 'edit devis', 'print devis',
                    'view visits', 'create visit', 'edit visit',
                ],
            ],
        ];
    }

    /**
     * Recrée les permissions manquantes et re-synchronise les rôles de base.
     */
    public function sync(): array
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        $created = 0;
        foreach ($this->allPermissions() as $name) {
            $exists = Permission::query()
                ->where('name', $name)
                ->where('guard_name', $this->guard)
                ->exists();

            if (! $exists) {
                Permission::create(['name' => $name, 'guard_name' => $this->guard]);
                $created++;
            }
        }

        $rolesSynced = [];
        foreach ($this->defaultRoles() as $name => $definition) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => $this->guard]);
            $role->label = $definition['label'];
            $role->save();
            $role->syncPermissions($definition['permissions']);
            $rolesSynced[] = $name;
        }

        $registrar->forgetCachedPermissions();

        return [
            'permissions_total' => Permission::query()->where('guard_name', $this->guard)->count(),
            'permissions_created' => $created,
            'roles_synced' => $rolesSynced,
        ];
    }
}