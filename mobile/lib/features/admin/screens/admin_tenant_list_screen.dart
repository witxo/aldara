import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../core/widgets/empty_state.dart';
import '../../auth/providers/auth_provider.dart';

class AdminTenantListScreen extends StatefulWidget {
  const AdminTenantListScreen({super.key});

  @override
  State<AdminTenantListScreen> createState() => _AdminTenantListScreenState();
}

class _AdminTenantListScreenState extends State<AdminTenantListScreen> {
  List<dynamic> _tenants = [];
  bool _loading = true;
  final _searchCtrl = TextEditingController();

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().api;
      final params = <String, dynamic>{'per_page': '50'};
      if (_searchCtrl.text.isNotEmpty) params['search'] = _searchCtrl.text;
      final res = await api.get('/admin/tenants', queryParameters: params);
      if (mounted) setState(() { _tenants = (res['data'] as List<dynamic>?) ?? []; _loading = false; });
    } catch (e) { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _toggleTenant(dynamic t) async {
    try {
      final api = context.read<AuthProvider>().api;
      await api.post('/admin/tenants/${t['id']}/toggle');
      _load();
    } catch (e) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'))); }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('admin.tenants'),
      currentRoute: 'admin',
      body: Column(children: [
        Padding(padding: const EdgeInsets.all(16), child: TextField(
          controller: _searchCtrl,
          decoration: InputDecoration(hintText: l.translate('common.search'), prefixIcon: const Icon(Icons.search, size: 20), isDense: true, contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10)),
          onSubmitted: (_) => _load(),
        )),
        Expanded(child: _loading ? const Center(child: CircularProgressIndicator())
            : _tenants.isEmpty ? EmptyState()
            : RefreshIndicator(onRefresh: _load, child: ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 16), itemCount: _tenants.length,
                itemBuilder: (_, i) {
                  final t = _tenants[i];
                  return Card(margin: const EdgeInsets.only(bottom: 8), child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Row(children: [
                      Container(width: 44, height: 44, decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(10)),
                        child: Center(child: Text(((t['company_name'] as String? ?? '?')[0]).toUpperCase(), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.primary)))),
                      const SizedBox(width: 12),
                      Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Text(t['company_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600)),
                        Text('${t['email'] ?? ''} · ${t['properties_count'] ?? 0} aloj.', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                      ])),
                      StatusBadge(t['status'] as String?),
                      IconButton(icon: Icon(t['status'] == 'active' ? Icons.toggle_on : Icons.toggle_off_outlined, color: t['status'] == 'active' ? AppColors.success : AppColors.textMuted),
                        onPressed: () => _toggleTenant(t)),
                    ]),
                  ));
                },
              ))),
      ]),
    );
  }
}
