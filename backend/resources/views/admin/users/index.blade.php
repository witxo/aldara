@extends('layouts.admin')
@section('title', 'Usuarios')
@section('content')
<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-400 border-b border-gray-700 bg-gray-850">
            <th class="p-3">Nombre</th>
            <th class="p-3">Email</th>
            <th class="p-3">Superadmin</th>
            <th class="p-3">Tenants</th>
            <th class="p-3">Creado</th>
        </tr></thead>
        <tbody>
            @forelse($users as $user)
            <tr class="border-b border-gray-700 hover:bg-gray-750">
                <td class="p-3 text-white">{{ $user->name }}</td>
                <td class="p-3 text-gray-400">{{ $user->email }}</td>
                <td class="p-3">
                    @if($user->is_superadmin)
                    <span class="text-yellow-400 font-medium">Sí</span>
                    @else
                    <span class="text-gray-500">No</span>
                    @endif
                </td>
                <td class="p-3">
                    @foreach($user->tenants as $tenant)
                    <span class="inline-block px-2 py-0.5 text-xs bg-gray-700 text-gray-300 rounded mr-1">{{ $tenant->company_name }}</span>
                    @endforeach
                </td>
                <td class="p-3 text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-8 text-center text-gray-500">No hay usuarios</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection
