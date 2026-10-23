<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class ImportLegacySiteContent extends Command
{
    protected $signature = 'cms:import-legacy {--source= : Pasta que contém os ficheiros HTML e as pastas css/js/images} {--force : Substitui os conteúdos já importados}';
    protected $description = 'Importa as páginas HTML Giro Fácil para o CMS';

    private array $pages = [
        ['file' => 'index-ship-freight.html', 'slug' => 'home', 'name' => 'Início'],
        ['file' => 'about.html', 'slug' => 'sobre-nos', 'name' => 'Sobre Nós'],
        ['file' => 'services.html', 'slug' => 'inspecoes', 'name' => 'Inspecções'],
        ['file' => 'booking.html', 'slug' => 'agendamento', 'name' => 'Agendamento'],
        ['file' => 'faq.html', 'slug' => 'faq', 'name' => 'FAQ'],
        ['file' => 'contact.html', 'slug' => 'contactos', 'name' => 'Contactos'],
    ];

    public function handle(): int
    {
        $source = $this->option('source') ?: dirname(base_path());
        $source = rtrim($source, DIRECTORY_SEPARATOR);
        $force = (bool) $this->option('force');
        $documents = [];

        foreach ($this->pages as $definition) {
            $path = $source.DIRECTORY_SEPARATOR.$definition['file'];
            if (! File::exists($path)) {
                $this->warn("Página não encontrada, ignorada: {$path}");
                continue;
            }

            $html = File::get($path);
            $document = $this->parse($html);
            $documents[] = ['slug' => $definition['slug'], 'document' => $document];
            $page = Page::query()->firstOrNew(['slug' => $definition['slug']]);
            $alreadyImported = $page->exists && $page->sections()->exists();

            if ($alreadyImported && ! $force) {
                $this->warn("{$definition['name']}: já contém secções; use --force para substituir.");
                continue;
            }

            $page->fill([
                'name' => $definition['name'],
                'meta_title' => $this->text($document->xpath, '//title'),
                'meta_description' => $this->metaDescription($document->xpath),
                'is_published' => true,
            ])->save();

            if ($alreadyImported) {
                $page->sections()->delete();
            }

            $content = $document->xpath->query('//*[@id="content"]')->item(0);
            $position = 0;
            if ($content) {
                foreach ($content->childNodes as $node) {
                    if (! $node instanceof DOMElement || strtolower($node->tagName) !== 'section') {
                        continue;
                    }
                    $sectionHtml = $this->normalize($document->dom->saveHTML($node));
                    $label = $this->sectionLabel($document->xpath, $node, ++$position);
                    $page->sections()->create([
                        'label' => $label,
                        'content' => $sectionHtml,
                        'position' => $position,
                        'is_visible' => true,
                    ]);
                }
            }

            $this->info("{$definition['name']}: {$position} secções importadas.");
        }

        $home = $documents[0]['document'] ?? null;
        if ($home) {
            $this->saveSetting('header_html', $this->nodeHtml($home, '//header'), $force);
            $footer = str_replace('href="#marcar-inspecao"', 'href="/agendamento"', $this->nodeHtml($home, '//footer'));
            $this->saveSetting('footer_html', $footer, $force);
            $this->saveSetting('extra_html', $this->nodeHtml($home, '//*[@id="extra-wrap"]'), $force);

            $headLinks = [];
            $stylesByPage = [];
            $scripts = [];
            foreach ($documents as $entry) {
                $document = $entry['document'];
                foreach ($document->xpath->query('//link') as $link) {
                    if (! $link instanceof DOMElement) continue;
                    $href = $link->getAttribute('href');
                    if ($href !== '') $link->setAttribute('href', $this->assetUrl($href));
                    $tag = $this->normalize($document->dom->saveHTML($link));
                    if (! in_array($tag, $headLinks, true)) $headLinks[] = $tag;
                }
                foreach ($document->xpath->query('//style') as $style) {
                    $stylesByPage[$entry['slug']][] = $this->normalize($style->textContent);
                }
                foreach ($document->xpath->query('//body//script') as $script) {
                    if (! $script instanceof DOMElement) continue;
                    if ($script->hasAttribute('src')) {
                        $src = $this->assetUrl($script->getAttribute('src'));
                        if (! in_array($src, array_column($scripts, 'src'), true)) $scripts[] = ['src' => $src];
                    } elseif (trim($script->textContent) !== '') {
                        $scripts[] = ['inline' => $script->textContent];
                    }
                }
            }
            $scriptHtml = implode("\n", array_map(fn ($script) => isset($script['src'])
                ? '<script src="'.e($script['src']).'"></script>'
                : '<script>'.$script['inline'].'</script>', $scripts));
            $this->saveSetting('head_assets', implode("\n", $headLinks), $force);
            foreach ($stylesByPage as $slug => $styles) {
                $this->saveSetting('page_css_'.$slug, implode("\n", $styles), $force);
            }
            $this->saveSetting('scripts_html', $scriptHtml, $force);
            $this->saveSetting('site_name', 'Giro Fácil', $force);
        }

        $this->syncTemplateImages();

        return self::SUCCESS;
    }

    private function parse(string $html): object
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return (object) ['dom' => $dom, 'xpath' => new DOMXPath($dom)];
    }

    private function text(DOMXPath $xpath, string $query): ?string
    {
        $value = trim($xpath->query($query)->item(0)?->textContent ?? '');
        return $value !== '' ? $value : null;
    }

    private function metaDescription(DOMXPath $xpath): ?string
    {
        $node = $xpath->query('//meta[@name="description"]')->item(0);
        return $node instanceof DOMElement ? $node->getAttribute('content') : null;
    }

    private function sectionLabel(DOMXPath $xpath, DOMElement $section, int $position): string
    {
        $heading = $xpath->query('.//h1|.//h2|.//h3|.//h4', $section)->item(0)?->textContent;
        $label = preg_replace('/\s+/u', ' ', trim($heading ?: $section->getAttribute('aria-label')));
        return $label !== '' ? mb_substr($label, 0, 120) : "Secção {$position}";
    }

    private function nodeHtml(object $document, string $query): string
    {
        $node = $document->xpath->query($query)->item(0);
        return $node ? $this->normalize($document->dom->saveHTML($node)) : '';
    }

    private function normalize(string $html): string
    {
        $html = preg_replace_callback('/\b(src|href)=("|\')(?!https?:|mailto:|tel:|#|\/)([^"\']+)\2/i', function ($match) {
            $url = $this->assetUrl($match[3]);
            return $match[1].'='.$match[2].$url.$match[2];
        }, $html);
        $html = preg_replace('/url\((\s*["\']?)images\//i', 'url($1/images/', $html);
        $html = str_replace('href="#top"', 'href="/#top"', $html);
        $routes = [
            'index-ship-freight.html' => '/', 'about.html' => '/sobre-nos', 'services.html' => '/inspecoes',
            'booking.html' => '/agendamento', 'faq.html' => '/faq', 'contact.html' => '/contactos',
        ];
        foreach ($routes as $old => $new) {
            $html = str_replace('href="'.$old, 'href="'.$new, $html);
            $html = str_replace('href="/'.$old, 'href="'.$new, $html);
        }
        return $html;
    }

    private function assetUrl(string $url): string
    {
        if (preg_match('/^(?:https?:)?\/\//i', $url) || str_starts_with($url, '/') || str_starts_with($url, 'data:')) {
            return $url;
        }
        $url = ltrim($url, './');
        return '/'.str_replace(' ', '%20', $url);
    }

    private function saveSetting(string $key, string $value, bool $force): void
    {
        if ($force || ! SiteSetting::query()->where('key', $key)->exists()) {
            SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function syncTemplateImages(): void
    {
        $directory = public_path('images');
        if (! File::isDirectory($directory)) return;
        $extensions = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'];
        foreach (File::allFiles($directory) as $file) {
            if (! in_array(strtolower($file->getExtension()), $extensions, true)) continue;
            $relative = 'images/'.str_replace('\\', '/', $file->getRelativePathname());
            MediaAsset::query()->updateOrCreate(['path' => $relative], [
                'name' => $file->getFilename(), 'mime_type' => File::mimeType($file->getPathname()),
                'size' => $file->getSize(), 'is_managed' => false,
            ]);
        }
    }
}
