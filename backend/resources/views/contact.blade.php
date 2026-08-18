@extends('layouts.app')

@section('title', 'Contacto')

@section('body')
<div class="min-h-screen flex items-center justify-center py-8">
    <div class="w-full max-w-lg">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo_hospedacheck.png') }}" alt="HospedaCheck" class="h-16 mx-auto mb-3">
            <h1 class="text-3xl font-bold text-blue-600">{{ config('app.name') }}</h1>
            <p class="text-gray-500 mt-2">Contacta con nosotros</p>
        </div>
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-semibold mb-6">Envíanos un mensaje</h2>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('contacto.send') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asunto *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje *</label>
                    <textarea name="message" rows="5" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition">Enviar mensaje</button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Volver al inicio</a>
            </p>
        </div>
    </div>
</div>
@endsection
