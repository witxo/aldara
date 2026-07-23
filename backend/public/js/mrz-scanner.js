class MRZScanner {
    constructor(options = {}) {
        this.onResult = options.onResult || null;
        this.formPrefix = options.formPrefix || 'guests[0]';
        this.stream = null;
        this.video = null;
        this.canvas = null;
        this.ctx = null;
        this.modal = null;
        this.bestResult = null;
        this.bestConfidence = 0;
        this.captureCount = 0;
        this.maxCaptures = options.maxCaptures || 10;
        this.docType = 'dni';
    }

    openCamera(docType = 'dni') {
        this.docType = docType;
        this.bestResult = null;
        this.bestConfidence = 0;
        this.captureCount = 0;
        this._createModal();
        this._startCamera();
    }

    closeCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(t => t.stop());
            this.stream = null;
        }
        if (this.modal) {
            this.modal.remove();
            this.modal = null;
        }
        this.video = null;
        this.canvas = null;
        this.ctx = null;
    }

    captureFrame() {
        if (!this.video || !this.canvas) return;

        const vw = this.video.videoWidth;
        const vh = this.video.videoHeight;

        const cropY = Math.floor(vh * 0.65);
        const cropH = Math.floor(vh * 0.35);

        this.canvas.width = vw;
        this.canvas.height = cropH;
        this.ctx.drawImage(this.video, 0, cropY, vw, cropH, 0, 0, vw, cropH);

        this._preprocessImage(this.canvas);
        this._recognizeMRZ(this.canvas);
    }

    _createModal() {
        this.closeCamera();

        const label = this.docType === 'dni'
            ? 'DNI (parte trasera)'
            : 'Pasaporte (zona inferior)';

        const modal = document.createElement('div');
        modal.id = 'mrz-modal';
        modal.className = 'fixed inset-0 bg-black z-[9999] flex flex-col';
        modal.innerHTML = `
            <div class="flex items-center justify-between bg-gray-900 text-white px-4 py-3 shrink-0">
                <span class="font-semibold text-sm">Escanear ${label}</span>
                <span id="mrz-status" class="text-xs text-gray-400">Esperando captura...</span>
                <button onclick="window.mrzScanner && window.mrzScanner.closeCamera()" class="text-white text-2xl leading-none hover:text-gray-300">&times;</button>
            </div>
            <div class="flex-1 relative bg-black flex items-center justify-center min-h-0">
                <video id="mrz-video" class="max-w-full max-h-full object-contain" autoplay playsinline></video>
                <div id="mrz-overlay" class="absolute left-[5%] right-[5%] bottom-[10%] h-[28%] border-2 border-dashed border-green-400 rounded-lg pointer-events-none flex items-center justify-center">
                    <span class="text-green-400 text-xs font-medium bg-black/60 px-2 py-1 rounded select-none">Encuadre el MRZ aquí</span>
                </div>
            </div>
            <div class="bg-gray-900 text-white px-4 py-3 flex items-center justify-between shrink-0">
                <div class="text-xs">
                    <span id="mrz-confidence" class="text-gray-400">Confianza: --</span>
                    <span id="mrz-attempt" class="text-gray-500 ml-2"></span>
                </div>
                <button id="mrz-capture-btn" onclick="window.mrzScanner && window.mrzScanner.captureFrame()" class="bg-green-600 hover:bg-green-700 active:bg-green-800 text-white px-6 py-2 rounded-lg font-semibold text-sm transition">
                    Escanear
                </button>
            </div>
            <div id="mrz-result" class="hidden bg-gray-800 text-gray-200 px-4 py-3 text-xs border-t border-gray-700 shrink-0"></div>
        `;
        document.body.appendChild(modal);
        this.modal = modal;
        this.video = document.getElementById('mrz-video');
    }

    async _startCamera() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
            });
            this.video.srcObject = this.stream;
            await this.video.play();

            this.canvas = document.createElement('canvas');
            this.ctx = this.canvas.getContext('2d');
        } catch (err) {
            this._setStatus('Error: No se pudo acceder a la cámara');
            console.error('Camera error:', err);
        }
    }

    _setStatus(msg) {
        const el = document.getElementById('mrz-status');
        if (el) el.textContent = msg;
    }

    _setConfidence(val) {
        const el = document.getElementById('mrz-confidence');
        if (el) el.textContent = 'Confianza: ' + val + '%';
    }

    _showResult(html) {
        const el = document.getElementById('mrz-result');
        if (el) {
            el.innerHTML = html;
            el.classList.remove('hidden');
        }
    }

    _preprocessImage(canvas) {
        const ctx = canvas.getContext('2d');
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;

        for (let i = 0; i < data.length; i += 4) {
            const gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            data[i] = data[i + 1] = data[i + 2] = gray;
        }

        const histogram = new Array(256).fill(0);
        for (let i = 0; i < data.length; i += 4) {
            histogram[Math.round(data[i])]++;
        }

        let total = data.length / 4;
        let sum = 0;
        for (let i = 0; i < 256; i++) sum += i * histogram[i];

        let sumB = 0, wB = 0, wF = 0;
        let maxVariance = 0, threshold = 128;

        for (let i = 0; i < 256; i++) {
            wB += histogram[i];
            if (wB === 0) continue;
            wF = total - wB;
            if (wF === 0) break;

            sumB += i * histogram[i];
            const mB = sumB / wB;
            const mF = (sum - sumB) / wF;
            const variance = wB * wF * (mB - mF) * (mB - mF);

            if (variance > maxVariance) {
                maxVariance = variance;
                threshold = i;
            }
        }

        for (let i = 0; i < data.length; i += 4) {
            const val = data[i] < threshold ? 0 : 255;
            data[i] = data[i + 1] = data[i + 2] = val;
        }

        ctx.putImageData(imageData, 0, 0);
    }

    async _recognizeMRZ(canvas) {
        if (typeof Tesseract === 'undefined') {
            this._setStatus('Error: Tesseract.js no está cargado');
            return;
        }

        this._setStatus('Reconociendo MRZ...');

        try {
            const { data } = await Tesseract.recognize(canvas, 'eng', {
                psm: 6,
                config: {
                    tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<'
                },
                logger: (m) => {
                    if (m.status === 'recognizing text') {
                        const pct = Math.round(m.progress * 100);
                        this._setStatus('Reconociendo... ' + pct + '%');
                    }
                }
            });

            const result = this._extractMRZFromText(data.text, data.words);

            if (result) {
                const parsed = this._parseMRZ(result);

                if (parsed) {
                    const validated = this._validateCheckDigits(parsed);
                    const confidence = this._calculateConfidence(validated);

                    this.captureCount++;

                    if (confidence > this.bestConfidence) {
                        this.bestResult = validated;
                        this.bestConfidence = confidence;
                    }

                    this._setConfidence(Math.round(confidence * 100));
                    this._showResult(this._formatResult(validated, confidence));

                    if (confidence >= 0.9 || this.captureCount >= this.maxCaptures) {
                        if (this.bestResult && this.bestConfidence >= 0.5) {
                            this._setStatus('MRZ válido. Cerrando...');
                            this.populateForm(this.bestResult);
                            setTimeout(() => this.closeCamera(), 800);
                        } else {
                            this._setStatus('No se pudo leer el MRZ correctamente. Intente de nuevo.');
                        }
                    } else {
                        this._setStatus('Intento ' + this.captureCount + '/' + this.maxCaptures + '. Capture de nuevo para mejorar.');
                        const at = document.getElementById('mrz-attempt');
                        if (at) at.textContent = '(' + this.captureCount + '/' + this.maxCaptures + ')';
                    }
                } else {
                    this._setStatus('MRZ detectado pero no se pudo interpretar. Intente de nuevo.');
                }
            } else {
                this._setStatus('No se detectó MRZ. Asegúrese de que la franja de caracteres esté visible y bien iluminada.');
            }
        } catch (err) {
            console.error('Tesseract error:', err);
            this._setStatus('Error al procesar la imagen');
        }
    }

    _extractMRZFromText(text, words) {
        const lines = text.split('\n')
            .map(l => l.trim().replace(/\s+/g, ''))
            .filter(l => l.length > 20);

        const td1Lines = lines.filter(l => /^I[<A-Z0-9]/.test(l));
        if (td1Lines.length >= 3) {
            return { format: 'TD1', lines: td1Lines.slice(0, 3) };
        }

        const td3First = lines.filter(l => /^P<[A-Z]{3}/.test(l));
        const td3Second = lines.filter(l => /^[A-Z0-9<]{40,}$/.test(l));
        if (td3First.length >= 1 && td3Second.length >= 1) {
            return { format: 'TD3', lines: [td3First[0], td3Second[0]] };
        }

        if (words && words.length > 5) {
            const mrzWords = words.filter(w =>
                /^[A-Z0-9<]+$/.test(w.text) && w.text.length > 3
            );

            const rows = {};
            for (const w of mrzWords) {
                const y = Math.round((w.bbox.y0 || 0) / 8) * 8;
                if (!rows[y]) rows[y] = [];
                rows[y].push(w);
            }

            const sortedYs = Object.keys(rows).sort((a, b) => a - b);
            const candidateLines = sortedYs
                .map(y => rows[y].sort((a, b) => (a.bbox.x0 || 0) - (b.bbox.x0 || 0)).map(w => w.text).join(''))
                .filter(l => l.length > 20);

            if (candidateLines.length >= 2) {
                if (/^I/.test(candidateLines[0])) {
                    return { format: 'TD1', lines: candidateLines.slice(0, 3) };
                }
                if (/^P/.test(candidateLines[0])) {
                    return { format: 'TD3', lines: candidateLines.slice(0, 2) };
                }
            }
        }

        return null;
    }

    _parseMRZ(data) {
        const { format, lines } = data;

        if (format === 'TD1' && lines.length >= 3) {
            return this._parseTD1(lines);
        } else if (format === 'TD3' && lines.length >= 2) {
            return this._parseTD3(lines);
        }

        return null;
    }

    _parseTD1(lines) {
        const line1 = lines[0].replace(/[^A-Z0-9<]/g, '');
        const line2 = lines[1].replace(/[^A-Z0-9<]/g, '');
        const line3 = lines[2].replace(/[^A-Z0-9<]/g, '');

        const docType = line1.substring(0, 1);
        const country = line1.substring(2, 5);
        const docNumber = line1.substring(5, 14).replace(/</g, '');
        const docNumberCheck = line1.length > 14 ? line1.substring(14, 15) : '';
        const optional1 = line1.length > 15 ? line1.substring(15, 29).replace(/</g, '') : '';
        const finalCheck = line1.length > 29 ? line1.substring(29, 30) : '';

        const birthDateRaw = line2.length > 6 ? line2.substring(0, 6) : '';
        const birthDateCheck = line2.length > 7 ? line2.substring(6, 7) : '';
        const sex = line2.length > 8 ? line2.substring(7, 8) : '';
        const expiryDateRaw = line2.length > 14 ? line2.substring(8, 14) : '';
        const expiryDateCheck = line2.length > 15 ? line2.substring(14, 15) : '';
        const nationality = line2.length > 18 ? line2.substring(15, 18) : '';
        const optional2 = line2.length > 18 ? line2.substring(18, 29).replace(/</g, '') : '';

        const nameParts = line3.split('<<').filter(p => p.replace(/</g, '').length > 0);
        let surname = '', givenNames = '';
        if (nameParts.length >= 1) {
            surname = nameParts[0].replace(/</g, ' ').trim();
            if (nameParts.length >= 2) {
                givenNames = nameParts.slice(1).join(' ').replace(/</g, ' ').trim();
            }
        }

        return {
            format: 'TD1',
            raw: { line1: line1, line2: line2, line3: line3 },
            docType: docType === 'I' || docType === '1' ? 'dni' : (docType === 'P' ? 'passport' : 'unknown'),
            country: country,
            documentNumber: docNumber,
            documentNumberValid: null,
            birthDate: this._parseMRZDate(birthDateRaw),
            birthDateValid: null,
            sex: sex === 'M' ? 'M' : (sex === 'F' ? 'F' : ''),
            expiryDate: this._parseMRZDate(expiryDateRaw),
            expiryDateValid: null,
            nationality: nationality,
            surname: surname,
            givenNames: givenNames,
            checkDigits: {
                documentNumber: docNumberCheck,
                birthDate: birthDateCheck,
                expiryDate: expiryDateCheck,
                final: finalCheck
            }
        };
    }

    _parseTD3(lines) {
        const line1 = lines[0].replace(/[^A-Z0-9<]/g, '');
        const line2 = lines[1].replace(/[^A-Z0-9<]/g, '');

        const docType = line1.substring(0, 1);
        const country = line1.substring(2, 5);

        const namePart = line1.length > 5 ? line1.substring(5).replace(/<+$/, '') : '';
        const nameParts = namePart.split('<<').filter(p => p.replace(/</g, '').length > 0);
        let surname = '', givenNames = '';
        if (nameParts.length >= 1) {
            surname = nameParts[0].replace(/</g, ' ').trim();
            if (nameParts.length >= 2) {
                givenNames = nameParts.slice(1).join(' ').replace(/</g, ' ').trim();
            }
        }

        let idx = 0;
        const passportNumber = line2.substring(idx, idx + 9).replace(/</g, '');
        idx += 9;
        const passportCheck = line2.substring(idx, idx + 1);
        idx += 1;
        const nationality = line2.substring(idx, idx + 3);
        idx += 3;
        const birthDateRaw = line2.substring(idx, idx + 6);
        idx += 6;
        const birthDateCheck = line2.substring(idx, idx + 1);
        idx += 1;
        const sex = line2.substring(idx, idx + 1);
        idx += 1;
        const expiryDateRaw = line2.substring(idx, idx + 6);
        idx += 6;
        const expiryDateCheck = line2.substring(idx, idx + 1);
        idx += 1;
        const personalNumber = line2.substring(idx, idx + 14).replace(/</g, '');
        idx += 14;

        return {
            format: 'TD3',
            raw: { line1: line1, line2: line2 },
            docType: 'passport',
            country: country,
            documentNumber: passportNumber,
            documentNumberValid: null,
            birthDate: this._parseMRZDate(birthDateRaw),
            birthDateValid: null,
            sex: sex === 'M' ? 'M' : (sex === 'F' ? 'F' : ''),
            expiryDate: this._parseMRZDate(expiryDateRaw),
            expiryDateValid: null,
            nationality: nationality,
            surname: surname,
            givenNames: givenNames,
            checkDigits: {
                documentNumber: passportCheck,
                birthDate: birthDateCheck,
                expiryDate: expiryDateCheck,
                final: ''
            }
        };
    }

    _parseMRZDate(raw) {
        if (!raw || raw.length < 6 || /^<+$/.test(raw)) return '';
        const yy = raw.substring(0, 2);
        const mm = raw.substring(2, 4);
        const dd = raw.substring(4, 6);
        const year = parseInt(yy, 10);
        const fullYear = year >= 50 ? 1900 + year : 2000 + year;
        return fullYear + '-' + mm + '-' + dd;
    }

    _validateCheckDigits(record) {
        const weights = [7, 3, 1];

        const calcCheckDigit = (str) => {
            let sum = 0;
            for (let i = 0; i < str.length; i++) {
                const c = str[i];
                let val;
                if (c >= '0' && c <= '9') val = c.charCodeAt(0) - 48;
                else if (c >= 'A' && c <= 'Z') val = c.charCodeAt(0) - 55;
                else val = 0;
                sum += val * weights[i % 3];
            }
            return (sum % 10).toString();
        };

        if (record.format === 'TD1') {
            const l1 = (record.raw && record.raw.line1) || '';
            const l2 = (record.raw && record.raw.line2) || '';

            if (l1.length > 14) {
                const docStr = l1.substring(5, 14);
                record.documentNumberValid = calcCheckDigit(docStr) === record.checkDigits.documentNumber;
            }

            if (l2.length > 6) {
                const birthStr = l2.substring(0, 6);
                record.birthDateValid = calcCheckDigit(birthStr) === record.checkDigits.birthDate;
            }

            if (l2.length > 14) {
                const expiryStr = l2.substring(8, 14);
                record.expiryDateValid = calcCheckDigit(expiryStr) === record.checkDigits.expiryDate;
            }
        } else if (record.format === 'TD3') {
            const l2 = (record.raw && record.raw.line2) || '';

            if (l2.length > 9) {
                const docStr = l2.substring(0, 9);
                record.documentNumberValid = calcCheckDigit(docStr) === record.checkDigits.documentNumber;
            }

            if (l2.length > 19) {
                const birthStr = l2.substring(13, 19);
                record.birthDateValid = calcCheckDigit(birthStr) === record.checkDigits.birthDate;
            }

            if (l2.length > 27) {
                const expiryStr = l2.substring(21, 27);
                record.expiryDateValid = calcCheckDigit(expiryStr) === record.checkDigits.expiryDate;
            }
        }

        return record;
    }

    _calculateConfidence(record) {
        let score = 0;
        let factors = 0;

        if (record.documentNumberValid !== null) {
            score += record.documentNumberValid ? 0.4 : 0;
            factors += 0.4;
        }
        if (record.birthDateValid !== null) {
            score += record.birthDateValid ? 0.3 : 0;
            factors += 0.3;
        }
        if (record.expiryDateValid !== null) {
            score += record.expiryDateValid ? 0.3 : 0;
            factors += 0.3;
        }

        if (factors === 0) factors = 1;

        let completeness = 0;
        if (record.documentNumber) completeness += 0.3;
        if (record.surname) completeness += 0.25;
        if (record.givenNames) completeness += 0.25;
        if (record.birthDate) completeness += 0.2;

        return (score / factors) * 0.6 + Math.min(completeness, 1) * 0.4;
    }

    _formatResult(record, confidence) {
        const docTypeLabel = record.docType === 'dni' ? 'DNI' : 'Pasaporte';
        const validIcon = function(v) {
            if (v === true) return '&#10003;';
            if (v === false) return '&#10007;';
            return '?';
        };

        return '<div class="space-y-1">' +
            '<div class="font-semibold mb-1">Resultado (' + Math.round(confidence * 100) + '% confianza):</div>' +
            '<div class="grid grid-cols-2 gap-x-4 gap-y-1">' +
            '<span>Tipo: ' + docTypeLabel + '</span>' +
            '<span>Pa\u00eds: ' + (record.country || '') + '</span>' +
            '<span>N\u00famero: ' + (record.documentNumber || '') + ' ' + validIcon(record.documentNumberValid) + '</span>' +
            '<span>Apellidos: ' + (record.surname || '') + '</span>' +
            '<span>Nombre: ' + (record.givenNames || '') + '</span>' +
            '<span>Nacimiento: ' + (record.birthDate || '') + ' ' + validIcon(record.birthDateValid) + '</span>' +
            '<span>Caducidad: ' + (record.expiryDate || '') + ' ' + validIcon(record.expiryDateValid) + '</span>' +
            '<span>Sexo: ' + (record.sex || '') + '</span>' +
            '</div></div>';
    }

    populateForm(record) {
        var prefix = this.formPrefix;

        var setVal = function(name, value) {
            var el = document.querySelector('[name="' + prefix + '[' + name + ']"]') ||
                     document.querySelector('[name="' + name + '"]');
            if (el) {
                el.value = value || '';
                if (value) {
                    el.style.borderColor = '#22c55e';
                    el.style.backgroundColor = '#f0fdf4';
                } else {
                    el.style.borderColor = '';
                    el.style.backgroundColor = '';
                }
            }
        };

        var docTypeEl = document.querySelector('[name="' + prefix + '[document_type]"]');
        if (docTypeEl) {
            docTypeEl.value = record.docType;
        }

        setVal('document_number', record.documentNumber);
        setVal('first_name', record.givenNames);
        setVal('last_name', record.surname);
        setVal('birth_date', record.birthDate);

        var natEl = document.querySelector('[name="' + prefix + '[nationality]"]');
        if (natEl && record.country) {
            if (natEl.querySelector('option[value="' + record.country + '"]')) {
                natEl.value = record.country;
            } else {
                natEl.value = 'other';
            }
        }

        if (this.onResult) {
            this.onResult(record);
        }
    }
}
