<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload    = $request->getContent();
        $signature  = $request->header('Stripe-Signature');
        $secret     = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $secret
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    protected function handlePaymentSucceeded($event): void
    {
        $paymentIntent = $event->data->object;

        $invoiceId = $paymentIntent->metadata->invoice_id ?? null;

        if (! $invoiceId) {
            return;
        }

        $invoice = Invoice::find($invoiceId);

        if (! $invoice || $invoice->isPaid()) {
            return;
        }

        $invoice->markAsPaid();

        // 🔔 Notify provider
        app(NotificationService::class)->notify(
            $invoice->provider,
            'invoice_paid',
            'An invoice has been paid'
        );
    }

    protected function handlePaymentFailed($event): void
    {
        // Optional: logging, retry logic, etc.
    }
}
