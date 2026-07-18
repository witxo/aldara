@extends('layouts.panel')
@section('title', 'Integraciones')
@section('content')

{{-- Modal de confirmación para eliminar calendario --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Eliminar calendario</h3>
        <p id="deleteMessage" class="text-gray-600 text-sm mb-6"></p>
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 mb-8">
    {{-- Calendarios iCal por propiedad --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Calendarios iCal</h3>
        <p class="text-sm text-gray-500 mb-6">Añade las URLs iCal de Airbnb, Booking u otras plataformas para importar reservas automáticamente cada 15 minutos.</p>

        @forelse($properties as $property)
        <div class="mb-6 last:mb-0 border rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                <h4 class="font-medium text-gray-800">{{ $property->name }}</h4>
                <button type="button" onclick="showAddCalendar({{ $property->id }}, '{{ $property->name }}')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Añadir calendario</button>
            </div>

            @php $propertyCalendars = $calendars->where('property_id', $property->id); @endphp

            @if($propertyCalendars->isEmpty())
            <div class="p-4 text-sm text-gray-400 text-center">Sin calendarios configurados</div>
            @else
            <div class="divide-y">
                @foreach($propertyCalendars as $cal)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $cal->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-sm text-gray-900">{{ $cal->label }}</span>
                                <span class="px-1.5 py-0.5 text-xs rounded {{ $cal->provider === 'airbnb' ? 'bg-pink-100 text-pink-700' : ($cal->provider === 'booking' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ ucfirst($cal->provider) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 truncate max-w-md mt-0.5">{{ $cal->url }}</p>
                            <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                                @if($cal->last_sync_at)
                                <span>Última sincro: {{ $cal->last_sync_at->diffForHumans() }}</span>
                                @if($cal->last_sync_status === 'ok')
                                <span class="text-green-600">{{ $cal->last_sync_count }} importadas</span>
                                @elseif($cal->last_sync_status === 'error')
                                <span class="text-red-600" title="{{ $cal->last_error }}">Error</span>
                                @endif
                                @else
                                <span>Sin sincronizar</span>
                                @endif
                                <span>{{ $cal->reservations->count() }} reservas</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <form method="POST" action="{{ route('integrations.calendar.sync', $cal) }}" class="inline sync-form">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium sync-btn" data-label="{{ $cal->label }}">Sincronizar ahora</button>
                        </form>
                        <button type="button" onclick="showEditCalendar({{ $cal->id }}, '{{ $cal->provider }}', '{{ $cal->label }}', '{{ $cal->url }}', '{{ $cal->color }}', {{ $cal->is_active ? 'true' : 'false' }})" class="text-xs text-gray-500 hover:text-gray-700 underline">Editar</button>
                        <button type="button" onclick="confirmDelete({{ $cal->id }}, '{{ $cal->label }}', {{ $cal->reservations->count() }})" class="text-xs text-red-500 hover:text-red-700 underline">Eliminar</button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
            <p>No hay propiedades. <a href="{{ route('properties.create') }}" class="text-blue-600 hover:underline">Crea una propiedad primero</a>.</p>
        </div>
        @endforelse
    </div>

    {{-- Formulario para crear/editar calendario (inline, oculto) --}}
    <div id="calendarForm" class="bg-white rounded-lg shadow p-6 hidden">
        <h3 id="formTitle" class="text-lg font-semibold mb-4">Añadir calendario iCal</h3>
        <form method="POST" id="calendarFormElement">
            @csrf
            <input type="hidden" name="property_id" id="cal_property_id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                    <select name="provider" id="cal_provider" required class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="airbnb">Airbnb</option>
                        <option value="booking">Booking.com</option>
                        <option value="other">Otra plataforma</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Etiqueta</label>
                    <input type="text" name="label" id="cal_label" required maxlength="100" placeholder="Ej: Airbnb Principal" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">URL iCal</label>
                <input type="url" name="url" id="cal_url" required placeholder="https://www.airbnb.com/calendar/ical/..." class="w-full rounded-lg border-gray-300 text-sm">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color (opcional)</label>
                    <input type="color" name="color" id="cal_color" class="h-10 w-full rounded border-gray-300">
                </div>
                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" id="cal_is_active" value="1" checked class="rounded border-gray-300">
                        Calendario activo
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideCalendarForm()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm">Guardar calendario</button>
            </div>
        </form>
    </div>

    {{-- Importación manual ICS --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Importar archivo ICS manual</h3>
        <p class="text-sm text-gray-500 mb-4">Sube un archivo .ics para importar reservas de forma puntual.</p>
        <form method="POST" action="{{ route('integrations.ics.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <select name="property_id" required class="w-full rounded-lg border-gray-300 text-sm">
                        @foreach($properties as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="file" name="file" accept=".ics,.txt" required class="w-full text-sm border border-gray-300 rounded-lg">
                </div>
                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm w-full">Importar</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Conectores antiguos (legacy) --}}
    @if($integrations->isNotEmpty())
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Conectores (legacy)</h3>
        <div class="space-y-4">
            @foreach($integrations as $integration)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium">{{ ucfirst($integration->provider) }}</p>
                    <p class="text-sm text-gray-500">{{ $integration->property->name }}</p>
                    <p class="text-xs text-gray-400">Última sincro: {{ $integration->last_sync_at?->diffForHumans() ?? 'Nunca' }}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full {{ $integration->is_connected ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $integration->is_connected ? 'Conectado' : 'Desconectado' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function showAddCalendar(propertyId, propertyName) {
    document.getElementById('formTitle').textContent = 'Añadir calendario — ' + propertyName;
    document.getElementById('cal_property_id').value = propertyId;
    document.getElementById('calendarFormElement').action = '{{ route("integrations.calendar.store") }}';
    document.getElementById('calendarFormElement').method = 'POST';
    document.getElementById('cal_provider').value = 'airbnb';
    document.getElementById('cal_label').value = '';
    document.getElementById('cal_url').value = '';
    document.getElementById('cal_color').value = '#3b82f6';
    document.getElementById('cal_is_active').checked = true;
    document.getElementById('calendarForm').classList.remove('hidden');
    document.getElementById('calendarForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showEditCalendar(id, provider, label, url, color, isActive) {
    document.getElementById('formTitle').textContent = 'Editar calendario';
    document.getElementById('cal_property_id').disabled = true;
    document.getElementById('calendarFormElement').action = '/integrations/calendar/' + id;
    document.getElementById('calendarFormElement').method = 'POST';
    let methodInput = document.getElementById('calendarFormElement').querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        document.getElementById('calendarFormElement').appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    document.getElementById('cal_provider').value = provider;
    document.getElementById('cal_label').value = label;
    document.getElementById('cal_url').value = url;
    document.getElementById('cal_color').value = color || '#3b82f6';
    document.getElementById('cal_is_active').checked = isActive;
    document.getElementById('calendarForm').classList.remove('hidden');
    document.getElementById('calendarForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function hideCalendarForm() {
    document.getElementById('calendarForm').classList.add('hidden');
    document.getElementById('cal_property_id').disabled = false;
}

function confirmDelete(id, label, reservationCount) {
    document.getElementById('deleteMessage').textContent = '¿Eliminar el calendario «' + label + '»? Se eliminarán las reservas futuras sin check-in asociado (' + reservationCount + ' reservas totales).';
    document.getElementById('deleteForm').action = '/integrations/calendar/' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.querySelectorAll('.sync-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        var btn = form.querySelector('.sync-btn');
        btn.textContent = 'Sincronizando...';
        btn.disabled = true;
    });
});
</script>
@endpush
@endsection
