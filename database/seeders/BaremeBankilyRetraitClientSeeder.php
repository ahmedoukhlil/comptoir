<?php

namespace Database\Seeders;

use App\Models\Operateur;
use Illuminate\Database\Seeder;

/**
 * Grille tarifaire réelle "Retrait Espèce Client" de Bankily : met à jour
 * le barème de retrait client de l'opérateur global Bankily (partagé par
 * tous les tenants). Le dépôt reste gratuit et le retrait bénéficiaire
 * n'est pas concerné par ce barème.
 *
 * Exécution : php artisan db:seed --class=BaremeBankilyRetraitClientSeeder
 */
class BaremeBankilyRetraitClientSeeder extends Seeder
{
    public function run(): void
    {
        $bareme = [
            'tranches' => [
                ['min' => 0, 'max' => 500, 'frais' => 6],
                ['min' => 501, 'max' => 1000, 'frais' => 9],
                ['min' => 1001, 'max' => 2000, 'frais' => 12],
                ['min' => 2001, 'max' => 3000, 'frais' => 15],
                ['min' => 3001, 'max' => 5000, 'frais' => 18],
                ['min' => 5001, 'max' => 7000, 'frais' => 21],
                ['min' => 7001, 'max' => 10000, 'frais' => 24],
                ['min' => 10001, 'max' => 15000, 'frais' => 27],
                ['min' => 15001, 'max' => 20000, 'frais' => 30],
                ['min' => 20001, 'max' => 30000, 'frais' => 54],
                ['min' => 30001, 'max' => 40000, 'frais' => 66],
                ['min' => 40001, 'max' => 50000, 'frais' => 84],
                ['min' => 50001, 'max' => 60000, 'frais' => 96],
                ['min' => 60001, 'max' => 70000, 'frais' => 114],
                ['min' => 70001, 'max' => 80000, 'frais' => 126],
                ['min' => 80001, 'max' => 90000, 'frais' => 144],
                ['min' => 90001, 'max' => 100000, 'frais' => 165],
                ['min' => 100001, 'max' => 130000, 'frais' => 195],
                ['min' => 130001, 'max' => 160000, 'frais' => 240],
                ['min' => 160001, 'max' => 200000, 'frais' => 300],
                ['min' => 200001, 'max' => 230000, 'frais' => 420],
                ['min' => 230001, 'max' => 260000, 'frais' => 540],
                ['min' => 260001, 'max' => 300000, 'frais' => 600],
                ['min' => 300001, 'max' => null, 'frais' => 600],
            ],
        ];

        $nombreMisAJour = Operateur::where('nom', 'Bankily')->update([
            'bareme_retrait_client' => $bareme,
        ]);

        $this->command?->info("Barème Retrait Client Bankily appliqué à {$nombreMisAJour} opérateur(s).");
    }
}
