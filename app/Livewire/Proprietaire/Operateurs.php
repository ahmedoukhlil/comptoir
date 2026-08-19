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

    public bool $estCash = false;

    public string $pourcentagePartagePoint = '50';

    public bool $commissionVerseeDansSolde = false;

    /** @var array<int, array{min: string, max: string, frais: string}> */
    public array $tranches = [
        ['min' => '0', 'max' => '', 'frais' => ''],
    ];

    public ?int $operateurAModifierId = null;

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

    #[Computed]
    public function operateurAModifier(): ?Operateur
    {
        if (! $this->operateurAModifierId) {
            return null;
        }

        return Operateur::where('tenant_id', $this->tenant->id)
            ->whereKey($this->operateurAModifierId)
            ->first();
    }

    public function ajouterTranche(): void
    {
        $this->tranches[] = ['min' => '', 'max' => '', 'frais' => ''];
    }

    public function retirerTranche(int $index): void
    {
        if (count($this->tranches) <= 1) {
            return;
        }

        unset($this->tranches[$index]);
        $this->tranches = array_values($this->tranches);
    }

    public function creer(): void
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            $this->addError('lectureSeule', $erreur['erreur']);

            return;
        }

        $this->validerFormulaire();

        Operateur::create([
            'tenant_id' => $this->tenant->id,
            'nom' => $this->nom,
            'bareme_commission' => $this->baremeDepuisFormulaire(),
            'est_cash' => $this->estCash,
            'pourcentage_partage_point' => $this->estCash ? 0 : (float) $this->pourcentagePartagePoint,
            'commission_versee_dans_solde' => $this->estCash ? false : $this->commissionVerseeDansSolde,
            'actif' => true,
        ]);

        $this->reset(['nom', 'estCash', 'pourcentagePartagePoint', 'commissionVerseeDansSolde', 'tranches']);
        $this->pourcentagePartagePoint = '50';
        $this->tranches = [['min' => '0', 'max' => '', 'frais' => '']];
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
        $this->pourcentagePartagePoint = (string) $operateur->pourcentage_partage_point;
        $this->commissionVerseeDansSolde = $operateur->commission_versee_dans_solde;

        $tranchesExistantes = $operateur->bareme_commission['tranches'] ?? [];

        $this->tranches = $tranchesExistantes
            ? array_map(fn ($t) => [
                'min' => (string) ($t['min'] ?? 0),
                'max' => $t['max'] !== null ? (string) $t['max'] : '',
                'frais' => (string) ($t['frais'] ?? 0),
            ], $tranchesExistantes)
            : [['min' => '0', 'max' => '', 'frais' => '']];
    }

    public function fermerModification(): void
    {
        $this->operateurAModifierId = null;
        $this->reset(['pourcentagePartagePoint', 'commissionVerseeDansSolde', 'tranches']);
        $this->pourcentagePartagePoint = '50';
        $this->tranches = [['min' => '0', 'max' => '', 'frais' => '']];
        $this->resetErrorBag();
    }

    public function modifier(): void
    {
        if ($erreur = $this->refuserSiLectureSeule()) {
            $this->addError('lectureSeule', $erreur['erreur']);

            return;
        }

        $operateur = Operateur::where('tenant_id', $this->tenant->id)
            ->whereKey($this->operateurAModifierId)
            ->firstOrFail();

        $this->estCash = $operateur->est_cash;

        $this->validerFormulaire();

        $operateur->update([
            'bareme_commission' => $this->baremeDepuisFormulaire(),
            'pourcentage_partage_point' => $operateur->est_cash ? 0 : (float) $this->pourcentagePartagePoint,
            'commission_versee_dans_solde' => $operateur->est_cash ? false : $this->commissionVerseeDansSolde,
        ]);

        $this->fermerModification();
        unset($this->operateurs);
    }

    private function validerFormulaire(): void
    {
        $regles = [
            'nom' => ['required_without:operateurAModifierId', 'string', 'max:255'],
        ];

        if (! $this->estCash) {
            $regles['pourcentagePartagePoint'] = ['required', 'numeric', 'min:0', 'max:100'];
            $regles['tranches'] = ['required', 'array', 'min:1'];
            $regles['tranches.*.min'] = ['required', 'integer', 'min:0'];
            $regles['tranches.*.max'] = ['nullable', 'integer', 'min:0'];
            $regles['tranches.*.frais'] = ['required', 'integer', 'min:0'];
        }

        $this->validate($regles);
    }

    private function baremeDepuisFormulaire(): array
    {
        if ($this->estCash) {
            return ['tranches' => []];
        }

        return [
            'tranches' => collect($this->tranches)->map(fn ($t) => [
                'min' => (int) $t['min'],
                'max' => $t['max'] === '' ? null : (int) $t['max'],
                'frais' => (int) $t['frais'],
            ])->all(),
        ];
    }

    public function render()
    {
        return view('livewire.proprietaire.operateurs');
    }
}
