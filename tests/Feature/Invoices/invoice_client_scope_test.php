<?php

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a client sees only their invoices', function () {
    $client = User::factory()->clientRole()->create();
    $other  = User::factory()->clientRole()->create();

    Invoice::factory()->count(2)->create([
        'user_id' => $client->id,
    ]);

    Invoice::factory()->count(1)->create([
        'user_id' => $other->id,
    ]);

    $invoices = Invoice::forClient($client->id)->get();

    expect($invoices)->toHaveCount(2);
    expect($invoices->pluck('user_id')->unique()->first())
        ->toBe($client->id);
});
