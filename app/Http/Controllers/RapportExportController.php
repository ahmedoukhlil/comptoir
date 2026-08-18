<?php

namespace App\Http\Controllers;

use App\Exports\RapportRentabiliteExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RapportExportController extends Controller
{
    public function excel(Request $request)
    {
        $tenant = Auth::user()->tenant;
        [$debut, $fin] = $this->bornes($request->string('periode')->toString());

        $nomFichier = 'rapport-rentabilite-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new RapportRentabiliteExport($tenant, $debut, $fin), $nomFichier);
    }

    public function pdf(Request $request)
    {
        $tenant = Auth::user()->tenant;
        [$debut, $fin] = $this->bornes($request->string('periode')->toString());

        $points = $tenant->points()->orderBy('nom')->get()->map(function ($point) use ($debut, $fin) {
            $capital = (int) $point->alimentations()->whereBetween('date', [$debut, $fin])->sum('montant');
            $commissions = (int) $point->operations()->whereBetween('created_at', [$debut, $fin])->sum('commission_calculee');

            return ['point' => $point, 'capital' => $capital, 'commissions' => $commissions];
        });

        $pdf = Pdf::loadView('exports.rapport-pdf', [
            'tenant' => $tenant,
            'points' => $points,
            'totalCapital' => $points->sum('capital'),
            'totalCommissions' => $points->sum('commissions'),
        ])->setPaper('a4', 'portrait');

        $nomFichier = 'rapport-rentabilite-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($nomFichier);
    }

    private function bornes(string $periode): array
    {
        return match ($periode) {
            'semaine' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'mois' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };
    }
}
