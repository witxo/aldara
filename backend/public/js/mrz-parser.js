(function () {
    'use strict';

    var MRZParser = {
        parse: function (text) {
            if (!text) return null;

            var candidates = this._extractCandidateLines(text);
            if (!candidates.length) return null;

            var best = null;

            for (var i = 0; i < candidates.length; i++) {
                var parsed = this._parseCandidate(candidates[i]);
                if (!parsed) continue;

                parsed.confidence = this._calculateConfidence(parsed);

                if (!best || parsed.confidence > best.confidence) {
                    best = parsed;
                }
            }

            if (!best) return null;

            if (best.surname) best.surname = this._cleanPersonName(best.surname);
            if (best.givenNames) best.givenNames = this._cleanPersonName(best.givenNames);

            if (!best.surname && !best.givenNames) return null;

            return best;
        },

        _extractCandidateLines: function (text) {
            var rawLines = String(text)
                .toUpperCase()
                .split(/\r?\n/)
                .map(function (line) {
                    return line.replace(/[^A-Z0-9<]/g, '');
                })
                .filter(function (line) {
                    return line.length >= 20;
                });

            var candidates = [];
            var i;

            for (i = 0; i < rawLines.length; i++) {
                rawLines[i] = this._normalizeOcrLine(rawLines[i]);
            }

            for (i = 0; i + 2 < rawLines.length; i++) {
                var a = rawLines[i];
                var b = rawLines[i + 1];
                var c = rawLines[i + 2];

                if (a.length >= 25 && b.length >= 25 && c.length >= 25 && /^[IAC]/.test(a)) {
                    candidates.push([this._fit(a, 30), this._fit(b, 30), this._fit(c, 30)]);
                }
            }

            for (i = 0; i + 1 < rawLines.length; i++) {
                var l1 = rawLines[i];
                var l2 = rawLines[i + 1];

                if (/^P</.test(l1) && l1.length >= 35 && l2.length >= 35) {
                    candidates.push([this._fit(l1, 44), this._fit(l2, 44)]);
                }

                if (/^[IAC]/.test(l1) && l1.length >= 30 && l2.length >= 30 && l1.length < 44 && l2.length < 44) {
                    candidates.push([this._fit(l1, 36), this._fit(l2, 36)]);
                }
            }

            var compact = String(text).toUpperCase().replace(/[^A-Z0-9<]/g, '');
            compact = this._normalizeOcrLine(compact);

            var idxP = compact.indexOf('P<');
            while (idxP !== -1 && idxP + 88 <= compact.length) {
                candidates.push([
                    compact.slice(idxP, idxP + 44),
                    compact.slice(idxP + 44, idxP + 88)
                ]);
                idxP = compact.indexOf('P<', idxP + 1);
            }

            var prefixes = ['I<', 'A<', 'C<', 'ID', 'IA', 'IC'];
            for (var p = 0; p < prefixes.length; p++) {
                var idx = compact.indexOf(prefixes[p]);
                while (idx !== -1 && idx + 90 <= compact.length) {
                    candidates.push([
                        this._fit(compact.slice(idx, idx + 30), 30),
                        this._fit(compact.slice(idx + 30, idx + 60), 30),
                        this._fit(compact.slice(idx + 60, idx + 90), 30)
                    ]);
                    idx = compact.indexOf(prefixes[p], idx + 1);
                }
            }

            return candidates;
        },

        _parseCandidate: function (lines) {
            if (lines.length === 3) {
                return this._parseTD1(lines);
            }

            if (lines.length === 2) {
                if ((lines[0] || '').length >= 40 || /^P</.test(lines[0] || '')) {
                    return this._parseTD3(lines);
                }

                return this._parseTD2(lines);
            }

            return null;
        },

        _parseTD1: function (lines) {
            var l1 = this._fit(lines[0], 30);
            var l2 = this._fit(lines[1], 30);
            var l3 = this._fit(lines[2], 30);

            var names = this._splitNames(l3);
            var rawDocumentNumber = l1.slice(5, 14);
            var documentCheck = l1.charAt(14);
            var birthRaw = l2.slice(0, 6);
            var birthCheck = l2.charAt(6);
            var sex = l2.charAt(7);
            var expiryRaw = l2.slice(8, 14);
            var expiryCheck = l2.charAt(14);
            var nationality = l2.slice(15, 18);
            var compositeRaw = l1.slice(5, 30) + l2.slice(0, 7) + l2.slice(8, 15) + l2.slice(18, 29);
            var compositeCheck = l2.charAt(29);

            var record = {
                format: 'TD1',
                docType: this._mapDocType(l1.charAt(0)),
                issuingCountry: l1.slice(2, 5),
                documentNumber: this._cleanDocumentNumber(rawDocumentNumber),
                surname: names.surname,
                givenNames: names.givenNames,
                birthDate: this._parseDate(birthRaw),
                expiryDate: this._parseDate(expiryRaw),
                nationality: nationality.replace(/</g, ''),
                sex: this._normalizeSex(sex),
                checkDigits: {
                    documentNumber: documentCheck,
                    birthDate: birthCheck,
                    expiryDate: expiryCheck,
                    composite: compositeCheck
                },
                validation: {
                    documentNumber: this._check(rawDocumentNumber, documentCheck),
                    birthDate: this._check(birthRaw, birthCheck),
                    expiryDate: this._check(expiryRaw, expiryCheck),
                    composite: this._check(compositeRaw, compositeCheck)
                }
            };

            record.documentNumberValid = record.validation.documentNumber;
            record.birthDateValid = record.validation.birthDate;
            record.expiryDateValid = record.validation.expiryDate;

            return record;
        },

        _parseTD2: function (lines) {
            var l1 = this._fit(lines[0], 36);
            var l2 = this._fit(lines[1], 36);

            var names = this._splitNames(l1.slice(5));
            var rawDocumentNumber = l2.slice(0, 9);
            var documentCheck = l2.charAt(9);
            var nationality = l2.slice(10, 13);
            var birthRaw = l2.slice(13, 19);
            var birthCheck = l2.charAt(19);
            var sex = l2.charAt(20);
            var expiryRaw = l2.slice(21, 27);
            var expiryCheck = l2.charAt(27);
            var compositeRaw = l2.slice(0, 10) + l2.slice(13, 20) + l2.slice(21, 35);
            var compositeCheck = l2.charAt(35);

            var record = {
                format: 'TD2',
                docType: this._mapDocType(l1.charAt(0)),
                issuingCountry: l1.slice(2, 5),
                documentNumber: this._cleanDocumentNumber(rawDocumentNumber),
                surname: names.surname,
                givenNames: names.givenNames,
                birthDate: this._parseDate(birthRaw),
                expiryDate: this._parseDate(expiryRaw),
                nationality: nationality.replace(/</g, ''),
                sex: this._normalizeSex(sex),
                checkDigits: {
                    documentNumber: documentCheck,
                    birthDate: birthCheck,
                    expiryDate: expiryCheck,
                    composite: compositeCheck
                },
                validation: {
                    documentNumber: this._check(rawDocumentNumber, documentCheck),
                    birthDate: this._check(birthRaw, birthCheck),
                    expiryDate: this._check(expiryRaw, expiryCheck),
                    composite: this._check(compositeRaw, compositeCheck)
                }
            };

            record.documentNumberValid = record.validation.documentNumber;
            record.birthDateValid = record.validation.birthDate;
            record.expiryDateValid = record.validation.expiryDate;

            return record;
        },

        _parseTD3: function (lines) {
            var l1 = this._fit(lines[0], 44);
            var l2 = this._fit(lines[1], 44);

            var names = this._splitNames(l1.slice(5));
            var rawDocumentNumber = l2.slice(0, 9);
            var documentCheck = l2.charAt(9);
            var nationality = l2.slice(10, 13);
            var birthRaw = l2.slice(13, 19);
            var birthCheck = l2.charAt(19);
            var sex = l2.charAt(20);
            var expiryRaw = l2.slice(21, 27);
            var expiryCheck = l2.charAt(27);
            var personalNumber = l2.slice(28, 42);
            var personalCheck = l2.charAt(42);
            var compositeRaw = l2.slice(0, 10) + l2.slice(13, 20) + l2.slice(21, 43);
            var compositeCheck = l2.charAt(43);

            var record = {
                format: 'TD3',
                docType: 'passport',
                issuingCountry: l1.slice(2, 5),
                documentNumber: this._cleanDocumentNumber(rawDocumentNumber),
                surname: names.surname,
                givenNames: names.givenNames,
                birthDate: this._parseDate(birthRaw),
                expiryDate: this._parseDate(expiryRaw),
                nationality: nationality.replace(/</g, ''),
                sex: this._normalizeSex(sex),
                personalNumber: personalNumber.replace(/</g, ''),
                checkDigits: {
                    documentNumber: documentCheck,
                    birthDate: birthCheck,
                    expiryDate: expiryCheck,
                    personalNumber: personalCheck,
                    composite: compositeCheck
                },
                validation: {
                    documentNumber: this._check(rawDocumentNumber, documentCheck),
                    birthDate: this._check(birthRaw, birthCheck),
                    expiryDate: this._check(expiryRaw, expiryCheck),
                    personalNumber: this._check(personalNumber, personalCheck),
                    composite: this._check(compositeRaw, compositeCheck)
                }
            };

            record.documentNumberValid = record.validation.documentNumber;
            record.birthDateValid = record.validation.birthDate;
            record.expiryDateValid = record.validation.expiryDate;

            return record;
        },

        _splitNames: function (raw) {
            var clean = this._fit(raw || '', (raw || '').length).replace(/<+$/g, '');
            var parts = clean.split('<<');
            var surname = this._cleanPersonName(parts[0] || '');
            var givenNames = this._cleanPersonName(parts.slice(1).join(' '));

            return {
                surname: surname,
                givenNames: givenNames
            };
        },

        _cleanPersonName: function (value) {
            if (!value) return '';

            var text = value
                .replace(/</g, ' ')
                .replace(/[^A-ZÁÉÍÓÚÜÑ\s'-]/gi, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            var tokens = text.split(' ').filter(Boolean);
            var cleanTokens = [];

            for (var i = 0; i < tokens.length; i++) {
                var t = tokens[i];

                if (/^[BCDFGHJKLMNPQRSTVWXYZ]{6,}$/.test(t)) continue;
                if (/^(.)\1{2,}$/.test(t)) continue;
                if (/^[A-Z]{1}$/.test(t) && tokens.length > 2) continue;
                if (/^(KL|LK|LL|KK|XX|YY|ZZ){2,}$/i.test(t)) continue;

                cleanTokens.push(t);
            }

            return cleanTokens.join(' ').substring(0, 60).trim();
        },

        _cleanDocumentNumber: function (raw) {
            if (!raw) return '';

            var normalized = raw
                .replace(/</g, '')
                .replace(/O/g, '0')
                .replace(/Q/g, '0')
                .replace(/I/g, '1')
                .replace(/L/g, '1')
                .replace(/Z/g, '2')
                .replace(/S/g, '5')
                .replace(/B/g, '8')
                .replace(/G/g, '6')
                .replace(/\s+/g, '');

            return normalized.substring(0, 15);
        },

        _normalizeOcrLine: function (line) {
            return String(line || '')
                .toUpperCase()
                .replace(/«|»/g, '<')
                .replace(/K(?=<{2,})/g, '<')
                .replace(/X(?=<{2,})/g, '<')
                .replace(/[‘’`´]/g, '')
                .replace(/ /g, '')
                .replace(/[^A-Z0-9<]/g, '');
        },

        _fit: function (value, len) {
            value = String(value || '');
            if (value.length > len) return value.slice(0, len);
            return value.padEnd(len, '<');
        },

        _mapDocType: function (ch) {
            if (ch === 'P') return 'passport';
            if (ch === 'I' || ch === 'A' || ch === 'C') return 'id';
            return 'document';
        },

        _normalizeSex: function (value) {
            if (value === 'M') return 'M';
            if (value === 'F') return 'F';
            return '';
        },

        _parseDate: function (raw) {
            if (!raw || raw.length !== 6 || /[^0-9]/.test(raw)) return '';

            var yy = parseInt(raw.slice(0, 2), 10);
            var mm = parseInt(raw.slice(2, 4), 10);
            var dd = parseInt(raw.slice(4, 6), 10);

            if (mm < 1 || mm > 12 || dd < 1 || dd > 31) return '';

            var currentYear = new Date().getFullYear() % 100;
            var fullYear = yy <= currentYear ? (2000 + yy) : (1900 + yy);

            return fullYear + '-' +
                String(mm).padStart(2, '0') + '-' +
                String(dd).padStart(2, '0');
        },

        _charValue: function (ch) {
            if (ch >= '0' && ch <= '9') return ch.charCodeAt(0) - 48;
            if (ch >= 'A' && ch <= 'Z') return ch.charCodeAt(0) - 55;
            return 0;
        },

        _calculateCheckDigit: function (input) {
            var weights = [7, 3, 1];
            var sum = 0;

            for (var i = 0; i < input.length; i++) {
                sum += this._charValue(input.charAt(i)) * weights[i % 3];
            }

            return String(sum % 10);
        },

        _check: function (raw, digit) {
            if (!raw || !digit || /[^0-9]/.test(digit)) return null;
            return this._calculateCheckDigit(raw) === digit;
        },

        _calculateConfidence: function (record) {
            var score = 0;

            if (record.validation.documentNumber === true) score += 0.30;
            if (record.validation.birthDate === true) score += 0.20;
            if (record.validation.expiryDate === true) score += 0.15;
            if (record.validation.composite === true) score += 0.20;
            if (record.surname) score += 0.07;
            if (record.givenNames) score += 0.05;
            if (record.nationality && record.nationality.length === 3) score += 0.03;

            if (record.surname && /[BCDFGHJKLMNPQRSTVWXYZ]{7,}/.test(record.surname)) score -= 0.20;
            if (record.givenNames && /[BCDFGHJKLMNPQRSTVWXYZ]{7,}/.test(record.givenNames)) score -= 0.20;
            if (record.documentNumber && !/^[A-Z0-9]{5,15}$/.test(record.documentNumber)) score -= 0.20;
            if (record.documentNumberValid === false) score -= 0.25;

            if (score < 0) score = 0;
            if (score > 1) score = 1;

            return score;
        }
    };

    window.MRZParser = MRZParser;
})();