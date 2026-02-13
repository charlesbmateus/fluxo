<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ───────── CATEGORIES ─────────
        $categories = Category::factory(10)->create();

        // ───────── USERS ─────────
        $providers = User::factory(20)->providerRole()->create();
        $clients   = User::factory(40)->clientRole()->create();

        // ───────── SERVICES + AVAILABILITY ─────────
        $providers->each(function ($provider) use ($categories) {
            $services = Service::factory(3)->create([
                'user_id'     => $provider->id,
                'category_id' => $categories->random()->id,
            ]);

            foreach ($services as $service) {
                foreach (range(0, 6) as $day) {
                    ServiceAvailability::factory()->create([
                        'service_id'  => $service->id,
                        'day_of_week' => $day,
                    ]);
                }
            }
        });

        $services = Service::all();

        // ───────── BOOKINGS ─────────
        $clients->each(function ($client) use ($services) {
            $services
                ->random(rand(1, 3))
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
            Review::factory(rand(1, 5))->create([
                'service_id' => $service->id,
                'user_id'    => $clients->random()->id,
            ]);
        });

        $this->call([
            ConversationSeeder::class,
            MessageSeeder::class,
        ]);
    }
}
