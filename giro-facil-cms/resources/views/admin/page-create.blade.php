@extends('admin.layout')
@section('title', 'Nova página · CMS Giro Fácil')
@section('content')
<div class="admin-wrap">
    <div class="admin-title"><div><h1>Nova página</h1><p class="hint">Crie uma página e adicione secções no editor visual.</p></div></div>
    <section class="admin-panel"><form method="post" action="{{ route('admin.pages.store') }}">@csrf
        <div class="admin-grid"><div class="admin-field"><label>Nome da página</label><input name="name" value="{{ old('name') }}" required placeholder="Ex.: Notícias e dicas"></div><div class="admin-field"><label>Endereço (slug)</label><input name="slug" value="{{ old('slug') }}" required placeholder="noticias-e-dicas"></div><div class="admin-field"><label>Título para motores de busca</label><input name="meta_title" value="{{ old('meta_title') }}"></div><div class="admin-field"><label>Descrição para motores de busca</label><input name="meta_description" value="{{ old('meta_description') }}"></div></div>
        <label class="admin-check"><input type="checkbox" name="is_published" value="1"> Publicar imediatamente</label><button class="admin-button">Criar página</button>
    </form></section>
</div>
@endsection
