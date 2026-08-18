<?php

namespace App\Exports;

use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RapportRentabiliteExport implements FromArray, WithHeadings, WithStyles
{
    public function __construct(
        private Tenant $tenant,
        private Carbon $debut,
        private Carbon $fin,
    ) {}

    public function headings(): array
    {
        return [
            __('caisse.dashboard_par_point'),
            __('caisse.rapport_capital_injecte'),
            __('caisse.rapport_commissions'),
        ];
    }

    public function array(): array
    {
        $points = $this->tenant->points()->orderBy('nom')->get();

        $lignes = $points->map(function ($point) {
            $capital = (int) $point->alimentations()->whereBetween('date', [$this->debut, $this->fin])->sum('montant');
            $commissions = (int) $point->operations()->whereBetween('created_at', [$this->debut, $this->fin])->sum('commission_calculee');

            return [$point->nom, $capital, $commissions];
        })->all();

        $lignes[] = [
            __('caisse.colonne_total'),
            array_sum(array_column($lignes, 1)),
            array_sum(array_column($lignes, 2)),
        ];

        return $lignes;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle($sheet->getHighestRow())->getFont()->setBold(true);

        return [];
    }
}
