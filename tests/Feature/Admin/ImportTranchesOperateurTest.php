<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Operateurs\Edit;
use App\Models\Operateur;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportTranchesOperateurTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_puis_enregistrement_fonctionne(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $operateur = Operateur::factory()->create(['nom' => 'Bankily']);

        Excel::store(new \App\Exports\TranchesOperateurTemplateExport(), 'test-import.xlsx', 'local');
        $chemin = storage_path('app/private/test-import.xlsx');
        $fichier = UploadedFile::fake()->createWithContent('modele.xlsx', file_get_contents($chemin));

        $this->actingAs($superAdmin);

        $resultat = Livewire::test(Edit::class, ['operateurId' => $operateur->id])
            ->call('changerOnglet', 'retrait')
            ->set('fichierImport', $fichier)
            ->call('importerTranches')
            ->assertHasNoErrors();

        $resultat->call('modifier')->assertHasNoErrors();

        $operateur->refresh();
        $this->assertCount(3, $operateur->bareme_retrait_client['tranches']);
    }
}
