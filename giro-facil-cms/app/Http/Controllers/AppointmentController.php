<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:160'],
            'telefone' => ['required', 'string', 'max:40'],
            'matricula' => ['required', 'string', 'max:30'],
            'servico' => ['required', 'string', Rule::in([
                'Inspecção periódica — Ligeiros',
                'Inspecção periódica — Pesados',
                'Inspecção de Reboques e Semi-reboques',
                'Reinspecção',
                'Emissão de segunda via da ficha',
            ])],
            'data' => ['required', 'date_format:d/m/Y'],
            'hora' => ['required', 'string', Rule::in([
                '08h00 — 10h00', '10h00 — 12h00', '12h00 — 14h00', '14h00 — 16h00', '16h00 — 18h00',
            ])],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ]);

        $appointment = Appointment::query()->create([
            'customer_name' => $data['nome'],
            'phone' => $data['telefone'],
            'license_plate' => strtoupper($data['matricula']),
            'service' => $data['servico'],
            'preferred_date' => CarbonImmutable::createFromFormat('!d/m/Y', $data['data'])->toDateString(),
            'preferred_time' => $data['hora'],
            'notes' => $data['observacoes'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pedido recebido. A equipa da Giro Fácil irá contactar para confirmar a marcação.',
                'reference' => $appointment->id,
            ], 201);
        }

        return redirect('/agendamento')->with('status', 'Pedido recebido. A equipa da Giro Fácil irá contactar para confirmar a marcação.');
    }
}
