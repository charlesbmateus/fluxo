<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            [
                'name' => 'Cleaning',
                'slug' => 'cleaning',
                'description' => 'Home and office cleaning services',
                'icon' => 'mdi-broom',
            ],
            [
                'name' => 'Electrician',
                'slug' => 'electrician',
                'description' => 'Electrical installation and repair',
                'icon' => 'mdi-flash',
            ],
            [
                'name' => 'Babysitting',
                'slug' => 'babysitting',
                'description' => 'Child care services',
                'icon' => 'mdi-baby',
            ],
        ];

        foreach ($categoriesData as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']], // UNIQUE KEY
                $category
            );
        }
    }
}
