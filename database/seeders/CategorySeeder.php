<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Home Cleaning',
            'Plumbing',
            'Electrical',
            'Painting',
            'Handyman',
            'Moving Services',
            'Beauty & Makeup',
            'Hair Styling',
            'Massage Therapy',
            'Fitness Training',
            'Tutoring',
            'Photography',
            'Tech Support',
            'Business Consulting',
        ];

        foreach ($categories as $name) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
