let cameraStream = null;

function openCamera() {
    const modal = document.getElementById('camera-modal');
    const video = document.getElementById('camera-preview');
    modal.classList.remove('hidden');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } } })
        .then(stream => {
            cameraStream = stream;
            video.srcObject = stream;
            video.play();
        })
        .catch(err => {
            alert('Error al acceder a la cámara: ' + err.message);
            closeCamera();
        });
}

function closeCamera() {
    const modal = document.getElementById('camera-modal');
    modal.classList.add('hidden');
    if (cameraStream) {
        cameraStream.getTracks().forEach(t => t.stop());
        cameraStream = null;
    }
}

function capturePhoto() {
    const video = document.getElementById('camera-preview');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    closeCamera();
    canvas.toBlob(blob => {
        const file = new File([blob], 'capture.jpg', { type: 'image/jpeg' });
        scanDocument(file, getFieldMappings());
    }, 'image/jpeg', 0.95);
}

function getFieldMappings() {
    const prefix = window.SCAN_FIELD_PREFIX || '';
    const suffix = window.SCAN_FIELD_SUFFIX || '';
    return {
        firstName:     prefix + 'first_name' + suffix,
        lastName:      prefix + 'last_name' + suffix,
        documentType:  prefix + 'document_type' + suffix,
        documentNumber: prefix + 'document_number' + suffix,
        birthDate:     prefix + 'birth_date' + suffix,
        nationality:   prefix + 'nationality' + suffix,
        gender:        prefix + 'gender' + suffix,
    };
}

async function scanDocument(file, fields) {
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

        if (parsed.firstName) setVal(fields.firstName, parsed.firstName);
        if (parsed.lastName) setVal(fields.lastName, parsed.lastName);
        if (parsed.documentType) setVal(fields.documentType, parsed.documentType);
        if (parsed.documentNumber) setVal(fields.documentNumber, parsed.documentNumber);
        if (parsed.birthDate) setVal(fields.birthDate, parsed.birthDate);
        if (parsed.nationality) {
            const el = document.querySelector(`[name="${fields.nationality}"]`);
            if (el) {
                if (el.tagName === 'SELECT' && [...el.options].some(o => o.value === parsed.nationality)) {
                    el.value = parsed.nationality;
                } else {
                    el.value = parsed.nationality;
                }
            }
        }
        if (parsed.gender) {
            const el = document.querySelector(`[name="${fields.gender}"]`);
            if (el && el.tagName === 'SELECT' && [...el.options].some(o => o.value === parsed.gender)) {
                el.value = parsed.gender;
            }
        }

        const found = [];
        if (parsed.firstName) found.push('nombre');
        if (parsed.lastName) found.push('apellidos');
        if (parsed.documentNumber) found.push('doc');
        if (parsed.birthDate) found.push('fecha nac.');
        if (found.length > 1) {
            statusEl.innerHTML = `✅ Datos rellenados (${found.join(', ')}). Revísalos antes de enviar.`;
            statusEl.className = 'mt-2 text-xs text-green-600';
        } else if (found.length === 1) {
            statusEl.innerHTML = `⚠️ Solo se reconoció ${found[0]}. Intenta una foto más clara.`;
            statusEl.className = 'mt-2 text-xs text-orange-600';
        } else {
            statusEl.innerHTML = '⚠️ No se pudieron reconocer datos. Intenta con una foto más clara o selecciona la foto de la galería.';
            statusEl.className = 'mt-2 text-xs text-orange-600';
        }
    } catch (e) {
        statusEl.innerHTML = '❌ Error al escanear: ' + e.message;
        statusEl.className = 'mt-2 text-xs text-red-600';
    }
}

function setVal(name, value) {
    const el = document.querySelector(`[name="${name}"]`);
    if (el) el.value = value;
}

function parseSpanishId(text, lines) {
    const result = { firstName: null, lastName: null, documentType: null, documentNumber: null, nationality: null, birthDate: null, gender: null, hasAny: false };
    const upperText = text.toUpperCase();
    const joined = lines.join(' ');

    // --- 1) MRZ parsing: find IDESP or P< and grab surrounding block ---
    let mrzBlock = '';
    let mrzStart = -1;
    for (let i = 0; i < lines.length; i++) {
        const l = lines[i].toUpperCase().replace(/\s/g, '');
        if (l.startsWith('IDESP') || l.startsWith('P<')) {
            mrzBlock = l;
            mrzStart = i;
            for (let j = 1; j <= 2; j++) {
                if (i + j < lines.length) mrzBlock += lines[i + j].toUpperCase().replace(/\s/g, '');
            }
            break;
        }
    }
    // Also search raw text (MRZ may span lines oddly)
    if (!mrzBlock) {
        const idx = upperText.replace(/\s/g, '').indexOf('IDESP');
        if (idx >= 0) {
            mrzBlock = upperText.replace(/\s/g, '').substring(idx, idx + 90);
        }
    }

    if (mrzBlock) {
        // Extract names: after IDESP / P<XXX<, split by < or digit boundary
        let nameRaw = '';
        if (mrzBlock.startsWith('IDESP')) {
            nameRaw = mrzBlock.replace(/^IDESP/, '');
        } else if (mrzBlock.startsWith('P<')) {
            nameRaw = mrzBlock.replace(/^P<[A-Z]{3}</, '');
            result.documentType = 'passport';
        }
        // Split name by < sequences; filter out digit-only tokens
        const nameTokens = nameRaw.split(/<+/).filter(t => t && !/^\d/.test(t));
        if (/^IDESP/.test(mrzBlock)) {
            if (nameTokens.length > 0) result.lastName = cleanName(nameTokens[0]);
            if (nameTokens.length > 1) result.firstName = cleanName(nameTokens[1]);
            // Document number: 8 digits + letter
            const docM = mrzBlock.match(/(\d{8})([A-Z])/);
            if (docM) { result.documentNumber = docM[1] + docM[2]; result.documentType = 'dni'; }
        } else {
            // Passport: letter(s) + digits
            if (nameTokens.length > 0) result.lastName = cleanName(nameTokens[0]);
            if (nameTokens.length > 1) result.firstName = cleanName(nameTokens[1]);
            const docM = mrzBlock.match(/([A-Z]{1,2})(\d{6,7})/);
            if (docM) { result.documentNumber = docM[1] + docM[2]; result.documentType = 'passport'; }
            const natM = mrzBlock.match(/[A-Z]{3}/);
            if (natM) result.nationality = natM[0] === 'ESP' ? 'ES' : natM[0];
        }
        // Birth date in YYMMDD + check digit + M/F/C
        const dobM = mrzBlock.match(/(\d{6})\d[MFC]/);
        if (dobM) result.birthDate = formatMrzDate(dobM[1]);
        if (mrzBlock.includes('ESP')) result.nationality = 'ES';
        const gM = mrzBlock.match(/[MFC]/);
        if (gM) result.gender = gM[0] === 'M' ? 'male' : (gM[0] === 'F' ? 'female' : '');
        result.hasAny = !!(result.documentNumber || result.firstName || result.lastName);
        return result;
    }

    // --- 2) Fallback: field-based OCR ---
    // Document number
    const nieMatch = text.match(/\b([XYZ]\d{7}[A-Z])\b/);
    const dniMatch = text.match(/\b(\d{8}[A-Z])\b/);
    if (nieMatch) { result.documentNumber = nieMatch[1]; result.documentType = 'nie'; }
    else if (dniMatch) { result.documentNumber = dniMatch[1]; result.documentType = 'dni'; }
    else if (upperText.includes('PASAPORTE') || upperText.includes('PASSPORT')) {
        result.documentType = 'passport';
        const m = joined.match(/\b([A-Z]{1,2}\d{5,8}[A-Z]?)\b/);
        if (m) result.documentNumber = m[1];
    }

    // Names: try "APELLIDOS" / "NOMBRE" field labels
    let apellidosIdx = -1, nombreIdx = -1;
    lines.forEach((l, i) => {
        const u = l.toUpperCase().replace(/[^A-Z\s]/g, '');
        if (u.startsWith('APELLID') && apellidosIdx === -1) apellidosIdx = i;
        if ((u.startsWith('NOMBRE') || u === 'NOM' || u.startsWith('NOM ')) && nombreIdx === -1) nombreIdx = i;
    });
    if (apellidosIdx >= 0 && apellidosIdx + 1 < lines.length) {
        const n = lines[apellidosIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s'-]/g, '').trim();
        if (n.length >= 2) result.lastName = n.split(/\s+/).filter(w => w.length > 1).join(' ').substring(0, 50);
    }
    if (nombreIdx >= 0 && nombreIdx + 1 < lines.length) {
        const n = lines[nombreIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s'-]/g, '').trim();
        if (n.length >= 2) result.firstName = n.split(/\s+/).filter(w => w.length > 1).join(' ').substring(0, 50);
    }
    // If labels not found, try names near document number
    if (!result.lastName && !result.firstName && result.documentNumber) {
        const pos = upperText.indexOf(result.documentNumber);
        if (pos > 0) {
            const before = upperText.substring(0, pos).trim();
            const words = before.split(/[\s<]+/).filter(w => /^[A-Z]{3,}$/.test(w));
            if (words.length >= 2) {
                result.lastName = words.slice(-2).join(' ').substring(0, 50);
            } else if (words.length === 1) {
                result.lastName = words[0];
            }
        }
    }

    // Nationality
    const natMap = { 'ESP':'ES', 'ESPAÑA':'ES', 'SPAIN':'ES', 'FRANCE':'FR', 'FRANCIA':'FR', 'GERMANY':'DE', 'ALEMANIA':'DE', 'ITALIA':'IT', 'ITALY':'IT', 'GB':'GB', 'REINO UNIDO':'GB', 'PORTUGAL':'PT', 'USA':'US', 'ESTADOS UNIDOS':'US' };
    for (const [k, v] of Object.entries(natMap)) { if (upperText.includes(k)) { result.nationality = v; break; } }
    if (!result.nationality) {
        const m = text.match(/NACIONALIDAD[:\s]*([A-Z]{2,4})/i);
        if (m) result.nationality = m[1].toUpperCase();
    }

    // Birth date: first try "NACIMIENTO" label, then look for any valid date
    const nacIdx = upperText.indexOf('NACIMIENTO');
    const dateRegex = /(\d{2})[\/\s-](\d{2})[\/\s-](\d{4})/g;
    let m;
    if (nacIdx >= 0) {
        const after = upperText.substring(nacIdx, Math.min(nacIdx + 80, upperText.length));
        const mm = dateRegex.exec(after);
        if (mm) { const d=parseInt(mm[1]),mo=parseInt(mm[2]),y=parseInt(mm[3]); if(d>=1&&d<=31&&mo>=1&&mo<=12&&y>=1900&&y<=2010) result.birthDate = `${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`; }
    }
    if (!result.birthDate) {
        const allDates = [];
        while ((m = dateRegex.exec(upperText)) !== null) {
            const d=parseInt(m[1]),mo=parseInt(m[2]),y=parseInt(m[3]);
            if (d>=1&&d<=31&&mo>=1&&mo<=12&&y>=1900&&y<=2010)
                allDates.push(`${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
        }
        // Pick the earliest date as birth date (birth tends to be the oldest date on the card)
        if (allDates.length === 1) result.birthDate = allDates[0];
        else if (allDates.length > 1) result.birthDate = allDates.sort()[0];
    }

    if (upperText.includes('VARON') || upperText.includes('MASCULINO')) result.gender = 'male';
    else if (upperText.includes('MUJER') || upperText.includes('FEMENINO')) result.gender = 'female';

    result.hasAny = result.documentType || result.documentNumber || result.firstName || result.lastName;
    return result;
}

function cleanName(s) {
    return s.replace(/[^A-Za-zÀ-ÿÑñ\s'-]/g, ' ').replace(/\s+/g, ' ').trim().substring(0, 50);
}

function formatMrzDate(mrzDate) {
    if (mrzDate.length !== 6) return mrzDate;
    const prefix = parseInt(mrzDate.substring(0, 2)) > 50 ? '19' : '20';
    return `${prefix}${mrzDate.substring(0, 2)}-${mrzDate.substring(2, 4)}-${mrzDate.substring(4, 6)}`;
}
