<?php

use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a user can mark their notification as read', function () {
    // 1️⃣ Arrange
    $user = User::factory()->create();

    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'read'    => false,
    ]);

    Sanctum::actingAs($user);

    // 2️⃣ Act
    $response = $this->patchJson("/api/notifications/{$notification->id}/read");

    // 3️⃣ Assert
    $response->assertStatus(200);

    $notification->refresh();

    expect($notification->read)->toBeTrue();
});

test('a user cannot mark another users notification as read', function () {
    // 1️⃣ Arrange
    $owner    = User::factory()->create();
    $intruder = User::factory()->create();

    $notification = Notification::factory()->create([
        'user_id' => $owner->id,
        'read'    => false,
    ]);

    Sanctum::actingAs($intruder);

    // 2️⃣ Act
    $response = $this->patchJson("/api/notifications/{$notification->id}/read");

    // 3️⃣ Assert
    $response->assertStatus(403);

    $notification->refresh();

    expect($notification->read)->toBeFalse();
});
