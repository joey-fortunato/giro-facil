@extends('admin.layout')
@section('title', 'Agendamentos · CMS Giro Fácil')
@section('page-heading', 'Agendamentos')
@section('content')
<div class="admin-wrap">
    <div class="admin-title"><div><h1>Agendamentos</h1><p class="hint">Consulte os pedidos enviados pelo formulário do website e actualize o seu estado.</p></div></div>
    @if(session('status'))<div class="admin-alert">{{ session('status') }}</div>@endif
    <section class="admin-panel">
        @if(request()->filled('pedido'))<p><a href="{{ route('admin.appointments.index') }}">← Ver todos os pedidos</a></p>@endif
        <form method="get" class="admin-filter"><label for="status-filter">Mostrar</label><select id="status-filter" name="status" onchange="this.form.submit()"><option value="">Todos os pedidos</option>@foreach(['pending'=>'A aguardar contacto','contacted'=>'Contacto feito','confirmed'=>'Confirmado','completed'=>'Concluído','cancelled'=>'Cancelado'] as $value=>$label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select></form>
        @if($appointments->isEmpty())
            <div class="admin-empty"><strong>Ainda não há pedidos de marcação.</strong><p class="hint">Quando alguém enviar o formulário da página Agendamento, o pedido aparecerá aqui.</p></div>
        @else
            <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Cliente</th><th>Viatura e serviço</th><th>Data pretendida</th><th>Pedido recebido</th><th>Estado</th></tr></thead><tbody>
                @foreach($appointments as $appointment)
                    <tr><td><strong>{{ $appointment->customer_name }}</strong><br><a href="https://wa.me/{{ preg_replace('/\D+/', '', $appointment->phone) }}" target="_blank" rel="noopener">{{ $appointment->phone }}</a></td><td>{{ $appointment->license_plate }}<br><span class="hint">{{ $appointment->service }}</span>@if($appointment->notes)<details><summary>Ver observações</summary><span>{{ $appointment->notes }}</span></details>@endif</td><td>{{ $appointment->preferred_date->format('d/m/Y') }}<br><span class="hint">{{ $appointment->preferred_time }}</span></td><td>{{ $appointment->created_at->format('d/m/Y H:i') }}</td><td><form method="post" action="{{ route('admin.appointments.update', $appointment) }}">@csrf @method('patch')<select name="status" aria-label="Estado do pedido" onchange="this.form.submit()">@foreach(['pending'=>'A aguardar contacto','contacted'=>'Contacto feito','confirmed'=>'Confirmado','completed'=>'Concluído','cancelled'=>'Cancelado'] as $value=>$label)<option value="{{ $value }}" @selected($appointment->status === $value)>{{ $label }}</option>@endforeach</select><noscript><button class="admin-button secondary">Guardar</button></noscript></form></td></tr>
                @endforeach
            </tbody></table></div>
            <div class="admin-pagination">{{ $appointments->links() }}</div>
        @endif
    </section>
</div>
@endsection
