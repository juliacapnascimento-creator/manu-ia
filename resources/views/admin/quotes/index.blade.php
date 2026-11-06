@extends('admin.layout')

@section('title', 'Orçamentos')

@section('content')
    <div class="page-kicker"><span></span> RELACIONAMENTO / ORÇAMENTOS</div>
    <div class="page-title-row"><div><h1>Orçamentos</h1><p>Acompanhe as simulações salvas pelo site e atualize a situação.</p></div></div>
    <div class="admin-demo-banner"><span>✳</span><p>Atualizar a situação é apenas um registro interno. Nenhuma notificação ou mensagem é enviada ao cliente.</p></div>
    <section class="admin-panel list-panel"><div class="panel-heading"><div><h2>Simulações recentes</h2><p>Exibindo até 100 registros mais recentes.</p></div></div>
        @if ($quotes->isEmpty())<div class="empty-state"><span>▤</span><strong>Nenhum orçamento ainda</strong><small>As simulações enviadas pelo site aparecerão nesta lista.</small></div>
        @else
            <div class="table-scroll"><table class="admin-table quote-table"><thead><tr><th>Referência</th><th>Peça</th><th>Qtd.</th><th>Personalização</th><th>Total</th><th>Finalidade</th><th>Data</th><th>Situação</th><th></th></tr></thead><tbody>
                @foreach ($quotes as $quote)
                    <tr><td><span class="quote-reference">#{{ substr($quote->public_id, 0, 8) }}</span>@if ($quote->is_demo)<span class="table-demo-tag">DEMO</span>@endif</td><td>{{ $quote->product->name }}</td><td>{{ $quote->quantity }}</td><td>{{ $quote->customization ? 'Sim' : 'Não' }}</td><td class="quote-total-cell">R$ {{ number_format((float) $quote->total, 2, ',', '.') }}</td><td>{{ ucfirst($quote->purpose) }}</td><td>{{ $quote->created_at->format('d/m/Y') }}</td><td><span class="status-pill status-{{ $quote->status }}">{{ ['pending' => 'Pendente', 'approved' => 'Em análise', 'converted' => 'Convertido', 'abandoned' => 'Encerrado'][$quote->status] ?? $quote->status }}</span></td><td><form class="status-form" method="POST" action="{{ route('admin.quotes.status', $quote) }}">@csrf @method('PATCH')<label class="sr-only" for="quote-status-{{ $quote->id }}">Atualizar situação</label><select id="quote-status-{{ $quote->id }}" name="status" aria-label="Atualizar situação para orçamento #{{ substr($quote->public_id, 0, 8) }}">@foreach (['pending' => 'Pendente', 'approved' => 'Em análise', 'converted' => 'Convertido', 'abandoned' => 'Encerrado'] as $value => $label)<option value="{{ $value }}" @selected($quote->status === $value)>{{ $label }}</option>@endforeach</select><button type="submit" title="Salvar situação">✓</button></form></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
@endsection
