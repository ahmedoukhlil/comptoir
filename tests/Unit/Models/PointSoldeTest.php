<?php

namespace Tests\Unit\Models;

use App\Models\Alimentation;
use App\Models\Operateur;
use App\Models\Operation;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\Transfert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Teste directement Point::soldesParOperateur()/soldeCaisse() en manipulant
 * les modèles (Alimentation, Operation, Transfert), sans passer par le
 * composant Livewire de saisie : ce sont les règles de calcul du solde qui
 * sont visées ici, pas le flux de confirmation d'une opération.
 */
class PointSoldeTest extends TestCase
{
    use RefreshDatabase;

    public function test_alimentation_credite_le_solde_de_loperateur_correspondant(): void
    {
        [$tenant, $point, $bankily] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $bankily->id, 'montant' => 20000, 'date' => today()]);

        $soldes = $point->soldesParOperateur()->pluck('solde', 'operateur.id');

        $this->assertSame(20000, $soldes[$bankily->id]);
        $this->assertSame(20000, $point->soldeCaisse());
    }

    public function test_depot_diminue_loperateur_mobile_money_et_augmente_le_cash(): void
    {
        [$tenant, $point, $bankily, , $cash] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $bankily->id, 'montant' => 20000, 'date' => today()]);

        Operation::create([
            'numero_piece' => 'OP-2026-000001', 'point_id' => $point->id, 'agent_id' => $point->agents()->first()?->id ?? User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id])->id,
            'operateur_id' => $bankily->id, 'type' => 'depot', 'montant' => 5000, 'client_telephone' => '22334455',
            'commission_calculee' => 0, 'commission_part_point' => 0, 'commission_part_banque' => 0,
        ]);

        $soldes = $point->soldesParOperateur()->pluck('solde', 'operateur.id');

        $this->assertSame(15000, $soldes[$bankily->id]);
        $this->assertSame(5000, $soldes[$cash->id]);
        $this->assertSame(20000, $point->soldeCaisse());
    }

    public function test_retrait_augmente_loperateur_mobile_money_et_diminue_le_cash(): void
    {
        [$tenant, $point, $bankily, , $cash] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $cash->id, 'montant' => 20000, 'date' => today()]);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        Operation::create([
            'numero_piece' => 'OP-2026-000002', 'point_id' => $point->id, 'agent_id' => $agent->id,
            'operateur_id' => $bankily->id, 'type' => 'retrait', 'montant' => 5000, 'client_telephone' => '22334455',
            'commission_calculee' => 0, 'commission_part_point' => 0, 'commission_part_banque' => 0,
        ]);

        $soldes = $point->soldesParOperateur()->pluck('solde', 'operateur.id');

        $this->assertSame(5000, $soldes[$bankily->id]);
        $this->assertSame(15000, $soldes[$cash->id]);
        $this->assertSame(20000, $point->soldeCaisse());
    }

    public function test_transfert_deplace_le_solde_entre_deux_operateurs_sans_toucher_le_cash(): void
    {
        [$tenant, $point, $bankily, $masrivi, $cash] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $bankily->id, 'montant' => 20000, 'date' => today()]);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        Transfert::create([
            'point_id' => $point->id, 'agent_id' => $agent->id,
            'operateur_source_id' => $bankily->id, 'operateur_destination_id' => $masrivi->id,
            'montant' => 8000,
        ]);

        $soldes = $point->soldesParOperateur()->pluck('solde', 'operateur.id');

        $this->assertSame(12000, $soldes[$bankily->id]);
        $this->assertSame(8000, $soldes[$masrivi->id]);
        $this->assertSame(0, $soldes[$cash->id]);
        $this->assertSame(20000, $point->soldeCaisse());
    }

    public function test_commission_versee_dans_solde_credite_loperateur_uniquement_si_active(): void
    {
        [$tenant, $point, $bankily] = $this->creerContexte();
        $bankily->update(['commission_versee_dans_solde' => true]);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        Operation::create([
            'numero_piece' => 'OP-2026-000003', 'point_id' => $point->id, 'agent_id' => $agent->id,
            'operateur_id' => $bankily->id, 'type' => 'retrait', 'montant' => 5000, 'client_telephone' => '22334455',
            'commission_calculee' => 200, 'commission_part_point' => 100, 'commission_part_banque' => 100,
        ]);

        $soldes = $point->soldesParOperateur()->pluck('solde', 'operateur.id');

        // Retrait : +5000 (mobile money) puis +100 de commission versée sur le solde.
        $this->assertSame(5100, $soldes[$bankily->id]);
    }

    public function test_commission_non_versee_dans_solde_nest_pas_creditee(): void
    {
        [$tenant, $point, $bankily] = $this->creerContexte();
        // commission_versee_dans_solde reste false (défaut de la factory).
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        Operation::create([
            'numero_piece' => 'OP-2026-000004', 'point_id' => $point->id, 'agent_id' => $agent->id,
            'operateur_id' => $bankily->id, 'type' => 'retrait', 'montant' => 5000, 'client_telephone' => '22334455',
            'commission_calculee' => 200, 'commission_part_point' => 100, 'commission_part_banque' => 100,
        ]);

        $soldes = $point->soldesParOperateur()->pluck('solde', 'operateur.id');

        $this->assertSame(5000, $soldes[$bankily->id]);
    }

    /**
     * @return array{0: Tenant, 1: Point, 2: Operateur, 3: Operateur, 4: Operateur}
     */
    private function creerContexte(): array
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $bankily = Operateur::factory()->create(['nom' => 'Bankily']);
        $masrivi = Operateur::factory()->create(['nom' => 'Masrivi']);
        $cash = Operateur::factory()->create(['nom' => 'Cash', 'est_cash' => true]);
        $tenant->operateurs()->attach([$bankily->id, $masrivi->id, $cash->id], ['actif' => true]);

        return [$tenant, $point, $bankily, $masrivi, $cash];
    }
}
