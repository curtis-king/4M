<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InsurerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReagentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'home'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Le tableau de bord est routé par profil : chaque rôle a sa page dédiée.
    Route::get('/dashboard/administrateur', [DashboardController::class, 'administrateur'])
        ->middleware('role:administrateur')->name('dashboard.administrateur');

    Route::get('/dashboard/comptabilite', [DashboardController::class, 'comptabilite'])
        ->middleware('role:comptable')->name('dashboard.comptabilite');

    Route::get('/dashboard/accueil', [DashboardController::class, 'accueil'])
        ->middleware('role:secretaire')->name('dashboard.accueil');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Clients
    Route::middleware('permission:view clients')->prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');

        Route::middleware('permission:create client')->group(function () {
            Route::get('/create', [ClientController::class, 'create'])->name('create');
            Route::post('/', [ClientController::class, 'store'])->name('store');
        });

        Route::middleware('permission:edit client')->group(function () {
            Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [ClientController::class, 'update'])->name('update');

            // Agents (nested under client)
            Route::post('/{client}/agents', [ClientController::class, 'storeAgent'])->name('agents.store');
            Route::delete('/{client}/agents/{agent}', [ClientController::class, 'destroyAgent'])->name('agents.destroy');

            // Sites (nested under client)
            Route::post('/{client}/sites', [ClientController::class, 'storeSite'])->name('sites.store');
            Route::put('/{client}/sites/{site}', [ClientController::class, 'updateSite'])->name('sites.update');
            Route::delete('/{client}/sites/{site}', [ClientController::class, 'destroySite'])->name('sites.destroy');

            // Contrats d'assurance (nested under client)
            Route::post('/{client}/contracts', [ClientController::class, 'storeContract'])->name('contracts.store');
            Route::put('/{client}/contracts/{contract}', [ClientController::class, 'updateContract'])->name('contracts.update');
            Route::delete('/{client}/contracts/{contract}', [ClientController::class, 'destroyContract'])->name('contracts.destroy');
        });

        Route::get('/{client}', [ClientController::class, 'show'])->name('show');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy')->middleware('permission:delete client');
    });

    // Assureurs (compagnies d'assurance)
    Route::middleware('permission:manage insurers')->prefix('assureurs')->name('assureurs.')->group(function () {
        Route::get('/', [InsurerController::class, 'index'])->name('index');
        Route::get('/{insurer}', [InsurerController::class, 'assures'])->name('assures');
        Route::post('/{insurer}/assures', [InsurerController::class, 'storeAssure'])->name('assures.store');
        Route::put('/{insurer}/assures/{contract}', [InsurerController::class, 'updateAssure'])->name('assures.update');
        Route::delete('/{insurer}/assures/{contract}', [InsurerController::class, 'destroyAssure'])->name('assures.destroy');
        Route::post('/{insurer}/discount', [InsurerController::class, 'updateDiscount'])->name('discount');
    });

    // Comptabilité : exports Sage + imports Excel
    Route::middleware('permission:view financial data')->prefix('comptabilite')->name('finance.')->group(function () {
        Route::middleware('permission:export financial data')->group(function () {
            Route::get('/exports', [FinanceController::class, 'exports'])->name('exports');
            Route::get('/exports/sales', [FinanceController::class, 'exportSales'])->name('exports.sales');
            Route::get('/exports/receipts', [FinanceController::class, 'exportReceipts'])->name('exports.receipts');
        });

        Route::middleware('permission:import financial data')->group(function () {
            Route::get('/imports', [FinanceController::class, 'imports'])->name('imports');
            Route::get('/imports/template/{type}', [FinanceController::class, 'importTemplate'])->name('import.template');
            Route::post('/imports/{type}', [FinanceController::class, 'importStore'])->name('import.store');
        });
    });

    // Factures
    Route::middleware('permission:view invoices')->prefix('invoices')->name('invoices.')->group(function () {
        // Factures de sommation d'assurance (déclaré avant le wildcard {invoice})
        Route::get('/statement/create', [StatementController::class, 'create'])->name('statement.create');
        Route::get('/statement/options', [StatementController::class, 'options'])->name('statement.options');
        Route::post('/statement', [StatementController::class, 'store'])->name('statement.store')->middleware('permission:create invoice');

        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/api/client/{client}', [InvoiceController::class, 'apiClientData'])->name('api.client');

        Route::middleware('permission:create invoice')->group(function () {
            Route::get('/create', [InvoiceController::class, 'create'])->name('create');
            Route::post('/', [InvoiceController::class, 'store'])->name('store');
        });

        Route::middleware('permission:edit invoice')->group(function () {
            Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
            Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
            Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('status');
        });

        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy')->middleware('permission:delete invoice');

        Route::middleware('permission:print invoice')->group(function () {
            Route::get('/{invoice}/print', [InvoiceController::class, 'print'])->name('print');
            Route::post('/{invoice}/certify', [InvoiceController::class, 'certify'])->name('certify');
        });
    });

    // Devis
    Route::middleware('permission:view devis')->prefix('devis')->name('devis.')->group(function () {
        Route::get('/', [DevisController::class, 'index'])->name('index');
        Route::get('/api/client/{client}', [DevisController::class, 'apiClientData'])->name('api.client');

        Route::middleware('permission:create devis')->group(function () {
            Route::get('/create', [DevisController::class, 'create'])->name('create');
            Route::post('/', [DevisController::class, 'store'])->name('store');
        });

        Route::middleware('permission:edit devis')->group(function () {
            Route::get('/{devis}/edit', [DevisController::class, 'edit'])->name('edit');
            Route::put('/{devis}', [DevisController::class, 'update'])->name('update');
            Route::patch('/{devis}/status', [DevisController::class, 'updateStatus'])->name('status');
        });

        Route::get('/{devis}', [DevisController::class, 'show'])->name('show');

        Route::delete('/{devis}', [DevisController::class, 'destroy'])->name('destroy')->middleware('permission:delete devis');
        Route::get('/{devis}/print', [DevisController::class, 'print'])->name('print')->middleware('permission:print devis');
        Route::post('/{devis}/convert', [DevisController::class, 'convert'])->name('convert')->middleware('permission:convert devis');
    });

    // Paiements (nested under invoice)
    Route::middleware('permission:manage payments')->prefix('invoices/{invoice}/payments')->name('payments.')->group(function () {
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
    });

    // Suivi des paiements
    Route::middleware('permission:manage payments')->prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
    });

    // Visites
    Route::middleware('permission:view visits')->prefix('visits')->name('visits.')->group(function () {
        Route::get('/', [VisitController::class, 'index'])->name('index');
        Route::get('/calendar', [VisitController::class, 'calendar'])->name('calendar');

        Route::middleware('permission:create visit')->group(function () {
            Route::get('/create', [VisitController::class, 'create'])->name('create');
            Route::post('/', [VisitController::class, 'store'])->name('store');
        });

        Route::middleware('permission:edit visit')->group(function () {
            Route::get('/{visit}/edit', [VisitController::class, 'edit'])->name('edit');
            Route::put('/{visit}', [VisitController::class, 'update'])->name('update');
        });

        Route::get('/{visit}', [VisitController::class, 'show'])->name('show');
        Route::delete('/{visit}', [VisitController::class, 'destroy'])->name('destroy')->middleware('permission:delete visit');
    });

    // Services
    Route::middleware('permission:manage services')->prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('/create', [ServiceController::class, 'create'])->name('create');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
        Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
    });

    // Réactifs
    Route::middleware('permission:manage reagents')->prefix('reagents')->name('reagents.')->group(function () {
        Route::get('/', [ReagentController::class, 'index'])->name('index');
        Route::get('/create', [ReagentController::class, 'create'])->name('create');
        Route::post('/', [ReagentController::class, 'store'])->name('store');
        Route::get('/{reagent}/edit', [ReagentController::class, 'edit'])->name('edit');
        Route::put('/{reagent}', [ReagentController::class, 'update'])->name('update');
        Route::delete('/{reagent}', [ReagentController::class, 'destroy'])->name('destroy');
    });

    // Parametres (directeur uniquement)
    Route::middleware('permission:manage settings')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [CompanySettingController::class, 'edit'])->name('edit');
        Route::put('/', [CompanySettingController::class, 'update'])->name('update');
        Route::post('/repair-access', [CompanySettingController::class, 'repairAccess'])->name('repair-access');
    });

    // Utilisateurs (directeur uniquement)
    Route::middleware('permission:manage users')->prefix('settings/users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Rôles & permissions (administrateur uniquement)
    Route::middleware('permission:manage roles')->prefix('settings/roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
