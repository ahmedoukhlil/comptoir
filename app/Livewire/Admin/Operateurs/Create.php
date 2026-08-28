<?php

namespace App\Livewire\Admin\Operateurs;

use App\Livewire\Concerns\GereBaremesParType;
use App\Models\Operateur;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Create extends Component
{
    use GereBaremesParType;
    use WithFileUploads;

    public string $nom = '';

    public $logo = null;

    public bool $estCash = false;

    public bool $commissionVerseeDansSolde = false;

    public function mount(): void
    {
        $this->reinitialiserTranches();
    }

    public function creer(): void
    {
        $this->validate([
            'nom' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:1024'],
            ...$this->reglesBaremes($this->estCash),
        ]);

        $logoChemin = $this->logo?->store('logos-operateurs', 'public');

        $operateur = Operateur::create([
            'nom' => $this->nom,
            'logo_chemin' => $logoChemin,
            'bareme_depot' => $this->baremeDepuisFormulaire('depot', $this->estCash),
            'bareme_retrait_client' => $this->baremeDepuisFormulaire('retrait', $this->estCash),
            'bareme_retrait_beneficiaire' => $this->baremeDepuisFormulaire('retrait_beneficiaire', $this->estCash),
            'pourcentage_partage_point_depot' => $this->pourcentageDepuisFormulaire('depot', $this->estCash),
            'pourcentage_partage_point_retrait_client' => $this->pourcentageDepuisFormulaire('retrait', $this->estCash),
            'pourcentage_partage_point_retrait_beneficiaire' => $this->pourcentageDepuisFormulaire('retrait_beneficiaire', $this->estCash),
            'est_cash' => $this->estCash,
            'commission_versee_dans_solde' => $this->estCash ? false : $this->commissionVerseeDansSolde,
            'actif' => true,
        ]);

        $this->redirectRoute('admin.operateurs.index');
    }

    public function render()
    {
        return view('livewire.admin.operateurs.create');
    }
}
