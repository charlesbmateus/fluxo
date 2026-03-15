<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // 🎯 Payment succeeded
        if ($event->type === 'payment_intent.succeeded') {

            $intent = $event->data->object;

            $booking = Booking::where(
                'stripe_payment_intent_id',
                $intent->id
            )->first();

            if ($booking && $booking->status !== 'paid') {

                $booking->update([
                    'status' => 'paid',
                ]);
            }
        }

        return response('Webhook handled', 200);
    }
}
