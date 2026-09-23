<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_role_can_access_dashboard_without_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_comptable_user_gets_comptabilite_dashboard(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'export financial data']);
        Permission::firstOrCreate(['name' => 'import financial data']);
        Permission::firstOrCreate(['name' => 'view financial data']);

        $role = Role::findOrCreate('comptable');
        $role->givePermissionTo(['export financial data', 'import financial data', 'view financial data']);
        $user = User::factory()->create();
        $user->assignRole('comptable');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Export Sage', false);
    }

    public function test_comptable_can_open_finance_pages(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'view financial data']);
        Permission::firstOrCreate(['name' => 'export financial data']);
        Permission::firstOrCreate(['name' => 'import financial data']);

        $role = Role::findOrCreate('comptable');
        $role->givePermissionTo(['view financial data', 'export financial data', 'import financial data']);
        $user = User::factory()->create();
        $user->assignRole('comptable');

        $this->actingAs($user)
            ->get(route('finance.exports'))
            ->assertOk()
            ->assertSee('Export Sage', false);

        $this->actingAs($user)
            ->get(route('finance.imports'))
            ->assertOk()
            ->assertSee('Imp');
    }

    public function test_secretaire_user_gets_accueil_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Visites du jour');
    }
}