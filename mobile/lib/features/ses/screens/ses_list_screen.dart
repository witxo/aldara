import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../core/widgets/empty_state.dart';
import '../../auth/providers/auth_provider.dart';

class SesListScreen extends StatefulWidget {
  const SesListScreen({super.key});

  @override
  State<SesListScreen> createState() => _SesListScreenState();
}

class _SesListScreenState extends State<SesListScreen> {
  List<dynamic> _submissions = [];
  bool _loading = true;
  int _page = 1, _lastPage = 1;
  final _scrollCtrl = ScrollController();

  @override
  void initState() { super.initState(); _load(); _scrollCtrl.addListener(_onScroll); }
  @override
  void dispose() { _scrollCtrl.dispose(); super.dispose(); }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200 && _page < _lastPage) {
      _page++; _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() { _loading = true; _page = 1; });
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/ses/submissions', queryParameters: {'per_page': '20', 'page': '$_page'});
      if (mounted) setState(() { _submissions = (res['data'] as List<dynamic>?) ?? []; _lastPage = (res['meta']?['last_page'] as int?) ?? 1; _loading = false; });
    } catch (e) { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _loadMore() async {
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/ses/submissions', queryParameters: {'per_page': '20', 'page': '$_page'});
      if (mounted) setState(() { _submissions.addAll(res['data'] as List<dynamic>? ?? []); _lastPage = (res['meta']?['last_page'] as int?) ?? 1; });
    } catch (_) {}
  }

  Future<void> _sendSubmission(dynamic s) async {
    try {
      final api = context.read<AuthProvider>().api;
      await api.post('/ses/submissions/${s['id']}/send');
      if (mounted) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Envío realizado'), backgroundColor: AppColors.success)); _load(); }
    } catch (e) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'))); }
  }

  Future<void> _retrySubmission(dynamic s) async {
    try {
      final api = context.read<AuthProvider>().api;
      await api.post('/ses/submissions/${s['id']}/retry');
      if (mounted) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Reintentando envío'), backgroundColor: AppColors.primary)); _load(); }
    } catch (e) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'))); }
  }

  Future<void> _deleteSubmission(dynamic s) async {
    final confirm = await showDialog<bool>(context: context, builder: (ctx) => AlertDialog(
      title: const Text('Eliminar envío'),
      content: const Text('¿Está seguro de eliminar este envío SES?'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
        TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Eliminar', style: TextStyle(color: Colors.red))),
      ],
    ));
    if (confirm != true) return;
    try {
      final api = context.read<AuthProvider>().api;
      await api.delete('/ses/submissions/${s['id']}');
      if (mounted) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Envío eliminado'), backgroundColor: AppColors.success)); _load(); }
    } catch (e) { if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e'))); }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('ses.title'),
      currentRoute: 'ses',
      body: _loading ? const Center(child: CircularProgressIndicator())
          : _submissions.isEmpty ? EmptyState(icon: Icons.description_outlined)
          : RefreshIndicator(onRefresh: _load, child: ListView.builder(
              controller: _scrollCtrl, padding: const EdgeInsets.all(16),
              itemCount: _submissions.length,
              itemBuilder: (_, i) {
                final s = _submissions[i];
                final r = s['reservation'] as Map<String, dynamic>?;
                return Card(margin: const EdgeInsets.only(bottom: 8), child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Row(children: [
                    Container(width: 44, height: 44, decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(10)),
                      child: const Icon(Icons.description, color: AppColors.primary)),
                    const SizedBox(width: 12),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(r?['guest_name'] ?? 'SES #${s['id']}', style: const TextStyle(fontWeight: FontWeight.w600)),
                      Text(s['mode'] == 'auto' ? 'Automático' : s['mode'] == 'manual' ? 'Manual' : '', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                    ])),
                    StatusBadge(s['status'] as String?),
                    if (s['status'] == 'prepared')
                      IconButton(icon: const Icon(Icons.send, size: 18, color: AppColors.primary), onPressed: () => _sendSubmission(s)),
                    if (s['status'] == 'failed')
                      IconButton(icon: const Icon(Icons.refresh, size: 18, color: AppColors.warning), onPressed: () => _retrySubmission(s)),
                    IconButton(icon: const Icon(Icons.delete_outline, size: 18, color: AppColors.danger), onPressed: () => _deleteSubmission(s)),
                  ]),
                ));
              },
            )),
    );
  }
}
