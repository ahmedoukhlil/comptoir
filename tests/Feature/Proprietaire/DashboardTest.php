<?php

namespace Tests\Feature\Proprietaire;

use App\Livewire\Proprietaire\Alimentation;
use App\Livewire\Proprietaire\Dashboard;
use App\Models\Operateur;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_proprietaire_voit_le_solde_consolide_de_ses_deux_points(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'reseau', 'statut' => 'actif']);
        $pointA = Point::create(['tenant_id' => $tenant->id, 'nom' => 'Point A']);
        $pointB = Point::create(['tenant_id' => $tenant->id, 'nom' => 'Point B']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id]);
        $bankily = Operateur::create(['tenant_id' => $tenant->id, 'nom' => 'Bankily', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []]]);
        $cash = Operateur::create(['tenant_id' => $tenant->id, 'nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);

        $this->actingAs($proprietaire);

        Livewire::test(Dashboard::class)
            ->assertSee('Point A')
            ->assertSee('Point B')
            ->assertSet('soldeTotal', 0);

        // Alimenter les deux points (répartition cash + opérateur) puis vérifier la consolidation
        Livewire::test(Alimentation::class)
            ->call('alimenter', $pointA->id, [$cash->id => 10000, $bankily->id => 5000], null);

        Livewire::test(Alimentation::class)
            ->call('alimenter', $pointB->id, [$cash->id => 8000], 'apport initial');

        $dashboard = Livewire::test(Dashboard::class);
        $dashboard->assertSet('soldeTotal', 23000);

        $this->assertEquals(15000, $pointA->fresh()->soldeCaisse());
        $this->assertEquals(8000, $pointB->fresh()->soldeCaisse());

        $soldesA = $pointA->fresh()->soldesParOperateur()->pluck('solde', 'operateur.id');
        $this->assertEquals(10000, $soldesA[$cash->id]);
        $this->assertEquals(5000, $soldesA[$bankily->id]);
    }

    public function test_agent_ne_peut_pas_acceder_au_tableau_de_bord(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($agent)->get('/tableau-de-bord')->assertForbidden();
    }

    public function test_proprietaire_ne_peut_pas_acceder_a_lecran_de_saisie(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($proprietaire)->get('/caisse')->assertForbidden();
    }
}
