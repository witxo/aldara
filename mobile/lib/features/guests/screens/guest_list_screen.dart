import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/i18n/app_localizations.dart';
import '../../../core/api/api_client.dart';
import '../../../core/widgets/app_layout.dart';
import '../../../core/widgets/empty_state.dart';
import '../../auth/providers/auth_provider.dart';

class GuestListScreen extends StatefulWidget {
  const GuestListScreen({super.key});

  @override
  State<GuestListScreen> createState() => _GuestListScreenState();
}

class _GuestListScreenState extends State<GuestListScreen> {
  List<dynamic> _guests = [];
  bool _loading = true;
  int _page = 1, _lastPage = 1;
  final _searchCtrl = TextEditingController();
  final _scrollCtrl = ScrollController();

  @override
  void initState() { super.initState(); _load(); _scrollCtrl.addListener(_onScroll); }

  @override
  void dispose() { _searchCtrl.dispose(); _scrollCtrl.dispose(); super.dispose(); }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200 && _page < _lastPage) {
      _page++; _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() { _loading = true; _page = 1; });
    try {
      final api = context.read<AuthProvider>().api;
      final params = <String, dynamic>{'per_page': '20', 'page': '$_page'};
      if (_searchCtrl.text.isNotEmpty) params['search'] = _searchCtrl.text;
      final res = await api.get('/guests', queryParameters: params);
      if (mounted) setState(() {
        _guests = (res['data'] as List<dynamic>?) ?? [];
        _lastPage = (res['meta']?['last_page'] as int?) ?? 1;
        _loading = false;
      });
    } catch (e) { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _loadMore() async {
    try {
      final api = context.read<AuthProvider>().api;
      final res = await api.get('/guests', queryParameters: {'per_page': '20', 'page': '$_page'});
      if (mounted) setState(() {
        _guests.addAll(res['data'] as List<dynamic>? ?? []);
        _lastPage = (res['meta']?['last_page'] as int?) ?? 1;
      });
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('guest.title'),
      currentRoute: 'guests',
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchCtrl,
              decoration: InputDecoration(hintText: l.translate('common.search'), prefixIcon: const Icon(Icons.search, size: 20), isDense: true, contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10)),
              onSubmitted: (_) => _load(),
            ),
          ),
          Expanded(
            child: _loading ? const Center(child: CircularProgressIndicator())
                : _guests.isEmpty ? EmptyState()
                : RefreshIndicator(onRefresh: _load, child: ListView.builder(
                    controller: _scrollCtrl, padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: _guests.length,
                    itemBuilder: (_, i) => Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(12),
                        onTap: () => Navigator.pushNamed(context, '/guests/detail', arguments: _guests[i]),
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Row(children: [
                            Container(width: 44, height: 44, decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(10)),
                              child: Center(child: Text(((_guests[i]['first_name'] as String? ?? '?')[0]).toUpperCase(), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.primary)))),
                            const SizedBox(width: 12),
                            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              Text('${_guests[i]['first_name'] ?? ''} ${_guests[i]['last_name'] ?? ''}', style: const TextStyle(fontWeight: FontWeight.w600)),
                              Text('${_guests[i]['document_type'] ?? ''} ${_guests[i]['document_number'] ?? ''}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                            ])),
                            const Icon(Icons.chevron_right, color: AppColors.textMuted),
                          ]),
                        ),
                      ),
                    ),
                  )),
          ),
        ],
      ),
    );
  }
}
