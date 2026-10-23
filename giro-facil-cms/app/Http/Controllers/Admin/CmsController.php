<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function dashboard(): View
    {
        $pendingAppointments = Appointment::query()->where('status', 'pending')->oldest()->take(4)->get();
        $nextAppointment = Appointment::query()
            ->whereDate('preferred_date', '>=', now('Africa/Luanda')->toDateString())
            ->where('status', 'confirmed')
            ->orderBy('preferred_date')
            ->orderBy('preferred_time')
            ->first();

        return view('admin.dashboard', [
            'pendingAppointmentCount' => Appointment::query()->where('status', 'pending')->count(),
            'pendingAppointments' => $pendingAppointments,
            'nextAppointment' => $nextAppointment,
            'recentRequestCount' => Appointment::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'contactedCount' => Appointment::query()->where('status', 'contacted')->count(),
            'confirmedCount' => Appointment::query()->where('status', 'confirmed')->count(),
            'recentActivity' => Appointment::query()->latest('updated_at')->take(8)->get(),
        ]);
    }

    public function pages(): View
    {
        return view('admin.pages', ['pages' => Page::withCount('sections')->orderBy('id')->get()]);
    }

    public function appointments(Request $request): View
    {
        $status = $request->query('status');
        $appointments = Appointment::query()
            ->when($request->integer('pedido') > 0, fn ($query) => $query->whereKey($request->integer('pedido')))
            ->when(in_array($status, ['pending', 'contacted', 'confirmed', 'completed', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.appointments', compact('appointments', 'status'));
    }

    public function updateAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,contacted,confirmed,completed,cancelled'],
        ]);
        $appointment->update($data);

        return back()->with('status', 'Estado do pedido actualizado.');
    }

    public function contacts(): View
    {
        $settings = SiteSetting::query()->pluck('value', 'key');
        $contacts = [
            'contact_email' => $settings['contact_email'] ?? 'geral@girofacil.ao',
            'contact_phone' => $settings['contact_phone'] ?? '+244 923 176 967',
            'contact_whatsapp' => $settings['contact_whatsapp'] ?? '+244 923 176 966',
            'contact_address' => $settings['contact_address'] ?? 'Talatona, Luanda - Angola',
            'contact_hours_weekdays' => $settings['contact_hours_weekdays'] ?? 'Segunda a sexta, 08h00–18h00',
            'contact_hours_weekend' => $settings['contact_hours_weekend'] ?? 'Sábado e domingo, 08h00–12h00',
        ];

        return view('admin.contacts', compact('contacts'));
    }

    public function updateContacts(Request $request): RedirectResponse
    {
        $contacts = $request->validate([
            'contact_email' => ['required', 'email', 'max:180'],
            'contact_phone' => ['required', 'string', 'max:40'],
            'contact_whatsapp' => ['required', 'string', 'max:40'],
            'contact_address' => ['required', 'string', 'max:255'],
            'contact_hours_weekdays' => ['required', 'string', 'max:160'],
            'contact_hours_weekend' => ['required', 'string', 'max:160'],
        ]);

        foreach ($contacts as $key => $value) {
            SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('status', 'Contactos e horário guardados.');
    }

    public function createPage(): View
    {
        return view('admin.page-create');
    }

    public function storePage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', 'unique:pages,slug'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $page = Page::query()->create($data);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Página criada. Adicione as secções e conteúdos.');
    }

    public function editPage(Page $page): View
    {
        return view('admin.page-edit', [
            'page' => $page->load('sections'),
            'assets' => MediaAsset::query()->latest()->get(),
        ]);
    }

    public function updatePage(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', 'unique:pages,slug,'.$page->id],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $page->update($data);

        return back()->with('status', 'Página actualizada.');
    }

    public function updateSection(Request $request, PageSection $section): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:2000000'],
            'is_visible' => ['nullable', 'boolean'],
        ]);
        $data['is_visible'] = $request->boolean('is_visible');
        $section->update($data);

        return back()->with('status', 'Secção guardada.');
    }

    public function createSection(Page $page): RedirectResponse
    {
        $page->sections()->create([
            'label' => 'Nova secção',
            'content' => '<section class="giro-about-section"><div class="container"><div class="row"><div class="col-12"><h2>Nova secção</h2><p>Escreva aqui o conteúdo desta secção.</p></div></div></div></section>',
            'position' => (int) $page->sections()->max('position') + 1,
        ]);

        return back()->with('status', 'Secção adicionada.');
    }

    public function deleteSection(PageSection $section): RedirectResponse
    {
        $section->delete();

        return back()->with('status', 'Secção removida.');
    }

    public function reorderSections(Request $request, Page $page): RedirectResponse
    {
        $order = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']])['order'];
        foreach ($order as $position => $id) {
            $page->sections()->whereKey($id)->update(['position' => $position]);
        }

        return back()->with('status', 'Ordem das secções actualizada.');
    }

    public function settings(): View
    {
        return view('admin.settings', [
            'settings' => SiteSetting::query()->pluck('value', 'key'),
            'assets' => MediaAsset::query()->latest()->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:120'],
            'header_html' => ['nullable', 'string', 'max:2000000'],
            'footer_html' => ['nullable', 'string', 'max:2000000'],
            'extra_html' => ['nullable', 'string', 'max:2000000'],
            'extra_css' => ['nullable', 'string', 'max:2000000'],
            'scripts_html' => ['nullable', 'string', 'max:2000000'],
        ]);

        foreach ($data as $key => $value) {
            SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value ?? '']);
        }

        return back()->with('status', 'Definições do site guardadas.');
    }

    public function uploadMedia(Request $request): RedirectResponse
    {
        $data = $request->validate(['media' => ['required', 'image', 'max:10240']]);
        $file = $data['media'];
        $path = $file->store('media', 'public');
        MediaAsset::query()->create([
            'name' => $file->getClientOriginalName(), 'path' => $path,
            'mime_type' => $file->getMimeType(), 'size' => $file->getSize(),
        ]);

        return back()->with('status', 'Imagem carregada: '.Storage::disk('public')->url($path));
    }

    public function deleteMedia(MediaAsset $asset): RedirectResponse
    {
        abort_unless($asset->is_managed, 403);
        Storage::disk('public')->delete($asset->path);
        $asset->delete();

        return back()->with('status', 'Imagem removida.');
    }
}
