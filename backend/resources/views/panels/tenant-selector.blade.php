@extends('layouts.app')
@section('title', 'Seleccionar empresa')
@section('body')
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo_hospedacheck.png') }}" alt="HospedaCheck" class="h-16 mx-auto mb-3">
            <h1 class="text-3xl font-bold text-blue-600">{{ config('app.name') }}</h1>
            <p class="text-gray-500 mt-2">HospedaCheck, gestión de visitantes</p>
        </div>
        <div class="bg-white rounded-lg shadow">
            @forelse($tenants as $tenant)
            <form method="POST" action="{{ route('tenant.switch') }}">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                <button type="submit" class="w-full p-4 text-left border-b hover:bg-gray-50 flex items-center justify-between">
                    <div>
                        <p class="font-semibold">{{ $tenant->company_name }}</p>
                        <p class="text-sm text-gray-500">{{ $tenant->pivot->role }} · {{ $tenant->status }}</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </form>
            @empty
            <div class="p-8 text-center text-gray-500">
                <p>No pertenece a ninguna empresa</p>
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline mt-2 block">Crear una nueva empresa</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
