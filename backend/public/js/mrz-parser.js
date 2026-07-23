(function () {
'use strict';

var MRZParser = {
  parse: function (text) {
    var lines = this._cleanText(text);
    if (lines.length < 2) return null;

    var format = this._detectFormat(lines);
    if (!format) return null;

    var record;
    if (format === 'TD1') record = this._parseTD1(lines);
    else if (format === 'TD2') record = this._parseTD2(lines);
    else if (format === 'TD3') record = this._parseTD3(lines);
    if (!record) return null;

    record = this._validateCheckDigits(record);
    record.confidence = this._calculateConfidence(record);
    return record;
  },

  _cleanText: function (text) {
    var lines = text.split('\n')
      .map(function (l) { return l.toUpperCase().replace(/[^A-Z0-9<]/g, ''); })
      .filter(function (l) { return l.length > 10; });

    if (lines.length > 0) return lines;

    var compressed = text.toUpperCase().replace(/[^A-Z0-9<\n]/g, '');
    return compressed.split('\n').filter(function (l) { return l.length > 10; });
  },

  _detectFormat: function (lines) {
    for (var i = 0; i < lines.length; i++) {
      var l0 = lines[i];

      if (/^I[<A-Z0-9]/.test(l0) && l0.length >= 25) {
        if (i + 2 < lines.length && lines[i + 1].length >= 25 && lines[i + 2].length >= 25) {
          return 'TD1';
        }
        if (i + 1 < lines.length && lines[i + 1].length >= 30 && lines[i + 1].length < 40) {
          return 'TD2';
        }
      }

      if (/^P[<A-Z]{2}/.test(l0) && l0.length >= 40) {
        if (i + 1 < lines.length && lines[i + 1].length >= 40) {
          return 'TD3';
        }
      }
    }

    if (lines.length >= 3) {
      var joined = lines.slice(0, 3).join('');
      if (joined.length >= 80) return 'TD1';
    }
    if (lines.length >= 2) {
      var joined2 = lines.slice(0, 2).join('');
      if (/^P/.test(lines[0]) && joined2.length >= 80) return 'TD3';
      if (/^I/.test(lines[0]) && joined2.length >= 60) return 'TD2';
    }

    return null;
  },

  _parseTD1: function (lines) {
    var l1 = lines[0] || '';
    var l2 = lines[1] || '';
    var l3 = lines[2] || '';

    var docType = l1.charAt(0);
    var country = l1.substring(2, 5);
    var docNumber = l1.substring(5, 14).replace(/</g, '');
    var docNumberCheck = l1.length > 14 ? l1.charAt(14) : '';
    var optional1 = l1.length > 15 ? l1.substring(15, 28).replace(/</g, '') : '';
    var finalCheck = l1.length > 29 ? l1.charAt(29) : '';

    var birthDateRaw = l2.length > 6 ? l2.substring(0, 6) : '';
    var birthDateCheck = l2.length > 7 ? l2.charAt(6) : '';
    var sex = l2.length > 8 ? l2.charAt(7) : '';
    var expiryDateRaw = l2.length > 14 ? l2.substring(8, 14) : '';
    var expiryDateCheck = l2.length > 15 ? l2.charAt(14) : '';
    var nationality = l2.length > 18 ? l2.substring(15, 18) : '';

    var nameParts = l3.split('<<');
    var surname = '', givenNames = '';
    if (nameParts.length >= 1) {
      surname = MRZParser._normalizeName(nameParts[0].replace(/</g, ' '));
      if (nameParts.length >= 2) {
        givenNames = MRZParser._normalizeName(nameParts.slice(1).join(' ').replace(/</g, ' '));
      }
    }

    return {
      format: 'TD1',
      docType: docType === 'I' || docType === '1' ? 'dni' : (docType === 'P' ? 'passport' : 'unknown'),
      country: country,
      documentNumber: docNumber,
      surname: surname,
      givenNames: givenNames,
      birthDate: MRZParser._parseDate(birthDateRaw),
      sex: sex === 'M' ? 'M' : (sex === 'F' ? 'F' : ''),
      expiryDate: MRZParser._parseDate(expiryDateRaw),
      nationality: nationality,
      checkDigits: { documentNumber: docNumberCheck, birthDate: birthDateCheck, expiryDate: expiryDateCheck, final: finalCheck },
      _birthDateRaw: birthDateRaw,
      _expiryDateRaw: expiryDateRaw
    };
  },

  _parseTD3: function (lines) {
    var l1 = lines[0] || '';
    var l2 = lines[1] || '';

    var docType = l1.charAt(0);
    var country = l1.substring(2, 5);
    var namePart = l1.length > 5 ? l1.substring(5).replace(/<+$/, '') : '';
    var nameParts = namePart.split('<<');
    var surname = '', givenNames = '';
    if (nameParts.length >= 1) {
      surname = MRZParser._normalizeName(nameParts[0].replace(/</g, ' '));
      if (nameParts.length >= 2) {
        givenNames = MRZParser._normalizeName(nameParts.slice(1).join(' ').replace(/</g, ' '));
      }
    }

    var passportNumber = l2.substring(0, 9).replace(/</g, '');
    var passportCheck = l2.length > 9 ? l2.charAt(9) : '';
    var nationality = l2.length > 12 ? l2.substring(10, 13) : '';
    var birthDateRaw = l2.length > 18 ? l2.substring(13, 19) : '';
    var birthDateCheck = l2.length > 19 ? l2.charAt(19) : '';
    var sex = l2.length > 20 ? l2.charAt(20) : '';
    var expiryDateRaw = l2.length > 26 ? l2.substring(21, 27) : '';
    var expiryDateCheck = l2.length > 27 ? l2.charAt(27) : '';

    return {
      format: 'TD3',
      docType: 'passport',
      country: country,
      documentNumber: passportNumber,
      surname: surname,
      givenNames: givenNames,
      birthDate: MRZParser._parseDate(birthDateRaw),
      sex: sex === 'M' ? 'M' : (sex === 'F' ? 'F' : ''),
      expiryDate: MRZParser._parseDate(expiryDateRaw),
      nationality: nationality,
      checkDigits: { documentNumber: passportCheck, birthDate: birthDateCheck, expiryDate: expiryDateCheck, final: '' },
      _birthDateRaw: birthDateRaw,
      _expiryDateRaw: expiryDateRaw
    };
  },

  _parseTD2: function (lines) {
    var l1 = lines[0] || '';
    var l2 = lines[1] || '';

    var docType = l1.charAt(0);
    var country = l1.substring(2, 5);
    var namePart = l1.length > 5 ? l1.substring(5).replace(/<+$/, '') : '';
    var nameParts = namePart.split('<<');
    var surname = '', givenNames = '';
    if (nameParts.length >= 1) {
      surname = MRZParser._normalizeName(nameParts[0].replace(/</g, ' '));
      if (nameParts.length >= 2) {
        givenNames = MRZParser._normalizeName(nameParts.slice(1).join(' ').replace(/</g, ' '));
      }
    }

    var docNumber = l2.substring(0, 9).replace(/</g, '');
    var docNumberCheck = l2.length > 9 ? l2.charAt(9) : '';
    var nationality = l2.length > 13 ? l2.substring(10, 13) : '';
    var birthDateRaw = l2.length > 19 ? l2.substring(13, 19) : '';
    var birthDateCheck = l2.length > 20 ? l2.charAt(19) : '';
    var sex = l2.length > 21 ? l2.charAt(20) : '';
    var expiryDateRaw = l2.length > 27 ? l2.substring(21, 27) : '';
    var expiryDateCheck = l2.length > 28 ? l2.charAt(27) : '';

    return {
      format: 'TD2',
      docType: 'dni',
      country: country,
      documentNumber: docNumber,
      surname: surname,
      givenNames: givenNames,
      birthDate: MRZParser._parseDate(birthDateRaw),
      sex: sex === 'M' ? 'M' : (sex === 'F' ? 'F' : ''),
      expiryDate: MRZParser._parseDate(expiryDateRaw),
      nationality: nationality,
      checkDigits: { documentNumber: docNumberCheck, birthDate: birthDateCheck, expiryDate: expiryDateCheck, final: '' },
      _birthDateRaw: birthDateRaw,
      _expiryDateRaw: expiryDateRaw
    };
  },

  _parseDate: function (raw) {
    if (!raw || raw.length < 6 || /^<+$/.test(raw)) return '';
    var yy = parseInt(raw.substring(0, 2), 10);
    var mm = raw.substring(2, 4);
    var dd = raw.substring(4, 6);
    var fullYear = yy >= 50 ? 1900 + yy : 2000 + yy;
    var d = parseInt(dd, 10), m = parseInt(mm, 10);
    if (m < 1 || m > 12 || d < 1 || d > 31) return '';
    return fullYear + '-' + mm + '-' + dd;
  },

  _normalizeName: function (name) {
    return name.replace(/[^A-Za-zÀ-ÿÑñ\s'-]/g, ' ').replace(/\s+/g, ' ').trim().substring(0, 50);
  },

  _calculateCheckDigit: function (str) {
    var weights = [7, 3, 1];
    var sum = 0;
    for (var i = 0; i < str.length; i++) {
      var c = str[i];
      var val;
      if (c >= '0' && c <= '9') val = c.charCodeAt(0) - 48;
      else if (c >= 'A' && c <= 'Z') val = c.charCodeAt(0) - 55;
      else val = 0;
      sum += val * weights[i % 3];
    }
    return (sum % 10).toString();
  },

  _validateCheckDigits: function (record) {
    var calc = MRZParser._calculateCheckDigit;

    if (record.format === 'TD1') {
      if (record.documentNumber) {
        var docStr = (record.documentNumber + '<' + '      ').substring(0, 9);
        var actual = record.checkDigits.documentNumber;
        record.documentNumberValid = actual ? calc(docStr) === actual : null;
      }
      if (record._birthDateRaw) {
        record.birthDateValid = calc(record._birthDateRaw) === record.checkDigits.birthDate;
      }
      if (record._expiryDateRaw) {
        record.expiryDateValid = calc(record._expiryDateRaw) === record.checkDigits.expiryDate;
      }
    } else if (record.format === 'TD3') {
      if (record.documentNumber) {
        var docStr = record.documentNumber;
        while (docStr.length < 9) docStr += '<';
        var actual = record.checkDigits.documentNumber;
        record.documentNumberValid = actual ? calc(docStr) === actual : null;
      }
      if (record._birthDateRaw) {
        record.birthDateValid = calc(record._birthDateRaw) === record.checkDigits.birthDate;
      }
      if (record._expiryDateRaw) {
        record.expiryDateValid = calc(record._expiryDateRaw) === record.checkDigits.expiryDate;
      }
    } else if (record.format === 'TD2') {
      if (record.documentNumber) {
        var docStr2 = record.documentNumber;
        while (docStr2.length < 9) docStr2 += '<';
        var actual2 = record.checkDigits.documentNumber;
        record.documentNumberValid = actual2 ? calc(docStr2) === actual2 : null;
      }
      if (record._birthDateRaw) {
        record.birthDateValid = calc(record._birthDateRaw) === record.checkDigits.birthDate;
      }
      if (record._expiryDateRaw) {
        record.expiryDateValid = calc(record._expiryDateRaw) === record.checkDigits.expiryDate;
      }
    }

    delete record._birthDateRaw;
    delete record._expiryDateRaw;
    return record;
  },

  _calculateConfidence: function (record) {
    var score = 0;
    var factors = 0;

    if (record.documentNumberValid !== null) {
      score += record.documentNumberValid ? 0.35 : 0;
      factors += 0.35;
    }
    if (record.birthDateValid !== null) {
      score += record.birthDateValid ? 0.25 : 0;
      factors += 0.25;
    }
    if (record.expiryDateValid !== null) {
      score += record.expiryDateValid ? 0.25 : 0;
      factors += 0.25;
    }

    var checkScore = factors > 0 ? score / factors : 0;

    var completeness = 0;
    if (record.documentNumber && record.documentNumber.length >= 5) completeness += 0.3;
    if (record.surname) completeness += 0.2;
    if (record.givenNames) completeness += 0.2;
    if (record.birthDate) completeness += 0.2;
    if (record.nationality && record.nationality.length === 3) completeness += 0.1;

    return checkScore * 0.6 + Math.min(completeness, 1) * 0.4;
  }
};

window.MRZParser = MRZParser;
})();