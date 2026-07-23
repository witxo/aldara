(function () {
'use strict';

function log() {
  return function () {
    var args = Array.prototype.slice.call(arguments);
    args.unshift('[DocScanner]');
    console.log.apply(console, args);
  };
}

document.addEventListener('alpine:init', function () {
  Alpine.data('documentScanner', function (config) {
    config = config || {};
    var logger = log();

    return {
      prefix: config.prefix || '',
      suffix: config.suffix || '',
      debug: config.debug || false,
      maxCaptures: config.maxCaptures || 10,
      confidenceThreshold: config.confidenceThreshold || 0.55,

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
        var t = {
          idle: '',
          camera: 'Alinee el documento en la guía',
          capturing: 'Capturando...',
          processing: 'Procesando documento...',
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

      openCamera: function () {
        this._resetSession();
        this.showCamera = true;
        this.status = 'camera';
        this.errorMessage = '';

        var self = this;
        this.$nextTick(function () {
          self._videoEl = self.$refs.video;
          if (!self._videoEl) {
            self._fail('Error interno: no se encontró el elemento de video');
            return;
          }
          self._startStream();
        });
      },

      _startStream: function () {
        var self = this;
        navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
        }).then(function (stream) {
          self._stream = stream;
          self._videoEl.srcObject = stream;
          return self._videoEl.play();
        }).then(function () {
          self._canvasEl = document.createElement('canvas');
          logger('Camera started');
        }).catch(function (err) {
          self._fail('No se pudo acceder a la cámara: ' + err.message);
          logger('Camera error:', err);
        });
      },

      closeCamera: function () {
        if (this._captureTimer) {
          clearTimeout(this._captureTimer);
          this._captureTimer = null;
        }
        if (this._stream) {
          this._stream.getTracks().forEach(function (t) { t.stop(); });
          this._stream = null;
        }
        this._videoEl = null;
        this._canvasEl = null;
        this.showCamera = false;
        this.status = 'idle';
        logger('Camera closed');
      },

      capture: function () {
        if (!this._videoEl || !this._canvasEl) return;
        this.status = 'capturing';

        var vw = this._videoEl.videoWidth || 640;
        var vh = this._videoEl.videoHeight || 480;
        var cropX = Math.floor(vw * 0.04);
        var cropW = Math.floor(vw * 0.92);
        var cropY = Math.floor(vh * 0.62);
        var cropH = Math.floor(vh * 0.35);

        this._canvasEl.width = cropW;
        this._canvasEl.height = cropH;
        var ctx = this._canvasEl.getContext('2d');
        ctx.drawImage(this._videoEl, cropX, cropY, cropW, cropH, 0, 0, cropW, cropH);

        this._preprocess(ctx);
        this._recognizeMRZ();
      },

      _preprocess: function (ctx) {
        var w = this._canvasEl.width;
        var h = this._canvasEl.height;
        if (w < 10 || h < 10) return;

        var scaled = document.createElement('canvas');
        var sctx = scaled.getContext('2d');
        scaled.width = w * 2;
        scaled.height = h * 2;
        sctx.drawImage(this._canvasEl, 0, 0, scaled.width, scaled.height);

        var imageData = sctx.getImageData(0, 0, scaled.width, scaled.height);
        var data = imageData.data;
        var n = data.length;
        var i;

        for (i = 0; i < n; i += 4) {
          var gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
          gray = Math.max(0, Math.min(255, (gray - 128) * 1.35 + 128));
          data[i] = data[i + 1] = data[i + 2] = gray;
        }

        var histogram = new Array(256).fill(0);
        for (i = 0; i < n; i += 4) {
          histogram[Math.round(data[i])]++;
        }
        var total = n / 4;
        var sum = 0;
        for (i = 0; i < 256; i++) sum += i * histogram[i];

        var sumB = 0, wB = 0, wF = 0;
        var maxVariance = 0, threshold = 128;
        for (i = 0; i < 256; i++) {
          wB += histogram[i];
          if (wB === 0) continue;
          wF = total - wB;
          if (wF === 0) break;
          sumB += i * histogram[i];
          var mB = sumB / wB;
          var mF = (sum - sumB) / wF;
          var variance = wB * wF * (mB - mF) * (mB - mF);
          if (variance > maxVariance) {
            maxVariance = variance;
            threshold = i;
          }
        }

        for (i = 0; i < n; i += 4) {
          var val = data[i] < threshold ? 0 : 255;
          data[i] = data[i + 1] = data[i + 2] = val;
        }

        sctx.putImageData(imageData, 0, 0);

        this._canvasEl.width = scaled.width;
        this._canvasEl.height = scaled.height;
        ctx.drawImage(scaled, 0, 0);
      },

      _recognizeMRZ: function () {
        if (typeof Tesseract === 'undefined') {
          this._fail('Tesseract.js no está cargado');
          return;
        }

        this.status = 'processing';
        var self = this;

        logger('Starting OCR...');

        var timedOut = false;
        var timeoutId = setTimeout(function () {
          timedOut = true;
          logger('OCR TIMED OUT after 60s');
          self._handleResult(null, true);
        }, 60000);

        var tryWithAPI = function () {
          if (typeof Tesseract.createWorker === 'function') {
            logger('Using createWorker API');
            Tesseract.createWorker('eng').then(function (worker) {
              if (timedOut) { try { worker.terminate(); } catch(e) {} return; }
              return worker.setParameters({
                tessedit_pageseg_mode: '6'
              }).then(function () {
                if (timedOut) { try { worker.terminate(); } catch(e) {} return null; }
                logger('Worker ready, recognizing...');
                return worker.recognize(self._canvasEl);
              }).then(function (result) {
                if (timedOut) { try { worker.terminate(); } catch(e) {} return; }
                clearTimeout(timeoutId);
                try { worker.terminate(); } catch(e) {}
                var text = result && result.data ? result.data.text : '';
                logger('OCR result:', text ? text.replace(/\n/g, ' | ').substring(0, 200) : '(empty)');
                self._handleResult(text, false);
              }).catch(function (err) {
                clearTimeout(timeoutId);
                try { worker.terminate(); } catch(e) {}
                logger('Tesseract worker error:', err);
                if (!timedOut) {
                  logger('Falling back to recognize() API');
                  tryRecognizeAPI();
                } else {
                  self._handleResult(null, false);
                }
              });
            }).catch(function (err) {
              clearTimeout(timeoutId);
              logger('Tesseract createWorker error:', err);
              if (!timedOut) {
                logger('Falling back to recognize() API');
                tryRecognizeAPI();
              } else {
                self._handleResult(null, false);
              }
            });
          } else {
            tryRecognizeAPI();
          }
        };

        var tryRecognizeAPI = function () {
          if (timedOut) { self._handleResult(null, true); return; }
          logger('Using recognize() API');
          Tesseract.recognize(self._canvasEl, 'eng', {
            logger: function (m) {
              if (m.status === 'recognizing text') {
                self.status = 'processing';
              }
            }
          }).then(function (result) {
            if (timedOut) return;
            clearTimeout(timeoutId);
            var text = result && result.data ? result.data.text : '';
            logger('OCR recognize result:', text ? text.replace(/\n/g, ' | ').substring(0, 200) : '(empty)');
            self._handleResult(text, false);
          }).catch(function (err) {
            clearTimeout(timeoutId);
            logger('Tesseract recognize error:', err);
            self._handleResult(null, false);
          });
        };

        tryWithAPI();
      },

      _handleResult: function (text, timedOut) {
        this._captureCount++;
        var parsed = text ? MRZParser.parse(text) : null;
        logger('parsed', parsed);

        if (parsed && parsed.confidence > this._bestConfidence) {
          this._bestResult = parsed;
          this._bestConfidence = parsed.confidence;
        }

        var accepted = parsed &&
          parsed.confidence >= this.confidenceThreshold &&
          parsed.documentNumber &&
          (parsed.surname || parsed.givenNames);

        if (accepted) {
          this.lastResult = this._bestResult || parsed;
          this._populateForm(this.lastResult);
          this.status = 'success';
          logger('Accepted: conf=' + parsed.confidence.toFixed(2));
          var self = this;
          setTimeout(function () { self.closeCamera(); }, 1200);
          return;
        }

        if (this._captureCount >= this.maxCaptures) {
          if (this._bestResult &&
              this._bestResult.confidence >= this.confidenceThreshold &&
              this._bestResult.documentNumber) {
            this.lastResult = this._bestResult;
            this._populateForm(this._bestResult);
            this.status = 'success';
            logger('Using best result (conf=' + this._bestConfidence.toFixed(2) + ')');
            var self = this;
            setTimeout(function () { self.closeCamera(); }, 1200);
            return;
          }
          this._fail(timedOut
            ? 'Tiempo de espera agotado (60s). Pruebe con una foto más nítida, bien iluminada, con la zona de caracteres visible.'
            : (parsed
              ? 'Confianza baja (' + Math.round(parsed.confidence * 100) + '%). Intente con mejor iluminación.'
              : 'No se detectó MRZ válida. Asegúrese de que la franja de caracteres sea visible y esté bien iluminada.'));
          return;
        }

        this.status = 'camera';
        logger('Retry ' + this._captureCount + '/' + this.maxCaptures + (parsed ? ' conf=' + parsed.confidence.toFixed(2) : ' no MRZ'));
        var self = this;
        this._captureTimer = setTimeout(function () {
          if (self.showCamera) self.capture();
        }, 600);
      },

      uploadFile: function (event) {
        logger('uploadFile called', event ? event.type : 'no event');
        var file = event && event.target && event.target.files ? event.target.files[0] : null;
        if (!file) {
          logger('uploadFile: no file selected');
          return;
        }
        logger('uploadFile: file selected', file.name, file.type, file.size);

        this._resetSession();
        this.showCamera = false;
        this.status = 'processing';
        logger('uploadFile: status set to processing');
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
          self._canvasEl = canvas;

          self._preprocess(ctx);
          self._recognizeMRZ();
          URL.revokeObjectURL(img.src);
        };
        img.onerror = function () {
          self._fail('No se pudo cargar la imagen');
        };
        img.src = URL.createObjectURL(file);
      },

      _mapNationality: function (code) {
        if (!code || code.length < 2) return code;
        if (code.length === 2) return code;
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
        var firstName = record.givenNames || '';
        var lastName = record.surname || '';
        if (record.docType === 'dni' && record.givenNames) {
          var tokens = record.givenNames.replace(/\s+/g, ' ').trim().split(' ');
          if (tokens.length >= 2) {
            firstName = tokens.pop();
            lastName = (record.surname + ' ' + tokens.join(' ')).trim();
          } else {
            firstName = record.givenNames;
          }
        }
        if (firstName) fields['first_name'] = firstName;
        if (lastName) fields['last_name'] = lastName;
        if (record.documentNumber) fields['document_number'] = record.documentNumber;
        if (record.birthDate) fields['birth_date'] = record.birthDate;

        if (record.docType) {
          fields['document_type'] = record.docType;
        } else if (record.documentType) {
          fields['document_type'] = record.documentType;
        }

        if (record.nationality) {
          fields['nationality'] = self._mapNationality(record.nationality);
        }

        if (record.sex) {
          fields['gender'] = record.sex === 'M' ? 'male' : (record.sex === 'F' ? 'female' : '');
        }

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
      },

      retry: function () {
        this._resetSession();
        this.status = 'camera';
        this.showCamera = true;
        var self = this;
        this.$nextTick(function () {
          self._videoEl = self.$refs.video;
          if (self._videoEl && !self._stream) {
            self._startStream();
          }
        });
      }
    };
  });
});

})();