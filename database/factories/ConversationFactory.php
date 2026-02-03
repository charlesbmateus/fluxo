<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        $client = User::factory()->clientRole()->create();
        $provider = User::factory()->providerRole()->create();

        return [
            'client_id'   => $client->id,
            'provider_id' => $provider->id,
            'booking_id'  => null, // opcional
        ];
    }

    /**
     * Attach conversation to a booking
     */
    public function forBooking(Booking $booking): static
    {
        return $this->state(fn () => [
            'booking_id'  => $booking->id,
            'client_id'   => $booking->user_id,
            'provider_id' => $booking->service->user_id,
        ]);
    }
}
