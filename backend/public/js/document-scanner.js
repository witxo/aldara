(function () {
'use strict';

function log(debug) {
  return function () {
    if (debug) {
      var args = Array.prototype.slice.call(arguments);
      args.unshift('[DocScanner]');
      console.log.apply(console, args);
    }
  };
}

document.addEventListener('alpine:init', function () {
  Alpine.data('documentScanner', function (config) {
    config = config || {};
    var logger = log(config.debug || false);

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
        this.showCamera = true;
        this.status = 'camera';
        this.errorMessage = '';
        this.lastResult = null;
        this.lastResultFields = {};
        this._bestResult = null;
        this._bestConfidence = 0;
        this._captureCount = 0;

        var self = this;
        this.$nextTick(function () {
          self._videoEl = self.$refs.video;
          if (!self._videoEl) {
            self.status = 'error';
            self.errorMessage = 'Error interno: no se encontró el elemento de video';
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
          self.status = 'error';
          self.errorMessage = 'No se pudo acceder a la cámara: ' + err.message;
          logger('Camera error:', err);
        });
      },

      closeCamera: function () {
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
        var cropY = Math.floor(vh * 0.62);
        var cropH = Math.floor(vh * 0.35);

        this._canvasEl.width = vw;
        this._canvasEl.height = cropH;
        var ctx = this._canvasEl.getContext('2d');
        ctx.drawImage(this._videoEl, 0, cropY, vw, cropH, 0, 0, vw, cropH);

        this._preprocess(ctx);
        this._recognizeMRZ();
      },

      _preprocess: function (ctx) {
        var w = this._canvasEl.width;
        var h = this._canvasEl.height;
        var imageData = ctx.getImageData(0, 0, w, h);
        var data = imageData.data;
        var n = data.length;
        var i;

        for (i = 0; i < n; i += 4) {
          var gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
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

        ctx.putImageData(imageData, 0, 0);
      },

      _recognizeMRZ: function () {
        if (typeof Tesseract === 'undefined') {
          this.status = 'error';
          this.errorMessage = 'Tesseract.js no está cargado';
          return;
        }

        this.status = 'processing';
        var self = this;

        Tesseract.recognize(this._canvasEl, 'eng', {
          psm: 6,
          config: { tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<' },
          logger: function (m) {
            if (m.status === 'recognizing text' && self.debug) {
              logger('OCR:', Math.round(m.progress * 100) + '%');
            }
          }
        }).then(function (result) {
          var text = result.data.text;
          logger('OCR result:', text.replace(/\n/g, ' | '));

          var parsed = MRZParser.parse(text);
          self._captureCount++;

          if (parsed && parsed.confidence >= self.confidenceThreshold) {
            if (parsed.confidence > self._bestConfidence) {
              self._bestResult = parsed;
              self._bestConfidence = parsed.confidence;
            }
            logger('Parsed: conf=' + parsed.confidence.toFixed(2), parsed.surname, parsed.givenNames);

            if (parsed.confidence >= 0.85 || self._captureCount >= self.maxCaptures) {
              self.lastResult = self._bestResult || parsed;
              self.lastResultFields = {};
              self._populateForm(self.lastResult);
              self.status = 'success';
              setTimeout(function () { self.closeCamera(); }, 1200);
            } else {
              self.status = 'camera';
              logger('Low conf (' + parsed.confidence.toFixed(2) + '), retry ' + self._captureCount + '/' + self.maxCaptures);
              setTimeout(function () {
                if (self.showCamera) self.capture();
              }, 600);
            }
          } else {
            if (self._captureCount >= self.maxCaptures) {
              self.status = 'error';
              self.errorMessage = parsed
                ? 'Confianza baja (' + Math.round(parsed.confidence * 100) + '%). Intente con mejor iluminación.'
                : 'No se detectó MRZ válida. Asegúrese de que la franja de caracteres sea visible y esté bien iluminada.';
            } else {
              self.status = 'camera';
              logger('No MRZ (attempt ' + self._captureCount + '/' + self.maxCaptures + ')');
              setTimeout(function () {
                if (self.showCamera) self.capture();
              }, 600);
            }
          }
        }).catch(function (err) {
          self.status = 'error';
          self.errorMessage = 'Error al procesar: revise la iluminación e intente de nuevo';
          logger('Tesseract error:', err);
        });
      },

      uploadFile: function (event) {
        var file = event.target.files ? event.target.files[0] : null;
        if (!file) return;

        this.showCamera = false;
        this.status = 'processing';
        var self = this;

        var img = new Image();
        img.onload = function () {
          var canvas = document.createElement('canvas');
          var cropY = Math.floor(img.height * 0.62);
          var cropH = Math.floor(img.height * 0.35);
          canvas.width = img.width;
          canvas.height = cropH;
          var ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, cropY, img.width, cropH, 0, 0, img.width, cropH);
          self._preprocess(ctx);
          self._canvasEl = canvas;
          self._recognizeMRZ();
          URL.revokeObjectURL(img.src);
        };
        img.onerror = function () {
          self.status = 'error';
          self.errorMessage = 'No se pudo cargar la imagen';
        };
        img.src = URL.createObjectURL(file);
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
        if (record.documentNumber) fields['document_number'] = record.documentNumber;
        if (record.birthDate) fields['birth_date'] = record.birthDate;
        if (record.documentType) fields['document_type'] = record.documentType;
        if (record.nationality) fields['nationality'] = record.nationality;
        if (record.sex) fields['gender'] = record.sex;

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
              }
            } else {
              el.value = fields[key];
            }
            el.style.borderColor = '#22c55e';
            el.style.backgroundColor = '#f0fdf4';
            setTimeout(function () {
              el.style.borderColor = '';
              el.style.backgroundColor = '';
            }, 4000);
          }
        });

        self.lastResultFields = fields;
        logger('Form populated:', Object.keys(fields).join(', '));
      },

      retry: function () {
        this.errorMessage = '';
        this.lastResult = null;
        this.lastResultFields = {};
        this._bestResult = null;
        this._bestConfidence = 0;
        this._captureCount = 0;
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