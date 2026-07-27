@extends('layouts.panel')
@section('title', 'Cambiar plan')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6">Seleccionar plan</h3>
        <form method="POST" action="{{ route('billing.change-plan') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($plans as $plan)
                    @if($plan->code === 'enterprise')
                        <div class="border rounded-lg p-4 bg-gray-50 border-dashed flex flex-col items-center text-center">
                            <h4 class="font-semibold text-lg">{{ $plan->name }}</h4>
                            <p class="text-2xl font-bold mt-2 text-blue-600">Bajo demanda</p>
                            <p class="text-sm text-gray-500 mt-2">{{ $plan->description }}</p>
                            <ul class="text-xs text-gray-500 mt-3 space-y-1">
                                <li>Alojamientos ilimitados</li>
                                <li>Usuarios ilimitados</li>
                                <li>Reservas ilimitadas</li>
                            </ul>
                            <a href="{{ route('contacto.show') }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Contactar</a>
                        </div>
                    @else
                        <label class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="plan_code" value="{{ $plan->code }}" class="sr-only">
                            <h4 class="font-semibold">{{ $plan->name }}</h4>
                            <p class="text-2xl font-bold mt-2">{{ number_format($plan->price_yearly, 0) }} €<span class="text-sm font-normal text-gray-500">/año</span></p>
                            <p class="text-sm text-gray-500 mt-2">{{ $plan->description }}</p>
                            <ul class="text-xs text-gray-500 mt-3 space-y-1">
                                <li>Máx. {{ $plan->max_properties ?? '∞' }} alojamientos</li>
                                <li>Reservas ilimitadas</li>
                            </ul>
                            @if($plan->trial_days > 0)
                                <div class="mt-3 text-xs text-blue-600 font-medium">{{ $plan->trial_days }} días de prueba</div>
                            @endif
                        </label>
                    @endif
                @endforeach
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('billing.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm">Cambiar plan</button>
            </div>
        </form>
    </div>
</div>
@endsection
