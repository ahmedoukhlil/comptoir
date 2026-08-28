<?php

namespace Tests\Feature\Cloture;

use App\Livewire\Caisse\Cloture;
use App\Models\Alimentation;
use App\Models\Cloture as ClotureModel;
use App\Models\Operateur;
use App\Models\Operation;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ClotureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cloture_avec_ecart_saffiche_correctement(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $bankily = Operateur::create(['nom' => 'Bankily', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'pourcentage_partage_point_depot' => 50, 'pourcentage_partage_point_retrait_client' => 50, 'pourcentage_partage_point_retrait_beneficiaire' => 50]);
        $cash = Operateur::create(['nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);
        $tenant->operateurs()->attach([$bankily->id, $cash->id], ['actif' => true]);

        Operation::create([
            'numero_piece' => 'OP-2026-000001',
            'uuid_client' => (string) Str::uuid(),
            'point_id' => $point->id,
            'agent_id' => $agent->id,
            'operateur_id' => $bankily->id,
            'type' => 'depot',
            'montant' => 10000,
            'commission_calculee' => 100,
            'commission_part_point' => 50,
            'commission_part_banque' => 50,
            'client_telephone' => '22334455',
            'synced' => true,
        ]);

        $this->actingAs($agent);

        // Un depot fait diminuer le solde Bankily du point (l'agent envoie
        // l'equivalent au client depuis son propre compte, qui avait ete
        // alimente au prealable) et augmenter le cash de la meme somme.
        // Alimentation Bankily = 10000, depot de 10000 -> solde theorique
        // Bankily = 0, Cash = 10000. L'agent compte 9500 en cash (ecart de
        // -500 sur le cash, qui porte l'ecart total).
        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $bankily->id, 'montant' => 10000, 'date' => today()]);

        Livewire::test(Cloture::class)
            ->assertSet('clotureDuJour', null)
            ->call('cloturer', [$bankily->id => 0, $cash->id => 9500]);

        $cloture = ClotureModel::first();
        $this->assertNotNull($cloture);
        $this->assertEquals(10000, $cloture->solde_theorique);
        $this->assertEquals(9500, $cloture->solde_compte);
        $this->assertEquals(-500, $cloture->ecart);

        // L'opération est désormais verrouillée (non modifiable)
        $operation = Operation::first();
        $this->assertNotNull($operation->cloture_id);
        $this->assertFalse($operation->estModifiable());
    }

    public function test_cloture_sans_ecart(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $cash = Operateur::create(['nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);
        $tenant->operateurs()->attach([$cash->id], ['actif' => true]);

        $this->actingAs($agent);

        Livewire::test(Cloture::class)->call('cloturer', [$cash->id => 0]);

        $cloture = ClotureModel::first();
        $this->assertEquals(0, $cloture->ecart);
    }

    public function test_ne_peut_pas_cloturer_deux_fois_le_meme_jour(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $cash = Operateur::create(['nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);
        $tenant->operateurs()->attach([$cash->id], ['actif' => true]);

        $this->actingAs($agent);

        Livewire::test(Cloture::class)->call('cloturer', [$cash->id => 0]);

        $this->assertCount(1, ClotureModel::all());

        // Une seconde tentative de clôture est ignorée (déjà clôturé)
        Livewire::test(Cloture::class)->call('cloturer', [$cash->id => 500]);

        $this->assertCount(1, ClotureModel::all(), 'Une seule clôture par jour et par point.');
    }
}
