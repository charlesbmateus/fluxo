<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Http\Responses\ApiResponse;
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
}
