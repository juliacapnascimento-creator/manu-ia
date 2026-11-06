<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ChatAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class ChatController extends Controller
{
    public function __invoke(Request $request, ChatAssistant $assistant): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'uuid'],
            'channel' => ['required', Rule::in(['site', 'whatsapp', 'instagram'])],
            'message' => ['required', 'string', 'max:800'],
            'marketing_consent' => ['sometimes', 'boolean'],
        ]);

        $conversation = Conversation::query()->firstOrCreate(
            ['public_id' => $validated['conversation_id']],
            ['channel' => $validated['channel']],
        );

        $conversation->channel = $validated['channel'];

        if (array_key_exists('marketing_consent', $validated)) {
            $conversation->marketing_consent = $validated['marketing_consent'];
        }

        $conversation->last_message_at = now();
        $conversation->save();
        $conversation->messages()->create([
            'role' => 'user',
            'channel' => $validated['channel'],
            'content' => $validated['message'],
        ]);

        $history = $conversation->messages()
            ->latest('id')
            ->limit(12)
            ->get(['role', 'content'])
            ->reverse()
            ->map(fn ($message): array => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();

        try {
            $reply = $assistant->reply($history, $validated['channel']);
        } catch (Throwable $exception) {
            Log::warning('Chat assistant failed to respond.', [
                'conversation_id' => $conversation->public_id,
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'Não consegui responder agora. Tente novamente em instantes.',
            ], 503);
        }

        $conversation->messages()->create([
            'role' => 'assistant',
            'channel' => $validated['channel'],
            'content' => $reply['content'],
        ]);

        return response()->json([
            'conversation_id' => $conversation->public_id,
            'message' => $reply['content'],
            'mode' => $reply['mode'],
            'marketing_consent' => $conversation->marketing_consent,
        ]);
    }
}
