<?php

namespace Database\Seeders;

use App\Models\Operateur;
use App\Models\Point;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tenant = Tenant::create([
            'nom' => 'Sidi Ahmed — Réseau Capitale',
            'plan' => 'reseau',
            'statut' => 'actif',
        ]);

        $pointCapitale = Point::create([
            'tenant_id' => $tenant->id,
            'nom' => 'Marché Capitale — Kiosque 3',
            'localisation' => 'Nouakchott, Marché Capitale',
        ]);

        $pointSocogim = Point::create([
            'tenant_id' => $tenant->id,
            'nom' => 'Socogim — Kiosque 1',
            'localisation' => 'Nouakchott, Socogim',
        ]);

        User::factory()->proprietaire()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Sidi Ahmed',
            'telephone' => '22334455',
        ]);

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'point_id' => $pointCapitale->id,
            'name' => 'Mohamed Lemine',
            'telephone' => '36112233',
        ]);

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'point_id' => $pointCapitale->id,
            'name' => 'Fatimetou Mint Sidi',
            'telephone' => '41556677',
        ]);

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'point_id' => $pointSocogim->id,
            'name' => 'Abdellahi Ould Salem',
            'telephone' => '22998877',
        ]);

        $bareme = [
            'tranches' => [
                ['min' => 0, 'max' => 500, 'frais' => 9],
                ['min' => 501, 'max' => 1000, 'frais' => 13],
                ['min' => 1001, 'max' => 2000, 'frais' => 17],
            ],
        ];

        Operateur::create(['nom' => 'Bankily', 'bareme_commission' => $bareme, 'pourcentage_partage_point' => 50]);
        Operateur::create(['nom' => 'Masrivi', 'bareme_commission' => $bareme, 'pourcentage_partage_point' => 50]);
        Operateur::create(['nom' => 'Sedad', 'bareme_commission' => $bareme, 'pourcentage_partage_point' => 50]);
        Operateur::create(['nom' => 'Cash', 'bareme_commission' => ['tranches' => []], 'est_cash' => true]);

        // Tenant Solo de démo (essai gratuit en cours), pour tester le
        // verrouillage des écrans multi-points par plan tarifaire.
        $tenantSolo = Tenant::create([
            'nom' => 'Khadija — Kiosque Teyarett',
            'plan' => 'solo',
            'statut' => 'essai',
            'essai_expire_le' => now()->addDays(12),
        ]);

        $pointTeyarett = Point::create([
            'tenant_id' => $tenantSolo->id,
            'nom' => 'Teyarett — Kiosque unique',
            'localisation' => 'Nouakchott, Teyarett',
        ]);

        User::factory()->proprietaire()->create([
            'tenant_id' => $tenantSolo->id,
            'point_id' => $pointTeyarett->id,
            'name' => 'Khadija Mint Ahmed',
            'telephone' => '33112244',
        ]);

        // Compte super-admin de démo pour le back-office Syslog.
        User::factory()->superAdmin()->create([
            'name' => 'Admin Syslog',
            'telephone' => '00000000',
        ]);
    }
}
