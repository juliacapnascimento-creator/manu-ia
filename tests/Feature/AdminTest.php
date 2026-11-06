<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_require_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/products')->assertRedirect('/admin/login');
        $this->get('/admin/quotes')->assertRedirect('/admin/login');
        $this->get('/admin/conversations')->assertRedirect('/admin/login');
    }

    public function test_team_member_can_sign_in_to_the_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'equipe@manu-ia.local',
            'password' => 'a-strong-password',
        ]);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'a-strong-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk()->assertSee('Visão geral');
    }

    public function test_admin_can_create_and_archive_a_catalog_product(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/admin/products', [
            'name' => 'Mico-leão-dourado',
            'species' => 'mico-leão-dourado',
            'category' => 'Fauna da Mata Atlântica',
            'description' => 'Descrição confirmada para a peça.',
            'price' => '89.00',
            'personalization_price' => '',
            'availability' => 'available',
            'illustration' => 'tamandua-mirim',
            'is_demo' => '0',
            'is_active' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'mico-leao-dourado')->firstOrFail();
        $this->assertFalse($product->is_demo);

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_update_quote_status_without_sending_a_message(): void
    {
        $this->actingAs(User::factory()->create());
        $quote = Quote::factory()->create(['status' => 'pending']);

        $this->patch(route('admin.quotes.status', $quote), ['status' => 'converted'])
            ->assertRedirect(route('admin.quotes.index'))
            ->assertSessionHas('status', 'Situação do orçamento atualizada. Nenhuma mensagem foi enviada.');

        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'status' => 'converted',
        ]);
    }

    public function test_dashboard_counts_quotes_and_labels_conversions_as_manual(): void
    {
        $this->actingAs(User::factory()->create());
        Quote::factory()->create(['status' => 'pending']);
        Quote::factory()->create(['status' => 'converted']);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Orçamentos enviados')
            ->assertSee('Total de simulações recebidas')
            ->assertSee('2')
            ->assertSee('Conversões registradas')
            ->assertSee('compras ainda não são registradas');
    }

    public function test_admin_can_review_conversation_history_and_consent_filter(): void
    {
        $this->actingAs(User::factory()->create());
        $conversation = Conversation::factory()->create(['marketing_consent' => true]);
        Message::query()->create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'channel' => 'site',
            'content' => 'Gostaria de conhecer as peças.',
        ]);

        $this->get(route('admin.conversations.index', ['consent' => 'yes']))
            ->assertOk()
            ->assertSee(substr($conversation->public_id, 0, 8));

        $this->get(route('admin.conversations.show', $conversation))
            ->assertOk()
            ->assertSee('Gostaria de conhecer as peças.')
            ->assertSee('aceita');
    }
}
