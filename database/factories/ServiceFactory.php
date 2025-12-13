<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Service::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        $slug = Str::slug($title) . '-' . fake()->unique()->randomNumber(4);

        return [
            // Service owner — must be a provider
            'user_id' => User::factory()->state(['role' => 'provider']),

            // Category
            'category_id' => Category::factory(),

            'title' => $title,
            'slug' => $slug,
            'description' => fake()->paragraph(4),

            // Pricing
            'price' => fake()->randomFloat(2, 20, 500),
            'pricing_model' => fake()->randomElement(['fixed', 'hourly']),

            // Location
            'city' => fake()->city(),
            'country' => fake()->country(),

            'is_active' => true,

            // Optional thumbnail
            'thumbnail' => fake()->imageUrl(640, 480, 'business', true),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
