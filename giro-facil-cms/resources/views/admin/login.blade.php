@extends('admin.layout')
@section('title', 'Acesso ao CMS · Giro Fácil')
@section('content')
<main class="login-page">
    <aside class="login-brand-panel">
        <img src="/images/logo-giro-white.png" alt="Giro Fácil">
        <div class="login-brand-copy"><div class="login-brand-kicker">Painel de gestão</div><h2>O seu website.<br>Mais simples de gerir.</h2><p>Actualize páginas, acompanhe pedidos e mantenha as informações da Giro Fácil em dia.</p></div>
        <div class="login-brand-foot">Giro Fácil · Centro de Inspecção de Automóveis</div>
    </aside>
    <section class="login-form-panel"><div class="login-box">
        <h1>Iniciar sessão</h1><p>Aceda à área de gestão do website.</p>
        @if($errors->any())<div class="admin-alert" style="background:#fff0f0;color:#99232d">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('admin.login.store') }}">@csrf
            <div class="admin-field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></div>
            <div class="admin-field"><label for="password">Palavra-passe</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
            <label class="admin-check"><input type="checkbox" name="remember" value="1"> Manter sessão iniciada</label>
            <button class="admin-button" style="width:100%;margin-top:12px">Entrar</button>
        </form>
    </div></section>
</main>
@endsection
