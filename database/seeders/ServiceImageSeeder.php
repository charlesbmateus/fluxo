<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceImageSeeder extends Seeder
{
    public function run(): void
    {
        $imagesByCategory = [
            'cleaning' => [
                'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800',
                'https://images.unsplash.com/photo-1556911220-bff31c812dba?w=800',
            ],
            'plumbing' => [
                'https://images.unsplash.com/photo-1605146768851-eda79da39897?w=800',
                'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?w=800',
            ],
            'beauty' => [
                'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=800',
                'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?w=800',
            ],
            'default' => [
                'https://picsum.photos/seed/default/800/600'
            ]
        ];

        Service::with('category')->get()->each(function ($service) use ($imagesByCategory) {

            $slug = $service->category->slug ?? 'default';

            $images = $imagesByCategory[$slug] ?? $imagesByCategory['default'];

            $service->update([
                'thumbnail' => $images[array_rand($images)]
            ]);
        });
    }
}
