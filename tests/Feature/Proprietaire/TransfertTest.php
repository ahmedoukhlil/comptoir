<?php

namespace Tests\Feature\Proprietaire;

use App\Livewire\Proprietaire\Alimentation;
use App\Livewire\Proprietaire\Transfert;
use App\Models\Operateur;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\Transfert as TransfertModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransfertTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfert_deplace_le_solde_entre_deux_operateurs_sans_changer_le_total(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'reseau', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id]);
        $bankily = Operateur::create(['tenant_id' => $tenant->id, 'nom' => 'Bankily', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []]]);
        $cash = Operateur::create(['tenant_id' => $tenant->id, 'nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);

        $this->actingAs($proprietaire);

        Livewire::test(Alimentation::class)
            ->call('alimenter', $point->id, [$cash->id => 100000, $bankily->id => 0], null);

        $soldeTotalAvant = $point->fresh()->soldeCaisse();

        Livewire::test(Transfert::class)
            ->call('transferer', $point->id, $cash->id, $bankily->id, 30000, 'rééquilibrage')
            ->assertSet('message', null); // pas d'assertion directe sur le message, juste pas d'exception

        $this->assertCount(1, TransfertModel::all());

        $soldes = $point->fresh()->soldesParOperateur()->pluck('solde', 'operateur.id');
        $this->assertEquals(70000, $soldes[$cash->id]);
        $this->assertEquals(30000, $soldes[$bankily->id]);

        // Le solde total du point ne doit pas bouger : un transfert est un rééquilibrage interne.
        $this->assertEquals($soldeTotalAvant, $point->fresh()->soldeCaisse());
    }

    public function test_transfert_refuse_si_solde_source_insuffisant(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'reseau', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id]);
        $bankily = Operateur::create(['tenant_id' => $tenant->id, 'nom' => 'Bankily', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []]]);
        $cash = Operateur::create(['tenant_id' => $tenant->id, 'nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);

        $this->actingAs($proprietaire);

        Livewire::test(Alimentation::class)
            ->call('alimenter', $point->id, [$cash->id => 5000], null);

        Livewire::test(Transfert::class)
            ->call('transferer', $point->id, $cash->id, $bankily->id, 10000, null);

        $this->assertCount(0, TransfertModel::all(), 'Le transfert ne doit pas être créé si le solde source est insuffisant.');
    }
}
