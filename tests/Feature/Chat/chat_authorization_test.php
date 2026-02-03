<?php

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('non participants cannot view a conversation', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();
    $intruder = User::factory()->create();

    $conversation = Conversation::factory()->create([
        'client_id'   => $client->id,
        'provider_id' => $provider->id,
    ]);

    Sanctum::actingAs($intruder);

    $this->getJson("/api/v1/conversations/{$conversation->id}")
        ->assertStatus(403);
});
