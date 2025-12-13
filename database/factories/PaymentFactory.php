<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id'     => Booking::factory(),
            'amount'          => $this->faker->randomFloat(2, 50, 300),
            'method'          => $this->faker->randomElement(Payment::cases())->value,
            'status'          => PaymentStatus::PENDING->value,
            'transaction_id'  => $this->faker->uuid(),
            'paid_at'         => null,
        ];
    }
}
