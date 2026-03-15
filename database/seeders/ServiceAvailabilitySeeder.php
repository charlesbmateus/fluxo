<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceAvailability;
use Illuminate\Database\Seeder;

class ServiceAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        // Monday (1) to Friday (5), 09:00 – 18:00
        $weekdays = [1, 2, 3, 4, 5];

        Service::all()->each(function (Service $service) use ($weekdays) {
            foreach ($weekdays as $day) {
                ServiceAvailability::create([
                    'service_id'  => $service->id,
                    'day_of_week' => $day,
                    'start_time'  => '09:00:00',
                    'end_time'    => '18:00:00',
                    'is_active'   => true,
                ]);
            }
        });
    }
}
