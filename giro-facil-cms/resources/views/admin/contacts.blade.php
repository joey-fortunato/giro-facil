@extends('admin.layout')
@section('title', 'Contactos e horário · CMS Giro Fácil')
@section('page-heading', 'Contactos e horário')
@section('content')
<div class="admin-wrap">
    <div class="admin-title"><div><h1>Contactos e horário</h1><p class="hint">Altere aqui os dados que aparecem nas páginas, no cabeçalho e no rodapé do website.</p></div></div>
    @if(session('status'))<div class="admin-alert">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="admin-error" role="alert">Verifique os campos assinalados e tente novamente.</div>@endif
    <form method="post" action="{{ route('admin.contacts.update') }}" class="admin-panel">@csrf @method('put')
        <div class="admin-grid">
            <div class="admin-field"><label for="contact-email">Email de contacto</label><input id="contact-email" type="email" name="contact_email" value="{{ old('contact_email', $contacts['contact_email']) }}" required>@error('contact_email')<span class="admin-error">{{ $message }}</span>@enderror</div>
            <div class="admin-field"><label for="contact-phone">Telefone</label><input id="contact-phone" name="contact_phone" value="{{ old('contact_phone', $contacts['contact_phone']) }}" required>@error('contact_phone')<span class="admin-error">{{ $message }}</span>@enderror</div>
            <div class="admin-field"><label for="contact-whatsapp">WhatsApp</label><input id="contact-whatsapp" name="contact_whatsapp" value="{{ old('contact_whatsapp', $contacts['contact_whatsapp']) }}" required>@error('contact_whatsapp')<span class="admin-error">{{ $message }}</span>@enderror</div>
            <div class="admin-field"><label for="contact-address">Morada</label><input id="contact-address" name="contact_address" value="{{ old('contact_address', $contacts['contact_address']) }}" required>@error('contact_address')<span class="admin-error">{{ $message }}</span>@enderror</div>
            <div class="admin-field"><label for="contact-weekdays">Horário · segunda a sexta</label><input id="contact-weekdays" name="contact_hours_weekdays" value="{{ old('contact_hours_weekdays', $contacts['contact_hours_weekdays']) }}" required>@error('contact_hours_weekdays')<span class="admin-error">{{ $message }}</span>@enderror</div>
            <div class="admin-field"><label for="contact-weekend">Horário · sábado e domingo</label><input id="contact-weekend" name="contact_hours_weekend" value="{{ old('contact_hours_weekend', $contacts['contact_hours_weekend']) }}" required>@error('contact_hours_weekend')<span class="admin-error">{{ $message }}</span>@enderror</div>
        </div>
        <p class="hint">Estas informações são partilhadas pelo website para manter os contactos consistentes em todas as páginas.</p>
        <button class="admin-button gold">Guardar contactos</button>
    </form>
</div>
@endsection
