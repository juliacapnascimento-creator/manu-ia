<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => fake()->uuid(),
            'product_id' => Product::factory(),
            'conversation_id' => Conversation::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'customization' => false,
            'customization_note' => null,
            'purpose' => 'presentear',
            'desired_date' => null,
            'unit_price' => 89.00,
            'customization_unit_price' => 0,
            'total' => 89.00,
            'status' => 'pending',
            'is_demo' => true,
        ];
    }
}
