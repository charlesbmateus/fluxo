<?php

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a user can mark messages as read', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $conversation = Conversation::factory()->create([
        'client_id'   => $client->id,
        'provider_id' => $provider->id,
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'sender_id'       => $provider->id,
        'read_at'         => null,
    ]);

    Sanctum::actingAs($client);

    $this->patchJson("/api/v1/conversations/{$conversation->id}/read")
        ->assertStatus(200);

    $this->assertDatabaseMissing('messages', [
        'conversation_id' => $conversation->id,
        'read_at'         => null,
    ]);
});
