@extends('layouts.panel')

@section('title', 'Dashboard')

@section('content')
@if($tenant = current_tenant())
    @if($tenant->isTrialing())
    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded">
        <p class="font-medium">🧪 Periodo de prueba — {{ now()->diffInDays($tenant->trial_ends_at) }} días restantes</p>
        <p class="text-sm mt-1">Tu prueba gratuita finaliza el {{ $tenant->trial_ends_at->format('d/m/Y') }}.
        <a href="{{ route('billing.index') }}" class="underline font-medium">Configurar método de pago</a> para no perder el acceso.</p>
    </div>
    @endif
@endif
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Reservas del día</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $todayReservations ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Check-ins pendientes</p>
        <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $pendingCheckins ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Huéspedes activos</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ $activeGuests ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Próximas llegadas</p>
        <p class="text-3xl font-bold text-blue-600 mt-1">{{ $upcomingArrivals ?? 0 }}</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold">Reservas de hoy</h3>
    </div>
    <div class="p-6">
        @if(isset($todayReservationsList) && $todayReservationsList->count() > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-3">Código</th>
                        <th class="pb-3">Huésped</th>
                        <th class="pb-3">Alojamiento</th>
                        <th class="pb-3">Entrada</th>
                        <th class="pb-3">Salida</th>
                        <th class="pb-3">Estado</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayReservationsList as $res)
                    <tr class="border-b last:border-0">
                        <td class="py-3">{{ $res->code }}</td>
                        <td class="py-3">{{ $res->guest_name }}</td>
                        <td class="py-3">{{ $res->property->name ?? '—' }}</td>
                        <td class="py-3">{{ $res->checkin_date->format('d/m/Y') }}</td>
                        <td class="py-3">{{ $res->checkout_date->format('d/m/Y') }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($res->status === 'confirmed') bg-blue-100 text-blue-700
                                @elseif($res->status === 'checkin_sent') bg-yellow-100 text-yellow-700
                                @elseif($res->status === 'completed') bg-green-100 text-green-700
                                @elseif($res->status === 'cancelled') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700
                                @endif">{{ $res->status }}</span>
                        </td>
                        <td class="py-3">
                            <a href="{{ route('reservations.show', $res) }}" class="text-blue-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500 text-center py-8">No hay reservas para hoy</p>
        @endif
    </div>
</div>
@endsection
