<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncLegacyPageStyles extends Command
{
    protected $signature = 'cms:sync-page-styles {--source= : Pasta que contém os ficheiros HTML originais}';
    protected $description = 'Separa os estilos inline do template por página para evitar conflitos entre secções';

    private array $pages = [
        'index-ship-freight.html' => 'home',
        'about.html' => 'sobre-nos',
        'services.html' => 'inspecoes',
        'booking.html' => 'agendamento',
        'faq.html' => 'faq',
        'contact.html' => 'contactos',
    ];

    public function handle(): int
    {
        $source = rtrim($this->option('source') ?: dirname(base_path()), DIRECTORY_SEPARATOR);
        $aggregate = [];
        $count = 0;

        foreach ($this->pages as $file => $slug) {
            $path = $source.DIRECTORY_SEPARATOR.$file;
            if (! File::exists($path)) {
                $this->warn("Página não encontrada, ignorada: {$path}");
                continue;
            }

            $dom = new DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="UTF-8">'.File::get($path), LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            $xpath = new DOMXPath($dom);
            $styles = [];
            foreach ($xpath->query('//style') as $style) {
                $css = preg_replace('/url\((\s*["\']?)images\//i', 'url($1/images/', $style->textContent);
                if (trim($css) !== '') {
                    $styles[] = $css;
                    $aggregate[] = $css;
                }
            }

            SiteSetting::query()->updateOrCreate(
                ['key' => 'page_css_'.$slug],
                ['value' => implode("\n", $styles)],
            );
            $this->info("{$slug}: estilos separados por página (".strlen(implode("\n", $styles))." caracteres).");
            $count++;
        }

        $legacyAggregate = implode("\n", $aggregate);
        $existingGlobalCss = SiteSetting::query()->where('key', 'extra_css')->value('value');
        if ($existingGlobalCss !== null && hash_equals($legacyAggregate, $existingGlobalCss)) {
            SiteSetting::query()->where('key', 'extra_css')->update(['value' => '']);
            $this->info('CSS agregado antigo removido de extra_css; estilos globais manuais preservados.');
        } elseif ($existingGlobalCss !== null && trim($existingGlobalCss) !== '') {
            $this->warn('extra_css contém alterações diferentes do CSS importado; foi preservado.');
        }

        $this->info("{$count} páginas sincronizadas.");
        return self::SUCCESS;
    }
}
