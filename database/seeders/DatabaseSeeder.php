<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ───────── CATEGORIES ─────────
        $this->call(CategorySeeder::class);

        // ───────── USERS ─────────
        $providers = User::factory(5)->providerRole()->create();
        $clients   = User::factory(15)->clientRole()->create();

        // ───────── SERVICES (Seeder) ─────────
        $this->call(ServiceSeeder::class);

        // ───────── SERVICE AVAILABILITIES ─────────
        $this->call(ServiceAvailabilitySeeder::class);

        $services = Service::all();

        // ───────── BOOKINGS ─────────
        $clients->each(function ($client) use ($services) {
            $services
                ->random(rand(1, min(3, $services->count())))
                ->each(function ($service) use ($client) {
                    Booking::factory()->create([
                        'user_id'    => $client->id,
                        'service_id' => $service->id,
                        'price'      => $service->price,
                    ]);
                });
        });

        // ───────── REVIEWS ─────────
        $services->each(function ($service) use ($clients) {
            Review::factory(rand(1, 3))->create([
                'service_id' => $service->id,
                'user_id'    => $clients->random()->id,
            ]);
        });
    }
}
