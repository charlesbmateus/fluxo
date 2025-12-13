<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'user_id'  => User::factory()->clientRole(),
            'rating'     => $this->faker->numberBetween(1, 5),
            'comment'    => $this->faker->sentence(10),
        ];
    }
}
