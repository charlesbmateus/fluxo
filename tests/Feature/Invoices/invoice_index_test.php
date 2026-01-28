<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * CLIENT VIEW
 */
test('a client can see only their invoices', function () {
    // Arrange
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $myInvoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => InvoiceStatus::ISSUED,
    ]);

    $otherInvoice = Invoice::factory()->create();

    Sanctum::actingAs($client);

    // Act
    $response = $this->getJson('/api/invoices');

    // Assert
    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'id' => $myInvoice->id,
    ]);
});

/**
 * PROVIDER VIEW
 */
test('a provider can see only invoices for their services', function () {
    // Arrange
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $myInvoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => InvoiceStatus::ISSUED,
    ]);

    $otherInvoice = Invoice::factory()->create();

    Sanctum::actingAs($provider);

    // Act
    $response = $this->getJson('/api/invoices');

    // Assert
    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment([
        'id' => $myInvoice->id,
    ]);
});
