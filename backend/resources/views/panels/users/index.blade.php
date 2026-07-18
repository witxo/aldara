@extends('layouts.panel')
@section('title', 'Usuarios')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Usuarios del equipo</h3>
    <a href="{{ route('tenant-users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Invitar usuario</a>
</div>
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Nombre</th>
                <th class="p-3">Email</th>
                <th class="p-3">Rol</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Aceptado</th>
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                @forelse($users as $user)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">{{ $user->name }}</td>
                    <td class="p-3">{{ $user->email }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($user->pivot->role === 'admin') bg-purple-100 text-purple-700
                            @else bg-gray-100 text-gray-700 @endif">{{ $user->pivot->role === 'admin' ? 'Admin' : 'Operador' }}</span>
                    </td>
                    <td class="p-3">{{ $user->pivot->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td class="p-3">{{ $user->pivot->accepted_at ? 'Sí' : 'Pendiente' }}</td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('tenant-users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar usuario?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 text-xs hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-gray-500">No hay usuarios</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
