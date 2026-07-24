@extends('layouts.checkin')

@section('title', 'Check-in Online')

@php $prop = $reservation->property ?? null; @endphp
@php
    $totalGuests = min($reservation->adults + $reservation->children, config('checkin.max_guests', 20));
@endphp
@section('property_logo', $prop?->logo_url ?? asset('images/logo_aldara.png'))
@section('property_favicon', $prop?->logo_url ?? asset('images/logo_aldara.png'))
@section('property_name', $prop?->name ?? config('app.name'))
@section('property_title', $prop ? 'Check-in — ' . $prop->name : 'Check-in Online')
@section('property_subtitle', $prop ? 'Check-in online' : 'Aldara, gestión de visitantes')

@section('content')
@if(isset($error))
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="text-red-500 text-5xl mb-4">!</div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Enlace no válido</h2>
        <p class="text-gray-500">{{ $error }}</p>
    </div>
@elseif(isset($completed))
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="text-green-500 text-5xl mb-4">✓</div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">¡Check-in completado!</h2>
        <p class="text-gray-500">Sus datos han sido registrados correctamente.</p>
        <p class="text-gray-400 text-sm mt-4">Código de reserva: {{ $reservation->code }}</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow p-6"
         x-data="checkinWizard()"
         x-init="init()">
        <form method="POST" action="{{ route('public.checkin.submit', $reservation->checkin_token) }}" class="space-y-6">
            @csrf

            <input type="hidden" name="total_guests" value="{{ $totalGuests }}">

            <div class="text-center mb-4">
                <span class="text-sm text-gray-500" x-text="currentGuest < totalGuests
                    ? 'Huésped ' + (currentGuest + 1) + ' de ' + totalGuests
                    : 'Finalizar'">
                </span>
            </div>

            @for($i = 0; $i < $totalGuests; $i++)
            <div x-show="currentGuest === {{ $i }}" x-cloak>
                <h3 class="font-semibold mb-3">Huésped {{ $i + 1 }} de {{ $totalGuests }}</h3>

                <div class="mb-4 p-4 border-2 border-dashed border-blue-300 rounded-lg bg-blue-50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-blue-700">Escanear DNI / Pasaporte</span>
                        <span class="text-xs text-blue-500">Los datos se rellenarán automáticamente</span>
                    </div>
                    <p class="text-xs text-blue-600 mb-3">Tus datos no salen de tu dispositivo. El escaneo se procesa localmente.</p>
                    <x-document-scanner prefix="guests[{{ $i }}][" suffix="]" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="g-first_name-{{ $i }}">Nombre *</label>
                        <input type="text" name="guests[{{ $i }}][first_name]" id="g-first_name-{{ $i }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="g-last_name-{{ $i }}">Apellidos *</label>
                        <input type="text" name="guests[{{ $i }}][last_name]" id="g-last_name-{{ $i }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="g-document_type-{{ $i }}">Tipo documento *</label>
                        <select name="guests[{{ $i }}][document_type]" id="g-document_type-{{ $i }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="dni">DNI</option>
                            <option value="nie">NIE</option>
                            <option value="passport">Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="g-document_number-{{ $i }}">Nº documento *</label>
                        <input type="text" name="guests[{{ $i }}][document_number]" id="g-document_number-{{ $i }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="g-nationality-{{ $i }}">Nacionalidad *</label>
                        <select name="guests[{{ $i }}][nationality]" id="g-nationality-{{ $i }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="ES">España</option>
                            <option value="FR">Francia</option>
                            <option value="GB">Reino Unido</option>
                            <option value="DE">Alemania</option>
                            <option value="IT">Italia</option>
                            <option value="US">Estados Unidos</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="g-birth_date-{{ $i }}">Fecha de nacimiento</label>
                        <input type="date" name="guests[{{ $i }}][birth_date]" id="g-birth_date-{{ $i }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="g-email-{{ $i }}">Email</label>
                        <input type="email" name="guests[{{ $i }}][email]" id="g-email-{{ $i }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="g-phone-{{ $i }}">Teléfono</label>
                        <input type="tel" name="guests[{{ $i }}][phone]" id="g-phone-{{ $i }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            @endfor

            <div x-show="currentGuest === totalGuests" x-cloak>
                <h3 class="font-semibold mb-3">Finalizar</h3>

                @if(config('checkin.require_signature'))
                <div class="mb-6">
                    <h4 class="font-semibold mb-2">Firma digital</h4>
                    <p class="text-sm text-gray-500 mb-2">Firme en el recuadro inferior</p>
                    <canvas id="signature-pad" class="w-full border border-gray-300 rounded-lg" height="150"></canvas>
                    <input type="hidden" name="signature_data" id="signature-data">
                    <button type="button" onclick="clearSignature()" class="mt-1 text-sm text-gray-500 hover:text-gray-700">Limpiar firma</button>
                </div>
                @endif

                <div class="space-y-3 bg-gray-50 rounded-lg p-4">
                    <label class="flex items-start">
                        <input type="checkbox" name="consent_legal" required class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">He leído y acepto las condiciones legales y la política de privacidad *</span>
                    </label>
                    <label class="flex items-start">
                        <input type="checkbox" name="consent_marketing" class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Acepto recibir comunicaciones comerciales (opcional)</span>
                    </label>
                    <label class="flex items-start">
                        <input type="checkbox" name="consent_data_retention" required class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Consiento la conservación de mis datos según la política de retención *</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div x-show="currentGuest > 0">
                    <button type="button" @click="prev()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                        ← Anterior
                    </button>
                </div>
                <div x-show="currentGuest === 0"></div>

                <div>
                    <button type="button" @click="next()" x-show="currentGuest < totalGuests"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        Siguiente →
                    </button>

                    <button type="submit" x-show="currentGuest === totalGuests"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                        Enviar check-in
                    </button>
                </div>
            </div>
        </form>
    </div>
@endif
@endsection

@push('head')
<style>
    [x-cloak] { display: none !important; }
</style>
<script src="{{ asset('js/mrz-parser.js') }}"></script>
<script src="{{ asset('js/document-scanner.js') }}"></script>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', function () {
        Alpine.data('checkinWizard', function () {
            return {
                totalGuests: {{ $totalGuests }},
                currentGuest: 0,

                init() {
                    if (this.totalGuests === 0) {
                        this.currentGuest = 0;
                    }
                },

                next() {
                    if (this.currentGuest < this.totalGuests) {
                        this.currentGuest++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        if (this.currentGuest === this.totalGuests) {
                            this.$nextTick(initSignature);
                        }
                    }
                },

                prev() {
                    if (this.currentGuest > 0) {
                        this.currentGuest--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
            };
        });
    });

    function initSignature() {
        var canvas = document.getElementById('signature-pad');
        if (!canvas || canvas._initialized) return;
        canvas._initialized = true;

        var ctx = canvas.getContext('2d');
        var drawing = false;
        var lastX = 0, lastY = 0;

        function resize() {
            var rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = 150;
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
        }
        resize();

        canvas.addEventListener('mousedown', function (e) {
            drawing = true;
            var rect = canvas.getBoundingClientRect();
            lastX = e.clientX - rect.left;
            lastY = e.clientY - rect.top;
        });
        canvas.addEventListener('mousemove', function (e) {
            if (!drawing) return;
            var rect = canvas.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();
            lastX = x;
            lastY = y;
            document.getElementById('signature-data').value = canvas.toDataURL();
        });
        canvas.addEventListener('mouseup', function () { drawing = false; });
        canvas.addEventListener('mouseleave', function () { drawing = false; });

        canvas.addEventListener('touchstart', function (e) {
            e.preventDefault();
            var rect = canvas.getBoundingClientRect();
            var touch = e.touches[0];
            lastX = touch.clientX - rect.left;
            lastY = touch.clientY - rect.top;
            drawing = true;
        });
        canvas.addEventListener('touchmove', function (e) {
            e.preventDefault();
            if (!drawing) return;
            var rect = canvas.getBoundingClientRect();
            var touch = e.touches[0];
            var x = touch.clientX - rect.left;
            var y = touch.clientY - rect.top;
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();
            lastX = x;
            lastY = y;
            document.getElementById('signature-data').value = canvas.toDataURL();
        });
        canvas.addEventListener('touchend', function () { drawing = false; });
    }

    if (document.getElementById('signature-pad')) {
        initSignature();
    }

    window.clearSignature = function () {
        var canvas = document.getElementById('signature-pad');
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signature-data').value = '';
    };
</script>
@endpush
