@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-3xl font-bold text-white">{{ $totalTenants }}</div>
        <div class="text-sm text-gray-400 mt-1">Total tenants</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-3xl font-bold text-green-400">{{ $activeTenants }}</div>
        <div class="text-sm text-gray-400 mt-1">Activos</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-3xl font-bold text-red-400">{{ $suspendedTenants }}</div>
        <div class="text-sm text-gray-400 mt-1">Suspendidos</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-3xl font-bold text-yellow-400">{{ $pastDueTenants }}</div>
        <div class="text-sm text-gray-400 mt-1">Morosos</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-4">Total usuarios</h3>
        <div class="text-4xl font-bold text-blue-400">{{ $totalUsers }}</div>
    </div>
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-4">Últimos tenants</h3>
        <div class="space-y-3">
            @forelse($recentTenants as $t)
            <a href="{{ route('admin.tenants.show', $t) }}" class="flex justify-between items-center p-2 rounded hover:bg-gray-700">
                <span>{{ $t->company_name }}</span>
                <span class="text-xs px-2 py-1 rounded {{ $t->status === 'active' ? 'bg-green-900 text-green-300' : ($t->status === 'suspended' ? 'bg-red-900 text-red-300' : 'bg-yellow-900 text-yellow-300') }}">
                    {{ $t->status }}
                </span>
            </a>
            @empty
            <p class="text-gray-500">Sin tenants</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
