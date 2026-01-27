<?php

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a client cannot confirm a booking', function () {
    $provider = User::factory()->providerRole()->create();
    $client   = User::factory()->clientRole()->create();

    $service = Service::factory()->create([
        'user_id' => $provider->id,
    ]);

    $booking = Booking::factory()->create([
        'service_id' => $service->id,
        'user_id'    => $client->id,
        'status'     => 'pending',
    ]);

    // ❗ Client tries to confirm (NOT allowed)
    Sanctum::actingAs($client);

    $response = $this->patchJson("/api/bookings/{$booking->id}/status", [
        'status' => 'confirmed',
    ]);

    $response->assertStatus(422);

    $this->assertDatabaseHas('bookings', [
        'id'     => $booking->id,
        'status' => 'pending',
    ]);
});
