<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()->orderByDesc('updated_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);
        $attributes['slug'] = $this->uniqueSlug($attributes['name']);

        Product::query()->create($attributes);

        return redirect()->route('admin.products.index')->with('status', 'Produto cadastrado.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);
        $attributes['slug'] = $this->uniqueSlug($attributes['name'], $product);
        $product->update($attributes);

        return redirect()->route('admin.products.index')->with('status', 'Produto atualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);

        return redirect()->route('admin.products.index')->with('status', 'Produto arquivado; os orçamentos antigos foram preservados.');
    }

    /** @return array<string, mixed> */
    private function validatedAttributes(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'species' => ['nullable', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'personalization_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'availability' => ['required', Rule::in(['unknown', 'available', 'made_to_order', 'out_of_stock'])],
            'illustration' => ['required', Rule::in(['boto-cinza', 'sapinho-montanha', 'tartaruga-verde', 'caranguejo-uca', 'tamandua-mirim', 'tatu-galinha'])],
            'is_demo' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?Product $product = null): string
    {
        $baseSlug = Str::slug($name) ?: 'produto';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($product, fn ($query) => $query->where('id', '!=', $product->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
