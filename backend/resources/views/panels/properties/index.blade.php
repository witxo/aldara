@extends('layouts.panel')
@section('title', 'Alojamientos')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Mis alojamientos</h3>
    <a href="{{ route('properties.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Nuevo alojamiento</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($properties as $property)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-semibold">{{ $property->name }}</h4>
            <span class="px-2 py-0.5 text-xs rounded {{ $property->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $property->is_active ? 'Activo' : 'Inactivo' }}</span>
        </div>
        <p class="text-sm text-gray-500">{{ $property->type }} — {{ $property->city }}</p>
        @if($property->license_number)
        <p class="text-xs text-gray-400 mt-1">Licencia: {{ $property->license_number }}</p>
        @endif
        <div class="mt-4 flex justify-between items-center">
            <span class="text-sm text-gray-500">Capacidad: {{ $property->capacity }}</span>
            <a href="{{ route('properties.show', $property) }}" class="text-blue-600 text-sm hover:underline">Ver</a>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12 text-gray-500">
        <p class="text-lg mb-2">No hay alojamientos</p>
        <a href="{{ route('properties.create') }}" class="text-blue-600 hover:underline">Crear el primer alojamiento</a>
    </div>
    @endforelse
</div>
@endsection
