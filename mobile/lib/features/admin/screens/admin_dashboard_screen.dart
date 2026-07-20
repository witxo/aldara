import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../auth/providers/auth_provider.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  Map<String, dynamic>? _stats;
  bool _loading = true;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/admin/stats');
      if (mounted) setState(() { _stats = res['data'] as Map<String, dynamic>?; _loading = false; });
    } catch (e) { if (mounted) setState(() => _loading = false); }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('admin.title'),
      currentRoute: 'admin',
      body: _loading ? const Center(child: CircularProgressIndicator())
          : ListView(padding: const EdgeInsets.all(16), children: [
              Text('Panel de administración', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              if (_stats != null) ...[
                GridView.count(crossAxisCount: 2, shrinkWrap: true, physics: const NeverScrollableScrollPhysics(), mainAxisSpacing: 12, crossAxisSpacing: 12, childAspectRatio: 1.5,
                  children: [
                    _statCard('Empresas', '${_stats!['total_tenants'] ?? 0}', Icons.business, AppColors.primary),
                    _statCard('Activas', '${_stats!['active_tenants'] ?? 0}', Icons.check_circle, AppColors.success),
                    _statCard('Alojamientos', '${_stats!['total_properties'] ?? 0}', Icons.home, AppColors.warning),
                    _statCard('Reservas', '${_stats!['total_reservations'] ?? 0}', Icons.calendar_today, AppColors.primary),
                    _statCard('Usuarios', '${_stats!['total_users'] ?? 0}', Icons.people, AppColors.success),
                    _statCard('Ingresos', '${_stats!['monthly_revenue'] ?? 0}€', Icons.euro, AppColors.warning),
                  ],
                ),
              ],
              const SizedBox(height: 16),
              Card(child: ListTile(
                leading: const Icon(Icons.business, color: AppColors.primary),
                title: const Text('Empresas'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.pushNamed(context, '/admin/tenants'),
              )),
            ]),
    );
  }

  Widget _statCard(String label, String value, IconData icon, Color color) {
    return Card(margin: EdgeInsets.zero, child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.center, children: [
      Icon(icon, size: 20, color: color),
      const SizedBox(height: 8),
      Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
      Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
    ])));
  }
}
