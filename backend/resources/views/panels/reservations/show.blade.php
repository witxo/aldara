@extends('layouts.panel')
@section('title', "Reserva {$reservation->code}")
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold">{{ $reservation->guest_name }}</h3>
                    <p class="text-sm text-gray-500">Código: {{ $reservation->code }}</p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full
                    @if($reservation->status === 'confirmed') bg-blue-100 text-blue-700
                    @elseif($reservation->status === 'checkin_sent') bg-yellow-100 text-yellow-700
                    @elseif($reservation->status === 'completed') bg-green-100 text-green-700
                    @elseif($reservation->status === 'cancelled') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-700 @endif">{{ $reservation->status }}</span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Alojamiento:</span> {{ $reservation->property->name ?? '—' }}</div>
                <div><span class="text-gray-500">Origen:</span> {{ $reservation->source }}</div>
                <div><span class="text-gray-500">Entrada:</span> {{ $reservation->checkin_date->format('d/m/Y') }}</div>
                <div><span class="text-gray-500">Salida:</span> {{ $reservation->checkout_date->format('d/m/Y') }}</div>
                <div><span class="text-gray-500">Adultos:</span> {{ $reservation->adults }}</div>
                <div><span class="text-gray-500">Menores:</span> {{ $reservation->children }}</div>
                <div><span class="text-gray-500">Email:</span> {{ $reservation->guest_email ?? '—' }}</div>
                <div><span class="text-gray-500">Teléfono:</span> {{ $reservation->guest_phone ?? '—' }}</div>
            </div>
            @if($reservation->notes)
                <div class="mt-4 p-3 bg-gray-50 rounded text-sm">{{ $reservation->notes }}</div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-semibold">Huéspedes ({{ $reservation->guests->count() }})</h4>
                <a href="{{ route('guests.create', ['reservation_id' => $reservation->id]) }}" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-blue-700">+ Añadir huésped</a>
            </div>
            @if($reservation->guests->count() > 0)
                <div class="space-y-3">
                    @foreach($reservation->guests as $guest)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div>
                            <p class="font-medium">{{ $guest->first_name }} {{ $guest->last_name }}
                                @if($guest->is_main_guest)<span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded ml-1">Principal</span>@endif
                            </p>
                            <p class="text-sm text-gray-500">{{ $guest->document_type }}: {{ substr($guest->document_number, -4) }}... (cifrado) · {{ $guest->nationality }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('guests.edit', $guest) }}" class="text-blue-600 text-xs hover:underline">Editar</a>
                            <form method="POST" action="{{ route('guests.destroy', $guest) }}" onsubmit="return confirm('¿Eliminar este huésped?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 text-xs hover:underline">Eliminar</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No hay huéspedes registrados</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Check-ins ({{ $reservation->checkins->count() }})</h4>
            @forelse($reservation->checkins as $checkin)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded mb-2">
                <div>
                    <p class="font-medium">{{ $checkin->type }} — {{ $checkin->status }}</p>
                    <p class="text-sm text-gray-500">{{ $checkin->completed_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</p>
                </div>
                <a href="{{ route('checkins.show', $checkin) }}" class="text-blue-600 text-sm">Ver</a>
            </div>
            @empty
            <p class="text-gray-500 text-sm">Sin check-ins</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Acciones</h4>
            <div class="space-y-3">
                @if($reservation->status === 'confirmed' || $reservation->status === 'pending')
                <form method="POST" action="{{ route('api.v1.reservations.send-checkin', $reservation) }}" onsubmit="event.preventDefault(); fetch(this.action, {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(r=>r.json()).then(d=>alert('Enlace generado: ' + d.data.url));">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Enviar enlace check-in</button>
                </form>
                @endif
                <a href="{{ route('reservations.edit', $reservation) }}" class="block text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm">Editar reserva</a>
                <hr>
                <h5 class="font-semibold text-sm">SES Hospedajes</h5>
                <a href="{{ route('ses.prepare', $reservation) }}" class="block text-center bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm">Preparar envío SES</a>
            </div>
        </div>

        @if($reservation->sesSubmissions->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Envios SES</h4>
            @foreach($reservation->sesSubmissions as $sub)
            <div class="p-3 bg-gray-50 rounded mb-2 text-sm">
                <p>Estado: <strong>{{ $sub->status }}</strong></p>
                <p>Modo: {{ $sub->mode }}</p>
                <p class="text-xs text-gray-500">{{ $sub->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
