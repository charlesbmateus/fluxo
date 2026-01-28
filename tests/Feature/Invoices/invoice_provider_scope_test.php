<?php

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a provider sees only their invoices', function () {
    $providerA = User::factory()->providerRole()->create();
    $providerB = User::factory()->providerRole()->create();

    Invoice::factory()->count(3)->create([
        'provider_id' => $providerA->id,
    ]);

    Invoice::factory()->count(2)->create([
        'provider_id' => $providerB->id,
    ]);

    $results = Invoice::forProvider($providerA->id)->get();

    expect($results)->toHaveCount(3);
    expect($results->pluck('provider_id')->unique()->first())
        ->toBe($providerA->id);
});
