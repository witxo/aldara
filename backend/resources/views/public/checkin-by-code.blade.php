@extends('layouts.checkin')

@section('title', 'Check-in Online')
@section('property_logo', isset($property) && $property->logo_url ? $property->logo_url : asset('images/logo_hospedacheck.png'))
@section('property_favicon', isset($property) && $property->logo_url ? $property->logo_url : asset('images/logo_hospedacheck.png'))
@section('property_name', isset($property) ? $property->name : config('app.name'))
@section('property_title', isset($property) ? 'Check-in — ' . $property->name : 'Check-in Online')
@section('property_subtitle', 'Check-in online')

@section('content')
@if(isset($error))
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="text-red-500 text-5xl mb-4">!</div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Reserva no encontrada</h2>
        <p class="text-gray-500">{{ $error }}</p>
        @if(isset($checkin) && isset($checkout))
            <p class="text-sm text-gray-400 mt-4">
                Fechas: {{ \Carbon\Carbon::parse($checkin)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($checkout)->format('d/m/Y') }}
            </p>
        @endif
    </div>
@elseif(isset($multiple))
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 text-center">Selecciona tu reserva</h2>
        <p class="text-sm text-gray-500 text-center mb-6">
            {{ $property->name }} · {{ \Carbon\Carbon::parse($checkin)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($checkout)->format('d/m/Y') }}
        </p>
        <div class="space-y-3">
            @foreach($reservations as $res)
                @php
                    if (!$res->checkin_token) {
                        $res->generateCheckinToken();
                    }
                    $guestName = $res->mainGuest?->first_name ? $res->mainGuest->first_name . ' ' . $res->mainGuest->last_name : $res->guest_name;
                @endphp
                <a href="{{ route('public.checkin.show', ['token' => $res->checkin_token]) }}"
                   class="block p-4 border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition text-center">
                    <p class="font-medium text-gray-900">{{ $guestName }}</p>
                    <p class="text-sm text-gray-500">{{ $res->adults }} adultos · {{ $res->children }} niños</p>
                </a>
            @endforeach
        </div>
    </div>
@endif
@endsection
