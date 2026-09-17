<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InsurerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReagentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [InvoiceController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Clients
    Route::middleware('role:admin,editor')->prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/create', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('/{client}', [ClientController::class, 'show'])->name('show');
        Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::put('/{client}', [ClientController::class, 'update'])->name('update');
        Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');

        // Agents (nested under client)
        Route::post('/{client}/agents', [ClientController::class, 'storeAgent'])->name('agents.store');
        Route::delete('/{client}/agents/{agent}', [ClientController::class, 'destroyAgent'])->name('agents.destroy');

        // Sites (nested under client)
        Route::post('/{client}/sites', [ClientController::class, 'storeSite'])->name('sites.store');
        Route::delete('/{client}/sites/{site}', [ClientController::class, 'destroySite'])->name('sites.destroy');

        // Contrats d'assurance (nested under client)
        Route::post('/{client}/contracts', [ClientController::class, 'storeContract'])->name('contracts.store');
        Route::put('/{client}/contracts/{contract}', [ClientController::class, 'updateContract'])->name('contracts.update');
        Route::delete('/{client}/contracts/{contract}', [ClientController::class, 'destroyContract'])->name('contracts.destroy');
    });

    // Assureurs (compagnies d'assurance)
    Route::middleware('role:admin,editor')->prefix('assureurs')->name('assureurs.')->group(function () {
        Route::get('/', [InsurerController::class, 'index'])->name('index');
        Route::get('/{insurer}', [InsurerController::class, 'assures'])->name('assures');
        Route::post('/{insurer}/assures', [InsurerController::class, 'storeAssure'])->name('assures.store');
        Route::put('/{insurer}/assures/{contract}', [InsurerController::class, 'updateAssure'])->name('assures.update');
        Route::delete('/{insurer}/assures/{contract}', [InsurerController::class, 'destroyAssure'])->name('assures.destroy');
        Route::post('/{insurer}/discount', [InsurerController::class, 'updateDiscount'])->name('discount');
    });

    // Factures
    Route::middleware('role:admin,editor')->prefix('invoices')->name('invoices.')->group(function () {
        // Factures de sommation d'assurance (déclaré avant le wildcard {invoice})
        Route::get('/statement/create', [StatementController::class, 'create'])->name('statement.create');
        Route::get('/statement/options', [StatementController::class, 'options'])->name('statement.options');
        Route::post('/statement', [StatementController::class, 'store'])->name('statement.store');

        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::get('/{invoice}/print', [InvoiceController::class, 'print'])->name('print');
        Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('status');
        Route::post('/{invoice}/certify', [InvoiceController::class, 'certify'])->name('certify');
        Route::get('/api/client/{client}', [InvoiceController::class, 'apiClientData'])->name('api.client');
    });

    // Paiements (nested under invoice)
    Route::middleware('role:admin,editor')->prefix('invoices/{invoice}/payments')->name('payments.')->group(function () {
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
    });

    // Suivi des paiements
    Route::middleware('role:admin,editor')->prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
    });

    // Visites
    Route::middleware('role:admin,editor')->prefix('visits')->name('visits.')->group(function () {
        Route::get('/', [VisitController::class, 'index'])->name('index');
        Route::get('/calendar', [VisitController::class, 'calendar'])->name('calendar');
        Route::get('/create', [VisitController::class, 'create'])->name('create');
        Route::post('/', [VisitController::class, 'store'])->name('store');
        Route::get('/{visit}', [VisitController::class, 'show'])->name('show');
        Route::get('/{visit}/edit', [VisitController::class, 'edit'])->name('edit');
        Route::put('/{visit}', [VisitController::class, 'update'])->name('update');
        Route::delete('/{visit}', [VisitController::class, 'destroy'])->name('destroy');
    });

    // Services
    Route::middleware('role:admin,editor')->prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::get('/create', [ServiceController::class, 'create'])->name('create');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::get('/{service}/edit', [ServiceController::class, 'edit'])->name('edit');
        Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
        Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
    });

    // Réactifs
    Route::middleware('role:admin,editor')->prefix('reagents')->name('reagents.')->group(function () {
        Route::get('/', [ReagentController::class, 'index'])->name('index');
        Route::get('/create', [ReagentController::class, 'create'])->name('create');
        Route::post('/', [ReagentController::class, 'store'])->name('store');
        Route::get('/{reagent}/edit', [ReagentController::class, 'edit'])->name('edit');
        Route::put('/{reagent}', [ReagentController::class, 'update'])->name('update');
        Route::delete('/{reagent}', [ReagentController::class, 'destroy'])->name('destroy');
    });

    // Parametres (admin only)
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [CompanySettingController::class, 'edit'])->name('edit');
        Route::put('/', [CompanySettingController::class, 'update'])->name('update');
    });
});

require __DIR__.'/auth.php';
