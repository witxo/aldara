import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../auth/providers/auth_provider.dart';
import '../widgets/document_scanner.dart';
import '../widgets/spanish_id_parser.dart';

class GuestFormScreen extends StatefulWidget {
  final int? reservationId;
  final dynamic guest;
  const GuestFormScreen({super.key, this.reservationId, this.guest});

  @override
  State<GuestFormScreen> createState() => _GuestFormScreenState();
}

class _GuestFormScreenState extends State<GuestFormScreen> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _firstName, _lastName, _lastName2, _docNumber, _nationality, _birthDate, _email, _phone, _parentesco, _address, _city, _postalCode;
  String _docType = 'dni';
  String _gender = '';
  bool _isMainGuest = false;
  bool _saving = false;
  bool get _editing => widget.guest != null;

  @override
  void initState() {
    super.initState();
    final g = widget.guest as Map<String, dynamic>?;
    _firstName = TextEditingController(text: g?['first_name'] ?? '');
    _lastName = TextEditingController(text: g?['last_name'] ?? '');
    _lastName2 = TextEditingController(text: g?['last_name2'] ?? '');
    _docNumber = TextEditingController(text: g?['document_number'] ?? '');
    _nationality = TextEditingController(text: g?['nationality'] ?? 'ES');
    _birthDate = TextEditingController(text: g?['birth_date'] ?? '');
    _email = TextEditingController(text: g?['email'] ?? '');
    _phone = TextEditingController(text: g?['phone'] ?? '');
    _parentesco = TextEditingController(text: g?['parentesco'] ?? '');
    _address = TextEditingController(text: g?['address_line1'] ?? '');
    _city = TextEditingController(text: g?['address_city'] ?? '');
    _postalCode = TextEditingController(text: g?['address_postal_code'] ?? '');
    _docType = g?['document_type'] ?? 'dni';
    _gender = g?['gender'] ?? '';
    _isMainGuest = g?['is_main_guest'] ?? false;
  }

  @override
  void dispose() {
    _firstName.dispose(); _lastName.dispose(); _lastName2.dispose(); _docNumber.dispose();
    _nationality.dispose(); _birthDate.dispose(); _email.dispose();
    _phone.dispose(); _parentesco.dispose(); _address.dispose();
    _city.dispose(); _postalCode.dispose();
    super.dispose();
  }

  void _onScanResult(IdParseResult result) {
    setState(() {
      if (result.documentType != null) _docType = result.documentType!;
      if (result.documentNumber != null) _docNumber.text = result.documentNumber!;
      if (result.firstName != null) _firstName.text = result.firstName!;
      if (result.lastName != null) _lastName.text = result.lastName!;
      if (result.lastName2 != null) _lastName2.text = result.lastName2!;
      if (result.nationality != null) _nationality.text = result.nationality!;
      if (result.birthDate != null) _birthDate.text = result.birthDate!;
      if (result.gender != null) _gender = result.gender!;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Datos rellenados desde el documento. Revísalos antes de guardar.'),
        backgroundColor: Colors.green,
      ),
    );
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    try {
      final api = context.read<AuthProvider>().api;
      final data = <String, dynamic>{
        'first_name': _firstName.text,
        'last_name': _lastName.text,
        'last_name2': _lastName2.text.isEmpty ? null : _lastName2.text,
        'document_type': _docType,
        'document_number': _docNumber.text,
        'nationality': _nationality.text.toUpperCase(),
        'birth_date': _birthDate.text.isEmpty ? null : _birthDate.text,
        'gender': _gender.isEmpty ? null : _gender,
        'is_main_guest': _isMainGuest,
        'email': _email.text.isEmpty ? null : _email.text,
        'phone': _phone.text.isEmpty ? null : _phone.text,
        'parentesco': _parentesco.text.isEmpty ? null : _parentesco.text,
        'address_line1': _address.text.isEmpty ? null : _address.text,
        'address_city': _city.text.isEmpty ? null : _city.text,
        'address_postal_code': _postalCode.text.isEmpty ? null : _postalCode.text,
      };

      if (_editing) {
        await api.put('/guests/${widget.guest['id']}', data: data);
      } else {
        data['reservation_id'] = widget.reservationId;
        await api.post('/guests', data: data);
      }
      if (mounted) Navigator.pop(context, true);
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return Scaffold(
      appBar: AppBar(title: Text(_editing ? 'Editar huésped' : 'Nuevo huésped')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              DocumentScannerWidget(onResult: _onScanResult),
              Row(children: [
                Expanded(child: TextFormField(controller: _firstName, decoration: const InputDecoration(labelText: 'Nombre'), validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null)),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _lastName, decoration: const InputDecoration(labelText: 'Apellido 1'), validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null)),
              ]),
              if (_docType == 'dni' || _docType == 'nie')
                Padding(padding: const EdgeInsets.only(top: 12), child: TextFormField(controller: _lastName2, decoration: const InputDecoration(labelText: 'Segundo apellido'))),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: DropdownButtonFormField<String>(
                  value: _docType, decoration: const InputDecoration(labelText: 'Tipo doc.'),
                  items: const [
                    DropdownMenuItem(value: 'dni', child: Text('DNI')),
                    DropdownMenuItem(value: 'nie', child: Text('NIE')),
                    DropdownMenuItem(value: 'passport', child: Text('Pasaporte')),
                    DropdownMenuItem(value: 'other', child: Text('Otro')),
                  ],
                  onChanged: (v) => setState(() => _docType = v ?? 'dni'),
                )),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _docNumber, decoration: const InputDecoration(labelText: 'Nº documento'), validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null)),
              ]),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: TextFormField(controller: _nationality, decoration: const InputDecoration(labelText: 'Nacionalidad', hintText: 'ES'), maxLength: 2)),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _birthDate, decoration: const InputDecoration(labelText: 'Fecha nac.', hintText: 'YYYY-MM-DD'))),
              ]),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _gender.isEmpty ? null : _gender,
                decoration: const InputDecoration(labelText: 'Sexo'),
                items: const [
                  DropdownMenuItem(value: 'male', child: Text('Hombre')),
                  DropdownMenuItem(value: 'female', child: Text('Mujer')),
                  DropdownMenuItem(value: 'other', child: Text('Otro')),
                ],
                onChanged: (v) => setState(() => _gender = v ?? ''),
              ),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: TextFormField(controller: _email, decoration: const InputDecoration(labelText: 'Email'), keyboardType: TextInputType.emailAddress)),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _phone, decoration: const InputDecoration(labelText: 'Teléfono'))),
              ]),
              const SizedBox(height: 12),
              TextFormField(controller: _address, decoration: const InputDecoration(labelText: 'Dirección')),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: TextFormField(controller: _city, decoration: const InputDecoration(labelText: 'Ciudad'))),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _postalCode, decoration: const InputDecoration(labelText: 'CP'))),
              ]),
              const SizedBox(height: 12),
              CheckboxListTile(
                value: _isMainGuest,
                onChanged: (v) => setState(() => _isMainGuest = v ?? false),
                title: const Text('Huésped principal', style: TextStyle(fontSize: 14)),
                contentPadding: EdgeInsets.zero,
                controlAffinity: ListTileControlAffinity.leading,
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(fixedSize: const Size.fromHeight(44)),
                child: _saving ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text(_editing ? 'Guardar cambios' : 'Añadir huésped'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}