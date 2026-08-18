<?php

namespace Tests\Feature\Plans;

use App\Livewire\Caisse\Saisie;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class PlanTarifaireTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_solo_recoit_automatiquement_un_essai_gratuit_de_14_jours(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo']);

        $this->assertEquals('essai', $tenant->statut);
        $this->assertNotNull($tenant->essai_expire_le);
        $this->assertEqualsWithDelta(
            now()->addDays(14)->timestamp,
            $tenant->essai_expire_le->timestamp,
            5
        );
    }

    public function test_tenant_solo_ne_voit_pas_les_ecrans_multi_points(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($proprietaire);

        $this->get('/tableau-de-bord')->assertForbidden();
        $this->get('/tableau-de-bord/alimentation')->assertForbidden();
        $this->get('/tableau-de-bord/transfert')->assertForbidden();
        $this->get('/tableau-de-bord/rapport')->assertForbidden();

        // Le propriétaire Solo utilise en revanche l'écran de saisie comme un agent.
        $this->get('/caisse')->assertOk();
    }

    public function test_passer_le_tenant_en_plan_reseau_debloque_immediatement_le_dashboard(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $proprietaire = User::factory()->proprietaire()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->actingAs($proprietaire);
        $this->get('/tableau-de-bord')->assertForbidden();

        $tenant->update(['plan' => 'reseau']);

        // Ré-authentifier pour simuler une nouvelle requête HTTP dans une
        // vraie session : Auth::user() est mis en cache par le guard pour
        // la durée du test, donc il faut forcer une relecture pour refléter
        // le changement de plan fait directement en base.
        $this->actingAs($proprietaire->fresh());

        $this->get('/tableau-de-bord')->assertOk();
    }

    public function test_essai_expire_passe_le_tenant_en_lecture_seule(): void
    {
        $tenant = Tenant::create([
            'nom' => 'T',
            'plan' => 'solo',
            'statut' => 'essai',
            'essai_expire_le' => Carbon::now()->subDay(),
        ]);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);

        $this->assertTrue($tenant->enLectureSeule());

        $this->actingAs($agent);

        // L'écran reste consultable...
        $this->get('/caisse')->assertOk();

        // ...mais aucune nouvelle opération ne peut être enregistrée.
        $resultat = Livewire::test(Saisie::class)->call('confirmer', [
            'type' => 'depot',
            'operateurId' => 1,
            'telephone' => '22334455',
            'clientNom' => '',
            'clientNni' => '',
            'montant' => '5000',
        ]);

        $resultat->assertReturned(fn ($valeur) => isset($valeur['erreur']));
    }
}
