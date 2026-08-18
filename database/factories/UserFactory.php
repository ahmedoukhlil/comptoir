<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'telephone' => fake()->unique()->numerify('##2#####'),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'agent',
            'remember_token' => Str::random(10),
        ];
    }

    public function proprietaire(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'proprietaire',
            'point_id' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
            'tenant_id' => null,
            'point_id' => null,
        ]);
    }
}
