import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/status_badge.dart';
import '../../auth/providers/auth_provider.dart';

class CheckinDetailScreen extends StatefulWidget {
  final dynamic checkin;
  const CheckinDetailScreen({super.key, required this.checkin});

  @override
  State<CheckinDetailScreen> createState() => _CheckinDetailScreenState();
}

class _CheckinDetailScreenState extends State<CheckinDetailScreen> {
  late Map<String, dynamic>? _c;
  bool _loading = true;
  bool _verifying = false;

  @override
  void initState() { super.initState(); _c = widget.checkin as Map<String, dynamic>?; _loadDetail(); }

  Future<void> _loadDetail() async {
    if (_c == null) return;
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/checkins/${_c!['id']}');
      if (mounted) setState(() { _c = res['data'] as Map<String, dynamic>?; _loading = false; });
    } catch (e) { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _verify(String status) async {
    setState(() => _verifying = true);
    try {
      final api = context.read<AuthProvider>().api;
      await api.post('/checkins/${_c!['id']}/verify', data: {'status': status});
      if (mounted) { ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(status == 'verified' ? 'Verificado' : 'Rechazado'), backgroundColor: status == 'verified' ? AppColors.success : AppColors.danger)); _loadDetail(); }
    } catch (e) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'))); }
    finally { if (mounted) setState(() => _verifying = false); }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    final r = _c?['reservation'] as Map<String, dynamic>?;
    return AppLayout(
      title: 'Check-in #${_c?['id'] ?? ''}',
      currentRoute: 'checkins',
      body: _loading ? const Center(child: CircularProgressIndicator())
          : _c == null ? const SizedBox()
          : ListView(padding: const EdgeInsets.all(16), children: [
              Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Row(children: [Expanded(child: Text(r?['guest_name'] ?? '', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold))), StatusBadge(_c!['status'] as String?)],
                ), const Divider(height: 24),
                _field('Tipo', _c!['type'] ?? '-'),
                _field('Reserva', r?['code'] ?? '-'),
                _field('Huésped', r?['guest_name'] ?? '-'),
                if (_c!['verified_at'] != null) _field('Verificado', _c!['verified_at']),
                if (_c!['notes'] != null) _field('Notas', _c!['notes']),
              ]))),
              if (_c!['status'] == 'completed')
                Padding(padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8), child: Row(children: [
                  Expanded(child: OutlinedButton.icon(onPressed: _verifying ? null : () => _verify('rejected'), icon: const Icon(Icons.close, size: 16), label: Text(l.translate('checkin.reject')), style: OutlinedButton.styleFrom(foregroundColor: AppColors.danger, side: const BorderSide(color: AppColors.danger), fixedSize: const Size.fromHeight(44)))),
                  const SizedBox(width: 12),
                  Expanded(child: ElevatedButton.icon(onPressed: _verifying ? null : () => _verify('verified'), icon: _verifying ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.check, size: 16), label: Text(l.translate('checkin.verify')), style: ElevatedButton.styleFrom(fixedSize: const Size.fromHeight(44)))),
                ])),
            ]),
    );
  }

  Widget _field(String label, String value) => Padding(padding: const EdgeInsets.only(bottom: 6), child: Row(children: [SizedBox(width: 100, child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13))), Expanded(child: Text(value, style: const TextStyle(fontSize: 13)))]));
}
