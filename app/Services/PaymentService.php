<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function create(User $user, array $data): Payment
    {
        $booking = Booking::findOrFail($data['booking_id']);

        // ✅ Only confirmed bookings can be paid
        if ($booking->status !== BookingStatus::CONFIRMED) {
            throw ValidationException::withMessages([
                'booking' => 'Only confirmed bookings can be paid.',
            ]);
        }

        // ✅ Booking must belong to the client
        if ($booking->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'booking' => 'You are not allowed to pay this booking.',
            ]);
        }

        return Payment::create([
            'user_id'        => $user->id,
            'booking_id'     => $booking->id,
            'amount'         => $booking->price,
            'currency'       => 'CHF',
            'status'         => PaymentStatus::PENDING,
            'platform_fee'   => 0,
            'payment_method' => null,
        ]);
    }
}
