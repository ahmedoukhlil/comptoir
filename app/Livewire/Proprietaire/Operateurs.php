<?php

namespace App\Livewire\Proprietaire;

use App\Livewire\Concerns\BasculeLangue;
use App\Livewire\Concerns\VerifieLectureSeule;
use App\Models\Operateur;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Operateurs extends Component
{
    use BasculeLangue, VerifieLectureSeule;

    public string $nom = '';

    public string $commissionPourcentage = '';

    public bool $estCash = false;

    public ?int $operateurAModifierId = null;

    public string $commissionModifiee = '';

    #[Computed]
    public function tenant()
    {
        return Auth::user()->tenant;
    }

    #[Computed]
    public function operateurs()
    {
        return Operateur::query()
            ->duTenant($this->tenant->id)
            ->orderByDesc('actif')
            ->orderBy('nom')
            ->get();
    }

    public function creer(): void
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            $this->addError('lectureSeule', $erreur['erreur']);

            return;
        }

        $this->validate([
            'nom' => ['required', 'string', 'max:255'],
            'commissionPourcentage' => ['required_if:estCash,false', 'nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $bareme = $this->estCash
            ? ['tranches' => []]
            : ['tranches' => [['min' => 0, 'max' => null, 'pourcentage' => (float) $this->commissionPourcentage]]];

        Operateur::create([
            'tenant_id' => $this->tenant->id,
            'nom' => $this->nom,
            'bareme_commission' => $bareme,
            'est_cash' => $this->estCash,
            'actif' => true,
        ]);

        $this->reset(['nom', 'commissionPourcentage', 'estCash']);
        unset($this->operateurs);
    }

    public function basculerActif(int $operateurId): void
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            $this->addError('lectureSeule', $erreur['erreur']);

            return;
        }

        $operateur = Operateur::where('tenant_id', $this->tenant->id)
            ->whereKey($operateurId)
            ->firstOrFail();

        $operateur->update(['actif' => ! $operateur->actif]);

        unset($this->operateurs);
    }

    public function ouvrirModification(int $operateurId): void
    {
        $operateur = Operateur::where('tenant_id', $this->tenant->id)
            ->whereKey($operateurId)
            ->firstOrFail();

        $this->operateurAModifierId = $operateur->id;
        $this->commissionModifiee = (string) ($operateur->bareme_commission['tranches'][0]['pourcentage'] ?? 0);
    }

    public function fermerModification(): void
    {
        $this->operateurAModifierId = null;
        $this->reset(['commissionModifiee']);
        $this->resetErrorBag('commissionModifiee');
    }

    public function modifierCommission(): void
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            $this->addError('lectureSeule', $erreur['erreur']);

            return;
        }

        $this->validate([
            'commissionModifiee' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $operateur = Operateur::where('tenant_id', $this->tenant->id)
            ->whereKey($this->operateurAModifierId)
            ->firstOrFail();

        $operateur->update([
            'bareme_commission' => ['tranches' => [['min' => 0, 'max' => null, 'pourcentage' => (float) $this->commissionModifiee]]],
        ]);

        $this->fermerModification();
        unset($this->operateurs);
    }

    public function render()
    {
        return view('livewire.proprietaire.operateurs');
    }
}
