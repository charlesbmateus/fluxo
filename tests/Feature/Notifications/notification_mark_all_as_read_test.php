<?php

use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a user can mark all their notifications as read', function () {
    // 1️⃣ Arrange
    $user = User::factory()->create();

    Notification::factory()->count(3)->create([
        'user_id' => $user->id,
        'read'    => false,
    ]);

    Sanctum::actingAs($user);

    // 2️⃣ Act
    $response = $this->patchJson('/api/notifications/read-all');

    // 3️⃣ Assert
    $response->assertStatus(200);

    expect(
        Notification::where('user_id', $user->id)->where('read', false)->count()
    )->toBe(0);
});
