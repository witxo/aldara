import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../auth/providers/auth_provider.dart';

class BillingScreen extends StatefulWidget {
  const BillingScreen({super.key});

  @override
  State<BillingScreen> createState() => _BillingScreenState();
}

class _BillingScreenState extends State<BillingScreen> {
  Map<String, dynamic>? _subscription;
  List<dynamic> _invoices = [];
  bool _loading = true;
  bool _hasSubscription = false;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final api = context.read<AuthProvider>().api;
      try { final sRes = await api.get('/billing/subscription'); _subscription = sRes['data'] as Map<String, dynamic>?; _hasSubscription = true; } catch (_) { _hasSubscription = false; }
      try { final iRes = await api.get('/billing/invoices'); _invoices = (iRes['data'] as List<dynamic>?) ?? []; } catch (_) {}
      if (mounted) setState(() => _loading = false);
    } catch (_) { if (mounted) setState(() => _loading = false); }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('billing.title'),
      currentRoute: 'billing',
      body: _loading ? const Center(child: CircularProgressIndicator())
          : ListView(padding: const EdgeInsets.all(16), children: [
              if (_hasSubscription && _subscription != null) ...[
                Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(l.translate('billing.current_plan'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 12),
                  Row(children: [
                    Container(padding: const EdgeInsets.all(8), decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(8)), child: const Icon(Icons.workspace_premium, color: AppColors.primary)),
                    const SizedBox(width: 12),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text((_subscription!['plan'] as Map<String, dynamic>?)?['name'] ?? 'Plan', style: const TextStyle(fontWeight: FontWeight.w600)),
                      Text('${(_subscription!['plan'] as Map<String, dynamic>?)?['price_yearly'] ?? ''}€/año', style: const TextStyle(color: AppColors.textSecondary)),
                    ])),
                    StatusBadgeWidgetBilling(_subscription!['status'] as String? ?? ''),
                  ]),
                  if (_subscription!['trial_ends_at'] != null) ...[
                    const Divider(), Text('Período de prueba hasta ${_subscription!['trial_ends_at']}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                  ],
                  const Divider(),
                  _limitRow('Alojamientos', '${(_subscription!['plan'] as Map<String, dynamic>?)?['max_properties'] ?? '∞'}'),
                  _limitRow('Usuarios', '${(_subscription!['plan'] as Map<String, dynamic>?)?['max_users'] ?? '∞'}'),
                  _limitRow('Reservas/mes', (_subscription!['plan'] as Map<String, dynamic>?)?['max_reservations'] != null ? '${(_subscription!['plan'] as Map<String, dynamic>?)?['max_reservations']}' : 'Ilimitadas'),
                ]))),
                const SizedBox(height: 24),
              ],
              Text(l.translate('billing.invoices'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 12),
              if (_invoices.isEmpty)
                Card(child: Padding(padding: const EdgeInsets.all(24), child: Center(child: Text('No hay facturas', style: TextStyle(color: AppColors.textSecondary)))))
              else ..._invoices.map((inv) => Card(margin: const EdgeInsets.only(bottom: 8), child: ListTile(
                leading: const Icon(Icons.receipt, color: AppColors.primary),
                title: Text('Factura #${inv['id']}'),
                subtitle: Text(inv['created_at'] ?? ''),
                trailing: Text('${inv['amount'] ?? ''}€', style: const TextStyle(fontWeight: FontWeight.w600)),
              ))),
            ]),
    );
  }

  Widget _limitRow(String label, String value) {
    return Padding(padding: const EdgeInsets.symmetric(vertical: 3), child: Row(children: [
      Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
      const SizedBox(width: 8),
      Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
    ]));
  }
}

class StatusBadgeWidgetBilling extends StatelessWidget {
  final String status;
  const StatusBadgeWidgetBilling(this.status, {super.key});
  @override
  Widget build(BuildContext context) {
    final active = status == 'active' || status == 'trialing';
    return Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3), decoration: BoxDecoration(color: active ? AppColors.successBg : AppColors.dangerBg, borderRadius: BorderRadius.circular(12)),
      child: Text(active ? 'Activo' : status, style: TextStyle(color: active ? AppColors.success : AppColors.danger, fontSize: 11, fontWeight: FontWeight.w600)));
  }
}
