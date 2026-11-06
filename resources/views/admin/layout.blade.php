<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') · Manu IA</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-crm.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
</head>
<body class="admin-body">
    <header class="admin-topbar">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="admin-brand-mark">m</span><span><strong>manu<span>pekena</span></strong><small>PAINEL MANU IA</small></span></a>
        <div class="admin-top-right"><span class="prototype-badge"><i></i> protótipo</span><a href="{{ route('home') }}">Ver site <span>↗</span></a><span class="admin-user">{{ auth()->user()->name }}</span><form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="logout-button">Sair</button></form></div>
    </header>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <span class="sidebar-label">ESPAÇO DE TRABALHO</span>
            <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'selected' : '' }}" href="{{ route('admin.dashboard') }}"><span>⌂</span> Visão geral</a>
            <a class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'selected' : '' }}" href="{{ route('admin.products.index') }}"><span>✿</span> Catálogo</a>
            <a class="sidebar-link {{ request()->routeIs('admin.conversations.*') ? 'selected' : '' }}" href="{{ route('admin.conversations.index') }}"><span>♡</span> Conversas</a>
            <a class="sidebar-link {{ request()->routeIs('admin.quotes.*') ? 'selected' : '' }}" href="{{ route('admin.quotes.index') }}"><span>▤</span> Orçamentos</a>
            <div class="sidebar-note"><span>✳</span><p>Confirme os dados com a ManuPekena antes de marcá-los como revisados.</p></div>
            <div class="sidebar-bottom"><span>AMBIENTE</span><strong>DEMONSTRAÇÃO</strong></div>
        </aside>
        <main class="admin-main">
            <div class="admin-content">
                @if (session('status'))<div class="admin-flash" role="status"><span>✓</span>{{ session('status') }}</div>@endif
                @yield('content')
            </div>
            <footer class="admin-footer">Manu IA · painel demonstrativo · ações do painel não enviam mensagens para clientes</footer>
        </main>
    </div>
</body>
</html>
