<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CentreDonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeSangController;
use App\Http\Controllers\DonController;
use App\Http\Controllers\DonneurController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RapportMedicalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - MEKILINK Plateforme Don de Sang
|--------------------------------------------------------------------------
*/

// --- Routes Publiques ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/api/compatibilite', [HomeController::class, 'checkCompatibility'])->name('api.compatibilite');
Route::get('/centres', [CentreDonController::class, 'index'])->name('centres.index');
Route::get('/centres/{id}', [CentreDonController::class, 'show'])->name('centres.show');
Route::get('/api/centres-geojson', [CentreDonController::class, 'geojson'])->name('centres.geojson');

// --- Authentification (Invités) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// --- Routes Utilisateurs Authentifiés ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [AuthController::class, 'profil'])->name('profil');
    Route::post('/profil', [AuthController::class, 'updateProfil'])->name('profil.update');

    // --- Espace Donneur ---
    Route::prefix('donneur')->name('donneur.')->group(function () {
        Route::get('/dashboard', [DonneurController::class, 'dashboard'])->name('dashboard');
        Route::post('/disponibilite', [DonneurController::class, 'toggleDisponibilite'])->name('toggle-disponibilite');
        Route::get('/demande/{id}', [DonneurController::class, 'showDemande'])->name('demande');
        Route::post('/demande/{id}/repondre', [DonneurController::class, 'repondre'])->name('repondre');
        Route::get('/historique', [DonneurController::class, 'historique'])->name('historique');
    });

    // --- Espace Demandeur & Demandes de sang ---
    Route::prefix('demandeur')->name('demandeur.')->group(function () {
        Route::get('/dashboard', [DemandeSangController::class, 'index'])->name('dashboard');
    });

    Route::resource('demandes', DemandeSangController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/demandes/{id}/annuler', [DemandeSangController::class, 'annuler'])->name('demandes.annuler');
    Route::post('/demandes/{id}/satisfaire', [DemandeSangController::class, 'satisfaire'])->name('demandes.satisfaire');
    Route::post('/demandes/{id}/relancer-ia', [DemandeSangController::class, 'relancerMatching'])->name('demandes.relancer-ia');
    Route::post('/demandes/{id}/notifier/{donneurId}', [DemandeSangController::class, 'notifierDonneur'])->name('demandes.notifier-donneur');

    // --- Enregistrement des Dons ---
    Route::resource('dons', DonController::class)->only(['index', 'create', 'store', 'show']);

    // --- Espace Administrateur & Validation Médicale ---
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{id}/toggle', [AdminController::class, 'toggleStatut'])->name('users.toggle');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.delete');
    });

    Route::get('/admin/rapports', [RapportMedicalController::class, 'index'])->name('rapports.index');
    Route::get('/admin/rapports/{id}', [RapportMedicalController::class, 'show'])->name('rapports.show');
    Route::post('/admin/rapports/{id}/valider', [RapportMedicalController::class, 'valider'])->name('rapports.valider');
});
