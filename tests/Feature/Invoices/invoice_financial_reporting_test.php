<?php

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('total paid invoices can be calculated', function () {
    Invoice::factory()->create([
        'status' => 'paid',
        'total'  => 100,
    ]);

    Invoice::factory()->create([
        'status' => 'paid',
        'total'  => 50,
    ]);

    Invoice::factory()->create([
        'status' => 'draft',
        'total'  => 999,
    ]);

    $sum = Invoice::paid()->sum('total');

    expect($sum)->toEqual(150.0);
});
