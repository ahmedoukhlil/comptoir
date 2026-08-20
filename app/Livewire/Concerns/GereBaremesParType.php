<?php

namespace App\Livewire\Concerns;

use App\Imports\TranchesOperateurImport;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Logique partagée de saisie des grilles tarifaires (barème + pourcentage
 * de partage) par type d'opération (dépôt / retrait client / retrait
 * bénéficiaire), utilisée par les formulaires de création/modification
 * d'opérateur du back-office admin.
 */
trait GereBaremesParType
{
    use WithFileUploads;

    private const TYPES = ['depot', 'retrait', 'retrait_beneficiaire'];

    private const TRANCHE_VIDE = ['min' => '0', 'max' => '', 'frais' => ''];

    public string $onglet = 'depot';

    /** @var array<string, array<int, array{min: string, max: string, frais: string}>> */
    public array $tranchesParType = [];

    /** @var array<string, string> */
    public array $pourcentagesParType = [];

    #[Validate('nullable|file|mimes:xlsx,xls|max:2048')]
    public $fichierImport = null;

    public function importerTranches(): void
    {
        $this->validate(['fichierImport' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048']]);

        $import = new TranchesOperateurImport();
        Excel::import($import, $this->fichierImport->getRealPath());

        if (! $import->tranches) {
            $this->addError('fichierImport', __('caisse.import_tranches_vide'));

            return;
        }

        $this->tranchesParType[$this->onglet] = $import->tranches;
        $this->fichierImport = null;
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

    private function reinitialiserTranches(): void
    {
        $this->tranchesParType = [
            'depot' => [self::TRANCHE_VIDE],
            'retrait' => [self::TRANCHE_VIDE],
            'retrait_beneficiaire' => [self::TRANCHE_VIDE],
        ];

        $this->pourcentagesParType = [
            'depot' => '50',
            'retrait' => '50',
            'retrait_beneficiaire' => '50',
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

    private function reglesBaremes(bool $estCash): array
    {
        if ($estCash) {
            return [];
        }

        $regles = [];

        foreach (self::TYPES as $type) {
            // Une tranche jamais remplie (placeholder min=0/max=''/frais='',
            // ex: un onglet encore sans grille comme retrait bénéficiaire)
            // doit rester valide : seules les lignes où l'utilisateur a
            // renseigné un frais sont réellement validées comme entiers.
            $regles["tranchesParType.{$type}"] = ['required', 'array', 'min:1'];
            $regles["tranchesParType.{$type}.*.min"] = ['required', 'integer', 'min:0'];
            $regles["tranchesParType.{$type}.*.max"] = ['nullable', 'integer', 'min:0'];
            $regles["tranchesParType.{$type}.*.frais"] = ['nullable', 'integer', 'min:0'];
            $regles["pourcentagesParType.{$type}"] = ['required', 'numeric', 'min:0', 'max:100'];
        }

        return $regles;
    }

    private function baremeDepuisFormulaire(string $type, bool $estCash): array
    {
        if ($estCash) {
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

    private function pourcentageDepuisFormulaire(string $type, bool $estCash): float
    {
        return $estCash ? 0 : (float) ($this->pourcentagesParType[$type] ?? 0);
    }
}
