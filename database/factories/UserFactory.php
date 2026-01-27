<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => Role::CLIENT, // default
            'avatar' => null,
        ];
    }

    /* ─────────────────────────────
     | STATES
     ───────────────────────────── */

    public function providerRole(): static
    {
        return $this->state(fn () => [
            'role' => Role::PROVIDER,
        ]);
    }

    public function clientRole(): static
    {
        return $this->state(fn () => [
            'role' => Role::CLIENT,
        ]);
    }

    public function adminRole(): static
    {
        return $this->state(fn () => [
            'role' => Role::ADMIN,
        ]);
    }
}
