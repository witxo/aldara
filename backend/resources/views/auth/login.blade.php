@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('body')
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo_hospedacheck.png') }}" alt="HospedaCheck" class="h-16 mx-auto mb-3">
            <h1 class="text-3xl font-bold text-blue-600">{{ config('app.name') }}</h1>
            <p class="text-gray-500 mt-2">HospedaCheck, gestión de visitantes</p>
        </div>
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-semibold mb-6">Iniciar sesión</h2>
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <input type="hidden" name="recaptcha_token" id="recaptcha_token_login">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="mb-4 flex items-center justify-between">
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-gray-600">Recordarme</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">¿Olvidó su contraseña?</a>
                </div>
                @error('recaptcha_token')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition">Entrar</button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-500">
                ¿No tiene cuenta? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Regístrese</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        if (typeof recaptchaExecute === 'function') {
            e.preventDefault();
            try {
                const token = await recaptchaExecute('login');
                document.getElementById('recaptcha_token_login').value = token;
                this.submit();
            } catch (err) {
                console.error('reCAPTCHA error:', err);
                this.submit();
            }
        }
    });
</script>
@endpush
