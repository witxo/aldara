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
    const compressed = upperText.replace(/\s/g, '');
    const joined = lines.join(' ');

    // Common DNI label words to exclude from name extraction
    const labelWords = new Set([
        'NOMBRE', 'APELLIDOS', 'APELLIDO', 'NACIONALIDAD', 'NACIONAL',
        'IDENTIDAD', 'DOCUMENTO', 'DOMICILIO', 'DIRECCION', 'FECHA',
        'NACIMIENTO', 'SEXO', 'FIRMA', 'VALIDEZ', 'VÁLIDO', 'EXPIRA',
        'VARON', 'MUJER', 'MASCULINO', 'FEMENINO', 'ESPAÑA', 'ESPANA',
        'PASAPORTE', 'PASSPORT', 'DNI', 'NIF', 'NIE', 'NUMERO', 'NUM',
        'ESTADO', 'CIVIL', 'PROFESION', 'LUGAR', 'NACIMIENTO', 'LIMITACIONES',
        'CONDICIONES', 'CARACTERES', 'ELECTRONICO', 'SOPORTE', 'TITULAR',
        'CERTIFICADO', 'ORGANO', 'EXPIDE', 'EXPEDICION',
    ]);

    // --- 1) MRZ parsing ---
    let mrzBlock = '';
    // Match: IDESP, I<ESP, I ESP, or 1<ESP etc (Tesseract misreads I as 1 or l)
    const mrzPatterns = [/IDESP/i, /I[<\s]ESP/i, /1[<\s]ESP/i, /I[<\s]/i];
    let mrzIdx = -1;
    for (let i = 0; i < lines.length; i++) {
        const cleaned = lines[i].toUpperCase().replace(/\s/g, '');
        if (mrzPatterns.some(p => p.test(cleaned))) {
            mrzIdx = i;
            mrzBlock = lines[i].toUpperCase();
            for (let j = 1; j <= 2; j++) {
                if (i + j < lines.length) mrzBlock += lines[i + j].toUpperCase().replace(/\s/g, '');
            }
            break;
        }
    }
    // Also search raw compressed text
    if (!mrzBlock) {
        const tryPatterns = ['IDESP', 'I ESP', 'I<ESP', '1<ESP', '1 ESP'];
        let bestIdx = -1, bestCode = '';
        for (const p of tryPatterns) {
            const idx = compressed.indexOf(p);
            if (idx >= 0 && (bestIdx === -1 || idx < bestIdx)) {
                bestIdx = idx; bestCode = p;
            }
        }
        if (bestIdx >= 0) {
            mrzBlock = compressed.substring(bestIdx, bestIdx + 90);
        }
    }

    if (mrzBlock) {
        // Strip leading IDESP / I<ESP / I ESP
        let block = mrzBlock.replace(/^I?\s*[<]?\s*ESP\s*/i, '').replace(/^IDESP/i, '');
        // Split by < or digits; first tokens are names
        const tokens = block.split(/<+/).filter(t => t && t.length > 1 && /^[A-Z]{2,}$/.test(t.replace(/\d/g, '')));
        // Names: surname before <<, given name after
        const nameStr = tokens.join(' ');
        const nameParts = nameStr.split(/(?:\s{2,}|<{2,})/); // double space or << splits surname/given
        if (nameParts.length >= 2) {
            result.lastName = cleanName(nameParts[0].replace(/\d/g, '').trim());
            result.firstName = cleanName(nameParts.slice(1).join(' ').replace(/\d/g, '').trim());
        } else if (tokens.length >= 2) {
            result.lastName = cleanName(tokens[0]);
            result.firstName = cleanName(tokens.slice(1).join(' '));
        } else if (tokens.length === 1) {
            result.lastName = cleanName(tokens[0]);
        }
        // Birth date: YYMMDD + digit + M/F/C
        const dobM = mrzBlock.match(/(\d{6})\d[MFC]/);
        if (dobM) result.birthDate = formatMrzDate(dobM[1]);
        // Document number: 8 digits + letter
        const docM = mrzBlock.match(/(\d{8})([A-Z])/);
        if (docM) { result.documentNumber = docM[1] + docM[2]; result.documentType = 'dni'; }
        else {
            // Try passport doc number
            const pdocM = mrzBlock.match(/([A-Z]{1,2})(\d{6,7})/);
            if (pdocM) { result.documentNumber = pdocM[1] + pdocM[2]; result.documentType = 'passport'; }
        }
        if (mrzBlock.includes('ESP')) result.nationality = 'ES';
        const gM = mrzBlock.match(/[MFC]/);
        if (gM) result.gender = gM[0] === 'M' ? 'male' : (gM[0] === 'F' ? 'female' : '');
        result.hasAny = !!(result.documentNumber || result.firstName || result.lastName);
        if (result.hasAny) return result;
    }

    // --- 2) Also look for MRZ birth date even if MRZ block not fully detected ---
    // (MRZ might be partially readable; YYMMDD + check digit + M/F/C pattern)
    if (!result.birthDate) {
        const mrzDobMatch = compressed.match(/(\d{6})\d[MFC]/);
        if (mrzDobMatch) result.birthDate = formatMrzDate(mrzDobMatch[1]);
    }

    // --- 3) Fallback: field-based OCR ---
    const nieMatch = text.match(/\b([XYZ]\d{7}[A-Z])\b/);
    const dniMatch = text.match(/\b(\d{8}[A-Z])\b/);
    if (nieMatch) { result.documentNumber = nieMatch[1]; result.documentType = 'nie'; }
    else if (dniMatch) { result.documentNumber = dniMatch[1]; result.documentType = 'dni'; }
    else if (upperText.includes('PASAPORTE') || upperText.includes('PASSPORT')) {
        result.documentType = 'passport';
        const m = joined.match(/\b([A-Z]{1,2}\d{5,8}[A-Z]?)\b/);
        if (m) result.documentNumber = m[1];
    }

    // Names by field labels: search whole text for label:value patterns
    if (!result.lastName && !result.firstName) {
        // Try "APELLIDOS: VALUE" and "NOMBRE: VALUE" on the same line
        const apM = text.match(/APELLIDOS?\s*[:\s]+([A-Za-zÀ-ÿÑñ\s'-]{2,}?)(?:\s+(?:NOMBRE|NACIONALIDAD|FECHA|SEXO|DOCUMENTO|DNI|DOMICILIO)|$)/i);
        if (apM) result.lastName = cleanName(apM[1]);
        const nomM = text.match(/NOMBRE\s*[:\s]+([A-Za-zÀ-ÿÑñ\s'-]{2,}?)(?:\s+(?:APELLIDO|NACIONALIDAD|FECHA|SEXO|DOCUMENTO|DNI)|$)/i);
        if (nomM) result.firstName = cleanName(nomM[1]);
    }
    // If one-line patterns didn't work, try multi-line (label on one line, value on next)
    if (!result.lastName || !result.firstName) {
        let apellidosIdx = -1, nombreIdx = -1;
        lines.forEach((l, i) => {
            const u = l.toUpperCase().replace(/[^A-Z\s:]/g, '');
            if (u.startsWith('APELLID') && apellidosIdx === -1) apellidosIdx = i;
            if ((/^NOMBRE/.test(u) || u.trim() === 'NOM') && nombreIdx === -1) nombreIdx = i;
        });
        if (!result.lastName && apellidosIdx >= 0) {
            // Check same line first (label: value)
            const sameLine = lines[apellidosIdx].replace(/^[^:]*[:]\s*/, '').trim();
            if (sameLine.length >= 2 && !labelWords.has(sameLine.toUpperCase().split(/\s+/)[0])) {
                result.lastName = cleanName(sameLine);
            } else if (apellidosIdx + 1 < lines.length) {
                const n = lines[apellidosIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s'-]/g, '').trim();
                const firstWord = n.toUpperCase().split(/\s+/)[0];
                if (n.length >= 2 && !labelWords.has(firstWord)) result.lastName = cleanName(n);
            }
        }
        if (!result.firstName && nombreIdx >= 0) {
            const sameLine = lines[nombreIdx].replace(/^[^:]*[:]\s*/, '').trim();
            if (sameLine.length >= 2 && !labelWords.has(sameLine.toUpperCase().split(/\s+/)[0])) {
                result.firstName = cleanName(sameLine);
            } else if (nombreIdx + 1 < lines.length) {
                const n = lines[nombreIdx + 1].replace(/[^A-Za-zÀ-ÿÑñ\s'-]/g, '').trim();
                const firstWord = n.toUpperCase().split(/\s+/)[0];
                if (n.length >= 2 && !labelWords.has(firstWord)) result.firstName = cleanName(n);
            }
        }
    }
    // Final attempt: find uppercase name-like words near document number, excluding labels
    if (!result.lastName && !result.firstName && result.documentNumber) {
        const pos = upperText.indexOf(result.documentNumber);
        if (pos > 10) {
            const before = upperText.substring(0, pos).trim();
            const words = before.split(/[\s,;:<]+/).filter(w => /^[A-ZÀ-ÿ]{3,}$/.test(w) && !labelWords.has(w));
            if (words.length >= 2) {
                result.lastName = words.slice(-2).join(' ').substring(0, 50);
                if (words.length >= 4) result.firstName = words.slice(-4, -2).join(' ');
            } else if (words.length === 1) {
                result.lastName = words[0];
            }
        }
    }

    // Nationality
    if (!result.nationality) {
        const natMap = { 'ESP':'ES', 'ESPAÑA':'ES', 'SPAIN':'ES', 'FRANCE':'FR', 'FRANCIA':'FR', 'GERMANY':'DE', 'ALEMANIA':'DE', 'ITALIA':'IT', 'ITALY':'IT', 'GB':'GB', 'REINO UNIDO':'GB', 'PORTUGAL':'PT', 'USA':'US', 'ESTADOS UNIDOS':'US' };
        for (const [k, v] of Object.entries(natMap)) { if (upperText.includes(k)) { result.nationality = v; break; } }
        if (!result.nationality) {
            const m = text.match(/NACIONALIDAD[:\s]*([A-Za-zÀ-ÿ]{2,6})/i);
            if (m) {
                const c = m[1].toUpperCase();
                result.nationality = natMap[c] || (c.length === 2 ? c : null);
            }
        }
    }

    // Birth date by field labels
    if (!result.birthDate) {
        const dateRegex = /(\d{2})[\/\s-](\d{2})[\/\s-](\d{4})/g;
        const nacIdx = upperText.indexOf('NACIMIENTO');
        if (nacIdx >= 0) {
            const after = upperText.substring(nacIdx, Math.min(nacIdx + 80, upperText.length));
            const mm = dateRegex.exec(after);
            if (mm) { const d=parseInt(mm[1]),mo=parseInt(mm[2]),y=parseInt(mm[3]); if(d>=1&&d<=31&&mo>=1&&mo<=12&&y>=1900&&y<=2010) result.birthDate = `${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`; }
        }
        if (!result.birthDate) {
            // Also try "FECHA NACIMIENTO" or "F.NAC" patterns
            const fnacM = upperText.match(/FECHA\s+NAC[:\s]+(\d{2})[\/\s-](\d{2})[\/\s-](\d{4})/);
            if (fnacM) result.birthDate = `${fnacM[3]}-${fnacM[2].padStart(2,'0')}-${fnacM[1].padStart(2,'0')}`;
        }
    }
    // Last resort: find any valid birth-like date
    if (!result.birthDate) {
        const allDates = []; let m;
        const dr = /(\d{2})[\/\s-](\d{2})[\/\s-](\d{4})/g;
        while ((m = dr.exec(upperText)) !== null) {
            const d=parseInt(m[1]),mo=parseInt(m[2]),y=parseInt(m[3]);
            if (d>=1&&d<=31&&mo>=1&&mo<=12&&y>=1900&&y<=2010)
                allDates.push(`${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
        }
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
