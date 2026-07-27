@extends('layouts.panel')
@section('title', "SES #{$submission->id}")
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold">Envío SES #{{ $submission->id }}</h3>
                    <p class="text-sm text-gray-500">{{ $submission->mode }} — {{ $submission->reservation->code ?? '—' }}</p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full
                    @if($submission->status === 'sent') bg-blue-100 text-blue-700
                    @elseif($submission->status === 'acknowledged') bg-green-100 text-green-700
                    @elseif($submission->status === 'partially_sent') bg-yellow-100 text-yellow-700
                    @elseif($submission->status === 'failed' || $submission->status === 'rejected') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-700 @endif">{{ $submission->status }}</span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Creado:</span> {{ $submission->created_at->format('d/m/Y H:i') }}</div>
                <div><span class="text-gray-500">Enviado:</span> {{ $submission->sent_at?->format('d/m/Y H:i') ?? '—' }}</div>
                <div><span class="text-gray-500">Intentos:</span> {{ $submission->retry_count ?? 0 }}</div>
                <div><span class="text-gray-500">ID externo:</span> {{ $submission->reference ?? '—' }}</div>
            </div>
            @if(in_array($submission->status, ['failed', 'draft', 'rejected']) && $submission->error_message)
            <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded text-sm text-red-700">
                <strong>Error:</strong> {{ $submission->error_message }}
            </div>
            @endif
            @if($submission->payload)
            <div class="mt-4">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Payload</h5>
                <pre class="p-3 bg-gray-50 rounded text-xs overflow-x-auto">{{ json_encode($submission->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
            @if($submission->response)
            <div class="mt-4">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Respuesta</h5>
                <pre class="p-3 bg-gray-50 rounded text-xs overflow-x-auto">{{ json_encode($submission->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Acciones</h4>
            @if(in_array($submission->status, ['ready', 'partially_sent']))
            <form method="POST" action="{{ route('ses.send', $submission) }}">
                @csrf
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Enviar ahora</button>
            </form>
            @endif
            @if(in_array($submission->status, ['failed', 'draft']))
            <form method="POST" action="{{ route('ses.retry', $submission) }}">
                @csrf
                <button type="submit" class="w-full bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm">Reintentar envío</button>
            </form>
            @endif
            @if($submission->status === 'sent')
            <p class="text-sm text-gray-500">Esperando confirmación</p>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Reserva</h4>
            @if($submission->reservation)
            <div class="p-3 bg-gray-50 rounded text-sm">
                <p class="font-medium">{{ $submission->reservation->guest_name }}</p>
                <p class="text-gray-500">{{ $submission->reservation->code }}</p>
                <a href="{{ route('reservations.show', $submission->reservation) }}" class="text-blue-600 text-xs mt-2 inline-block">Ver reserva</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
