@extends('layouts.panel')
@section('title', isset($user) ? 'Editar usuario' : 'Invitar usuario')
@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6">@yield('title')</h3>
        <form method="POST" action="{{ isset($user) ? route('tenant-users.update', $user) : route('tenant-users.store') }}">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            @if(isset($user))
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <p class="text-gray-900">{{ $user->name }} ({{ $user->email }})</p>
            </div>
            @else
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email del usuario</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required placeholder="email@ejemplo.com">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                <select name="role" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    @if(!isset($user) || $tenantUser->role !== 'admin')
                    <option value="operator" {{ old('role', $tenantUser->role ?? '') === 'operator' ? 'selected' : '' }}>Operador (check-ins + consulta)</option>
                    @endif
                    @if(isset($user) && $tenantUser->role === 'admin')
                    <option value="admin" selected>Administrador (acceso total)</option>
                    @endif
                    @if(!isset($user))
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador (acceso total)</option>
                    @endif
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('tenant-users.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm">{{ isset($user) ? 'Actualizar' : 'Invitar' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
