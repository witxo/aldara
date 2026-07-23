@extends('layouts.panel')
@section('title', isset($property) ? 'Editar alojamiento' : 'Nuevo alojamiento')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6">@yield('title')</h3>
        <form method="POST" action="{{ isset($property) ? route('properties.update', $property) : route('properties.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($property)) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $property->name ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="type" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="apartment" {{ old('type', $property->type ?? '') === 'apartment' ? 'selected' : '' }}>Apartamento</option>
                        <option value="house" {{ old('type', $property->type ?? '') === 'house' ? 'selected' : '' }}>Casa</option>
                        <option value="rural" {{ old('type', $property->type ?? '') === 'rural' ? 'selected' : '' }}>Casa rural</option>
                        <option value="hotel" {{ old('type', $property->type ?? '') === 'hotel' ? 'selected' : '' }}>Hotel</option>
                        <option value="hostel" {{ old('type', $property->type ?? '') === 'hostel' ? 'selected' : '' }}>Hostal</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $property->capacity ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" min="1">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="address_line1" value="{{ old('address_line1', $property->address_line1 ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input type="text" name="city" value="{{ old('city', $property->city ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                    <input type="text" name="state" value="{{ old('state', $property->state ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código postal</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $property->postal_code ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nº licencia</label>
                    <input type="text" name="license_number" value="{{ old('license_number', $property->license_number ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @if(isset($property) && $property->logo)
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $property->logo_url }}" alt="Logo" class="h-10 object-contain">
                        <label class="text-xs text-gray-400"><input type="checkbox" name="remove_logo" value="1"> Eliminar logo</label>
                    </div>
                    @endif
                    @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código establecimiento MIR</label>
                    <input type="text" name="ses_establecimiento_code" value="{{ old('ses_establecimiento_code', $property->ses_establecimiento_code ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" maxlength="10">
                    <p class="text-xs text-gray-400 mt-0.5">Código de establecimiento asignado por el Ministerio del Interior (10 caracteres)</p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <h4 class="text-md font-semibold mb-4">Conexión SES Hospedajes (Ministerio del Interior)</h4>
                <p class="text-sm text-gray-500 mb-4">Credenciales para el envío de partes de viajeros al MIR vía SOAP.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                        <input type="text" name="ses_username" value="{{ old('ses_username', $property->ses_username ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Usuario HTTP Basic Auth">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input type="password" name="ses_password" value="{{ old('ses_password', $property->ses_password ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="••••••••">
                        <p class="text-xs text-gray-400 mt-0.5">Dejar en blanco para mantener la actual</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código de arrendador</label>
                        <input type="text" name="ses_codigo_arrendador" value="{{ old('ses_codigo_arrendador', $property->ses_codigo_arrendador ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" maxlength="10" placeholder="0000000001">
                        <p class="text-xs text-gray-400 mt-0.5">Código asignado por el Ministerio del Interior</p>
                    </div>
                </div>
            </div>

            @if(isset($property))
            <div class="mt-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $property->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <span class="text-sm">Activo</span>
                </label>
            </div>
            @endif

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('properties.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm">{{ isset($property) ? 'Actualizar' : 'Crear' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
