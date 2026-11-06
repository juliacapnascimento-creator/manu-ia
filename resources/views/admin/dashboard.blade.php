@extends('admin.layout')

@section('title', 'Visão geral')

@section('content')
    <div class="page-kicker"><span></span> {{ now()->locale('pt_BR')->translatedFormat('l, d \d\e F') }}</div>
    <div class="page-title-row"><div><h1>Visão geral</h1><p>Acompanhe a atividade do protótipo Manu IA.</p></div><a class="admin-primary-link" href="{{ route('admin.products.create') }}">+ Cadastrar produto</a></div>
    <div class="admin-demo-banner"><span>✳</span><p><strong>Indicadores deste protótipo.</strong> Os gráficos usam conversas e simulações registradas. Orçamentos marcados como convertidos são status manuais, não compras confirmadas; compras reais ainda não são registradas. Nenhuma campanha ou mensagem automática está ativa.</p></div>
    <section class="metric-grid">
        <article class="metric-card metric-sage"><div class="metric-top"><span>Orçamentos enviados</span><span class="metric-icon">▤</span></div><strong>{{ $totalQuotes }}</strong><small>Total de simulações recebidas</small></article>
        <article class="metric-card metric-clay"><div class="metric-top"><span>Orçamentos pendentes</span><span class="metric-icon">▤</span></div><strong>{{ $pendingQuotes }}</strong><small>Simulações aguardando análise</small></article>
        <article class="metric-card metric-blue"><div class="metric-top"><span>Conversas</span><span class="metric-icon">♡</span></div><strong>{{ $conversations }}</strong><small>{{ $consentedConversations }} com novidades · {{ $conversationsWithInterests }} com temas de interesse</small></article>
        <article class="metric-card metric-cream"><div class="metric-top"><span>Orçamentos marcados como convertidos</span><span class="metric-icon">↗</span></div><strong>{{ $convertedQuotes }}</strong><small>Marcação manual; não confirma uma venda</small></article>
    </section>
    <div class="dashboard-insights-grid">
        <section class="admin-panel insight-panel">
            <div class="panel-heading"><div><h2>Atividade dos últimos 6 meses</h2><p>Contagem mensal de registros salvos neste protótipo.</p></div></div>
            <div class="activity-chart" role="img" aria-label="Simulações de orçamento e novas conversas por mês nos últimos seis meses">
                @foreach ($monthlyActivity as $month)
                    <div class="activity-month" data-month="{{ $month['key'] }}" data-quotes="{{ $month['quotes'] }}" data-conversations="{{ $month['conversations'] }}">
                        <div class="activity-bars">
                            <div class="activity-bar-group">
                                <span>{{ $month['quotes'] }}</span>
                                <div class="activity-bar quote-activity-bar" style="height: {{ max(2, (int) round($month['quotes'] / $maxMonthlyActivity * 100)) }}%"></div>
                            </div>
                            <div class="activity-bar-group">
                                <span>{{ $month['conversations'] }}</span>
                                <div class="activity-bar conversation-activity-bar" style="height: {{ max(2, (int) round($month['conversations'] / $maxMonthlyActivity * 100)) }}%"></div>
                            </div>
                        </div>
                        <small>{{ $month['label'] }}</small>
                    </div>
                @endforeach
            </div>
            <div class="activity-legend"><span><i class="quote-activity-bar"></i>Simulações</span><span><i class="conversation-activity-bar"></i>Conversas</span></div>
        </section>
        <section class="admin-panel insight-panel">
            <div class="panel-heading"><div><h2>Peças mais simuladas</h2><p>Ordenadas pela quantidade de orçamentos criados.</p></div><a href="{{ route('admin.quotes.index') }}">Orçamentos <span>→</span></a></div>
            @if ($topQuotedProducts->isEmpty())
                <div class="empty-state"><span>✿</span><strong>Nenhuma peça simulada ainda</strong><small>As peças aparecem aqui quando alguém calcula um orçamento.</small></div>
            @else
                <ol class="top-quoted-products">
                    @foreach ($topQuotedProducts as $quote)
                        <li><span class="product-rank">{{ $loop->iteration }}</span><span class="popular-product-name">{{ $quote->product->name }}</span><strong>{{ $quote->quote_count }} {{ (int) $quote->quote_count === 1 ? 'simulação' : 'simulações' }}</strong></li>
                    @endforeach
                </ol>
            @endif
            <p class="insight-disclaimer">Interesse demonstrado em simulações não significa compra.</p>
        </section>
    </div>
    <section class="admin-panel recent-panel"><div class="panel-heading"><div><h2>Orçamentos recentes</h2><p>As alterações aqui não enviam mensagens.</p></div><a href="{{ route('admin.quotes.index') }}">Ver todos <span>→</span></a></div>
        @if ($recentQuotes->isEmpty())<div class="empty-state"><span>▤</span><strong>Nenhuma simulação por enquanto</strong><small>Quando alguém enviar um orçamento pelo site, ele aparecerá aqui.</small></div>
        @else
            <div class="table-scroll"><table class="admin-table"><thead><tr><th>Referência</th><th>Peça</th><th>Quantidade</th><th>Data</th><th>Situação</th></tr></thead><tbody>
                @foreach ($recentQuotes as $quote)<tr><td><span class="quote-reference">#{{ substr($quote->public_id, 0, 8) }}</span>@if ($quote->is_demo)<span class="table-demo-tag">DEMO</span>@endif</td><td>{{ $quote->product->name }}</td><td>{{ $quote->quantity }}</td><td>{{ $quote->created_at->format('d/m/Y') }}</td><td><span class="status-pill status-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span></td></tr>@endforeach
            </tbody></table></div>
        @endif
    </section>
    <section class="admin-help"><div class="help-icon">✳</div><div><strong>Cadastre somente informações confirmadas</strong><p>Use o selo “dados revisados” apenas depois da ManuPekena validar preço, descrição e disponibilidade.</p></div><a href="{{ route('admin.products.index') }}">Abrir catálogo →</a></section>
@endsection
