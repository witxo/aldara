class IdParseResult {
  final String? documentType;
  final String? documentNumber;
  final String? firstName;
  final String? lastName;
  final String? nationality;
  final String? birthDate;
  final String? gender;

  IdParseResult({
    this.documentType,
    this.documentNumber,
    this.firstName,
    this.lastName,
    this.nationality,
    this.birthDate,
    this.gender,
  });

  bool get hasAny =>
      documentType != null || documentNumber != null ||
      firstName != null || lastName != null;
}

class SpanishIdParser {
  static final _dniPattern = RegExp(r'\b(\d{8}[A-Z])\b');
  static final _niePattern = RegExp(r'\b([XYZ]\d{7}[A-Z])\b');
  static final _datePattern = RegExp(r'(\d{2})[/-](\d{2})[/-](\d{4})');

  static final _keywords = {
    'nombre': ['NOMBRE', 'NOMBRE', 'NOM'],
    'apellidos': ['APELLIDOS', 'APELIIDOS', 'APELLIDO', 'APELIDOS'],
    'nacionalidad': ['NACIONALIDAD', 'NACIONALID', 'NACION', 'NAC'],
    'nacimiento': ['NACIMIENTO', 'NACI', 'F.NACIMIENTO', 'FECHA NAC'],
    'fecha': ['FECHA', 'FECH', 'F'],
    'sexo': ['SEXO', 'SEX'],
    'dni': ['DNI', 'D.N.I', 'D.N.I.'],
    'nie': ['NIE', 'N.I.E'],
    'numero': ['NUMERO', 'NUM', 'NO'],
  };

  static final _nationalityMap = {
    'ESP': 'ES', 'ESPAÑA': 'ES', 'ESPANA': 'ES', 'SPAIN': 'ES',
    'FRANCIA': 'FR', 'FRANCE': 'FR',
    'ALEMANIA': 'DE', 'GERMANY': 'DE',
    'ITALIA': 'IT', 'ITALY': 'IT',
    'REINO UNIDO': 'GB', 'UK': 'GB',
    'PORTUGAL': 'PT',
    'USA': 'US', 'ESTADOS UNIDOS': 'US',
  };

  static IdParseResult parse(String rawText) {
    final lines = rawText
        .split('\n')
        .map((l) => l.trim())
        .where((l) => l.isNotEmpty)
        .toList();

    if (lines.isEmpty) return IdParseResult();

    String? docNumber, docType, firstName, lastName, nationality, birthDate, gender;

    docNumber = _findDocNumber(rawText, lines);
    docType = _findDocType(rawText, docNumber);

    final nameInfo = _findNames(lines);
    firstName = nameInfo['firstName'];
    lastName = nameInfo['lastName'];

    nationality = _findNationality(rawText, lines);
    birthDate = _findBirthDate(rawText, lines);
    gender = _findGender(rawText, lines);

    return IdParseResult(
      documentType: docType,
      documentNumber: docNumber,
      firstName: firstName,
      lastName: lastName,
      nationality: nationality,
      birthDate: birthDate,
      gender: gender,
    );
  }

  static String? _findDocNumber(String text, List<String> lines) {
    for (final line in lines) {
      final nie = _niePattern.firstMatch(line);
      if (nie != null) return nie.group(1);
    }
    for (final line in lines) {
      final dni = _dniPattern.firstMatch(line);
      if (dni != null) return dni.group(1);
    }
    final textNie = _niePattern.firstMatch(text);
    if (textNie != null) return textNie.group(1);
    final textDni = _dniPattern.firstMatch(text);
    if (textDni != null) return textDni.group(1);
    return null;
  }

  static String? _findDocType(String text, String? docNumber) {
    if (docNumber == null) return null;
    if (docNumber.startsWith('X') || docNumber.startsWith('Y') || docNumber.startsWith('Z')) {
      if (_niePattern.hasMatch(docNumber)) return 'nie';
    }
    if (_dniPattern.hasMatch(docNumber)) return 'dni';
    final upper = text.toUpperCase();
    if (upper.contains('PASAPORTE') || upper.contains('PASSPORT')) return 'passport';
    return 'passport';
  }

  static Map<String, String?> _findNames(List<String> lines) {
    String? firstName, lastName;
    int apellidosIdx = -1;
    int nombreIdx = -1;

    for (int i = 0; i < lines.length; i++) {
      final upper = lines[i].toUpperCase().replaceAll(RegExp(r'[^A-Z\s]'), '');
      if (upper.startsWith('APELLID') && apellidosIdx == -1) apellidosIdx = i;
      if ((upper.startsWith('NOMBRE') || upper == 'NOM') && nombreIdx == -1) nombreIdx = i;
    }

    if (apellidosIdx >= 0 && apellidosIdx + 1 < lines.length) {
      lastName = _cleanName(lines[apellidosIdx + 1]);
      if (lastName != null && lastName.length > 50) {
        lastName = lastName.substring(0, 50);
      }
    }

    if (nombreIdx >= 0 && nombreIdx + 1 < lines.length) {
      firstName = _cleanName(lines[nombreIdx + 1]);
      if (firstName != null && firstName.length > 50) {
        firstName = firstName.substring(0, 50);
      }
    }

    if (lastName == null && nombreIdx >= 0 && apellidosIdx >= 0 && apellidosIdx + 1 < nombreIdx) {
      lastName = _cleanName(lines[apellidosIdx + 1]);
    }

    return {'firstName': firstName, 'lastName': lastName};
  }

  static String? _cleanName(String raw) {
    final cleaned = raw.replaceAll(RegExp(r"[^A-Za-zÀ-ÿÑñ\s'-]"), '').trim();
    if (cleaned.isEmpty || cleaned.length < 2) return null;
    return cleaned.split(RegExp(r'\s+')).where((w) => w.length > 1).join(' ');
  }

  static String? _findNationality(String text, List<String> lines) {
    final upperText = text.toUpperCase().replaceAll('Ó', 'O').replaceAll('Í', 'I');
    for (final entry in _nationalityMap.entries) {
      if (upperText.contains(entry.key)) return entry.value;
    }
    final natMatch = RegExp(r'NACIONALIDAD[:\s]*([A-Z]{2,4})', caseSensitive: false).firstMatch(text);
    if (natMatch != null) {
      final code = natMatch.group(1)!.toUpperCase();
      if (code.length == 2) return code;
      if (_nationalityMap.containsKey(code)) return _nationalityMap[code];
    }
    for (int i = 0; i < lines.length; i++) {
      final upper = lines[i].toUpperCase().replaceAll(RegExp(r'[^A-Z\s]'), '');
      if (upper.startsWith('NACION')) {
        if (i + 1 < lines.length) {
          final match = _nationalityMap.entries.firstWhere(
            (e) => lines[i + 1].toUpperCase().contains(e.key),
            orElse: () => MapEntry('', ''),
          );
          if (match.value.isNotEmpty) return match.value;
          final codeMatch = RegExp(r'\b([A-Z]{2})\b').firstMatch(lines[i + 1].toUpperCase());
          if (codeMatch != null && codeMatch.group(1)!.length == 2) return codeMatch.group(1);
        }
      }
    }
    return null;
  }

  static String? _findBirthDate(String text, List<String> lines) {
    final dateMatches = _datePattern.allMatches(text).toList();
    if (dateMatches.isNotEmpty) {
      for (final m in dateMatches) {
        final day = int.tryParse(m.group(1)!);
        final month = int.tryParse(m.group(2)!);
        final year = int.tryParse(m.group(3)!);
        if (day != null && month != null && year != null &&
            day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= 2010) {
          return '${year.toString().padLeft(4, '0')}-${month.toString().padLeft(2, '0')}-${day.toString().padLeft(2, '0')}';
        }
      }
    }
    for (int i = 0; i < lines.length; i++) {
      final upper = lines[i].toUpperCase().replaceAll(RegExp(r'[^A-Z\s]'), '');
      if (upper.contains('NACIMIENTO') || upper.startsWith('FECHA')) {
        final nearby = (i > 0 ? lines[i - 1] + ' ' : '') + lines[i] + ' ' + (i + 1 < lines.length ? lines[i + 1] : '');
        final m = _datePattern.firstMatch(nearby);
        if (m != null) {
          final day = int.tryParse(m.group(1)!);
          final month = int.tryParse(m.group(2)!);
          final year = int.tryParse(m.group(3)!);
          if (day != null && month != null && year != null &&
              day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= 2010) {
            return '${year.toString().padLeft(4, '0')}-${month.toString().padLeft(2, '0')}-${day.toString().padLeft(2, '0')}';
          }
        }
      }
    }
    return null;
  }

  static String? _findGender(String text, List<String> lines) {
    final upperText = text.toUpperCase();
    if (upperText.contains('VARON') || upperText.contains('VARÓ') || upperText.contains(' H ')) return 'male';
    if (upperText.contains('MUJER') || upperText.contains('MUJE') || upperText.contains(' M ')) return 'female';
    if (upperText.contains('SEXO')) {
      int idx = upperText.indexOf('SEXO');
      final after = upperText.substring(idx + 4).trim();
      if (after.startsWith('H') || after.startsWith('V')) return 'male';
      if (after.startsWith('M')) return 'female';
    }
    for (int i = 0; i < lines.length; i++) {
      final upper = lines[i].toUpperCase().replaceAll(RegExp(r'[^A-Z\s]'), '');
      if (upper.startsWith('SEXO') && i + 1 < lines.length) {
        final val = lines[i + 1].trim();
        if (val.startsWith('H') || val.startsWith('V')) return 'male';
        if (val.startsWith('M')) return 'female';
      }
    }
    return null;
  }
}