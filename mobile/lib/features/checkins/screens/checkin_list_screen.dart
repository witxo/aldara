import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../core/widgets/empty_state.dart';
import '../../auth/providers/auth_provider.dart';

class CheckinListScreen extends StatefulWidget {
  const CheckinListScreen({super.key});

  @override
  State<CheckinListScreen> createState() => _CheckinListScreenState();
}

class _CheckinListScreenState extends State<CheckinListScreen> {
  List<dynamic> _checkins = [];
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
      final res = await api.get('/checkins', queryParameters: {'per_page': '20', 'page': '$_page', 'sort': '-created_at'});
      if (mounted) setState(() {
        _checkins = (res['data'] as List<dynamic>?) ?? [];
        _lastPage = (res['meta']?['last_page'] as int?) ?? 1;
        _loading = false;
      });
    } catch (e) { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _loadMore() async {
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/checkins', queryParameters: {'per_page': '20', 'page': '$_page'});
      if (mounted) setState(() { _checkins.addAll(res['data'] as List<dynamic>? ?? []); _lastPage = (res['meta']?['last_page'] as int?) ?? 1; });
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('checkin.title'),
      currentRoute: 'checkins',
      floatingActionButton: FloatingActionButton(
        backgroundColor: AppColors.primary,
        onPressed: () => Navigator.pushNamed(context, '/checkin/presential'),
        child: const Icon(Icons.add, color: Colors.white),
      ),
      body: _loading ? const Center(child: CircularProgressIndicator())
          : _checkins.isEmpty ? EmptyState(icon: Icons.check_circle_outline)
          : RefreshIndicator(onRefresh: _load, child: ListView.builder(
              controller: _scrollCtrl, padding: const EdgeInsets.all(16),
              itemCount: _checkins.length,
              itemBuilder: (_, i) => _buildCard(_checkins[i]),
            )),
    );
  }

  Widget _buildCard(dynamic c) {
    final r = c['reservation'] as Map<String, dynamic>?;
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => Navigator.pushNamed(context, '/checkins/detail', arguments: c),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(children: [
            Container(width: 44, height: 44, decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(10)),
              child: const Icon(Icons.check_circle, color: AppColors.primary)),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(r?['guest_name'] ?? 'Check-in', style: const TextStyle(fontWeight: FontWeight.w600)),
              Text('${c['type'] ?? ''} · ${r?['code'] ?? ''}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
            ])),
            StatusBadge(c['status'] as String?),
          ]),
        ),
      ),
    );
  }
}
