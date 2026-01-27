<?php

use App\Models\User;
use App\Models\Service;
use App\Models\ServiceAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a client can create a booking', function () {
    $client = User::factory()->clientRole()->create();

    $service = Service::factory()->create();

    // Service availability (required)
    ServiceAvailability::factory()->create([
        'service_id'  => $service->id,
        'day_of_week' => now()->addDays(2)->dayOfWeek,
        'start_time'  => '09:00:00',
        'end_time'    => '18:00:00',
        'is_active'   => true,
    ]);

    Sanctum::actingAs($client);

    $start = now()->addDays(2)->setTime(10, 0);

    $response = $this->postJson('/api/bookings', [
        'service_id'     => $service->id,
        'start_datetime' => $start->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('bookings', [
        'user_id'        => $client->id,
        'service_id'     => $service->id,
        'start_datetime'=> $start->format('Y-m-d H:i:s'),
    ]);
});
