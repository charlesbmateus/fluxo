<?php

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('provider net earnings are calculated correctly', function () {
    $invoice = Invoice::factory()->create([
        'subtotal' => 100,
        'fee' => 10,
    ]);

    expect($invoice->providerNetAmount())->toBe(90.0);
});
