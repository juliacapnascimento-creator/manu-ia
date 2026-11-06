<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:30'],
            'customization' => ['required', 'boolean'],
            'customization_note' => ['nullable', 'string', 'max:180', 'required_if:customization,true'],
            'purpose' => ['required', Rule::in(['presentear', 'colecionar', 'decorar', 'outro'])],
            'desired_date' => ['nullable', 'date', 'after_or_equal:today'],
            'conversation_id' => ['nullable', 'uuid'],
        ]);

        $product = Product::query()->whereKey($validated['product_id'])->firstOrFail();

        if ($product->price === null) {
            return response()->json([
                'message' => 'Este produto ainda não tem preço cadastrado. Consulte a equipe da ManuPekena.',
            ], 422);
        }

        if ($validated['customization'] && $product->personalization_price === null) {
            return response()->json([
                'message' => 'O valor da personalização precisa ser consultado com a equipe da ManuPekena.',
            ], 422);
        }

        $conversation = null;

        if (! empty($validated['conversation_id'])) {
            $conversation = Conversation::query()->firstOrCreate(
                ['public_id' => $validated['conversation_id']],
                ['channel' => 'site'],
            );
        }

        $quantity = (int) $validated['quantity'];
        $unitPriceInCents = (int) round((float) $product->price * 100);
        $customizationPriceInCents = $validated['customization']
            ? (int) round((float) $product->personalization_price * 100)
            : 0;
        $totalInCents = ($unitPriceInCents + $customizationPriceInCents) * $quantity;

        $quote = Quote::query()->create([
            'public_id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'conversation_id' => $conversation?->id,
            'quantity' => $quantity,
            'customization' => $validated['customization'],
            'customization_note' => $validated['customization'] ? ($validated['customization_note'] ?? null) : null,
            'purpose' => $validated['purpose'],
            'desired_date' => $validated['desired_date'] ?? null,
            'unit_price' => $product->price,
            'customization_unit_price' => number_format($customizationPriceInCents / 100, 2, '.', ''),
            'total' => number_format($totalInCents / 100, 2, '.', ''),
            'status' => 'pending',
            'is_demo' => $product->is_demo,
        ]);

        return response()->json([
            'quote_id' => $quote->public_id,
            'product' => $product->name,
            'quantity' => $quote->quantity,
            'customization' => $quote->customization,
            'desired_date' => $quote->desired_date?->format('d/m/Y'),
            'unit_price' => $quote->unit_price,
            'customization_unit_price' => $quote->customization_unit_price,
            'total' => $quote->total,
            'status' => $quote->status,
            'is_demo' => $quote->is_demo,
            'message' => 'Simulação salva como orçamento pendente. O valor, a disponibilidade e o prazo precisam ser confirmados pela ManuPekena.',
        ], 201);
    }
}
