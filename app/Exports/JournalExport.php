<?php

namespace App\Exports;

use App\Models\Point;
use App\Support\JournalBuilder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JournalExport implements FromArray, WithHeadings, WithStyles
{
    private array $journal;

    public function __construct(Point $point, Collection $operations)
    {
        $this->journal = JournalBuilder::construire($point, $operations);
    }

    public function headings(): array
    {
        return [
            __('caisse.colonne_date_heure'),
            __('caisse.colonne_numero_piece'),
            __('caisse.colonne_type'),
            __('caisse.colonne_client'),
            __('caisse.colonne_entrees'),
            __('caisse.colonne_sorties'),
            __('caisse.colonne_solde'),
            __('caisse.colonne_commission'),
            __('caisse.colonne_observation'),
        ];
    }

    public function array(): array
    {
        $lignes = array_map(fn ($ligne) => [
            $ligne['date_heure']->format('d/m/Y H:i'),
            $ligne['numero_piece'],
            $ligne['type'] === 'depot' ? __('caisse.depot') : __('caisse.retrait'),
            $ligne['client'],
            $ligne['entree'] ?: null,
            $ligne['sortie'] ?: null,
            $ligne['solde'],
            $ligne['commission'],
            $ligne['observation'],
        ], $this->journal['lignes']);

        $lignes[] = [
            __('caisse.colonne_total'), null, null, null,
            $this->journal['total_entrees'],
            $this->journal['total_sorties'],
            $this->journal['solde_net'],
            null, null,
        ];

        return $lignes;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        $derniereLigne = $sheet->getHighestRow();
        $sheet->getStyle($derniereLigne)->getFont()->setBold(true);

        return [];
    }
}
