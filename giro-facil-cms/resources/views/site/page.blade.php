<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $page->meta_title ?: $page->name.' | '.($settings['site_name'] ?? 'Giro Fácil') }}</title>
    @if($page->meta_description)<meta name="description" content="{{ $page->meta_description }}">@endif
    <link rel="icon" href="/images/icon-girofacil.png" type="image/png">
    {!! $settings['head_assets'] ?? '' !!}
    <style>{!! $settings['extra_css'] ?? '' !!}
        {!! $pageCss !!}
        /* Títulos sobre fundos escuros mantêm contraste consistente com o design Giro Fácil. */
        #content > section#subheader h1,#content > section#subheader h2,#content > section#subheader h3,#content > section#subheader h4,
        #content > section#como-funciona h1,#content > section#como-funciona h2,#content > section#como-funciona h3,#content > section#como-funciona h4,
        #content > section.giro-why-section h1,#content > section.giro-why-section h2,#content > section.giro-why-section h3,#content > section.giro-why-section h4,
        #content > section.giro-final-cta h1,#content > section.giro-final-cta h2,#content > section.giro-final-cta h3,#content > section.giro-final-cta h4,
        #content > section.giro-dark-section h1,#content > section.giro-dark-section h2,#content > section.giro-dark-section h3,#content > section.giro-dark-section h4,
        #content > section.giro-gradient-section h1,#content > section.giro-gradient-section h2,#content > section.giro-gradient-section h3,#content > section.giro-gradient-section h4{color:#fff!important}
        #content #como-funciona .giro-process-title,
        #content .giro-why-section h2,
        #content .giro-final-cta-copy h2{color:#fff!important}
    </style>
</head>
<body>
<div id="wrapper">
    <a href="#" id="back-to-top"></a>
    <div id="de-loader"></div>
    {!! $settings['header_html'] ?? '' !!}
    <div class="no-bottom no-top" id="content">
        <div id="top"></div>
        @foreach($sections as $section)
            {!! $section->content !!}
        @endforeach
    </div>
    {!! $settings['footer_html'] ?? '' !!}
</div>
{!! $settings['extra_html'] ?? '' !!}
{!! $settings['scripts_html'] ?? '' !!}
<script src="/js/giro-active-nav.js" defer></script>
<script src="/js/site-forms.js" defer></script>
</body>
</html>
