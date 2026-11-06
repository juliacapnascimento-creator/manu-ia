@extends('admin.layout')

@section('title', $product->exists ? 'Editar produto' : 'Novo produto')

@section('content')
    <div class="page-kicker"><span></span> CATÁLOGO / {{ $product->exists ? 'EDITAR' : 'NOVO PRODUTO' }}</div>
    <div class="page-title-row"><div><h1>{{ $product->exists ? 'Editar produto' : 'Novo produto' }}</h1><p>O agente usa estes dados nas respostas e nos orçamentos.</p></div><a class="admin-back-link" href="{{ route('admin.products.index') }}">← voltar ao catálogo</a></div>
    <form class="admin-panel product-edit-form" method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($product->exists) @method('PUT') @endif
        <div class="form-section-heading"><span>01</span><div><h2>Informações da peça</h2><p>Preencha apenas o que foi confirmado.</p></div></div>
        <div class="admin-form-grid">
            <div class="admin-field"><label for="name">Nome do produto <b>*</b></label><input id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="120" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="admin-field"><label for="species">Espécie representada</label><input id="species" name="species" value="{{ old('species', $product->species) }}" maxlength="120" placeholder="Ex.: mico-leão-dourado">@error('species')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="admin-field"><label for="category">Categoria <b>*</b></label><input id="category" name="category" value="{{ old('category', $product->category) }}" maxlength="120" placeholder="Ex.: Fauna da Mata Atlântica" required>@error('category')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="admin-field"><label for="availability">Disponibilidade cadastrada <b>*</b></label><select id="availability" name="availability" required>@foreach (['unknown' => 'A confirmar', 'available' => 'Disponível', 'made_to_order' => 'Por encomenda', 'out_of_stock' => 'Indisponível'] as $value => $label)<option value="{{ $value }}" @selected(old('availability', $product->availability ?: 'unknown') === $value)>{{ $label }}</option>@endforeach</select>@error('availability')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="admin-field full-field"><label for="description">Descrição <b>*</b></label><textarea id="description" name="description" rows="3" maxlength="2000" required>{{ old('description', $product->description) }}</textarea>@error('description')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="admin-field"><label for="illustration">Foto da peça <b>*</b></label><select id="illustration" name="illustration" required>@foreach (['boto-cinza' => 'Boto-cinza', 'sapinho-montanha' => 'Sapinho-da-montanha', 'tartaruga-verde' => 'Tartaruga-verde', 'caranguejo-uca' => 'Caranguejo-uçá', 'tamandua-mirim' => 'Tamanduá-mirim', 'tatu-galinha' => 'Tatu-galinha'] as $value => $label)<option value="{{ $value }}" @selected(old('illustration', $product->illustration ?: 'tamandua-mirim') === $value)>{{ $label }}</option>@endforeach</select>@error('illustration')<small class="field-error">{{ $message }}</small>@enderror</div>
        </div>
        <div class="form-section-heading form-section-next"><span>02</span><div><h2>Preços em reais (R$)</h2><p>Valores vazios serão tratados como “a consultar”.</p></div></div>
        <div class="admin-form-grid price-grid">
            <div class="admin-field"><label for="price">Preço por unidade</label><div class="input-prefix"><span>R$</span><input id="price" name="price" type="number" min="0" max="9999999.99" step="0.01" value="{{ old('price', $product->price) }}" placeholder="deixe vazio para consultar"></div>@error('price')<small class="field-error">{{ $message }}</small>@enderror</div>
            <div class="admin-field"><label for="personalization_price">Adicional de personalização por unidade</label><div class="input-prefix"><span>R$</span><input id="personalization_price" name="personalization_price" type="number" min="0" max="9999999.99" step="0.01" value="{{ old('personalization_price', $product->personalization_price) }}" placeholder="deixe vazio para consultar"></div>@error('personalization_price')<small class="field-error">{{ $message }}</small>@enderror</div>
        </div>
        <div class="form-section-heading form-section-next"><span>03</span><div><h2>Revisão do cadastro</h2><p>Marque como revisado somente após confirmação com a empresa.</p></div></div>
        <div class="admin-checkboxes">
            <input type="hidden" name="is_demo" value="0"><label><input type="checkbox" name="is_demo" value="1" @checked((bool) old('is_demo', $product->exists ? $product->is_demo : true))><span><strong>Cadastro a confirmar</strong><small>Use enquanto preço, descrição e disponibilidade ainda não foram confirmados com a empresa.</small></span></label>
            <input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $product->exists ? $product->is_active : true))><span><strong>Produto ativo</strong><small>Produtos ativos aparecem no site e no simulador de orçamento.</small></span></label>
        </div>
        @if ($errors->any())<div class="form-errors"><strong>Revise os campos sinalizados antes de salvar.</strong></div>@endif
        <div class="form-actions"><a class="admin-secondary-link" href="{{ route('admin.products.index') }}">Cancelar</a><button class="admin-primary-link" type="submit">{{ $product->exists ? 'Salvar alterações' : 'Cadastrar produto' }} <span>↗</span></button></div>
    </form>
@endsection
