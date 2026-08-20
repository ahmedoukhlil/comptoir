<?php

namespace Database\Factories;

use App\Models\Operateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Operateur>
 */
class OperateurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->company(),
            'bareme_depot' => ['tranches' => []],
            'bareme_retrait_client' => ['tranches' => []],
            'bareme_retrait_beneficiaire' => ['tranches' => []],
            'est_cash' => false,
            'actif' => true,
            'pourcentage_partage_point_depot' => 50,
            'pourcentage_partage_point_retrait_client' => 50,
            'pourcentage_partage_point_retrait_beneficiaire' => 50,
            'commission_versee_dans_solde' => false,
        ];
    }
}
