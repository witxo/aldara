(function () {
'use strict';

function log() {
  return function () {
    var args = Array.prototype.slice.call(arguments);
    args.unshift('[DocScannerWebMRZ]');
    console.log.apply(console, args);
  };
}

document.addEventListener('alpine:init', function () {
  Alpine.data('documentScannerWebmrz', function (config) {
    config = config || {};
    var logger = log();

    return {
      prefix: config.prefix || '',
      suffix: config.suffix || '',
      debug: config.debug || false,

      showScanner: false,
      status: 'idle',
      errorMessage: '',
      lastResult: null,
      lastResultFields: {},

      _reader: null,

      get statusText() {
        var t = {
          idle: '',
          camera: 'Alinee el documento',
          capturing: 'Procesando...',
          success: 'Documento leído correctamente'
        };
        return t[this.status] || this.errorMessage;
      },

      get hasResult() {
        return this.lastResult !== null;
      },

      get resultEntries() {
        var entries = [];
        if (!this.lastResult) return entries;
        var map = {
          documentType: 'Tipo',
          documentNumber: 'Nº documento',
          supportNumber: 'Nº soporte',
          surname: 'Apellidos',
          givenNames: 'Nombre',
          birthDate: 'Fecha nacimiento',
          nationality: 'Nacionalidad'
        };
        var self = this;
        Object.keys(map).forEach(function (k) {
          if (self.lastResult[k]) {
            entries.push({ label: map[k], value: self.lastResult[k] });
          }
        });
        return entries;
      },

      init: function () {
        logger('Component initialized');
      },

      openScanner: function () {
        this._resetSession();
        this.showScanner = true;
        this.status = 'camera';
        this.errorMessage = '';

        var self = this;
        this.$nextTick(function () {
          if (typeof MRZReader === 'undefined') {
            self._fail('Lector MRZ no disponible. Recargue la página.');
            return;
          }
          var container = self.$refs.cameraContainer;
          if (!container) {
            self._fail('Error interno: contenedor no encontrado');
            return;
          }
          container.innerHTML = '';
          try {
            self._reader = MRZReader.initMRZReader({
              container: container,
              workerPath: '/tesseract/worker.min.js',
              corePath: '/tesseract/',
              langPath: '/model/',
              onResult: function (result) {
                if (result && result.parsed && typeof result.parsed === 'object') {
                  self._handleParsed(result.parsed);
                } else {
                  var msg = typeof result?.parsed === 'string' ? result.parsed : 'No se pudo leer el documento';
                  self._fail(msg);
                }
              },
              onError: function (err) {
                self._fail(typeof err === 'string' ? err : 'Error al leer el documento');
              }
            });
            logger('MRZReader initialized');
          } catch (e) {
            self._fail('Error al iniciar lector: ' + e.message);
          }
        });
      },

      capture: function () {
        if (this._reader) {
          this.status = 'capturing';
          try {
            this._reader.capture();
          } catch (e) {
            this._fail('Error al capturar: ' + e.message);
          }
        }
      },

      closeScanner: function () {
        if (this._reader) {
          try { this._reader.stop(); } catch (e) {}
          this._reader = null;
        }
        this.showScanner = false;
        this.status = 'idle';
        logger('Scanner closed');
      },

      uploadFile: function (event) {
        logger('uploadFile called');
        var file = event && event.target && event.target.files ? event.target.files[0] : null;
        if (!file) return;

        this._resetSession();
        this.showScanner = false;
        this.status = 'processing';
        var self = this;

        var img = new Image();
        img.onload = function () {
          var vw = img.width;
          var vh = img.height;
          var cropX = Math.floor(vw * 0.04);
          var cropW = Math.floor(vw * 0.92);
          var cropY = Math.floor(vh * 0.62);
          var cropH = Math.floor(vh * 0.35);

          var canvas = document.createElement('canvas');
          canvas.width = cropW;
          canvas.height = cropH;
          var ctx = canvas.getContext('2d');
          ctx.drawImage(img, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);

          self._processImage(canvas);
          URL.revokeObjectURL(img.src);
        };
        img.onerror = function () {
          self._fail('No se pudo cargar la imagen');
        };
        img.src = URL.createObjectURL(file);
      },

      _processImage: function (canvas) {
        var self = this;
        if (typeof window.Tesseract === 'undefined') {
          self._fail('Tesseract no disponible');
          return;
        }
        logger('Starting OCR with Tesseract.recognize()...');
        var timedOut = false;
        var timeoutId = setTimeout(function () {
          timedOut = true;
          self._fail('Tiempo de espera agotado (30s). Pruebe con una foto más nítida.');
        }, 30000);

        window.Tesseract.recognize(canvas, 'mrz', {
          workerPath: '/tesseract/worker.min.js',
          corePath: '/tesseract/',
          langPath: '/model/'
        }).then(function (result) {
          if (timedOut) return;
          clearTimeout(timeoutId);
          logger('OCR completed');
          var text = result && result.data ? result.data.text : '';
          logger('OCR text:', text ? text.replace(/\n/g, ' | ').substring(0, 200) : '(empty)');
          var cleanText = text ? text.replace(/[^A-Z0-9<]/g, '') : '';
          logger('Cleaned MRZ:', cleanText ? cleanText.substring(0, 90) : '(empty)');
          var extracted = cleanText ? MRZReader.extractMRZData(cleanText) : null;
          if (extracted && extracted.parsed && typeof extracted.parsed === 'object') {
            self._handleParsed(extracted.parsed);
          } else {
            var errMsg = typeof extracted?.parsed === 'string' ? extracted.parsed : 'No se detectó MRZ válida en la imagen';
            self._fail(errMsg);
          }
        }).catch(function (err) {
          if (timedOut) return;
          clearTimeout(timeoutId);
          logger('OCR error:', err);
          self._fail('Error al procesar la imagen: ' + (err?.message || err));
        });
      },

      _handleParsed: function (parsed) {
        logger('Parsed:', parsed);
        var record = this._mapToRecord(parsed);
        this.lastResult = record;
        this._populateForm(record);
        this.status = 'success';
        var self = this;
        setTimeout(function () { self.closeScanner(); }, 1200);
      },

      _mapToRecord: function (parsed) {
        var docType = '';
        var dt = parsed['Document Type'] || '';
        if (dt) {
          var first = dt.charAt(0).toUpperCase();
          if (first === 'P') docType = 'passport';
          else if (first === 'I' || first === '1') docType = dt.charAt(1) === 'N' ? 'nie' : 'dni';
          else docType = 'dni';
        }

        var gender = parsed.Gender || '';
        var sex = '';
        if (gender === 'Male') sex = 'M';
        else if (gender === 'Female') sex = 'F';

        var birthDateRaw = parsed['Date of Birth'] || '';
        var birthDate = '';
        if (birthDateRaw.length === 6) {
          birthDate = this._parseDate(birthDateRaw);
        } else if (birthDateRaw) {
          birthDate = birthDateRaw;
        }

        var documentNumber = parsed['Document Number'] || '';
        var supportNumber = '';
        var optData1 = parsed['Optional Data 1'] || parsed['Optional Data'] || '';
        if (docType === 'dni' && optData1) {
          supportNumber = documentNumber;
          documentNumber = optData1.replace(/</g, '');
        }

        var surname = parsed.Surname || '';
        var givenNames = parsed['Given Names'] || '';
        var surname2 = '';

        if (docType === 'dni' || docType === 'nie') {
          // DNI/NIE: givenNames may contain second surname at the end
          var tokens = givenNames.replace(/\s+/g, ' ').trim().split(' ');
          if (tokens.length >= 2) {
            surname2 = tokens.pop();
            givenNames = tokens.join(' ');
          }
        } else {
          // Passport: surname field may contain both surnames
          var parts = surname.replace(/\s+/g, ' ').trim().split(' ');
          if (parts.length >= 2) {
            surname = parts[0];
            surname2 = parts.slice(1).join(' ');
          }
        }

        return {
          documentType: docType,
          documentNumber: documentNumber,
          supportNumber: supportNumber,
          surname: surname,
          surname2: surname2,
          givenNames: givenNames,
          birthDate: birthDate,
          nationality: parsed.Nationality || '',
          sex: sex
        };
      },

      _parseDate: function (raw) {
        if (!raw || raw.length < 6 || /^<+$/.test(raw)) return '';
        var yy = parseInt(raw.substring(0, 2), 10);
        var mm = raw.substring(2, 4);
        var dd = raw.substring(4, 6);
        var d = parseInt(dd, 10);
        var m = parseInt(mm, 10);
        if (m < 1 || m > 12 || d < 1 || d > 31) return '';
        var fullYear = yy >= 50 ? 1900 + yy : 2000 + yy;
        return fullYear + '-' + mm + '-' + dd;
      },

      _mapNationality: function (code) {
        if (!code || code.length <= 2) return code;
        var map = {
          'ESP': 'ES', 'FRA': 'FR', 'GBR': 'GB', 'DEU': 'DE',
          'ITA': 'IT', 'PRT': 'PT', 'BEL': 'BE', 'NLD': 'NL',
          'CHE': 'CH', 'AUT': 'AT', 'DNK': 'DK', 'SWE': 'SE',
          'NOR': 'NO', 'FIN': 'FI', 'GRC': 'GR', 'IRL': 'IE',
          'POL': 'PL', 'CZE': 'CZ', 'HUN': 'HU', 'ROU': 'RO',
          'BGR': 'BG', 'HRV': 'HR', 'SVK': 'SK', 'SVN': 'SI',
          'LTU': 'LT', 'LVA': 'LV', 'EST': 'EE', 'USA': 'US',
          'CAN': 'CA', 'MEX': 'MX', 'BRA': 'BR', 'ARG': 'AR',
          'CHL': 'CL', 'COL': 'CO', 'PER': 'PE', 'JPN': 'JP',
          'CHN': 'CN', 'IND': 'IN', 'RUS': 'RU', 'TUR': 'TR',
          'AUS': 'AU', 'NZL': 'NZ', 'MAR': 'MA', 'DZA': 'DZ',
          'TUN': 'TN', 'EGY': 'EG', 'ZAF': 'ZA'
        };
        return map[code.toUpperCase()] || code.substring(0, 2);
      },

      _populateForm: function (record) {
        if (!record) return;
        var self = this;

        var f = function (name) {
          return self.prefix + name + self.suffix;
        };

        var fields = {};
        if (record.givenNames) fields['first_name'] = record.givenNames;
        if (record.surname) fields['last_name'] = record.surname;
        if (record.surname2) fields['last_name2'] = record.surname2;
        if (record.documentNumber) fields['document_number'] = record.documentNumber;
        if (record.supportNumber) fields['document_support'] = record.supportNumber;
        if (record.birthDate) fields['birth_date'] = record.birthDate;
        if (record.documentType) fields['document_type'] = record.documentType;
        if (record.nationality) fields['nationality'] = self._mapNationality(record.nationality);
        if (record.sex) fields['gender'] = record.sex === 'M' ? 'male' : (record.sex === 'F' ? 'female' : '');

        Object.keys(fields).forEach(function (key) {
          var name = f(key);
          var el = document.querySelector('[name="' + name + '"]');
          if (el) {
            if (el.tagName === 'SELECT') {
              var opts = el.options;
              var found = false;
              for (var j = 0; j < opts.length; j++) {
                if (opts[j].value === fields[key]) {
                  el.value = fields[key];
                  found = true;
                  break;
                }
              }
              if (!found && key === 'nationality') {
                el.value = 'other';
              } else if (!found && key === 'gender') {
                el.value = 'other';
              }
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
            }, 4000);
          }
        });

        self.lastResultFields = fields;
        logger('Form populated:', JSON.stringify(fields));
      },

      _resetSession: function () {
        this.errorMessage = '';
        this.lastResult = null;
        this.lastResultFields = {};
      },

      _fail: function (message) {
        this.status = 'error';
        this.errorMessage = message || 'Error desconocido';
      },

      retry: function () {
        this._resetSession();
        this.closeScanner();
        var self = this;
        setTimeout(function () { self.openScanner(); }, 100);
      }
    };
  });
});

})();
