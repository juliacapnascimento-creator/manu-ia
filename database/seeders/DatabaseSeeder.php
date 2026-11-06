<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Product::query()
            ->whereIn('slug', ['mico-leao-dourado', 'tucano-bico-verde', 'preguica-de-coleira', 'jardim-de-bromelias'])
            ->where('is_demo', true)
            ->update(['is_active' => false]);

        $products = [
            ['name' => 'Boto-cinza', 'slug' => 'boto-cinza', 'species' => 'Sotalia guianensis', 'category' => 'Fauna da Mata Atlântica', 'description' => 'Miniatura de biscuit de 5–7 cm e 10–20 g. A captura informa a classificação Vulnerável (VU), a família Delphinidae e um certificado de origem assinado.', 'price' => 39.00, 'personalization_price' => null, 'availability' => 'unknown', 'illustration' => 'boto-cinza'],
            ['name' => 'Sapinho-da-montanha', 'slug' => 'sapinho-da-montanha', 'species' => 'Brachycephalus sp.', 'category' => 'Fauna da Mata Atlântica', 'description' => 'Miniatura de biscuit de 5–7 cm e 10–20 g. A captura descreve a espécie como microendêmica e cita a família Brachycephalidae.', 'price' => 39.00, 'personalization_price' => null, 'availability' => 'unknown', 'illustration' => 'sapinho-montanha'],
            ['name' => 'Tartaruga-verde', 'slug' => 'tartaruga-verde', 'species' => 'Chelonia mydas', 'category' => 'Fauna da Mata Atlântica', 'description' => 'Miniatura de biscuit de 5–7 cm e 10–20 g. A captura informa a categoria Quase ameaçada (NT), a família Cheloniidae e um certificado de origem assinado.', 'price' => 39.00, 'personalization_price' => null, 'availability' => 'unknown', 'illustration' => 'tartaruga-verde'],
            ['name' => 'Caranguejo-uçá', 'slug' => 'caranguejo-uca', 'species' => 'Ucides cordatus', 'category' => 'Fauna da Mata Atlântica', 'description' => 'Miniatura de biscuit de 5–7 cm e 10–20 g. A captura informa a categoria Quase ameaçada (NT), a família Ocypodidae e um certificado de origem assinado.', 'price' => 39.00, 'personalization_price' => null, 'availability' => 'unknown', 'illustration' => 'caranguejo-uca'],
            ['name' => 'Tamanduá-mirim', 'slug' => 'tamandua-mirim', 'species' => 'Tamandua tetradactyla', 'category' => 'Fauna da Mata Atlântica', 'description' => 'Miniatura de biscuit de 5–7 cm e 10–20 g. A captura cita a família Myrmecophagidae, a categoria Pouco preocupante (LC) e um certificado de origem assinado.', 'price' => 39.00, 'personalization_price' => null, 'availability' => 'unknown', 'illustration' => 'tamandua-mirim'],
            ['name' => 'Tatu-galinha', 'slug' => 'tatu-galinha', 'species' => 'Dasypus novemcinctus', 'category' => 'Fauna da Mata Atlântica', 'description' => 'Miniatura de biscuit de 5–7 cm e 10–20 g. A captura cita a família Dasypodidae, a categoria Pouco preocupante (LC) e um certificado de origem assinado.', 'price' => 39.00, 'personalization_price' => null, 'availability' => 'unknown', 'illustration' => 'tatu-galinha'],
        ];

        foreach ($products as $product) {
            Product::query()->firstOrCreate(
                ['slug' => $product['slug']],
                [...$product, 'is_demo' => true, 'is_active' => true],
            );
        }
    }
}
