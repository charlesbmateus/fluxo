<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 300);
        $fee = round($subtotal * 0.1, 2);
        $tax = round($subtotal * 0.077, 2);

        return [
            'number'       => 'INV-' . now()->year . '-' . Str::random(6),
            'user_id'      => User::factory()->clientRole(),
            'provider_id'  => User::factory()->providerRole(),
            'booking_id'   => Booking::factory(),
            'subtotal'     => $subtotal,
            'fee'          => $fee,
            'tax'          => $tax,
            'total'        => $subtotal + $fee + $tax,
            'currency'     => 'CHF',
            'status'       => InvoiceStatus::ISSUED,
            'issued_at'    => now(),
        ];
    }
}
