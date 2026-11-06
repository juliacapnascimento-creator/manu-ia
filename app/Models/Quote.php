<?php

namespace App\Models;

use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['public_id', 'product_id', 'conversation_id', 'quantity', 'customization', 'customization_note', 'purpose', 'desired_date', 'unit_price', 'customization_unit_price', 'total', 'status', 'is_demo'])]
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'customization' => 'boolean',
            'desired_date' => 'date',
            'unit_price' => 'decimal:2',
            'customization_unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'is_demo' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
