<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $service = Service::inRandomOrder()->first();
        $client  = User::where('role', 'client')->inRandomOrder()->first();

        return [
            'user_id'      => $client->id,
            'service_id'   => $service->id,
            'scheduled_at' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            'status'       => $this->faker->randomElement(['pending', 'confirmed', 'completed', 'cancelled']),
            'price'        => $service->price,
            'notes'        => $this->faker->optional()->sentence(),
        ];
    }
}
