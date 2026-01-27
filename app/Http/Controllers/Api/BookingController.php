<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

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

        return response()->json([
            'message' => 'Booking created successfully',
            'data' => $booking->load(['service', 'provider']),
        ], 201);
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

        return response()->json([
            'message' => 'Booking status updated',
            'data' => $updated,
        ]);
    }
}
