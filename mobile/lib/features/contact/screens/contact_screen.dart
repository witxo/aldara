import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_wkwebview/webview_flutter_wkwebview.dart';
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
  String? _recaptchaToken;
  bool _sending = false;
  bool _recaptchaLoading = false;

  Future<void> _executeRecaptcha() async {
    if (_recaptchaToken != null) return;

    setState(() => _recaptchaLoading = true);

    try {
      final WebViewController controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..addJavaScriptChannel(
          'RecaptchaChannel',
          onMessageReceived: (JavaScriptMessage message) {
            final token = message.message;
            if (token.isNotEmpty) {
              setState(() => _recaptchaToken = token);
            }
          },
        )
        ..loadHtmlString(_recaptchaHtml());

      await Future.delayed(const Duration(milliseconds: 500));

      final token = await controller.runJavaScriptReturningResult(
        "grecaptcha.execute('${_getSiteKey()}', {action: 'contact'});",
      );

      if (token != null && token.toString().isNotEmpty && token.toString() != 'null') {
        setState(() => _recaptchaToken = token.toString());
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error reCAPTCHA: $e'), backgroundColor: Colors.orange),
        );
      }
    } finally {
      if (mounted) setState(() => _recaptchaLoading = false);
    }
  }

  String _getSiteKey() {
    // This should be configured via build config or fetched from API
    // For now, return empty string - the HTML will handle missing key gracefully
    return const String.fromEnvironment('RECAPTCHA_SITE_KEY', defaultValue: '');
  }

  String _recaptchaHtml() {
    final siteKey = _getSiteKey();
    return '''
    <!DOCTYPE html>
    <html>
    <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <script src="https://www.google.com/recaptcha/api.js?render=$siteKey"></script>
    </head>
    <body>
      <div style="text-align:center;padding:20px;">
        <p style="color:#666;">Verificando reCAPTCHA...</p>
      </div>
      <script>
        window.getRecaptchaToken = function(action) {
          return new Promise((resolve, reject) => {
            if (typeof grecaptcha === 'undefined') {
              reject('reCAPTCHA not loaded');
              return;
            }
            grecaptcha.ready(function() {
              grecaptcha.execute('$siteKey', {action: action}).then(resolve).catch(reject);
            });
          });
        };
      </script>
    </body>
    </html>
    ''';
  }

  Future<void> _send() async {
    if (!_formKey.currentState!.validate()) return;

    await _executeRecaptcha();

    if (_recaptchaToken == null) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Error al obtener token reCAPTCHA'), backgroundColor: Colors.red),
        );
      }
      return;
    }

    setState(() => _sending = true);
    try {
      final api = context.read<AuthProvider>().api;
      await api.post('/contact', data: {
        'name': _nameCtrl.text.trim(),
        'email': _emailCtrl.text.trim(),
        'subject': _subjectCtrl.text.trim(),
        'message': _messageCtrl.text.trim(),
        'recaptcha_token': _recaptchaToken,
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
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _subjectCtrl.dispose();
    _messageCtrl.dispose();
    super.dispose();
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
              const SizedBox(height: 16),
              if (_recaptchaLoading)
                const Center(child: CircularProgressIndicator())
              else if (_recaptchaToken != null)
                Row(
                  children: [
                    const Icon(Icons.check_circle, color: Colors.green),
                    const SizedBox(width: 8),
                    Text('reCAPTCHA verificado', style: TextStyle(color: Colors.green[700])),
                  ],
                )
              else
                TextButton.icon(
                  onPressed: _executeRecaptcha,
                  icon: const Icon(Icons.security),
                  label: const Text('Verificar reCAPTCHA'),
                ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: (_sending || _recaptchaLoading) ? null : _send,
                style: ElevatedButton.styleFrom(fixedSize: const Size.fromHeight(44)),
                child: (_sending || _recaptchaLoading)
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Enviar mensaje'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}