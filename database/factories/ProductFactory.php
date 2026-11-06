<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'species' => fake()->word(),
            'category' => 'Peça decorativa',
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 50, 300),
            'personalization_price' => fake()->randomFloat(2, 5, 30),
            'availability' => 'available',
            'illustration' => 'tamandua-mirim',
            'is_demo' => true,
            'is_active' => true,
        ];
    }
}
