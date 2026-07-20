@extends('layouts.panel')
@section('title', isset($guest) ? 'Editar huésped' : 'Nuevo huésped')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6">@yield('title')</h3>

        @if(isset($reservation))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
            Añadiendo huésped a la reserva <strong>{{ $reservation->code }}</strong>
            — <a href="{{ route('reservations.show', $reservation) }}" class="underline">Volver</a>
        </div>
        @endif

        <form method="POST" action="{{ isset($guest) ? route('guests.update', $guest) : route('guests.store') }}">
            @csrf
            @if(isset($guest)) @method('PUT') @endif

            @if(isset($reservation))
            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $guest->first_name ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $guest->last_name ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo documento *</label>
                    <select name="document_type" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="dni" {{ old('document_type', $guest->document_type ?? '') === 'dni' ? 'selected' : '' }}>DNI</option>
                        <option value="nie" {{ old('document_type', $guest->document_type ?? '') === 'nie' ? 'selected' : '' }}>NIE</option>
                        <option value="passport" {{ old('document_type', $guest->document_type ?? '') === 'passport' ? 'selected' : '' }}>Pasaporte</option>
                        <option value="other" {{ old('document_type', $guest->document_type ?? '') === 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nº documento *</label>
                    <input type="text" name="document_number" value="{{ old('document_number', $guest->document_number ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nacionalidad *</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $guest->nationality ?? 'ES') }}" maxlength="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-400 mt-1">Código ISO 2 letras (ES, FR, GB, DE...)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha nacimiento</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', isset($guest) && $guest->birth_date ? $guest->birth_date->format('Y-m-d') : '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                    <select name="gender" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">—</option>
                        <option value="male" {{ old('gender', $guest->gender ?? '') === 'male' ? 'selected' : '' }}>Hombre</option>
                        <option value="female" {{ old('gender', $guest->gender ?? '') === 'female' ? 'selected' : '' }}>Mujer</option>
                        <option value="other" {{ old('gender', $guest->gender ?? '') === 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parentesco</label>
                    <input type="text" name="parentesco" value="{{ old('parentesco', $guest->parentesco ?? '') }}" maxlength="5" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="H, M, P...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $guest->email ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone', $guest->phone ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="address_line1" value="{{ old('address_line1', $guest->address_line1 ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input type="text" name="address_city" value="{{ old('address_city', $guest->address_city ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código postal</label>
                    <input type="text" name="address_postal_code" value="{{ old('address_postal_code', $guest->address_postal_code ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_main_guest" value="1" {{ old('is_main_guest', $guest->is_main_guest ?? false) ? 'checked' : '' }} class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Huésped principal</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ isset($reservation) ? route('reservations.show', $reservation) : route('guests.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm">{{ isset($guest) ? 'Actualizar' : 'Añadir huésped' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection