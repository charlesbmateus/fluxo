<?php

use App\Models\User;
use App\Models\Service;
use App\Models\Booking;
use App\Enums\BookingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a client can create a payment for a confirmed booking', function () {
    $client = User::factory()->clientRole()->create();
    $service = Service::factory()->create();

    $booking = Booking::factory()->create([
        'user_id'    => $client->id,
        'service_id' => $service->id,
        'status'     => BookingStatus::CONFIRMED,
        'price'      => 120,
    ]);

    $this->assertEquals(
        BookingStatus::CONFIRMED,
        $booking->fresh()->status
    );

    Sanctum::actingAs($client);

    $response = $this->postJson('/api/payments', [
        'booking_id' => $booking->id,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('payments', [
        'user_id'    => $client->id,
        'booking_id' => $booking->id,
        'amount'     => 120,
    ]);
});
