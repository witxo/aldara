import 'package:flutter/material.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/widgets/app_layout.dart';

class GuestDetailScreen extends StatelessWidget {
  final dynamic guest;
  const GuestDetailScreen({super.key, required this.guest});

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    final g = guest as Map<String, dynamic>;
    return AppLayout(
      title: '${g['first_name'] ?? ''} ${g['last_name'] ?? ''}',
      currentRoute: 'guests',
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    CircleAvatar(radius: 28, backgroundColor: AppColors.primaryBg,
                      child: Text(((g['first_name'] as String? ?? '?')[0]).toUpperCase(), style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: AppColors.primary))),
                    const SizedBox(width: 16),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text('${g['first_name'] ?? ''} ${g['last_name'] ?? ''}', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      Text(g['email'] ?? '', style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                    ])),
                  ]),
                  const Divider(height: 24),
                  _field(l.translate('guest.document_type'), g['document_type'] ?? '-'),
                  _field(l.translate('guest.document_number'), g['document_number'] ?? '-'),
                  _field(l.translate('guest.nationality'), g['nationality'] ?? '-'),
                  _field(l.translate('guest.birth_date'), g['birth_date'] ?? '-'),
                  if (g['phone'] != null) _field(l.translate('common.phone'), g['phone']),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _field(String label, String value) {
    return Padding(padding: const EdgeInsets.only(bottom: 8), child: Row(children: [
      SizedBox(width: 120, child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))),
      Expanded(child: Text(value, style: const TextStyle(fontSize: 13))),
    ]));
  }
}
