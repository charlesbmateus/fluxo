<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('only the client can cancel an invoice', function () {
    // Arrange
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();
    $intruder = User::factory()->clientRole()->create();

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => InvoiceStatus::ISSUED,
    ]);

    // Act: intruder tries to cancel
    Sanctum::actingAs($intruder);

    $response = $this->patchJson("/api/invoices/{$invoice->id}/cancel");

    // Assert
    $response->assertStatus(403);

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::ISSUED);
});
