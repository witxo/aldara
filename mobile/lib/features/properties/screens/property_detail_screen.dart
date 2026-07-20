import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/status_badge.dart';
import '../../auth/providers/auth_provider.dart';

class PropertyDetailScreen extends StatefulWidget {
  final dynamic property;
  const PropertyDetailScreen({super.key, required this.property});

  @override
  State<PropertyDetailScreen> createState() => _PropertyDetailScreenState();
}

class _PropertyDetailScreenState extends State<PropertyDetailScreen> {
  bool _testingSes = false;
  String? _sesResult;

  Future<void> _testSes() async {
    setState(() { _testingSes = true; _sesResult = null; });
    try {
      final api = context.read<AuthProvider>().api;
      final p = widget.property as Map<String, dynamic>;
      final res = await api.post('/ses/test/${p['id']}');
      setState(() { _sesResult = res['message'] as String? ?? 'OK'; _testingSes = false; });
    } catch (e) {
      setState(() { _sesResult = 'Error: $e'; _testingSes = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    final p = widget.property as Map<String, dynamic>;

    return AppLayout(
      title: p['name'] ?? '',
      currentRoute: 'properties',
      actions: [
        IconButton(
          icon: const Icon(Icons.edit_outlined),
          onPressed: () => Navigator.pushNamed(context, '/properties/edit', arguments: widget.property),
        ),
      ],
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            margin: const EdgeInsets.only(bottom: 16),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(p['name'] ?? '', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                      const Spacer(),
                      StatusBadge(p['is_active'] == true ? 'active' : 'inactive'),
                    ],
                  ),
                  const SizedBox(height: 16),
                  _field(l.translate('property.type'), _typeLabel(l, p['type'])),
                  _field(l.translate('property.city'), p['city']),
                  _field(l.translate('property.capacity'), '${p['capacity'] ?? '-'} ${p['capacity'] == 1 ? 'persona' : 'personas'}'),
                  _field(l.translate('property.license'), p['license_number'] ?? '-'),
                  _field(l.translate('property.checkin_time'), p['checkin_time'] ?? '-'),
                  _field(l.translate('property.checkout_time'), p['checkout_time'] ?? '-'),
                  const Divider(),
                  Text('SES Hospedajes', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
                  const SizedBox(height: 4),
                  _field('Código MIR', p['ses_establecimiento_code'] ?? '-'),
                  _field('Usuario SES', p['ses_username'] ?? '-'),
                  _field('Código arrendador', p['ses_codigo_arrendador'] ?? '-'),
                  const SizedBox(height: 8),
                  ElevatedButton.icon(
                    onPressed: _testingSes ? null : _testSes,
                    icon: _testingSes
                        ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.check_circle, size: 16),
                    label: Text('Probar conexión SES'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      textStyle: const TextStyle(fontSize: 13),
                    ),
                  ),
                  if (_sesResult != null) ...[
                    const SizedBox(height: 8),
                    Text(_sesResult!, style: TextStyle(color: _sesResult!.startsWith('Error') ? AppColors.danger : AppColors.success, fontSize: 13)),
                  ],
                ],
              ),
            ),
          ),
          Card(
            margin: const EdgeInsets.only(bottom: 16),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(l.translate('property.address'), style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(p['address_line1'] ?? '', style: const TextStyle(color: AppColors.textSecondary)),
                  if (p['address_line2'] != null) Text(p['address_line2'], style: const TextStyle(color: AppColors.textSecondary)),
                  Text('${p['postal_code'] ?? ''} ${p['city'] ?? ''}, ${p['state'] ?? ''}'),
                  Text(p['country'] ?? ''),
                ],
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: OutlinedButton.icon(
              onPressed: () => Navigator.pushNamed(context, '/properties/edit', arguments: widget.property),
              icon: const Icon(Icons.edit_outlined),
              label: Text(l.translate('common.edit')),
              style: OutlinedButton.styleFrom(fixedSize: const Size.fromHeight(44)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _field(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 120, child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))),
          Expanded(child: Text(value, style: const TextStyle(fontSize: 13))),
        ],
      ),
    );
  }

  String _typeLabel(AppLocalizations l, String? type) {
    switch (type) {
      case 'apartment': return l.translate('property.type_apartment');
      case 'house': return l.translate('property.type_house');
      case 'villa': return l.translate('property.type_villa');
      case 'studio': return l.translate('property.type_studio');
      case 'hotel': return l.translate('property.type_hotel');
      case 'rural': return l.translate('property.type_rural');
      default: return l.translate('property.type_other');
    }
  }
}
