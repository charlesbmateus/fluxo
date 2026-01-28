<?php

use App\Enums\BookingStatus;
use App\Models\User;
use App\Models\Service;
use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an invoice can be created from a completed booking', function () {
    // 1️⃣ Arrange
    $client   = User::factory()->clientRole()->create();
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

    $invoiceService = app(InvoiceService::class);

    // 2️⃣ Act
    $invoice = $invoiceService->createFromBooking($booking);

    // 3️⃣ Assert (modelo)
    expect($invoice)->not->toBeNull();
    expect($invoice->booking_id)->toBe($booking->id);
    expect($invoice->user_id)->toBe($client->id);
    expect($invoice->provider_id)->toBe($provider->id);
    expect($invoice->status->value)->toBe('draft');

    // 4️⃣ Assert (base de datos)
    $this->assertDatabaseHas('invoices', [
        'booking_id'  => $booking->id,
        'user_id'     => $client->id,
        'provider_id' => $provider->id,
        'status'      => 'draft',
    ]);
});
