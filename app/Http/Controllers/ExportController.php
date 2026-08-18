<?php

namespace App\Http\Controllers;

use App\Exports\JournalExport;
use App\Support\JournalBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function excel(Request $request)
    {
        $point = Auth::user()->point;
        $operations = $this->operationsFiltrees($point, $request);

        $nomFichier = 'journal-'.$point->id.'-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new JournalExport($point, $operations), $nomFichier);
    }

    public function pdf(Request $request)
    {
        $point = Auth::user()->point;
        $operations = $this->operationsFiltrees($point, $request);
        $journal = JournalBuilder::construire($point, $operations);

        $pdf = Pdf::loadView('exports.journal-pdf', [
            'point' => $point,
            'journal' => $journal,
        ])->setPaper('a4', 'landscape');

        $nomFichier = 'journal-'.$point->id.'-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($nomFichier);
    }

    private function operationsFiltrees($point, Request $request)
    {
        return $point->operations()
            ->with('operateur')
            ->when($request->integer('operateurId'), fn ($q, $v) => $q->where('operateur_id', $v))
            ->when($request->string('type')->toString(), fn ($q, $v) => $q->where('type', $v))
            ->when($request->string('recherche')->toString(), fn ($q, $v) => $q->where('client_telephone', 'like', '%'.$v.'%'))
            ->get();
    }
}
