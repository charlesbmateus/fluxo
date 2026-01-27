<?php

use App\Models\User;
use App\Models\Service;
use App\Models\ServiceAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a client cannot book outside service availability', function () {
    // Arrange
    $client = User::factory()->clientRole()->create();
    $service = Service::factory()->create();

    // Service is available ONLY from 09:00 to 10:00
    ServiceAvailability::factory()->create([
        'service_id'  => $service->id,
        'day_of_week' => now()->addDays(2)->dayOfWeek,
        'start_time'  => '09:00:00',
        'end_time'    => '10:00:00',
    ]);

    Sanctum::actingAs($client);

    // Client tries to book at 11:00 ❌ (outside availability)
    $start = now()->addDays(2)->setTime(11, 0);

    // Act
    $response = $this->postJson('/api/bookings', [
        'service_id'     => $service->id,
        'start_datetime' => $start->format('Y-m-d H:i:s'),
    ]);

    // Assert
    $response->assertStatus(422);

    $response->assertJsonValidationErrors([
        'start_datetime',
    ]);
});
