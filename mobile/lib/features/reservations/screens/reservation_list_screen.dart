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

class ReservationListScreen extends StatefulWidget {
  const ReservationListScreen({super.key});

  @override
  State<ReservationListScreen> createState() => _ReservationListScreenState();
}

class _ReservationListScreenState extends State<ReservationListScreen> {
  List<dynamic> _reservations = [];
  bool _loading = true;
  bool _loadingMore = false;
  int _page = 1;
  int _lastPage = 1;
  final _searchController = TextEditingController();
  String? _statusFilter;
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _load();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200 && !_loadingMore && _page < _lastPage) {
      _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    _page = 1;
    try {
      final api = context.read<AuthProvider>().api;
      final params = <String, dynamic>{'per_page': '20', 'page': '$_page', 'sort': '-created_at'};
      if (_statusFilter != null) params['status'] = _statusFilter;
      if (_searchController.text.isNotEmpty) params['search'] = _searchController.text;
      final res = await api.get('/reservations', queryParameters: params);
      if (mounted) setState(() {
        _reservations = (res['data'] as List<dynamic>?) ?? [];
        _lastPage = (res['meta']?['last_page'] as int?) ?? 1;
        _loading = false;
      });
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _loadMore() async {
    setState(() => _loadingMore = true);
    _page++;
    try {
      final api = context.read<AuthProvider>().api;
      final params = <String, dynamic>{'per_page': '20', 'page': '$_page', 'sort': '-created_at'};
      if (_statusFilter != null) params['status'] = _statusFilter;
      final res = await api.get('/reservations', queryParameters: params);
      if (mounted) setState(() {
        _reservations.addAll(res['data'] as List<dynamic>? ?? []);
        _lastPage = (res['meta']?['last_page'] as int?) ?? 1;
        _loadingMore = false;
      });
    } catch (e) {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l = AppLocalizations.of(context);
    return AppLayout(
      title: l.translate('reservation.title'),
      currentRoute: 'reservations',
      floatingActionButton: FloatingActionButton(
        backgroundColor: AppColors.primary,
        onPressed: () => Navigator.pushNamed(context, '/reservations/create').then((_) => _load()),
        child: const Icon(Icons.add, color: Colors.white),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: l.translate('common.search'),
                      prefixIcon: const Icon(Icons.search, size: 20),
                      isDense: true,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    ),
                    onSubmitted: (_) => _load(),
                  ),
                ),
                const SizedBox(width: 8),
                PopupMenuButton<String?>(
                  icon: const Icon(Icons.filter_list),
                  onSelected: (v) { setState(() => _statusFilter = v); _load(); },
                  itemBuilder: (_) => [
                    const PopupMenuItem(value: null, child: Text('Todos')),
                    const PopupMenuItem(value: 'confirmed', child: Text('Confirmadas')),
                    const PopupMenuItem(value: 'pending', child: Text('Pendientes')),
                    const PopupMenuItem(value: 'checkin_sent', child: Text('Check-in enviado')),
                    const PopupMenuItem(value: 'completed', child: Text('Completadas')),
                    const PopupMenuItem(value: 'cancelled', child: Text('Canceladas')),
                  ],
                ),
              ],
            ),
          ),
          Expanded(
            child: _loading
              ? const Center(child: CircularProgressIndicator())
              : _reservations.isEmpty
                ? EmptyState(icon: Icons.calendar_today_outlined)
                : RefreshIndicator(
                    onRefresh: _load,
                    child: ListView.builder(
                      controller: _scrollController,
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: _reservations.length + (_loadingMore ? 1 : 0),
                      itemBuilder: (_, i) {
                        if (i >= _reservations.length) return const Center(child: Padding(padding: EdgeInsets.all(16), child: CircularProgressIndicator()));
                        return _buildCard(_reservations[i]);
                      },
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildCard(dynamic r) {
    final ci = r['checkin_date'] != null ? _parseDate(r['checkin_date']) : null;
    final co = r['checkout_date'] != null ? _parseDate(r['checkout_date']) : null;
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => Navigator.pushNamed(context, '/reservations/detail', arguments: r),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              Container(
                width: 44, height: 44,
                decoration: BoxDecoration(color: AppColors.primaryBg, borderRadius: BorderRadius.circular(10)),
                child: Center(child: Text(
                  (r['guest_name'] as String? ?? '?')[0].toUpperCase(),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.primary),
                )),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(r['guest_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 2),
                    if (ci != null && co != null)
                      Text('${DateFormat('dd/MM').format(ci)} - ${DateFormat('dd/MM').format(co)}',
                        style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                    Text('${r['adults'] ?? 0} adultos · ${r['children'] ?? 0} niños',
                      style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                  ],
                ),
              ),
              StatusBadge(r['status'] as String?),
            ],
          ),
        ),
      ),
    );
  }

  DateTime? _parseDate(String? d) {
    if (d == null || d.length < 10) return null;
    try {
      final ds = d.substring(0, 10).split('-');
      return DateTime(int.parse(ds[0]), int.parse(ds[1]), int.parse(ds[2]));
    } catch (_) { return null; }
  }
}
