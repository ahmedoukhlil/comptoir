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

    public function test_retrait_refuse_si_solde_de_loperateur_choisi_est_insuffisant_meme_si_le_total_suffit(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $bankily = Operateur::create(['nom' => 'Bankily', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'pourcentage_partage_point_depot' => 50, 'pourcentage_partage_point_retrait_client' => 50, 'pourcentage_partage_point_retrait_beneficiaire' => 50]);
        $masrivi = Operateur::create(['nom' => 'Masrivi', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'pourcentage_partage_point_depot' => 50, 'pourcentage_partage_point_retrait_client' => 50, 'pourcentage_partage_point_retrait_beneficiaire' => 50]);
        $cash = Operateur::create(['nom' => 'Cash', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => []], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'est_cash' => true]);
        $tenant->operateurs()->attach([$bankily->id, $masrivi->id, $cash->id], ['actif' => true]);

        // Bankily a très peu, Masrivi et Cash ont beaucoup : le total suffit largement
        // mais le solde Bankily spécifique ne suffit pas pour un retrait Bankily.
        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $bankily->id, 'montant' => 1000, 'date' => today()]);
        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $masrivi->id, 'montant' => 100000, 'date' => today()]);
        Alimentation::create(['tenant_id' => $tenant->id, 'point_id' => $point->id, 'operateur_id' => $cash->id, 'montant' => 100000, 'date' => today()]);

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

        // Le même retrait sur Masrivi, qui a un solde suffisant, doit passer.
        $resultat2 = Livewire::test(Saisie::class)
            ->call('confirmer', [
                'type' => 'retrait',
                'operateurId' => $masrivi->id,
                'telephone' => '22334455',
                'clientNom' => '',
                'clientNni' => '',
                'montant' => '5000',
            ]);

        $resultat2->assertReturned(fn ($valeur) => ! isset($valeur['erreur']));
    }
}
