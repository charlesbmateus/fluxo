<?php

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a participant can send a message', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $conversation = Conversation::factory()->create([
        'client_id'   => $client->id,
        'provider_id' => $provider->id,
    ]);

    Sanctum::actingAs($client);

    $response = $this->postJson(
        "/api/v1/conversations/{$conversation->id}/messages",
        ['content' => 'Hello provider']
    );

    $response->assertStatus(201);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'sender_id'       => $client->id,
        'content'         => 'Hello provider',
    ]);
});
