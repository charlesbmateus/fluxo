<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceAvailability>
 */
class ServiceAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ServiceAvailability::class;

    public function definition(): array
    {
        // Day of week: 0 (Sunday) - 6 (Saturday)
        $dayOfWeek = $this->faker->numberBetween(0, 6);

        // Start hour between 8:00 and 18:00
        $startHour = $this->faker->numberBetween(8, 18);

        return [
            'service_id' => Service::factory(),
            'day_of_week' => $dayOfWeek,
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time'   => sprintf('%02d:00:00', $startHour + 2),
            'is_active'  => true,
        ];
    }
}
