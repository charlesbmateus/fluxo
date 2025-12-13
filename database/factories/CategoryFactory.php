<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true); // "Home Cleaning", "Child Care", etc.

        return [
            'name' => ucfirst($name),
            'slug' => str()->slug($name),
            'description' => fake()->optional()->sentence(12),
            'icon' => fake()->optional()->randomElement([
                'mdi-home',
                'mdi-broom',
                'mdi-hammer',
                'mdi-account',
                'mdi-tools',
                'mdi-sofa',
                'mdi-lawn-mower',
            ]),
        ];
    }
}
