import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../core/widgets/empty_state.dart';
import '../../auth/providers/auth_provider.dart';

class PropertyListScreen extends StatefulWidget {
  const PropertyListScreen({super.key});

  @override
  State<PropertyListScreen> createState() => _PropertyListScreenState();
}

class _PropertyListScreenState extends State<PropertyListScreen> {
  List<dynamic> _properties = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/properties');
      if (mounted) setState(() { _properties = (res['data'] as List<dynamic>?) ?? []; _loading = false; });
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('property.title'),
      currentRoute: 'properties',
      floatingActionButton: FloatingActionButton(
        backgroundColor: AppColors.primary,
        onPressed: () => Navigator.pushNamed(context, '/properties/create').then((_) => _load()),
        child: const Icon(Icons.add, color: Colors.white),
      ),
      body: _loading
        ? const Center(child: CircularProgressIndicator())
        : _properties.isEmpty
          ? EmptyState(icon: Icons.business_outlined, message: l.translate('common.no_data'), actionLabel: l.translate('property.create'), onAction: () => Navigator.pushNamed(context, '/properties/create'))
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _properties.length,
                itemBuilder: (_, i) => _buildCard(_properties[i]),
              ),
            ),
    );
  }

  Widget _buildCard(dynamic p) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => Navigator.pushNamed(context, '/properties/detail', arguments: p),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 48, height: 48,
                decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(10)),
                child: const Icon(Icons.business, color: AppColors.primary),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(p['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 2),
                    Text('${p['city'] ?? ''} · ${p['capacity'] ?? '-'} pers', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                  ],
                ),
              ),
              StatusBadge(p['is_active'] == true ? 'active' : 'inactive'),
              const SizedBox(width: 8),
              const Icon(Icons.chevron_right, color: AppColors.textMuted),
            ],
          ),
        ),
      ),
    );
  }
}
