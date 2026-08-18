class ParsedId {
  final String documentType;
  final String documentNumber;
  final String? firstName;
  final String? lastName;
  final String? lastName2;
  final String? birthDate;
  final String? nationality;
  final String? gender;
  final String? expiryDate;
  final double confidence;

  ParsedId({
    required this.documentType,
    required this.documentNumber,
    this.firstName,
    this.lastName,
    this.lastName2,
    this.birthDate,
    this.nationality,
    this.gender,
    this.expiryDate,
    this.confidence = 0.0,
  });
}

class IdParser {
  static final RegExp _dniRegex = RegExp(r'(\d{8})([A-Z])');
  static final RegExp _nieRegex = RegExp(r'([XYZ])(\d{7})([A-Z])');
  static final RegExp _passportRegex = RegExp(r'([A-Z]{3})(\d{6})');

  static final Map<String, String> _nationalityMap = {
    'ESP': 'ES',
    'SPAIN': 'ES',
  };

  static final Map<String, String> _genderMap = {
    'M': 'M',
    'F': 'F',
  };

  /// Main entry point: detect document type and parse all fields from raw OCR text
  static ParsedId? parse(String rawText) {
    final cleaned = _cleanOcrText(rawText);
    final lines = cleaned
        .split('\n')
        .map((l) => l.trim())
        .where((l) => l.isNotEmpty)
        .toList();

    if (lines.isEmpty) return null;

    final mrzResult = _parseMrz(lines, cleaned);
    if (mrzResult != null) return mrzResult;

    final documentResult = _parseDocumentNumber(cleaned);
    if (documentResult != null) return documentResult;

    return null;
  }

  /// Aggressively clean OCR noise: D/K/L → <, SS → <<, etc.
  static String _cleanOcrText(String text) {
    var t = text.toUpperCase();

    // Normalize line breaks
    t = t.replaceAll(RegExp(r'\|'), '\n');
    t = t.replaceAll(RegExp(r'[ \t]+'), '\n');

    // Remove non-MRZ characters from each line
    final lines = t.split('\n');
    final cleaned = lines.map((line) {
      // Keep only A-Z 0-9 <
      line = line.replaceAll(RegExp(r'[^A-Z0-9<]'), '');
      // SS -> << (filler misread)
      line = line.replaceAll('SS', '<<');
      // KL/LK/KK/LL runs -> <<
      line = line.replaceAll(RegExp(r'[KL]{2,}'), '<<');
      // Single K/L between < chars -> <
      line = line.replaceAll(RegExp(r'<[KL]'), '<<');
      line = line.replaceAll(RegExp(r'[KL]<'), '<<');
      // D between < chars -> <
      line = line.replaceAll(RegExp(r'<D'), '<<');
      // Fix IDESP -> I<ESP (D at position 1 misread)
      if (line.startsWith('ID')) {
        line = 'I<' + line.substring(2);
      }
      return line;
    }).toList();

    // Filter short noise lines
    final filtered = cleaned.where((l) => l.length >= 20).toList();

    // Trim or pad each line to exact MRZ length for TD1 (30) or TD3 (44)
    final result = filtered.map((line) {
      if (line.length > 30) line = line.substring(0, 30);
      if (filtered.length == 3 && line.length < 30) {
        line = line.padRight(30, '<');
      }
      return line;
    }).toList();

    return result.join('\n');
  }

  /// Find MRZ lines and parse them
  static ParsedId? _parseMrz(List<String> lines, String fullText) {
    ParsedId? result;

    // TD1: 3 lines of 30 chars, starts with I/A/C
    if (lines.length >= 3) {
      final first = lines[0];
      if (first.isNotEmpty &&
          (first.startsWith('I<') || first.startsWith('ID'))) {
        final mrzLines = lines.sublist(0, 3);
        result = _parseTd1(mrzLines);
        if (result != null) return result;
      }
    }

    // TD3: 2 lines of 44 chars, starts with P
    if (lines.length >= 2) {
      final first = lines[0];
      if (first.startsWith('P<')) {
        result = _parseTd3(lines[0], lines[1]);
        if (result != null) return result;
      }
    }

    // Fallback: scan through all lines to find MRZ patterns
    for (int i = 0; i < lines.length; i++) {
      final line = lines[i];
      final cleaned = line.replaceAll(RegExp(r'[^A-Z0-9<]'), '');

      // Check for TD1 pattern
      if (i + 2 < lines.length && (cleaned.startsWith('I<') || cleaned.startsWith('ID'))) {
        final mrzLines = [cleaned];
        for (int j = 1; j <= 2; j++) {
          if (i + j < lines.length) {
            mrzLines.add(lines[i + j].replaceAll(RegExp(r'[^A-Z0-9<]'), ''));
          }
        }
        if (mrzLines.length >= 3) {
          result = _parseTd1(mrzLines);
          if (result != null) return result;
        }
      }

      // Check for TD3 pattern
      if (i + 1 < lines.length && cleaned.startsWith('P<')) {
        final second = lines[i + 1].replaceAll(RegExp(r'[^A-Z0-9<]'), '');
        result = _parseTd3(cleaned, second);
        if (result != null) return result;
      }
    }

    return null;
  }

  /// Parse TD1 format (3 lines x 30 chars) — Spanish DNI/e-ID
  static ParsedId? _parseTd1(List<String> lines) {
    if (lines.length < 3) return null;

    final line1 = lines[0];
    final line2 = lines[1];
    final line3 = lines[2];

    // Extract fields from TD1 structure
    // Line 1: I<ESP<DOC_NUMBER<OPTIONAL<<<<<<
    // Line 2: DOB(6) + CHK + SEX + EXPIRY(6) + CHK + NATIONALITY(3) + OPTIONAL(11)
    // Line 3: SURNAME<<FIRST_NAME<<...

    String docNumber = '';
    String birthDate = '';
    String gender = '';
    String expiryDate = '';
    String nationality = '';
    String lastName = '';
    String lastName2 = '';
    String firstName = '';

    // Document number from line1 (positions 5-13, 0-indexed)
    if (line1.length > 14) {
      docNumber = line1.substring(5, 14).replaceAll('<', '').trim();
      // Add trailing check digit letter if present
      if (docNumber.isNotEmpty && line1.length > 14) {
        final check = line1[14];
        if (check != '<' && !RegExp(r'^\d$').hasMatch(check)) {
          docNumber += check;
        }
      }
    }

    // Birth date from line2 (positions 0-5, 0-indexed)
    if (line2.length > 7) {
      final dobRaw = line2.substring(0, 6);
      if (RegExp(r'^\d{6}$').hasMatch(dobRaw)) {
        birthDate = _formatMrzDate(dobRaw);
      }
    }

    // Gender from line2 (position 7)
    if (line2.length > 7) {
      final g = line2[7];
      if (g == 'M' || g == 'F') {
        gender = g;
      }
    }

    // Expiry date from line2 (positions 8-13)
    if (line2.length > 14) {
      final expRaw = line2.substring(8, 14);
      if (RegExp(r'^\d{6}$').hasMatch(expRaw)) {
        expiryDate = _formatMrzDate(expRaw);
      }
    }

    // Nationality from line2 (positions 15-17)
    if (line2.length > 17) {
      final natRaw = line2.substring(15, 18).replaceAll('<', '').trim();
      if (natRaw.isNotEmpty) {
        nationality = _nationalityMap[natRaw] ?? natRaw;
      }
    }

    // Name from line3: SURNAME1<SURNAME2<<FIRST_NAME<<...
    if (line3.isNotEmpty) {
      final parts = line3.split('<<');
      if (parts.isNotEmpty) {
        final surnameBlock = parts[0];
        final surnameParts = surnameBlock.split('<').where((s) => s.isNotEmpty).toList();
        lastName = surnameParts.isNotEmpty ? surnameParts[0].trim() : '';
        lastName2 = surnameParts.length > 1 ? surnameParts.sublist(1).join(' ').trim() : '';
      }
      if (parts.length > 1) {
        firstName = parts.sublist(1).join(' ').replaceAll('<', ' ').trim();
      }
    }

    if (docNumber.isEmpty) return null;

    final confidence = _calculateConfidence(
      documentNumber: docNumber,
      firstName: firstName,
      lastName: lastName,
      birthDate: birthDate,
      expiryDate: expiryDate,
    );

    return ParsedId(
      documentType: 'dni',
      documentNumber: docNumber,
      firstName: firstName.isNotEmpty ? firstName : null,
      lastName: lastName.isNotEmpty ? lastName : null,
      lastName2: lastName2.isNotEmpty ? lastName2 : null,
      birthDate: birthDate.isNotEmpty ? birthDate : null,
      nationality: nationality.isNotEmpty ? nationality : null,
      gender: gender.isNotEmpty ? gender : null,
      expiryDate: expiryDate.isNotEmpty ? expiryDate : null,
      confidence: confidence,
    );
  }

  /// Parse TD3 format (2 lines x 44 chars) — Passport
  static ParsedId? _parseTd3(String firstLine, String secondLine) {
    if (firstLine.length < 10 || secondLine.length < 10) return null;

    String lastName = '';
    String lastName2 = '';
    String firstName = '';
    String docNumber = '';
    String nationality = '';
    String birthDate = '';
    String gender = '';
    String expiryDate = '';

    // Name from firstLine: P<ESP<SURNAME1<SURNAME2<<FIRST_NAME<<...
    final afterPrefix = firstLine.replaceFirst(RegExp(r'P<[A-Z]{3}<'), '');
    final names = afterPrefix.split('<<');
    if (names.isNotEmpty) {
      final surnameBlock = names[0];
      final surnameParts = surnameBlock.split('<').where((s) => s.isNotEmpty).toList();
      lastName = surnameParts.isNotEmpty ? surnameParts[0].trim() : '';
      lastName2 = surnameParts.length > 1 ? surnameParts.sublist(1).join(' ').trim() : '';
    }
    if (names.length > 1) {
      firstName = names.sublist(1).join(' ').replaceAll('<', ' ').trim();
    }

    // Document number from secondLine (positions 0-8)
    if (secondLine.length > 9) {
      docNumber = secondLine.substring(0, 9).replaceAll('<', '').trim();
    }

    // Nationality from secondLine (positions 10-12)
    if (secondLine.length > 12) {
      final natRaw = secondLine.substring(10, 13).replaceAll('<', '').trim();
      if (natRaw.isNotEmpty) {
        nationality = _nationalityMap[natRaw] ?? natRaw;
      }
    }

    // Birth date from secondLine (positions 13-18)
    if (secondLine.length > 19) {
      final dobRaw = secondLine.substring(13, 19);
      if (RegExp(r'^\d{6}$').hasMatch(dobRaw)) {
        birthDate = _formatMrzDate(dobRaw);
      }
    }

    // Gender from secondLine (position 20)
    if (secondLine.length > 20) {
      final g = secondLine[20];
      if (g == 'M' || g == 'F') {
        gender = g;
      }
    }

    // Expiry date from secondLine (positions 21-26)
    if (secondLine.length > 27) {
      final expRaw = secondLine.substring(21, 27);
      if (RegExp(r'^\d{6}$').hasMatch(expRaw)) {
        expiryDate = _formatMrzDate(expRaw);
      }
    }

    if (docNumber.isEmpty) return null;

    final confidence = _calculateConfidence(
      documentNumber: docNumber,
      firstName: firstName,
      lastName: lastName,
      birthDate: birthDate,
      expiryDate: expiryDate,
    );

    return ParsedId(
      documentType: 'passport',
      documentNumber: docNumber,
      firstName: firstName.isNotEmpty ? firstName : null,
      lastName: lastName.isNotEmpty ? lastName : null,
      lastName2: lastName2.isNotEmpty ? lastName2 : null,
      birthDate: birthDate.isNotEmpty ? birthDate : null,
      nationality: nationality.isNotEmpty ? nationality : null,
      gender: gender.isNotEmpty ? gender : null,
      expiryDate: expiryDate.isNotEmpty ? expiryDate : null,
      confidence: confidence,
    );
  }

  /// Confidence score based on how many fields were successfully extracted
  static double _calculateConfidence({
    required String documentNumber,
    String? firstName,
    String? lastName,
    String? birthDate,
    String? expiryDate,
  }) {
    double score = 0.4; // Base: document number found
    if (documentNumber.length >= 8) score += 0.15;
    if (firstName != null && firstName.isNotEmpty) score += 0.15;
    if (lastName != null && lastName.isNotEmpty) score += 0.1;
    if (birthDate != null && birthDate.isNotEmpty) score += 0.1;
    if (expiryDate != null && expiryDate.isNotEmpty) score += 0.1;
    return score.clamp(0.0, 1.0);
  }

  /// Fallback: find any recognizable document number in text
  static ParsedId? _parseDocumentNumber(String text) {
    // Try NIE first (more specific pattern)
    final nieMatch = _nieRegex.firstMatch(text);
    if (nieMatch != null) {
      final num = '${nieMatch.group(1)}${nieMatch.group(2)}${nieMatch.group(3)}';
      return ParsedId(
        documentType: 'nie',
        documentNumber: num,
        firstName: _extractNameNearby(text, nieMatch.start),
        confidence: 0.5,
      );
    }

    final dniMatch = _dniRegex.firstMatch(text);
    if (dniMatch != null) {
      final num = '${dniMatch.group(1)}${dniMatch.group(2)}';
      return ParsedId(
        documentType: 'dni',
        documentNumber: num,
        firstName: _extractNameNearby(text, dniMatch.start),
        confidence: 0.5,
      );
    }

    final passportMatch = _passportRegex.firstMatch(text);
    if (passportMatch != null) {
      final num = '${passportMatch.group(1)}${passportMatch.group(2)}';
      return ParsedId(
        documentType: 'passport',
        documentNumber: num,
        firstName: _extractNameNearby(text, passportMatch.start),
        confidence: 0.4,
      );
    }

    return null;
  }

  /// Try to find a name near the document number position
  static String? _extractNameNearby(String text, int position) {
    final before = text
        .substring(0, position.clamp(0, text.length))
        .trim();
    final words = before.split(RegExp(r'\s+')).where((w) => w.length > 2).toList();
    if (words.length >= 2) {
      return words.sublist(words.length - 2).join(' ');
    }
    return null;
  }

  /// Convert MRZ date (YYMMDD) to DD/MM/YYYY
  static String _formatMrzDate(String mrzDate) {
    if (mrzDate.length != 6) return mrzDate;
    final prefix = int.parse(mrzDate.substring(0, 2)) > 50 ? '19' : '20';
    final day = mrzDate.substring(4, 6);
    final month = mrzDate.substring(2, 4);
    final year = '$prefix${mrzDate.substring(0, 2)}';
    return '$day/$month/$year';
  }
}
