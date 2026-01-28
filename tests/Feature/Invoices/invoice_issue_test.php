<?php

use App\Models\User;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Invoice;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a draft invoice can be issued', function () {
    // 1️⃣ Arrange
    $client = User::factory()->clientRole()->create();
    $provider = User::factory()->providerRole()->create();

    $service = Service::factory()->create([
        'user_id' => $provider->id,
        'price'   => 100,
    ]);

    $booking = Booking::factory()->create([
        'user_id'    => $client->id,
        'service_id' => $service->id,
        'status'     => BookingStatus::COMPLETED,
        'price'      => 100,
    ]);

    $invoice = Invoice::factory()->create([
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'booking_id'  => $booking->id,
        'status'      => InvoiceStatus::DRAFT,
    ]);

    // 2️⃣ Act
    $invoice->markAsIssued();

    // 3️⃣ Assert
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::ISSUED);
    expect($invoice->issued_at)->not->toBeNull();

    $this->assertDatabaseHas('invoices', [
        'id'     => $invoice->id,
        'status' => 'issued',
    ]);
});
