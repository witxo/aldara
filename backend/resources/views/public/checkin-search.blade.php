@extends('layouts.checkin')

@section('title', 'Check-in Online')
@section('property_logo', isset($property) && $property->logo_url ? $property->logo_url : asset('images/logo_aldara.png'))
@section('property_favicon', isset($property) && $property->logo_url ? $property->logo_url : asset('images/logo_aldara.png'))
@section('property_name', isset($property) ? $property->name : config('app.name'))
@section('property_title', isset($property) ? 'Check-in — ' . $property->name : 'Check-in Online')
@section('property_subtitle', 'Check-in online')

@section('content')
@if(isset($error))
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="text-red-500 text-5xl mb-4">!</div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Alojamiento no encontrado</h2>
        <p class="text-gray-500">{{ $error }}</p>
    </div>
@elseif($errors->has('not_found'))
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center mb-6">
            <div class="text-orange-500 text-5xl mb-4">?</div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Reserva no encontrada</h2>
            <p class="text-gray-500">{{ $errors->first('not_found') }}</p>
        </div>
        <div class="border-t border-gray-200 pt-6 mt-4">
            @include('public.checkin-search-form')
        </div>
    </div>
@elseif(isset($multiple))
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 text-center">Selecciona tu reserva</h2>
        <p class="text-sm text-gray-500 text-center mb-6">{{ $property->name }}</p>
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
@else
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Check-in online</h2>
            <p class="text-gray-500">Introduce las fechas de tu reserva para acceder al check-in.</p>
        </div>
        @include('public.checkin-search-form')
    </div>
@endif
@endsection
