<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['public_id', 'channel', 'marketing_consent', 'last_message_at'])]
class Conversation extends Model
{
    public const MARKETING_INTEREST_LABELS = [
        'new_releases' => 'Novas peças',
        'gift_ideas' => 'Ideias para presentear',
        'fauna_stories' => 'Curiosidades sobre a fauna',
    ];

    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'marketing_consent' => 'boolean',
            'marketing_interests' => 'array',
            'last_message_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /** @return array<int, string> */
    public function marketingInterestNames(): array
    {
        return collect($this->marketing_interests ?? [])
            ->map(fn (string $interest): string => self::MARKETING_INTEREST_LABELS[$interest] ?? $interest)
            ->all();
    }
}
