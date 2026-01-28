<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an issued invoice can be paid', function () {
    // 1️⃣ Arrange
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatus::ISSUED,
        'paid_at' => null,
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
