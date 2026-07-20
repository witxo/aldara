import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/app_layout.dart' show AppCard;
import '../../auth/providers/auth_provider.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _testingSes = false;
  String? _sesResult;

  Future<void> _testSes() async {
    setState(() { _testingSes = true; _sesResult = null; });
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.post('/ses/test');
      setState(() { _sesResult = res['message'] as String? ?? 'OK'; _testingSes = false; });
    } catch (e) {
      setState(() { _sesResult = 'Error: $e'; _testingSes = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    final auth = context.watch<AuthProvider>();
    final lang = auth.user?['language'] as String? ?? 'es';

    return AppLayout(
      title: l.translate('setting.title'),
      currentRoute: 'settings',
      body: ListView(padding: const EdgeInsets.all(16), children: [
        AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(l.translate('setting.company'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 12),
          _field(l.translate('common.name'), auth.selectedTenant?['company_name'] as String? ?? '-'),
          _field(l.translate('common.email'), auth.user?['email'] as String? ?? '-'),
        ])),
        const SizedBox(height: 16),
        AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(l.translate('setting.language'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            value: lang,
            decoration: const InputDecoration(isDense: true),
            items: const [DropdownMenuItem(value: 'es', child: Text('Español')), DropdownMenuItem(value: 'en', child: Text('English'))],
            onChanged: (v) {
              if (v != null) {
                final updatedUser = Map<String, dynamic>.from(auth.user ?? {});
                updatedUser['language'] = v;
                auth.notifyListeners();
              }
            },
          ),
        ])),
        const SizedBox(height: 16),
        AppCard(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(l.translate('setting.ses_test'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 12),
          ElevatedButton.icon(
            onPressed: _testingSes ? null : _testSes,
            icon: _testingSes ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.check_circle, size: 16),
            label: Text(l.translate('setting.ses_test')),
          ),
          if (_sesResult != null) ...[
            const SizedBox(height: 8),
            Text(_sesResult!, style: TextStyle(color: _sesResult!.startsWith('Error') ? AppColors.danger : AppColors.success, fontSize: 13)),
          ],
        ])),
      ]),
    );
  }

  Widget _field(String label, String value) => Padding(padding: const EdgeInsets.only(bottom: 6), child: Row(children: [SizedBox(width: 100, child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))), Expanded(child: Text(value, style: const TextStyle(fontSize: 13)))]));
}
