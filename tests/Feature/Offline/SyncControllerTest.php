<?php

namespace Tests\Feature\Offline;

use App\Models\Operateur;
use App\Models\Operation;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_synchronise_des_operations_hors_ligne_sans_doublon(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $bankily = Operateur::create(['tenant_id' => $tenant->id, 'nom' => 'Bankily', 'bareme_commission' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]], 'pourcentage_partage_point' => 50]);

        $uuid1 = (string) Str::uuid();
        $uuid2 = (string) Str::uuid();
        $uuid3 = (string) Str::uuid();

        $payload = [
            'operations' => [
                ['uuid_client' => $uuid1, 'operateur_id' => $bankily->id, 'type' => 'depot', 'montant' => 10000, 'client_telephone' => '22334455', 'cree_le' => now()->toIso8601String()],
                ['uuid_client' => $uuid2, 'operateur_id' => $bankily->id, 'type' => 'retrait', 'montant' => 3000, 'client_telephone' => '22334455', 'cree_le' => now()->toIso8601String()],
                ['uuid_client' => $uuid3, 'operateur_id' => $bankily->id, 'type' => 'depot', 'montant' => 5000, 'client_telephone' => '22334455', 'cree_le' => now()->toIso8601String()],
            ],
        ];

        $reponse = $this->actingAs($agent)->postJson('/api/operations/sync', $payload);

        $reponse->assertOk();
        $this->assertCount(3, Operation::all());
        $this->assertEquals(12000, $point->fresh()->soldeCaisse());

        // Rejouer la même synchro (simulateur de coupure réseau au retour de la réponse) : aucun doublon.
        $reponse2 = $this->actingAs($agent)->postJson('/api/operations/sync', $payload);
        $reponse2->assertOk();
        $this->assertCount(3, Operation::all(), 'Aucune opération ne doit être dupliquée en cas de re-synchronisation.');

        foreach ($reponse2->json('resultats') as $resultat) {
            $this->assertEquals('deja_synchronisee', $resultat['statut']);
        }
    }

    public function test_deux_appareils_du_meme_point_synchronisent_sans_ecrasement(): void
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $bankily = Operateur::create(['tenant_id' => $tenant->id, 'nom' => 'Bankily', 'bareme_commission' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]], 'pourcentage_partage_point' => 50]);

        $reponseA = $this->actingAs($agent)->postJson('/api/operations/sync', [
            'operations' => [
                ['uuid_client' => (string) Str::uuid(), 'operateur_id' => $bankily->id, 'type' => 'depot', 'montant' => 8000, 'client_telephone' => '22334455', 'cree_le' => now()->toIso8601String()],
            ],
        ]);

        $reponseB = $this->actingAs($agent)->postJson('/api/operations/sync', [
            'operations' => [
                ['uuid_client' => (string) Str::uuid(), 'operateur_id' => $bankily->id, 'type' => 'depot', 'montant' => 4000, 'client_telephone' => '22334455', 'cree_le' => now()->toIso8601String()],
            ],
        ]);

        $reponseA->assertOk();
        $reponseB->assertOk();

        $this->assertCount(2, Operation::all(), 'Les deux opérations des deux appareils doivent être conservées.');
        $this->assertEquals(12000, $point->fresh()->soldeCaisse());

        $numeros = Operation::pluck('numero_piece')->all();
        $this->assertEquals(2, count(array_unique($numeros)), 'Les numéros de pièce doivent être uniques.');
    }
}
