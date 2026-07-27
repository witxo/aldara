@extends('layouts.app')

@section('title', 'Registro')

@section('body')
<div class="min-h-screen flex items-center justify-center py-8">
    <div class="w-full max-w-2xl">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo_aldara.png') }}" alt="Aldara" class="h-16 mx-auto mb-3">
            <h1 class="text-3xl font-bold text-blue-600">{{ config('app.name') }}</h1>
            <p class="text-gray-500 mt-2">Aldara, gestión de visitantes</p>
        </div>
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-semibold mb-6">Crear cuenta</h2>
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la empresa</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('company_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CIF/NIF</label>
                        <input type="text" name="tax_id" value="{{ old('tax_id') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input type="password" name="password" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Selecciona tu plan</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($plans as $plan)
                            @if($plan->code === 'enterprise')
                                <div class="relative border border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 flex flex-col items-center text-center">
                                    <div class="font-semibold text-gray-900">{{ $plan->name }}</div>
                                    <div class="text-2xl font-bold text-blue-600 mt-1">Bajo demanda</div>
                                    <ul class="mt-3 text-sm text-gray-600 space-y-1">
                                        <li>✓ Alojamientos ilimitados</li>
                                        <li>✓ Usuarios ilimitados</li>
                                        <li>✓ Reservas ilimitadas</li>
                                    </ul>
                                    <a href="{{ route('contacto.show') }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Contactar</a>
                                </div>
                            @else
                                <label class="relative border rounded-lg p-4 cursor-pointer transition {{ old('plan') === $plan->code ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500' : 'border-gray-200 hover:border-blue-300' }}">
                                    <input type="radio" name="plan" value="{{ $plan->code }}" class="sr-only" {{ old('plan') === $plan->code ? 'checked' : ($loop->first ? 'checked' : '') }}>
                                    <div class="font-semibold text-gray-900">{{ $plan->name }}</div>
                                    <div class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($plan->price_yearly, 0) }}€<span class="text-sm font-normal text-gray-500">/año</span></div>
                                    <ul class="mt-3 text-sm text-gray-600 space-y-1">
                                        <li>✓ {{ $plan->max_properties }} {{ $plan->max_properties === 1 ? 'alojamiento' : 'alojamientos' }}</li>
                                        <li>✓ {{ $plan->max_users }} {{ $plan->max_users === 1 ? 'usuario' : 'usuarios' }}</li>
                                        <li>✓ Reservas ilimitadas</li>
                                    </ul>
                                    <div class="mt-3 text-xs text-blue-600 font-medium">Prueba gratuita {{ $plan->trial_days }} días</div>
                                </label>
                            @endif
                        @endforeach
                    </div>
                    @error('plan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition">Crear cuenta — Prueba gratuita 15 días</button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-500">
                ¿Ya tiene cuenta? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Inicie sesión</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('input[name="plan"]').forEach(input => {
        input.addEventListener('change', function() {
            document.querySelectorAll('label:has(> input[name="plan"])').forEach(label => {
                label.classList.remove('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-500');
                label.classList.add('border-gray-200');
            });
            this.closest('label').classList.add('border-blue-500', 'bg-blue-50', 'ring-2', 'ring-blue-500');
            this.closest('label').classList.remove('border-gray-200');
        });
    });
</script>
@endpush
@endsection
