@extends('layouts.panel')
@section('title', "{$guest->first_name} {$guest->last_name}")
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">{{ $guest->first_name }} {{ $guest->last_name }}</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Nacionalidad:</span> {{ $guest->nationality }}</div>
                <div><span class="text-gray-500">Documento:</span> {{ $guest->document_type }} — {{ $guest->document_number ? '****' . substr($guest->document_number, -4) : '—' }}</div>
                <div><span class="text-gray-500">Fecha nacimiento:</span> {{ $guest->birth_date?->format('d/m/Y') ?? '—' }}</div>
                <div><span class="text-gray-500">Email:</span> {{ $guest->email ?? '—' }}</div>
                <div><span class="text-gray-500">Teléfono:</span> {{ $guest->phone ?? '—' }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Documentos</h4>
            @forelse($guest->documents as $doc)
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
            <h4 class="font-semibold mb-4">Reserva</h4>
            @if($guest->reservation)
            <div class="p-3 bg-gray-50 rounded text-sm">
                <p class="font-medium">{{ $guest->reservation->code }}</p>
                <p class="text-gray-500">{{ $guest->reservation->property?->name ?? '—' }}</p>
                <a href="{{ route('reservations.show', $guest->reservation) }}" class="text-blue-600 text-xs mt-2 inline-block">Ver reserva</a>
            </div>
            @else
            <p class="text-gray-500 text-sm">Sin reserva asignada</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('guests.destroy', $guest) }}" onsubmit="return confirm('¿Eliminar este huésped?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm hover:bg-red-200">Eliminar huésped</button>
            </form>
        </div>
    </div>
</div>
@endsection
