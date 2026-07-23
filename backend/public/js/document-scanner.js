(function () {
    'use strict';

    function createLogger(debug) {
        return function () {
            if (!debug) return;
            var args = Array.prototype.slice.call(arguments);
            args.unshift('[DocScanner]');
            console.log.apply(console, args);
        };
    }

    document.addEventListener('alpine:init', function () {
        Alpine.data('documentScanner', function (config) {
            config = config || {};
            var logger = createLogger(!!config.debug);

            return {
                prefix: config.prefix || '',
                suffix: config.suffix || '',
                debug: !!config.debug,
                maxCaptures: config.maxCaptures || 6,
                confidenceThreshold: config.confidenceThreshold || 0.72,

                showCamera: false,
                status: 'idle',
                errorMessage: '',
                lastResult: null,
                lastResultFields: {},

                _stream: null,
                _videoEl: null,
                _canvasEl: null,
                _bestResult: null,
                _bestConfidence: 0,
                _captureCount: 0,
                _captureTimer: null,

                get statusText() {
                    var labels = {
                        idle: '',
                        camera: 'Alinee la MRZ dentro de la guía',
                        capturing: 'Capturando imagen...',
                        processing: 'Procesando documento...',
                        success: 'Documento leído correctamente',
                        error: this.errorMessage
                    };
                    return labels[this.status] || '';
                },

                get hasResult() {
                    return !!this.lastResult;
                },

                get resultEntries() {
                    if (!this.lastResult) return [];

                    var map = {
                        docType: 'Tipo',
                        documentNumber: 'Nº documento',
                        surname: 'Apellidos',
                        givenNames: 'Nombre',
                        birthDate: 'Fecha nacimiento',
                        nationality: 'Nacionalidad',
                        format: 'Formato',
                        confidence: 'Confianza'
                    };

                    var out = [];
                    var self = this;

                    Object.keys(map).forEach(function (key) {
                        var value = self.lastResult[key];
                        if (value === null || value === undefined || value === '') return;
                        if (key === 'confidence') value = Math.round(value * 100) + '%';
                        out.push({ label: map[key], value: value });
                    });

                    return out;
                },

                init: function () {
                    logger('initialized');
                },

                openCamera: function () {
                    this._resetSession();
                    this.showCamera = true;
                    this.status = 'camera';

                    var self = this;
                    this.$nextTick(function () {
                        self._videoEl = self.$refs.video;
                        if (!self._videoEl) {
                            self._fail('No se encontró el elemento de vídeo');
                            return;
                        }
                        self._startStream();
                    });
                },

                _startStream: function () {
                    var self = this;

                    navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: 'environment' },
                            width: { ideal: 1920 },
                            height: { ideal: 1080 }
                        },
                        audio: false
                    }).then(function (stream) {
                        self._stream = stream;
                        self._videoEl.srcObject = stream;
                        return self._videoEl.play();
                    }).then(function () {
                        self._canvasEl = document.createElement('canvas');
                        self.status = 'camera';
                        logger('camera started');
                    }).catch(function (err) {
                        self._fail('No se pudo acceder a la cámara: ' + err.message);
                    });
                },

                closeCamera: function () {
                    if (this._captureTimer) {
                        clearTimeout(this._captureTimer);
                        this._captureTimer = null;
                    }

                    if (this._stream) {
                        this._stream.getTracks().forEach(function (track) {
                            track.stop();
                        });
                    }

                    this._stream = null;
                    this._videoEl = null;
                    this._canvasEl = null;
                    this.showCamera = false;
                    this.status = 'idle';
                    logger('camera closed');
                },

                retry: function () {
                    if (this.showCamera && this._stream) {
                        this.errorMessage = '';
                        this.lastResult = null;
                        this.lastResultFields = {};
                        this._bestResult = null;
                        this._bestConfidence = 0;
                        this._captureCount = 0;
                        this.status = 'camera';
                        return;
                    }

                    this.openCamera();
                },

                capture: function () {
                    if (!this._videoEl || !this._canvasEl) return;

                    this.status = 'capturing';

                    var crop = this._captureMrzFromSource(this._videoEl.videoWidth, this._videoEl.videoHeight, this._videoEl);
                    if (!crop) {
                        this._fail('No se pudo capturar la imagen');
                        return;
                    }

                    this._canvasEl = crop;
                    this._preprocessCanvas(this._canvasEl);
                    this._recognizeMRZ(this._canvasEl);
                },

                uploadFile: function (event) {
                    var file = event.target && event.target.files ? event.target.files[0] : null;
                    if (!file) return;

                    this._resetSession();
                    this.status = 'processing';
                    this.showCamera = false;

                    var self = this;
                    var img = new Image();

                    img.onload = function () {
                        var canvas = self._captureMrzFromSource(img.width, img.height, img);
                        if (!canvas) {
                            self._fail('No se pudo preparar la imagen');
                            return;
                        }

                        self._canvasEl = canvas;
                        self._preprocessCanvas(self._canvasEl);
                        self._recognizeMRZ(self._canvasEl);
                        URL.revokeObjectURL(img.src);
                    };

                    img.onerror = function () {
                        self._fail('No se pudo cargar la imagen');
                    };

                    img.src = URL.createObjectURL(file);
                },

                _captureMrzFromSource: function (width, height, source) {
                    if (!width || !height || !source) return null;

                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');

                    var cropY = Math.floor(height * 0.60);
                    var cropH = Math.floor(height * 0.26);
                    var cropX = Math.floor(width * 0.04);
                    var cropW = Math.floor(width * 0.92);

                    canvas.width = cropW;
                    canvas.height = cropH;

                    ctx.drawImage(source, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);
                    return canvas;
                },

                _preprocessCanvas: function (canvas) {
                    var ctx = canvas.getContext('2d');
                    var w = canvas.width;
                    var h = canvas.height;
                    var scaled = document.createElement('canvas');
                    var sctx = scaled.getContext('2d');

                    scaled.width = w * 2;
                    scaled.height = h * 2;
                    sctx.drawImage(canvas, 0, 0, scaled.width, scaled.height);

                    var imageData = sctx.getImageData(0, 0, scaled.width, scaled.height);
                    var data = imageData.data;
                    var histogram = new Array(256).fill(0);

                    for (var i = 0; i < data.length; i += 4) {
                        var gray = Math.round(0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]);
                        gray = Math.max(0, Math.min(255, (gray - 128) * 1.35 + 128));
                        data[i] = data[i + 1] = data[i + 2] = gray;
                        histogram[gray]++;
                    }

                    var total = data.length / 4;
                    var sum = 0;
                    for (var j = 0; j < 256; j++) sum += j * histogram[j];

                    var sumB = 0;
                    var wB = 0;
                    var maxVariance = 0;
                    var threshold = 128;

                    for (var t = 0; t < 256; t++) {
                        wB += histogram[t];
                        if (!wB) continue;

                        var wF = total - wB;
                        if (!wF) break;

                        sumB += t * histogram[t];
                        var mB = sumB / wB;
                        var mF = (sum - sumB) / wF;
                        var variance = wB * wF * Math.pow(mB - mF, 2);

                        if (variance > maxVariance) {
                            maxVariance = variance;
                            threshold = t;
                        }
                    }

                    for (var k = 0; k < data.length; k += 4) {
                        var val = data[k] < threshold ? 0 : 255;
                        data[k] = data[k + 1] = data[k + 2] = val;
                    }

                    sctx.putImageData(imageData, 0, 0);

                    canvas.width = scaled.width;
                    canvas.height = scaled.height;
                    ctx.drawImage(scaled, 0, 0);
                },

                _recognizeMRZ: function (canvas) {
                    if (typeof Tesseract === 'undefined') {
                        this._fail('Tesseract.js no está cargado');
                        return;
                    }

                    this.status = 'processing';
                    var self = this;

                    Tesseract.recognize(canvas, 'eng', {
                        psm: 6,
                        config: {
                            tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<'
                        },
                        logger: function (m) {
                            if (self.debug && m.status === 'recognizing text') {
                                logger('ocr', Math.round(m.progress * 100) + '%');
                            }
                        }
                    }).then(function (result) {
                        self._captureCount++;

                        var text = result && result.data ? result.data.text : '';
                        logger('ocr text', text.replace(/\n/g, ' | '));

                        var parsed = MRZParser.parse(text);
                        logger('parsed', parsed);

                        if (parsed && parsed.confidence > self._bestConfidence) {
                            self._bestResult = parsed;
                            self._bestConfidence = parsed.confidence;
                        }

                        var accepted = parsed &&
                            parsed.confidence >= self.confidenceThreshold &&
                            parsed.documentNumber &&
                            parsed.documentNumberValid !== false &&
                            (parsed.surname || parsed.givenNames);

                        if (accepted) {
                            self.lastResult = parsed;
                            self._populateForm(parsed);
                            self.status = 'success';
                            setTimeout(function () {
                                self.closeCamera();
                            }, 1000);
                            return;
                        }

                        if (self._captureCount < self.maxCaptures && self.showCamera) {
                            self.status = 'camera';
                            self._captureTimer = setTimeout(function () {
                                self.capture();
                            }, 700);
                            return;
                        }

                        if (self._bestResult &&
                            self._bestResult.confidence >= self.confidenceThreshold &&
                            self._bestResult.documentNumber &&
                            self._bestResult.documentNumberValid !== false) {
                            self.lastResult = self._bestResult;
                            self._populateForm(self._bestResult);
                            self.status = 'success';
                            setTimeout(function () {
                                self.closeCamera();
                            }, 1000);
                            return;
                        }

                        self._fail('No se pudo leer una MRZ válida. Prueba con mejor luz, más enfoque y acercando la franja inferior.');
                    }).catch(function (err) {
                        logger('ocr error', err);
                        self._fail('Error al procesar el documento');
                    });
                },

                _populateForm: function (record) {
                    if (!record) return;

                    var self = this;
                    var fields = {};

                    if (record.givenNames) fields.first_name = record.givenNames.trim();
                    if (record.surname) fields.last_name = record.surname.trim();
                    if (record.documentNumber) fields.document_number = record.documentNumber.trim();
                    if (record.birthDate) fields.birth_date = record.birthDate;
                    if (record.docType) fields.document_type = record.docType;
                    if (record.nationality) fields.nationality = this._mapNationality(record.nationality);
                    if (record.sex) fields.gender = record.sex === 'M' ? 'male' : (record.sex === 'F' ? 'female' : '');

                    Object.keys(fields).forEach(function (key) {
                        var inputName = self.prefix + key + self.suffix;
                        var el = document.querySelector('[name="' + inputName + '"]');
                        if (!el) return;

                        if (el.tagName === 'SELECT') {
                            var found = false;
                            for (var i = 0; i < el.options.length; i++) {
                                if (el.options[i].value === fields[key]) {
                                    el.value = fields[key];
                                    found = true;
                                    break;
                                }
                            }
                            if (!found && key === 'nationality') el.value = 'other';
                            if (!found && key === 'gender') el.value = 'other';
                        } else {
                            el.value = fields[key];
                        }

                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));

                        el.style.borderColor = '#22c55e';
                        el.style.backgroundColor = '#f0fdf4';
                        setTimeout(function () {
                            el.style.borderColor = '';
                            el.style.backgroundColor = '';
                        }, 2500);
                    });

                    this.lastResultFields = fields;
                    logger('form populated', fields);
                },

                _mapNationality: function (code) {
                    if (!code || code.length < 2) return code;
                    if (code.length === 2) return code;

                    var map = {
                        ESP: 'ES',
                        FRA: 'FR',
                        GBR: 'GB',
                        DEU: 'DE',
                        ITA: 'IT',
                        PRT: 'PT',
                        BEL: 'BE',
                        NLD: 'NL',
                        CHE: 'CH',
                        AUT: 'AT',
                        DNK: 'DK',
                        SWE: 'SE',
                        NOR: 'NO',
                        FIN: 'FI',
                        GRC: 'GR',
                        IRL: 'IE',
                        POL: 'PL',
                        CZE: 'CZ',
                        HUN: 'HU',
                        ROU: 'RO',
                        BGR: 'BG',
                        HRV: 'HR',
                        SVK: 'SK',
                        SVN: 'SI',
                        LTU: 'LT',
                        LVA: 'LV',
                        EST: 'EE',
                        USA: 'US',
                        CAN: 'CA',
                        MEX: 'MX',
                        BRA: 'BR',
                        ARG: 'AR',
                        CHL: 'CL',
                        COL: 'CO',
                        PER: 'PE',
                        JPN: 'JP',
                        CHN: 'CN',
                        IND: 'IN',
                        RUS: 'RU',
                        TUR: 'TR',
                        AUS: 'AU',
                        NZL: 'NZ',
                        MAR: 'MA'
                    };

                    return map[code.toUpperCase()] || code.slice(0, 2);
                },

                _resetSession: function () {
                    this.errorMessage = '';
                    this.lastResult = null;
                    this.lastResultFields = {};
                    this._bestResult = null;
                    this._bestConfidence = 0;
                    this._captureCount = 0;

                    if (this._captureTimer) {
                        clearTimeout(this._captureTimer);
                        this._captureTimer = null;
                    }
                },

                _fail: function (message) {
                    this.status = 'error';
                    this.errorMessage = message || 'Error desconocido';
                }
            };
        });
    });
})();