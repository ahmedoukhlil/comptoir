<?php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\RapportExportController;
use App\Http\Controllers\SyncController;
use App\Livewire\Admin\Agents\Create as AdminAgentsCreate;
use App\Livewire\Admin\Points\Create as AdminPointsCreate;
use App\Livewire\Admin\Tenants\Create as AdminTenantsCreate;
use App\Livewire\Admin\Tenants\Index as AdminTenantsIndex;
use App\Livewire\Admin\Tenants\Show as AdminTenantsShow;
use App\Livewire\Auth\Login;
use App\Livewire\Caisse\Cloture;
use App\Livewire\Caisse\Historique;
use App\Livewire\Caisse\Saisie;
use App\Livewire\Compte\ChangerMotDePasse;
use App\Livewire\Proprietaire\Agents as ProprietaireAgents;
use App\Livewire\Proprietaire\Alimentation;
use App\Livewire\Proprietaire\Dashboard;
use App\Livewire\Proprietaire\Rapport;
use App\Livewire\Proprietaire\Transfert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Login::class)
    ->middleware('guest')
    ->name('connexion');

Route::post('/deconnexion', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('connexion');
})->middleware('auth')->name('deconnexion');

Route::get('/accueil', function () {
    if (Auth::user()->estSuperAdmin()) {
        return redirect()->route('admin.tenants.index');
    }

    return Auth::user()->peutUtiliserLaCaisse()
        ? redirect()->route('caisse.saisie')
        : redirect()->route('proprietaire.dashboard');
})->middleware('auth')->name('accueil');

Route::get('/caisse', Saisie::class)
    ->middleware(['auth', 'agent'])
    ->name('caisse.saisie');

Route::get('/caisse/cloture', Cloture::class)
    ->middleware(['auth', 'agent'])
    ->name('caisse.cloture');

Route::get('/caisse/historique', Historique::class)
    ->middleware(['auth', 'agent'])
    ->name('caisse.historique');

Route::get('/caisse/historique/export/excel', [ExportController::class, 'excel'])
    ->middleware(['auth', 'agent'])
    ->name('caisse.historique.export.excel');

Route::get('/caisse/historique/export/pdf', [ExportController::class, 'pdf'])
    ->middleware(['auth', 'agent'])
    ->name('caisse.historique.export.pdf');

Route::get('/tableau-de-bord', Dashboard::class)
    ->middleware(['auth', 'proprietaire', 'plan.multi-points'])
    ->name('proprietaire.dashboard');

Route::get('/tableau-de-bord/alimentation', Alimentation::class)
    ->middleware(['auth', 'proprietaire', 'plan.multi-points'])
    ->name('proprietaire.alimentation');

Route::get('/tableau-de-bord/rapport', Rapport::class)
    ->middleware(['auth', 'proprietaire', 'plan.multi-points'])
    ->name('proprietaire.rapport');

Route::get('/tableau-de-bord/rapport/export/excel', [RapportExportController::class, 'excel'])
    ->middleware(['auth', 'proprietaire', 'plan.multi-points'])
    ->name('proprietaire.rapport.export.excel');

Route::get('/tableau-de-bord/rapport/export/pdf', [RapportExportController::class, 'pdf'])
    ->middleware(['auth', 'proprietaire', 'plan.multi-points'])
    ->name('proprietaire.rapport.export.pdf');

Route::get('/tableau-de-bord/transfert', Transfert::class)
    ->middleware(['auth', 'proprietaire', 'plan.multi-points'])
    ->name('proprietaire.transfert');

Route::get('/tableau-de-bord/agents', ProprietaireAgents::class)
    ->middleware(['auth', 'proprietaire'])
    ->name('proprietaire.agents');

Route::get('/mon-mot-de-passe', ChangerMotDePasse::class)
    ->middleware('auth')
    ->name('compte.changer-mot-de-passe');

Route::post('/api/operations/sync', [SyncController::class, 'synchroniser'])
    ->middleware('auth')
    ->name('operations.sync');

Route::get('/admin/tenants', AdminTenantsIndex::class)
    ->middleware(['auth', 'super-admin'])
    ->name('admin.tenants.index');

Route::get('/admin/tenants/creer', AdminTenantsCreate::class)
    ->middleware(['auth', 'super-admin'])
    ->name('admin.tenants.create');

Route::get('/admin/tenants/{tenantId}', AdminTenantsShow::class)
    ->middleware(['auth', 'super-admin'])
    ->name('admin.tenants.show');

Route::get('/admin/tenants/{tenantId}/points/creer', AdminPointsCreate::class)
    ->middleware(['auth', 'super-admin'])
    ->name('admin.points.create');

Route::get('/admin/points/{pointId}/agents/creer', AdminAgentsCreate::class)
    ->middleware(['auth', 'super-admin'])
    ->name('admin.agents.create');
