<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Booking;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        Booking::all()->each(function ($booking) {
            Conversation::create([
                'booking_id' => $booking->id,
                'client_id' => $booking->user_id,
                'provider_id' => $booking->service->user_id,
            ]);
        });
    }
}
