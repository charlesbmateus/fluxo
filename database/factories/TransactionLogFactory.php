<?php

namespace Database\Factories;

use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionLog>
 */
class TransactionLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'action'   => $this->faker->word(),
            'amount'   => $this->faker->randomFloat(2, -50, 500),
            'metadata' => ['ip' => $this->faker->ipv4()],
        ];
    }
}
