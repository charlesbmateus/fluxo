<?php

use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a user can list their notifications', function () {
    // 1️⃣ Arrange
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Notification::factory()->create([
        'user_id' => $user->id,
        'type'    => 'invoice_paid',
        'message' => 'Invoice paid',
    ]);

    Notification::factory()->create([
        'user_id' => $user->id,
        'type'    => 'booking_confirmed',
        'message' => 'Booking confirmed',
    ]);

    // Noise (should NOT be returned)
    Notification::factory()->create([
        'user_id' => $otherUser->id,
        'type'    => 'invoice_paid',
    ]);

    Sanctum::actingAs($user);

    // 2️⃣ Act
    $response = $this->getJson('/api/notifications');

    // 3️⃣ Assert
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment([
            'type' => 'invoice_paid',
        ])
        ->assertJsonFragment([
            'type' => 'booking_confirmed',
        ]);
});
