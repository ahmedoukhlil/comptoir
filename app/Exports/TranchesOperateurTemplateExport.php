<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Modèle Excel vierge à remplir pour importer une grille tarifaire :
 * colonnes min/max/frais, avec une ligne d'exemple. "max" vide signifie
 * une tranche sans plafond (illimité).
 */
class TranchesOperateurTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['min', 'max', 'frais'];
    }

    public function array(): array
    {
        return [
            [0, 500, 6],
            [501, 1000, 9],
            [1001, null, 12],
        ];
    }
}
