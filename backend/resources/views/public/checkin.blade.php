@extends('layouts.checkin')

@section('title', 'Check-in Online')

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
    <div class="bg-white rounded-lg shadow p-6">
        <div class="step-indicator">
            <div class="step step-active">1</div>
            <div class="step step-pending">2</div>
            <div class="step step-pending">3</div>
        </div>

        <h2 class="text-lg font-semibold mb-4">Datos de la reserva</h2>
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500">Código:</span> <span class="font-medium">{{ $reservation->code }}</span></div>
                <div><span class="text-gray-500">Huésped:</span> <span class="font-medium">{{ $reservation->guest_name }}</span></div>
                <div><span class="text-gray-500">Entrada:</span> <span class="font-medium">{{ $reservation->checkin_date->format('d/m/Y') }}</span></div>
                <div><span class="text-gray-500">Salida:</span> <span class="font-medium">{{ $reservation->checkout_date->format('d/m/Y') }}</span></div>
                <div><span class="text-gray-500">Adultos:</span> <span class="font-medium">{{ $reservation->adults }}</span></div>
                <div><span class="text-gray-500">Menores:</span> <span class="font-medium">{{ $reservation->children }}</span></div>
            </div>
        </div>

        <form method="POST" action="{{ route('public.checkin.submit', $reservation->checkin_token) }}" class="space-y-6">
            @csrf
            <div id="guests-container">
                <h3 class="font-semibold mb-3">Datos del viajero principal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                        <input type="text" name="guests[0][first_name]" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Apellidos *</label>
                        <input type="text" name="guests[0][last_name]" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo documento *</label>
                        <select name="guests[0][document_type]" id="doc-type-select" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="dni">DNI</option>
                            <option value="nie">NIE</option>
                            <option value="passport">Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nº documento *</label>
                        <input type="text" name="guests[0][document_number]" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <button type="button" id="scan-btn" onclick="openScanner(document.getElementById('doc-type-select').value, 'guests[0]')" class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-purple-700 transition">
                            Escanear DNI (parte trasera)
                        </button>
                        <p class="text-xs text-gray-400 mt-1">La cámara escaneará automáticamente el MRZ. Para DNI use la parte trasera.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nacionalidad *</label>
                        <select name="guests[0][nationality]" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                        <label class="block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
                        <input type="date" name="guests[0][birth_date]" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="guests[0][email]" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="tel" name="guests[0][phone]" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div id="additional-guests" class="hidden">
                <h3 class="font-semibold mb-3">Acompañantes</h3>
                <div id="guests-list"></div>
                <button type="button" onclick="addGuest()" class="text-sm text-blue-600 hover:text-blue-800">+ Añadir acompañante</button>
            </div>

            @if(config('checkin.require_signature'))
            <div>
                <h3 class="font-semibold mb-3">Firma digital</h3>
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

            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition">
                Enviar check-in
            </button>
        </form>
    </div>
@endif
@endsection

@push('head')
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script src="{{ asset('js/mrz-scanner.js') }}"></script>
<style>
    #mrz-modal video { transform: scaleX(-1); }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('doc-type-select').addEventListener('change', function() {
        const btn = document.getElementById('scan-btn');
        if (this.value === 'dni') {
            btn.textContent = 'Escanear DNI (parte trasera)';
        } else if (this.value === 'passport') {
            btn.textContent = 'Escanear Pasaporte';
        } else {
            btn.textContent = 'Escanear documento';
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    });

    function openScanner(docType, formPrefix) {
        if (window.mrzScanner) {
            window.mrzScanner.closeCamera();
        }
        window.mrzScanner = new MRZScanner({ formPrefix: formPrefix || 'guests[0]' });
        window.mrzScanner.openCamera(docType);
    }

    let guestCount = 1;
    const maxGuests = {{ config('checkin.max_guests', 20) }};
    const totalGuests = {{ $reservation->adults + $reservation->children }};

    if (totalGuests > 1) {
        document.getElementById('additional-guests').classList.remove('hidden');
        for (let i = 1; i < Math.min(totalGuests, maxGuests); i++) {
            addGuest();
        }
    }

    function addGuest() {
        if (guestCount >= maxGuests) return;
        const i = guestCount;
        const html = `
            <div class="guest-entry border rounded-lg p-4 mb-3 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="guests[${i}][first_name]" placeholder="Nombre *" required class="block w-full rounded-lg border-gray-300 text-sm">
                    <input type="text" name="guests[${i}][last_name]" placeholder="Apellidos *" required class="block w-full rounded-lg border-gray-300 text-sm">
                    <select name="guests[${i}][document_type]" required class="block w-full rounded-lg border-gray-300 text-sm">
                        <option value="dni">DNI</option><option value="nie">NIE</option><option value="passport">Pasaporte</option>
                    </select>
                    <input type="text" name="guests[${i}][document_number]" placeholder="Nº documento *" required class="block w-full rounded-lg border-gray-300 text-sm">
                    <select name="guests[${i}][nationality]" class="block w-full rounded-lg border-gray-300 text-sm">
                        <option value="ES">España</option><option value="FR">Francia</option><option value="GB">Reino Unido</option><option value="other">Otro</option>
                    </select>
                    <button type="button" onclick="this.closest('.guest-entry').remove()" class="text-red-500 text-sm">Eliminar</button>
                </div>
            </div>`;
        document.getElementById('guests-list').insertAdjacentHTML('beforeend', html);
        guestCount++;
    }

    @if(config('checkin.require_signature'))
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    let drawing = false;
    let lastX, lastY;

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseleave', stopDrawing);
    canvas.addEventListener('touchstart', handleTouch);
    canvas.addEventListener('touchmove', handleTouch);
    canvas.addEventListener('touchend', stopDrawing);

    function startDrawing(e) {
        drawing = true;
        [lastX, lastY] = [e.offsetX, e.offsetY];
    }

    function draw(e) {
        if (!drawing) return;
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        [lastX, lastY] = [e.offsetX, e.offsetY];
        document.getElementById('signature-data').value = canvas.toDataURL();
    }

    function handleTouch(e) {
        e.preventDefault();
        const rect = canvas.getBoundingClientRect();
        const touch = e.touches[0];
        const x = touch.clientX - rect.left;
        const y = touch.clientY - rect.top;
        if (e.type === 'touchstart') { drawing = true; [lastX, lastY] = [x, y]; }
        else if (e.type === 'touchmove' && drawing) {
            ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(x, y); ctx.stroke();
            [lastX, lastY] = [x, y];
            document.getElementById('signature-data').value = canvas.toDataURL();
        }
    }

    function stopDrawing() { drawing = false; }

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signature-data').value = '';
    }
    @endif
</script>
@endpush
