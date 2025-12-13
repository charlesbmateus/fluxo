<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Service;
use App\Models\ServiceAvailability;
use App\Models\User;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = Category::factory(10)->create();

        // Providers
        $providers = User::factory(20)->providerRole()->create();

        // Clients
        $clients = User::factory(40)->clientRole()->create();

        // Services + Availabilities
        $providers->each(function ($provider) use ($categories) {
            $services = Service::factory(3)->create([
                'user_id'     => $provider->id,
                'category_id' => $categories->random()->id,
            ]);

            foreach ($services as $service) {
                foreach (range(0, 4) as $day) {
                    ServiceAvailability::factory()->create([
                        'service_id' => $service->id,
                        'day_of_week' => $day,
                    ]);
                }
            }
        });

        // REVIEWS
        $services = Service::all();

        $services->each(function ($service) use ($clients) {
            Review::factory(rand(1, 5))->create([
                'service_id' => $service->id,
                'user_id'  => $clients->random()->id,
            ]);
        });

        // CHAT MESSAGES
        foreach ($clients as $client) {
            // Each client sends messages to random providers
            $randomProviders = $providers->random(rand(1, 5));
            foreach ($randomProviders as $provider) {
                ChatMessage::factory(rand(1, 3))->create([
                    'sender_id'   => $client->id,
                    'receiver_id' => $provider->id,
                ]);
            }
        }

        // BOOKING
        $services = Service::all();

        $clients->each(function ($client) use ($services) {
            // each client can book 1-3 random services
            $randomServices = $services->random(rand(1, 3));
            foreach ($randomServices as $service) {
                Booking::factory()->create([
                    'user_id' => $client->id,     // client
                    'service_id' => $service->id, // existing service
                    'price' => $service->price,   // optional: copy service price
                ]);
            }
        });
    }
}
