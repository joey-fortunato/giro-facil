@extends('admin.layout')
@section('title', 'Visão geral · CMS Giro Fácil')
@section('page-heading', 'Visão geral')
@section('content')
<div class="admin-wrap">
    <div class="admin-title"><div><h1>Visão geral</h1><p class="hint">Veja o que precisa de atenção e aceda rapidamente às tarefas principais.</p></div></div>
    @if(session('status'))<div class="admin-alert">{{ session('status') }}</div>@endif
    <section class="dashboard-priority {{ $pendingAppointmentCount > 0 ? 'needs-attention' : 'all-clear' }}" aria-live="polite">
        <span class="dashboard-priority-mark" aria-hidden="true">{{ $pendingAppointmentCount > 0 ? '!' : '✓' }}</span>
        <div class="dashboard-priority-copy"><span class="dashboard-eyebrow">{{ $pendingAppointmentCount > 0 ? 'Acção prioritária' : 'Tudo em dia' }}</span>
            <h2>{{ $pendingAppointmentCount > 0 ? $pendingAppointmentCount.' pedido(s) de marcação aguardam resposta' : 'Não há pedidos de marcação por tratar' }}</h2>
            <p>{{ $pendingAppointmentCount > 0 ? 'Reveja os dados e actualize o estado depois de contactar o cliente.' : 'Os novos pedidos recebidos pelo website aparecerão aqui.' }}</p>
        </div>
        <a class="admin-button {{ $pendingAppointmentCount > 0 ? 'gold' : 'secondary' }}" href="{{ route('admin.appointments.index', $pendingAppointmentCount > 0 ? ['status' => 'pending'] : []) }}">{{ $pendingAppointmentCount > 0 ? 'Ver pedidos pendentes' : 'Ver agendamentos' }}</a>
    </section>

    <div class="dashboard-insight-grid">
        <article class="dashboard-insight"><span class="dashboard-eyebrow">Próxima marcação confirmada</span>
            @if($nextAppointment)
                <strong>{{ $nextAppointment->preferred_date->format('d/m/Y') }}</strong><p>{{ $nextAppointment->preferred_time }} · {{ $nextAppointment->customer_name }}</p>
            @else
                <strong class="dashboard-insight-empty">Sem marcações próximas</strong><p>Pedidos futuros confirmados serão destacados aqui.</p>
            @endif
        </article>
        <a class="dashboard-insight" href="{{ route('admin.appointments.index') }}"><span class="dashboard-eyebrow">Pedidos recebidos · últimos 7 dias</span><strong>{{ $recentRequestCount }}</strong><p>Novos pedidos enviados pelo formulário de agendamento.</p></a>
        <a class="dashboard-insight" href="{{ route('admin.appointments.index', ['status' => 'contacted']) }}"><span class="dashboard-eyebrow">Contacto realizado</span><strong>{{ $contactedCount }}</strong><p>Pedidos contactados que ainda aguardam confirmação.</p></a>
        <a class="dashboard-insight" href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}"><span class="dashboard-eyebrow">Marcações confirmadas</span><strong>{{ $confirmedCount }}</strong><p>Pedidos atualmente no estado confirmado.</p></a>
    </div>

    @if($pendingAppointments->isNotEmpty())
        <div class="admin-title dashboard-subheading"><div><h2>Contactos por realizar</h2><p class="hint">Os pedidos que aguardam há mais tempo aparecem primeiro.</p></div><a class="admin-page-preview" href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">Ver todos <span aria-hidden="true">→</span></a></div>
        <section class="dashboard-pending-list" aria-label="Pedidos de marcação pendentes">
            @foreach($pendingAppointments as $appointment)
                <article class="dashboard-pending-item"><div><strong>{{ $appointment->customer_name }}</strong><span>{{ $appointment->service }} · {{ $appointment->license_plate }}</span></div><div class="dashboard-pending-date"><strong>{{ $appointment->preferred_date->format('d/m/Y') }}</strong><span>{{ $appointment->preferred_time }}</span></div><a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">Abrir pedido <span aria-hidden="true">↗</span></a></article>
            @endforeach
        </section>
    @endif

    <div class="admin-title dashboard-subheading"><div><h2>Actividade recente</h2><p class="hint">Última actualização de cada pedido: recepção, contacto, confirmação ou encerramento.</p></div></div>
    <section class="dashboard-pending-list" aria-label="Actividade recente do website">
        @forelse($recentActivity as $event)
            @php($stateLabel = ['pending' => 'Pedido recebido', 'contacted' => 'Contacto realizado', 'confirmed' => 'Marcação confirmada', 'completed' => 'Inspecção concluída', 'cancelled' => 'Pedido cancelado'][$event->status] ?? 'Pedido actualizado')
            <article class="dashboard-pending-item">
                <div><strong>{{ $stateLabel }} · {{ $event->customer_name }}</strong><span>{{ $event->service }} · {{ $event->license_plate }}</span></div>
                <div class="dashboard-pending-date"><strong>{{ $event->updated_at->timezone('Africa/Luanda')->format('d/m/Y') }}</strong><span>{{ $event->updated_at->timezone('Africa/Luanda')->format('H:i') }}</span></div>
                <a href="{{ route('admin.appointments.index', ['pedido' => $event->id]) }}">Ver pedido →</a>
            </article>
        @empty
            <div class="admin-empty"><strong>Ainda não há actividade registada.</strong><p>Os pedidos recebidos e as alterações de estado aparecerão aqui.</p></div>
        @endforelse
    </section>
    <p class="hint">Os contactos apresentados correspondem aos pedidos de marcação. O website ainda não disponibiliza cadastro público nem formulário de mensagens.</p>

</div>
@endsection

