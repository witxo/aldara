@extends('layouts.panel')
@section('title', $property->name)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold">{{ $property->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $property->type }} — {{ $property->license_number ?? 'Sin licencia' }}</p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full {{ $property->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $property->is_active ? 'Activo' : 'Inactivo' }}</span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Dirección:</span> {{ $property->address_line1 }}</div>
                <div><span class="text-gray-500">Ciudad:</span> {{ $property->city }}</div>
                <div><span class="text-gray-500">Provincia:</span> {{ $property->state }}</div>
                <div><span class="text-gray-500">C.P.:</span> {{ $property->postal_code }}</div>
                <div><span class="text-gray-500">Capacidad:</span> {{ $property->capacity ?? '—' }}</div>
                <div><span class="text-gray-500">Código MIR:</span> {{ $property->ses_establecimiento_code ?? '—' }}</div>
                <div><span class="text-gray-500">Usuario SES:</span> {{ $property->ses_username ?? '—' }}</div>
                <div><span class="text-gray-500">Código arrendador:</span> {{ $property->ses_codigo_arrendador ?? '—' }}</div>
                <div class="col-span-2 mt-2">
                    <a href="{{ route('properties.test-ses', $property) }}" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-plug mr-1"></i> Probar conexión SES
                    </a>
                </div>
                <div><span class="text-gray-500">Check-in:</span> {{ $property->checkin_time ?? '—' }}</div>
                <div><span class="text-gray-500">Check-out:</span> {{ $property->checkout_time ?? '—' }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Próximas reservas</h4>
            @forelse($property->reservations()->where('checkout_date', '>=', now())->orderBy('checkin_date')->limit(5)->get() as $res)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded mb-2">
                <div>
                    <p class="font-medium">{{ $res->guest_name }}</p>
                    <p class="text-sm text-gray-500">{{ $res->checkin_date->format('d/m/Y') }} — {{ $res->checkout_date->format('d/m/Y') }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded {{ $res->status === 'confirmed' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $res->status }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-sm">No hay reservas próximas</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Acciones</h4>
            <div class="space-y-3">
                <a href="{{ route('properties.edit', $property) }}" class="block text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm">Editar</a>
                <a href="{{ route('reservations.index', ['property_id' => $property->id]) }}" class="block text-center bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm">Ver reservas</a>
                <hr>
                <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('¿Eliminar este alojamiento?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm">Eliminar</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Integraciones</h4>
            @forelse($property->integrations as $int)
            <div class="p-3 bg-gray-50 rounded mb-2 text-sm">
                <p class="font-medium">{{ $int->provider }}</p>
                <p class="text-xs text-gray-500 {{ $int->is_connected ? 'text-green-600' : 'text-red-600' }}">{{ $int->is_connected ? 'Conectado' : 'Desconectado' }}</p>
            </div>
            @empty
            <p class="text-gray-500 text-sm">Sin integraciones</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
