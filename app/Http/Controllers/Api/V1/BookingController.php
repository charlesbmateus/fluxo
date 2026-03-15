<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * Client creates a booking
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create(
            auth()->user(),
            $request->validated()
        );

        return ApiResponse::success(
            $booking->load(['service', 'provider']),
            'Booking created successfully',
            201
        );
    }

    /**
     * Update booking status (confirm / cancel / complete)
     */
    public function updateStatus(
        UpdateBookingStatusRequest $request,
        Booking $booking
    ): JsonResponse {
        $updated = $this->bookingService->updateStatus(
            auth()->user(),
            $booking,
            $request->validated()['status']
        );

        return ApiResponse::success(
            $updated,
            'Booking status updated'
        );
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentIntent(Booking $booking): JsonResponse
    {
        $user = auth()->user();

        // Ensure booking belongs to an authenticated client
        if ($booking->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // 🚫 Prevent paying twice
        if ($booking->status === 'paid') {
            return response()->json([
                'message' => 'Booking already paid.'
            ], 400);
        }

        $provider = $booking->service->provider;

        if (! $provider->stripe_account_id) {
            return response()->json([
                'message' => 'Provider has not connected Stripe yet.'
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $amount = (int) round($booking->price * 100);

        $intent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'chf',
            'application_fee_amount' => (int) round($amount * 0.10), // 10% platform fee
            'transfer_data' => [
                'destination' => $provider->stripe_account_id,
            ],
            'metadata' => [
                'booking_id' => $booking->id,
                'client_id' => $user->id,
                'provider_id' => $provider->id,
            ],
        ]);

        // Save PaymentIntent ID for webhook verification
        $booking->update([
            'stripe_payment_intent_id' => $intent->id,
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret,
        ]);
    }
}
