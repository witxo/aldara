@extends('layouts.panel')
@section('title', "Check-in #{$checkin->id}")
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold">Check-in #{{ $checkin->id }}</h3>
                    <p class="text-sm text-gray-500">{{ $checkin->type }} — {{ $checkin->reservation->property->name ?? '—' }}</p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full {{ $checkin->status === 'verified' ? 'bg-green-100 text-green-700' : ($checkin->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $checkin->status }}</span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Iniciado:</span> {{ $checkin->created_at->format('d/m/Y H:i') }}</div>
                <div><span class="text-gray-500">Completado:</span> {{ $checkin->completed_at?->format('d/m/Y H:i') ?? '—' }}</div>
                <div><span class="text-gray-500">Verificado por:</span> {{ $checkin->verifiedBy?->name ?? '—' }}</div>
                <div><span class="text-gray-500">IP:</span> {{ $checkin->ip_address ?? '—' }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Reserva asociada</h4>
            @if($checkin->reservation)
            <div class="p-4 bg-gray-50 rounded text-sm">
                <p><strong>{{ $checkin->reservation->guest_name }}</strong> — {{ $checkin->reservation->code }}</p>
                <p class="text-gray-500 mt-1">{{ $checkin->reservation->checkin_date->format('d/m/Y') }} → {{ $checkin->reservation->checkout_date->format('d/m/Y') }}</p>
                <a href="{{ route('reservations.show', $checkin->reservation) }}" class="text-blue-600 text-xs mt-2 inline-block">Ver reserva</a>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Documentos ({{ $checkin->guestDocuments->count() }})</h4>
            @forelse($checkin->guestDocuments as $doc)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded mb-2 text-sm">
                <span>{{ $doc->type }} — {{ $doc->filename }}</span>
                <span class="text-xs text-gray-500">{{ $doc->created_at->format('d/m/Y') }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-sm">Sin documentos</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Acciones</h4>
            @if($checkin->status === 'pending')
            <div class="space-y-3">
                <form method="POST" action="{{ route('checkins.verify', $checkin) }}">
                    @csrf
                    <input type="hidden" name="action" value="verify">
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg text-sm">Verificar</button>
                </form>
                <form method="POST" action="{{ route('checkins.verify', $checkin) }}">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="w-full bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm">Rechazar</button>
                </form>
            </div>
            @else
            <p class="text-sm text-gray-500">Check-in {{ $checkin->status === 'verified' ? 'verificado' : 'rechazado' }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
