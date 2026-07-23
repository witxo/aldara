@extends('layouts.panel')
@section('title', isset($guest) ? 'Editar huésped' : 'Nuevo huésped')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6">@yield('title')</h3>

        @if(isset($reservation))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
            Añadiendo huésped a la reserva <strong>{{ $reservation->code }}</strong>
            — <a href="{{ route('reservations.show', $reservation) }}" class="underline">Volver</a>
        </div>
        @endif

        <div class="mb-6 p-4 border-2 border-dashed border-blue-300 rounded-lg bg-blue-50">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-blue-700">📷 Escanear DNI / Pasaporte</span>
                <span class="text-xs text-blue-500">Los datos se rellenarán automáticamente</span>
            </div>
            <p class="text-xs text-blue-600 mb-3">El escaneo se procesa localmente en tu navegador.</p>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('admin-scan-input').click()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition flex items-center gap-2">
                    <span>📸</span> Capturar con cámara
                </button>
                <button type="button" onclick="document.getElementById('admin-gallery-input').click()" class="bg-white text-blue-600 border border-blue-300 px-4 py-2 rounded-lg text-sm hover:bg-blue-100 transition flex items-center gap-2">
                    <span>🖼️</span> Seleccionar foto
                </button>
            </div>
            <input id="admin-scan-input" type="file" accept="image/*" capture="environment" class="hidden" onchange="adminScanDocument(this)">
            <input id="admin-gallery-input" type="file" accept="image/*" class="hidden" onchange="adminScanDocument(this)">
            <div id="admin-scan-status" class="mt-2 text-xs text-gray-500 hidden"></div>
        </div>

        <form method="POST" action="{{ isset($guest) ? route('guests.update', $guest) : route('guests.store') }}">
            @csrf
            @if(isset($guest)) @method('PUT') @endif

            @if(isset($reservation))
            <input type="hidden" name="reservation_id" value="{{ $reservation->id }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $guest->first_name ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $guest->last_name ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo documento *</label>
                    <select name="document_type" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="dni" {{ old('document_type', $guest->document_type ?? '') === 'dni' ? 'selected' : '' }}>DNI</option>
                        <option value="nie" {{ old('document_type', $guest->document_type ?? '') === 'nie' ? 'selected' : '' }}>NIE</option>
                        <option value="passport" {{ old('document_type', $guest->document_type ?? '') === 'passport' ? 'selected' : '' }}>Pasaporte</option>
                        <option value="other" {{ old('document_type', $guest->document_type ?? '') === 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nº documento *</label>
                    <input type="text" name="document_number" value="{{ old('document_number', $guest->document_number ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nacionalidad *</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $guest->nationality ?? 'ES') }}" maxlength="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-400 mt-1">Código ISO 2 letras (ES, FR, GB, DE...)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha nacimiento</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', isset($guest) && $guest->birth_date ? $guest->birth_date->format('Y-m-d') : '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                    <select name="gender" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">—</option>
                        <option value="male" {{ old('gender', $guest->gender ?? '') === 'male' ? 'selected' : '' }}>Hombre</option>
                        <option value="female" {{ old('gender', $guest->gender ?? '') === 'female' ? 'selected' : '' }}>Mujer</option>
                        <option value="other" {{ old('gender', $guest->gender ?? '') === 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parentesco</label>
                    <input type="text" name="parentesco" value="{{ old('parentesco', $guest->parentesco ?? '') }}" maxlength="5" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="H, M, P...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $guest->email ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone', $guest->phone ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="address_line1" value="{{ old('address_line1', $guest->address_line1 ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input type="text" name="address_city" value="{{ old('address_city', $guest->address_city ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código postal</label>
                    <input type="text" name="address_postal_code" value="{{ old('address_postal_code', $guest->address_postal_code ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_main_guest" value="1" {{ old('is_main_guest', $guest->is_main_guest ?? false) ? 'checked' : '' }} class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Huésped principal</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ isset($reservation) ? route('reservations.show', $reservation) : route('guests.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm">{{ isset($guest) ? 'Actualizar' : 'Añadir huésped' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function adminScanDocument(input) {
    const file = input.files[0];
    if (!file) return;

    const statusEl = document.getElementById('admin-scan-status');
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

        if (parsed.firstName) document.querySelector('[name="first_name"]').value = parsed.firstName;
        if (parsed.lastName) document.querySelector('[name="last_name"]').value = parsed.lastName;
        if (parsed.documentType) document.querySelector('[name="document_type"]').value = parsed.documentType;
        if (parsed.documentNumber) document.querySelector('[name="document_number"]').value = parsed.documentNumber;
        if (parsed.birthDate) document.querySelector('[name="birth_date"]').value = parsed.birthDate;
        if (parsed.nationality) document.querySelector('[name="nationality"]').value = parsed.nationality;
        if (parsed.gender) {
            const sel = document.querySelector('[name="gender"]');
            if ([...sel.options].some(o => o.value === parsed.gender)) sel.value = parsed.gender;
        }

        if (parsed.hasAny) {
            statusEl.innerHTML = '✅ Datos rellenados desde el documento. Revísalos antes de guardar.';
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

    let mrzLine = null, mrzIdx = -1;
    lines.forEach((l, i) => {
        const t = l.toUpperCase().replace(/\s/g, '');
        if (t.startsWith('IDESP') || t.startsWith('P<')) { mrzLine = t; mrzIdx = i; }
    });

    if (mrzLine && mrzLine.startsWith('IDESP')) {
        const l2 = mrzIdx + 1 < lines.length ? lines[mrzIdx + 1].toUpperCase().replace(/\s/g, '') : '';
        const namePart = mrzLine.replace('IDESP', '').split('<<');
        if (namePart.length > 0) result.lastName = namePart[0].replace(/</g, ' ').trim();
        if (namePart.length > 1) result.firstName = namePart[1].replace(/</g, ' ').trim();
        const docM = l2.match(/(\d{8})([A-Z])/);
        if (docM) { result.documentNumber = docM[1] + docM[2]; result.documentType = 'dni'; }
        const dobM = l2.match(/(\d{6})\d[MFC]/);
        if (dobM) result.birthDate = formatMrzDate(dobM[1]);
        if (l2.includes('ESP')) result.nationality = 'ES';
        if (/[MFC]/.test(l2)) result.gender = /M/.test(l2) ? 'male' : (/F/.test(l2) ? 'female' : '');
        result.hasAny = true;
        return result;
    }

    if (mrzLine && mrzLine.startsWith('P<')) {
        const l2 = mrzIdx + 1 < lines.length ? lines[mrzIdx + 1].toUpperCase().replace(/\s/g, '') : '';
        const namePart = mrzLine.replace(/P<[A-Z]{3}</, '');
        const names = namePart.split('<<');
        if (names.length > 0) result.lastName = names[0].replace(/</g, ' ').trim();
        if (names.length > 1) result.firstName = names[1].replace(/</g, ' ').trim();
        const docM = l2.match(/([A-Z]{1,2})(\d{6,7})/);
        if (docM) { result.documentNumber = docM[1] + docM[2]; result.documentType = 'passport'; }
        const natM = l2.match(/[A-Z]{3}/);
        if (natM) result.nationality = natM[0] === 'ESP' ? 'ES' : natM[0];
        const dobM = l2.match(/(\d{6})\d[MFC]/);
        if (dobM) result.birthDate = formatMrzDate(dobM[1]);
        if (/[MFC]/.test(l2)) result.gender = /M/.test(l2) ? 'male' : (/F/.test(l2) ? 'female' : '');
        result.hasAny = true;
        return result;
    }

    const nieMatch = text.match(/\b([XYZ]\d{7}[A-Z])\b/);
    const dniMatch = text.match(/\b(\d{8}[A-Z])\b/);
    if (nieMatch) { result.documentNumber = nieMatch[1]; result.documentType = 'nie'; }
    else if (dniMatch) { result.documentNumber = dniMatch[1]; result.documentType = 'dni'; }
    else if (upperText.includes('PASAPORTE') || upperText.includes('PASSPORT')) {
        result.documentType = 'passport';
        const m = lines.join(' ').match(/\b([A-Z]{1,2}\d{5,8}[A-Z]?)\b/);
        if (m) result.documentNumber = m[1];
    }

    let apellidosIdx = -1, nombreIdx = -1;
    lines.forEach((l, i) => {
        const u = l.toUpperCase().replace(/[^A-Z\s]/g, '');
        if (u.startsWith('APELLID') && apellidosIdx === -1) apellidosIdx = i;
        if ((u.startsWith('NOMBRE') || u === 'NOM') && nombreIdx === -1) nombreIdx = i;
    });
    if (apellidosIdx >= 0 && apellidosIdx + 1 < lines.length) {
        const n = lines[apellidosIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s\'-]/g, '').trim();
        if (n.length >= 2) result.lastName = n.split(/\s+/).filter(w => w.length > 1).join(' ').substring(0, 50);
    }
    if (nombreIdx >= 0 && nombreIdx + 1 < lines.length) {
        const n = lines[nombreIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s\'-]/g, '').trim();
        if (n.length >= 2) result.firstName = n.split(/\s+/).filter(w => w.length > 1).join(' ').substring(0, 50);
    }

    const natMap = { 'ESP':'ES', 'ESPAÑA':'ES', 'ESPANA':'ES', 'SPAIN':'ES', 'FRANCE':'FR', 'FRANCIA':'FR', 'GERMANY':'DE', 'ALEMANIA':'DE', 'ITALIA':'IT', 'ITALY':'IT', 'UK':'GB', 'REINO UNIDO':'GB', 'PORTUGAL':'PT', 'USA':'US', 'ESTADOS UNIDOS':'US' };
    for (const [k, v] of Object.entries(natMap)) { if (upperText.includes(k)) { result.nationality = v; break; } }
    if (!result.nationality) {
        const m = text.match(/NACIONALIDAD[:\s]*([A-Z]{2,4})/i);
        if (m) result.nationality = m[1].toUpperCase().length === 2 ? m[1].toUpperCase() : (natMap[m[1].toUpperCase()] || null);
    }

    const dateRegex = /(\d{2})[\/-](\d{2})[\/-](\d{4})/g;
    const allDates = []; let m;
    while ((m = dateRegex.exec(text)) !== null) {
        const d = parseInt(m[1]), mo = parseInt(m[2]), y = parseInt(m[3]);
        if (d >= 1 && d <= 31 && mo >= 1 && mo <= 12 && y >= 1900 && y <= 2010)
            allDates.push(`${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
    }
    if (allDates.length === 1) result.birthDate = allDates[0];
    else if (allDates.length > 1) {
        const idx = upperText.indexOf('NACIMIENTO');
        if (idx >= 0) {
            const after = upperText.substring(idx, idx + 100);
            const mm = dateRegex.exec(after);
            if (mm) { const d=parseInt(mm[1]),mo=parseInt(mm[2]),y=parseInt(mm[3]); if(d>=1&&d<=31&&mo>=1&&mo<=12&&y>=1900&&y<=2010) result.birthDate = `${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`; }
        }
        if (!result.birthDate) result.birthDate = allDates[0];
    }

    if (upperText.includes('VARON')) result.gender = 'male';
    else if (upperText.includes('MUJER')) result.gender = 'female';

    result.hasAny = result.documentType || result.documentNumber || result.firstName || result.lastName;
    return result;
}

function formatMrzDate(mrzDate) {
    if (mrzDate.length !== 6) return mrzDate;
    const prefix = parseInt(mrzDate.substring(0, 2)) > 50 ? '19' : '20';
    return `${prefix}${mrzDate.substring(0, 2)}-${mrzDate.substring(2, 4)}-${mrzDate.substring(4, 6)}`;
}
</script>
@endpush