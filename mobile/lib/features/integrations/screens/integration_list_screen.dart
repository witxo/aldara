import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/empty_state.dart';
import '../../auth/providers/auth_provider.dart';

class IntegrationListScreen extends StatefulWidget {
  const IntegrationListScreen({super.key});

  @override
  State<IntegrationListScreen> createState() => _IntegrationListScreenState();
}

class _IntegrationListScreenState extends State<IntegrationListScreen> {
  List<dynamic> _integrations = [];
  List<dynamic> _properties = [];
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().api;
      final iRes = await api.get('/integrations');
      final pRes = await api.get('/properties');
      if (mounted) setState(() {
        _integrations = (iRes['data'] as List<dynamic>?) ?? [];
        _properties = (pRes['data'] as List<dynamic>?) ?? [];
        _loading = false;
      });
    } catch (e) { if (mounted) setState(() => _loading = false); }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('integration.title'),
      currentRoute: 'integrations',
      body: _loading ? const Center(child: CircularProgressIndicator())
          : ListView(padding: const EdgeInsets.all(16), children: [
              if (_integrations.isEmpty && _properties.isEmpty)
                EmptyState(icon: Icons.settings_outlined)
              else ...[
                if (_integrations.isNotEmpty) ...[
                  Text('Conexiones activas', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 12),
                  ..._integrations.map((i) => Card(margin: const EdgeInsets.only(bottom: 8), child: ListTile(
                    leading: Icon(i['provider'] == 'booking' ? Icons.hotel : i['provider'] == 'airbnb' ? Icons.home : Icons.link, color: AppColors.primary),
                    title: Text(i['provider'] ?? ''),
                    subtitle: Text('${i['provider_id'] ?? ''}'),
                    trailing: StatusBadgeWidget(i['is_active'] == true ? 'active' : 'inactive'),
                  ))),
                  const SizedBox(height: 24),
                ],
                if (_properties.isNotEmpty) ...[
                  Text('Importar ICS', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 12),
                  ..._properties.map((p) => Card(margin: const EdgeInsets.only(bottom: 8), child: ListTile(
                    leading: const Icon(Icons.upload_file, color: AppColors.primary),
                    title: Text(p['name'] ?? ''),
                    trailing: const Icon(Icons.chevron_right, color: AppColors.textMuted),
                    onTap: () => _showIcsImport(context, p),
                  ))),
                ],
              ],
            ]),
    );
  }

  void _showIcsImport(BuildContext context, dynamic property) {
    showDialog(context: context, builder: (ctx) => AlertDialog(
      title: const Text('Importar ICS'),
      content: const Text('Seleccione un archivo ICS para importar las reservas.'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancelar')),
        ElevatedButton(onPressed: () => Navigator.pop(ctx), child: const Text('Seleccionar archivo')),
      ],
    ));
  }
}

class StatusBadgeWidget extends StatelessWidget {
  final String status;
  const StatusBadgeWidget(this.status, {super.key});

  @override
  Widget build(BuildContext context) {
    final color = status == 'active' ? AppColors.success : AppColors.textMuted;
    final bg = status == 'active' ? AppColors.successBg : AppColors.surface;
    return Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3), decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(12)),
      child: Text(status == 'active' ? 'Activo' : 'Inactivo', style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w600)));
  }
}
