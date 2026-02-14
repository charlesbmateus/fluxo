<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $providers = User::where('role', 'provider')->get();
        $categories = Category::all();

        if ($providers->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $servicesData = [

            'cleaning' => [
                'title' => 'Professional Home Cleaning',
                'description' => 'Complete professional home cleaning service with eco-friendly products.',
                'image' => 'https://picsum.photos/seed/cleaning/600/400',
            ],

            'plumbing' => [
                'title' => 'Emergency Plumbing Service',
                'description' => 'Fast and reliable plumbing repairs and installations.',
                'image' => 'https://picsum.photos/seed/plumbing/600/400',
            ],

            'electrical' => [
                'title' => 'Certified Electrician Services',
                'description' => 'Certified electrical installation and maintenance services.',
                'image' => 'https://picsum.photos/seed/electrical/600/400',
            ],

            'beauty-makeup' => [
                'title' => 'Professional Makeup Artist',
                'description' => 'Makeup services for events and weddings.',
                'image' => 'https://picsum.photos/seed/makeup/600/400',
            ],

            'tutoring' => [
                'title' => 'Private Tutoring Sessions',
                'description' => 'Personalized tutoring sessions for students of all levels.',
                'image' => 'https://picsum.photos/seed/tutoring/600/400',
            ],
        ];

        foreach ($categories as $category) {

            if (!isset($servicesData[$category->slug])) {
                continue;
            }

            foreach ($providers->random(3) as $provider) {

                $data = $servicesData[$category->slug];

                Service::create([
                    'user_id'       => $provider->id,
                    'category_id'   => $category->id,
                    'title'         => $data['title'],
                    'slug'          => Str::slug($data['title']) . '-' . Str::random(5),
                    'description'   => $data['description'],
                    'price'         => rand(50, 200),
                    'pricing_model' => 'fixed',
                    'city'          => 'Zurich',
                    'country'       => 'Switzerland',
                    'is_active'     => true,
                    'thumbnail'     => $data['image'],
                ]);
            }
        }
    }
}
