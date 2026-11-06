<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_total_uses_catalog_price_and_optional_personalization(): void
    {
        $product = Product::factory()->create([
            'name' => 'Mico-leão-dourado',
            'price' => 89.00,
            'personalization_price' => 15.00,
        ]);
        $conversationId = fake()->uuid();

        $this->postJson('/api/quotes', [
            'product_id' => $product->id,
            'quantity' => 3,
            'customization' => true,
            'customization_note' => 'Plaquinha com nome',
            'purpose' => 'presentear',
            'desired_date' => today()->addDays(10)->toDateString(),
            'conversation_id' => $conversationId,
        ])->assertCreated()
            ->assertJsonPath('product', 'Mico-leão-dourado')
            ->assertJsonPath('total', '312.00')
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('is_demo', true)
            ->assertJsonPath('customization', true);

        $quote = Quote::query()->firstOrFail();
        $this->assertSame('presentear', $quote->purpose);
        $this->assertSame('Plaquinha com nome', $quote->customization_note);
        $this->assertSame(3, $quote->quantity);
        $this->assertSame($conversationId, Conversation::query()->findOrFail($quote->conversation_id)->public_id);
    }

    public function test_quote_does_not_guess_a_missing_catalog_price(): void
    {
        $product = Product::factory()->create(['price' => null]);

        $this->postJson('/api/quotes', [
            'product_id' => $product->id,
            'quantity' => 1,
            'customization' => false,
            'purpose' => 'decorar',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Este produto ainda não tem preço cadastrado. Consulte a equipe da ManuPekena.');

        $this->assertDatabaseCount('quotes', 0);
    }

    public function test_quote_validates_quantity_and_customization_details(): void
    {
        $product = Product::factory()->create();

        $this->postJson('/api/quotes', [
            'product_id' => $product->id,
            'quantity' => 31,
            'customization' => true,
            'purpose' => 'decorar',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity', 'customization_note']);
    }
}
