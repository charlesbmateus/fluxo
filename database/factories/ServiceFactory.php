<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'user_id' => User::factory()->providerRole(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph(4),
            'price' => $this->faker->randomFloat(2, 40, 150),
            'pricing_model' => $this->faker->randomElement(['fixed', 'hourly']),
            'city' => $this->faker->city(),
            'country' => 'Switzerland',
            'is_active' => true,
            'thumbnail' => null,
        ];
    }
}
