<?php

namespace App\Services;

use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentIntent(
        int $amountInCents,
        string $currency = 'chf',
        array $metadata = []
    ): PaymentIntent {
        return PaymentIntent::create([
            'amount'   => $amountInCents,
            'currency' => $currency,
            'metadata' => $metadata,
        ]);
    }
}
