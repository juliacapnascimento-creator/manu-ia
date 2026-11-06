<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_chat_uses_catalog_data_and_saves_shared_conversation(): void
    {
        config(['services.openai.key' => null]);
        Product::factory()->create([
            'name' => 'Mico-leão-dourado',
            'species' => 'mico-leão-dourado',
            'price' => 89.00,
            'description' => 'Peça demonstrativa inspirada na fauna.',
        ]);

        $conversationId = fake()->uuid();

        $response = $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'channel' => 'whatsapp',
            'message' => 'Qual é o preço do mico-leão-dourado?',
            'marketing_consent' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('mode', 'demo')
            ->assertJsonPath('marketing_consent', true)
            ->assertJsonPath('conversation_id', $conversationId)
            ->assertJsonPath('message', fn (string $message): bool => str_contains($message, '89,00') && str_contains($message, 'captura enviada')
            );

        $conversation = Conversation::query()->where('public_id', $conversationId)->firstOrFail();
        $this->assertSame('whatsapp', $conversation->channel);
        $this->assertTrue($conversation->marketing_consent);
        $this->assertCount(2, $conversation->messages);
        Http::assertNothingSent();

        $this->getJson('/api/chat/'.$conversationId)
            ->assertOk()
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('messages.0.channel', 'whatsapp');
    }

    public function test_chat_uses_openai_responses_api_when_configured(): void
    {
        config(['services.openai.key' => 'test-api-key']);
        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'output' => [[
                    'type' => 'message',
                    'content' => [['type' => 'output_text', 'text' => 'Olá! Posso ajudar.']],
                ]],
            ]),
        ]);

        $this->postJson('/api/chat', [
            'conversation_id' => fake()->uuid(),
            'channel' => 'instagram',
            'message' => 'Olá!',
        ])->assertOk()
            ->assertJsonPath('mode', 'openai')
            ->assertJsonPath('message', 'Olá! Posso ajudar.');

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.openai.com/v1/responses' &&
            $request->hasHeader('Authorization', 'Bearer test-api-key') &&
            $request['store'] === false &&
            str_contains($request['instructions'], 'Nunca invente preços') &&
            str_contains($request['instructions'], 'Simule o tom de Direct do Instagram')
        );
    }

    public function test_demo_chat_lists_only_products_from_the_catalog(): void
    {
        config(['services.openai.key' => null]);
        Product::factory()->create([
            'name' => 'Mico-leão-dourado',
            'species' => 'mico-leão-dourado',
            'description' => 'Escultura demonstrativa inspirada na fauna.',
        ]);

        $this->postJson('/api/chat', [
            'conversation_id' => fake()->uuid(),
            'channel' => 'site',
            'message' => 'Quais peças tem no catálogo?',
        ])->assertOk()
            ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'Mico-leão-dourado') &&
                ! str_contains($message, 'Preguiça-de-coleira')
            );

        Http::assertNothingSent();
    }

    public function test_chat_rejects_unsupported_channels_and_long_messages(): void
    {
        $this->postJson('/api/chat', [
            'conversation_id' => fake()->uuid(),
            'channel' => 'telegram',
            'message' => str_repeat('a', 801),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['channel', 'message']);
    }

    public function test_demo_chat_keeps_context_and_changes_tone_between_simulated_channels(): void
    {
        config(['services.openai.key' => null]);
        $product = Product::factory()->create([
            'name' => 'Tamanduá-mirim',
            'slug' => 'tamandua-mirim',
            'species' => 'Tamandua tetradactyla',
            'price' => 39.00,
            'illustration' => 'tamandua-mirim',
            'description' => 'Peça avulsa em biscuit, 5–7 cm e 10–20 g.',
        ]);
        $conversationId = fake()->uuid();

        $introduction = $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'channel' => 'whatsapp',
            'message' => 'Me conta sobre o tamanduá-mirim',
        ])->assertOk()->json('message');

        $followUp = $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'channel' => 'instagram',
            'message' => 'E quanto custa?',
        ])->assertOk()->json('message');

        $measurement = $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'channel' => 'site',
            'message' => 'E qual o tamanho?',
        ])->assertOk()->json('message');

        $this->assertStringContainsString($product->name, $introduction);
        $this->assertStringContainsString('😊', $introduction);
        $this->assertStringContainsString('39,00', $followUp);
        $this->assertStringContainsString('captura enviada', $followUp);
        $this->assertStringContainsString('✨', $followUp);
        $this->assertStringContainsString('5–7 cm', $measurement);
        $this->assertSame(6, Message::query()->whereHas('conversation', fn ($query) => $query->where('public_id', $conversationId))->count());
    }

    public function test_homepage_uses_the_supplied_real_product_photo(): void
    {
        Product::factory()->create([
            'name' => 'Boto-cinza',
            'slug' => 'boto-cinza',
            'species' => 'Sotalia guianensis',
            'illustration' => 'boto-cinza',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('images/boto-cinza.jpg')
            ->assertSee('Peça de biscuit real: Boto-cinza');

        $this->assertFileExists(public_path('images/boto-cinza.jpg'));
    }

    public function test_demo_chat_explains_when_a_requested_product_is_missing_from_the_catalog(): void
    {
        config(['services.openai.key' => null]);

        $this->postJson('/api/chat', [
            'conversation_id' => fake()->uuid(),
            'channel' => 'site',
            'message' => 'Qual é o preço da coruja?',
        ])->assertOk()
            ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'não quero chutar') || str_contains($message, 'Não encontrei uma peça específica'));
    }

    public function test_demo_chat_clarifies_an_ambiguous_yes_using_the_product_context(): void
    {
        config(['services.openai.key' => null]);
        Product::factory()->create([
            'name' => 'Boto-cinza',
            'slug' => 'boto-cinza',
            'species' => 'Sotalia guianensis',
            'price' => 39.00,
            'illustration' => 'boto-cinza',
            'description' => 'Peça avulsa em biscuit, 5–7 cm e 10–20 g.',
        ]);
        $conversationId = fake()->uuid();

        $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'channel' => 'site',
            'message' => 'boto',
        ])->assertOk()
            ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'valor ou simular'));

        $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'channel' => 'site',
            'message' => 'sim',
        ])->assertOk()
            ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'Boto-cinza') && str_contains($message, 'valor') && str_contains($message, 'simular'));
    }

    public function test_homepage_shows_a_clearly_marked_demo_rating(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Como foi conversar com a Manu?')
            ->assertSee('id="rating-form"', false)
            ->assertSee('sua resposta não é enviada nem salva');
    }

    public function test_demo_chat_keeps_product_context_when_customer_asks_its_price(): void
    {
        config(['services.openai.key' => null]);
        Product::factory()->create([
            'name' => 'Tatu-galinha',
            'slug' => 'tatu-galinha',
            'species' => 'Dasypus novemcinctus',
            'price' => 39.00,
            'illustration' => 'tatu-galinha',
            'description' => 'Miniatura de biscuit de 5–7 cm e 10–20 g.',
        ]);
        $conversationId = fake()->uuid();

        $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'channel' => 'site',
            'message' => 'Me conta sobre o Tatu-galinha',
        ])->assertOk()
            ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'valor ou simular'));

        $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'channel' => 'site',
            'message' => 'Queria saber o valor',
        ])->assertOk()
            ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'Tatu-galinha') && str_contains($message, '39,00'));
    }
}
