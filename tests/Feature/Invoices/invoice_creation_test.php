<?php

use App\Enums\InvoiceStatus;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an invoice can be created for a booking', function () {
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $service = Service::factory()->create([
        'user_id' => $provider->id,
        'price'   => 100,
    ]);

    $booking = Booking::factory()->create([
        'user_id'    => $client->id,
        'service_id' => $service->id,
        'price'      => 100,
    ]);

    $invoice = Invoice::create([
        'number'      => 'INV-TEST-001',
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'booking_id'  => $booking->id,
        'subtotal'    => 100,
        'fee'         => 10,
        'tax'         => 7.7,
        'total'       => 117.7,
        'currency'    => 'CHF',
        'status'      => InvoiceStatus::ISSUED,
        'issued_at'   => now(),
    ]);

    expect($invoice->isIssued())->toBeTrue();
});
