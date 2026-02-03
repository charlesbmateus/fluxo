<?php

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a user can list their conversations', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    Conversation::factory()->create([
        'client_id'   => $client->id,
        'provider_id' => $provider->id,
    ]);

    Sanctum::actingAs($client);

    $response = $this->getJson('/api/v1/conversations');

    $response
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');
});
