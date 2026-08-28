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

class SoldeParOperateurTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Un dépôt fait diminuer le solde mobile money de l'opérateur choisi
     * (l'agent envoie l'équivalent au destinataire depuis son propre
     * compte) : il doit être refusé si ce solde est insuffisant, même si
     * le cash ou un autre opérateur suffit largement.
     */
    public function test_depot_refuse_si_solde_de_loperateur_choisi_est_insuffisant(): void
    {
        [$tenant, $point, $agent, $bankily, $masrivi, $cash] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $bankily->id, 'montant' => 1000, 'date' => today()]);
        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $masrivi->id, 'montant' => 100000, 'date' => today()]);
        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $cash->id, 'montant' => 100000, 'date' => today()]);

        $this->actingAs($agent);

        $resultat = Livewire::test(Saisie::class)
            ->call('confirmer', [
                'type' => 'depot',
                'operateurId' => $bankily->id,
                'telephone' => '22334455',
                'clientNom' => '',
                'clientNni' => '',
                'montant' => '5000',
            ]);

        $resultat->assertReturned(fn ($valeur) => isset($valeur['erreur']));

        $resultat2 = Livewire::test(Saisie::class)
            ->call('confirmer', [
                'type' => 'depot',
                'operateurId' => $masrivi->id,
                'telephone' => '22334455',
                'clientNom' => '',
                'clientNni' => '',
                'montant' => '5000',
            ]);

        $resultat2->assertReturned(fn ($valeur) => ! isset($valeur['erreur']));
    }

    /**
     * Un retrait fait diminuer le cash (contrepartie physique remise au
     * client) : il doit être refusé si le cash est insuffisant, même si le
     * solde de l'opérateur mobile money choisi suffit largement.
     */
    public function test_retrait_refuse_si_cash_insuffisant(): void
    {
        [$tenant, $point, $agent, $bankily, , $cash] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $bankily->id, 'montant' => 100000, 'date' => today()]);
        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $cash->id, 'montant' => 1000, 'date' => today()]);

        $this->actingAs($agent);

        $resultat = Livewire::test(Saisie::class)
            ->call('confirmer', [
                'type' => 'retrait',
                'operateurId' => $bankily->id,
                'telephone' => '22334455',
                'clientNom' => '',
                'clientNni' => '',
                'montant' => '5000',
            ]);

        $resultat->assertReturned(fn ($valeur) => isset($valeur['erreur']));

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $cash->id, 'montant' => 10000, 'date' => today()]);

        $resultat2 = Livewire::test(Saisie::class)
            ->call('confirmer', [
                'type' => 'retrait',
                'operateurId' => $bankily->id,
                'telephone' => '22334455',
                'clientNom' => '',
                'clientNni' => '',
                'montant' => '5000',
            ]);

        $resultat2->assertReturned(fn ($valeur) => ! isset($valeur['erreur']));
    }

    /**
     * Un dépôt puis un retrait du même montant sur le même opérateur
     * laissent le solde mobile money et le cash inchangés (aller-retour
     * exact), et le total combiné (soldeCaisse) ne bouge qu'avec les
     * alimentations.
     */
    public function test_depot_puis_retrait_annulent_leur_impact_reciproque(): void
    {
        [$tenant, $point, $agent, $bankily, , $cash] = $this->creerContexte();

        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $bankily->id, 'montant' => 50000, 'date' => today()]);
        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $cash->id, 'montant' => 50000, 'date' => today()]);

        $this->actingAs($agent);

        Livewire::test(Saisie::class)->call('confirmer', [
            'type' => 'depot',
            'operateurId' => $bankily->id,
            'telephone' => '22334455',
            'clientNom' => '',
            'clientNni' => '',
            'montant' => '5000',
        ]);

        Livewire::test(Saisie::class)->call('confirmer', [
            'type' => 'retrait',
            'operateurId' => $bankily->id,
            'telephone' => '22334455',
            'clientNom' => '',
            'clientNni' => '',
            'montant' => '5000',
        ]);

        $soldes = $point->fresh()->soldesParOperateur()->pluck('solde', 'operateur.id');

        $this->assertEquals(50000, $soldes[$bankily->id]);
        $this->assertEquals(50000, $soldes[$cash->id]);
        $this->assertEquals(100000, $point->fresh()->soldeCaisse());
    }

    /**
     * @return array{0: Tenant, 1: Point, 2: User, 3: Operateur, 4: Operateur, 5: Operateur}
     */
    private function creerContexte(): array
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $bankily = Operateur::create(['nom' => 'Bankily', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'pourcentage_partage_point_depot' => 50, 'pourcentage_partage_point_retrait_client' => 50, 'pourcentage_partage_point_retrait_beneficiaire' => 50]);
        $masrivi = Operateur::create(['nom' => 'Masrivi', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'pourcentage_partage_point_depot' => 50, 'pourcentage_partage_point_retrait_client' => 50, 'pourcentage_partage_point_retrait_beneficiaire' => 50]);
        $cash = Operateur::create(['nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);
        $tenant->operateurs()->attach([$bankily->id, $masrivi->id, $cash->id], ['actif' => true]);

        return [$tenant, $point, $agent, $bankily, $masrivi, $cash];
    }
}
