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

    private const TYPES = ['depot', 'retrait', 'retrait_beneficiaire'];

    private const TRANCHE_VIDE = ['min' => '0', 'max' => '', 'frais' => ''];

    public string $nom = '';

    public bool $estCash = false;

    public string $onglet = 'depot';

    public string $pourcentagePartagePoint = '50';

    public bool $commissionVerseeDansSolde = false;

    /** @var array<string, array<int, array{min: string, max: string, frais: string}>> */
    public array $tranchesParType = [];

    public ?int $operateurAModifierId = null;

    public function mount(): void
    {
        $this->reinitialiserTranches();
    }

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

    public function changerOnglet(string $type): void
    {
        if (in_array($type, self::TYPES, true)) {
            $this->onglet = $type;
        }
    }

    public function ajouterTranche(): void
    {
        $this->tranchesParType[$this->onglet][] = self::TRANCHE_VIDE;
    }

    public function retirerTranche(int $index): void
    {
        if (count($this->tranchesParType[$this->onglet]) <= 1) {
            return;
        }

        unset($this->tranchesParType[$this->onglet][$index]);
        $this->tranchesParType[$this->onglet] = array_values($this->tranchesParType[$this->onglet]);
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
            'bareme_depot' => $this->baremeDepuisFormulaire('depot'),
            'bareme_retrait_client' => $this->baremeDepuisFormulaire('retrait'),
            'bareme_retrait_beneficiaire' => $this->baremeDepuisFormulaire('retrait_beneficiaire'),
            'est_cash' => $this->estCash,
            'pourcentage_partage_point' => $this->estCash ? 0 : (float) $this->pourcentagePartagePoint,
            'commission_versee_dans_solde' => $this->estCash ? false : $this->commissionVerseeDansSolde,
            'actif' => true,
        ]);

        $this->reset(['nom', 'estCash', 'pourcentagePartagePoint', 'commissionVerseeDansSolde']);
        $this->pourcentagePartagePoint = '50';
        $this->onglet = 'depot';
        $this->reinitialiserTranches();
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
        $this->onglet = 'depot';
        $this->pourcentagePartagePoint = (string) $operateur->pourcentage_partage_point;
        $this->commissionVerseeDansSolde = $operateur->commission_versee_dans_solde;

        $this->tranchesParType = [
            'depot' => $this->tranchesDepuisBareme($operateur->bareme_depot),
            'retrait' => $this->tranchesDepuisBareme($operateur->bareme_retrait_client),
            'retrait_beneficiaire' => $this->tranchesDepuisBareme($operateur->bareme_retrait_beneficiaire),
        ];
    }

    public function fermerModification(): void
    {
        $this->operateurAModifierId = null;
        $this->reset(['pourcentagePartagePoint', 'commissionVerseeDansSolde']);
        $this->pourcentagePartagePoint = '50';
        $this->onglet = 'depot';
        $this->reinitialiserTranches();
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
            'bareme_depot' => $this->baremeDepuisFormulaire('depot'),
            'bareme_retrait_client' => $this->baremeDepuisFormulaire('retrait'),
            'bareme_retrait_beneficiaire' => $this->baremeDepuisFormulaire('retrait_beneficiaire'),
            'pourcentage_partage_point' => $operateur->est_cash ? 0 : (float) $this->pourcentagePartagePoint,
            'commission_versee_dans_solde' => $operateur->est_cash ? false : $this->commissionVerseeDansSolde,
        ]);

        $this->fermerModification();
        unset($this->operateurs);
    }

    private function reinitialiserTranches(): void
    {
        $this->tranchesParType = [
            'depot' => [self::TRANCHE_VIDE],
            'retrait' => [self::TRANCHE_VIDE],
            'retrait_beneficiaire' => [self::TRANCHE_VIDE],
        ];
    }

    private function tranchesDepuisBareme(?array $bareme): array
    {
        $tranches = $bareme['tranches'] ?? [];

        if (! $tranches) {
            return [self::TRANCHE_VIDE];
        }

        return array_map(fn ($t) => [
            'min' => (string) ($t['min'] ?? 0),
            'max' => $t['max'] !== null ? (string) $t['max'] : '',
            'frais' => (string) ($t['frais'] ?? 0),
        ], $tranches);
    }

    private function validerFormulaire(): void
    {
        $regles = [
            'nom' => ['required_without:operateurAModifierId', 'string', 'max:255'],
        ];

        if (! $this->estCash) {
            $regles['pourcentagePartagePoint'] = ['required', 'numeric', 'min:0', 'max:100'];

            foreach (self::TYPES as $type) {
                $regles["tranchesParType.{$type}"] = ['required', 'array', 'min:1'];
                $regles["tranchesParType.{$type}.*.min"] = ['required', 'integer', 'min:0'];
                $regles["tranchesParType.{$type}.*.max"] = ['nullable', 'integer', 'min:0'];
                $regles["tranchesParType.{$type}.*.frais"] = ['required', 'integer', 'min:0'];
            }
        }

        $this->validate($regles);
    }

    private function baremeDepuisFormulaire(string $type): array
    {
        if ($this->estCash) {
            return ['tranches' => []];
        }

        $tranches = $this->tranchesParType[$type] ?? [];

        // Une tranche vide (jamais remplie, ex: retrait bénéficiaire encore
        // sans grille) ne doit pas être enregistrée comme "0-∞ → 0 MRU".
        $tranchesRemplies = array_values(array_filter(
            $tranches,
            fn ($t) => $t['frais'] !== '' || $t['max'] !== ''
        ));

        return [
            'tranches' => collect($tranchesRemplies)->map(fn ($t) => [
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
