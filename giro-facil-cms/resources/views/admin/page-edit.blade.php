@extends('admin.layout')
@section('title', 'Editar '.$page->name.' · CMS Giro Fácil')
@section('page-heading', 'Páginas do site')
@section('content')
<div class="admin-wrap">
    <p><a class="admin-page-preview" href="{{ route('admin.pages.index') }}">← Voltar às páginas</a></p>
    <div class="admin-title"><div><h1>{{ $page->name }}</h1><p class="hint">Abra uma secção, clique no texto para o editar e guarde. As alterações aparecem no site de imediato.</p></div><a class="admin-button secondary" href="{{ $page->slug === 'home' ? '/' : '/'.$page->slug }}" target="_blank" rel="noopener">Ver página publicada</a></div>
    @if(session('status'))<div class="admin-alert">{{ session('status') }}</div>@endif
    <details class="admin-panel page-settings" style="margin-bottom:24px"><summary style="color:#043081;font-weight:750;cursor:pointer">Definições da página <span class="hint">· nome, endereço e publicação</span></summary><div style="padding-top:16px">
        <form method="post" action="{{ route('admin.pages.update', $page) }}">@csrf @method('put')
            <div class="admin-grid"><div class="admin-field"><label>Nome no painel</label><input name="name" value="{{ old('name', $page->name) }}" required></div><div class="admin-field"><label>Endereço (slug)</label><input name="slug" value="{{ old('slug', $page->slug) }}" required></div><div class="admin-field"><label>Título para motores de busca</label><input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div><div class="admin-field"><label>Descrição para motores de busca</label><input name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"></div></div>
            <label class="admin-check"><input type="checkbox" name="is_published" value="1" @checked($page->is_published)> Página publicada</label><button class="admin-button">Guardar dados</button>
        </form>
    </div></details>
    <form id="section-order-form" method="post" action="{{ route('admin.sections.reorder', $page) }}" data-reorder>@csrf</form>
    <div class="admin-title"><div><h2 style="font-size:22px;color:#043081">Conteúdo da página <span class="hint">({{ $page->sections->count() }} secções)</span></h2><p class="hint">Cada linha corresponde a uma parte da página. Abra apenas a parte que pretende alterar.</p></div><button class="admin-button secondary" form="section-order-form">Guardar posições</button></div>
    <div id="section-list">
        @foreach($page->sections as $section)
            <article class="section-card" data-section="{{ $section->id }}">
                <div class="section-head"><div><h2>{{ $section->label }}</h2><span class="hint">{{ $section->is_visible ? 'Visível no website' : 'Oculta no website' }}</span></div><details class="section-organize"><summary>Organizar</summary><div class="section-tools"><button type="button" class="admin-button secondary" data-move="up" aria-label="Mover {{ $section->label }} para cima">Subir</button><button type="button" class="admin-button secondary" data-move="down" aria-label="Mover {{ $section->label }} para baixo">Descer</button></div></details></div>
        <details class="section-edit" data-section-editor><summary>Editar texto e imagens</summary>
                    <form method="post" action="{{ route('admin.sections.update', $section) }}" data-editor-form>@csrf @method('put')
                        <p class="hint section-editor-help">Clique diretamente no texto para escrever. Para trocar uma imagem, clique nela e escolha uma imagem da biblioteca.</p>
                        <details class="editor-formatting"><summary>Precisa de formatar o texto?</summary><div class="editor-toolbar">@foreach(['bold'=>'Negrito','italic'=>'Itálico','underline'=>'Sublinhar','insertUnorderedList'=>'Lista','createLink'=>'Inserir ligação','removeFormat'=>'Limpar formatação'] as $command=>$label)<button type="button" data-editor-command="section-{{ $section->id }}" data-command="{{ $command }}">{{ $label }}</button>@endforeach</div></details>
                        <iframe class="editor-frame" loading="lazy" title="Editor visual de {{ $section->label }}" data-visual-editor data-editor="section-{{ $section->id }}" data-content="{{ base64_encode($section->content) }}"></iframe>
                        <textarea hidden name="content" data-editor-source="section-{{ $section->id }}">{{ $section->content }}</textarea>
                        <label class="admin-check" style="padding:0 18px"><input type="checkbox" name="is_visible" value="1" @checked($section->is_visible)> Mostrar esta secção no website</label>
                        <details class="section-label-settings"><summary>Opções adicionais</summary><div class="admin-field"><label>Nome interno (usado apenas no CMS)</label><input name="label" value="{{ $section->label }}" required></div></details>
                        <div class="section-actions"><button class="admin-button gold">Guardar alterações</button></div>
                    </form>
                    <details class="section-delete"><summary>Remover esta secção</summary><form method="post" action="{{ route('admin.sections.delete', $section) }}" onsubmit="return confirm('Remover definitivamente a secção “{{ $section->label }}”?')">@csrf @method('delete')<p class="hint">Esta acção apaga a secção do site.</p><button class="admin-button danger" type="submit">Confirmar remoção</button></form></details>
                </details>
            </article>
        @endforeach
    </div>
    <form method="post" action="{{ route('admin.sections.create', $page) }}" style="margin:18px 0 48px">@csrf<button class="admin-button gold">+ Adicionar secção</button></form>
    <dialog class="media-picker-dialog" data-media-picker aria-labelledby="media-picker-title">
        <div class="media-picker-head"><div><h2 id="media-picker-title">Escolher uma imagem</h2><p class="hint">Seleccione uma imagem para substituir a que está na secção.</p></div><button class="admin-button secondary" type="button" data-media-close>Fechar</button></div>
        <div class="media-picker-grid">
            @foreach($assets as $asset)
                @php($assetUrl = $asset->is_managed ? \Illuminate\Support\Facades\Storage::disk('public')->url($asset->path) : asset($asset->path))
                <button class="media-picker-item" type="button" data-media-url="{{ $assetUrl }}" aria-label="Usar imagem {{ $asset->name }}"><img src="{{ $assetUrl }}" alt=""><span>{{ $asset->name }}</span></button>
            @endforeach
        </div>
        <form class="media-picker-upload" method="post" action="{{ route('admin.media.upload') }}" enctype="multipart/form-data">@csrf
            <div><strong>Precisa de outra imagem?</strong><p class="hint">Carregue-a aqui e ficará disponível na biblioteca.</p></div>
            <label class="admin-button secondary media-upload-label">Escolher ficheiro<input type="file" name="media" accept="image/*" required></label>
            <button class="admin-button gold" type="submit">Carregar imagem</button>
        </form>
    </dialog>
</div>
@endsection
