<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'species', 'category', 'description', 'price', 'personalization_price', 'availability', 'illustration', 'is_demo', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'personalization_price' => 'decimal:2',
            'is_demo' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
