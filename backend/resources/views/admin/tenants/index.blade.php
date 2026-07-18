@extends('layouts.admin')
@section('title', 'Tenants')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Todos los tenants</h3>
    <a href="{{ route('admin.tenants.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Crear tenant</a>
</div>
<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-400 border-b border-gray-700 bg-gray-850">
            <th class="p-3">Empresa</th>
            <th class="p-3">Email</th>
            <th class="p-3">Estado</th>
            <th class="p-3">Usuarios</th>
            <th class="p-3">Alojamientos</th>
            <th class="p-3">Creado</th>
            <th class="p-3"></th>
        </tr></thead>
        <tbody>
            @forelse($tenants as $tenant)
            <tr class="border-b border-gray-700 hover:bg-gray-750">
                <td class="p-3 text-white">{{ $tenant->company_name }}</td>
                <td class="p-3 text-gray-400">{{ $tenant->email }}</td>
                <td class="p-3">
                    <span class="px-2 py-1 text-xs rounded {{ $tenant->status === 'active' ? 'bg-green-900 text-green-300' : ($tenant->status === 'trialing' ? 'bg-blue-900 text-blue-300' : ($tenant->status === 'suspended' ? 'bg-red-900 text-red-300' : 'bg-yellow-900 text-yellow-300')) }}">
                        {{ $tenant->status }}
                    </span>
                </td>
                <td class="p-3 text-gray-400">{{ $tenant->users_count }}</td>
                <td class="p-3 text-gray-400">{{ $tenant->properties_count }}</td>
                <td class="p-3 text-gray-400">{{ $tenant->created_at->format('d/m/Y') }}</td>
                <td class="p-3">
                    <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-blue-400 hover:text-blue-300">Ver</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-8 text-center text-gray-500">No hay tenants</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $tenants->links() }}
</div>
@endsection
