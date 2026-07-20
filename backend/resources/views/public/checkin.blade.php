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
                <div class="mb-6 p-4 border-2 border-dashed border-blue-300 rounded-lg bg-blue-50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-blue-700">📷 Escanear DNI / Pasaporte</span>
                        <span class="text-xs text-blue-500">Los datos se rellenarán automáticamente</span>
                    </div>
                    <p class="text-xs text-blue-600 mb-3">Tus datos no salen de tu dispositivo. El escaneo se procesa localmente.</p>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('scan-input').click()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition flex items-center gap-2">
                            <span>📸</span> Capturar con cámara
                        </button>
                        <button type="button" onclick="document.getElementById('gallery-input').click()" class="bg-white text-blue-600 border border-blue-300 px-4 py-2 rounded-lg text-sm hover:bg-blue-100 transition flex items-center gap-2">
                            <span>🖼️</span> Seleccionar foto
                        </button>
                    </div>
                    <input id="scan-input" type="file" accept="image/*" capture="environment" class="hidden" onchange="scanDocument(this)">
                    <input id="gallery-input" type="file" accept="image/*" class="hidden" onchange="scanDocument(this)">
                    <div id="scan-status" class="mt-2 text-xs text-gray-500 hidden"></div>
                </div>

                <h3 class="font-semibold mb-3">Datos del viajero principal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                        <input type="text" name="guests[0][first_name]" id="g-first_name" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Apellidos *</label>
                        <input type="text" name="guests[0][last_name]" id="g-last_name" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo documento *</label>
                        <select name="guests[0][document_type]" id="g-document_type" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="dni">DNI</option>
                            <option value="nie">NIE</option>
                            <option value="passport">Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nº documento *</label>
                        <input type="text" name="guests[0][document_number]" id="g-document_number" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nacionalidad *</label>
                        <select name="guests[0][nationality]" id="g-nationality" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                        <input type="date" name="guests[0][birth_date]" id="g-birth_date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="guests[0][email]" id="g-email" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="tel" name="guests[0][phone]" id="g-phone" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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

@push('scripts')
<script>
    let guestCount = 1;

    async function scanDocument(input) {
        const file = input.files[0];
        if (!file) return;

        const statusEl = document.getElementById('scan-status');
        statusEl.classList.remove('hidden');
        statusEl.innerHTML = '⏳ Procesando imagen...';
        statusEl.className = 'mt-2 text-xs text-gray-500';

        try {
            const img = new Image();
            img.src = URL.createObjectURL(file);
            await new Promise(resolve => { img.onload = resolve; });

            const { data } = await Tesseract.recognize(img.src, 'spa', {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        statusEl.innerHTML = `⏳ Escaneando... ${Math.round(m.progress * 100)}%`;
                    }
                }
            });

            URL.revokeObjectURL(img.src);
            const text = data.text;
            const lines = text.split('\n').map(l => l.trim()).filter(Boolean);

            statusEl.innerHTML = '✅ Documento escaneado. Rellenando formulario...';
            statusEl.className = 'mt-2 text-xs text-green-600';

            const parsed = parseSpanishId(text, lines);

            if (parsed.firstName) document.querySelector('[name="guests[0][first_name]"]').value = parsed.firstName;
            if (parsed.lastName) document.querySelector('[name="guests[0][last_name]"]').value = parsed.lastName;
            if (parsed.documentType) document.querySelector('[name="guests[0][document_type]"]').value = parsed.documentType;
            if (parsed.documentNumber) document.querySelector('[name="guests[0][document_number]"]').value = parsed.documentNumber;
            if (parsed.nationality) {
                const sel = document.querySelector('[name="guests[0][nationality]"]');
                if ([...sel.options].some(o => o.value === parsed.nationality)) {
                    sel.value = parsed.nationality;
                } else {
                    sel.value = 'other';
                }
            }
            if (parsed.birthDate) document.querySelector('[name="guests[0][birth_date]"]').value = parsed.birthDate;

            if (parsed.hasAny) {
                statusEl.innerHTML = '✅ Datos rellenados desde el documento. Revísalos antes de enviar.';
                statusEl.className = 'mt-2 text-xs text-green-600';
            } else {
                statusEl.innerHTML = '⚠️ No se pudieron reconocer datos. Intenta con una foto más clara.';
                statusEl.className = 'mt-2 text-xs text-orange-600';
            }
        } catch (e) {
            statusEl.innerHTML = '❌ Error al escanear: ' + e.message;
            statusEl.className = 'mt-2 text-xs text-red-600';
        }

        input.value = '';
    }

    function parseSpanishId(text, lines) {
        const result = { firstName: null, lastName: null, documentType: null, documentNumber: null, nationality: null, birthDate: null, gender: null, hasAny: false };

        const upperText = text.toUpperCase();

        const nieMatch = text.match(/\b([XYZ]\d{7}[A-Z])\b/);
        const dniMatch = text.match(/\b(\d{8}[A-Z])\b/);
        if (nieMatch) {
            result.documentNumber = nieMatch[1];
            result.documentType = 'nie';
        } else if (dniMatch) {
            result.documentNumber = dniMatch[1];
            result.documentType = 'dni';
        } else if (upperText.includes('PASAPORTE') || upperText.includes('PASSPORT')) {
            result.documentType = 'passport';
            const linesStr = lines.join(' ');
            const numMatch = linesStr.match(/\b([A-Z]{1,2}\d{5,8}[A-Z]?)\b/);
            if (numMatch) result.documentNumber = numMatch[1];
        }

        let apellidosIdx = -1, nombreIdx = -1;
        lines.forEach((l, i) => {
            const u = l.toUpperCase().replace(/[^A-Z\s]/g, '');
            if (u.startsWith('APELLID') && apellidosIdx === -1) apellidosIdx = i;
            if ((u.startsWith('NOMBRE') || u === 'NOM') && nombreIdx === -1) nombreIdx = i;
        });

        if (apellidosIdx >= 0 && apellidosIdx + 1 < lines.length) {
            const name = lines[apellidosIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s\'-]/g, '').trim();
            if (name.length >= 2) result.lastName = name.split(/\s+/).filter(w => w.length > 1).join(' ').substring(0, 50);
        }
        if (nombreIdx >= 0 && nombreIdx + 1 < lines.length) {
            const name = lines[nombreIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s\'-]/g, '').trim();
            if (name.length >= 2) result.firstName = name.split(/\s+/).filter(w => w.length > 1).join(' ').substring(0, 50);
        }

        const nationalityMap = { 'ESP': 'ES', 'ESPAÑA': 'ES', 'ESPANA': 'ES', 'SPAIN': 'ES', 'FRANCE': 'FR', 'FRANCIA': 'FR', 'GERMANY': 'DE', 'ALEMANIA': 'DE', 'ITALIA': 'IT', 'ITALY': 'IT', 'UK': 'GB', 'REINO UNIDO': 'GB', 'PORTUGAL': 'PT', 'USA': 'US', 'ESTADOS UNIDOS': 'US' };
        for (const [key, val] of Object.entries(nationalityMap)) {
            if (upperText.includes(key)) { result.nationality = val; break; }
        }
        if (!result.nationality) {
            const natMatch = text.match(/NACIONALIDAD[:\s]*([A-Z]{2,4})/i);
            if (natMatch) {
                const code = natMatch[1].toUpperCase();
                result.nationality = code.length === 2 ? code : (nationalityMap[code] || null);
            }
        }

        const dateRegex = /(\d{2})[\/-](\d{2})[\/-](\d{4})/g;
        let dateMatch;
        const allDates = [];
        while ((dateMatch = dateRegex.exec(text)) !== null) {
            const day = parseInt(dateMatch[1]), month = parseInt(dateMatch[2]), year = parseInt(dateMatch[3]);
            if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= 2010) {
                allDates.push({ day, month, year, full: `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}` });
            }
        }
        if (allDates.length === 1) {
            result.birthDate = allDates[0].full;
        } else if (allDates.length > 1) {
            const nacIdx = upperText.indexOf('NACIMIENTO');
            if (nacIdx >= 0) {
                const after = upperText.substring(nacIdx, nacIdx + 100);
                const m = dateRegex.exec(after);
                if (m) {
                    const d = parseInt(m[1]), mo = parseInt(m[2]), y = parseInt(m[3]);
                    if (d >= 1 && d <= 31 && mo >= 1 && mo <= 12 && y >= 1900 && y <= 2010) {
                        result.birthDate = `${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                    }
                }
            }
            if (!result.birthDate) result.birthDate = allDates[0].full;
        }

        if (upperText.includes('VARON')) result.gender = 'male';
        else if (upperText.includes('MUJER')) result.gender = 'female';

        result.hasAny = result.documentType || result.documentNumber || result.firstName || result.lastName;
        return result;
    }
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
