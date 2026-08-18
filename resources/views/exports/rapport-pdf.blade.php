<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #123A66; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.sous-titre { color: #5C7CA3; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #D7E3F0; padding: 6px 8px; text-align: left; }
        th { background: #123A66; color: #FBFCFF; font-size: 10px; text-transform: uppercase; }
        tr:nth-child(even) { background: #F1F6FC; }
        .num { text-align: right; }
        .commission { color: #1F5138; font-weight: bold; }
        tfoot td { font-weight: bold; background: #E4EDF8; }
    </style>
</head>
<body>
    <h1>{{ __('caisse.rapport_titre') }} — {{ $tenant->nom }}</h1>
    <p class="sous-titre">{{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('caisse.dashboard_par_point') }}</th>
                <th class="num">{{ __('caisse.rapport_capital_injecte') }}</th>
                <th class="num">{{ __('caisse.rapport_commissions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($points as $ligne)
                <tr>
                    <td>{{ $ligne['point']->nom }}</td>
                    <td class="num">{{ number_format($ligne['capital'], 0, ',', ' ') }}</td>
                    <td class="num commission">{{ number_format($ligne['commissions'], 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>{{ __('caisse.colonne_total') }}</td>
                <td class="num">{{ number_format($totalCapital, 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($totalCommissions, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
