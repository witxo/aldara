import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/api/api_client.dart';
import '../../auth/providers/auth_provider.dart';

class PropertyFormScreen extends StatefulWidget {
  final dynamic property;
  const PropertyFormScreen({super.key, this.property});

  @override
  State<PropertyFormScreen> createState() => _PropertyFormScreenState();
}

class _PropertyFormScreenState extends State<PropertyFormScreen> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _name, _address, _city, _state, _postalCode, _license, _capacity, _checkinTime, _checkoutTime;
  String _type = 'apartment';
  bool _saving = false;
  bool get _editing => widget.property != null;

  @override
  void initState() {
    super.initState();
    final p = widget.property as Map<String, dynamic>?;
    _name = TextEditingController(text: p?['name'] ?? '');
    _address = TextEditingController(text: p?['address_line1'] ?? '');
    _city = TextEditingController(text: p?['city'] ?? '');
    _state = TextEditingController(text: p?['state'] ?? '');
    _postalCode = TextEditingController(text: p?['postal_code'] ?? '');
    _license = TextEditingController(text: p?['license_number'] ?? '');
    _capacity = TextEditingController(text: p?['capacity']?.toString() ?? '');
    _checkinTime = TextEditingController(text: p?['checkin_time'] ?? '');
    _checkoutTime = TextEditingController(text: p?['checkout_time'] ?? '');
    _type = p?['type'] ?? 'apartment';
  }

  @override
  void dispose() {
    _name.dispose(); _address.dispose(); _city.dispose(); _state.dispose();
    _postalCode.dispose(); _license.dispose(); _capacity.dispose();
    _checkinTime.dispose(); _checkoutTime.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    try {
      final api = context.read<AuthProvider>().api;
      final data = {
        'name': _name.text, 'type': _type, 'address_line1': _address.text,
        'city': _city.text, 'state': _state.text, 'postal_code': _postalCode.text,
        'country': 'ES', 'license_number': _license.text,
        'capacity': int.tryParse(_capacity.text), 'checkin_time': _checkinTime.text.isEmpty ? null : _checkinTime.text,
        'checkout_time': _checkoutTime.text.isEmpty ? null : _checkoutTime.text,
      };

      if (_editing) {
        await api.put('/properties/${widget.property['id']}', data: data);
      } else {
        await api.post('/properties', data: data);
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
    return Scaffold(
      appBar: AppBar(title: Text(_editing ? 'Editar alojamiento' : 'Nuevo alojamiento')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(controller: _name, decoration: const InputDecoration(labelText: 'Nombre'), validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _type, decoration: const InputDecoration(labelText: 'Tipo'),
                items: ['apartment','house','villa','studio','hotel','rural','other'].map((t) => DropdownMenuItem(value: t, child: Text(t))).toList(),
                onChanged: (v) => setState(() => _type = v ?? 'apartment'),
              ),
              const SizedBox(height: 12),
              TextFormField(controller: _address, decoration: const InputDecoration(labelText: 'Dirección'), maxLines: 2),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: TextFormField(controller: _city, decoration: const InputDecoration(labelText: 'Ciudad'))),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _state, decoration: const InputDecoration(labelText: 'Provincia'))),
              ]),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: TextFormField(controller: _postalCode, decoration: const InputDecoration(labelText: 'CP'))),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _capacity, decoration: const InputDecoration(labelText: 'Capacidad'), keyboardType: TextInputType.number)),
              ]),
              const SizedBox(height: 12),
              Row(children: [
                Expanded(child: TextFormField(controller: _checkinTime, decoration: const InputDecoration(labelText: 'Hora entrada (HH:MM)'))),
                const SizedBox(width: 12),
                Expanded(child: TextFormField(controller: _checkoutTime, decoration: const InputDecoration(labelText: 'Hora salida (HH:MM)'))),
              ]),
              const SizedBox(height: 12),
              TextFormField(controller: _license, decoration: const InputDecoration(labelText: 'Nº licencia')),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _saving ? null : _save,
                style: ElevatedButton.styleFrom(fixedSize: const Size.fromHeight(44)),
                child: _saving ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text(_editing ? 'Guardar cambios' : 'Crear alojamiento'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
