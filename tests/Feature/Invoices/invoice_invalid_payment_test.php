<?php

use App\Models\User;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Invoice;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a draft invoice cannot be paid', function () {
    // 1️⃣ Arrange
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $service = Service::factory()->create([
        'user_id' => $provider->id,
        'price'   => 80,
    ]);

    $booking = Booking::factory()->create([
        'user_id'    => $client->id,
        'service_id' => $service->id,
        'status'     => BookingStatus::COMPLETED,
        'price'      => 80,
    ]);

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'booking_id'  => $booking->id,
        'subtotal'    => 80,
        'fee'         => 8,
        'tax'         => 0,
        'total'       => 88,
        'status'      => InvoiceStatus::DRAFT,
    ]);

    // 2️⃣ Act
    $invoice->markAsPaid(); // should do NOTHING

    // 3️⃣ Assert
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::DRAFT);
    expect($invoice->paid_at)->toBeNull();

    $this->assertDatabaseHas('invoices', [
        'id'     => $invoice->id,
        'status' => 'draft',
    ]);
});

test('a cancelled invoice cannot be paid', function () {
    // 1️⃣ Arrange
    $client   = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $service = Service::factory()->create([
        'user_id' => $provider->id,
        'price'   => 60,
    ]);

    $booking = Booking::factory()->create([
        'user_id'    => $client->id,
        'service_id' => $service->id,
        'status'     => BookingStatus::COMPLETED,
        'price'      => 60,
    ]);

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'booking_id'  => $booking->id,
        'subtotal'    => 60,
        'fee'         => 6,
        'tax'         => 0,
        'total'       => 66,
        'status'      => InvoiceStatus::CANCELLED,
    ]);

    // 2️⃣ Act
    $invoice->markAsPaid(); // should do NOTHING

    // 3️⃣ Assert
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::CANCELLED);
    expect($invoice->paid_at)->toBeNull();

    $this->assertDatabaseHas('invoices', [
        'id'     => $invoice->id,
        'status' => 'cancelled',
    ]);
});
