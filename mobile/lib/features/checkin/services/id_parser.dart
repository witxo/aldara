class ParsedId {
  final String documentType;
  final String documentNumber;
  final String? firstName;
  final String? lastName;
  final String? birthDate;
  final String? nationality;
  final String? gender;
  final String? expiryDate;

  ParsedId({
    required this.documentType,
    required this.documentNumber,
    this.firstName,
    this.lastName,
    this.birthDate,
    this.nationality,
    this.gender,
    this.expiryDate,
  });
}

class IdParser {
  static final RegExp _dniRegex = RegExp(r'(\d{8})([A-Z])');
  static final RegExp _nieRegex = RegExp(r'([XYZ])(\d{7})([A-Z])');
  static final RegExp _passportRegex = RegExp(r'([A-Z]{3})(\d{6})');

  /// Main entry point: detect document type and parse all fields from raw OCR text
  static ParsedId? parse(String rawText) {
    final cleaned = rawText
        .replaceAll(RegExp(r'\s+'), ' ')
        .replaceAll(RegExp(r'[<]'), ' ')
        .trim();

    final mrzResult = _parseMrz(cleaned);
    if (mrzResult != null) return mrzResult;

    final documentResult = _parseDocumentNumber(cleaned);
    if (documentResult != null) return documentResult;

    return null;
  }

  /// Parse MRZ (Machine Readable Zone) from passports and newer DNI
  static ParsedId? _parseMrz(String text) {
    final lines = text
        .split(RegExp(r'\n'))
        .map((l) => l.trim())
        .where((l) => l.isNotEmpty)
        .toList();

    for (int i = 0; i < lines.length; i++) {
      final line = lines[i];
      if (line.startsWith('IDESP')) {
        final mrzLines = [line];
        if (i + 1 < lines.length) mrzLines.add(lines[i + 1]);
        if (i + 2 < lines.length) mrzLines.add(lines[i + 2]);
        return _parseSpanishIdMrz(mrzLines.join('\n'));
      }
      if (line.startsWith('P<ESP') || line.startsWith('P<')) {
        final secondLine = i + 1 < lines.length ? lines[i + 1] : '';
        return _parsePassportMrz(line, secondLine);
      }
    }

    return null;
  }

  /// Parse Spanish DNI/e-ID from MRZ line: IDESP<...
  static ParsedId? _parseSpanishIdMrz(String mrz) {
    // IDESP<BOND<<JAMES<<<<<<<<<<<<<<<<<<<<<<<
    // 12345678Z<ESP9307128M2801285<<<<<<<<<<<<
    // Format: IDESP<last_name<<first_name<<<...
    //          doc_number<checksum+DOB+gender+expiry...

    final parts = mrz.split('<').where((p) => p.isNotEmpty).toList();
    if (parts.length < 3) return null;

    String lastName = '';
    String firstName = '';
    String docNumber = '';
    String birthDate = '';
    String expiryDate = '';
    String nationality = '';
    String gender = '';

    final lines = mrz.split('\n').where((l) => l.trim().isNotEmpty).toList();

    if (lines.length >= 2) {
      final firstLine = lines[0].trim();
      final secondLine = lines[1].trim();

      final nameParts =
          firstLine.replaceFirst('IDESP', '').split('<<');
      lastName = nameParts.isNotEmpty ? nameParts[0].replaceAll('<', ' ') : '';
      firstName = nameParts.length > 1
          ? nameParts[1].replaceAll('<', ' ').trim()
          : '';

      final docMatch = RegExp(r'(\d{8})([A-Z])').firstMatch(secondLine);
      if (docMatch != null) {
        docNumber = '${docMatch.group(1)}${docMatch.group(2)}';
      }

      final dobMatch = RegExp(r'(\d{6})\d[MFC]').firstMatch(secondLine);
      if (dobMatch != null) {
        birthDate = _formatMrzDate(dobMatch.group(1)!);
      }

      final genderMatch = RegExp(r'[MFC]').firstMatch(secondLine);
      if (genderMatch != null) {
        gender = genderMatch.group(0)!;
      }

      final expiryMatch =
          RegExp(r'(?:M|F|C)(\d{6})\d').firstMatch(secondLine);
      if (expiryMatch != null) {
        expiryDate = _formatMrzDate(expiryMatch.group(1)!);
      }

      final natMatch = RegExp(r'ESP').firstMatch(secondLine);
      if (natMatch != null) {
        nationality = 'ES';
      }
    }

    return ParsedId(
      documentType: 'dni',
      documentNumber: docNumber,
      firstName: firstName.isNotEmpty ? firstName : null,
      lastName: lastName.isNotEmpty ? lastName : null,
      birthDate: birthDate.isNotEmpty ? birthDate : null,
      nationality: nationality.isNotEmpty ? nationality : null,
      gender: gender.isNotEmpty ? gender : null,
      expiryDate: expiryDate.isNotEmpty ? expiryDate : null,
    );
  }

  /// Parse passport MRZ lines (TD3 format)
  static ParsedId? _parsePassportMrz(String firstLine, String secondLine) {
    // P<ESP<<SURNAME<<NAME<<<<<<<<<<<<<<<<
    // PA123456<7ESP8001010M2001017<<<<<<<<<<
    String lastName = '';
    String firstName = '';
    String docNumber = '';
    String nationality = '';
    String birthDate = '';
    String gender = '';
    String expiryDate = '';

    final namePart = firstLine.replaceFirst(RegExp(r'P<[A-Z]{3}<'), '');
    final names = namePart.split('<<');
    lastName = names.isNotEmpty ? names[0].replaceAll('<', ' ') : '';
    firstName = names.length > 1
        ? names[1].replaceAll('<', ' ').trim()
        : '';

    if (secondLine.isNotEmpty) {
      final docMatch =
          RegExp(r'([A-Z]{1,2})(\d{6,7})').firstMatch(secondLine);
      if (docMatch != null) {
        docNumber = '${docMatch.group(1)}${docMatch.group(2)}';
      }

      final natMatch = RegExp(r'[A-Z]{3}').firstMatch(secondLine);
      if (natMatch != null) {
        nationality = natMatch.group(0)!;
      }

      final dobMatch = RegExp(r'(\d{6})\d[MFC]').firstMatch(secondLine);
      if (dobMatch != null) {
        birthDate = _formatMrzDate(dobMatch.group(1)!);
      }

      final genderMatch = RegExp(r'[MFC]').firstMatch(secondLine);
      if (genderMatch != null) {
        gender = genderMatch.group(0)!;
      }

      final expiryMatch =
          RegExp(r'(?:M|F|C)(\d{6})\d').firstMatch(secondLine);
      if (expiryMatch != null) {
        expiryDate = _formatMrzDate(expiryMatch.group(1)!);
      }
    }

    return ParsedId(
      documentType: 'passport',
      documentNumber: docNumber,
      firstName: firstName.isNotEmpty ? firstName : null,
      lastName: lastName.isNotEmpty ? lastName : null,
      nationality: nationality.isNotEmpty ? nationality : null,
      birthDate: birthDate.isNotEmpty ? birthDate : null,
      gender: gender.isNotEmpty ? gender : null,
      expiryDate: expiryDate.isNotEmpty ? expiryDate : null,
    );
  }

  /// Fallback: find any recognizable document number in text
  static ParsedId? _parseDocumentNumber(String text) {
    final dniMatch = _dniRegex.firstMatch(text);
    if (dniMatch != null) {
      final num = '${dniMatch.group(1)}${dniMatch.group(2)}';
      return ParsedId(
        documentType: 'dni',
        documentNumber: num,
        firstName: _extractNameNearby(text, dniMatch.start),
      );
    }

    final nieMatch = _nieRegex.firstMatch(text);
    if (nieMatch != null) {
      final num = '${nieMatch.group(1)}${nieMatch.group(2)}${nieMatch.group(3)}';
      return ParsedId(
        documentType: 'nie',
        documentNumber: num,
        firstName: _extractNameNearby(text, nieMatch.start),
      );
    }

    final passportMatch = _passportRegex.firstMatch(text);
    if (passportMatch != null) {
      final num = '${passportMatch.group(1)}${passportMatch.group(2)}';
      return ParsedId(
        documentType: 'passport',
        documentNumber: num,
        firstName: _extractNameNearby(text, passportMatch.start),
      );
    }

    return null;
  }

  /// Try to find a name near the document number position
  static String? _extractNameNearby(String text, int position) {
    final before = text
        .substring(0, position.clamp(0, text.length))
        .trim();
    final words = before.split(' ').where((w) => w.length > 2).toList();
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
