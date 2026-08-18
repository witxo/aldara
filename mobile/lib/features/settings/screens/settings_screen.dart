import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/app_layout.dart' show AppCard;
import '../../auth/providers/auth_provider.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

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
          Text('Soporte', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 12),
          ListTile(contentPadding: EdgeInsets.zero, leading: const Icon(Icons.mail_outline, color: AppColors.primary), title: const Text('Contacto'), subtitle: const Text('Envíanos un mensaje', style: TextStyle(fontSize: 12)), onTap: () => Navigator.pushNamed(context, '/contact')),
        ])),
      ]),
    );
  }

  Widget _field(String label, String value) => Padding(padding: const EdgeInsets.only(bottom: 6), child: Row(children: [SizedBox(width: 100, child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))), Expanded(child: Text(value, style: const TextStyle(fontSize: 13)))]));
}
