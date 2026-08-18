import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../auth/providers/auth_provider.dart';

class ContactScreen extends StatefulWidget {
  const ContactScreen({super.key});

  @override
  State<ContactScreen> createState() => _ContactScreenState();
}

class _ContactScreenState extends State<ContactScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _subjectCtrl = TextEditingController();
  final _messageCtrl = TextEditingController();
  bool _sending = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _subjectCtrl.dispose();
    _messageCtrl.dispose();
    super.dispose();
  }

  Future<void> _send() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _sending = true);
    try {
      final api = context.read<AuthProvider>().api;
      await api.post('/contact', data: {
        'name': _nameCtrl.text.trim(),
        'email': _emailCtrl.text.trim(),
        'subject': _subjectCtrl.text.trim(),
        'message': _messageCtrl.text.trim(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Mensaje enviado correctamente'), backgroundColor: AppColors.success));
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: 'Contacto',
      currentRoute: 'contact',
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text('¿Tienes alguna pregunta?', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 4),
              Text('Rellena el formulario y te responderemos lo antes posible.', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
              const SizedBox(height: 24),
              TextFormField(controller: _nameCtrl, decoration: const InputDecoration(labelText: 'Nombre'), validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null),
              const SizedBox(height: 12),
              TextFormField(controller: _emailCtrl, decoration: const InputDecoration(labelText: 'Email'), keyboardType: TextInputType.emailAddress, validator: (v) => (v == null || v.isEmpty || !v.contains('@')) ? 'Email no válido' : null),
              const SizedBox(height: 12),
              TextFormField(controller: _subjectCtrl, decoration: const InputDecoration(labelText: 'Asunto'), validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null),
              const SizedBox(height: 12),
              TextFormField(controller: _messageCtrl, decoration: const InputDecoration(labelText: 'Mensaje'), maxLines: 5, validator: (v) => (v == null || v.isEmpty) ? 'Requerido' : null),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _sending ? null : _send,
                style: ElevatedButton.styleFrom(fixedSize: const Size.fromHeight(44)),
                child: _sending ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Enviar mensaje'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}