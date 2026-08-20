<?php

namespace App\Livewire\Admin\Operateurs;

use App\Livewire\Concerns\GereBaremesParType;
use App\Models\Operateur;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Edit extends Component
{
    use GereBaremesParType;

    public int $operateurId;

    public bool $commissionVerseeDansSolde = false;

    public function mount(int $operateurId): void
    {
        $this->operateurId = $operateurId;

        $operateur = $this->operateur;

        $this->commissionVerseeDansSolde = $operateur->commission_versee_dans_solde;

        $this->tranchesParType = [
            'depot' => $this->tranchesDepuisBareme($operateur->bareme_depot),
            'retrait' => $this->tranchesDepuisBareme($operateur->bareme_retrait_client),
            'retrait_beneficiaire' => $this->tranchesDepuisBareme($operateur->bareme_retrait_beneficiaire),
        ];

        $this->pourcentagesParType = [
            'depot' => (string) $operateur->pourcentage_partage_point_depot,
            'retrait' => (string) $operateur->pourcentage_partage_point_retrait_client,
            'retrait_beneficiaire' => (string) $operateur->pourcentage_partage_point_retrait_beneficiaire,
        ];
    }

    #[Computed]
    public function operateur(): Operateur
    {
        return Operateur::findOrFail($this->operateurId);
    }

    public function modifier(): void
    {
        $operateur = $this->operateur;

        $this->validate($this->reglesBaremes($operateur->est_cash));

        $operateur->update([
            'bareme_depot' => $this->baremeDepuisFormulaire('depot', $operateur->est_cash),
            'bareme_retrait_client' => $this->baremeDepuisFormulaire('retrait', $operateur->est_cash),
            'bareme_retrait_beneficiaire' => $this->baremeDepuisFormulaire('retrait_beneficiaire', $operateur->est_cash),
            'pourcentage_partage_point_depot' => $this->pourcentageDepuisFormulaire('depot', $operateur->est_cash),
            'pourcentage_partage_point_retrait_client' => $this->pourcentageDepuisFormulaire('retrait', $operateur->est_cash),
            'pourcentage_partage_point_retrait_beneficiaire' => $this->pourcentageDepuisFormulaire('retrait_beneficiaire', $operateur->est_cash),
            'commission_versee_dans_solde' => $operateur->est_cash ? false : $this->commissionVerseeDansSolde,
        ]);

        unset($this->operateur);

        $this->redirectRoute('admin.operateurs.index');
    }

    public function render()
    {
        return view('livewire.admin.operateurs.edit');
    }
}
