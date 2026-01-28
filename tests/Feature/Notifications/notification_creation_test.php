<?php

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a notification can be created for a user', function () {
    $user = User::factory()->create();

    $service = app(NotificationService::class);

    $notification = $service->notify(
        $user,
        'invoice_paid',
        'Your invoice has been paid'
    );

    expect($notification)->not->toBeNull();
    expect($notification->user_id)->toBe($user->id);
    expect($notification->type)->toBe('invoice_paid');

    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'type'    => 'invoice_paid',
    ]);
});
