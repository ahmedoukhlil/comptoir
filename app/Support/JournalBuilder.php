<?php

namespace App\Support;

use App\Models\Point;
use Illuminate\Support\Collection;

class JournalBuilder
{
    /**
     * Construit les lignes du journal avec le solde cumulé après chaque
     * opération, dans l'ordre chronologique (le plus ancien en premier).
     */
    public static function construire(Point $point, Collection $operations): array
    {
        $soldeInitial = (int) $point->alimentations()->sum('montant');

        $operationsTriees = $operations->sortBy('created_at')->values();

        $solde = $soldeInitial;
        $lignes = [];
        $totalEntrees = 0;
        $totalSorties = 0;

        foreach ($operationsTriees as $operation) {
            $entree = $operation->type === 'depot' ? $operation->montant : 0;
            $sortie = $operation->type === 'retrait' ? $operation->montant : 0;
            $solde += $entree - $sortie;
            $totalEntrees += $entree;
            $totalSorties += $sortie;

            $client = trim(implode(' — ', array_filter([
                $operation->client_nom,
                $operation->client_telephone,
                $operation->client_nni ? 'NNI: '.self::masquerNni($operation->client_nni) : null,
            ])));

            $lignes[] = [
                'date_heure' => $operation->created_at,
                'numero_piece' => $operation->numero_piece,
                'type' => $operation->type,
                'client' => $client,
                'entree' => $entree,
                'sortie' => $sortie,
                'solde' => $solde,
                'commission' => $operation->commission_calculee,
                'commission_part_point' => $operation->commission_part_point,
                'commission_part_banque' => $operation->commission_part_banque,
                'observation' => $operation->observation,
            ];
        }

        return [
            'lignes' => array_reverse($lignes),
            'total_entrees' => $totalEntrees,
            'total_sorties' => $totalSorties,
            'solde_net' => $totalEntrees - $totalSorties,
        ];
    }

    private static function masquerNni(string $nni): string
    {
        $visible = substr($nni, -3);
        $masque = str_repeat('*', max(0, strlen($nni) - 3));

        return $masque.$visible;
    }
}
