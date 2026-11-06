<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Entrar no painel · Manu IA</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-body">
    <div class="login-wrap">
        <a class="admin-brand login-brand" href="{{ route('home') }}"><span class="admin-brand-mark">m</span><span><strong>manu<span>pekena</span></strong><small>PAINEL MANU IA</small></span></a>
        <section class="login-card"><div class="login-eyebrow"><span></span> ÁREA DA EQUIPE</div><h1>Boas-vindas<br>de <em>volta.</em></h1><p>Entre para acompanhar o catálogo e os orçamentos demonstrativos.</p>
            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <label for="email">E-mail</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
                <label for="password">Senha</label><input id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
                <button class="login-button" type="submit">Entrar no painel <span>↗</span></button>
            </form>
            <div class="login-note"><span>♧</span> Sem uma conta de equipe? Crie a primeira pelo terminal com <code>php artisan admin:create-user</code>.</div>
        </section>
        <a class="login-back" href="{{ route('home') }}">← voltar ao protótipo do site</a>
    </div>
</body>
</html>
