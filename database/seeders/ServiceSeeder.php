<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar datos
        ServiceImage::truncate();
        Service::truncate();

        $providers = User::where('role', 'provider')->get();
        $categories = Category::all();

        if ($providers->isEmpty()) {
            return;
        }

        $servicesData = [

            // 🧖 SPA
            [
                'title' => 'Alpine Luxury Spa Experience',
                'category' => 'Spa & Wellness',
                'price' => 220,
                'city' => 'Zermatt',
                'images' => [
                    'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=1200',
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=1200',
                    'https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=1200',
                ]
            ],

            // 💆 MASSAGE
            [
                'title' => 'Private Chalet Massage',
                'category' => 'Massages',
                'price' => 180,
                'city' => 'St. Moritz',
                'images' => [
                    'https://images.unsplash.com/photo-1552693673-1bf958298935?w=1200',
                    'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?w=1200',
                    'https://images.unsplash.com/photo-1519823551278-64ac92734fb1?w=1200',
                ]
            ],

            // 👩‍🍳 CHEF
            [
                'title' => 'Private Swiss Chef at Home',
                'category' => 'Private Chef',
                'price' => 350,
                'city' => 'Verbier',
                'images' => [
                    'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=1200',
                    'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200',
                    'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?w=1200',
                ]
            ],

            // 🎿 SKI
            [
                'title' => 'Private Ski Instructor (Half Day)',
                'category' => 'Ski Instructors',
                'price' => 260,
                'city' => 'Davos',
                'images' => [
                    'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200',
                    'https://images.unsplash.com/photo-1483721310020-03333e577078?w=1200',
                    'https://images.unsplash.com/photo-1516567727245-ad8c3c3c91b8?w=1200',
                ]
            ],

            // 🧹 CLEANING
            [
                'title' => 'Luxury Chalet Cleaning Service',
                'category' => 'House Cleaning',
                'price' => 140,
                'city' => 'Interlaken',
                'images' => [
                    'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1200',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200',
                    'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?w=1200',
                ]
            ],

            // 💅 NAILS
            [
                'title' => 'Manicure & Pedicure at Your Hotel',
                'category' => 'Nail Services',
                'price' => 120,
                'city' => 'Zurich',
                'images' => [
                    'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?w=1200',
                    'https://images.unsplash.com/photo-1604654894610-df63bc536371?w=1200',
                    'https://images.unsplash.com/photo-1610992015732-2449b76344bc?w=1200',
                ]
            ],

            // 🚁 HELICOPTER
            [
                'title' => 'Alps Scenic Helicopter Tour',
                'category' => 'Helicopter Tours',
                'price' => 890,
                'city' => 'Lucerne',
                'images' => [
                    'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=1200',
                    'https://images.unsplash.com/photo-1526779259212-756e19dfd8df?w=1200',
                    'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=1200',
                ]
            ],

            // 🚗 TRANSFER
            [
                'title' => 'Private Airport Transfer (Luxury SUV)',
                'category' => 'Airport Transfers',
                'price' => 180,
                'city' => 'Geneva',
                'images' => [
                    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200',
                    'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=1200',
                    'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=1200',
                ]
            ],
        ];

        foreach ($servicesData as $data) {

            $category = $categories->firstWhere('name', $data['category']);
            $provider = $providers->random();

            $service = Service::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => fake()->paragraph(4),
                'price' => $data['price'],
                'city' => $data['city'],
                'country' => 'Switzerland',
                'is_active' => true,
                'user_id' => $provider->id,
                'category_id' => $category->id ?? null,
            ]);

            foreach ($data['images'] as $index => $image) {
                ServiceImage::create([
                    'service_id' => $service->id,
                    'path' => $image,
                    'is_primary' => $index === 0,
                ]);
            }
        }
    }
}
