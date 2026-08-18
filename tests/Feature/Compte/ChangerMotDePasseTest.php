<?php

namespace Tests\Feature\Compte;

use App\Livewire\Compte\ChangerMotDePasse;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ChangerMotDePasseTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_peut_changer_son_mot_de_passe(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($agent);

        Livewire::test(ChangerMotDePasse::class)
            ->set('motDePasseActuel', 'password')
            ->set('nouveauMotDePasse', 'nouveauMdp123')
            ->set('nouveauMotDePasse_confirmation', 'nouveauMdp123')
            ->call('changer')
            ->assertHasNoErrors()
            ->assertSet('reussi', true);

        $this->assertTrue(Hash::check('nouveauMdp123', $agent->fresh()->password));

        // L'ancien mot de passe ne fonctionne plus, le nouveau oui.
        Auth::logout();
        $this->assertFalse(Auth::attempt(['telephone' => $agent->telephone, 'password' => 'password']));
        $this->assertTrue(Auth::attempt(['telephone' => $agent->telephone, 'password' => 'nouveauMdp123']));
    }

    public function test_refuse_si_le_mot_de_passe_actuel_est_incorrect(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($agent);

        Livewire::test(ChangerMotDePasse::class)
            ->set('motDePasseActuel', 'mauvais-mot-de-passe')
            ->set('nouveauMotDePasse', 'nouveauMdp123')
            ->set('nouveauMotDePasse_confirmation', 'nouveauMdp123')
            ->call('changer')
            ->assertHasErrors('motDePasseActuel');

        $this->assertTrue(Hash::check('password', $agent->fresh()->password));
    }

    public function test_refuse_si_la_confirmation_ne_correspond_pas(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($agent);

        Livewire::test(ChangerMotDePasse::class)
            ->set('motDePasseActuel', 'password')
            ->set('nouveauMotDePasse', 'nouveauMdp123')
            ->set('nouveauMotDePasse_confirmation', 'different')
            ->call('changer')
            ->assertHasErrors('nouveauMotDePasse');
    }

    public function test_super_admin_peut_aussi_changer_son_mot_de_passe(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin);

        $this->get('/mon-mot-de-passe')->assertOk();

        Livewire::test(ChangerMotDePasse::class)
            ->set('motDePasseActuel', 'password')
            ->set('nouveauMotDePasse', 'adminNouveau123')
            ->set('nouveauMotDePasse_confirmation', 'adminNouveau123')
            ->call('changer')
            ->assertHasNoErrors()
            ->assertSet('reussi', true);

        $this->assertTrue(Hash::check('adminNouveau123', $admin->fresh()->password));
    }
}
