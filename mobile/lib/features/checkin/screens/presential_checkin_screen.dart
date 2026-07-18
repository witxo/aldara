import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../auth/providers/auth_provider.dart';
import 'id_scanner_screen.dart';
import '../widgets/signature_pad.dart';

class PresentialCheckinScreen extends StatefulWidget {
  const PresentialCheckinScreen({super.key});

  @override
  State<PresentialCheckinScreen> createState() => _PresentialCheckinScreenState();
}

class _PresentialCheckinScreenState extends State<PresentialCheckinScreen> {
  final _formKey = GlobalKey<FormState>();
  final _searchController = TextEditingController();
  final _nameController = TextEditingController();
  final _surnameController = TextEditingController();
  final _docNumberController = TextEditingController();
  final _nationalityController = TextEditingController(text: 'ES');
  final _birthDateController = TextEditingController();

  String _documentType = 'dni';
  dynamic _selectedReservation;
  bool _searching = false;
  bool _submitting = false;
  String? _error;
  String? _signatureBase64;
  bool _scanned = false;

  Future<void> _searchReservation() async {
    if (_searchController.text.isEmpty) return;
    setState(() { _searching = true; _error = null; });

    try {
      final api = context.read<AuthProvider>().api;
      final response = await api.get('/reservations', queryParameters: {
        'search': _searchController.text,
        'per_page': '5',
      });

      final reservations = response.data['data'] as List;
      if (reservations.isEmpty) {
        setState(() { _error = 'No se encontraron reservas'; _searching = false; });
        return;
      }

      if (reservations.length == 1) {
        setState(() { _selectedReservation = reservations.first; _searching = false; });
      } else {
        _showReservationPicker(reservations);
      }
    } catch (e) {
      setState(() { _error = 'Error al buscar: $e'; _searching = false; });
    }
  }

  void _showReservationPicker(List<dynamic> reservations) {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => ListView(
        padding: const EdgeInsets.all(16),
        children: reservations.map((r) => ListTile(
          title: Text(r['guest_name'] ?? ''),
          subtitle: Text('${r['code']} - ${r['checkin_date']}'),
          onTap: () {
            Navigator.pop(ctx);
            setState(() { _selectedReservation = r; _searching = false; });
          },
        )).toList(),
      ),
    );
  }

  Future<void> _openScanner() async {
    final result = await Navigator.push<IdScannerResult>(
      context,
      MaterialPageRoute(builder: (context) => const IdScannerScreen()),
    );

    if (result != null) {
      setState(() {
        _documentType = result.documentType;
        _docNumberController.text = result.documentNumber;
        if (result.firstName != null) _nameController.text = result.firstName!;
        if (result.lastName != null) _surnameController.text = result.lastName!;
        if (result.birthDate != null) _birthDateController.text = result.birthDate!;
        if (result.nationality != null) _nationalityController.text = result.nationality!;
        _scanned = true;
      });
    }
  }

  Future<void> _submitCheckin() async {
    if (!_formKey.currentState!.validate() || _selectedReservation == null) return;

    setState(() { _submitting = true; _error = null; });

    try {
      final api = context.read<AuthProvider>().api;
      final data = {
        'reservation_id': _selectedReservation!['id'],
        'type': 'presential',
        'guests': [{
          'first_name': _nameController.text.trim(),
          'last_name': _surnameController.text.trim(),
          'document_type': _documentType,
          'document_number': _docNumberController.text.trim(),
          'nationality': _nationalityController.text.trim(),
          'birth_date': _birthDateController.text.trim(),
          'is_main_guest': true,
        }],
      };

      if (_signatureBase64 != null) {
        data['signature'] = _signatureBase64;
      }

      await api.post('/checkins', data: data);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Check-in registrado correctamente'), backgroundColor: Colors.green),
        );
        _resetForm();
      }
    } catch (e) {
      setState(() { _error = 'Error al registrar check-in'; _submitting = false; });
    }
  }

  void _resetForm() {
    setState(() {
      _selectedReservation = null;
      _nameController.clear();
      _surnameController.clear();
      _docNumberController.clear();
      _nationalityController.text = 'ES';
      _birthDateController.clear();
      _searchController.clear();
      _submitting = false;
      _error = null;
      _signatureBase64 = null;
      _scanned = false;
      _documentType = 'dni';
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    _nameController.dispose();
    _surnameController.dispose();
    _docNumberController.dispose();
    _nationalityController.dispose();
    _birthDateController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Check-in presencial')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (_selectedReservation == null) ...[
              Text('Buscar reserva', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 12),
              TextFormField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Nombre, código o email',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: _searchController.text.isNotEmpty
                      ? IconButton(icon: const Icon(Icons.clear), onPressed: () => _searchController.clear())
                      : null,
                ),
                onFieldSubmitted: (_) => _searchReservation(),
              ),
              const SizedBox(height: 12),
              ElevatedButton.icon(
                onPressed: _searching ? null : _searchReservation,
                icon: _searching
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Icon(Icons.search),
                label: const Text('Buscar'),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
              ),
              if (_error != null) ...[
                const SizedBox(height: 12),
                Text(_error!, style: const TextStyle(color: Colors.red)),
              ],
            ] else ...[
              _buildSelectedReservation(),
              const SizedBox(height: 24),
              Text('Datos del huésped', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      value: _documentType,
                      decoration: const InputDecoration(labelText: 'Tipo documento'),
                      items: const [
                        DropdownMenuItem(value: 'dni', child: Text('DNI')),
                        DropdownMenuItem(value: 'nie', child: Text('NIE')),
                        DropdownMenuItem(value: 'passport', child: Text('Pasaporte')),
                      ],
                      onChanged: (v) => setState(() => _documentType = v ?? 'dni'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: TextFormField(
                      controller: _docNumberController,
                      decoration: InputDecoration(
                        labelText: 'Nº Documento *',
                        suffixIcon: _scanned
                            ? const Icon(Icons.check_circle, color: Colors.green, size: 20)
                            : null,
                      ),
                      validator: (v) => v?.isEmpty == true ? 'Obligatorio' : null,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _nameController,
                      decoration: const InputDecoration(labelText: 'Nombre *'),
                      validator: (v) => v?.isEmpty == true ? 'Obligatorio' : null,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: TextFormField(
                      controller: _surnameController,
                      decoration: const InputDecoration(labelText: 'Apellidos *'),
                      validator: (v) => v?.isEmpty == true ? 'Obligatorio' : null,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _nationalityController,
                      decoration: const InputDecoration(labelText: 'Nacionalidad'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextFormField(
                      controller: _birthDateController,
                      decoration: const InputDecoration(labelText: 'Fecha nacimiento'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: _openScanner,
                icon: const Icon(Icons.document_scanner),
                label: const Text('Escanear DNI / Pasaporte'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.indigo,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
              ),
              const SizedBox(height: 24),
              Text('Firma del huésped', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 12),
              SignaturePad(
                onSign: (base64) => setState(() => _signatureBase64 = base64),
              ),
              const SizedBox(height: 24),
              if (_error != null) ...[
                Text(_error!, style: const TextStyle(color: Colors.red)),
                const SizedBox(height: 12),
              ],
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: _resetForm,
                      child: const Text('Cancelar'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: ElevatedButton.icon(
                      onPressed: _submitting ? null : _submitCheckin,
                      icon: _submitting
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                          : const Icon(Icons.check_circle),
                      label: const Text('Registrar check-in'),
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildSelectedReservation() {
    final r = _selectedReservation!;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.green, size: 20),
                const SizedBox(width: 8),
                Expanded(child: Text(r['guest_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16))),
                IconButton(icon: const Icon(Icons.close, size: 20), onPressed: _resetForm),
              ],
            ),
            const Divider(),
            _detailRow('Código', r['code'] ?? ''),
            _detailRow('Entrada', r['checkin_date'] ?? ''),
            _detailRow('Salida', r['checkout_date'] ?? ''),
            _detailRow('Huéspedes', '${r['adults']} adultos / ${r['children']} niños'),
          ],
        ),
      ),
    );
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          Text('$label: ', style: TextStyle(color: Colors.grey[600], fontSize: 13)),
          Text(value, style: const TextStyle(fontSize: 13)),
        ],
      ),
    );
  }
}
