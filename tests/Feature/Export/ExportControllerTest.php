<?php

namespace Tests\Feature\Export;

use App\Models\Operateur;
use App\Models\Operation;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    private function creerJeuDeDonnees(): array
    {
        $tenant = Tenant::create(['nom' => 'T', 'plan' => 'solo', 'statut' => 'actif']);
        $point = Point::create(['tenant_id' => $tenant->id, 'nom' => 'K']);
        $agent = User::factory()->create(['tenant_id' => $tenant->id, 'point_id' => $point->id]);
        $bankily = Operateur::create(['nom' => 'Bankily', 'bareme_depot' => ['tranches' => []], 'bareme_retrait_client' => ['tranches' => [['min' => 0, 'max' => null, 'frais' => 100]]], 'bareme_retrait_beneficiaire' => ['tranches' => []], 'pourcentage_partage_point_depot' => 50, 'pourcentage_partage_point_retrait_client' => 50, 'pourcentage_partage_point_retrait_beneficiaire' => 50]);
        $tenant->operateurs()->attach([$bankily->id], ['actif' => true]);

        Operation::create([
            'numero_piece' => 'OP-2026-000001',
            'uuid_client' => (string) Str::uuid(),
            'point_id' => $point->id,
            'agent_id' => $agent->id,
            'operateur_id' => $bankily->id,
            'type' => 'depot',
            'montant' => 10000,
            'commission_calculee' => 100,
            'client_nom' => 'Fatimetou',
            'client_telephone' => '22334455',
            'client_nni' => '1234567890123',
            'synced' => true,
        ]);

        Operation::create([
            'numero_piece' => 'OP-2026-000002',
            'uuid_client' => (string) Str::uuid(),
            'point_id' => $point->id,
            'agent_id' => $agent->id,
            'operateur_id' => $bankily->id,
            'type' => 'retrait',
            'montant' => 3000,
            'commission_calculee' => 30,
            'client_telephone' => '22334456',
            'synced' => true,
        ]);

        return [$agent, $point];
    }

    public function test_export_excel_fonctionne(): void
    {
        [$agent] = $this->creerJeuDeDonnees();

        $reponse = $this->actingAs($agent)->get('/caisse/historique/export/excel');

        $reponse->assertOk();
        $reponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_pdf_fonctionne_et_masque_le_nni(): void
    {
        [$agent] = $this->creerJeuDeDonnees();

        $reponse = $this->actingAs($agent)->get('/caisse/historique/export/pdf');

        $reponse->assertOk();
        $reponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_journal_builder_masque_le_nni_et_calcule_le_solde_cumule(): void
    {
        [$agent, $point] = $this->creerJeuDeDonnees();

        $operations = $point->operations()->get();
        $journal = \App\Support\JournalBuilder::construire($point, $operations);

        $this->assertCount(2, $journal['lignes']);
        $this->assertEquals(10000, $journal['total_entrees']);
        $this->assertEquals(3000, $journal['total_sorties']);
        $this->assertEquals(7000, $journal['solde_net']);

        // Le NNI ne doit jamais apparaître en clair dans le journal exporté.
        foreach ($journal['lignes'] as $ligne) {
            $this->assertStringNotContainsString('1234567890123', $ligne['client']);
        }
    }
}
