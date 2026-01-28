<?php

use App\Models\User;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Invoice;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an issued invoice can be paid', function () {
    // 1️⃣ Arrange
    $client = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $service = Service::factory()->create([
        'user_id' => $provider->id,
        'price'   => 120,
    ]);

    $booking = Booking::factory()->create([
        'user_id'    => $client->id,
        'service_id' => $service->id,
        'status'     => BookingStatus::COMPLETED,
        'price'      => 120,
    ]);

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'booking_id'  => $booking->id,
        'subtotal'    => 120,
        'fee'         => 12,
        'tax'         => 0,
        'total'       => 132,
        'status'      => InvoiceStatus::ISSUED,
        'issued_at'   => now(),
    ]);

    // 2️⃣ Act
    $invoice->markAsPaid();

    // 3️⃣ Assert
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::PAID);
    expect($invoice->paid_at)->not->toBeNull();

    $this->assertDatabaseHas('invoices', [
        'id'     => $invoice->id,
        'status' => 'paid',
    ]);
});
