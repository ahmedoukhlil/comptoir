<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Lit un fichier Excel de grille tarifaire (colonnes min, max, frais) pour
 * remplacer les tranches d'un onglet du formulaire opérateur. La première
 * ligne doit contenir les en-têtes "min", "max", "frais" (insensible à la
 * casse, cf. WithHeadingRow).
 */
class TranchesOperateurImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, array{min: string, max: string, frais: string}> */
    public array $tranches = [];

    public function collection(\Illuminate\Support\Collection $rows): void
    {
        $this->tranches = $rows
            ->filter(fn ($row) => $row['frais'] !== null && $row['frais'] !== '')
            ->map(fn ($row) => [
                'min' => (string) (int) ($row['min'] ?? 0),
                'max' => ($row['max'] ?? null) === null || $row['max'] === '' ? '' : (string) (int) $row['max'],
                'frais' => (string) (int) $row['frais'],
            ])
            ->values()
            ->all();
    }
}
