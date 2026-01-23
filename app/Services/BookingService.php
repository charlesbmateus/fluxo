<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Create a booking safely.
     *
     * @throws ValidationException
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {

            /** @var Service $service */
            $service = Service::query()->lockForUpdate()->findOrFail($data['service_id']);

            $start = Carbon::parse($data['scheduled_at']);
            $end   = $this->calculateEndTime($service, $start);

            // 1️⃣ Availability check
            if (! $service->isAvailableAt($start, $end)) {
                throw ValidationException::withMessages([
                    'scheduled_at' => 'The service is not available at the selected time.',
                ]);
            }

            // 2️⃣ Create booking
            $booking = Booking::create([
                'user_id'      => $data['user_id'], // client
                'service_id'   => $service->id,
                'scheduled_at' => $start,
                'status'       => BookingStatus::PENDING,
                'price'        => $this->calculatePrice($service, $start, $end),
                'notes'        => $data['notes'] ?? null,
            ]);

            // 🔜 FUTURE:
            // - create invoice
            // - emit event BookingCreated
            // - send notification

            return $booking;
        });
    }

    /**
     * Calculate booking end time.
     * (simple default = 1 hour)
     */
    protected function calculateEndTime(Service $service, Carbon $start): Carbon
    {
        // Later: derive from service duration / pricing model
        return $start->copy()->addHour();
    }

    /**
     * Calculate booking price.
     */
    protected function calculatePrice(Service $service, Carbon $start, Carbon $end): float
    {
        if ($service->pricing_model === 'hourly') {
            $hours = max(1, $start->diffInMinutes($end) / 60);
            return round($service->price * $hours, 2);
        }

        return (float) $service->price;
    }
}
