@extends('layouts.panel')
@section('title', 'Facturación')
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Plan actual</h3>
            @if($subscription && $subscription->plan)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold">{{ $subscription->plan->name }}</p>
                        <p class="text-gray-500">{{ $subscription->plan->description }}</p>
                    </div>
                    <span class="px-3 py-1 text-sm rounded-full
                        @if($subscription->status === 'active') bg-green-100 text-green-700
                        @elseif($subscription->status === 'trialing') bg-blue-100 text-blue-700
                        @else bg-yellow-100 text-yellow-700 @endif">{{ $subscription->status }}</span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
                    <div><span class="text-gray-500">Alojamientos:</span> {{ $subscription->plan->max_properties == -1 ? 'Ilimitados' : $subscription->plan->max_properties }}</div>
                    <div><span class="text-gray-500">Usuarios:</span> {{ $subscription->plan->max_users == -1 ? 'Ilimitados' : $subscription->plan->max_users }}</div>
                    <div><span class="text-gray-500">Reservas:</span> Ilimitadas</div>
                </div>
            @else
                <p class="text-gray-500">Sin suscripción activa</p>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Planes disponibles</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($plans as $plan)
                    @if($plan->code === 'enterprise')
                        <div class="border rounded-lg p-4 bg-gray-50 border-dashed flex flex-col items-center text-center">
                            <p class="font-bold text-lg">{{ $plan->name }}</p>
                            <p class="text-2xl font-bold mt-2 text-blue-600">Bajo demanda</p>
                            <ul class="mt-3 space-y-1 text-sm text-center">
                                <li>Alojamientos ilimitados</li>
                                <li>Usuarios ilimitados</li>
                                <li>Reservas ilimitadas</li>
                            </ul>
                            <a href="{{ route('contacto.show') }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Contactar</a>
                        </div>
                    @else
                        <div class="border rounded-lg p-4 {{ $subscription?->plan_id === $plan->id ? 'border-blue-500 bg-blue-50' : '' }}">
                            <p class="font-bold text-lg">{{ $plan->name }}</p>
                            <p class="text-2xl font-bold mt-2">{{ $plan->price_yearly > 0 ? number_format($plan->price_yearly, 0) . '€' : 'Gratis' }}<span class="text-sm text-gray-500">/año</span></p>
                            <ul class="mt-3 space-y-1 text-sm">
                                <li>✓ {{ $plan->max_properties == -1 ? 'Alojamientos ilimitados' : "Hasta {$plan->max_properties} alojamientos" }}</li>
                                <li>✓ {{ $plan->max_users == -1 ? 'Usuarios ilimitados' : "Hasta {$plan->max_users} usuarios" }}</li>
                                <li>✓ Reservas ilimitadas</li>
                            </ul>
                            @if($plan->code === 'basic' || $plan->code === 'advanced')
                                <div class="mt-3 text-xs text-blue-600 font-medium">Prueba {{ $plan->trial_days }} días</div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Acciones</h3>
            <a href="{{ route('billing.change-plan') }}" class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-lg text-sm mb-3">Cambiar de plan</a>
            <a href="{{ route('billing.invoices') }}" class="block w-full text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm">Ver facturas</a>
        </div>
    </div>
</div>
@endsection
