@extends('admin.layout')

@section('title', 'Histórico de conversa')

@section('content')
    <div class="page-kicker"><span></span> RELACIONAMENTO</div>
    <div class="page-title-row"><div><h1>Histórico</h1><p>Conversa {{ substr($conversation->public_id, 0, 8) }} · atividade {{ $conversation->last_message_at?->format('d/m/Y H:i') ?? 'não registrada' }}</p></div><a class="admin-back-link" href="{{ route('admin.conversations.index') }}">← Todas as conversas</a></div>
    <div class="admin-demo-banner"><span>♡</span><p>Preferência de novidades: <strong>{{ $conversation->marketing_consent ? 'aceita' : 'não aceita' }}</strong>. Para este protótipo, nenhuma comunicação externa é enviada.</p></div>
    <section class="admin-panel transcript-panel"><div class="panel-heading"><div><h2>Mensagens</h2><p>Conteúdo fornecido no chat demonstrativo.</p></div></div>
        @forelse ($conversation->messages as $message)
            <article class="transcript-message {{ $message->role === 'assistant' ? 'assistant-message' : 'visitor-message' }}"><div><strong>{{ $message->role === 'assistant' ? 'Manu IA' : 'Visitante' }}</strong><small>{{ ucfirst($message->channel) }} · {{ $message->created_at->format('d/m/Y H:i') }}</small></div><p>{{ $message->content }}</p></article>
        @empty
            <div class="empty-state"><strong>Esta conversa ainda não tem mensagens</strong></div>
        @endforelse
    </section>
    <section class="admin-panel list-panel conversation-quotes"><div class="panel-heading"><div><h2>Orçamentos ligados</h2><p>Simulações associadas a esta conversa.</p></div></div>
        @forelse ($conversation->quotes as $quote)
            <div class="linked-quote"><span>#{{ substr($quote->public_id, 0, 8) }} @if($quote->is_demo)<small>DEMO</small>@endif</span><strong>{{ $quote->product->name }} · {{ $quote->quantity }} un.</strong><strong>R$ {{ number_format((float) $quote->total, 2, ',', '.') }}</strong><span class="status-pill status-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span></div>
        @empty
            <div class="empty-state"><strong>Sem orçamentos associados</strong></div>
        @endforelse
    </section>
@endsection
