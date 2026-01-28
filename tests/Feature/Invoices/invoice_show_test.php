<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * CLIENT CAN VIEW THEIR INVOICE
 */
test('a client can view their invoice', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => InvoiceStatus::ISSUED,
    ]);

    Sanctum::actingAs($client);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertOk();
    $response->assertJsonFragment([
        'id' => $invoice->id,
    ]);
});

/**
 * PROVIDER CAN VIEW THEIR INVOICE
 */
test('a provider can view an invoice for their service', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => InvoiceStatus::ISSUED,
    ]);

    Sanctum::actingAs($provider);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertOk();
    $response->assertJsonFragment([
        'id' => $invoice->id,
    ]);
});

/**
 * UNAUTHORIZED USER CANNOT VIEW THE INVOICE
 */
test('a random user cannot view someone else invoice', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();
    $intruder = User::factory()->clientRole()->create();

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => InvoiceStatus::ISSUED,
    ]);

    Sanctum::actingAs($intruder);

    $response = $this->getJson("/api/invoices/{$invoice->id}");

    $response->assertStatus(403);
});
