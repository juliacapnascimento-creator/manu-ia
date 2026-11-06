<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Conheça o protótipo de atendimento da ManuPekena, inspirado na fauna e na flora da Mata Atlântica.">
    <title>ManuPekena — pequenas esculturas, grandes encontros</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quote.css') }}">
    <link rel="stylesheet" href="{{ asset('css/photos-and-channels.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rating.css') }}">
    <script defer src="{{ asset('js/app.js') }}"></script>
</head>
<body data-chat-url="{{ route('chat.send') }}" data-quote-url="{{ route('quote.send') }}" data-chat-history-url="{{ url('/api/chat') }}" data-ai-enabled="{{ $aiEnabled ? 'true' : 'false' }}">
    <div class="demo-ribbon"><span class="ribbon-dot"></span> Peças em fotos reais fornecidas pela ManuPekena <span class="ribbon-divider">•</span> preços e disponibilidade precisam de confirmação <span class="ribbon-divider">•</span> sem mensagens para canais reais</div>

    <header class="site-header">
        <a class="brand" href="#inicio" aria-label="ManuPekena, início">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 44 44" fill="none"><path d="M22 37c0-9-2-16-8-22M22 36c1-9 4-15 10-21" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M14 15c-5-1-8-4-8-9 6 0 10 2 12 7M28 15c2-5 6-8 12-8 0 6-3 10-9 12M22 28c-4-1-7-4-7-8 5 0 8 2 9 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="brand-copy"><strong>manu<span>pekena</span></strong><small>feito com afeto, em biscuit</small></span>
        </a>
        <nav class="main-nav" aria-label="Navegação principal">
            <a href="#colecao">A coleção</a>
            <a href="#orcamento">Simular orçamento</a>
            <a href="#historia">Nossa inspiração</a>
        </nav>
        <a class="header-cta" href="#conversa">Fale com a Manu <span aria-hidden="true">↗</span></a>
    </header>

    <main id="inicio">
        <section class="hero-section">
            <div class="hero-copy">
                <div class="eyebrow"><span></span> UM PEDACINHO DA FLORESTA, FEITO À MÃO</div>
                <h1>A natureza<br>ganhou <em>forma.</em></h1>
                <p class="hero-lede">Pequenas esculturas de biscuit inspiradas nos encontros mais bonitos da Mata Atlântica.</p>
                <div class="hero-actions">
                    <a class="button button-dark" href="#colecao">Explorar a coleção <span aria-hidden="true">↓</span></a>
                    <span class="handmade-note"><span class="note-flower">✳</span> Cada peça tem seu próprio jeitinho</span>
                </div>
                <div class="hero-footnote"><span class="tiny-sun">✳</span> Foto real · dados do catálogo sujeitos a confirmação.</div>
            </div>

            <div class="hero-art" aria-label="Fotografia de uma peça real de biscuit">
                <div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div>
                <div class="hero-sticker sticker-top"><span>feito<br>à mão</span><i>✳</i></div>
                <div class="hero-plant plant-left"><i></i><i></i><i></i><i></i><b></b></div>
                <div class="hero-plant plant-right"><i></i><i></i><i></i><i></i><b></b></div>
                <img class="hero-real-photo" src="{{ asset('images/tatu-galinha.jpg') }}" alt="Peça de biscuit real de um tatu-galinha, fotografada na mão" fetchpriority="high">
                <div class="hero-caption"><span class="caption-line"></span><span><b>Tatu-galinha</b><small>peça real em biscuit</small></span></div>
                <div class="hero-sticker sticker-bottom">um carinho<br>da floresta <span>♡</span></div>
                <div class="art-caption-small">peça<br><b>real</b></div>
            </div>
            <div class="hero-index">01 <span>/</span> 06</div>
        </section>

        <section class="trust-strip" id="historia">
            <div><span class="strip-icon">✳</span><p><strong>Biscuit artesanal</strong><br>contexto fornecido para o projeto</p></div>
            <div><span class="strip-icon">⌁</span><p><strong>Fauna da Mata Atlântica</strong><br>inspiração para o catálogo</p></div>
            <div><span class="strip-icon">♡</span><p><strong>Atendimento simulado</strong><br>sem envio para redes sociais</p></div>
            <div class="strip-aside">catálogo<br><em>em confirmação.</em></div>
        </section>

        <section class="collection-section" id="colecao">
            <div class="section-heading"><div><div class="eyebrow"><span></span> PEÇAS COM HISTÓRIA</div><h2>Encontre seu <em>pedacinho.</em></h2></div><p>Uma coleção de pequenos encontros<br>com a natureza brasileira.</p></div>
            <div class="product-grid">
                @foreach ($products as $product)
                    <article class="product-card">
                        <div class="product-art art-{{ $product->illustration }}">
                            <span class="product-label">{{ $product->category === 'Coleção botânica' ? 'botânica' : 'fauna' }}</span>
                    <span class="demo-stamp">{{ $product->is_demo ? 'A CONFIRMAR' : 'REVISADO' }}</span>
                            <img class="product-photo" src="{{ asset('images/'.$product->illustration.'.jpg') }}" alt="Peça de biscuit real: {{ $product->name }}" loading="lazy">
                        </div>
                        <div class="product-meta"><div><h3>{{ $product->name }}</h3><p>{{ $product->species ?? 'peça decorativa' }} · feito à mão</p></div><span class="product-arrow" aria-hidden="true">↗</span></div>
                        <div class="product-price"><span>{{ $product->is_demo ? 'valor mostrado na captura' : 'preço cadastrado' }}</span><strong>{{ $product->price !== null ? 'R$ '.number_format((float) $product->price, 2, ',', '.') : 'consultar' }}</strong></div>
                    </article>
                @endforeach
            </div>
            <div class="catalog-note"><span>✳</span> As fotos são das peças reais enviadas pela ManuPekena. Os preços aparecem na captura fornecida; confirme valores, disponibilidade e prazos atualizados com a equipe.</div>
        </section>

        <section class="quote-section" id="orcamento">
            <div class="quote-intro">
                <div class="eyebrow"><span></span> UMA PRIMEIRA IDEIA</div>
                <h2>Vamos imaginar<br>um <em>orçamento?</em></h2>
                <p>Escolha uma peça das fotos enviadas e veja como a simulação é calculada. O valor de referência usa o preço mostrado na captura.</p>
                <div class="quote-rule"><span>01</span><p><strong>O cálculo é transparente</strong><br>quantidade × preço cadastrado</p></div>
                <div class="quote-rule"><span>02</span><p><strong>Personalização é opcional</strong><br>o valor precisa ser confirmado com a equipe</p></div>
            </div>
            <div class="quote-form-card">
                <div class="quote-card-heading"><span class="quote-icon">✳</span><div><h3>Simulador de orçamento</h3><p>Leva menos de um minuto</p></div><span class="quote-demo-badge">SIMULAÇÃO</span></div>
                <form id="quote-form">
                    <div class="form-field"><label for="quote-product">Peça</label><select id="quote-product" name="product_id" required><option value="" disabled selected>Escolha uma peça</option>@foreach ($products as $product)<option value="{{ $product->id }}" data-personalizable="{{ $product->personalization_price !== null ? 'true' : 'false' }}">{{ $product->name }}</option>@endforeach</select></div>
                    <div class="quote-fields-row"><div class="form-field"><label for="quote-quantity">Quantidade</label><input id="quote-quantity" name="quantity" type="number" value="1" min="1" max="30" required></div><div class="form-field"><label for="quote-purpose">Finalidade</label><select id="quote-purpose" name="purpose" required><option value="presentear">Presentear</option><option value="colecionar">Colecionar</option><option value="decorar">Decorar</option><option value="outro">Outra ideia</option></select></div></div>
                    <label class="customization-toggle"><input id="quote-customization" name="customization" type="checkbox"><span class="fake-checkbox"></span><span>Quero explorar uma personalização <i>(opcional)</i></span></label>
                    <div class="form-field customization-field" id="customization-field" hidden><label for="customization-note">O que você imaginou?</label><input id="customization-note" name="customization_note" maxlength="180" placeholder="Conte sua ideia em poucas palavras"></div>
                    <div class="form-field date-field"><label for="desired-date">Data desejada <i>(opcional)</i></label><input id="desired-date" name="desired_date" type="date"></div>
                    <button class="button quote-submit" type="submit"><span>Calcular simulação</span><span aria-hidden="true">↗</span></button>
                </form>
                <div class="quote-result" id="quote-result" aria-live="polite" hidden>
                    <div class="result-check">✓</div><div class="result-copy"><span class="result-kicker">ORÇAMENTO PENDENTE · DEMONSTRAÇÃO</span><h4 id="quote-result-title"></h4><p id="quote-result-detail"></p><strong id="quote-result-total"></strong><small id="quote-result-note"></small></div>
                </div>
                <p class="quote-disclaimer"><span>ⓘ</span> Não é uma proposta comercial. Preço, personalização e prazo reais precisam ser confirmados pela ManuPekena. Nenhuma compra é feita por aqui.</p>
            </div>
        </section>

        <section class="conversation-section" id="conversa">
            <div class="conversation-intro"><div class="eyebrow light-eyebrow"><span></span> UM PAPO COM A MANU</div><h2>Qual história<br>vamos <em>contar?</em></h2><p>Descubra uma peça, tire dúvidas ou imagine um presente. A Manu está por aqui para conversar.</p><div class="intro-note"><span>✳</span> Atendimento gentil, no seu tempo.</div><div class="intro-flower flower-large">✳</div></div>
            <section class="chat-card" aria-label="Chat demonstrativo com a Manu" data-channel="site">
                <div class="chat-topbar"><div class="chat-agent"><span class="agent-avatar"><svg viewBox="0 0 44 44" fill="none"><path d="M22 36c0-8-1-14-7-20M22 36c1-8 4-14 10-20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M15 17c-5 0-8-3-8-8 5 0 9 2 11 6m9 2c2-5 6-7 11-7 0 5-3 9-8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span><span><strong id="channel-agent-name">Manu</strong><small id="channel-agent-status"><i></i><span>por aqui agora</span></small></span></div><button class="new-chat-button" type="button" id="new-chat" title="Começar uma nova conversa" aria-label="Começar uma nova conversa"><svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button></div>
                <div class="channel-switch" role="tablist" aria-label="Canal simulado">
                    <button class="channel-tab active" type="button" role="tab" aria-selected="true" data-channel="site"><span class="channel-icon site-icon">⌂</span> Site</button>
                    <button class="channel-tab" type="button" role="tab" aria-selected="false" data-channel="whatsapp"><span class="channel-icon whatsapp-icon">◔</span> WhatsApp</button>
                    <button class="channel-tab" type="button" role="tab" aria-selected="false" data-channel="instagram"><span class="channel-icon instagram-icon">◎</span> Instagram</button>
                </div>
                <div class="channel-caption" id="channel-caption">Simulação de atendimento pelo site</div>
                <div class="chat-messages" id="chat-messages" aria-live="polite" aria-relevant="additions text">
                    <div class="date-separator"><span>hoje</span></div>
                    <div class="message-row assistant-row"><span class="message-avatar">m</span><div class="message-bubble assistant-bubble">Oi, que bom ter você por aqui! 🌿<br>O que você gostaria de conhecer?</div></div>
                    <div class="quick-prompts" id="quick-prompts"><button type="button" data-prompt="Quais peças tem no catálogo?">Ver as peças</button><button type="button" data-prompt="Me conta sobre o tatu-galinha">Conhecer o destaque</button><button type="button" data-prompt="Como faço para simular um orçamento?">Simular orçamento</button></div>
                </div>
                <div class="chat-foot">
                    <label class="consent-option"><input type="checkbox" id="marketing-consent"><span class="fake-checkbox"></span><span>Quero receber novidades <i>(simulado)</i></span></label>
                    <form class="chat-form" id="chat-form"><label class="sr-only" for="chat-input">Escreva sua mensagem</label><input id="chat-input" name="message" maxlength="800" autocomplete="off" placeholder="Escreva sua mensagem..." required><button id="send-button" type="submit" aria-label="Enviar mensagem"><svg viewBox="0 0 24 24" fill="none"><path d="M21 3 10.8 13.2M21 3l-6.5 18-3.7-7.8L3 9.5 21 3Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button></form>
                    <p class="chat-privacy"><span>♧</span> Protótipo: não compartilhe dados pessoais. Se a IA estiver ativa, a mensagem será processada pela OpenAI. <a href="#privacidade">Saiba mais</a></p>
                </div>
                <div class="chat-mode"><span class="mode-light {{ $aiEnabled ? 'mode-live' : '' }}"></span><span id="mode-label">{{ $aiEnabled ? 'IA conectada' : 'Modo demonstração' }}</span><span class="mode-separator">·</span> histórico compartilhado entre canais</div>
            </section>
        </section>

        <section class="rating-section" id="avaliacao" aria-labelledby="rating-title">
            <div class="rating-card">
                <div class="eyebrow">ANTES DE IR</div>
                <h2 id="rating-title">Como foi conversar com a Manu?</h2>
                <p>Escolha uma nota para ver uma avaliação de demonstração.</p>
                <form id="rating-form">
                    <fieldset class="rating-fieldset">
                        <legend>Sua nota, de 1 a 5 estrelas</legend>
                        <div class="rating-stars">
                            @for ($star = 1; $star <= 5; $star++)
                                <button type="button" data-rating="{{ $star }}" aria-label="{{ $star }} {{ $star === 1 ? 'estrela' : 'estrelas' }}" aria-pressed="false">★</button>
                            @endfor
                        </div>
                    </fieldset>
                    <button class="rating-submit" type="submit">Mostrar avaliação</button>
                </form>
                <p class="rating-result" id="rating-result" aria-live="polite" hidden></p>
                <small>Avaliação demonstrativa: sua resposta não é enviada nem salva.</small>
            </div>
        </section>
    </main>

    <footer class="site-footer" id="privacidade"><a class="footer-brand" href="#inicio">manu<span>pekena</span></a><p>Um carinho da Mata Atlântica, moldado à mão.</p><small>Protótipo acadêmico · confirme os dados do catálogo · mensagens não vão para canais reais · <a href="{{ route('admin.login') }}">Painel da equipe</a></small><a class="back-top" href="#inicio">Voltar ao topo ↑</a></footer>
</body>
</html>
