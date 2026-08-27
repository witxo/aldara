@extends('layouts.app')

@section('title', 'Recuperar contraseña')

@section('body')
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo_hospedacheck.png') }}" alt="HospedaCheck" class="h-16 mx-auto mb-3">
            <h1 class="text-3xl font-bold text-blue-600">{{ config('app.name') }}</h1>
            <p class="text-gray-500 mt-2">Recuperar contraseña</p>
        </div>
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-semibold mb-6">¿Olvidó su contraseña?</h2>
            <p class="text-gray-500 mb-6">Introduzca su email y le enviaremos un enlace para restablecer la contraseña.</p>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
                @csrf
                <input type="hidden" name="recaptcha_token" id="recaptcha_token_forgot_password">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                @error('recaptcha_token')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition">Enviar enlace de recuperación</button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Volver al inicio de sesión</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
        if (typeof recaptchaExecute === 'function') {
            e.preventDefault();
            try {
                const token = await recaptchaExecute('forgot_password');
                document.getElementById('recaptcha_token_forgot_password').value = token;
                this.submit();
            } catch (err) {
                console.error('reCAPTCHA error:', err);
                this.submit();
            }
        }
    });
</script>
@endpush