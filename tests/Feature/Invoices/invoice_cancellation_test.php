<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an issued invoice can be cancelled', function () {
    // Arrange
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatus::ISSUED,
    ]);

    // Act
    $invoice->cancel();

    // Assert
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::CANCELLED);
    expect($invoice->paid_at)->toBeNull();
});

test('a paid invoice cannot be cancelled', function () {
    // Arrange
    $invoice = Invoice::factory()->create([
        'status'  => InvoiceStatus::PAID,
        'paid_at'=> now(),
    ]);

    // Act
    $invoice->cancel();

    // Assert
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::PAID);
    expect($invoice->paid_at)->not->toBeNull();
});
