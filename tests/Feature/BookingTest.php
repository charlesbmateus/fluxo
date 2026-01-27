<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_can_create_a_booking()
    {
        $client   = User::factory()->client()->create();
        $provider = User::factory()->provider()->create();

        $service = Service::factory()->create([
            'user_id' => $provider->id,
        ]);

        ServiceAvailability::factory()->create([
            'service_id'  => $service->id,
            'day_of_week' => now()->dayOfWeek,
            'start_time'  => '08:00',
            'end_time'    => '18:00',
        ]);

        $response = $this
            ->actingAs($client, 'sanctum')
            ->postJson('/api/bookings', [
                'service_id'   => $service->id,
                'scheduled_at' => now()->addDay()->setHour(10),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'user_id'    => $client->id,
            'service_id' => $service->id,
            'status'     => BookingStatus::PENDING->value,
        ]);
    }

    /** @test */
    public function client_cannot_book_unavailable_service()
    {
        $client   = User::factory()->client()->create();
        $provider = User::factory()->provider()->create();

        $service = Service::factory()->create([
            'user_id' => $provider->id,
        ]);

        // No availability created ❌

        $response = $this
            ->actingAs($client, 'sanctum')
            ->postJson('/api/bookings', [
                'service_id'   => $service->id,
                'scheduled_at' => now()->addDay()->setHour(10),
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function provider_can_confirm_booking()
    {
        $client   = User::factory()->client()->create();
        $provider = User::factory()->provider()->create();

        $service = Service::factory()->create([
            'user_id' => $provider->id,
        ]);

        $booking = Booking::factory()->create([
            'user_id'    => $client->id,
            'service_id' => $service->id,
            'status'     => BookingStatus::PENDING,
        ]);

        $response = $this
            ->actingAs($provider, 'sanctum')
            ->patchJson("/api/bookings/{$booking->id}/status", [
                'status' => BookingStatus::CONFIRMED->value,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('bookings', [
            'id'     => $booking->id,
            'status' => BookingStatus::CONFIRMED->value,
        ]);
    }
}
