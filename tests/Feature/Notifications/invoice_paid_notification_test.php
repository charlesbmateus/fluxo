<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('a notification is created when an invoice is paid', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => InvoiceStatus::ISSUED,
    ]);

    Sanctum::actingAs($client);

    $response = $this->patchJson("/api/invoices/{$invoice->id}/pay");
    $response->assertStatus(200);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $provider->id,
        'type'    => 'invoice_paid',
    ]);
});
