<?php

namespace App\Http\Controllers;

use App\Models\Operateur;
use App\Models\Operation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SyncController extends Controller
{
    public function synchroniser(Request $request): JsonResponse
    {
        $tenantId = Auth::user()->point->tenant_id;

        $data = $request->validate([
            'operations' => ['required', 'array'],
            'operations.*.uuid_client' => ['required', 'uuid'],
            'operations.*.operateur_id' => ['required', 'integer', Rule::exists('operateurs', 'id')->where('tenant_id', $tenantId)],
            'operations.*.type' => ['required', 'in:depot,retrait'],
            'operations.*.montant' => ['required', 'integer', 'min:1'],
            'operations.*.client_nom' => ['nullable', 'string'],
            'operations.*.client_telephone' => ['required', 'digits:8'],
            'operations.*.client_nni' => ['nullable', 'string'],
            'operations.*.cree_le' => ['required', 'date'],
        ]);

        $agent = Auth::user();
        $resultats = [];

        foreach ($data['operations'] as $entree) {
            $resultats[] = DB::transaction(function () use ($entree, $agent) {
                $existante = Operation::where('uuid_client', $entree['uuid_client'])->first();

                if ($existante) {
                    return [
                        'uuid_client' => $entree['uuid_client'],
                        'numero_piece' => $existante->numero_piece,
                        'statut' => 'deja_synchronisee',
                    ];
                }

                $operateur = Operateur::findOrFail($entree['operateur_id']);
                $montant = (int) $entree['montant'];
                $repartition = $operateur->repartirCommission($montant);

                $operation = Operation::create([
                    'numero_piece' => Operation::genererNumeroPiece(),
                    'uuid_client' => $entree['uuid_client'],
                    'point_id' => $agent->point_id,
                    'agent_id' => $agent->id,
                    'operateur_id' => $operateur->id,
                    'type' => $entree['type'],
                    'montant' => $montant,
                    'commission_calculee' => $repartition['frais'],
                    'commission_part_point' => $repartition['part_point'],
                    'commission_part_banque' => $repartition['part_banque'],
                    'client_nom' => $entree['client_nom'] ?? null,
                    'client_telephone' => $entree['client_telephone'],
                    'client_nni' => $entree['client_nni'] ?? null,
                    'synced' => true,
                    'created_at' => $entree['cree_le'],
                ]);

                return [
                    'uuid_client' => $entree['uuid_client'],
                    'numero_piece' => $operation->numero_piece,
                    'statut' => 'synchronisee',
                ];
            });
        }

        return response()->json(['resultats' => $resultats]);
    }
}
