<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Les rôles & permissions peuvent être modifiés en direct via l'interface
         * (Rôles & permissions, Utilisateurs). Pour que chaque changement soit
         * appliqué immédiatement SANS 403 de cache périmé, on vide le cache
         * Spatie à chaque requête. Applicable vu la taille de l'application.
         */
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
