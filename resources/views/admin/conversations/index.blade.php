@extends('admin.layout')

@section('title', 'Conversas')

@section('content')
    <div class="page-kicker"><span></span> RELACIONAMENTO</div>
    <div class="page-title-row"><div><h1>Conversas</h1><p>Histórico compartilhado dos canais simulados e preferência de novidades.</p></div></div>
    <div class="admin-demo-banner"><span>♡</span><p><strong>Privacidade em primeiro lugar.</strong> As conversas são demonstrativas e não incluem nome ou contato. Só conversas com consentimento registrado poderiam receber novidades; nenhum envio está conectado.</p></div>
    <section class="admin-panel list-panel">
        <div class="conversation-filters" aria-label="Filtrar conversas">
            <a class="{{ $consentFilter === 'all' ? 'active' : '' }}" href="{{ route('admin.conversations.index') }}">Todas</a>
            <a class="{{ $consentFilter === 'yes' ? 'active' : '' }}" href="{{ route('admin.conversations.index', ['consent' => 'yes']) }}">Aceitaram novidades</a>
            <a class="{{ $consentFilter === 'no' ? 'active' : '' }}" href="{{ route('admin.conversations.index', ['consent' => 'no']) }}">Sem consentimento</a>
        </div>
        @if ($conversations->isEmpty())
            <div class="empty-state"><span>♡</span><strong>Nenhuma conversa neste filtro</strong><small>Quando alguém usar o chat, o histórico aparecerá aqui.</small></div>
        @else
            <div class="table-scroll"><table class="admin-table conversation-table"><thead><tr><th>Referência</th><th>Canal recente</th><th>Mensagens</th><th>Orçamentos</th><th>Novidades</th><th>Atividade</th><th></th></tr></thead><tbody>
                @foreach ($conversations as $conversation)
                    <tr><td><span class="quote-reference">{{ substr($conversation->public_id, 0, 8) }}</span></td><td>{{ ucfirst($conversation->channel) }}</td><td>{{ $conversation->messages_count }}</td><td>{{ $conversation->quotes_count }}</td><td><span class="consent-pill {{ $conversation->marketing_consent ? 'consent-yes' : 'consent-no' }}">{{ $conversation->marketing_consent ? 'Aceitou' : 'Não aceitou' }}</span></td><td>{{ $conversation->last_message_at?->format('d/m/Y H:i') ?? '—' }}</td><td><a class="row-actions" href="{{ route('admin.conversations.show', $conversation) }}">Ver histórico →</a></td></tr>
                @endforeach
            </tbody></table></div>
            <div class="pagination-wrap">{{ $conversations->links() }}</div>
        @endif
    </section>
@endsection
