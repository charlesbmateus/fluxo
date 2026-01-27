<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function create(User $client, array $data): Booking
    {
        $service = Service::findOrFail($data['service_id']);

        $start = Carbon::parse($data['start_datetime']);
        $end   = $this->calculateEndTime($service, $start);

        // ⛔️ disponibilidad
        if (! $service->isAvailableAt($start, $end)) {
            throw ValidationException::withMessages([
                'start_datetime' => 'The service is not available at the selected time.',
            ]);
        }

        return Booking::create([
            'user_id'        => $client->id,
            'service_id'     => $service->id,
            'start_datetime' => $start,
            'end_datetime'   => $end,
            'status'         => BookingStatus::PENDING,
            'price'          => $this->calculatePrice($service, $start, $end),
        ]);
    }

    public function updateStatus(
        User $actor,
        Booking $booking,
        string $newStatus
    ): Booking {
        $currentStatus = $booking->status->value;

        // Allowed transitions map
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
        ];

        // Check transition validity
        if (! isset($allowedTransitions[$currentStatus]) ||
            ! in_array($newStatus, $allowedTransitions[$currentStatus], true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid status transition.',
            ]);
        }

        // Authorization rules
        if ($newStatus === 'confirmed' || $newStatus === 'completed') {
            // Only provider can confirm or complete
            if ($actor->id !== $booking->provider->id) {
                throw ValidationException::withMessages([
                    'status' => 'Only the provider can perform this action.',
                ]);
            }
        }

        if ($newStatus === 'cancelled') {
            // Client OR provider can cancel
            if (
                $actor->id !== $booking->user_id &&
                $actor->id !== $booking->provider->id
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Not authorized to cancel this booking.',
                ]);
            }
        }

        $booking->update([
            'status' => $newStatus,
        ]);

        return $booking->fresh();
    }

    protected function calculateEndTime(Service $service, Carbon $start): Carbon
    {
        return $start->copy()->addHour(); // MVP
    }

    protected function calculatePrice(Service $service, Carbon $start, Carbon $end): float
    {
        if ($service->pricing_model === 'hourly') {
            $hours = max(1, ceil($start->diffInMinutes($end) / 60));
            return round($service->price * $hours, 2);
        }

        return (float) $service->price;
    }
}
