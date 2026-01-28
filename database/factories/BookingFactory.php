<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory()->clientRole(),
            'service_id' => Service::factory(),
            'start_datetime' => now()->addDays(1),
            'end_datetime'   => now()->addDays(1)->addHour(),
            'status' => BookingStatus::PENDING,
            'price'  => 100,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::COMPLETED,
        ]);
    }
}
