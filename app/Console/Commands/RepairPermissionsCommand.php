<?php

namespace App\Console\Commands;

use App\Services\PermissionSyncService;
use Illuminate\Console\Command;

class RepairPermissionsCommand extends Command
{
    protected $signature = 'app:repair-permissions';

    protected $description = 'Répare les accès : recrée les permissions manquantes, re-synchronise les rôles de base et vide le cache.';

    public function handle(PermissionSyncService $sync): int
    {
        $result = $sync->sync();

        $this->info(sprintf(
            'Accès réparés : %d permission(s) au total, %d nouvellement créée(s), rôles synchronisés : %s.',
            $result['permissions_total'],
            $result['permissions_created'],
            implode(', ', $result['roles_synced'])
        ));

        return self::SUCCESS;
    }
}
