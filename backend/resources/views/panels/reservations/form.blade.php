@extends('layouts.panel')
@section('title', isset($reservation) ? 'Editar reserva' : 'Nueva reserva')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6">@yield('title')</h3>
        <form method="POST" action="{{ isset($reservation) ? route('reservations.update', $reservation) : route('reservations.store') }}">
            @csrf
            @if(isset($reservation)) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alojamiento</label>
                    <select name="property_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        @foreach($properties as $p)
                        <option value="{{ $p->id }}" {{ old('property_id', $reservation->property_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del huésped</label>
                    <input type="text" name="guest_name" value="{{ old('guest_name', $reservation->guest_name ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="guest_email" value="{{ old('guest_email', $reservation->guest_email ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="guest_phone" value="{{ old('guest_phone', $reservation->guest_phone ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adultos</label>
                    <input type="number" name="adults" value="{{ old('adults', $reservation->adults ?? 1) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menores</label>
                    <input type="number" name="children" value="{{ old('children', $reservation->children ?? 0) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha entrada</label>
                    <input type="date" name="checkin_date" value="{{ old('checkin_date', isset($reservation) ? $reservation->checkin_date->format('Y-m-d') : '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha salida</label>
                    <input type="date" name="checkout_date" value="{{ old('checkout_date', isset($reservation) ? $reservation->checkout_date->format('Y-m-d') : '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total (€)</label>
                    <input type="number" step="0.01" name="total_amount" value="{{ old('total_amount', $reservation->total_amount ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                @if(isset($reservation))
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending" {{ $reservation->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="confirmed" {{ $reservation->status === 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                        <option value="checkin_sent" {{ $reservation->status === 'checkin_sent' ? 'selected' : '' }}>Check-in enviado</option>
                        <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>Completada</option>
                        <option value="cancelled" {{ $reservation->status === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                @endif
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $reservation->notes ?? '') }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('reservations.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm">{{ isset($reservation) ? 'Actualizar' : 'Crear' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
