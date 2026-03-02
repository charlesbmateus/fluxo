<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar primero (opcional pero recomendado en desarrollo)
        Category::truncate();

        $categories = [

            // 🧖 WELLNESS & RELAX
            [
                'name' => 'Spa & Wellness',
                'description' => 'Relaxing spa treatments, saunas and wellness experiences.',
                'icon' => 'mdi-spa',
            ],
            [
                'name' => 'Massages',
                'description' => 'Therapeutic and relaxation massages at home or hotel.',
                'icon' => 'mdi-hand-heart',
            ],
            [
                'name' => 'Physiotherapy',
                'description' => 'Professional physiotherapy and sports recovery services.',
                'icon' => 'mdi-arm-flex',
            ],
            [
                'name' => 'Yoga & Meditation',
                'description' => 'Private yoga and guided meditation sessions.',
                'icon' => 'mdi-yoga',
            ],

            // 💅 BEAUTY
            [
                'name' => 'Nail Services',
                'description' => 'Manicure and pedicure services.',
                'icon' => 'mdi-hand-back-left',
            ],
            [
                'name' => 'Hairdressers & Styling',
                'description' => 'Haircuts, styling and personal grooming.',
                'icon' => 'mdi-content-cut',
            ],
            [
                'name' => 'Makeup & Beauty',
                'description' => 'Professional makeup and beauty treatments.',
                'icon' => 'mdi-face-woman',
            ],
            [
                'name' => 'Barber Services',
                'description' => 'Traditional and modern barber services.',
                'icon' => 'mdi-razor-double-edge',
            ],

            // 🏡 HOME SERVICES
            [
                'name' => 'House Cleaning',
                'description' => 'Cleaning services for apartments and chalets.',
                'icon' => 'mdi-broom',
            ],
            [
                'name' => 'Private Chef',
                'description' => 'Personal chef services at your location.',
                'icon' => 'mdi-chef-hat',
            ],
            [
                'name' => 'Babysitting & Childcare',
                'description' => 'Professional childcare during your stay.',
                'icon' => 'mdi-baby-face',
            ],
            [
                'name' => 'Home Maintenance',
                'description' => 'Repairs and maintenance for vacation homes.',
                'icon' => 'mdi-tools',
            ],
            [
                'name' => 'Laundry Services',
                'description' => 'Laundry and ironing services.',
                'icon' => 'mdi-washing-machine',
            ],

            // 🎿 SWISS EXPERIENCES
            [
                'name' => 'Ski Instructors',
                'description' => 'Private ski and snowboard lessons.',
                'icon' => 'mdi-ski',
            ],
            [
                'name' => 'Mountain Guides',
                'description' => 'Guided hiking and alpine experiences.',
                'icon' => 'mdi-image-filter-hdr',
            ],
            [
                'name' => 'Helicopter Tours',
                'description' => 'Scenic helicopter flights over the Alps.',
                'icon' => 'mdi-helicopter',
            ],
            [
                'name' => 'Wine & Chocolate Tasting',
                'description' => 'Swiss wine and chocolate tasting experiences.',
                'icon' => 'mdi-glass-wine',
            ],

            // 🚗 TOURIST PRACTICAL SERVICES
            [
                'name' => 'Airport Transfers',
                'description' => 'Private drivers and airport transport.',
                'icon' => 'mdi-car',
            ],
            [
                'name' => 'Concierge Services',
                'description' => 'Personal assistance during your vacation.',
                'icon' => 'mdi-account-tie',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
            ]);
        }
    }
}
