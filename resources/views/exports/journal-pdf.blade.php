<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #123A66; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.sous-titre { color: #5C7CA3; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #D7E3F0; padding: 5px 7px; text-align: left; }
        th { background: #123A66; color: #FBFCFF; font-size: 9px; text-transform: uppercase; }
        tr:nth-child(even) { background: #F1F6FC; }
        .entree { color: #1F5138; font-weight: bold; }
        .sortie { color: #8A3620; font-weight: bold; }
        tfoot td { font-weight: bold; background: #E4EDF8; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ __('caisse.historique_titre') }} — {{ $point->nom }}</h1>
    <p class="sous-titre">{{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('caisse.colonne_date_heure') }}</th>
                <th>{{ __('caisse.colonne_numero_piece') }}</th>
                <th>{{ __('caisse.colonne_type') }}</th>
                <th>{{ __('caisse.colonne_client') }}</th>
                <th class="num">{{ __('caisse.colonne_entrees') }}</th>
                <th class="num">{{ __('caisse.colonne_sorties') }}</th>
                <th class="num">{{ __('caisse.colonne_solde') }}</th>
                <th class="num">{{ __('caisse.colonne_commission') }}</th>
                <th>{{ __('caisse.colonne_observation') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($journal['lignes'] as $ligne)
                <tr>
                    <td>{{ $ligne['date_heure']->format('d/m/Y H:i') }}</td>
                    <td>{{ $ligne['numero_piece'] }}</td>
                    <td>{{ $ligne['type'] === 'depot' ? __('caisse.recu') : __('caisse.donne') }}</td>
                    <td>{{ $ligne['client'] }}</td>
                    <td class="num entree">{{ $ligne['entree'] ? number_format($ligne['entree'], 0, ',', ' ') : '' }}</td>
                    <td class="num sortie">{{ $ligne['sortie'] ? number_format($ligne['sortie'], 0, ',', ' ') : '' }}</td>
                    <td class="num">{{ number_format($ligne['solde'], 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($ligne['commission'], 0, ',', ' ') }}</td>
                    <td>{{ $ligne['observation'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">{{ __('caisse.colonne_total') }}</td>
                <td class="num entree">{{ number_format($journal['total_entrees'], 0, ',', ' ') }}</td>
                <td class="num sortie">{{ number_format($journal['total_sorties'], 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($journal['solde_net'], 0, ',', ' ') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
