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
}

function setVal(name, value) {
    const el = document.querySelector(`[name="${name}"]`);
    if (el) el.value = value;
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
        const n = lines[apellidosIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s'-]/g, '').trim();
        if (n.length >= 2) result.lastName = n.split(/\s+/).filter(w => w.length > 1).join(' ').substring(0, 50);
    }
    if (nombreIdx >= 0 && nombreIdx + 1 < lines.length) {
        const n = lines[nombreIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s'-]/g, '').trim();
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
