<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('an invoice can go through full lifecycle: draft → issued → paid', function () {
    // 1️⃣ Arrange
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => InvoiceStatus::DRAFT,
    ]);

    // 2️⃣ Provider issues the invoice
    Sanctum::actingAs($provider);

    $issueResponse = $this->patchJson("/api/invoices/{$invoice->id}/issue");

    $issueResponse->assertStatus(200);

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::ISSUED);
    expect($invoice->issued_at)->not->toBeNull();

    // 3️⃣ Client pays the invoice
    Sanctum::actingAs($client);

    $payResponse = $this->patchJson("/api/invoices/{$invoice->id}/pay");

    $payResponse->assertStatus(200);

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::PAID);
    expect($invoice->paid_at)->not->toBeNull();

    // 4️⃣ Database assertion
    $this->assertDatabaseHas('invoices', [
        'id'     => $invoice->id,
        'status' => InvoiceStatus::PAID->value,
    ]);
});
