<?php

use App\Models\User;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a service cannot be double booked at the same time', function () {
    // Client
    $client = User::factory()->clientRole()->create();

    // Provider + Service
    $service = Service::factory()->create();

    // Service availability (covers the booking time)
    ServiceAvailability::factory()->create([
        'service_id'  => $service->id,
        'day_of_week' => now()->addDays(2)->dayOfWeek,
        'start_time'  => '09:00:00',
        'end_time'    => '18:00:00',
    ]);

    // Existing booking (10:00 → 11:00)
    $start = now()->addDays(2)->setTime(10, 0);
    $end   = (clone $start)->addHour();

    Booking::factory()->create([
        'service_id'     => $service->id,
        'user_id'        => $client->id,
        'start_datetime' => $start,
        'end_datetime'   => $end,
        'status'         => 'confirmed',
        'price'          => $service->price,
    ]);

    Sanctum::actingAs($client);

    // Try to book the SAME time again
    $response = $this->postJson('/api/bookings', [
        'service_id'     => $service->id,
        'start_datetime' => $start->format('Y-m-d H:i:s'),
        'end_datetime'   => $end->format('Y-m-d H:i:s'),
    ]);

    // ❌ Must be rejected
    $response->assertStatus(422);

    $response->assertJsonValidationErrors([
        'start_datetime',
    ]);
});
