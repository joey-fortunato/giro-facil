<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\View\View;

class SitePageController extends Controller
{
    private const ALIASES = [
        '' => 'home', '/' => 'home', 'index.html' => 'home', 'index-ship-freight.html' => 'home',
        'about' => 'sobre-nos', 'about.html' => 'sobre-nos', 'services' => 'inspecoes', 'services.html' => 'inspecoes',
        'booking' => 'agendamento', 'booking.html' => 'agendamento', 'faq.html' => 'faq',
        'contact' => 'contactos', 'contact.html' => 'contactos',
    ];

    public function show(string $path = ''): View
    {
        $slug = self::ALIASES[$path] ?? $path;
        $page = Page::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();
        $sections = $page->sections()->where('is_visible', true)->get();
        $settings = SiteSetting::query()->pluck('value', 'key');
        $pageCss = $settings['page_css_'.$page->slug] ?? '';

        $phone = $settings['contact_phone'] ?? '+244 923 176 967';
        $whatsapp = $settings['contact_whatsapp'] ?? '+244 923 176 966';
        $email = $settings['contact_email'] ?? 'geral@girofacil.ao';
        $address = $settings['contact_address'] ?? 'Talatona, Luanda - Angola';
        $weekdayHours = $settings['contact_hours_weekdays'] ?? 'Segunda a sexta, 08h00–18h00';
        $weekendHours = $settings['contact_hours_weekend'] ?? 'Sábado e domingo, 08h00–12h00';
        $replacements = [
            'geral@girofacil.ao' => e($email),
            '+244 923 176 967' => e($phone),
            '+244 923 176 966' => e($whatsapp),
            '244923176967' => preg_replace('/\\D+/', '', $phone),
            '244923176966' => preg_replace('/\\D+/', '', $whatsapp),
            'Talatona, Luanda - Angola' => e($address),
            'Segunda a sexta, 08h00–18h00' => e($weekdayHours),
            'Sábado e domingo, 08h00–12h00' => e($weekendHours),
        ];
        $sections->each(function ($section) use ($replacements): void {
            $section->content = str_replace(array_keys($replacements), array_values($replacements), $section->content);
        });
        foreach (['header_html', 'footer_html', 'extra_html'] as $key) {
            if (isset($settings[$key])) {
                $settings[$key] = str_replace(array_keys($replacements), array_values($replacements), $settings[$key]);
            }
        }

        return view('site.page', compact('page', 'sections', 'settings', 'pageCss'));
    }
}
