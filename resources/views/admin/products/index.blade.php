@extends('admin.layout')

@section('title', 'Catálogo')

@section('content')
    <div class="page-kicker"><span></span> PRODUTOS</div>
    <div class="page-title-row"><div><h1>Catálogo</h1><p>Atualize o que a Manu pode apresentar e usar nos cálculos.</p></div><a class="admin-primary-link" href="{{ route('admin.products.create') }}">+ Novo produto</a></div>
    <div class="admin-demo-banner"><span>✳</span><p>Itens com selo <strong>A CONFIRMAR</strong> usam dados das imagens enviadas. Confirme preço e disponibilidade atualizados antes de marcar como revisados.</p></div>
    <section class="admin-panel list-panel"><div class="panel-heading"><div><h2>Produtos cadastrados</h2><p>{{ $products->where('is_active', true)->count() }} ativos · {{ $products->count() }} no total</p></div></div>
        @if ($products->isEmpty())<div class="empty-state"><span>✿</span><strong>O catálogo está vazio</strong><small>Cadastre um produto para exibi-lo no protótipo.</small></div>
        @else
            <div class="table-scroll"><table class="admin-table product-table"><thead><tr><th>Produto</th><th>Preço-base</th><th>Personalização</th><th>Disponibilidade</th><th>Dados</th><th></th></tr></thead><tbody>
                @foreach ($products as $product)
                    <tr class="{{ $product->is_active ? '' : 'row-inactive' }}"><td><div class="table-product-name"><span class="product-spot spot-{{ $product->illustration }}">✿</span><span><strong>{{ $product->name }}</strong><small>{{ $product->species ?? $product->category }}</small></span></div></td><td>{{ $product->price !== null ? 'R$ '.number_format((float) $product->price, 2, ',', '.') : 'A consultar' }}</td><td>{{ $product->personalization_price !== null ? '+ R$ '.number_format((float) $product->personalization_price, 2, ',', '.') : 'A consultar' }}</td><td>{{ ['unknown' => 'A confirmar', 'available' => 'Disponível', 'made_to_order' => 'Por encomenda', 'out_of_stock' => 'Indisponível'][$product->availability] ?? $product->availability }}</td><td><span class="status-pill {{ $product->is_demo ? 'status-pending' : 'status-converted' }}">{{ $product->is_demo ? 'A confirmar' : 'Revisado' }}</span>@unless ($product->is_active)<small class="inactive-label">Arquivado</small>@endunless</td><td><div class="row-actions"><a href="{{ route('admin.products.edit', $product) }}">Editar</a>@if ($product->is_active)<form action="{{ route('admin.products.destroy', $product) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="archive-button">Arquivar</button></form>@endif</div></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
@endsection
