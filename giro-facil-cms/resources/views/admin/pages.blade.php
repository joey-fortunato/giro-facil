@extends('admin.layout')
@section('title', 'Páginas · CMS Giro Fácil')
@section('page-heading', 'Páginas do site')
@section('content')
<div class="admin-wrap">
    @if(session('status'))<div class="admin-alert">{{ session('status') }}</div>@endif
    <div class="admin-title" id="paginas"><div><h1>Páginas do site</h1><p class="hint">Escolha uma página para editar o que os visitantes veem.</p></div><a href="{{ route('admin.pages.create') }}" class="admin-button gold">Criar página</a></div>
    <section class="admin-pages-list" aria-label="Páginas disponíveis para edição">
        @forelse($pages as $page)
            @php($pageUrl = $page->slug === 'home' ? '/' : '/'.$page->slug)
            <article class="admin-page-item">
                <div class="admin-page-identity"><span class="admin-page-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h8l5 5v13H6z"/><path d="M14 3v6h5M9 13h7M9 17h7"/></svg></span>
                    <div><h3 class="admin-page-name">{{ $page->name }}</h3><span class="admin-page-url">{{ $pageUrl }}</span></div>
                </div>
                <div class="admin-page-details"><span class="admin-page-sections">{{ $page->sections_count }} {{ $page->sections_count === 1 ? 'secção' : 'secções' }}</span><span class="admin-page-status {{ $page->is_published ? 'is-published' : 'is-draft' }}"><i aria-hidden="true"></i>{{ $page->is_published ? 'Publicada' : 'Rascunho' }}</span></div>
                <div class="admin-page-actions"><a class="admin-button" href="{{ route('admin.pages.edit', $page) }}">Editar conteúdo</a><a class="admin-page-preview" href="{{ $pageUrl }}" target="_blank" rel="noopener">Pré-visualizar<span aria-hidden="true">↗</span></a></div>
            </article>
        @empty
            <div class="admin-empty"><strong>As páginas ainda não estão disponíveis.</strong><p>Quando forem preparadas, poderá editar os textos e imagens nesta área.</p></div>
        @endforelse
    </section>
</div>
@endsection

