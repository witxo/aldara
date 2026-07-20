import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/api/api_client.dart';
import '../../auth/providers/auth_provider.dart';

class ReservationFormScreen extends StatefulWidget {
  const ReservationFormScreen({super.key});

  @override
  State<ReservationFormScreen> createState() => _ReservationFormScreenState();
}

class _ReservationFormScreenState extends State<ReservationFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _adultsCtrl = TextEditingController(text: '1');
  final _childrenCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();
  DateTime? _checkinDate = DateTime.now().add(const Duration(days: 1));
  DateTime? _checkoutDate = DateTime.now().add(const Duration(days: 3));
  int? _propertyId;
  List<dynamic> _properties = [];
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _loadProperties();
  }

  Future<void> _loadProperties() async {
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/properties');
      if (mounted) setState(() => _properties = (res['data'] as List<dynamic>?) ?? []);
    } catch (_) {}
  }

  @override
  void dispose() {
    _nameCtrl.dispose(); _emailCtrl.dispose(); _phoneCtrl.dispose();
    _adultsCtrl.dispose(); _childrenCtrl.dispose(); _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate() || _propertyId == null || _checkinDate == null || _checkoutDate == null) return;
    setState(() => _saving = true);
    try {
      final api = context.read<AuthProvider>().api;
      await api.post('/reservations', data: {
        'property_id': _propertyId,
        'guest_name': _nameCtrl.text,
        'guest_email': _emailCtrl.text.isEmpty ? null : _emailCtrl.text,
        'guest_phone': _phoneCtrl.text.isEmpty ? null : _phoneCtrl.text,
        'adults': int.tryParse(_adultsCtrl.text) ?? 1,
        'children': int.tryParse(_childrenCtrl.text) ?? 0,
        'checkin_date': _checkinDate!.toIso8601String().split('T')[0],
        'checkout_date': _checkoutDate!.toIso8601String().split('T')[0],
        'notes': _notesCtrl.text.isEmpty ? null : _notesCtrl.text,
      });
      if (mounted) Navigator.pop(context, true);
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nueva reserva')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              DropdownButtonFormField<int>(
                decoration: const InputDecoration(labelText: 'Alojamiento'),
                items: _properties.map((p) => DropdownMenuItem(value: p['id'] as int?, child: Text(p['name'] ?? ''))).toList(),
                onChanged: (v) => _propertyId = v,
                validator: (v) => v == null ? 'Seleccione un alojamiento' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(controller: _nameCtrl, decoration: const InputDecoration(labelText: 'Nombre del huésped'), validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: TextFormField(controller: _emailCtrl, decoration: const InputDecoration(labelText: 'Email'), keyboardType: TextInputType.emailAddress)),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _phoneCtrl, decoration: const InputDecoration(labelText: 'Teléfono'))),
              ]),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: _dateField('Fecha entrada', _checkinDate, (d) => setState(() => _checkinDate = d))),
                const SizedBox(width: 12),
                Expanded(child: _dateField('Fecha salida', _checkoutDate, (d) => setState(() => _checkoutDate = d))),
              ]),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: TextFormField(controller: _adultsCtrl, decoration: const InputDecoration(labelText: 'Adultos'), keyboardType: TextInputType.number)),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _childrenCtrl, decoration: const InputDecoration(labelText: 'Niños'), keyboardType: TextInputType.number)),
              ]),
              const SizedBox(height: 12),
              TextFormField(controller: _notesCtrl, decoration: const InputDecoration(labelText: 'Notas'), maxLines: 3),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(fixedSize: const Size.fromHeight(44)),
                child: _saving ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Crear reserva'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _dateField(String label, DateTime? value, Function(DateTime) onSelected) {
    return TextFormField(
      readOnly: true,
      decoration: InputDecoration(labelText: label, suffixIcon: const Icon(Icons.calendar_today, size: 18)),
      controller: TextEditingController(text: value != null ? '${value.day}/${value.month}/${value.year}' : ''),
      onTap: () async {
        final d = await showDatePicker(context: context, initialDate: value ?? DateTime.now(), firstDate: DateTime.now(), lastDate: DateTime.now().add(const Duration(days: 365)));
        if (d != null) onSelected(d);
      },
      validator: (v) => value == null ? 'Requerido' : null,
    );
  }
}
