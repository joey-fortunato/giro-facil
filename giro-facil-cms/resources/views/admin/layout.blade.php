<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CMS Giro Fácil')</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<div class="admin-shell @guest admin-guest @endguest">
    @auth
    <aside class="admin-sidebar" aria-label="Navegação principal do CMS">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}" aria-label="Giro Fácil CMS — início"><img src="/images/logo-giro-white.png" alt="Giro Fácil"></a>
        <div class="admin-sidebar-caption">Gestão do website</div>
        <nav class="admin-nav">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg><span>Visão geral</span></a>
            <a class="{{ request()->routeIs('admin.pages.*') || request()->routeIs('admin.sections.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h8l5 5v13H7z"/><path d="M15 3v6h5M10 13h7M10 17h7"/></svg><span>Páginas do site</span></a>
            <a class="{{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h3M8 17h7"/></svg><span>Agendamentos</span>@if(($pendingAppointmentsNav ?? 0) > 0)<span class="admin-nav-badge">{{ $pendingAppointmentsNav }}</span>@endif</a>
            <a class="{{ request()->routeIs('admin.contacts*') ? 'active' : '' }}" href="{{ route('admin.contacts') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6M7 16h4"/></svg><span>Contactos e horário</span></a>
            <a class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.7 2.94-.08-.02a1.7 1.7 0 0 0-1.77.49l-.05.07h-3.4l-.02-.08a1.7 1.7 0 0 0-1.37-1.23l-.08-.01-2.95-1.7.02-.08a1.7 1.7 0 0 0-.48-1.77l-.07-.05v-3.4l.08-.02a1.7 1.7 0 0 0 1.23-1.37l.01-.08 1.7-2.95.08.02a1.7 1.7 0 0 0 1.77-.48l.05-.07h3.4l.02.08a1.7 1.7 0 0 0 1.37 1.23l.08.01 2.95 1.7-.02.08a1.7 1.7 0 0 0 .48 1.77l.07.05z" transform="translate(-2 -2) scale(1.17)"/></svg><span>Conteúdos globais</span></a>
            <a href="{{ route('admin.settings') }}#biblioteca"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/></svg><span>Biblioteca de imagens</span></a>
        </nav>
        <div class="admin-sidebar-bottom"><nav class="admin-nav"><a class="admin-external" href="/" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14 3h7v7M10 14 21 3"/><path d="M19 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6"/></svg><span>Ver website</span></a></nav>
            <nav class="admin-nav"><form method="post" action="{{ route('admin.logout') }}">@csrf<button type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg><span>Terminar sessão</span></button></form></nav>
        </div>
    </aside>
    @endauth
    <div class="admin-main">
        @auth<header class="admin-topbar"><div class="admin-topbar-label">Giro Fácil <strong>@yield('page-heading', 'Gestão do website')</strong></div><div class="admin-user"><span class="admin-user-mark">GF</span><span>{{ auth()->user()->name }}</span></div></header>@endauth
        <div class="admin-content">@yield('content')</div>
    </div>
</div>
<script src="/js/admin-editor.js" defer></script>
</body>
</html>


