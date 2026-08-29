<?php

namespace Tests\Feature\Caisse;

use App\Livewire\Caisse\Saisie;
use App\Models\Alimentation;
use App\Models\Operateur;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Test d'intégration bout en bout du composant Saisie : soumet une vraie
 * opération de retrait bénéficiaire avec commission reversée au point de
 * vente, et vérifie que la commission est bien créditée sur le solde de
 * l'opérateur, l'Operation persistée en base, et que soldeCaisse() reste
 * cohérent avec la somme par opérateur.
 */
class SaisieCommissionSoldeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrait_beneficiaire_credite_la_commission_reversee_et_persiste_loperation(): void
    {
        [$tenant, $point, $agent, $bankily, $cash] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $cash->id, 'montant' => 100000, 'date' => today()]);

        $this->actingAs($agent);

        $resultat = Livewire::test(Saisie::class)
            ->call('confirmer', [
                'type' => 'retrait_beneficiaire',
                'operateurId' => $bankily->id,
                'telephone' => '22334455',
                'clientNom' => 'Client Test',
                'clientNni' => '',
                'montant' => '5000',
            ]);

        $resultat->assertReturned(fn ($valeur) => ! isset($valeur['erreur']));

        $this->assertDatabaseHas('operations', [
            'point_id' => $point->id,
            'operateur_id' => $bankily->id,
            'type' => 'retrait_beneficiaire',
            'montant' => 5000,
            'commission_calculee' => 100,
            'commission_part_point' => 50,
            'commission_part_banque' => 50,
        ]);

        $soldes = $point->fresh()->soldesParOperateur()->pluck('solde', 'operateur.id');

        // Retrait : +5000 sur bankily, puis +50 de commission reversée sur le solde.
        $this->assertSame(5050, $soldes[$bankily->id]);
        // Cash débité de 5000 (contrepartie physique du retrait).
        $this->assertSame(95000, $soldes[$cash->id]);
        // soldeCaisse() doit toujours être la somme des soldes par opérateur.
        $this->assertSame((int) $soldes->sum(), $point->fresh()->soldeCaisse());
    }

    public function test_retrait_beneficiaire_refuse_si_cash_insuffisant_malgre_solde_operateur_suffisant(): void
    {
        [$tenant, $point, $agent, $bankily, $cash] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $cash->id, 'montant' => 1000, 'date' => today()]);

        $this->actingAs($agent);

        $resultat = Livewire::test(Saisie::class)
            ->call('confirmer', [
                'type' => 'retrait_beneficiaire',
                'operateurId' => $bankily->id,
                'telephone' => '22334455',
                'clientNom' => '',
                'clientNni' => '',
                'montant' => '5000',
            ]);

        $resultat->assertReturned(fn ($valeur) => isset($valeur['erreur']));
        $this->assertDatabaseMissing('operations', ['point_id' => $point->id, 'montant' => 5000]);
    }

    /**
     * @return array{0: Tenant, 1: Point, 2: User, 3: Operateur, 4: Operateur}
     */
    private function creerContexte(): array
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $bankily = Operateur::create([
            'nom' => 'Bankily',
            'bareme_depot' => ['tranches' => []],
            'bareme_retrait_client' => ['tranches' => []],
            'bareme_retrait_beneficiaire' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]],
            'pourcentage_partage_point_depot' => 50,
            'pourcentage_partage_point_retrait_client' => 50,
            'pourcentage_partage_point_retrait_beneficiaire' => 50,
            'commission_versee_dans_solde' => true,
        ]);
        $cash = Operateur::create(['nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);
        $tenant->operateurs()->attach([$bankily->id, $cash->id], ['actif' => true]);

        return [$tenant, $point, $agent, $bankily, $cash];
    }
}
