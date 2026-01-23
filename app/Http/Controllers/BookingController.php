<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Display a list of bookings for the authenticated user.
     */
    public function index()
    {
        $user = auth()->user();

        // Client → their bookings
        if ($user->isClient()) {
            return Booking::with(['service', 'service.user'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        // Provider → bookings for their services
        if ($user->isProvider()) {
            return Booking::with(['service', 'client'])
                ->forProvider($user->id)
                ->latest()
                ->get();
        }

        // Admin → everything
        return Booking::with(['service', 'client', 'provider'])->latest()->get();
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request, BookingService $bookingService)
    {
        $validated = $request->validate([
            'service_id'   => ['required', 'exists:services,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $booking = $bookingService->create([
                'user_id'      => auth()->id(),
                'service_id'   => $validated['service_id'],
                'scheduled_at' => $validated['scheduled_at'],
                'notes'        => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'message' => 'Booking created successfully.',
                'booking' => $booking->load(['service', 'service.user']),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Booking failed.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display a specific booking.
     */
    public function show(Booking $booking)
    {
        $this->authorizeAccess($booking);

        return $booking->load(['service', 'client', 'provider']);
    }

    /**
     * Cancel a booking (client or provider).
     */
    public function destroy(Booking $booking)
    {
        $this->authorizeAccess($booking);

        if (! $booking->status->canBeCancelled()) {
            return response()->json([
                'message' => 'This booking cannot be cancelled.',
            ], 422);
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Booking cancelled successfully.',
        ]);
    }

    /**
     * Authorization helper.
     */
    protected function authorizeAccess(Booking $booking): void
    {
        $user = auth()->user();

        if (
            $user->isAdmin() ||
            $booking->user_id === $user->id ||
            $booking->service->user_id === $user->id
        ) {
            return;
        }

        abort(403, 'Unauthorized');
    }
}
